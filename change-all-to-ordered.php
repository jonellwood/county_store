<?php

session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$dept_id = $_REQUEST['dept_id'];

// manually setting values for testing purposes
// $dept_id = 41515;

$idsql = "SELECT ord.order_details_id from uniform_orders.ord_ref ord WHERE ord.department = $dept_id AND ord.status='Approved'";
$idstmt = $conn->prepare($idsql);
$getList = $idstmt->execute();
$odres = $idstmt->get_result();
$data = array();

// Check if odres has any rows
if ($odres->num_rows > 0) {

    // Loop through the returned results
    foreach ($odres as $row) {

        // Add each item to the data array
        foreach ($row as $item) {
            array_push($data, $item);
        }
    }
}


// We are subtracting 1 from the total count of data stored in $data since an array is 0 base
$count = (count($data) - 1);


echo "<pre>";
echo "initial count" . var_dump($count);
// echo var_dump($data);
echo "</pre>";
// Loop through array of order_details_ids and update status
while ($count >= 0) {
    // echo "<pre>";
    // var_dump($data[$count]); // Print contents of each array item for testing purposes - remove for production
    // echo "</pre>";
    $sql = "UPDATE uniform_orders.order_details SET status = 'Ordered' WHERE order_details_id = $data[$count]";
    // Update status on order_details table for corresponding order_details_id
    $result = $conn->query($sql); // Execute query
    $count--;
}
