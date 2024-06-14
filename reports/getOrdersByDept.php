<?php

include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$department = $_GET['department'];
$statusQ = $_GET['status'];
// $orderby = $_GET['orderby'];
$sql = "SELECT order_id, order_details_id, concat(rf_first_name, ' ', rf_last_name) as requestedFor, product_code, size_name, color_id, quantity,
product_price, line_item_total, logo, dept_patch_place, created, status, vendor
FROM uniform_orders.ord_ref
WHERE department = $department
AND $statusQ
ORDER BY emp_id, vendor, product_id, size_id, color_id, logo, dept_patch_place;";
// ORDER BY '$orderby';";
// echo json_encode($sql);

$stmt = $conn->prepare($sql);
$stmt->execute();
$report = $stmt->get_result();
$data = array();
if ($report->num_rows > 0) {
    while ($row = $report->fetch_assoc()) {
        array_push($data, $row);
    }
}
// else {
//     array_push($data, [
//         'color_id' => 'Nothing',
//         'size_id' => 'found',
//     ]);
// }
echo json_encode($data);