<?php
/*
Created: 2026/02/11
Updated: 2026/02/11 - Support for multiple items and validated employee data
Purpose: Process item request form submissions and send notification email
Organization: Berkeley County IT Department
*/

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: request-items.php');
    exit();
}

// Get employee data
$emp_number = isset($_POST['emp_number']) ? trim($_POST['emp_number']) : '';
$emp_name = isset($_POST['emp_name']) ? trim($_POST['emp_name']) : '';
$emp_email = isset($_POST['emp_email']) ? trim($_POST['emp_email']) : '';
$dept_name = isset($_POST['dept_name']) ? trim($_POST['dept_name']) : '';
$dept_number = isset($_POST['dept_number']) ? trim($_POST['dept_number']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$additional_notes = isset($_POST['additional_notes']) ? trim($_POST['additional_notes']) : '';

// Get items array
$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];

// Validate required fields
$errors = [];
if (empty($emp_number)) $errors[] = 'Employee number is required';
if (empty($emp_name)) $errors[] = 'Employee name is required';
if (empty($emp_email) || !filter_var($emp_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address is required';
if (empty($dept_name)) $errors[] = 'Department is required';
if (empty($reason)) $errors[] = 'Reason for request is required';
if (empty($items)) $errors[] = 'At least one item is required';

// Validate each item
foreach ($items as $index => $item) {
    $itemNum = $index + 1;
    if (empty($item['category'])) $errors[] = "Item #{$itemNum}: Category is required";
    if (empty($item['product_url']) || !filter_var($item['product_url'], FILTER_VALIDATE_URL)) {
        $errors[] = "Item #{$itemNum}: Valid product URL is required";
    }
    if (empty($item['name'])) $errors[] = "Item #{$itemNum}: Item name is required";
    if (empty($item['priority'])) $errors[] = "Item #{$itemNum}: Priority is required";
}

// If there are errors, redirect back
if (!empty($errors)) {
    session_start();
    $_SESSION['request_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: request-items.php?error=validation');
    exit();
}

// Save to database
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

if ($conn->connect_error) {
    header('Location: request-items.php?error=database');
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert main request
    $stmt = $conn->prepare("INSERT INTO item_requests 
        (emp_number, emp_name, emp_email, dept_name, dept_number, reason, additional_notes, request_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')");

    $stmt->bind_param("sssssss", $emp_number, $emp_name, $emp_email, $dept_name, $dept_number, $reason, $additional_notes);
    $stmt->execute();
    $request_id = $stmt->insert_id;
    $stmt->close();

    // Insert each item
    $stmt = $conn->prepare("INSERT INTO request_items 
        (request_id, item_category, product_url, item_name, item_details, quantity_estimate, priority, item_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

    foreach ($items as $item) {
        $quantity = !empty($item['quantity']) ? intval($item['quantity']) : 1;
        $details = isset($item['details']) ? trim($item['details']) : '';

        $stmt->bind_param(
            "issssis",
            $request_id,
            $item['category'],
            $item['product_url'],
            $item['name'],
            $details,
            $quantity,
            $item['priority']
        );
        $stmt->execute();
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    header('Location: request-items.php?error=database');
    exit();
}

$conn->close();

// Send email notifications
$adminEmailSent = sendAdminNotification(
    $request_id,
    $emp_number,
    $emp_name,
    $emp_email,
    $dept_name,
    $dept_number,
    $items,
    $reason,
    $additional_notes
);

$employeeEmailSent = sendEmployeeConfirmation($emp_name, $emp_email, $request_id, $items);

if (!$adminEmailSent || !$employeeEmailSent) {
    error_log('[Item Requests] Email dispatch issue for request #' . $request_id . ' (admin: ' . ($adminEmailSent ? 'ok' : 'failed') . ', employee: ' . ($employeeEmailSent ? 'ok' : 'failed') . ')');
}

// Redirect to success page
header('Location: request-items.php?success=1');
exit();

// Function to send email to store management
function sendAdminNotification($request_id, $emp_number, $emp_name, $emp_email, $dept_name, $dept_number, $items, $reason, $additional_notes)
{
    $to = "store@berkeleycountysc.gov";
    $subject = "New Item Request #" . $request_id . " - " . count($items) . " Item(s) - " . $emp_name;

    // Build items HTML
    $itemsHtml = '';
    $itemNumber = 1;
    foreach ($items as $item) {
        $priority_class = ['high' => 'danger', 'medium' => 'warning', 'low' => 'success'][$item['priority']] ?? 'secondary';
        $priority_display = [
            'low' => 'Low - Nice to have',
            'medium' => 'Medium - Needed soon',
            'high' => 'High - Urgent'
        ][$item['priority']] ?? ucfirst($item['priority']);

        $itemsHtml .= "
        <div class='item-section'>
            <h3 style='color: #005677; margin-bottom: 10px;'>Item #{$itemNumber}</h3>
            <div class='field'>
                <span class='field-label'>Category:</span>
                <span class='field-value'>" . htmlspecialchars(ucfirst($item['category'])) . "</span>
            </div>
            <div class='field'>
                <span class='field-label'>Item Name:</span>
                <span class='field-value'>" . htmlspecialchars($item['name']) . "</span>
            </div>
            <div class='field'>
                <span class='field-label'>Product Link:</span>
                <div class='field-value'><a href='" . htmlspecialchars($item['product_url']) . "' target='_blank'>" . htmlspecialchars($item['product_url']) . "</a></div>
            </div>";

        if (!empty($item['details'])) {
            $itemsHtml .= "
            <div class='field'>
                <span class='field-label'>Details:</span>
                <div class='field-value'>" . nl2br(htmlspecialchars($item['details'])) . "</div>
            </div>";
        }

        $itemsHtml .= "
            <div class='field'>
                <span class='field-label'>Quantity:</span>
                <span class='field-value'>" . (!empty($item['quantity']) ? htmlspecialchars($item['quantity']) : '1') . "</span>
            </div>
            <div class='field'>
                <span class='field-label'>Priority:</span>
                <span class='field-value priority-{$item['priority']}'>" . htmlspecialchars($priority_display) . "</span>
            </div>
        </div>";

        $itemNumber++;
    }

    $message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background-color: #005677; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .section { margin-bottom: 20px; padding: 15px; background-color: white; border-left: 4px solid #005677; }
        .item-section { margin-bottom: 20px; padding: 15px; background-color: #f0f8ff; border-left: 4px solid #789b48; }
        .section-title { font-weight: bold; color: #005677; margin-bottom: 10px; font-size: 16px; }
        .field { margin-bottom: 10px; }
        .field-label { font-weight: bold; color: #666; }
        .field-value { color: #333; margin-left: 10px; }
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #f57f43; font-weight: bold; }
        .priority-low { color: #789b48; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        a { color: #005677; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Item Request - #" . $request_id . "</h2>
            <p style='margin: 5px 0;'>" . count($items) . " Item(s) Requested</p>
        </div>
        
        <div class='content'>
            <div class='section'>
                <div class='section-title'>Employee Information</div>
                <div class='field'>
                    <span class='field-label'>Name:</span>
                    <span class='field-value'>" . htmlspecialchars($emp_name) . "</span>
                </div>
                <div class='field'>
                    <span class='field-label'>Employee #:</span>
                    <span class='field-value'>" . htmlspecialchars($emp_number) . "</span>
                </div>
                <div class='field'>
                    <span class='field-label'>Email:</span>
                    <span class='field-value'>" . htmlspecialchars($emp_email) . "</span>
                </div>
                <div class='field'>
                    <span class='field-label'>Department:</span>
                    <span class='field-value'>" . htmlspecialchars($dept_name) . " (#" . htmlspecialchars($dept_number) . ")</span>
                </div>
            </div>
            
            <div class='section'>
                <div class='section-title'>Requested Items</div>
                {$itemsHtml}
            </div>
            
            <div class='section'>
                <div class='section-title'>Request Information</div>
                <div class='field'>
                    <span class='field-label'>Reason for Request:</span>
                    <div class='field-value' style='margin-top: 5px;'>" . nl2br(htmlspecialchars($reason)) . "</div>
                </div>";

    if (!empty($additional_notes)) {
        $message .= "
                <div class='field'>
                    <span class='field-label'>Additional Notes:</span>
                    <div class='field-value' style='margin-top: 5px;'>" . nl2br(htmlspecialchars($additional_notes)) . "</div>
                </div>";
    }

    $message .= "
            </div>
        </div>
        
        <div class='footer'>
            <p>Request submitted on " . date('F j, Y \a\t g:i A') . "</p>
            <p>Berkeley County Employee Store - Item Request System</p>
            <p><a href='https://store.berkeleycountysc.gov/manage-item-requests.php'>View in Admin Dashboard</a></p>
        </div>
    </div>
</body>
</html>";

    $fromAddress = "Berkeley County Store <store@berkeleycountysc.gov>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $fromAddress . "\r\n";
    $headers .= "Reply-To: " . $emp_email . "\r\n";

    $sent = mail($to, $subject, $message, $headers);
    if (!$sent) {
        error_log('[Item Requests] Failed to send admin notification for request #' . $request_id);
    }

    return $sent;
}

// Function to send confirmation to employee
function sendEmployeeConfirmation($emp_name, $emp_email, $request_id, $items)
{
    $subject = "Item Request Confirmed - Request #" . $request_id;

    // Build items list
    $itemsList = '';
    foreach ($items as $index => $item) {
        $itemsList .= "<li>" . htmlspecialchars($item['name']) . " (" . htmlspecialchars(ucfirst($item['category'])) . ")</li>";
    }

    $message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #005677; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .message-box { background-color: white; padding: 20px; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .highlight { background-color: #e6f4f9; padding: 10px; border-left: 4px solid #005677; margin: 15px 0; }
        ul { margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Item Request Confirmed</h2>
            <p>Request #" . $request_id . "</p>
        </div>
        
        <div class='content'>
            <div class='message-box'>
                <p>Dear " . htmlspecialchars($emp_name) . ",</p>
                
                <p>Thank you for submitting your item request. We have received your request for the following " . count($items) . " item(s):</p>
                
                <div class='highlight'>
                    <ul>{$itemsList}</ul>
                </div>
                
                <p>Your request has been forwarded to the store management team for review. You can expect to hear back from us within 5-7 business days.</p>
                
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>Our team will review your request and product links</li>
                    <li>We'll evaluate availability, pricing, and vendor options</li>
                    <li>You'll receive an email with the decision for each item</li>
                    <li>If approved, we'll notify you when items become available in the store</li>
                </ul>
                
                <p><strong>Your Request ID:</strong> #" . $request_id . "</p>
                <p style='font-size: 12px; color: #666;'>Please reference this number if you need to contact us about your request.</p>
                
                <p>If you have any questions, please contact us at <a href='mailto:store@berkeleycountysc.gov'>store@berkeleycountysc.gov</a></p>
                
                <p>Thank you for using the Berkeley County Employee Store!</p>
            </div>
        </div>
        
        <div class='footer'>
            <p>Berkeley County Employee Store</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Berkeley County Store <store@berkeleycountysc.gov>" . "\r\n";
    $headers .= "Reply-To: store@berkeleycountysc.gov\r\n";

    $sent = mail($emp_email, $subject, $message, $headers);
    if (!$sent) {
        error_log('[Item Requests] Failed to send confirmation to ' . $emp_email . ' for request #' . $request_id);
    }

    return $sent;
}
