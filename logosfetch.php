<?php
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * FROM uniform_orders.logos WHERE isactive = 1 and iscomm = 0;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);