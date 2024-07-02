<?php

/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/12/2024
Purpose: View the items with type mens-outwear.
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

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Products by Category</title>

    <script>
        let sizesToRemove = [];
        let typesToRemove = [];
        let sleevesToRemove = [];

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
                        html += `<input type="checkbox" name="${sizes[i].filter}" value="${sizes[i].id}" id="${prefix + sizes[i].id}" checked onchange="sendToSizesToRemoveArray(this.value)"/>`
                        html += `</label>`
                    }
                    html += `</div>`
                    html += `<div class="filter-group" id="type-filter-group">`
                    html += `<span>Type Filters:</span>`
                    for (var j = 0; j < types.length; j++) {
                        var prefix = 't'
                        html += `<label for="${prefix + types[j].id}">${types[j].filter}`
                        html += `<input type="checkbox" name="${types[j].filter}" value="${types[j].id}" id="${prefix + types[j].id}" checked onchange="sendToTypesToRemoveArray(this.value)"/>`
                        html += `</label>`
                    }
                    html += `</div>`
                    html += `<div class="filter-group" id="sleeve-filter-group">`
                    html += `<span>Sleeve Filters:</span>`
                    for (var k = 0; k < sleeves.length; k++) {
                        var prefix = 'sl'
                        html += `<label for="${prefix + sleeves[k].id}">${sleeves[k].filter}`
                        html += `<input type="checkbox" name="${sleeves[k].filter}" value="${sleeves[k].id}" id="${prefix + sleeves[k].id}" checked onchange="sendToSleevesToRemoveArray(this.value)"/>`
                        html += `</label>`
                    }
                    html += `</div>`
                    html += `</div>`
                    document.getElementById('filters').innerHTML = html;
                })
        }


        function getFilteredProducts(typeID, genderFilter) {
            fetch('fetchFilteredProducts.php?type=' + typeID + '&gender=' + genderFilter)
                .then((response) => response.json())
                .then((data) => {
                    // console.log(data);
                    var html = '';
                    html += `<div class="products-container" id="products-container">`
                    for (var i = 0; i < data.length; i++) {
                        html += `

                        <div class="product-card-holder">
                        <div class="card home-product-info" id="${data[i].product_id}" value="${data[i].product_id}" data-gender="${data[i].gender_filter}" data-type="${data[i].type_filter}" data-size="${data[i].size_filter}" data-sleeve="${data[i].sleeve_filter}" view-transition-group="image-transition">

                            <img src="${data[i].image}" class="card-img-top" alt="${data[i].name}" view-transition-old="image-transition">
                            <div class="card-body featured">
                                <h6 class="card-title">${data[0].name} <br> Item #: ${data[i].code}</h6>
                            </div>
                        </div>
                        <div class="card-button-holder">
                            <button class="button" id="details-button" value="product-details.php?product_id=${data[i].product_id}" onclick="gotoPage(this.value)">Details</button>
                            
                        </div>
                    </div>
                    
                    `
                    }
                    html += `</div>`
                    document.getElementById('products-target').innerHTML = html;

                    setListener();
                    getFilterData();
                })
        }
        getFilteredProducts(5, 1)
    </script>

</head>

<body>

    <div class="container">
        <?php include "./components/slider.php" ?>
        <div class="spacer23"> - </div>

        <div class="btn-container" id="btn-container">
            <button class="btn js-toggle-filter" popovertarget="filters-popover">filter <code>products</code></button>
            <button class="btn js-toggle-grid-gap">toggle <code>item-spacing</code></button>
            <button class="btn js-toggle-grid-columns">toggle <code>items-per-row</code></button>

            <div id="filters-popover" class="filters-popover" popover>
                <button popovertarget="filters-popover" popovertargetaction="hide">
                    <span aria-hidden="true">❌</span>
                    <span class="sr-only">Close</span>
                </button>
                <div class="popover-description">
                    <span>Uncheck items you don't want to see in results</span>
                </div>
                <div id="filters"></div>
            </div>
        </div>
        <!-- // ? product cards will render from function in this div. -->
        <div id="products-target" class="products-target"></div>

        <div class="button-holder">
            <a href="index.php"><button class="button" type="button">🏡 Home </button></a>
            <button class="button" type="button">🔎 Filter </button>
        </div>
    </div>
    <script>
        function setListener() {
            const grid = document.querySelector("#products-container");
            const card = document.querySelector(".card-body");
            const goBtn = document.querySelector("#details-button");

            document.querySelector(".js-toggle-grid-columns").addEventListener("click", () => {
                if (document.startViewTransition) {
                    document.startViewTransition(_ => grid.classList.toggle("grid--big-columns"));
                } else {
                    grid.classList.toggle("grid--big-columns");
                }
            });
            document.querySelector(".js-toggle-grid-gap").addEventListener("click", () => {
                if (document.startViewTransition) {
                    document.startViewTransition(_ => grid.classList.toggle("grid--big-gap"));
                } else {
                    grid.classList.toggle("grid--big-gap");
                }
            })
            goBtn.addEventListener("click", e => {
                e.stopPropagation();
                let target = e.target;
                return;
            })
            grid.addEventListener("click", ev => {
                // console.log('grid event listener');
                let target = ev.target;
                let parent = ev.target.parentElement;
                if (target.classList.contains("card")) {
                    if (document.startViewTransition) {
                        const direction = target.classList.contains('card--expanded') ? 'shrink' : 'grow';
                        const origVtName = target.style.viewTransitionName;
                        target.style.viewTransitionName = `img-${direction}`;
                        document.startViewTransition(_ => {
                            parent.classList.toggle("card--expanded");
                            setTimeout(_ => target.style.viewTransitionName = origVtName, 0);
                        });
                    } else {
                        parent.classList.toggle("card--expanded");
                    }
                    return;
                }
            });
        }
    </script>
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
    <?php include "cartSlideout.php" ?>
    <?php include "footer.php" ?>
    <script>
        function extractProfileIdFromUrl(url) {
            return url.searchParams.get("product_id");
        }
        window.addEventListener("pageswap", async (e) => {
            // only run if active page trans exists
            if (e.viewTransition) {
                const currentUrl = e.activation.from?.url ? new URL(e.activation.from.url) : null;
                const targetUrl = new URL(e.activation.entry.url);

                // going from productDetails page to shirts-view-page
                if (isViewPage(targetUrl)) {
                    const profile = extractProfileIdFromUrl(targetUrl);

                    // set view-transition-name values on the elements to animate
                    document.querySelector(`#${profile} img`).style.viewTransitionName = "image";
                    // Remove view-transition-names after snapshots have been taken
                    // Stops naming conflicts resulting from the page state persisting in BFCache
                    await e.viewTransition.finished;
                    document.querySelector(`#${profile} img`).style.viewTransitionName = "none";
                }

            }
        })
    </script>
</body>


</html>

<style>
    @font-face {
        font-family: bcFont;
        src: url(./fonts/Gotham-Medium.otf);
    }

    body {
        background-color: #ffffff10;
    }

    .container {
        max-width: unset !important;
    }

    .products-container {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        padding: 1rem;
        position: relative;
        z-index: 1;
    }

    .product-card-holder {
        padding: 0px;
        margin: 0px;
        max-width: 400px;
        max-height: fit-content;
        border: #ffffff10 4px solid;
    }

    .card {
        margin-right: 20px;
        border-radius: 1px;
        box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        padding: 12px;
    }

    .card-img-top {
        width: 100%;
        object-fit: cover;
        position: relative;
        z-index: -1;
    }

    button {
        border-radius: 5px;
    }

    .button-holder {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6em;
    }

    .button {
        margin: 5px;
    }

    .button {
        display: inline-block;
        padding: 5px 10px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border: 2px solid #000000;
        border-radius: 5px;
        background-color: #4CAF50;
        color: #000000;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #4EB14F;
    }

    .getBig {
        background-color: #93c !important;
        animation: createBox 1.0s;
        color: #93c !important;
    }

    @keyframes createBox {
        from {
            transform: scale(1);
        }

        to {
            transform: scale(100);
        }
    }

    img {
        margin-left: auto;
        margin-right: auto;
        background-color: transparent;
    }

    .card-text {
        color: aliceblue;
    }

    /* #featured-card {
        position: relative;
        z-index: 0;
        cursor: crosshair;
    } */

    .featured {
        width: 100%;
        height: 5rem;
        position: absolute;
        bottom: 0;
        z-index: 2;
        background-color: #000000;
        transition: background-color .5s linear;
        display: block;
    }

    .featured:hover {
        background-color: #00000020;
        transition: background-color .5s linear;
    }

    .card-title,
    .card-subtitle {
        font-size: .85rem;
    }

    .hidden {
        display: none;
    }


    ::view-transition-old(*),
    ::view-transition-new(*) {
        animation: none;
        mix-blend-mode: normal;
        height: 100%;
        overflow: clip;
    }

    ::view-transition-old(*) {
        object-fit: contain;
    }

    ::view-transition-new(*) {
        object-fit: cover;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10vw, 1fr));
        grid-auto-rows: 40vw;
        grid-gap: 2vw;
        grid-auto-flow: dense;
    }

    .grid--big-columns {
        grid-template-columns: repeat(auto-fit, minmax(15vw, 2fr));
    }

    .grid--big-gap {
        grid-gap: 2vw;
    }

    /* styling */

    .card--expanded {
        grid-column: span 2;
        grid-row: span 2;
        border-radius: 1px;
        max-width: unset;
    }

    .card--expanded .card__img {
        transform: scale(1.03);
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: scale(0);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .fade-in {
        opacity: 0;
        animation: fadeIn 1.9s forwards;
        animation-delay: 0.9s;
    }

    .card {
        position: relative;

        z-index: 0;
        cursor: crosshair;
    }

    .card>* {
        pointer-events: none;
    }

    .card__img {
        transition: transform 1s;
    }

    .filters-popover {
        width: fit-content;
        height: fit-content;
        background-color: #A3A3A3;
        border: 7px solid #005677;
        color: #000000;

    }

    .popover-description {
        margin-top: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: center;
        font-size: large;
    }

    .filters-holder {
        display: grid;
        grid-template-rows: auto;
        gap: 10px
    }

    .filter-group {
        display: flex;
        gap: 10px;
        font-size: large;
        margin-bottom: 10px;

        label {
            cursor: pointer;
            margin-right: 5px;
            line-height: 1.5;
        }

        input {
            margin-right: 5px;
            cursor: pointer;
        }

        input[type="checkbox"] {
            background-color: #789a48 !important;
        }

    }

    ::backdrop {
        backdrop-filter: blur(3px);
    }


    @keyframes grow-x {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    @keyframes shrink-x {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }

    @-webkit-keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30 px;
            opacity: 1;
        }
    }

    @keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30 px;
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeout {
        from {
            bottom: 30 px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    @keyframes fadeout {
        from {
            bottom: 30 px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    ::view-transition-group(card-transition) {
        height: auto;
        right: 0;
        left: auto;
        transform-origin: right center;
    }

    ::view-transition-old(image) {
        animation: 0.25s linear both shrink-x;
    }

    ::view-transition-new(image) {
        animation: 0.25s 0.25s linear both grow-x;
    }

    @keyframes move-out {
        from {
            transform: translateY(0%);

        }

        to {
            transform: translateY(-100%);
        }
    }

    @keyframes move-in {
        from {
            transform: translateX(100%);
        }

        to {
            transform: translateX(0%);
        }
    }


    @view-transition {
        navigation: auto;
    }


    /* ::view-transition-old(root) {
        animation: 0.75s ease-in both fadeout;
    }

    ::view-transition-new(root) {
        animation: 0.75s ease-in both fadein;
    } */

    /* ::view-transition-old(image) {
        animation: 0.75s ease-in both fadeout;
    }

    ::view-transition-new(image) {
        animation: 0.75s ease-in both fadein;
    } */

    ::view-transition-group(root) {
        animation-duration: 0.5s;
    }

    /* this is from Codium */
    @keyframes grow-and-move {
        from {
            transform: scale(0) translateY(0);
        }

        to {
            transform: scale(1) translateY(-100%);
        }
    }

    ::view-transition-group(image-transition) {
        height: auto;
        position: absolute;
        top: 0;
        left: 0;
        transform-origin: top left;
    }

    ::view-transition-old(image-transition) {
        animation: 0.5s ease-in-out both grow-and-move;
    }

    ::view-transition-new(image-transition) {
        animation: 0.5s ease-in-out both grow-and-move;
    }
</style>