<?php

session_start();
header('Content-type: application/json');

$userID = $_GET['userID'];

function checkAndAddUser($userID)
{
    require_once "config.php";
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

    $res = array();
    // check for user already in system
    $checkQuery = "SELECT * from users where emp_num = '$userID'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        array_push($res, [
            'res' => [$userID . ' already exists.']
        ]);
    } else {
        // add user with default role (for now)
        $addQuery = "INSERT into users (emp_num) VALUES ('$userID')";
        mysqli_query($conn, $addQuery);

        // close db conn
        mysqli_close($conn);

        array_push($res, [
            'res' => [$userID . ' added successfuly.']
        ]);
    }
    echo json_encode($res);
}
checkAndAddUser($userID);
