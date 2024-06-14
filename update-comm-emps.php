<?php
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


function copyEmpNumbers($conn)
{

    // Get current date/time
    $current_date = date('Y-m-d H:i:s');
    // echo $current_date;

    // Query the source table
    $source_query = "SELECT empNumber, empName 
                FROM uniform_orders.emp_ref
                WHERE deptNumber = 42103 AND seperation_date is null";
    $source_result = $conn->query($source_query);

    // Check if there are any new or updated rows in the source table
    $diff_query = "SELECT empNumber, empName FROM emp_ref WHERE updated > DATE_SUB('$current_date', INTERVAL 1 DAY) AND deptNumber = 41203 AND seperation_date is null";
    $diff_result = mysqli_query($conn, $diff_query);

    // Check if there are any differences and update the commEmp table if necessary
    if (mysqli_num_rows($diff_result) > 0) {
        while ($row = mysqli_fetch_assoc($diff_result)) {
            // Update commEmp table with new or updated rows
            $empNumber = $row['empNumber'];
            $empName = $row['empName'];
            $update_query = "REPLACE INTO comm_emps (empNumber, empName) VALUES ('$empNumber', '$empName')";
            mysqli_query($conn, $update_query);
        }
    }

    // Close the database connection
    // mysqli_close($conn);
}
