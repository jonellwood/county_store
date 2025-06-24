<?php

session_start();
include('DBConn.php');

$inv_UID = $_GET['inv_UID']; // UID for specific inventory item
$dept_head = $_SESSION['empNumber']; // write this value to emp_id as the person creating the event being logged
// $dept_head = '4438';
$emp_id = $_GET['emp_id']; // this is referencing the employee the inv is aassigned too


$sql = "INSERT into events (timestamp,created_by_emp_id, event_type, order_details_id) VALUES (NOW(),?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $db_created_by_emp_id, $db_event_type, $db_order_details_id);
$db_created_by_emp_id = $dept_head;
$db_event_type = "Item: " . $inv_UID . " assigned to emp# " . $emp_id;
$db_order_details_id = $order_details_id;
$stmt->execute();
