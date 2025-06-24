<?php
include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$dept = $_GET['dept'];
// echo "Department: $dept";
function fiscalYear()
{
    $currentMonth = date('m');
    $currentYear = date('Y');
    if ($currentMonth < 7) {
        $fy_start_year = $currentYear - 1;
        $fy_end_year = $currentYear;
    } elseif ($currentMonth >= 7) {
        $fy_start_year = $currentYear;
        $fy_end_year = $currentYear + 1;
    }
    return [$fy_start_year, $fy_end_year];
}

$fiscalYear = fiscalYear();
$fy_start_year = $fiscalYear[0];
$fy_end_year = $fiscalYear[1];

// $data = array();
$data = [];
$current_fy = array();
$previous_fy = array();

// $sql = "SELECT od.emp_id, od.status, SUM(od.line_item_total) as total_line_item_total,
// CONCAT(od.rf_first_name, ' ', od.rf_last_name) as name
// FROM ord_ref od
// where od.bill_to_fy = '2425' AND
// -- where od.created < '$fy_end_year-07-01' AND od.created > '$fy_start_year-06-30' AND
// od.department = '$dept'
// GROUP BY od.emp_id, od.status
// order by od.emp_id ASC
// ;";
$sql = "SELECT 
            c.emp_id,
            od.status,
            SUM(od.line_item_total) AS total_line_item_total,
            CONCAT(c.first_name, ' ', c.last_name) AS name
        FROM orders r
        LEFT JOIN customers c ON c.customer_id = r.customer_id
        LEFT JOIN order_details od ON r.order_id = od.order_id
        WHERE 
            od.bill_to_fy = '2425' AND
            c.department = '$dept'
        GROUP BY 
            c.emp_id,
            od.status,
            c.first_name,
            c.last_name
        ORDER BY c.emp_id ASC;";
// echo $sql;
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $emp_id = $row['emp_id'];

        if (!isset($current_fy[$emp_id])) {
            $current_fy[$emp_id] = [
                "emp_id" => $emp_id,
                "name" => $row['name'],
                "totals" => [],
            ];
        }
        $current_fy[$emp_id]['totals'][] = [
            "status" => $row['status'],
            "total_line_item_total" => $row['total_line_item_total'],
        ];
    }
    array_push($data, $current_fy);
} else {
    array_push($data, []);
}



$prev_fy_start_year = $fy_start_year - 1;
$prev_fy_end_year = $fy_end_year - 1;
$sql = "SELECT od.emp_id, od.status, SUM(od.line_item_total) as total_line_item_total,
CONCAT(od.rf_first_name, ' ', od.rf_last_name) as name
FROM ord_ref od
where od.bill_to_fy = '2324' AND
-- where od.created < '$prev_fy_end_year-07-01' AND od.created > '$prev_fy_start_year-06-30' AND
od.department = '$dept'
GROUP BY od.emp_id, od.status
order by od.emp_id ASC
;";
// echo $sql;
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $emp_id = $row['emp_id'];

        if (!isset($previous_fy[$emp_id])) {
            $previous_fy[$emp_id] = [
                "emp_id" => $emp_id,
                "name" => $row['name'],
                "totals" => [],
            ];
        }
        $previous_fy[$emp_id]['totals'][] = [
            "status" => $row['status'],
            "total_line_item_total" => $row['total_line_item_total'],
        ];
    }

    array_push($data, $previous_fy);
} else {
    array_push($data, []);
}


array_push($data, [
    'current_fy_start_year' => $fy_start_year,
    'current_fy_end_year' => $fy_end_year,
    'prev_fy_start_year' => $prev_fy_start_year,
    'prev_fy_end_year' => $prev_fy_end_year
]);
header('Content-Type: application/json');
echo json_encode($data);
