<?php
$hostName = "10.50.10.94";
$userName = "EmpOrderForm";
$password = "FwpIXaIf1jGCpjS5Banp";
$databaseName = "uniform_orders";
$conn = new mysqli($hostName, $userName, $password, $databaseName);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


// This connects to the DB that PETE made