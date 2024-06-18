<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the price_id and price arrays from the POST data
    $price_ids = $_POST['price_id'];
    $prices = $_POST['price'];

    // Ensure both arrays have the same length
    if (count($price_ids) != count($prices)) {
        echo "Error: Arrays do not have the same length.";
        exit;
    }

    // Prepare an UPDATE statement for each price
    foreach ($price_ids as $key => $price_id) {
        $stmt = $conn->prepare("UPDATE prices SET price =? WHERE price_id =?");
        $stmt->bind_param("ds", $prices[$key], $price_id); // "d" for double (float), "s" for string
        $stmt->execute();
        $stmt->close();
    }

    echo "Prices updated successfully.";
} else {
    echo "No form data posted.";
}
?>

<!-- Close the database connection -->
<?php $conn->close(); ?>