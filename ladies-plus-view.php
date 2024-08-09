<?php

/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/12/2024
Purpose: View the items with type mens-shirts.
Includes:    slider.php, viewHead.php, cartSlideout.php, footer.php
*/
session_start();
if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// check if users isComm. If so redirect to comm only products
if (isset($_SESSION["isComm"]) && ($_SESSION["isComm"] === true)) {
    header("location: products-by-communications.php");
    exit;
}
// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;

?>
<?php include "./components/viewHead.php" ?>
<div class="container">
    <div class="btn-container" id="btn-container">
        <!-- <button class="btn js-toggle-filter" popovertarget="filters-popover">filter <code>products</code></button> -->

        <div id="filters-popover" class="filters-popover" popover>
            <button popovertarget="filters-popover" popovertargetaction="hide" class="btn-close ms-2 mb-1"
                role="button">
                <span aria-hidden="true"></span>
            </button>
            <div class="popover-description">
                <span>Uncheck items you don't want to see in results</span>
            </div>
            <div id="filters"></div>
            <button class="button" onclick="resetFilters()">Reset Filters</button>
        </div>
    </div>
</div>

<script>
let sizesToRemove = [];
let typesToRemove = [];
let sleevesToRemove = [];

function resetFilters() {
    location.reload();
}

function getFilterData() {
    fetch('fetchFilters.php')
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            // console.log(data[3].type_filters[0].filter);
            var sizes = data[1].size_filters;
            var types = data[3].type_filters;
            var sleeves = data[2].sleeve_filters;
            var html = '';
            html += `<div class='filters-holder'>`
            html += `<div class="filter-group" id="size-filter-group">`
            html += `<span>Size Filters:</span>`
            for (var i = 0; i < sizes.length; i++) {
                var prefix = 's'
                html += `<label for="${prefix + sizes[i].id}">${sizes[i].filter}`
                html +=
                    `<input type="checkbox" name="${sizes[i].filter}" value="${sizes[i].id}" id="${prefix + sizes[i].id}" checked onchange="sendToSizesToRemoveArray(this.value)"/>`
                html += `</label>`
            }
            html += `</div>`
            html += `<div class="filter-group" id="type-filter-group">`
            html += `<span>Type Filters:</span>`
            for (var j = 0; j < types.length; j++) {
                var prefix = 't'
                html += `<label for="${prefix + types[j].id}">${types[j].filter}`
                html +=
                    `<input type="checkbox" name="${types[j].filter}" value="${types[j].id}" id="${prefix + types[j].id}" checked onchange="sendToTypesToRemoveArray(this.value)"/>`
                html += `</label>`
            }
            html += `</div>`
            html += `<div class="filter-group" id="sleeve-filter-group">`
            html += `<span>Sleeve Filters:</span>`
            for (var k = 0; k < sleeves.length; k++) {
                var prefix = 'sl'
                html += `<label for="${prefix + sleeves[k].id}">${sleeves[k].filter}`
                html +=
                    `<input type="checkbox" name="${sleeves[k].filter}" value="${sleeves[k].id}" id="${prefix + sleeves[k].id}" checked onchange="sendToSleevesToRemoveArray(this.value)"/>`
                html += `</label>`
            }
            html += `</div>`
            html += `</div>`
            document.getElementById('filters').innerHTML = html;
        })
}


function getFilteredProducts(genderFilter) {
    fetch('fetchFilteredProductsBySize.php?gender=' + genderFilter)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            var html = '';

            for (var i = 0; i < data.length; i++) {
                html += renderProduct(data[i]);
            }
            document.getElementById('products-target').innerHTML = html;
        })
}
</script>
<script src="functions/renderProduct.js"></script>
<script src="functions/renderFiltersBtnAndPopover.js"></script>
<div id="filters-target" class="products-target"></div>
<div id="products-target" class="d-grid-4 gap-3"></div>

<script>
getFilteredProducts('2');
// renderFiltersBtnAndPopover()
</script>
<?php include "footer.php" ?>

<script>
function gotoPage(val) {
    console.log('Bubbbling');;
    // e.stopPropagation()
    // alert('You will be directed to the details page for this product');
    document.location.replace(val);
}

function sendToSizesToRemoveArray(val) {
    if (sizesToRemove.includes(val)) {
        sizesToRemove = sizesToRemove.filter((x) => x !== val);
        console.log(sizesToRemove);
    } else {
        sizesToRemove.push(val);
        console.log(sizesToRemove);
    }
    // find all elements with class of home-product-info and if they have data-sizes attribute equal to val add the class of hidden
    var elements = document.getElementsByClassName("home-product-info");
    console.log('Elements with sizes ')
    console.log(elements);
    // check if elements data-size attribute is in the array sizesToRemove and hide if it is. show if it isn't
    for (var i = 0; i < elements.length; i++) {
        if (sizesToRemove.includes(elements[i].getAttribute("data-size"))) {
            elements[i].parentElement.classList.add("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been hidden');
        } else {
            elements[i].parentElement.classList.remove("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been shown');
        }
    }
}


function sendToTypesToRemoveArray(val) {
    if (typesToRemove.includes(val)) {
        typesToRemove = typesToRemove.filter((x) => x !== val);
        console.log(typesToRemove);
    } else {
        typesToRemove.push(val);
        console.log(typesToRemove);
    }

    // find all elements with class of home-product-info and if they have data-types attribute equal to val add the class of hidden
    var elements = document.getElementsByClassName("home-product-info");
    console.log('Elements with types ')
    console.log(elements);
    // check if elements data-type attribute is in the array typesToRemove and hide if it is. show if it isn't
    for (var i = 0; i < elements.length; i++) {
        if (typesToRemove.includes(elements[i].getAttribute("data-type"))) {
            elements[i].parentElement.classList.add("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been hidden');
        } else {
            elements[i].parentElement.classList.remove("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been shown');
        }
    }
}

function sendToSleevesToRemoveArray(val) {
    if (sleevesToRemove.includes(val)) {
        sleevesToRemove = sleevesToRemove.filter((x) => x !== val);
        console.log(sleevesToRemove);
    } else {
        sleevesToRemove.push(val);
        console.log(sleevesToRemove);
    }

    // find all elements with class of home-product-info and if they have data-sleeves attribute equal to val add the class of hidden
    var elements = document.getElementsByClassName("home-product-info");
    console.log('Elements with sleeves ')
    console.log(elements);
    // check if elements data-sleeves attribute is in the array sleevesToRemove and hide if it is. show if it isn't
    for (var i = 0; i < elements.length; i++) {
        if (sleevesToRemove.includes(elements[i].getAttribute("data-sleeve"))) {
            elements[i].parentElement.classList.add("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been hidden');
        } else {
            elements[i].parentElement.classList.remove("hidden");
            console.log('elements parent: ' + elements[i].parentElement + 'has been shown');
        }
    }
}
</script>

</body>

</html>