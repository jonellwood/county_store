<?php
// Created: 2024/09/12 13:12:49
// Last modified: 2024/09/24 08:42:22
// require_once '../data/storeConfig.php';
require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp_id = '4438';

$sql = "SELECT * from curr_emp_ref where deptNumber = (
    SELECT deptNumber from curr_emp_ref where empNumber = '$emp_id'
) order by empName ASC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
}
$stmt->execute();
$result = $stmt->get_result();
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
