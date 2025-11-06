<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 09/09/2024
Purpose: Checkout page for requests from user
Includes:    viewHead.php,  footer.php
*/
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init class
include_once 'Cart.class.php';
$cart = new Cart;

// if cart is empty send back to products page
if ($cart->total_items() <= 0) {
    header("Location: index.php");
}

// get CAPTCHA text from captcha and write to SESSION
// if (isset($_POST['captcha_challange']) && $_POST['captcha_challenge'] === $_SESSION['captcha_text']) {
//     echo var_dump($_SESSION['captcha_challange']);
// }


// get posted data from session

$postData = !empty($_SESSION['postData']) ? $_SESSION['postData'] : array();
unset($_SESSION['postData']);

// get status from session
$sessData = !empty($_SESSION['sessData']) ? $_SESSION['sessData'] : '';
if (!empty($sessData['status']['msg'])) {
    $statusMsg = $sessData['status']['msg'];
    $statusMsgType = $sessData['status']['type'];
    unset($_SESSION['sessData']['status']);
}


// include "./components/viewHead.php";
?>

<!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" async> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
<!-- <link rel="icon" type="image/x-icon" href="favicons/favicon.ico"> -->
<link rel="stylesheet" href="./style/global-variables.css">
<link rel="stylesheet" href="checkout-modern.css">
<script>
    // async function to set captcha then
    // fetch the captcha from empcaptcha.php endpoint then
    // convert response into json
    async function setCaptcha(empID) {
        await fetch('./empCaptcha.php?emp_num=' + empID)
            .then((response => response.json()))
        // .then(data => console.log('Captcha set to: ' + empID))
    }
    async function getEmpSpending(empID) {
        let USDollar = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });
        await fetch('./getEmpSpending.php?emp_num=' + empID)
            .then((response => response.json()))
            // .then(data => console.log(data))
            .then((data) => {
                // var totalThisYear = data[0]['SUM(line_item_total)']
                var totalThisYear = data[0].total_sum;
                var html = "<p>Spending this fiscal year: " + USDollar.format(totalThisYear) + "</p> ";
                html += "<p>Including this request: " + USDollar.format(totalThisYear +
                    <?php echo (($cart->total() + $cart->total_logo_fees()) * 1.09) ?>) + "</p>";
                document.getElementById('annualSpending').innerHTML = html;
            })
    }
    // fetch employee data from server using supplied employee ID then
    // convert response into json then
    // split the name into first and last then
    // build HTML string then
    // write HTML to page
    async function getEmpData(empID) {
        const employee = await fetch("./getEmpData.php?empNum=" + empID)
            .then((response) => response.json())
            .then(data => {
                const name = data[0].empName.split(" ");
                let fname = name[0];
                let lname = name[1];
                var html = "<p>" + data[0].empName + "</p>";
                html += "<input type='hidden' name='first_name' value=" + fname + ">";
                html = "<span><p>" + data[0].empName + " - " + data[0].deptName + "</p> </span>";
                html += "<input type='hidden' name='first_name' value=" + fname + ">";
                html += "<input type='hidden' name='last_name' value=" + lname + ">";
                // html += "<p name='department'>" + data[0].deptName + "</p>";
                html += "<input type='hidden' name='department' value=" + data[0].deptNumber + ">";
                html += "<p name='email'>" + data[0].email + "</p>";
                html += "<input type='hidden' name='email' value=" + data[0].email + ">";
                html += "<p id='annualSpending'></p>"

                html += "<input type='hidden' name='emp_number' id='emp_number' value='" + data[0].empNumber +
                    "' >"
                console.log(data[0].empNumber)
                // setCaptcha(data[0].empNumber)
                document.getElementById('empInfo').innerHTML = html
                // getEmpSpending(empID);

            })

    }
    // Fetch employee data with given ID Number then
    // convert response into json then
    // Get first and last name from `EMPName` field in fetched data then
    // Create html markup with employee data then
    // Add input fields with names autofilled based on employee data then
    // Display prompt for user
    async function getSubData(empID) {
        const submitter = await fetch("./getEmpData.php?empNum=" + empID)
            .then((response) => response.json())
            .then(data => {
                const name = data[0].empName.split(" ");
                let fname = name[0];
                let lname = name[1];
                var html2 = "<p>" + data[0].empName + "</p>";
                // html2 += "<input type='hidden' name='sub_first_name' value=" + fname + ">";
                html2 += "<p>" + data[0].deptName + "</p>";
                html2 += "<input type='hidden' name='sub_first_name' value=" + fname + ">";
                html2 += "<input type='hidden' name='sub_last_name' value=" + lname + ">";
                html2 += "<p name='email'>" + data[0].email + "</p>";
                html2 += "<input type='hidden' name='sub_email' value=" + data[0].email + ">";
                html2 += "<input type='hidden' name='sub_number' id='sub_number' value='" + data[0].empNumber +
                    "' >";
                html2 += "<br>";
                html2 += "<hr>";
                html2 += "<p>" + data[0].empName + " Please enter your employee ID Number </p>"
                document.getElementById('subInfo').innerHTML = html2;
            })
    }


    // The function toggle() is used to open and close dropdown menus
    // Get the element with the ID "empDropdown" then 
    // Toggle the class "show" to show / hide the dropdown
    function toggle() {
        document.getElementById("empDropdown").classList.toggle("show");

    }
    // Function to toggle the subDropdown and show captcha holder and place order button 
    // Enables a dropdown menu with an ID of "subDropdown" when the function subToggle() is called.
    // Removes the "hidden" class from an element with an ID of "captcha-holder".
    // Removes the "hidden" class from an element with an ID of "place-order-button".
    function subToggle() {
        document.getElementById("subDropdown").classList.toggle("show");
        // document.getElementById('captcha-holder').classList.remove("hidden");
        // document.getElementById('place-order-button').classList.remove("hidden");
    }

    // This code  performs a filter function. It gets the user's input from an HTML element with the id "empInput". It also gets references to different elements with the tag name button which are contained within an element with the id of "empDropdown".
    // It then looks through each of the elements that it found searching for any words that match the user's input (the input is converted to upper case for consistency). If it finds a match, it sets the style attribute of the element to make it visible; otherwise it sets it to none which hides the element. filterFunctionSub (below) does the same thing for a differnt input.
    function filterFunction() {
        console.log('starting filter');
        var input, filter, ul, li, a, i;
        input = document.getElementById("empInput");
        filter = input.value.toUpperCase();
        div = document.getElementById("empDropdown");
        a = div.getElementsByTagName("button");
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
    }

    function filterFunctionSub() {
        // console.log('starting filter');
        var input, filter, ul, li, a, i;
        input = document.getElementById("subInput");
        filter = input.value.toUpperCase();
        div = document.getElementById("subDropdown");
        a = div.getElementsByTagName("button");
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
    }

    // The setSub() function takes in a parameter called EMPID and does several things:
    // Gets data using this EMPID using getSubData()
    // Calls the setCaptcha() function using the same EMPID
    // Activates or deactivates the functionality of submitting using subToggle()
    function setSub(empID) {
        getSubData(empID)
        // alert('Submitter set');
        setCaptcha(empID);
        subToggle();
        document.getElementById('captcha-holder').classList.remove("hidden");
        document.getElementById('place-order-button').classList.remove("hidden");
    }

    // function to set employee data
    // get employee data by empid then
    // toggle data
    function setData(empID) {
        document.getElementById('sub-info-holder').classList.remove('hidden');
        getEmpData(empID);
        // setCaptcha(empID);
        toggle();
    }
</script>



</head>

<body>
    <div class="alert-banner" id="alert-banner">
    </div>
    <?php include "./components/viewHead.php" ?>
    <div class="container m-4">
        <h1>Checkout</h1>
        <div class="col-12">
            <div class="checkout">

                <div class="row">
                    <?php if (!empty($statusMsg) && ($statusMsgType == 'success')) { ?>

                        <div class="col-md-12">
                            <div class="alert alert-success"><?php echo $statusMsg; ?></div>
                        </div>
                    <?php } elseif (!empty($statusMsg) && ($statusMsgType == 'error')) { ?>
                        <div class="col-md-12">
                            <div class="alert alert-danger"><?php echo $statusMsg; ?> </div>
                        </div>
                    <?php } ?>

                    <!-- LEFT COLUMN: Cart Items (Detailed Product List) -->
                    <div class="col-md-8 order-md-1">
                        <h4 class="cart-items-title">
                            Your Cart
                            <span class="items-count">(<?php echo $cart->total_items(); ?> item<?php echo $cart->total_items() != 1 ? 's' : ''; ?>)</span>
                        </h4>

                        <div class="cart-items-list">
                            <?php
                            if ($cart->total_items() > 0) {
                                // get cart items from session
                                $cartItems = $cart->contents();
                                // remove these item from the object so they are not displayed in cart
                                unset($cartItems['total_logo_fees'], $cartItems['total_items'], $cartItems['cart_total']);
                                foreach ($cartItems as $item) {
                            ?>
                                    <div class="cart-item-card">
                                        <div class="item-details">
                                            <div class="item-header">
                                                <h6 class="item-name"><?php echo $item["name"]; ?></h6>
                                                <span class="item-subtotal"><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2); ?></span>
                                            </div>
                                            <div class="item-specs">
                                                <span class="spec">Color: <?php echo $item['color_name']; ?></span>
                                                <span class="spec">Size: <?php echo $item['size_name']; ?></span>
                                                <span class="spec">Qty: <?php echo $item["qty"]; ?></span>
                                                <span class="spec">Price: <?php echo CURRENCY_SYMBOL . number_format($item["price"], 2); ?></span>
                                            </div>
                                            <?php if ($item['id'] != 105) { ?>
                                                <div class="item-logo">
                                                    <img src="<?php echo $item['logo'] ?>" alt="bc logo" class="logo-img">
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>

                        <div class="continue-shopping">
                            <a href="index.php#nav-container" class="continue-link">
                                <img src="assets/icons/add.svg" alt="add" width="18" height="18">
                                Continue Shopping
                            </a>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Order Summary & Checkout -->
                    <div class="col-md-4 order-md-2">
                        <div class="order-summary-card">
                            <h4 class="summary-title">Order Summary</h4>

                            <div class="summary-lines">
                                <div class="summary-line">
                                    <span>Items (<?php echo $cart->total_items(); ?>)</span>
                                    <span><?php echo CURRENCY_SYMBOL . number_format($cart->total(), 2); ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>Logo Fees</span>
                                    <span><?php echo CURRENCY_SYMBOL . number_format($cart->total_logo_fees(), 2); ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>Sales Tax (9%)</span>
                                    <?php $sales_tax = (($cart->total() + $cart->total_logo_fees()) * 0.09) ?>
                                    <span><?php echo CURRENCY_SYMBOL . number_format(($sales_tax), 2) ?></span>
                                </div>
                                <div class="summary-line total-line">
                                    <span>Total</span>
                                    <strong><?php echo CURRENCY_SYMBOL . number_format(($cart->total() + $cart->total_logo_fees() + $sales_tax), 2) ?></strong>
                                </div>
                            </div>

                            <form method="post" action="cartAction.php" id='order-form'>
                                <fieldset id='fieldset'>
                                    <div class="employee-selectors">
                                        <div class="selector-group">
                                            <label class="selector-label">Employee</label>
                                            <?php include "getEmpNumSearch.php" ?>
                                            <p id="empInfo" class="selector-info"></p>
                                        </div>

                                        <div class="selector-group hidden" id="sub-info-holder">
                                            <label class="selector-label">Submitter</label>
                                            <?php include "getSubNumSearch.php" ?>
                                            <p id="subInfo" class="selector-info"></p>
                                        </div>

                                        <div class='captcha-holder hidden' id='captcha-holder'>
                                            <label class="selector-label">Verification Code</label>
                                            <input type="text" id="captcha" name="captcha_challenge" pattern="[0-9]{4}" class="captcha-input">
                                        </div>
                                    </div>

                                    <input type="hidden" name="action" value="placeOrder" />
                                    <button class="checkout-btn hidden" id="place-order-button" type="submit" name="checkoutSubmit">
                                        Place Order
                                    </button>
                                </fieldset>
                            </form>

                            <div class="edit-cart-link">
                                <a href="viewCart.php">
                                    <img src="assets/icons/edit.svg" alt="edit" width="16" height="16">
                                    Edit Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </?php include "viewCartDump.php" ?>
</body>
<script>
    function fiscalYear() {
        var currentMonth = new Date().getMonth() + 1;
        // console.log(currentMonth);
        var currentYear = new Date().getFullYear();
        var currentFY = 0
        // console.log('current year: ', currentYear)
        // console.log('current fy: ', currentFY)
        if (currentMonth < 6) {
            // console.log('less than 6')
            currentFYStart = (currentYear - 1);
            currentFYEnd = currentYear
        } else {
            // console.log('else')
            currentFYStart = currentYear
            currentFYEnd = (currentYear + 1)
        }
        // console.log("Current Fiscal Year Start, year is: ", currentFYStart)
        // console.log("Current Fiscal Year End, year is: ", currentFYEnd)
        return [currentFYStart, currentFYEnd, currentMonth];
    }
    var fyData = fiscalYear();
    var html = '';
    if (fyData[2] < 6) {
        html += `<div class="alert-text">
        🚨 All requests must be submitted by May 31st, ${fyData[1]}. Requests will not be able to be submitted between June 1st and June 30th, ${fyData[1]}</div>
        `
        document.getElementById('alert-banner').innerHTML = html
    } else {
        html += `<div class="alert-text">
            🚨 All requests must be submitted by May 31st, ${fyData[1]}. Requests will not be able to be submitted between June 1st and June 30th, ${fyData[1]}</div>
            `
        document.getElementById('alert-banner').innerHTML = html
    }
</script>

</html>