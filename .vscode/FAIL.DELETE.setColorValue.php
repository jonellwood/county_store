<?php
session_start();

$colorValue = $_GET['colorValue'];

$_SESSION['colorValue'] = $colorValue;

$data = "Status: 200";

echo json_encode($data);
