<?php
include('DBConn.php');

session_start();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving Department</title>
    <script>
        async function getOrders() {
            await fetch('./orders-to-be-received-get.php')
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    // console.log(data.length, 'total orders');
                    const groupedByDepartment = data.reduce((acc, entry) => {
                        const departmentNumber = entry.dep_name;
                        if (!acc[departmentNumber]) {
                            // If the array doesn't exist for the department, create it
                            acc[departmentNumber] = [];
                        }
                        // Push the entry to the matching department's array
                        acc[departmentNumber].push(entry);
                        return acc;
                    }, {});

                    var tableHolder = document.getElementById('main');
                    tableHolder.innerHTML = '';

                    Object.keys(groupedByDepartment).forEach(departmentNumber => {
                        var table = document.createElement('table');
                        table.classList.add('styled-table')
                        table.innerHTML = `
                            <caption>${departmentNumber}</caption>
                            
                            `;
                        tableHolder.appendChild(table);

                        // Group entries by order_id within each department
                        const groupedByOrderId = groupedByDepartment[departmentNumber].reduce((acc,
                            entry) => {
                            const orderId = entry.order_id;
                            if (!acc[orderId]) {
                                // If the array doesn't exist for the order_id, create it
                                acc[orderId] = [];
                            }
                            // Push the entry to the matching order_id's array
                            acc[orderId].push(entry);
                            return acc;
                        }, {});

                        // Display the entries grouped by order_id for each department
                        Object.keys(groupedByOrderId).forEach(orderId => {
                            var orderTable = document.createElement('table');
                            orderTable.classList.add('styled-table')
                            // orderTable.innerHTML = `
                            //     <caption class='orderIDCaption'>Order ${orderId} </caption>`;

                            const firstEntry = groupedByOrderId[orderId][0];

                            if (firstEntry) {
                                const tr = document.createElement('tr');
                                tr.classList.add('orderIDCaption')
                                tr.innerHTML = `
                                    <td colspan='4'>
                                    Order #: ${firstEntry.order_id} for ${firstEntry.rf_first_name} ${firstEntry.rf_last_name} for total of ${new Intl.NumberFormat('us-EN', { style: 'currency', currency: 'USD' }).format(firstEntry.grand_total)}
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td colspan='2'>
                                    <button popovertarget="whole-order-confirm" popovertargetaction="show" onclick="createWholeOrderPopoverContent(${orderId})">Receive Entire Order</button>
                                    <td>
                                `;
                                orderTable.appendChild(tr);

                            }

                            tableHolder.appendChild(orderTable);

                            const groupedByOrderDetailsId = groupedByOrderId[orderId].reduce((acc,
                                entry) => {
                                const orderDetailsId = entry.order_details_id;
                                if (!acc[orderDetailsId]) {
                                    acc[orderDetailsId] = [];
                                }
                                acc[orderDetailsId].push(entry);
                                return acc;
                            }, {});

                            Object.keys(groupedByOrderDetailsId).forEach(orderDetailsId => {
                                var orderLineTable = document.createElement('tr');

                                orderLineTable.innerHTML = `
                            ${groupedByOrderDetailsId[orderDetailsId].map(entry => `
                                <tr> 
                                    <td width=10%>${entry.quantity}</td>
                                    <td width=30%>${entry.product_name}</td>
                                    <td width=10%>${entry.size_name}</td>
                                    <td width=10%>${entry.color_id}</td>
                                    <td width=10%><img src='../../${entry.logo}' alt='logo' /></td>
                                    <td width=10%>${entry.dept_patch_place}</td>
                                    <td>
                                        <button 
                                            popovertarget = "single-item-confirm"
                                            popovertargetaction = "show";
                                            onclick = createPopoverContent(${entry.order_details_id})
                                            >
                                               Receive Item (# ${entry.order_details_id})
                                        </button>
                                    </td>
                                </tr>
                                `).join('')}
                        `;
                                orderTable.appendChild(orderLineTable);
                            })
                        });
                    });
                });
        }

        getOrders();
    </script>



</head>

<!-- <body class="p-3 m-0 border-0 bd-example m-0 border-0"> -->
<!-- <div class="parent"> -->
<!-- <div class="div1"> -->
<!-- </?php include('hideNav.php'); ?> -->
<!-- </div> -->
<div class="main-list-holder">

    <div class="div2">
        <div id="main">Div 2</div>
    </div>
</div>
<!-- <div class="div3">
            <p>Div 3</p>
        </div> -->
<!-- <div class="div4">
    </?php include('hideTopNav.php'); ?>
</div> -->
<div class="div6 total-hidden open" id="totalsOffCanvas">
    <div class="div5" id="depTotals">Div 5 inside of Div 6</div>
</div>
<div class="div7 hidden" id="showTotalsButton">
    <button class="seeTotals" id="seeTotals">Dont press me</button>
</div>
</div>
<button id="showButton">Show</button>

<div id="not-off-canvas" popover=manual>
    <button class="close-btn" popovertarget="not-off-canvas" popovertargetaction="hide">
        <span aria-hidden=”true”>❌</span>
        <span class="sr-only">Close</span>
    </button>
    <div class="request-action-options">
        <div class="action-buttons" id="action-buttons">
        </div>
    </div>
</div>

<div id="action-jackson" popover=manual>
    <h1>ACTION JACKSON!!!!</h1>
</div>

<div id="whole-order-confirm" popover=manual>
    <button class="close-btn" popovertarget="whole-order-confirm" popovertargetaction="hide">
        <span aria-hidden=”true”>❌</span>
        <span class="sr-only">Close</span>
    </button>
    <span id="confirm-details-holder">
        <br>
        <p id="whole-order-popover-values"></p>
    </span>
</div>
<div id="single-item-confirm" popover=manual>
    <button popovertarget="single-item-confirm" popovertargetaction="hide">
        <span aria-hidden=”true”>❌</span>
        <span class="sr-only">Cancel</span>
    </button>
    <span id="confirm-details-holder">
        <br>
        <p id="single-item-popover-values">

        </p>
    </span>
</div>
<!-- <div id="whole-order-deny-confirm" popover=manual>
    <button class="close-btn" popovertarget="whole-order-deny-confirm" popovertargetaction="hide">
        <span aria-hidden=”true”>❌</span>
        <span class="sr-only">Close</span>
    </button>
    <span id="confirm-details-holder">
        <br>
        <p id="popover-values"></p>
    </span>
</div> -->
</body>

</html>
<script>
    function displayAlert() {

        var html = '';
        html +=
            `<div class="info-banner">
            It's gonna be a lovely day.</p>
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }
    displayAlert();

    async function receiveItem(orderDetailsId) {
        await fetch('./API/receiveSingleItemAdmin.php?id=' + orderDetailsId)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                if (data.success) {
                    window.location.replace('orders-to-be-received.php');
                }
            })
    }
    async function receiveWholeOrder(orderId) {
        await fetch('./API/receiveWholeOrderAdmin.php?id=' + orderId)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                if (data.success) {
                    window.location.replace('orders-to-be-received.php');
                }
            })
    }

    function createPopoverContent(id) {
        // console.log("ID:", id);
        var html = ''
        html += '<hr><div><p>Are you sure you want to receive this item?</p>'
        html += '<button onclick="receiveItem(\'' + id +
            '\')" popovertarget="single-item-confirm" popovertargetaction="hide">Yes</button></div>'
        // console.log("Generated HTML:", html);

        document.getElementById('single-item-popover-values').innerHTML = html;
    }

    function createWholeOrderPopoverContent(id) {
        // console.log("ID:", id);
        var html = ''
        html += '<hr><div><p>Are you sure you want to receive all items in this order?</p>'
        html += '<button onclick="receiveWholeOrder(\'' + id +
            '\')" popovertarget="whole-order-confirm" popovertargetaction="hide">Yes</button></div>'
        // console.log("Generated HTML:", html);

        document.getElementById('whole-order-popover-values').innerHTML = html;
    }
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

    /* .div2 {
        display: flex;
        flex-direction: column;
        grid-area: 2 / 2 / 2 / 2;
        
        scrollbar-gutter: stable;
        background-color: #80808030;
    } */

    /* .div3 {
        display: flex;
        grid-area: 2 / 3 / 2 / 3;
        height: 100vh;
        scrollbar-gutter: stable;
        padding-left: 20px;
        overflow-y: auto;
        border-top: 3px solid #80808050;
        border-left: 3px solid #80808050;
        border-bottom: 3px solid #80808050;

    } */

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
        font-family: robofont;
        margin-top: 25px;
        /* display: grid; */
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

        /* font-family: robofont; */
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        background-color: antiquewhite;
        margin-bottom: 20px;
        border-bottom: 5px solid #80808050;
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
    .approve-order-button {
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

    caption {
        /* display: flex;
        justify-content: space-evenly; */
        font-family: robofont;
        text-wrap: balance;
        font-size: max(1.25vw, 14px);
        text-align: center;
        /* padding-top: 10px; */
        padding-bottom: 20px;
        width: auto;
    }

    .orderIDCaption {
        text-align: left;
        text-transform: capitalize;
        padding-left: 15px;
        font-size: large;

    }

    .details-row {
        padding-left: 30px;
    }

    .rec-btn {
        margin-left: 100px;
    }
</style>