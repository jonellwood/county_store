<?php
header('Content-Type: application/json');
session_start();
require_once "DBConn.php";
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());
// $empNumber = $_SESSION['empNumber'];
$sql = "SELECT department. SUM(grand_total_ as dep_total from ord_ref group by department";

$result = mysqli_query($conn, $sql);

$data = array();

// foreach ($result as $row) {
//     $data[] = $row;
// }
while ($row = mysqli_fetch_assoc($result))
    array_push($data, $row);
echo json_encode($data);