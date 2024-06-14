<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders by Department</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css">
    <script>
        async function getDepartments() {
            await fetch('../../reports/getDepartments.php')
                .then((response) => response.json())
                .then((data) => {
                    dhtml = '<select id="department" name="department">';
                    for (var i = 0; i < data.length; i++) {
                        dhtml += '<option value="' + data[i].department + '">' + data[i].dep_name + '</option>';
                    }
                    dhtml += '</select>';
                })
            document.getElementById('dept-select').innerHTML = dhtml
        }
        getDepartments();
        async function getOrdersByDepartment(department, status) {
            console.log(status);
            await fetch('../../reports/getOrdersByDept.php?department=' + department + '&status=' + status)
                .then((response) => response.json())
                .then((data) => {
                    console.table(data);
                    html = '<table class="styled-table">';
                    html += '<thead><tr>'
                    html +=
                        '<th>Order ID</th><th>Order Details ID</th><th>Style #</th><th>Size</th><th>Color</th><th>Qty</th><th>Product Price</th><th>Tax</th><th>Line Item Total</th><th colspan="2">Logo</th><th>Date Created</th><th>Status</th>'
                    html += '</tr></thead>'
                    html += '<tbody>';
                    for (var i = 0; i < data.length; i++) {
                        var tax = (data[i].product_price * 0.09);
                        var prodPrice = parseFloat(data[i].product_price);
                        var total = tax + prodPrice;
                        html += '<tr>';
                        html += '<td>' + data[i].order_id + '</td>';
                        html += '<td>' + data[i].order_details_id + '</td>';
                        html += '<td>' + data[i].product_code + '</td>';
                        html += '<td>' + data[i].size_name + '</td>';
                        html += '<td>' + data[i].color_id + '</td>';
                        html += '<td>' + data[i].quantity + '</td>';
                        html += '<td>$' + prodPrice.toFixed(2) + '</td>';
                        html += '<td>$' + tax.toFixed(2) + '</td>';
                        html += '<td>$' + data[i].line_item_total.toFixed(2) + '</td>';
                        // html += '<td>$' + total.toFixed(2) + '</td>';
                        html += '<td><img src="../../' + data[i].logo + '" alt="logo"/></td>';
                        html += '<td>' + data[i].dept_patch_place + '</td>';
                        html += '<td>' + data[i].created + '</td>';
                        html += '<td>' + data[i].status + '</td>';
                        html += '</tr>';
                    }
                    html += "</tbody>";
                    html += "<table>";
                });
            document.getElementById("data-table").innerHTML = html;
        }
    </script>
</head>
<!-- <//?php include "Header.php"; ?> -->
<?php include "./topNav.php" ?>

<body>
    <div id="content">
        <p>NOTE: Requests submitted before 2/28/2023 the tax rate was calculated at 8%. All tax values here are
            calculatd at
            9%</p>
        <form id="orderForm" onsubmit="submitForm(event)" class="orderForm">
            <div class="drop-down-holder">

                <label for="department">Department Number:</label>
                <p id="dept-select"> </p>

                <!-- <br> -->
                <label for="status">Order Status:</label>
                <select id="status" name="status" required>
                    <option value="(status = 'Pending' OR status = 'Approved' OR status = 'Ordered' OR status = 'Received' OR status = 'Denied')">
                        All</option>
                    <option value='(status = "Pending")'>Pending</option>
                    <option value='(status = "Approved")'>Approved</option>
                    <option value='(status = "Denied")'>Denied</option>
                    <option value='(status = "Ordered")'>Ordered</option>
                    <option value='(status = "Received")'>Received</option>
                    <option value="(status = 'Approved' OR status = 'Ordered')">Approved OR Ordered</option>
                    <option value="(status = 'Ordered' OR status = 'Received')">Ordered OR Received</option>
                </select>
                <!-- <label for="orderby">Sort By:</label>
        <select id="orderby" name="orderby" required>
            <option value='order_id'>Order ID</option>
            <option value='created'>Date Created</option>
            <option value='submitted_by'>Submitted By</option>
            <option value='status'>Status</option>
            <option value='color_id'>Color</option>
            <option value='size_name'>Size</option>
            <option value='order_placed'>Date order Placed</option>
            <option value='product_code'>Product Code</option>
        </select> -->
                <br>
                <button type="submit">Submit</button>
            </div>
        </form>
        <hr>
    </div>
    <div id="data-table"></div>
    <div id="custom-menu">
        <ul>
            <li><span class="icon-check"></span> Approve</li>
            <li><span class="icon-close"></span> Deny</li>
            <li><span class="icon-wrench"></span> Edit</li>
            <li><span class="icon-trash"></span> Delete</li>
        </ul>
    </div>
</body>

</html>
<script>
    function submitForm(event) {
        event.preventDefault();
        var department = document.getElementById("department").value;
        var status = document.getElementById("status").value;
        getOrdersByDepartment(department, status);
    }
    const customMenu = document.getElementById('custom-menu');
    const content = document.getElementById('data-table');

    content.addEventListener('contextmenu', function(event) {
        event.preventDefault();

        // Position menu at the mouse - not sure if the px will work or not though..... 
        // Yep they do :)
        customMenu.style.left = event.pageX + 'px';
        customMenu.style.top = event.pageY + 'px';
        // Show menu
        customMenu.style.display = 'block';
    });

    document.addEventListener('click', function(event) {
        // click out to hide menu
        customMenu.style.display = 'none';
    });
</script>

<style>
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
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
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

    img {
        width: 50px !important;
    }

    .header-holder {
        display: flex;
        justify-content: space-around;
    }

    .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.9em;
        font-family: sans-serif;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
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

    .orderForm {
        display: inline-block;
    }

    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

    .tabcontent {
        animation: fadeEffect 1s;
        /* Fading effect takes 1 second */
    }

    .drop-down-holder {
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        gap: 10px;
    }

    label {
        margin-left: 15px;
    }

    select {
        margin-left: 5px;
    }

    .hidden {
        opacity: 0;
        transition: opacity 1s ease;
    }

    /* #content {
    opacity: 1;
} */

    #custom-menu {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        border: 1px solid #ccc;
        padding: 5px;
    }

    #custom-menu ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    #custom-menu li {
        cursor: pointer;
        padding: 5px;
    }

    /* Go from zero to full opacity */
    @keyframes fadeEffect {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>