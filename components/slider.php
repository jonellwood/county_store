<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Nav bar for site throughout user facing portion of the site.
Includes:   none
*/


?>

<div class="navbar sticky">
    <a class="navbar-brand" href="./index.php" title="to the home page">
        <img src="./dept_logos/bc-circ-white.png" alt="bc logo" class="logo-img">
    </a>
    <div class="spacer"></div>
    <div class="header-list">
        <ul class="list-level-1">
            <li class="header-list-item-1">Men
                <ul class="hidden-list-1">
                    <li><a href="./mens-shirts-view.php">Shirts</a></li>
                    <li><a href="./mens-outerwear-view.php">Outerwear</a></li>
                    <li><a href="./mens-sweatshirts-view.php">Sweatshirts</a></li>
                    <li><a href="./mens-bnt-view.php">Big & Tall</a></li>
                    <li><a href="./accessories-view.php">Accessories</a></li>
                </ul>
            </li>
            <li class="header-list-item-2">Woman
                <ul class="hidden-list-2">
                    <li><a href="./ladies-shirts-view.php">Shirts</a></li>
                    <li><a href="./ladies-outerwear-view.php">Outerwear</a></li>
                    <li><a href="./ladies-sweatshirts-view.php">Sweatshirts</a></li>
                    <li><a href="./ladies-plus-view.php">Plus Size</a></li>
                    <li><a href="./accessories-view.php">Accessories</a></li>
                </ul>
            </li>
            <li class="header-list-item-3">
                <a href="./boots-details.php">Boots</a>
            </li>
            <li class=" header-list-item-4">
                <a href="./hats-view.php">Hats</a>
            </li>
        </ul>
    </div>
    <div class="cart-view">
        <p title="View Cart" id="toggle-button" onclick="toggleSlideout()" role="button"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
            (<?php echo ($cart->total_items() > 0) ? $cart->total_items() . ' Items' : 0; ?>)
        </p>
    </div>
    <div class="search">
        <form action="search.php" method="post">
            <input type="text" name="param" id="param" class="paraminput" placeholder="Search...">
            <i class="fa "></i>
            <button type="submit" class="search-button">Search</button>
        </form>
    </div>
    <div class="help">
        <a href="support.php">
            <i class="fa fa-question-circle"></i>
        </a>
    </div>
</div>
<!-- </div> -->


<style>
    @font-face {
        font-family: RoboCondensed;
        src: url('./fonts/RobotoCondensed-Regular.ttf');
    }

    .navbar {
        font-family: RoboCondensed;
        padding: 0 1rem;
        color: aliceblue;
        background-color: #00000080;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        /* z-index: 1030; */
        min-height: 6rem;
        font-size: 1.5rem;
        font-weight: 400;
        line-height: 1.5;
        display: flex;
    }

    .navbar-brand {
        padding-top: var(--bs-navbar-brand-padding-y);
        padding-bottom: var(--bs-navbar-brand-padding-y);
        margin-right: var(--bs-navbar-brand-margin-end);
        font-size: var(--bs-navbar-brand-font-size);
        color: var(--bs-navbar-brand-color);
        text-decoration: none;
        white-space: nowrap;
        padding-top: 20px;
        padding-bottom: 20px;
        /* margin-right: 50px; */
        font-size: large;
        color: aliceblue;
        text-decoration: none;
        white-space: nowrap;
    }


    .cart-view {
        margin-left: 50px;

    }

    .header-list {
        font-family: RoboCondensed;
        display: flex;
        justify-content: space-evenly;
        width: 40%;
        /* color: var(--bs-link-color) */
        color: aliceblue;
    }

    .header-list a {
        color: aliceblue;
    }

    .cart-view {
        font-family: RoboCondensed;
        font-size: large;
        margin-right: 10%;
    }

    .cart-view:hover {
        color: #000;
    }

    .logo-img {
        height: 50px;
        margin-left: 50px;
    }

    .spacer {
        width: 2%;

    }

    ul {
        list-style-type: none;
    }

    .list-level-1 {
        display: inline;
    }

    .list-level-1 li {
        display: inline;
        padding-right: 30px;
        /* cursor: pointer; */
        position: relative;
    }

    .hidden-list-1 {
        display: none;
        background-color: #06060650;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 64px;
        z-index: 4;
        padding-top: 2px;
        margin-left: -50px;
    }

    .hidden-list-2 {
        display: none;
        background-color: #06060650;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 64px;
        z-index: 4;
        padding-top: 2px;
        margin-left: -50px;
    }

    .hidden-list-3,
    .hidden-list-4 {
        display: none;
        background-color: #06060650;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 64px;
        z-index: 4;
        padding-top: 2px;
        margin-left: -50px;
    }

    .hidden-list-1 a:hover {
        color: #d5ca9e;
    }

    .hidden-list-2 a:hover {
        color: #d5ca9e;
    }

    .hidden-list-3 a:hover {
        color: #d5ca9e;
    }

    .hidden-list-4 a:hover {
        color: #d5ca9e;
    }

    .header-list-item-1:hover {
        color: #d5ca9e;
    }

    .header-list-item-2:hover {
        color: #d5ca9e;
    }

    .header-list-item-3:hover {
        color: #d5ca9e;
    }

    .header-list-item-4:hover {
        color: #d5ca9e;
    }

    .header-list-item-1:hover ul {
        color: white;
        display: inline;
        position: absolute;
        top: 100%;
    }



    .header-list-item-2:hover ul {
        color: white;
        display: inline;
        position: absolute;
        top: 100%;
    }

    .header-list-item-3:hover a,
    .header-list-item-4:hover a {
        color: #d5ca9e;
    }

    .header-list-item-3:hover ul {
        color: white;
        display: inline;
        position: absolute;
        top: 100%;
    }

    .header-list-item-4:hover ul {
        color: white;
        display: inline;
        position: absolute;
        top: 100%;
    }

    a {
        text-decoration: none;
    }

    .sticky {
        position: fixed;
        top: 0;
        z-index: 3;
        padding-left: 40px;
        padding-right: 40px;
    }

    .paraminput input {
        line-height: 1 !important;
    }

    @media (max-width: 1475px) {
        #param {
            /* width: 50px; */
            display: none;
        }
    }

    @media (max-width: 1000px) {
        .search {
            display: none;
        }


    }
</style>