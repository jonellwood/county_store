<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp_id = $_GET['emp_id'];

function convertColorStr($str)
{
    return strtolower(preg_replace('/\s+|\//', '', $str));
}


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
$orders_data = array();
$totals_data = array();
$emp_data = array();
// echo "<pre>";
// echo "<p> FY Start " . var_dump($db_fystart) . "</p>";
// echo "<p> FY End " . var_dump($db_fyend) . "</p>";
// echo "</pre>";
// Get submitted amount total for the employee this request is for
$sql1 = "SELECT COALESCE(sum(line_item_total), 0) as total_submitted, COUNT(order_details_id) as count_submitted FROM uniform_orders.ord_submitted WHERE emp_id = ? and order_created BETWEEN ? AND ?";
// echo "<pre>";
// echo var_dump($sql1);
// echo "</pre>";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
$stmt1->execute();
$result1 = $stmt1->get_result();

while ($row1 = $result1->fetch_assoc()) {
    array_push($totals_data, $row1);
};
// Get approved amount total for the employee this request is for
$sql2 = "SELECT COALESCE(sum(line_item_total), 0) as total_approved, COUNT(order_details_id) as count_approved FROM uniform_orders.ord_approved WHERE emp_id = ? and order_created BETWEEN ? AND ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
$stmt2->execute();
$result2 = $stmt2->get_result();

while ($row2 = $result2->fetch_assoc()) {
    array_push($totals_data, $row2);
};
// Get ordered amount total for the employee this request is for
$sql3 = "SELECT COALESCE(sum(line_item_total), 0) as total_ordered, COUNT(order_details_id) as count_ordered FROM uniform_orders.ord_ordered WHERE emp_id = ? and order_created BETWEEN ? AND ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
$stmt3->execute();
$result3 = $stmt3->get_result();

while ($row3 = $result3->fetch_assoc()) {
    array_push($totals_data, $row3);
};
// Get completed amount total for the employee this request is for
$sql4 = "SELECT COALESCE(sum(line_item_total), 0) as total_completed, COUNT(order_details_id) as count_completed 
FROM uniform_orders.ord_completed WHERE emp_id = ? and order_created BETWEEN ? AND ?";
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("iss", $db_emp_id, $db_fystart, $db_fyend);
$stmt4->execute();
$result4 = $stmt4->get_result();

while ($row4 = $result4->fetch_assoc()) {
    array_push($totals_data, $row4);
};
array_push($data, $totals_data);
// echo "<pre>";
// var_dump($totals_data);
// echo "</pre>";

$empSql = "SELECT empName, email from emp_ref where empNumber = $emp_id";
$empStmt = $conn->prepare($empSql);
$empStmt->execute();
$empResult = $empStmt->get_result();
if ($empResult->num_rows > 0) {
    while ($row = $empResult->fetch_assoc()) {
        array_push($emp_data, $row);
    }
} else {
    array_push($emp_data, [
        'empName' => 'Employee Not Found',
        'email' => 'store@berkeleycountysc.gov'
    ]);
};
array_push($data, $emp_data);

$ordersSql = "SELECT ord_ref.product_id, ord_ref.quantity, ord_ref.status, ord_ref.color_id, ord_ref.size_name, ord_ref.order_placed, 
ord_ref.line_item_total, ord_ref.tax, ord_ref.logo_fee, ord_ref.logo, ord_ref.product_price, 
ord_ref.comment, ord_ref.dept_patch_place, ord_ref.bill_to_dept, ord_ref.product_name, ord_ref.status, 
ord_ref.product_code, ord_ref.dep_name, emp_ref.email, emp_ref.empName, p.image
FROM uniform_orders.ord_ref
LEFT JOIN emp_ref on emp_ref.empNumber = ord_ref.emp_id
LEFT JOIN products p on p.product_id = ord_ref.product_id
WHERE ord_ref.emp_id = $emp_id AND ord_ref.created between '$db_fystart' AND '$db_fyend'
";
// var_dump($ordersSql);

$stmt = $conn->prepare($ordersSql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($orders_data, $row);
    }
};
// else {
//     array_push($orders_data, [
//         // 'order_details_id' => ['Nothing'],
//         'response' => ['nothing_found'],
//     ]);
// };
array_push($data, $orders_data);

$cust_email = $emp_data[0]['email'];
$cust_name = $emp_data[0]['empName'];
$pattern = "/[^a-zA-Z0-9\s\-_]/";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

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
    $mail->addAddress($cust_email, $cust_name);
    $mail->addEmbeddedImage('./' . 'bg-lightblue.png', 'logo_p2t');
    $mail->isHTML(true);
    $mail->Subject = 'Berkeley County Store Order Summary for ' . $cust_name;

    $mail->Body =
        "
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
            <body style='margin:10px;padding:20px;'>
            <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;'>
                <tr>
                    <td align='center' style='padding:40px 0 30px 0;background:#70bbd9;'>
                        <img src=\"cid:logo_p2t\" alt='County Logo' width='300' style='height:auto;display:block;' />
                    </td>
                </tr>
                <tr>
                    <td colspan='4' style='padding:0 0 36px 0;color:#153643;'>
                        <h2 style='font-size:20px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Current Fiscal Year Totals for " . $cust_name . " <h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0; padding:'>
                            <tr>
                                <th style='text-align: center; width:25%;'>Submitted</th>
                                <th style='text-align: center; width:25%;'>Approved</th>
                                <th style='text-align: center; width:25%;'>Ordered</th>
                                <th style='text-align: center; width:25%;'>Received</th>
                            </tr>
                            <tr style='border-bottom: 2px solid black;'>
                                <td style='font-size:14px;margin:0 0 20px 25px;font-family:Arial,sans-serif; text-align: center; width:25%;'> $ " . number_format($totals_data[0]['total_submitted'], 2) . "</td>
                                <td style='font-size:14px;margin:0 0 20px 25px;font-family:Arial,sans-serif; text-align: center; width:25%;'> $ " . number_format($totals_data[1]['total_approved'], 2) . "</td>
                                <td style='font-size:14px;margin:0 0 20px 25px;font-family:Arial,sans-serif; text-align: center; width:25%;'> $ " . number_format($totals_data[2]['total_ordered'], 2) . "</td>
                                <td style='font-size:14px;margin:0 0 20px 25px;font-family:Arial,sans-serif; text-align: center; width:25%;'> $ " . number_format($totals_data[3]['total_completed'], 2) . "</td>
                            </tr>
                        </table>
                    </td>
                </tr>";
    if (!empty($orders_data)) {

        $orderCounter = 0;
        foreach ($orders_data as $order) {
            $orderCounter++;
            // $proImage = $order['image'];
            $proImage = "product-images/" . convertColorStr($order['color_id']) . '_' . strtolower($order['product_code']) . ".jpg";
            // echo $proImage;
            // echo "<br>";
            $logoImage = $order['logo'];
            $mail->addEmbeddedImage('./' . $proImage, 'logo_p2t' . $orderCounter);
            $mail->addEmbeddedImage('./' . $logoImage, 'pro_logo' . $orderCounter);
            $mail->Body .= "
                <tr>
                    <td padding-left:20px;vertical-align:top;color:#153643;'>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Name:</b></span>
                        <span style='font-size:14px;line-height:14px'>" . preg_replace($pattern, " ", $order['product_name']) . "</span>
                    </td>
                </tr>
                <tr>
                    <td style='width: 100%;padding-left:20px;vertical-align:top;color:#153643;'>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Size:</b></span>
                        <span style='font-size:14px;line-height:14px'>" . $order['size_name'] . "</span>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Color:</b></span>
                        <span style='font-size:14px;line-height:14px'>" . $order['color_id'] . "</span>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Quantity:</b></span>
                        <span style='font-size:14px;line-height:14px'>" . number_format($order['quantity'], 0) . "</span>
                        <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Status:</b></span>
                        <span style='font-size:14px;line-height:14px'>" . $order['status'] . "</span><br>
                    </td>
                </tr>
                <tr>
                    <td style='width:100%;padding-left:20px;vertical-align:top;color:#153643;'> 
                        <hr>
                    </td>
                </tr>
                ";
            unset($proImage);
        }
    }
    $mail->Body .= "
            <hr>
            <tr>
                <td style='padding:30px;background:#286142;'>
                    <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;'>
                        <tr>
                            <td style='padding:0;width:50%;' align='left'>
                                <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;'>&reg; Berkeley County Store 2024 </p>
                            </td> 
                            <td style='padding:0;width:50%;' align='right'>
                                <table role='presentation' style='border-collapse:collapse;border:0;border-spacing:0;'>
                                    <tr>
                                        <td style='padding:0;width:50%;' align='left'>
                                            <p style='margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;'>You're still here? It's over.</p>
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
    // echo ($body);
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
// echo json_encode(($data));


// <td style='width:260px;padding:0;vertical-align:top;color:#153643;'>
// <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:logo_p2t" . $orderCounter . "' alt='" . $proImage . "' width='155' style='height:auto;display:block;' /></p>                
// </td>

// <tr>
// <h2 style='font-size:20px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Current Fiscal Year Totals </h2>
// </tr>

//  <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Price:</b>$</span>
// <span style='font-size:14px;line-height:14px'>" . number_format($order['product_price'], 2) . "</span><br>
// <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Logo Fee:</b>$</span>
// <span style='font-size:14px;line-height:14px'>" . number_format($order['logo_fee'], 2) . "</span><br>

// this is the style for the first td that hold the returned fy values
// <td style='padding:0 0 36px 0;'>

// <tr>
//                                             <td>
//                                                 <p style='font-size:14px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Submitted: $" . number_format($totals_data[0]['total_submitted'], 2) . "<p>
//                                             </td>
//                                             <td>
//                                                 <p style='font-size:14px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Approved: $" . number_format($totals_data[1]['total_approved'], 2) . "<p>
//                                             </td>
//                                             <td>                               
//                                                 <p style='font-size:14px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Ordered: $" . number_format($totals_data[2]['total_ordered'], 2) . "<p>  
//                                             </td>
//                                             <td>
//                                                 <p style='font-size:14px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Received:" . number_format($totals_data[3]['total_completed'], 2) . "<p>
//                                             </td>
//                                         </tr>

// Herein lies the logo image and placement information
// <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;'>
//                                                 <span style='width:260px;padding:0;vertical-align:top;color:#153643;'>
//                                                 <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:pro_logo" . $orderCounter . "' alt='" . $order['logo'] . "' width='50' style='height:auto;display:block;' /></p>
//                                                 </span>
//                                             </td>
//                                             <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;'>
//                                                 <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Dept Name:</b></span>
//                                                 <span style='font-size:14px;line-height:14px'>" . $order['dept_patch_place'] . "</span><br>
//                                             </td>