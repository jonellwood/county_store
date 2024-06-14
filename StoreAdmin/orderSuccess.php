<?php
if (empty($_REQUEST['id'])) {
    header("Location: index.php");
}
$order_id = base64_decode($_REQUEST['id']);

// init connect
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// fetch order details
$sql = "SELECT r.*, c.first_name, c.last_name, c.email, c.emp_id, c.department, s.empName as submitted_by FROM orders as r LEFT JOIN customers as c ON c.customer_id = r.customer_id LEFT JOIN emp_ref as s on s.empNumber = r.submitted_by WHERE r.order_id=?";
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

<!-- <body onload='sendmail(<//?php echo $order_id ?>)'> -->

<body>
    <!-- uncomment line above, and remove this one, to reinstate email order confirmation feature after testing is completed -->
    <div class="container">
        <h1>Request Status</h1>
        <div class="col-12">
            <?php if (!empty($orderInfo)) { ?>
            <div class="col-md-12">
                <div class="alert alert-success">Your Request has been placed succesfully</div>
            </div>
            <!-- Order status and contact info -->
            <div class="row col-lg-12 ord-addr-info">
                <div class="hdr">Order Info</div>
                <p>Please keep your Reference ID for future use</p>
                <p><b>Reference ID:</b> #<?php echo $orderInfo['order_id']; ?></p>
                <p><b>Total:
                    </b><?php echo CURRENCY_SYMBOL . number_format($orderInfo['grand_total'], 2) . ' ' . CURRENCY; ?>
                </p>
                <p><b>Placed On: </b> <?php echo $orderInfo['created'] ?></p>
                <p><b>Requested For: </b><?php echo $orderInfo['first_name'] . ' ' . $orderInfo['last_name']; ?></p>
                <p><b>Department ID: </b><?php echo $orderInfo['department']; ?></p>
                <p><b>Email: </b><?php echo $orderInfo['email']; ?></p>
                <p><b>Employee Number: </b> <?php echo $orderInfo['emp_id']; ?></p>
                <p><b>Requested By: </b><?php echo $orderInfo['submitted_by']; ?></p>
            </div>
            <!-- order items -->
            <div class="row col-lg-12">
                <table class="table table-hover cart">
                    <thead>
                        <tr>
                            <th width="10%"></th>
                            <th width="45%">Product</th>
                            <th width="15%">Price</th>
                            <th width="10%">QTY</th>
                            <th width="20%">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // get order items from the database 
                            $sql = "SELECT i.*, p.name, p.price, p.image FROM order_details as i LEFT JOIN products as p ON p.product_id = i.product_id WHERE i.order_id=?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $db_id);
                            $db_id = $order_id;
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($item = $result->fetch_assoc()) {
                                    $price = $item["price"];
                                    $quantity = $item["quantity"];
                                    // $sub_total = number_format(($price * $quantity), 2);
                                    $sub_total = number_format($item['line_item_total'], 2);
                                    $proImage = !empty($item['image']) ? $item['image'] : 'product-images/demo-img.jpg';
                            ?>
                        <tr>
                            <td><img src="<?php echo $proImage; ?>" alt="..." width="200px"></td>
                            <td><?php echo $item["name"]; ?></td>
                            <td><?php echo CURRENCY_SYMBOL . $price . ' ' . CURRENCY; ?></td>
                            <td><?php echo $item["quantity"]; ?></td>
                            <td><?php echo CURRENCY_SYMBOL . $sub_total . ' ' . CURRENCY; ?></td>
                        </tr>
                        <?php }
                            } ?>

                    </tbody>
                </table>
            </div>
            <div class="col mb-2">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <a href="index.php" class="btn btn-primary btn-block"><i class="fa fa-arrow-left"></i>
                            Continue
                            Shopping</a>
                        <!-- <button class='hide-from-printer btn btn-secondary' onclick='window.print()'>Print</button> -->
                        <!-- <button class="close btn btn-danger btn-block" id="close">Done <i class="fa fa-times-circle"
                                aria-hidden="true"></i></button> -->
                        <!-- <button class='btn' value='<//?php echo $order_id ?>' id='send=email' -->
                        <!-- onclick='sendmail(this.value)'>Send Email</button> -->
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