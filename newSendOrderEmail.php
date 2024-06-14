<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$ord_id = $_GET['ord_id'];
$emp_id = $_GET['emp_id'];

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
// echo "<pre>";
// echo "<p> FY Start " . var_dump($db_fystart) . "</p>";
// echo "<p> FY End " . var_dump($db_fyend) . "</p>";
// echo "</pre>";
// Get submitted amount total for the employee this request is for
// $sql1 = "SELECT sum(line_item_total) as total_submitted, COUNT(order_details_id) as count_submitted FROM uniform_orders.ord_submitted WHERE emp_id = ? and order_created BETWEEN ? AND ?";
// $stmt1 = $conn->prepare($sql1);
// $stmt1->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
// $stmt1->execute();
// $result1 = $stmt1->get_result();

// while ($row1 = $result1->fetch_assoc()) {
//     array_push($data, $row1);
// };
// Get approved amount total for the employee this request is for
// $sql2 = "SELECT sum(line_item_total) as total_approved, COUNT(order_details_id) as count_approved FROM uniform_orders.ord_approved WHERE emp_id = ? and order_created BETWEEN ? AND ?";
// $stmt2 = $conn->prepare($sql2);
// $stmt2->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
// $stmt2->execute();
// $result2 = $stmt2->get_result();

// while ($row2 = $result2->fetch_assoc()) {
//     array_push($data, $row2);
// };
// Get ordered amount total for the employee this request is for
// $sql3 = "SELECT sum(line_item_total) as total_ordered, COUNT(order_details_id) as count_ordered FROM uniform_orders.ord_ordered WHERE emp_id = ? and order_created BETWEEN ? AND ?";
// $stmt3 = $conn->prepare($sql3);
// $stmt3->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
// $stmt3->execute();
// $result3 = $stmt3->get_result();

// while ($row3 = $result3->fetch_assoc()) {
//     array_push($data, $row3);
// };
// Get completed amount total for the employee this request is for
// $sql4 = "SELECT sum(line_item_total) as total_completed, COUNT(order_details_id) as count_completed 
// FROM uniform_orders.ord_completed WHERE emp_id = ? and order_created BETWEEN ? AND ?";
// $stmt4 = $conn->prepare($sql4);
// $stmt4->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
// $stmt4->execute();
// $result4 = $stmt4->get_result();

// while ($row4 = $result4->fetch_assoc()) {
//     array_push($data, $row4);
// };
// echo "<pre>";
// var_dump($data);
// echo "</pre>";

// Get order details
$ordSql = "SELECT ord.order_id, ord.customer_id, ord.created, ord.grand_total, ord.product_id, ord.quantity, ord.status, ord.size_id, ord.color_id, 
ord.order_details_id, ord.line_item_total, ord.logo, ord.dept_patch_place, ord.comment, ord.product_price, ord.logo_fee, ord.product_code, 
CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
s.email as submitted_by_email, d.dep_name, p.name, p.price, p.image, si.size as size_name
FROM ord_ref as ord 
LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
LEFT JOIN curr_emp_ref as s on s.empNumber = ord.submitted_by
LEFT JOIN departments d on d.dep_num = s.deptNumber 
JOIN sizes as si on si.size_id = ord.size_id
JOIN products as p ON p.product_id = ord.product_id
WHERE ord.order_id=?
";

$ordStmt = $conn->prepare($ordSql);
$ordStmt->bind_param("i", $db_id);
$db_id = $ord_id;
$ordStmt->execute();
$ordResult = $ordStmt->get_result();

// if ($ordResult->num_rows > 0) {
//     $orderInfo = $ordResult->fetch_assoc();
// };

$ordArray = array();

while ($ordRow = $ordResult->fetch_assoc()) {
    array_push($ordArray, $ordRow);
};

$order_id = $ordArray[0]['order_id'];
$submitted_for = $ordArray[0]['submitted_for'];
$submitted_by_email = $ordArray[0]['submitted_by_email'];
$submitted_by = $ordArray[0]['submitted_by'];
// start calculating and formatting results
// echo "<pre>";
// TOTALS FOR SUBMITTED AMOUNTS
// $total_submitted = ($data[0]['total_submitted']);
// $total_submitted = is_null($total_submitted) ? $total_submitted = 0 : $total_submitted;
// echo "<p>Total Submitted</p>";
// var_dump(number_format($total_submitted, 2));
// $count_submitted = ($data[0]['count_submitted']);
// $count_submitted - is_null($count_submitted) ? $count_submitted = 0 : $count_submitted;
// echo "<p>Count Submitted</p>";
// var_dump($count_submitted);

// TOTALS FOR APPROVED AMOUNTS
// $total_approved = ($data[1]['total_approved']);
// $total_approved = is_null($total_approved) ? $total_approved = 0 : $total_approved;
// echo "<p>Total Approved</p>";
// var_dump(number_format($total_approved, 2));
// $count_approved = ($data[1]['count_approved']);
// $count_approved - is_null($count_approved) ? $count_approved = 0 : $count_approved;
// echo "<p>Count Approved</p>";
// var_dump($count_approved);

// TOTALS FOR ORDERED AMOUNTS
// $total_ordered = ($data[2]['total_ordered']);
// $total_ordered = is_null($total_ordered) ? $total_ordered = 0 : $total_ordered;
// echo "<p>Total Ordered</p>";
// var_dump(number_format($total_ordered, 2));
// $count_ordered = ($data[2]['count_ordered']);
// $count_ordered - is_null($count_ordered) ? $count_ordered = 0 : $count_ordered;
// echo "<p>Count Ordered</p>";
// var_dump($count_ordered);

// TOTALS FOR COMPLETED AMOUNTS

// $total_completed = ($data[3]['total_completed']);
// $total_completed = is_null($total_completed) ? $total_completed = 0 : $total_completed;
// echo "<p>Total Completed</p>";
// var_dump(number_format($total_completed, 2));
// $count_completed = ($data[3]['count_completed']);
// $count_completed - is_null($count_completed) ? $count_completed = 0 : $count_completed;
// echo "<p>Count Completed</p>";
// var_dump($count_completed);
// echo "</pre>";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

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
    // $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');
    $mail->addAddress('store@berkeleycountysc.gov', 'County Store');
    $mail->addAddress($submitted_by_email, $submitted_by);

    // $mail->addAddress('james.troy@berkeleycountysc.gov', 'James Troy');
    // expected result. IF requested_for and submitted_by are the same email address only one email will be sent. Otherwise each will get an email.
    // $mail->addAddress($orderInfo["email"], ($orderInfo["first_name"] . " " . $orderInfo["last_name"]));
    // if ($orderInfo["email"] != $orderInfo["submitted_by_email"]) {
    //     $mail->addAddress($orderInfo["submitted_by_email"], $orderInfo["submitted_by"]);
    // }
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
                                        <td style='width:260px;padding:0;vertical-align:top;color:#153643;'>
                                            <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:logo_p2t" . $orderCounter . "' alt='" . $order['name'] . "' width='155' style='height:auto;display:block;' /></p>
                                            
                                        </td>
                                        <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;'>        
                                            <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Name:</b></span>
                                                <span style='font-size:14px;line-height:14px'>" . $order['name'] . "</span><br>
                                            <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Size:</b></span>
                                                <span style='font-size:14px;line-height:14px'>" . $order['size_name'] . "</span><br>
                                            <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Color:</b></span>
                                                <span style='font-size:14px;line-height:14px'>" . $order['color_id'] . "</span><br>
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
                        <td style='padding:30px;background:#286142;'>
                        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;'>
                            <tr>
                            <td style='padding:0;width:50%;' align='left'>
                                <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;'>
                                &reg; Berkeley County Store 2023 
                                </p>
                            </td> 
                            <td style='padding:0;width:50%;' align='right'>
                                <table role='presentation' style='border-collapse:collapse;border:0;border-spacing:0;'>
                                <tr>
                                </tr>
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

    // echo ($mail->Body);
    // LOOK AT LINES 293 TO 302 OR SO. ITS ALL JACKED UP                                            

    //$mail->AltBody = 'Request for' . $orderInfo["submitted_for"]  . 'to purchase' . $orderInfo['quantity'] . ' ' . $orderInfo["product_name"] . 'was submitted by' . $orderInfo["submitted_by"] . 'on' . $orderInfo["created"] . '.';

    $mail->send();
    // echo 'Message has been sent - now get to work';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
