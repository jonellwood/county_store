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
<!-- <link href="prod-admin-style.css" rel="stylesheet" /> -->
<script src="../../functions/formatForUrl.js"></script>
<script src="components/createPopoverButton.js"></script>
<script src="functions/renderRequestList.js"></script>
<script src="functions/renderOrderDetails.js"></script>
<script src="functions/renderDepartmentTotals.js"></script>
<script src="functions/renderEmployeeTotals.js"></script>
<script src="functions/helpers.js"></script>
<script src="functions/createActionPopover.js"></script>
<script src="functions/createWholeOrderActionPopover.js"></script>
<script src="functions/logAction.js"></script>

<script>
let firstData = [];
async function getRequests() {
    // await fetch('./getAllRequests.php')
    // await fetch('./getDeptRequests.php')
    await fetch('./getDeptRequestsRefactored.php')
        .then((response) => response.json())
        .then((data) => {
            firstData.push(data);
            renderRequestList(data)
            clickFirst();

            // getDepartmentTotals(data[0].department)
        })
}
getRequests();

function makeDeptTotalsButton() {
    var button = createPopoverButton('totals-popover', 'See Totals');
    const target = document.getElementById('totals-button');
    // console.log(target);
    target.innerHTML = button;
}


async function getOrderDetails(order_id) {
    // console.log('order_id', order_id);
    setActiveRequest(order_id);
    await fetch('getOrderDetails.php?id=' + order_id)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data)
            renderOrderDetails(data);
            getDepartmentTotals(data[0].department)
            getEmpTotals(data[0].emp_id);


        })
        .then(() => makeDeptTotalsButton())
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
            renderDepartmentTotals(data);
            // makeDeptTotalsButton()


        })
}

async function getEmpTotals(emp) {
    await fetch('getEmployeeTotalsFY.php?emp=' + emp)
        .then((response) => response.json())
        .then((data) => {
            renderEmployeeTotals(data, 'employeeTotals')
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
            }
        }
    }
}

// function deleteCookie(cookieName) {
//     if (document.cookie.includes('introjs-dontShowAgain')) {
//         document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
//         // console.log(`Deleted cookie: ${cookieName}`);
//         window.location.reload();
//     } else {
//         console.log(`Cookie not found: ${cookieName}`)
//     }
// }

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



<!-- <body onload="getRequests()"> -->

<!-- <div> -->
<!-- // ? Alert banner renders here  -->
<!-- <div class="alert-banner" id="alert-banner"></div> -->
<!-- </div>
                <div class="total-hidden" id="totalsOffCanvas">
                    <div id="depTotals">
                    </div>
                </div>
                <div class="total-hidden" id="showTotalsButton">
                    <button class="button seeTotals" id="seeTotals" onclick="showTotals()">Show Totals</button>
                </div>
            </div>
        </div>
    </div> -->
<!-- <button id="showButton">Show</button> -->
<!-- CONVERTING from off-canvas to popover. This is the popover -->
<div id="not-off-canvas" popover=manual class="action-options-popover">
    <button class="btn btn-outline-dark btn-close" popovertarget="not-off-canvas" popovertargetaction="hide">
        <span aria-hidden=”true”></span>
        <span class="sr-only"></span>
    </button>
    <div class="request-action-options">
        <!-- <p>Request Line Item Action Options</p> -->
        <div class="action-buttons" id="action-buttons">
        </div>
    </div>
</div>

<div id="action-jackson" popover=manual popover>
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

function setLineItemSession(order_details_id, status, department) {
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
                html += "<button class='btn btn-receive' onclick='receiveItem(" + order_details_id +
                    ")' popovertarget='action-jackson' popovertargetaction='show'> Receive </button>";
                html += "<button class='btn btn-approve' disabled>Approve</button>";
                html += "<button class='btn btn-deny' disabled>Deny</button>";
                html += "<button class='btn btn-edit' disabled>Edit</button>";
                html += "<p>Orders in this status can not be edited.</p>"
            } else if (status == 'Received') {
                html += "<button class='btn btn-approve' disabled>Approve</button>";
                html += "<button class='btn btn-deny' disabled>Deny</button>";
                html += "<button class='btn btn-edit' disabled>Edit</button>";
                html += "<p>Orders in this status can not be edited.</p>"
            } else if (status == 'Approved') {
                html +=
                    `
                    <button class='btn btn-deny' onclick='createActionPopover(${order_details_id}, "deny")' popovertarget='action-jackson' popovertargetaction='show'> Deny </button>
                    <button class='btn btn-comment' onclick='createActionPopover(${order_details_id}, "comment")' popovertarget='action-jackson' popovertargetaction='show'> Comment </button>
                    <button class='btn btn-edit'><a href='./edit-request.php?order_id="${order_details_id}"&department=${department}'>Edit</a></button>`
            } else {
                html +=
                    `<button class='btn btn-approve' onclick='createActionPopover(${order_details_id}, "approve")' popovertarget='action-jackson' popovertargetaction='show'> Approve </button>
                    <button class='btn btn-deny' onclick='createActionPopover(${order_details_id}, "deny")' popovertarget='action-jackson' popovertargetaction='show'> Deny </button>
                    <button class='btn btn-comment' onclick='createActionPopover(${order_details_id}, "comment")' popovertarget='action-jackson' popovertargetaction='show'> Comment </button>
                    <button class='btn btn-edit'><a href='./edit-request.php?order_id="${order_details_id}"&department=${department}'>Edit</a></button>`
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
            if (data.length > 0) {
                comments =
                    `<table> <tr><th width='50%'>Comment</th><th width='20%'>By</th><th width='20%'>On</th></tr>`
                for (var c = 0; c < data.length; c++) {
                    comments +=
                        `<tr><td> ${data[c].comment ? data[c].comment : ''} </td><td> ${data[c].empName ? data[c].empName : ''}</td><td>${data[c].submitted ? data[c].submitted : ''}</td></tr>`;
                }
                comments += "</table>"
                document.getElementById('display-comments').innerHTML = comments;
            } else {
                document.getElementById('display-comments').innerHTML =
                    "<p class='no-comment'>No comments yet</p>";
            }
        })
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
    html +=
        `<div class="info-banner">
        🚨 All requests must be submitted by May 31st, ${fyData[0]}. Requests will not be able to be submitted between June 1st and June 30th, ${fyData[1]}</div>`
    document.getElementById('alert-banner').innerHTML = html
}
displayAlert();
</script>

</html>

<style>
/* @font-face {
    font-family: 'bcg';
    src: url('../../fonts/Gotham-Medium.otf');
} */

@keyframes slide {
    from {
        translate: 100vi;
    }
}

#totals-popover[popover] {
    transition:
        display 0.3s allow-discrete,
        overlay 0.5s allow-discrete,
        opacity 0.3s,
        translate 0.3s;
    animation: slide 0.5s ease-in-out reverse;
    transition-timing-function: ease-in;
    translate: 100%;
    margin: 0;
    block-size: 100vb;
    inline-size: 25vi;
    inset-inline-start: unset;
    inset-inline-end: 0;
    box-shadow: 0 0 25px -5px #808080;


    &:popover-open {
        animation: slide 0.5s ease-in-out forwards;
        translate: 0;
        transition-timing-function: ease-out;
    }
}

#not-off-canvas[popover],
#action-jackson[popover] {
    transition: display 0.3s allow-discrete, opacity 0.3s, translate 0.3s;
    transition-timing-function: ease-in;
    opacity: 0;
    translate: 0 50%;
}

#not-off-canvas[popover]:popover-open,
#action-jackson[popover]:popover-open {
    opacity: 1;
    translate: 0 0;
    transition-timing-function: ease-out;
}

@starting-style {
    #action-jackson[popover]:popover-open {
        opacity: 0;
        translate: 0 50%;
    }

    #not-off-canvas[popover]:popover-open {
        opacity: 0;
        translate: 0 50%;
    }
}

/* // !Specific to this page - this alters the commonHead template */
.div3 {
    grid-area: 2/3/2/5 !important;
    position: relative;
    z-index: 1;
}

.div6 {
    align-items: flex-start;
    position: relative;
    z-index: 0;
}

.action-options-popover {
    width: 30%;
    min-height: 15%;
    box-shadow: 0 0 25px -5px #000000;
}

.action-options-popover[popover] {
    &:popover-open {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
}

.btn-close {
    width: fit-content;
    margin: 10px;
}

.btn-holder-in-popover {
    margin-top: 5%;
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
    justify-content: center;
    align-content: center;
    text-align: center;

}




/* img,
picture,
video,
canvas,
svg {
    display: block;
    max-width: 100%;
} */

/* input,
button,
textarea,
select {
    font: inherit;
} */

/* p,
h1,
h2,
h3,
h4,
h5,
h6 {
    overflow-wrap: break-word;
} */

/* #root,
#__next {
    isolation: isolate;
} */



/* .div4 {
    grid-area: 1 / 2 / 1 / 6;
    height: 45px;
    align-items: baseline;
} */


/* Keep this here for now */
/* .total-hidden {
    margin-left: 550px;
} */



/* .hidden {
    visibility: hidden;
} */

/* #main {
    overflow-y: auto;
} */

/* .emp-info-holder, */
/* .main-list-holder, */
.main-order-info-holder {
    border-top: var(--table-row-alt-bg-color) 15px solid;
    border-bottom: #256141 5px solid;
    margin-top: 25px;
}

.stats-key-holder {
    display: flex;
    justify-content: space-between;
}

.stats-key-holder span:first-child {
    margin-left: 0;
}

.stats-key-holder span:last-child {
    margin-right: 0 !important;
}

/* .main-list-holder table td,
.main-order-info-holder table td {
    cursor: pointer;
} */

.emp-info-holder {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    background-color: #d3d3d3;
    padding: 10px;

    span {
        margin-left: 10px;
    }
}

/* .dep-info-holder {
    width: 80%;
    align-items: flex-end;
} */

/* .details {
    box-shadow: 10px 10px 38px -17px rgba(59, 54, 59, 1);
} */

.table-title {
    display: flex;
    justify-content: space-evenly;
    align-content: flex-start;
    text-wrap: balance;
    font-size: max(1.25vw, 14px);
    margin-top: 10px;
    text-align: center;
    padding-top: 10px;
    padding-bottom: 10px;
    width: auto;
    background-color: #00000099;
    color: #FFF;

    .approve-order-button {
        margin-top: -5px;
        ;
    }
}


/* .totals td {
    text-align: center;
} */

/* .itemTotal,
.total {
    background-color: #00000020;
   
} */

/* .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.8em;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    } */

/* .styled-table thead tr {
    position: sticky;
    top: 0;
    background-color: var(--table-row-alt-bg-color);
    color: var(--table-row-alt-text-color);
    text-align: center;
} */

/* .center-text {
    text-align: center
} */

/* .styled-table th,
.styled-table td {
    padding: 12px 15px;
} */

/* .styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
    height: 75px;
} */

/* .styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
} */

/* .styled-table tbody tr:last-of-type {
    border-bottom: 2px solid var(--table-row-alt-bg-color);
} */

/* .styled-table tbody tr.active-row {
    font-weight: bold;
    color: var(--table-row-alt-bg-color);
} */

/* .order-details-row th {
    border-right: #057a6d 1px solid;
} */

/* .orders-list-row th {
    border-right: #057a6d 1px solid;
} */

/* tr:nth-child(odd) td {
    border-right: 1px solid #f3f3f3;
} */

/* tr:nth-child(even) td {
    border-right: 1px solid #e1e0db;
} */

/* .Pending td {
    background-color: #b3ffb3;
    border-bottom: #008000 2px solid;
} */

/* .Approved td {
    background-color: #b3b3ff;
    border-bottom: #0000ff 2px solid;
} */

/* .Denied td {
    background-color: #ff000050;
    border-bottom: #ff0000 2px solid;
} */

/* .Ordered td {
    background-color: #ffb3b3;
    border-bottom: #ff0000 2px solid;
} */

/* .Received td {
    background-color: #ffedcc;
    border-bottom: #ffa500 2px solid;
} */

/* td.img {
    background-color: #808080;
} */

/* tr img {
    width: 50px !important;
} */

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

/* .receipt {
    text-align: right;
    font-family: 'Courier New', Courier, monospace;
    font-weight: bold;
    font-size: large;
} */

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
    width: 100%;
    height: 100%;
    background-color: #f1f1f1;
    padding: 5px;
}

.action-buttons {
    display: flex;
    align-items: baseline;
    gap: 20px;
    padding-top: 10px;

}

/* .action-buttons button {
    border-radius: 5px;
   
    cursor: pointer;
    font-family: monospace;
    font-size: medium;
    padding-left: 10px;
    padding-top: 5px;
    padding-right: 10px;
    padding-bottom: 5px;
} */

/* .receive-btn:hover,
.action-buttons button:hover {
    transform: scale3d(1.05, 1.05, 1.05);
    font-weight: bolder;
   
} */

/* .action-buttons .approve {
    border: darkgreen 1px solid;
    background-color: lightgreen;
} */

/* .action-buttons .deny {
    border: darkred 1px solid;
    background-color: lightcoral;
} */

/* .action-buttons .comment {
    border: darkblue 1px solid;
    background-color: lightblue;
    color: #000000;
} */

/* .action-buttons .edit {
    border: black 1px solid;
    background-color: #525252;
   

    a {
        color: #FFFFFF;
    }
} */

/* .action-buttons .receive {
    font-size: small;
    background-color: #389CFF;
    color: #000000;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid darkblue;
} */

/* #receive-whole-order-confirm,
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
} */

/* #po-req {
    width: 65%;
 
    background-color: hsl(0, 0%, 100%);
    color: hsl(224, 10%, 23%);
    margin-top: 10em;
    padding: 50px;
    margin-left: 25%;
    border: 5px solid hsl(224, 10%, 23%);
} */

/* #whole-order-confirm {
    box-shadow: 0px 0px 10px 4px rgba(41, 40, 41, 1), inset 0px 0px 10px 0px rgba(5, 148, 5, 1);
} */

/* #whole-order-deny-confirm {
    box-shadow: 0px 0px 10px 4px rgba(41, 40, 41, 1), inset 0px 0px 10px 0px rgba(135, 55, 5, 1);
} */

#action-jackson {
    width: 50%;
    height: 50%;
    background-color: hsl(0, 0%, 100%);
    color: hsl(224, 10%, 23%);
    padding: 20px;
    border: 3px solid hsl(224, 10%, 23%);
    box-shadow: 0px 0px 80px 20px rgba(41, 40, 41, 1);
}

#action-jackson h3 {
    /* background-color: hsl(224, 20%, 94%); */
    padding: 15px;
    width: 100%;
    color: hsl(224, 10%, 10%);
    font-weight: 900;
    border: 1px solid hsl(224, 6%, 77%);
    border-radius: 7px;
    margin-bottom: 10px;
}



/* CSS for the off-canvas element */
/* .off-canvas {
    position: fixed;
    bottom: -200px;
    
    left: 25%;
    right: 25%;
    width: 50%;
    height: 200px;
    background-color: #f0f0f0;
    transition: transform 0.3s ease-in-out;
    z-index: 999;
} */

/* .off-canvas.open {
    transform: translate(0, -200px);
   
} */

/* CSS for the close button */
/* .seeTotals,
.close-button {
    display: inline;
    z-index: 3;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
} */

/* .close-button {
    right: 2%;
    top: 15%;
} */

/* .div6.open {
    transition: transform 0.5s ease;
    margin-left: 0 !important;
} */

/* .seeTotals {
    width: 96px;
   
} */

/* #showButton {
    display: none;
} */



/* .submit-approve {
    border: darkgreen 1px solid;
    background-color: lightgreen;
    border-radius: 5px;
   
    cursor: pointer;
} */

/* .close-btn {
    border: none;
    background: none;
    position: absolute;
    right: 0.25rem;
    top: 0.5rem;
   

} */

/* .receive-btn {
    cursor: pointer;
} */

/* .goAway {
    display: none;
} */

/* .buttons-in-approval-popover-holder {
    display: flex;
    justify-content: space-evenly;
} */

/* .confirm-approval-button,
.approve-order-button,
.gen-po-button {
   
    font-size: medium;
    font-family: monospace;
    border-radius: 5px;
} */

/* .confirm-approval-button {
    margin-top: 10px;
    border-color: #009879;
} */

/* .huge-mistake-button {
    font-size: medium;
    font-family: monospace;
    border-radius: 5px;
    margin-top: 10px;
    border-color: #ff0000;
} */

/* .approve {
    background-color: #00800050;
    color: #000000;
    padding: 1px;
    border: 1px solid darkgreen;
    border-radius: 3px;
} */

/* .deny {
    background-color: #ff000050;
    color: #000000;
    padding: 1px;
    border: 1px solid darkred;
    border-radius: 3px;
} */

/* .tiny-text {
    font-size: small;
 
    background-color: #00000090;
    color: #FFFFFF;
    padding: 5px;
    border-radius: 5px;
} */

/* .receive {
        font-size: small;
        background-color: #6B6B6B;
        color: #FFFFFF;
        padding: 5px;
        border-radius: 5px;
    } */

/* .active-request {
    background-color: #008000 !important;
    color: #FFFFFF;
    font-weight: bold;
} */

/* #not-off-canvas {
    margin: auto;
    position: fixed;
    width: 40%;
    height: 20%;
    overflow: hidden;
    color: #000000;
} */

/* #pobutton {
    background-color: hotpink;
    height: 100px;
    width: 100px;

} */

/* button:disabled {
    color: grey;
    cursor: not-allowed;
} */

/* button:disabled:hover {
    color: grey;
    cursor: not-allowed;
} */

/* #prodImgPopover {
    display: none;
    position: absolute;
    border: 1px solid #ccc;
    padding: 10px;
    background-color: #fff;
    z-index: 999;
} */

[data-currentfy=false] td:first-of-type::before {
    content: '\2795';
}


[data-currentfy=false] td {
    color: grey !important;
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

/* .alert-text {
    text-align: center;
} */
</style>