<?php
// SanMar Product Scraper API - LOCAL DEVELOPMENT VERSION (No Auth)
// This version is for local development only - authentication disabled

// For local development, simulate logged-in admin user
$_SESSION = [
    'loggedin' => true,
    'role_id' => 1,
    'empName' => 'Local Developer',
    'empNumber' => 'DEV001'
];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'URL is required']);
    exit;
}

$url = $input['url'];
$imageFolder = $input['imageFolder'] ?? 'product_images';
$namingFormat = $input['namingFormat'] ?? 'code_color_view';

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL) || !str_contains($url, 'sanmar.com')) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid SanMar URL']);
    exit;
}

// Create downloads directory if it doesn't exist
$downloadDir = __DIR__ . '/downloads/' . $imageFolder;
if (!file_exists($downloadDir)) {
    if (!mkdir($downloadDir, 0755, true)) {
        echo json_encode(['error' => 'Failed to create download directory']);
        exit;
    }
}

// Function to send progress updates
function sendProgress($percentage, $message, $data = null)
{
    $response = [
        'progress' => $percentage,
        'message' => $message,
        'timestamp' => date('H:i:s')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response) . "\n";
    ob_flush();
    flush();
}

// Function to download image with proper headers
function downloadImage($imageUrl, $savePath)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive'
    ]);

    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($imageData && $httpCode === 200) {
        return file_put_contents($savePath, $imageData);
    }

    return false;
}

// Function to fetch a page with proper headers
function scrapeProductData($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // Handle gzip/deflate automatically
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
        'Cache-Control: no-cache',
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$html || $httpCode !== 200) {
        throw new Exception("Failed to fetch page: HTTP $httpCode");
    }

    return $html;
}

// Function to extract product code from URL
function extractProductCode($url)
{
    // Pattern: https://www.sanmar.com/p/5682_DkGreenNv
    if (preg_match('/\/p\/([^_]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Extract the embedded JSON product data from the HTML page.
 * SanMar embeds a large JSON object in a script tag containing
 * galleryImages, styleNumber, variantOptions, etc.
 */
function extractEmbeddedJson($html, &$messages)
{
    // Find the galleryImages marker in the HTML
    $galleryPos = strpos($html, '"galleryImages"');
    if ($galleryPos === false) {
        $messages[] = "Could not find galleryImages in HTML";
        return null;
    }

    // Walk backwards from galleryPos to find the opening brace of the containing JSON object
    $searchStart = max(0, $galleryPos - 50000);
    $segment = substr($html, $searchStart, $galleryPos - $searchStart);

    $braceDepth = 0;
    $objectStart = $galleryPos;

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

    // Extract from objectStart forward, finding the matching closing brace
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
        $messages[] = "Could not find matching closing brace for product JSON";
        return null;
    }

    $jsonString = substr($jsonCandidate, 0, $objectEnd);

    // Unescape unicode sequences
    $jsonString = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
    }, $jsonString);

    $decoded = json_decode($jsonString, true);
    if ($decoded === null) {
        $messages[] = "JSON decode failed: " . json_last_error_msg();
        return null;
    }

    $messages[] = "Successfully parsed product JSON (" . strlen($jsonString) . " bytes)";
    return $decoded;
}

/**
 * Determine view type from SanMar mediaCode string
 */
function determineViewType($mediaCodeOrUrl)
{
    $text = strtolower($mediaCodeOrUrl);

    if (str_contains($text, 'hatleft') || str_contains($text, 'capleft')) return 'left';
    if (str_contains($text, 'hatright') || str_contains($text, 'capright')) return 'right';
    if (str_contains($text, 'hatstraight') || str_contains($text, 'capstraight') || str_contains($text, 'hatfront') || str_contains($text, 'capfront')) return 'front';
    if (str_contains($text, 'hatback') || str_contains($text, 'capback')) return 'back';
    if (str_contains($text, 'modelfront')) return 'model_front';
    if (str_contains($text, 'modelback')) return 'model_back';
    if (str_contains($text, 'modelside') || str_contains($text, 'model34')) return 'model_side';
    if (str_contains($text, 'model')) return 'model';
    if (str_contains($text, 'flat') || str_contains($text, 'flt')) return 'flat';
    if (str_contains($text, 'front')) return 'front';
    if (str_contains($text, 'back')) return 'back';
    if (str_contains($text, 'side') || str_contains($text, 'left') || str_contains($text, 'right')) return 'side';
    if (str_contains($text, 'detail') || str_contains($text, 'closeup')) return 'detail';
    return 'main';
}

// Start processing
try {
    // Enable output buffering for streaming response
    ob_start();

    sendProgress(10, "Fetching product page...");

    $html = scrapeProductData($url);

    sendProgress(20, "Parsing embedded product JSON...");

    // Extract product code from URL
    $productCode = extractProductCode($url);
    if (!$productCode) {
        throw new Exception("Could not extract product code from URL");
    }

    // Extract embedded JSON data
    $debugMessages = [];
    $productJson = extractEmbeddedJson($html, $debugMessages);

    foreach ($debugMessages as $msg) {
        sendProgress(22, "Debug: $msg");
    }

    if (!$productJson) {
        throw new Exception("Could not extract product data from page. SanMar page structure may have changed.");
    }

    // Extract product info from JSON
    $styleNumber = $productJson['styleNumber'] ?? $productCode;
    $brandName = $productJson['brandName'] ?? '';

    // Extract product name from title tag
    $productName = '';
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch)) {
        $productName = trim(strip_tags($titleMatch[1]));
        $productName = preg_replace('/\s*[-|]\s*SanMar.*$/', '', $productName);
    }

    $productDescription = $productJson['productMessage'] ?? '';

    sendProgress(30, "Found product: $productName ($styleNumber)");

    // Extract color variants from JSON
    $colors = [];
    $colorData = [];

    // Check variantOptions first
    if (isset($productJson['variantOptions'])) {
        foreach ($productJson['variantOptions'] as $variant) {
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
    }

    // Fallback: check filterOptions
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
                break;
            }
        }
    }

    sendProgress(40, "Found " . count($colors) . " color variants");

    // Collect all images for all colors
    $allImages = [];
    $totalColors = count($colorData);

    // Gallery images for the first color are already in the JSON
    $currentGalleryImages = $productJson['galleryImages'] ?? [];

    foreach ($colorData as $colorIndex => $color) {
        $colorName = $color['name'];
        $variantCode = $color['variant_code'];
        $variantUrl = $color['url'];

        $colorProgress = 40 + (($colorIndex + 1) / $totalColors) * 30;
        sendProgress($colorProgress, "Processing color: $colorName (" . ($colorIndex + 1) . "/$totalColors)");

        try {
            $galleryImages = [];

            if ($colorIndex === 0 && !empty($currentGalleryImages)) {
                $galleryImages = $currentGalleryImages;
            } else {
                // Fetch the color variant page
                $colorHtml = scrapeProductData($variantUrl);
                $colorMessages = [];
                $colorJson = extractEmbeddedJson($colorHtml, $colorMessages);

                if ($colorJson && isset($colorJson['galleryImages'])) {
                    $galleryImages = $colorJson['galleryImages'];
                }

                usleep(500000); // 0.5s delay between requests
            }

            // Extract zoom (1200W) images from gallery
            foreach ($galleryImages as $galleryImage) {
                $imageUrl = '';
                $mediaCode = '';

                // Prefer zoom (highest resolution)
                if (isset($galleryImage['zoom']['url'])) {
                    $imageUrl = $galleryImage['zoom']['url'];
                    $mediaCode = $galleryImage['zoom']['mediaCode'] ?? '';
                } elseif (isset($galleryImage['mainImage']['url'])) {
                    $imageUrl = $galleryImage['mainImage']['url'];
                    $mediaCode = $galleryImage['mainImage']['mediaCode'] ?? '';
                }

                if (empty($imageUrl)) continue;

                // Convert protocol-relative URLs
                if (strpos($imageUrl, '//') === 0) {
                    $imageUrl = 'https:' . $imageUrl;
                }

                $viewType = determineViewType($mediaCode ?: $imageUrl);

                // Generate filename
                $cleanColor = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(['/', ' '], ['_', '_'], $colorName));

                switch ($namingFormat) {
                    case 'code_color':
                        $filename = "{$styleNumber}_{$cleanColor}.jpg";
                        break;
                    case 'color_code':
                        $filename = "{$cleanColor}_{$styleNumber}.jpg";
                        break;
                    case 'code_color_view':
                    default:
                        $filename = "{$styleNumber}_{$cleanColor}_{$viewType}.jpg";
                        break;
                }

                $allImages[] = [
                    'url' => $imageUrl,
                    'filename' => $filename,
                    'color' => $colorName,
                    'variant_code' => $variantCode,
                    'view' => $viewType,
                    'media_code' => $mediaCode
                ];
            }

            sendProgress($colorProgress, "Found " . count($galleryImages) . " images for $colorName");
        } catch (Exception $e) {
            sendProgress($colorProgress, "Error processing color $colorName: " . $e->getMessage());
        }
    }

    sendProgress(70, "Found " . count($allImages) . " total images across all colors");

    // Download images
    $totalImages = count($allImages);
    $downloadedCount = 0;

    $productData = [
        'code' => $styleNumber,
        'name' => $productName,
        'brand' => $brandName,
        'description' => $productDescription,
        'url' => $url,
        'colors' => $colors,
        'images' => [],
        'download_folder' => $imageFolder,
        'naming_format' => $namingFormat,
        'scraped_at' => date('Y-m-d H:i:s'),
        'debug_info' => [
            'total_colors_found' => count($colors),
            'total_image_urls_found' => $totalImages
        ]
    ];

    foreach ($allImages as $index => $image) {
        $progress = 70 + (($index + 1) / max($totalImages, 1)) * 25;
        $savePath = $downloadDir . '/' . $image['filename'];

        sendProgress($progress, "Downloading: " . $image['filename']);

        if (downloadImage($image['url'], $savePath)) {
            $downloadedCount++;
            $productData['images'][] = [
                'url' => $image['url'],
                'filename' => $image['filename'],
                'local_path' => $savePath,
                'color' => $image['color'],
                'view' => $image['view'],
                'size' => filesize($savePath)
            ];
            sendProgress($progress, "✓ Downloaded: " . $image['filename'] . " (" . round(filesize($savePath) / 1024, 1) . " KB)");
        } else {
            sendProgress($progress, "✗ Failed to download: " . $image['filename']);
            $productData['images'][] = [
                'url' => $image['url'],
                'filename' => $image['filename'],
                'local_path' => null,
                'color' => $image['color'],
                'view' => $image['view'],
                'size' => 0,
                'download_failed' => true
            ];
        }
    }

    sendProgress(95, "Finalizing data...");

    // Save product data as JSON
    $jsonPath = $downloadDir . '/product_data.json';
    file_put_contents($jsonPath, json_encode($productData, JSON_PRETTY_PRINT));

    sendProgress(100, "Scraping completed successfully!");

    // Send final result
    echo json_encode([
        'success' => true,
        'product' => $productData,
        'stats' => [
            'total_images' => $totalImages,
            'downloaded_images' => $downloadedCount,
            'colors_found' => count($colors),
            'download_folder' => $downloadDir
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ]);
}
