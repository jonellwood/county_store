<?php
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$q = $_GET['id'];
$id = intval($q);

$data = [];
$current_colors = [];
$all_colors = [];
$current_product = [];

// Fetch current colors
$sql = "SELECT * from products_colors where product_id =?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product_details = $stmt->get_result();
if ($product_details->num_rows > 0) {
    while ($row = $product_details->fetch_array()) {
        array_push($current_colors, $row);
    }
    $data[] = [
        "current_colors" => $current_colors
    ];
} else {
    array_push($current_colors, null);
    $data[] = [
        "current_colors" => $current_colors
    ];
}

// Fetch all colors
$stmt = $conn->prepare("SELECT * from colors");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        $all_colors[] = [
            'color_id' => $row['color_id'],
            'color_name' => $row['color'],
            'p_hex' => $row['p_hex'],
            's_hex' => $row['s_hex'],
            't_hex' => $row['t_hex']
        ];
    }
    $data[] = [
        'all_colors' => $all_colors
    ];
}

// Fetch current product from products_new
$sql = "SELECT * from products_new where product_id =?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product_data = $stmt->get_result();
if ($product_data->num_rows > 0) {
    while ($row = $product_data->fetch_assoc()) {
        $current_product[] = $row;
    }
    $data[] = [
        "current_product" => $current_product
    ];
}

echo json_encode($data);
