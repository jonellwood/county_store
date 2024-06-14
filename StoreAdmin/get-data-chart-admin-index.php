<?php
header('Content-Type: application/json');
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// $empNumber = $_SESSION['empNumber'];
$sql = "SELECT d.dep_name as label, SUM(ord.line_item_total) as total
FROM ord_ref ord
JOIN departments d on ord.department = d.dep_num
GROUP BY department";

$result = mysqli_query($conn, $sql);

$data = array();

foreach ($result as $row) {
    $data[] = $row;
}
echo json_encode($data);