<?php
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init cart class
include_once 'Cart.class.php';
$cart = new Cart;

$empNumber = $_SESSION['empNumber'];
include_once "functions/setFiscalYear.php";

$sql = "SELECT comm_emps.empName,
comm_emps.empNumber,
ifnull(comm_emps.fy_budget, NULL) as fy_budget,
ifnull(SUM(ord_ref.line_item_total), 0.00) total
FROM uniform_orders.comm_emps
LEFT JOIN ord_ref ON ord_ref.emp_id = comm_emps.empNumber
-- AND ord_ref.created BETWEEN '2022-07-01T00:00:00+02:00' AND '2023-06-30T23:59:59+02:00'
AND ord_ref.created BETWEEN '$db_fystart' AND '$db_fyend'
WHERE comm_emps.empNumber = $empNumber
GROUP BY comm_emps.empNumber
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $empSpending = $row['total'];
        $empBudget = $row['fy_budget'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Cart</title>
    <meta charset='utf-8'>
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/comm-favicon.ico">
    <script src=" https://code.jquery.com/jquery-3.6.1.js"></script>
    <script>
    // This function updates an item in the shopping cart
    function updateCartItem(obj, id) {
        // Get the current details of the item via AJAX
        $.get("cartAction.php", {
                action: "updateCartItem",
                id: id,
                qty: obj.value
            },
            // Catch the response and act accordingly
            function(data) {
                if (data == 'ok') {
                    // If data is ok, reload the page
                    location.reload();
                } else {
                    // Else alert user that the update failed
                    alert('Cart update failed, please try again.');
                }
            });
    }


    // This function is used to update cart item comment
    // id: holds the ID of the item whose comment is being updated 
    // obj: holds the new comment for the item 
    function updateCartItemComment(obj, id) {
        // Logging the received parameters' values in console
        console.log('id: ' + id);
        console.log('comment: ' + obj);

        // Requesting the "cartAction.php" page via AJAX
        $.get("cartAction.php", {
                action: "updateCartItemComment",
                id: id,
                comment: obj
            },
            // Callback that runs if the request succeeds
            function(data) {
                // If the response is 'ok', indicating success
                if (data == 'ok') {
                    // Do something (e.g. reload)
                    // location.reload(); // uncomment this line to execute this logic
                } else {
                    // Show alert if update failed
                    alert('Comment update failed, please try again');
                }
            })
    }
    </script>
</head>

<body>
    <div class="container">
        <h1><?php echo ($_SESSION['empName']) ?>'s Shopping Cart</h1>

        <span class='comm-data-holder'>
            <p>Your current fiscal year budget is $
                <?php echo number_format(($empBudget), 2) ?></p>
            <p>Year-to-date you have spent $ <?php echo number_format($empSpending, 2)  ?></p>

            <?php
            $currentCartTotal = ($cart->total() * 1.09);
            $delta = (($currentCartTotal + $empSpending) - $empBudget);
            if ($delta <= 0) {
                echo '<style>
                    .delta{
                        display: none;
                    }
                </style>';
            }
            if ($delta >= 0) {
                echo '<style>
                .go-buttons{
                        display: none;
                    }
                </style>';
            }


            ?>
            <p class='delta'>This request will put you $ <?php echo number_format($delta, 2) ?> over budget</p>
        </span>

        <div class="row">
            <div class="cart">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped cart">
                            <thead>
                                <tr>
                                    <th width="1%"></th>
                                    <th width="30%">Product</th>
                                    <th width="18%">Logo</th>
                                    <th width="5%">Color</th>
                                    <th width="5%">Size</th>
                                    <th width="10%">Price</th>
                                    <th width="10%">Quantity</th>
                                    <th width="20%">Item Sub Total</th>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($cart->total_items() > 0) {
                                    // get cart items from session
                                    $cartItems = $cart->contents();
                                    // var_dump($cartItems);
                                    foreach ($cartItems as $item) {
                                        $proImg = !empty($item["image"]) ? $item["image"] : 'product-images/demo-img.png';
                                        $sizeSql = "SELECT size from uniform_orders.sizes WHERE size_id = $item[size_id]";
                                        $sizeStmt = $conn->prepare($sizeSql);
                                        $sizeStmt->execute();
                                        $sizeResult = $sizeStmt->get_result();
                                        while ($sizeRow = $sizeResult->fetch_assoc()) {
                                            // var_dump($sizeRow);


                                ?>
                                <tr>
                                    <td><img src="<?php echo $proImg; ?>" alt="..." width="100px"></td>
                                    <td><?php echo $item["name"]; ?>
                                        <hr> <?php echo $item["code"]; ?>
                                    </td>
                                    <td>
                                        <span>
                                            <img src="<?php echo $item['logo'] ?>" alt="bc logo" id="logo-img">
                                            <p class='dept-patch-info'><b>Department Name
                                                    Placement:</b> <?php echo $item["deptPatchPlace"] ?></p>
                                        </span>
                                    </td>
                                    <td><?php echo $item["color_id"] ?> </td>
                                    <!-- <td><//?php echo $item["size_id"] ?> </td> -->
                                    <td><?php echo $sizeRow["size"] ?> </td>
                                    <?php } ?>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2) . ' ' . CURRENCY; ?>
                                    </td>
                                    <td><input class="form-control" type="number" min="1"
                                            value="<?php echo $item["qty"]; ?>"
                                            onchange="updateCartItem(this, '<?php echo $item["rowid"]; ?>')" /></td>
                                    <td><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2) . ' ' . CURRENCY; ?>
                                    </td>
                                    <td><button class="btn btn-danger"
                                            onclick="return confirm('Are you sure to remove cart item?')?window.location.href='cartAction.php?action=removeCartItem&id=<?php echo $item["rowid"]; ?>':false;"
                                            title="Remove Item"><i class="fa fa-trash" aria-hidden="true"></i>
                                            Remove </button> </td>
                                </tr>
                                <tr>

                                    <td colspan="2"><label for "line_item_comment" id="line_item_comment"
                                            class='tiny-text'>Comment
                                            about <?php echo $item["name"]; ?> </label></td>
                                    <td><textarea name="line_item_comment[]" id="line_item_comment"
                                            placeholder="Please enter any comments regarding the above line item"
                                            cols="50"
                                            onchange="updateCartItemComment(this.value, '<?php echo $item["rowid"]; ?>')"></textarea>
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
                                    <!-- <td></td> -->
                                    <td colspan="2"><strong>Sub-Total</strong></td>
                                    <?php $subtotal = $cart->total() ?> <td colspan="2">
                                        <?php echo CURRENCY_SYMBOL . number_format($subtotal, 2)  . ' ' . CURRENCY; ?>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <!-- <td></td> -->
                                    <td colspan="2"><strong>Sales Tax:</strong>
                                        <?php $sales_tax = number_format(($cart->total() * .09), 2) ?>
                                    <td colspan="2"> <?php echo CURRENCY_SYMBOL . $sales_tax . ' ' . CURRENCY; ?>
                                    </td>
                                    <!-- <td></td> -->
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <!-- <td></td> -->
                                    <td colspan="2"><strong>Cart Total:</strong></td>
                                    <td colspan="2">
                                        <strong><?php echo CURRENCY_SYMBOL . number_format(($subtotal + $sales_tax), 2) . ' ' . CURRENCY ?>
                                        </strong>
                                    </td>
                                    <!-- <td></td> -->
                                    <td></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col mb-2">
                    <div class="row go-buttons">
                        <div class="col-sm-12 col-md-6">
                            <a href="products-by-communications.php"><button class="btn btn-block btn-secondary"
                                    type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                                    Shopping </button></a>
                            <!-- <a href="<//?php echo $_SERVER['HTTP_REFERER'] ?>"><button class="btn btn-block btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                                    Shopping </button></a> -->
                        </div>
                        <div class="col-sm-12 col-md-6 text-right">
                            <?php if ($cart->total_items() > 0) { ?>
                            <a href="comm-checkout.php"><button class="btn btn-primary" type="button"> Proceed to
                                    Checkout <i class="fa fa-arrow-right" aria-hidden="true"></i></button></a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="alert-warning">NOTE: Cart will empty after 24 minutes of inactivity</div>
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

#logo-img {
    width: 50px;
}

.comm-data-holder {
    display: flex;
    justify-content: space-between;
    font-size: 1.25em;
    padding: 10px;
    color: white;
    border-top: 1px solid white;
    border-bottom: 1px solid white;
}

.delta {
    color: red;
    font-weight: 500;
    animation: blinker 1s linear infinite;
}


@keyframes blinker {
    50% {
        opacity: 0;
    }
}

#logo-img {
    /* margin-right: 15px; */
    /* margin-left: 15px; */
    transition: all .2s ease-in-out;
}

#logo-img:hover {
    transform: scale(3.5);
    border: 1px solid var(--bc-grey);
    border-radius: 5px;

}
</style>