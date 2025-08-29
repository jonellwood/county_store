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
            // Handle place order action
            $departmentId = $input['departmentId'] ?? null;
            $poNumber = $input['poNumber'] ?? null;

            if (!$departmentId) {
                http_response_code(400);
                echo json_encode(['error' => 'Department ID is required']);
                exit;
            }

            // Start transaction for data consistency
            $conn->begin_transaction();

            try {
                // Get all order_details_ids for this department that are approved
                $idsql = "SELECT ord.order_details_id 
                         FROM uniform_orders.ord_ref ord 
                         WHERE ord.department = ? AND ord.status = 'Approved'";
                $idstmt = $conn->prepare($idsql);
                $idstmt->bind_param("i", $departmentId);
                $idstmt->execute();
                $odres = $idstmt->get_result();

                $orderDetailIds = [];
                while ($row = $odres->fetch_assoc()) {
                    $orderDetailIds[] = $row['order_details_id'];
                }

                if (empty($orderDetailIds)) {
                    throw new Exception('No approved orders found for this department');
                }

                // Update all order_details to 'Ordered' status
                $updateCount = 0;
                foreach ($orderDetailIds as $orderDetailId) {
                    $updateSql = "UPDATE uniform_orders.order_details 
                                 SET status = 'Ordered'" .
                        ($poNumber ? ", po_number = ?" : "") .
                        " WHERE order_details_id = ?";

                    $updateStmt = $conn->prepare($updateSql);

                    if ($poNumber) {
                        $updateStmt->bind_param("si", $poNumber, $orderDetailId);
                    } else {
                        $updateStmt->bind_param("i", $orderDetailId);
                    }

                    if ($updateStmt->execute()) {
                        $updateCount++;
                    }
                }

                // Commit the transaction
                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => "Successfully updated $updateCount orders to 'Ordered' status",
                    'ordersUpdated' => $updateCount,
                    'poNumber' => $poNumber
                ]);
                exit;
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                throw $e;
            }
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
            CONCAT(customers.first_name, ' ', customers.last_name) as req_for
        FROM order_details
        JOIN products_new on products_new.product_id = order_details.product_id
        JOIN prices on prices.product_id = order_details.product_id
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
