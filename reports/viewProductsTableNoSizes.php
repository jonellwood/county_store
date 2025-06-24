<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Products</title>
    <script>
        function addListener() {
            var tds = document.querySelectorAll("td");
            //console.log(tds)
            for (var i = 0; i < tds.length; i++) {
                if (tds[i].getAttribute('value') == 0) {
                    tds[i].classList.add('white-text');
                }
            }
        }


        async function getProducts() {
            await fetch('getProductsTable.php')
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    html = '<table class="styled-table">';
                    html += '<thead><tr>';
                    html +=
                        '<th>Product Code</th><th>Name</th><th>Starting Price</th><th>+</th><th>Logo Fee</th><th>+</th><th>Tax</th><th> = </th><th>Total Starting Price</th><th>Stitch Charge</th><th>Price w/ Stitch Charge</th>';
                    // html += '<th class="start-sizes">2XL Price</th>'
                    // html += '<th>3XL Price</th>'
                    // html += '<th>4XL Price</th>'
                    // html += '<th>5XL Price</th>'
                    // html += '<th>6XL Price</th>'
                    // html += '<th>LT Price</th>'
                    // html += '<th>XLT Price</th>'
                    // html += '<th>XXLT Price</th>'
                    // html += '<th>XXXLT Price</th>'
                    // html += '<th>XXXXLT Price</th>'
                    html += '</tr></thead>';
                    html += '<tbody>';
                    for (var i = 0; i < data.length; i++) {
                        var prodPrice = parseFloat(data[i].price);
                        var logoFee = parseFloat(5);
                        var stitchFee = parseFloat(5);
                        var subTotal = prodPrice + logoFee
                        var subTotalStitch = prodPrice + logoFee + stitchFee
                        var tax = (subTotal * 0.09);
                        var taxStitch = (subTotalStitch * 0.09)
                        var total = subTotal + tax;
                        var stitchTotal = subTotalStitch + taxStitch;

                        var xxlinc = parseFloat(data[i].xxl_inc);
                        var xxlTotal = xxlinc + prodPrice;

                        var xxxlinc = parseFloat(data[i].xxxl_inc);
                        var xxxlTotal = xxxlinc + prodPrice;

                        var xxxxlinc = parseFloat(data[i].xxxxl_inc);
                        var xxxxlTotal = xxxxlinc + prodPrice;

                        var xxxxxlinc = parseFloat(data[i].xxxxxl_inc);
                        var xxxxxlTotal = xxxxxlinc + prodPrice;

                        var xxxxxxlinc = parseFloat(data[i].xxxxxxl_inc);
                        var xxxxxxlTotal = xxxxxxlinc + prodPrice;

                        var ltinc = parseFloat(data[i].lt_inc);
                        var ltTotal = ltinc + prodPrice;

                        var xltinc = parseFloat(data[i].xlt_inc);
                        var xltTotal = xltinc + prodPrice;

                        var xxltinc = parseFloat(data[i].xxlt_inc);
                        var xxltTotal = xxltinc + prodPrice;

                        var xxxltinc = parseFloat(data[i].xxxlt_inc);
                        var xxxltTotal = xxxltinc + prodPrice;

                        var xxxxltinc = parseInt(data[i].xxxxlt_inc);
                        var xxxxltTotal = xxxxltinc + prodPrice;
                        html += '<tr>';
                        html += '<td>' + data[i].code + '</td>';
                        html += '<td>' + data[i].name + '</td>';
                        html += '<td value="(' + prodPrice + ')" id="prodPrice">$' + prodPrice.toFixed(2) + '</td>';
                        html += '<td>+</td>';
                        html += '<td>$' + logoFee.toFixed(2) + '</td>';
                        html += '<td>+</td>';
                        html += '<td>$' + tax.toFixed(2) + '</td>';
                        html += '<td> = </td>';
                        html += '<td class="endPrice">$' + total.toFixed(2) + '</td>';
                        html += '<td>$' + logoFee.toFixed(2) + '</td>';
                        html += '<td>$' + stitchTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxlinc +
                        //     '" onload="compareAndApplyClass(this.value)" class="start-sizes">$' +
                        //     xxlTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxlinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxlTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxxlinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxxlTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxxxlinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxxxlTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxxxxlinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxxxxlTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + ltinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     ltTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xltinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xltTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxltinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxltTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxltinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxltTotal.toFixed(2) + '</td>';
                        // html += '<td value="' + xxxxltinc + '" onload="compareAndApplyClass(this.value)">$' +
                        //     xxxxltTotal.toFixed(2) + '</td>';
                        html += '</tr>';
                    }
                    html += '</tbody>';
                    html += '<table>';
                });

            document.getElementById('table-data').innerHTML = html;
            addListener();
        }
    </script>

</head>
<?php include "Header.php"; ?>

<body onload="getProducts()">
    <ul class="note">
        <li>
            NOTE: "Stitch Charge" is applied when Department Name is on the left sleeve or when the word
            "Berkeley" is on the
            back of a hat
        </li>
        <li>Tax is calculated at 9% of Product Price + Logo Fee + Stitch Fee (if applicable)</li>
        <!-- <li>Pricing for sizes 2Xl and up are the starting price only and do not refelct any logo or sitching fees</li> -->

    </ul>
    <div id="table-data"></div>
</body>

</html>

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
        text-align: center;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #74E193;

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

    .endPrice {
        border-right: 2px solid darkgrey;
    }

    .secondary-head {
        background-color: lightgrey !important;
        color: black !important;
    }

    .start-sizes {
        border-left: 2px solid darkgreen;
    }

    .white-text {
        /* color: #f3f3f3; */
        visibility: hidden;
    }

    .note {
        /* text-align: center; */
        font-size: medium;
        margin-left: 5%;
        margin-right: 30%;
    }

    ul {
        list-style: none;
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