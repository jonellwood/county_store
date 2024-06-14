<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Composers Autoloader
require 'vendor/autoload.php';

// init instance with true enabling exceptions
$mail = new PHPMailer(true);
$fromEmail = $_SESSION["email"];

if (isset($_POST['sendMailBtn'])) {
    $message = $_POST['message'];
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

        $mail->setFrom($fromEmail, 'Admin Needs Assistance - County Store Portal');
        $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');
        $mail->addAddress('james.troy@berkeleycountysc.gov', 'James Troy');
        $mail->addAddress('help@berkeleycountysc.gov', 'IT Customer Support');


        $mail->isHTML(true);
        $mail->Subject = 'Berkeley County Store Admin Portal Help';
        $mail->Body = $message;
        $mail->AltBody = 'This is the plain text version';

        $mail->send();
        echo '<script>alert("Email sent successfully! IT will contact you soon!! :)")</script>';
        echo '<script>window.location.href="overview.php";</script>';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    // }
}
