<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


// $ord_id = $_GET['ord_id'];
$ord_id = $_REQUEST['ord_id'];
// $emp_id = $_GET['emp_id'];
$emp_id = $_REQUEST['emp_id'];
// echo "ord_id: " . $ord_id . " emp_id: " . $emp_id;
class MyDateTime extends DateTime
{
    /**
     * Calculates start and end date of fiscal year
     * @param DateTime $dateToCheck A date withn the year to check
     * @return array('start' => timestamp of start date ,'end' => timestamp of end date) 
     */
    public function fiscalYear()
    {
        $result = array();
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $end = new DateTime();
        $end->setTime(23, 59, 59);
        $year = $this->format('Y');
        $start->setDate($year, 7, 1);
        if ($start <= $this) {
            $end->setDate($year + 1, 3, 31);
        } else {
            $start->setDate($year - 1, 7, 1);
            $end->setDate($year, 6, 30);
        }
        $result['start'] = $start->getTimestamp();
        $result['end'] = $end->getTimestamp();
        return $result;
    }
}

$mydate = new MyDateTime();    // will use the current date time
$year = $mydate->format('Y');  // to get the current year and 
$mydate->setDate($year, 6, 30); // pass into here to set the values to apply
$result = $mydate->fiscalYear(); // the fiscalYear method too

$fystart = $result['start'];
$fyend = $result['end'];

$db_emp_id = $emp_id;
$db_fystart = date(DATE_RFC3339, $fystart);
$db_fyend = date(DATE_RFC3339, $fyend);
$data = array();

$orderIDList = [];
$sql = "SELECT order_details_id FROM ord_ref WHERE order_id = $ord_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    array_push($orderIDList, $row);
}

// echo "<pre>";
// var_dump($orderIDList);
// echo "</pre>";
$ordArray = array();
foreach ($orderIDList as $key => $value) {
    $ord_id = $value['order_details_id'];
    //     // Get order details
    $ordSql = "SELECT ord.order_id, ord.customer_id, ord.created, ord.grand_total, ord.product_id, ord.quantity, ord.status, 
    ord.size_id, ord.color_name, ord.order_details_id, ord.line_item_total, ord.logo, ord.dept_patch_place, ord.comment, 
    ord.product_price, ord.logo_fee, ord.product_code, 
    CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, 
    c.department, s.empName as submitted_by, s.email as submitted_by_email, d.dep_name, p.name, p.image, si.size_name as size_name 
    FROM ord_ref as ord 
    LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
    LEFT JOIN curr_emp_ref as s on s.empNumber = ord.submitted_by 
    LEFT JOIN departments d on d.dep_num = s.deptNumber 
    JOIN sizes_new as si on si.size_id = ord.size_id 
    JOIN products_new as p ON p.product_id = ord.product_id 
    WHERE ord.order_details_id=$ord_id
    ";
    // echo "<br />";
    // echo $ordSql;
    // echo "<br />";
    $ordStmt = $conn->prepare($ordSql);
    $ordStmt->execute();

    $ordResult = $ordStmt->get_result();

    //$ordArray = array();

    while ($ordRow = $ordResult->fetch_assoc()) {
        array_push($ordArray, $ordRow);
    };
}
// echo "<pre>";
// var_dump($ordArray);
// echo "</pre>";

$order_id = $ordArray[0]['order_id'];
$submitted_for = $ordArray[0]['submitted_for'];
$submitted_by_email = $ordArray[0]['submitted_by_email'];
$submitted_by = $ordArray[0]['submitted_by'];

// echo "<br />";
// echo "order Id:";
// var_dump($order_id);
// echo "<br />";
// echo "submitted for: ";
// var_dump($submitted_for);
// echo "<br/>";
// echo "submitted by email: ";
// var_dump($submitted_by_email);
// echo "<br />";
// echo "submitted by: ";
// var_dump($submitted_by);
// echo "<br />";
// echo "<br />";
// echo "<br />";
// echo "<br />";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

// init instance with true enabling exceptions
$mail = new PHPMailer(true);


try {
    //server settings
    $mail = new PHPMailer;
    $mail->IsSMTP();
    $mail->Host = "smtp.berkeleycountysc.gov";
    $mail->Host = "10.50.10.10";
    $mail->Port = 25;
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;

    $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');

    $mail->addAddress('store@berkeleycountysc.gov', 'County Store');
    $mail->addAddress($submitted_by_email, $submitted_by);

    $mail->addEmbeddedImage('./' . 'bg-lightblue.png', 'logo_p2t');
    $mail->isHTML(true);
    $mail->Subject = 'Berkeley County Store request for ' .  $submitted_for;

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
        <body style='margin:0;padding:0;'>
        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;'>
            <tr>
            <td align='center' style='padding:0;'>
                <table role='presentation' style='width:700px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;'>
                <tr>
                    <td align='center' style='padding:40px 0 30px 0;background:#70bbd9;'>
                    <img src=\"cid:logo_p2t\" alt='County Logo' width='300' style='height:auto;display:block;' />
                    </td>
                </tr>
                <tr>
                    <td style='padding:36px 30px 42px 30px;'>
                    <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;'>
                        <tr>
                        <td style='padding:0 0 36px 0;color:#153643;'>
                            <h1 style='font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Request Details for " . $submitted_for . " </h1> 
                            <p style='margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color:#f57f43'>Request Confirmation #: " . $order_id . "</p>                                
                        </td>
                        </tr>";
    $orderCounter = 0;
    foreach ($ordArray as $order) {
        $orderCounter++;
        $proImage = $order['image'];
        $logoImage = $order['logo'];
        // from php mail documentation proper syntax is $mail->AddEmbeddedImage(filename, cid, name);
        // therefore the $proImage variable that updates each time through the look should update the cid ??
        // the line below this work the same as the uncommented one for some reason.... 
        $mail->addEmbeddedImage('./' . $proImage, 'logo_p2t' . $orderCounter);
        $mail->addEmbeddedImage('./' . $logoImage, 'pro_logo' . $orderCounter);
        $mail->Body .= " 
                    <tr>
                    <td style='padding:0;'>
                        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;margin-top:10px'>
                        <tr>
                            <td style='width:260px;padding:0;vertical-align:top;color:#f57f43;'>
                                <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:logo_p2t" . $orderCounter . "' alt='" . $order['name'] . "' width='155' style='height:auto;display:block;' /></p>
                                
                            </td>
                            <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;'>        
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Name:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . $order['name'] . "</span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Size:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . $order['size_name'] . "</span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Color:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . $order['color_name'] . "</span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Price:</b>$</span>
                                    <span style='font-size:14px;line-height:14px'>" . number_format($order['product_price'], 2) . "</span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Logo Fee:</b>$</span>
                                    <span style='font-size:14px;line-height:14px'>" . number_format($order['logo_fee'], 2) . "</span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Quantity:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . number_format($order['quantity'], 0) . "</span><br>
                                
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Sub Total:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . number_format((($order['product_price'] + $order['logo_fee']) * $order['quantity']), 2) . "</span><br>
                                <span style='width:260px;padding:0;vertical-align:top;color:#153643;'>
                                <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:pro_logo" . $orderCounter . "' alt='" . $order['logo'] . "' width='50' style='height:auto;display:block;' /></p>
                                </span><br>
                                <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Dept Name:</b></span>
                                    <span style='font-size:14px;line-height:14px'>" . $order['dept_patch_place'] . "</span><br>
                            </td>
                            
                        </tr>
                        <hr>
                        <tr>
                        <td>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Comment: </b></span>
                            <span style='font-size:14px;line-height:14px;'>" . $order['comment'] . "</span><br>
                        </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                    ";
        unset($proImage);
    }
    $mail->Body .= "                                                
        </table>                   
            <hr>
                <table role='presentation' style='width:700px;border-collapse:collapse;border-spacing:0;text-align:right;'>
                    <tr>
                        <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;>
                            <span style='margin:0 0 12px 0;font-size:12px;line-height:12px;font-family:Arial,sans-serif;'><b>Order Total with Tax: </span>
                            <span style='font-size:14px;line-height:14px;margin-right:15px;'>" . number_format($order['grand_total'], 2) . "</b></span><br>
                        </td>
                    </tr>
                </table>
                    <tr>
                        <td style='padding:30px; background:#FFF;'>
                        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;'>
                            <tr>
                            <td style='padding:0;width:50%;' align='left'>
                                <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#000000;margin-bottom:20px;'>
                                 This email was sent from a notification-only address that cannot accept incoming email. Do not reply to this message. For questions regarding your request send your inquiry to the 
                                 <a href='mailto:store@berkeleycountysc.gov?cc=$submitted_by_email,&subject=Request%20Question%20-$ord_id&body=I%20have%20a%20question%20about%20my%20order'>Store Help Inbox</a>
                                </p>
                            </td>
                    </tr>
                    <tr>
                        <td style='padding:30px;background:#286142;'>
                        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;'>
                            <tr>
                                <td style='padding:0;width:50%;' align='left'>
                                    <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;'>
                                    &reg; Berkeley County Store $year 
                                    </p>
                                </td> 
                                <td style='padding:0;width:50%;' align='right'>
                                    <table role='presentation' style='border-collapse:collapse;border:0;border-spacing:0;'>
                                        <tr></tr>
                                    </table>
                                </td>
                            </tr>
                </table>
            </td>
            </tr>
            </table>
            </td>
            </tr>
        </table>
        </body>
    </html>
    ";
    $mail->send();
    // echo 'Message has been sent - now get to work';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}