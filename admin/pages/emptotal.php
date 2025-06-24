<?php
include_once "DBConn.php";

$ORDID = $_GET["ORDID"];
$EMPID = $_GET["EMPID"];


$sql = "SELECT IFNULL(SUM(ord_ref.line_item_total), 0) as gToats FROM uniform_orders.ord_ref
WHERE (emp_id = $EMPID AND status='Received') OR (emp_id = $EMPID AND status='Ordered')";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total = $stmt->get_result();

$sql1 = "SELECT IFNULL(SUM(ord_ref.line_item_total), 0) as gToats1 FROM uniform_orders.ord_ref
WHERE emp_id = $EMPID AND status='Approved' AND product_id != '105'";
$stmt1 = $conn->prepare($sql1);
$stmt1->execute();
$total1 = $stmt1->get_result();

$data = array();

while ($row = $total->fetch_assoc()) {
    $sql2 = "SELECT o.order_id, o.customer_id, o.comment, o.created, o.grand_total, o.size_name, o.submitted_by, o.emp_id, o.department,
    o.product_id, o.quantity, o.status, o.size_id, o.color_id, o.order_details_id, o.order_placed, o.line_item_total, o.logo, o.dept_patch_place,
    o.product_name, o.product_price, o.product_code, o.vendor, e.empNumber, e.empName, e.email, e.deptName 
    FROM uniform_orders.ord_ref o 
    join emp_ref e on o.emp_id = e.empNumber 
    WHERE o.order_details_id = $ORDID
    AND e.seperation_date is null";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    $result = $stmt2->get_result();
    while ($row2 = $result->fetch_assoc()) {
        array_push($data, $row2);
    }
    array_push($data, $row);
}

while ($row1 = $total1->fetch_assoc()) {

    array_push($data, $row1);
}

echo json_encode($data);
