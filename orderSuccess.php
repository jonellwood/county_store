<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/05/2024
Purpose: Display order success notification to user with order details, trigger email to user, and wipe cart from local storage.
Includes:   
            viewHead.php is a common html head element with css, favicon, and metadata 
            footer.php is the footer element.

*/
if (empty($_REQUEST['id'])) {
    header("Location: index.php");
}
$order_id = base64_decode($_REQUEST['id']);
$emp_id = base64_decode($_REQUEST['emp_id']);


function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}
// init connect
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// fetch order details
$sql = "SELECT ord.order_id, ord.customer_id, ord.created, ord.grand_total, ord.product_id, ord.quantity, ord.status, ord.size_id, ord.color_id, 
ord.order_details_id, ord.line_item_total, ord.logo, ord.comment, ord.product_price, ord.logo_fee, ord.product_code,
CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
d.dep_name
FROM ord_ref as ord
LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
LEFT JOIN curr_emp_ref as s on s.empNumber = ord.submitted_by 
LEFT JOIN dep_ref as d on ord.department = d.dep_num
WHERE ord.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $db_id);
$db_id = $order_id;
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orderInfo = $result->fetch_assoc();
} else {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Request Status</title>
    <meta charset="utf-8">
    <!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" async> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <?php include "./components/viewHead.php" ?>
    <script>
        // ! This is called inside the sendmail function on page load.
        function wipeLocalCart() {
            localStorage.removeItem('store-cart');
        }
    </script>
</head>

<!-- <body onload='sendmail(<?php echo $order_id ?>, <?php echo $emp_id ?>)'> -->

<body onload='wipeLocalCart()'>
    <!-- uncomment line above, and remove this one, to reinstate email order confirmation feature after testing is completed -->
    <div class="container">
        <h1>Request Status <img src="bg-lightblue.png" alt="bc logo" style="width:500px" class='logo-image'></h1>
        <div class="col-12">
            <?php if (!empty($orderInfo)) { ?>
                <div class="col-md-12">
                    <div class="alert alert-success">Your Request has been placed succesfully. You should receive an email
                        confirmation shortly.</div>
                </div>
                <!-- Order status and contact info -->

                <div class="row col-lg-12 ord-addr-info">

                    <p><b>Reference ID:</b> #<?php echo $orderInfo['order_id']; ?></p>
                    <p><b>Total:
                        </b><?php echo CURRENCY_SYMBOL . number_format($orderInfo['grand_total'], 2) . ' ' . CURRENCY; ?>
                    </p>
                    <p><b>Requested On: </b> <?php echo $orderInfo['created'] ?></p>
                    <p><b>Requested For: </b><?php echo $orderInfo['submitted_for']; ?></p>
                    <p><b>Department ID: </b><?php echo $orderInfo['dep_name']; ?></p>
                    <p><b>Email: </b><?php echo $orderInfo['submitted_for_email']; ?></p>
                    <p><b>Employee Number: </b> <?php echo $orderInfo['emp_id']; ?></p>
                    <p><b>Requested By: </b><?php echo $orderInfo['submitted_by']; ?></p>
                </div>
                <!-- order items -->
                <div class="row col-lg-12">
                    <?php
                    // get order items from the database 

                    $sql = "SELECT ord.order_id, ord.customer_id, ord.created, ord.grand_total, ord.product_id, ord.quantity, ord.status, ord.size_id, ord.color_id, 
                            ord.order_details_id, ord.line_item_total, ord.logo, ord.comment, ord.product_price, ord.logo_fee, ord.product_code, 
                            CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
                            d.dep_name, p.name, p.price, p.image, si.size as size_name
                            FROM ord_ref as ord
                            LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
                            LEFT JOIN curr_emp_ref as s on s.empNumber = ord.submitted_by 
                            LEFT JOIN dep_ref as d on ord.department = d.dep_num
                            JOIN sizes as si on si.size_id = ord.size_id
                            JOIN products as p ON p.product_id = ord.product_id
                            WHERE ord.order_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $db_id);
                    $db_id = $order_id;
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($item = $result->fetch_assoc()) {
                            $price = number_format($item["product_price"], 2);
                            $quantity = $item["quantity"];
                            $logoFee = number_format($item["logo_fee"], 2);
                            // $sub_total = number_format(($price * $quantity), 2);
                            $sub_total = number_format($item['line_item_total'], 2);
                            $proImage = !empty($item['image']) ? $item['image'] : 'product-images/demo-img.jpg';
                    ?>
                            <div class="second-container">
                                <div class="image-container">
                                    <img src="<?php echo $proImage; ?>" alt="..." width="100px">
                                </div>
                                <div>
                                    <span class='order-container'>
                                        <p><b>Product</b></p>
                                        <p><b>Logo</b></p>
                                        <p><b>Quantity</b></p>
                                        <p><b>Price</b></p>
                                        <p><b>Sub-Total</b></p>
                                        <p class='bottomb'><?php echo $item['name'] ?></p>
                                        <p class='bottomb'><img src="<?php echo $item['logo'] ?>" alt="bc logo" id="logo-img"></p>
                                        <p class='bottomb'><?php echo $item["quantity"]; ?></p>
                                        <p class='bottomb'><?php echo CURRENCY_SYMBOL . $price; ?> (each)</p>
                                        <p class='bottomb'><?php echo CURRENCY_SYMBOL . $sub_total; ?></p>
                                        <hr>
                                        <hr>
                                        <hr>
                                        <hr>
                                        <hr>
                                        <p><b>Color: </b><?php echo $item['color_id'] ?></p>
                                        <p><b>Size: </b><?php echo $item['size_name'] ?></p>
                                        <p></p>
                                        <p><b>Logo Fee: </b><?php echo CURRENCY_SYMBOL . $logoFee; ?> (each)</p>
                                        <p></p>
                                        <p></p>
                                        <p></p>
                                        <p></p>
                                        <p></p>
                                        <p></p>
                                        <!-- <p><b>Logo: </b><//?php echo $item['logo'] ?></p> -->
                                        <!-- <p></p> -->
                                        <!-- <p></p> -->
                                        <!-- <p></p> -->
                                    </span>
                                </div>
                            </div>
                            <span class='comment-container'><?php echo $item['submitted_by'] . ' said: ' . html_escape($item['comment']) ?></span>
                            <!-- <tr> -->
                            <!-- <td><img src="<//?php echo $proImage; ?>" alt="..." width="100px"></td> -->
                            <!-- <td><//?php echo $item["name"]; ?></td> -->
                            <!-- <td><//?php echo CURRENCY_SYMBOL . $price . ' ' . CURRENCY; ?></td> -->
                            <!-- <td><//?php echo $item["quantity"]; ?></td> -->
                            <!-- <td><//?php echo CURRENCY_SYMBOL . $sub_total . ' ' . CURRENCY; ?></td> -->
                            <!-- </tr> -->
                            <!-- <tr> -->
                            <!-- <td></td> -->
                            <!-- <td class='comment'><//?php echo $item['submitted_by'] ?> said: </td> -->
                            <!-- <td class='comment'><//?php echo $item['comment'] ?></td> -->
                            <!-- <td></td> -->
                            <!-- <td></td> -->
                            <!-- </tr> -->

                    <?php }
                    } ?>

                    <!-- </tbody> -->
                    <!-- </table> -->

                </div>
                <div class="col mb-2 button-container">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <a href="index.php" class="button btn btn-primary btn-block">💰
                                Continue
                                Shopping</a>
                            <!-- <button class='hide-from-printer btn btn-secondary' onclick='window.print()'>Print</button> -->
                            <!-- <button class="close btn btn-danger btn-block" id="close">Done <i class="fa fa-times-circle"
                                aria-hidden="true"></i></button> -->
                            <!-- <button class='btn' value='<?php echo $order_id ?>' id='send=email'
                            onclick='sendmail(this.value)'>Send Email</button> -->
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="col md-12">
                    <div class="alert alert-danger">Your order submission failed!</div>
                </div>
            <?php }
            $conn->close();
            ?>
        </div>
    </div>
    <?php include "footer.php" ?>
</body>


</html>
<script>
    function sendmail(id, emp_id) {
        fetch('./newSendOrderEmail.php?ord_id=' + id + '&emp_id=' + emp_id);
        wipeLocalCart();
    }
</script>
<style>
    .container {
        max-width: unset !important;
        margin-left: 5%;
        margin-right: 5%;
    }

    h1 {
        background-color: #00000090;
        padding: 10px;
        /* font-size: 26px; */
    }

    .alert {
        color: #000000;
    }

    .second-container {
        display: grid;
        grid-template-columns: 1fr 5fr;
        margin-bottom: 10px;
        padding-top: 10px;
        border-top: 1px dashed lightblue;
    }

    .order-container {
        padding: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        gap: 5px;
        align-items: center;
        background-color: #00000090;
        color: #ffffff;
    }

    .order-container p {
        padding: 1px;
        margin: 0;
    }

    .bottomb {
        border-bottom: 1px dashed black;
    }

    .comment-container {
        margin-bottom: 10px;
        color: #FBFCFD;
        font-family: monospace;
        background-color: #731BC5;
        border-radius: 0.375rem;
    }

    .button-container {
        margin-top: 15px;
    }

    .ord-addr-info {
        padding-top: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        background-color: #00000090;
        color: #ffffff;
    }

    img {
        border-radius: 0.375rem;

    }

    .logo-image {
        margin-left: 150px;
        max-width: 20%;
    }

    #logo-img {
        width: 30px;
    }

    button {
        border-radius: 5px;
    }

    .button {
        margin: 5px;
    }

    .button {
        display: inline-block;
        padding: 5px 10px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border: 2px solid #000000;
        border-radius: 5px;
        background-color: #4CAF50;
        color: #000000;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #4CAF50 !important;
        color: #000000 !important;
        font-weight: bold !important;
    }

    @media print {

        /* hide the print button when printing */
        .hide-from-printer {
            display: none;
        }

        body {
            width: 2500px;
            font-size: 12px;
        }

        img {
            display: block;
            z-index: 2;
            margin-left: 20px;
            height: 100px;

        }

        .report td {
            margin-top: 10px;
        }

    }
</style>