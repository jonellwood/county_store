<?php
header('Content-Type: application/json');
session_start();
require_once "./pages/DBConn.php";
$empNumber = $_SESSION["empNumber"];

$sql = "SELECT monthname(created) as label, SUM(line_item_total) as total
FROM ord_ref
JOIN departments on ord_ref.department = departments.dep_num
WHERE (departments.dep_head = $empNumber AND status = 'Ordered' ) OR (departments.dep_head = $empNumber AND status = 'Received')
GROUP BY year(created), month(created)";

$result = mysqli_query($conn, $sql);

$data = array();

foreach ($result as $row) {
    $data[] = $row;
}
echo json_encode($data);
