<?php
include('DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

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
    <title>Employee Requests</title>
    <!-- <link href="../assets/css/icons.css" rel="stylesheet" /> -->
    <!-- <script async defer src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script> -->
    <title>Request Management</title>
    <link href="https://cdn.jsdelivr.net/npm/intro.js@7.0/minified/introjs.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/intro.js@7.0/intro.min.js"></script>
    <link rel="icon" type="image/x-icon" href="./favicons/favicon.ico">
    <link href="../../build/style.max.css" rel="stylesheet" />
    <!-- <link href="../../index23.css" rel="stylesheet" /> -->
    <link href="prod-admin-style.css" rel="stylesheet" />
    <script src="../../functions/formatForUrl.js"></script>
    <script>
        function getLastTwoDigits(year) {
            // console.log(year.toString())
            var lastTwo = year.toString().slice(-2)
            return lastTwo
        }

        function fiscalYear() {
            var currentMonth = new Date().getMonth() + 1;
            console.log(currentMonth);
            var currentYear = new Date().getFullYear();
            var currentFY = 0
            console.log('current year: ', currentYear)
            console.log('current fy: ', currentFY)
            if (currentMonth < 6) {
                currentFYStart = (currentYear - 1);
                currentFYEnd = currentYear
            } else {
                currentFYStart = currentYear
                currentFYEnd = (currentYear + 1)
            }
            console.log("Current Fiscal Year Start, year is: ", getLastTwoDigits(currentFYStart))
            console.log("Current Fiscal Year End, year is: ", getLastTwoDigits(currentFYEnd))
            return [getLastTwoDigits(currentFYStart), getLastTwoDigits(currentFYEnd)];
        }

        function isThisFiscalYear(fy) {
            var newFiscalYear = fiscalYear();
            var fyStart = newFiscalYear[0];
            console.log(fyStart)
            var fyEnd = newFiscalYear[1];
            console.log(fyEnd)
            var currentFY = (fyStart + fyEnd);
            console.log('current fy is: ', currentFY)
            console.log('fy passed in is: ', fy)
            return fy >= currentFY
        }

        let firstData = [];
        async function getRequests() {
            // await fetch('./getAllRequests.php')
            // await fetch('./getDeptRequests.php')
            await fetch('./getDeptRequestsRefactored.php')
                .then((response) => response.json())
                .then((data) => {
                    firstData.push(data);
                    // console.log('First data')
                    console.log(data);
                    if (data.length == 0) {
                        alert("No Requests Found");
                    } else {
                        var html = '';
                        html += "<div class='main-list-holder' id='main-list-holder'>";
                        html += "<span class='table-title'>Employee Requests</span>";
                        // html += "<span><label for = 'filter-input'> Filter: </label> <input type = 'text' id = 'filter-input' placeholder = 'Type to filter...' oninput='getFilterInput()'></span>"
                        html += "<table class='styled-table' id='data-table'>";
                        html += "<thead>";
                        html += "<tr>";
                        html += "<th>Order ID</th>";
                        html += "<th>Grand Total</th>";
                        html += "<th>Created</th>";
                        html += "<th>Requested For</th>";
                        // html += "<th>PO Required</th>";
                        html += "</tr>"
                        html += "</thead>";
                        html += "<tbody>";
                        for (var i = 0; i < data.length; i++) {
                            html += "<tr value='" + data[i].order_id + "' onclick=getOrderDetails(" + data[i]
                                .order_id +
                                ") data-currentfy='" + isThisFiscalYear(data[i].bill_to_fy) + "'>";
                            html += "<td>" + data[i].order_id + "</td>";
                            html += "<td>" + money_format(data[i].grand_total) + "</td>";
                            html += "<td>" + extractDate(data[i].created) + "</td>";
                            html += "<td>" + data[i].rf_first_name + ' ' + data[i].rf_last_name + "</td>";
                            // if (data[i].vendor_id == '3') {
                            //     html += "<td>&#9989;</td>"
                            // } else {
                            //     html += "<td>&#10062;</td>"
                            // }
                            // html += "</tr>";
                        }
                        html += "</tbody>";
                        html += "</table>";
                        html += "</div>";
                        document.getElementById('main').innerHTML = html;
                        clickFirst();
                    }
                })
        }
        // getRequests();

        function money_format(amount) {
            // console.log(amount);
            // return '$' + parseFloat(amount).toFixed(2);
            let USDollar = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });
            return USDollar.format(amount);
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
            // console.log('order_id', order_id);
            setActiveRequest(order_id);
            await fetch('getOrderDetails.php?id=' + order_id)
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data)
                    var hasVendorID3 = data.some(item => item.vendor_id === 3);

                    var department = data[0].department;
                    // console.log('department is :' + department);
                    var empID = data[0].emp_id;
                    var totalCost = 0
                    var totalCount = 0
                    var grogu = '';
                    grogu += "<div class='main-order-info-holder'>";
                    if (data[0].status == 'Ordered') {
                        grogu +=
                            "<span class='table-title'>Order Details for " +
                            data[0].rf_first_name + ' ' + data[0]
                            .rf_last_name +
                            " <button class='approve-order-button' value='" + data[0].order_id +
                            "'popovertarget='receive-whole-order-confirm'> Receive Whole Order </button>"
                    } else {
                        grogu +=
                            "<span class='table-title'>Order Details for " +
                            data[0].rf_first_name + ' ' + data[0]
                            .rf_last_name +
                            " <button class='approve-order-button' value='" + data[0].order_id +
                            "'popovertarget='whole-order-confirm'> Approve or Deny Order </button>"
                        if (hasVendorID3) {
                            grogu += "<button class='gen-po-button' onclick='genPOreq(" + data[0].order_id +
                                ")'>Gen PO Request</button>"
                        }
                    }
                    grogu += "</span>"
                    grogu += "<table class='styled-table'>";
                    grogu += "<thead>";
                    grogu += "<tr>";
                    grogu += "<th>Qty</th>";
                    grogu += "<th colspan=3>Product </th>";
                    grogu += "<th>Total</th>";
                    grogu += "<th>Logo</th>";
                    grogu += "<th>Dept Placement</th>";
                    grogu += "<th>Status</th>";
                    grogu += "<th>PO required</th>";
                    grogu += "<th>Image</th>";
                    grogu += "</tr>";
                    grogu += "</thead>";
                    grogu += "<tbody>";
                    for (var j = 0; j < data.length; j++) {
                        totalCost += data[j].line_item_total;
                        totalCount += parseInt(data[j].quantity);
                        // console.log(totalCount);
                        // grogu += "<tr class='" + data[j].status + "' onclick=setLineItemSession(" + data[j].order_details_id + ")>";
                        grogu += "<tr class='" + data[j].status + "' onclick=setLineItemSession(" + data[j]
                            .order_details_id + ",'" + data[j].status + "')>";
                        grogu += "<td>" + data[j].quantity + "</td>";
                        grogu += "<td>" + data[j].product_code + "</td>";
                        grogu += "<td>" + data[j].size_name + "</td>";
                        grogu += "<td>" + data[j].color_name + "</td>";
                        grogu += "<td>" + money_format(data[j].line_item_total) + "</td>";
                        grogu += "<td class='img'><img src=../../" + data[j].logo + " alt='..' /></td>";
                        grogu += "<td>" + data[j].dept_patch_place + "</td>";
                        grogu += "<td>" + data[j].status + "</td>";
                        if (data[j].vendor_id == '3') {
                            grogu += "<td class='center-text'>&#9989;</td>"
                        } else {
                            grogu += "<td class='center-text'> ❌ </td>"
                        }
                        grogu += "<td id='prod_img_" + [j] + "'><img class='img prod_img' src=../../" + formatValueForUrl(data[j].product_image) + " alt='..' width='50px'/></td>";
                        grogu += "</tr>";
                        // grogu +=
                        //     "<tr><td colspan='8'><button class='action-button'>Approve</button><button class='action-button'>Deny</button><button class='action-button'>Email Employee</button></td></tr>"
                    }
                    grogu += "</tbody>";
                    grogu += "</table>";
                    grogu += "<p class='tiny-text'>&#128161; Interact with a specific line by selecting it</p>";
                    grogu += "<p class='tiny-text'>+  Indicates request was submitted in the previous fiscal year.</p>";
                    grogu += "<div class='orderSummary'>";
                    grogu += "<div class='total' id='orderTotal'>";
                    grogu += "</div>";
                    grogu += "<div class='itemTotal' id='itemTotal'>";
                    grogu += "</div>";
                    grogu += "</div>";
                    grogu += "</div>";
                    grogu += "<div id='spend-summary'></div>";

                    var orderTotal = "<p class='receipt'>Request Total: " + money_format(totalCost) + "</p>";
                    var itemTotal = "<p class='receipt'>Item Count: " + parseInt(totalCount) + "</p>";

                    // console.log('itemTotal is ', itemTotal)
                    function feedPopover(orderTotal, itemTotal) {
                        // alert("These are the values" + totalCost + ' ' + totalCount);
                        var popOverValues = "";
                        popOverValues += "<p>Confirm your decision for these <b>" + totalCount +
                            "</b> items - for a cost of  <b>$" +
                            totalCost + "</b></p>";
                        popOverValues += "<div class='buttons-in-approval-popover-holder'>"
                        popOverValues += "<button class='confirm-approval-button' value='" + order_id +
                            "' onclick='approveWholeOrder(this.value)'>Confirm Approval</button>";
                        popOverValues += "<button class='huge-mistake-button' value='" + order_id +
                            "' onclick='denyWholeOrder(this.value)'>Deny Order</button>";
                        // popOverValues += "<span aria-hidden='true'>MyB</span>";
                        // popOverValues += "<span class='sr-only'>Close</span>";

                        popOverValues += "</div>"
                        document.getElementById('popover-values').innerHTML = popOverValues
                    }

                    function makeReceiveButtons() {
                        var recButtons = "";
                        recButtons += "<div class='buttons-in-approval-popover-holder'>"
                        recButtons += "<button class='approve' value='" + order_id + "' onclick='receiveWholeOrder(this.value)'>Receive All</button>";
                        recButtons += "<button class='deny' popovertarget='receive-whole-order-confirm' popovertargetaction='hide'>Nope, I'm out of here</button>";
                        recButtons += "</div>";
                        document.getElementById('receive-popover-btns').innerHTML = recButtons;
                    }
                    feedPopover();
                    if (data[0].status == 'Ordered') {
                        makeReceiveButtons();
                    }
                    document.getElementById('details').innerHTML = grogu;
                    document.getElementById('orderTotal').innerHTML = orderTotal;
                    document.getElementById('itemTotal').innerHTML = itemTotal;
                    getDepartmentTotals(data[0].department)
                        .then(() => getEmpTotals(data[0].emp_id))
                        .catch((error) => {
                            console.error("Error in department and or Emp totals: ", error);
                        });
                    setupImagePopover()
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
                    console.log(data);
                    var fydata = "";
                    fydata += `<span class='table-title'>${data[4].dep_name} FY Totals</span>`
                    fydata += "<div class='emp-info-holder'>"
                    fydata += "<span> <b>Submitted</b> " + money_format(data[0].dep_submitted) + "</span>";
                    fydata += "<span> <b>Approved</b> " + money_format(data[1].dep_approved) + "</span>";
                    fydata += "<span> <b>Ordered</b> " + money_format(data[2].dep_ordered) + "</span>";
                    fydata += "<span> <b>Completed</b> " + money_format(data[3].dep_completed) + "</span>";
                    fydata += "</div>";
                    // fydata += "<div class='div6' id='empTotals'>Emp Totals</div>";
                    fydata += "<div class='totals-slideout'  id='empTotals'>Emp Totals</div>";
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
                    // console.log(data);
                    var mando = "";
                    mando += "<span class='table-title'>" + data[8].empName + " Clothing FY Totals</span>";
                    mando += "<div class='emp-info-holder'>";
                    mando += "<span> <b>Submitted:</b> " + money_format(data[0].emp_submitted) + "</span>";
                    mando += "<span> <b>Approved:</b>  " + money_format(data[1].emp_approved) + "</span>";
                    mando += "<span> <b>Ordered:</b>   " + money_format(data[2].emp_ordered) + "</span>";
                    mando += "<span> <b>Completed:</b> " + money_format(data[3].emp_completed) + "</span>";
                    mando += "</div>"
                    mando += "<span class='table-title'>" + data[8].empName + " Boots FY Totals</span>";
                    mando += "<div class='emp-info-holder'>";

                    mando += "<span> <b>Submitted:</b> " + money_format(data[4].emp_boots_submitted) + "</span>";
                    mando += "<span> <b>Approved:</b>  " + money_format(data[5].emp_boots_approved) + "</span>";
                    mando += "<span> <b>Ordered:</b>   " + money_format(data[6].emp_boots_ordered) + "</span>";
                    mando += "<span> <b>Completed:</b> " + money_format(data[7].emp_boots_completed) + "</span>";
                    mando += "</div>"
                    mando += "<p class='receipt'>FY Start: " + extractDateFromDB(data[9].fy_start) + '</p><p class="receipt"> ' +
                        "FY End: " + extractDateFromDB(data[10].fy_end) + "</p>";
                    mando += "</div>";
                    document.getElementById('empTotals').innerHTML = mando;
                })
        }

        function clickFirst() {
            var mainListHolder = document.getElementById('main-list-holder');
            if (mainListHolder) {
                var table = mainListHolder.querySelector("table");
                if (table) {
                    var firstRow = table.querySelector("tbody tr:first-child");
                    if (firstRow) {
                        firstRow.click();
                        introJs().setOption("dontShowAgain", true).start();
                        // introJs().addHints();
                        // introJs().start();
                    }
                }
            }
        }

        function deleteCookie(cookieName) {
            if (document.cookie.includes('introjs-dontShowAgain')) {
                document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
                // console.log(`Deleted cookie: ${cookieName}`);
                window.location.reload();
            } else {
                console.log(`Cookie not found: ${cookieName}`)
            }
        }

        function genPOreq(id) {
            // alert('Making PO Request for ' + id)
            var reqHolder = document.getElementById('po-req-values')
            fetch('getOrderDetails.php?id=' + id)
                .then((response) => response.json())
                .then((data) => {
                    var poreq = ""
                    poreq += `
                    <table class='styled-table'>
                    <tr>
                        <th>Dept Req For</th>
                        <th>Emp Req For</th>
                        <th>Qty</th>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Pre Unit Cost</th>
                        <th>Vendor Name</th>
                        <th>Vendor Number</th>
                    </tr>`
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].vendor_id === 3) {
                            poreq += `<tr>
                                <td>${data[i].dep_name} <br> Bill to: (${data[i].bill_to_dept}) </td>
                                <td>${data[i].rf_first_name} ${data[i].rf_last_name}</td>
                                <td>${data[i].quantity}</td>
                                <td>${data[i].product_code}</td>
                                <td>${data[i].product_name}</td>
                                <td>$ ${data[i].pre_tax_price}</td>
                                <td>${data[i].vendor}</td>
                                <td>${data[i].vendor_number_finance}</td>
                            </tr>`
                        }
                    }
                    poreq += `<table>`
                    reqHolder.innerHTML = poreq;
                })
            var poReqPopover = document.getElementById('po-req')
            poReqPopover.showPopover();
        }

        // function hideDenied() {
        //     var denied = document.getElementsByClassName('Denied')
        //     console.log(denied);
        // }
    </script>

</head>

<body onload="getRequests()">
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2" id="main" data-title="Orders List" data-intro="These are all the orders submitted for your department(s). Each order most likely has several line items in each one." data-step="6">
        </div>
        <div class="div3" id="details" data-title="Detailed Order Info" data-intro="These are the specific items requested by the employee. You can approve or deny them all at once using the button to the left; or one at time by clicking a specific line item. When selecting a specific line item a popup menu will allow you to make decisions on the specific line item." data-step="7">
        </div>
        <div class="div4">
            <!-- // ? Alert banner renders here  -->
            <div class="alert-banner" id="alert-banner"></div>
        </div>
        <div class="div6 total-hidden" id="totalsOffCanvas">
            <div class="div5" id="depTotals" data-title="Department and Employee Information" data-intro="Here you can see Fiscal Year data for your Department as well as detailed information about the employee for whom this request has been submitted. Values are broken out by status as well if the request is for clothing or boots." data-step="8">
            </div>
        </div>
        <div class="div7 hidden" id="showTotalsButton" data-title="Toggle View" data-intro="Need more screen space? Toggle the view for Department and Employee Totals here" data-step="5">
            <button class="button seeTotals" id="seeTotals" onclick="showTotals()">Show Totals</button>
        </div>
    </div>
    <button id="showButton">Show</button>
    <!-- CONVERTING from off-canvas to popover. This is the popover -->
    <div id="not-off-canvas" popover=manual>
        <button class="button close-btn" popovertarget="not-off-canvas" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <div class="request-action-options">
            <!-- <p>Request Line Item Action Options</p> -->
            <div class="action-buttons" id="action-buttons">
            </div>
        </div>
    </div>

    <div id="action-jackson" popover=manual>
        <h1>ACTION JACKSON!!!!</h1>
    </div>

    <div id="whole-order-confirm" popover=manual>
        <button class="button close-btn" popovertarget="whole-order-confirm" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <span id="confirm-details-holder">
            <p>This will <mark class="approve">approve</mark> or <mark class="deny">deny</mark> <b>every item</b> listed
                in this order. Approve or deny
                specific lines items in this
                request by selecting the individual line item.</p>
            <br>
            <p id="popover-values"></p>
        </span>

    </div>
    <div id="whole-order-deny-confirm" popover=manual>
        <button class="close-btn" popovertarget="whole-order-deny-confirm" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <span id="confirm-details-holder">
            <p>This will <mark class="deny">deny</mark> <b>every item</b> listed in this order. Approve or deny specific
                lines items
                in this
                request by selecting the individual line item.</p>
            <br>
            <p id="popover-values"></p>
        </span>
    </div>
    <div id="po-req" popover=manual>
        <button class=" button close-btn" popovertarget="po-req" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <span id="po-req-details">
            <p class="hide-from-printer">Print this request for the details needed to make a PO request.</p>
            <br>
            <p id="po-req-values"></p>
            <button onclick='printReq(this)' type='button'>Print</button>
        </span>
    </div>
    <div id="receive-whole-order-confirm" popover=manual>
        <button class="button close-btn" popovertarget="receive-whole-order-confirm" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <span id="confirm-details-holder">
            <p>This will <mark class="approve">receive</mark> <b>every item</b> listed
                in this order. Receive
                specific lines items in this
                request by selecting the individual line item.</p>
            <p>Contact the Help Desk regarding any changes to this request.</p>
            <!-- <p>
            <pre>
                </?php echo var_dump($_SESSION) ?>
            </pre>
            </p> -->
            <br>
            <div id="receive-popover-btns">

            </div>
            <!-- <div class="buttons-in-approval-popover-holder">
                <button class='approve receive-btn'>Receive All Items</button>
                <button class=' deny receive-btn' popovertarget="receive-whole-order-confirm" popovertargetaction="hide">Nope, I am out of here</button>
            </div> -->
            <!-- <p id="popover-values"></p> -->
        </span>

    </div>

    <script>
        // introJs().setOption("dontShowAgain", true).start();
        // introJs().start();
    </script>
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

    // showButton.addEventListener('click', showOffCanvas);
    // closeButton.addEventListener('click', hideOffCanvas);

    window.addEventListener('click', (event) => {
        if (event.target === offCanvas) {
            hideOffCanvas();
        }
    });

    function showActionButtons() {
        var buttonDiv = document.getElementById('not-off-canvas')
        buttonDiv.showPopover();
    }

    function setLineItemSession(order_details_id, status) {
        // console.log('order_details_id: ', order_details_id);
        console.log('status: ', status);

        fetch('setLineItemSession.php?id=' + order_details_id)
            .then((response) => response.json())
            .then((data) => {
                // console.log('(*)(*)(*)(*)(*)');
                // console.log(order_details_id);
                // console.log('data: ');
                // console.log(data);
                var html = '';
                if (status == 'Ordered') {
                    html += "<button class='receive' onclick='receiveItem(" + order_details_id +
                        ")' popovertarget='action-jackson' popovertargetaction='show'> Receive </button>";
                    html += "<button class='approve' disabled>Approve</button>";
                    html += "<button class='deny' disabled>Deny</button>";
                    html += "<button class='edit' disabled>Edit</button>";
                    html += "<p>Orders in this status can not be edited.</p>"
                } else if (status == 'Received') {
                    html += "<button class='approve' disabled>Approve</button>";
                    html += "<button class='deny' disabled>Deny</button>";
                    html += "<button class='edit' disabled>Edit</button>";
                    html += "<p>Orders in this status can not be edited.</p>"
                } else {
                    html += " <button class='approve' onclick='approveRequest(" + order_details_id +
                        ")' popovertarget='action-jackson' popovertargetaction='show'> Approve </button>";
                    html += "<button class='deny' onclick='denyRequest(" + order_details_id +
                        ")' popovertarget='action-jackson' popovertargetaction='show'> Deny </button>";
                    html += "<button class='comment' onclick='addComment(" + order_details_id +
                        ")' popovertarget='action-jackson' popovertargetaction='show'> Comment </button>";
                    html += "<button class='edit'><a href='./edit-request.php?order_id=" + order_details_id +
                        "'>Edit</a></button>";
                };
                document.getElementById('action-buttons').innerHTML = html;
                // showOffCanvas();
                showActionButtons();
            })
    }
    async function getComments(id) {
        await fetch('getCommentsForDisplay.php?id=' + id)
            .then((res) => res.json())
            .then((data) => {
                // console.log(data);
                comments =
                    "<table> <tr><th width='50%'>Comment</th><th width='20%'>By</th><th width='20%'>On</th></tr>"
                for (var c = 0; c < data.length; c++) {
                    comments += "<tr><td>" + data[c].comment + "</td><td>" + data[c].empName + " </td><td>" + data[
                        c].submitted + "</td></tr>";
                }
                comments += "</table>"
                document.getElementById('display-comments').innerHTML = comments;
            })
    }

    function receiveItem(id) {
        // console.log('Receiving ' + id);
        commentData = getComments(id);
        var html = "";
        html +=
            "<button class='button close-btn' popovertarget='action-jackson' popovertargetaction='hide' onclick='hideOffCanvas()'>";
        html += "<span aria-hidden='true'> ❌ </span>";
        html += "<span class='sr-only'> Close </span>";
        html += "</button>";
        html += "<h3>Receiving item for order # " + id + ". Enter any comments below - comments are optional for receiving items</h3>";

        html += "<form action='set-request-status.php' method='post'>";
        html += "<input type='hidden' name='id' value='" + id + "' />";
        html += "<input type='hidden' name='status' value='Received' />";
        html += "<textarea name='comment' id='comment' cols='60' rows='5'></textarea>";
        html += "<br />";
        html += "<button class='submit-approve' type='submit'>Submit</button>";
        html += "</form>";
        html += "<p id='display-comments'></p>";
        document.getElementById('action-jackson').innerHTML = html;
    }

    function approveRequest(id) { // alert('Approving ' + id);
        commentData = getComments(id);
        var html = "";
        html +=
            "<button class='button close-btn' popovertarget='action-jackson' popovertargetaction='hide' onclick='hideOffCanvas()'>";
        html += "<span aria-hidden='true'> ❌ </span>";
        html += "<span class='sr-only'> Close </span>";
        html += "</button>";
        html += "<h3>Approving request # " + id + ". Enter any comments below - comments are optional for approvals</h3>";

        // html += "<p></p>"
        html += "<form action='set-request-status.php' method='post'>";
        html += "<input type='hidden' name='id' value='" + id + "' />";
        html += "<input type='hidden' name='status' value='Approved' />";
        html += "<textarea name='comment' id='comment' cols='60' rows='5'></textarea>";
        html += "<br />";
        html += "<button class='submit-approve' type='submit'>Submit</button>";
        html += "</form>";
        html += "<p id='display-comments'></p>";
        document.getElementById('action-jackson').innerHTML = html;
    }

    function denyRequest(id) {
        // alert('DENIED SUCKA ' + id);
        commentData = getComments(id);
        var html = "";
        html +=
            "<button class='button close-btn' popovertarget='action-jackson' popovertargetaction='hide' onclick='hideOffCanvas()'>";
        html += "<span aria-hidden='true'> ❌ </span>";
        html += "<span class='sr-only'> Close </span>";
        html += "</button>";
        html += "<h3>Denying request # " + id +
            ". Enter any comments below - comments are required for denied requests</h3>";
        // html += "<p></p>"
        html += "<form action='set-request-status.php' method='post'>";
        html += "<input type='hidden' name='id' value='" + id + "' />";
        html += "<input type='hidden' name='status' value='Denied' />";
        html += "<textarea name='comment' id='comment' cols='60' rows='5' oninput='enableButton()'></textarea>";
        html += "<br />";
        html += "<button class='submit-approve' type='submit' disabled id='denySubmit'>Submit</button>";
        html += "</form>";
        html += "<p id='display-comments'></p>";
        document.getElementById('action-jackson').innerHTML = html;
    }

    function addComment(id) {
        // alert('Commenting on ' + id);
        commentData = getComments(id);
        var html = "";
        html +=
            "<button class='button close-btn' popovertarget='action-jackson' popovertargetaction='hide' onclick='hideOffCanvas()'>";
        html += "<span aria-hidden='true'> ❌ </span>";
        html += "<span class='sr-only'> Close </span>";
        html += "</button>";
        html += "<h3>Commenting on request # " + id +
            ". </h3>";
        // html += "<p></p>"
        html += "<form action='add-comment-noaction.php' method='post'>";
        html += "<input type='hidden' name='id' value='" + id + "' />";
        // html += "<input type='hidden' name='status' value='Denied' />";
        html += "<textarea name='comment' id='comment' cols='60' rows='5'></textarea>";
        html += "<br />";
        html += "<button class='submit-approve' type='submit'>Submit</button>";
        html += "</form>";
        html += "<p id='display-comments'></p>";
        document.getElementById('action-jackson').innerHTML = html;
    }

    function unsetLineItemSession() {
        fetch('unsetLineItemSession.php')
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
            })
    }

    function showTotalsButtonUnhide() {
        document.getElementById('showTotalsButton').classList.toggle('hidden');
    }


    function showTotals() {
        // console.log('show totals clicked')
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


    function enableButton() {
        // denyComment = document.getElementById('comment');
        denySubmit = document.getElementById('denySubmit');

        denySubmit.disabled = false;
    }

    function approveWholeOrder(id) {
        // alert('You just approved a HUGE order!!!');
        fetch('approve-whole-order.php?order_id=' + id)
            .then(window.location.reload());
    }

    function denyWholeOrder(id) {
        // alert('You just approved a HUGE order!!!');
        fetch('deny-whole-order.php?order_id=' + id)
            .then(window.location.reload());
    }

    function receiveWholeOrder(orderId) {
        //alert('Okay we will receive everything in order ' + orderId)
        fetch('receive-whole-order.php?order_id' + orderId)
            .then(window.location.reload());
    }

    function setActiveRequest(id) {
        var activeRows = document.querySelectorAll("tr.active-request");
        activeRows.forEach(function(row) {
            row.classList.remove("active-request");
        });
        var targetRow = document.querySelector(`tr[value="${id}"]`);
        if (targetRow) {
            targetRow.classList.add("active-request");
        }
    }

    function printReq() {
        var poReqValuesContent = document.getElementById('po-req-values').innerText;

        // console.log(poReqValuesContent);
        window.print(poReqValuesContent);
    }
    // test function to see if I can make this work. 

    function setupImagePopover() {

        const existingPopovers = document.querySelectorAll('.prod-img-popover');
        // console.log(existingPopovers);
        existingPopovers.forEach(popover => {
            popover.parentNode.removeChild(popover);
        });

        let j = 0;
        let imageCell = document.getElementById(`prod_img_${j}`);
        // console.log(imageCell);
        //const imgElement = imageCell.querySelector('img');

        while (imageCell) {
            const imgElement = imageCell.querySelector('img');

            const popover = document.createElement('div');
            popover.classList.add('prod-img-popover');
            popover.id = `popover_${j}`;
            document.body.appendChild(popover);

            imageCell.addEventListener('mouseenter', function(event) {
                // console.log('mouseenter');

                const src = imgElement.src;
                const alt = imgElement.alt;

                const img = document.createElement('img');
                img.src = src;
                img.alt = alt;

                img.style.width = '400px';
                img.style.height = 'auto';
                popover.innerHTML = '';
                popover.appendChild(img);

                // const viewportWidth = window.innerWidth;
                // const viewportHeight = window.innerHeight;
                // const popoverWidth = popover.offsetWidth;
                // const popoverHeight = popover.offsetHeight;

                // const popoverTop = (viewportHeight - popoverHeight) / 2;
                // const popoverLeft = (viewportWidth - popoverWidth) / 2;


                // popover.style.top = `${event.clientY}px`;
                // popover.style.top = `${popoverTop}px`;
                popover.style.top = '25%';
                // popover.style.left = `${event.clientX}px`;
                // popover.style.left = `${popoverLeft}px`;
                popover.style.left = '50%';

                popover.style.position = 'fixed';
                popover.style.zIndex = '1000';
                popover.style.display = 'block';


            });

            imageCell.addEventListener('mouseleave', function() {
                popover.style.display = 'none';
                const popoverTop = "";
                const popoverLeft = "";
            });
            j++;
            imageCell = document.getElementById(`prod_img_${j}`);
            //})
        }
    }
    // Work in progress .... 
    // function getFilterInput() {
    //     document.getElementById('filter-input').addEventListener('input', function() {
    //         console.log(firstData)
    //         const filterText = this.value.toLowerCase();
    //         console.log(filterText)
    //         const filteredData = firstData.filter(item =>
    //             item.includes(filterText)
    //         )
    //         if (filteredData) {
    //             console.log('We have a match');
    //         }
    //     })
    // }
    function displayAlert() {
        var fyData = fiscalYear();
        var html = '';
        html += `<div class="alert-text">
        🚨 All requests must be submitted by May 31st, ${fyData[0]}. Requests will not be able to be submitted between June 1st and June 30th, ${fyData[1]}</div>`
        document.getElementById('alert-banner').innerHTML = html
    }
    displayAlert();
</script>

</html>

<style>
    @font-face {
        font-family: 'bcg';
        src: url('../../fonts/Gotham-Medium.otf');
    }

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
        font-family: bcg;
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



    .div4 {
        grid-area: 1 / 2 / 1 / 6;
        height: 45px;
        align-items: baseline;
    }


    /* Keep this here for now */
    .total-hidden {
        margin-left: 550px;
    }



    .hidden {
        visibility: hidden;
    }

    #main {
        overflow-y: auto;


    }

    .emp-info-holder,
    .main-list-holder,
    .main-order-info-holder,
    .dep-info-holder {
        /* border-top: #256141 15px solid; */
        border-top: var(--table-row-alt-bg-color) 15px solid;
        border-bottom: #256141 5px solid;
        margin-top: 25px;
    }

    .main-list-holder table td,
    .main-order-info-holder table td {
        cursor: pointer;
    }

    .emp-info-holder {
        /* position: relative; */
        /* z-index: 2; */
        display: grid;
        grid-template-columns: 1fr 1fr;
        justify-items: end;
        gap: 15px;
        background-color: #d3d3d3;
        padding-right: 10px;

    }

    .dep-info-holder {
        width: 80%;
        align-items: flex-end;
    }

    .details {
        box-shadow: 10px 10px 38px -17px rgba(59, 54, 59, 1);
    }

    .table-title {
        display: flex;
        justify-content: space-evenly;
        align-content: flex-start;
        text-wrap: balance;
        /* font-size: max(1.25vw, 14px); */
        text-align: center;
        padding-top: 20px;
        width: auto;
        background-color: #FFFFFF90;

        .approve-order-button {
            margin-top: -5px;
            ;
        }
    }


    .totals td {
        text-align: center;
    }

    .itemTotal,
    .total {
        background-color: #00000020;
        /* color: #000000; */
    }

    /* .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.8em;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    } */

    .styled-table thead tr {
        position: sticky;
        top: 0;
        background-color: var(--table-row-alt-bg-color);
        color: var(--table-row-alt-text-color);
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
        border-bottom: 2px solid var(--table-row-alt-bg-color);
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: var(--table-row-alt-bg-color);
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
        /* display: none; */
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

    /* .prod_img:hover {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        transform: scale(5.0);
        margin-left: auto;
        margin-top: auto;
        margin-right: auto;
        margin-bottom: auto;

    } */

    .receipt {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
        font-size: large;
    }

    .orderSummary {
        background-color: #00000099;
        color: #FFFFFF;
        padding: 5px;
        border-radius: 5px;
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
        align-items: baseline;
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

    .receive-btn:hover,
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
        color: #000000;
    }

    .action-buttons .edit {
        border: black 1px solid;
        background-color: #525252;
        /* color: #FFFFFF; */

        a {
            color: #FFFFFF;
        }
    }

    .action-buttons .receive {
        font-size: small;
        background-color: #389CFF;
        color: #000000;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid darkblue;
    }

    #receive-whole-order-confirm,
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
        /* border: 2px solid tomato; */
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
        /* filter: grayscale() brightness(20); */

    }

    .receive-btn {
        cursor: pointer;
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
        /* color: rgba(0, 0, 0, 0.85) */
        background-color: #00000090;
        color: #FFFFFF;
        padding: 5px;
        border-radius: 5px;
    }

    /* .receive {
        font-size: small;
        background-color: #6B6B6B;
        color: #FFFFFF;
        padding: 5px;
        border-radius: 5px;
    } */

    .active-request {
        background-color: #008000 !important;
        color: #FFFFFF;
        font-weight: bold;
    }

    #not-off-canvas {
        margin: auto;
        position: fixed;
        width: 40%;
        height: 20%;
        overflow: hidden;
        color: #000000;
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

    #prodImgPopover {
        display: none;
        position: absolute;
        border: 1px solid #ccc;
        padding: 10px;
        background-color: #fff;
        z-index: 999;
    }

    [data-currentfy=false] td:first-of-type::before {
        content: '\2795';
    }


    [data-currentfy=false] td {
        color: grey;
    }

    .alert-banner {
        background-color: #1F9CED;
        color: #000000;
        justify-content: center;
        align-items: center;
        padding: 20px;
        width: 100%;
        gap: 25px;
    }

    .alert-text {
        text-align: center;
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
</style>