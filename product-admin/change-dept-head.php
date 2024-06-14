<?php

session_start();


require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp = strip_tags($_GET['emp']);
$dep = strip_tags(intval($_GET['dep']));

$sql = "UPDATE departments SET dep_head = $emp WHERE dep_num = $dep";
$stmt = $conn->prepare($sql);
$stmt->execute();

if ($stmt->affected_rows > 0) {
// if ($stmt) {
    // The update was successful, now check if the employee exists in the users table
    $checkUserQuery = "SELECT * FROM users WHERE emp_num = '$emp'";
    $userResult = mysqli_query($conn, $checkUserQuery);

    if (mysqli_num_rows($userResult) == 0) {
        // Employee does not exist in the users table, insert them with default role_id
        $insertUserQuery = "INSERT INTO users (emp_num, role_id) VALUES ('$emp', 2)";
        mysqli_query($conn, $insertUserQuery);
    }

    echo "Employee assigned as department head and user table updated successfully.";
} else {
    // The update failed
    echo "Error updating department table:";
}

// Close the database connection
$stmt->close();
mysqli_close($conn);