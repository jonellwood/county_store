<?php

/**
 * Product URL Checker - Database Version
 * Checks product IDs from database for dead pricing routes
 * Sends email report to store@berkeleycountysc.gov
 * Intended to run as a daily cron job
 */

// Include database configuration
require_once __DIR__ . '/../../config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

// Email configuration
$emailTo = 'store@berkeleycountysc.gov';
$emailSubject = 'Daily Product URL Check Report - ' . date('Y-m-d');

// Base URL for product checks
$baseUrl = 'https://store.berkeleycountysc.gov/fetchProductDetails.php?id=';

// Results array
$errors = [];
$count = 0;

// Connect to database
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error . "\n");
}

// Query for active products
$sql = "SELECT product_id FROM products_new WHERE keep = 1 ORDER BY product_id";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error . "\n");
}

// Check each product
while ($row = $result->fetch_assoc()) {
    $productId = $row['product_id'];
    $count++;

    // Build URL and fetch content
    $url = $baseUrl . $productId;
    $content = @file_get_contents($url);

    if ($content === false) {
        $errors[] = [
            'id' => $productId,
            'error' => 'Failed to fetch URL'
        ];
        continue;
    }

    // Parse JSON response
    $data = json_decode($content, true);

    if ($data === null) {
        $errors[] = [
            'id' => $productId,
            'error' => 'Invalid JSON response'
        ];
        continue;
    }

    // Check for error messages in the JSON
    if (isset($data['product_message']) && $data['product_message'] === 'No product found with the given ID') {
        $errors[] = [
            'id' => $productId,
            'error' => 'No product found with the given ID'
        ];
    } elseif (isset($data['price_message']) && $data['price_message'] === 'No price data found with the given ID') {
        $errors[] = [
            'id' => $productId,
            'error' => 'No price data found with the given ID'
        ];
    }

    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second delay
}

$result->free();
$conn->close();

// Build email content
$emailBody = buildEmailReport($count, $errors);

// Send email using PHPMailer
try {
    $mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->Host = "10.50.10.10";
    $mail->Port = 25;
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;

    $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store - Automated Monitor');
    $mail->addAddress($emailTo, 'County Store');

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8'; // Enable UTF-8 for emoji support
    $mail->Subject = $emailSubject;
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags($emailBody); // Plain text version

    $mail->send();
    echo "Email report sent successfully to $emailTo\n";

    // Log to file as backup
    $logFile = __DIR__ . '/product_check_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - Checked: $count products, Errors: " . count($errors) . " - Email sent successfully\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
} catch (Exception $e) {
    echo "Failed to send email report: {$mail->ErrorInfo}\n";

    // Log failure
    $logFile = __DIR__ . '/product_check_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - Checked: $count products, Errors: " . count($errors) . " - Email FAILED: {$mail->ErrorInfo}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Build HTML email report
 */
function buildEmailReport($totalChecked, $errors)
{
    $errorCount = count($errors);
    $date = date('F j, Y \a\t g:i A');

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #286142;
            border-bottom: 3px solid #286142;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #286142;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-label {
            font-weight: 600;
            color: #495057;
        }
        .summary-value {
            color: #1a1a1a;
            font-weight: 700;
        }
        .status-good {
            color: #28a745;
        }
        .status-warning {
            color: #ffc107;
        }
        .status-error {
            color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #286142;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .no-errors {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏬 Daily Product URL Check Report</h1>
        
        <div class="summary">
            <div class="summary-item">
                <span class="summary-label">Report Date:</span>
                <span class="summary-value">$date</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Products Checked:</span>
                <span class="summary-value">$totalChecked</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Products with Errors:</span>
                <span class="summary-value status-error">$errorCount</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Status:</span>
                <span class="summary-value
HTML;

    if ($errorCount === 0) {
        $html .= ' status-good">✅ All Products Valid';
    } elseif ($errorCount <= 5) {
        $html .= ' status-warning">⚠️ Minor Issues Detected';
    } else {
        $html .= ' status-error">❌ Multiple Issues Detected';
    }

    $html .= '</span>
            </div>
        </div>';

    if ($errorCount > 0) {
        $html .= '
        <h2 style="color: #dc3545; margin-top: 30px;">⚠️ Products with Issues</h2>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Error Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($errors as $error) {
            $productId = htmlspecialchars($error['id']);
            $errorMsg = htmlspecialchars($error['error']);
            $productUrl = "https://store.berkeleycountysc.gov/product-details.php?product_id=$productId";

            $html .= "
                <tr>
                    <td><strong>$productId</strong></td>
                    <td>$errorMsg</td>
                    <td><a href='$productUrl' style='color: #286142; text-decoration: none;'>View Product →</a></td>
                </tr>";
        }

        $html .= '
            </tbody>
        </table>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107; margin-top: 20px;">
            <strong>⚠️ Action Required:</strong> Please review the products listed above and update or remove them from the active inventory.
        </div>';
    } else {
        $html .= '
        <div class="no-errors">
            ✅ Excellent! All ' . $totalChecked . ' active products are properly configured with valid pricing routes.
        </div>';
    }

    $html .= '
        <div class="footer">
            <p><strong>Berkeley County Store - Automated Product Monitoring</strong></p>
            <p>This is an automated report generated by the daily product URL checker cron job.</p>
            <p>If you have questions about this report, please contact the IT Department.</p>
        </div>
    </div>
</body>
</html>';

    return $html;
}
