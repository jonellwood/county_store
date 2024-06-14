<?php

include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$id = $_GET['id'];

$sql = "SELECT * from products where product_id = $id ";
$stmt = $conn->prepare($sql);
$stmt->execute();
$departments = $stmt->get_result();
$data = array();

// var_dump($mod);
while ($row = $departments->fetch_assoc()) {
    array_push($data, $row);
}
// echo "<pre>";
// var_dump($data);
// echo "</pre>";

$mod = $data[0]['price_size_mod'];
$ssql = "SELECT * from price_mods where price_mod = $mod";
$sstmt = $conn->prepare($ssql);
$sstmt->execute();
$mods = $sstmt->get_result();
while ($srow = $mods->fetch_assoc()) {
    array_push($data, $srow);
};


echo json_encode($data);
