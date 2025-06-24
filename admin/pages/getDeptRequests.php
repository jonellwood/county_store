<?php
session_start();
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$empNumber = $_SESSION["empNumber"];
$DEPT = $_SESSION["department"];
$ROLE = $_SESSION["role_name"];
// var_dump($_SESSION);
$depList = [];
$ordersList = [];
if (($_SESSION['empNumber'] == '6865') || ($_SESSION['empNumber'] == '4438')) {
    // echo "Sherry is logged in";
    // $sql = "SELECT dep_num from departments";
    $sql = "SELECT department as dep_num from ord_ref";
} else {
    $sql = "SELECT * from departments where dep_head = $empNumber OR dep_assist = $empNumber or dep_asset_mgr = $empNumber";
}
// $sql = "SELECT * from departments where dep_head = $empNumber OR dep_assist = $empNumber or dep_asset_mgr = $empNumber";

$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($depList, $row['dep_num']);
    }
}

foreach ($depList as $dep_num) {
    $sql = "SELECT ord.order_id, ord.grand_total, ord.emp_id, ord.created, ord.quantity, ord.line_item_total, ord.product_name, ord.submitted_by, concat(rf_first_name, ' ', rf_last_name) as requested_for, ord.status 
    from ord_ref ord
    WHERE ord.department = $dep_num and status != 'Received'
    group by order_id
    order by order_id DESC";
    $ordResult = mysqli_query($conn, $sql);
    if (mysqli_num_rows($ordResult) > 0) {
        while ($oRow = mysqli_fetch_assoc($ordResult)) {
            array_push($ordersList, $oRow);
        }
    }
}
echo json_encode($ordersList);
