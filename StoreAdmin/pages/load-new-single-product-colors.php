<?php
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$q = $_GET['id'];
$id = intval($q);

$data = [];
// $product = [];
$current_colors = [];
$all_colors = [];

$sql = "SELECT * from products_colors where product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product_details = $stmt->get_result();
if ($product_details->num_rows > 0) {
    while ($row = $product_details->fetch_assoc()) {
        array_push($current_colors, $row);
    }
    array_push($data, [
        "current_colors" => $current_colors
    ]);
}

$stmt = $conn->prepare("SELECT * from colors");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        array_push($all_colors, [
            'color_id' => $row['color_id'],
            'color_name' => $row['color'],
            'p_hex' => $row['p_hex'],
            's_hex' => $row['s_hex'],
            't_hex' => $row['t_hex']
        ]);
    }
    array_push($data, [
        array_push($data, [
            'all_colors' => $all_colors
        ])
    ]);
}

echo json_encode($data);
