<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
include('DBConn.php');
// $uid = filter_input(INPUT_GET, 'UID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$uid = $_GET['uid'];
// $uid = '18bcfc29077905ec78bd29bb7646da5323d23547196';
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$data = [];
$sql = "SELECT order_inst_order_details_id.order_inst_id,order_inst_order_details_id.order_details_id, ord_ref.product_code,
            ord_ref.size_name, ord_ref.quantity, ord_ref.color_id,
            ord_ref.line_item_total, ord_ref.logo, ord_ref.dept_patch_place,
            ord_ref.logo_fee, ord_ref.tax, ord_ref.pre_tax_price, ord_ref.vendor_id, ord_ref.vendor,
            order_inst.po_number, dep_ref.dep_name, ord_ref.rf_first_name, ord_ref.rf_last_name, ord_ref.product_name, ord_ref.status, ord_ref.order_id, ord_ref.comment
        FROM
            uniform_orders.order_inst_order_details_id
        RIGHT JOIN
            ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
        RIGHT JOIN
            dep_ref ON ord_ref.department = dep_ref.dep_num
        JOIN 
            order_inst on order_inst.order_inst_id = order_inst_order_details_id.order_inst_id
        WHERE
            order_inst_order_details_id.order_inst_id = '$uid'
        -- GROUP BY
        --     order_inst_order_details_id.order_inst_id, ord_ref.product_code, ord_ref.size_name, 
        --     ord_ref.quantity, ord_ref.color_id, ord_ref.line_item_total, ord_ref.logo, 
        --     ord_ref.dept_patch_place, ord_ref.logo_fee, ord_ref.pre_tax_price, ord_ref.vendor_id, 
        --     order_inst.po_number, dep_ref.dep_name
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
