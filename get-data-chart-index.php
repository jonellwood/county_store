<?php
header('Content-Type: application/json');
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$empNumber = $_SESSION['empNumber'];
$sql = "SELECT monthname(created) as label, SUM(line_item_total) as total
FROM ord_ref
JOIN departments on ord_ref.department = departments.dep_num
WHERE departments.dep_head = $empNumber;
GROUP BY year(created), month(created)";

$result = mysqli_query($conn, $sql);

$data = array();

foreach ($result as $row) {
    $data[] = $row;
}
echo json_encode($data);