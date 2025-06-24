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
$all_products = [];
// TODO update this to new products, colors, and prices
$sql1 = "SELECT p.code, p.description, p.featured, p.isComm, p.isactive, p.name, p.price, p.price_size_mod, p.product_id, 
p.producttype as producttype_id, v.id as vendor_id, v.name as vendor, pt.producttype as producttype
from vendors v
LEFT JOIN products p on p.vendor_id = v.id 
LEFT JOIN producttypes pt on pt.productType_id = p.producttype
WHERE product_id = $id";

// TODO see if this needs updated
$sql2 = "SELECT c.color, c.color_id from products_colors pc
LEFT JOIN colors c on c.color_id = pc.color_id WHERE product_id = $id ORDER BY color ASC";

// TODO see if this is still needed
$sql3 = "SELECT s.size, s.size_id from products_sizes ps
LEFT JOIN sizes s on s.size_id = ps.size_id WHERE product_id = $id";

$sql4 = "SELECT gender_filter, type_filter, size_filter, sleeve_filter from products_filters WHERE product = $id";

$sql5 = "SELECT color, color_id, p_hex, s_hex, t_hex FROM colors ORDER BY color ASC";

$sql6 = "SELECT size, size_id FROM sizes";

$sql7 = "SELECT id, filter from filters_gender";

$sql8 = "SELECT id, filter from filters_type";

$sql9 = "SELECT id, filter from filters_size";

$sql10 = "SELECT id, filter from filters_sleeve";

$sql11 = "SELECT id, price_mod FROM price_mods";
// TODO almost certain this is no longer needed
$sql12 = "SELECT product_id, code, name FROM products WHERE isactive = 1";

if ($conn->multi_query("$sql1; $sql2; $sql3; $sql4; $sql5; $sql6; $sql7; $sql8; $sql9; $sql10; $sql11; $sql12")) {
    $resultSetCounter = 0;

    do {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                switch ($resultSetCounter) {
                    case 0:
                        $product[] = $row;
                        break;
                    case 1:
                        $current_colors[] = $row;
                        break;
                    case 2:
                        $current_sizes[] = $row;
                        break;
                    case 3:
                        $current_filters[] = $row;
                        break;
                    case 4:
                        $all_colors[] = $row;
                        break;
                    case 5:
                        $all_sizes[] = $row;
                        break;
                    case 6:
                        $all_gender_filters[] = $row;
                        break;
                    case 7:
                        $all_type_filters[] = $row;
                        break;
                    case 8:
                        $all_size_filters[] = $row;
                        break;
                    case 9:
                        $all_sleeve_filters[] = $row;
                        break;
                    case 10:
                        $all_price_mods[] = $row;
                        break;
                    case 11:
                        $all_products[] = $row;
                        break;
                }
            }
            $result->free();
            $resultSetCounter++;
        } else {
            echo "Error fetching results: " . $conn->error;
        }
    } while ($conn->more_results() && $conn->next_result());
    if ($conn->errno) {
        echo "Multi-query execution error: " . $conn->error;
    }
} else {
    echo "Error executing multi-query: " . $conn->error;
}
$data['product'] = $product;
$data['current_colors'] = $current_colors;
$data['current_sizes'] = $current_sizes;
$data['current_filters'] = $current_filters;
$data['all_colors'] = $all_colors;
$data['all_sizes'] = $all_sizes;
$data['all_gender_filters'] = $all_gender_filters;
$data['all_type_filters'] = $all_type_filters;
$data['all_size_filters'] = $all_size_filters;
$data['all_sleeve_filters'] = $all_sleeve_filters;
$data['all_price_mods'] = $all_price_mods;
$data['all_products'] = $all_products;

echo json_encode($data);
