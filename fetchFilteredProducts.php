<?php
session_start();

// if ($_SESSION['GOBACK'] == '') {
$_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
// }
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$producttype = $_GET['type'];
$genderFilter = $_GET['gender'];



$data = [];

$sql = "SELECT pf.id, pf.product as product_id, pf.gender_filter, pf.type_filter, pf.size_filter, pf.sleeve_filter, pn.name, pn.code, pn.image  
FROM uniform_orders.products_filters pf
JOIN products_new pn on pn.product_id = pf.product
WHERE (pf.gender_filter = $genderFilter OR pf.gender_filter = 3)
AND pn.product_type = $producttype
AND pn.keep = 1
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);