<?php

$cart = [];

// Get customer's selection type, size, and price from POST request
$type = $_POST['type'];
$size = $_POST['size'];
$price = $_POST['price'];

// Add customer's selections to the cart array
array_push($cart, $type, $size, $price);

// Set cookie for customer's cart with serialization
setcookie('cart', serialize($cart), time() + 60 * 100000, '/');
