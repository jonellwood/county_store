<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

// Check if the array is already set in the session - the plan is to make this array global so it can be accessed elsewhere
if (!isset($_SESSION['employeeInv'])) {

    // If it's not set, initialize it as an empty array
    $_SESSION['employeeInv'] = array();
}

// Assign the array to a variable for easier use
$employeeInv = $_SESSION['employeeInv'];


require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// $_SESSION['dep_nums'] is an array of departments. The query below needs to loop over the array and return a list of all employees matching the query. 

$dep_list = $_SESSION['dep_nums'];
$empList = array();

// loop through each department number and query for each one
foreach ($dep_list as $dep_number) {
    $sql = "SELECT empNumber, empName from emp_ref WHERE deptNumber = $dep_number AND seperation_date is NULL";
    $result = mysqli_query($conn, $sql);

    // check for errs
    if (!$result) {
        die("Query failed:" . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($empList, $row);
    }
}

// $deptNumber = '41515';

// var_dump($empList[0]);
// var_dump($_SESSION['dep_nums']);

$employeeInv = array();
foreach ($empList as $employee) {

    // $query = "SELECT empNumber, empName, deptName FROM uniform_orders.emp_ref WHERE empNumber = '" . $employee['empNumber'] . "'";
    $query = "SELECT order_details_id, inv_status, inv_UID, emp_inv_assigned_to, dep_inv_assigned_to, product_code, product_price, size, color, ordered_for, ordered_for_department, logo, assigned_to_empName, size_name
    FROM uniform_orders.inv_ref
    WHERE emp_inv_assigned_to =  '" . $employee['empNumber'] . "'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $employeeInv[] = [
                'order_details_id' => $row['order_details_id'],
                'inv_status' => $row['inv_status'],
                'inv_UID' => $row['inv_UID'],
                'emp_inv_assigned_to' => $row['emp_inv_assigned_to'],
                'dep_inv_assigned_to' => $row['dep_inv_assigned_to'],
                'product_code' => $row['product_code'],
                'product_price' => $row['product_price'],
                'size' => $row['size'],
                'color' => $row['color'],
                'ordered_for' => $row['ordered_for'],
                'ordered_for_department' => $row['ordered_for_department'],
                'logo' => $row['logo'],
                'assigned_to_empName' => $row['assigned_to_empName'],
                'size_name' => $row['size_name']
            ];
        }
    } else {
        array_push($employeeInv, $employee['empNumber']); // push empNumber if nothing found to create the record
    }
};
// var_dump($empList);
// echo json_encode($empList);
// echo json_encode($employeeInv);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../favicons/favicon.ico">
    <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
    <title>Customers</title>
    <!-- alert('Emp ID is: ' + employeeID); -->

    <script>
    function removeTable(employeeID) {
        toRemove = document.getElementById("inv-holder-" + employeeID);
        toRemove.classList.add("hidden");
        location.reload();
    }


    async function getEmpInv(employeeID) {
        await fetch('./get-emp-inventory.php?emp_id=' + employeeID)
            .then(response => response.json())
            .then(data => {
                console.table(data);
                var html = "<td><table class='emp-inv-table'>"
                html +=
                    "<tr><th>Order ID</th><th>Product Code</th><th>Size</th><th>Color</th><th>Price</th><th>Logo</th></tr>"
                html += "<tr><td>"
                html += data[0].order_details_id;
                html += "</td>";
                html += "<td>";
                html += data[0].product_code;
                html += "</td>"
                html += "<td>";
                html += data[0].product_price;
                html + -"</td>";
                html += "<td>";
                html += data[0].color;
                html + -"</td>";
                html += "<td>";
                html += data[0].size_name;
                html + -"</td>";
                html += "<td>";
                html += "<img src=../" + data[0].logo + " alt='logo' width=50px />"
                html + -"</td>";
                html += "<td>";
                html += "<button type='button' value=" + employeeID +
                    " onclick='removeTable(this.value)' class='btn btn-info'>X</button>";
                html + -"</td>";

                html += "</tr></td></table>";
                document.getElementById("inv-holder-" + employeeID).innerHTML = html;
            })
    }
    </script>
</head>

<body>
    <div class="one-div">
        <?php include "inv-nav.php" ?>

        <div class="main">
            <h5><em>TODO:</em>
                <ul>
                    <li class='lilList'>Make secondary query a foreach loop to populate all items in inventory for that
                        employee.</li>
                    <li class='lilList'>Make this page pretty.</li>
                    <li class='lilList'>A11y audit - check contrast</li>
                </ul>
            </h5>
            <div class="container">
                <!-- <//?php foreach ($empList as $emp) { ?> -->
                <table class='main-table'>
                    <tr>
                        <th width=20%>Employee Name:</th>
                        <th width=20%>Employee Number:</th>
                        <th width=5%></th>
                    </tr>
                    <tr>
                        <?php foreach ($empList as $emp) {
                            echo "<tr><td>" . $emp['empName'] . "</td>";
                            echo "<td>" . $emp['empNumber'] . "</td>";
                            echo "<td><button type='button' value='" . $emp["empNumber"] . "' onclick='getEmpInv(this.value)'>GO!</button></td><tr>";
                            echo "<tr id='inv-holder-" . $emp["empNumber"] . "'></tr>";
                        }
                        ?>
                    </tr>
                    <!-- <tr id="inv-holder"></tr> -->

                </table>
                <hr>
            </div>
        </div>
    </div>
</body>

</html>

<style>
body {
    background-color: whitesmoke;
    margin-left: 15px;
    margin-right: 15px;

}

.nav-container {
    display: flex;
    position: absolute;
    margin-left: 20px;
    margin-top: 50px;
    max-width: 150px;
    margin-right: 20px;
}

.one-div {
    display: grid;
    grid-template-columns: 1fr;
}

li {
    text-decoration: none;
    list-style: none;
    font-size: x-large;
    text-transform: uppercase;
}

a {
    text-decoration: none;
    color: inherit;
}

a:hover {
    color: blue;
}

.main {
    /* position: relative; */
    margin-left: 15%;
    margin-top: 45px;
}

button {
    border-color: #005677;
    border-width: 1px;

}

table {
    width: 900px;
    table-layout: fixed;
}

thead tr th {
    color: #1e242b;
    padding: 15px;
    text-align: center;
    border-top: 3px solid #005677;
    /* border-left: 1px dashed #cbc8c7; */
    border-left: 1px dashed #005677;
}

tbody tr td {
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 5px;
    padding-bottom: 5px;
    text-align: center;
    /* border-top: 1px solid #005677; */
    border-top: 1px solid #789b48;
    border-left: 1px dashed #cbc8c7;
    font-weight: 600;
}

thead tr th:last-child {
    /* border-right: 1px dashed #cbc8c7; */
    border-right: 1px dashed #005677;
}

tbody tr:last-child {
    border-bottom: 1px solid #789b48;
}

tbody tr td:last-child {
    border-right: 1px dashed #cbc8c7;
}

tbody tr:nth-child(even) {
    background-color: #00567799;
}

code {
    color: #789b48;
}

.offcanvas {
    background-color: #cbc8c7;
    position: absolute !important;
    right: 0 !important;
    /* height: 75%; */
    color: #1e242b;
    display: block;
    overflow: scroll;
    height: 50vh !important;
    font-size: larger;
}

.offcanvas-start {
    right: 0 !important;
}



.btn-close {
    color: red;
    border: 1px solid red;
}

h5 {
    /* text-align: center; */
    color: #005677;
    border-bottom: #1e242b 1px solid;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

#left {

    margin-top: 20px;
    margin-left: 20px;
}

#left label {
    margin-right: 10px;
    font-size: larger;
}

#center {
    margin-top: 20px;
    margin-left: 20px;
}

#right {
    margin-top: 20px;
    margin-left: 20px;
}

.options-container {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
}

.button-span {
    width: 100%;
}

.btn-warning {
    /* background-color: #d5ca9e; */
    background-color: #f57f43;
    border: 1px solid #ff8800;
}


a:hover {
    color: blue;
}



.gimme-space {
    margin-bottom: 20px;
    text-align: justify;
}

th {
    text-align: center;
    font-weight: bolder;
}

/* tr {
        margin-bottom: 15px;
    } */

.headrow {
    padding-top: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.datarow {
    padding: 10px;
    text-align: center;
}

table {
    border-right: 2px solid #005677;
    box-shadow: 1px 0px #888888;
    margin-left: 15px;
}

caption {
    padding-bottom: 15px;
    font-weight: bold;
    font-size: x-large;
    border-bottom: 1px solid grey;
    box-shadow: 0px 1px #888888;
}

.hidden {
    display: none;
    /* visibility: hidden; */
}

.emp-inv-table {
    margin-left: -15px;
}

.main-table {
    font-size: medium;
}

.main-table th {
    border: #005677 2px solid;
    border-bottom: #888888 2px solid;
    padding-bottom: 10px;
    padding-top: 10px;


}

.lilList {
    font-size: 1em;
    list-style: lower-greek;
}
</style>