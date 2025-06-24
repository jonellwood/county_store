<?php

function get_user_deets($ldapUser)
{
    require_once '../config.php';
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());

    // First query to get user details
    $sql = "SELECT empNumber, empName, deptNumber
    FROM emp_ref
    WHERE email = '$ldapUser'";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $_SESSION["im_loggedin"] = true;
        $_SESSION["name"] = $row['empName'];
        $_SESSION["empNumber"] = $row['empNumber'];

        // Second query to get department info based on dept_head
        $dep_sql = "SELECT dep_num
        FROM departments
        WHERE dep_head = '" . $row['deptNumber'] . "'";

        $dep_result = mysqli_query($conn, $dep_sql);
        $dep_num_array = array();

        while ($dep_row = mysqli_fetch_assoc($dep_result)) {
            $dep_num_array[] = $dep_row['dep_num'];
        }

        $_SESSION["dep_nums"] = $dep_num_array;
    }
    $conn->close();
}

function get_emps_from_all_deps($dep_list)
{


    // Connect to the database
    $servername = "localhost";
    $username = "username";
    $password = "password";
    $dbname = "database_name";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check for errors
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Define the dep_list array
    $dep_list = array(1, 2, 3, 4, 5);

    // Loop through each department number and execute a SQL query for each one
    foreach ($dep_list as $dep_number) {
        $sql = "SELECT empName FROM emp_ref WHERE depNumber = $dep_number";
        $result = mysqli_query($conn, $sql);

        // Check for errors
        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }

        // Print the results
        while ($row = mysqli_fetch_assoc($result)) {
            echo "Employee Name: " . $row["empName"] . "<br>";
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
