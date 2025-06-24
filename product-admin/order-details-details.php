<?php

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$od_id = $_GET['od_id'];

$order = array();
$sql = "SELECT * from ord_ref where order_details_id = $od_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        // $order[] = $row;
        array_push($order, $row);
    }
}

// echo "<pre>";
// echo print_r($order);
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="icon" href="./favicons/favicon.ico">
    <title>Order Details</title>
</head>

<body>
    <div class="header-holder">
        <h1>Order Details for Order # <?php echo $order[0]['order_id'] ?> <a
                href='event-log.php'><button>Back</button></a>
        </h1>
    </div>
    <div class="body">


        <div class="details-holder">
            <div>
                <b>Order Requested for:</b>
                <?php echo $order[0]['rf_first_name'] . ' ' . $order[0]['rf_last_name'] ?><br>
                <b>Quantity: </b><?php echo $order[0]['quantity'] ?><br>
                <b>Product Code: </b><?php echo $order[0]['product_code'] ?><br>
                <b>Product Name:</b><?php echo $order[0]['product_name'] ?><br>
                <b>Product Color:</b><?php echo $order[0]['color_id'] ?><br>
                <b>Product Size:</b><?php echo $order[0]['size_name'] ?><br>

            </div>

            <div>
                <b>Request Submitted:</b><?php echo $order[0]['created'] ?><br>
                <b>Order Placed:</b><?php echo $order[0]['order_placed'] ?><br>
                <b>Order Status:</b><?php echo $order[0]['status'] ?><br>
                <!-- <b>Image:</b><//?php echo $order[0]['name.jpg'] ?><br> -->
            </div>
            <!-- </div> -->
            <hr>
            <br>
            <div class="images-holder">
                <b>Logo:</b><br><img src='../<?php echo $order[0]['logo'] ?>' alt='logo' class='logo-img'><br>
                <!-- <hr> -->
                <img src='../product-images/<?php echo $order[0]['product_code'] ?>.jpg' alt='product image'
                    class='prod-img'>
                <img src='../product-images/<?php echo $order[0]['product_code'] ?>_prod.jpg' alt='product image'
                    class='prod-img'><br>
            </div>
        </div>

        </span>
    </div>
</body>

</html>
<style>
/* html {
    background: rgb(247, 195, 177);
    background: radial-gradient(circle, rgba(247, 195, 177, 1) 0%, rgba(235, 101, 54, 1) 50%, rgba(132, 62, 100, 1) 100%);

}
*/
/* body {
        margin: 20px;
    } */

.details-holder {
    display: grid;
    grid-template-columns: 1fr 1fr;
    line-height: 1.5;
}

.images-holder {
    display: inline;
}

.logo-img {
    width: 5%;
}

.prod-img {
    width: 15%;
}
</style>