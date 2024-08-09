<?php

include_once "config.php";

$sql = "SELECT empNumber, empName from uniform_orders.emp_ref WHERE seperation_date IS NULL ORDER BY empNumber ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='dropdown'>";
    echo "<button type='button' onclick='subToggle()' class='btn btn-primary'>Being ordered by...</button>";
    echo "<div id='subDropdown' class='dropdown-content'>";
    // echo "<label for='emp_number'>Select Your Employee Name:</label>";
    echo "<div title='sub_number' name='sub_number' id='sub_number' class='form-control' >";
    echo "<input type='text' placeholder='Search...' id='subInput' onkeyup='filterFunctionSub()'>";
    while ($row = $result->fetch_assoc()) {
        echo "<button type='button' class='invisi-button' onclick='setSub(this.value)' value=" . $row['empNumber'] . ">" . $row['empName'] . "</button>";
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";
}