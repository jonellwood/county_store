<?php
//session_start();
include_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;

?>
<script>
    function setCartInLocalStorage() {
        var cart = <?php echo $cart->serializeCart() ?>;
        // console.log('cart');
        // console.log(cart);
        cart.timestamp = new Date().toISOString();
        localStorage.setItem('store-cart', JSON.stringify(cart));

    }


    function getCartFromLocalStorage() {
        var cartData = localStorage.getItem('store-cart');
        if (cartData) {
            return JSON.parse(cartData);
        }
        return;
    }
    getCartFromLocalStorage();
    // helper function for comparing arrays of carts ... that I don't think I actually need now.
    function findItemByUid(array, uid) {
        return array.find(item => item.add_item_uid === uid);
    }
    // helper function to compare a timestamp in local storage against now() to see the age
    function howOldIsLocalStorage(str) {
        var now = new Date();
        var older = new Date(str)
        var gap = (now - older);
        return gap;
    }

    function flattenEntries(ent) {
        return ent.reduce((acc, [key, value]) => {
            if (typeof value === 'object' && value !== null) {
                acc.push([key, ...flattenEntries(Object.entries(value))]);
            } else {
                acc.push([key, value]);
            }
            return acc;
        }, []);
    }

    function syncCartWithServer() {
        // check for cart in local storage
        var cartData = getCartFromLocalStorage();

        // init cartDat with values if it does not exist
        if (!cartData) {
            cartData = {
                total_items: 0,
                cart_total: 0,
                timestamp: Date.now()
            };
        }
        // convert to an array of entries - makes comparing them much easier
        var cartDataArray = Object.entries(cartData);

        // get cart from server if exists
        var cartServerData = <?php echo $cart->serializeCart(); ?>;
        var cartServerArray = Object.entries(cartServerData);

        // see how old the cart in local storage is.
        // TODO we want to purge after 90 days (maybe... )
        howOldIsLocalStorage(cartData.timestamp);

        cartServerArray.forEach(serverItem => {
            if (!findItemByUid(cartDataArray, serverItem.add_item_uid)) {
                cartDataArray.push(serverItem);
            }
        })
        cartDataArray.forEach(dataItem => {
            if (!findItemByUid(cartServerArray, dataItem.add_item_uid)) {
                cartServerArray.push(dataItem)
            }
        })


        if (cartData.total_items == 0 && cartData.cart_total == 0 && cartServerData.total_items != 0) {
            //console.log('Writing server data to local storage');
            setCartInLocalStorage(cartServerData);
            return
        } else if (cartData.total_items != 0 && cartServerData.total_items == 0 && howOldIsLocalStorage(cartData.timestamp) > 1440000) {
            fetch('cartSync.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(cartServerArray)
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                })
                .catch((error) => {
                    console.error('Error: ', error);
                });
        } else if (cartServerData.total_items > 0) {
            setCartInLocalStorage(cartServerArray)
        }
    }
    // function syncCartWithServer() {
    //     var serverCartData = <?php echo ($cart->serializeCart()); ?>;
    //     console.log('Server Cart  Data')
    //     console.log(serverCartData)
    //     var localStorageCartData = JSON.parse(localStorage.getItem('store-cart')) || {};
    //     if (!localStorageCartData) {
    //         cartData = {
    //             total_items: 0,
    //             cart_total: 0,
    //             timestamp: Date.now()
    //         };
    //         localStorage.setItem('store-cart', JSON.stringify(localStorageCartData));
    //     }
    //     console.log('Local Cart  Data')
    //     console.log(localStorageCartData)
    //     // convert to arrays - way easier to compare
    //     var serverCartArray = Object.entries(serverCartData)
    //     var localStorageCartArray = Object.entries(localStorageCartData)
    //     console.log('serverCartArray')
    //     console.log(serverCartArray)
    //     console.log('localStorageCartArray')
    //     console.log(localStorageCartArray)
    //     // dif arrays
    //     function compareArrays(arrayA, arrayB) {
    //         const flatA = flattenEntries(arrayA);
    //         const flatB = flattenEntries(arrayB);

    //         const mapA = new Map(flatA.map(item => [item[0], item]));
    //         const mapB = new Map(flatB.map(item => [item[0], item]));
    //         //const mapA = new Map(arrayA.map(item => [item[0], item]));
    //         //const mapB = new Map(arrayB.map(item => [item[0], item]));

    //         const diffA = [...mapA].filter(([key]) => !mapB.has(key));
    //         const diffB = [...mapB].filter(([key]) => !mapA.has(key));
    //         console.log('diffA')
    //         console.log(diffA)
    //         console.log('diffB')
    //         console.log(diffB)
    //         return {
    //             diffA,
    //             diffB
    //         };
    //     }
    //     // compare using dif
    //     const {
    //         diffA,
    //         diffB
    //     } = compareArrays(serverCartArray, localStorageCartArray);

    //     // if server and local differ AND server cart is empty, send local to server
    //     if (diffB.length > 0 && serverCartData.total_items === 0) {
    //         fetch('cartSync.php', {
    //                 method: 'POST',
    //                 headers: {
    //                     'Content-Type': 'application/json'
    //                 },
    //                 body: JSON.stringify(diffB)
    //             })
    //             .then(response => response.json())
    //             .then(data => {
    //                 console.log('Success:', data);
    //             })
    //             .catch(error => {
    //                 console.error('Error:', error);
    //             });
    //     }
    //     // if both carts have items, merge differeces
    //     if (diffA.length > 0 && diffB.length >= 0) {
    //         const mergedData = [...serverCartArray, ...localStorageCartArray];
    //         localStorage.setItem('store-cart', JSON.stringify(mergedData));
    //     }
    // }
    // latest version in Github. gets server to local storage just fine, but breaks if there is nothing in local storage 
    // function syncCartWithServer() {
    //     var cartData = getCartFromLocalStorage();
    //     var cartDataArray = Object.entries(cartData);
    //     var cartServerData = <?php echo $cart->serializeCart(); ?>;
    //     var cartServerArray = Object.entries(cartServerData);
    //     howOldIsLocalStorage(cartData.timestamp);
    //     cartServerArray.forEach(serverItem => {
    //         if (!findItemByUid(cartDataArray, serverItem.add_item_uid)) {
    //             cartDataArray.push(serverItem);
    //         }
    //     })
    //     cartDataArray.forEach(dataItem => {
    //         if (!findItemByUid(cartServerArray, dataItem.add_item_uid)) {
    //             cartServerArray.push(dataItem)
    //         }
    //     })


    //     if (cartData.total_items == 0 && cartData.cart_total == 0 && cartServerData.total_items != 0) {
    //         console.log('Writing server data to local storage');
    //         setCartInLocalStorage(cartServerData);
    //         return
    //     } else if (cartData.total_items != 0 && cartServerData.total_items == 0 && howOldIsLocalStorage(cartData, timestamp) > 1440000) {
    //         fetch('cartSync.php', {
    //                 method: 'POST',
    //                 headers: {
    //                     'Content-Type': 'application/json'
    //                 },
    //                 body: JSON.stringify(cartServerArray)
    //             })
    //             .then(response => response.json())
    //             .then(data => {
    //                 console.log('Success:', data);
    //             })
    //             .catch((error) => {
    //                 console.error('Error: ', error);
    //             });
    //     } else if (cartServerData.total_items > 0) {
    //         setCartInLocalStorage(cartServerArray)
    //     }
    // }
    syncCartWithServer();
</script>
<div class="cart-slideout hidden" id="cart-slideout">
    <div class="cart-header-holder">
        <h1>Your Cart</h1>
    </div>
    <div class="cart-slide-row">
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
                                <!-- <td class='a-right'><i class="fa fa-trash" aria-hidden="true" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item"></i></td> -->
                                <td class='a-right'>
                                    <p aria-hidden="true" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item">🗑️</p>
                                </td>
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

                </tbody>
            </table>
        </div>
        <div class="slideoutButtonsHolder">
            <a href="./viewCart.php"><button class="cart-btn btn-primary">Go To Cart</button></a>
            <a href="./checkout.php"><button class="cart-btn btn-primary">Go to Checkout</button></a>
        </div>

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
        /* color: black !important; */
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

    .cart table tbody tr td {
        color: rgba(6, 6, 6, 1);
    }

    .slideoutButtonsHolder {
        display: flex;
        justify-content: space-evenly;
        margin-left: 5%;
        margin-right: 5%;
    }
</style>