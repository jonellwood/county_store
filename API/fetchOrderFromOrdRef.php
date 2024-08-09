<?php

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$id = $_GET['id'];

$sql = "SELECT 
ord.order_id,
ord.order_details_id,
ord.customer_id,
ord.created,
ord.grand_total,
ord.submitted_by,
ord.emp_id,
ord.department,
ord.rf_first_name,
ord.rf_last_name,
ord.dep_head,
ord.product_id,
ord.quantity,
ord.status,
ord.color_id,
ord.color_name,
ord.size_id,
ord.size_name,
ord.order_placed,
ord.line_item_total,
ord.pre_tax_price,
ord.tax,
ord.logo_fee,
ord.logo,
ord.product_price,
ord.comment,
ord.dept_patch_place,
ord.bill_to_dept,
ord.bill_to_fy,
ord.product_name, 
ord.product_code,
ord.product_image,
ord.vendor,
ord.vendor_id,
ord.vendor_number_finance,
ord.requested_by_name,
ord.requested_by_last,
ord.dep_name,
cus.email
FROM ord_ref ord
join customers cus on cus.customer_id = ord.customer_id 
WHERE ord.order_id=$id";
$stmt = $conn->prepare($sql);

$stmt->execute();
$result = $stmt->get_result();

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
} else {
    echo $id;
}

echo json_encode($data);