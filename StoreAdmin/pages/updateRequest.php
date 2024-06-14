<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include('DBConn.php');

$origColorName = $_POST['color_name'];
$origSizeName = $_POST['size_name'];

$newColor = $_POST['color'];
$newSize = $_POST['size'];
$newStatus = "Updated";
$comment = strip_tags($_POST['comment']);
$changedBy = $_SESSION['empNumber'];
$order_id = $_POST['order_id'];
// $newStatus = $_POST['newStatus'];
$newStatus = 'Approved';
$color = array();
$size = array();
$submitted_by = $_SESSION['empNumber'];
$uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));


$csql = "SELECT color from colors where color_id = $newColor";
$cstmt = $conn->prepare($csql);
$cstmt->execute();
$newColorName = $cstmt->get_result();
if ($newColorName->num_rows > 0) {
    while ($newColorRow = $newColorName->fetch_assoc()) {
        array_push($color, $newColorRow);
    }
};

$newColorID = $color[0]['color'];

$ssql = "SELECT size from sizes where size_id = $newSize";
$sstmt = $conn->prepare($ssql);
$sstmt->execute();
$newSizeName = $sstmt->get_result();
if ($newSizeName->num_rows > 0) {
    while ($newSizeRow = $newSizeName->fetch_assoc()) {
        array_push($size, $newSizeRow);
    }
};

echo "new size_id: " . $newSize . "<br>";
echo "new color_id: " . $newColorID . "<br>";
echo "new status: " . $newStatus . "<br>";
echo "Order Details ID: " . $order_id . "<br>";


$updateSql = "UPDATE order_details SET size_id = '$newSize', color_id = '$newColorID', status = '$newStatus' WHERE order_details_id = $order_id";
$updateStmt = $conn->prepare($updateSql);
$order_details_updated = $updateStmt->execute();
if ($order_details_updated) {
    echo "<p>Order " . $order_id . " was updated to size: " . $newSize . "and / or color " . $newColorID . "by " . $_SESSION['empNumber'];
    $commentSql = "INSERT into comments (id, order_details_id, comment, submitted_by) values ('$uid', '$order_id', '$comment', $submitted_by)";
    $commentStmt = $conn->prepare($commentSql);
    $itWorked = $commentStmt->execute();
    if ($itWorked) {
        header("Location: employeeRequests.php");
    } else {
        echo "Something failed. Please return <a href='./employeeRequests.php'>Home</a> and try again.";
    }
}



// echo "<pre>";
// echo "<p>Original Color Name: " . $origColorName . "</p><br>";
// echo "Original Size Name: " . $origSizeName . "</p><br>";
// echo "New Color Id Value: " . $newColor .  "</p><br>";
// echo "New Size Id: " . $newSize . "</p><br>";
// echo "Comment: " . $comment . "</p><br>";
// echo "Changed By: " . $changedBy . "</p><br>";
// echo "Order Details ID: " . $order_id . "</p><br>";
// echo "Here Come the arrays: ";
// echo "<br>";
// var_dump($color);
// echo "<br>";
// var_dump($size);
// echo "<br>";
// echo "<p>This is the new <b>color-id</b> - stored in db as color_id becuase I suck: "  . $color[0]['color'] . "</p><br>";
// echo "<p>This is the new size name - which is NOT stored in db so I guess I dont need it:  "  . $size[0]['size'] . "</p><br>";
// echo "<br>";
// var_dump($_SESSION);
// echo "</pre>";
