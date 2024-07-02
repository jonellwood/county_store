<?php

/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/02/2024
Purpose: Fetch all the filter options from the database and returns them as JSON.
Includes:    config.php
*/
// session_start();

// if ($_SESSION['GOBACK'] == '') {
// $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
// // }
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$data = [];
$gf = [];
$sf = [];
$sl = [];
$tf = [];

$sql = "SELECT id, filter FROM filters_gender";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($gf, $row);
    }
    array_push($data, [
        "gender_filters" => $gf
    ]);
}

$sql = "SELECT id, filter FROM filters_size";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($sf, $row);
    }
    array_push($data, [
        "size_filters" => $sf
    ]);
}

$sql = "SELECT id, filter FROM filters_sleeve";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($sl, $row);
    }
    array_push($data, [
        "sleeve_filters" => $sl
    ]);
}

$sql = "SELECT id, filter FROM filters_type";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($tf, $row);
    }
    array_push($data, [
        "type_filters" => $tf
    ]);
}


echo json_encode($data);
