<?php
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp = $_GET['emp'];

$sql = "SELECT * from ord_ref where emp_id = $emp";

$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->get_result();
$data = array();
if ($orders->num_rows > 0) {
    while ($row = $orders->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);
