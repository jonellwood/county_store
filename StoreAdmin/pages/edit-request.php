<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}


include('DBConn.php');
$order_id = $_GET['order_id'];
include "components/commonHead.php";
?>



<script src="./functions/helpers.js"></script>
<script src="functions/renderSingleRequest.js"></script>
<script src="functions/renderProductOptionsForEdit.js"></script>
<script src="components/createCancelOrderPopover.js"></script>
<script>
function cancelOrder(id) {
    fetch('./cancelOrder.php?id=' + id)
        .then((response) => response.json())
        .then((data) => {
            console.log('cancel reply')
            console.log(data[0].code);
            if (data[0].code == '200') {
                location.reload();
            }
        })
}

function getOrderDetails(id) {
    fetch('./getWaitingOrderDetails.php?id=' + id)
        .then((response) => response.json())
        .then((data) => {
            // console.log('Uno el order')
            // console.log(data);
            var ohtml = ""
            ohtml += "<tr>";
            ohtml += "<td>" + data[0].order_id + "</td>";
            ohtml += "<td>" + data[0].rf_first_name + " " + data[0].rf_last_name + "</td>";
            ohtml += "<td>" + extractDate(data[0].last_contact) + "</td>";
            ohtml += "</tr>";
            // console.log(ohtml);
            document.getElementById(data[0].order_id).innerHTML = ohtml;
        })
}

async function getOptions(prod_id, order_det_id) {
    await fetch('./getProductOptions.php?prod_id=' + prod_id)
        .then((response) => response.json())
        .then((data) => {
            // console.log('product options');
            // console.log(data);
            // console.log(data.color[0]);
            // console.log('order_det_id is', order_det_id)
            renderProductOptionsForEdit(data, order_det_id)
            // var formHTML = ""
            // formHTML += "<div class='dep-info-holder'>"
            // formHTML += "<h4 class='what-h4'> - </h4>";
            // // formHTML += "<table class='styled-table'>";
            // // formHTML += "<h3>Select Updated Size and Or Color</h3>"
            // // formHTML += "<thead><tr><th colspan=2>Select Updated Size and Or Color</th></tr></thead>"
            // formHTML += "<form action='updateOrder.php' method='post'>";
            // // formHTML += "<tbody>";
            // // formHTML += "<tr><td>";
            // formHTML += "<div class='styled-table top-row'>"
            // formHTML += "<label for='color'>Color</label>";
            // formHTML += "<select name='color' id='colorSelect'>"
            // for (var j = 0; j < data.color[0].length; j++) {
            //     formHTML += "<option value='" + data.color[0][j].color_id + "'>" + data.color[0][j].color +
            //         "</option>";
            // }
            // formHTML += "</select>"
            // // formHTML += "</td></tr>"
            // // formHTML += "<tr><td>"
            // formHTML += "<label for='size'>Size</label>";
            // formHTML += "<select name='size' id='sizeSelect'>"
            // for (var j = 0; j < data.size[0].length; j++) {
            //     formHTML += "<option value='" + data.size[0][j].size_id + "'>" + data.size[0][j].size +
            //         "</option>";
            // }
            // formHTML += "</select>";
            // // formHTML += "<tr><div>"
            // formHTML += "</div>";
            // formHTML += "<div class='styled-table top-row'>"
            // formHTML += "<label for='bill_to'>Bill To Account</label>";
            // formHTML +=
            //     "<input type='text' name='bill_to' rows='1' maxlength='5' maxlength='5' placeholder='" +
            //     bill_to + "'></input>";
            // formHTML += "</div>";
            // formHTML += "<div class='styled-table top-row'>"
            // formHTML += "<label for='comment'>Comment</label>";
            // formHTML += "<textarea name='comment' cols=50 rows=4 oninput='makeButtonActive()'></textarea>";
            // formHTML += "</div>";
            // formHTML += "<div class='styled-table top-row'>"
            // formHTML += "<p>A comment is required when submitting a change</p>"
            // // formHTML += "</div>";
            // formHTML += "<input type='hidden' name='order_id' value='" + order_det_id + "'>"
            // formHTML += "<input type='hidden' name='color_name' value='" + color_id + "'>"
            // formHTML += "<input type='hidden' name='size_name' value='" + size_name + "'>"
            // formHTML += "<input type='hidden' name='bill_to' value='" + bill_to + "'>"
            // formHTML += "<input type='hidden' id='currentColor' name='currentColor' value='" +
            //     currentColor + "'>"
            // formHTML += "<input type='hidden' id='currentSize'  name='currentSize' value='" + currentSize +
            //     "'>"
            // formHTML += "</div>";
            // formHTML += "<div class='styled-table bottom-row'>"
            // formHTML += "<button type='submit' id='update-button' disabled>Update</button>";
            // formHTML += "<button type='button' class='cancel-button' onclick='cancelOrder(" + order_det_id +
            //     ")' disabled id='cancel-button'>Cancel Order</button>";
            // formHTML += "</div>";
            // formHTML += "</form>";

            // // formHTML += "</table>";
            // formHTML += "</div>";
            // document.getElementById('details').innerHTML = html;

            //setDefaultColorOption();
            //setDefaultSizeOption();
        })

}

// function getSingleOrderDetails(id) {
//     console.log(id);
//     setActiveRequest(id);
//     fetch('./getSingleOrderDetails.php?id=' + id)
//         .then((response) => response.json())
//         .then((order) => {
//             // console.log('team team team team team')
//             console.log(order);
//             var orderHTML = "";
//             orderHTML += "<div class='main-order-info-holder'>";
//             orderHTML += "<table class='styled-table'>";
//             orderHTML += "<thead>";
//             orderHTML += "<tr>"
//             orderHTML += "<th>Product Code</th>"
//             orderHTML += "<th>Product Name</th>"
//             orderHTML += "<th>Color</th>"
//             orderHTML += "<th>Size</th>"
//             orderHTML += "<th>Logo</th>"
//             orderHTML += "<th>Dept Placement</th>"
//             orderHTML += "<th>Vendor</th>"
//             orderHTML += "</tr>"
//             orderHTML += "</thead>";
//             orderHTML += "<tbody>";
//             orderHTML += "<tr>";
//             orderHTML += "<td>" + order[0].product_code + "</td>";
//             orderHTML += "<td>" + order[0].product_name + "</td>";
//             orderHTML += "<td id='currentColor'>" + order[0].color_id + "</td>";
//             orderHTML += "<td id='currentSize'>" + order[0].size_name + "</td>";
//             orderHTML += "<td><img src='../../" + order[0].logo + "'></td>";
//             orderHTML += "<td>" + order[0].dept_patch_place + "</td>";
//             orderHTML += "<td>" + order[0].vendor + "</td>";
//             orderHTML += "</tr>";
//             orderHTML += "</tbody>";
//             orderHTML += "</table>";
//             orderHTML += "</div>";
//             document.getElementById('details').innerHTML = orderHTML;
//             // getOptions(order[0].product_id, order[0].order_details_id, order[0].color_id, order[0].size_name);
//         })
// }

async function getOrder(id) {
    // await fetch('./check-for-waiting-get.php')
    await fetch('./getSingleOrderDetails.php?id=' + id)
        .then((response) => response.json())
        .then((data) => {

            console.log(data);
            renderSingleRequest('main', data)
            // var html = '';
            // html += "<div class='main-list-holder' id='main-list-holder'>";
            // html += "<span class='table-title'>Edit Employee Request</span>";
            // html += "<table class='styled-table'>";
            // html += "<thead>";
            // html += "<tr>";
            // html += "<th></th>";
            // html += "<th></th>";
            // // html += "<th>Last Contacted</th>";
            // // html += "<th>Created</th>";
            // // html += "<th>Requested For</th>";
            // html += "</tr>"
            // html += "</thead>";
            // html += "<tbody>";
            // // for (var i = 0; i < data.length; i++) {
            // //     getOrderDetails(data[i].order_id);
            // //     html += "<tr onclick='getSingleOrderDetails(" + data[i].order_id + ")' id='" + data[i].order_id + "'></tr>"
            // // }
            // html += "<tr><td>Order ID:</td><td>" + data[0].order_id + "</td>"
            // html += "<tr><td>Order for:</td><td>" + data[0].rf_first_name + " " + data[0].rf_last_name +
            //     "</td>"
            // html += "<tr><td>Quantity:</td><td>" + data[0].quantity + "</td>"
            // html += "<tr><td>Product Code:</td><td>" + data[0].product_code + "</td>"
            // html += "<tr><td>Product Name:</td><td>" + data[0].product_name + "</td>"
            // html += "<tr><td>Product Size:</td><td>" + data[0].size_name + "</td>"
            // html += "<tr><td>Product Color:</td><td>" + data[0].color_id + "</td>"
            // html += "<tr><td>Dept Name:</td><td>" + data[0].dep_name + "</td>"
            // html += "<tr><td>Dept Number:</td><td>" + data[0].department + "</td>"
            // html += "<tr><td>Bill To Dept Number:</td><td>" + data[0].bill_to_dept + "</td>"
            // html += "<tr><td>Logo:</td><td><img src='../../" + data[0].logo +
            //     "' alt='dept logo' class='logo-img'></td>"
            // html += "<tr><td>Dept Placement:</td><td>" + data[0].dept_patch_place + "</td>"
            // html += "</tbody>";
            // html += "</table>";
            // html += "</div>";
            // document.getElementById('main').innerHTML = html;
            getOptions(data[0].product_id, data[0].order_details_id, data[0].color_id, data[0].size_name,
                data[0].bill_to_dept, data[0].color_id, data[0].size_name);
            // setDefaultSizeOption();

        })
}
getOrder(<?php echo $order_id ?>);
</script>
</head>

<!-- <body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent">
        <div class="div1">

        </div>
        <div class="div2" id="main">
            <h1>Requests waiting on customer</h1>
        </div>
        <div class="div3" id="details">
            <h4 class='instructions'>&#128072; Select a request to view and edit details</h4>
        </div>
        <div class="div4">

        </div>
        <div class="div5">

        </div>
        <div class="div6" id="change-order-form">

        </div>
        <div class="div7">

        </div>
    </div> -->

<script>
function setActiveRequest(id) {
    console.log('setting active on ', id);
    var activeRows = document.querySelectorAll("tr.active-request");
    activeRows.forEach(function(row) {
        row.classList.remove("active-request");
    });
    var targetRow = document.querySelector(`tr[value="${id}"]`);
    if (targetRow) {
        targetRow.classList.add("active-request");
    }
}
// Ideally in the selects the current values of the order would be selected by default to prevent accidental overwrite of values not meant to be udpated. Getting the current values to compare to each is a bit tricker than I thought. Might have to do it after everthing has rendered?
// FOR NOW this works for the single element in the function. Will replicate for other select for size - but should be able to combine down the road - but if it aint broke...
function setDefaultColorOption() {
    var select = document.getElementById("colorSelect");
    var options = select.options;
    var currentColor = document.getElementById('currentColor').innerText

    for (var i = 0; i < options.length; i++) {
        if (options[i].innerText === currentColor) {
            options[i].setAttribute("selected", "selected");
        } else {
            options[i].removeAttribute("selected");
        }
    }
}

function setDefaultSizeOption() {
    var select = document.getElementById("sizeSelect");
    var options = select.options;
    var currentSize = document.getElementById('currentSize').innerText

    for (var i = 0; i < options.length; i++) {
        if (options[i].innerText === currentSize) {
            options[i].setAttribute("selected", "selected");
        } else {
            options[i].removeAttribute("selected");
        }
    }
};

function setDefaultQtyOption() {
    var input = document.getElementById("quantity");
    // var options = select.options;
    var currentQty = document.getElementById('currentQuantity').innerText

    input.value = currentQty
};

function setDefaultBillToOption() {
    var billTo = document.getElementById("currentBillTo").dataset.billto;
    // var options = select.options;
    var billToInput = document.getElementById('bill_to')

    billToInput.value = billTo;
};

function setDeptNamePlacementOption() {
    var select = document.getElementById("deptPlacementSelect");
    var options = select.options;
    var currentPlacement = document.getElementById('currentDeptPlacement').innerText

    for (var i = 0; i < options.length; i++) {
        if (options[i].innerText === currentPlacement) {
            options[i].setAttribute("selected", "selected");
        } else {
            options[i].removeAttribute("selected");
        }
    }
};

function setDefaultLogoOption() {
    var select = document.getElementById("logoSelect");
    var options = select.options;
    var currentLogo = document.getElementById('currentLogo').dataset.value;

    console.log(currentLogo);
    for (var i = 0; i < options.length; i++) {
        if (options[i].value === currentLogo) {
            options[i].setAttribute("selected", "selected");
        } else {
            options[i].removeAttribute("selected");
        }
    }
};

function makeButtonActive() {
    var cButton = document.getElementById('cancel-button');
    var sButton = document.getElementById('update-button');

    cButton.disabled = false
    sButton.disabled = false
}

function displayAlert() {
    var fyData = fiscalYear();
    var html = '';
    html +=
        `<div class="info-banner">
        Select the changes you wish to make and click 'Update' to save them</div>`
    document.getElementById('alert-banner').innerHTML = html
}
displayAlert();
</script>;

<?php include "./components/viewFoot.php" ?>
</body>
<div id="cancel-confirm" name="cancel-confirm" popover popover="manual" class="p-2">

</div>

</html>
<style>
.parent {
    grid-template-rows: 75px 25% 85% 10% !important;
    gap: 10px;
}

.div1 {
    min-height: 90dvh;
}

.div2 {
    grid-area: 2 / 2 / 2 / 5;
    background-color: transparent;
    border: none !important;
}

.div3 {
    grid-area: 3 / 2 / 3 / 5;
    border: none !important;

    form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;

    }

    .form-holder {
        display: grid;
        grid-template-columns: 1fr 1fr;
        justify-items: center;
    }
}

.div6 {
    display: none;
    border: none !important;
}

.div7 {
    display: none;
}

/* #main {
    position: relative !important;
    z-index: 3 !important;
} */
</style>



<!-- <style>
.instructions {
    margin-top: 25%;
}

img {
    background-color: #80808080;
}

.what-h4 {
    color: white;
}

.top-row {
    display: flex;
    justify-content: space-evenly;
}

.bottom-row {
    display: flex;
    justify-content: space-evenly;
    /* margin-left: 10%;
        margin-right: 10%; */
}

.cancel-button {
    background-color: lightpink;
    color: red;
    border: 1px solid darkred;
}

.styled-table tbody tr {
    height: 50px !important;
}

.logo-img {
    height: 100px !important;
    width: auto !important;
    background-color: transparent;
}
</style> -->