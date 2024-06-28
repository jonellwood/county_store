<?php
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Home | Berkeley County Store</title>
</head>

<body>
    <div class="front-image-background">
        <img src="./County-Store-Image.png" alt="some-store" />
    </div>
    <?php include "components/slider.php" ?>
    <div class="hot-sellers">
        <?php include "stats.php" ?>
    </div>
    <?php include "cartSlideout.php" ?>
</body>
<?php include "footer.php" ?>

</html>
<style>
.hot-sellers {
    width: 75%;
    position: relative;
    margin-top: 150px;
    margin-left: auto;
    margin-right: auto;
}


.card {
    margin-top: 20px;
    margin-right: 20px;
    border-radius: 1px;
    box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
}

.alert {
    margin-top: 20px;
    margin-bottom: 20px;
}
</style>