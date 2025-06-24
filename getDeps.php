<?php

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT deptNumber, deptName from uniform_orders.emp_ref GROUP by deptName";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<label for='department'>Department:</label>";
    echo "<select title='department' name='department' id='department' class='form-control'>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value=" . $row['deptNumber'] . ">" . $row['deptName'] . "</option>";
    }
    echo "</select>";
}
$conn->close();