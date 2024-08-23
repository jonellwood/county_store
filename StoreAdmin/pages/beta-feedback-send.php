<?php

// include('DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
};

$feedbackType = $_POST['feedback-type'];
$feedbackText = $_POST['feedback-textarea'];
$feedbackUser = $_POST['feedback-user'];
$feedbackEmail = $_POST['feedback-user-email'];
// $feedbackType = "&#127881; YOU ARE THE BEST EVER";
// $feedbackText = "Seriously! Best Ever. I can not beleive how awesome this Beta is!!!";
// $feedbackUser = 'Jon Ellwood';
// $feedbackEmail = 'jon.ellwood@berkeleycountysc.gov';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// init instance with true enabling exceptions
$mail = new PHPMailer(true);

try {
    $mail = new PHPMailer;
    $mail->IsSMTP();
    $mail->Host = "smtp.berkeleycountysc.gov";
    $mail->Host = "10.50.10.10";
    $mail->Port = 25;
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;

    $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');
    // $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');
    $mail->addAddress('store@berkeleycountysc.gov', 'County Store');
    $mail->addAddress($feedbackEmail, $feedbackUser);

    $mail->addEmbeddedImage('./' . 'bg-lightblue.png', 'logo_p2t');
    $mail->isHTML(true);
    $mail->Subject = 'Beta Feedback submitted by: ' .  $feedbackUser;

    $mail->Body = "
    <!DOCTYPE html>
    <html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office'>
    <head>
    <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width,initial-scale=1'>
            <meta name='x-apple-disable-message-reformatting'>
            <title></title>
            <!--[if mso]>
            <noscript>
                <xml>
                <o:OfficeDocumentSettings>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
                </xml>
            </noscript>
            <![endif]-->
            <style>
                table, td, div, h1, p {font-family: Arial, sans-serif;}
            </style>
            </head>
        <body style='margin:10;padding:10;'>
        <div style='background-color: whitesmoke; color: #005677; font-family: Arial, Helvetica, sans-serif;  font-size: 18px'>
                <img src='https://d7e3m5n2.stackpathcdn.com/wp-content/uploads/menuLogo.png' alt='bc logo' style='width:50em'> 
            <h1 style='font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Beta Feedback Type " . $feedbackType . " </h1> 
            <p style='margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color:#f57f43'><b>" . $feedbackUser . " Said: </b>" . $feedbackText . "</p>
            </div>
        </body>
        </html>
    ";
    $mail->send();
    header("Location: employeeRequests.php");
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}