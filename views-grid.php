<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Component file to load some products onto the home page for the user to see when first going to index.php. In the past it has loaded the 4 best selling items and had loaded 4 "random" items just for a change of scenery. 
Includes:   config.php for database connection
*/

// session_start();

?>
<script>
    async function loadGrid() {
        await fetch('grid-data.json')
            .then(response => response.json())
            .then(data => {
                console.log(data.data.length);
                let x = data.data;
                var html = '';
                for (let i = 0; i < x.length; i++) {
                    html += `<div class="card" id="featured-card" view-transition-group="image-transition">`
                    html += `<a href="${x[i].link}">`
                    html += `<img src="${x[i].image}" alt="${x[i].title}" class="card-img-top" view-transition-old="image-transition">`
                    html += `<div class="card-body">`
                    html += `<p class="grid-card-title">${x[i].title}</p>`
                    html += `</div>`
                    html += `</a>`
                    // html += `</div>`
                    html += `<p class="grid-hot-item">`
                    html += `${x[i].count} Items Available`
                    // html += `<img alt="Custom badge" src="https://img.shields.io/badge/${x[i].count}-Products-red?style=social&logo=Clubhouse">`
                    html += `</p>`
                    html += `</div>`
                }
                console.log(html);
                document.getElementById('products-container').innerHTML = html;
            });
    }
    loadGrid();
</script>
<div class="products-container" id="products-container"></div>


<style>
    .products-container {

        grid-template-columns: auto auto !important;
    }

    table {
        border: 0;
        border-collapse: collapse;
        caption-side: bottom;
        line-height: 1.5rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        width: 100%;
        table-layout: fixed;
    }


    thead {
        display: table-header-group;
        vertical-align: middle;
        border-color: inherit;
    }

    thead tr {
        border-bottom: 1px solid rgba(0, 0, 0, .15);
        vertical-align: top;
    }

    thead th {
        padding-bottom: .75rem;
        padding-top: .7505rem;
    }

    tbody {
        display: table-row-group;
        vertical-align: middle;
        border-color: inherit;
    }

    tr {
        display: table-row;
        vertical-align: inherit;
        border-color: inherit;
    }

    td,
    th {
        font-weight: 400;
        overflow: hidden;
        padding-left: .5rem;
        padding-right: .5rem;
        text-align: left;
        text-overflow: ellipsis;
        vertical-align: top;
    }

    tfoot tr,
    tbody tr:not(:first-child) {
        border-top: 1px solid rgba(0, 0, 0, .1);
    }

    tfoot {
        display: table-footer-group;
        vertical-align: middle;
        border-color: inherit;
    }

    @font-face {
        font-family: hot;
        src: url(./fonts/ConcertOne-Regular.ttf);
    }

    .grid-hot-item {
        font-family: hot;
        font-weight: bolder;
        position: absolute;
        z-index: 3;
        /* left: 0; */
        right: 0;
        /* top: 0; */
        bottom: 0;
        color: #DB4437;
        /* font-size: 2em; */
        /* transform: rotate(-15deg); */
        transform: rotate(-3deg);
        background-color: #00000030;
        margin: 0;
        padding: 5px;
        /* margin-top: 25px; */
        margin-bottom: 10px;
        margin-right: 10px;

    }

    .hot-item img {
        width: 165px;
        height: auto;
        /* border: 1px solid black; */
        border-radius: 5px;

    }

    .top-seller-header {
        font-family: RoboCondensed;
        background-color: #06060650;
        text-align: center;
    }

    .products-container {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        /* position: relative; */
        /* gap: 10; */
        z-index: 1;
    }

    .card-title,
    .card-subtitle {
        color: white;
        font-size: smaller;
        /* text-transform: uppercase; */
    }

    .card-body {
        display: grid;
        align-content: end;

    }

    .grid-card-title {
        display: flex;
        flex-wrap: nowrap;
        /* height: 50px; */
        color: white;
        font-size: medium;

    }


    .card {

        height: fit-content;
    }

    .card-img-top {
        border-radius: 10px;

    }

    .card:hover {
        box-shadow: 1px 1px 11px 1px rgba(0, 85, 119, 1);
    }

    @media screen and (max-width: 900px) {
        body {
            overflow: scroll;
        }

        .products-container {
            grid-template-columns: 1fr 1fr;
        }
    }

    @view-transition {
        navigation: auto;
    }


    /* ::view-transition-old(root) {
        animation: 3.75s ease-in both fadeout;
    }

    ::view-transition-new(root) {
        animation: 3.75s ease-in both fadein;
    } */

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
        animation: 0.5s ease-in-out both grow-x;
    }

    ::view-transition-new(image-transition) {
        animation: 0.5s ease-in-out both shrink-x;
    }
</style>