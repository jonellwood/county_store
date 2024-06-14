<?php

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$status = $_GET['status'];
$id = $_GET['id'];

$sql = "UPDATE uniform_orders.products SET isactive = $status WHERE product_id = $id";
$stmt = $conn->prepare($sql);
$stmt->execute();
