    <?php
    session_start();
    include_once "config.php";
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());
    include_once 'Cart.class.php';
    $cart = new Cart;
    include "nav.php";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    // Composers Autoloader
    require 'vendor/autoload.php';

    // init instance with true enabling exceptions
    $mail = new PHPMailer(true);

    if (isset($_POST['sendMailBtn'])) {
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $userName = $_POST['name'];
        $userAddress = $_POST['email'];

        try {
            $mail = new PHPMailer;
            $mail->IsSMTP();
            $mail->Host = "smtp.berkeleycountysc.gov";
            $mail->Host = "10.50.10.10";
            $mail->Port = 25;
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;

            // $mail->setFrom('noreply@berkeleycountysc.gov', 'Help from Berkeley County Store');
            $mail->setFrom($userAddress, $userName);
            $mail->addAddress($userAddress, $userName);
            $mail->addAddress('store@berkeleycountysc.gov');
            $mail->addAddress('help@berkeleycountysc.gov');
            // $mail->addAddress('jon.ellwood@berkeleycountysc.gov');
            // $mail->addAddress('james.troy@berkeleycountysc.gov');
            // $mail->addAddress('help@berkeleycountysc.gov');

            $mail->isHTML(true);
            $mail->Subject = 'County Store Help Request ' . $subject;
            $mail->Body = $message . ' ' . $userName;
            $mail->AltBody = $userName . 'said this->' . $subject . 'then said this->' . $message;

            $mail->send();
            header("location: index.php");
        } catch (Exception $e) {
            echo "Message couuld not be sent. Mailer error ID Ten T: {$mail->ErrorInfo}";
        }
    }

    ?>