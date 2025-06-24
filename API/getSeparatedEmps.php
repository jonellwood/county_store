<?php

include_once "../config.php";

$sql = "SELECT *
FROM `emp_ref`
WHERE seperation_date BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                          AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
}
echo json_encode($data);
