<?php
require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init cart class
// error_log(print_r($_REQUEST, true));
require_once 'Cart.class.php';
$cart = new Cart;
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//session_start();
// Function to escape a given string

function html_escape($string)
{
    // Replace special characters with HTML entities
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}
// set default redirect page
$redirectURL = 'index.php';
// process request based on requested action

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'addToCart' && !empty($_REQUEST['product_id'])) {
        $product_id = filter_var($_REQUEST['product_id'], FILTER_VALIDATE_INT);
        if (!$product_id) {
            error_log(print_r('product_id wrong type', true));
            $product_id = intval($product_id);
        }
        $name = $_REQUEST['name'];
        $productPrice = $_REQUEST['productPrice'];
        $itemQuantity = $_REQUEST['itemQuantity'];
        $add_item_uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(16));
        $code = $_REQUEST['code'];
        $color_id = $_REQUEST['hidden_color_id'];
        $color_name = $_REQUEST['color_name'];
        $price_id = $_REQUEST['size-price-id'];
        $logoId = $_REQUEST['logo'];
        $selectedLogo = $_REQUEST['logo-url'];
        $deptPatchPlace = $_REQUEST['deptPatchPlace'];
        $logoFee = $_REQUEST['logoCharge'];
        $logo_upCharge = $_REQUEST['logo_upCharge'];
        $logoFee = ($logoFee + $logo_upCharge);
        $size_id = $_REQUEST['size_id'];
        $size_name = $_REQUEST['size_name'];
        $image = $_REQUEST['image-url'];
        if (isset($_REQUEST['comment'][0])) {
            $comment = strip_tags($_REQUEST['comment'][0]);
        } else {
            $comment = '';
        }
        // $comment = 'No Comment Yet';
        $tax = (floatval($productPrice + $logoFee) * .09);
        $fy = $_REQUEST['fy'];


        $itemData = array(
            'id' => $product_id,
            'name' => $name,
            'price' => $productPrice,
            'qty' => $itemQuantity,
            'add_item_uid' => $add_item_uid,
            'image' => $image,
            'name' => $name,
            'code' => $code,
            // 'nologoPrice' => $actualPrice,
            'logoFee' => $logoFee,
            'tax' => $tax,
            'price_id' => $price_id,
            'color_id' => $color_id,
            'color_name' => $color_name,
            'size_id' => $size_id,
            'size_name' => $size_name,
            'comment' => $comment,
            'logo' => $selectedLogo,
            'deptPatchPlace' => $deptPatchPlace,
            'fy' => $fy
        );

        // insert item into cart
        $insertItem = $cart->insert($itemData);
        error_log(print_r($insertData, true));
        if (!$insertItem) {
            error_log('Failed to insert item into cart');
        } else {
            error_log('Successfully inserted item into cart with ID: ' . $insertItem);
        }
        $redirectURL = $insertItem ? $_SERVER['HTTP_REFERER'] : 'index.php';
    } elseif ($_REQUEST['action'] == 'updateCartItem' && !empty($_REQUEST['id'])) {
        // update item data in cart
        $itemData = array(
            'rowid' => $_REQUEST['id'],
            'color_id' => $_REQUEST['color_id'], // this value is actually the string of the name .... but here we are ..
            'color_name' => $_REQUEST['color_name'],
            'size_id' => $_REQUEST['size_id'],
            'size_name' => $_REQUEST['size_name'],
            'logo' => $_REQUEST['logo'],
            'deptPatchPlace' => $_REQUEST['deptPatchPlace'],
            'price_id' => $_REQUEST['price_id'],
            'price' => $_REQUEST['price'],
            'logoFee' => $_REQUEST['logoFee'],
            'qty' => $_REQUEST['qty'],

        );
        $updateItem = $cart->update($itemData);
        error_log(print_r($updateItem, true));
        // return status
        // $redirectURL = $updateItem ? $_SERVER['HTTP_REFERER'] : 'index.php';
        $redirectURL = $updateItem ? 'viewCart.php' : 'index.php';
        // echo $updateItem ? 'ok' : 'err';
        header("Location: $redirectURL");
        exit;

        // if it all goes to hell delete from here down to the next elseif
        // this is going to add a comment to the line item 
    } elseif ($_REQUEST['action'] == 'updateCartItemComment' && !empty($_REQUEST['id'])) {
        $comment = $_REQUEST['comment'][0];
        $itemData = array(
            'rowid' => $_REQUEST['id'],
            'comment' => $comment
        );
        $updateItem = $cart->update($itemData);
        $redirectURL = $updateItem ? 'viewCart.php' : 'index.php';
        header("Location: $redirectURL");
        exit;
    } elseif ($_REQUEST['action'] === 'removeCartItem' && !empty($_REQUEST['id'])) {
        // remove item from cart
        $deleteItem = $cart->remove($_REQUEST['id']);

        // redirect
        // $redirectURL = 'viewCart.php';
        $redirectURL = $_SERVER['HTTP_REFERER'];
    } elseif ($_REQUEST['action'] == 'placeOrder' && $cart->total_items() > 0) {
        // $redirectURL = 'checkout.php';
        $redirectURL = $_SERVER['HTTP_REFERER'];
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
                    // this should include taxes and fees
                    $db_grand_total = (($cart->total() + $cart->total_logo_fees()) * 1.09);
                    $db_submitted_by = $submitted_by;

                    $insertOrder = $stmt->execute();

                    if ($insertOrder) {
                        $orderID = $stmt->insert_id;

                        // get cart items
                        $cartItems = $cart->contents();
                        // Prepare the SQL Statement
                        if (!empty($cartItems)) {
                            $sql = "INSERT INTO order_details (
                            order_id, 
                            product_id, 
                            price_id, 
                            quantity, 
                            size_id, 
                            color_id, 
                            item_price, 
                            logo_fee, 
                            tax, 
                            line_item_total, 
                            logo, 
                            comment, 
                            dept_patch_place, 
                            emp_dept, 
                            bill_to_dept, 
                            bill_to_fy, 
                            status_id
                            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                            $stmt = $conn->prepare($sql);

                            // insert order items into database

                            foreach ($cartItems as $item) {
                                $stmt->bind_param(
                                    "iiiisssdddssssssi",
                                    $db_order_id,
                                    $db_product_id,
                                    $db_price_id,
                                    $db_quantity,
                                    $db_size_id,
                                    $db_color_id,
                                    $db_item_price,
                                    $db_logo_fee,
                                    $db_tax,
                                    $db_line_item_total,
                                    $db_logo,
                                    $db_comment,
                                    $db_dept_patch_place,
                                    $db_emp_dept,
                                    $db_bill_to_dept,
                                    $db_bill_to_fy,
                                    $db_status_id
                                );
                                $db_order_id = $orderID; // int(11)
                                $db_product_id = $item['id']; // int(11)
                                $db_price_id = $item['price_id']; // int(11)
                                $db_quantity = intval($item['qty']); // int(11)
                                $db_size_id = $item['size_id']; // varchar(45)
                                $db_color_id = $item['color_id']; // varchar(45)
                                $db_item_price = $item['price']; // varchar(25)
                                $db_logo_fee = $item['logoFee']; // float(10,2)
                                $db_tax = $item['tax']; // float(10,2)
                                $db_line_item_total = (($db_item_price + $db_logo_fee + $db_tax) * $db_quantity); // float(10,2)
                                $db_logo = $item['logo']; // varchar(125)
                                $db_comment = $item['comment']; // varchar(255)
                                $db_dept_patch_place = $item['deptPatchPlace']; // varchar(150)
                                $db_emp_dept = $department; // varchar(45)
                                $db_bill_to_dept = $department; // varchar(45)
                                $db_bill_to_fy = $item['fy']; // varchar(45)
                                $db_status_id = 5; // int(11)


                                $order_details_id = $stmt->execute();
                            }
                        }
                        $cart->destroy();
                        $redirectURL = 'orderSuccess.php?id=' . base64_encode($orderID) . '&emp_id=' . base64_encode($emp_number);
                        //header("Location: $redirectURL");
                        // header("Location: orderSuccess.php?id=" . base64_encode($orderID) . "&emp_id=" . base64_encode($emp_number));
                    } else {
                        //echo "FAILURE at inserting into order_details.";
                        $sessData['status']['type'] = 'error';
                        $sesData['status']['msg'] = 'Something went wrong. Restart your computer and try again 101';
                        // return;
                        // error_log('Redirecting to: ' . $redirectURL);
                        // error_log('orderID: ' . $orderID);
                        // redirect to order status page
                        //echo 'Something went wrong. Restart your computer and try again 101';
                    }
                } else {
                    $sessData['status']['type'] = 'error';
                    $sessData['status']['msg'] = 'Something went wrong. Restart your computer and try again 102';
                    // echo 'Something went wrong. Restart your computer and try again 102';
                }
            } else {
                $sesData['status']['type'] = 'error';
                $sesData['status']['msg'] = 'Something went wrong. Restart your computer and try again 103';
                // echo 'Something went wrong. Restart your computer and try again 103';
            }
        } else {
            //echo "The Employee ID you entered does not match our records. Please try again.";
            $sessData['status']['type'] = 'error';
            $sessData['status']['msg'] = '<p>The Employee ID you entered does not match our records. Please try again.</p>' . $errorMsg;
        }
        // $_SESSION['sessData'] = $sessData;
    } else {
        echo "<div class='alert'>Incorrect CAPTCHA</div>";
    }
}

$conn->close();

// redirect to specific page
header("Location: $redirectURL");
exit();