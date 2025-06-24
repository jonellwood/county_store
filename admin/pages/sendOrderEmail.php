<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}

$order_id = $_REQUEST['id'];

$sql = "SELECT ord.order_id, ord.customer_id, ord.created, ord.grand_total, ord.product_id, ord.quantity, ord.status, ord.size_id, ord.color_id, 
ord.order_details_id, ord.line_item_total, ord.logo, ord.comment, ord.product_price, ord.product_code, 
CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
d.dep_name, p.name, p.price, p.image
FROM ord_ref as ord 
LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
LEFT JOIN emp_ref as s on s.empNumber = ord.submitted_by AND s.seperation_date IS NULL
LEFT JOIN departments d on d.dep_num = s.deptNumber 
JOIN products as p ON p.product_id = ord.product_id
WHERE ord.order_id=?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $db_id);
$db_id = $order_id;
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orderInfo = $result->fetch_assoc();
} else {
    header("Location: index.php");
};


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
    $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');
    // $mail->addAddress('kristin.tweed@berkeleycountysc.gov', 'K Rock');
    $mail->addAddress('james.troy@berkeleycountysc.gov', 'James Troy');
    // expected result. IF requested_for and submitted_by are the same email address only one email will be sent. Otherwise each will get an email.
    // $mail->addAddress($orderInfo["email"], ($orderInfo["first_name"] . " " . $orderInfo["last_name"]));
    // if ($orderInfo["email"] != $orderInfo["submitted_by_email"]) {
    //     $mail->addAddress($orderInfo["submitted_by_email"], $orderInfo["submitted_by"]);
    // }

    $mail->isHTML(true);
    $mail->Subject = 'Berkeley County Store request for ' .  $orderInfo["submitted_for"];
    $mail->Body = '<div style="background-color: whitesmoke; color: #005677; font-family: Arial, Helvetica, sans-serif;  font-size: 18px">
                <img src="https://d7e3m5n2.stackpathcdn.com/wp-content/uploads/menuLogo.png" alt="bc logo" style="width:50em"> 
                <h2>Request Confirmation</h2>
                <h4>Please keep this email for future USE</h4>            
                <p><b>Reference ID:</b> #' . $orderInfo["order_id"] . '</p>
                <p><b>Total: </b>' .  CURRENCY_SYMBOL . number_format($orderInfo["grand_total"], 2) . ' ' . CURRENCY . '</p>
                <p><b>Placed On: </b>' . $orderInfo["created"] . '</p>
                <p><b>Requested For: </b>' . $orderInfo["submitted_for"] . '</p>
                <p><b>Department ID: </b>' . $orderInfo["dep_name"] . '</p>
                <p><b>Email: </b>' . $orderInfo["submitted_for_email"] . '</p>
                <p><b>Employee Number: </b>' . $orderInfo["emp_id"] . '</p>
                <p><b>Requested By: </b>' . $orderInfo["submitted_by"] . '</p>
                </div>';
    // ob_start();
    // include('orderSuccess.php');
    // $mail->Body = ob_get_contents();
    // ob_end_clean();
    $mail->AltBody = 'Request for' . $orderInfo["first_name"] . " " . $orderInfo["last_name"] . 'to purchase' . $orderInfo['quantity'] . ' ' . $orderInfo["product_name"] . 'was submitted by' . $orderInfo["submitted_by"] . 'on' . $orderInfo["created"] . '.';

    $mail->send();
    echo 'Message has been sent - now get to work';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
