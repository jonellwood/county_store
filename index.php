<?php
/*
Created: 2024/08/28 14:24:35
Last modified: 2025/06/26 14:32:40
Organization: Berkeley County IT Department
Purpose: Entry point for County Store Application. 
Includes:   Cart.class.php is to initialize a Cart Object for users shopping session.
            viewHead.php is a common html head element with css, favicon, and metadata 
            slider.php is nav element across the top. Poorly named and designed and soon to be replaced.
            stats.php loads 4 "random" products in for the user to see with badges for the number of orders for that product.
            cartSlideout.php is a cart viewer that sits off screen to right which displays the items in the cart to the user. It also servers as a link point to the cart page.
             footer.php is the footer element.
*/

require_once "config.php";
// require_once "/data/dbconfig.php";

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
<div id="sticky-parallax-header"></div>
<div class="sticky">
    <div class="alert-banner" id="alert-banner"> - </div>
    <?php include "./components/viewHead.php" ?>
</div>

<!-- 
<div class="front-image-background">
    <img src="./County-Store-Image.png" alt="some-store" />
</div> -->


<!-- <div class="d-flex justify-content-center mx-1"> -->
<div class="d-grid-4 gap-3" id="hot-sellers">
    <!-- <div class="row align-items-center justify-content-start gap-3" id="hot-sellers">

    </div> -->
</div>
</?php include "cartSlideout.php" ?>
<?php include "footer.php" ?>
<script src="./functions/renderProduct.js"></script>
<!-- <div class="alert-banner" id="alert-banner" popover=auto>
    <div class="alert-text">All requests must be submitted by May 14th, Requests will not be able to be submitted
        between May 15th and June 30th. </div>
    <div class="holder">
        <p>

          
            <button class="button" popovertarget="alert-banner" popovertargetaction="hide"
                id="dontShowAgain">OK</button>
        </p>
    </div>
</div> -->

</body>


<script src="functions/createIndexedDB.js"></script>
<script src="functions/renderFiscalYearAlertBanner.js"></script>
<script>
    renderBanner();
    // function renderAlert() {
    //     var html =
    //         '<div class="alert-banner"> <div class="alert-text">All requests must be submitted by May 14th. Requests will not be able to be submitted between May 15th and June 30th. </div></div>';
    //     document.getElementById('alert-banner').innerHTML = html
    // }
    //renderAlert();
    // copyDataToIndexedDB();
    async function fetchTopProducts() {
        await fetch('./API/fetchTopProducts.php')
            .then(response => response.json())
            .then(data => {
                var productsHtml = '';
                for (var i = 0; i < data.length; i++) {
                    productsHtml += renderProduct(data[i]);
                }
                document.getElementById('hot-sellers').innerHTML = productsHtml;
            })
            .catch(error => console.error(error));
    }
    fetchTopProducts()

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

    //showWarning();

    function showPopover() {
        var popover = document.getElementById('alert-banner');
        popover.showPopover()
    }

    // var closeButton = document.getElementById("dontShowAgain");
    // closeButton.addEventListener("click", function() {
    //     createCookie("countyStore-doNotAlert", "true", 1); // Set the cookie to expire in 24 hours
    // });
</script>

</html>
<style>
    #hot-sellers {
        position: absolute;
        top: 99dvh;
        /* z-index: -2 !important; */
        padding-bottom: 75px;
        margin-bottom: 75px;
    }

    body {
        /* background-image: url(./County-Store-Image.png); */
        /* background-size: contain; */
        max-height: 100dvh;
    }

    /* .alert-banner,
    .navbar {} */

    .sticky {
        position: fixed;
        top: 0;
        width: 100%;
        /* z-index: 5 !important; */

        .alert-banner {
            position: sticky;
            top: 0;
            z-index: 20 !important;
        }

        header {
            /* position: sticky; */
            top: 0;

        }

    }

    /* .alert-banner {
    background-color: #e31c3d;
    
    color: #ffffff;
    
    justify-content: center;
    align-items: center;
    padding: 20px;
    font-size: larger;
    gap: 25px;
}

.alert-text {
    text-align: center;
} */

    /* .front-image-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    } */
</style>