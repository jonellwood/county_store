<?php
// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Products By Catagories" />
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>

    <link href="./style/storeLux.css" rel="stylesheet" />
    <link href="./style/mediaQueries.css" rel="stylesheet" />
    <link href="./style/viewTransitions.css" rel="stylesheet" />
    <link href="./style/custom.css" rel="stylesheet" />
    <script src="functions/autoClosePopover.js"></script>
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <meta name="view-transition" content="same-origin">
</head>

<body class="body">
    <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
        <div class="container-fluid align-items-baseline">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/bc-seal.png" class="m-2" alt="logo" width="75px" />
            </a>
            <div class="m-2 border-right"></div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02"
                aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarColor02">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <button class="nav-link" href="#" popovertarget="mens-menu-popover" popovertargetaction="show"
                            id="mens-menu-anchor" role="button">MENS
                            <span class="visually-hidden">(current)</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" href="#" popovertarget="womans-menu-popover" popovertargetaction="show"
                            id="womans-menu-anchor" role="button">WOMANS</button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="boots-details.php">BOOTS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hats-view.php">HATS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="accessories-view.php">ACCESSORIES</a>
                    </li>
                </ul>
            </div>
            <div class="collapse navbar-collapse" id="navbarColor02">
                <div class="d-flex align-items-center align-self-center flex-grow-1 m-2">
                    <button class="btn btn-outline-primary nav-link" title="View Cart" popovertarget="cart-slideout"
                        role="button">
                        <img src="assets/icons/cart.svg" alt="cart" width="35px" class="m-0 p-0" />
                        (<?php echo ($cart->total_items() > 0) ? $cart->total_items() . ' Items' : 0; ?>)
                        </p>
                </div>
                <form action="search.php" method="post" class="d-flex">
                    <input class="form-control me-sm-2" type="text" name="param" placeholder="Search...">
                    <button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <div id="mens-menu-popover" popover=manual>
        <button popovertarget="mens-menu-popover" popovertargetaction="hide" class="btn-close ms-2 mb-1" role="button">
            <span aria-hidden="true"></span>
        </button>
        <ul class="list-group">
            <li>
                <a href="mens-shirts-view.php"
                    class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
                    Shirts
                    <img src="assets/icons/polo-shirt.svg" alt="shirt" width="25px" />
                </a>
            </li>
            <li>
                <a href="mens-outerwear-view.php"
                    class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                    Outerwear
                    <img src="assets/icons/winter-jacket.svg" alt="jacket" width="25px" />
                </a>
            </li>
            <li>
                <a href="mens-sweatshirts-view.php"
                    class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
                    Sweatshirts
                    <img src="assets/icons/hoodie-jacket.svg" alt="shirt" width="25px" />
                </a>
            </li>
            <li> <a href="mens-bnt-view.php"
                    class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                    Big & Tall
                    <img src="assets/icons/shirt.svg" alt="shirt" width="25px" />
                </a>
            </li>
        </ul>
    </div>
    <div id="womans-menu-popover" popover=manual>
        <button popovertarget="womans-menu-popover" popovertargetaction="hide" class="btn-close ms-2 mb-1"
            role="button">
            <span aria-hidden="true"></span>
        </button>
        <ul class="list-group">
            <li>
                <a href="ladies-shirts-view.php"
                    class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
                    Shirts
                    <img src="assets/icons/polo-shirt.svg" alt="shirt" width="25px" />
                </a>
            </li>
            <li>
                <a href="ladies-outerwear-view.php"
                    class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                    Outerwear
                    <img src="assets/icons/winter-jacket.svg" alt="jacket" width="25px" />
                </a>
            </li>
            <li>
                <a href="ladies-sweatshirts-view.php"
                    class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
                    Sweatshirts
                    <img src="assets/icons/hoodie-jacket.svg" alt="shirt" width="25px" />
                </a>
            </li>
            <li>
                <a href="ladies-plus-view.php"
                    class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                    Plus Size
                    <img src="assets/icons/shirt.svg" alt="shirt" width="25px" />
                </a>
            </li>
        </ul>
    </div>

    <div id="cart-slideout" popover=manual>
        <button popovertarget="cart-slideout" popovertargetaction="hide" class="btn-close ms-2 mb-1">
            <span aria-hidden="true"></span>
        </button>
        <div id="cart-slideout-container">
            <?php include "cartSlideout.php"; ?>
        </div>
    </div>
    <script>
    addPopoverAutoClose('#womans-menu-popover');
    addPopoverAutoClose('#mens-menu-popover');
    </script>

    <style>
    #mens-menu-anchor {
        anchor-name: --mens-menu-popover;
    }

    #womans-menu-anchor {
        anchor-name: --womans-menu-popover;
    }

    #mens-menu-popover[popover] {
        transition:
            display 0.3s allow-discrete,
            opacity 0.3s,
            translate 0.3s;
        transition-timing-function: ease-in;
        opacity: 0;
        translate: 0 30px;
        position: absolute;
        inset: unset;
        top: anchor(--mens-menu-popover bottom);
        left: anchor(--mens-menu-popover left);
    }

    #mens-menu-popover[popover]:popover-open {
        opacity: 1;
        translate: 0 0;
        transition-timing-function: ease-out;
    }

    #womans-menu-popover[popover] {
        transition:
            display 0.3s allow-discrete,
            opacity 0.3s,
            translate 0.3s;
        transition-timing-function: ease-in;
        opacity: 0;
        translate: 0 30px;
        position: absolute;
        inset: unset;
        top: anchor(--womans-menu-popover bottom);
        left: anchor(--womans-menu-popover left);
    }



    #womans-menu-popover[popover]:popover-open {
        opacity: 1;
        translate: 0 0;
        transition-timing-function: ease-out;
    }

    @starting-style {
        #mens-menu[popover]:popover-open {
            opacity: 0;
            translate: 0 50px;
        }

        #womans-menu[popover]:popover-open {
            opacity: 0;
            translate: 0 30px;
        }
    }

    #anchored-menu[popover]:popover-open {
        opacity: 1;
        translate: 0 0;
        transition-timing-function: ease-out;
    }
    </style>