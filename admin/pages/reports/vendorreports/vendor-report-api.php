<?php
// Modern Vendor Report API - Berkeley County Store Admin
// Created: 2025/09/30
// Optimized query with proper NULL handling for vendor data

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../signin/signin.php");
    exit;
}

include('../../DBConn.php');

// Input validation and sanitization
$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
if (empty($uid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid UID parameter']);
    exit;
}

$data = [];

// Optimized query with proper LEFT JOINs to handle missing vendor data
$sql = "SELECT 
            oi_od.order_inst_id,
            oi_od.order_details_id, 
            od.product_id,
            od.price_id,
            od.size_id, 
            od.quantity, 
            od.color_id,
            od.line_item_total, 
            od.logo, 
            od.dept_patch_place,
            od.logo_fee,
            od.comment,
            od.tax, 
            od.item_price AS pre_tax_price,
            od.order_placed, 
            od.status_id,
            od.status,
            od.order_id,
            
            -- Vendor information (handle NULLs properly)
            COALESCE(v.name, 'Unknown Vendor') AS vendor_name,
            COALESCE(v.id, 0) AS vendor_id,
            
            -- Order and customer info
            oi.po_number, 
            o.customer_id,
            c.first_name AS rf_first_name,
            c.last_name AS rf_last_name,
            
            -- Product details
            pn.name AS product_name,
            pn.code AS product_code,
            
            -- Department info
            d.dep_name, 
            
            -- Logo information (get name instead of file path)
            COALESCE(lg.logo_name, 'No Logo') AS logo_name,
            
            -- Size and color info
            sn.size_name,
            colors.color AS color_name,
            
            -- Comments (optimized with proper aggregation)
            GROUP_CONCAT(DISTINCT comments.comment ORDER BY comments.submitted SEPARATOR ' || ') AS comments,
            GROUP_CONCAT(DISTINCT comments.submitted_by ORDER BY comments.submitted SEPARATOR ' || ') AS comment_submitters,
            GROUP_CONCAT(DISTINCT comments.submitted ORDER BY comments.submitted SEPARATOR ' || ') AS comment_submitted,
            GROUP_CONCAT(DISTINCT cer.empName ORDER BY comments.submitted SEPARATOR ' || ') AS comment_sub_name 
            
        FROM uniform_orders.order_inst_order_details_id oi_od
        
        -- Core required joins
        JOIN order_inst oi ON oi.order_inst_id = oi_od.order_inst_id
        JOIN order_details od ON od.order_details_id = oi_od.order_details_id
        JOIN orders o ON o.order_id = od.order_id
        JOIN departments d ON d.dep_num = od.emp_dept
        JOIN customers c ON c.customer_id = o.customer_id
        JOIN products_new pn ON pn.product_id = od.product_id
        JOIN sizes_new sn ON od.size_id = sn.size_id COLLATE utf8_unicode_ci
        JOIN colors ON colors.color_id = od.color_id
        
        -- Potentially problematic joins - make them LEFT JOINs
        LEFT JOIN prices p ON p.price_id = od.price_id
        LEFT JOIN vendors v ON v.id = p.vendor_id
        
        -- Logo information join (use logo_id, not logo field)
        LEFT JOIN logos lg ON lg.id = od.logo_id
        
        -- Optional joins for comments
        LEFT JOIN comments ON od.order_details_id = comments.order_details_id
        LEFT JOIN curr_emp_ref cer ON cer.empNumber = comments.submitted_by
        
    WHERE oi_od.order_inst_id = ?
    GROUP BY 
        oi_od.order_details_id,
        oi_od.order_inst_id,
        od.product_id,
        od.price_id,
        od.size_id,
        od.color_id
    ORDER BY 
        COALESCE(v.name, 'Unknown Vendor'),
        d.dep_name,
        pn.code,
        sn.size_name,
        colors.color";

// Use prepared statement for security
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query preparation failed: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query execution failed: ' . mysqli_error($conn)]);
    exit;
}

// Fetch and structure the data
$vendorGroups = [];
$totalItems = 0;
$totalValue = 0;
$departments = [];
$orderDate = null;
$poNumber = null;

while ($row = mysqli_fetch_assoc($result)) {
    $vendorId = $row['vendor_id'];
    $vendorName = $row['vendor_name'];

    // Collect department information
    if (!in_array($row['dep_name'], $departments)) {
        $departments[] = $row['dep_name'];
    }

    // Get order date and PO number (same for all rows)
    if (!$orderDate) {
        $orderDate = $row['order_placed'];
    }
    if (!$poNumber) {
        $poNumber = $row['po_number'];
    }

    if (!isset($vendorGroups[$vendorId])) {
        $vendorGroups[$vendorId] = [
            'vendor_info' => [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorName
            ],
            'items' => [],
            'summary' => [
                'total_items' => 0,
                'total_quantity' => 0,
                'total_value' => 0
            ]
        ];
    }

    $vendorGroups[$vendorId]['items'][] = $row;
    $vendorGroups[$vendorId]['summary']['total_items']++;
    $vendorGroups[$vendorId]['summary']['total_quantity'] += (int)$row['quantity'];
    $vendorGroups[$vendorId]['summary']['total_value'] += (float)$row['line_item_total'];

    $totalItems++;
    $totalValue += (float)$row['line_item_total'];
}

mysqli_stmt_close($stmt);

// Response structure
$response = [
    'success' => true,
    'order_instance_id' => $uid,
    'order_info' => [
        'po_number' => $poNumber,
        'order_date' => $orderDate,
        'departments' => $departments,
        'department_display' => count($departments) === 1 ? $departments[0] : implode(', ', $departments)
    ],
    'summary' => [
        'total_vendors' => count($vendorGroups),
        'total_items' => $totalItems,
        'total_value' => $totalValue
    ],
    'vendor_groups' => array_values($vendorGroups),
    'generated_at' => date('Y-m-d H:i:s')
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
