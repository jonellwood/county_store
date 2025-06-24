<?php
include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$sql = "SELECT ord.department, d.dep_name
from ord_ref ord
JOIN departments d on d.dep_num = ord.department
group by ord.department";
$stmt = $conn->prepare($sql);
$stmt->execute();
$departments = $stmt->get_result();
$data = array();
while ($row = $departments->fetch_assoc()) {
    array_push($data, $row);
}
echo json_encode($data);
