<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$code = strip_tags($_POST['productCode']);
$name = strip_tags($_POST['productName']);
$price = strip_tags($_POST['price']);
$description = strip_tags($_POST['description']);
$producttype = strip_tags($_POST['productType']);


$prodSql = "INSERT into uniform_orders.products (code, name, price, description, producttype) VALUES (?, ?, ?, ?, ?)";
$prodStmt = $conn->prepare($prodSql);
$prodStmt->bind_param("ssdsi", $db_code, $db_name, $db_price, $db_description, $db_producttype);
$db_code = $code;
$db_name = $name;
$db_price = $price;
$db_description = $description;
$db_producttype = $producttype;
$insertProduct = $prodStmt->execute();

if ($insertProduct) {
    $productID = $prodStmt->insert_id;

    foreach ($_POST['sizes'] as $key => $value) {
        $sizeSql = "INSERT into products_sizes (product_id, size_id) VALUES (?,?)";
        if ($sizeStmt = mysqli_prepare($conn, $sizeSql)) {
            mysqli_stmt_bind_param($sizeStmt, 'ii', $param_product_id, $param_size_id);

            $param_product_id = $productID;
            $param_size_id = $_POST['sizes'][$key];
            $insertSize = $sizeStmt->execute();
        }
    }
    if ($insertSize) {
        foreach ($_POST['colors'] as $ckey => $cvalue) {
            $colorSql = "INSERT into products_colors (product_id, color_id) VALUES (?,?)";
            if ($colorStmt = mysqli_prepare($conn, $colorSql)) {
                mysqli_stmt_bind_param($colorStmt, 'ii', $cparam_product_id, $cparam_color_id);

                $cparam_product_id = $productID;
                $cparam_color_id = $_POST['colors'][$ckey];
                $insertColor = $colorStmt->execute();
            }
        }
    }
}
if ($insertColor) {
    header("location: add-product-to-db.php");
}
