<?php

/**
 * Company Casuals batch price scraper.
 *
 * Examples:
 *   php company-casuals-batch-runner.php               # scrape every code in products_new
 *   php company-casuals-batch-runner.php K525 L508     # scrape specified codes only
 *   php company-casuals-batch-runner.php --codes=K525,L508 --no-colors
 *   php company-casuals-batch-runner.php --limit=100   # scrape only the first 100 DB codes
 */

ini_set('display_errors', 'stderr');
error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    http_response_code(405);
    echo "This script must be run from the command line." . PHP_EOL;
    exit(1);
}

require_once dirname(__DIR__, 3) . '/config.php';
require_once __DIR__ . '/company-casuals-scraper-service.php';

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

$options = parseArguments($argv);
$includeColors = $options['includeColors'];
$limit = $options['limit'];
$requestedCodes = $options['codes'];

$codes = $requestedCodes ?: fetchProductCodes($host, $user, $password, $dbname, $port, $socket, $limit);

if (empty($codes)) {
    fwrite(STDERR, "No product codes available to scrape.\n");
    exit(1);
}

$outputDir = __DIR__ . '/downloads/company_casuals';
if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Failed to prepare output directory: {$outputDir}\n");
    exit(1);
}

$scraper = new CompanyCasualsScraper();
$total = count($codes);
$successCount = 0;
$noDataCount = 0;
$errorCount = 0;
$startTime = microtime(true);

echo "Starting Company Casuals batch scrape" . PHP_EOL;
echo "  Total codes: {$total}" . PHP_EOL;
echo "  Include colors: " . ($includeColors ? 'yes' : 'no') . PHP_EOL;
echo "  Output folder: {$outputDir}" . PHP_EOL;

foreach ($codes as $index => $rawCode) {
    $position = $index + 1;
    $normalizedInfo = $scraper->normalizeCode($rawCode);
    $normalizedCode = $normalizedInfo['normalized'] ?: 'unknown';
    $displayCode = $normalizedInfo['normalized'] ?: $normalizedInfo['original'] ?: 'unknown';

    echo "[{$position}/{$total}] Processing {$rawCode} (normalized: {$displayCode})... ";

    $result = $scraper->scrape([
        'code' => $rawCode,
        'includeColors' => $includeColors
    ]);

    $timestamp = date('Ymd_His');
    $safeCode = preg_replace('/[^A-Z0-9_-]+/i', '_', $normalizedCode);
    $payload = [
        'scraper' => 'company_casuals',
        'generated_at' => gmdate('c'),
        'input_code' => $rawCode,
        'normalized_code' => $normalizedInfo['normalized'],
        'result' => $result,
    ];

    if ($result['success'] && !empty($result['data']['prices'])) {
        $filename = sprintf('%s_price_scrape_%s.json', $safeCode, $timestamp);
        $filepath = $outputDir . '/' . $filename;
        file_put_contents($filepath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "saved ✅ {$filename}" . PHP_EOL;
        $successCount++;
    } else {
        $filename = sprintf('nodata_%s_price_scrape_%s.json', $safeCode, $timestamp);
        $filepath = $outputDir . '/' . $filename;

        if ($result['success']) {
            $noDataCount++;
        } else {
            $errorCount++;
        }

        file_put_contents($filepath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $status = $result['success'] ? 'no data' : 'error';
        echo "saved ⚠️ {$filename} ({$status})" . PHP_EOL;
    }

    usleep(200000); // politeness delay
}

$elapsed = microtime(true) - $startTime;
echo PHP_EOL;
echo "Batch complete in " . number_format($elapsed, 2) . " seconds" . PHP_EOL;
echo "  Successful scrapes: {$successCount}" . PHP_EOL;
echo "  No data files:      {$noDataCount}" . PHP_EOL;
echo "  Errors:             {$errorCount}" . PHP_EOL;
echo "  Output directory:   {$outputDir}" . PHP_EOL;

exit($errorCount > 0 ? 2 : ($noDataCount > 0 ? 1 : 0));

function parseArguments(array $argv): array
{
    $includeColors = true;
    $limit = null;
    $codes = [];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--no-colors') {
            $includeColors = false;
            continue;
        }

        if (preg_match('/^--limit=(\d+)$/', $arg, $matches)) {
            $limit = (int)$matches[1];
            continue;
        }

        if (str_starts_with($arg, '--codes=')) {
            $list = substr($arg, 8);
            $parts = array_filter(array_map('trim', explode(',', $list)));
            $codes = array_merge($codes, $parts);
            continue;
        }

        if (str_starts_with($arg, '--')) {
            fwrite(STDERR, "Unknown option: {$arg}\n");
            continue;
        }

        $codes[] = $arg;
    }

    $codes = array_values(array_unique(array_filter($codes)));

    return [
        'includeColors' => $includeColors,
        'limit' => $limit,
        'codes' => $codes
    ];
}

function fetchProductCodes(string $host, string $user, string $password, string $dbname, ?int $port, ?string $socket, ?int $limit): array
{
    $codes = [];
    $conn = @new mysqli($host, $user, $password, $dbname, $port ?? ini_get('mysqli.default_port'), $socket ?? ini_get('mysqli.default_socket'));

    if ($conn->connect_errno !== 0) {
        fwrite(STDERR, "Database connection failed: {$conn->connect_error}\n");
        return $codes;
    }

    $sql = "SELECT DISTINCT code FROM products_new WHERE code IS NOT NULL AND code <> '' ORDER BY code";
    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int)$limit;
    }

    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $code = trim($row['code']);
            if ($code !== '') {
                $codes[] = $code;
            }
        }
        $res->free();
    } else {
        fwrite(STDERR, "Failed to fetch product codes: {$conn->error}\n");
    }

    $conn->close();

    return $codes;
}
