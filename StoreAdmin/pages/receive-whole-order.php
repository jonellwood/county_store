<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
};

include('DBConn.php');

$order_id = $_GET['order_id'];

$sql = "UPDATE order_details SET status = 'Received' where order_id = '$order_id'";
$stmt = $conn->prepare($sql);
$updated = $stmt->execute();

if ($updated) {
    $line_items_to_receive = array();

    $usql = "SELECT order_details_id from order_details where order_id = '$order_id'";
    $ustmt = $conn->prepare($usql);
    $ustmt->execute();
    $orderIdList = $ustmt->get_result();
    if ($orderIdList->num_rows > 0) {
        while ($row = $orderIdList->fetch_assoc()) {
            array_push($line_items_to_receive, $row);
        }
    }
    foreach ($line_items_to_receive as $item) {
        $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
        $order_details_id = $item['order_details_id'];
        $comment = 'Items Received';
        $submitted_by = $_SESSION['empNumber'];
        $sql = "INSERT INTO comments (id, order_details_id, comment, submitted_by) VALUES ('$uid', '$order_details_id','$comment', '$submitted_by')";

        if ($conn->query($sql) === TRUE) {
            echo "Record inserted successfully for order_details_id: $order_details_id<br>";
        } else {
            echo "Error inserting record: " . $conn->error . "<br>";
        }
    }
}
