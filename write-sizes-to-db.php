<?php

session_start();

require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// Retreive product ID from the POST request
$product = $_POST['product'];


// Loop throught sizes array in POST request and insert into the database
foreach ($_POST['sizes'] as $key => $value) {
    $sql = 'INSERT into products_sizes (product_id, size_id) VALUES (?,?)';
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $param_product_id, $param_size_id);

        // Assign parameters for product and size IDs
        $param_product_id = $product;
        $param_size_id = $_POST['sizes'][$key];

        // Execute query and update or redirect
        if (mysqli_stmt_execute($stmt)) {
            echo 'updated';
            header("location: update-product-sizes.php");
        } else {
            echo "size messed up";
        }
    }
}
