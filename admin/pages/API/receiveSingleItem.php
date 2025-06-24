<?php
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
$order_details_id = $_GET['id'];

$sql = "UPDATE order_details SET status = 'Received', status_id = 6 where order_details_id = '$order_details_id'";
$stmt = $conn->prepare($sql);
$updated = $stmt->execute();

if ($updated) {
    $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
    $comment = 'Item Received';
    $submitted_by = $_SESSION['empNumber'];
    $commentSql = "INSERT into comments (id, order_details_id, comment, submitted_by) values ('$uid', '$order_details_id', '$comment', $submitted_by)";
    $commentStmt = $conn->prepare($commentSql);
    $commentStmt->execute();
    header("Location: employeeRequests.php");
} else {
    header("Location: employeeRequests.php");
}
