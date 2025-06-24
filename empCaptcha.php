<?php
session_start();

$empNum = $_GET['emp_num'];

$_SESSION['captcha_text'] = $empNum;


$data = "Status: 200";

echo json_encode($data);