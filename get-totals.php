<?php
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$allSql = "SELECT SUM(line_item_total) as sub_total, count(order_details_id) as sub_count 
    FROM uniform_orders.ord_submitted
    WHERE department = 41515;
SELECT SUM(line_item_total) as app_total, count(order_details_id) as app_count 
    FROM uniform_orders.ord_approved
    WHERE department = 41515;
SELECT SUM(line_item_total) as ord_total, count(order_details_id) as ord_count 
    FROM uniform_orders.ord_ordered
    WHERE department = 41515;
SELECT SUM(line_item_total) as com_total, count(order_details_id) as com_count 
    FROM uniform_orders.ord_completed
    WHERE department = 41515";

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
