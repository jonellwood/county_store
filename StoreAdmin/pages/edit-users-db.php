<?php

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// check for logged in. If yes send to dashboard
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
$data = [];
$sql = "SELECT u.emp_num, u.empName, u.email, u.role_name, 
d1.dep_num as dep_num_1, d1.dep_name as dep_name_1, 
d2.dep_num as dep_num_2, d2.dep_name as dep_name_2, 
d3.dep_num as dep_num_3, d3.dep_name as dep_name_3
FROM user_ref u
LEFT JOIN dep_ref d1 ON u.emp_num = d1.dep_head
LEFT JOIN dep_ref d2 on u.emp_num = d2.dep_assist
LEFT JOIN dep_ref d3 on u.emp_num = d3.dep_asset_mgr";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $employeeData = [];

    while ($row = $result->fetch_assoc()) {
        $empNum = $row['emp_num'];

        if (!isset($employeeData[$empNum])) {
            $employeeData[$empNum] = array(
                'emp_num' => $empNum,
                'empName' => $row['empName'],
                'role_name' => $row['role_name'],
                'departments' => array(),
            );
        }

        $employeeData[$empNum]['departments'][] = array(
            'dep_num_1' => $row['dep_num_1'],
            'dep_name_1' => $row['dep_name_1'],
            'dep_num_2' => $row['dep_num_2'],
            'dep_name_2' => $row['dep_name_2'],
            'dep_num_3' => $row['dep_num_3'],
            'dep_name_3' => $row['dep_name_3'],
        );
    }
}
echo json_encode($employeeData);
