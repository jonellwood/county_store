<?php
// Batch Image Resizer - Resize all images in a folder to specified dimensions
// Usage: Called via the admin tools UI or directly
// Target: 337 × 506 pixels

session_start();

// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (php_sapi_name() !== 'cli') {
        header("Location: ../../login.php");
        exit;
    }
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'preview':
        previewImages($input);
        break;
    case 'resize':
        resizeImages($input);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: preview or resize']);
        break;
}

function previewImages($input)
{
    $folder = getTargetFolder($input);
    if (!$folder) return;

    $files = scanForImages($folder);

    $images = [];
    foreach ($files as $file) {
        $path = $folder . '/' . $file;
        $size = getimagesize($path);
        $images[] = [
            'filename' => $file,
            'width' => $size[0] ?? 0,
            'height' => $size[1] ?? 0,
            'filesize' => filesize($path),
            'type' => $size['mime'] ?? 'unknown',
            'needs_resize' => ($size[0] ?? 0) !== 337 || ($size[1] ?? 0) !== 506
        ];
    }

    echo json_encode([
        'success' => true,
        'folder' => $folder,
        'total' => count($images),
        'needs_resize' => count(array_filter($images, fn($i) => $i['needs_resize'])),
        'images' => $images
    ]);
}

function resizeImages($input)
{
    $folder = getTargetFolder($input);
    if (!$folder) return;

    $targetWidth = intval($input['width'] ?? 337);
    $targetHeight = intval($input['height'] ?? 506);
    $overwrite = $input['overwrite'] ?? true;
    $quality = intval($input['quality'] ?? 90);

    $files = scanForImages($folder);
    $results = [];
    $resized = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($files as $file) {
        $path = $folder . '/' . $file;
        $info = getimagesize($path);

        if (!$info) {
            $results[] = ['filename' => $file, 'status' => 'failed', 'error' => 'Could not read image'];
            $failed++;
            continue;
        }

        $origWidth = $info[0];
        $origHeight = $info[1];
        $mime = $info['mime'];

        // Skip if already correct size
        if ($origWidth === $targetWidth && $origHeight === $targetHeight) {
            $results[] = [
                'filename' => $file,
                'status' => 'skipped',
                'reason' => 'Already correct size'
            ];
            $skipped++;
            continue;
        }

        // Load the image based on type
        $srcImage = null;
        switch ($mime) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($path);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($path);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($path);
                break;
            default:
                $results[] = ['filename' => $file, 'status' => 'failed', 'error' => "Unsupported type: $mime"];
                $failed++;
                continue 2;
        }

        if (!$srcImage) {
            $results[] = ['filename' => $file, 'status' => 'failed', 'error' => 'Failed to load image'];
            $failed++;
            continue;
        }

        // Create the resized image
        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preserve transparency for PNG/GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            // White background for JPEG
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $white);
        }

        // Resize maintaining aspect ratio, centered with padding
        $srcRatio = $origWidth / $origHeight;
        $dstRatio = $targetWidth / $targetHeight;

        if ($srcRatio > $dstRatio) {
            // Source is wider - fit to width
            $newWidth = $targetWidth;
            $newHeight = intval($targetWidth / $srcRatio);
            $offsetX = 0;
            $offsetY = intval(($targetHeight - $newHeight) / 2);
        } else {
            // Source is taller - fit to height
            $newHeight = $targetHeight;
            $newWidth = intval($targetHeight * $srcRatio);
            $offsetX = intval(($targetWidth - $newWidth) / 2);
            $offsetY = 0;
        }

        imagecopyresampled(
            $dstImage,
            $srcImage,
            $offsetX,
            $offsetY,
            0,
            0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        // Save
        $savePath = $overwrite ? $path : preg_replace('/\.(\w+)$/', '_resized.$1', $path);
        $saved = false;

        switch ($mime) {
            case 'image/jpeg':
                $saved = imagejpeg($dstImage, $savePath, $quality);
                break;
            case 'image/png':
                $pngQuality = intval(9 - ($quality / 100 * 9));
                $saved = imagepng($dstImage, $savePath, $pngQuality);
                break;
            case 'image/gif':
                $saved = imagegif($dstImage, $savePath);
                break;
            case 'image/webp':
                $saved = imagewebp($dstImage, $savePath, $quality);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if ($saved) {
            $newSize = filesize($savePath);
            $results[] = [
                'filename' => $file,
                'status' => 'resized',
                'from' => "{$origWidth}x{$origHeight}",
                'to' => "{$targetWidth}x{$targetHeight}",
                'filesize' => $newSize
            ];
            $resized++;
        } else {
            $results[] = ['filename' => $file, 'status' => 'failed', 'error' => 'Failed to save resized image'];
            $failed++;
        }
    }

    echo json_encode([
        'success' => true,
        'summary' => [
            'total' => count($files),
            'resized' => $resized,
            'skipped' => $skipped,
            'failed' => $failed,
            'target' => "{$targetWidth}x{$targetHeight}"
        ],
        'results' => $results
    ]);
}

function getTargetFolder($input)
{
    $folder = trim($input['folder'] ?? '');

    // Determine project root (document root or fallback)
    $projectRoot = $_SERVER['DOCUMENT_ROOT'] ?: dirname(dirname(dirname(dirname(__FILE__))));

    // Default to product-images
    if (empty($folder)) {
        $folder = $projectRoot . '/product-images';
    } else {
        // If the path is not absolute, treat it as relative to document root
        if ($folder[0] !== '/') {
            $folder = $projectRoot . '/' . ltrim($folder, '/');
        }
    }

    // Security: don't allow path traversal outside the project
    $realFolder = realpath($folder);
    if (!$realFolder || !is_dir($realFolder)) {
        http_response_code(400);
        echo json_encode(['error' => "Folder not found: $folder"]);
        return null;
    }

    return $realFolder;
}

function scanForImages($folder)
{
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $files = [];

    foreach (scandir($folder) as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $extensions)) {
            $files[] = $file;
        }
    }

    sort($files);
    return $files;
}
