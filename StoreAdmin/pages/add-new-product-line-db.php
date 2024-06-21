<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
echo 'Hello there';
$product_id = strip_tags($_POST['product_id']);
$product_id = intval($product_id);
$vendor = strip_tags($_POST['addVendor']);
$vendor = intval($vendor);
$size = strip_tags($_POST['addSize']);
$size = intval($size);
$price = strip_tags($_POST['price']);
$price = floatval($price);

// echo "<pre>";
// echo $product_id;
// echo "<br />";
// echo $vendor;
// echo "<br />";
// echo $size;
// echo "<br />";
// echo $price;
// echo "<br />";
// echo "</pre>";

$db_product_id = $product_id;
$db_vendor_id = $vendor;
$db_size_id = $size;
$db_price = $price;

$sql = "INSERT into prices (product_id, vendor_id, size_id, price) VALUES (?,?,?,?);";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $db_product_id, $db_vendor_id, $db_size_id, $db_price);
$priceInsert = $stmt->execute();

if (!$priceInsert) {
    echo "Error: " . $stmt->error;
} else {
    // $priceInsertId = $stmt->insert_id;
    // echo "Inserted ID: " . $priceInsertId;
    header("Location: edit-new-product-line-ui.php");
}

$stmt->close();
$conn->close();
