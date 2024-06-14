<?php
// *************************************************************************//
//THIS FILE IS NOT BEING USED IN PRODUCTION AND EXISTS FOR HISTORICAL REFERENCE ONLY. DO NOT WASTE TIME DEBUGGING HERE. THE PRODUCUCTION FILE IS AT STOREADMIN/PAGES/WRITE-TO-INV.PHP//
// *************************************************************************//
session_start();

require_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// od_uid is order_details_id

$od_id = $_POST['od_id'];
// $od_id = 518;
$odsql = "SELECT ord.quantity, ord.emp_id FROM uniform_orders.ord_ref ord WHERE ord.order_details_id = $od_id";
$odstmt = $conn->prepare($odsql);
$getQty = $odstmt->execute();
// we get the quantity value from the order details and store the value to set a basis for counter below
$odstmt->store_result();
$odstmt->bind_result($value, $idvalue);
$odres = $odstmt->fetch();

var_dump($value);
var_dump($idvalue);
if ($getQty) {
    $count = $value;
    var_dump($od_id);
    while ($count != 0) {
        $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
        $invsql = "INSERT into uniform_orders.inventory (inv_UID, order_details_id, inv_emp_assigned, inv_requested_for, inv_received) VALUES ('$uid', $od_id, $idvalue, $idvalue, now())";
        $invstmt = $conn->prepare($invsql);
        $invstmt->execute();
        $count--;

        var_dump($count);
    }
}
