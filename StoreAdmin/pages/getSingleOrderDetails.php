<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$id = $_GET['id'];
$sql = "SELECT order_details.order_details_id, order_details.order_id, orders.submitted_by, customers.emp_id,customers.department, customers.first_name as rf_first_name, customers.last_name as rf_last_name, 
order_details.product_id, order_details.quantity,order_details.color_id, 
order_details.line_item_total, order_details.logo, order_details.logo_fee, order_details.tax, order_details.item_price, order_details.dept_patch_place,products_new.name as product_name, products_new.code as product_code, vendors.name, sizes_new.size_name, order_details.bill_to_dept, colors.color as color_name,
departments.dep_name,order_details.bill_to_fy,orders.customer_id,order_details.price_id,order_details.status,order_details.status_id, 
prices.vendor_id, logos.id as logo_id,
ifnull(MAX(comments.submitted), 'none') as last_contact
from order_details  
LEFT JOIN comments on comments.order_details_id = order_details.order_details_id
JOIN orders on orders.order_id = order_details.order_id
JOIN customers on customers.customer_id = orders.customer_id
JOIN products_new on products_new.product_id = order_details.product_id
JOIN prices on prices.product_id = order_details.product_id
JOIN vendors on vendors.id = prices.vendor_id
JOIN sizes_new on sizes_new.size_id = order_details.size_id
JOIN colors on colors.color_id = order_details.color_id
JOIN departments on departments.dep_num = customers.department
JOIN logos on logos.image = order_details.logo COLLATE utf8_unicode_ci
where order_details.order_details_id =  $id;";
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