<?php

session_start();
include_once 'DBConn.php';

function convertToBerkEmail($string)
{
    // Replace the space between names with a dot
    $string = preg_replace('/\s+/', '.', $string);
    // Append the email domain to the end of the string
    $string .= "@berkeleycountysc.gov";
    return $string;
}

$order_details_id = $_GET['order_details_id']; // get sent via function
$submitted_by = $_SESSION['empNumber'];

$sql = "SELECT order_details_id, order_id, size_name as size_id, color_id, product_name, requested_by_name as req_by, 
CONCAT(rf_first_name,' ', rf_last_name) as req_for
FROM uniform_orders.ord_ref 
WHERE order_details_id = $order_details_id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orderInfo = $result->fetch_assoc();
} else {
    echo "Order not found";
};

$db_size_id = $orderInfo['size_id'];
$db_color_id = $orderInfo['color_id'];
$db_product_name = $orderInfo['product_name'];
$db_uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(8));
$db_order_details_id = $order_details_id;
$db_comment = 'Could Not Order as Item is Backordered. Item ' . 'in color ' . $orderInfo['color_id'] . ' ' . 'in size ' . $orderInfo['size_id'];
$db_submitted_by = $submitted_by;
$db_order_id = $orderInfo['order_id'];


$commSql = "INSERT into comments(id, order_details_id, comment, submitted_by, order_id) VALUES (?,?,?,?,?)";
$commStmt = $conn->prepare($commSql);
$commStmt->bind_param("sissi", $db_uid, $db_order_details_id, $db_comment, $db_submitted_by, $db_order_id);


$commStmt->execute();

$req_email = convertToBerkEmail($orderInfo['req_by']);
// echo $req_email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Composers Autoloader
require 'vendor/autoload.php';

// init instance with true enabling exceptions
$mail = new PHPMailer(true);

// function sendEmail()
// {
try {
    // server settings
    $mail = new PHPMailer;
    $mail->IsSMTP();
    $mail->Host = "smtp.berkeleycountysc.gov";
    $mail->Host = "10.50.10.10";
    $mail->Port = 25;
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;

    $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');
    $mail->addAddress('store@berkeleycountysc.gov', 'County Store');
    $mail->addAddress('james.troy@berkeleycountysc.gov', 'James Troy');
    $mail->addAddress($req_email);

    $mail->isHTML(true);
    $mail->Subject = 'Berkeley County Store request for ' .  $orderInfo["req_for"];
    $mail->Body = '<div style="background-color: whitesmoke; color: #005677; font-family: Arial, Helvetica, sans-serif;  font-size: 18px">
                <img src="https://d7e3m5n2.stackpathcdn.com/wp-content/uploads/menuLogo.png" alt="bc logo" style="width:50em"> 
                <h2>Request Update</h2>
                <h4>' . $db_comment . '</h4>            
                <p><b>Reference ID:</b> #' . $orderInfo["order_id"] . '</p>
                <p><b>Product Name:</b> #' . $orderInfo["product_name"] . '</p>
                <p><b>Requested For: </b>' . $orderInfo["req_for"] . '</p>
                <p><b>Requested By: </b>' . $orderInfo["req_by"] . '</p>
                </div>';
    // ob_start();
    // include('orderSuccess.php');
    // $mail->Body = ob_get_contents();
    // ob_end_clean();
    // $mail->AltBody = 'Request for' . $orderInfo["first_name"] . " " . $orderInfo["last_name"] . 'to purchase' . $orderInfo['quantity'] . ' ' . $orderInfo["product_name"] . 'was submitted by' . $orderInfo["submitted_by"] . 'on' . $orderInfo["created"] . '.';

    $mail->send();
    echo 'Message has been sent - now get to work';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}