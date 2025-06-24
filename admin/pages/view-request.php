<?php if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include('DBConn.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="new-jack-city.css" />
    <title>Document</title>
    <script>
        function extractDateFromDB(inputString) {
            const parts = inputString.split('T');
            return parts[0];
        }

        function extractDate(inputString) {
            const parts = inputString.split(' ');
            return parts[0];
        }
        // Ideally in the selects the current values of the order would be selected by default to prevent accidental overwrite of values not meant to be udpated. Getting the current values to compare to each is a bit tricker than I thought. Might have to do it after everthing has rendered?
        // FOR NOW this works for the single element in the function. Will replicate for other select for size - but should be able to combine down the road - but if it aint broke...
        function setDefaultColorOption() {
            var select = document.getElementById("colorSelect");
            var options = select.options;
            var currentColor = document.getElementById('currentColor').innerText
            // console.log(select);
            // console.log(options);
            // console.log(currentColor);

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
            // console.log(select);
            // console.log(options);
            // console.log(currentColor);

            for (var i = 0; i < options.length; i++) {
                if (options[i].innerText === currentSize) {
                    options[i].setAttribute("selected", "selected");
                } else {
                    options[i].removeAttribute("selected");
                }
            }
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
                    console.log(ohtml);
                    document.getElementById(data[0].order_id).innerHTML = ohtml;

                })
        }

        function getOptions(prod_id) {
            fetch('./getProductOptions.php?prod_id=' + prod_id)
                .then((response) => response.json())
                .then((data) => {
                    // console.log('product options');
                    // console.log(data.color[0]);
                    var formHTML = ""
                    formHTML += "<div class='dep-info-holder'>"
                    formHTML += "<table class='styled-table'>";
                    // formHTML += "<h3>Select Updated Size and Or Color</h3>"
                    formHTML += "<thead><tr><th colspan=2>Select Updated Size and Or Color</th></tr></thead>"
                    formHTML += "<form>";
                    formHTML += "<tbody>";
                    formHTML += "<tr><td>";
                    formHTML += "<label for='color'>Color</label></td>";
                    formHTML += "<td><select name='color' id='colorSelect'>"
                    for (var j = 0; j < data.color[0].length; j++) {
                        formHTML += "<option value='" + data.color[0][j].color_id + "'>" + data.color[0][j].color + "</option>";
                    }
                    formHTML += "</select>"
                    formHTML += "</td></tr>"
                    formHTML += "<tr><td>"
                    formHTML += "<label for='size'>Size</label></td>";
                    formHTML += "<td><select name='size' id='sizeSelect'>"
                    for (var j = 0; j < data.size[0].length; j++) {
                        formHTML += "<option value='" + data.size[0][j].size_id + "'>" + data.size[0][j].size + "</option>";
                    }
                    formHTML += "</select></td></tr>"
                    formHTML += "<tr><td>"
                    formHTML += "<label for='comment'>Comment</label> </td>";
                    formHTML += "<td><textarea name='comment' cols=50 rows=4></textarea> </td></tr>";
                    formHTML += "<tr><td></td><td><button type='submit'>Update</button></td></tr>";
                    // formHTML += "<label for='placement'>Dept Name Placement</label><br>";
                    // formHTML += "<input name='placement'><br>"
                    formHTML += "</form>";
                    formHTML += "</table>";
                    formHTML += "</div>";
                    document.getElementById('change-order-form').innerHTML = formHTML;
                    setDefaultColorOption();
                    setDefaultSizeOption();
                })
        }

        function getSingleOrderDetails(id) {
            console.log(id);
            setActiveRequest(id);
            fetch('./getWaitingOrderDetails.php?id=' + id)
                .then((response) => response.json())
                .then((order) => {
                    // console.log('team team team team team')
                    console.log(order);
                    var orderHTML = "";
                    orderHTML += "<div class='main-order-info-holder'>";
                    orderHTML += "<table class='styled-table'>";
                    orderHTML += "<thead>";
                    orderHTML += "<tr>"
                    orderHTML += "<th>Product Code</th>"
                    orderHTML += "<th>Product Name</th>"
                    orderHTML += "<th>Color</th>"
                    orderHTML += "<th>Size</th>"
                    orderHTML += "<th>Logo</th>"
                    orderHTML += "<th>Dept Placement</th>"
                    orderHTML += "<th>Vendor</th>"
                    orderHTML += "</tr>"
                    orderHTML += "</thead>";
                    orderHTML += "<tbody>";
                    orderHTML += "<tr>";
                    orderHTML += "<td>" + order[0].product_code + "</td>";
                    orderHTML += "<td>" + order[0].product_name + "</td>";
                    orderHTML += "<td id='currentColor'>" + order[0].color_id + "</td>";
                    orderHTML += "<td id='currentSize'>" + order[0].size_name + "</td>";
                    orderHTML += "<td><img src='../../" + order[0].logo + "'></td>";
                    orderHTML += "<td>" + order[0].dept_patch_place + "</td>";
                    orderHTML += "<td>" + order[0].vendor + "</td>";
                    orderHTML += "</tr>";
                    orderHTML += "</tbody>";
                    orderHTML += "</table>";
                    orderHTML += "</div>";
                    document.getElementById('details').innerHTML = orderHTML;
                    getOptions(order[0].product_id);
                })
        }

        async function getWaiting() {
            await fetch('./check-for-waiting-get.php')
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
                    html += "<th>Order For</th>";
                    html += "<th>Last Contacted</th>";
                    // html += "<th>Created</th>";
                    // html += "<th>Requested For</th>";
                    html += "</tr>"
                    html += "</thead>";
                    html += "<tbody>";
                    for (var i = 0; i < data.length; i++) {
                        getOrderDetails(data[i].order_id);
                        html += "<tr onclick='getSingleOrderDetails(" + data[i].order_id + ")' id='" + data[i].order_id + "'></tr>"
                    }
                    html += "</tbody>";
                    html += "</table>";
                    html += "</div>";
                    document.getElementById('main').innerHTML = html;
                })
        }
        getWaiting();
    </script>
</head>

<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2" id="main">
            <h1>Requests waiting on customer</h1>
        </div>
        <div class="div3" id="details">
            <h4 class='instructions'>&#128072; Select a request to view and edit details</h4>
        </div>
        <div class="div4">
            <?php include('hideTopNav.php'); ?>
        </div>
        <div class="div5">
            Div 5
        </div>
        <div class="div6" id="change-order-form">
            Div 6s
        </div>
        <div class="div7">
            Div 7
        </div>
    </div>
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
    </script>
</body>

</html>

<style>
    .instructions {
        margin-top: 25%;
    }

    img {
        background-color: #80808080;
    }
</style>