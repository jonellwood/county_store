<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$code = strip_tags($_POST['productCode']);
$name = strip_tags($_POST['productName']);
$price = strip_tags($_POST['price']);
$description = strip_tags($_POST['description']);
$producttype = strip_tags($_POST['productType']);
$isFeatured = strip_tags($_POST['isFeatured']);
$vendor_id = strip_tags($_POST['vendor_id']);
$price_size_mod = strip_tags($_POST['price_size_mod']);
$imgName = "product-images/" . $code . ".jpg";
$gender_filter = strip_tags($_POST['g_filter']);
$type_filter = strip_tags($_POST['t_filter']);
$size_filter = strip_tags($_POST['s_filter']);
$sleeve_filter = strip_tags($_POST['a_filter']);


$prodSql = "INSERT into uniform_orders.products (code, name, image, price, description, producttype, featured, vendor_id, price_size_mod) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$prodStmt = $conn->prepare($prodSql);
$prodStmt->bind_param("sssdsiiii", $db_code, $db_name, $db_image, $db_price, $db_description, $db_producttype, $db_featured, $db_vendor_id, $db_price_size_mod);
$db_code = $code;
$db_name = $name;
$db_image = $imgName;
$db_price = $price;
$db_description = $description;
$db_producttype = $producttype;
$db_featured = $isFeatured;
$db_vendor_id = $vendor_id;
$db_price_size_mod = $price_size_mod;


$insertProduct = $prodStmt->execute();

// Inserting new product into database
if ($insertProduct) {
    $productID = $prodStmt->insert_id;

    // Inserting product sizes
    $sql = "INSERT into products_sizes (product_id, size_id) VALUES (?,?)";
    $stmt = $conn->prepare($sql);
    foreach ($_POST['sizes'] as $key => $value) {
        $stmt->bind_param('ii', $param_product_id, $param_size_id);
        $param_product_id = $productID;
        $param_size_id = $_POST['sizes'][$key];
        $insertSize = $stmt->execute();
    }

    // Inserting product colors
    if ($insertSize) {
        $sql = "INSERT into products_colors (product_id, color_id) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        foreach ($_POST['colors'] as $key => $value) {
            $stmt->bind_param('ii', $cparam_product_id, $cparam_color_id);
            $cparam_product_id = $productID;
            $cparam_color_id = $_POST['colors'][$key];
            $insertColor = $stmt->execute();
        }
        // Inserting product filters
        if ($insertColor) {
            $sql = "INSERT into products_filters (product, gender_filter, type_filter, size_filter, sleeve_filter) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiiii', $fparam_product_id, $fparam_gender, $fparam_type, $fparam_size, $fparam_sleeve);
            $fparam_product_id = $productID;
            $fparam_gender = $gender_filter;
            $fparam_type = $type_filter;
            $fparam_size = $size_filter;
            $fparam_sleeve = $sleeve_filter;
            $insertFilters = $stmt->execute();
        }
        if ($insertFilters) {
            // header("location: KN6EVPXT3B9FHT1TEC22.php");
            if (isset($_FILES['files'])) {
                $errors = array();

                foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                    $file_name = $_FILES['files']['name'][$key];
                    $file_tmp = $_FILES['files']['tmp_name'][$key];
                    // start adding image upload 
                    $imagename = $_FILES['productImage']["name"];
                    $tempname = $_FILES['productImage']['tmp_name'];
                    $folder = "product-images/" . $imagename;
                    // end inmage upload section
                    $desired_dir = "spec-sheets/";
                    if (empty($errors) == true) {
                        if (is_dir($desired_dir) == false) {
                            mkdir("$desired_dir", 0700);
                        }
                        if (is_dir("$desired_dir/" . $file_name) == false) {
                            move_uploaded_file($file_tmp, "spec-sheets/" . $file_name);
                        } else {
                            $new_dir = "user_data/" . $file_name . time();
                            rename($file_tmp, $new_dir);
                        }

                        if (move_uploaded_file($tempname, "product-images/" . $imagename)) {
                            echo $folder;
                        } else {
                            echo "Image updload failed";
                        }
                    } else {
                        print_r($errors);
                    }
                }
                if (empty($error)) {
                    header("location: KN6EVPXT3B9FHT1TEC22.php");
                }
            }
        }
    }
}
