<?php
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$id = $_GET['id'];

$data = [];
$product = [];
$current_colors = [];
$current_sizes = [];
$current_filters = [];
$all_colors = [];
$all_sizes = [];
$all_price_mods = [];
$all_gender_filters = [];
$all_type_filters = [];
$all_size_filters = [];
$all_sleeve_filters = [];
$all_filters = [];

$stmt = $conn->prepare("SELECT p.code, p.description, p.featured, p.isComm, p.isactive, p.name, p.price, p.price_size_mod, p.product_id, 
p.producttype as producttype_id, v.id as vendor_id, v.name as vendor, pt.producttype as producttype
from vendors v
LEFT JOIN products p on p.vendor_id = v.id 
LEFT JOIN producttypes pt on pt.productType_id = p.producttype
WHERE product_id = $id");
$stmt->execute();
$productDetails = $stmt->get_result();
if ($productDetails->num_rows > 0) {
    while ($row = $productDetails->fetch_assoc()) {
        array_push($product, $row);
    }
    array_push($data, [
        "product" => $product
    ]);
}

// Select color_ids and names
$stmt = $conn->prepare("SELECT c.color, c.color_id 
from products_colors pc
LEFT JOIN colors c on c.color_id = pc.color_id WHERE product_id = $id ORDER BY color ASC");
$stmt->execute();
$colorIds = $stmt->get_result();
if ($colorIds->num_rows > 0) {
    while ($row = $colorIds->fetch_assoc()) {
        array_push($current_colors, $row);
    }
    array_push($data, [
        "current_colors" => $current_colors
    ]);
}


// Select size_ids and names
$stmt = $conn->prepare("SELECT s.size, s.size_id 
from products_sizes ps
LEFT JOIN sizes s on s.size_id = ps.size_id WHERE product_id = $id");
$stmt->execute();
$sizeIds = $stmt->get_result();
if ($sizeIds->num_rows > 0) {
    while ($row = $sizeIds->fetch_assoc()) {
        array_push($current_sizes, $row);
    }
    array_push($data, [
        "current_sizes" => $current_sizes
    ]);
}

// Select current product filters
$stmt = $conn->prepare("SELECT gender_filter, type_filter, size_filter, sleeve_filter from products_filters WHERE product = $id");
$stmt->execute();
$filterIds = $stmt->get_result();
if ($sizeIds->num_rows > 0) {
    while ($row = $filterIds->fetch_assoc()) {
        array_push($current_filters, $row);
    }
    array_push($data, [
        "current_filters" => $current_filters
    ]);
}

// Get all colors and id's
$stmt = $conn->prepare("SELECT color, color_id, p_hex, s_hex, t_hex FROM colors ORDER BY color ASC");
$stmt->execute();
$allColors = $stmt->get_result();
if ($allColors->num_rows > 0) {
    while ($row = $allColors->fetch_assoc()) {
        array_push($all_colors, $row);
    }
    array_push($data, [
        "all_colors" => $all_colors
    ]);
}

// Get all sizes and id's
$stmt = $conn->prepare("SELECT size, size_id FROM sizes");
$stmt->execute();
$allSizes = $stmt->get_result();
if ($allSizes->num_rows > 0) {
    while ($row = $allSizes->fetch_assoc()) {
        array_push($all_sizes, $row);
    }
    array_push($data, [
        "all_sizes" => $all_sizes
    ]);
}

// get all gender filters
$stmt = $conn->prepare("SELECT id, filter from filters_gender");
$stmt->execute();
$allGenderFilters = $stmt->get_result();
if($allGenderFilters->num_rows > 0){
    while($row = $allGenderFilters->fetch_assoc()){
        array_push($all_gender_filters,$row);
    }
    array_push($all_filters, [ 
        "gender_filters" => $all_gender_filters
    ]);
}

// Get all type filters 
$stmt = $conn->prepare("SELECT id, filter from filters_type");
$stmt->execute();
$allTypeFilters = $stmt->get_result();
if($allTypeFilters->num_rows > 0){
    while($row = $allTypeFilters->fetch_assoc()){
        array_push($all_type_filters,$row);
    } 
    array_push($all_filters, [
        "type_filters" => $all_type_filters
    ]);
}

// Get all size filters 
$stmt = $conn->prepare("SELECT id, filter from filters_size");
$stmt->execute();
$allSizeFilters = $stmt->get_result();
if($allSizeFilters->num_rows > 0){
    while($row = $allSizeFilters->fetch_assoc()){
        array_push($all_size_filters,$row);
    }
    array_push($all_filters, [
        "size_filters" => $all_size_filters
    ]);
}

// Get all sleeve filters 
$stmt = $conn->prepare("SELECT id, filter from filters_sleeve");
$stmt->execute();
$allSleeveFilters = $stmt->get_result();
if($allSleeveFilters->num_rows > 0){
    while($row = $allSleeveFilters->fetch_assoc()){
        array_push($all_sleeve_filters,$row);
    } 
    array_push($all_filters, [
        "sleeve_filters" => $all_sleeve_filters
    ]);
}
array_push($data,[
    "all_filters" => $all_filters
]);

$stmt = $conn->prepare("SELECT id, price_mod FROM price_mods");
$stmt->execute();
$allMods = $stmt->get_result();
if ($allMods->num_rows > 0) {
    while ($row = $allMods->fetch_assoc()) {
        array_push($all_price_mods, $row);
    }
    array_push($data, [
        "all_mods" => $all_price_mods
    ]);
}

echo json_encode($data);