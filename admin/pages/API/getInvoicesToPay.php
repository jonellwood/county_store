<?php

include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$data = [];

// $sql = "SELECT oi.order_inst_id
// FROM order_inst oi
// WHERE oi.order_inst_created >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
// AND oi.is_paid = FALSE
// AND (
//     SELECT COUNT(*)
//     FROM order_inst_order_details_id oiodi
//     WHERE oiodi.order_inst_id = oi.order_inst_id
//     AND EXISTS (
//         SELECT 1
//         FROM order_details od
//         WHERE od.order_details_id = oiodi.order_details_id
//         AND od.status_id = 6
//     )
// ) = (
//     SELECT COUNT(*)
//     FROM order_inst_order_details_id oiodi
//     WHERE oiodi.order_inst_id = oi.order_inst_id
// );";
$sql = "SELECT oi.order_inst_id
FROM order_inst oi
WHERE oi.order_inst_created >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
-- AND oi.is_paid = FALSE
-- AND NOT EXISTS (
--     SELECT 1
--     FROM order_inst_order_details_id oiodi
--     JOIN order_details od ON od.order_details_id = oiodi.order_details_id
--     WHERE oiodi.order_inst_id = oi.order_inst_id
--     AND od.status_id != 6
-- )
order by oi.order_inst_created DESC;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->get_result();
if ($orders->num_rows > 0) {
    while ($row = $orders->fetch_assoc()) {
        array_push($data, $row);
    }
}
// echo json_encode($data);
$results = [];
foreach ($data as $order_inst_id) {
    // echo "<p>" . $order_inst_id['order_inst_id'] . "</p>";
    $sql = "SELECT oi.order_inst_id, oi.order_for_dept, oi.po_number, oi.order_inst_created, d.dep_name, SUM(od.line_item_total) as total 
                FROM uniform_orders.order_inst oi 
                JOIN departments d on d.dep_num = oi.order_for_dept 
                JOIN order_inst_order_details_id oiodi on oiodi.order_inst_id = oi.order_inst_id 
                JOIN order_details od on od.order_details_id = oiodi.order_details_id 
                WHERE oi.order_inst_id ='" . $order_inst_id['order_inst_id'] . "';";
    // echo "<p>" . $sql . "</p>";
    $stmt = $conn->prepare($sql);
    // $stmt->execute([strval($order_inst_id)]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($results, $row);
        }
    }
}

echo json_encode($results);
