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


$sql = "SELECT order_details_id, order_id, size_name as size_id, color_id, product_name, product_id, product_code, CONCAT(requested_by_name,' ', requested_by_last) as req_by, 
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

$product_id = $orderInfo['product_id'];
$unavailableColor = $orderInfo['color_id'];
$db_size_id = $orderInfo['size_id'];
$db_color_id = $orderInfo['color_id'];
$db_product_name = $orderInfo['product_name'];
$db_uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(8));
$db_order_details_id = $order_details_id;
$db_comment = 'Could not order ' . $orderInfo['product_code']  . ' in color ' . $orderInfo['color_id'] . ' as it is unavailable.';
$db_submitted_by = $submitted_by;
$db_order_id = $orderInfo['order_id'];


$commSql = "INSERT into comments(id, order_details_id, comment, submitted_by, order_id) VALUES (?,?,?,?,?)";
$commStmt = $conn->prepare($commSql);
$commStmt->bind_param("sissi", $db_uid, $db_order_details_id, $db_comment, $db_submitted_by, $db_order_id);


$commStmt->execute();

$statSql = "UPDATE uniform_orders.order_details SET status = 'Waiting on Customer' WHERE order_details_id = $order_details_id";
$statStmt = $conn->prepare($statSql);
$statStmt->execute();

$req_email = convertToBerkEmail($orderInfo['req_for']);
$req_by_email = convertToBerkEmail($orderInfo['req_by']);
$colorsList = [];
$listSql = "SELECT colors.color 
FROM uniform_orders.products_colors
JOIN colors on products_colors.color_id = colors.color_id WHERE product_id = $product_id";
$listStmt = $conn->prepare($listSql);
$listStmt->execute();
$listResult = $listStmt->get_result();
while ($listRow = $listResult->fetch_assoc()) {
    array_push($colorsList, $listRow);
}
$colors = array_column($colorsList, 'color');
// remove the color that is not available from the array of available colors
$index = array_search($unavailableColor, $colors);
if ($index !== false) {
    unset($colors[$index]);
}

// echo "<pre>";
// var_dump($unavailableColor);
// var_dump($colors);
// var_dump($req_email);
// var_dump($req_by_email);
// echo "</pre>";


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
    $mail->addEmbeddedImage('./' . 'alien-sorry.png', 'logo_p2t');

    $mail->isHTML(true);
    $mail->Subject = 'Berkeley County Store request for ' .  $orderInfo["req_for"];
    $mail->Body = '<div style="background-color: whitesmoke; color: #005677; font-family: Arial, Helvetica, sans-serif;  font-size: 18px">             
                <img src="cid:logo_p2t" alt="We are Sorry Alien Face" style="width:8em; margin-left:auto;margin-right:auto;"> 
                <h2>Well this is awkward.....</h2>
                <p>Unfortunately the ' . $orderInfo["product_name"] . ' is not available right now in ' . $unavailableColor . '. Please select another color and reply to this email with the color you would like us to order instead.</p>
                <p>Here is a list of other colors for this product: ' . implode(', ', $colors) . '.</p>
                <p><b>Reference ID:</b> #' . $orderInfo["order_id"] . '</p>
                <p><b>Product Name:</b> #' . $orderInfo["product_name"] . '</p>
                <p><b>Item Number:</b> #' . $orderInfo["product_code"] . '</p>
                <p><b>Requested For: </b>' . $orderInfo["req_for"] . '</p>
                <p><b>Requested By: </b>' . $orderInfo["req_by"] . '</p>
                </div>';

    $mail->send();
    echo 'Message has been sent.';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
