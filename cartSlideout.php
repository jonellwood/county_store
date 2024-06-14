<?php
// session_start();
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;

?>

<div class="cart-slideout hidden" id="cart-slideout">
    <div class="cart-header-holder">
        <h1>Your Cart</h1>
    </div>
    <div class="cart-slide-row">
        <!-- <div> -->
        <div class="cart-table-holder">
            <table class="cart">
                <thead>
                    <tr>
                        <th width='55%'>Product</th>
                        <th width='15%'>Qty</th>
                        <th width='20%' class='a-right'>Price</th>
                        <th width='10%'></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($cart->total_items() > 0) {
                        $cartItems = $cart->contents();
                        foreach ($cartItems as $item) {

                    ?>
                            <tr>
                                <td><?php echo $item["name"]; ?></td>
                                <td><?php echo $item["qty"]; ?> </td>
                                <td class='a-right'><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2); ?> </td>
                                <td class='a-right'><i class="fa fa-trash" aria-hidden="true" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item"></i></td>
                            </tr>


                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="3">
                                <p>Your cart is empty
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($cart->total_items() > 0) { ?>

                        <tr class='start-totals'>

                            <td><strong>Sub-Total</strong></td>
                            <?php $subtotal = $cart->total() ?> <td colspan="2" class='a-right'>
                                <?php echo CURRENCY_SYMBOL . number_format($subtotal, 2) ?>
                            </td>

                        </tr>
                        <tr>
                            <hr>
                        </tr>
                        <tr>

                            <td><strong>Sales Tax:</strong>
                                <?php $sales_tax = number_format(($cart->total() * .09), 2) ?>
                            <td colspan="2" class="a-right"> <?php echo CURRENCY_SYMBOL . $sales_tax ?>
                            </td>


                        </tr>
                        <tr>

                            <td><strong>Cart Total:</strong></td>
                            <td colspan="2" class="a-right">
                                <strong><?php echo CURRENCY_SYMBOL . number_format(($subtotal + $sales_tax), 2) ?>
                                </strong>
                            </td>

                        </tr>

                    <?php } ?>
                    <!-- </tr> -->
                </tbody>
            </table>
        </div>
        <a href="./viewCart.php"><button class="cart-btn btn-primary">Go To Cart</button></a>
        <!-- </div> -->
    </div>
</div>

<script>
    function toggleSlideout() {
        var cartSlideout = document.getElementById("cart-slideout");
        cartSlideout.classList.toggle("hidden");
    }
    document.body.addEventListener("click", function(event) {
        var cartSlideout = document.getElementById("cart-slideout");
        var targetElement = event.target;

        // Check if the target element is inside the slideout
        var isClickInsideSlideout = cartSlideout.contains(targetElement);


        // If the click is not inside the slideout, hide the slideout
        if (!isClickInsideSlideout && targetElement.id !== "toggle-button") {
            cartSlideout.classList.add("hidden");

        }
    });
</script>


<style>
    .cart-slideout {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        position: fixed;
        top: 0;
        right: -0px;
        /* Start with the element hidden outside the viewport */
        width: 400px;
        height: 100vh;
        /* background-color: lightgrey; */
        background-color: #ffffff;
        opacity: 0.97;
        /* border-left: 2px solid darkgray; */
        box-shadow: -10px 0px 16px -4px rgba(6, 6, 6, 1);
        padding-left: 1em;
        padding-right: 1em;
        color: #282828;
        transition: right 0.3s ease-in-out;
        z-index: 5;
    }

    .cart-slideout.hidden {
        right: -400px;
        transition: right 0.3s ease-in-out;
    }

    .cart-slide-row {
        display: grid;
        grid-template-columns: 1fr;
        justify-content: center;
    }


    .cart-table-holder {
        display: flex;
        justify-content: center;
    }

    table {
        color: #282828;
        /* text-align: justify; */
        border-collapse: collapse;
    }


    .cart-header-holder h1 {
        font-size: large;
        text-align: center;
        text-transform: uppercase;
        margin-top: 20px;
        background-color: #555555;
        padding-top: 15px;
        padding-bottom: 15px;
    }

    .start-totals {
        padding-top: 35px;
        border-top: 3px solid darkblue;
        border-radius: 5%;
        margin-top: 25px !important;
        color: #282828;
    }

    .a-right {
        text-align: right;
    }

    a {
        text-decoration: none;
    }

    .cart-btn {
        margin-top: 35px !important;
        background-color: #555555;
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

    i:hover {
        cursor: pointer;
    }


    /* #logo-img {
    width: 50px;

    transition: all .2s ease-in-out;
} */
</style>