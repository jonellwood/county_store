<?php
session_start();
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$empNumber = $_SESSION["empNumber"];

$data = [];
if (($_SESSION['empNumber'] == '6865') || ($_SESSION['empNumber'] == '4438')
) {
    $sql = "SELECT ord_ref.*
    FROM ord_ref
    WHERE STATUS != 'Received' and STATUS != 'Expired' and STATUS != 'Denied'
    -- FROM ord_ref
    -- WHERE (STATUS = 'Denied' AND created >= DATE_SUB(CURDATE(), INTERVAL 45 DAY))
    -- OR (STATUS != 'Received' AND STATUS != 'Expired');
    GROUP BY order_id
    ORDER BY order_id DESC
    ";
} else {
    $sql = "SELECT ord_ref.*
    FROM departments
    JOIN ord_ref ON departments.dep_num = ord_ref.department
    WHERE departments.dep_head = '$empNumber'
        OR departments.dep_assist = '$empNumber'
        OR departments.dep_asset_mgr = '$empNumber'
    AND STATUS != 'Received' and STATUS != 'Expired' 
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
