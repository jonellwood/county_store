<?php

session_start();

if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp_id = $_GET['emp_id'];

$sql = "SELECT order_details_id, inv_status, inv_UID, emp_inv_assigned_to, inv_received, dep_inv_assigned_to, product_code, product_name, product_price, size, color, ordered_for, ordered_for_department, logo, assigned_to_empName, size_name
FROM uniform_orders.inv_ref
WHERE emp_inv_assigned_to = $emp_id
";

$data = array();

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
} else {
    array_push($data, [
        // 'order_details_id' => ['Nothing'],
        'product_code' => ['nothing_found'],
        'product_price' => ['just'],
        'color' => ['Go buy'],
        'size_name' => ['something'],
        'logo' => ['/dept_logos/clown.png'],
        'inv_received' => ['kidding'],
        'product_name' => ['Razzle Dazzle']
    ]);
};

echo json_encode($data);
