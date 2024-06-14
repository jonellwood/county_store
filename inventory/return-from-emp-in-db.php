<?php
session_start();
include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$inv_UID = $_GET['inv_UID'];
$noemp = "NULL";
$sql = "UPDATE uniform_orders.inventory SET inv_emp_assigned = $noemp, inv_status = 'Available' WHERE inv_UID = '$inv_UID'";
$stmt = $conn->prepare($sql);
$go = $stmt->execute();
if ($go) {
    // header("location: view-inventory.php");
    header("location: $_SERVER[HTTP_REFERER]");
} else {
    header("location: clothes.jpg");
};
