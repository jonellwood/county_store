<?php


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../signin/signin.php");
    exit;
}

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// $sql = "SELECT * from ord_ref where status = 'Waiting on Customer'";
$sql = "SELECT status, order_details_id, order_id from order_details where status = 'Waiting on Customer'";
// $sql = "SELECT ord_ref.created, ord_ref.requested_by_name, ord_ref.requested_by_last, MAX(comments.submitted) AS last_contact 
// FROM uniform_orders.ord_ref
// JOIN comments on comments.order_details_id = ord_ref.order_details_id
// where status = 'Waiting on Customer'";
// $sql = "SELECT orders.order_id, orders.created, CONCAT(customers.first_name, ' ', customers.last_name) as requested_for, order_details.order_details_id, MAX(comments.submitted) AS last_contact
// FROM uniform_orders.orders
// JOIN customers on orders.customer_id = customers.customer_id
// JOIN order_details on order_details.order_id = orders.order_id
// JOIN comments on comments.order_details_id = order_details.order_details_id
// WHERE order_details.status = 'Waiting on Customer'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$waiting = $stmt->get_result();
$data = array();


if ($waiting->num_rows > 0) {
    while ($row = $waiting->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);