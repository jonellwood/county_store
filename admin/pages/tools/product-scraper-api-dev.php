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

// Function to extract product code from URL
function extractProductCode($url)
{
    // Pattern: https://www.sanmar.com/p/5682_DkGreenNv
    if (preg_match('/\/p\/([^_]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// Function to scrape product data
function scrapeProductData($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate'); // Handle compression
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$html || $httpCode !== 200) {
        throw new Exception("Failed to fetch page: HTTP $httpCode");
    }

    // Check if we got binary data (compressed but not decoded)
    if (!mb_check_encoding($html, 'UTF-8') && !preg_match('/<html/i', $html)) {
        throw new Exception("Received compressed data that couldn't be decoded");
    }

    return $html;
}

// Start processing
try {
    // Enable output buffering for streaming response
    ob_start();

    sendProgress(10, "Fetching product page...");

    $html = scrapeProductData($url);

    sendProgress(20, "Parsing product information...");

    // Extract product code from URL
    $productCode = extractProductCode($url);
    if (!$productCode) {
        throw new Exception("Could not extract product code from URL");
    }

    // Extract product name using regex patterns
    $productName = '';
    if (preg_match('/<h1[^>]*class="[^"]*product-name[^"]*"[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
        $productName = trim(strip_tags($matches[1]));
    } elseif (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
        $productName = trim(strip_tags($matches[1]));
    }

    // Extract product description
    $productDescription = '';
    if (preg_match('/<div[^>]*class="[^"]*product-description[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        $productDescription = trim(strip_tags($matches[1]));
    }

    sendProgress(30, "Extracting color variants...");

    // Look for color swatches and links
    $colors = [];
    $imageUrls = [];

    // Pattern 1: Color swatch links - be more specific to avoid navigation links
    preg_match_all('/<a[^>]*class="[^"]*color[^"]*"[^>]*href="[^"]*\/p\/\d+_([^"\/\?#]+)"[^>]*>/i', $html, $colorMatches);
    if (!empty($colorMatches[1])) {
        foreach ($colorMatches[1] as $color) {
            // Skip navigation/spec sheet links
            if (!preg_match('/(specSheet|decoration|Images|inventory)/i', $color) && !in_array($color, $colors)) {
                $colors[] = $color;
            }
        }
    }

    // Pattern 2: Try alternative color link patterns
    if (empty($colors)) {
        preg_match_all('/<a[^>]*href="[^"]*\/p\/\d+_([^"\/\?#]+)"[^>]*title="[^"]*color[^"]*"/i', $html, $colorMatches2);
        if (!empty($colorMatches2[1])) {
            foreach ($colorMatches2[1] as $color) {
                if (!preg_match('/(specSheet|decoration|Images|inventory)/i', $color) && !in_array($color, $colors)) {
                    $colors[] = $color;
                }
            }
        }
    }

    // Pattern 3: Look for color option elements
    if (empty($colors)) {
        preg_match_all('/<option[^>]*value="[^"]*\/p\/\d+_([^"\/\?#]+)"[^>]*>/i', $html, $colorMatches3);
        if (!empty($colorMatches3[1])) {
            foreach ($colorMatches3[1] as $color) {
                if (!preg_match('/(specSheet|decoration|Images|inventory)/i', $color) && !in_array($color, $colors)) {
                    $colors[] = $color;
                }
            }
        }
    }

    // If still no colors found, extract from current URL
    if (empty($colors)) {
        if (preg_match('/\/p\/\d+_([^\/\?#]+)/', $url, $matches)) {
            $currentColor = $matches[1];
            if (!preg_match('/(specSheet|decoration|Images|inventory)/i', $currentColor)) {
                $colors[] = $currentColor;
            }
        }
    }

    sendProgress(40, "Finding product images...");

    // Find product images using the correct SanMar CDN patterns

    // Pattern 1: Look for CDN images with product code
    preg_match_all('/(?:src|data-src|content)="([^"]*cdnp\.sanmar\.com[^"]*' . preg_quote($productCode) . '[^"]*\.(?:jpg|jpeg|png|webp)[^"]*)"[^>]*/i', $html, $cdnMatches1);
    if (!empty($cdnMatches1[1])) {
        foreach ($cdnMatches1[1] as $imgSrc) {
            // Ensure https protocol
            $highResUrl = $imgSrc;
            if (strpos($highResUrl, '//') === 0) {
                $highResUrl = 'https:' . $highResUrl;
            } elseif (strpos($highResUrl, 'http') !== 0) {
                $highResUrl = 'https://cdnp.sanmar.com' . $highResUrl;
            }

            if (!in_array($highResUrl, $imageUrls)) {
                $imageUrls[] = $highResUrl;
            }
        }
    }

    // Pattern 2: Look for any CDN images that might be product-related
    preg_match_all('/(\/\/cdnp\.sanmar\.com\/medias\/sys_master\/images\/[^"\s]*\.(?:jpg|jpeg|png|webp))/i', $html, $cdnMatches2);
    if (!empty($cdnMatches2[1])) {
        foreach ($cdnMatches2[1] as $imgSrc) {
            // Filter for product-looking images (containing numbers that might be product codes)
            if (preg_match('/\d{3,5}/', $imgSrc)) {
                $highResUrl = 'https:' . $imgSrc;
                if (!in_array($highResUrl, $imageUrls)) {
                    $imageUrls[] = $highResUrl;
                }
            }
        }
    }

    // Pattern 3: Look for meta property og:image (often the main product image)
    preg_match_all('/<meta[^>]*property="og:image"[^>]*content="([^"]*)"[^>]*>/i', $html, $ogMatches);
    if (!empty($ogMatches[1])) {
        foreach ($ogMatches[1] as $imgSrc) {
            $highResUrl = $imgSrc;
            if (strpos($highResUrl, '//') === 0) {
                $highResUrl = 'https:' . $highResUrl;
            }
            if (!in_array($highResUrl, $imageUrls)) {
                $imageUrls[] = $highResUrl;
            }
        }
    }

    // Pattern 4: Look for JSON data that might contain image URLs
    if (preg_match_all('/["\'](?:image|photo|picture)["\']:\s*["\']([^"\']*cdnp\.sanmar\.com[^"\']*\.(?:jpg|jpeg|png|webp))["\']/', $html, $jsonMatches)) {
        foreach ($jsonMatches[1] as $imgSrc) {
            $highResUrl = $imgSrc;
            if (strpos($highResUrl, '//') === 0) {
                $highResUrl = 'https:' . $highResUrl;
            }
            if (!in_array($highResUrl, $imageUrls)) {
                $imageUrls[] = $highResUrl;
            }
        }
    }

    sendProgress(42, "Debug: Found " . count($imageUrls) . " potential image URLs from HTML");

    // If we still have no images, try searching for images by fetching color variant pages
    if (empty($imageUrls) && !empty($colors)) {
        sendProgress(44, "Trying to find color variant pages...");

        foreach (array_slice($colors, 0, 3) as $color) { // Limit to first 3 colors
            $colorUrl = "https://www.sanmar.com/p/{$productCode}_{$color}";

            sendProgress(46, "Checking color variant: $color");

            try {
                $colorHtml = scrapeProductData($colorUrl);

                // Look for CDN images in this color's page
                preg_match_all('/(?:src|data-src|content)="([^"]*cdnp\.sanmar\.com[^"]*' . preg_quote($productCode) . '[^"]*\.(?:jpg|jpeg|png|webp)[^"]*)"[^>]*/i', $colorHtml, $colorImageMatches);
                if (!empty($colorImageMatches[1])) {
                    foreach ($colorImageMatches[1] as $imgSrc) {
                        $highResUrl = $imgSrc;
                        if (strpos($highResUrl, '//') === 0) {
                            $highResUrl = 'https:' . $highResUrl;
                        }

                        if (!in_array($highResUrl, $imageUrls)) {
                            $imageUrls[] = $highResUrl;
                        }
                    }
                }
            } catch (Exception $e) {
                sendProgress(47, "Skipped color $color: " . $e->getMessage());
            }
        }
    }

    sendProgress(48, "Final image count: " . count($imageUrls));

    sendProgress(50, "Preparing download data...");

    // Debug information
    sendProgress(52, "Debug: Found " . count($colors) . " colors: " . implode(', ', array_slice($colors, 0, 5)));
    sendProgress(54, "Debug: Found " . count($imageUrls) . " image URLs");

    $productData = [
        'code' => $productCode,
        'name' => $productName,
        'description' => $productDescription,
        'url' => $url,
        'colors' => $colors,
        'images' => [],
        'download_folder' => $imageFolder,
        'naming_format' => $namingFormat,
        'scraped_at' => date('Y-m-d H:i:s'),
        'debug_info' => [
            'total_colors_found' => count($colors),
            'total_image_urls_found' => count($imageUrls),
            'sample_image_urls' => array_slice($imageUrls, 0, 3)
        ]
    ];

    // Download images
    $totalImages = count($imageUrls);
    $downloadedCount = 0;

    foreach ($imageUrls as $index => $imageUrl) {
        $progress = 50 + (($index + 1) / $totalImages) * 40;
        sendProgress($progress, "Downloading image " . ($index + 1) . " of $totalImages...");

        // Generate filename based on naming format
        $colorName = !empty($colors) ? $colors[min($index, count($colors) - 1)] : 'default';
        $viewType = ($index === 0) ? 'front' : 'view' . ($index + 1);

        switch ($namingFormat) {
            case 'code_color':
                $filename = "{$productCode}_{$colorName}.jpg";
                break;
            case 'color_code':
                $filename = "{$colorName}_{$productCode}.jpg";
                break;
            case 'code_color_view':
            default:
                $filename = "{$productCode}_{$colorName}_{$viewType}.jpg";
                break;
        }

        $savePath = $downloadDir . '/' . $filename;

        sendProgress($progress, "Downloading: $filename from $imageUrl");

        if (downloadImage($imageUrl, $savePath)) {
            $downloadedCount++;
            $productData['images'][] = [
                'url' => $imageUrl,
                'filename' => $filename,
                'local_path' => $savePath,
                'color' => $colorName,
                'view' => $viewType,
                'size' => filesize($savePath)
            ];
            sendProgress($progress, "✓ Downloaded: $filename (" . round(filesize($savePath) / 1024, 1) . " KB)");
        } else {
            sendProgress($progress, "✗ Failed to download: $filename");
            // Log the failed download but continue
            $productData['images'][] = [
                'url' => $imageUrl,
                'filename' => $filename,
                'local_path' => null,
                'color' => $colorName,
                'view' => $viewType,
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
