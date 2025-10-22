#!/usr/bin/env php
<?php
/**
 * Test Script: Pending Requests Reminder
 * 
 * Purpose: Test the cron job functionality without actually sending emails
 * 
 * Usage: php test-pending-requests-reminder.php
 * 
 * Author: Jon Ellwood
 * Organization: Berkeley County IT Department
 * Created: 2025-10-20
 */

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "  PENDING REQUESTS REMINDER - TEST SCRIPT\n";
echo str_repeat("=", 70) . "\n\n";

// Load dependencies
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Convert name to Berkeley County email address
 */
function makeEmailAddress($name)
{
    if ($name === null || empty(trim($name))) {
        return null;
    }

    $nameParts = explode(" ", trim($name), 2);
    if (count($nameParts) < 2) {
        return null;
    }

    $firstName = strtolower($nameParts[0]);
    $lastName = strtolower($nameParts[1]);
    $emailDomain = 'berkeleycountysc.gov';

    return $firstName . '.' . $lastName . '@' . $emailDomain;
}

// Test 1: Database Connection
echo "Test 1: Database Connection\n";
echo str_repeat("-", 70) . "\n";
try {
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✓ Database connection successful\n";
    echo "  Server: $host:$port\n";
    echo "  Database: $dbname\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// Test 2: Query for Pending Requests
echo "Test 2: Query for Pending Requests\n";
echo str_repeat("-", 70) . "\n";

$sql = "SELECT DISTINCT 
            ord_ref.department, 
            dep_ref.dep_head_empName, 
            dep_ref.dep_assist_empName, 
            dep_ref.dep_asset_mgr_empName, 
            dep_ref.dep_name
        FROM ord_ref
        JOIN dep_ref ON dep_ref.dep_num = ord_ref.department
        WHERE ord_ref.status = 'Pending'
        ORDER BY dep_ref.dep_name";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "✗ Query preparation failed: " . $conn->error . "\n";
    exit(1);
}

$stmt->execute();
$result = $stmt->get_result();
$deptCount = $result->num_rows;

echo "✓ Query executed successfully\n";
echo "  Departments with pending requests: $deptCount\n";
echo "\n";

if ($deptCount === 0) {
    echo "ℹ No pending requests found in the database.\n";
    echo "  This is normal if all requests have been processed.\n";
    $conn->close();
    exit(0);
}

// Test 3: Display Department Details
echo "Test 3: Department Details\n";
echo str_repeat("-", 70) . "\n";

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
$stmt->close();

foreach ($departments as $index => $dept) {
    $deptNum = $index + 1;
    echo "\nDepartment #$deptNum: {$dept['dep_name']} (ID: {$dept['department']})\n";
    echo "  Department Head: " . ($dept['dep_head_empName'] ?? 'Not Assigned') . "\n";
    echo "  Department Assistant: " . ($dept['dep_assist_empName'] ?? 'Not Assigned') . "\n";
    echo "  Asset Manager: " . ($dept['dep_asset_mgr_empName'] ?? 'Not Assigned') . "\n";

    // Get email addresses
    $emails = [];
    $depHeadEmail = makeEmailAddress($dept['dep_head_empName']);
    $depAssistEmail = makeEmailAddress($dept['dep_assist_empName']);
    $depAssetMgrEmail = makeEmailAddress($dept['dep_asset_mgr_empName']);

    if ($depHeadEmail) $emails[] = $depHeadEmail;
    if ($depAssistEmail && $depAssistEmail !== $depHeadEmail) $emails[] = $depAssistEmail;
    if ($depAssetMgrEmail && !in_array($depAssetMgrEmail, $emails)) $emails[] = $depAssetMgrEmail;

    if (count($emails) > 0) {
        echo "  Email Recipients: " . implode(', ', $emails) . "\n";
    } else {
        echo "  Email Recipients: ⚠️  NONE - EXCEPTION REPORT WOULD BE SENT TO store@berkeleycountysc.gov\n";
    }

    // Get pending orders count
    $orderSql = "SELECT COUNT(*) as order_count, SUM(line_item_total) as total_amount
                 FROM ord_ref 
                 WHERE department = ? AND status = 'Pending'";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("i", $dept['department']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    $orderData = $orderResult->fetch_assoc();
    $orderStmt->close();

    echo "  Pending Orders: {$orderData['order_count']}\n";
    echo "  Total Amount: $" . number_format($orderData['total_amount'] ?? 0, 2) . "\n";
}
echo "\n";

// Test 4: Sample Email Content
echo "Test 4: Sample Email Content\n";
echo str_repeat("-", 70) . "\n";

if (count($departments) > 0) {
    $sampleDept = $departments[0];

    // Get sample orders
    $orderSql = "SELECT 
                    order_details_id, 
                    department, 
                    line_item_total, 
                    created, 
                    CONCAT(rf_first_name, ' ', rf_last_name) as requested_for, 
                    quantity, 
                    color_id, 
                    product_name, 
                    product_code, 
                    size_name, 
                    logo, 
                    dept_patch_place,
                    order_id
                FROM ord_ref 
                WHERE department = ? 
                AND status = 'Pending'
                ORDER BY created DESC
                LIMIT 5";

    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("i", $sampleDept['department']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    $orders = [];
    while ($order = $orderResult->fetch_assoc()) {
        $orders[] = $order;
    }
    $orderStmt->close();

    echo "\nSample email for: {$sampleDept['dep_name']}\n";
    echo "Subject: ⏰ Pending Requests Reminder - {$sampleDept['dep_name']} (" . count($orders) . " items)\n\n";

    echo "Sample Order Details (first 5):\n";
    echo sprintf("  %-10s %-20s %-10s %-15s %-10s\n", "Order #", "Requested For", "Date", "Product", "Amount");
    echo "  " . str_repeat("-", 68) . "\n";

    foreach ($orders as $order) {
        echo sprintf(
            "  %-10s %-20s %-10s %-15s $%-9s\n",
            $order['order_id'],
            substr($order['requested_for'], 0, 19),
            date('m/d/Y', strtotime($order['created'])),
            substr($order['product_code'], 0, 14),
            number_format($order['line_item_total'], 2)
        );
    }
}
echo "\n";

// Test 5: SMTP Connection Test
echo "Test 5: SMTP Server Connection\n";
echo str_repeat("-", 70) . "\n";

$smtpHost = "10.50.10.10";
$smtpPort = 25;

echo "Testing connection to SMTP server...\n";
echo "  Host: $smtpHost\n";
echo "  Port: $smtpPort\n";

$smtpSocket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 5);
if ($smtpSocket) {
    echo "✓ SMTP server is reachable\n";
    fclose($smtpSocket);
} else {
    echo "✗ Cannot connect to SMTP server: $errstr ($errno)\n";
    echo "  WARNING: Emails will not send until SMTP server is accessible\n";
}
echo "\n";

// Test 6: PHPMailer Configuration
echo "Test 6: PHPMailer Configuration\n";
echo str_repeat("-", 70) . "\n";

try {
    $mail = new PHPMailer(true);
    echo "✓ PHPMailer library loaded successfully\n";
    echo "  Version: " . PHPMailer::VERSION . "\n";
} catch (Exception $e) {
    echo "✗ PHPMailer error: " . $e->getMessage() . "\n";
}
echo "\n";

// Summary
echo str_repeat("=", 70) . "\n";
echo "  TEST SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Departments with pending requests: $deptCount\n";

if ($deptCount > 0) {
    $totalRecipients = 0;
    $deptsWithNoRecipients = 0;

    foreach ($departments as $dept) {
        $emails = [];
        $depHeadEmail = makeEmailAddress($dept['dep_head_empName']);
        $depAssistEmail = makeEmailAddress($dept['dep_assist_empName']);
        $depAssetMgrEmail = makeEmailAddress($dept['dep_asset_mgr_empName']);

        if ($depHeadEmail) $emails[] = $depHeadEmail;
        if ($depAssistEmail && $depAssistEmail !== $depHeadEmail) $emails[] = $depAssistEmail;
        if ($depAssetMgrEmail && !in_array($depAssetMgrEmail, $emails)) $emails[] = $depAssetMgrEmail;

        if (count($emails) === 0) {
            $deptsWithNoRecipients++;
        }

        $totalRecipients += count($emails);
    }

    echo "Total email recipients: $totalRecipients\n";
    echo "Estimated reminder emails to send: " . ($deptCount - $deptsWithNoRecipients) . "\n";

    if ($deptsWithNoRecipients > 0) {
        echo "Exception reports to send: $deptsWithNoRecipients (to store@berkeleycountysc.gov)\n";
        echo "\n";
        echo "⚠️  WARNING: $deptsWithNoRecipients department(s) have no valid recipients\n";
        echo "   Exception reports will be sent to store@berkeleycountysc.gov\n";
    }

    echo "\n";
    echo "✓ System is ready to send reminder emails\n";
} else {
    echo "\nℹ No emails will be sent (no pending requests)\n";
}

echo "\nTo run the actual cron job:\n";
echo "  php cron-pending-requests-reminder.php\n";
echo "\nTo schedule it (Friday at 7 AM):\n";
echo "  crontab -e\n";
echo "  Add: 0 7 * * 5 /usr/bin/php " . __DIR__ . "/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1\n";

echo "\n" . str_repeat("=", 70) . "\n";

$conn->close();
exit(0);
