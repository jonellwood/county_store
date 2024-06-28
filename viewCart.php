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
        // This function updates an item in the shopping cart back in 1989
        // function updateCartItem(obj, id) {
        //     // Get the current details of the item via AJAX
        //     $.get("cartAction.php", {
        //             action: "updateCartItem",
        //             id: id,
        //             qty: obj.value
        //         },
        //         // Catch the response and act accordingly
        //         function(data) {
        //             if (data == 'ok') {
        //                 // If data is ok, reload the page
        //                 location.reload();
        //             } else {
        //                 // Else alert user that the update failed
        //                 alert('Cart update failed, please try again.');
        //             }
        //         });
        // }

        // this is the same function in Vanilla JS
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


        // This function is used to update cart item comment back when Ice T was cool and Ice Cube was a rapper
        // id: holds the ID of the item whose comment is being updated 
        // obj: holds the new comment for the item 
        // function updateCartItemComment(obj, id) {
        //     // Logging the received parameters' values in console
        //     // console.log('id: ' + id);
        //     // console.log('comment: ' + obj);

        //     // Requesting the "cartAction.php" page via AJAX
        //     // TODO convert this to native fetch and either use the if statement or dump it
        //     $.get("cartAction.php", {
        //             action: "updateCartItemComment",
        //             id: id,
        //             comment: obj
        //         },
        //         // Callback that runs if the request succeeds
        //         function(data) {
        //             // If the response is 'ok', indicating success
        //             if (data == 'ok') {
        //                 // Do something (e.g. reload)
        //                 // location.reload(); // uncomment this line to execute this logic
        //             } else {
        //                 // Show alert if update failed
        //                 alert('Comment update failed, please try again');
        //             }
        //         })
        //}
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
    </script>
</head>

<body>
    <?php include "./components/slider.php" ?>
    <div class="spacer23"> - </div>
    <div class="container">
        <!-- <div class="viewcart">
            </?php
            echo "<pre>";
            var_dump($cart->contents());
            echo "<pre>";
            ?>
        </div> -->
        <!-- <div class="row big-row"> -->
        <table>
            <thead>
                <tr>
                    <!-- <th width="1%"></th>
                        <th width="30%">Product</th>
                        <th width="9%">Logo</th>
                        <th width="5%">Color</th>
                        <th width="5%">Size</th>
                        <th width="10%">Price</th>
                        <th width="10%">Quantity</th>
                        <th width="20%">Item Sub Total</th>
                        <th width="1%"></th> -->
                    <th></th>
                    <th>Product</th>
                    <th>Logo</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Item Sub Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php

                if ($cart->total_items() > 0) {
                    // get cart items from session
                    $cartItems = $cart->contents();
                    // var_dump($cartItems);
                    foreach ($cartItems as $item) {
                        $sizeSql = "SELECT size_name from uniform_orders.sizes_new WHERE size_id = $item[size_id]";
                        $sizeStmt = $conn->prepare($sizeSql);
                        $sizeStmt->execute();
                        $sizeResult = $sizeStmt->get_result();
                        while ($sizeRow = $sizeResult->fetch_assoc()) {
                            // var_dump($sizeRow);


                ?>
                            <tr>
                                <!-- <td><img src="</?php echo $proImg; ?>" alt="..." width="100px"></td> -->
                                <td><img src="<?php echo $item["image"]; ?>" alt="..." width="100px" class="little-prod-img"></td>
                                <td><?php echo $item["name"]; ?>
                                    <hr> <?php echo $item["code"]; ?>
                                </td>
                                <td><span><img src="<?php echo $item['logo'] ?>" alt="bc logo" id="cart-logo-img">
                                        <p class='dept-patch-info'><b>Department Name
                                                Placement:</b> <?php echo $item["deptPatchPlace"] ?></p>
                                    </span></td>
                                <td><?php echo $item["color_id"] ?> </td>
                                <!-- <td><//?php echo $item["size_id"] ?> </td> -->
                                <td><?php echo $item["size_name"] ?> </td>
                            <?php } ?>
                            <td><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2); ?>
                            </td>
                            <td><input class="form-control" type="number" min="1" value="<?php echo $item["qty"]; ?>" onchange="updateCartItem(this, '<?php echo $item["rowid"]; ?>')" /></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2); ?>
                            </td>
                            <td><button class="button remove" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item"><i class="fa fa-trash" aria-hidden="true"></i>
                                    Remove </button> </td>
                            </tr>
                            <tr>

                                <td colspan="2"><label for "line_item_comment" id="line_item_comment" class='tiny-text'>Comment
                                        about <?php echo $item["name"]; ?> </label></td>
                                <td><textarea name="line_item_comment[]" id="line_item_comment" placeholder="Please enter any comments regarding the above line item" cols="50" onchange="updateCartItemComment(this.value, '<?php echo $item["rowid"]; ?>')"></textarea>
                                </td>
                                <td colspan=2>You said:</td>
                                <td colspan=4><?php echo $item["comment"] ?></td>

                            </tr>
                        <?php }
                } else { ?>
                        <tr>
                            <td colspan="6">
                                <p>Your cart is empty</p>
                            </td>
                        <?php } ?>
                        <?php if ($cart->total_items() > 0) { ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"><strong>Sub-Total</strong></td>
                            <?php $subtotal = $cart->total() ?> <td colspan="2">
                                <?php echo CURRENCY_SYMBOL . number_format($subtotal, 2); ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"><strong>Sales Tax:</strong>
                                <?php $sales_tax = number_format(($cart->total() * .09), 2) ?>
                            <td colspan="2"> <?php echo CURRENCY_SYMBOL . $sales_tax; ?>
                            </td>
                            <!-- <td></td> -->
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"><strong>Cart Total:</strong></td>
                            <td colspan="2">
                                <strong><?php echo CURRENCY_SYMBOL . number_format(($subtotal + $sales_tax), 2) ?>
                                </strong>
                            </td>
                            <!-- <td></td> -->
                            <td></td>
                        </tr>
                    <?php } ?>
            </tbody>
        </table>
        <div class="col mb-2">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <!-- <a href="index.php"><button class="btn btn-block btn-secondary" type="button"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                                    Shopping </button></a> -->
                    <a href="<?php echo $_SESSION['GOBACK'] ?>"><button class="btn btn-block btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                            Shopping </button></a>
                </div>
                <div class="col-sm-12 col-md-6 text-right">
                    <?php if ($cart->total_items() > 0) { ?>
                        <a href="checkout.php"><button class="btn btn-primary" type="button"> Proceed to Checkout <i class="fa fa-arrow-right" aria-hidden="true"></i></button></a>
                    <?php } ?>
                </div>
            </div>
            <!-- </ /div class="alert-warning">NOTE: Cart will empty after 24 minutes of inactivity -->
        </div>
        <!-- </div> -->
    </div>
    </div>
    <?php include "cartSlideout.php" ?>
    <?php include "footer.php" ?>
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
        margin-left: 20px;
        margin-right: 20px;
        /* width: 100%; */
        /* width: 100vw; */
    }


    table {
        color: aliceblue;
        margin-bottom: 20px;
        background-color: #00000099;
        padding: 10px;

        thead tr th {
            font-size: larger;
            text-align: center;
            border-bottom: 1px dotted aliceblue;
        }

        tbody tr td {
            padding: 5px;
        }
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
</style>