<?php
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT ord_ref.department, departments.dep_name
FROM uniform_orders.ord_ref
JOIN departments on departments.dep_num = ord_ref.department
GROUP BY ord_ref.department";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
