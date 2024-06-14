<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$id = $_GET['id'];
$sql = "SELECT * from ord_ref where order_id = $id AND product_code !=105 ;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$order = $stmt->get_result();
$data = array();
if ($order->num_rows > 0) {
    while ($row = $order->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);
