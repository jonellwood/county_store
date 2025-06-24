<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$uid = $_GET['uid'];

$depNumberFromQ = array();

$sql = "SELECT dep_num FROM uniform_orders.inv_ref WHERE inv_UID = '$uid' ";
$stmt = $conn->prepare($sql);
$getDept = $stmt->execute();
$stmt->store_result();
$stmt->bind_result($value);
$res = $stmt->fetch();

if ($getDept) {

    $deptEmpListSql = "SELECT empNumber, empName FROM uniform_orders.emp_ref WHERE seperation_date IS NULL AND deptNumber = $value ";

    $listData = array();

    $deptListStmt = $conn->prepare($deptEmpListSql);
    $deptListStmt->execute();
    $deptEmpListResult = $deptListStmt->get_result();
    if ($deptEmpListResult->num_rows > 0) {
        while ($deptRow = $deptEmpListResult->fetch_assoc()) {
            array_push($listData, $deptRow);
        }
    };
    echo json_encode($listData);
}