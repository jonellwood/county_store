<?php
// Quick test to see if images were downloaded successfully
$downloadDir = dirname(__FILE__) . '/downloads/product_images/';

echo "<h2>🔍 SanMar Scraper - Image Download Test</h2>";
echo "<p><strong>Looking in:</strong> $downloadDir</p>";

if (is_dir($downloadDir)) {
    $files = glob($downloadDir . '*.jpg');
    echo "<p><strong>Found " . count($files) . " image files:</strong></p>";

    if (count($files) > 0) {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;'>";
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $sizeKB = round($size / 1024, 1);

            echo "<div style='border: 1px solid #ddd; padding: 10px; text-align: center;'>";
            echo "<img src='downloads/product_images/$filename' style='max-width: 100%; height: 150px; object-fit: cover;' alt='$filename'>";
            echo "<br><small>$filename</small>";
            echo "<br><small>{$sizeKB} KB</small>";
            echo "</div>";
        }
        echo "</div>";

        echo "<h3>✅ Success! Images downloaded and accessible.</h3>";
    } else {
        echo "<p>❌ No image files found in download directory.</p>";
    }
} else {
    echo "<p>❌ Download directory does not exist: $downloadDir</p>";
}

echo "<p><a href='product-scraper.php'>← Back to Product Scraper</a></p>";
