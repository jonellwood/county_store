<?php

session_start();
require "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require './vendor/autoload.php';

// create an array of department numbers with order requests within the last 7 days.
$deptList = array();

$sql = "SELECT DISTINCT ord_ref.department, dep_ref.dep_head_empName, dep_ref.dep_assist_empName, dep_ref.dep_asset_mgr_empName, dep_ref.dep_name
FROM ord_ref
JOIN dep_ref on dep_ref.dep_num = ord_ref.department
WHERE created >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
AND created <= CURDATE()
AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($deptList, $row);
        // $deptList[] = $row["department"];
    }
}
$conn->close();
// echo "<p>dump of deptList array</p>";
// echo "<pre>";
// print_r($deptList);
// var_dump($deptList);
// echo "</pre>";

// is it Friday ?
// function isFriday()
// {
//     $currentDay = date('l'); // Get the current day (full day name)

//     if ($currentDay === 'Friday') {
//         return true;
//     } else {
//         return false;
//     }
// }



function getOrdersByDept($deptList)
{
    require "config.php";
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());
    foreach ($deptList as $department) {
        echo "<pre>";
        echo "department value";
        var_dump($department['department']);
        echo "</pre>";
        $dept = $department['department'];

        $sql = "SELECT order_details_id, department, line_item_total, created, CONCAT(rf_first_name, ' ', rf_last_name) as requested_for, quantity, color_id, product_name, product_code, size_name, logo, dept_patch_place from ord_ref WHERE department = $dept and ord_ref.status = 'Pending'";
        var_dump($sql);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ordersList[] = $row;
            }
        }
    }
    echo "<pre>dump of ordersList array<pre>";
    var_dump($ordersList);
    echo "</pre>";
    // Create an associative array to store orders by department
    $ordersByDepartment = [];

    // Populate the ordersByDepartment array
    foreach ($ordersList as $order) {
        $department = $order['department'];
        // $orderDetailsId = $order['order_details_id'];

        if (!isset($ordersByDepartment[$department])) {
            $ordersByDepartment[$department] = [];
        }

        $ordersByDepartment[$department][] = $order;
    }

    function makeEmailAddress($name)
    {
        if ($name === null) {
            return null;
        }

        $nameParts = explode(" ", $name);
        $firstName = strtolower($nameParts[0]);
        $lastName = strtolower($nameParts[1]);
        $emaildomain = 'berkeleycountysc.gov';

        $email = $firstName . '.' . $lastName . '@' . $emaildomain;
        return $email;
    }




    // Loop over deptList and generate tables for each department
    foreach ($deptList as $deptInfo) {
        $department = $deptInfo['department'];

        if (isset($ordersByDepartment[$department])) {
            $ordersForDepartment = $ordersByDepartment[$department];
            $dep_head = $deptInfo['dep_head_empName'];
            if ($dep_head === null) {
                $name = 'NO NAME';
            } else {
                $dep_head_email = makeEmailAddress($dep_head);
            }
            $dep_assist = $deptInfo['dep_assist_empName'];
            if ($dep_assist === null) {
                $name = 'NO NAME';
            } else {
                $dep_assist_email = makeEmailAddress($dep_assist);
            }
            $dep_asset_mgr = $deptInfo['dep_asset_mgr_empName'];
            if ($dep_asset_mgr === null) {
                $name = 'NO NAME';
            } else {
                $dep_asset_mgr_email = makeEmailAddress($dep_asset_mgr);
            }
            $departmentName = $deptInfo['dep_name'];
            $total_orders = count($ordersForDepartment);
            ob_start();

            echo "<h2>" . $departmentName . ' has ' . $total_orders . " pending requests</h2>";
            echo "<p>Department Head: $dep_head</p>";
            echo "<p>Department Admin: $dep_assist</p>";
            echo "<p>Department Asset Manager: $dep_asset_mgr</p>";
            echo `<style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid #dddddd;
                    background-color: #ffffff;
                    font-family: Arial, sans-serif;
                }

                th, td {
                    border: 1px solid #dddddd;
                    padding: 8px;
                    text-align: left;
                }

                th {
                    background-color: #f2f2f2;
                }
                </style>`;
            echo "<table role='presentation' style='width: 100%; border-collapse: collapse; border: 1px;border-color:blue; font-family: Arial, sans-serif'>";
            echo "<tr style='background-color:#f2f2f2'><th style='border: 1px solid black; padding:8px;text-align:left;'>Requested For</th><th style='border: 1px solid black; padding:8px;text-align:left;'>Qty</th><th style='border: 1px solid black; padding:8px;text-align:left;'>Prod Code</th><th style='border: 1px solid black; padding:8px;text-align:left;'>Prod Name</th><th style='border: 1px solid black; padding:8px;text-align:left;'>Color</th><th style='border: 1px solid black; padding:8px;text-align:left;'>Size</th></tr>";

            foreach ($ordersForDepartment as $order) {
                echo "<tr>";
                // echo "<td>" . $order['order_details_id'] . "</td>";
                // echo "<td>" . $order['created'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['requested_for'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['quantity'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['product_code'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['product_name'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['color_id'] . "</td>";
                echo "<td style='border: 1px solid black; padding:4px;text-align:left;'>" . $order['size_name'] . "</td>";
                // echo "<td>" . $order['logo'] . "</td>";
                // echo "<td>" . $order['dept_patch_place'] . "</td>";
                echo "</tr>";
            }

            echo "</table>";
            $tableHtml = ob_get_clean();
            $mail = new PHPMailer;
            $mail->IsSMTP();
            $mail->Host = "smtp.berkeleycountysc.gov";
            $mail->Host = "10.50.10.10";
            $mail->Port = 25;
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;

            $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');
            $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');

            $emailAddresses = [$dep_head_email];
            if ($dep_assist_email !== $dep_head_email) {
                $emailAddresses[] = $dep_assist_email;
            }
            if ($dep_asset_mgr_email !== $dep_head_email && $dep_asset_mgr_email !== $dep_assist_email) {
                $emailAddresses[] = $dep_asset_mgr_email;
            }
            // foreach ($emailAddresses as $emailAddress) {
            //     // $mail->addAddress($emailAddress);
            //     var_dump($emailAddress); // I beleive this will work as inteneded....but to test it we have to send emails to real email addresses. YIKES!
            // }
            $mail->Subject = "Pending Requests for $departmentName";
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
                <body style='margin:0;padding:0;'>";
            $mail->Body .= $tableHtml;
            $mail->Body .= "<br>";
            $mail->Body .= "<a href='https://store.berkeleycountysc.gov/storeadmin/pages/sign-in.php'><button>Click for Details</button></a>";
            $mail->Body .= "<br><p>Reminder the store can only be accessed from a Berkeley County network connected device.</p>";
            // $mail->Body .= "<br><p>" . $dep_head_email . ', ' . $dep_assist_email . ', ' .  $dep_asset_mgr_email . "</p>";

            $mail->Body .= "</body></html>";
            $mail->isHTML(true);


            if (!$mail->send()) {
                echo "Mail could not be sent. Mailer Error: " . $mail->ErrorInfo;
            } else {
                echo "Message sent!";
            }
            // Unset the email variables
            unset($dep_head_email);
            unset($dep_assist_email);
            unset($dep_asset_mgr_email);
        }
    }
}

// Check if today is Friday
// if (!isFriday()) {
//     echo "It's not Friday";
// } else {
//     echo "Today is Friday";
// }

getOrdersByDept($deptList);
// lets find out if today is a day to send the email or not - and if it is lets send some email
// function itIsFriday($deptList)
// {
//     require "config.php";
//     $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//         or die('Could not connect to the database server' . mysqli_connect_error());

//     $today = date("l");
//     if ($today != 'Thursday') {
//         return false;
//     } else {

//         foreach ($deptList as $dept) {
//             // var_dump($dept);
//             $ordersList = array();
//             $sql = "SELECT *, products.image FROM ord_ref JOIN dep_ref on dep_ref.dep_head = ord_ref.dep_head JOIN products on products.product_id = ord_ref.product_id WHERE department='$dept' AND created > DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
//             $result = mysqli_query($conn, $sql);
//             if ($result->num_rows > 0) {
//                 while ($row = mysqli_fetch_array($result)) {
//                     // echo "Order # for " . $dept . ": " . $row['order_details_id'];
//                     // echo "<br>";
//                     $ordersList[] = $row;
//                 }
//             }
//         };

// return true;
// $sql = "SELECT ifnull(sum(line_item_total), 0.00) dept_total FROM uniform_orders.ord_ref WHERE department = 41515 and created > DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
// echo "<p>VarDump of ordersList array</p>";
// echo "<pre>";

// print_r($ordersList);
// var_dump($ordersList);
// echo "</pre>";

// init instance with true enabling exceptions
// $mail = new PHPMailer(true);
// // set some variables for easier use down below
// $depHeadEmail = $ordersList[0]['email'];
// $depHeadName = $ordersList[0]['empName'];
// // server settings
// $mail = new PHPMailer;
// $mail->IsSMTP();
// $mail->Host = "smtp.berkeleycountysc.gov";
// $mail->Host = "10.50.10.10";
// $mail->Port = 25;
// $mail->SMTPAuth = false;
// $mail->SMTPAutoTLS = false;

// $mail->setFrom('noreply@berkeleycountysc.gov', 'Berkeley County Store');
// $mail->addAddress('jon.ellwood@berkeleycountysc.gov', 'Jon Ellwood');
// //$mail->addAddress('store@berkeleycountysc.gov', 'County Store');
// //$mail->addAddress('james.troy@berkeleycountysc.gov', 'James Troy');
// //$mail->addAddress($depHeadEmail, $depHeadName);

// $mail->addEmbeddedImage('./' . 'bg-lightblue.png', 'logo_p2t');
// $mail->isHTML(true);
// $mail->Subject = 'Berkeley County Store weekly recap for ' .  $ordersList[0]['dep_name'];
// var_dump($ordersList[0]['dep_name']);
//         $mail->Body = "
//         <!DOCTYPE html>
//             <html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office'>
//             <head>
//             <meta charset='UTF-8'>
//             <meta name='viewport' content='width=device-width,initial-scale=1'>
//             <meta name='x-apple-disable-message-reformatting'>
//             <title></title>
//             <!--[if mso]>
//             <noscript>
//                 <xml>
//                 <o:OfficeDocumentSettings>
//                     <o:PixelsPerInch>96</o:PixelsPerInch>
//                 </o:OfficeDocumentSettings>
//                 </xml>
//             </noscript>
//             <![endif]-->
//             <style>
//                 table, td, div, h1, p {font-family: Arial, sans-serif;}
//             </style>
//             </head>
//             <body style='margin:0;padding:0;'>
//             <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;'>
//                 <tr>
//                 <td align='center' style='padding:0;'>
//                 <table role='presentation' style='width:700px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;'>
//                 <tr>
//                     <td align='center' style='padding:40px 0 30px 0;background:#70bbd9;'>
//                     <img src=\"cid:logo_p2t\" alt='County Logo' width='300' style='height:auto;display:block;' />
//                         </td>
//                     </tr>
//                     <tr>
//                     <td style='padding:36px 30px 42px 30px;'>
//                         <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;'>
//                             <tr>
//                             <td style='padding:0 0 36px 0;color:#153643;'>
//                                 <h1 style='font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;'>Request Summary for " . $ordersList[0]['dep_name'] . " </h1> 

//                             </td>
//                             </tr>";
//         $orderCounter = 0;

//         foreach ($ordersList as $order) {
//             echo "<p>trying to get single order</p>";
//             echo "<pre>";
//             print_r($order[21]);
//             echo "</pre>";

//             $orderCounter++;
//             $proImage = $order[44];
//             $logoImage = $order[18];

//             $mail->addEmbeddedImage('./' . $proImage, 'logo_p2t' . $orderCounter);
//             $mail->addEmbeddedImage('./' . $logoImage, 'pro_logo' . $orderCounter);

//             $mail->Body .=
//                 "
//             <tr>
//                 <td style='padding:0;'>
//                     <table role='presentation' style='width:100%;border-collapse:collapse;border:0;border-spacing:0;margin-top:10px'>
//                     <tr>
//                         <td style='width:260px;padding:0;vertical-align:top;color:#153643;'>
//                             <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:logo_p2t" . $orderCounter . "' alt='" . $order['name'] . "' width='155' style='height:auto;display:block;' /></p>

//                         </td>
//                         <td style='width:260px;padding-left:20px;vertical-align:top;color:#153643;'>        
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Requested By:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . $order[25] . "</span><br>
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Name:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . $order[21] . "</span><br>
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Size:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . $order[24] . "</span><br>
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Color:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . $order[13] . "</span><br>
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Product Price:</b>$</span>
//                                 <span style='font-size:14px;line-height:14px'>" . number_format($order[19], 2) . "</span><br>
//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Quantity:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . number_format($order[11], 0) . "</span><br>

//                             <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Sub Total:</b></span>
//                                 <span style='font-size:14px;line-height:14px'>" . number_format(($order[19] * $order[11]), 2) . "</span><br>
//                             <span style='width:260px;padding:0;vertical-align:top;color:#153643;'>
//                             <p style='margin:0 0 25px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><img src='cid:pro_logo" . $orderCounter . "' alt='" . $order[18] . "' width='50' style='height:auto;display:block;' /></p>
//                             </span><br>
//                         </td>

//                     </tr>
//                     <hr>
//                     <tr>
//                     <td>
//                     <span style='margin:0 0 12px 0;font-size:14px;line-height:14px;font-family:Arial,sans-serif;'><b>Comment: </b></span>
//                         <span style='font-size:14px;line-height:14px;'>" . $order[20] . "</span><br>
//                     </td>
//                     </tr>
//                     </table>
//                 </td>
//                 </tr>

//             ";
//         }
//     }
//     // $mail->send();
// }


// $friday = @isFriday($deptList);




// put this whole function in the if-is Friday


?>

<!-- <!DOCTYPE html>
<script>
    function isTodayFriday() {
        const today = new Date();
        return today.getDay() === 5;
    }

    if (isTodayFriday()) {
        console.log("It's Friday!");
    } else {
        console.log("It's not Friday :(");
    }
</script>

</html> -->