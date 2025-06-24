<?php
require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// check for logged in. If yes send to dashboard
if (isset($_SESSION["pa_loggedin"]) && $_SESSION["pa_loggedin"] === true) {
    header("location: index.php");
    exit;
};

$sql = "SELECT u.emp_num, u.empName, u.email, u.role_name, 
d1.dep_num as dep_num_1, d1.dep_name as dep_name_1, 
d2.dep_num as dep_num_2, d2.dep_name as dep_name_2, 
d3.dep_num as dep_num_3, d3.dep_name as dep_name_3
FROM user_ref u
LEFT JOIN dep_ref d1 ON u.emp_num = d1.dep_head
LEFT JOIN dep_ref d2 on u.emp_num = d2.dep_assist
LEFT JOIN dep_ref d3 on u.emp_num = d3.dep_asset_mgr";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) > 0) {
    // Initialize an associative array to store data by employee
    $employeeData = array();

    // Loop through the results and organize the data by employee
    while ($row = mysqli_fetch_assoc($result)) {
        $empNum = $row['emp_num'];

        // Check if the employee exists in the associative array
        if (!isset($employeeData[$empNum])) {
            // If not, initialize the employee data
            $employeeData[$empNum] = array(
                'emp_num' => $empNum,
                'empName' => $row['empName'],
                'role_name' => $row['role_name'],
                'departments' => array(),
            );
        }

        // Add the department information to the employee's data
        $employeeData[$empNum]['departments'][] = array(
            'dep_num_1' => $row['dep_num_1'],
            'dep_name_1' => $row['dep_name_1'],
            'dep_num_2' => $row['dep_num_2'],
            'dep_name_2' => $row['dep_name_2'],
            'dep_num_3' => $row['dep_num_3'],
            'dep_name_3' => $row['dep_name_3'],
        );
    }
    // echo "<pre>";
    // echo var_dump($employeeData);
    // echo "</pre>";
    include "nav.php";
    echo "<h1>User Management Page that looks like 💩 ";
    // echo "<button><a href='index.php'>Back to Dashboard</a></button> </h1>";
    echo "<div class='dealer'>";
    // Loop through the employee data and generate cards
    foreach ($employeeData as $employee) {
        echo "<div class='card'>";
        echo "<div class='emp'>";
        echo "<p class='bold'>Name: {$employee['empName']}";
        echo " ({$employee['emp_num']})</p>";
        echo "<p class='bold'>Role Name: {$employee['role_name']}</p>";
        
        
        // echo "</div>";
        // Loop through the departments assigned to the employee
        // echo "<tr>";
        // echo "<th colspan='4' class='departments'>Departments</th>";
        // echo "</tr>";
        
        echo "<div class='right'> <h4>Departments</h4>";
        
        foreach ($employee['departments'] as $department) {
            if (isset($department['dep_name_1']) && isset($department['dep_num_1'])) {
                echo "<p> {$department['dep_name_1']} ({$department['dep_num_1']}) </p>";
            }

            if (isset($department['dep_name_2']) && isset($department['dep_num_2'])) {
                echo "<p> {$department['dep_name_2']} ({$department['dep_num_2']}) </p>";
            }

            if (isset($department['dep_name_3']) && isset($department['dep_num_3'])) {
                echo "<p> {$department['dep_name_3']} ({$department['dep_num_3']}) </p>";
            }
        }
        echo "</div>";
        echo "<p><button onclick='deleteUser()'>Delete User</button> <button><a href='dept-admin.php'>Edit Departments</a></button></p>";
        // echo "<tr>";
        // echo "<button>Delete User</button>";
        // echo "<button>Edit Role</button>";
        // echo "</tr>";
        // echo "</table>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "No records found.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon-16x16.png">
    <title>User Management</title>
    <script>
    function deleteUser() {
        alert('🚫 Not yet. Gotta think about the best way to do this. 🚫')
    }
    </script>
</head>

<body>




</body>

</html>


<style>
html {
    margin: 0px;
    padding: 40px;
    /* color: whitesmoke; */
    background-color: #0d0e0e;
    background-image: linear-gradient(0deg, #0d0e0e 27%, #5e5e6a 100%);
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
    /* background-repeat: no-repeat; */
}

body {
    /* margin: 20px; */
    background-color: #5e5e6a;
}

/* becuase hes holding all the cards */
.dealer {
    display: grid;
    grid-template-columns: auto auto;
    gap: 25px;
    margin-left: 5%;

    margin-right: auto;
}

.card {
    border: 2px solid #DBDBDB;
    border-radius: 15px;
    padding: 10px;
    /* height: 300px; */
    width: 600px;
    background-color: #999999;
    box-shadow: 0px 0px 20px 7px rgba(0, 0, 0, 0.75);
}

/* .button-holder {
    display: flex;
    justify-content: space-between;
    bottom: 0; */
/* } */


.top-row td {
    border-top: 4px solid whitesmoke;
    padding: 10px;
    padding-top: 15px;
    background-color: azure;
}

.departments {
    background-color: lightblue !important;
}

.right {
    border-top: 1px solid darkgray;
    border-bottom: 1px solid darkgray;
    padding-top: 5px;
    font-weight: 400;
}

.bold {
    font-weight: bold;
}

/* .emp {
    border-bottom: 5px solid red !important;
    padding-bottom: 20px;

} */
</style>