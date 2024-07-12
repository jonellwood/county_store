<?php

include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$data = [];
$prodFilterData = [];
$gf = [];
$sf = [];
$tf = [];
$lf = [];

$sql = "SELECT * from prod_filter_ref";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->get_result();
if ($products->num_rows > 0) {
    while ($row = $products->fetch_assoc()) {
        array_push($prodFilterData, $row);
    }
    array_push(
        $data,
        [
            'product' => $prodFilterData
        ]
    );
}

$sql = "SELECT * from filters_gender";
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

$sql = "SELECT * from filters_size";
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

$sql = "SELECT * from filters_type";
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

$sql = "SELECT * from filters_sleeve";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($lf, $row);
    }
    array_push($data, [
        "sleeve_filters" => $lf
    ]);
}






echo json_encode($data);
