<?php

/**
 * Company Casuals Image Scraper API
 * Extracts product images from color swatches on Company Casuals pages
 */

header('Content-Type: application/json');

class CompanyCasualsImageScraper
{
    private const IMAGE_BASE_URL = 'https://cdnp.companycasuals.com/catalog/images/';
    private const SWATCH_BASE_URL = 'https://cdnp.companycasuals.com/swatch/gifs/';

    private array $defaultHeaders = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.8',
        'Accept-Encoding: gzip, deflate, br',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Pragma: no-cache',
        'Upgrade-Insecure-Requests: 1'
    ];

    private array $log = [];

    public function scrape(string $url): array
    {
        $this->log = [];

        $url = trim($url);
        if ($url === '') {
            return $this->errorResult('URL is required');
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->errorResult('Invalid URL format');
        }

        if (strpos($url, 'companycasuals.com') === false) {
            return $this->errorResult('URL must be from companycasuals.com');
        }

        $this->logStep('Starting scrape', ['url' => $url]);

        // Fetch the HTML
        $fetchResult = $this->fetchHtml($url);
        if ($fetchResult['status'] !== 'ok') {
            return $this->errorResult($fetchResult['message'], [
                'http_code' => $fetchResult['http_code'] ?? null,
                'url' => $url
            ]);
        }

        $html = $fetchResult['html'];
        $this->logStep('Fetched HTML', ['bytes' => strlen($html)]);

        // Parse the HTML
        $result = $this->parseHtml($html, $url);
        $result['log'] = $this->log;

        return $result;
    }

    private function fetchHtml(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($html === false) {
            return [
                'status' => 'error',
                'http_code' => $httpCode,
                'message' => 'Failed to fetch page: ' . ($curlError ?: 'unknown error')
            ];
        }

        if ($httpCode !== 200) {
            return [
                'status' => 'error',
                'http_code' => $httpCode,
                'message' => "HTTP error: {$httpCode}"
            ];
        }

        return [
            'status' => 'ok',
            'http_code' => $httpCode,
            'html' => $html
        ];
    }

    private function parseHtml(string $html, string $sourceUrl): array
    {
        // Extract product info
        $productCode = $this->extractProductCode($html);
        $productName = $this->extractProductName($html);

        // Extract color swatches with images
        $colors = $this->extractColorImages($html);

        if (empty($colors)) {
            return $this->errorResult('No color swatches found on page');
        }

        $this->logStep('Parsing complete', [
            'product_code' => $productCode,
            'colors_found' => count($colors)
        ]);

        return [
            'success' => true,
            'data' => [
                'product_code' => $productCode,
                'product_name' => $productName,
                'source_url' => $sourceUrl,
                'scraped_at' => gmdate('c'),
                'image_base_url' => self::IMAGE_BASE_URL,
                'colors' => $colors,
                'color_count' => count($colors)
            ]
        ];
    }

    private function extractProductCode(string $html): ?string
    {
        // Try to get from hidden input
        if (preg_match('/name=["\']productId["\'].*?value=["\']([^"\']+)["\']/', $html, $matches)) {
            $this->logStep('Found product code from hidden input', ['code' => $matches[1]]);
            return strtoupper(trim($matches[1]));
        }

        // Try from URL in the page
        if (preg_match('/productId=([A-Za-z0-9\-]+)/', $html, $matches)) {
            return strtoupper(trim($matches[1]));
        }

        // Try from title
        if (preg_match('/\.\s*([A-Za-z0-9\-]+)\s*<\/title>/i', $html, $matches)) {
            return strtoupper(trim($matches[1]));
        }

        return null;
    }

    private function extractProductName(string $html): ?string
    {
        // Try to get from title
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]));
            // Clean up the title - typically format is "Print Charleston, Brand - Product Name. CODE"
            if (preg_match('/,\s*(.+?)\.\s*[A-Za-z0-9\-]+\s*$/', $title, $nameMatches)) {
                return trim($nameMatches[1]);
            }
            return $title;
        }

        // Try from prod_title_text
        if (preg_match('/<h4[^>]*class=["\'][^"\']*prod_title_text[^"\']*["\'][^>]*>(.+?)<\/h4>/is', $html, $matches)) {
            return strip_tags(html_entity_decode(trim($matches[1])));
        }

        return null;
    }

    private function extractColorImages(string $html): array
    {
        $colors = [];

        // Pattern to match the onclick handler in color swatches
        // onclick="changeSwatch('/printcharleston/b.jsp?id=8893788&prodimage=imglib/catl/2013/f5/BG970_Flat_Black_MS05.jpg&swatch=Black')"
        $pattern = '/onclick=["\']changeSwatch\(["\']([^"\']+)["\']\)["\'].*?(?:alt|title)=["\']([^"\']+)["\']/is';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            $this->logStep('Found color swatches via onclick pattern', ['count' => count($matches)]);

            foreach ($matches as $match) {
                $onclickUrl = $match[1];
                $colorName = html_entity_decode(trim($match[2]));

                // Extract prodimage parameter
                if (preg_match('/prodimage=([^&]+)/', $onclickUrl, $imgMatch)) {
                    $prodimage = urldecode($imgMatch[1]);
                    $fullImageUrl = self::IMAGE_BASE_URL . $prodimage;

                    // Extract swatch parameter
                    $swatchName = '';
                    if (preg_match('/swatch=([^&]+)/', $onclickUrl, $swatchMatch)) {
                        $swatchName = urldecode($swatchMatch[1]);
                    }

                    $colors[] = [
                        'color_name' => $colorName,
                        'swatch_name' => $swatchName ?: $colorName,
                        'image_path' => $prodimage,
                        'image_url' => $fullImageUrl,
                        'onclick_url' => $onclickUrl
                    ];

                    $this->logStep('Extracted color', [
                        'color' => $colorName,
                        'image_path' => $prodimage
                    ]);
                }
            }
        }

        // Alternative pattern - sometimes alt comes before onclick
        if (empty($colors)) {
            $pattern2 = '/(?:alt|title)=["\']([^"\']+)["\'].*?onclick=["\']changeSwatch\(["\']([^"\']+)["\']\)["\']/is';

            if (preg_match_all($pattern2, $html, $matches, PREG_SET_ORDER)) {
                $this->logStep('Found color swatches via alternative pattern', ['count' => count($matches)]);

                foreach ($matches as $match) {
                    $colorName = html_entity_decode(trim($match[1]));
                    $onclickUrl = $match[2];

                    if (preg_match('/prodimage=([^&]+)/', $onclickUrl, $imgMatch)) {
                        $prodimage = urldecode($imgMatch[1]);
                        $fullImageUrl = self::IMAGE_BASE_URL . $prodimage;

                        $swatchName = '';
                        if (preg_match('/swatch=([^&]+)/', $onclickUrl, $swatchMatch)) {
                            $swatchName = urldecode($swatchMatch[1]);
                        }

                        $colors[] = [
                            'color_name' => $colorName,
                            'swatch_name' => $swatchName ?: $colorName,
                            'image_path' => $prodimage,
                            'image_url' => $fullImageUrl,
                            'onclick_url' => $onclickUrl
                        ];
                    }
                }
            }
        }

        // Also try to get the main product image
        if (preg_match('/src=["\']([^"\']*cdnp\.companycasuals\.com\/catalog\/images\/[^"\']+)["\'].*?class=["\'][^"\']*productphoto/is', $html, $mainImg)) {
            $mainImageUrl = $mainImg[1];
            $this->logStep('Found main product image', ['url' => $mainImageUrl]);

            // Add as first item if not already in list
            $mainPath = str_replace(self::IMAGE_BASE_URL, '', $mainImageUrl);
            $hasMain = false;
            foreach ($colors as $color) {
                if ($color['image_path'] === $mainPath) {
                    $hasMain = true;
                    break;
                }
            }

            if (!$hasMain && $mainPath !== '') {
                array_unshift($colors, [
                    'color_name' => 'Main Image',
                    'swatch_name' => 'main',
                    'image_path' => $mainPath,
                    'image_url' => $mainImageUrl,
                    'onclick_url' => null,
                    'is_main' => true
                ]);
            }
        }

        // Remove duplicates based on image_path
        $seen = [];
        $unique = [];
        foreach ($colors as $color) {
            if (!isset($seen[$color['image_path']])) {
                $seen[$color['image_path']] = true;
                $unique[] = $color;
            }
        }

        return $unique;
    }

    private function errorResult(string $message, array $context = []): array
    {
        $this->logStep('Error', array_merge(['message' => $message], $context));

        return [
            'success' => false,
            'error' => $message,
            'data' => $context,
            'log' => $this->log
        ];
    }

    private function logStep(string $message, array $context = []): void
    {
        $this->log[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => $context
        ];
    }
}

// Handle API request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'scrape') {
    $url = $_POST['url'] ?? $_GET['url'] ?? '';

    $scraper = new CompanyCasualsImageScraper();
    $result = $scraper->scrape($url);

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($action === 'download_image') {
    // Proxy download an image from Company Casuals CDN
    $imageUrl = $_POST['image_url'] ?? $_GET['image_url'] ?? '';
    $filename = $_POST['filename'] ?? $_GET['filename'] ?? 'image.jpg';

    if (empty($imageUrl)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'image_url is required']);
        exit;
    }

    // Validate URL is from Company Casuals CDN
    if (
        strpos($imageUrl, 'cdnp.companycasuals.com') === false &&
        strpos($imageUrl, 'companycasuals.com') === false
    ) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid image URL']);
        exit;
    }

    // Fetch the image
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $imageUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($imageData === false || $httpCode !== 200) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to fetch image']);
        exit;
    }

    // Sanitize filename
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
    if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
        $filename .= '.jpg';
    }

    // Send image as download
    header('Content-Type: ' . ($contentType ?: 'image/jpeg'));
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($imageData));
    header('Cache-Control: no-cache, must-revalidate');
    echo $imageData;
    exit;
}

// Default response
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'error' => 'Invalid action. Use action=scrape or action=download_image.'
]);
