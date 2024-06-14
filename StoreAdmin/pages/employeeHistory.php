<?php
include('DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Requests</title>
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <script async defer src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <title>Employee History Report</title>
    <script>
        async function getAllEmployeeRequests(emp) {
            await fetch('getAllEmpRequests.php?emp=' + emp)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    var html = ""
                    html += "<div class='main-order-info-holder'>";
                    html += "<span class='table-title'>Historical Report for " + data[0].rf_first_name + ' ' + data[
                        0].rf_last_name + "</span>";
                    html += "<table class='styled-table'>";
                    html += "<thead>";
                    html += "<tr>";
                    html += "<th>Date Submitted</th>";
                    html += "<th>Date Ordered</th>";
                    html += "<th>Qty</th>";
                    html += "<th colspan=4>Product</th>"; // product code and name and color_id and size_name
                    html += "<th>Logo</th>";
                    html += "<th>Dept Placement</th>";
                    html += "<th>Total</th>";
                    html += "<th>Status</th>";
                    html += "<th>Department</th>";
                    html +=
                        "<th>Comment</th>"; // this will be expanded to include all comments for this request - need additional query somehow?
                    html += "</tr>"
                    html += "</thead>";
                    html += "<tbody>";
                    for (var i = 0; i < data.length; i++) {
                        html += "<tr class='" + data[i].status + "'>";
                        html += "<td>" + data[i].created + "</td>";
                        html += "<td>" + data[i].ordered + "</td>";
                        html += "<td>" + data[i].quantity + "</td>";
                        html += "<td>" + data[i].product_code + "</td>";
                        html += "<td>" + data[i].product_name + "</td>";
                        html += "<td>" + data[i].color_id + "</td>";
                        html += "<td>" + data[i].size_name + "</td>";
                        html += "<td class='img'><img src=../../" + data[i].logo + " alt='BCG Logo'</td>";
                        html += "<td>" + data[i].dept_patch_place + "</td>";
                        html += "<td>" + data[i].line_item_total + "</td>";
                        html += "<td>" + data[i].status + "</td>";
                        html += "<td>" + data[i].department + "</td>";
                        html += "<td>" + data[i].comment + "</td>";
                        html += "</tr>";
                    }
                    html += "</tbody>";
                    html += "</table>";
                    document.getElementById('main').innerHTML = html;
                })
        }
        getAllEmployeeRequests(4438)
    </script>
</head>

<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2" id="main">MAIN</div>
        <div class="div3" id="details"></div>
        <div class="div4">
            <?php include('hideTopNav.php'); ?>
        </div>
        <div class="div5" id="depTotals"></div>

    </div>
</body>

</html>

<style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    * {
        margin: 0;
    }

    html {
        height: 100%;
    }

    body {
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    img,
    picture,
    video,
    canvas,
    svg {
        display: block;
        max-width: 100%;
    }

    input,
    button,
    textarea,
    select {
        font: inherit;
    }

    p,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        overflow-wrap: break-word;
    }

    #root,
    #__next {
        isolation: isolate;
    }

    .parent {
        display: grid;
        grid-template-columns: 10% 28% 35% 25%;
        grid-template-rows: 75px 1fr 1fr;
        height: 100vh;
        /* overflow: hidden; */
    }

    .div1 {
        display: flex;
        grid-area: 1 / 1 / 3 / 1;

    }

    .div2 {
        display: flex;
        grid-area: 2 / 2 / 2 / 4;
        /* height: 100vh; */
        scrollbar-gutter: stable;
    }

    .div3 {
        display: flex;
        grid-area: 2 / 3 / 2 / 3;
        height: 100vh;
        scrollbar-gutter: stable;
        padding-left: 20px;
        overflow-y: auto;
    }

    .div4 {
        display: flex;
        grid-area: 1 / 2 / 1 / 5;
    }

    .div5 {
        display: flex;
        grid-area: 2/ 4 / 2 /4;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }

    .div6 {
        display: flex;
        flex-direction: column;
        /* grid-area: 3/4/3/4; */
    }

    #main {
        overflow-y: auto;

    }

    .emp-info-holder,
    .main-list-holder,
    .main-order-info-holder {
        border-top: #1aa260 44px solid;
        font-family: robofont;
        margin-top: 25px;

    }

    .dep-info-holder {
        border-top: #1aa260 44px solid;
        font-family: robofont;
        margin-top: 25px;
    }

    .details {
        box-shadow: 10px 10px 38px -17px rgba(59, 54, 59, 1);
    }

    .table-title {
        display: flex;
        justify-content: center;
        font-family: robofont;
        text-wrap: balance;
        font-size: max(1.25vw, 14px);
        text-align: center;
        padding-top: 20px;
        width: auto;
    }

    .totals td {
        text-align: center;
    }

    .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.8em;
        /* font-family: sans-serif; */
        font-family: robofont;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        position: sticky;
        top: 0;
        background-color: #1aa260;
        color: #ffffff;
        text-align: center;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
        height: 75px;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #009879;
    }

    .order-details-row th {
        border-right: #057a6d 1px solid;
    }

    .orders-list-row th {
        border-right: #057a6d 1px solid;
    }

    tr:nth-child(odd) td {
        border-right: 1px solid #f3f3f3;
    }

    tr:nth-child(even) td {
        border-right: 1px solid #e1e0db;
    }

    .Pending td {
        background-color: #b3ffb3;
        /* color: #008000; */
        border-bottom: #008000 2px solid;
    }

    .Approved td {
        background-color: #b3b3ff;
        /* color: #0000ff; */
        border-bottom: #0000ff 2px solid;
    }

    .Denied td {
        background-color: #ff000050;
        /* color: #ff0000; */
        border-bottom: #ff0000 2px solid;
    }

    .Ordered td {
        background-color: #ffb3b3;
        /* color: #ff0000; */
        border-bottom: #ff0000 2px solid;
    }

    .Received td {
        background-color: #ffedcc;
        /* color: #ffa500; */
        border-bottom: #ffa500 2px solid;
    }

    td.img {
        background-color: #808080;
    }

    tr img {
        width: 50px !important;
    }

    .receipt {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
    }
</style>