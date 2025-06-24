<?php

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function copyEmpNumberToCommEmp($conn)
{
    // Set the query to select empNumber from the source table
    $sql = "SELECT empNumber, empName, email, updated
    FROM uniform_orders.emp_ref
    WHERE deptNumber = 42103 AND seperation_date is null";

    // Execute the query and retrieve the results
    $result = $conn->query($sql);

    // Check if there are any rows returned
    if ($result->num_rows > 0) {
        // Loop through each row and insert the empNumber into the target table
        while ($row = $result->fetch_assoc()) {
            $empNumber = $row['empNumber'];
            $empName = $row['empName'];
            $empEmail = $row['email'];
            $lastUpdated = $row['updated'];
            $insertSql = "INSERT INTO comm_emps (empNumber, empName, empEmail, last_updated) VALUES ('$empNumber', '$empName', '$empEmail', '$lastUpdated')";
            $conn->query($insertSql);
        }
    }
}
