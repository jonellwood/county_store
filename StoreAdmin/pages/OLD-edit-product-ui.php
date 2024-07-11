<?php

require_once 'config.php';
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());

// include('DBConn.php');
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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Edit products in the store database" />
    <link rel="icon" href="./favicons/favicon.ico">
    <link rel="stylesheet" href="prod-admin-style.css">
    <title>Edit Product</title>

    <script>
        function formatColorValueForUrl(str) {
            var noSpaces = str.replace(/[\s/]/g, '');
            var lowercaseString = noSpaces.toLowerCase();
            return lowercaseString;
        }

        function loadProducts() {
            fetch('load-all-products.php')
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    var html = ""
                    html += `
                    <table class="styled-table">
                    <thead>
                        <tr> 
                            <th>DataBase ID</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Vendor</th>
                            <th>Is Active</th>
                            <th>Is Communications</th>
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
                            <td>${data[i].price}</td>
                            <td>${data[i].vendor_id}</td>
                            <td>${data[i].isactive}</td>
                            <td>${data[i].isComm}</td>
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
            // alert('Editing product ' + id)
            fetch('load-single-product.php?id=' + id)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    var html = ""
                    html += `
                    <table class='styled-table'>
                    <h2>Existing Values for <mark>${data[0].product[0].name} - ${data[0].product[0].code}</mark> </h2>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Desc</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Vendor</th>
                            <th>Price Size Mod</th>
                            <th>Product Type</th>
                            <th>Is Active</th>
                            <th>Is Communications</th>
                            <th>Is Featured</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${data[0].product[0].code}</td>
                            <td>${data[0].product[0].description}</td>
                            <td>${data[0].product[0].name}</td>
                            <td>${data[0].product[0].price}</td>
                            <td>${data[0].product[0].vendor}</td>
                            <td>${data[0].product[0].price_size_mod}</td>
                            <td>${data[0].product[0].producttype}</td>
                            <td data-int='${data[0].product[0].isactive}'></td>
                            <td data-int='${data[0].product[0].isComm}'></td>
                            <td data-int='${data[0].product[0].featured}'></td>
                        </tr>
                    </tbody>
                    </table>
                    
                  
                    </div>
                    `
                    document.getElementById('edit-table').innerHTML = html;

                    var fhtml = '';
                    fhtml += `
                    <form action="edit-product-db.php" method="POST">
                    <input type="hidden" name="product_id" value=${data[0].product[0].product_id} />
                    <input type="hidden" name="code" value=${data[0].product[0].code} />

                    <div>
                    <fieldset id='product-options'>
                    <h2>Select new values for options you wish to update <p>If there is no change, leave the existing value in place </p></h2></br>
                    <div class='options-holder'>
                    <div>
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" value=${data[0].product[0].price} />
                    </div>
                    <div>
                    <label for="producttype">Product Type</label>
                    <select id="producttypeSelect" name="producttypeSelect">
                        <option value="1">Pants</option>
                        <option value="2">Shirts</option>
                        <option value="3">Hats</option>
                        <option value="4">Outerwear</option>
                        <option value="5">Sweatshirts</option>
                        <option value="7">Boots</option>
                        <option value="8">Accessories</option>
                    </select>
                    </div>
                    <div>
                    <label for="vendor">Vendor</label>
                    <select id="vendorSelect" name="vendorSelect">
                        <option value="1">Low Country Native</option>
                        <option value="2">The Bootjack</option>
                        <option value="3">Safety Products Inc</option>
                        <option value="4">Reids Uniforms Inc</option>
                    </select>
                    </div>
                    <div>
                    <label for "price_size_mod">Price Size Mod</label>
                    <select id="price_size_mod" name="priceModSelect">`
                    for (var i = 0; i < data[7].all_mods.length; i++) {
                        fhtml += `
                                <option value=${data[7].all_mods[i].id}>${data[7].all_mods[i].price_mod}</option>
                            `
                    }

                    fhtml += `</select>
                    </div>
                    <div>
                        <label for="isActiveSelect">Is Active</label>
                        <select id="isActiveSelect" name="isActive">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div>
                        <label for="isCommSelect">Is Comm</label>
                        <select id="isCommSelect" name="isComm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div>
                        <label for="isFeaturedSelect">Is Featured</label>
                        <select id="isFeaturedSelect" name="isFeatured">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div> 
                        <label for="gender_filter_select">Gender Filters</label>
                        <select id="gender_filter_select" name="gender_filter">
                            `
                    for (var i = 0; i < data[6].all_filters[0].gender_filters.length; i++) {
                        var filters = data[6].all_filters[0].gender_filters
                        fhtml += `
                                    <option value=${filters[i].id}>${filters[i].filter}</option>
                                `
                    }
                    fhtml += `
                        <select/>
                    </div>
                    <div>
                        <label for="type_filter_select">Product Sub Type Filters</label>
                        <select id="type_filter_select" name="type_filter">
                            `
                    for (var i = 0; i < data[6].all_filters[1].type_filters.length; i++) {
                        var filters = data[6].all_filters[1].type_filters
                        fhtml += `
                                    <option value=${filters[i].id}>${filters[i].filter}</option>
                                `
                    }

                    fhtml += `
                        </select>
                    </div>
                    <div>
                        <label for="size_filter_select">Size Category Filters</label>
                        <select id="size_filter_select" name="size_filter">
                    `
                    for (var i = 0; i < data[6].all_filters[2].size_filters.length; i++) {
                        var filters = data[6].all_filters[2].size_filters
                        fhtml += `
                                    <option value=${filters[i].id}>${filters[i].filter}</option>
                        `
                    }

                    fhtml += `

                        </select>
                    </div>
                    <div>
                        <label for="sleeve_filter_select">Sleeve Filters</label>
                        <select id="sleeve_filter_select" name="sleeve_filter">
                           `
                    for (var i = 0; i < data[6].all_filters[3].sleeve_filters.length; i++) {
                        var filters = data[6].all_filters[3].sleeve_filters
                        fhtml += `
                                    <option value=${filters[i].id}>${filters[i].filter}</option>
                        `
                    }
                    fhtml += `</select>
                </div>
                  </div>
                    <br>

                    <label for="description">Description</label>
                    <input class="description-entry" type="text" id="description" name="description" value="${data[0].product[0].description}" maxlength="255">
                    
                    
                    <details>
                    <summary>Legend</summary>
                    <div class="legend">
                            <p><b><i>Price: </i></b>This is the base price of the item. It does <b><mark>NOT</mark></b> include <i><u>any</u></i> taxes or fees.</p>

                            <p><b><i>Product Type: </i></b> This is the main category for the product. There can be only one.</p> 

                            <p><b><i>Vendor: </i></b> This is the vendor for the product. There can be only one.</p> 

                            <p><b><i>Price Size Mod: </i></b> This determines the price increases as sizes increase. If you are not sure <mark>DO NOT EDIT THIS</mark>. </p> 

                            <p><b><i>IsActive: </i>No</b> will remove it the product from queries and make unavailable for purchase. <b>Yes</b> is the opposite.</p>

                            <p><b><i>IsComm: </i> Yes</b> will make this product available for the Communications Department. <b>No</b> does the opposite</p>
                            
                            <p><b><i>IsFeatured: </i></b> This is not currently used - but a yes would make it show up in featured products in the front page of the store. Currently it does not matter what the value is.</p>
                        
                            <p><b><i>Gender Filters: </i></b> Assign the product to a category to assist in search and filter function. Many of which are disabled at the present time but will be coming back. You can only have one category right now... but that is dumb and I am going to change that soon I think....<mark>Look for the N/A option if the product is not gender specific</mark> </p>
                        
                            <p><b><i>Product Sub Type Filter: </i></b>Assign the product to a category to assist in search and filter function. Many of which are disabled at the present time but will be coming back. You can only have one category right now... but that is dumb and I am going to change that soon I think.... </p>
                        
                            <p><b><i>Size Category Filter: </i></b>Assign the product to a category to assist in search and filter function. Many of which are disabled at the present time but will be coming back. You can only have one category right now... but that is dumb and I am going to change that soon I think.... </p>
                        
                            <p><b><i>Sleeve Type Filter: </i></b>Assign the product to a category to assist in search and filter function. Many of which are disabled at the present time but will be coming back. You can only have one category right now... but that is dumb and I am going to change that soon I think.... </p>
                            </div>
                        </details>
                    </div>
                    
                    <div id='color-pick-form'></div>
                    <div id='size-pick-form'></div>
                    <hr/>
                    </fieldset>
                    <br>`
                    var color_fhtml = "";
                    color_fhtml += `<fieldset id='color_options'>
                    <span><b>Color Options</b> - Currently assigned values are already checked. Check or uncheck to assign or remove color options for this product.</span>
                    <ul class='color-list'>`
                    for (var j = 0; j < data[4].all_colors.length; j++) {
                        color_fhtml += `<li style="background-image: linear-gradient(to right,${data[4].all_colors[j].p_hex},${data[4].all_colors[j].s_hex === 'NULL' ? '#FFFFFF' : data[4].all_colors[j].s_hex} )"><input type="checkbox" id=${data[4].all_colors[j].color_id} name="colorCheckbox[]" value=${data[4].all_colors[j].color_id}><label for=${data[4].all_colors[j].color_id}>${data[4].all_colors[j].color}</label> 
                        </li>`
                    }
                    color_fhtml += `</ul>
                    </fieldset>
                    <br>`

                    var size_fhtml = "";
                    size_fhtml += `<fieldset id='size_options'>
                    <span><b>Size Options</b> - Currently assigned values are already checked. Check or uncheck to assign or remove size options for this product</span>
                    <ul class='size-list'>`
                    for (var k = 0; k < data[5].all_sizes.length; k++) {
                        size_fhtml +=
                            `<li>
                                        <input type="checkbox" id=${data[5].all_sizes[k].size} name="sizeCheckbox[]" value=${data[5].all_sizes[k].size_id}> 
                                        
                                        <label for=${data[5].all_sizes[k].size}>${data[5].all_sizes[k].size}</label></li>`
                    }
                    size_fhtml += `</ul>
                    </fieldset>
                    <button type="submit">Make it so</button>
                    </form>
                    `
                    document.getElementById('edit-form').innerHTML = fhtml;
                    document.getElementById('color-pick-form').innerHTML = color_fhtml;
                    document.getElementById('size-pick-form').innerHTML = size_fhtml;
                    setCurrentProducttype(`${data[0].product[0].producttype_id}`);
                    setCurrentPrice(`${data[0].product[0].price}`);
                    setCurrentVendor(`${data[0].product[0].vendor_id}`);
                    setCurrentActiveState(`${data[0].product[0].isactive}`)
                    setCurrentFilters(data[3].current_filters);
                    setCurrentColors(data[1].current_colors);
                    setCurrentSizes(data[2].current_sizes);
                    setCurrentPriceMod(`${data[0].product[0].price_size_mod}`);
                })

        }
    </script>

</head>

<body onload="loadProducts()">
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
                <!-- <button onclick="setValues()">Set Vals</button> -->
                <div id="edit-table"></div>
                <div id=edit-form></div>
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

    #edit-form {
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
</style>




<!-- <input type="checkbox" name="colorCheckbox" value="color1"> Color 1
<input type="checkbox" name="colorCheckbox" value="color2"> Color 2 -->


<script>
    // var currentVendorValue = "${data[0].product[0].vendor}";
    function setCurrentProducttype(id) {
        console.log('Setting current product type too: ' + id);
        var selectedElement = document.getElementById('producttypeSelect');
        for (var i = 0; i < selectedElement.options.length; i++) {
            var option = selectedElement.options[i];
            if (option.value === id) {
                option.selected = true;
                break;
            }
        }
    }

    function setCurrentPrice(price) {
        console.log("Setting current price to: " + price);
        var selectedElement = document.getElementById('price');
        selectedElement.value = price;
    }

    function setCurrentVendor(id) {
        console.log('Setting current vendor too: ' + id);
        var selectElement = document.getElementById('vendorSelect');
        for (var i = 0; i < selectElement.options.length; i++) {
            var option = selectElement.options[i];
            if (option.value == id) {
                option.selected = true;
                break; // Stop the loop when a math is found
            }
        }
    }

    function setCurrentActiveState(id) {
        console.log('Setting current vendor to : ' + id);
        var selectedElement = document.getElementById('isActiveSelect');
        for (var i = 0; i < selectedElement.options.length; i++) {
            var option = selectedElement.options[i];
            if (option.value == id) {
                option.selected = true;
                break;
            }
        }
    }

    function setCurrentPriceMod(mod) {
        console.log('Setting current price mod to:' + mod);
        var modElement = document.getElementById('price_size_mod');
        // console.log(modElement.options.length);
        for (var i = 0; i < modElement.options.length; i++) {
            var option = modElement.options[i];
            if (option.value === mod) {
                option.selected = true;
                break;
            }
        }
    }

    function setCurrentFilters(filters) {
        if (filters == null) {
            console.log('Filters is null - bailing out 🪂');
            return;
        } else {
            // console.log(filters[0].hasOwnProperty('gender_filter'));
            for (let filter of filters) {
                for (let key in filter) {
                    if (filter.hasOwnProperty(key)) {
                        // console.log('True dat');
                        let value = filter[key];
                        // console.log('value is :', value);
                        let selectElement = document.getElementById(key + '_select');
                        // console.log(key + '_select');
                        // console.log(selectElement);
                        if (selectElement) {
                            let options = selectElement.options;
                            // console.log(options.length);
                            for (let i = 0; i < options.length; i++) {
                                if (options[i].value == value) {
                                    options[i].selected = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function setCurrentColors(colors) {
        console.log('Setting Current colors');
        if (!Array.isArray(colors)) {
            console.log("Colors is not an array - deleting all data from your computer 🖥️ ");
            return;
        }
        var currentColors = colors.map(color => color.color_id)
        var checkboxes = document.getElementsByName('colorCheckbox[]');

        for (var i = 0; i < checkboxes.length; i++) {
            var checkbox = checkboxes[i];
            var checkboxParent = checkbox.parentElement
            // console.log(checkboxParent);
            var checkboxValue = parseInt(checkbox.value);
            if (currentColors.includes(checkboxValue)) {
                checkbox.checked = true;
                checkboxParent.classList.add('checked-li');
            }
        }
    };

    function setCurrentSizes(sizes) {
        console.log("Setting current sizes");
        if (!Array.isArray(sizes)) {
            console.log("Sizes is not an array - getting out of here: 🏎 ");
            return;
        }
        var currentSizes = sizes.map(size => size.size_id)
        var checkboxes = document.getElementsByName('sizeCheckbox[]')
        for (var i = 0; i < checkboxes.length; i++) {
            var checkbox = checkboxes[i];
            var checkboxValue = parseInt(checkbox.value);
            if (currentSizes.includes(checkboxValue)) {
                checkbox.checked = true;
            }
        }

    };

    document.getElementById('edit-form').addEventListener('submit', function(event) {
        let checkboxes = document.querySelectorAll('input[name="colorCheckbox[]"]');
        let sizechecks = document.querySelectorAll('input[name="sizeCheckbox[]"]');

        let checked = false;

        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                checked = true;
            }
        });
        if (!checked) {
            event.preventDefault();
            alert("Please select at least one color.");
        }

        let schecked = false;

        sizechecks.forEach(function(scheck) {
            if (scheck.checked) {
                schecked = true;
            }
        });
        if (!schecked) {
            event.preventDefault();
            alert("Please select at least one size")
        }
    });
</script>