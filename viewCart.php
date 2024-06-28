<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/28/2024
Purpose: View the items in the users cart, add comments if needed, and make changes to the quantity if needed. 
Includes:    slider.php, viewHead.php, cartSlideout.php, footer.php
*/

session_start();

if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init cart class
include_once 'Cart.class.php';
$cart = new Cart;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>View Cart</title>
    <meta charset='utf-8'>

    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <script>
        function updateCartItem(obj, id) {
            // Construct the URL with parameters
            const url = new URL("cartAction.php");
            url.searchParams.append("action", "updateCartItem");
            url.searchParams.append("id", id);
            url.searchParams.append("qty", obj.value);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    if (data === 'ok') {
                        // If data is ok, reload the page
                        location.reload();
                    } else {
                        // Else alert user that the update failed
                        alert('Cart update failed, please try again.');
                    }
                })
                .catch(error => {
                    console.error('There has been a problem with your fetch operation:', error);
                    alert('An error occurred, please try again later.');
                });
        }

        function updateCartItemComment(obj, id) {

            const url = new URL("cartAction.php");
            url.searchParams.append("action", "updateCartItemComment");
            url.searchParams.append("id", id);
            url.searchParams.append("comment", obj.value);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // Parse the response body as text
                })
                .then(data => {
                    if (data === 'ok') {
                        // If data is ok, reload the page or perform another action
                        location.reload(); // Comment this line we decide we don't want it. I hate reloads but...
                    } else {
                        alert('Comment update failed, please try again');
                    }
                })
                .catch(error => {
                    console.error('There has been a problem with your fetch operation:', error);
                    alert('An error occurred, please try again later.');
                });
        }

        function convertObjectToArray(obj) {
            let resultArray = [];
            for (let key in obj) {
                if (typeof obj[key] === "object") {
                    resultArray.push([key, convertObjectToArray(obj[key])]);
                } else {
                    resultArray.push([key, obj[key]]);
                }
            }
            return resultArray;
        }

        function makeDollar(str) {
            let amount = parseFloat(str);
            return `$${amount.toFixed(2)}`;
        }

        function renderCheckout(cart) {
            // console.log(cart);
            cartArray = convertObjectToArray(cart);
            //console.log(cartArray)
            // let accumulatedHtml = '';
            // let selectQuantities = {};
            var html = '';
            for (var i = 2; i < cartArray.length; i++) {
                const itemEntry = cartArray[i][1];
                //console.log("Item Entry # ", i)
                //console.log(itemEntry)
                var html = '';
                html += `
                <div>
                    <div class="active-items">
                        <div class="active-item" data-cartId=${itemEntry[4][1]}>
                            <div class="item-content">
                                <div class="item-content-inner">
                                    <div class="image-wrapper">
                                        <img src="${itemEntry[5][1]}" alt="${itemEntry[1][1]}" class="product-image" />
                                    </div>
                                    <div class="item-content-inner-inner">
                                        <ul class="unordered-list">
                                            <li class="product-title">${itemEntry[6][1]} - ${itemEntry[1][1]}</li>
                                            <div class="price-block">${makeDollar(itemEntry[2][1])}</div>
                                            <div class="content-tail">
                                                <li>Size: ${itemEntry[12][1]}</li>
                                                <li>Color: ${itemEntry[10][1]}</li>
                                                <li>Qty: ${itemEntry[3][1]}</li>
                                                <li>Dept Name: ${itemEntry[14][1]} </li>
                                                <li class='logo-holder'>Logo: <img src=${itemEntry[13][1]} alt="logo" class="logo-pict"></li>
                                            </div>
                                            <div class="item-content-footer">
                                                <button class='remove'>Delete</button> <button class='change'>Edit</button><button class='comment'>Add Comment</button>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                `

                document.getElementById('items').innerHTML += html;
            }
        }
    </script>
</head>

<body>
    <?php include "./components/slider.php" ?>
    <div class="spacer23"> - </div>
    <div class="container">
        <div id="items" class="items"></div>
        <div class="checkout">
            <p>Total: <?php echo CURRENCY_SYMBOL . (number_format(($cart->total() * 1.09), 2))  ?></p>
            <p>Total Items: <?php echo $cart->total_items() ?></p>
            <button>Checkout</button>
        </div>
        <!-- <div class="viewcart">
            </?php echo "<pre>";
            var_dump($cart->contents());
            echo "<pre>"; ?>
        </div> -->
        // TODO build popover for editing each entry and updating the cart
        // ! check to make sure the update Cart method also updates the local storage
    </div>
    </div>
    <!-- <div> -->
    <div class="bottom-buttons-holder">
        <div>
            <a href="<?php echo $_SESSION['GOBACK'] ?>"><button class="button" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                    Shopping </button></a>
        </div>
        <div>
            <?php if ($cart->total_items() > 0) { ?>
                <a href="checkout.php"><button class="button" type="button"> Proceed to Checkout <i class="fa fa-arrow-right" aria-hidden="true"></i></button></a>
            <?php } ?>
        </div>
    </div>

    <!-- </div> -->
    <?php include "cartSlideout.php" ?>
    <?php include "footer.php" ?>
    <script>
        renderCheckout(<?php echo $cart->serializeCart() ?>);
    </script>
</body>


</html>
<style>
    a {
        text-decoration: none;
    }

    .tiny-text {
        font-size: small;
    }

    .alert-warning {
        margin-top: 40px;
        text-align: center;
        font-weight: 500;
        font-size: larger;
        color: black;
        width: 50%;
        margin-left: auto;
        margin-right: auto;
        border-radius: 5px;

    }

    #cart-logo-img {
        width: 50px;
        /* margin-right: 15px; */
        /* margin-left: 15px; */
        transition: all .2s ease-in-out;
    }


    .dept-patch-info {
        margin-top: 10px;
    }

    pre {
        background-color: dodgerblue;
        color: white;

    }

    .container {
        max-width: unset !important;
        margin-top: 20px;
        margin-left: 10%;
        margin-right: 10%;
        /* width: 100%; */
        /* width: 100vw; */
        display: grid;
        grid-template-columns: 5fr 1fr;
    }

    .cart-display {
        display: grid;
        grid-template-columns: 5fr 1fr;
    }

    .little-prod-img {
        padding: 5px;
    }


    .button {
        border-radius: 5px;
        /* padding: 1px; */


    }

    .remove {
        background-color: darkred;
    }

    .comment {
        background-color: dodgerblue;
    }

    .change {
        background-color: green;
    }

    .items {
        background-color: #00000080;
        border-radius: 5px;
    }

    .active-items {
        display: grid;
        grid-template-columns: repeat(auto-fill, 100%);
        gap: 12px;
        color: aliceblue;
        font-size: larger;
        margin-bottom: 20px;
    }

    .active-item {
        max-width: none;
        border-bottom: 1px dotted aliceblue;
        padding-bottom: 12px;
    }

    .item-content {
        position: relative;
        margin-top: 12px;
    }

    .item-content-inner {
        width: 100%;
        display: flex !important;
        flex-direction: row;
        table-layout: fixed;
        zoom: 1;
        border-collapse: collapse;
    }

    .item-content-inner-inner {
        width: 100%;
        display: flex !important;
        flex-direction: row;
        table-layout: fixed;
        zoom: 1;
        border-collapse: collapse;
    }

    .image-wrapper {
        margin-right: 12px;
        margin-inline-end: 12px;
        flex-shrink: 0;
        margin-bottom: 4px;
    }

    .product-image {
        vertical-align: top;
        max-width: 100%;
        border: 0;
        height: 180px;
        /* width: 180px; */
        /* aspect-ratio: auto 180 / 180; */
    }

    .item-content {
        min-width: 0;
        flex: auto;
        margin-inline-end: 0;
        margin-right: 12px;
    }

    .item-content-footer {
        display: flex;
        justify-content: space-between;
        padding-top: 12px;
    }


    .unordered-list {
        display: grid;
        column-gap: 12px;
        grid-template-areas: "head price" "tail price";
        grid-template-rows: auto 1fr;
        grid-template-columns: 1fr minmax(13ch, 20%);
        list-style: none;
        word-wrap: break-word;
        margin: 0;
        width: 100%
    }

    .product-title {
        grid-area: head;
        line-height: 1.3rem;
        max-height: 2.6em;
        font-size: x-large;
        word-break: normal;
    }

    .price-block {
        grid-area: price;
        display: flex;
        flex-flow: column;
        align-items: end;
        text-align: end;
    }

    .content-tail {
        grid-area: tail;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
    }

    .logo-pict {
        width: 100px;
        margin-left: 50px;
        padding-top: 12px;
        margin-right: auto;

    }

    .logo-holder {
        grid-column-start: 4;
        grid-column-end: 5;
        grid-row-start: 2;
        grid-row-end: 4;
    }

    .dropdown {

        /* opacity: .01; */
        max-width: 100%;
        left: 0;
        transition: all .1s linear;
        line-height: 19px;

    }

    .bottom-buttons-holder {
        display: flex;
        justify-content: space-evenly;
        /* margin-left: 10px; */
        /* margin-right: 10px; */
    }

    .checkout {
        background-color: #ffffff50;
        height: fit-content;
        border-radius: 5px;
        padding: 12px;
        /* padding-bottom: 12px; */
        color: aliceblue;
        margin-left: 10px;
        padding-right: 20px;
        text-align: end;
        font-size: larger;
    }
</style>