<?php

include_once "DBConn.php";

$id = $_GET['id'];

$sql = "UPDATE uniform_orders.order_details
SET status = 'Denied'
WHERE order_details_id = $id";

$result = mysqli_query($conn, $sql);


$data = array();
echo json_encode($data);
