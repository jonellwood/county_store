<?php
// require_once 'config.php';
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());



// check for logged in.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}
include "components/commonHead.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <!-- <link rel="stylesheet" href="prod-admin-style.css"> -->
    <!-- <link rel="stylesheet" id='test' href="https://berkeley-county.github.io/berkstrap/berkstrap-dark.css" defer async> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
    <link rel="icon" type="image/png" sizes="32x32" href="./favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon-16x16.png">
    <title>User Management</title>
    <script>
        function deleteUser() {
            alert('🚫 Not yet. Gotta think about the best way to do this. 🚫')
        }

        async function fetch_data() {
            await fetch('edit-users-db.php')
                .then((response) => response.json())
                .then((data) => {
                    console.log(data)
                    const table = document.createElement('table');
                    table.classList.add('styled-table');
                    // table.classList.add('table-striped');
                    // table.classList.add('table-dark');
                    const thead = document.createElement('thead');
                    thead.innerHTML = `
                        <tr>
                            <th>Employee Name</th>
                            <th>Employee Number</th>
                            <th>Role</th>
                            <th>Departments</th>
                            <th>Actions</th>
                        </tr>
                    `;
                    table.appendChild(thead);
                    const tbody = document.createElement('tbody');

                    for (const key in data) {
                        if (Object.prototype.hasOwnProperty.call(data, key)) {
                            const employeeData = data[key];
                            const tr = document.createElement('tr');

                            const nameTd = document.createElement('td');
                            nameTd.textContent = employeeData.empName;
                            tr.appendChild(nameTd);

                            const empNumTd = document.createElement('td');
                            empNumTd.textContent = employeeData.emp_num;
                            tr.appendChild(empNumTd);

                            const roleTd = document.createElement('td');
                            roleTd.textContent = employeeData.role_name;
                            tr.appendChild(roleTd);

                            const departmentsTd = document.createElement('td');
                            const departmentsList = document.createElement('ul');
                            employeeData.departments.forEach((department) => {
                                const departmentItem = document.createElement('li');
                                let departmentText = "";
                                if (department.dep_name_1) {
                                    departmentText +=
                                        `Dept Head: ${department.dep_name_1} - ${department.dep_num_1}<br>`;
                                }
                                if (department.dep_name_2) {
                                    departmentText +=
                                        `Dept Asst: ${department.dep_name_2} - ${department.dep_num_2}<br>`;
                                }
                                if (department.dep_name_3) {
                                    departmentText +=
                                        `Dept Asset Mgr: ${department.dep_name_3} - ${department.dep_num_3}<br>`;
                                }
                                departmentItem.innerHTML = departmentText;
                                departmentsList.appendChild(departmentItem);
                            });
                            departmentsTd.appendChild(departmentsList);
                            tr.appendChild(departmentsTd);

                            const actionsTd = document.createElement('td');
                            actionsTd.innerHTML = `<a href='dept-admin.php'><button>Edit Department</button></a>`;
                            tr.appendChild(actionsTd);

                            tbody.appendChild(tr);
                        }
                    }

                    table.appendChild(tbody);
                    document.getElementById('main').appendChild(table);
                })
        }
        fetch_data();
    </script>
</head>

<!-- <body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent"> -->
<!-- <div class="div1">
            </?php include('hidenav.php') ?>
        </div> -->
<div class="div2" id="users">
    <div id="main"></div>
</div>
<!-- <div class="div3">
            3
        </div> -->
<!-- <div class="div4">
            </?php include('hideTopNav.php'); ?>
        </div>
    </div> -->
</body>


</html>
<script>
    function displayAlert() {
        // var fyData = fiscalYear();
        var html = '';
        html +=
            `<div class="info-banner">
            Everything is everything.</p>
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
        /* overflow: hidden; */
    }

    .div1 {
        display: flex;
        grid-area: 1 / 1 / 4 / 1;

    }

    /* .div2 {
        display: flex;
        grid-area: 2 / 2 / 4 / 5;
    
        scrollbar-gutter: stable;
        background-color: #80808030;
    } */

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
        /* grid-area: 2/ 4 / 2 /4; */
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
        /* font-size: 0.8em; */
        /* font-family: sans-serif; */
        /* font-family: robofont; */
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

    /* html {
        margin: 0px;
        padding: 40px;
     
        background-color: #0d0e0e;
        background-image: linear-gradient(0deg, #0d0e0e 27%, #5e5e6a 100%);
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
     
    } */

    /* body {
        margin: 20px;
        background-color: #5e5e6a;
    } */

    /* becuase hes holding all the cards */
    .dealer {
        display: grid;
        grid-template-columns: auto auto;
        gap: 25px;
        margin-left: 5%;

        margin-right: auto;
    }

    #users {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-left: 2em;
    }

    .card {
        border: 2px solid #DBDBDB;
        border-radius: 4px;
        padding: 10px;
        /* height: 300px; */
        width: 550px;
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