#!/usr/bin/env php
<?php
/**
 * Cron Job: Weekly Pending Requests Reminder
 * 
 * Purpose: Send email notifications to department approvers (heads, assistants, asset managers)
 *          who have pending user requests that need their attention.
 * 
 * Schedule: Every Friday morning (recommended: 7:00 AM)
 * Cron Example: 0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /path/to/logs/pending-requests-reminder.log 2>&1
 * 
 * Author: Jon Ellwood
 * Organization: Berkeley County IT Department
 * Created: 2025-10-20
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEB_ACCESS')) {
    die('This script is meant to be run from the command line only.');
}

// Set error reporting for cron job logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start timing
$startTime = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Starting Pending Requests Reminder Job\n";

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
    echo "[" . date('Y-m-d H:i:s') . "] Database connection successful\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Convert name to Berkeley County email address
 * 
 * @param string|null $name Full name (First Last)
 * @return string|null Email address or null
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

    $email = $firstName . '.' . $lastName . '@' . $emailDomain;
    return $email;
}

/**
 * Get departments with pending requests and their approvers
 * 
 * @param mysqli $conn Database connection
 * @return array Array of departments with pending requests
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
 * Get pending orders for a specific department
 * 
 * @param mysqli $conn Database connection
 * @param int $department Department number
 * @return array Array of pending orders
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
 * Generate HTML table for pending orders
 * 
 * @param array $orders Array of pending orders
 * @param string $departmentName Department name
 * @param string $depHead Department head name
 * @param string $depAssist Department assistant name
 * @param string $depAssetMgr Asset manager name
 * @return string HTML table
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
                <p><strong>&#9888; Important:</strong> The store can only be accessed from a Berkeley County network-connected device.</p>
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
 * Send exception report to store admin when no valid recipients found
 * 
 * @param array $deptInfo Department information
 * @param array $orders Pending orders
 * @return bool Success status
 */
function sendExceptionReport($deptInfo, $orders)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "10.50.10.10";
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;

        // From address
        $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store - EXCEPTION');

        // Send to store admin
        $mail->addAddress('store@berkeleycountysc.gov', 'Store Administrator');

        // Email content
        $departmentName = $deptInfo['dep_name'];
        $departmentId = $deptInfo['department'];
        $totalOrders = count($orders);
        $totalAmount = array_sum(array_column($orders, 'line_item_total'));

        $mail->isHTML(true);
        $mail->Subject = "&#9888; EXCEPTION: No Recipients for Pending Requests - " . $departmentName;

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
                    &#9888; EXCEPTION REPORT - NO EMAIL RECIPIENTS AVAILABLE
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
                    <p><strong>Script:</strong> cron-pending-requests-reminder.php</p>
                    <p style='margin-top: 10px;'><small>This is an automated exception report from the Berkeley County Store system.</small></p>
                </div>
            </div>
        </body>

        </html>
<?php
        $mail->Body = ob_get_clean();

        // Send exception report
        $mail->send();
        echo "[" . date('Y-m-d H:i:s') . "] EXCEPTION REPORT sent to store@berkeleycountysc.gov for department: $departmentName\n";
        return true;
    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] CRITICAL: Could not send exception report for {$deptInfo['dep_name']}. Error: {$mail->ErrorInfo}\n";
        return false;
    }
}

/**
 * Send email notification to department approvers
 * 
 * @param array $deptInfo Department information
 * @param array $orders Pending orders
 * @return bool Success status
 */
function sendReminderEmail($deptInfo, $orders)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "10.50.10.10";
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;

        // From address
        $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');

        // Get approver email addresses
        $depHeadEmail = makeEmailAddress($deptInfo['dep_head_empName']);
        $depAssistEmail = makeEmailAddress($deptInfo['dep_assist_empName']);
        $depAssetMgrEmail = makeEmailAddress($deptInfo['dep_asset_mgr_empName']);

        // Add recipients (avoiding duplicates)
        $recipients = array();
        if ($depHeadEmail) $recipients[$depHeadEmail] = $deptInfo['dep_head_empName'] ?? 'Department Head';
        if ($depAssistEmail && $depAssistEmail !== $depHeadEmail) {
            $recipients[$depAssistEmail] = $deptInfo['dep_assist_empName'] ?? 'Department Assistant';
        }
        if ($depAssetMgrEmail && !in_array($depAssetMgrEmail, array_keys($recipients))) {
            $recipients[$depAssetMgrEmail] = $deptInfo['dep_asset_mgr_empName'] ?? 'Asset Manager';
        }

        if (empty($recipients)) {
            echo "[" . date('Y-m-d H:i:s') . "] WARNING: No valid email addresses for department: " . $deptInfo['dep_name'] . "\n";
            echo "[" . date('Y-m-d H:i:s') . "] Sending exception report to store@berkeleycountysc.gov\n";
            sendExceptionReport($deptInfo, $orders);
            return false;
        }

        foreach ($recipients as $email => $name) {
            $mail->addAddress($email, $name);
            echo "[" . date('Y-m-d H:i:s') . "] Adding recipient: $name <$email>\n";
        }

        // Email content
        $departmentName = $deptInfo['dep_name'];
        $totalOrders = count($orders);

        $mail->isHTML(true);
        $mail->Subject = "⏰ Pending Requests Reminder - " . $departmentName . " ($totalOrders items)";
        $mail->Body = generateOrdersTable(
            $orders,
            $departmentName,
            $deptInfo['dep_head_empName'],
            $deptInfo['dep_assist_empName'],
            $deptInfo['dep_asset_mgr_empName']
        );

        // Send email
        $mail->send();
        echo "[" . date('Y-m-d H:i:s') . "] SUCCESS: Email sent to " . count($recipients) . " recipient(s) for $departmentName\n";
        return true;
    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: Could not send email for {$deptInfo['dep_name']}. Error: {$mail->ErrorInfo}\n";
        return false;
    }
}

// Main execution
try {
    // Get all departments with pending requests
    $departments = getDepartmentsWithPendingRequests($conn);
    $deptCount = count($departments);

    echo "[" . date('Y-m-d H:i:s') . "] Found $deptCount department(s) with pending requests\n";

    if ($deptCount === 0) {
        echo "[" . date('Y-m-d H:i:s') . "] No pending requests found. Nothing to send.\n";
        $conn->close();
        exit(0);
    }

    $emailsSent = 0;
    $emailsFailed = 0;
    $exceptionReports = 0;

    // Process each department
    foreach ($departments as $dept) {
        echo "[" . date('Y-m-d H:i:s') . "] Processing department: {$dept['dep_name']} (ID: {$dept['department']})\n";

        // Get pending orders for this department
        $orders = getPendingOrdersByDepartment($conn, $dept['department']);
        $orderCount = count($orders);

        echo "[" . date('Y-m-d H:i:s') . "] Found $orderCount pending order(s) for {$dept['dep_name']}\n";

        if ($orderCount > 0) {
            // Send email
            $result = sendReminderEmail($dept, $orders);
            if ($result === true) {
                $emailsSent++;
            } elseif ($result === false) {
                // Check if it was due to missing recipients (exception report sent)
                $depHeadEmail = makeEmailAddress($dept['dep_head_empName']);
                $depAssistEmail = makeEmailAddress($dept['dep_assist_empName']);
                $depAssetMgrEmail = makeEmailAddress($dept['dep_asset_mgr_empName']);

                if (!$depHeadEmail && !$depAssistEmail && !$depAssetMgrEmail) {
                    $exceptionReports++;
                } else {
                    $emailsFailed++;
                }
            }

            // Small delay between emails to avoid overwhelming SMTP server
            sleep(2);
        }
    }

    // Summary
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Job Complete\n";
    echo "Departments processed: $deptCount\n";
    echo "Emails sent successfully: $emailsSent\n";
    echo "Emails failed: $emailsFailed\n";
    echo "Exception reports sent: $exceptionReports\n";
    echo "Execution time: {$duration} seconds\n";
    echo str_repeat("=", 60) . "\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    // Clean up
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

exit(0);
