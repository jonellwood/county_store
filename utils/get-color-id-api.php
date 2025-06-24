<?php
session_start();
header('Content-type: application/json');
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
// echo "hi";

// echo "I hate web dev";
$color = $_GET['color'];
// $color = 'red';
$data = [];

$sql = "SELECT color_id, color from colors where color = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $color);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    array_push($data, $row);
}
echo json_encode($data, http_response_code(200));
