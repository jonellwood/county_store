<?php

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


function copyEmpNumberToCommEmp($conn)
{
    // Set the query to select empNumber from the source table
    $sql = "SELECT empNumber
            FROM uniform_orders.emp_ref
            WHERE deptNumber = 42103 AND seperation_date is null";

    // Execute the query and retrieve the results
    $result = $conn->query($sql);

    // Check if there are any rows returned
    if ($result->num_rows > 0) {
        // Loop through each row and insert the empNumber into the target table
        while ($row = $result->fetch_assoc()) {
            $empNumber = $row['empNumber'];
            $insertSql = "INSERT INTO comm_emps (empNumber) VALUES ('$empNumber')";
            $conn->query($insertSql);
        }
    }
}

function copyEmpNumbers()
{
    // connection info
    require_once 'config.php';
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());

    // Get current date/time
    $current_date = date('Y-m-d H:i:s');

    // Query the source table
    $source_query = "SELECT empNumber FROM FROM uniform_orders.emp_ref
            WHERE deptNumber = 42103 AND seperation_date is null";
    $source_result = mysqli_query($conn, $source_query);

    // Check if there are any new or updated rows in the source table
    $diff_query = "SELECT empNumber FROM emp_ref WHERE last_updated > DATE_SUB('$current_date', INTERVAL 1 DAY)";
    $diff_result = mysqli_query($conn, $diff_query);

    // Check if there are any differences and update the commEmp table if necessary
    if (mysqli_num_rows($diff_result) > 0) {
        while ($row = mysqli_fetch_assoc($diff_result)) {
            // Update commEmp table with new or updated rows
            $empNumber = $row['empNumber'];
            $update_query = "REPLACE INTO comm_emps (empNumber) VALUES ('$empNumber')";
            mysqli_query($conn, $update_query);
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
