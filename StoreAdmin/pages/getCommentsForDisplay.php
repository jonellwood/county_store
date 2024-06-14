<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
};

include('DBConn.php');

$id = $_GET['id'];
$sql = "SELECT * from comment_ref where order_details_id = '$id' order by submitted_by ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$comments = $stmt->get_result();
$data = array();

if ($comments->num_rows > 0) {
    while ($row = $comments->fetch_assoc()) {
        array_push($data, $row);
    }
} else {
    array_push($data, [
        "comment" => "No comments found.",
        "submitted_by" => "N/A",
        "submitted" => "N/A"
    ]);
}

echo json_encode($data);
