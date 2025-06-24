<?php

session_start();

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// init cart class
include_once 'Cart.class.php';
$cart = new Cart;

$savedItems = json_encode($_SESSION['cart_contents']);

// echo gettype($savedItems);
// echo "<hr>";
// var_dump($savedItems);

// echo "<hr>";

?>

<script>
Storage.prototype.setObj = function(key, obj) {
    return this.setItem(key, JSON.stringify(obj))
}
Storage.prototype.getObj = function(key) {
    return JSON.parse(this.getItem(key))
}

var savedCart = [];
// savedCart[0] = savedCart.push(JSON.stringify(<//?php $_SESSION['cart_contents'] ?>));
var cartInfo = JSON.stringify(<?php echo $savedItems ?>);
savedCart.push(cartInfo);
// console.table(savedCart);
localStorage.setItem("savedCart", JSON.stringify(savedCart));


var cartValues = JSON.parse(localStorage.getItem("savedCart"));
console.log(cartValues);
</script>