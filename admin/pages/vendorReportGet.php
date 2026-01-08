<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
include('DBConn.php');

$uid = $_GET['uid'];

$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$data = [];
$sql = "SELECT order_inst_order_details_id.order_inst_id,
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
            order_details.comment,
            order_details.tax, 
            order_details.item_price AS pre_tax_price,
            order_details.order_placed, 
            order_inst_order_details_id.vendor_id, 
            prices.vendor_id,
            vendors.name AS vendor_name,
            order_inst.po_number, 
            orders.customer_id,
            customers.first_name AS rf_first_name,
            customers.last_name AS rf_last_name,
            order_details.order_id, 
            products_new.name AS product_name,
            products_new.code AS product_code,
            order_details.status_id,
            order_details.status,
            order_details.comment,
            departments.dep_name, 
            sizes_new.size_name,
            colors.color AS color_name,
            GROUP_CONCAT(comments.comment SEPARATOR ' || ') AS comments,
            GROUP_CONCAT(comments.submitted_by SEPARATOR ' || ') AS comment_submitters,
            GROUP_CONCAT(comments.submitted SEPARATOR ' || ') AS comment_submitted,
            GROUP_CONCAT(emp_sync.empName SEPARATOR ' || ') AS comment_sub_name 
        FROM
            uniform_orders.order_inst_order_details_id
        JOIN order_inst ON order_inst.order_inst_id = order_inst_order_details_id.order_inst_id
        JOIN order_details ON order_details.order_details_id = order_inst_order_details_id.order_details_id
        JOIN orders ON orders.order_id = order_details.order_id
        JOIN departments ON departments.dep_num = order_details.emp_dept
        JOIN customers ON customers.customer_id = orders.customer_id
        JOIN products_new ON products_new.product_id = order_details.product_id
        JOIN sizes_new ON order_details.size_id = sizes_new.size_id COLLATE utf8_unicode_ci
        JOIN colors ON colors.color_id = order_details.color_id
        JOIN prices ON prices.price_id = order_details.price_id
        JOIN vendors ON vendors.id = prices.vendor_id
        LEFT JOIN comments ON order_details.order_details_id = comments.order_details_id
        LEFT JOIN emp_sync on emp_sync.empNumber = comments.submitted_by
    WHERE
        order_inst_order_details_id.order_inst_id = '$uid'
    GROUP BY
        order_inst_order_details_id.order_details_id
    ORDER BY
        order_details.color_id";

$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }
}


header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
