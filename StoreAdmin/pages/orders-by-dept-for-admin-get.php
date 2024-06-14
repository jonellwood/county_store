<?php
include('DBConn.php');
?>
<?php
session_start();
// if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

//     header("location: pages/sign-in.php");

//     exit;
// }
$fakeJsonData = [
    [
        "message" => "No records found.",
        "status" => "No Data"
    ]
];

// 1. Get list of all departments with orders in Approved state from ord_approved. List only needs to be the department number 
// USING ORD_ORDERED FOR TESTING ONLY BECUASE THERE IS NO DATA IN ORD_APPROVED RIGHT NOW. CHANGE BACK TO ORD_APPROVED BEFORE PUSH
$deptList = [];
$sql = "SELECT * FROM uniform_orders.ord_approved";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($deptList, $row);
    }
} else {
    echo "No Records found";
};

// 2. for each dept number in the list return all the order details for each one, loop over them and display them
$ordList = [];
foreach ($deptList as $department) {
    $departmentId = $department['order_details_id'];
    // $departmentId has been replaced by the order_details_id but I dont want to rewrite the whole thing
    $ordSql = "SELECT ord_ref.order_id, ord_ref.quantity, ord_ref.product_name, ord_ref.product_code, ord_ref.color_id, 
    ord_ref.size_name, ord_ref.line_item_total, ord_ref.rf_first_name, ord_ref.rf_last_name, ord_ref.department, departments.dep_name, v.id as vendor_id, v.vendor_number_finance
    FROM ord_ref 
    JOIN departments on ord_ref.department = departments.dep_num
    JOIN vendors v on ord_ref.vendor = v.name
    WHERE order_details_id = $departmentId";
    $ordStmt = $conn->prepare($ordSql);
    $ordStmt->execute();

    $ordResult = $ordStmt->get_result();
    while ($ordRow = $ordResult->fetch_assoc()) {
        array_push($ordList, $ordRow);
    }
}

echo json_encode($ordList);


// 3. Make a button for each group that marks them all as ordered and creates the vendor report.



?>