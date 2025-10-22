#!/usr/bin/env php
<?php
/**
 * Email Preview Test: Pending Requests Reminder
 * 
 * Purpose: Send actual emails to store@berkeleycountysc.gov for testing/validation
 *          This sends both reminder emails and exception reports to the store inbox
 *          so you can see exactly what approvers and admins will receive.
 * 
 * ⚠️ WARNING: This sends REAL EMAILS! Use only for testing/validation.
 * 
 * Usage: php test-send-emails.php
 * 
 * Author: Jon Ellwood
 * Organization: Berkeley County IT Department
 * Created: 2025-10-21
 */

// Prevent accidental web access
if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEB_ACCESS')) {
    die('This script is meant to be run from the command line only.');
}

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "  PENDING REQUESTS REMINDER - EMAIL PREVIEW TEST\n";
echo "  ⚠️  WARNING: THIS SENDS REAL EMAILS TO store@berkeleycountysc.gov\n";
echo str_repeat("=", 70) . "\n\n";

// Confirmation prompt
echo "This script will send actual test emails to store@berkeleycountysc.gov\n";
echo "for each department with pending requests.\n\n";
echo "Do you want to continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'yes') {
    echo "\nTest cancelled. No emails sent.\n";
    exit(0);
}

echo "\n";
echo str_repeat("-", 70) . "\n";
echo "Starting email send test...\n";
echo str_repeat("-", 70) . "\n\n";

// Load dependencies
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Database connection
try {
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

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

/**
 * Get departments with pending requests
 */
function getDepartmentsWithPendingRequests($conn)
{
    $deptList = array();

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
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deptList[] = $row;
        }
    }

    $stmt->close();
    return $deptList;
}

/**
 * Get pending orders for a department
 */
function getPendingOrdersByDepartment($conn, $department)
{
    $ordersList = array();

    $sql = "SELECT 
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
            ORDER BY created DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ordersList[] = $row;
        }
    }

    $stmt->close();
    return $ordersList;
}

/**
 * Generate HTML table for pending orders (SAME AS PRODUCTION)
 */
function generateOrdersTable($orders, $departmentName, $depHead, $depAssist, $depAssetMgr)
{
    $totalOrders = count($orders);
    $totalAmount = array_sum(array_column($orders, 'line_item_total'));

    ob_start();
?>
    <!DOCTYPE html>
    <html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office'>

    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width,initial-scale=1'>
        <meta name='x-apple-disable-message-reformatting'>
        <title>Pending Requests Reminder</title>
        <style>
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 5px;
            }

            h2 {
                color: #003366;
                border-bottom: 2px solid #003366;
                padding-bottom: 10px;
            }

            .info-box {
                background-color: #f0f8ff;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #003366;
            }

            .info-box p {
                margin: 5px 0;
                color: #333;
            }

            .summary {
                background-color: #fff3cd;
                padding: 10px;
                margin: 15px 0;
                border-radius: 3px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            th {
                background-color: #003366;
                color: white;
                padding: 12px 8px;
                text-align: left;
                font-weight: bold;
            }

            td {
                border: 1px solid #dddddd;
                padding: 10px 8px;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tr:hover {
                background-color: #f0f8ff;
            }

            .button {
                display: inline-block;
                padding: 12px 24px;
                margin: 20px 0;
                background-color: #003366;
                color: white !important;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }

            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>

    <body>
        <div class='container'>
            <h2>&#128203; Pending Requests for <?php echo htmlspecialchars($departmentName); ?></h2>

            <div class='summary'>
                <strong>Summary:</strong> You have <?php echo $totalOrders; ?> pending request<?php echo $totalOrders !== 1 ? 's' : ''; ?>
                totaling $<?php echo number_format($totalAmount, 2); ?> awaiting approval.
            </div>

            <div class='info-box'>
                <p><strong>Department Head:</strong> <?php echo htmlspecialchars($depHead ?? 'Not Assigned'); ?></p>
                <p><strong>Department Assistant:</strong> <?php echo htmlspecialchars($depAssist ?? 'Not Assigned'); ?></p>
                <p><strong>Asset Manager:</strong> <?php echo htmlspecialchars($depAssetMgr ?? 'Not Assigned'); ?></p>
            </div>

            <table role='presentation'>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Requested For</th>
                        <th>Date</th>
                        <th>Qty</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['requested_for']); ?></td>
                            <td><?php echo date('m/d/Y', strtotime($order['created'])); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_code']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['size_name']); ?></td>
                            <td>$<?php echo number_format($order['line_item_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='7' style='text-align: right; font-weight: bold;'>Total:</td>
                        <td style='font-weight: bold;'>$<?php echo number_format($totalAmount, 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <center>
                <a href='https://store.berkeleycountysc.gov/storeadmin/pages/sign-in.php' class='button'>
                    Review & Approve Requests
                </a>
            </center>

            <div class='footer'>
                <p><strong>⚠️ Important:</strong> The store can only be accessed from a Berkeley County network-connected device.</p>
                <p>This is an automated reminder sent every Friday morning. If you have questions, please contact the IT Department.</p>
                <p style='margin-top: 10px;'><small>Berkeley County Store - Internal Use Only</small></p>
            </div>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

/**
 * Generate exception report HTML (SAME AS PRODUCTION)
 */
function generateExceptionReport($deptInfo, $orders)
{
    $departmentName = $deptInfo['dep_name'];
    $departmentId = $deptInfo['department'];
    $totalOrders = count($orders);
    $totalAmount = array_sum(array_column($orders, 'line_item_total'));

    ob_start();
?>
    <!DOCTYPE html>
    <html lang='en'>

    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width,initial-scale=1'>
        <title>Exception Report</title>
        <style>
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 5px;
            }

            .alert {
                background-color: #dc3545;
                color: white;
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
                font-weight: bold;
            }

            h2 {
                color: #dc3545;
                border-bottom: 2px solid #dc3545;
                padding-bottom: 10px;
            }

            .warning-box {
                background-color: #fff3cd;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #ffc107;
            }

            .info-box {
                background-color: #f0f8ff;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #17a2b8;
            }

            .info-box p {
                margin: 5px 0;
                color: #333;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            th {
                background-color: #dc3545;
                color: white;
                padding: 12px 8px;
                text-align: left;
                font-weight: bold;
            }

            td {
                border: 1px solid #dddddd;
                padding: 10px 8px;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>

    <body>
        <div class='container'>
            <div class='alert'>
                ⚠️ EXCEPTION REPORT - NO EMAIL RECIPIENTS AVAILABLE
            </div>

            <h2>Missing Recipient Information for <?php echo htmlspecialchars($departmentName); ?></h2>

            <div class='warning-box'>
                <p><strong>Issue:</strong> Department has <?php echo $totalOrders; ?> pending request<?php echo $totalOrders !== 1 ? 's' : ''; ?>
                    totaling $<?php echo number_format($totalAmount, 2); ?>, but no valid email recipients could be determined.</p>
                <p><strong>Action Required:</strong> Please assign appropriate approvers to this department or manually process these requests.</p>
            </div>

            <div class='info-box'>
                <p><strong>Department ID:</strong> <?php echo htmlspecialchars($departmentId); ?></p>
                <p><strong>Department Name:</strong> <?php echo htmlspecialchars($departmentName); ?></p>
                <p><strong>Department Head:</strong> <?php echo htmlspecialchars($deptInfo['dep_head_empName'] ?? 'NOT ASSIGNED'); ?></p>
                <p><strong>Department Assistant:</strong> <?php echo htmlspecialchars($deptInfo['dep_assist_empName'] ?? 'NOT ASSIGNED'); ?></p>
                <p><strong>Asset Manager:</strong> <?php echo htmlspecialchars($deptInfo['dep_asset_mgr_empName'] ?? 'NOT ASSIGNED'); ?></p>
            </div>

            <h3>Pending Requests Details</h3>
            <table role='presentation'>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Requested For</th>
                        <th>Date</th>
                        <th>Qty</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['requested_for']); ?></td>
                            <td><?php echo date('m/d/Y', strtotime($order['created'])); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_code']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['size_name']); ?></td>
                            <td>$<?php echo number_format($order['line_item_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='7' style='text-align: right; font-weight: bold;'>Total:</td>
                        <td style='font-weight: bold;'>$<?php echo number_format($totalAmount, 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class='warning-box'>
                <p><strong>Recommended Actions:</strong></p>
                <ol>
                    <li>Verify department approver assignments in the database (dep_ref table)</li>
                    <li>Ensure approver names follow format: "FirstName LastName"</li>
                    <li>Check that approvers have valid Berkeley County email addresses</li>
                    <li>Manually notify department of pending requests if urgent</li>
                    <li>Update department approver information to prevent future exceptions</li>
                </ol>
            </div>

            <div class='footer'>
                <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Script:</strong> test-send-emails.php (TEST MODE)</p>
                <p style='margin-top: 10px;'><small>This is an automated exception report from the Berkeley County Store system.</small></p>
            </div>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// Main execution
try {
    $startTime = microtime(true);

    // Get departments
    $departments = getDepartmentsWithPendingRequests($conn);
    $deptCount = count($departments);

    echo "Found $deptCount department(s) with pending requests\n\n";

    if ($deptCount === 0) {
        echo "No pending requests found. Nothing to send.\n";
        $conn->close();
        exit(0);
    }

    $remindersSent = 0;
    $exceptionsSent = 0;
    $failed = 0;

    // Process each department
    foreach ($departments as $index => $dept) {
        $deptNum = $index + 1;
        echo "[$deptNum/$deptCount] Processing: {$dept['dep_name']}\n";

        // Get orders
        $orders = getPendingOrdersByDepartment($conn, $dept['department']);
        $orderCount = count($orders);

        // Check if department has recipients
        $depHeadEmail = makeEmailAddress($dept['dep_head_empName']);
        $depAssistEmail = makeEmailAddress($dept['dep_assist_empName']);
        $depAssetMgrEmail = makeEmailAddress($dept['dep_asset_mgr_empName']);

        $hasRecipients = ($depHeadEmail || $depAssistEmail || $depAssetMgrEmail);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = "10.50.10.10";
            $mail->Port = 25;
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;

            // ALL EMAILS GO TO store@berkeleycountysc.gov FOR TESTING
            $mail->addAddress('store@berkeleycountysc.gov', 'Store Admin (TEST)');
            $mail->isHTML(true);

            if ($hasRecipients) {
                // Send REMINDER EMAIL to store inbox
                $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store (TEST)');
                $mail->Subject = "[TEST] Pending Requests Reminder - {$dept['dep_name']} ($orderCount items)";
                $mail->Body = generateOrdersTable(
                    $orders,
                    $dept['dep_name'],
                    $dept['dep_head_empName'],
                    $dept['dep_assist_empName'],
                    $dept['dep_asset_mgr_empName']
                );

                // Add note at top of email
                $testNote = "<div style='background-color: #ffc107; padding: 10px; margin: 10px 0; border: 2px solid #ff9800;'>";
                $testNote .= "<strong>🧪 TEST EMAIL</strong><br>";
                $testNote .= "This would normally be sent to:<br>";
                if ($depHeadEmail) $testNote .= "• $depHeadEmail<br>";
                if ($depAssistEmail && $depAssistEmail !== $depHeadEmail) $testNote .= "• $depAssistEmail<br>";
                if ($depAssetMgrEmail && !in_array($depAssetMgrEmail, [$depHeadEmail, $depAssistEmail])) $testNote .= "• $depAssetMgrEmail<br>";
                $testNote .= "</div>";

                $mail->Body = str_replace('<div class=\'container\'>', '<div class=\'container\'>' . $testNote, $mail->Body);

                $mail->send();
                echo "  ✓ REMINDER email sent ($orderCount orders)\n";
                echo "    Would go to: ";
                $recipients = array_filter([$depHeadEmail, $depAssistEmail, $depAssetMgrEmail]);
                echo implode(', ', array_unique($recipients)) . "\n";
                $remindersSent++;
            } else {
                // Send EXCEPTION REPORT to store inbox
                $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store - EXCEPTION (TEST)');
                $mail->Subject = "[TEST] ⚠️ EXCEPTION: No Recipients - {$dept['dep_name']}";
                $mail->Body = generateExceptionReport($dept, $orders);

                // Add test note
                $testNote = "<div style='background-color: #ffc107; padding: 10px; margin: 10px 0; border: 2px solid #ff9800;'>";
                $testNote .= "<strong>🧪 TEST EMAIL</strong><br>";
                $testNote .= "In production, this would be sent to: store@berkeleycountysc.gov<br>";
                $testNote .= "Reason: No valid recipients found for this department";
                $testNote .= "</div>";

                $mail->Body = str_replace('<div class=\'container\'>', '<div class=\'container\'>' . $testNote, $mail->Body);

                $mail->send();
                echo "  ⚠️  EXCEPTION report sent (no recipients assigned)\n";
                $exceptionsSent++;
            }

            // Small delay between emails
            sleep(1);
        } catch (Exception $e) {
            echo "  ✗ FAILED to send email: {$e->getMessage()}\n";
            $failed++;
        }

        echo "\n";
    }

    // Summary
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo str_repeat("=", 70) . "\n";
    echo "TEST COMPLETE\n";
    echo str_repeat("=", 70) . "\n";
    echo "Departments processed: $deptCount\n";
    echo "Reminder emails sent: $remindersSent\n";
    echo "Exception reports sent: $exceptionsSent\n";
    echo "Failed: $failed\n";
    echo "Execution time: {$duration} seconds\n";
    echo "\n";
    echo "✓ All test emails sent to: store@berkeleycountysc.gov\n";
    echo "  Check your inbox to review the emails!\n";
    echo str_repeat("=", 70) . "\n";
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

exit(0);
