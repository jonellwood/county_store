<?php
session_start();
header('Content-type: application/json');
// require_once "config.php";
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());

$colorName = $_GET['colorName'];

function checkAndAddColor($colorName)
{
    require_once "config.php";
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

    $res = array();
    // Check if the color already exists in the table
    $checkQuery = "SELECT * FROM colors WHERE color = '$colorName'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Color already exists
        // echo "Color already exists.";
        array_push($res, [
            'res' => [$colorName . ' already exists.']
        ]);
    } else {
        // Add the color to the table
        $addQuery = "INSERT INTO colors (color) VALUES ('$colorName')";
        mysqli_query($conn, $addQuery);

        // Close the database connection
        mysqli_close($conn);

        // Return success message
        // echo "Color added successfully.";
        // $res['result'] = "$colorName . ' added to database'";
        array_push($res, [
            'res' => [$colorName . ' added Successfully.']
        ]);
    }
    echo json_encode($res);
}

checkAndAddColor($colorName);
