<?php

require_once 'config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

//     header("location: ../signin/signin.php");

//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Edit products in the store database" />
    <link rel="icon" href="./favicons/favicon.ico">
    <link rel="stylesheet" href="../../index23.css">
    <link rel="stylesheet" href="prod-admin-style.css">
    <title>Product Comparison</title>

    <script>
    function money_format(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }

    function fetch_data() {
        fetch('product-comparison-db.php')
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                var html = "";
                html += "<table class='styled-table'>";
                html += "<thead>"
                html += "<tr>"
                html += "<th>Product ID </th>"
                html += "<th>Product Code </th>"
                html += "<th>Product Price </th>"
                html += "<th>Vendor </th>"
                html += "<th></th>"
                html += "<th>Product ID </th>"
                html += "<th>Product Name </th>"
                html += "<th>Product Code </th>"
                html += "<th>Vendor </th>"
                html += "<th>Price Diff</th>"
                html += "</tr>"
                html += "</thead>"
                html += "<tbody>"
                for (var i = 0; i < data.length; i++) {
                    html += "<tr>"
                    html += "<td>" + data[i].p1_id + "</td>"
                    html += "<td>" + data[i].p1_code + "</td>"
                    html += "<td>" + money_format(data[i].p1_price) + "</td>"
                    html += "<td value=" + data[i].p1_vendor + ">" + data[i].p1_vendor + "</td>"
                    html += "<td> :: </td>"
                    html += "<td>" + data[i].p2_id + "</td>"
                    html += "<td>" + data[i].p2_code + "</td>"
                    html += "<td>" + money_format(data[i].p2_price) + "</td>"
                    html += "<td>" + data[i].p2_vendor + "</td>"
                    html += "<td>" + money_format(data[i].p1_price - data[i].p2_price) + "</td>"
                    html += "</tr>"
                }
                html += "</tbody>"
                document.getElementById("res-table").innerHTML = html
                setVendor();
                makeRed();
            })
    }
    fetch_data();

    function setVendor() {
        var tds = document.querySelectorAll('td');
        // console.log(tds);
        tds.forEach(function(td) {
            if (td.textContent.trim() === '4') {
                td.textContent = 'Reads';
            } else if (td.textContent.trim() === '1') {
                td.textContent = 'LCN';
            }
        });
    }

    function makeRed() {
        var mtds = document.querySelectorAll('td');
        mtds.forEach(function(td) {
            var content = td.textContent.trim();
            // console.log(content)
            if (content.startsWith('$-')) {
                td.classList.add('negative');
            }
        })
    }
    </script>
</head>

<body>
    <div class="container">
        <div id="res-table">
        </div>
    </div>
</body>

</html>
<style>
.container {
    background-color: #00000099;
    border-radius: 10px;
    width: auto;
    margin-left: 2%;
    margin-right: 2%;
    padding-left: 2%;
    padding-right: 2%;
    padding-top: 1%;
    padding-bottom: 5%;
}

td:value('4')::before {
    content: 'Reids';
}

td[textContent^="$-"] {
    color: red;
}

.negative {
    color: red;
}
</style>