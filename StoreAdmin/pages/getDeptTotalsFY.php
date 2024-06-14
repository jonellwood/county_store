<?php

include_once "../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
$dept = $_GET['dept'];

class MyDateTime extends DateTime
{
    /**
     * Calculates start and end date of fiscal year
     * @param DateTime $dateToCheck A date withn the year to check
     * @return array('start' => timestamp of start date ,'end' => timestamp of end date) 
     */
    public function fiscalYear()
    {
        $result = array();
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $end = new DateTime();
        $end->setTime(23, 59, 59);
        $year = $this->format('Y');
        $start->setDate($year, 7, 1);
        $end->setDate($year + 1, 6, 30);
        $result['start'] = $start->getTimestamp();
        $result['end'] = $end->getTimestamp();
        return $result;
    }
}

$mydate = new MyDateTime(); // will use the current date time

$year = $mydate->format('Y');  // to get the current year and 
$mydate->setDate($year - 1, 6, 30); // pass into here to set the values to apply
$result = $mydate->fiscalYear(); // the fiscalYear method too

$fystart = $result['start'];
$fyend = $result['end'];

$db_fystart = date(DATE_RFC3339, $fystart);
$db_fyend = date(DATE_RFC3339, $fyend);

$query1 = "SELECT ifnull(sum(line_item_total), 0.00) as dep_submitted from ord_submitted where department = '$dept' and order_created BETWEEN '$db_fystart' AND '$db_fyend'";
$query2 = "SELECT ifnull(sum(line_item_total), 0.00) as dep_approved from ord_approved where department = '$dept' and order_created BETWEEN   '$db_fystart' AND '$db_fyend'";
$query3 = "SELECT ifnull(sum(line_item_total), 0.00) as dep_ordered from ord_ordered where department = '$dept' and order_created BETWEEN     '$db_fystart' AND '$db_fyend'";
$query4 = "SELECT ifnull(sum(line_item_total), 0.00) as dep_completed from ord_completed where department = '$dept' and order_created BETWEEN '$db_fystart' AND '$db_fyend'";

$results = [];
$stmt1 = $conn->prepare($query1);
$stmt1->execute();
$res1 = $stmt1->get_result();
if ($res1->num_rows > 0) {
    while ($row = $res1->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt2 = $conn->prepare($query2);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt3 = $conn->prepare($query3);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3->num_rows > 0) {
    while ($row = $res3->fetch_assoc()) {
        array_push($results, $row);
    }
}

$stmt4 = $conn->prepare($query4);
$stmt4->execute();
$res4 = $stmt4->get_result();
if ($res4->num_rows > 0) {
    while ($row = $res4->fetch_assoc()) {
        array_push($results, $row);
    }
}
array_push($results, ['fy_start' => $db_fystart], ['fy_end' => $db_fyend], ['my_date' => $mydate]);
echo json_encode($results);
