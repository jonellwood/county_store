<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
include('DBConn.php');

$data = [];
// $sql = "SELECT od.line_item_total, od.emp_id, od.department, od.order_details_id, od.order_created, od.product_id, d.dep_name
//         from uniform_orders.ord_ordered od
//         JOIN departments d on d.dep_num = od.department";

$sql = "SELECT ord.dep_name, ord.order_details_id, ord.rf_first_name, ord.rf_last_name, ord.created, ord.grand_total, ord.line_item_total, 
ord.quantity, ord.size_name, ord.product_code, ord.product_name, ord.logo, ord.dept_patch_place, ord.order_id, ord.order_details_id,
c.color as color_id
FROM uniform_orders.ord_ref ord
JOIN colors c on c.color_id = ord.color_id
WHERE ord.status = 'Ordered'
OR ord.status = 'Waiting on Customer'";
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
