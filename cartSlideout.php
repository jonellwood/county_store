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

    // Function to update cart totals with logo fees from localStorage
    function updateCartTotals() {
        var cartData = getCartFromLocalStorage();
        if (cartData && cartData.total_logo_fees) {
            var logoFees = parseFloat(cartData.total_logo_fees) || 0;
            var subtotal = <?php echo $cart->total(); ?>;
            var taxRate = 0.09;
            var salesTax = (subtotal + logoFees) * taxRate;
            var cartTotal = subtotal + logoFees + salesTax;

            // Update the display elements with null checks
            const logoFeesEl = document.getElementById('logo-fees-display');
            const salesTaxEl = document.getElementById('sales-tax-display');
            const cartTotalEl = document.getElementById('cart-total-display');
            const logoFeesRow = document.querySelector('.logo-fees-row');

            if (logoFeesEl) logoFeesEl.textContent = logoFees.toFixed(2);
            if (salesTaxEl) salesTaxEl.textContent = salesTax.toFixed(2);
            if (cartTotalEl) cartTotalEl.textContent = cartTotal.toFixed(2);

            // Show the logo fees row
            if (logoFeesRow) logoFeesRow.style.display = logoFees > 0 ? 'table-row' : 'none';
        } else {
            // Hide logo fees row if no logo fees
            const logoFeesRow = document.querySelector('.logo-fees-row');
            if (logoFeesRow) logoFeesRow.style.display = 'none';
        }
    }

    // Update totals when the slideout loads
    document.addEventListener('DOMContentLoaded', function() {
        updateCartTotals();
    });

    // Also update when the cart slideout is opened
    document.addEventListener('toggle', function(e) {
        if (e.target.id === 'cart-slideout' && e.target.matches(':popover-open')) {
            updateCartTotals();
        }
    });
</script>

<style>
    /* Modern Cart Slideout Styling */
    .cart-slideout {
        background: var(--bg-elevated);
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border-light);
        overflow: hidden;
        font-family: var(--font-family-base);
    }

    .cart-header-holder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px 24px;
        margin: 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-header-holder h1 {
        color: var(--color-white);
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.5px;
    }

    .cart-header-holder .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--color-white);
        font-size: 18px;
        font-weight: bold;
    }

    .cart-header-holder .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .cart-slide-row {
        padding: 0;
    }

    .cart-table-holder {
        padding: 0;
        margin: 0;
    }

    .cart-slideout .table {
        margin: 0;
        border: none;
        background: transparent;
    }

    .cart-slideout .thead-dark {
        background: var(--bg-surface);
        border-bottom: 2px solid var(--border-light);
    }

    .cart-slideout .thead-dark th {
        background: var(--bg-surface);
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.875rem;
        padding: 16px 20px;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 0;
    }

    .cart-slideout tbody tr {
        border-bottom: 1px solid var(--border-light);
        transition: background-color 0.2s ease;
    }

    .cart-slideout tbody tr:hover {
        background-color: var(--bg-hover);
    }

    .cart-slideout tbody tr:last-child {
        border-bottom: none;
    }

    .cart-slideout td {
        color: var(--text-primary) !important;
        padding: 16px 20px;
        border: none;
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .cart-slideout .start-totals td,
    .cart-slideout .logo-fees-row td {
        background: var(--bg-surface);
        border-top: 2px solid var(--border-light);
        font-weight: normal;
        font-style: italic;
        font-size: 1rem;
    }

    /* Sales Tax row styling to match other summary rows */
    .cart-slideout tbody tr:nth-last-child(2) td {
        background: var(--bg-surface);
        border-top: 2px solid var(--border-light);
        font-weight: normal;
        font-style: italic;
        font-size: 1rem;
    }

    .cart-slideout .cart-total-row td {
        background: var(--bc-dark-green);
        color: var(--color-white) !important;
        font-size: 1.1rem;
        font-weight: bold;
        font-style: normal;
        border-top: 3px solid var(--bc-dark-green);
        border-radius: 0 !important;
    }

    .cart-slideout .cart-total-row td:first-child,
    .cart-slideout .cart-total-row td:last-child {
        border-radius: 0 !important;
    }

    .cart-slideout .logo-fees-row {
        display: none;
        /* Hidden by default, shown via JavaScript when logo fees exist */
    }

    .a-right {
        text-align: right;
    }

    /* Delete Button Styling */
    .btn-delete {
        background: var(--color-danger);
        border: none;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(255, 71, 87, 0.2);
    }

    .btn-delete:hover {
        filter: brightness(0.9);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 71, 87, 0.3);
    }

    .btn-delete:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(255, 71, 87, 0.3);
    }

    /* Buttons Container */
    .slideoutButtonsHolder {
        padding: 24px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        background: var(--bg-surface);
        border-top: 1px solid var(--border-light);
        width: 100%;
    }

    .slideoutButtonsHolder .btn {
        flex: 1;
        padding: 12px 20px;
        font-weight: 600;
        border-radius: 8px;
        border: 2px solid transparent;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-align: center;
        display: inline-block;
    }

    .slideoutButtonsHolder .btn-outline-secondary {
        background: var(--bg-elevated);
        border-color: var(--border-dark);
        color: var(--text-secondary);
    }

    .slideoutButtonsHolder .btn-outline-secondary:hover {
        background: var(--text-secondary);
        color: var(--color-white);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
    }

    .slideoutButtonsHolder .btn-success {
        background: var(--color-success);
        border-color: var(--color-success);
        color: var(--color-white);
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
    }

    .slideoutButtonsHolder .btn-success:hover {
        filter: brightness(0.9);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .slideoutButtonsHolder a {
        text-decoration: none;
    }

    /* Empty Cart Styling */
    .cart-slideout tbody tr td p {
        text-align: center;
        color: var(--text-secondary);
        font-style: italic;
        margin: 20px 0;
        font-size: 1rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .cart-slideout {
            border-radius: 12px;
            margin: 10px;
        }

        .cart-header-holder {
            padding: 16px 20px;
        }

        .cart-header-holder h1 {
            font-size: 1.3rem;
        }

        .cart-slideout .thead-dark th {
            padding: 12px 16px;
            font-size: 0.8rem;
        }

        .cart-slideout td {
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .slideoutButtonsHolder {
            padding: 20px;
            flex-direction: column;
        }

        .slideoutButtonsHolder .btn {
            margin-bottom: 8px;
        }
    }

    @media (max-width: 480px) {

        .cart-slideout .thead-dark th:nth-child(2),
        .cart-slideout td:nth-child(2) {
            display: none;
        }

        .cart-slideout .thead-dark th:first-child {
            width: 60%;
        }

        .cart-slideout .thead-dark th:nth-child(3) {
            width: 25%;
        }

        .cart-slideout .thead-dark th:last-child {
            width: 15%;
        }
    }
</style>

<div class="cart-slideout" id="cart-slideout">
    <div class="cart-header-holder">
        <h1>Your Cart</h1>
        <button popovertarget="cart-slideout" popovertargetaction="hide" class="btn-close">
            <span aria-hidden="true">&times;</span>
        </button>
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
                                    <button class="btn-delete"
                                        onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;"
                                        title="Remove Item" aria-label="Remove Item">
                                        🗑️
                                    </button>
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

                        <tr class="logo-fees-row">
                            <td colspan="2"><strong>Logo Fees:</strong></td>
                            <td colspan="2" class="a-right logo-fees-amount">
                                $<span id="logo-fees-display">0.00</span>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2"><strong>Sales Tax:</strong></td>
                            <td colspan="2" class="a-right">
                                $<span id="sales-tax-display"><?php echo number_format(($cart->total() * .09), 2) ?></span>
                            </td>
                        </tr>

                        <tr class="cart-total-row">
                            <td colspan="2"><strong>Cart Total:</strong></td>
                            <td colspan="2" class="a-right">
                                <strong>$<span id="cart-total-display"><?php echo number_format(($subtotal + ($cart->total() * .09)), 2) ?></span></strong>
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>
        </div>
        <div class="slideoutButtonsHolder">
            <a href="./viewCart.php"><button class="btn btn-outline-secondary">View Cart Page</button></a>
            <a href="./checkout.php"><button class="btn btn-success">Go to Checkout</button></a>
        </div>

    </div>
</div>