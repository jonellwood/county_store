<?php
require_once "../config.php";
echo "hello";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

require_once 'Cart.class.php';
$cart = new Cart;

function html_escape($string)
{
    // Replace special characters with HTML entities
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}

// set default redirect page
$redirectURL = 'index.php';

if ($cart->total_items() > 0) {
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