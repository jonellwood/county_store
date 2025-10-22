#!/usr/bin/env php
<?php
/**
 * Test Size Mapping for Company Casuals Scraper
 * 
 * This script tests that the size normalization logic correctly maps
 * JSON size values to database size_ids.
 * 
 * Usage: php test-size-mapping.php
 */

require_once dirname(__DIR__, 3) . '/config.php';
require_once __DIR__ . '/company-casuals-scraper-service.php';

// Normalize function (copied from API)
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

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "  SIZE MAPPING TEST - Company Casuals Scraper\n";
echo str_repeat("=", 70) . "\n\n";

// Connect to database
try {
    $mysqli = new mysqli($host, $user, $password, $dbname, $port, $socket);
    if ($mysqli->connect_errno !== 0) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Build size lookup
echo "Building size lookup from sizes_new table...\n";
echo str_repeat("-", 70) . "\n";

$map = [];
$sql = 'SELECT size_id, size_name FROM sizes_new';
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['size_id'];
        $name = (string)$row['size_name'];

        $normalized = normalizeSizeKey($name);
        $map[$normalized] = $id;

        printf("%-30s → %-15s → size_id: %d\n", $name, $normalized, $id);

        // Also check if there's a parenthetical alternate
        if (preg_match('/\(([^)]+)\)/', $name, $match)) {
            $altNormalized = normalizeSizeKey($match[1]);
            if ($altNormalized !== $normalized) {
                $map[$altNormalized] = $id;
                printf("  (alternate: %-15s → size_id: %d)\n", $altNormalized, $id);
            }
        }
    }
    $result->free();
}

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "Size Lookup Map Created: " . count($map) . " entries\n";
echo str_repeat("=", 70) . "\n\n";

// Test with BB18403 sizes
$testSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];

echo "Testing BB18403 sizes from JSON:\n";
echo str_repeat("-", 70) . "\n";

foreach ($testSizes as $size) {
    $normalized = normalizeSizeKey($size);
    $sizeId = $map[$normalized] ?? null;

    if ($sizeId !== null) {
        printf("✓ %-10s → %-15s → size_id: %d\n", $size, $normalized, $sizeId);
    } else {
        printf("✗ %-10s → %-15s → NOT FOUND (would be skipped)\n", $size, $normalized);
    }
}

echo "\n";
echo str_repeat("=", 70) . "\n";

$mysqli->close();
exit(0);
