<?php
// Company Casuals Product Scraper API
session_start();
header('Content-Type: application/json');

if (!defined('COMPANY_CASUALS_REQUIRE_AUTH')) {
    define('COMPANY_CASUALS_REQUIRE_AUTH', false);
}

if (COMPANY_CASUALS_REQUIRE_AUTH) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit;
    }
}

require_once __DIR__ . '/company-casuals-scraper-service.php';
require_once dirname(__DIR__, 3) . '/config.php';

if (!defined('COMPANY_CASUALS_VENDOR_ID')) {
    define('COMPANY_CASUALS_VENDOR_ID', 5);
}

if (!defined('COMPANY_CASUALS_PRICE_TABLE')) {
    define('COMPANY_CASUALS_PRICE_TABLE', 'prices_new');
}

if (!defined('COMPANY_CASUALS_PRICE_TABLE_FALLBACK')) {
    define('COMPANY_CASUALS_PRICE_TABLE_FALLBACK', 'prices');
}

$action = $_POST['action'] ?? $_GET['action'] ?? parseJsonAction() ?? 'scrape';

switch ($action) {
    case 'scrape':
        handleScrape();
        break;
    case 'import':
        handleImport();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleScrape(): void
{
    $code = trim($_POST['code'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $includeColors = isset($_POST['includeColors']) ? $_POST['includeColors'] !== '0' : true;

    if ($code === '' && $url === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Provide a product code or URL']);
        return;
    }

    $scraper = new CompanyCasualsScraper();
    $result = $scraper->scrape([
        'code' => $code,
        'url' => $url,
        'includeColors' => $includeColors
    ]);

    if (!$result['success']) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => $result['error'],
            'data' => $result['data'],
            'log' => $result['log'] ?? []
        ]);
        return;
    }

    $data = $result['data'];
    $log = $data['log'] ?? [];
    unset($data['log']);
    $timestamp = date('Ymd_His');
    $baseCode = $data['normalized_code'] ?: ($data['original_code'] ?: 'product');
    $filename = sprintf('%s_price_scrape_%s.json', $baseCode, $timestamp);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'log' => $log,
        'suggestedFilename' => $filename
    ]);
}

function handleImport(): void
{
    global $host, $user, $password, $dbname, $port, $socket;

    $payloadInfo = readImportPayload();
    if ($payloadInfo['raw'] === null) {
        http_response_code(400);
        echo json_encode(['error' => 'No JSON payload provided.']);
        return;
    }

    $decoded = json_decode($payloadInfo['raw'], true);
    if (!is_array($decoded)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid JSON payload.',
            'details' => ['json_error' => json_last_error_msg()]
        ]);
        return;
    }

    $extracted = extractImportData($decoded);
    if ($extracted['data'] === null) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Could not locate parsed scrape data inside JSON.',
            'details' => ['hints' => $extracted['hints']]
        ]);
        return;
    }

    $data = $extracted['data'];
    $prices = $data['prices'] ?? [];
    if (!is_array($prices) || count($prices) === 0) {
        http_response_code(422);
        echo json_encode(['error' => 'No pricing entries found in payload.']);
        return;
    }

    $codeCandidates = array_values(array_unique(array_filter($extracted['codes'], static fn($value) => is_string($value) && trim($value) !== '')));
    if (empty($codeCandidates)) {
        http_response_code(422);
        echo json_encode(['error' => 'Could not determine product code from payload.']);
        return;
    }

    $mysqli = @new mysqli($host, $user, $password, $dbname, $port ?? ini_get('mysqli.default_port'), $socket ?? ini_get('mysqli.default_socket'));
    if ($mysqli->connect_errno !== 0) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database connection failed.',
            'details' => ['reason' => $mysqli->connect_error]
        ]);
        return;
    }

    $mysqli->set_charset('utf8mb4');

    $transactionStarted = false;
    $insertStmt = null;
    $updateStmt = null;

    try {
        $productId = findProductId($mysqli, $codeCandidates);
        if ($productId === null) {
            http_response_code(404);
            echo json_encode([
                'error' => 'Product code not found in database.',
                'details' => ['codes_tested' => $codeCandidates]
            ]);
            $mysqli->close();
            return;
        }

        $sizeLookup = buildSizeLookup($mysqli);
        if (empty($sizeLookup)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to build size lookup from sizes_new table.']);
            $mysqli->close();
            return;
        }

        $priceTable = resolvePriceTable($mysqli);
        $existingPrices = loadExistingPrices($mysqli, $productId, COMPANY_CASUALS_VENDOR_ID, $priceTable);

        if (!preg_match('/^[A-Za-z0-9_]+$/', $priceTable)) {
            throw new RuntimeException('Unexpected price table name: ' . $priceTable);
        }

        $tableIdentifier = sprintf('`%s`', $priceTable);
        $insertStmt = $mysqli->prepare(sprintf('INSERT INTO %s (product_id, vendor_id, size_id, price, isActive) VALUES (?, ?, ?, ?, 1)', $tableIdentifier));
        $updateStmt = $mysqli->prepare(sprintf('UPDATE %s SET price = ? WHERE price_id = ?', $tableIdentifier));

        if ($insertStmt === false || $updateStmt === false) {
            throw new RuntimeException('Failed to prepare insert/update statements: ' . $mysqli->error);
        }

        $mysqli->begin_transaction();
        $transactionStarted = true;

        $summary = [
            'inserted' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0
        ];
        $warnings = [];

        $productIdInt = (int)$productId;
        $vendorIdInt = (int)COMPANY_CASUALS_VENDOR_ID;

        foreach ($prices as $entry) {
            if (!is_array($entry)) {
                $summary['skipped']++;
                $warnings[] = ['reason' => 'Malformed price entry', 'entry' => $entry];
                continue;
            }

            $sizeLabel = (string)($entry['size'] ?? '');
            $priceValue = $entry['price'] ?? null;

            if ($sizeLabel === '') {
                $summary['skipped']++;
                $warnings[] = ['reason' => 'Missing size label', 'entry' => $entry];
                continue;
            }

            $sizeKey = normalizeSizeKey($sizeLabel);
            $sizeId = $sizeLookup[$sizeKey] ?? null;

            if ($sizeId === null) {
                $summary['skipped']++;
                $warnings[] = ['reason' => 'Unknown size', 'size' => $sizeLabel, 'normalized' => $sizeKey];
                continue;
            }

            if (!is_numeric($priceValue)) {
                $summary['skipped']++;
                $warnings[] = ['reason' => 'Invalid price', 'size' => $sizeLabel, 'price' => $priceValue];
                continue;
            }

            $priceFloat = (float)$priceValue;
            $sizeIdInt = (int)$sizeId;

            if (isset($existingPrices[$sizeIdInt])) {
                $existing = $existingPrices[$sizeIdInt];
                $currentPrice = (float)$existing['price'];

                if (abs($currentPrice - $priceFloat) < 0.0001) {
                    $summary['unchanged']++;
                    continue;
                }

                $priceId = (int)$existing['price_id'];
                $updateStmt->bind_param('di', $priceFloat, $priceId);
                if (!$updateStmt->execute()) {
                    throw new RuntimeException('Failed to update price_id ' . $priceId . ': ' . $updateStmt->error);
                }
                $summary['updated']++;
            } else {
                $insertStmt->bind_param('iiid', $productIdInt, $vendorIdInt, $sizeIdInt, $priceFloat);
                if (!$insertStmt->execute()) {
                    throw new RuntimeException('Failed to insert price for size ' . $sizeLabel . ': ' . $insertStmt->error);
                }
                $summary['inserted']++;
            }
        }

        $mysqli->commit();
        $transactionStarted = false;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'summary' => array_merge($summary, [
                'product_id' => $productId,
                'vendor_id' => COMPANY_CASUALS_VENDOR_ID,
                'processed_codes' => $codeCandidates,
                'total_prices' => count($prices)
            ]),
            'warnings' => $warnings,
            'source' => $payloadInfo['source'],
            'payload_hint' => $extracted['hint']
        ]);
    } catch (Throwable $e) {
        if ($transactionStarted) {
            $mysqli->rollback();
        }

        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to import pricing data.',
            'details' => $e->getMessage()
        ]);
    } finally {
        if ($insertStmt instanceof mysqli_stmt) {
            $insertStmt->close();
        }
        if ($updateStmt instanceof mysqli_stmt) {
            $updateStmt->close();
        }
        $mysqli->close();
    }
}

function parseJsonAction(): ?string
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') === false) {
        return null;
    }

    $body = getRawRequestBody();
    if ($body === '') {
        return null;
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return null;
    }

    return isset($decoded['action']) && is_string($decoded['action']) ? $decoded['action'] : null;
}

function readImportPayload(): array
{
    $raw = null;
    $source = 'unknown';

    if (!empty($_FILES['jsonFile']) && is_uploaded_file($_FILES['jsonFile']['tmp_name'])) {
        $raw = file_get_contents($_FILES['jsonFile']['tmp_name']);
        $source = 'file-upload';
    } elseif (isset($_POST['payload'])) {
        $raw = (string)$_POST['payload'];
        $source = 'form-payload';
    } else {
        $body = getRawRequestBody();
        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['payload'])) {
                $raw = is_string($decoded['payload']) ? $decoded['payload'] : json_encode($decoded['payload']);
                $source = 'json-body';
            } else {
                $raw = $body;
                $source = 'raw-json';
            }
        }
    }

    return ['raw' => $raw, 'source' => $source];
}

function extractImportData(array $decoded): array
{
    $hints = [];
    $codes = [];
    $candidateData = null;
    $hint = null;

    if (isset($decoded['result']['data']) && is_array($decoded['result']['data'])) {
        $candidateData = $decoded['result']['data'];
        $hint = 'result.data';
        $codes[] = $candidateData['normalized_code'] ?? null;
        $codes[] = $candidateData['original_code'] ?? null;
        $codes[] = $candidateData['input_code'] ?? null;
        $codes[] = $decoded['normalized_code'] ?? null;
        $codes[] = $decoded['input_code'] ?? null;
    } elseif (isset($decoded['result']) && is_array($decoded['result']) && isset($decoded['result']['prices'])) {
        $candidateData = $decoded['result'];
        $hint = 'result';
        $codes[] = $candidateData['normalized_code'] ?? null;
        $codes[] = $candidateData['original_code'] ?? null;
        $codes[] = $candidateData['input_code'] ?? null;
    } elseif (isset($decoded['data']) && is_array($decoded['data']) && isset($decoded['data']['prices'])) {
        $candidateData = $decoded['data'];
        $hint = 'data';
        $codes[] = $candidateData['normalized_code'] ?? null;
        $codes[] = $candidateData['original_code'] ?? null;
        $codes[] = $candidateData['input_code'] ?? null;
    } elseif (isset($decoded['prices'])) {
        $candidateData = $decoded;
        $hint = 'root';
        $codes[] = $decoded['normalized_code'] ?? null;
        $codes[] = $decoded['original_code'] ?? null;
        $codes[] = $decoded['input_code'] ?? null;
    } else {
        $hints[] = 'Expected keys: result.data, result, or data containing prices array.';
    }

    return [
        'data' => $candidateData,
        'codes' => $codes,
        'hints' => $hints,
        'hint' => $hint
    ];
}

function normalizeSizeKey(?string $label): string
{
    if ($label === null) {
        return '';
    }

    $decoded = html_entity_decode($label, ENT_QUOTES | ENT_HTML5);
    if (preg_match('/\(([^)]+)\)$/', $decoded, $match)) {
        $decoded = $match[1];
    }

    $decoded = str_replace(['–', '—'], '-', $decoded);
    $decoded = strtoupper(trim($decoded));

    if ($decoded === '') {
        return '';
    }

    if (stripos($decoded, 'SIZE ') === 0) {
        $decoded = trim(substr($decoded, 5));
    }

    $decoded = str_replace([' ', '-', '_'], '', $decoded);

    $aliases = [
        'XXL' => '2XL',
        'XXXL' => '3XL',
        'XXXXL' => '4XL',
        'LARGETALL' => 'LT',
        'EXTRALARGETALL' => 'XLT',
        '2XLARGETALL' => '2XLT',
        '3XLARGETALL' => '3XLT',
        '4XLARGETALL' => '4XLT',
        'OSFA' => 'ONESIZE',
        'OSFM' => 'ONESIZE',
        'OS' => 'ONESIZE'
    ];

    $upper = strtoupper($decoded);
    return $aliases[$upper] ?? $upper;
}

function buildSizeLookup(mysqli $mysqli): array
{
    $map = [];
    $sql = 'SELECT size_id, size_name FROM sizes_new';
    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $id = (int)$row['size_id'];
            $name = (string)$row['size_name'];

            $map[normalizeSizeKey($name)] = $id;

            if (preg_match('/\(([^)]+)\)/', $name, $match)) {
                $map[normalizeSizeKey($match[1])] = $id;
            }
        }
        $result->free();
    }

    // Remove empty keys
    return array_filter($map, static fn($value) => $value !== null && $value !== '');
}

function findProductId(mysqli $mysqli, array $codes): ?int
{
    $stmt = $mysqli->prepare('SELECT product_id FROM products_new WHERE code = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('Failed to prepare product lookup statement: ' . $mysqli->error);
    }

    $productId = null;
    foreach ($codes as $rawCode) {
        $code = trim((string)$rawCode);
        if ($code === '') {
            continue;
        }

        $stmt->bind_param('s', $code);
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute product lookup: ' . $stmt->error);
        }

        $stmt->bind_result($foundId);
        if ($stmt->fetch()) {
            $productId = (int)$foundId;
            $stmt->free_result();
            break;
        }
        $stmt->free_result();

        $alternate = 'SM-' . $code;
        $stmt->bind_param('s', $alternate);
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute alternate product lookup: ' . $stmt->error);
        }

        $stmt->bind_result($foundAltId);
        if ($stmt->fetch()) {
            $productId = (int)$foundAltId;
            $stmt->free_result();
            break;
        }
        $stmt->free_result();
    }

    $stmt->close();
    return $productId;
}

function resolvePriceTable(mysqli $mysqli): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    $candidates = array_filter(array_unique([
        COMPANY_CASUALS_PRICE_TABLE,
        COMPANY_CASUALS_PRICE_TABLE_FALLBACK,
    ]));

    foreach ($candidates as $candidate) {
        $query = sprintf("SHOW TABLES LIKE '%s'", $mysqli->real_escape_string($candidate));
        $result = $mysqli->query($query);
        if ($result === false) {
            throw new RuntimeException('Failed to execute table existence check: ' . $mysqli->error);
        }

        $exists = $result->num_rows > 0;
        if ($result) {
            $result->free();
        }

        if ($exists) {
            $resolved = $candidate;
            break;
        }
    }

    if ($resolved === null) {
        throw new RuntimeException('Neither prices_new nor prices table exists.');
    }

    return $resolved;
}

function loadExistingPrices(mysqli $mysqli, int $productId, int $vendorId, string $table): array
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        throw new RuntimeException('Unexpected price table name: ' . $table);
    }

    $query = sprintf('SELECT price_id, size_id, price, isActive FROM `%s` WHERE product_id = ? AND vendor_id = ?', $table);
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        throw new RuntimeException('Failed to prepare existing price lookup: ' . $mysqli->error);
    }

    $stmt->bind_param('ii', $productId, $vendorId);
    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to execute price lookup: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[(int)$row['size_id']] = [
            'price_id' => (int)$row['price_id'],
            'price' => (float)$row['price'],
            'isActive' => (int)$row['isActive']
        ];
    }
    $result->free();
    $stmt->close();

    return $map;
}

function getRawRequestBody(): string
{
    static $cachedBody = null;
    if ($cachedBody === null) {
        $cachedBody = file_get_contents('php://input') ?: '';
    }

    return $cachedBody;
}
