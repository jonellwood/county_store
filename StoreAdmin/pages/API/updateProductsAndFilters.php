<?php

include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// echo "hello";
$product_id = $_REQUEST["p"];
// echo $product_id;
$filter_id = $_REQUEST["f"];
// echo $filter_id;
switch ($filter_id) {
    case 1:
        $updateColumn = 'gender_filter';
        break;
    case 2:
        $updateColumn = 'size_filter';
        break;
    case 3:
        $updateColumn = 'type_filter';
        break;
    case 4:
        $updateColumn = 'sleeve_filter';
        break;
}
// echo $updateColumn;
$new_value = $_REQUEST["n"];

// echo $new_value;

$sql = "UPDATE products_filters SET $updateColumn = ? WHERE product = ?";
// echo $sql;
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_value, $product_id);
// echo $sql;
$stmt->execute();

echo json_encode(["success" => true]);
