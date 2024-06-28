<?php

session_start();

require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// Insert item into the products_colors table for the given product
$product = $_POST['product'];

foreach ($_POST['colors'] as $key => $value) {
    $sql = "INSERT into products_colors (product_id, color_id) VALUES (?,?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $param_product_id, $param_color_id);

        // set the parameter values
        $param_product_id = $product;
        $param_color_id = $_POST['colors'][$key];

        // execute statement
        if (mysqli_stmt_execute($stmt)) {
            echo 'updated';
            header("location: update-product-colors.php");
        } else {
            echo 'BARRRRRFFFF'; // Error message (of sorts)
        }
    }
}
