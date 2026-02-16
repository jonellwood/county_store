<?php
// Product Image Downloader API - Database-driven approach
// Pulls colors from the database, constructs CDN URLs from a sample URL template
// No SanMar page scraping needed

header('Content-Type: application/json');

// DB connection
require_once __DIR__ . '/../../../config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Route based on action
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_products':
        getProducts($conn);
        break;
    case 'get_colors':
        getColors($conn, $input);
        break;
    case 'download':
        downloadImages($conn, $input);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: get_products, get_colors, or download']);
        break;
}

$conn->close();

// ─────────────────────────────────────────────
// Action Handlers
// ─────────────────────────────────────────────

function getProducts($conn)
{
    $stmt = $conn->prepare("SELECT product_id, code, name, product_type FROM products_new ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'products' => $products]);
}

function getColors($conn, $input)
{
    $productId = intval($input['product_id'] ?? 0);
    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'product_id is required']);
        return;
    }

    $stmt = $conn->prepare("
        SELECT c.color_id, c.color, c.p_hex, c.s_hex
        FROM products_colors pc
        JOIN colors c ON c.color_id = pc.color_id
        WHERE pc.product_id = ?
        ORDER BY c.color ASC
    ");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $colors = [];
    while ($row = $result->fetch_assoc()) {
        $colors[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'colors' => $colors]);
}

function downloadImages($conn, $input)
{
    $productId = intval($input['product_id'] ?? 0);
    $sampleUrl = trim($input['sample_url'] ?? '');
    $sampleColor = trim($input['sample_color'] ?? '');
    $namingFormat = $input['naming_format'] ?? 'code_color_view';
    $selectedColorIds = $input['selected_colors'] ?? []; // optional: subset of colors

    if ($productId <= 0 || empty($sampleUrl) || empty($sampleColor)) {
        http_response_code(400);
        echo json_encode(['error' => 'product_id, sample_url, and sample_color are required']);
        return;
    }

    // Get product info
    $stmt = $conn->prepare("SELECT product_id, code, name, product_type FROM products_new WHERE product_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }

    // Get colors from DB
    $colorQuery = "
        SELECT c.color_id, c.color
        FROM products_colors pc
        JOIN colors c ON c.color_id = pc.color_id
        WHERE pc.product_id = ?
        ORDER BY c.color ASC
    ";
    $stmt = $conn->prepare($colorQuery);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $colors = [];
    while ($row = $result->fetch_assoc()) {
        // If specific colors were selected, filter by color_id
        if (!empty($selectedColorIds) && !in_array(intval($row['color_id']), $selectedColorIds)) {
            continue;
        }
        $colors[] = $row;
    }
    $stmt->close();

    if (empty($colors)) {
        echo json_encode(['error' => 'No colors found for this product']);
        return;
    }

    // Set up download directory
    $downloadDir = $_SERVER['DOCUMENT_ROOT'] . '/product-images/';
    if (!is_dir($_SERVER['DOCUMENT_ROOT'])) {
        $downloadDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/product-images/';
    }
    if (!is_dir($downloadDir)) {
        mkdir($downloadDir, 0775, true);
    }

    $log = [];
    $log[] = "[" . date('H:i:s') . "] Starting image download for product: {$product['name']} ({$product['code']})";
    $log[] = "[" . date('H:i:s') . "] Sample URL: $sampleUrl";
    $log[] = "[" . date('H:i:s') . "] Sample color: $sampleColor";
    $log[] = "[" . date('H:i:s') . "] Colors to download: " . count($colors);

    // ── Parse the sample URL to understand the pattern ──
    // We need to find where the color name appears in the URL
    // Sample: https://cdnp.sanmar.com/medias/sys_master/images/hd8/h81/30953130131486/624Wx724H_72574_Black-0-112BlackHatLeft/624Wx724H-72574-Black-0-112BlackHatLeft.jpg
    //
    // The color appears in:
    //   - The directory name portion (after the hash dirs)
    //   - The filename
    // The hash dirs (hd8/h81/30953130131486) are unique per color - can't predict them.
    //
    // Strategy: Extract just the filename/media-code portion and try the CDN's
    //           direct media URL: https://cdnp.sanmar.com/medias/{mediaCode}
    //           If that fails, try the full URL with color swapped.

    $urlInfo = parseImageUrl($sampleUrl, $sampleColor, $log);

    if (!$urlInfo) {
        echo json_encode([
            'success' => false,
            'error' => "Could not identify the color '$sampleColor' in the sample URL",
            'log' => $log
        ]);
        return;
    }

    $downloadedImages = [];
    $failedImages = [];
    $productCode = $product['code'];

    foreach ($colors as $index => $color) {
        $colorName = $color['color'];
        // Remove spaces for URL construction (SanMar uses no-space color names in URLs)
        $urlColorName = str_replace(' ', '', $colorName);

        $log[] = "[" . date('H:i:s') . "] [" . ($index + 1) . "/" . count($colors) . "] Processing color: $colorName";

        // Build the URLs for this color by swapping the sample color name
        $urls = buildColorUrls($urlInfo, $urlColorName, $log);

        $downloaded = false;
        foreach ($urls as $attempt => $tryUrl) {
            $log[] = "[" . date('H:i:s') . "]   Attempt $attempt: $tryUrl";

            $imageData = downloadImageFile($tryUrl);
            if ($imageData !== false) {
                // Generate filename
                $viewType = detectViewType($urlInfo['filename_template']);
                $cleanColor = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colorName));
                $lcCode = strtolower($productCode);

                switch ($namingFormat) {
                    case 'code_color':
                        $filename = "{$lcCode}_{$cleanColor}.jpg";
                        break;
                    case 'color_code':
                        $filename = "{$cleanColor}_{$lcCode}.jpg";
                        break;
                    case 'code_color_view':
                    default:
                        $filename = "{$lcCode}_{$cleanColor}_{$viewType}.jpg";
                        break;
                }

                $filePath = $downloadDir . $filename;
                $bytesWritten = file_put_contents($filePath, $imageData);

                if ($bytesWritten !== false) {
                    @chmod($filePath, 0664);
                    $downloadedImages[] = [
                        'filename' => $filename,
                        'color' => $colorName,
                        'view' => $viewType,
                        'size' => strlen($imageData),
                        'url' => $tryUrl,
                        'path' => $filePath
                    ];
                    $log[] = "[" . date('H:i:s') . "]   ✓ Saved: $filename (" . formatBytes(strlen($imageData)) . ")";
                    $downloaded = true;
                    break; // Success, move to next color
                }
            }
        }

        if (!$downloaded) {
            $failedImages[] = [
                'color' => $colorName,
                'urls_tried' => $urls
            ];
            $log[] = "[" . date('H:i:s') . "]   ✗ FAILED: Could not download image for $colorName";
        }

        // Small delay between colors
        if ($index < count($colors) - 1) {
            usleep(300000); // 0.3s
        }
    }

    $log[] = "[" . date('H:i:s') . "] ═══════════════════════════════";
    $log[] = "[" . date('H:i:s') . "] Download complete!";
    $log[] = "[" . date('H:i:s') . "] Successful: " . count($downloadedImages) . " / " . count($colors);
    $log[] = "[" . date('H:i:s') . "] Failed: " . count($failedImages);

    echo json_encode([
        'success' => true,
        'productInfo' => [
            'code' => $productCode,
            'name' => $product['name'],
            'colors' => array_column($colors, 'color')
        ],
        'images' => $downloadedImages,
        'failed' => $failedImages,
        'log' => $log,
        'summary' => [
            'totalImages' => count($downloadedImages),
            'totalFailed' => count($failedImages),
            'totalColors' => count($colors),
            'productCode' => $productCode,
            'productName' => $product['name']
        ]
    ]);
}

// ─────────────────────────────────────────────
// URL Parsing & Building
// ─────────────────────────────────────────────

/**
 * Parse the sample URL to understand where the color name appears.
 * Returns an array with template info for color swapping.
 */
function parseImageUrl($sampleUrl, $sampleColor, &$log)
{
    // Remove spaces from the color for URL matching
    $urlColor = str_replace(' ', '', $sampleColor);

    // Check if the color appears in the URL
    if (stripos($sampleUrl, $urlColor) === false) {
        $log[] = "[" . date('H:i:s') . "] Color '$urlColor' not found in URL";
        return null;
    }

    // Extract the filename (last path segment before .jpg/.png)
    $path = parse_url($sampleUrl, PHP_URL_PATH);
    $filename = basename($path);

    // Create a template by replacing the color name with a placeholder
    $filenameTemplate = str_ireplace($urlColor, '{{COLOR}}', $filename);
    $fullUrlTemplate = str_ireplace($urlColor, '{{COLOR}}', $sampleUrl);

    // Also extract just the media code (filename without extension)
    $mediaCode = pathinfo($filename, PATHINFO_FILENAME);
    $mediaCodeTemplate = str_ireplace($urlColor, '{{COLOR}}', $mediaCode);

    // Try to extract the directory name before the filename (also contains color)
    $dirParts = explode('/', $path);
    array_pop($dirParts); // remove filename
    $dirName = end($dirParts); // get the directory name
    $dirNameTemplate = str_ireplace($urlColor, '{{COLOR}}', $dirName);

    $log[] = "[" . date('H:i:s') . "] URL parsed successfully:";
    $log[] = "[" . date('H:i:s') . "]   Filename template: $filenameTemplate";
    $log[] = "[" . date('H:i:s') . "]   Media code template: $mediaCodeTemplate";
    $log[] = "[" . date('H:i:s') . "]   Directory template: $dirNameTemplate";

    return [
        'original_url' => $sampleUrl,
        'sample_color' => $urlColor,
        'filename_template' => $filenameTemplate,
        'media_code_template' => $mediaCodeTemplate,
        'dir_name_template' => $dirNameTemplate,
        'full_url_template' => $fullUrlTemplate,
        'extension' => pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg'
    ];
}

/**
 * Build a list of URLs to try for a given color, ordered by likelihood of success.
 */
function buildColorUrls($urlInfo, $colorName, &$log)
{
    $urls = [];

    // Attempt 1: Direct media code URL (most likely to work with SAP Commerce CDN)
    // e.g., https://cdnp.sanmar.com/medias/624Wx724H-72574-AmberGold-0-112AmberGoldHatLeft
    $mediaCode = str_replace('{{COLOR}}', $colorName, $urlInfo['media_code_template']);
    $urls['direct_media'] = "https://cdnp.sanmar.com/medias/" . $mediaCode;

    // Attempt 2: Full URL with color swapped everywhere (hash dirs will be wrong but worth trying)
    $fullUrl = str_replace('{{COLOR}}', $colorName, $urlInfo['full_url_template']);
    $urls['full_swap'] = $fullUrl;

    // Attempt 3: Direct media code with extension
    $urls['direct_media_ext'] = "https://cdnp.sanmar.com/medias/" . $mediaCode . "." . $urlInfo['extension'];

    return $urls;
}

/**
 * Detect view type from a filename template.
 */
function detectViewType($filenameTemplate)
{
    $text = strtolower($filenameTemplate);

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
    if (str_contains($text, 'side')) return 'side';
    return 'main';
}

// ─────────────────────────────────────────────
// Download helpers
// ─────────────────────────────────────────────

function downloadImageFile($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    // Verify we got an actual image (not an HTML error page)
    if ($httpCode === 200 && $data && strlen($data) > 1000) {
        // Quick check: images start with known magic bytes
        if (isImageData($data)) {
            return $data;
        }
    }

    return false;
}

/**
 * Check if the data looks like an actual image file (not an HTML page).
 */
function isImageData($data)
{
    // JPEG: starts with FF D8 FF
    if (substr($data, 0, 3) === "\xFF\xD8\xFF") return true;
    // PNG: starts with 89 50 4E 47
    if (substr($data, 0, 4) === "\x89\x50\x4E\x47") return true;
    // GIF: starts with GIF8
    if (substr($data, 0, 4) === "GIF8") return true;
    // WebP: starts with RIFF....WEBP
    if (substr($data, 0, 4) === "RIFF" && substr($data, 8, 4) === "WEBP") return true;

    return false;
}

function formatBytes($bytes, $precision = 1)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
