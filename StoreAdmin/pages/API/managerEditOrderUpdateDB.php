<?php
include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// foreach ($_POST as $key => $value) {
//     echo "$key: $value\n";
// }


$quantity = $_POST["quantity"];
// echo "<pre>";
// echo "<p>QUANTITY: ";
// print_r($quantity);
// echo "</p></pre";

$size_id = $_POST["hidden_size_id"];
// echo "<pre>";
// echo "<p>SIZE ID: ";
// print_r($size_id);
// echo "</p></pre";

$color_id = $_POST["hidden_color_id"];
// echo "<pre>";
// echo "<p>COLOR ID: ";
// print_r($color_id);
// echo "</p></pre";

$price_id = $_POST["hidden_price_id"];
// echo "<pre>";
// echo "<p>PRICE ID: ";
// print_r($price_id);
// echo "</p></pre";

$item_price = $_POST["hidden_product_price"];
// echo "<pre>";
// echo "<p>ITEM PRICE: ";
// print_r($item_price);
// echo "</p></pre";

$logo_id = $_POST["logo"];
// echo "<pre>";
// echo "<p>LOGO ID: ";
// print_r($logo_id);
// echo "</p></pre";

$logo_url = $_POST["hidden_logo_url"];
// echo "<pre>";
// echo "<p>LOGO URL: ";
// print_r($logo_url);
// echo "</p></pre>";

$logo_fee = $_POST["hidden_logo_fee"];
// echo "<pre>";
// echo "<p>LOGO FEE: ";
// print_r($logo_fee);
// echo "</p></pre";

$tax = $_POST["hidden_tax"];
// echo "<pre>";
// echo "<p>TAX: ";
// print_r($tax);
// echo "</p></pre";

$dept_place_id = $_POST["hidden_dept_place_id"];
if ($dept_place_id == "p1") {
    $dept_patch_place = "No Dept Name";
    // echo "<pre>";
    // echo "<p>DEPT PATCH PLACE: ";
    // print_r($dept_patch_place);
    // echo "</p></pre";
} else if ($dept_place_id == "p2") {
    $dept_patch_place = "Below Logo";
    // echo "<pre>";
    // echo "<p>DEPT PATCH PLACE: ";
    // print_r($dept_patch_place);
    // echo "</p></pre";
} else if ($dept_place_id == "p3") {
    $dept_patch_place = "Left Sleeve";
    // echo "<pre>";
    // echo "<p>DEPT PATCH PLACE: ";
    // print_r($dept_patch_place);
    // echo "</p></pre";
}

$bill_to = $_POST["bill_to"];
// echo "<pre>";
// echo "<p>BILL TO: ";
// print_r($bill_to);
// echo "</p></pre";

$comment = strip_tags($_POST["comment"]);
// echo "<pre>";
// echo "<p>COMMENT: ";
// print_r($comment);
// echo "</p></pre";

$newLineItemTotal = $_POST["newLineItemTotal"];
// echo "<pre>";
// echo "<p>NEW LINE ITEM TOTAL: ";
// print_r($newLineItemTotal);
// echo "</p></pre";

$order_details_id = $_POST["order_details_id"];
// echo "<pre>";
// echo "<p>ORDER DETAILS ID: ";
// print_r($order_details_id);
// echo "</p></pre";


$sql = "UPDATE order_details 
        SET 
        price_id = $price_id, 
        quantity = $quantity,
        size_id = $size_id, 
        color_id = $color_id, 
        item_price = $item_price,
        logo_fee = $logo_fee,
        tax = $tax,
        line_item_total = $newLineItemTotal,
        logo = '$logo_url',
        logo_id = $logo_id, 
        comment = '$comment',
        dept_patch_place = '$dept_patch_place', 
        bill_to_dept = $bill_to,  
        last_updated = NOW()
        WHERE order_details_id = $order_details_id";

echo "<pre>";
print_r($sql);
echo "</p></pre";
$stmt = $conn->prepare($sql);
// $stmt->bind_param("iiiiisdi", $quantity, $color_id, $price_id, $logo_id, $dept_place_id, $bill_to, $comment, $newLineItemTotal, $order_details_id);

// echo $sql;
$stmt->execute();
header('Location: ../employeeRequests.php');
// echo json_encode(["success" => true]);