<?php

include_once "config.php";

$sql = "SELECT empNumber, empName from uniform_orders.emp_ref WHERE seperation_date IS NULL ORDER BY empNumber ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='dropdown'>";
    echo "<button type='button' onclick='toggle()' class='btn btn-success dropbtn'>This order is for...</button>";
    echo "<div id='empDropdown' class='dropdown-content'>";
    // echo "<label for='emp_number'>Select Your Employee Name:</label>";
    echo "<div title='emp_number' name='emp_number' id='emp_number' class='form-control' >";
    echo "<input type='text' placeholder='Search...' id='empInput' onkeyup='filterFunction()'>";
    while ($row = $result->fetch_assoc()) {
        echo "<button type='button' class='btn invisi-button' onclick='setData(this.value)' value=" . $row['empNumber'] . ">" . $row['empName'] . "</button>";
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";
}
