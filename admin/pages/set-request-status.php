<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
};

include('DBConn.php');
$status_map = array(
    'Approved' => 1,
    'Denied' => 2,
    'Expired' => 3,
    'Ordered' => 4,
    'Pending' => 5,
    'Received' => 6,
    'Updated' => 7,
);
$status = $_POST['status'];
$status_id = $status_map[$status];
$id = $_POST['id'];
$comment = strip_tags($_POST['comment']);
$submitted_by = $_SESSION['empNumber'];

$sql = "UPDATE order_details set status = '$status', status_id = '$status_id' where order_details_id = '$id'";
$stmt = $conn->prepare($sql);
$update = $stmt->execute();

if ($update) {
    $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
    $commentSql = "INSERT into comments (id, order_details_id, comment, submitted_by) values ('$uid', '$id', '$comment', $submitted_by)";
    $commentStmt = $conn->prepare($commentSql);
    $commentAdd = $commentStmt->execute();
    if ($commentAdd) {
        header("Location: employeeRequests.php");
    }
}
