<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$id = $_GET['id'];
// $sql = "SELECT * from ord_ref where order_id = $id AND product_code !=105 ;";
$sql = "SELECT r.order_id AS order_id,
od.order_details_id AS order_details_id,
r.customer_id AS customer_id,
r.created AS created,
r.grand_total AS grand_total,
r.submitted_by AS submitted_by,
c.emp_id AS emp_id,
c.department AS department,
c.first_name AS rf_first_name,
c.last_name AS rf_last_name,
dr.dep_head AS dep_head,
od.product_id AS product_id,
od.quantity AS quantity,
od.status AS status,
od.color_id AS color_id,
co.color AS color_name,
od.size_id AS size_id,
si.size_name AS size_name,
od.order_placed AS order_placed,
od.line_item_total AS line_item_total,
od.item_price AS pre_tax_price,
od.tax AS tax,
od.logo_fee AS logo_fee,
od.logo AS logo,
od.item_price AS product_price,
od.comment AS comment,
od.dept_patch_place AS dept_patch_place,
od.bill_to_dept AS bill_to_dept,
od.bill_to_fy AS bill_to_fy,
p.name AS product_name,
p.code AS product_code,
p.image AS product_image,
v.name AS vendor,
v.id AS vendor_id,
v.vendor_number_finance AS vendor_number_finance,
er.first_name AS requested_by_name,
er.last_name AS requested_by_last,
dr.dep_name AS dep_name 
from orders r 
left join customers c on c.customer_id = r.customer_id 
left join order_details od on r.order_id = od.order_id 
join products_new p on od.product_id = p.product_id
join prices pr on pr.product_id = od.product_id
join vendors v on pr.vendor_id = v.id 
join sizes_new si on od.size_id = si.size_id 
join colors co on od.color_id = co.color_id 
left join departments dr on c.department = dr.dep_num 
left join customers er on er.emp_id = r.submitted_by 
where od.order_id = $id
group by od.order_details_id
";
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