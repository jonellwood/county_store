<?php
// session_start();


$navSql = "SELECT * from uniform_orders.producttypes where productType_id=6";
$navStmt = $conn->prepare($navSql);
$navStmt->execute();
$navResult = $navStmt->get_result();

$navStmt->close();
if ($navResult->num_rows > 0) {

?>


<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="nav-container">
    <a class="navbar-brand" id="navbar-text" href="./comm-logout.php"><i class="fa fa-sign-out-alt"
            aria-hidden="true"></i> Logout</a>
    <a class="navbar-brand" id="navbar-text" href="./communications-emp-managment.php"><i class="fa fa-user"
            aria-hidden="true"></i> Admin</a>
    <!-- <a class="navbar-brand" id="bavbar-text" href="./search.php"><i class="fa fa-jedi" aria-hidden="true"></i>Search</a> -->
    <!-- <a class="navbar-brand" id='navbar-text' href="index.php#nav-container"><i class="fa fa-home" aria-hidden="true"></i>Home</a> -->
    <?php
    while ($navRow = $navResult->fetch_assoc()) {
        echo "<a class='navbar-brand " . $navRow['productType'] . "' href='products-by-catagories.php?productType=" . $navRow['productType_id'] . "'>" . $navRow['productType'] . "</a>";
    };
}
    ?>
    <div class="cart-view">
        <a href="comm-viewCart.php" title="View Cart"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
            (<?php echo ($cart->total_items() > 0) ? $cart->total_items() . ' Items' : 0;
                $salestax = number_format(($cart->total() * .09), 2)
                ?>)

            $<?php echo ($cart->total() > 0) ? number_format(($salestax + $cart->total()), 2) . ' Total' : number_format(0.00, 2); ?>
        </a>

    </div>
    <!-- <a class="navbar-brand right" id='store-link' href="https://www.companycasuals.com/LOWCOUNTRYNATIVE//start.jsp" target="_blank">LowCounty Native <i class='fas fa-store-alt' style='font-size:12px'></i></a> -->
    <a class="navbar-brand far-right" id='navbar-text' href="support.php">Help <i class="fa fa-question-circle"
            aria-hidden="true"></i></a>
</nav>
<style>
.navbar-brand:hover {
    transform: rotate(-2deg);
    color: whitesmoke;
}

.right {
    margin-left: 50px;
    font-size: small;
}

.far-right {
    margin-left: 75px;
}

.Communications {
    display: none;
}
</style>