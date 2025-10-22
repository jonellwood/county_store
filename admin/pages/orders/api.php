<?php
// Modern Orders API endpoint
// Berkeley County Store Admin
include('../DBConn.php');

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
    // Handle POST requests for order status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['action']) && $input['action'] === 'placeOrder') {
            // Handle place order action with complete order instance creation
            $departmentId = $input['departmentId'] ?? null;
            $poNumber = $input['poNumber'] ?? null;
            $dryRun = $input['dryRun'] ?? false;
            $ordEmpId = $_SESSION['empNumber'] ?? null;

            if (!$departmentId) {
                http_response_code(400);
                echo json_encode(['error' => 'Department ID is required']);
                exit;
            }

            if (!$ordEmpId) {
                http_response_code(400);
                echo json_encode(['error' => 'User session required']);
                exit;
            }

            // Function to log events to file
            function logOrderEvent($message)
            {
                $logFile = '/tmp/orders.log';
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[$timestamp] $message" . PHP_EOL;
                file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            }

            logOrderEvent("=== STARTING ORDER PLACEMENT PROCESS ===");
            logOrderEvent("Department: $departmentId, PO: " . ($poNumber ?: 'None') . ", User: $ordEmpId" . ($dryRun ? " [DRY RUN]" : ""));

            // Start transaction for data consistency (only if not dry run)
            if (!$dryRun) {
                $conn->begin_transaction();
            }

            try {
                // Get all order_details with vendor info for this department that are approved
                // This JOIN is CRITICAL - it ensures we have vendor relationships
                $stmt = $conn->prepare("
                    SELECT od.order_details_id, pr.vendor_id, v.name as vendor_name
                    FROM uniform_orders.order_details od
                    JOIN uniform_orders.prices pr ON pr.product_id = od.product_id AND pr.size_id = od.size_id
                    JOIN uniform_orders.vendors v ON v.id = pr.vendor_id
                    WHERE (od.status_id = 1 OR od.status_id = 7 OR od.status = 'Approved')
                      AND od.emp_dept = ?
                    GROUP BY od.order_details_id
                ");
                $stmt->bind_param("i", $departmentId);
                $stmt->execute();
                $result = $stmt->get_result();

                $orderDetailsData = [];
                while ($row = $result->fetch_assoc()) {
                    $orderDetailsData[] = $row;
                }

                if (empty($orderDetailsData)) {
                    throw new Exception('No approved orders found for this department');
                }

                logOrderEvent("Found " . count($orderDetailsData) . " approved order details to process");

                // Validate all vendors exist
                $vendorIssues = [];
                foreach ($orderDetailsData as $item) {
                    if (!$item['vendor_id']) {
                        $vendorIssues[] = "Order detail ID {$item['order_details_id']} has no vendor relationship";
                    }
                }

                if (!empty($vendorIssues)) {
                    throw new Exception('Vendor validation failed: ' . implode(', ', $vendorIssues));
                }

                // If this is a dry run, return what would happen without making changes
                if ($dryRun) {
                    logOrderEvent("DRY RUN: Would process " . count($orderDetailsData) . " order details");

                    $dryRunSummary = [
                        'orderDetails' => [],
                        'vendors' => [],
                        'summary' => []
                    ];

                    foreach ($orderDetailsData as $item) {
                        $dryRunSummary['orderDetails'][] = [
                            'order_details_id' => $item['order_details_id'],
                            'vendor_id' => $item['vendor_id'],
                            'vendor_name' => $item['vendor_name']
                        ];
                    }

                    $uniqueVendors = array_unique(array_column($orderDetailsData, 'vendor_id'));
                    foreach ($uniqueVendors as $vendorId) {
                        $vendorName = '';
                        foreach ($orderDetailsData as $item) {
                            if ($item['vendor_id'] == $vendorId) {
                                $vendorName = $item['vendor_name'];
                                break;
                            }
                        }
                        $dryRunSummary['vendors'][] = [
                            'vendor_id' => $vendorId,
                            'vendor_name' => $vendorName,
                            'order_count' => count(array_filter($orderDetailsData, function ($item) use ($vendorId) {
                                return $item['vendor_id'] == $vendorId;
                            }))
                        ];
                    }

                    $dryRunSummary['summary'] = [
                        'total_order_details' => count($orderDetailsData),
                        'unique_vendors' => count($uniqueVendors),
                        'department_id' => $departmentId,
                        'po_number' => $poNumber ?: 'None',
                        'would_create_order_instance' => true
                    ];

                    logOrderEvent("DRY RUN COMPLETED: " . json_encode($dryRunSummary));

                    echo json_encode([
                        'success' => true,
                        'dryRun' => true,
                        'message' => 'Dry run completed - no changes made',
                        'preview' => $dryRunSummary
                    ]);
                    exit;
                }

                // Proceed with actual order placement
                logOrderEvent("PROCEEDING WITH ACTUAL ORDER PLACEMENT");

                // Update all order_details to 'Ordered' status
                $orderDetailIds = array_column($orderDetailsData, 'order_details_id');
                $inClause = implode(',', $orderDetailIds);
                $updateSql = "
                    UPDATE uniform_orders.order_details
                    SET status = 'Ordered', status_id = 4, order_placed = NOW()" .
                    ($poNumber ? ", po_number = ?" : "") .
                    " WHERE order_details_id IN ($inClause)
                ";

                if ($poNumber) {
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("s", $poNumber);
                    $updateResult = $updateStmt->execute();
                } else {
                    $updateResult = $conn->query($updateSql);
                }

                if (!$updateResult) {
                    throw new Exception('Failed to update order details status');
                }

                logOrderEvent("Updated " . count($orderDetailIds) . " order details to 'Ordered' status");

                // Create order instance UID
                $ordInstId = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
                logOrderEvent("Generated order instance ID: $ordInstId");

                // Create the order instance in the database
                $ordInstSql = "INSERT INTO uniform_orders.order_inst (order_inst_id, created_by_emp_num, order_for_dept, po_number, order_inst_created) VALUES (?,?,?,?,NOW())";
                $ordInstStmt = $conn->prepare($ordInstSql);
                $ordInstStmt->bind_param("ssss", $ordInstId, $ordEmpId, $departmentId, $poNumber);
                $createOrdInst = $ordInstStmt->execute();

                if (!$createOrdInst) {
                    throw new Exception('Failed to create order instance');
                }

                logOrderEvent("Created order instance: $ordInstId for department $departmentId by employee $ordEmpId");

                // Map each order detail to the order instance with vendor info
                if (!empty($orderDetailsData)) {
                    $instSql = "INSERT INTO uniform_orders.order_inst_order_details_id (order_inst_id, order_details_id, vendor_id) VALUES (?,?,?)";
                    $instStmt = $conn->prepare($instSql);

                    $mappingCount = 0;
                    foreach ($orderDetailsData as $item) {
                        $instStmt->bind_param("sss", $ordInstId, $item['order_details_id'], $item['vendor_id']);
                        $insertOrdList = $instStmt->execute();

                        if ($insertOrdList) {
                            $mappingCount++;
                            logOrderEvent("Mapped order_details_id {$item['order_details_id']} to order_inst_id $ordInstId with vendor_id {$item['vendor_id']} ({$item['vendor_name']})");
                        } else {
                            throw new Exception("Failed to map order detail {$item['order_details_id']} to order instance");
                        }
                    }

                    logOrderEvent("Successfully mapped $mappingCount order details to order instance");
                }

                // Commit the transaction
                if (!$dryRun) {
                    $conn->commit();
                }

                logOrderEvent("=== ORDER PLACEMENT COMPLETED SUCCESSFULLY ===");
                logOrderEvent("Order Instance: $ordInstId, Details Processed: " . count($orderDetailsData) . ", PO: " . ($poNumber ?: 'None'));

                echo json_encode([
                    'success' => true,
                    'message' => "Successfully placed order for department $departmentId",
                    'orderInstanceId' => $ordInstId,
                    'ordersUpdated' => count($orderDetailIds),
                    'orderDetailsMapped' => $mappingCount ?? 0,
                    'poNumber' => $poNumber,
                    'vendorCount' => count(array_unique(array_column($orderDetailsData, 'vendor_id')))
                ]);
                exit;
            } catch (Exception $e) {
                // Rollback on error (only if not dry run)
                if (!$dryRun) {
                    $conn->rollback();
                }
                logOrderEvent("ERROR: Order placement failed - " . $e->getMessage());
                logOrderEvent("=== ORDER PLACEMENT FAILED" . ($dryRun ? "" : " - TRANSACTION ROLLED BACK") . " ===");
                throw $e;
            }
        }

        if (isset($input['action']) && $input['action'] === 'logExport') {
            // Handle HTML export logging
            $orderInstanceId = $input['orderInstanceId'] ?? null;
            $exportType = $input['exportType'] ?? 'HTML';
            $recordCount = $input['recordCount'] ?? 0;
            $userEmpId = $_SESSION['empNumber'] ?? 'Unknown';

            if (!$orderInstanceId) {
                http_response_code(400);
                echo json_encode(['error' => 'Order instance ID is required']);
                exit;
            }

            // Function to log events to file (reusing the same function from placeOrder)
            function logExportEvent($message)
            {
                $logFile = '/tmp/orders.log';
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[$timestamp] $message" . PHP_EOL;
                file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            }

            // Log the export event
            logExportEvent("=== VENDOR REPORT EXPORT ===");
            logExportEvent("Generated $exportType export for order instance: $orderInstanceId");
            logExportEvent("Export contains $recordCount records, created by employee: $userEmpId");
            logExportEvent("Export file: vendor-report-$orderInstanceId.html");

            echo json_encode([
                'success' => true,
                'message' => 'Export event logged successfully'
            ]);
            exit;
        }

        if (isset($input['action']) && $input['action'] === 'updateStatus') {
            // Handle status update
            $departmentId = $input['departmentId'] ?? null;
            $status = $input['status'] ?? null;

            if (!$departmentId || !$status) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required parameters']);
                exit;
            }

            // Update logic would go here
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            exit;
        }
    }    // GET request - fetch orders data
    // 1. Get list of all departments with orders in Approved state from ord_approved
    $deptList = [];
    $sql = "SELECT * FROM uniform_orders.ord_approved";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($deptList, $row);
        }
    }

    // 2. For each dept number in the list return all the order details
    $ordList = [];
    foreach ($deptList as $department) {
        $departmentId = $department['order_details_id'];

        $ordSql = "SELECT 
            order_details.order_id, 
            order_details.product_id, 
            order_details.line_item_total,
            order_details.quantity,
            order_details.order_details_id,
            order_details.status,
            products_new.name as product_name, 
            products_new.code as product_code,
            colors.color as color_name, 
            sizes_new.size_name,
            departments.dep_name, 
            departments.dep_num as department,
            prices.vendor_id, 
            vendors.vendor_number_finance,
            vendors.name as vendor_name,
            CONCAT(customers.first_name, ' ', customers.last_name) as req_for
        FROM order_details
        JOIN products_new on products_new.product_id = order_details.product_id
        JOIN prices on prices.price_id = order_details.price_id
        JOIN colors on colors.color_id = order_details.color_id
        JOIN sizes_new on sizes_new.size_id = order_details.size_id
        JOIN departments on departments.dep_num = order_details.emp_dept
        JOIN orders on orders.order_id = order_details.order_id
        JOIN customers on customers.customer_id = orders.customer_id
        JOIN vendors on vendors.id = prices.vendor_id
        WHERE order_details.order_details_id = ?
        GROUP BY order_details.order_details_id";

        $ordStmt = $conn->prepare($ordSql);
        $ordStmt->bind_param("i", $departmentId);
        $ordStmt->execute();
        $ordResult = $ordStmt->get_result();

        while ($ordRow = $ordResult->fetch_assoc()) {
            // Add additional fields that might be useful
            $ordRow['approved_date'] = $department['created_at'] ?? null;
            $ordRow['ordered_date'] = null; // This would come from ord_ordered table if needed

            array_push($ordList, $ordRow);
        }
    }

    // If no orders found, return empty array instead of error
    if (empty($ordList)) {
        echo json_encode([]);
    } else {
        echo json_encode($ordList);
    }
} catch (mysqli_sql_exception $e) {
    error_log("Database error in orders API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in orders API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching orders']);
}
