<?php

session_start();

include "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$dept = '47090';
// $dept = $_SESSION['department']; or in reality we can just pass the $_SESSION variable in down below without assigning to a variable most likely.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Department Logos</title>
</head>

<body>
    <div class='image-holder'>
        <!-- <img src="./dept_logos/41210.png" alt='some_logo'> -->
        <!-- The actual path may need to be edited based on where images are located in reference to the page displaying them -->
        <!-- <img src="./dept_logos/42101.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/42102.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/42103.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/43107.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/44104.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/45201.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/45519.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/47090.png" alt='some_logo'> -->
        <!-- <img src="./dept_logos/47214.png" alt='some_logo'> -->
        <img src="./dept_logos/<?php echo $dept ?>.png" alt='some_logo'>
    </div>


</body>

</html>

<style>
.image-holder {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
}

img {
    width: 50%;
    border: 1px solid lightblue;
    align-self: center;

    justify-self: center;
}
</style>