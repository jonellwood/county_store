<?php

session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$id = $_GET['id'];
$p_id = $_GET['p_id'];

$data = [];
$request_data = [];
$product_data = [];
$similar_products = [];
$logos = [];

$sql = "SELECT od.order_details_id, od.order_id, od.product_id, od.quantity, od.size_name as size, 
od.size_id as size_id, od.color_id as color, c.color_id,
od.status, od.order_placed, od.vendor, od.vendor_id, od.product_code,
od.product_name, od.pre_tax_price as price, od.logo_fee, od.tax, od.line_item_total, od.order_placed, 
od.logo, od.comment, od.dept_patch_place, od.department, od.bill_to_dept,
CONCAT(od.rf_first_name, ' ', od.rf_last_name) as req_for_name
FROM uniform_orders.ord_ref od
JOIN colors c on c.color = od.color_id COLLATE utf8_unicode_ci
WHERE od.order_details_id = $id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
// $data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($request_data, $row);
    }
    array_push($data, [
        "request_data" => $request_data
    ]);
}

$stmt = $conn->prepare("SELECT * from products WHERE product_id = $p_id");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $producttype = $row['producttype'];
        array_push($product_data, $row);
    }
    array_push($data, [
        "product_data" => $product_data
    ]);
    if($producttype){
        $stmt = $conn->prepare("SELECT * from products where producttype = $producttype and isactive = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()){
                array_push($similar_products, $row);
            }
            array_push($data, [
                "similar_products" => $similar_products
            ]);
        }
    }
}

$stmt = $conn->prepare("SELECT * from logos WHERE isactive = 1");
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        array_push($logos, $row);
    }
    array_push($data, [
        "logos" => $logos
    ]);
}

echo json_encode($data);