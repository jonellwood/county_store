<?php

session_start();

if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}
require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$approvedSql = "SELECT count(distinct(order_details_id)) as acount FROM uniform_orders.order_details where status = 'Approved' ";
$approvedStmt = $conn->prepare($approvedSql);
$approvedStmt->execute();
$approvedResult = $approvedStmt->get_result();
if ($approvedResult->num_rows > 0) {
    while ($approvedRow = $approvedResult->fetch_assoc()) {
        $approvedCount = $approvedRow['acount'];
    }
}

$availableSql = "SELECT count(distinct(inv_UID)) as icount FROM uniform_orders.inv_ref where inv_status = 'Available'";
$availableStmt = $conn->prepare($availableSql);
$availableStmt->execute();
$availableResult = $availableStmt->get_result();
if ($availableResult->num_rows > 0) {
    while ($availableRow = $availableResult->fetch_assoc()) {
        $availableCount = $availableRow['icount'];
    }
}
$assignedSql = "SELECT count(distinct(inv_UID)) as icount FROM uniform_orders.inv_ref where inv_status = 'Assigned'";
$assignedStmt = $conn->prepare($assignedSql);
$assignedStmt->execute();
$assignedResult = $assignedStmt->get_result();
if ($assignedResult->num_rows > 0) {
    while ($assignedRow = $assignedResult->fetch_assoc()) {
        $assignedCount = $assignedRow['icount'];
    }
}
$orderedSql = "SELECT count(distinct(order_details_id)) as ocount FROM uniform_orders.order_details where status = 'Ordered' ";
$orderedStmt = $conn->prepare($orderedSql);
$orderedStmt->execute();
$orderedResult = $orderedStmt->get_result();
if ($orderedResult->num_rows > 0) {
    while ($orderedRow = $orderedResult->fetch_assoc()) {
        $orderedCount = $orderedRow['ocount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon.png">
    <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
    <title>Inventory Management Dashboard</title>
</head>

<body>
    <div class="main">
        <?php include "inv-nav.php" ?>
        <div class="container">
            <div class="top-line">
                <Tile class="approved-card">
                    <svg id="icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: none;
                                }
                            </style>
                        </defs>
                        <path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2ZM14,21.5908l-5-5L10.5906,15,14,18.4092,21.41,11l1.5957,1.5859Z" />
                        <polygon id="inner-path" class="cls-1" points="14 21.591 9 16.591 10.591 15 14 18.409 21.41 11 23.005 12.585 14 21.591" />
                        <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
                    </svg>
                    <div class="sub-card">
                        <h5>
                            <?php echo $approvedCount ?>
                        </h5>
                        <p>Total Orders Approved</p>
                    </div>
                </Tile>
                <Tile class="available-card">
                    <svg id="icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: none;
                                }
                            </style>
                        </defs>
                        <rect x="4" y="16" width="12" height="2" />
                        <rect x="2" y="11" width="10" height="2" />
                        <path d="M29.9189,16.6064l-3-7A.9985.9985,0,0,0,26,9H23V7a1,1,0,0,0-1-1H6V8H21V20.5562A3.9924,3.9924,0,0,0,19.1421,23H12.8579a4,4,0,1,0,0,2h6.2842a3.9806,3.9806,0,0,0,7.7158,0H29a1,1,0,0,0,1-1V17A.9965.9965,0,0,0,29.9189,16.6064ZM9,26a2,2,0,1,1,2-2A2.0023,2.0023,0,0,1,9,26ZM23,11h2.3408l2.1431,5H23Zm0,15a2,2,0,1,1,2-2A2.0023,2.0023,0,0,1,23,26Zm5-3H26.8579A3.9954,3.9954,0,0,0,23,20V18h5Z" />
                        <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
                    </svg>
                    <div class="sub-card">
                        <h5><?php echo $availableCount ?></h5>
                        <p>Total Inventory Available</p>
                    </div>
                </Tile>
                <Tile class="assigned-card">
                    <svg id="icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: none;
                                }
                            </style>
                        </defs>
                        <title>unavailable</title>
                        <rect x="4" y="15" width="10" height="2" />
                        <rect x="18" y="15" width="10" height="2" />
                        <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
                    </svg>
                    <div class="sub-card">
                        <h5><?php echo $assignedCount ?></h5>
                        <p>Total Inventory Assigned</p>
                    </div>
                </Tile>
                <Tile class="ordered-card">
                    <svg id="icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: none;
                                }
                            </style>
                        </defs>
                        <title>building</title>
                        <path d="M28,2H16a2.002,2.002,0,0,0-2,2V14H4a2.002,2.002,0,0,0-2,2V30H30V4A2.0023,2.0023,0,0,0,28,2ZM9,28V21h4v7Zm19,0H15V20a1,1,0,0,0-1-1H8a1,1,0,0,0-1,1v8H4V16H16V4H28Z" />
                        <rect x="18" y="8" width="2" height="2" />
                        <rect x="24" y="8" width="2" height="2" />
                        <rect x="18" y="14" width="2" height="2" />
                        <rect x="24" y="14" width="2" height="2" />
                        <rect x="18" y="19.9996" width="2" height="2" />
                        <rect x="24" y="19.9996" width="2" height="2" />
                        <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
                    </svg>
                    <div class="sub-card">
                        <h5><?php echo $assignedCount ?></h5>
                        <p>Total Inventory Items on Order</p>
                    </div>
                </Tile>
            </div>
        </div>
        <hr>
        <div class="cool-stuff">
            <?php
            $summarySql = "SELECT product_name,COUNT(1) as OnHandValue FROM inv_ref WHERE inv_status = 'Available' GROUP BY product_code ORDER BY OnHandValue;";
            $summaryStmt = $conn->prepare($summarySql);
            $summaryStmt->execute();
            $summaryResult = $summaryStmt->get_result();
            if ($summaryResult->num_rows > 0) {
            ?>


                <h2 class="gimme-space"><i class="fa-brands fa-pied-piper-hat"></i>Inventory Summary</h2>
                <div class="summary-holder">


                    <table>
                        <caption>Inventory On Hand</caption>
                        <thead>
                            <tr>
                                <th class='headrow'>Qty on Hand</th>
                                <th class='headrow'>Item Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class='datarow'>
                            <?php
                            while ($summaryRow = $summaryResult->fetch_assoc()) {
                                echo "<td class='datarow'>" . $summaryRow['OnHandValue'] . "</td>";
                                echo "<td class='datarow'>" . $summaryRow['product_name'] . "</td></tr>";
                            }
                        }
                            ?>
                            <?php
                            $asummarySql = "SELECT product_name,COUNT(1) as OnHandValue FROM inv_ref WHERE inv_status = 'Assigned' GROUP BY product_code ORDER BY OnHandValue;";
                            $asummaryStmt = $conn->prepare($asummarySql);
                            $asummaryStmt->execute();
                            $asummaryResult = $asummaryStmt->get_result();
                            ?>
                        </tbody>
                    </table>

                    <table>
                        <caption>Inventory Assigned</caption>
                        <thead>
                            <tr class='headrow'>
                                <th class='headrow'>Qty Assigned</th>
                                <th class='headrow'>Item Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                while ($asummaryRow = $asummaryResult->fetch_assoc()) {
                                    echo "<td class='datarow'>" . $asummaryRow['OnHandValue'] . "</td>";
                                    echo "<td class='datarow'>" . $asummaryRow['product_name'] . "</td></tr>";
                                }

                                ?>
                        </tbody>
                    </table>



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

    /* .nav-shift {
        position: absolute;
        left: 0;
    } */

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
        position: relative;
    }



    .top-line {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        margin-top: 10px;
        margin-left: 14%;
        margin-right: auto;
        margin-bottom: 10px;
        padding-top: 50px;
        max-width: 900px;
    }

    .approved-card {
        border-top: 4px solid green;
        text-align: center;
        margin: 10px;
        width: 12rem;
        background-color: white;
        display: grid;
        grid-template-columns: 1fr 3fr;
        padding-top: 5px;
        padding-left: 5px;
    }

    .available-card {
        border-top: 4px solid blue;
        text-align: center;
        margin: 10px;
        width: 12rem;
        background-color: white;
        display: grid;
        grid-template-columns: 1fr 3fr;
        padding-top: 5px;
        padding-left: 5px;
    }

    .assigned-card {
        border-top: 4px solid purple;
        text-align: center;
        margin: 10px;
        width: 12rem;
        background-color: white;
        display: grid;
        grid-template-columns: 1fr 3fr;
        padding-top: 5px;
        padding-left: 5px;
    }

    .ordered-card {
        border-top: 4px solid red;
        text-align: center;
        margin: 10px;
        width: 12rem;
        background-color: white;
        display: grid;
        grid-template-columns: 1fr 3fr;
        padding-top: 5px;
        padding-left: 5px;
    }

    .sub-card {
        padding-top: 5px;
        padding-left: 5px;
        padding-right: 5px;
        display: grid;
        grid-template-rows: 1fr 1fr;
    }

    .summary-holder {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
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
        border-right: 1px solid grey;
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
</style>