<?php
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init cart class
require_once 'Cart.class.php';
$cart = new Cart;

function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}

// set default redirect page
$redirectURL = 'index.php';

// process request based on requested action

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'addToCart' && !empty($_REQUEST['id'])) {
        $product_id = $_REQUEST['id'];
        $color_id = $_REQUEST['color_id'];
        $size_id = $_REQUEST['size_id'];
        $comment = strip_tags($_REQUEST['comment']);


        // fetch details from the database
        $sql = "SELECT * from products WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $db_id);
        $db_id = $product_id;
        $stmt->execute();
        $result = $stmt->get_result();
        $productRow = $result->fetch_assoc();

        $itemData = array(
            'id' => $productRow['product_id'],
            'image' => $productRow['image'],
            'name' => $productRow['name'],
            'price' => $productRow['price'],
            'qty' => 1,
            'color_id' => $color_id,
            'size_id' => $size_id,
            'comment' => $comment
        );

        // insert item into cart
        $insertItem = $cart->insert($itemData);

        // redirect to cart page
        $redirectURL = $insertItem ? 'viewCart.php' : 'index.php';
    } elseif ($_REQUEST['action'] == 'updateCartItem' && !empty($_REQUEST['id'])) {
        // update item data in cart
        $itemData = array(
            'rowid' => $_REQUEST['id'],
            'qty' => $_REQUEST['qty']
        );
        $updateItem = $cart->update($itemData);

        // return status
        echo $updateItem ? 'ok' : 'err';
        die;
        // if it all goes to hell delete from here down to the next elseif
        // this is going to add a comment to the line item 
    } elseif ($_REQUEST['action'] == 'updateCartItemComment' && !empty($_REQUEST['id'])) {
        $comment = $_REQUEST['comment'];
        $itemData = array(
            'rowid' => $_REQUEST['id'],
            'comment' => $comment
        );
        $updateItem = $cart->update($itemData);
        echo $updateItem ? 'ok' : 'err';
        die;
    } elseif ($_REQUEST['action'] === 'removeCartItem' && !empty($_REQUEST['id'])) {
        // remove item from cart
        $deleteItem = $cart->remove($_REQUEST['id']);

        // redirect
        $redirectURL = 'viewCart.php';
    } elseif ($_REQUEST['action'] == 'placeOrder' && $cart->total_items() > 0) {
        $redirectURL = 'checkout.php';
        if (isset($_POST['captcha_challenge']) && $_POST['captcha_challenge'] == $_SESSION['captcha_text']) {
            // store post data
            $_SESSION['postData'] = $_POST;

            $first_name = strip_tags($_POST['first_name']);
            $last_name = strip_tags($_POST['last_name']);
            $emp_number = strip_tags($_POST['emp_number']);
            $email = strip_tags($_POST['email']);
            $department = strip_tags($_POST['department']);
            $submitted_by = strip_tags($_POST['sub_number']);
            // $comment = strip_tags($comment);

            $errorMsg = '';
            if (empty($first_name)) {
                $errorMsg .= 'Please enter your first name. <br/>';
            }
            if (empty($last_name)) {
                $errorMsg .= 'Please enter your last name. <br/>';
            }
            if (empty('emp_number')) {
                $errorMsg .= 'Please enter your County Employee ID Number. <br/>';
            }
            if (empty('department')) {
                $errorMsg .= 'Please enter department. <br/>';
            }
            if (empty($errorMsg)) {
                // insert into the data base 
                $sql = "INSERT INTO customers (first_name, last_name, email, emp_id, department, created, modified) VALUES (?,?,?,?,?,NOW(),NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $db_first_name, $db_last_name, $db_email, $db_emp_number, $db_department);
                $db_first_name = $first_name;
                $db_last_name = $last_name;
                $db_email = $email;
                $db_emp_number = $emp_number;
                $db_department = $department;
                $insertCust = $stmt->execute();

                if ($insertCust) {
                    $custID = $stmt->insert_id;

                    // insert order information into the database

                    $sql = 'INSERT into orders (customer_id, grand_total, created, submitted_by ) VALUES (?,?,NOW(), ?)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ids", $db_customer_id, $db_grand_total, $db_submitted_by);
                    $db_customer_id = $custID;
                    $db_grand_total = ($cart->total() * 1.08);
                    $db_submitted_by = $submitted_by;

                    $insertOrder = $stmt->execute();

                    if ($insertOrder) {
                        $orderID = $stmt->insert_id;

                        // get cart items
                        $cartItems = $cart->contents();
                        // insert order items into database
                        if (!empty($cartItems)) {
                            $sql = "INSERT INTO order_details (order_id, product_id, quantity, size_id, color_id, status, item_price, line_item_total, comment) VALUES (?,?,?,?,?,?,?,?,?)";

                            $stmt = $conn->prepare($sql);
                            foreach ($cartItems as $item) {
                                $stmt->bind_param("iisssssss", $db_order_id, $db_product_id, $db_quantity, $db_size_id, $db_color_id, $db_status, $db_item_price, $db_line_item_total, $db_comment);
                                $db_order_id = $orderID;
                                $db_product_id = $item['id'];
                                $db_quantity = $item['qty'];
                                $db_size_id = $item['size_id'];
                                $db_color_id = $item['color_id'];
                                $db_status = 'Pending';
                                $db_item_price = $item['price'];
                                $db_line_item_total = (($db_quantity * $db_item_price) * 1.08);
                                $db_comment = $item['comment'];
                                $order_details_add = $stmt->execute();
                            }
                        } // break out of first IF statement before starting the next
                        // if ($order_details_add) {
                        //     $order_details_id = $stmt->insert_id;

                        //     // get comment(s) and add them to comments table 
                        //     if (!empty($cartItems)) {
                        //         $sql = "INSERT into comments(id, order_details_id, comment, submitted_by, order_id) VALUES (?,?,?,?,?)";

                        //         $stmt = $conn->prepare($sql);
                        //         foreach ($cartItems as $item) {
                        //             $stmt->bind_param("sissi", $db_uid, $db_order_details_id, $db_comment, $db_submitted_by, $db_order_id);
                        //             $db_uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(8));
                        //             $db_order_details_id = $order_details_id;

                        //             $db_submitted_by = $submitted_by;
                        //             $db_order_id = $orderID;
                        //             $stmt->execute();
                        //             $order_details_id = ($order_details_id + 1);
                        //         }
                        //     }
                        // }
                        // remove all items from cart
                        // }  I AM UNSURE OF WHERE THESE CLOSING BRACKETS GO IN RELATION TO CART->DESTROY and REDIRECT CALLS
                        $cart->destroy();
                        // redirect to order status page
                        $redirectURL = 'orderSuccess.php?id=' . base64_encode($orderID);
                    } else {
                        echo "FAILURE at inserting into order_details.";
                        $sessData['status']['type'] = 'error';
                        $sesData['status']['msg'] = 'Something went wrong. Restart your computer and try again 101';
                    }
                } else {
                    $sessData['status']['type'] = 'error';
                    $sessData['status']['msg'] = 'Something went wrong. Restart your computer and try again 102';
                }
            } else {
                $sesData['status']['type'] = 'error';
                $sesData['status']['msg'] = 'Something went wrong. Restart your computer and try again 103';
            }
        } else {
            $sessData['status']['type'] = 'error';
            $sessData['status']['msg'] = '<p>The Employee ID you entered does not match our records. Please try again.</p>' . $errorMsg;
        }
        // store status in session
        $_SESSION['sessData'] = $sessData;
    } else {
        //  $statusMsg .= "Inncorrect CAPTCHA</br>";
        echo "<div class='alert'>Incorrect CAPTCHA</div>";
    }
}
$conn->close();


// redirect to specific page
header("Location: $redirectURL");
exit();
