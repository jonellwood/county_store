<?php

define('CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');
// $conn = mysqli_connect("localhost", "EmpOrderForm", "FwpIXaIf1jGCpjS5Banp", "uniform_orders");

// check connet
// if ($conn->connect_error) {
//     die("COnnection failed: " . $conn->connect_error);
// };

$host = "10.50.10.94";
$port = 3306;
$socket = "";
$user = "EmpOrderForm";
$password = "FwpIXaIf1jGCpjS5Banp";
$dbname = "uniform_orders";

// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());

//$con->close();