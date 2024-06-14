<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$data = [];
$sql = "SELECT p1.code AS p1_code, p1.price as p1_price, p1.product_id as p1_id, p1.vendor_id as p1_vendor,
p2.code AS p2_code, p2.price AS p2_price, p2.product_id as p2_id, p2.vendor_id as p2_vendor
FROM products p1
INNER JOIN products p2 ON p1.code != p2.code
WHERE p1.code LIKE '%-%'  
  AND p2.code LIKE CONCAT('%', SUBSTRING(p1.code, INSTR(p1.code, '-') + 1))
GROUP BY p1.product_id
ORDER BY p1_code
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
