<?php
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * FROM uniform_orders.ord_ref ord
JOIN (SELECT * from departments) d on ord.department = d.dep_num
WHERE status = 'Ordered'
group by product_code ASC, size_id ASC, color_id ASC";
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
