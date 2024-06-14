<?php

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT p.product_id, p.code, p.name, p.description, pt.productType, p.featured, p.isactive 
from products p
JOIN producttypes pt on p.producttype = pt.productType_id
order by producttype, code, isactive, featured;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script>
    function setText() {
        // console.log('Loading....');
        var tds = document.getElementsByClassName('torf');
        for (let i = 0; i < tds.length; i++) {
            // console.log('i is: ' + i);
            // console.log('lenght is: ' + tds.length);
            var val = tds[i].innerText;
            if (val == '0') {
                // console.log('False');
                tds[i].innerHTML = "False"
            } else {
                // console.log('True');
                tds[i].innerHTML = "True"
            }
        }
    }

    function toggleStatus(status) {
        if (status == 0) {
            return 1;
        } else if (status == 1) {
            return 0;
        } else {
            return "Please enter a valid input of either 0 or 1.";
        }
    }
    // need the damn product ID as well dumbass
    async function sendFeaturedStatus(newStatus, id) {
        var realNew = toggleStatus(newStatus);
        await fetch('./changeFeaturedStatus.php?status=' + realNew + '&id=' + id)
        if (realNew == 0) {
            alert('Active status set to False');
            location.reload();
        } else {
            alert('Active status set to True');
            location.reload();
        }
    }
    async function sendActiveStatus(newStatus, id) {
        var realNew = toggleStatus(newStatus);

        await fetch('./changeActiveStatus.php?status=' + realNew + '&id=' + id)
        if (realNew == 0) {
            alert('Active status set to False');
            location.reload();
        } else {
            alert('Active status set to True');
            location.reload();
        }

    }
    </script>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="icon" href="./favicons/favicon.ico">
    <title>Update Product Status in Store</title>
</head>

<body onload='setText()'>
    <?php include "nav.php" ?>
    <div class="header-holder">
        <h3>Change Product Status</h3>
        <!-- <a href='index.php'><button>Back</button></a> -->
    </div>
    <div class="body">

        <table class="styled-table">
            <thead>
                <tr>
                    <th colspan=7>TODO: Add some kind of filtering to easily locate the product users want to edit</th>
                </tr>
                <tr>
                    <th>Product Type</th>
                    <th>Code</th>
                    <th>Name</th>

                    <th colspan="2">Is Featured</th>
                    <!-- <th></th> -->
                    <th colspan="2">Is Active</th>
                    <!-- <th></th> -->
                </tr>
            </thead>
            <tbody>

                <?php
                while ($row = $res->fetch_assoc()) {
                    echo "<td>" . $row['productType'] . "</td>";
                    echo "<td>" . $row['code'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td class='torf'>" . $row['featured'] . "</td>";
                    echo "<td><button type='button' onclick='sendFeaturedStatus(" . $row['featured'] . ", " . $row['product_id'] . ")'>Change Featured Status</button></td>";
                    echo "<td class='torf'>" . $row['isactive'] . "</td>";
                    echo "<td><button type='button' onclick='sendActiveStatus(" . $row['isactive'] . ", " . $row['product_id'] . ")'>Change Active Status</button></td></tr>";
                }
            }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<style>
/* html {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    margin: 10px;
}

table {
    width: 95%;
    margin-left: auto;
    margin-right: auto;
    font-size: large;
}


td {
    vertical-align: middle;
}

thead tr th {
    color: #1e242b;
    padding: 15px;
    text-align: center;
    border-top: 3px solid #77216F;
    border-left: 1px dashed #77216F;
}


tbody tr td {
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 5px;
    padding-bottom: 5px;
    text-align: center;

    border-top: 1px solid #2C001E;
    border-left: 1px dashed #2C001E;
    font-weight: 400;

}

thead tr th:last-child {

    border-right: 1px dashed #77216F;
}

tbody tr:last-child {
    border-bottom: 1px solid #789b48;
}

tbody tr td:last-child {
    border-right: 1px dashed #2C001E;
} */

/* orange and white rows */
/* tbody tr:nth-child(even) {

    background-color: #E95420;
    color: #ffffff;
} */

/* black and white rows */
/* tbody tr:nth-child(odd) {
    background-color: #e4e2e0;
    color: #212121;
}

button {
    font-size: smaller;
} */
</style>