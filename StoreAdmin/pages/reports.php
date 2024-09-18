<?php
include('DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: pages/sign-in.php");

    exit;
}

if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders by Department</title>
    <link rel="stylesheet" type="text/css" src="new-jack-city.css">

    <script>
    // function orderAll(dept_id) {
    //     // alert("All items have been ordered for : " + dept_id);
    //     fetch('./change-all-to-ordered.php?dept_id=' + dept_id)
    //         .then(window.location.reload())
    // }

    function formatDate(inputDate) {
        const dateObject = new Date(inputDate);
        const month = String(dateObject.getMonth() + 1).padStart(2, '0');
        const day = String(dateObject.getDate()).padStart(2, '0');
        const year = dateObject.getFullYear();

        return `${month}-${day}-${year}`;
    }

    function formatAsCurrency(value) {
        return value.toFixed(2);
    }

    // function checkVendorId(items) {
    //     needsPO = items.some(item => item.vendor_id === 3)
    // }

    async function getPastReports() {
        await fetch('./past-reports-get.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                let reportTable =
                    `<table class='styled-table' id='reportsTable'>
                        <caption>Vendor reports</caption>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Dept</th>
                                <th>Amount</th>
                                <th width='2em'></th>
                            </tr>
                        </thead>`;
                for (var i = 0; i < data.length; i++) {
                    reportTable += `<tbody>
                                            <tr>
                                                <td>${formatDate(data[i].order_inst_created)}</td>
                                                <td>${data[i].dep_name}</td>
                                                <td>$${data[i].grand_total}</td>
                                                <td><a href='vendorReport.php?uid=${data[i].order_inst_id}'>&#129531;</a></td>
                                            </tr>
                                        </tbody>`;
                }
                reportTable += '</table>'
                var reportContainer = document.getElementById("past-reports");
                reportContainer.innerHTML = reportTable;
            })
    }
    getPastReports();
    </script>
</head>

<body class="p-3 m-0 border-0 bd-example m-0 border-0">

    <div class="parent">
        <div class="div1">
            <?php include "hideNav.php" ?>
        </div>

        <!-- <div class="main-container"> -->
        <!-- <div class="left"> -->
        <div id="past-reports"></div>

        <!-- </div> -->
        <!-- <div class="right"> -->
        <!-- <div id="orders-list"></div> -->

        <!-- </div> -->
        <div class="div4">
            <?php include('topNav.php'); ?>
        </div>
        <!-- </div> -->
    </div>

</body>

</html>
<style>
@font-face {
    font-family: bc-g;
    src: url('../../fonts/Gotham-Medium.otf');
}

html {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    /* background-color: #ffe53b; */
    background-color: #dcd9d4;
    background-image: linear-gradient(to bottom,
            rgba(255, 255, 255, 0.5) 0%,
            rgba(0, 0, 0, 0.5) 100%),
        radial-gradient(at 50% 0%,
            rgba(255, 255, 255, 0.1) 0%,
            rgba(0, 0, 0, 0.5) 50%);
    background-blend-mode: soft-light, screen;
    font-family: bc-g;
}

.body {
    margin: 20px;
    display: flex;
    justify-content: center;
}

h3 {
    text-align: center;
}

.body {
    display: flex;
    justify-content: center;
}

.header-holder {
    display: flex;
    justify-content: space-around;
}

.styled-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    /* font-family: sans-serif; */
    width: 98%;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}

.styled-table thead tr {
    background-color: #0F9D58;
    color: #ffffff;
    /* text-align: center; */
}

.styled-table thead tr th {
    background-color: #0F9D58;
    color: #ffffff;
    text-align: left;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
}

.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
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

a:link {
    text-decoration: none;
    color: black;
}

a:link:hover {
    background-color: #ffe53b;
}

th {
    cursor: pointer;
}

caption {
    font-size: x-large;
    font-weight: bold;
    text-align: left;
    padding: 10px;
    margin-bottom: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    background-color: #4285F4;
    position: relative;
}

.order-button {
    background-color: #F4B400;
    border: 1px solid black;
    border-radius: 5px;
    position: absolute;
    right: 0;
    margin-right: 45px;
}

.main-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    /* margin: 20px; */
    padding: 20px;
}

.left {
    width: 75vw;
}

.right {
    width: 20vw;
}

#reportsTable th,
#reportsTable td {
    padding-top: 5px;
    padding-right: 7px;
    padding-bottom: 5px;
    padding-left: 7px;
}

.popover {
    position: fixed;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
}

.tiny-text {
    font-size: smaller;
    color: #808080;
}

.center-text {
    width: 100%;
    text-align: center;
}

.poform {
    /* display: flex; */
    /* justify-content: space-evenly; */
    width: 250px;
    height: 100px;
}

.poform span {
    margin-top: 5px;
    margin-bottom: 5px;
    padding: 5px;
}

.poform button {
    margin-top: 10px;
    position: relative !important;
}

/* HERE IS THE START OF A MASSIVE MISTAKE */
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
    grid-template-columns: 10% 80% 1% 1%;
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
    background-color: #80808030;
}

.div3 {
    display: flex;
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
    display: flex;
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
    /* font-family: robofont; */
    margin-top: 25px;
}

.main-list-holder table td,
.main-order-info-holder table td {
    cursor: pointer;
}

.dep-info-holder {
    border-top: #1aa260 44px solid;
    /* font-family: robofont; */
    margin-top: 25px;
}

.details {
    box-shadow: 10px 10px 38px -17px rgba(59, 54, 59, 1);
}

.table-title {
    display: flex;
    justify-content: space-evenly;
    /* font-family: robofont; */
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

#past-reports {
    width: 90% !important;
}
</style>