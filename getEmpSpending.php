<?php 

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// function to determine the current fiscal year ..... I think this should work.... 
function fiscalYear() {
    $currentMonth = date('m');
    $currentYear = date('Y');
    if ($currentMonth < 6) {
        $currentYear--;
    }
    return $currentYear;
}
$fiscalYear = strval(fiscalYear());
// var_dump($fiscalYear);
$empNum = $_GET['emp_num'];
// var_dump($empNum);

$sql = "SELECT SUM(line_item_total) FROM uniform_orders.ord_ref 
WHERE status NOT IN ('Pending', 'Denied')
AND emp_id = $empNum AND created > '$fiscalYear-07-01'"; 

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
while ($row = $result->fetch_assoc()) {
    array_push($data, $row);
}
echo json_encode($data);
$conn->close();