<?php
// Debug SanMar HTML Content
// This will help us see exactly what HTML we're getting from SanMar

$url = "https://www.sanmar.com/p/5682_DkGreenNv";

echo "🔍 Debugging SanMar HTML Content\n";
echo "URL: $url\n\n";

function scrapeWithDebug($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    echo "Content Type: $contentType\n";
    echo "Content Length: " . strlen($html) . " bytes\n";
    echo "Is UTF-8: " . (mb_check_encoding($html, 'UTF-8') ? 'Yes' : 'No') . "\n";
    echo "Contains <html: " . (preg_match('/<html/i', $html) ? 'Yes' : 'No') . "\n\n";

    return $html;
}

$html = scrapeWithDebug($url);

// Look for image patterns
echo "🖼️ Image Analysis:\n";

// Pattern 1: Basic img tags
preg_match_all('/<img[^>]*src="([^"]*)"[^>]*>/i', $html, $allImages);
echo "Total img tags found: " . count($allImages[1]) . "\n";

// Pattern 2: Product images specifically
preg_match_all('/<img[^>]*src="([^"]*(?:productimages|images)[^"]*\.(?:jpg|jpeg|png|webp)[^"]*)"[^>]*>/i', $html, $productImages);
echo "Product images found: " . count($productImages[1]) . "\n";

if (!empty($productImages[1])) {
    echo "Sample product image URLs:\n";
    foreach (array_slice($productImages[1], 0, 5) as $img) {
        echo "  - $img\n";
    }
}

// Pattern 3: Data attributes
preg_match_all('/data-src="([^"]*(?:productimages|images)[^"]*\.(?:jpg|jpeg|png|webp)[^"]*)"[^>]*>/i', $html, $dataImages);
echo "Data-src images found: " . count($dataImages[1]) . "\n";

if (!empty($dataImages[1])) {
    echo "Sample data-src image URLs:\n";
    foreach (array_slice($dataImages[1], 0, 3) as $img) {
        echo "  - $img\n";
    }
}

// Pattern 4: Any image URLs in the content
preg_match_all('/(https?:\/\/[^"\s]*(?:productimages|images\.sanmar|sanmar\.com\/images)[^"\s]*\.(?:jpg|jpeg|png|webp))/i', $html, $anyImages);
echo "Any image URLs found: " . count($anyImages[1]) . "\n";

if (!empty($anyImages[1])) {
    echo "Sample any image URLs:\n";
    foreach (array_slice($anyImages[1], 0, 3) as $img) {
        echo "  - $img\n";
    }
}

echo "\n🎨 Color Analysis:\n";

// Look for color links
preg_match_all('/<a[^>]*href="[^"]*\/p\/\d+_([^"\/\?#]+)"[^>]*>/i', $html, $colorLinks);
echo "Color links found: " . count($colorLinks[1]) . "\n";

if (!empty($colorLinks[1])) {
    echo "Sample colors:\n";
    foreach (array_slice($colorLinks[1], 0, 10) as $color) {
        echo "  - $color\n";
    }
}

// Save a snippet of the HTML for manual inspection
$snippet = substr($html, 0, 5000);
file_put_contents(__DIR__ . '/debug_html_snippet.txt', $snippet);
echo "\n💾 Saved first 5000 characters of HTML to debug_html_snippet.txt\n";

// Look for specific product name
if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $nameMatch)) {
    echo "\n📝 Product name found: " . trim(strip_tags($nameMatch[1])) . "\n";
} else {
    echo "\n❌ No product name found in h1 tag\n";
}

echo "\n✅ Debug complete. Check the results above to see what we're getting from SanMar.\n";
