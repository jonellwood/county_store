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
function checkMonthAndRedirect()
{
    if (date('F') === 'June') {
        header('Location: store-closed.php');
        exit();
    }
}
checkMonthAndRedirect();
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
    <?php include "footer.php" ?>
    <div class="alert-banner" id="alert-banner" popover=auto>
        <div class="alert-text">🚨 All orders must be submitted by May 31st, Requests will not be able to be submitted between June 1st and June 30th. </div>
        <div class="holder">
            <p>

                <!-- <label for="dontShowAgain" id="dontShowAgainLabel">Don't show again</label> -->
                <!-- <input type="checkbox" id="dontShowAgain" name="dontShowAgain"> -->
                <button class="button" popovertarget="alert-banner" popovertargetaction="hide" id="dontShowAgain">OK</button>
            </p>
        </div>
    </div>

</body>
<script>
    function createCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function showWarning() {
        var currentMonth = new Date().getMonth() + 1;
        console.log(currentMonth);
        if (currentMonth == 3 || currentMonth == 4 || currentMonth == 5) {
            showPopover();
        } else {
            return;
        }
    };

    showWarning();

    function showPopover() {
        var popover = document.getElementById('alert-banner');
        popover.showPopover()
    }

    var closeButton = document.getElementById("dontShowAgain");
    closeButton.addEventListener("click", function() {
        createCookie("countyStore-doNotAlert", "true", 1); // Set the cookie to expire in 24 hours
    });
</script>

</html>

<style>
    .hot-sellers {
        width: 90%;
        position: relative;
        z-index: 3;
        margin-top: 80px;
        margin-left: auto;
        margin-right: auto;
        display: flex;
        /* align-items: flex-end; */
        min-height: 80vh;
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

    .alert-banner {
        background-color: #1F9CED;
        color: #000000;
        justify-content: center;
        align-items: center;
        padding: 20px;
        font-size: larger;
        gap: 25px;
    }

    .holder {
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
    }

    ::backdrop {
        backdrop-filter: blur(3px);
    }
</style>