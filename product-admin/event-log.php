<?php

session_start();
if (!isset($_SESSION["pa_loggedin"]) || $_SESSION["pa_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$events = array();
$sql = "SELECT e.timestamp, e.created_by_emp_id, e.event_type, e.assigned_to_emp_id, e.removed_from_emp_id, e.order_details_id,
i.product_code, i.product_name
from uniform_orders.events e
JOIN inv_ref i on e.inv_UID = i.inv_UID";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}
// echo "<pre>";
// echo print_r($events);
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="icon" href="./favicons/favicon.ico">
    <title>Event Viewer for County Store</title>
</head>

<body>
    <?php include "nav.php" ?>
    <div class="header-holder">
        <h3>IMS Event Log Viewer</h3>
        <!-- <a href='index.php'><button>Back</button></a> -->
    </div>
    <div class="body">


        <table class="styled-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Initiator</th>
                    <th>Event Type</th>
                    <th colspan="2">Product</th>
                    <th>Emp Assigned To</th>
                    <th>Emp Removed From</th>
                    <th>Order Details ID</th>
                    <!-- LEts make this od_id a link to a detailed page about that specific order.  -->
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($events as $e) {
                    echo "<tr>";
                    echo "<td>" . $e['timestamp'] . "</td>";
                    echo "<td>" . $e['created_by_emp_id'] . "</td>";
                    echo "<td>" . $e['event_type'] . "</td>";
                    echo "<td>" . $e['product_code'] . "</td>";
                    echo "<td>" . $e['product_name'] . "</td>";
                    echo "<td>" . $e['assigned_to_emp_id'] . "</td>";
                    echo "<td>" . $e['removed_from_emp_id'] . "</td>";
                    echo "<td><a href='order-details-details.php?od_id=" . $e['order_details_id'] . "' target='_black'>" . $e['order_details_id'] . "</a></td>";
                    echo "</tr>";
                };
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<style>
/* html {
        background: rgb(247, 195, 177);
        background: radial-gradient(circle, rgba(247, 195, 177, 1) 0%, rgba(235, 101, 54, 1) 50%, rgba(132, 62, 100, 1) 100%);

    } */

/* body {
        margin: 10px;
    } */

/* table {
        width: 95%;
        margin-left: auto;
        margin-right: auto;
        font-size: large;
    } */


/* td {
        vertical-align: middle;
    }

    thead tr th {
        color: #1e242b;
        padding: 15px;
        text-align: center;
        border-top: 3px solid #77216F;
        border-left: 1px dashed #77216F;
    }


    tbody tr td {
        padding-left: 15px;
        padding-right: 15px;
        padding-top: 5px;
        padding-bottom: 5px;
        text-align: center;

        border-top: 1px solid #2C001E;
        border-left: 1px dashed #2C001E;
        font-weight: 400;

    }

    thead tr th:last-child {

        border-right: 1px dashed #77216F;
    }

    tbody tr:last-child {
        border-bottom: 1px solid #789b48;
    }

    tbody tr td:last-child {
        border-right: 1px dashed #2C001E;
    } */

/* orange and white rows */
/* tbody tr:nth-child(even) {

        background-color: #E9542040;
        color: #ffffff;
    } */

/* black and white rows */
/* tbody tr:nth-child(odd) {
        background-color: #e4e2e040;
        color: #212121;
    } */

button {
    font-size: smaller;
}

.dh_confirm,
.da_confirm,
.am_confirm {
    position: absolute;
    top: 0;
    margin-top: 150px;
    border: 1px solid #772953;
    border-top: 10px solid #E95420;
    border-radius: 10px;
    bottom: 0;
    margin-bottom: 250px;
    left: 0;
    margin-left: 35%;
    right: 0;
    margin-right: 35%;
    z-index: 2;
    background-color: #f0f0f0;
    max-height: 600px;
    padding: 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    align-content: center;
    -webkit-box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
    -moz-box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
    box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
}

.button-holder {
    display: inline-flex;
    gap: 20px;
    margin: auto;
}

.dh_button,
.da_button,
.am_button {
    font-size: medium;
    font-weight: bolder;
    margin-top: 20px;
    padding: 5px;
    border-width: 2px;
}


.dh_button:hover,
.da_button:hover,
.am_button:hover {
    transform: rotate(-2deg);
}

#dh_1,
#da_1,
#am_1 {
    color: #0F9D58;
    border-color: #0F9D58;
}

#hide {
    color: #DB4437;
    border-color: #E95420;
}

.dh_h1,
.da_h1,
.am_h1 {
    color: #E95420;
    font-weight: bolder;
    text-transform: uppercase;
}

.hidden {
    display: none;
}
</style>