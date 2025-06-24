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
    <title>Request Management</title>
    <script>
        async function getRequests() {
            await fetch('./getAllRequests.php')
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    var html = '';
                    html += "<div class='main-list-holder' id='main-list-holder'>";
                    html += "<span class='table-title'>Employee Requests</span>";
                    html += "<table class='styled-table'>";
                    html += "<thead>";
                    html += "<tr>";
                    html += "<th>Order ID</th>";
                    html += "<th>Grand Total</th>";
                    html += "<th>Created</th>";
                    html += "<th>Requested For</th>";
                    html += "</tr>"
                    html += "</thead>";
                    html += "<tbody>";
                    for (var i = 0; i < data.length; i++) {
                        html += "<tr value='" + data[i].order_id + "' onclick=getOrderDetails(" + data[i].order_id +
                            ")>";
                        html += "<td>" + data[i].order_id + "</td>";
                        html += "<td>" + money_format(data[i].grand_total) + "</td>";
                        html += "<td>" + extractDate(data[i].created) + "</td>";
                        html += "<td>" + data[i].requested_for + "</td>";
                        html += "</tr>";
                    }
                    html += "</tbody>";
                    html += "</table>";
                    html += "</div>";
                    document.getElementById('main').innerHTML = html;
                })
        }
        getRequests();

        function money_format(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }

        function extractDate(inputString) {
            const parts = inputString.split(' ');
            return parts[0];
        }

        function extractDateFromDB(inputString) {
            const parts = inputString.split('T');
            return parts[0];
        }

        async function getOrderDetails(order_id) {
            console.log('order_id', order_id);
            await fetch('getOrderDetails.php?id=' + order_id)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data)
                    console.log('department is :' + data[0].department);
                    var department = data[0].department;
                    console.log('department is :' + department);
                    var empID = data[0].emp_id;
                    var totalCost = 0
                    var totalCount = 0
                    var grogu = '';
                    grogu += "<div class='main-order-info-holder'>";
                    grogu += "<span class='table-title'>Order Details for " + data[0].rf_first_name + ' ' + data[0]
                        .rf_last_name +
                        " </span>"
                    grogu += "<table class='styled-table'>";
                    grogu += "<thead>";
                    grogu += "<tr>";
                    grogu += "<th>Qty</th>";
                    grogu += "<th colspan=3>Product</th>";
                    grogu += "<th>Total</th>";
                    grogu += "<th>Logo</th>";
                    grogu += "<th>Dept Placement</th>";
                    grogu += "<th>Status</th>";
                    grogu += "</tr>"
                    grogu += "</thead>";
                    grogu += "<tbody>";
                    for (var j = 0; j < data.length; j++) {
                        totalCost += data[j].line_item_total;
                        totalCount += parseInt(data[j].quantity);
                        // console.log(totalCount);
                        grogu += "<tr class='" + data[j].status + "' onclick=setLineItemSession(" + data[j]
                            .order_details_id +
                            ")>";
                        grogu += "<td>" + data[j].quantity + "</td>";
                        grogu += "<td>" + data[j].product_code + "</td>";
                        grogu += "<td>" + data[j].size_name + "</td>";
                        grogu += "<td>" + data[j].color_id + "</td>";
                        grogu += "<td>" + money_format(data[j].line_item_total) + "</td>";
                        grogu += "<td class='img'><img src=../../" + data[j].logo + " alt='..' /></td>";
                        grogu += "<td>" + data[j].dept_patch_place + "</td>";
                        grogu += "<td>" + data[j].status + "</td>";
                        grogu += "</tr>";
                    }
                    grogu += "</tbody>";
                    grogu += "</table>";
                    grogu += "<div class='total' id='orderTotal'>";
                    grogu += "</div>";
                    grogu += "<div class='itemTotal' id='itemTotal'>";
                    grogu += "</div>";
                    grogu += "</div>";

                    var orderTotal = "<p class='receipt'>Request Total: " + money_format(totalCost) + "</p>";
                    var itemTotal = "<p class='receipt'>Item Count: " + parseInt(totalCount) + "</p>"
                    // console.log('itemTotal is ', itemTotal)
                    document.getElementById('details').innerHTML = grogu;
                    document.getElementById('orderTotal').innerHTML = orderTotal;
                    document.getElementById('itemTotal').innerHTML = itemTotal;
                    getDepartmentTotals(data[0].department)
                        .then(() => getEmpTotals(data[0].emp_id))
                        .catch((error) => {
                            console.error("Error in department and or Emp totals: ", error);
                        });
                })
                .catch((error) => {
                    console.error("Error in getOrderDetails: ", error);
                })


        }
        async function getDepartmentTotals(dept) {
            // console.log("fetching department totals for ", dept);
            const seeButton = document.getElementById('showTotalsButton');
            // console.log(seeButton);
            await fetch('getDeptTotalsFY.php?dept=' + dept)
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    var fydata = "";
                    fydata += "<div class='dep-info-holder'>"
                    fydata += "<span class='table-title'>Department FY Totals</span class='table-title'>"
                    fydata += "<table class='styled-table totals'>";
                    fydata += "<thead>";
                    fydata += "<tr>";
                    fydata += "<th>Submitted</th>";
                    fydata += "<th>Approved</th>";
                    fydata += "<th>Ordered</th>";
                    fydata += "<th>Completed</th>";
                    fydata += "</tr>"
                    fydata += "</thead>";
                    fydata += "<tbody>";
                    fydata += "<tr>";
                    fydata += "<td>" + money_format(data[0].dep_submitted) + "</td>";
                    fydata += "<td>" + money_format(data[1].dep_approved) + "</td>";
                    fydata += "<td>" + money_format(data[2].dep_ordered) + "</td>";
                    fydata += "<td>" + money_format(data[3].dep_completed) + "</td>";
                    fydata += "</tr>";
                    fydata += "</tbody>";
                    fydata += "</table>";
                    fydata += "<div class='div6' id='empTotals'>Emp Totals</div>";
                    fydata += "</div>";
                    document.getElementById('depTotals').innerHTML = fydata;
                    if (seeButton.classList.contains('hidden')) {
                        showTotalsButtonUnhide();
                    } else {
                        console.log('seeTotalsButton is already unhidden');
                    }
                })
        }

        async function getEmpTotals(emp) {
            await fetch('getEmployeeTotalsFY.php?emp=' + emp)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    var mando = "";
                    mando += "<div class='emp-info-holder'>";
                    mando += "<span class='table-title'>" + data[8].empName + " Clothing FY Totals</span>";
                    mando += "<table class='styled-table totals'>";
                    mando += "<thead>";
                    mando += "<tr>";
                    mando += "<th>Submitted</th>";
                    mando += "<th>Approved</th>";
                    mando += "<th>Ordered</th>";
                    mando += "<th>Completed</th>";
                    mando += "</tr>"
                    mando += "</thead>";
                    mando += "<tbody>";
                    mando += "<tr>";
                    mando += "<td>" + money_format(data[0].emp_submitted) + "</td>";
                    mando += "<td>" + money_format(data[1].emp_approved) + "</td>";
                    mando += "<td>" + money_format(data[2].emp_ordered) + "</td>";
                    mando += "<td>" + money_format(data[3].emp_completed) + "</td>";
                    mando += "</tr>";
                    mando += "</tbody>";
                    mando += "</table>";
                    mando += "</div>"
                    // mando += "<thead>";
                    mando += "<div class='emp-info-holder'>";
                    mando += "<span class='table-title'>" + data[8].empName + " Boots FY Totals</span>";
                    mando += "<table class='styled-table totals'>"
                    mando += "<thead>";
                    mando += "<tr>";
                    mando += "<th>Submitted</th>";
                    mando += "<th>Approved</th>";
                    mando += "<th>Ordered</th>";
                    mando += "<th>Completed</th>";
                    mando += "</tr>"
                    mando += "</thead>";
                    mando += "<tbody>";
                    mando += "<tr>";
                    mando += "<td>" + money_format(data[4].emp_boots_submitted) + "</td>";
                    mando += "<td>" + money_format(data[5].emp_boots_approved) + "</td>";
                    mando += "<td>" + money_format(data[6].emp_boots_ordered) + "</td>";
                    mando += "<td>" + money_format(data[7].emp_boots_completed) + "</td>";
                    mando += "</tbody>";
                    mando += "</table>";
                    mando += "<p class='receipt'>FY Start: " + extractDateFromDB(data[9].fy_start) + ' ' +
                        "FY End: " + extractDateFromDB(data[10].fy_end) + "</p>";
                    mando += "</div>";
                    document.getElementById('empTotals').innerHTML = mando;
                })
        }
    </script>
</head>

<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2" id="main"></div>
        <div class="div3" id="details"></div>
        <div class="div4">
            <?php include('hideTopNav.php'); ?>
        </div>
        <div class="div6 total-hidden" id="totalsOffCanvas">
            <div class="div5" id="depTotals"></div>
        </div>
        <div class="div7 hidden" id="showTotalsButton">
            <button class="seeTotals" id="seeTotals" onclick="showTotals()">See Totals</button>
        </div>
    </div>
    <button id="showButton">Show</button>

    <div class=" off-canvas" id="off-canvas">
        <div class="request-action-options">
            <p>Request Action Options</p>
            <div class="action-buttons" id="action-buttons">
                <button>Smack</button>
                <button>Punch</button>
                <button>Kick</button>
            </div>
            <button class="close-button" id="closeButton">Close</button>
        </div>
    </div>
    <!-- <button popovertarget="action-jackson" popovertargetaction="show"> Show Popover </button> -->
    <div id="action-jackson" popover>
        <h1>ACTION JACKSON!!!!</h1>
    </div>

</body>
<script>
    // JavaScript to handle showing and hiding the off-canvas element as well as setting and unsetting session variables
    const showButton = document.getElementById('showButton');
    const offCanvas = document.querySelector('.off-canvas');

    function showOffCanvas() {
        offCanvas.classList.add('open');
    }
    const closeButton = document.getElementById('closeButton');

    function hideOffCanvas() {
        unsetLineItemSession();
        offCanvas.classList.remove('open');
    }

    showButton.addEventListener('click', showOffCanvas);
    closeButton.addEventListener('click', hideOffCanvas);

    window.addEventListener('click', (event) => {
        if (event.target === offCanvas) {
            hideOffCanvas();
        }
    });

    function setLineItemSession(order_details_id) {
        console.log('order_details_id', order_details_id);
        fetch('setLineItemSession.php?id=' + order_details_id)
            .then((response) => response.json())
            .then((data) => {
                console.log('(*)(*)(*)(*)(*)');
                console.log(order_details_id);
                console.log(data);
                var html = '';
                html += "<button class='approve' onclick='approveRequest(" + order_details_id +
                    ")' popovertarget='action-jackson' popovertargetaction='show'> Approve </button>";
                html += "<button class='deny' onclick='denyRequest(" + order_details_id +
                    ")' popovertarget='action-jackson' popovertargetaction='show'> Deny </button>";
                html += "<button class='comment' onclick='addComment(" + order_details_id +
                    ")' popovertarget='action-jackson' popovertargetaction='show'> Comment </button>";
                document.getElementById('action-buttons').innerHTML = html;
                showOffCanvas();
            })
    }

    function approveRequest(id) {
        // alert('Approving ' + id);
        var html = "";
        html += "<h3>Approving request # " + id + ". Enter any comments below - comments are optional for approvals</h3>";
        // html += "<p></p>"
        html += "<form>";
        html += "<input type='hidden' value='" + id + "' />";
        html += "<textarea name='comment' id='comment' cols='60' rows='5'></textarea>";
        html += "<br />";
        html += "<button class='submit-approve' type='submit'>Submit</button>";
        html += "</form>";
        document.getElementById('action-jackson').innerHTML = html;
    }

    function denyRequest(id) {
        // alert('DENIED SUCKA ' + id);
    }

    function addComment(id) {
        alert('Commenting on ' + id);
    }

    function unsetLineItemSession() {
        fetch('unsetLineItemSession.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
            })
    }

    function showTotalsButtonUnhide() {
        document.getElementById('showTotalsButton').classList.toggle('hidden');
    }

    function showTotals() {
        console.log('show totals clicked')
        const totalsOffCanvas = document.getElementById('totalsOffCanvas');
        const div6 = document.querySelector('.div6');
        totalsOffCanvas.classList.toggle('open');
        const button = document.getElementById('seeTotals');
        if (totalsOffCanvas.classList.contains('open')) {
            button.innerHTML = 'Hide Totals';
        } else {
            button.innerHTML = 'Show Totals';
        }
    }
</script>

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
        grid-area: 2 / 2 / 2 / 2;
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
        /* grid-area: 2/ 4 / 2 /4; */
        overflow-y: auto;
        scrollbar-gutter: stable;
    }


    .div6 {
        display: flex;
        flex-direction: column;
        grid-area: 2 / 4 / 3 / 4;
    }

    .total-hidden {
        margin-left: 550px;
    }

    .div7 {
        display: flex;
        grid-area: 1 / 5 / 1 / 5;
        margin-left: -100px;
        /* visibility: hidden; */
    }

    .hidden {
        visibility: hidden;
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

    .request-action-options {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        border: 1px solid black;
        border-radius: 10px;
        width: 100%;
        height: 100%;
        background-color: #f1f1f1;
        /* color: white; */
        padding: 5px;
        margin-top: 10px;
        border-top: #1aa260 5px solid;
    }

    .action-buttons {
        display: flex;
        gap: 20px;
        padding-top: 10px;

    }

    .action-buttons button {
        border-radius: 5px;
        box-shadow: 0px 22px 35px -5px rgba(0, 0, 0, 0.75);
        cursor: pointer;
    }

    .action-buttons button:hover {
        transform: scale3d(1.05, 1.05, 1.05);
        text-transform: uppercase;
    }

    .action-buttons .approve {
        border: darkgreen 1px solid;
        background-color: lightgreen;
    }

    .action-buttons .deny {
        border: darkred 1px solid;
        background-color: lightcoral;
    }

    .action-buttons .comment {
        border: darkblue 1px solid;
        background-color: lightblue;
    }

    #action-jackson {
        /* display: flex; */
        /* justify-content: center; */
        width: 50%;
        height: 50%;
        /* position: absolute; */
        /* z-index: 5; */
        /* top: 0; */
        /* left: 0; */
        /* right: 0; */
        /* bottom: 0; */
        /* background-color: #b3ffb399; */
        background-color: hsl(0, 0%, 100%);
        color: hsl(224, 10%, 23%);
        /* color: white; */
        margin-top: 10em;
        padding: 50px;
        margin-left: 25em;
        /* margin-bottom: 10em; */
        /* margin-right: 25em; */
        border: 5px solid hsl(224, 10%, 23%);
        /* box-shadow: 0px 0px 80px 20px rgba(41, 40, 41, 1), inset 0px 0px 45px 5px rgba(168, 235, 158, 1); */
        box-shadow: 0px 0px 80px 20px rgba(41, 40, 41, 1);
    }

    #action-jackson h3 {
        background-color: hsl(224, 20%, 94%);
        padding: 15px;
        color: hsl(224, 10%, 10%);
        font-weight: 900;
        border: 1px solid hsl(224, 6%, 77%);
        border-radius: 7px;
        margin-bottom: 10px;
    }

    /* #action-jackson p {
        background-color: #00000050;
        padding: 15px;
        color: #000;
        font-weight: 600;
        border-bottom: 1px solid white;
        border-left: 1px solid white;
        border-right: 1px solid white;
        border-radius: 7px;
    } */

    /* CSS for the off-canvas element */
    .off-canvas {
        position: fixed;
        bottom: -200px;
        /* Initially hidden off the screen */
        left: 0;
        width: 100%;
        height: 200px;
        background-color: #f0f0f0;
        transition: transform 0.3s ease-in-out;
        z-index: 999;
    }

    .off-canvas.open {
        transform: translate(0, -200px);
        /* Bring it up from the bottom */
    }

    /* CSS for the close button */
    .seeTotals,
    .close-button {
        display: inline;
        z-index: 3;
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }

    .div6.open {
        transition: transform 0.5s ease;
        margin-left: 0 !important;
    }

    .seeTotals {
        width: 96px;
    }

    #showButton {
        display: none;
    }

    ::backdrop {
        backdrop-filter: blur(3px);
    }

    .submit-approve {
        border: darkgreen 1px solid;
        background-color: lightgreen;
        border-radius: 5px;
        box-shadow: 0px 22px 35px -5px rgba(0, 0, 0, 0.75);
        cursor: pointer;
    }
</style>