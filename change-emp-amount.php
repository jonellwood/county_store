<?php
// Start the session to use variables stored in $_SESSION. 
session_start();

// Include config file containing database information. 
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// I forget why we include this...
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");

// get empID and new_amount from POST

$empID = $_POST['emp_id'];
$new_amount = $_POST['new_amount'];


$sql = "UPDATE uniform_orders.comm_emps SET fy_budget = $new_amount WHERE empNumber = $empID";
$stmt = $conn->prepare($sql);
$result = $stmt->execute();

// Execute SQL query and check if operation was successful.
if ($result) {
    header("location:" .  $_SERVER['HTTP_REFERER']);
} else {
    // otherwise load the clothes image - just a silly placeholder for now
    header("location: clothes.jpg");
}
