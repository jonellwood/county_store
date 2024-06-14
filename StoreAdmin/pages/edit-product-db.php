<?php

session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$product_id = intval(strip_tags($_POST['product_id']));
$code = strip_tags($_POST['code']);
$price = floatval(strip_tags($_POST['price']));
$description = strip_tags($_POST['description']);
$producttype = intval(strip_tags($_POST['producttypeSelect']));
$isFeatured = intval(strip_tags($_POST['isFeatured']));
$isActive = intval(strip_tags($_POST['isActive']));
$vendor = intval(strip_tags($_POST['vendorSelect']));
$price_size_mod = intval(strip_tags($_POST['priceModSelect']));
$isComm = intval(strip_tags($_POST['isComm']));
$gender_filter = intval(strip_tags($_POST['gender_filter']));
$type_filter = intval(strip_tags($_POST['type_filter']));
$size_filter = intval(strip_tags($_POST['size_filter']));
$sleeve_filter = intval(strip_tags($_POST['sleeve_filter']));
echo var_dump($gender_filter);
echo "<br>";
echo var_dump($type_filter);
echo "<br>";
echo var_dump($size_filter);
echo "<br>";
echo var_dump($sleeve_filter);
echo "<br>";


$sql = "UPDATE products SET price = ?, description = ?, producttype = ?, featured = ?, isactive = ?, vendor_id = ?, price_size_mod = ?, isComm = ? WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsiiiiiii", $price, $description, $producttype, $isFeatured, $isActive,  $vendor, $price_size_mod, $isComm, $product_id);
$updateProduct = $stmt->execute();

if ($updateProduct) {
    $d_sql = "DELETE from products_colors where product_id = ?";
    $d_stmt = $conn->prepare($d_sql);
    $d_stmt->bind_param("i", $product_id);
    $deleted = $d_stmt->execute();
    if ($deleted) {
        $sql = "INSERT into products_colors (product_id, color_id) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        foreach ($_POST['colorCheckbox'] as $key => $value) {
            $stmt->bind_param('ii', $cparam_product_id, $cparam_color_id);
            $cparam_product_id = $product_id;
            $cparam_color_id = $_POST['colorCheckbox'][$key];
            $insertColor = $stmt->execute();
        }
        if ($insertColor) {
            echo "color updated";
            $d_sql = "DELETE from products_sizes where product_id = ?";
            $d_stmt = $conn->prepare($d_sql);
            $d_stmt->bind_param("i", $product_id);
            $deleted = $d_stmt->execute();
            if($deleted){
                $sql = "INSERT into products_sizes (product_id, size_id) VALUES (?,?)";
                $stmt = $conn->prepare($sql);
                foreach ($_POST['sizeCheckbox'] as $key => $value) {
                    $stmt->bind_param('ii', $sparam_product_id, $sparam_size_id);
                    $sparam_product_id = $product_id;
                    $sparam_size_id = $_POST['sizeCheckbox'][$key];
                    $insertSize = $stmt->execute();
                }
                if ($insertSize){
                    echo "size updated";
                    $sql = "UPDATE products_filters set gender_filter = ?, type_filter = ?, size_filter = ?, sleeve_filter = ? WHERE product = ?";
                    // echo "<br/>";
                    // echo "<pre>";
                    // echo var_dump($sql);
                    // echo "</pre>";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('iiiii', $fparam_gender, $fparam_type, $fparam_size, $fparam_sleeve, $fparam_product_id);
                    echo "<br/>";
                    echo "<pre>";
                    echo var_dump($stmt);
                    echo "</pre>";
                    $fparam_product_id = $product_id;
                    $fparam_gender = $gender_filter;
                    $fparam_type = $type_filter;
                    $fparam_size = $size_filter;
                    $fparam_sleeve = $sleeve_filter;
                    $updateFilters = $stmt->execute();
                }
                if ($updateFilters) {
                    // echo "Filters Updated";
                    header('Location:edit-product-ui.php');
                }
            } 
        }
        
    }
}