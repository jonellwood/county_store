<?php
session_start();
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$empNumber = $_SESSION["empNumber"];

$data = [];
if (($_SESSION['empNumber'] == '6865') || ($_SESSION['empNumber'] == '4438')
) {
    $sql = "SELECT order_details.bill_to_fy, order_details.order_id, 
    order_details.order_created as created,
    orders.grand_total, concat(customers.first_name, ' ', customers.last_name) as requested_for
    FROM order_details
    JOIN orders on orders.order_id = order_details.order_id
    JOIN customers on orders.customer_id = customers.customer_id
    WHERE status_id NOT IN (3,6)
    GROUP BY order_id
    ORDER BY order_details.bill_to_fy DESC, order_details.order_id DESC
    ";
} else {
    $sql = "SELECT order_details.bill_to_fy, order_details.order_id, 
    order_details.order_created as created,
    orders.grand_total, concat(customers.first_name, ' ', customers.last_name) as requested_for
    FROM order_details
    JOIN orders on orders.order_id = order_details.order_id
    JOIN customers on orders.customer_id = customers.customer_id
    JOIN departments on departments.dep_num = order_details.emp_dept
    WHERE order_details.status_id NOT IN (3,6)
    AND (
        departments.dep_head = '$empNumber'
        OR departments.dep_assist = '$empNumber'
        OR departments.dep_asset_mgr = '$empNumber'
        )
    GROUP BY order_id
    ";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }
}

echo json_encode($data);