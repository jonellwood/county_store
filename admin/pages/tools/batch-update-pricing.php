<?php

/**
 * Batch Pricing Update Script
 * 
 * Processes all JSON price scrape files from Company Casuals and updates the database
 * - Updates existing prices if product_id + size_id + vendor_id match
 * - Inserts new price records if no match exists
 * - Sets vendor_id = 5 for all Company Casuals pricing
 * - Maintains data integrity with unique constraint on (product_id, size_id, vendor_id)
 * 
 * Usage: Run from command line or browser
 * Author: Jon Ellwood
 * Created: 2025-10-21
 */

// Include database configuration
require_once '../../../config.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vendor ID for Company Casuals
const VENDOR_ID = 5;

// Email notification settings
const NOTIFICATION_EMAIL = 'store@berkeleycountysc.gov';
const SMTP_HOST = '10.50.10.10';
const SMTP_PORT = 25;

// Directory containing the JSON files
$jsonDirectory = __DIR__ . '/downloads/company_casuals/';

// DRY RUN MODE - Set to true to preview changes without committing
// Can be overridden via command line: php batch-update-pricing.php --dry-run
$dryRun = false;

// Size mapping - maps Company Casuals size names to database size_ids
// We need to query the database to build this mapping
function getSizeMapping($conn)
{
    $sizeMap = [];
    $sql = "SELECT size_id, size_name FROM sizes_new ORDER BY size_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Map size to size_id
            $sizeMap[strtoupper(trim($row['size_name']))] = $row['size_id'];
        }
    }

    return $sizeMap;
}

// Send email notification
function sendEmailNotification($stats, $duration, $dryRun, $errors = [])
{
    $subject = $dryRun
        ? "[DRY RUN] Batch Pricing Update - Preview Complete"
        : "Batch Pricing Update - Complete";

    $mode = $dryRun ? "DRY RUN MODE" : "LIVE MODE";
    $modeColor = $dryRun ? "#ff9800" : "#28a745";

    // Build HTML email
    ob_start();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }

            .header {
                background: <?php echo $modeColor; ?>;
                color: white;
                padding: 20px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin: 20px 0;
            }

            .stat-box {
                background: #f8f9fa;
                border-left: 4px solid #007bff;
                padding: 15px;
                border-radius: 3px;
            }

            .stat-box.success {
                border-left-color: #28a745;
            }

            .stat-box.warning {
                border-left-color: #ffc107;
            }

            .stat-box.error {
                border-left-color: #dc3545;
            }

            .stat-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
            }

            .stat-value {
                font-size: 28px;
                font-weight: bold;
                color: #333;
            }

            .summary {
                background: #e9ecef;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }

            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                font-size: 12px;
                color: #666;
            }

            .dry-run-notice {
                background: #fff3cd;
                border: 2px solid #ff9800;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="header">
                <h1>&#128203; Batch Pricing Update Report</h1>
                <p style="margin: 5px 0 0 0;"><?php echo $mode; ?> - Company Casuals (Vendor ID: <?php echo VENDOR_ID; ?>)</p>
            </div>

            <?php if ($dryRun): ?>
                <div class="dry-run-notice">
                    <strong>&#9888; DRY RUN MODE</strong><br>
                    This was a preview run. No changes were made to the database.<br>
                    To apply these changes, run the script without the --dry-run flag.
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-box success">
                    <div class="stat-label">Files Processed</div>
                    <div class="stat-value"><?php echo $stats['processed_files']; ?></div>
                </div>

                <div class="stat-box warning">
                    <div class="stat-label">Files Skipped</div>
                    <div class="stat-value"><?php echo $stats['skipped_files']; ?></div>
                </div>

                <div class="stat-box success">
                    <div class="stat-label">Prices Inserted</div>
                    <div class="stat-value"><?php echo $stats['inserted']; ?></div>
                </div>

                <div class="stat-box success">
                    <div class="stat-label">Prices Updated</div>
                    <div class="stat-value"><?php echo $stats['updated']; ?></div>
                </div>

                <div class="stat-box">
                    <div class="stat-label">Prices Unchanged</div>
                    <div class="stat-value"><?php echo $stats['unchanged']; ?></div>
                </div>

                <div class="stat-box error">
                    <div class="stat-label">Errors</div>
                    <div class="stat-value"><?php echo $stats['errors']; ?></div>
                </div>
            </div>

            <div class="summary">
                <strong>Summary:</strong><br>
                • Products not found: <?php echo $stats['products_not_found']; ?><br>
                • Unmapped sizes: <?php echo $stats['unmapped_sizes']; ?><br>
                • Total execution time: <?php echo $duration; ?> seconds
            </div>

            <div class="footer">
                <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Script:</strong> batch-update-pricing.php</p>
                <p style="margin-top: 10px;"><small>Berkeley County Store - Internal Use Only</small></p>
            </div>
        </div>
    </body>

    </html>
<?php
    $htmlBody = ob_get_clean();

    // Send email using PHP mail() function (uses local SMTP)
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@berkeleycountysc.gov\r\n";
    $headers .= "Reply-To: " . NOTIFICATION_EMAIL . "\r\n";

    $result = mail(NOTIFICATION_EMAIL, $subject, $htmlBody, $headers);

    if ($result) {
        echo "✉️  Email notification sent to " . NOTIFICATION_EMAIL . "\n";
    } else {
        echo "⚠️  Failed to send email notification\n";
    }

    return $result;
}

// Find product_id by product code
function getProductIdByCode($conn, $productCode)
{
    $sql = "SELECT product_id FROM products_new WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['product_id'];
    }

    return null;
}

// Check if price record exists
function priceExists($conn, $productId, $sizeId, $vendorId)
{
    $sql = "SELECT price_id, price FROM prices WHERE product_id = ? AND size_id = ? AND vendor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $productId, $sizeId, $vendorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row;
    }

    return null;
}

// Update existing price
function updatePrice($conn, $priceId, $newPrice, $dryRun = false)
{
    if ($dryRun) {
        return true; // Simulate success in dry-run mode
    }

    $sql = "UPDATE prices SET price = ?, updated_at = NOW() WHERE price_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $newPrice, $priceId);
    return $stmt->execute();
}

// Insert new price
function insertPrice($conn, $productId, $sizeId, $vendorId, $price, $dryRun = false)
{
    if ($dryRun) {
        return true; // Simulate success in dry-run mode
    }

    $sql = "INSERT INTO prices (product_id, size_id, vendor_id, price, isActive) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $productId, $sizeId, $vendorId, $price);
    return $stmt->execute();
}

// Process a single JSON file
function processJsonFile($conn, $filePath, $sizeMap, &$stats, $dryRun = false)
{
    $jsonContent = file_get_contents($filePath);
    $data = json_decode($jsonContent, true);

    if (!$data || !isset($data['result']['success']) || !$data['result']['success']) {
        $stats['skipped_files']++;
        echo "⚠️  Skipped: " . basename($filePath) . " (no valid data)\n";
        return;
    }

    $productCode = $data['result']['data']['page_code'] ?? $data['input_code'];
    $productName = $data['result']['data']['product_name'] ?? 'Unknown';
    $prices = $data['result']['data']['prices'] ?? [];

    // Get product_id from database
    $productId = getProductIdByCode($conn, $productCode);

    if (!$productId) {
        $stats['products_not_found']++;
        echo "❌ Product not found: $productCode ($productName)\n";
        return;
    }

    echo "📦 Processing: $productCode - $productName (product_id: $productId)\n";

    // Process each price/size
    foreach ($prices as $priceData) {
        $sizeName = strtoupper(trim($priceData['size']));
        $price = floatval($priceData['price']);

        // Map size name to size_id
        if (!isset($sizeMap[$sizeName])) {
            $stats['unmapped_sizes']++;
            echo "   ⚠️  Unknown size: $sizeName (skipped)\n";
            continue;
        }

        $sizeId = $sizeMap[$sizeName];

        // Check if price already exists
        $existing = priceExists($conn, $productId, $sizeId, VENDOR_ID);

        if ($existing) {
            // Update existing price if different
            if (abs($existing['price'] - $price) > 0.01) {
                if (updatePrice($conn, $existing['price_id'], $price, $dryRun)) {
                    $stats['updated']++;
                    $prefix = $dryRun ? "[DRY RUN] " : "";
                    $oldPrice = $existing['price'];
                    echo "   {$prefix}✏️  Updated: Size $sizeName (\${$oldPrice} → \$$price)\n";
                } else {
                    $stats['errors']++;
                    echo "   ❌ Failed to update: Size $sizeName\n";
                }
            } else {
                $stats['unchanged']++;
                echo "   ✓ Unchanged: Size $sizeName (\$$price)\n";
            }
        } else {
            // Insert new price
            if (insertPrice($conn, $productId, $sizeId, VENDOR_ID, $price, $dryRun)) {
                $stats['inserted']++;
                $prefix = $dryRun ? "[DRY RUN] " : "";
                echo "   {$prefix}➕ Inserted: Size $sizeName (\$$price)\n";
            } else {
                $stats['errors']++;
                echo "   ❌ Failed to insert: Size $sizeName\n";
            }
        }
    }

    $stats['processed_files']++;
    echo "\n";
}

// Main execution
echo "\n";
echo str_repeat("=", 80) . "\n";
echo "  BATCH PRICING UPDATE - Company Casuals (Vendor ID: " . VENDOR_ID . ")\n";
echo str_repeat("=", 80) . "\n\n";

// Check for command line arguments
if (php_sapi_name() === 'cli') {
    foreach ($argv as $arg) {
        if ($arg === '--dry-run' || $arg === '-d') {
            $dryRun = true;
        }
    }
}

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No changes will be made to the database\n\n";
}

// Establish database connection
try {
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    die("❌ ERROR: " . $e->getMessage() . "\n");
}

// Check if directory exists
if (!is_dir($jsonDirectory)) {
    die("❌ Error: Directory not found: $jsonDirectory\n");
}

// Get all JSON files
$jsonFiles = glob($jsonDirectory . '*_price_scrape_*.json');

if (empty($jsonFiles)) {
    die("❌ Error: No JSON price files found in $jsonDirectory\n");
}

echo "📂 Found " . count($jsonFiles) . " price files to process\n";
echo "📊 Building size mapping...\n";

// Build size mapping
$sizeMap = getSizeMapping($conn);
echo "   ✓ Mapped " . count($sizeMap) . " sizes\n\n";

// Statistics
$stats = [
    'processed_files' => 0,
    'skipped_files' => 0,
    'products_not_found' => 0,
    'inserted' => 0,
    'updated' => 0,
    'unchanged' => 0,
    'errors' => 0,
    'unmapped_sizes' => 0
];

// Confirmation prompt (only in CLI mode)
if (php_sapi_name() === 'cli' && !$dryRun) {
    echo "⚠️  WARNING: This will update pricing in the database!\n";
    echo "Do you want to continue? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);

    if (strtolower($confirmation) !== 'yes') {
        echo "\n❌ Operation cancelled.\n";
        exit(0);
    }
    echo "\n";
}

echo str_repeat("-", 80) . "\n";
echo "Starting processing...\n";
echo str_repeat("-", 80) . "\n\n";

$startTime = microtime(true);

// Process each file
foreach ($jsonFiles as $jsonFile) {
    processJsonFile($conn, $jsonFile, $sizeMap, $stats, $dryRun);
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Summary
echo str_repeat("=", 80) . "\n";
echo $dryRun ? "DRY RUN COMPLETE\n" : "PROCESSING COMPLETE\n";
echo str_repeat("=", 80) . "\n";
echo "Files processed:        {$stats['processed_files']}\n";
echo "Files skipped:          {$stats['skipped_files']}\n";
echo "Products not found:     {$stats['products_not_found']}\n";
echo str_repeat("-", 80) . "\n";
echo "Prices inserted:        {$stats['inserted']}\n";
echo "Prices updated:         {$stats['updated']}\n";
echo "Prices unchanged:       {$stats['unchanged']}\n";
echo "Errors:                 {$stats['errors']}\n";
echo "Unmapped sizes:         {$stats['unmapped_sizes']}\n";
echo str_repeat("-", 80) . "\n";
echo "Total execution time:   {$duration} seconds\n";
echo str_repeat("=", 80) . "\n";

// Send email notification
echo "\n";
sendEmailNotification($stats, $duration, $dryRun);

// Close database connection
$conn->close();

if ($dryRun) {
    echo "\n🔍 DRY RUN COMPLETE - No changes were made to the database\n";
    echo "To apply these changes, run without --dry-run flag:\n";
    echo "   php batch-update-pricing.php\n\n";
} else {
    echo "\n✅ Done!\n\n";
}
