<?php
// Created: 2025/02/28 12:24:45
// Last modified: 2025/02/28 13:12:40
session_start();
include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function bindNullableParam($stmt, $param, $value)
{
    if ($value === null) {
        $stmt->bind_param($param, $value, PDO::PARAM_NULL);
    } else {
        $stmt->bind_param($param, $value);
    }
}
$order_id = $_GET['id'];

$sql = "UPDATE order_details SET status = 'Received', status_id = 6 where order_id = '$order_id'";
$stmt = $conn->prepare($sql);
$updated = $stmt->execute();

if ($updated) {
    $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
    $comment = 'Order Received';
    $submitted_by = $_SESSION['empNumber'];
    $commentSql = "INSERT into comments (id, order_id, comment, submitted_by) values ('$uid', '$order_id', '$comment', $submitted_by)";
    $commentStmt = $conn->prepare($commentSql);
    $commentStmt->execute();
    // header("Location: orders-to-be-received.php");
    echo json_encode(array('success' => true));
} else {
    // header("Location: orders-to-be-received.php");
    echo json_encode(array('success' => false));
}
