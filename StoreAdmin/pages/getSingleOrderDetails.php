<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$id = $_GET['id'];
$sql = "SELECT ord_ref.order_details_id, ord_ref.order_id, ord_ref.submitted_by, ord_ref.emp_id, 
ord_ref.department, ord_ref.rf_first_name, ord_ref.rf_last_name, ord_ref.product_id, ord_ref.quantity,
ord_ref.color_id, ord_ref.line_item_total, ord_ref.logo, ord_ref.product_price, ord_ref.dept_patch_place,
ord_ref.product_name, ord_ref.product_code, ord_ref.vendor, ord_ref.size_name,  ord_ref.status, ord_ref.bill_to_dept,
ord_ref.dep_name, ifnull(MAX(comments.submitted), 'none') as last_contact
from ord_ref 
LEFT JOIN comments on comments.order_details_id = ord_ref.order_details_id
where ord_ref.order_details_id = $id;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$order = $stmt->get_result();
$data = array();
if ($order->num_rows > 0) {
    while ($row = $order->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);
