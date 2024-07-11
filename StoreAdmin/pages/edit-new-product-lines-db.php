<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// echo 'Hello there';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo "Server method is POST";
    // Update prices
    foreach ($_POST['price_ids'] as $index => $priceId) {
        $newSize = $_POST['size_id'][$index];
        //echo $newSize;
        $newPrice = $_POST['price'][$index];
        // Update the price in the database
        $updateSql = "UPDATE prices SET size_id = ? , price = ? WHERE price_id =?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("idi", $newSize, $newPrice, $priceId);
        $stmt->execute();
        $stmt->close();
        echo 'Price & Size updated';
    }

    // Delete prices based on unchecked checkboxes - sure sure this is gonna work 🤞
    $allPriceIds = $_POST['price_ids'];
    $allCheckPriceIds = $_POST['price_check_ids'];
    $idsToDelete = array_diff($allPriceIds, $allCheckPriceIds);

    foreach ($idsToDelete as $id) {
        $deleteSql = "DELETE FROM prices WHERE price_id =?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }


    header("Location: edit-new-product-line-ui.php");
    exit;
}
