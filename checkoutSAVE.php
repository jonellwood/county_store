<?php
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout</title>
    <meta charset="utf-8">
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <script>
        // async function to set captcha
        async function setCaptcha(EMPID) {

            // fetch the captcha from empcaptcha.php endpoint
            await fetch('./empCaptcha.php?EMP_num=' + empID)

                // convert response into json
                .then((response => response.json()))

                // log the captcha set for given employee id
                .then(data => console.log('Captcha set to: ' + empID))
        }


        // fetch employee data from server using supplied employee ID
        async function getEmpData(EMPID) {
            const employee = await fetch("./getEmpData.php?EMPNum=" + empID)
                .then((response) => response.json())
                .then(data => {
                    // split the name into first and last
                    const name = data[0].empName.split(" ");
                    let fname = name[0];
                    let lname = name[1];
                    // build HTML string
                    var html = "<p>" + data[0].empName + "</p>";
                    html += "<input type='hidden' name='first_name' value=" + fname + ">";
                    html = "<p>" + data[0].empName + "</p>";
                    html += "<input type='hidden' name='first_name' value=" + fname + ">";
                    html += "<input type='hidden' name='last_name' value=" + lname + ">";
                    html += "<p name='department'>" + data[0].deptName + "</p>";
                    html += "<input type='hidden' name='department' value=" + data[0].deptNumber + ">";
                    html += "<p name='email'>" + data[0].email + "</p>";
                    html += "<input type='hidden' name='email' value=" + data[0].email + ">";
                    html += "<input type='hidden' name='last_name' value=" + lname + ">";
                    html += "<input type='hidden' name='emp_number' id='emp_number' value='" + data[0].empNumber +
                        "' >"
                    // write HTML to page
                    document.getElementById('empInfo').innerHTML = html
                })

        }

        // Fetch employee data with given ID Number
        async function getSubData(empID) {
            const submitter = await fetch("./getEmpData.php?EMPNum=" + empID)
                .then((response) => response.json())
                .then(data => {
                    // Get first and last name from `EMPName` field in fetched data
                    const name = data[0].EMPName.split(" ");
                    let fname = name[0];
                    let lname = name[1];
                    // Create html markup with employee data
                    var html2 = "<p>" + data[0].EMPName + "</p>";
                    // html2 += "<input type='hidden' name='sub_first_name' value=" + fname + ">";
                    html2 += "<p>" + data[0].deptName + "</p>";
                    // Add input fields with names autofilled based on employee data
                    html2 += "<input type='hidden' name='sub_first_name' value=" + fname + ">";
                    html2 += "<input type='hidden' name='sub_last_name' value=" + lname + ">";
                    html2 += "<p name='email'>" + data[0].email + "</p>";
                    html2 += "<input type='hidden' name='sub_email' value=" + data[0].email + ">";
                    html2 += "<input type='hidden' name='sub_number' id='sub_number' value='" + data[0].empNumber +
                        "' >";
                    html2 += "<br>";
                    // Display prompt for user
                    html2 += "<hr>";
                    html2 += "<p>" + data[0].empName + " Please enter your employee ID Number </p>"
                    document.getElementById('subInfo').innerHTML = html2;
                })
        }




        // The function toggle() is used to open and close dropdown menus
        function toggle() {
            // Get the element with the ID "empDropdown"
            document.getElementById("empDropdown").classList.toggle("show");

            // Toggle the class "show" to show / hide the dropdown
        }


        // Function to toggle the subDropdown and show captcha holder and place order button 
        function subToggle() {
            // Get elements by ID so they can be manipulated
            let subDropdown = document.getElementById("subDropdown");
            let captchaHolder = document.getElementById('captcha-holder');
            let placeOrderButton = document.getElementById('place-order-button');

            // Toggle show class of subDropdown element
            subDropdown.classList.toggle("show");

            // Remove hidden class of captchaHolder and placeOrderButton elements
            captchaHolder.classList.remove("hidden");
            placeOrderButton.classList.remove("hidden");
        }



        // Filter function that takes input from the html page and then 
        // hides or displays the items in the list according to the input
        function filterFunction() {
            // console.log('starting filter'); COMMENT: Logs of the start of the filter operation
            var input, filter, ul, li, a, i;

            // Gets the element with the id "empInput" which is used as an input field 
            input = document.getElementById("empInput");

            // Get the text entered by the user and convert it to uppercase
            filter = input.value.toUpperCase();

            // Gets the element with the id "empDropdown" which contains the buttons
            div = document.getElementById("empDropdown");

            // Get all 'button' elements from within the div tag
            a = div.getElementsByTagName("button");

            // iterate over each button element
            for (i = 0; i < a.length; i++) {
                txtValue = a[i].textContent || a[i].innerText; // get button text value

                // if the button text matches with that of the user input text
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = ""; // display it
                } else {
                    a[i].style.display = "none"; // otherwise hide it
                }
            }
        }


        function filterFunctionSub() {
            // console.log('starting filter');
            var input, filter, ul, li, a, i;
            // Get elements from the DOM
            input = document.getElementById("subInput");
            filter = input.value.toUpperCase();
            div = document.getElementById("subDropdown");
            a = div.getElementsByTagName("button");
            // Loop through items in the dropdown and compare with filter input
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

        function setSub(EMPID) {
            // get the data using EMPID  
            getSubData(EMPID);

            // call the setCaptcha function
            setCaptcha(EMPID);

            // toggle the functionality of submitting
            subToggle();
        }


        // function to set employee data
        function setData(EMPID) {
            // get employee data by empid
            getEmpData(EMPID);

            // toggle data
            toggle();
        }
    </script>

</head>

<body>
    <div class="container">
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
                    <div class="col-md-4 order-md-2 mb-4">
                        <h4 class="d-flex justify-content-between align-items-center-md mb-3">
                            <span class="text-muted">Your Cart</span>
                            <span class="badge badge-secondary badge-pill"><?php echo $cart->total_items(); ?> item(s)
                            </span>
                        </h4>
                        <ul class="list-group mb-3">
                            <?php
                            if ($cart->total_items() > 0) {
                                // get cart items from session
                                $cartItems = $cart->contents();
                                // var_dump($cartItems);
                                foreach ($cartItems as $item) {
                                    $sizeSql = "SELECT size as size_name from uniform_orders.sizes WHERE size_id = '$item[size_id]'";
                                    $sizeStmt = $conn->prepare($sizeSql);
                                    $sizeStmt->execute();
                                    $sizeResult = $sizeStmt->get_result();
                                    while ($sizeRow = $sizeResult->fetch_assoc()) {
                            ?>
                                        <li class="list-group-item d-flex justify-content-between lh-condensed">
                                            <div>
                                                <h6 class="my=0"><?php echo $item["name"]; ?></h6>
                                                <small><?php echo CURRENCY_SYMBOL . number_format($item["price"], 2); ?> -
                                                    (<?php echo $item["qty"]; ?>)</small>
                                                <small><?php echo $item['color_id']; ?> -
                                                    <?php echo $sizeRow['size_name'] ?></small>

                                            </div>
                                            <span class="text"><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2); ?></span>
                                        </li>

                            <?php }
                                }
                            } ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Sub-Total (<?php echo CURRENCY; ?>) </span>
                                <strong><?php echo CURRENCY_SYMBOL . number_format($cart->total(), 2); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Sales-Tax (<?php echo CURRENCY; ?>) </span>
                                <?php $sales_tax = ($cart->total() * 0.09) ?>
                                <strong><?php echo CURRENCY_SYMBOL . number_format(($sales_tax), 2) ?>
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Cart Total (<?php echo CURRENCY; ?>)</span>
                                <strong><?php echo CURRENCY_SYMBOL . number_format(($cart->total() + $sales_tax), 2) ?></strong>
                            </li>

                        </ul>
                        <a href="index.php#nav-container" class="btn btn-sm btn-info"><i class="fa fa-plus" aria-hidden="true"></i>
                            Add More Items</a>
                    </div>
                    <div class="col-md-8 order-md-1">
                        <h4 class="mb-3">Contact Details</h4>
                        <form method="post" action="cartAction.php">
                            <div class="emp-row">
                                <div class="col-md-6 mb-3">
                                    <?php include "getEmpNumSearch.php" ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p id="empInfo"></p>

                                </div>

                            </div>

                            <div class="ord-row">
                                <div class="col-md-6 mb-3">
                                    <?php include "getSubNumSearch.php" ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p id="subInfo"></p>
                                </div>
                            </div>

                            <div class='captcha-holder hidden' id='captcha-holder'>
                                <!-- <label for="captcha">Please Enter Your Employee Number</label> -->

                                <br>
                                <input type="text" id="captcha" name="captcha_challenge" pattern="[0-9]{4}">

                            </div>

                            <input type="hidden" name="action" value="placeOrder" />
                            <input class="btn btn-success btn-block hidden" id="place-order-button" type="submit" name="checkoutSubmit" value="Place Order">

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>


</html>
<script>






</script>

<style>
    .captcha-holder {
        display: grid;
        grid-template-rows: 1fr 1fr;
    }

    #place-order-button {
        margin-top: 20px;
    }

    img {
        margin-bottom: 10px;
    }

    .emp-row {
        display: grid;
        grid-template-rows: 1fr;
    }

    .ord-row {
        display: grid;
        grid-template-rows: 1fr;
    }

    input {
        width: fit-content
    }

    .hidden {
        display: none;
    }

    .dropbtn {
        /* background-color: #04AA6D; */
        color: white;
        padding: 16px;
        font-size: 16px;
        border: none;
        cursor: pointer;
    }

    .dropbtn:hover,
    .dropbtn:focus {
        background-color: #3e8e41;
    }

    #myInput {
        box-sizing: border-box;
        background-image: url('searchicon.png');
        background-position: 14px 12px;
        background-repeat: no-repeat;
        font-size: 16px;
        padding: 14px 20px 12px 45px;
        border: none;
        border-bottom: 1px solid #ddd;
    }

    #myInput:focus {
        outline: 3px solid #ddd;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f6f6f6;
        min-width: 230px;
        overflow: auto;
        border: 1px solid #ddd;
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown button:hover {
        background-color: #ddd;
    }

    .show {
        display: block;
    }

    .invisi-button {
        background-color: transparent;
        color: black;
        border: none;
    }

    .invisi-button:hover {
        background-color: #ddd;
        border: none;
        color: #000;
    }
</style>