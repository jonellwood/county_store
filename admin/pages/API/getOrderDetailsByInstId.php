<?php

include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$data = [];

$id = $_GET['id'];

$sql = "SELECT od.order_details_id, od.order_id, od.product_id, pn.name, od.quantity, s.size_name,
c.color, od.status, od.item_price, od.logo_fee, od.tax, od.line_item_total,
od.order_placed, od.logo, od.comment, od.dept_patch_place, od.emp_dept, concat(cus.first_name, ' ', cus.last_name) as customer_name,
od.bill_to_dept, od.bill_to_fy, od.order_created, od.status_id, od.last_updated 
from order_details od
join order_inst_order_details_id oi on oi.order_details_id = od.order_details_id
join colors c on c.color_id = od.color_id
join sizes_new s on s.size_id = od.size_id
join products_new pn on pn.product_id = od.product_id
join orders o on o.order_id = od.order_id
join customers cus on cus.customer_id = o.customer_id
where oi.order_inst_id ='" . $id . "';";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
}

echo json_encode($data);
