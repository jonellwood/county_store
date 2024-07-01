<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Entry point for County Store Application. 
Includes:   Cart.class.php is to initialize a Cart Object for users shopping session.
            viewHead.php is a common html head element with css, favicon, and metadata 
            slider.php is nav element across the top. Poorly named and designed and soon to be replaced.
            stats.php loads 4 "random" products in for the user to see with badges for the number of orders for that product.
            cartSlideout.php is a cart viewer that sits off screen to right which displays the items in the cart to the user. It also servers as a link point to the cart page.
            footer.php is the footer element.

*/
require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Home | Berkeley County Store</title>
</head>

<body>
    <div class="front-image-background">
        <!-- <img src="./County-Store-Image.png" alt="some-store" /> -->
        <!-- <img src="./modern-mall.png" alt="some-store" /> -->
    </div>
    <?php include "components/slider.php" ?>
    <div class="hot-sellers">
        <?php include "stats.php" ?>
    </div>
    <?php include "cartSlideout.php" ?>
</body>
<?php include "footer.php" ?>



</html>

<style>
    body {
        background-color: #fff;
        background: url('./modern-mall.png') no-repeat center center fixed;
        background-size: cover;
        height: 100%;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
    }

    .hot-sellers {
        width: 90%;
        position: relative;
        /* margin-top: 150px; */
        margin-left: auto;
        margin-right: auto;
        display: flex;
        align-items: flex-end;
        min-height: 95vh;
        max-height: 100vh;
    }


    .card {
        margin-top: 20px;
        margin-right: 20px;
        border-radius: 1px;
        box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    }

    .alert {
        margin-top: 20px;
        margin-bottom: 20px;
    }
</style>