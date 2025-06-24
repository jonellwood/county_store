<?php

include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// $sql = "SELECT * from products WHERE isActive = true order by code ASC";
$sql = "SELECT p.code, p.name, p.price, p.isactive,
r.xs_inc, r.s_inc, r.m_inc, r.l_inc, r.xl_inc, r.xxl_inc, r.xxxl_inc, r.xxxxl_inc, r.xxxxxl_inc, r.xxxxxxl_inc, r.lt_inc, r.xlt_inc, r.xxlt_inc, r.xxxlt_inc, r.xxxxlt_inc
from products p 
JOIN prod_size_mod_ref r on p.product_id = r.product_id
WHERE isActive = true AND code <> 'boots'
order by code ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$report = $stmt->get_result();
$data = array();
while ($row = $report->fetch_assoc()) {
    array_push($data, $row);
}
echo json_encode($data);
