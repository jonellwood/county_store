<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$producttype = $_GET['pt'];

$sql = "SELECT pf.product, gf.filter as gender, tf.filter as type, sf.filter as size, af.filter as sleeve, p.name, p.producttype
FROM products_filters pf
JOIN products p ON pf.product = p.product_id
JOIN filters_gender gf ON pf.gender_filter = gf.id
JOIN filters_type tf ON pf.type_filter = tf.id
JOIN filters_size sf ON pf.size_filter = sf.id
JOIN filters_sleeve af ON pf.sleeve_filter = af.id
WHERE p.producttype = $producttype";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$g_filters = array();
$t_filters = array();
$s_filters = array();
$a_filters = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // echo "<pre>";
        // echo print_r($row);
        // echo "</pre>";
        foreach ($row as $filter) {
            if (!in_array($row['gender'], $g_filters)) {
                array_push($g_filters, $row['gender']);
            };
            if (!in_array($row['type'], $t_filters)) {
                array_push($t_filters, $row['type']);
            };
            if (!in_array($row['size'], $s_filters)) {
                array_push($s_filters, $row['size']);
            }
            if (!in_array($row['sleeve'], $a_filters)) {
                array_push($a_filters, $row['sleeve']);
            }
        }
    }
    // echo "<pre>";
    // echo "g-filters";
    // echo print_r($g_filters);
    // echo "t-filters";
    // echo print_r($t_filters);
    // echo "s-filters";
    // echo print_r($s_filters);
    // echo "a-filters";
    // echo print_r($a_filters);
    // echo "</pre>";
}