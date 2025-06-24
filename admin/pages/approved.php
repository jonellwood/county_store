<?php

include_once "DBConn.php";

$id = $_GET['id'];

$sql = "UPDATE uniform_orders.order_details
SET status = 'Approved'
WHERE order_details_id = $id";

$result = mysqli_query($conn, $sql);
