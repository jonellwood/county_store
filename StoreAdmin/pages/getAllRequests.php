<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT order_id, grand_total, created, concat(rf_first_name, ' ', rf_last_name) as requested_for, status, emp_id, department from ord_ref group by order_id order by order_id DESC;";
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
