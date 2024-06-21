<?php

require_once 'config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="prod-admin-style.css">
    <title>Edit Product Colors</title>

    <script>
        function formatColorValueForUrl(str) {
            var noSpaces = str.replace(/[\s/]/g, '');
            var lowercaseString = noSpaces.toLowerCase();
            return lowercaseString;
        }

        function loadProducts() {
            fetch('load-all-new-products.php')
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    var html = ""
                    html += `
                    <table class="styled-table">
                    <caption>This page is for editing color options for products. For Size and Price or adding new line items go <a href="/edit-new-product-line-ui.php">HERE</a></caption>
                    <thead>
                        <tr> 
                            <th>DataBase ID</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th> </th>
                        </tr>
                    </thead>
                    <tbody>
                    `
                    for (var i = 0; i < data.length; i++) {
                        html += `
                            <tr>
                            <td>${data[i].product_id}</td>
                            <td>${data[i].code}</td>
                            <td>${data[i].name}</td>
                            <td><button popovertarget='productEdit' onclick='editProduct(${data[i].product_id})'>Edit</button></td>
                            </tr>
                        `
                    }
                    html += `</tbody>

                    </table>
                    
                    `

                    document.getElementById('products-table').innerHTML = html;
                })
        }

        function editProduct(id) {
            fetch('load-new-single-product-colors.php?id=' + id)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                })
        }
        loadProducts();
    </script>
</head>

<body>
    <div class="parent">
        <div class="div1">
            <?php include "hideNav.php" ?>
        </div>
        <div class="div2">
            <div id='products-table'></div>
            <div id="productEdit" popover=manual>
                <button class="close-btn" popovertarget="productEdit" popovertargetaction="hide">
                    <span aria-hidden=”true”>❌</span>
                    <span class="sr-only">Close</span>
                </button>

                <div id="edit-table"></div>
                <div id=edit-form></div>
                <div id=add-form></div>

            </div>
        </div>
        <div class="div4">
            <?php include('hideTopNav.php'); ?>
        </div>
    </div>
</body>

</html>
<style>
    body {
        position: relative;
        max-width: 90vw;
        /* padding-right: calc(var(--bs-gutter-x) * 0.5); */
        /* padding-left: calc(var(--bs-gutter-x) * 0.5); */
        margin-right: 20px;
        margin-left: 20px;
        /* overflow: hidden; */
    }

    .close-btn {
        position: fixed;
        top: 0;
        margin-top: 20px;
        left: 0;
        margin-left: 60px;
        border: 3px solid tomato;
        width: 100px;
        height: 50px;
        border-radius: 5px;
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
    }

    .close-btn:hover {
        transform: scale(.95);
        border: 2px solid green;
        box-shadow: 0px 0px, 0px 0px, 0px 0px, 1px 1px;
    }

    #products-table {
        margin-left: 15%;
        margin-right: auto;
    }

    #productEdit {
        margin: auto;
        position: fixed;
        width: 90%;
        height: 90%;
        border: 5px solid tomato;
        /* overflow: hidden; */
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
    }

    #productEdit::backdrop {
        backdrop-filter: blur(5px);
    }

    #edit-table {
        display: grid;
        /* grid-template-rows: 1fr 1fr; */
    }

    .styled-table tr td {
        text-align: center;
    }

    .description-entry {
        width: 90%;
    }

    /* .yes:before {
        content: "Yes";
    }

    .no:before {
        content: "No";
    } */

    td[data-int="0"]:before {
        content: "No";
    }

    td[data-int="1"]:before {
        content: "Yes";
    }

    .cs-current {
        display: grid;
        grid-template-columns: 1fr auto;
        padding: 10px;
        justify-content: center;
    }

    .cs-current span {
        width: 100%;
        text-align: center;
        font-weight: bold;
    }

    #colors-table,
    #sizes-table {
        text-align: center;
    }

    .color-list {
        /* max-width: 70%; */
        list-style: none;
        gap: 10px;
        display: flex;
        flex-wrap: wrap;
        /* justify-content: flex-start; */
        justify-content: center;

    }

    .color-list li img {
        border: 1px solid black;
        /* box-shadow: 0px 0px 10px 3px rgba(128, 128, 128, 1); */
        width: 15px;
    }

    #color-pick-form {
        margin-top: 20px;
    }

    .size-list {
        /* max-width: 20%; */
        list-style: none;
        gap: 5px;
        display: flex;
        flex-wrap: wrap;
        /* justify-content: flex-start; */
        justify-content: center;
    }

    .color-list li,
    .size-list li {
        padding: 3px;
        border: 1px solid black;
        display: flex;
        align-content: center;
    }

    .color-list li label {
        align-content: center;
    }

    #edit-form,
    #add-form {
        border-top: 5px solid tomato;
        padding: 10px;
    }

    #color_options,
    #size_options {
        /* width: 100%; */
        margin: 10px;
        padding: 15px;
    }

    #product-options {

        margin: 10px;
        padding: 15px;
        /* display: flex; */
        /* gap: 10px; */
    }

    #product-options input {
        margin-right: 20px;
    }

    #product-options select {
        margin-right: 20px;
    }

    #product-options h2 p {
        font-size: small;
        color: darkslategray;
        font-weight: bold;
    }

    #product-options label {
        font-weight: bold;
    }

    input[type="checkbox"]~label {
        background-color: whitesmoke;
        padding: 5px;
    }

    input[type="checkbox"]:checked~label {
        background-color: limegreen !important;
        font-weight: bold;
        /* margin: 5px;
        padding: 5px; */
    }

    h2 {
        text-align: center;
        box-shadow: 1px 1px 5px 10px rgba(0, 0, 0, .04);
    }

    mark {
        font-family: monospace;
        background-color: lightgrey;
        font-size: larger;
        font-weight: bold;
        padding: 1px;
    }

    .legend {
        display: grid;
        /* flex-direction: row; */
        /* flex-wrap: wrap; */
        /* flex-basis: 100px; */
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        justify-content: center;
        align-content: center;
        margin-top: 1em;
        border: 1px solid rebeccapurple;
        padding: 10px;
    }

    .legend p {
        font-size: smaller;
        color: darkslategray;
        /* border: 1px solid hotpink; */
        margin: 0;
        padding: 5px;
    }

    .options-holder {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    details:hover {
        /* cursor: ns-resize; */
        cursor: cell;
    }

    .buttons-holder {
        display: flex;
        justify-content: space-evenly;
        margin-left: 10%;
        margin-right: 10%;
    }

    caption {
        font-size: small;

        a {
            font-size: small !important;
            background-color: #ffffff20;
            text-align: center;
            display: block !important;
        }
    }
</style>