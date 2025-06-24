<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
?>


<?php
require_once 'DBConn.php';
$DEPT = $_SESSION["department"];
$USER = $_SESSION["userName"];
$ROLE = $_SESSION["role_name"];
$empNumber = $_SESSION["empNumber"];


$allSql = "SELECT SUM(line_item_total) as sub_total, count(order_details_id) as sub_count 
    FROM uniform_orders.ord_submitted
    WHERE department = $DEPT;
SELECT SUM(line_item_total) as app_total, count(order_details_id) as app_count 
    FROM uniform_orders.ord_approved
    WHERE department = $DEPT;
SELECT SUM(line_item_total) as ord_total, count(order_details_id) as ord_count 
    FROM uniform_orders.ord_ordered
    WHERE department = $DEPT;
SELECT SUM(line_item_total) as com_total, count(order_details_id) as com_count 
    FROM uniform_orders.ord_completed
    WHERE department = $DEPT";

$data = array();
if ($conn->multi_query($allSql)) {
    do {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                // printf("%s\n", $row[0]);
                array_push($data, $row);
            }
            $result->free();
        }
        if (!$conn->more_results()) {
            break;
        }
    } while ($conn->next_result());
}

echo json_encode($data);

$conn->close();