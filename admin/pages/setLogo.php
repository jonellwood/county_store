<?php

include('DBConn.php');

$id = $_GET['id'];
$logo = $_GET['logo'];

$sql = "UPDATE uniform_orders.order_details 
SET logo = '$logo'
WHERE order_details_id = '$id'";

// $stmt = $conn->prepare($sql);
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "200";
}