<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// echo 'Hello there';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['productId']);
    $db_id = $id;
    $postedData = $_POST;

    $sql = "DELETE from products_colors WHERE product_id =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $db_id);
    $deleteCurrent = $stmt->execute();

    if (!$deleteCurrent) {
        echo "Error: " . $stmt->error;
    } else {
        // echo 'DB ID IS: ' . $db_id;

        foreach ($postedData['colorCheckbox'] as $colorId) {
            $color_id = intval($colorId);
            $insertSql = "INSERT INTO products_colors (product_id, color_id) VALUES (?,?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $db_id, $color_id);
            $insertResult = $insertStmt->execute();

            if (!$insertResult) {
                echo 'Error: ' . $insertStmt->error;
            } else {
                header('Location: edit-new-product-colors-ui.php');
                //echo 'It worked added ' . $color_id . 'for product ' . $db_id;
            }
            $insertStmt->close();
        }
    }
}
