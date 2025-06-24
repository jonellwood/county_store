<?php
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$sql = "SELECT od.order_details_id, od.order_id, od.product_id, od.quantity, od.size_id, od.status, od.item_price, od.logo_fee, od.tax, 
od.line_item_total, od.order_placed, od.logo, od.comment, od.dept_patch_place, od.emp_dept, od.bill_to_dept,
o.created, o.submitted_by, 
er.empName as submitted_by_name, er.deptName,
p.name, p.code, p.vendor_id

FROM uniform_orders.order_details od
LEFT JOIN orders o on o.order_id = od.order_id
LEFT JOIN emp_ref er on o.submitted_by = er.empNumber
LEFT JOIN products p on p.product_id = od.product_id
WHERE od.status NOT IN ('Received', 'Denied')";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        array_push($data, $row);
    }
}

echo json_encode($data);