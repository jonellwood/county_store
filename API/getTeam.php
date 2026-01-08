<?php
// Created: 2024/09/12 13:12:49
// Last modified: 2026/01/08 12:54:18
// require_once '../data/storeConfig.php';
require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp_id = '4438';

$sql = "SELECT * from emp_sync where deptNumber = (
    SELECT deptNumber from emp_sync where empNumber = '$emp_id'
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
