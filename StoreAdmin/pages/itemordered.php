<?php

include_once "DBConn.php";

$id = $_GET['id'];

$sql = "UPDATE uniform_orders.order_details
SET status = 'Ordered', order_placed = now()
WHERE order_details_id = $id";

$result = mysqli_query($conn, $sql);
