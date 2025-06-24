<?php
// Created: 2024/08/28 14:29:00
// Last Modified: 2024/12/23 08:15:17
include('DBConn.php');

$sql = "SELECT 
    oio.order_inst_id,
    SUM(od.line_item_total) as total,
    od.order_placed,
    d.dep_name
FROM 
    order_inst_order_details_id oio
JOIN 
    order_details od ON oio.order_details_id = od.order_details_id
JOIN 
	departments d on d.dep_num = od.emp_dept
GROUP BY 
    oio.order_inst_id
ORDER BY od.order_placed DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
if ($result->num_rows > 0) {
    // $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "order_inst_id" => $row["order_inst_id"],
            "total" => $row["total"],
            "order_placed" => $row["order_placed"],
            "dep_name" => $row["dep_name"]
        ];
    }
};

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
