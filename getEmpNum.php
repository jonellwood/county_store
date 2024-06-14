<?php

include_once "config.php";

$sql = "SELECT empNumber, empName from uniform_orders.emp_ref ORDER BY empNumber ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<label for='emp_number'>Select Your Employee Number:</label>";
    echo "<select title='emp_number' name='emp_number' id='emp_number' class='form-control' onchange='getEmpData(this.value)'>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value=" . $row['empNumber'] . ">" . $row['empName'] . "</option>";
    }
    echo "</select>";
}