<?php

// script that gets an order_details_id value and set a $_SESSION variable  with that value
session_start();
// include_once "../../config.php";
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());
$id = $_GET['id'];
$_SESSION['order_details_id'] = $id;

echo json_encode($_SESSION['order_details_id']);
