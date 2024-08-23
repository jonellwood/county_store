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
            renderProductOptionsForEdit(data, order_det_id)
        })

}


async function getOrder(id) {
    await fetch('./getSingleOrderDetails.php?id=' + id)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            renderSingleRequest('main', data)
            getOptions(data[0].product_id, data[0].order_details_id, data[0].color_id, data[0].size_name,
                data[0].bill_to_dept, data[0].color_id, data[0].size_name)
        })
}
getOrder(<?php echo $order_id ?>)
</script>
</head>

<script>
function setActiveRequest(id) {
    // console.log('setting active on ', id);
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
.image-logo-stack {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    max-height: 60dvh;

    .med-logo-img {
        margin-left: auto;
        margin-right: auto;
    }
}
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