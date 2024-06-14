<?php

session_start();

require_once "DBConn.php";
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
// or die('Could not connect to the database server' . mysqli_connect_error());

// od_id is order_details_id

$od_id = $_GET['id'];
// $od_id = 511;
$odsql = "SELECT ord.quantity, ord.emp_id, ord.department FROM uniform_orders.ord_ref ord WHERE ord.order_details_id = $od_id";
$odstmt = $conn->prepare($odsql);
$getQty = $odstmt->execute();
// we get the quantity value from the order details and store the value to set a basis for counter below
$odstmt->store_result();
$odstmt->bind_result($value, $idvalue, $depvalue);
$odres = $odstmt->fetch();
// echo "<pre>";
// echo "<p> var value is: ";
// print_r($value);
// echo "</p>";
// echo "<p> var idvalue is: ";
// var_dump($idvalue);
// echo "</p>";
// echo "</pre>";
if ($getQty) {
    $count = $value;
    // echo "<pre>In if stmt od_id is: ";
    // print_r($od_id);
    // echo "</pre>";
    while ($count != 0) {
        $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
        $invsql = "INSERT into uniform_orders.inventory (inv_UID, order_details_id, inv_emp_assigned, inv_dept_assigned, inv_requested_for, inv_received) VALUES ('$uid', $od_id, $idvalue, $depvalue, $idvalue, now())";
        $invstmt = $conn->prepare($invsql);
        $invstmt->execute();
        $count--;
        // echo "<pre> in if stmt count is: ";
        // print_r($count);
        // echo "</pre>";
    }
}