<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$emp = $_GET['emp'];

function fiscalYear()
{
    $currentMonth = date('m');
    $currentYear = date('Y');
    if ($currentMonth < 6) {
        $currentYear--;
    }
    return $currentYear;
}
$fiscalYear = strval(fiscalYear());

$fy_start = $fiscalYear . "-07-01";
$fy_end = $fiscalYear + 1 . "-06-30";

$query1 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_submitted from ord_submitted where emp_id = '$emp' and order_created BETWEEN '$fy_start' AND '$fy_end' AND product_id !=105;";
$query2 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_approved from  ord_approved  where emp_id = '$emp' and order_created BETWEEN   '$fy_start' AND '$fy_end' AND product_id !=105;";
$query3 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_ordered from   ord_ordered   where emp_id = '$emp' and order_created BETWEEN     '$fy_start' AND '$fy_end' AND product_id !=105;";
$query4 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_completed from ord_completed where emp_id = '$emp' and order_created BETWEEN '$fy_start' AND '$fy_end' AND product_id !=105;";

$query5 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_boots_submitted from ord_submitted where emp_id = '$emp' and order_created BETWEEN '$fy_start' AND '$fy_end' AND product_id = 105;";
$query6 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_boots_approved from  ord_approved  where emp_id = '$emp' and order_created BETWEEN   '$fy_start' AND '$fy_end' AND product_id = 105;";
$query7 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_boots_ordered from   ord_ordered   where emp_id = '$emp' and order_created BETWEEN     '$fy_start' AND '$fy_end' AND product_id = 105;";
$query8 = "SELECT ifnull(sum(line_item_total), 0.00) as emp_boots_completed from ord_completed where emp_id = '$emp' and order_created BETWEEN '$fy_start' AND '$fy_end' AND product_id = 105;";
$query9 = "SELECT empName from emp_ref where empNumber = '$emp' ";


$results = [];

$stmt1 = $conn->prepare($query1);
$stmt1->execute();
$res1 = $stmt1->get_result();
if ($res1->num_rows > 0) {
    while ($row = $res1->fetch_assoc()) {
        array_push($results, $row);
    }
}
// `ord_ref`.`product_id` AS `product_id`
$stmt2 = $conn->prepare($query2);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt3 = $conn->prepare($query3);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3->num_rows > 0) {
    while ($row = $res3->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt4 = $conn->prepare($query4);
$stmt4->execute();
$res4 = $stmt4->get_result();
if ($res4->num_rows > 0) {
    while ($row = $res4->fetch_assoc()) {
        array_push($results, $row);
    }
}
$stmt5 = $conn->prepare($query5);
$stmt5->execute();
$res5 = $stmt5->get_result();
if ($res5->num_rows > 0) {
    while ($row = $res5->fetch_assoc()) {
        array_push($results, $row);
    }
}
$stmt6 = $conn->prepare($query6);
$stmt6->execute();
$res6 = $stmt6->get_result();
if ($res6->num_rows > 0) {
    while ($row = $res6->fetch_assoc()) {
        array_push($results, $row);
    }
}
$stmt7 = $conn->prepare($query7);
$stmt7->execute();
$res7 = $stmt7->get_result();
if ($res7->num_rows > 0) {
    while ($row = $res7->fetch_assoc()) {
        array_push($results, $row);
    }
}
$stmt8 = $conn->prepare($query8);
$stmt8->execute();
$res8 = $stmt8->get_result();
if ($res8->num_rows > 0) {
    while ($row = $res8->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt9 = $conn->prepare($query9);
$stmt9->execute();
$res9 = $stmt9->get_result();
if ($res9->num_rows > 0) {
    while ($row = $res9->fetch_assoc()) {
        array_push($results, $row);
    }
}

array_push($results, ['fy_start' => $fy_start], ['fy_end' => $fy_end]);
echo json_encode($results);
