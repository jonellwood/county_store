<?php
// SanMar Product Scraper API - Berkeley County Store Admin
session_start();
header('Content-Type: application/json');

// Security checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'scrape':
        handleScrapeRequest();
        break;
    case 'progress':
        handleProgressRequest();
        break;
    case 'export':
        handleExportRequest();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleScrapeRequest()
{
    $url = $_POST['url'] ?? '';
    $imageFolder = $_POST['imageFolder'] ?? 'product_images';
    $namingFormat = $_POST['namingFormat'] ?? 'code_color_view';
    $productCodeOverride = $_POST['productCode'] ?? '';

    if (empty($url)) {
        http_response_code(400);
        echo json_encode(['error' => 'URL is required']);
        return;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL) || !str_contains($url, 'sanmar.com')) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide a valid SanMar URL']);
        return;
    }

    try {
        // Start the scraping process
        $result = scrapeProduct($url, $imageFolder, $namingFormat, $productCodeOverride);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Scraping failed: ' . $e->getMessage()]);
    }
}

function scrapeProduct($url, $imageFolder, $namingFormat, $productCodeOverride = '')
{
    $log = [];
    $log[] = "[" . date('Y-m-d H:i:s') . "] Starting SanMar product scraping for: $url";

    if (!empty($productCodeOverride)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Product code override: $productCodeOverride";
    }

    // Set up curl with proper headers to mimic a real browser
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: no-cache',
        ],
        CURLOPT_ENCODING => '', // This tells curl to handle gzip/deflate automatically
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        throw new Exception("Failed to fetch page. HTTP Code: $httpCode");
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] Successfully fetched page content (" . strlen($html) . " bytes)";

    // Parse the HTML (still useful for title extraction, etc.)
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress HTML parsing warnings
    $loadResult = $dom->loadHTML($html);
    libxml_clear_errors();

    if (!$loadResult) {
        throw new Exception("Failed to parse HTML content");
    }

    $xpath = new DOMXPath($dom);

    // Add some debugging to see what we can find
    $log[] = "[" . date('Y-m-d H:i:s') . "] Analyzing page structure...";
    try {
        debugPageStructure($html, $log);
    } catch (Exception $e) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Debug error: " . $e->getMessage();
    }

    // Extract product information (pass raw HTML for JSON extraction + xpath for DOM queries)
    $productInfo = extractProductInfo($html, $xpath, $log);

    // Use override if provided, otherwise use extracted code
    if (!empty($productCodeOverride)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Using product code override: {$productCodeOverride} (replacing {$productInfo['code']})";
        $productInfo['code'] = $productCodeOverride;
    }

    // Extract image URLs
    $images = extractImageUrls($productInfo, $log);

    // Download images
    $downloadedImages = downloadImages($images, $imageFolder, $namingFormat, $productInfo, $log);

    $log[] = "[" . date('Y-m-d H:i:s') . "] Scraping completed successfully!";

    return [
        'success' => true,
        'productInfo' => $productInfo,
        'images' => $downloadedImages,
        'log' => $log,
        'summary' => [
            'totalImages' => count($downloadedImages),
            'productCode' => $productInfo['code'] ?? 'Unknown',
            'productName' => $productInfo['name'] ?? 'Unknown',
            'colorsFound' => count($productInfo['colors'] ?? [])
        ]
    ];
}

function debugPageStructure($html, &$log)
{
    // Check for the embedded JSON product data (new SanMar site structure)
    $hasGalleryImages = strpos($html, 'galleryImages') !== false;
    $hasStyleNumber = strpos($html, '"styleNumber"') !== false;
    $hasVariantOptions = strpos($html, 'variantOptionQualifiers') !== false;

    $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - galleryImages JSON found: " . ($hasGalleryImages ? 'YES' : 'NO');
    $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - styleNumber JSON found: " . ($hasStyleNumber ? 'YES' : 'NO');
    $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - variantOptions JSON found: " . ($hasVariantOptions ? 'YES' : 'NO');
    $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - Page size: " . strlen($html) . " bytes";
}

function extractProductInfo($html, $xpath, &$log)
{
    $info = [];

    // The new SanMar site embeds product data as JSON in a JavaScript variable
    // Look for the JSON object containing styleNumber, variantOptions, galleryImages, etc.
    $log[] = "[" . date('Y-m-d H:i:s') . "] Extracting product info from embedded JSON...";

    // Extract the main product JSON data block
    $productJson = extractEmbeddedJson($html, $log);

    if ($productJson) {
        // Extract style number
        if (isset($productJson['styleNumber'])) {
            $info['code'] = $productJson['styleNumber'];
            $log[] = "[" . date('Y-m-d H:i:s') . "] Found style number: " . $info['code'];
        }

        // Extract brand name
        if (isset($productJson['brandName'])) {
            $info['brand'] = $productJson['brandName'];
            $log[] = "[" . date('Y-m-d H:i:s') . "] Found brand: " . $info['brand'];
        }

        // Extract product name from title tag or page content
        $titleElements = $xpath->query("//title");
        if ($titleElements->length > 0) {
            $titleText = trim($titleElements->item(0)->textContent);
            // Clean up - remove "SanMar" suffix
            $titleText = preg_replace('/\s*[-|]\s*SanMar.*$/', '', $titleText);
            $info['name'] = trim($titleText);
        }
        // Also check h1/h3 elements with product name
        if (empty($info['name'])) {
            $h1Elements = $xpath->query("//h1 | //h3");
            if ($h1Elements->length > 0) {
                $info['name'] = trim($h1Elements->item(0)->textContent);
            }
        }
        $log[] = "[" . date('Y-m-d H:i:s') . "] Found product name: " . ($info['name'] ?? 'Unknown');

        // Extract product description/message
        if (isset($productJson['productMessage'])) {
            $info['description'] = $productJson['productMessage'];
        }

        // Extract gallery images for the current color
        if (isset($productJson['galleryImages'])) {
            $info['gallery_images'] = $productJson['galleryImages'];
            $log[] = "[" . date('Y-m-d H:i:s') . "] Found " . count($productJson['galleryImages']) . " gallery images for current color";
        }

        // Extract color variants from the "All Colors" filter option
        $colors = [];
        $colorData = [];

        // First check variantOptions (flat list of all variants)
        if (isset($productJson['variantOptions'])) {
            foreach ($productJson['variantOptions'] as $variant) {
                $colorName = '';
                $variantCode = $variant['code'] ?? '';
                $variantUrl = $variant['url'] ?? '';

                // Get color name from variantOptionQualifiers
                if (isset($variant['variantOptionQualifiers'])) {
                    foreach ($variant['variantOptionQualifiers'] as $qualifier) {
                        if ($qualifier['qualifier'] === 'colourCategoryCode') {
                            $colorName = $qualifier['value'] ?? '';
                            break;
                        }
                    }
                }

                if (!empty($colorName) && !in_array($colorName, $colors)) {
                    $colors[] = $colorName;
                    $colorData[] = [
                        'name' => $colorName,
                        'variant_code' => $variantCode,
                        'url' => 'https://www.sanmar.com' . $variantUrl
                    ];
                }
            }
        }

        // Also check filterOptions for the "All Colors" group
        if (empty($colors) && isset($productJson['filterOptions'])) {
            foreach ($productJson['filterOptions'] as $filterGroup) {
                if (isset($filterGroup['name']) && $filterGroup['name'] === 'All Colors') {
                    foreach ($filterGroup['variantOptions'] as $variant) {
                        $colorName = '';
                        $variantCode = $variant['code'] ?? '';
                        $variantUrl = $variant['url'] ?? '';

                        if (isset($variant['variantOptionQualifiers'])) {
                            foreach ($variant['variantOptionQualifiers'] as $qualifier) {
                                if ($qualifier['qualifier'] === 'colourCategoryCode') {
                                    $colorName = $qualifier['value'] ?? '';
                                    break;
                                }
                            }
                        }

                        if (!empty($colorName) && !in_array($colorName, $colors)) {
                            $colors[] = $colorName;
                            $colorData[] = [
                                'name' => $colorName,
                                'variant_code' => $variantCode,
                                'url' => 'https://www.sanmar.com' . $variantUrl
                            ];
                        }
                    }
                    break; // Only need "All Colors"
                }
            }
        }

        $info['colors'] = $colors;
        $info['color_data'] = $colorData;

        $log[] = "[" . date('Y-m-d H:i:s') . "] Found " . count($colors) . " colors: " . implode(', ', array_slice($colors, 0, 10));
        if (count($colors) > 10) {
            $log[] = "[" . date('Y-m-d H:i:s') . "] ... and " . (count($colors) - 10) . " more colors";
        }
    } else {
        $log[] = "[" . date('Y-m-d H:i:s') . "] WARNING: Could not extract embedded JSON data. Falling back to basic extraction.";

        // Fallback: try to extract from URL
        if (preg_match('/\/p\/\d+_([^\/\?#]+)/', $_POST['url'] ?? '', $matches)) {
            $info['colors'] = [$matches[1]];
            $info['color_data'] = [];
        }
    }

    return $info;
}

/**
 * Extract the embedded JSON product data from the HTML page.
 * SanMar embeds a large JSON object in a script tag containing
 * galleryImages, styleNumber, variantOptions, etc.
 */
function extractEmbeddedJson($html, &$log)
{
    // Strategy 1: Look for JSON containing galleryImages and styleNumber
    // The JSON is embedded in a script block, often as a JS variable assignment
    if (preg_match('/"styleNumber"\s*:\s*"([^"]+)"/', $html, $styleMatch)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Found styleNumber in HTML: " . $styleMatch[1];
    }

    // Find the large JSON block containing galleryImages
    // It's typically in a pattern like: {...,"galleryImages":[...],...,"styleNumber":"C402",...}
    // We need to find the start of the JSON object that contains these keys

    // Look for the galleryImages array and extract the surrounding JSON object
    $galleryPos = strpos($html, '"galleryImages"');
    if ($galleryPos === false) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Could not find galleryImages in HTML";
        return null;
    }

    // Find the beginning of the JSON object containing galleryImages
    // Walk backwards from galleryPos to find the opening brace
    $searchStart = max(0, $galleryPos - 50000); // The JSON object can be very large
    $segment = substr($html, $searchStart, $galleryPos - $searchStart);

    // Find the last opening brace pattern that looks like start of product data
    // Look for a pattern like {"variantOptions" or {"code" that starts the product JSON
    $braceDepth = 0;
    $objectStart = $galleryPos;

    // Walk backwards to find the matching opening brace
    for ($i = strlen($segment) - 1; $i >= 0; $i--) {
        $char = $segment[$i];
        if ($char === '}' || $char === ']') {
            $braceDepth++;
        } elseif ($char === '{' || $char === '[') {
            if ($braceDepth === 0) {
                $objectStart = $searchStart + $i;
                break;
            }
            $braceDepth--;
        }
    }

    // Now extract from objectStart forward, finding the matching closing brace
    $jsonCandidate = substr($html, $objectStart);
    $braceDepth = 0;
    $objectEnd = 0;
    $inString = false;
    $escape = false;

    for ($i = 0; $i < strlen($jsonCandidate) && $i < 200000; $i++) {
        $char = $jsonCandidate[$i];

        if ($escape) {
            $escape = false;
            continue;
        }

        if ($char === '\\') {
            $escape = true;
            continue;
        }

        if ($char === '"') {
            $inString = !$inString;
            continue;
        }

        if (!$inString) {
            if ($char === '{') {
                $braceDepth++;
            } elseif ($char === '}') {
                $braceDepth--;
                if ($braceDepth === 0) {
                    $objectEnd = $i + 1;
                    break;
                }
            }
        }
    }

    if ($objectEnd === 0) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Could not find matching closing brace for product JSON";
        return null;
    }

    $jsonString = substr($jsonCandidate, 0, $objectEnd);

    // Unescape unicode sequences that SanMar uses (e.g., \u003d for =)
    $jsonString = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
    }, $jsonString);

    $decoded = json_decode($jsonString, true);
    if ($decoded === null) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] JSON decode failed: " . json_last_error_msg();
        $log[] = "[" . date('Y-m-d H:i:s') . "] JSON sample (first 500 chars): " . substr($jsonString, 0, 500);
        return null;
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] Successfully parsed product JSON (" . strlen($jsonString) . " bytes)";

    return $decoded;
}

function extractImageUrls($productInfo, &$log)
{
    $images = [];
    $colorData = $productInfo['color_data'] ?? [];

    if (empty($colorData)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] No color data found, cannot extract images";
        return $images;
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] Starting to scrape images for " . count($colorData) . " color variants";

    // If the current page already has gallery images (from the initially loaded color),
    // we can use those directly for the first color
    $firstColorHandled = false;
    $currentGalleryImages = $productInfo['gallery_images'] ?? [];

    foreach ($colorData as $index => $color) {
        $colorName = $color['name'];
        $variantCode = $color['variant_code'];
        $variantUrl = $color['url'];

        $log[] = "[" . date('Y-m-d H:i:s') . "] Scraping images for color: $colorName ($variantCode) [" . ($index + 1) . "/" . count($colorData) . "]";

        try {
            $galleryImages = [];

            // For the first page load, we may already have gallery images
            if (!$firstColorHandled && !empty($currentGalleryImages)) {
                $galleryImages = $currentGalleryImages;
                $firstColorHandled = true;
                $log[] = "[" . date('Y-m-d H:i:s') . "] Using gallery images from initial page load";
            } else {
                // Fetch the color variant page
                $variantHtml = fetchPageContent($variantUrl);
                if (!$variantHtml) {
                    $log[] = "[" . date('Y-m-d H:i:s') . "] Failed to fetch variant page: $variantUrl";
                    continue;
                }

                // Extract the JSON from the variant page to get gallery images
                $variantJson = extractEmbeddedJson($variantHtml, $log);
                if ($variantJson && isset($variantJson['galleryImages'])) {
                    $galleryImages = $variantJson['galleryImages'];
                    $log[] = "[" . date('Y-m-d H:i:s') . "] Found " . count($galleryImages) . " gallery images for $colorName";
                } else {
                    $log[] = "[" . date('Y-m-d H:i:s') . "] No gallery images found in JSON for $colorName";
                    continue;
                }

                // Small delay to be respectful to the server
                usleep(500000); // 0.5 seconds
            }

            // Process gallery images - prefer zoom (1200W) format
            foreach ($galleryImages as $galleryImage) {
                $imageUrl = '';
                $mediaCode = '';

                // Prefer zoom (highest resolution, 1200W)
                if (isset($galleryImage['zoom']['url'])) {
                    $imageUrl = $galleryImage['zoom']['url'];
                    $mediaCode = $galleryImage['zoom']['mediaCode'] ?? '';
                }
                // Fallback to mainImage (624W)
                elseif (isset($galleryImage['mainImage']['url'])) {
                    $imageUrl = $galleryImage['mainImage']['url'];
                    $mediaCode = $galleryImage['mainImage']['mediaCode'] ?? '';
                }

                if (empty($imageUrl)) continue;

                // Convert protocol-relative URLs to absolute
                if (strpos($imageUrl, '//') === 0) {
                    $imageUrl = 'https:' . $imageUrl;
                }

                // Determine view type from mediaCode
                $viewType = determineViewType($mediaCode ?: $imageUrl);

                // Generate a clean filename
                $extension = 'jpg';
                if (preg_match('/\.(\w+)$/', parse_url($imageUrl, PHP_URL_PATH), $extMatch)) {
                    $extension = $extMatch[1];
                }

                $cleanColorName = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(['/', ' '], ['_', '_'], $colorName));
                $styleCode = $productInfo['code'] ?? 'unknown';
                $filename = "{$styleCode}_{$cleanColorName}_{$viewType}.{$extension}";

                $images[] = [
                    'url' => $imageUrl,
                    'color' => $colorName,
                    'variant_code' => $variantCode,
                    'view' => $viewType,
                    'filename' => $filename,
                    'media_code' => $mediaCode
                ];

                $log[] = "[" . date('Y-m-d H:i:s') . "] Found image: $colorName ($viewType) - " . basename($imageUrl);
            }
        } catch (Exception $e) {
            $log[] = "[" . date('Y-m-d H:i:s') . "] Error scraping color $colorName: " . $e->getMessage();
        }
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] Total images found: " . count($images);

    return $images;
}

function fetchPageContent($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: no-cache',
        ],
        CURLOPT_ENCODING => '', // Handle gzip/deflate automatically
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $html : false;
}

function determineViewType($mediaCodeOrUrl)
{
    $text = strtolower($mediaCodeOrUrl);

    // New SanMar mediaCode patterns (e.g., "8332_WdlndCmBk-1-C112WdlndCmBkHatLeft_1200W")
    // View types are embedded in the mediaCode
    if (str_contains($text, 'hatleft') || str_contains($text, 'capleft')) {
        return 'left';
    } elseif (str_contains($text, 'hatright') || str_contains($text, 'capright')) {
        return 'right';
    } elseif (str_contains($text, 'hatstraight') || str_contains($text, 'capstraight') || str_contains($text, 'hatfront') || str_contains($text, 'capfront')) {
        return 'front';
    } elseif (str_contains($text, 'hatback') || str_contains($text, 'capback')) {
        return 'back';
    } elseif (str_contains($text, 'modelfront') || str_contains($text, 'modelfront')) {
        return 'model_front';
    } elseif (str_contains($text, 'modelback')) {
        return 'model_back';
    } elseif (str_contains($text, 'modelside') || str_contains($text, 'model34')) {
        return 'model_side';
    } elseif (str_contains($text, 'model')) {
        return 'model';
    } elseif (str_contains($text, 'flat') || str_contains($text, 'flt')) {
        return 'flat';
    } elseif (str_contains($text, 'front')) {
        return 'front';
    } elseif (str_contains($text, 'back')) {
        return 'back';
    } elseif (str_contains($text, 'side') || str_contains($text, 'left') || str_contains($text, 'right')) {
        return 'side';
    } elseif (str_contains($text, 'detail') || str_contains($text, 'closeup') || str_contains($text, 'close-up')) {
        return 'detail';
    } else {
        return 'main';
    }
}

function extractColorFromFilename($src, $alt)
{
    $colorPatterns = [
        'black' => ['black', 'blk', 'bk'],
        'white' => ['white', 'wht', 'wh'],
        'navy' => ['navy', 'nvy', 'nv'],
        'red' => ['red', 'rd'],
        'blue' => ['blue', 'blu', 'bl'],
        'green' => ['green', 'grn', 'gr'],
        'gray' => ['gray', 'grey', 'gry'],
        'yellow' => ['yellow', 'ylw'],
        'purple' => ['purple', 'prpl'],
        'orange' => ['orange', 'orng'],
        'pink' => ['pink', 'pk'],
        'brown' => ['brown', 'brn'],
    ];

    $text = strtolower($src . ' ' . $alt);

    foreach ($colorPatterns as $color => $patterns) {
        foreach ($patterns as $pattern) {
            if (str_contains($text, $pattern)) {
                return $color;
            }
        }
    }

    return 'unknown';
}

function extractViewFromFilename($src, $alt)
{
    $viewPatterns = [
        'front' => ['front', 'frnt', 'f'],
        'back' => ['back', 'bk', 'b'],
        'side' => ['side', 'sd', 's'],
        'sleeve' => ['sleeve', 'slv'],
        'detail' => ['detail', 'dtl'],
        'flat' => ['flat', 'flt'],
    ];

    $text = strtolower($src . ' ' . $alt);

    foreach ($viewPatterns as $view => $patterns) {
        foreach ($patterns as $pattern) {
            if (str_contains($text, $pattern)) {
                return $view;
            }
        }
    }

    return 'main';
}

function downloadImages($images, $folder, $namingFormat, $productInfo, &$log)
{
    $downloadedImages = [];

    // Save directly to product-images folder (not in downloads)
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/product-images/';

    // If document root doesn't exist (local dev), use current directory
    if (!is_dir($_SERVER['DOCUMENT_ROOT'])) {
        $baseDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/product-images/';
    }

    $downloadDir = $baseDir;

    // Enhanced debugging information
    $log[] = "[" . date('Y-m-d H:i:s') . "] === DIRECTORY DEBUG INFO ===";
    $log[] = "[" . date('Y-m-d H:i:s') . "] DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'];
    $log[] = "[" . date('Y-m-d H:i:s') . "] __FILE__: " . __FILE__;
    $log[] = "[" . date('Y-m-d H:i:s') . "] dirname(__FILE__): " . dirname(__FILE__);
    $log[] = "[" . date('Y-m-d H:i:s') . "] Base directory: $baseDir";
    $log[] = "[" . date('Y-m-d H:i:s') . "] Download directory: $downloadDir";
    $log[] = "[" . date('Y-m-d H:i:s') . "] Directory exists: " . (is_dir($downloadDir) ? 'YES' : 'NO');
    $log[] = "[" . date('Y-m-d H:i:s') . "] Directory writable: " . (is_writable($downloadDir) ? 'YES' : 'NO');

    if (is_dir($downloadDir)) {
        $perms = substr(sprintf('%o', fileperms($downloadDir)), -4);
        $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($downloadDir))['name'] : 'unknown';
        $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($downloadDir))['name'] : 'unknown';
        $log[] = "[" . date('Y-m-d H:i:s') . "] Directory permissions: $perms";
        $log[] = "[" . date('Y-m-d H:i:s') . "] Directory owner: $owner";
        $log[] = "[" . date('Y-m-d H:i:s') . "] Directory group: $group";
    }

    $currentUser = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown';
    $log[] = "[" . date('Y-m-d H:i:s') . "] PHP running as: $currentUser";
    $log[] = "[" . date('Y-m-d H:i:s') . "] === END DEBUG INFO ===";

    // Create download directory if it doesn't exist
    if (!is_dir($downloadDir)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Attempting to create directory...";
        if (!mkdir($downloadDir, 0775, true)) {
            $error = error_get_last();
            $log[] = "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to create directory: $downloadDir";
            $log[] = "[" . date('Y-m-d H:i:s') . "] mkdir error: " . ($error['message'] ?? 'Unknown error');
            return $downloadedImages;
        }
        // Set ownership to www-data if possible
        @chown($downloadDir, 'www-data');
        @chgrp($downloadDir, 'www-data');
        $log[] = "[" . date('Y-m-d H:i:s') . "] Created download directory: $downloadDir";
    }

    $productCode = $productInfo['code'] ?? 'unknown';
    $log[] = "[" . date('Y-m-d H:i:s') . "] Processing " . count($images) . " images...";

    foreach ($images as $index => $image) {
        try {
            // Generate filename based on naming format
            $variantCode = $image['variant_code'] ?? '';
            $filename = generateFilename($namingFormat, $productCode, $image['color'], $image['view'], $index, $variantCode);
            $extension = pathinfo($image['filename'], PATHINFO_EXTENSION) ?: 'jpg';
            $fullFilename = $filename . '.' . $extension;
            $filePath = $downloadDir . $fullFilename;

            $log[] = "[" . date('Y-m-d H:i:s') . "] [" . ($index + 1) . "/" . count($images) . "] Downloading from: " . $image['url'];
            $log[] = "[" . date('Y-m-d H:i:s') . "] [" . ($index + 1) . "/" . count($images) . "] Saving to: $filePath";

            // Download the image
            $imageData = downloadImageFile($image['url']);
            if ($imageData) {
                $log[] = "[" . date('Y-m-d H:i:s') . "] [" . ($index + 1) . "/" . count($images) . "] Downloaded " . formatBytes(strlen($imageData));

                $bytesWritten = @file_put_contents($filePath, $imageData);

                if ($bytesWritten === false) {
                    $error = error_get_last();
                    $log[] = "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to write file: $filePath";
                    $log[] = "[" . date('Y-m-d H:i:s') . "] PHP Error: " . ($error['message'] ?? 'Unknown error');
                    $log[] = "[" . date('Y-m-d H:i:s') . "] Parent directory writable: " . (is_writable(dirname($filePath)) ? 'YES' : 'NO');
                    continue;
                }

                // Verify file was actually written
                if (!file_exists($filePath)) {
                    $log[] = "[" . date('Y-m-d H:i:s') . "] ERROR: File does not exist after write: $filePath";
                    continue;
                }

                // Set file permissions
                @chmod($filePath, 0664);

                $downloadedImages[] = [
                    'original_url' => $image['url'],
                    'filename' => $fullFilename,
                    'path' => $filePath,
                    'color' => $image['color'],
                    'view' => $image['view'],
                    'size' => strlen($imageData)
                ];

                $log[] = "[" . date('Y-m-d H:i:s') . "] ✓ Successfully saved: $fullFilename (" . formatBytes($bytesWritten) . ")";
            } else {
                $log[] = "[" . date('Y-m-d H:i:s') . "] ERROR: Failed to download image from: " . $image['url'];
            }
        } catch (Exception $e) {
            $log[] = "[" . date('Y-m-d H:i:s') . "] EXCEPTION: " . $e->getMessage();
            $log[] = "[" . date('Y-m-d H:i:s') . "] Stack trace: " . $e->getTraceAsString();
        }
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] === DOWNLOAD SUMMARY ===";
    $log[] = "[" . date('Y-m-d H:i:s') . "] Total images attempted: " . count($images);
    $log[] = "[" . date('Y-m-d H:i:s') . "] Total images saved: " . count($downloadedImages);

    return $downloadedImages;
}

function downloadImageFile($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ]);

    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $data : false;
}

function generateFilename($format, $code, $color, $view, $index, $variantCode = '')
{
    // Clean up the color name for filename - lowercase everything
    $cleanColor = preg_replace('/[^A-Za-z0-9]/', '', $color);
    $cleanColor = strtolower($cleanColor);

    // Lowercase the product code too
    $code = strtolower($code);

    // Use variant code if available (SanMar specific)
    if (!empty($variantCode)) {
        // Extract just the color part from variant code (e.g., "60374_DeepBlack" -> "deepblack")
        $parts = explode('_', $variantCode);
        if (count($parts) >= 2) {
            // Get the color portion and lowercase it
            $variantColor = strtolower($parts[1]);
            $productCode = strtolower($parts[0]);
        } else {
            $variantColor = strtolower($variantCode);
            $productCode = $code;
        }

        switch ($format) {
            case 'code_color_view':
                return $productCode . '_' . $variantColor . '_' . $view;
            case 'code_color':
                // No index number for code_color format
                return $productCode . '_' . $variantColor;
            case 'color_code':
                return $variantColor . '_' . $productCode;
            default:
                return $productCode . '_' . $variantColor . '_' . $view;
        }
    }

    // Fallback to original logic (if no variant code)
    switch ($format) {
        case 'code_color_view':
            return $code . '_' . $cleanColor . '_' . $view;
        case 'code_color':
            // No index number for code_color format
            return $code . '_' . $cleanColor;
        case 'color_code':
            return $cleanColor . '_' . $code;
        default:
            return $code . '_' . $cleanColor . '_' . $view;
    }
}

function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

function handleProgressRequest()
{
    // This would be used for real-time progress updates in a more advanced implementation
    echo json_encode(['progress' => 100, 'status' => 'completed']);
}

function handleExportRequest()
{
    $data = $_POST['data'] ?? '';
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'No data to export']);
        return;
    }

    $productData = json_decode($data, true);
    if (!$productData) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data format']);
        return;
    }

    try {
        $exportData = generateProductExport($productData);
        echo json_encode([
            'success' => true,
            'export' => $exportData,
            'filename' => 'product_export_' . date('Y-m-d_H-i-s') . '.json'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
    }
}

function generateProductExport($scrapedData)
{
    $productInfo = $scrapedData['productInfo'] ?? [];
    $images = $scrapedData['images'] ?? [];
    $summary = $scrapedData['summary'] ?? [];

    // Generate structured data for product entry
    $exportData = [
        'product' => [
            'code' => $summary['productCode'] ?? '',
            'name' => $summary['productName'] ?? '',
            'description' => $productInfo['description'] ?? '',
            'brand' => 'SanMar', // Assuming SanMar as brand
            'category' => '', // To be filled manually
            'active' => true,
            'featured' => false
        ],
        'colors' => [],
        'sizes' => [], // To be filled manually
        'images' => [],
        'pricing' => [
            'base_price' => '', // To be filled manually
            'cost' => '', // To be filled manually
            'vendor_id' => '' // To be filled manually
        ],
        'metadata' => [
            'scraped_date' => date('Y-m-d H:i:s'),
            'total_images' => count($images),
            'colors_found' => $summary['colorsFound'] ?? 0,
            'source_url' => $scrapedData['source_url'] ?? ''
        ]
    ];

    // Process colors and images
    $colorMap = [];
    foreach ($images as $image) {
        $color = $image['color'];
        if (!isset($colorMap[$color])) {
            $colorMap[$color] = [
                'name' => ucfirst($color),
                'code' => strtoupper(substr($color, 0, 3)),
                'images' => []
            ];
        }

        $colorMap[$color]['images'][] = [
            'filename' => $image['filename'],
            'view' => $image['view'],
            'size' => $image['size'],
            'path' => $image['path']
        ];
    }

    $exportData['colors'] = array_values($colorMap);
    $exportData['images'] = $images;

    return $exportData;
}
