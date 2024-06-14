<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
include('DBConn.php');

$data = [];
// $sql = "SELECT od.line_item_total, od.emp_id, od.department, od.order_details_id, od.order_created, od.product_id, d.dep_name
//         from uniform_orders.ord_ordered od
//         JOIN departments d on d.dep_num = od.department";

$sql = "SELECT dep_name, order_details_id, rf_first_name, rf_last_name, created, grand_total, line_item_total, 
quantity, color_id, size_name, product_code, product_name, logo, dept_patch_place, order_id, order_details_id
FROM uniform_orders.ord_ref
WHERE status = 'Ordered'
OR status = 'Waiting on Customer'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
} else {
    echo "No Records found";
};

echo json_encode($data);
