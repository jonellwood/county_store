<?php
include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// foreach ($_POST as $key => $value) {
//     echo "$key: $value\n";
// }


$quantity = $_POST["quantity"];
echo "<pre>";
print_r($quantity);
echo "</pre>";
$color_id = $_POST["hidden_color_id"];
echo "<pre>";
print_r($color_id);
echo "</pre>";
$price_id = $_POST["hidden_price_id"];
echo "<pre>";
print_r($price_id);
echo "</pre>";
$logo_id = $_POST["logo"];
echo "<pre>";
print_r($logo_id);
echo "</pre>";
$dept_place_id = $_POST["hidden_dept_place_id"];
if ($dept_place_id == "p1") {
    $dept_patch_place = "No Dept Name";
    echo "<pre>";
    print_r($dept_patch_place);
    echo "</pre>";
} else if ($dept_place_id == "p2") {
    $dept_patch_place = "Below Logo";
    echo "<pre>";
    print_r($dept_patch_place);
    echo "</pre>";
} else if ($dept_place_id == "p3") {
    $dept_patch_place = "Left Sleeve";
    echo "<pre>";
    print_r($dept_patch_place);
    echo "</pre>";
}
$bill_to = $_POST["bill_to"];
echo "<pre>";
print_r($bill_to);
echo "</pre>";
$comment = $_POST["comment"];
echo "<pre>";
print_r($comment);
echo "</pre>";
$newLineItemTotal = $_POST["newLineItemTotal"];
echo "<pre>";
print_r($newLineItemTotal);
echo "</pre>";
$order_details_id = $_POST["order_details_id"];
echo "<pre>";
print_r($order_details_id);
echo "</pre>";


$sql = "UPDATE order_details SET quantity = $quantity, color_id = $color_id, price_id = $price_id, logo_id = $logo_id, dept_patch_place = '$dept_patch_place', bill_to_dept = $bill_to,  
WHERE order_details_id = $order_details_id";
// $sql = "UPDATE order_details SET quantity = ?, color_id = ?, price_id = ?, logo_id = ?, dept_place_id = ?, bill_to = ?, comment = ?, newLineItemTotal = ? WHERE order_details_id = ?";
// echo $sql;
echo "<pre>";
print_r($sql);
echo "</pre>";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiisdi", $quantity, $color_id, $price_id, $logo_id, $dept_place_id, $bill_to, $comment, $newLineItemTotal, $order_details_id);

// echo $sql;
//$stmt->execute();
// echo json_encode(["success" => true]);

// quantity: 1
// color: Black / Gold
// size: 13
// logo: 6
// deptPlacement: No Dept Name
// bill_to: 41515
// comment: Comment fo ya!
// newLineItemTotal: 19.62
// hidden_price_id: 1038
// hidden_color_id: 1026
// hidden_dept_place_id: p1