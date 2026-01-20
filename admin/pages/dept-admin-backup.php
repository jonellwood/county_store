<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
// if (isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
//     header("Location: 401.php");
//     exit;
// }
try {
    // Check if empNumber is set in the session
    if (!isset($_SESSION['empNumber'])) {
        throw new Exception('Employee number is not set in the session.');
    }

    // Validate empNumber
    if ($_SESSION['empNumber'] !== '4438' && $_SESSION['empNumber'] !== '6865') {
        header("Location: 401.php");
        exit; // Ensure no further code is executed after redirection
    }

    // Load the page if empNumber is valid

} catch (Exception $e) {
    // Handle the exception (e.g., log the error, display a message)
    error_log($e->getMessage()); // Log the error message
    header("Location: 401.php"); // Redirect to a generic error page at some point. For now 401
    exit;
}


require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * from dep_ref ORDER by dep_head IS NULL, dep_assist IS NULL, dep_asset_mgr IS NULL";

$deps = array();
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $deps[] = $row;
    }
}
// var_dump($deps);

$eSql = "SELECT empNumber, empName from emp_sync order by empName";
$emps = array();
$eStmt = $conn->prepare($eSql);
$eStmt->execute();
$eRes = $eStmt->get_result();
if ($eRes->num_rows > 0) {
    while ($eRow = $eRes->fetch_assoc()) {
        $emps[] = $eRow;
    }
}
include "components/commonHead.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="./favicons/favicon.ico">

    <title>Dept Admin Page</title>

    <script>
        // START Dept Head Change Section
        // function to close selected modal when cancel button is used
        function hideDHChange() {
            document.getElementById('dh_change').classList.add('hidden');
        }

        // function to show selected modal and set the dep_num as a value to be passed as param to db update script
        function showDHChange(dep) {
            var confirm_popup = document.getElementById('dh_change')
            // console.log(confirm_popup);
            confirm_popup.classList.remove('hidden');
            confirm_popup.setAttribute('data-dep', dep);
            // console.log('confirm_popup data-dep: ', confirm_popup.getAttribute('data-dep'));
        }

        function setDHValue(emp) {
            // console.log('setting DH to :', emp);
            var confirm_popup = document.getElementById('dh_change')
            confirm_popup.setAttribute('data-emp', emp);
            // console.log('confirm pop up second time ', confirm_popup);
        }

        function setDHValueToNull() {
            var confirm_popup = document.getElementById('dh_change')
            confirm_popup.setAttribute('data-emp', null);
            changeDeptHead();
        }

        function changeDeptHead() {
            showLoading();
            var popup = document.getElementById('dh_change');
            var dep = popup.getAttribute('data-dep')
            var emp = popup.getAttribute('data-emp')
            fetch('./change-dept-head.php?dep=' + dep + '&emp=' + emp)
                // .then(logDeptHeadChanged())
                .then(location.reload())
        }
        // START Dept Assistant Change Section
        // function to close selected modal when cancel button is used
        function hideDAChange() {
            document.getElementById('da_change').classList.add('hidden');
        }
        // function to show selected modal and set the dep_num as a value to be passed as param to db update script
        function showDAChange(dep) {
            var confirm_popup = document.getElementById('da_change')
            confirm_popup.classList.remove('hidden');
            confirm_popup.setAttribute('data-dep', dep);
        }

        function setDAValue(emp) {
            var confirm_popup = document.getElementById('da_change')
            confirm_popup.setAttribute('data-emp', emp);
        }

        function setDAValueToNull() {
            var confirm_popup = document.getElementById('da_change')
            confirm_popup.setAttribute('data-emp', null);
            changeDeptAssistant();
        }

        function changeDeptAssistant() {
            showLoading();
            var popup = document.getElementById('da_change');
            var dep = popup.getAttribute('data-dep')
            var emp = popup.getAttribute('data-emp')
            fetch('./change-dept-assist.php?dep=' + dep + '&emp=' + emp)
                .then(location.reload())
        }
        // START Dept Asset Manager Change Section
        // function to close selected modal when cancel button is used
        function hideAMChange() {
            document.getElementById('am_change').classList.add('hidden');
        }
        // function to show selected modal and set the dep_num as a value to be passed as param to db update script
        function showAMChange(dep) {
            var confirm_popup = document.getElementById('am_change')
            confirm_popup.classList.remove('hidden');
            confirm_popup.setAttribute('data-dep', dep);
        }

        function setAMValue(emp) {
            var confirm_popup = document.getElementById('am_change')
            confirm_popup.setAttribute('data-emp', emp);
        }

        function setAMValueToNull() {
            var confirm_popup = document.getElementById('am_change')
            confirm_popup.setAttribute('data-emp', null);
            changeDeptAssetMgr();
        }

        function changeDeptAssetMgr() {
            showLoading();
            var popup = document.getElementById('am_change');
            var dep = popup.getAttribute('data-dep')
            var emp = popup.getAttribute('data-emp')
            fetch('./change-asset-mgr.php?dep=' + dep + '&emp=' + emp)
                .then(location.reload())
        }

        function logDeptHeadChanged() {
            console.log('Department Head Changed');
        }

        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
        }

        var data = <?php echo json_encode($deps); ?>

        function renderTable(data) {
            var html = `
        <table class="styled-table">
                <thead>
                    <tr>
                        <th>Dep Number</th>
                        <th>Dep Name</th>
                        <th>Dep Head</th>
                        <th>DH Change</th>
                        <th>Dep Asst </th>
                        <th>DA Change</th>
                        <th>Dep Asset Mgr</th>
                        <th>AM Change</th>
                    </tr>
                </thead>
                <tbody>
                    `
            for (var i = 0; i < data.length; i++) {

                html += `<tr>
                 <td>${data[i].dep_num}</td>
                 <td>${data[i].dep_name}</td>
                 <td>${data[i].dep_head_empName}</td>
                
                 <td>
                    <button 
                        id='dh_data_button' 
                        value='${data[i].dep_num}'
                        onclick='showDHChange(this.value)'>
                            <i class=icon-arrow-left-circle m-auto text-primary'></i>
                                Change
                            </button>
                 </td>
                <td>${data[i].dep_assist_empName}</td>
                
                <td>
                    <button 
                        value='${data[i].dep_num}
                        onclick='showDAChange(this.value)'>
                            <i class=icon-arrow-left-circle m-auto text-primary'></i>
                                Change
                            </button>
                 </td>
                <td>${data[i].dep_asset_mgr_empName}</td>
                <td>
                    <button 
                        value='${data[i].dep_num}'
                        onclick='showAMChange(this.value)'>
                            <i class=icon-arrow-left-circle m-auto text-primary'></i>
                                Change
                            </button>
                 </td>
                 </tr>
                `
            }
            html += `
                </tbody>
            </table>
        `;
            document.getElementById('main').innerHTML = html;
        }
        renderTable(data);
    </script>



</head>
<div class=" div2">
    <div id="main">

        <div class="dh_confirm hidden" id="dh_change">
            <h1 class="dh_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department
                    Head</mark></label>
            <select onchange='setDHValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "' >" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='dh_button' id="dh_1" onclick="changeDeptHead()">Assign</button>
                <button type='button' class='dh_button' id="hide" onclick="hideDHChange()">Cancel</button>
                <button type='button' class='dh_button' id="reset" onclick="setDHValueToNull()">Set to
                    Blank</button>
            </div>
        </div>
        <div class="da_confirm hidden" id="da_change">
            <h1 class="da_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department
                    Assistant</mark></label>
            <select onchange='setDAValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='da_button' id="da_1" onclick="changeDeptAssistant()">Assign</button>
                <button type='button' class='da_button' id="hide" onclick="hideDAChange()">Cancel</button>
                <button type='button' class='da_button' id="reset" onclick="setDAValueToNull()">Set to
                    Blank</button>
            </div>
        </div>
        <div class="am_confirm hidden" id="am_change">
            <h1 class="am_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department Asset
                    Manager</mark></label>
            <select onchange='setAMValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='am_button' id="am_1" onclick="changeDeptAssetMgr()">Assign</button>
                <button type='button' class='am_button' id="hide" onclick="hideAMChange()">Cancel</button>
                <button type='button' class='am_button' id="reset" onclick="setAMValueToNull()">Set to
                    Blank</button>
            </div>
        </div>
        <div class="loading hidden" id="loading">
            <h1>Loading...</h1>
        </div>
    </div>
</div>
</body>

</html>
<script>
    function displayAlert() {

        var html = '';
        html +=
            `<div class="info-banner">
            You can not control others, only how you react to them.</p>
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }
    displayAlert();
</script>
<style>
    .info-banner {
        padding-left: 20px;
        padding-right: 20px;
    }

    .parent {
        display: grid;
        grid-template-columns: 10% 90%;
        grid-template-rows: 75px 1fr 1fr;
        height: 100vh;

    }

    .div1 {
        display: flex;
        grid-area: 1 / 1 / 4 / 1;

    }

    .div2 {
        display: grid;
        grid-template-columns: 1fr;
        grid-area: 2 / 2 / 5 / 5;
        scrollbar-gutter: stable;
        background-image:
            conic-gradient(from 127deg at 0% 100%,
                #00d5ff 47% 47%, #aa92ff 101% 101%);
    }

    .div3 {
        display: none;
        grid-area: 2 / 3 / 2 / 3;
        height: 100vh;
        scrollbar-gutter: stable;
        padding-left: 20px;
        overflow-y: auto;
        border-top: 3px solid #80808050;
        border-left: 3px solid #80808050;
        border-bottom: 3px solid #80808050;

    }

    .div4 {
        display: flex;
        grid-area: 1 / 2 / 1 / 5;
    }

    .div5 {
        display: flex;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }


    .div6 {
        display: none;
        flex-direction: column;
        grid-area: 2 / 4 / 3 / 4;
        border-top: 3px solid #80808050;
        border-right: 3px solid #80808050;
        border-bottom: 3px solid #80808050;
    }

    .total-hidden {
        margin-left: 550px;
    }

    .div7 {
        display: none;
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

    .main-list-holder table td,
    .main-order-info-holder table td {
        cursor: pointer;
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
        justify-content: space-evenly;
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

    .center-text {
        text-align: center
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
        /* border: 1px solid black; */
        /* border-radius: 10px; */
        width: 100%;
        height: 100%;
        background-color: #f1f1f1;
        /* color: white; */
        padding: 5px;
        /* margin-top: 10px; */
        /* border-top: #1aa260 5px solid; */
    }

    .action-buttons {
        display: flex;
        gap: 20px;
        padding-top: 10px;

    }

    .action-buttons button {
        border-radius: 5px;
        /* box-shadow: 0px 22px 35px -5px rgba(0, 0, 0, 0.75); */
        cursor: pointer;
        font-family: monospace;
        font-size: medium;
        padding-left: 10px;
        padding-top: 5px;
        padding-right: 10px;
        padding-bottom: 5px;
    }

    .action-buttons button:hover {
        transform: scale3d(1.05, 1.05, 1.05);
        font-weight: bolder;
        /* text-transform: uppercase; */
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


    #whole-order-confirm,
    #whole-order-deny-confirm {
        width: 30%;
        height: 35%;
        background-color: hsl(0, 0%, 100%);
        color: hsl(224, 10%, 23%);
        margin-top: 10em;
        padding: 50px;
        margin-left: 40%;
        border: 5px solid hsl(224, 10%, 23%);
    }

    #po-req {
        width: 65%;
        /* height: 35%; */
        background-color: hsl(0, 0%, 100%);
        color: hsl(224, 10%, 23%);
        margin-top: 10em;
        padding: 50px;
        margin-left: 25%;
        border: 5px solid hsl(224, 10%, 23%);
    }

    #whole-order-confirm {
        box-shadow: 0px 0px 10px 4px rgba(41, 40, 41, 1), inset 0px 0px 10px 0px rgba(5, 148, 5, 1);
    }

    #whole-order-deny-confirm {
        box-shadow: 0px 0px 10px 4px rgba(41, 40, 41, 1), inset 0px 0px 10px 0px rgba(135, 55, 5, 1);
    }

    #action-jackson {
        width: 50%;
        height: 50%;
        background-color: hsl(0, 0%, 100%);
        color: hsl(224, 10%, 23%);
        margin-top: 10em;
        padding: 50px;
        margin-left: 25em;
        border: 5px solid hsl(224, 10%, 23%);
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



    /* CSS for the off-canvas element */
    .off-canvas {
        position: fixed;
        bottom: -200px;
        /* Initially hidden off the screen */
        left: 25%;
        right: 25%;
        width: 50%;
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

    .close-button {
        right: 2%;
        top: 15%;
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
        /* box-shadow: 0px 22px 35px -5px rgba(0, 0, 0, 0.75); */
        cursor: pointer;
    }

    .close-btn {
        border: none;
        background: none;
        position: absolute;
        right: 0.25rem;
        top: 0.5rem;
        filter: grayscale() brightness(20);

    }

    .goAway {
        display: none;
    }

    .buttons-in-approval-popover-holder {
        display: flex;
        justify-content: space-evenly;
    }

    .confirm-approval-button,
    .approve-order-button,
    .gen-po-button {
        /* background-color: hotpink; */
        font-size: medium;
        font-family: monospace;
        border-radius: 5px;
    }

    .confirm-approval-button {
        margin-top: 10px;
        border-color: #009879;
    }

    .huge-mistake-button {
        font-size: medium;
        font-family: monospace;
        border-radius: 5px;
        margin-top: 10px;
        border-color: #ff0000;
    }

    .approve {
        background-color: #00800050;
        color: #000000;
        padding: 1px;
        border: 1px solid darkgreen;
        border-radius: 3px;
    }

    .deny {
        background-color: #ff000050;
        color: #000000;
        padding: 1px;
        border: 1px solid darkred;
        border-radius: 3px;
    }

    .tiny-text {
        font-size: small;
        color: rgba(0, 0, 0, 0.85)
    }

    .active-request {
        background-color: #00800030 !important;
    }

    #not-off-canvas {
        margin: auto;
        position: fixed;
        width: 40%;
        height: 20%;
        overflow: hidden;
    }

    #pobutton {
        background-color: hotpink;
        height: 100px;
        width: 100px;

    }

    button:disabled {
        color: grey;
        cursor: not-allowed;
    }

    button:disabled:hover {
        color: grey;
        cursor: not-allowed;
    }

    @media print {

        .parent,
        button {
            display: none;
        }

        #po-req {
            margin: 10px;
            border: none;
            width: fit-content;
        }

        .hide-from-printer {
            display: none;
        }
    }

    /* body {
        margin: 0px;
        padding: 40px;
        
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
    } */

    button {
        font-size: smaller;
    }

    .dh_confirm,
    .da_confirm,
    .am_confirm {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        /* margin-top: 150px; */
        border: 1px solid #772953;
        border-top: 10px solid #E95420;
        border-radius: 10px;
        /* bottom: 0; */
        margin-bottom: 250px;
        /* margin-left: 35%; */
        /* right: 0; */
        /* margin-right: 35%; */
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

    .loading {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url("https://png.pngtree.com/thumb_back/fh260/background/20200714/pngtree-loading-technology-screen-background-image_353489.jpg")
    }
</style>