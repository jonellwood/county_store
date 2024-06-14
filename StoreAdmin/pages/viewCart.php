<?php
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
    <title>View Cart</title>
    <meta charset='utf-8'>
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <script src=" https://code.jquery.com/jquery-3.6.1.js"></script>
    <script>
        function updateCartItem(obj, id) {
            $.get("cartAction.php", {
                action: "updateCartItem",
                id: id,
                qty: obj.value
            }, function(data) {
                if (data == 'ok') {
                    location.reload();
                } else {
                    alert('Cart update failed, please try again.');
                }
            });
        }

        function updateCartItemComment(obj, id) {
            console.log('id: ' + id);
            console.log('comment: ' + obj);
            $.get("cartAction.php", {
                action: "updateCartItemComment",
                id: id,
                comment: obj
            }, function(data) {
                if (data == 'ok') {
                    // location.reload();
                } else {
                    alert('Comment update failed, please try again');
                }
            })
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        <div class="row">
            <div class="cart">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-stiped cart">
                            <thead>
                                <tr>
                                    <th width="10%"></th>
                                    <th width="30%">Product</th>
                                    <th width="5%">Color</th>
                                    <th width="5%">Size</th>
                                    <th width="10%">Price</th>
                                    <th width="10%">Quantity</th>
                                    <th width="20%">Item Sub Total</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($cart->total_items() > 0) {
                                    // get cart items from session
                                    $cartItems = $cart->contents();
                                    foreach ($cartItems as $item) {
                                        $proImg = !empty($item["image"]) ? $item["image"] : 'product-images/demo-img.png';
                                ?>
                                        <tr>
                                            <td><img src="<?php echo $proImg; ?>" alt="..." width="100px"></td>
                                            <td><?php echo $item["name"]; ?> </td>
                                            <td><?php echo $item["color_id"] ?> </td>
                                            <td><?php echo $item["size_id"] ?> </td>
                                            <td><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2) . ' ' . CURRENCY; ?>
                                            </td>
                                            <td><input class="form-control" type="number" value="<?php echo $item["qty"]; ?>" onchange="updateCartItem(this, '<?php echo $item["rowid"]; ?>')" /></td>
                                            <td><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2) . ' ' . CURRENCY; ?>
                                            </td>
                                            <td><button class="btn btn-danger" onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;" title="Remove Item"><i class="fa fa-trash" aria-hidden="true"></i>
                                                    Remove </button> </td>
                                        </tr>
                                        <tr>

                                            <td><label for "line_item_comment" id="line_item_comment" class='tiny-text'>Comment
                                                    about <?php echo $item["name"]; ?> </label></td>
                                            <td><textarea name="line_item_comment[]" id="line_item_comment" placeholder="Please enter any comments regarding the above line item" cols="50" onchange="updateCartItemComment(this.value, '<?php echo $item["rowid"]; ?>')"></textarea>
                                            </td>
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
                                        <td><strong>Sub-Total</strong></td>
                                        <?php $subtotal = $cart->total() ?> <td>
                                            <?php echo CURRENCY_SYMBOL . number_format($subtotal, 2)  . ' ' . CURRENCY; ?>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><strong>Sales Tax:</strong>
                                            <?php $sales_tax = number_format(($cart->total() * .08), 2) ?>
                                        <td> <?php echo CURRENCY_SYMBOL . $sales_tax . ' ' . CURRENCY; ?>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><strong>Cart Total:</strong></td>
                                        <td><strong><?php echo CURRENCY_SYMBOL . number_format(($subtotal + $sales_tax), 2) . ' ' . CURRENCY ?>
                                            </strong></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col mb-2">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <a href="index.php#nav-container"><button class="btn btn-block btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                                    Shopping </button></a>
                        </div>
                        <div class="col-sm-12 col-md-6 text-right">
                            <?php if ($cart->total_items() > 0) { ?>
                                <a href="checkout.php"><button class="btn btn-primary" type="button"> Proceed to Checkout <i class="fa fa-arrow-right" aria-hidden="true"></i></button></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
<style>
    a {
        text-decoration: none;
    }

    .tiny-text {
        font-size: small;
    }
</style>