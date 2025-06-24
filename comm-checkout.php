<?php
session_start();
require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init class
include_once 'Cart.class.php';
$cart = new Cart;

$empNumber = $_SESSION['empNumber'];
// var_dump($empNumber);
$sql = "SELECT comm_emps.empName, comm_emps.empNumber, comm_emps.fy_budget,
ifnull(SUM(ord_ref.line_item_total), 0.00) total
FROM uniform_orders.comm_emps
LEFT JOIN ord_ref on ord_ref.emp_id = comm_emps.empNumber
WHERE comm_emps.empNumber = $empNumber";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $empSpending = $row['total'];
    }
}


// if cart is empty send back to products page
if ($cart->total_items() <= 0) {
    header("Location: index.php");
}

// $_SESSION['comm_emp_num'] = '5453';
// echo var_dump($_SESSION['comm_emp_num']);
// echo ($_SESSION['isComm']);
// echo ($_SESSION['fy_budget']);
// echo ($_SESSION['empNumber']);
// echo ($_SESSION['empName']);
// echo $empSpending;
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
    <link rel="icon" type="image/x-icon" href="favicons/comm-favicon.ico">

    <script>
    // async function to set captcha then
    // fetch the captcha from empcaptcha.php endpoint then
    // convert response into json
    async function setCaptcha(empID) {
        await fetch('./empCaptcha.php?emp_num=' + empID)
            .then((response => response.json()))
        // .then(data => console.log('Captcha set to: ' + empID))
    }

    // fetch employee data from server using supplied employee ID then
    // convert response into json then
    // split the name into first and last then
    // build HTML string then
    // write HTML to page
    // async function getEmpData(empID) {
    //     const employee = await fetch("./getEmpData.php?empNum=" + empID)
    //         .then((response) => response.json())
    //         .then(data => {
    //             const name = data[0].empName.split(" ");
    //             let fname = name[0];
    //             let lname = name[1];
    //             var html = "<p>" + data[0].empName + "</p>";
    //             html += "<input type='hidden' name='first_name' value=" + fname + ">";
    //             html = "<p>" + data[0].empName + "</p>";
    //             html += "<input type='hidden' name='first_name' value=" + fname + ">";
    //             html += "<input type='hidden' name='last_name' value=" + lname + ">";
    //             html += "<p name='department'>" + data[0].deptName + "</p>";
    //             html += "<input type='hidden' name='department' value=" + data[0].deptNumber + ">";
    //             html += "<p name='email'>" + data[0].email + "</p>";
    //             html += "<input type='hidden' name='email' value=" + data[0].email + ">";
    //             html += "<input type='hidden' name='last_name' value=" + lname + ">";
    //             // html += "<p name='department'>" + data[0].deptName + "</p>";
    //             // html += "<input type='hidden' name='department' value=" + data[0].deptNumber + ">";
    //             // html += "<p name='email'>" + data[0].email + "</p>";
    //             // html += "<input type='hidden' name='email' value=" + data[0].email + ">";
    //             html += "<input type='hidden' name='emp_number' id='emp_number' value='" + data[0].empNumber +
    //                 "' >"
    //             console.log('empdata :' + data[0].empNumber)
    //             // setCaptcha(data[0].empNumber)
    //             document.getElementById('empInfo').innerHTML = html

    //         })

    // }

    // refactoring function above to automatically populate the "order is for" fields since we have the empplyee data coming into the page. **This only works for Communications checkout because the employee is logged in**
    async function getEmpData(empID) {
        const employee = await fetch("./getEmpData.php?empNum=" + empID)
            .then((response) => response.json())
            .then(data => {
                const name = data[0].empName.split(" ");
                let fname = name[0];
                let lname = name[1];
                var html = "<h5> This request is for: </h5>";
                html += "<p>" + data[0].empName + "</p>";
                html += "<input type='hidden' name='first_name' value=" + fname + ">";
                html += "<input type='hidden' name='last_name' value=" + lname + ">";
                html += "<p name='department'>" + data[0].deptName + "</p>";
                html += "<input type='hidden' name='department' value=" + data[0].deptNumber + ">";
                html += "<p name='email'>" + data[0].email + "</p>";
                html += "<input type='hidden' name='email' value=" + data[0].email + ">";
                html += "<input type='hidden' name='emp_number' id='emp_number' value='" + data[0].empNumber +
                    "' >"
                // console.log('getEmpData called and rendered html');
                document.getElementById('empInfo').innerHTML = html;
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
                html2 += "<input type='hidden' name='sub_number' id='sub_number' value='" + data[0]
                    .empNumber +
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

    }

    function toggleButton() {
        document.getElementById('place-order-button').classList.remove("hidden");
        document.getElementById('place-order-button').disabled = false;
    }

    // This code  performs a filter function. It gets the user's input from an HTML element with the id "empInput". It also gets references to different elements with the tag name button which are contained within an element with the id of "empDropdown".
    // It then looks through each of the elements that it found searching for any words that match the user's input (the input is converted to upper case for consistency). If it finds a match, it sets the style attribute of the element to make it visible; otherwise it sets it to none which hides the element. filterFunctionSub (below) does the same thing for a differnt input.
    function filterFunction() {
        // console.log('starting filter');
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
    // This function is actully called in getSubNumSearch.php which is an include below. Read this instead of wasting 30 minutes looking for the actual call.
    function setSub(empID) {
        getSubData(empID)
        // alert('Submitter set to: ' + empID);
        setCaptcha(empID);
        subToggle();
        document.getElementById('captcha-holder').classList.remove("hidden");

    }

    // function to set employee data
    // get employee data by empid then
    // toggle data
    function setData(empID) {
        getEmpData(empID);
        // setCaptcha(empID);
        toggle();
    }

    // function to check the cart total with tax and if the amount is > $150 AND isComm is true - then.... idk.. do something so they can not submit the request and make them go remove items... THEN change the amount from $150 to the value returned from a query to comm_emps table. Will also have to change the function to set isComm session variable to also include the empNumber entered so that can be used to access the allowed amount per employee

    function checkTotal() {
        getEmpData(
            <?php echo $empNumber ?>
        ); // placing function call here here since checkTotal is already called on place load
        var cartTotal = <?php echo ($cart->total() * 1.09) ?>;
        var allowence = <?php echo $_SESSION['fy_budget'] ?>;
        var spending = <?php echo $empSpending ?>;
        var warningHolder = document.getElementById('warning-holder');
        var fieldset = document.getElementById('fieldset');
        var addButton = document.getElementById('add-items');
        var editButton = document.getElementById('edit-cart');
        var removeButton = document.getElementById('remove-items');
        var contactHeader = document.getElementById('contact-header');
        var rainbow = document.getElementsByClassName('rainbow');
        // console.log(addButton);
        if ((cartTotal + spending) > allowence) {
            var html = "<h1>WOAHHHH COWBOY! YOU CAN'T SPEND MORE THAN $" + allowence + "!!!!!</h1>";
            html += "<span class='spending-info'><p>You have spent $ " + spending.toFixed(2) +
                " so far this year.</p>";
            html += "<p> This request will exceed your budget by $ " + ((cartTotal + spending) - allowence)
                .toFixed(2) +
                "</p></span>";
            warningHolder.innerHTML = html;
            // addButton.style.visibility = 'hidden';
            // editButton.style.visibility = 'hidden';
            addButton.style.display = 'none';
            editButton.style.display = 'none';
            removeButton.classList.remove('hidden');
            fieldset.setAttribute('disabled', "true");
            contactHeader.style.color = "#5A5A5A40";
        } else {
            warningHolder.style.display = 'none';
            html = "";
        }
    }
    // var isComm = <//?php echo $_SESSION['isComm'] ?>;
    // console.log(isComm);
    </script>

</head>

<body onload='checkTotal()'>

    <div class="container">
        <h1>Checkout</h1>
        <div class="col-12">
            <div class="checkout">
                <div id='warning-holder'></div>
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
                            <span class="text-muted"><?php echo ($_SESSION['empName']); ?>'s Cart</span>
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
                                        <?php echo $sizeRow['size_name'] ?> -
                                        <img src="<?php echo $item['logo'] ?>" alt="bc logo" id="logo-img"></small>

                                </div>
                                <span
                                    class="text"><?php echo CURRENCY_SYMBOL . number_format($item["subtotal"], 2); ?></span>
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
                        <div class="button-holder">
                            <a href="products-by-communications.php" class="btn btn-sm btn-info" id="add-items"><i
                                    class="fa fa-plus" aria-hidden="true"></i>
                                Add More Items</a>
                            <a href="comm-viewCart.php" class="btn btn-sm btn-warning" id="edit-cart"><i
                                    class="fa fa-edit" aria-hidden="true"></i>
                                Edit Cart</a>
                            <a href="comm-viewCart.php" class="btn btn-sm btn-danger hidden" id="remove-items"><i
                                    class="fa fa-minus" aria-hidden="true"></i>
                                Remove Items</a>
                        </div>
                    </div>
                    <div class="col-md-8 order-md-1">
                        <h4 class="mb-3" id="contact-header">Contact Details</h4>
                        <form method="post" action="cartAction.php" id='order-form'>
                            <fieldset id='fieldset'>
                                <div class="emp-row">
                                    <div class="col-md-6 mb-3">
                                        </ /?php include "getEmpNumSearch.php" ?>
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
                                    <input type="text" id="captcha" name="captcha_challenge" pattern="[0-9]{4}"
                                        onchange="toggleButton()">

                                </div>

                                <input type="hidden" name="action" value="placeOrder" />
                                <input class="btn btn-success btn-block hidden" id="place-order-button" type="submit"
                                    name="checkoutSubmit" value="Place Order" disabled>
                            </fieldset>
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

#logo-img {
    width: 20px;
}

#warning-holder h1 {
    color: red;
    font-size: 2.5em;
    font-weight: bolder;
    animation: blinker .75s linear infinite;
}

.button-holder {
    display: flex;
    justify-content: center;
    gap: 1em;
}

@keyframes rotate {
    100% {
        transform: rotate(1turn);
    }
}

.rainbow {
    position: relative;
    z-index: 0;
    width: 60em;
    height: 15em;
    border-radius: 10px;
    overflow: hidden;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-items: center;
    text-align: center;

}

.rainbow::before {
    content: '';
    position: absolute;
    z-index: -2;
    left: -50%;
    top: -50%;
    width: 200%;
    height: 200%;
    background-color: #399953;
    background-repeat: no-repeat;
    background-size: 50% 50%, 50% 50%;
    background-position: 0 0, 100% 0, 100% 100%, 0 100%;
    background-image: linear-gradient(#399953, #399953), linear-gradient(#fbb300, #fbb300), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);
    animation: rotate 4s linear infinite;
}

.rainbow::after {
    content: '';
    position: absolute;
    z-index: -1;
    left: 6px;
    top: 6px;
    width: calc(100% - 12px);
    height: calc(100% - 12px);
    background: white;
    border-radius: 5px;
    animation: opacityChange 3s infinite alternate;
}

.spending-info {
    display: flex;
    justify-content: space-between;
    margin-left: 3em;
    margin-right: 3em;
}

@keyframes opacityChange {
    50% {
        opacity: 1;
    }

    100% {
        opacity: .5;
    }
}


@keyframes blinker {
    50% {
        opacity: 0;
    }
}
</style>