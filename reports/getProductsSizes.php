<?php
include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$sql = "SELECT p.product_id, p.code, p.name, 
       JSON_ARRAYAGG(s.size) AS sizes
FROM products p
JOIN products_sizes ps ON ps.product_id = p.product_id
JOIN sizes s ON s.size_id = ps.size_id
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
