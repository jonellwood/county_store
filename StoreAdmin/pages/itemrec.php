<?php

include_once "DBConn.php";

$id = $_GET['id'];
$uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));

$sql = "UPDATE uniform_orders.order_details
SET status = 'Received', order_placed = now()
WHERE order_details_id = $id";
// INSERT INTO uniform_orders.inventory (inv_UID, order_details_id, inv_emp_assigned, inv_dept_assigned, inv_requested_for, inv_received) VALUES ('$uid', '$id', '104', '105', '1008', now());



$result = mysqli_query($conn, $sql);
