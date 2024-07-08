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
    </?php include "alert-banner.php" ?>
    <div class="hot-sellers">
        <?php include "stats.php" ?>
        </?php include "views-grid.php" ?>
    </div>
    <?php include "cartSlideout.php" ?>
    <?php include "footer.php" ?>
    <div class="alert-banner" id="alert-banner" popover=auto>
        <div class="alert-text">🚨 All orders must be submitted by May 31st, Requests will not be able to be submitted between June 1st and June 30th. </div>
        <div class="holder">
            <p>

                <label for="dontShowAgain" id="dontShowAgainLabel">Don't show again</label>
                <input type="checkbox" id="dontShowAgain" name="dontShowAgain">
                <button class="button" popovertarget="alert-banner" popovertargetaction="hide">OK</button>
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

    function checkCookies() {
        var currentMonth = new Date().getMonth() + 1;
        // if month is not May, the check for the cookie and show if the cookie is not set
        if (currentMonth != 5) {
            if (document.cookie.indexOf("countyStore-doNotAlert") == -1) {
                showPopover();
            }
        } else {
            // if month is May, show the cookie no matter what
            // we are removing the checkbox for dont show again as well to avoid confusion
            var dontShowAgain = document.getElementById("dontShowAgain");
            var dontShowAgainLabel = document.getElementById("dontShowAgainLabel");
            dontShowAgain.style.display = "none";
            dontShowAgainLabel.style.display = "none";
            showPopover();
        }


    }
    checkCookies();

    function showPopover() {
        var popover = document.getElementById('alert-banner');
        popover.showPopover()
    }
    // showPopover();

    var checkbox = document.getElementById("dontShowAgain");
    checkbox.addEventListener("change", function() {
        if (this.checked) {
            createCookie("countyStore-doNotAlert", "true", 90); // Set the cookie to expire in 90 days
        } else {
            createCookie("countyStore-doNotAlert", "", -1); // Delete the cookie
        }
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
        background-color: red;
        color: white;
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