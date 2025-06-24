<?php

session_start();
require_once "DBConn.php";

// $dept_id = $_REQUEST['dept_id'];
$ord_emp_id = $_SESSION['empNumber'];
$dept_id = $_POST['dept_id'];
// echo $dept_id;
$po_number = $_POST['POnumber'];
// echo $po_number;
// $po_number = 00000;
//$ord_emp_id = 4438;

// manually setting values for testing purposes
// $dept_id = 41515;

$stmt = $conn->prepare("
    SELECT order_details.order_details_id, prices.vendor_id
    FROM order_details
    JOIN prices ON prices.product_id = order_details.product_id
    WHERE (order_details.status_id = 1 OR order_details.status_id = 7)
      AND order_details.emp_dept = ?
      GROUP BY order_details.order_details_id
");
$stmt->bind_param("i", $dept_id); // Bind the department ID parameter
$stmt->execute();
$result = $stmt->get_result();

// Create an array of order_details_ids
$order_details_data = array();
while ($row = $result->fetch_assoc()) {
    $order_details_data[] = $row;
}
//echo json_encode($order_details_data);

// Build an efficient update query using IN clause
$order_details_ids = array_column($order_details_data, 'order_details_id');
$in_clause = implode(',', $order_details_ids);
$update_sql = "
    UPDATE uniform_orders.order_details
    SET status = 'Ordered', status_id = 4, order_placed = NOW()
    WHERE order_details_id IN ($in_clause)
";
$result = $conn->query($update_sql);
// echo $update_sql;
// create a UID to be inserted into both tables for cross reference
$ordInstId = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));

// create the order isntance in the database
// $ordInstSql = "INSERT INTO uniform_orders.order_inst (order_inst_id, created_by_emp_num) VALUES ($ord_inst_id, $ord_emp_id);";
$ordInstSql = "INSERT INTO uniform_orders.order_inst (order_inst_id, created_by_emp_num, order_for_dept, po_number, order_inst_created) VALUES (?,?,?,?,NOW());";
$ordInstStmt = $conn->prepare($ordInstSql);
$ordInstStmt->bind_param("ssss", $db_order_inst_id, $db_created_by_emp_num, $db_order_for_dept, $db_po_number);
$db_order_inst_id = $ordInstId;
// echo $db_order_inst_id;
// echo "<br>";
$db_created_by_emp_num = $ord_emp_id;
// echo $db_created_by_emp_num;
// echo "<br>";
$db_order_for_dept = $dept_id;
// echo $db_order_for_dept;
// echo "<br>";
$db_po_number = $po_number;
// echo $db_po_number;
// echo "<br>";

$createOrdInst = $ordInstStmt->execute();
// once created we are using the array of order_details_id's created above and will insert each one into the database associated with the UID generated above. In theory.
// confirmed the script work to this point - J.E. 3/17 1548 hours
// echo $data;
// var_dump($order_details_data);
if ($createOrdInst) {
    $order_instance_id = $ordInstId;
    if (!empty($order_details_data)) {
        $InstSql = "INSERT INTO uniform_orders.order_inst_order_details_id (order_inst_id, order_details_id, vendor_id) VALUES (?,?,?)";
        $InstStmt = $conn->prepare($InstSql);
        foreach ($order_details_data as $item) {
            //echo "<p> Line 71 </p>";
            // script failing here. var_dump($item['order_details_id']) returns NULL
            //echo var_dump($item);
            $InstStmt->bind_param("sss", $db_order_inst_id, $db_order_details_id, $db_vendor_id);
            $db_order_inst_id = $order_instance_id;
            $db_order_details_id = $item['order_details_id'];
            $db_vendor_id = $item['vendor_id'];
            $insertOrdList = $InstStmt->execute();
        }
    }
    header("location: orders-by-dept-for-admin.php");
}
