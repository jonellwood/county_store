<?php

/**
 * Company Casuals product scraper service
 * Shared between UI API and batch runner.
 */

class CompanyCasualsScraper
{
    private string $baseUrl = 'https://www.companycasuals.com/printcharleston/b.jsp';

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

    public function scrape(array $options): array
    {
        $this->log = [];

        $rawCode = trim($options['code'] ?? '');
        $explicitUrl = trim($options['url'] ?? '');
        $includeColors = (bool)($options['includeColors'] ?? true);

        if ($explicitUrl === '' && $rawCode === '') {
            return $this->errorResult('A product code or URL is required.');
        }

        // Normalize code when provided
        $normalizedInfo = $this->normalizeCode($rawCode !== '' ? $rawCode : ($options['fallbackCode'] ?? ''));
        $normalizedCode = $normalizedInfo['normalized'];

        $targetUrl = $explicitUrl !== ''
            ? $explicitUrl
            : $this->buildProductUrl($normalizedCode);

        $this->logStep("Fetching product page", ['url' => $targetUrl, 'raw_code' => $rawCode, 'normalized_code' => $normalizedCode]);

        $fetchResult = $this->fetchHtml($targetUrl);
        if ($fetchResult['status'] !== 'ok') {
            return $this->errorResult($fetchResult['message'], [
                'http_code' => $fetchResult['http_code'],
                'url' => $targetUrl,
                'raw_code' => $rawCode,
                'normalized_code' => $normalizedCode
            ]);
        }

        $html = $fetchResult['html'];
        $finalUrl = $fetchResult['final_url'];

        $this->logStep('Fetched HTML', [
            'bytes' => strlen($html),
            'http_code' => $fetchResult['http_code'],
            'final_url' => $finalUrl
        ]);

        $parsed = $this->parseProductHtml($html, [
            'raw_code' => $rawCode,
            'normalized_code' => $normalizedCode,
            'target_url' => $targetUrl,
            'final_url' => $finalUrl,
            'include_colors' => $includeColors
        ]);

        if ($parsed['success'] === false) {
            return $this->errorResult($parsed['error'], $parsed['data']);
        }

        $parsed['data']['log'] = $this->log;

        return [
            'success' => true,
            'data' => $parsed['data']
        ];
    }

    public function normalizeCode(string $code): array
    {
        $original = trim($code);
        $normalized = $original;

        if ($normalized !== '') {
            // Strip leading SM-, sm-, sm , etc.
            $normalized = preg_replace('/^SM[-\s]*/i', '', $normalized);
            // Remove whitespace inside the code
            $normalized = preg_replace('/\s+/', '', $normalized);
            $normalized = strtoupper($normalized);
        }

        return [
            'original' => $original,
            'normalized' => $normalized
        ];
    }

    public function buildProductUrl(string $code): string
    {
        if ($code === '') {
            return $this->baseUrl;
        }

        $query = http_build_query([
            'productId' => $code,
            'parentId' => 115,
            'prodimage' => $code . '.jpg',
            'cart' => 'Y',
            'swatch' => '',
            'httpsecure' => 'Y'
        ]);

        return $this->baseUrl . '?' . $query;
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
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($html === false) {
            return [
                'status' => 'error',
                'http_code' => $httpCode,
                'message' => 'Unable to fetch product page: ' . ($curlError ?: 'unknown error')
            ];
        }

        if ($httpCode !== 200) {
            return [
                'status' => 'error',
                'http_code' => $httpCode,
                'message' => "Unexpected HTTP status {$httpCode} when fetching product page"
            ];
        }

        if (strlen($html) < 5000) {
            return [
                'status' => 'error',
                'http_code' => $httpCode,
                'message' => 'Received unusually small response body; page might not exist or requires authentication'
            ];
        }

        return [
            'status' => 'ok',
            'http_code' => $httpCode,
            'html' => $html,
            'final_url' => $finalUrl
        ];
    }

    private function parseProductHtml(string $html, array $context): array
    {
        $detectedEncoding = mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252', 'ASCII'], true) ?: 'UTF-8';
        if (strtoupper($detectedEncoding) !== 'UTF-8') {
            $convertedHtml = mb_convert_encoding($html, 'UTF-8', $detectedEncoding);
        } else {
            $convertedHtml = $html;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loadResult = $dom->loadHTML('<?xml encoding="UTF-8" ?>' . $convertedHtml);
        libxml_clear_errors();

        if (!$loadResult) {
            return [
                'success' => false,
                'error' => 'Failed to parse HTML content',
                'data' => $this->baseResult($context, ['parser' => 'loadHTML failed'])
            ];
        }

        $xpath = new DOMXPath($dom);

        $productName = $this->extractProductName($xpath);
        $productCodeFromPage = $this->extractProductCode($xpath);
        $priceTable = $this->extractPriceTable($xpath);
        $colors = $context['include_colors'] ? $this->extractColors($xpath) : [];

        if ($priceTable['success'] === false) {
            return [
                'success' => false,
                'error' => $priceTable['error'],
                'data' => $this->baseResult($context, [
                    'product_name' => $productName,
                    'page_code' => $productCodeFromPage,
                    'colors' => $colors,
                    'html_sample' => substr(strip_tags($dom->saveHTML()), 0, 2000)
                ])
            ];
        }

        $result = $this->baseResult($context, [
            'product_name' => $productName,
            'page_code' => $productCodeFromPage,
            'price_reference_color' => $priceTable['reference_color'],
            'sizes' => $priceTable['sizes'],
            'prices' => $priceTable['prices'],
            'colors' => $colors
        ]);

        return [
            'success' => true,
            'data' => $result
        ];
    }

    private function extractProductName(DOMXPath $xpath): ?string
    {
        $titleNode = $xpath->query('//title')->item(0);
        if ($titleNode) {
            $title = trim(html_entity_decode($titleNode->textContent));
            if (preg_match('/,\s*(.+?)\.\s*([A-Za-z0-9\-]+)\s*$/', $title, $matches)) {
                $this->logStep('Extracted product name from title', ['name' => $matches[1]]);
                return trim($matches[1]);
            }
            $this->logStep('Using raw title as product name fallback');
            return $title;
        }

        $nameNode = $xpath->query("//div[contains(@class,'prod_desc_name')]//span")->item(0);
        if ($nameNode) {
            $name = trim(html_entity_decode($nameNode->textContent));
            $this->logStep('Extracted product name from prod_desc_name');
            return $name;
        }

        return null;
    }

    private function extractProductCode(DOMXPath $xpath): ?string
    {
        $codeNode = $xpath->query("//input[@name='productId']")->item(0);
        if ($codeNode instanceof DOMElement && $codeNode->hasAttribute('value')) {
            $code = trim($codeNode->getAttribute('value'));
            if ($code !== '') {
                $this->logStep('Extracted product code from hidden input', ['code' => $code]);
                return strtoupper($code);
            }
        }

        $titleNode = $xpath->query('//title')->item(0);
        if ($titleNode) {
            $title = trim(html_entity_decode($titleNode->textContent));
            if (preg_match('/\.\s*([A-Za-z0-9\-]+)\s*$/', $title, $matches)) {
                return strtoupper($matches[1]);
            }
        }

        return null;
    }

    private function extractPriceTable(DOMXPath $xpath): array
    {
        $tableNode = $xpath->query("//div[@id='I1']//table[contains(@class,'shoptable')]")->item(0);
        if (!$tableNode) {
            $this->logStep('Price table not found');
            return ['success' => false, 'error' => 'Pricing table not found on page'];
        }

        $headerRow = $xpath->query(".//tr[contains(@class,'headergrey')]", $tableNode)->item(0);
        if (!$headerRow) {
            $this->logStep('Header row missing in price table');
            return ['success' => false, 'error' => 'Pricing header row not found'];
        }

        $expandedHeader = [];
        $headerCells = $xpath->query('.//td', $headerRow);
        foreach ($headerCells as $cell) {
            if (!$cell instanceof DOMElement) {
                continue;
            }

            $label = trim(preg_replace('/\s+/', ' ', html_entity_decode($cell->textContent, ENT_QUOTES | ENT_HTML5)));
            $colspan = (int)$cell->getAttribute('colspan');
            if ($colspan < 1) {
                $colspan = 1;
            }

            for ($i = 0; $i < $colspan; $i++) {
                $expandedHeader[] = $label;
            }
        }

        $colorRows = $xpath->query(".//tr[td[@class='swatch']]", $tableNode);
        if ($colorRows->length === 0) {
            $this->logStep('No color rows found in price table');
            return ['success' => false, 'error' => 'No pricing rows detected'];
        }

        $firstColorRow = $colorRows->item(0);
        $colorNameNode = $xpath->query(".//td[contains(@class,'description')]//strong", $firstColorRow)->item(0);
        $referenceColor = $colorNameNode ? trim($colorNameNode->textContent) : null;

        $priceCells = $xpath->query('.//td[position()>2]', $firstColorRow);
        $prices = [];
        $reportedSizes = [];

        $priceColumnCount = $priceCells->length;
        if ($priceColumnCount === 0) {
            $this->logStep('No price cells detected in first color row');
            return ['success' => false, 'error' => 'No pricing data detected'];
        }

        $sizeLabelsRaw = array_slice($expandedHeader, -$priceColumnCount);
        $sizeLabels = [];
        foreach ($sizeLabelsRaw as $rawLabel) {
            $sizeLabels[] = $this->normalizeSizeLabel($rawLabel);
        }

        foreach ($priceCells as $idx => $cell) {
            $size = $sizeLabels[$idx] ?? '';
            if ($size === '') {
                $size = 'Size ' . ($idx + 1);
            }
            $rawText = trim($cell->textContent);
            $amount = $this->parsePrice($rawText);

            $prices[] = [
                'size' => $size,
                'price' => $amount,
                'raw' => $rawText,
                'currency' => 'USD'
            ];
            $reportedSizes[] = $size;
        }

        $this->logStep('Parsed pricing row', [
            'reference_color' => $referenceColor,
            'sizes' => $reportedSizes,
            'price_count' => count($prices)
        ]);

        return [
            'success' => true,
            'sizes' => $reportedSizes,
            'prices' => $prices,
            'reference_color' => $referenceColor
        ];
    }

    private function extractColors(DOMXPath $xpath): array
    {
        $colors = [];

        $hiddenSwatches = $xpath->query("//input[@name='allswatches']")->item(0);
        if ($hiddenSwatches instanceof DOMElement && $hiddenSwatches->hasAttribute('value')) {
            $raw = $hiddenSwatches->getAttribute('value');
            $parts = array_filter(array_map('trim', explode('~', $raw)));
            foreach ($parts as $part) {
                $colors[] = html_entity_decode($part);
            }
        }

        if (empty($colors)) {
            $colorCells = $xpath->query("//div[@id='I1']//table[contains(@class,'shoptable')]//tr[td[@class='swatch']]//td[contains(@class,'description')]");
            foreach ($colorCells as $cell) {
                $strong = $xpath->query('.//strong', $cell)->item(0);
                $name = $strong ? $strong->textContent : $cell->textContent;
                $name = trim(preg_replace('/\s+/', ' ', $name));
                if ($name !== '') {
                    $colors[] = html_entity_decode($name);
                }
            }
        }

        $colors = array_values(array_unique(array_filter($colors)));
        $this->logStep('Extracted colors', ['count' => count($colors)]);
        return $colors;
    }

    private function normalizeSizeLabel(?string $label): string
    {
        if ($label === null) {
            return '';
        }

        $decoded = html_entity_decode($label, ENT_QUOTES | ENT_HTML5);
        $clean = trim(preg_replace('/\s+/', ' ', $decoded));
        if ($clean === '') {
            return '';
        }

        if (stripos($clean, 'Shop for this Item') === 0) {
            return '';
        }

        $clean = str_replace(['–', '—'], '-', $clean);
        $clean = preg_replace('/\s*-\s*/', '-', $clean);
        $clean = preg_replace('/\s*\/\s*/', '/', $clean);
        $clean = rtrim($clean, ':');

        $compact = strtoupper(str_replace(' ', '', $clean));
        $tokenMap = [
            'XXXS' => 'XXXS',
            'XXS' => 'XXS',
            'XS' => 'XS',
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            'XXL' => 'XXL',
            'XXXL' => 'XXXL',
            'XXXXL' => 'XXXXL',
            '2XL' => '2XL',
            '3XL' => '3XL',
            '4XL' => '4XL',
            '5XL' => '5XL',
            '6XL' => '6XL',
            '7XL' => '7XL',
            '8XL' => '8XL',
            'LT' => 'LT',
            'XLT' => 'XLT',
            '2XLT' => '2XLT',
            '3XLT' => '3XLT',
            '4XLT' => '4XLT',
            '5XLT' => '5XLT',
            '6XLT' => '6XLT',
            '7XLT' => '7XLT',
            '8XLT' => '8XLT',
            'OS' => 'OS',
            'OSFM' => 'OSFM',
            'OSFA' => 'OSFA',
            'ONESIZE' => 'ONE SIZE',
            'ONESZ' => 'ONE SIZE',
            'ONE SIZE' => 'ONE SIZE',
            'YXS' => 'YXS',
            'YS' => 'YS',
            'YM' => 'YM',
            'YL' => 'YL',
            'YXL' => 'YXL',
            '2T' => '2T',
            '3T' => '3T',
            '4T' => '4T',
            '5T' => '5T',
            '6T' => '6T'
        ];

        if (isset($tokenMap[$compact])) {
            return $tokenMap[$compact];
        }

        if (preg_match('/^SIZE\s*(\d+[A-Z]?)$/i', $clean, $matches)) {
            return 'SIZE ' . strtoupper($matches[1]);
        }

        $normalized = strtoupper($clean);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    private function parsePrice(string $value): ?float
    {
        $clean = str_replace([',', '$', 'USD'], '', $value);
        $clean = trim($clean);
        if ($clean === '') {
            return null;
        }

        if (preg_match('/-/', $clean)) {
            // Ignore ranges like "S-XL" by splitting and taking first numeric
            $parts = preg_split('/[^0-9.]+/', $clean);
            foreach ($parts as $part) {
                if ($part !== '') {
                    return (float)$part;
                }
            }
        }

        return is_numeric($clean) ? (float)$clean : null;
    }

    private function baseResult(array $context, array $extra = []): array
    {
        return array_merge([
            'original_code' => $context['raw_code'] ?? null,
            'normalized_code' => $context['normalized_code'] ?? null,
            'source_url' => $context['target_url'] ?? null,
            'final_url' => $context['final_url'] ?? null,
            'scraped_at' => gmdate('c'),
        ], $extra);
    }

    private function errorResult(string $message, array $context = []): array
    {
        $this->logStep('Error', ['message' => $message] + $context);

        return [
            'success' => false,
            'error' => $message,
            'data' => $this->baseResult($context, [
                'error' => $message,
                'notes' => ['Scrape did not return pricing data']
            ]),
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
