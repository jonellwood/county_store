<?php

include('DBConn.php');

// $sql = "SELECT order_inst.order_inst_id, order_inst.created_by_emp_num, order_inst.order_for_dept, order_inst.order_inst_created, departments.dep_name 
// FROM uniform_orders.order_inst
// JOIN departments ON order_inst.order_for_dept = departments.dep_num
// ORDER BY order_inst.order_inst_created
// ";
$sql = "SELECT order_inst.order_inst_id, order_inst.created_by_emp_num, order_inst.order_for_dept, order_inst.order_inst_created, departments.dep_name, 
OIOD.order_details_id, order_details.order_id, orders.grand_total
FROM uniform_orders.order_inst
JOIN order_inst_order_details_id OIOD on OIOD.order_inst_id = order_inst.order_inst_id
JOIN order_details on order_details.order_details_id = OIOD.order_details_id
JOIN orders on orders.order_id = order_details.order_id
JOIN departments ON order_inst.order_for_dept = departments.dep_num
GROUP BY order_inst.order_inst_id
ORDER BY order_inst.order_inst_created";

$reportList = [];

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($reportList, $row);
    }
} else {
    [
        "response" => "No reports found"
    ];
}

echo json_encode($reportList);
