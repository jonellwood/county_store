<?php

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT pn.product_id, pn.name, COUNT(*) as count, pn.image
FROM order_details od
JOIN products_new pn on pn.product_id=od.product_id
JOIN products_producttype pt on pt.product_id = od.product_id
WHERE pt.producttype_id NOT IN (3,8)
GROUP BY od.product_id, pn.product_id, pn.name, pn.image
order by count DESC LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        $proImage = !empty($row["image"]) ? $row['image'] : 'demo-img.jpg';
        $data[] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'count' => $row['count'],
            'image' => $proImage,
        ];
    }
} else {
    echo "<p>No hot items to see here</p>";
}

echo json_encode($data);
