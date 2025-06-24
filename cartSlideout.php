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
//setCartInLocalStorage();


function getCartFromLocalStorage() {
    var cartData = localStorage.getItem('store-cart');
    if (cartData) {
        return JSON.parse(cartData);
    } else {
        setCartInLocalStorage();
    }
    // return;
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
    } else if (cartData.total_items != 0 && cartServerData.total_items == 0 && howOldIsLocalStorage(cartData
            .timestamp) > 1440000) {
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
syncCartWithServer();
</script>
<div class="cart-slideout" id="cart-slideout">
    <div class="cart-header-holder">
        <h1>Your Cart</h1>
    </div>
    <div class="cart-slide-row">
        <div class="cart-table-holder">
            <table class="cart table table-striped">
                <thead class="thead-dark">
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
                        unset($cartItems['total_logo_fees'], $cartItems['total_items'], $cartItems['cart_total']);
                        foreach ($cartItems as $item) {
                    ?>
                    <tr>
                        <td><?php echo $item["name"]; ?></td>
                        <td><?php echo $item["qty"]; ?> </td>
                        <td class='a-right'><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2); ?> </td>
                        <!-- <td class='a-right'><i class="fa fa-trash" aria-hidden="true" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item"></i></td> -->
                        <td class='a-right'>
                            <p aria-hidden="true"
                                onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;"
                                title="Remove Item">🗑️</p>
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
                        <td colspan="2"><strong>Sub-Total</strong></td>
                        <?php $subtotal = $cart->total() ?> <td colspan="2" class='a-right'>
                            <?php echo CURRENCY_SYMBOL . number_format($subtotal, 2) ?>
                        </td>
                    </tr>

                    <tr>

                        <td colspan="2"><strong>Sales Tax:</strong>
                            <?php $sales_tax = number_format(($cart->total() * .09), 2) ?>
                        <td colspan="2" class="a-right"> <?php echo CURRENCY_SYMBOL . $sales_tax ?>
                        </td>


                    </tr>
                    <tr>

                        <td colspan="2"><strong>Cart Total:</strong></td>
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
            <a href="./viewCart.php"><button class="btn btn-primary">View Cart Page</button></a>
            <a href="./checkout.php"><button class="btn btn-primary">Go to Checkout</button></a>
        </div>

    </div>
</div>