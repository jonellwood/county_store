<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Component file to load some products onto the home page for the user to see when first going to index.php. In the past it has loaded the 4 best selling items and had loaded 4 "random" items just for a change of scenery. 
Includes:   config.php for database connection
*/

session_start();

require_once 'config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// ! This query returns 4 "random" products 
// $idsql = "SELECT products_new.product_id, products_new.name, COUNT(order_details.product_id) AS count 
// FROM (
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
// ) AS random_products
// LEFT JOIN products_new ON products_new.product_id = random_products.random_id
// LEFT JOIN order_details ON order_details.product_id = random_products.random_id

// GROUP BY products_new.product_id, products_new.name
// ORDER BY RAND()
// LIMIT 4";

// ! This query is top 4 products with most orders
// $idsql = "SELECT order_details.product_id, products_new.name, COUNT(*) as count FROM order_details JOIN products_new on products_new.product_id=order_details.product_id GROUP BY product_id order by count DESC LIMIT 4";
// ! this query is top 4 products with most orders excluding hats because they make the page look weird
$idsql = "SELECT od.product_id, pn.name, COUNT(*) as count 
FROM order_details od
JOIN products_new pn on pn.product_id=od.product_id 
JOIN products_producttype pt on pt.product_id = od.product_id
WHERE pt.producttype_id != 3
GROUP BY od.product_id order by count DESC LIMIT 4";
$idstmt = $conn->prepare($idsql);
$idstmt->execute();
$idresult = $idstmt->get_result();

$felist = array();
if ($idresult->num_rows > 0) {
    while ($idrow = $idresult->fetch_array()) {
        // $felist[$idrow['product_id']] = $idrow['count'];
        array_push($felist, [
            'product_id' => $idrow['product_id'],
            'name' => $idrow['name'],
            'count' => $idrow['count'],
        ]);
    }
}
?>

<!-- <div class="top-seller-header">
    <h2 class="hot-header-text"> Top Sellers </h2>
</div> -->
<div class="products-container" id="products-container">
    <?php

    $c = 0;
    foreach ($felist as $product) {


        $id = $product['product_id'];
        $prosql = "SELECT * from products_new where product_id = $id";
        $prostmt = $conn->prepare($prosql);
        $prostmt->execute();

        $proresult = $prostmt->get_result();
        if ($proresult->num_rows > 0) {
            while ($prorow = $proresult->fetch_assoc()) {
                $proImage = !empty($prorow["image"]) ? $prorow['image'] : 'demo-img.jpg';
                // $c++;
    ?>
                <div class="card" id="featured-card" view-transition-group="image-transition">
                    <a href="product-details.php?product_id=<?php echo $prorow["product_id"]; ?>">
                        <img src="<?php echo $proImage; ?>" alt="product name" class="card-img-top" view-transition-old="image-transition">
                        <div class="card-body featured">
                            <p class="card-title"><?php echo $prorow["name"]; ?></p>
                            <!-- <p class="card-subtitle mb-2 ">Starting at:
                                </?php echo CURRENCY_SYMBOL . $prorow["price"] . ' ' . CURRENCY; ?></p> -->
                            <!-- <div class="button-holder">
                        </div> -->
                        </div>
                        <p class="hot-item"><img alt="Custom badge" src="https://img.shields.io/badge/<?php echo $felist[$c]['count'] ?>-Ordered-red?style=social&logo=Clubhouse">
                        </p>
                    </a>
                    <!-- <p class="hot-item"><?php echo $felist[$c]['count'] ?> ordered so far </p> -->
                </div>

    <?php
            }
            $c++;
        } else {
            echo "<p>No hot items to see here</p>";
        }
    }
    ?>
</div>

<style>
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

    .hot-item {
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
        padding: 0;
        /* margin-top: 25px; */
        margin-bottom: 10px;

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

    .card-title {
        display: flex;
        flex-wrap: nowrap;
        height: 50px;
    }


    .card {

        height: fit-content;
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


    ::view-transition-old(root) {
        animation: 3.75s ease-in both fadeout;
    }

    ::view-transition-new(root) {
        animation: 3.75s ease-in both fadein;
    }

    /* @keyframes grow-x {
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

    ::view-transition-group(root) {
        height: auto;
        position: absolute;
        top: 0;
        left: 0;
        transform-origin: top left;
    }

    ::view-transition-old(root) {
        animation: 0.5s ease-in-out both grow-and-move;
    }

    ::view-transition-new(root) {
        animation: 0.5s ease-in-out both grow-and-move;
    } */
</style>