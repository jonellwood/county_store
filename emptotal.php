<?php
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$ORDID = $_GET["ORDID"];
$EMPID = $_GET["EMPID"];

$sql = "SELECT SUM(ord_ref.line_item_total) as gToats FROM uniform_orders.ord_ref
WHERE emp_id = $EMPID AND status='Ordered'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$total = $stmt->get_result();

$data = array();

while ($row = $total->fetch_assoc()) {
    $sql2 = "SELECT * FROM uniform_orders.ord_ref o join(select * from emp_ref) e on o.emp_id = e.empNumber WHERE o.order_details_id = $ORDID";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    $result = $stmt2->get_result();
    while ($row2 = $result->fetch_assoc()) {
        array_push($data, $row2);
    }
    array_push($data, $row);
}
// Suggested refactor of this function above: Move $sql2 to variable above to improve readabiltiy. Remove the loop that queries the uniform_orders.ord_ref table and emp_ref table, as you only need them once.
// Use array_push() more efficiently by puting the entire row result in one call, rather than looping through every record and calling it with every iteration. See suggestion below (not tested in production)

// Store SQL query as variable
// $sql2 = "SELECT * FROM uniform_orders.ord_ref o join(select * from emp_ref) e on o.EMP_ID = e.EMPNumber WHERE o.order_details_id = $ORDID";

// Query database only once  
// $stmt2 = $conn->prepare($sql2);
// $stmt2->execute();
// $result = $stmt2->get_result();

// Push entire row of results
// while ($row = $total->fetch_assoc()) {
//     array_push($data, [$row, $result->fetch_assoc()]);
// }



echo json_encode($data);
