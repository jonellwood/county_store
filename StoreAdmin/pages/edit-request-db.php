<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);


$order_details_id = strip_tags($_POST['order_details_id']); //int
$product_id = strip_tags($_POST['productCodeSelect']); //int
$price = strip_tags($_POST['new_price']); //string
$logo_fee = strip_tags($_POST['logoFee']); //float
$tax = (($price + $logo_fee) * 0.09); //float
$qty = strip_tags($_POST['quantity']); //int
$line_item_total = (($price + $logo_fee + $tax) * $qty); //float
$size_id = strip_tags($_POST['sizeSelect']); // string
$color_id = strip_tags($_POST['colorSelect']); //string
$bill_to_dept = strip_tags($_POST['billToDept']); //string
$logo = strip_tags($_POST['logoSelect']); //string
$status = strip_tags($_POST['status']); //string
$admin_id = $_SESSION['empNumber']; //string


$sql = "UPDATE uniform_orders.order_details SET 
product_id = ? , 
quantity = ?,
size_id = ?, 
color_id = ?, 
status = ?, 
item_price = ?, 
logo_fee = ?,
tax = ? , 
line_item_total = ?,
logo = ?, 
bill_to_dept = ? 
WHERE order_details_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssiiissi", $product_id, $qty, $size_id, $color_id, $status, $price, $logo_fee, $tax, $line_item_total, $logo, $status, $bill_to_dept, $order_details_id);
$updateOrder = $stmt->execute();


if($updateOrder){
    header('Location: edit-request-ui.php');
}

// "<pre>";
// echo "<p>Product Id " . $product_id . "</p><br>";
// echo "<p>Qty " . $qty . "</p><br>";
// echo "<p>Size Id " . ($size_id) . "</p><br>";
// echo "<p>Color " . $color_id . "</p><br>";
// echo "<p>Status " . $status . "</p><br>";
// echo "<p>Price " .  $price . "</p><br>";
// echo "<p>Logo Fee " .  $logo_fee . "</p><br>";
// echo "<p>Tax " .  $tax . "</p><br>";
// echo "<p>Line Item Total " .  $line_item_total . "</p><br>";
// echo "<p>Logo " . $logo . "</p><br>";
// echo "<p>Bill to Dept " . $bill_to_dept . "</p><br>";
// echo "<p><br></p>";
// echo "<p>Order Details Id " . $order_details_id . "</p><br>";
// echo "<p>Admin Id " . $admin_id . "</p><br>";
// echo "<p><br></p>";
// echo "<p>" . $sql . "</p>";
// "</pre>";