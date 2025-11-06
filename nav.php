<?php
// session_start();
// var_dump($_SESSION['isComm']);

if (isset($_SESSION['isComm']) && $_SESSION['isComm'] == true) {
    echo '<style>
            .comm{
                color: blue;
          </style>';
}



$navSql = "SELECT * from uniform_orders.producttypes where isactive=true";
$navStmt = $conn->prepare($navSql);
$navStmt->execute();
$navResult = $navStmt->get_result();

$navStmt->close();
if ($navResult->num_rows > 0) {

?>


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="nav-container">
        <a class="navbar-brand" id="bavbar-text" href="./search.php"><i class="fa fa-jedi" aria-hidden="true"></i>
            Search</a>
        <a class="navbar-brand" id='navbar-text' href="index.php#nav-container"><i class="fa fa-home" aria-hidden="true"></i> Home</a>
    <?php
    while ($navRow = $navResult->fetch_assoc()) {
        echo "<a class='navbar-brand " . $navRow['productType'] . "' href='products-by-catagories.php?productType=" . $navRow['productType_id'] . "'>" . $navRow['productType'] . "</a>";
    };
}
    ?>
    <a class="navbar-brand comm" id='navbar-text' href="products-by-communications.php">911 <i class="fa fa-phone" aria-hidden="true"></i></a>
    <div class="cart-view">
        <a href="viewCart.php" title="View Cart"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
            (<?php echo ($cart->total_items() > 0) ? $cart->total_items() . ' Items' : 0;
                $salestax = number_format(($cart->total() * .09), 2)
                ?>)

            $<?php echo ($cart->total() > 0) ? number_format(($salestax + $cart->total()), 2) . ' Total' : number_format(0.00, 2); ?>
        </a>

    </div>

    <button class="theme-toggle-btn" id="theme-toggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode">
        <span class="theme-icon">🌙</span>
    </button>

    <a class="navbar-brand far-right" id='navbar-text' href="support.php">Help <i class="fa fa-question-circle" aria-hidden="true"></i></a>
    </nav>
    <script>
        window.onscroll = function() {
            myFunction()
        };

        var navbar = document.getElementById("nav-container");
        // var sticky = navbar.offsetTop;
        var sticky = 960;

        function myFunction() {
            console.log('navbar offset is: ' + sticky);
            console.log('Yoffset is : ' + window.pageYOffset);
            if (window.pageYOffset >= sticky) {
                navbar.classList.add("sticky")
            } else {
                navbar.classList.remove("sticky");
            }
        }
    </script>
    <style>
        #nav-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

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

        /* Theme Toggle Button */
        .theme-toggle-btn {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin: 0 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }

        .theme-toggle-btn:active {
            transform: scale(0.95);
        }

        .theme-icon {
            font-size: 1.25rem;
            line-height: 1;
            display: block;
        }

        .theme-text {
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
            display: none;
        }

        @media (min-width: 1024px) {
            .theme-text {
                display: block;
            }
        }

        .sticky {
            position: fixed;
            top: 0;
            z-index: 3;
            padding-left: 40px;
            padding-right: 40px;
            /* -webkit-box-shadow: 0px 10px 20px 3px rgba(77, 75, 77, 0.7);
    -moz-box-shadow: 0px 10px 20px 3px rgba(77, 75, 77, 0.7);
    box-shadow: 0px 10px 20px 3px rgba(77, 75, 77, 0.7); */
        }

        .sticky+.categories-container {
            padding-top: 60px;
        }
    </style>