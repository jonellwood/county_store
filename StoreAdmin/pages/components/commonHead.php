<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Backend coming in all kind of ways" />

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>

    <link href="style/navBarStyles.css" rel="stylesheet" />
    <link href="style/backend.css" rel="stylesheet" />
    <link href="style/custom.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <meta name="view-transition" content="same-origin">

    <title>Admin Backend</title>
</head>

<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <div class="parent">
        <div class="div1">
            <nav class="sb-sidenav" width="auto">
                <div class="d-flex flex-column flex-shrink-0 pb-3 pt-3 ps-3">
                    <div class=mb-4 text-center>
                        <img src="../assets/logos/bc-bsqr.png" alt="BCG Logo" width="75px" class="nav-logo" />
                    </div>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="employeeRequests.php" class="nav-link" aria-current="page">
                                <img src="../../../assets/icons/home-2.svg" alt="home" width="25px" />
                                Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../inventory/index.php" class="nav-link">
                                <img src="../../../assets/icons/inventory-mgmt.svg" alt="inventory" width="25px" />
                                Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../support.php" class="nav-link">
                                <img src="../../../assets/icons/support.svg" alt="help" width="25px" />
                                Help
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../index.php" class="nav-link" target="_blank">
                                <img src="../../../assets/icons/store.svg" alt="store" width="25px" />
                                Store
                            </a>
                        </li>

                        <li class="nav-item">
                            <details name="nav" class="nav-link">
                                <summary>
                                    <img src="../../../assets/icons/admin-access.svg" alt="user" width="25px" />
                                    Admin
                                </summary>
                                <div class="admin-details">


                        <li class='nav-item'>
                            <a class='nav-link' href='edit-request-ui.php' target="_blank">
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/edit.svg' width='20' height='20'>
                                    Edit Requests
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='orders-by-dept-for-admin.php' target="_blank">
                                <span class='nav-link-text text-small ms-1'>
                                    <img class='icon' src='../../assets/icons/awaiting.svg' width='20' height='20'>
                                    Awaiting Order
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='./reports.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/reports.svg' width='20' height='20'>
                                    Reports
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='orders-to-be-received.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/delivery.svg' width='20' height='20'>
                                    Receiving
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                        <li class='nav-item'>
                            <a class='nav-link' href='edit-users.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/user.svg' width='20' height='20'>
                                    Edit Users
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='dept-admin.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/department.svg' width='20' height='20'>
                                    Edit Dept
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='editProductsFilters.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/filter.svg' width='20' height='20'>
                                    Edit Filters
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='add-product-ui.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/add.svg' width='20' height='20'>
                                    Add Product
                                </span>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='edit-product-ui.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/editPenguin.svg' width='20' height='20'>
                                    Edit Product
                                </span>
                            </a>
                        </li>

                        <li class='nav-item'>
                            <a class='nav-link' href='overview.php'>
                                <span class='nav-link-text ms-1'>
                                    <img class='icon' src='../../assets/icons/old.svg' width='20' height='20'>
                                    Old Dashboard
                                </span>
                            </a>
                        </li>

                </div>
                <!-- </ul> -->
                </details>
                </li>
                <hr>
                <li class="nav-item">
                    <a class="nav-link " href="../../logout.php">
                        <span class="nav-link-text ms-1 red">
                            <!-- <img class='icon' src='../../assets/icons/awaiting.svg' width='20' height='20'> -->
                            ✌🏼 Logout
                        </span>
                    </a>
                </li>
                </ul>
        </div>
        </nav>
    </div>
    <div class="div2" id="main"></div>
    <div class="div3" id="details"></div>
    <div class="div4" id="alert-banner">
        Alert message goes here
    </div>
    <div class="div6" id="div6"></div>
    <div class="div7" id="div7"></div>
    </div>




    <script>
        // addPopoverAutoClose('#womans-menu-popover');
        // addPopoverAutoClose('#mens-menu-popover');
    </script>

    <style>
        ::marker {
            display: none;
        }

        summary {
            list-style: none
        }

        summary::-webkit-details-marker {
            display: none
        }

        .parent {
            display: grid;
            grid-template-columns: 10% 25% 40% 22%;
            grid-template-rows: 75px 1fr 1fr;
            height: 100vh;
            /* overflow: hidden; */
        }

        .div1 {
            display: flex;
            grid-area: 1 / 1 / 3 / 1;
            background-color: #80808030;
        }

        .div2 {
            display: flex;
            grid-area: 2 / 2 / 2 / 2;
            /* height: 100vh; */
            scrollbar-gutter: stable;
            background-color: #80808030;
        }

        .div3 {
            display: flex;
            grid-area: 2 / 3 / 2 / 3;
            height: 100vh;
            scrollbar-gutter: stable;
            padding-left: 20px;
            overflow-y: auto;
            border-top: 3px solid #80808050;
            border-left: 3px solid #80808050;
            border-bottom: 3px solid #80808050;

        }

        .div4 {
            display: flex;
            grid-area: 1 / 2 / 1 / 5;
        }

        .div5 {
            display: flex;
            /* grid-area: 2/ 4 / 2 /4; */
            overflow-y: auto;
            scrollbar-gutter: stable;
        }


        .div6 {
            display: flex;
            flex-direction: column;
            grid-area: 2 / 4 / 3 / 4;
            border-top: 3px solid #80808050;
            border-right: 3px solid #80808050;
            border-bottom: 3px solid #80808050;
        }

        .total-hidden {
            margin-left: 550px;
        }

        .div7 {
            display: flex;
            grid-area: 1 / 5 / 1 / 5;
            margin-left: -100px;
            /* visibility: hidden; */
        }

        .hidden {
            visibility: hidden;
        }

        #main {
            overflow-y: auto;


        }

        details>summary {
            /* padding: 4px; */
            width: 150px;
            /* background-color: #fff; */
            border: none;
            /* box-shadow: 1px 1px 2px #bbbbbb; */
            cursor: pointer;
        }

        details>div {
            background-color: #fff0ff;
            box-shadow: 1px 1px 2px #bbbbbb;
            margin-left: -40px;
        }

        details>li {
            padding: 2px;
            margin: 0;
        }

        .nav-logo {
            margin-left: 18%;
            margin-right: auto;
            filter: drop-shadow(2px 3px 5px #808080)
        }

        /* #mens-menu-anchor {
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
    } */
    </style>