<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Fetch single product details for product_details page. Details include available colors, sizes, and a price for each size.
Includes:   config.php for database connection

*/
require_once "config.php";
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());
function getDatabaseConnection($host, $user, $password, $dbname)
{
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    return $conn;
}

$product_id = isset($_GET['id']) ? strip_tags($_GET['id']) : null;

$product_data = [];
$price_data = [];
$color_data = [];
$logo_data = [];
$data = [];


$sql = "SELECT p.product_id, p.code, p.name, p.image, p.description
from products_new p WHERE p.product_id=?";

$conn = getDatabaseConnection($host, $user, $password, $dbname);
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['error' => 'SQL prepare failed: ' . $conn->error]));
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
if ($product_result->num_rows > 0) {
    $product_data = $product_result->fetch_all(MYSQLI_ASSOC);
    $data['product_data'] = $product_data;
} else {
    $data = ["product_message" => "No product found with the given ID"];
}
$product_result->free();
$stmt->close();

if (!isset($data['product_message'])) {
    $sql = "SELECT pr.price_id, pr.vendor_id, pr.price, pr.size_id, sn.size_name
        FROM uniform_orders.prices pr
        JOIN sizes_new sn ON sn.size_id = pr.size_id
        JOIN (
            SELECT size_id, MAX(price) AS max_price
            FROM uniform_orders.prices
            WHERE product_id = ?
            GROUP BY size_id
        ) subquery ON pr.size_id = subquery.size_id AND pr.price = subquery.max_price
            WHERE pr.product_id = ?
            ORDER BY pr.size_id, pr.vendor_id;";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(['error' => 'SQL prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("ii", $product_id, $product_id);
    $stmt->execute();
    $price_result = $stmt->get_result();
    if ($price_result->num_rows > 0) {
        $price_data = $price_result->fetch_all(MYSQLI_ASSOC);
        $data['price_data'] = $price_data;
    } else {
        $data['price_message'] = "No price data found with the given ID";
    }
    $price_result->free();
    $stmt->close();
}
if (!isset($data['price_message'])) {
    $sql = "SELECT pc.color_id, c.color, c.p_hex, c.s_hex, c.t_hex
    FROM products_colors pc
    JOIN colors c on c.color_id = pc.color_id WHERE product_id = ? ORDER BY c.color ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(["error" => 'SQL prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $color_result = $stmt->get_result();
    if ($color_result->num_rows > 0) {
        $color_data = $color_result->fetch_all(MYSQLI_ASSOC);
        $data['color_data'] = $color_data;
    } else {
        $data['color_message'] = "No color data found with the given ID";
    }
    $color_result->free();
    $stmt->close();
}

if (!isset($data['color_message'])) {
    $sql = "SELECT id, logo_name, image, description FROM uniform_orders.logos where isactive = 1 AND iscomm = 0;";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die(json_encode(["error" => 'Your butt failed: ' . $conn->error]));
    }

    $stmt->execute();
    $logo_result = $stmt->get_result();
    if ($logo_result->num_rows > 0) {
        $logo_data = $logo_result->fetch_all(MYSQLI_ASSOC);
        $data['logo_data'] = $logo_data;
    } else {
        $data['logo_message'] = "No logo data found";
    }
    $logo_result->free();
    $stmt->close();
}
// Close the connection
$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
