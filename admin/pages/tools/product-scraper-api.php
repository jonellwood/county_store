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

    // Parse the HTML
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
        debugPageStructure($xpath, $log);
    } catch (Exception $e) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Debug error: " . $e->getMessage();
    }

    // Extract product information
    $productInfo = extractProductInfo($xpath, $log);

    // Use override if provided, otherwise use extracted code
    if (!empty($productCodeOverride)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] Using product code override: {$productCodeOverride} (replacing {$productInfo['code']})";
        $productInfo['code'] = $productCodeOverride;
    }

    // Extract image URLs
    $images = extractImageUrls($xpath, $productInfo, $log);

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

function debugPageStructure($xpath, &$log)
{
    // Get a sample of the HTML content to see what we're actually dealing with
    $htmlSample = $xpath->document->saveHTML();
    $sampleLength = min(2000, strlen($htmlSample));
    $sample = substr($htmlSample, 0, $sampleLength);
    $log[] = "[" . date('Y-m-d H:i:s') . "] HTML Sample (first {$sampleLength} chars): " . htmlspecialchars($sample);

    // Check for various elements we're looking for
    $checks = [
        'product-style-number' => "//span[@class='product-style-number']",
        'color-swatches' => "//div[contains(@class, 'color-swatches')]",
        'data-variant-code' => "//*[@data-variant-code]",
        'swatch-name' => "//span[@class='swatch-name']",
        'product-title h1' => "//h1",
        'title tag' => "//title",
        'any spans' => "//span",
        'any divs' => "//div",
        'script tags' => "//script"
    ];

    foreach ($checks as $name => $query) {
        $elements = $xpath->query($query);
        $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - $name: found " . $elements->length . " elements";

        if ($elements->length > 0 && $elements->length <= 3) {
            // Log first few elements' content for debugging
            for ($i = 0; $i < min(3, $elements->length); $i++) {
                $content = trim($elements->item($i)->textContent);
                $content = substr($content, 0, 100); // Limit length
                $log[] = "[" . date('Y-m-d H:i:s') . "] Debug - $name [$i]: '$content'";
            }
        }
    }
}

function extractProductInfo($xpath, &$log)
{
    $info = [];

    // Extract product code from the specific SanMar element
    $styleElements = $xpath->query("//span[@class='product-style-number']");
    if ($styleElements->length > 0) {
        $styleText = trim($styleElements->item(0)->textContent);
        // Remove any child elements like "Sale" text
        $styleNumber = preg_replace('/\s*(Sale|New|Discontinued).*$/', '', $styleText);
        $info['code'] = trim($styleNumber);
        $log[] = "[" . date('Y-m-d H:i:s') . "] Found style number: " . $info['code'];
    } else {
        // Fallback: look for data-style-number attributes
        $dataStyleElements = $xpath->query("//*[@data-style-number]");
        if ($dataStyleElements->length > 0) {
            $styleNumber = $dataStyleElements->item(0)->getAttribute('data-style-number');
            $info['code'] = $styleNumber;
            $log[] = "[" . date('Y-m-d H:i:s') . "] Found style number (fallback): $styleNumber";
        }
    }

    // Extract product name from title or h1
    $nameElements = $xpath->query("//h1[@class='product-title'] | //h1[contains(@class, 'product')] | //title | //h1");
    if ($nameElements->length > 0) {
        $nameText = trim($nameElements->item(0)->textContent);
        // Clean up the title
        $nameText = preg_replace('/\s*-\s*SanMar.*$/', '', $nameText);
        $nameText = preg_replace('/\s*\|\s*SanMar.*$/', '', $nameText); // Also handle | separator
        $info['name'] = trim($nameText);
        $log[] = "[" . date('Y-m-d H:i:s') . "] Found product name: " . $info['name'];
    }

    // Extract description from product details
    $descElements = $xpath->query("//div[contains(@class, 'product-description')] | //div[contains(@class, 'description')]");
    if ($descElements->length > 0) {
        $info['description'] = trim($descElements->item(0)->textContent);
    }

    // Extract available colors from color swatches (SanMar specific structure)
    $log[] = "[" . date('Y-m-d H:i:s') . "] Looking for color swatches...";

    $colorElements = $xpath->query("//div[contains(@class, 'color-swatches')]//li/a[@data-variant-code]");
    $log[] = "[" . date('Y-m-d H:i:s') . "] Found " . $colorElements->length . " color swatch elements";

    $colors = [];
    $colorData = [];

    foreach ($colorElements as $element) {
        $variantCode = $element->getAttribute('data-variant-code');
        $href = $element->getAttribute('href');
        $colorNameElement = $xpath->query(".//span[@class='swatch-name']", $element);

        if ($colorNameElement->length > 0) {
            $colorName = trim($colorNameElement->item(0)->textContent);
            $colors[] = $colorName;

            // Store detailed color data for image scraping
            $colorData[] = [
                'name' => $colorName,
                'variant_code' => $variantCode,
                'url' => 'https://www.sanmar.com' . $href
            ];

            $log[] = "[" . date('Y-m-d H:i:s') . "] Found color: $colorName ($variantCode)";
        }
    }

    $info['colors'] = $colors;
    $info['color_data'] = $colorData; // Store for image extraction

    $log[] = "[" . date('Y-m-d H:i:s') . "] Found " . count($colors) . " colors: " . implode(', ', $colors);

    return $info;
}

function extractImageUrls($xpath, $productInfo, &$log)
{
    $images = [];
    $colorData = $productInfo['color_data'] ?? [];

    if (empty($colorData)) {
        $log[] = "[" . date('Y-m-d H:i:s') . "] No color data found, cannot extract images";
        return $images;
    }

    $log[] = "[" . date('Y-m-d H:i:s') . "] Starting to scrape images for " . count($colorData) . " color variants";

    foreach ($colorData as $color) {
        $colorName = $color['name'];
        $variantCode = $color['variant_code'];
        $variantUrl = $color['url'];

        $log[] = "[" . date('Y-m-d H:i:s') . "] Scraping images for color: $colorName ($variantCode)";

        try {
            // Fetch the color variant page
            $variantHtml = fetchPageContent($variantUrl);
            if (!$variantHtml) {
                $log[] = "[" . date('Y-m-d H:i:s') . "] Failed to fetch variant page: $variantUrl";
                continue;
            }

            // Parse the variant page
            $variantDom = new DOMDocument();
            @$variantDom->loadHTML($variantHtml);
            $variantXpath = new DOMXPath($variantDom);

            // Look for the main product image link (following your manual process)
            $imageLinks = $variantXpath->query("//div[contains(@class, 'main-image')]//a[contains(@class, 'zoom')]/@href");

            foreach ($imageLinks as $link) {
                $imageUrl = $link->nodeValue;

                // Convert relative URLs to absolute
                if (strpos($imageUrl, 'http') !== 0) {
                    $imageUrl = 'https:' . $imageUrl;
                }

                // Determine view type from filename
                $viewType = determineViewType($imageUrl);

                $images[] = [
                    'url' => $imageUrl,
                    'color' => $colorName,
                    'variant_code' => $variantCode,
                    'view' => $viewType,
                    'filename' => basename(parse_url($imageUrl, PHP_URL_PATH))
                ];

                $log[] = "[" . date('Y-m-d H:i:s') . "] Found image: $colorName ($viewType) - " . basename($imageUrl);
            }

            // Small delay to be respectful to the server
            usleep(500000); // 0.5 seconds

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

function determineViewType($imageUrl)
{
    $filename = strtolower(basename($imageUrl));

    // SanMar specific view patterns
    if (str_contains($filename, 'modelfront') || str_contains($filename, 'front')) {
        return 'front';
    } elseif (str_contains($filename, 'modelback') || str_contains($filename, 'back')) {
        return 'back';
    } elseif (str_contains($filename, 'flat') || str_contains($filename, 'flt')) {
        return 'flat';
    } elseif (str_contains($filename, 'side')) {
        return 'side';
    } elseif (str_contains($filename, 'model')) {
        return 'model';
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
