<?php if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include('DBConn.php');

$prod_id = $_GET['prod_id'];

$sql = "SELECT products_new.code, products_new.name, products_new.image, products_new.description, products_new.product_type
FROM uniform_orders.products_new
where products_new.product_id = $prod_id AND products_new.keep = 1;";

$product_data = array();
$size_data = array();
$color_data = array();
$logo_data = array();
$stmt = $conn->prepare($sql);
$stmt->execute();
$prod = $stmt->get_result();
if ($prod->num_rows > 0) {
    while ($prod_row = $prod->fetch_assoc()) {
        // $prod_price_mod = $prod_row['price_size_mod'];
        // var_dump($prod_price_mod);
        array_push($product_data, $prod_row);
    }
}
if ($prod) {
    $size_sql = "SELECT pr.price_id, pr.vendor_id, pr.price, pr.size_id, sn.size_name
    FROM uniform_orders.prices pr
    JOIN sizes_new sn ON sn.size_id = pr.size_id
    JOIN (
        SELECT size_id, MAX(price) AS max_price
        FROM uniform_orders.prices
        WHERE product_id = $prod_id
        GROUP BY size_id
    ) subquery ON pr.size_id = subquery.size_id AND pr.price = subquery.max_price
        WHERE pr.product_id = $prod_id
        ORDER BY pr.size_id, pr.vendor_id
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
    $logo_sql = "SELECT * from logos where isactive = 1 AND iscomm = 0";
    $logo_stmt = $conn->prepare($logo_sql);
    $logo_stmt->execute();
    $prod_logo_data = $logo_stmt->get_result();
    if ($prod_logo_data->num_rows > 0) {
        while ($prod_logo_data_row = $prod_logo_data->fetch_assoc()) {
            array_push($logo_data, $prod_logo_data_row);
        }
    }
};
$data = array();
$data['product'] = array($product_data);
$data['size'] = array($size_data);
$data['color'] = array($color_data);
$data['logo'] = array($logo_data);

echo json_encode($data);