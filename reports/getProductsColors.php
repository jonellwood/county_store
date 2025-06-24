<?php
include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
  or die('Could not connect to the database server' . mysqli_connect_error());

// $sql = "SELECT JSON_OBJECT(
//   'products', JSON_ARRAYAGG(
// 	JSON_OBJECT(
//       'code', p.code,
//       'name', p.name,
//       'product_id', p.product_id,
//       'colors', (
//         SELECT JSON_ARRAYAGG(c.color)
//         FROM products_colors pc
//         JOIN colors c ON c.color_id = pc.color_id
//         WHERE pc.product_id = p.product_id
//      )
//     )
//   )
// ) AS json_data
// FROM products p
// WHERE p.isActive = true AND p.code <> 'boots'
// GROUP BY p.product_id, p.code, p.name;";

$sql = "SELECT p.product_id, p.code, p.name, 
       JSON_ARRAYAGG(c.color) AS colors
FROM products p
JOIN products_colors pc ON pc.product_id = p.product_id
JOIN colors c ON c.color_id = pc.color_id
WHERE p.isActive = true AND p.code <> 'boots'
GROUP BY p.product_id, p.code, p.name
ORDER BY p.code;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$report = $stmt->get_result();
$data = array();
while ($row = $report->fetch_assoc()) {
  array_push($data, $row);
}
echo json_encode($data);
