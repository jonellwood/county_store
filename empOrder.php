<?php
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$ORDID = $_GET["ORDID"];
$sql = "SELECT * FROM uniform_orders.ord_ref o join(select * from emp_ref) e on o.emp_id = e.empNumber WHERE o.order_details_id = $ORDID";
$stmt = $conn->prepare($sql);
$stmt->execute();
$getOrder = $stmt->get_result();
$data = array();

while ($row = $getOrder->fetch_assoc()) {
    array_push($data, $row);
}




echo json_encode($data);