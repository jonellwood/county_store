<?php

include_once "DBConn.php";

$sql = "SELECT oi.order_inst_id, oi.order_details_id, od.line_item_total, o.order_id, cast(od.item_price as decimal(10,2)) as item_price, orin.order_inst_created, d.dep_name
FROM uniform_orders.order_inst_order_details_id oi
JOIN order_details od on od.order_details_id = oi.order_details_id
JOIN order_inst orin on orin.order_inst_id = oi.order_inst_id
JOIN orders o on o.order_id = od.order_id
JOIN departments d on orin.order_for_dept = d.dep_num";
// echo "<p>Say something Im giving up on you</p>";

$stmt = $conn->prepare($sql);
// echo "<p> stmt </p>";
// echo var_dump($stmt);
$stmt->execute();
$res = $stmt->get_result();
// echo "<p> res </p>";
// echo var_dump($res);
$data = array();

// while ($row = $res->fetch_assoc()) {
//     array_push($data, $row);
//     array_push($data, floatval($row['item_price']));
// }
while ($row = $res->fetch_assoc()) {
    array_push($data, [
        'dep_name' => $row['dep_name'],
        'item_price' => floatval($row['item_price']),
        'line_item_total' => $row['line_item_total'],
        'order_details_id' => $row['order_details_id'],
        'order_id' => $row['order_id'],
        'order_inst_created' => $row['order_inst_created'],
        'order_inst_id' => $row['order_inst_id']
    ]);
}
// echo "<p>data</p>";
// echo var_dump($data);
echo json_encode($data);