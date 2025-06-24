<?php 
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$c_id = intval($_GET['c_id']) ?? null;
$s_id = intval($_GET['s_id']) ?? null;
$c_color = intval($_GET['c_color']) ?? null;
$c_size = intval($_GET['c_size']) ?? null;




if ($s_id !== null && $c_id !== null && $c_color !== null && $c_size !== null) {
    // Query the products_colors table to chrck for color match
    $stmt = $conn->prepare("SELECT color_id FROM products_colors WHERE product_id = ?");
    $stmt->bind_param("i", $s_id);
    $stmt->execute();
    $colors_result = $stmt->get_result();
    $colors = [];
    while ($row = $colors_result->fetch_assoc()) {
        $colors[] = $row['color_id'];
    }

    if (in_array($c_color, $colors)) {
        // Color match found, procceed to check for size match
        $stmt = $conn->prepare("SELECT size_id FROM products_sizes WHERE product_id = ?");
        $stmt->bind_param("i", $s_id);
        $stmt->execute();
        $sizes_result = $stmt->get_result();
        $sizes = [];
        while ($row = $sizes_result->fetch_assoc()) {
            $sizes[] = $row['size_id'];
        }

        if (in_array($c_size, $sizes)) {

            $stmt = $conn->prepare("SELECT price, name FROM products where product_id = ?");
            $stmt->bind_param("i", $s_id);
            $stmt->execute();
            $price_result = $stmt->get_result();
            $price = null;
            while ($row = $price_result->fetch_assoc()) {
                $price =$row['price'];
                $name = $row['name'];
            }

            $response = array(
                'message' => "We have a size match on $c_size and color match on $c_color.",
                'status' => 200,
                'selected_price' => $price,
                's_id' => $s_id,
                's_name' => $name
            );
        } else {
            $stmt = $conn->prepare("SELECT price, name FROM products where product_id = ?");
            $stmt->bind_param("i", $s_id);
            $stmt->execute();
            $price_result = $stmt->get_result();
            $price = null;
            while ($row = $price_result->fetch_assoc()) {
                $price =$row['price']; 
                $name = $row['name'];
            }

            $response = array(
                'message' => "The selected product does not have a matching size to the currently selected size.",
                'status' => 404,
                'selected_price' => $price,
                's_id' => $s_id,
                's_name' => $name
            );
        }
    } else {
        $stmt = $conn->prepare("SELECT price, name FROM products where product_id = ?");
            $stmt->bind_param("i", $s_id);
            $stmt->execute();
            $price_result = $stmt->get_result();
            $price = null;
            while ($row = $price_result->fetch_assoc()) {
                $price =$row['price']; 
                $name = $row['name'];
            }
            
        $response = array(
            'message' => "The selected product does not have an Exact Matching color to the currently selected color. Similar colors may be available.",
            'status' => 404,
            'selected_price' => $price,
            's_id' => $s_id,
            's_name' => $name
        );
        
    }
} else {
    // Return error message if any value is missing
   $response = array(
        'error' => 'Missing values',
        'status' => 400
    );
}

echo json_encode($response);