<?php
// Start the session to use variables stored in $_SESSION. 
session_start();

// Include config file containing database information. 
include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");


/* Get inv_UID and emp_id from POST variable. */
//$inv_UID = $_POST['inv_UID'];
//$emp_id = $_POST['emp_pick_list'];
$inv_UID = $_GET['inv_UID'];
$emp_id = $_GET['emp_pick_list'];




$sql = "UPDATE uniform_orders.inventory SET inv_emp_assigned = $emp_id, inv_status = 'Assigned' WHERE inv_UID = '$inv_UID'";
$stmt = $conn->prepare($sql);
$go = $stmt->execute();

// Execute SQL query and check if operation was successful. 
// check if $go is true, if it is then view inventory page
if ($go) {
    header("location: $_SERVER[HTTP_REFERER]");
    // header("location: view-emp-inventory.php?emp_id=" . $emp_id);

    // otherwise load the clothes image - just a silly placeholder for now
} else {
    header("location: clothes.jpg");
};
