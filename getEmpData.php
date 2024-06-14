<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$empNum = $_GET['empNum'];

$sql = "SELECT empName, empNumber, deptName, deptNumber, email FROM uniform_orders.curr_emp_ref WHERE empNumber=$empNum";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
while ($row = $result->fetch_assoc()) {
    $_SESSION['captcha_text'] = $row['empNumber'];
    array_push($data, $row);
}

echo json_encode($data);
$conn->close();
