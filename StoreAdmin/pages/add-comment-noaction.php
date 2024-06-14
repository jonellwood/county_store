<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
};

include('DBConn.php');

$id = $_POST['id'];
$comment = strip_tags($_POST['comment']);
$submitted_by = $_SESSION['empNumber'];
$uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));

$sql = "INSERT into comments (id, order_details_id, comment, submitted_by) values ('$uid', '$id', '$comment', $submitted_by)";
$stmt = $conn->prepare($sql);
$res = $stmt->execute();
if ($res) {
    header("Location: employeeRequests.php");
} else {
    echo "Something failed. Please return <a href='/'>Home</a> and try again.";
}
