<?php
// Modern Reports API endpoint
// Berkeley County Store Admin
include_once('../DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json');

try {
    // Use the same query structure as past-reports-get.php
    $sql = "SELECT 
        oio.order_inst_id,
        SUM(od.line_item_total) as total,
        od.order_placed,
        d.dep_name
    FROM 
        order_inst_order_details_id oio
    JOIN 
        order_details od ON oio.order_details_id = od.order_details_id
    JOIN 
        departments d on d.dep_num = od.emp_dept
    GROUP BY 
        oio.order_inst_id
    ORDER BY od.order_placed DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "order_inst_id" => $row["order_inst_id"],
                "total" => number_format((float)$row["total"], 2, '.', ''),
                "order_placed" => $row["order_placed"],
                "dep_name" => $row["dep_name"]
            ];
        }
    }

    echo json_encode($data);
} catch (mysqli_sql_exception $e) {
    error_log("Database error in reports API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in reports API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching reports']);
}
