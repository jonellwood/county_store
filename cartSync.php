<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Receive cart data from local storage via JavaScript and insert into cart object on server
Includes:   None
*/
$rawData = file_get_contents("php://input");

$cartData = json_decode($rawData, true);

if ($cartData) {
    require_once 'Cart.class.php';
    session_start();

    if (isset($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    } else {
        $cart = new Cart();
        $_SESSION['cart'] = $cart;
    }
    // add items to the cart on server
    $cart->insert($cartData);
    // save the cart back to the session 
    $_SESSION['cart'] = $cart;
    echo json_encode(['status' => 'success', 'message' => 'Cart synced Successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart data']);
}
