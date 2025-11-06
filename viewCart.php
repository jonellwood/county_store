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
include "./components/viewHead.php"
?>
<link rel="stylesheet" href="./style/global-variables.css">
<link rel="stylesheet" href="./style/viewCart-modern.css">

<script>
    function getCartItem(id) {
        const cart = <?php echo $cart->serializeCart(); ?>;
        const cartItem = cart[id];
        if (cartItem) {
            return cartItem;
        } else {
            console.error("Cart can not be retrieved")
        }
    }

    function updateSizeAndPriceData(event) {
        var selectElement = event.target;
        var selectedOption = selectElement.options[selectElement.selectedIndex];

        var hiddenPriceIdInput = document.getElementById('price_id')
        hiddenPriceIdInput.value = selectedOption.getAttribute('data-priceid');

        var hiddenPriceInput = document.getElementById('price')
        hiddenPriceInput.value = selectedOption.getAttribute('data-price');

        var hiddenSizeNameInput = document.getElementById('size_name')
        hiddenSizeNameInput.value = selectedOption.getAttribute('data-sizename');


    }

    function updateColorData(event) {
        var selectElement = event.target;
        var selectedOption = selectElement.options[selectElement.selectedIndex];

        var hiddenColorIdInput = document.getElementById('color_id')
        hiddenColorIdInput.value = selectedOption.getAttribute('data-colorid');

        var hiddenColorNameInput = document.getElementById('color_name')
        hiddenColorNameInput.value = selectedOption.getAttribute('data-colorname');
    }

    function updateLogo(val) {
        var hiddenLogoInput = document.getElementById('logo')
        hiddenLogoInput.value = val;
    }

    function updateDeptPatch(val) {
        var hiddenDeptInput = document.getElementById('deptPatchPlace')
        var hiddenLogoFeeInput = document.getElementById('logoFee')
        hiddenDeptInput.value = val;
        if (val == 'Left Sleeve') {
            hiddenLogoFeeInput.value = parseFloat(10.00)
        } else {
            hiddenLogoFeeInput.value = parseFloat(5.00)
        }
    }

    function formatColorValueForUrl(str) {
        var noSpaces = str.replace(/[\s/]/g, '');
        var lowercaseString = noSpaces.toLowerCase();
        return lowercaseString;
    }

    function getCartItemOptions(id, cartItem) {
        fetch(`./fetchProductDetails.php?id=${id}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error status: ${response.status}`);
                }
                return response.json();

            })
            .then((data) => {
                console.log('product-data-for-cart-item');
                console.log(data);
                //return data;


                var html = '';
                html += `
                    <form action='cartAction.php' method='post' id='editCartItem'>
                    <div class="editCartItemDiv">
                        <input type='hidden' name='id' value='${cartItem.rowid}' />
                        <input type='hidden' name='action' value='updateCartItem' />
                        <input type='hidden' name='color_id' id='color_id' value='${cartItem.color_id}' />
                        <input type='hidden' name='color_name' id='color_name' value='${cartItem.color_name}' />
                        <input type='hidden' name='size_id' id='size_id' value='${cartItem.size_id}' />
                        <input type='hidden' name='size_name' id='size_name' value='${cartItem.size_name}' />
                        <input type='hidden' name='logo' id='logo' value='${cartItem.logo}' />
                        <input type='hidden' name='logo_id' id='logo_id' value='${cartItem.logo_id}' />
                        <input type='hidden' name='deptPatchPlace' id='deptPatchPlace' value='${cartItem.deptPatchPlace}' />
                        <input type='hidden' name='price_id' id='price_id' value='${cartItem.price_id}' />
                        <input type='hidden' name='price' id='price' value='${cartItem.price}' />
                        <input type='hidden' name='logoFee' id='logoFee' value=${cartItem.logoFee} /> 

                        <fieldset>
                        <label for='editSize'>Edit Size</label>
                        <select name='size_id' id='editSize' onchange='updateSizeAndPriceData(event)'>
                            `;
                for (var i = 0; i < data['price_data'].length; i++) {
                    var isSelected = cartItem.size_id == data['price_data'][i].size_id ? 'selected' : '';

                    html += `
                                <option value="${data['price_data'][i].size_id}" ${isSelected === 'selected'? 'selected' : ''} data-priceid=${data['price_data'][i].price_id} data-sizename="${data['price_data'][i].size_name}" data-price=${data['price_data'][i].price}>
                                    ${data['price_data'][i].size_name}
                                </option>
                            `;
                }
                html += `
                        </select>
                        </fieldset>
                        <fieldset>
                        <label for='editSize'>Edit color</label>
                        <select name='color_id' id='editColor' onchange='updateColorData(event)'>
                        `;
                for (var j = 0; j < data['color_data'].length; j++) {
                    var isSelected = cartItem.color_id == data['color_data'][j].color ? 'selected' : '';

                    html += `
                                <option value="${data['color_data'][j].color_id}" ${isSelected === 'selected'? 'selected' : ''} data-colorid=${data['color_data'][j].color_id} data-colorname="${data['color_data'][j].color}" data-colorid="${data['color_data'][j].color_id}">
                                    ${data['color_data'][j].color}
                                </option>
                            `;
                }
                html += `
                        </select>
                        </fieldset>
                        <fieldset>
                        <label for='editLogo'>Edit Logo</label>
                        <select name='logo' id='editLogo' onchange='updateLogo(this.value)'>
                    `;
                for (var k = 0; k < data['logo_data'].length; k++) {
                    // var isSelected = cartItem.logo.replace(/_NO\.png$/, '.png') == data['logo_data'][k].image ?
                    //     'selected' : '';
                    var isSelected = cartItem.logo.replace(/_NO\.png$/, '.png').toLowerCase() === data['logo_data'][k]
                        .image.toLowerCase() ? 'selected' : '';

                    html += `
                            <option value="${data['logo_data'][k].image}" ${isSelected === 'selected'? 'selected' : ''}>
                                ${data['logo_data'][k].logo_name}
                            </option>
                            `;
                }
                html += `
                        </select>
                        </fieldset>
                        <fieldset>
                            <label for='deptPatchPlace'>Edit Dept Name</label>
                            <select name='deptPatchPlace' id='deptPatchPlace' onchange='updateDeptPatch(this.value)'>
                                <option value='No Dept Name' id='p1' ${cartItem.deptPatchPlace === 'No Dept Name'? 'selected' : ''}>No Dept Name</option>
                                <option value='Below Logo' id='p2' ${cartItem.deptPatchPlace === 'Below Logo'? 'selected' : ''}>Below Logo</option>
                                <option value='Left Sleeve' id='p3' ${cartItem.deptPatchPlace === 'Left Sleeve'? 'selected' : ''}>Left Sleeve</option>
                            </select>
                        </fieldset>

                        <fieldset>
                        <label for='editQty'>Quantity</label>
                        <input name='qty' id='editQty' type='number' value='${cartItem.qty}' min='1' max='100'>
                        </fieldset>
                        </div>
                        <div class='edit-cart-item-submit-btn-holder'>
                        <button class='btn cart-item-submit-btn' type='b'>
                        <span aria-hidden=”true”></span> 
                        <span class="sr-only">Submit</span>
                        </button>
                        </div>
                        </form>
                        `;
                document.getElementById('popover-edit-form-holder').innerHTML = html;



            })
            .catch((error) => {
                console.error('Error fetching product details:', error);
            });
    }


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

    function renderEdit(cartId) {
        console.log('CartId is ', cartId)
        const cartItem = getCartItem(cartId)
        console.log(cartItem);
        const itemOptions = getCartItemOptions(cartItem.id, cartItem)
    }

    function renderComment(cartId) {
        console.log('Comment id is :', cartId)
        var target = document.getElementById('add-comment-item-popover');
        var html = '';
        // <input type="hidden" name="action" value="updateCartItemComment">
        // <label for="comment"></label>
        html +=
            // <form class="comment-form" onsubmit=updateCartItemComment(this, ${cartId})>
            `
            <form class="comment-form" action="cartAction.php" method="post">
            <input type="hidden" name="action" value="updateCartItemComment">
            <input type="hidden" name="id" value="${cartId}">
            <textarea name="comment[]" id="comment" placeholder="Comment" rows="5" cols="50"></textarea>
            <br>
            <button class="button comment-form-btn" type="submit">Submit</button>
            </form>`;
        target.innerHTML = html;
    }

    function removeItem(cartId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch('cartAction.php?action=removeCartItem&id=' + cartId)
                .then(response => {
                    if (response.ok) {
                        localStorage.removeItem('store-cart');
                        // Force hard reload to prevent rendering issues
                        window.location.reload(true);
                    } else {
                        alert("Remove item failed!!!! 😲 ");
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                    alert('An error occurred while removing the item')
                })
        }
    }


    function renderCheckout(cart) {
        console.log('cart', cart);

        // Check if cart has items
        if (!cart || Object.keys(cart).length <= 3) {
            var html = '<img src="cart_empty.jpg" alt="Cart is empty" class="empty-cart-img" />';
            document.getElementById('items').innerHTML += html;
            return;
        }

        // Iterate through cart items (skip cart metadata)
        for (let cartId in cart) {
            // Skip metadata properties
            if (cartId === 'cart_total' || cartId === 'total_items' || cartId === 'total_logo_fees') {
                continue;
            }

            const item = cart[cartId];
            console.log("Cart Item:", item);

            var html = '';
            html += `
                <div>
                    <div class="active-items">
                        <div class="active-item" data-cartId="${item.rowid}">
                            <h2 class="product-title">${item.code} - ${item.name} <span class="price-block">${makeDollar(item.price)}</span></h2>
                            <div class="details">
                                <div class="item-content">
                                    <div class="item-content-inner">
                                        <div class="image-wrapper">
                                            <img src="${item.image}" alt="${item.name}" class="product-image" />
                                        </div>
                                        <div class="item-content-inner-inner">
                                            <div class="content-tail">
                                                <div> 
                                                    <p>Size: ${item.size_name}</p>
                                                    <p>Color: ${item.color_name}</p>
                                                    <p>Qty: ${item.qty}</p>
                                                    <p>Dept Name: ${item.deptPatchPlace || 'N/A'}</p>
                                                </div>
                                                <div class="logo-holder">
                                                    ${item.id !== 105 ? `<img src="${item.logo}" alt="logo" class="logo-pict">` : ''}
                                                </div>
                                            </div>
                                            
                                            <div class="item-content-footer">
                                                <button class='btn btn-danger' value="${item.rowid}" onclick="removeItem(this.value)">Delete</button>
                                                <button class='btn btn-info' value="${item.rowid}" onclick="renderEdit(this.value)" popovertarget="edit-cart-item" popovertargetaction="show">Edit</button>
                                                <button class='btn btn-warning' value="${item.rowid}" onclick="renderComment(this.value)" popovertarget="add-comment" popovertargetaction="show">Comment</button>
                                            </div>
                                            ${item.comment ? `
                                                <div class="item-comment">
                                                    <span class="item-comment-label">Note:</span>
                                                    <p class="item-comment-text">${item.comment}</p>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('items').innerHTML += html;
        }
    }

    function logCart() {
        let cartItems = JSON.parse(localStorage.getItem('store-cart')) || {};
        console.log('cartItems');
        console.log(cartItems);
    }
    // logCart();
</script>
</head>

<body>

    <div class="container">
        <div id="items" class="items"></div>
        <div class="items">
            <table class="checkout-summary-table">
                <tbody>
                    <tr>
                        <th>Total Items</th>
                        <td class="amount-column"><?php echo $cart->total_items() ?></td>
                    </tr>
                    <tr>
                        <th>Sub Total</th>
                        <td class="amount-column"><?php echo CURRENCY_SYMBOL . (number_format(($cart->total()), 2))  ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Logo Fees</th>
                        <td class="amount-column">
                            <?php echo CURRENCY_SYMBOL . (number_format(($cart->total_logo_fees()), 2))  ?></td>
                    </tr>
                    <tr>
                        <th>Total Tax</th>
                        <td class="amount-column"><?php
                                                    $totalWithFees = $cart->total() + $cart->total_logo_fees();
                                                    $taxes = $totalWithFees * 0.09;
                                                    echo CURRENCY_SYMBOL . (number_format(($taxes), 2))
                                                    ?></td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td class="amount-column">
                            <?php echo CURRENCY_SYMBOL . (number_format(($cart->total() + $cart->total_logo_fees() + $taxes), 2))  ?>
                        </td>
                    </tr>
                </tbody>
            </table>


            <?php if ($cart->total_items() > 0) { ?>
                <div class="button-holder">
                    <a href="checkout.php" class="btn btn-success">
                        Checkout
                    </a>
                </div>
            <?php } ?>
            <div class="comments-stream" id="comments-stream">Comment:</div>
        </div>
        </?php include "viewCartDump.php" ?>

        <div class="bottom-buttons-holder d-flex justify-content-between mx-5">
            <div>
                <a href="<?php echo $_SESSION['GOBACK'] ?>"><button class="btn btn-primary" type="button"> Continue
                        Shopping
                    </button></a>
            </div>
            <div>
                <?php if ($cart->total_items() > 0) { ?>
                    <a href="checkout.php"><button class="btn btn-success" type="button"> Proceed to Checkout </button></a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="edit-cart-item" popover>
        <div id="cart-item-edit-details" class="cart-item-edit-details">
            <div class="popover-btn-holder">
                <button popovertarget="edit-cart-item" popovertargetaction="hide" class="btn-close ms-2 mb-1"
                    role="button">
                    <span aria-hidden="true"></span>
                </button>

            </div>
            <div class="popover-desc-text-holder">
                <p class="popover-desc-text">Make changes to the color , size, quantity, logo, or dept name for this
                    cart item.</p>

            </div>
            <div class="popover-edit-form-holder" id="popover-edit-form-holder">

            </div>

            <!-- This is where the details for the cart item will render -->
            <div id="edit-cart-item-popover"></div>
        </div>
    </div>
    <div id="add-comment" popover=manual>
        <div class="popover-btn-holder">
            <button class="btn-close ms-2 mb-1" popovertarget="add-comment" popovertargetaction="hide">
                <span aria-hidden=”true”></span>

            </button>
        </div>
        <div class="popover-desc-text-holder">
            <p class="popover-desc-text">Add your comment.</p>
            <!-- This is where the form to add the comment will render -->
            <div id="add-comment-item-popover"></div>
        </div>
    </div>


    <?php include "footer.php" ?>
    <script>
        renderCheckout(<?php echo $cart->serializeCart() ?>);
    </script>

</body>


</html>