<?php
session_start();

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$search_param = $_GET['param'];
// $offset = $_GET['loaded'];

// WHERE requests.req_justification LIKE '%$string%'


// $sql = "SELECT * FROM products WHERE products.isactive = '1' AND products.name LIKE '%$search_param%' OR products.code LIKE '%$search_param%'";
$sql = "SELECT * FROM uniform_orders.products_new where products_new.name LIKE '%$search_param%'
or products_new.code like '%$search_param%'";
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    foreach ($result as $row) {
        $data[] = $row;
    }
} else {
    $data[0]["code"] = "Sorry, No";
    $data[0]["name"] = "Results Found";
    // $data[0]["price"] = "0";
    $data[0]["product_id"] = "0";
    $data[0]["image"] = "product-images/sorry.jpg";
    // $data[0]["code"] = "NO";
}


echo json_encode($data);
