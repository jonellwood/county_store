<?php

session_start();
require_once "DBConn.php";

// $dept_id = $_REQUEST['dept_id'];
$ord_emp_id = $_SESSION['empNumber'];
$dept_id = $_POST['dept_id'];
$po_number = $_POST['POnumber'];
// $ord_emp_id = 4438;

// manually setting values for testing purposes
// $dept_id = 41515;

$idsql = "SELECT ord.order_details_id from uniform_orders.ord_ref ord WHERE ord.department = $dept_id AND ord.status='Approved'";
$idstmt = $conn->prepare($idsql);
$getList = $idstmt->execute();
$odres = $idstmt->get_result();
$data = array();

// Check if odres has any rows
if ($odres->num_rows > 0) {

    // Loop through the returned results
    foreach ($odres as $row) {

        // Add each item to the data array
        foreach ($row as $item) {
            array_push($data, $item);
        }
    }
}
// var_dump($data);
// We are subtracting 1 from the total count of data stored in $data since an array is 0 base
$count = (count($data) - 1);

// echo "<pre>";
// echo "initial count" . var_dump($count);
// echo var_dump($data);
// echo "</pre>";
// Loop through array of order_details_ids and update status 
while ($count >= 0) {
    $sql = "UPDATE uniform_orders.order_details SET status = 'Ordered', order_placed = now() WHERE order_details_id = $data[$count]";
    // Update status on order_details table for corresponding order_details_id
    $result = $conn->query($sql); // Execute query
    $count--;
}

// create a UID to be inserted into both tables for cross reference
$ordInstId = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));


// create the order isntance in the database
// $ordInstSql = "INSERT INTO uniform_orders.order_inst (order_inst_id, created_by_emp_num) VALUES ($ord_inst_id, $ord_emp_id);";
$ordInstSql = "INSERT INTO uniform_orders.order_inst (order_inst_id, created_by_emp_num, order_for_dept, po_number, order_inst_created) VALUES (?,?,?,?,NOW());";
$ordInstStmt = $conn->prepare($ordInstSql);
$ordInstStmt->bind_param("ssss", $db_order_inst_id, $db_created_by_emp_num, $db_order_for_dept, $db_po_number);
$db_order_inst_id = $ordInstId;
$db_created_by_emp_num = $ord_emp_id;
$db_order_for_dept = $dept_id;
$db_po_number = $po_number;
$createOrdInst = $ordInstStmt->execute();
// once created we are using the array of order_details_id's created above and will insert each one into the database associated with the UID generated above. In theory.
// confirmed the script work to this point - J.E. 3/17 1548 hours
// $orderItems = array_reverse($data);
//echo "<p> Line 61 data var </p>";
//echo var_dump($data);
//echo "<p> Line 63 orderItems var </p>";
//echo var_dump($orderItems);
if ($createOrdInst) {
    // $order_instance_id = $ordInstStmt->insert_id;
    if (!empty($data)) {
        $InstSql = "INSERT INTO uniform_orders.order_inst_order_details_id (order_inst_id, order_details_id) VALUES (?,?)";
        $InstStmt = $conn->prepare($InstSql);
        foreach ($data as $item) {
            //echo "<p> Line 71 </p>";
            // script failing here. var_dump($item['order_details_id']) returns NULL
            //echo var_dump($item);
            $InstStmt->bind_param("ss", $db_order_inst_id, $db_order_details_id);
            // $db_order_inst_id = $order_instance_id;
            $db_order_details_id = $item;
            $insertOrdList = $InstStmt->execute();
        }
    }
    header("location: orders-by-dept-for-admin.php");
}
