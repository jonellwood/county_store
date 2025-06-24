<?php 

header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$id = $_GET['id'];
$data = [];
$color_options = [];
$size_options = [];

$stmt = $conn->prepare("SELECT ps.size_id, sizes.size 
FROM products_sizes ps
JOIN sizes on sizes.size_id = ps.size_id 
where product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sizes_results = $stmt->get_result();
if ($sizes_results->num_rows > 0) {
    while ($row = $sizes_results->fetch_assoc()) {
        array_push($size_options, $row);
    }
    array_push($data, [
        "size_options" => $size_options
    ]);
}

$stmt = $conn->prepare("SELECT pc.color_id, colors.color 
FROM products_colors pc
JOIN colors on colors.color_id = pc.color_id 
where product_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$colors_results = $stmt->get_result();
if ($colors_results->num_rows > 0) {
    while ($row = $colors_results->fetch_assoc()) {
        array_push($color_options, $row);
    }
    array_push($data, [
        "color_options" => $color_options
    ]);
}


echo json_encode($data);