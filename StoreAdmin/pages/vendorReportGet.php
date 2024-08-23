<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
include('DBConn.php');
// $uid = filter_input(INPUT_GET, 'UID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$uid = $_GET['uid'];
// $uid = '18bcfc29077905ec78bd29bb7646da5323d23547196';
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$data = [];
$sql = "SELECT 
        order_inst_order_details_id.order_inst_id,
        order_inst_order_details_id.order_details_id, 
        order_details.product_id,
        order_details.price_id,
        order_details.size_id, 
        order_details.quantity, 
        order_details.color_id,
        order_details.line_item_total, 
        order_details.logo, 
        order_details.dept_patch_place,
        order_details.logo_fee, 
        order_details.tax, 
        order_details.item_price as pre_tax_price, 
        order_inst_order_details_id.vendor_id, 
        vendors.name,
        order_inst.po_number, 
        dep_ref.dep_name, 
        orders.customer_id,
        customers.first_name as rf_first_name,
        customers.last_name as rf_last_name,
        order_details.order_id, 
        products_new.name as product_name,
        products_new.code as product_code,
        order_details.status_id,
        order_details.status,
        order_details.comment,
        sizes_new.size_name
    FROM
        uniform_orders.order_inst_order_details_id
    RIGHT JOIN
        ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
    RIGHT JOIN
        dep_ref ON ord_ref.department = dep_ref.dep_num
    JOIN 
        order_inst on order_inst.order_inst_id = order_inst_order_details_id.order_inst_id
    JOIN vendors on vendors.id = order_inst_order_details_id.vendor_id
    JOIN order_details on order_details.order_details_id = order_inst_order_details_id.order_details_id
    JOIN orders on orders.order_id = order_details.order_id
    JOIN customers on customers.customer_id = orders.customer_id
    JOIN products_new on products_new.product_id = order_details.product_id
    JOIN prices on order_details.product_id = prices.product_id and prices.size_id = order_details.size_id
    JOIN sizes_new on order_details.size_id = sizes_new.size_id COLLATE utf8_unicode_ci
    WHERE
        order_inst_order_details_id.order_inst_id = '$uid'
    ORDER BY
        ord_ref.color_id";

$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }
}

//var_dump($data);
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);