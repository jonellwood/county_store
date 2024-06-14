<?php

session_start();
header('Content-type: application/json');
echo "hiiiii";
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$code = $_GET['code'];
$name = $_GET['name'];
$description = $_GET['desc'];
$colors = explode(',', $_GET['colors']);
$sizes = explode(', ', $_GET['sizes']);

// $image  = $_GET['image'];
$image  = 'product-images/' . $code . '.jpg';
// $price = $_GET['price'];
$price = 1.00;
// $description = $_GET['description'];
// $description = "Update this description.";
// $producttype = $_GET['producttype'];
// We are going to pass a value of 0 here since that is not a real type. We can use that to find products that have been added but not updated.
$producttype = 0;
$featured = 0;
$isactive = 0;
// $vendor_id  = $_GET['vendor_id'];
// Because this was made for a webscrape from Reids I am leaving this as is for now. We can address it later if we scrape for another vendor
$vendor_id  = 4;
// leaving this as 9999 for now. When we edit price we will edit the price mod.
$price_size_mod = 9999;
$isComm = 0;
try {
    $sql = "INSERT INTO uniform_orders.products(code,name,image,price,description,producttype, featured, isactive, vendor_id) VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssiii", $code, $name, $image, $price, $description, $producttype, $featured, $isactive, $vendor_id);
    $stmt->execute();

    // Get the id of the last insert statement
    $insert_id = $conn->insert_id;

    $sql2 = "INSERT INTO uniform_orders.products_colors(product_id, color_id) VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql2);

    foreach ($colors as $color_id) {
        $color_id = intval($color_id);
        $stmt2->bind_param("ii", $insert_id, $color_id);
        $stmt2->execute();
    }

    // $sql3 = "INSERT INTO uniform_orders.products_sizes(product_id, size_id) VALUES (?, ?)";
    $sql3 = "INSERT INTO uniform_orders.products_sizes(product_id,size_id)VALUES(?,?)";
    $stmt3 = $conn->prepare($sql3);

    foreach ($sizes as $size_id) {
        // $size_id = intval($size_id);
        $stmt3->bind_param("ii", $insert_id, $size_id);
        $stmt3->execute();
    }

    $response_data = array(
        'insert_id' => $insert_id,
        'colors' => $colors,  // Assuming `$colors` still holds the original value
        'sizes' => $sizes,
        'colorsType' => gettype($colors),
        'sizesType' => gettype($sizes)
    );
    // $date = date('Y-m-d_H-i-s'); // Generate timestamp in the format YYYY-MM-DD_HH-MM-SS
    $res_file = "res_file.log";
    error_log("Response Data:" . json_encode($response_data) . "\n", 3, "$res_file");

    header('Content-Type: application/json');
    echo json_encode($response_data);
} catch (Exception $e) {
    header('Content-Type: application/json');
    // $date = date('Y-m-d_H-i-s'); // Generate timestamp in the format YYYY-MM-DD_HH-MM-SS
    $log_file = "error_log.log";
    error_log("Error: " . $e->getMessage() . "\n", 3, "$log_file");
    error_log("Code: " . $code . "\n", 3, "$log_file");
    error_log("Name: " . $name . "\n", 3, "$log_file");
    error_log("Colors: " . json_encode($colors) . "\n", 3, "$log_file");
    error_log("Sizes: " . json_encode($sizes) . "\n", 3, "$log_file");
    error_log("Res Data: " . json_encode($response_data) . "\n", 3, "$log_file");
    // Output error response
    // echo json_encode(array("success" => false, "error" => "An error occurred. Please try again later."));
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
}

// TODO- NEED TO ADD A SECONDARY INSERT STATEMENT THAT TAKES THE CREATED PRODUCT ID AND INSERTS IT INTO PRICES MODS TABLE WITH THE VALUE OF 0 SO THE EDIT PRODUCT TABLE WILL LOAD CORRECTLY