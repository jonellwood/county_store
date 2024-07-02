<?php
session_start();

// if ($_SESSION['GOBACK'] == '') {
$_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
// }
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// $producttype = $_GET['type'];
$genderFilter = $_GET['gender'];

$data = [];

$sql = "SELECT pf.id, pf.product as product_id, pf.gender_filter, pf.type_filter, pf.size_filter, pf.sleeve_filter, pn.name, pn.code, pn.image, pr.size_id  
FROM uniform_orders.products_filters pf
JOIN products_new pn on pn.product_id = pf.product
JOIN prices pr on pn.product_id = pr.product_id
WHERE (pf.gender_filter = $genderFilter OR pf.gender_filter = 3)
AND pr.size_id BETWEEN 8 AND 25
GROUP BY product_id
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
