<?php
if (empty($_REQUEST['id'])) {
    header("Location: index.php");
}
$order_id = base64_decode($_REQUEST['id']);
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
ord.order_details_id, ord.line_item_total, ord.logo, ord.comment, ord.product_price, ord.product_code,
CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
d.dep_name
FROM ord_ref as ord
LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
LEFT JOIN emp_ref as s on s.empNumber = ord.submitted_by 
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
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
</head>

<body onload='sendmail(<?php echo $order_id ?>)'>

    <!-- <body> -->
    <!-- uncomment line above, and remove this one, to reinstate email order confirmation feature after testing is completed -->
    <div class="container">
        <h1>Request Status <img src="https://d7e3m5n2.stackpathcdn.com/wp-content/uploads/menuLogo.png" alt="bc logo"
                style="width:500px" class='logo-image'></h1>
        <div class="col-12">
            <?php if (!empty($orderInfo)) { ?>
            <div class="col-md-12">
                <div class="alert alert-success">Your Request has been placed succesfully. Please keep your Reference ID
                    for future use</div>
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
                            ord.order_details_id, ord.line_item_total, ord.logo, ord.comment, ord.product_price, ord.product_code, 
                            CONCAT(c.first_name, ' ', c.last_name) as submitted_for, c.email as submitted_for_email, c.emp_id, c.department, s.empName as submitted_by,
                            d.dep_name, p.name, p.price, p.image
                            FROM ord_ref as ord
                            LEFT JOIN customers as c ON c.customer_id = ord.customer_id 
                            LEFT JOIN emp_ref as s on s.empNumber = ord.submitted_by 
                            LEFT JOIN dep_ref as d on ord.department = d.dep_num
                            JOIN products as p ON p.product_id = ord.product_id
                            WHERE ord.order_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $db_id);
                    $db_id = $order_id;
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($item = $result->fetch_assoc()) {
                            $price = number_format($item["price"], 2);
                            $quantity = $item["quantity"];
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
                            <p><b>Price</b></p>
                            <p><b>Quantity</b></p>
                            <p><b>Sub-Total</b></p>
                            <p class='bottomb'><?php echo $item['name'] ?></p>
                            <p class='bottomb'><?php echo CURRENCY_SYMBOL . $price . ' ' . CURRENCY; ?></p>
                            <p class='bottomb'><?php echo $item["quantity"]; ?></p>
                            <p class='bottomb'><?php echo CURRENCY_SYMBOL . $sub_total . ' ' . CURRENCY; ?></p>
                            <p><b>Color: </b><?php echo $item['color_id'] ?></p>
                            <p></p>
                            <p></p>
                            <p></p>
                            <p><b>Size: </b><?php echo $item['size_id'] ?></p>
                            <p></p>
                            <p></p>
                            <p></p>
                            <p><b>Logo: </b><?php echo $item['logo'] ?></p>
                            <p></p>
                            <p></p>
                            <p></p>
                        </span>
                    </div>
                </div>
                <span
                    class='comment-container'><?php echo $item['submitted_by'] . ' said: ' . html_escape($item['comment']) ?></span>
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
                        <a href="index.php" class="btn btn-primary btn-block"><i class="fa fa-arrow-left"></i>
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

</body>

</html>
<script>
function sendmail(id) {
    fetch('./sendOrderEmail.php?id=' + id);
}
</script>
<style>
.second-container {
    display: grid;
    grid-template-columns: 1fr 5fr;
    margin-bottom: 10px;
    padding-top: 10px;
    border-top: 1px dashed lightblue;
}

.order-container {
    display: grid;
    grid-template-columns: 3fr 1fr 1fr 1fr;
    gap: -10px;
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
    color: lightblue;
    font-family: monospace;
    background-color: blueviolet;
    border-radius: 0.375rem;
}

.button-container {
    margin-top: 15px;
}

.ord-addr-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
}

img {
    border-radius: 0.375rem;

}

.logo-image {
    margin-left: 150px;
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