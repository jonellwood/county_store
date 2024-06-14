<?php if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include('DBConn.php');

$prod_id = $_GET['prod_id'];

$sql = "SELECT products.code, products.price_size_mod 
FROM uniform_orders.products
where product_id = $prod_id";

$product_data = array();
$size_data = array();
$color_data = array();
$price_data = array();
$stmt = $conn->prepare($sql);
$stmt->execute();
$prod = $stmt->get_result();
if ($prod->num_rows > 0) {
    while ($prod_row = $prod->fetch_assoc()) {
        $prod_price_mod = $prod_row['price_size_mod'];
        // var_dump($prod_price_mod);
        array_push($product_data, $prod_row);
    }
}
if ($prod) {
    $size_sql = "SELECT products_sizes.size_id, sizes.size 
                FROM uniform_orders.products_sizes
                JOIN sizes on sizes.size_id = products_sizes.size_id 
                where products_sizes.product_id = $prod_id
                ;";
    $size_stmt = $conn->prepare($size_sql);
    $size_stmt->execute();
    $prod_size_data = $size_stmt->get_result();
    if ($prod_size_data->num_rows > 0) {
        while ($prod_size_data_row = $prod_size_data->fetch_assoc()) {
            array_push($size_data, $prod_size_data_row);
        }
    }
}
if ($prod_size_data) {
    $color_sql = "SELECT products_colors.color_id, colors.color 
        FROM uniform_orders.products_colors
        JOIN colors on colors.color_id = products_colors.color_id 
        where products_colors.product_id = $prod_id";
    $color_stmt = $conn->prepare($color_sql);
    $color_stmt->execute();
    $prod_color_data = $color_stmt->get_result();
    if ($prod_color_data->num_rows > 0) {
        while ($prod_color_data_row = $prod_color_data->fetch_assoc()) {
            array_push($color_data, $prod_color_data_row);
        }
    }
}
if ($prod_color_data) {
    $price_sql = "SELECT * from price_mods where price_mod = $prod_price_mod";
    $price_stmt = $conn->prepare($price_sql);
    $price_stmt->execute();
    $prod_price_data = $price_stmt->get_result();
    if ($prod_price_data->num_rows > 0) {
        while ($prod_price_data_row = $prod_price_data->fetch_assoc()) {
            array_push($price_data, $prod_price_data_row);
        }
    }
};
$data = array();
$data['product'] = array($product_data);
$data['size'] = array($size_data);
$data['color'] = array($color_data);
$data['price'] = array($price_data);

echo json_encode($data);
