<?php
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;
include "components/viewHead.php"
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here" />

    <!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" defer async> -->

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" defer async> -->
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <!-- <link href="style.css" rel="stylesheet" defer async> -->

    <title>Contact Support</title>
    <script>
    function seeSpending() {
        // alert("Hey stop that tickles!! Dont click that.")
        const spendingPopover = document.getElementById('spendingPopover')
        spendingPopover.showPopover();
        // console.log(spendingPopover);
    }

    function submitSpendingReq() {
        var empID = document.getElementById("empID").value;
        // console.log(empID)
        fetch('my-spending-sendmail.php?emp_id=' + empID)
        // .then(console.log('Request submitted for ' + empID))
        showSpendingToast()
    }

    function showSpendingToast() {
        const spendingToast = document.getElementById('requestConfirmToast')
        spendingToast.showPopover();
        setTimeout(hideSpendingToast, 3000);
    }

    function hideSpendingToast() {
        const spendingToast = document.getElementById('requestConfirmToast')
        spendingToast.classList.add('slide-down-toast');
        spendingToast.hidePopover();
    }
    </script>

</head>

<body>
    <section class="mb-4">
        <h2 class="h1-responsive font-weight-bold text my-4">Contact us</h2>
        <!--Section description-->
        <p class="text-left w-responsive mx-auto mb-5">Find a bug? Something not working as expected? Please do not
            hesitate to contact us
            directly. Our team will come back to you as quick as possible to assist you</p>

        <div class="row">
            <!--Grid column-->
            <div class="col-md-9 mb-md-0 mb-5">
                <form id="contact-form" name="contact-form" action="supportMail.php" method="POST">
                    <!--Grid row-->
                    <div class="form-group row">
                        <!--Grid column-->
                        <div class="col-md-6">
                            <div class="md-form mb-1">
                                <input type="text" id="name" name="name" class="form-control">
                                <label for="name" class="">Your name</label>
                            </div>
                        </div>
                        <!--Grid column-->

                        <!--Grid column-->
                        <div class="col-md-6">
                            <div class="md-form mb-1">
                                <input type="text" id="email" name="email" class="form-control">
                                <label for="email" class="">Your email</label>
                            </div>
                        </div>
                        <!--Grid column-->

                    </div>
                    <!--Grid row-->

                    <!--Grid row-->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="md-form mb-1">
                                <input type="text" id="subject" name="subject" class="form-control">
                                <label for="subject" class="">Subject</label>
                            </div>
                        </div>
                    </div>
                    <!--Grid row-->

                    <!--Grid row-->
                    <div class="row">

                        <!--Grid column-->
                        <div class="col-md-12">

                            <div class="md-form">
                                <textarea type="text" id="message" name="message" rows="2"
                                    class="form-control md-textarea"></textarea>
                                <label for="message">Your message</label>
                            </div>

                        </div>
                    </div>
                    <!--Grid row-->


                    <div class="text-center text-md-left">
                        <button type="submit" name="sendMailBtn" class="btn btn-primary">Send</button>
                    </div>
                </form>
                <div class="status"></div>
            </div>
            <!--Grid column-->

            <!--Grid column-->
            <div class="col-md-3 d-flex justify-content-end gap-2">
                <ul class="list-unstyled mb-0">
                    <li>
                        <a href="https://store.berkeleycountysc.gov/index.php">
                            <p><img src="assets/icons/home-2.svg" alt="home" width="30">
                                Back to Main Page</p>
                        </a>

                    </li>
                    <li onclick=seeSpending()>
                        <p class='clickable'>
                            <img src="assets/icons/piggy-bank.svg" alt="spending" width="30"> See my
                            Spending
                        </p>
                    </li>


                    <li>
                        <a href="https://store.berkeleycountysc.gov/admin/pages/sign-in.php" target="_blank">
                            <p> <img src="assets/icons/admin-access.svg" alt="admin" width="30">Admin Access</p>
                        </a>
                    </li>
                    <li>
                        <a href="https://store.berkeleycountysc.gov/inventory/login-ldap.php" target="_blank">
                            <p>
                                <img src="assets/icons/inventory-mgmt.svg" alt="inventory" width="30">Inventory
                                Management
                            </p>
                        </a>
                    </li>
                    </li>
                    <li>
                        <a href="./changelogView.php">
                            <p><img src="assets/icons/change-log.svg" alt="change log" width="30">
                                Change Log</p>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.companycasuals.com/LOWCOUNTRYNATIVE//start.jsp" target="_blank">

                            <p><img src="assets/icons/lcn.svg" alt="lcn" width="30">
                                LowCounty Native </p>
                        </a>
                    </li>
                </ul>
            </div>
            <!--Grid column-->
        </div>

    </section>
    <div name="spendingPopover" id="spendingPopover" class="spendingPopover" popover="manual">
        <button class="close-btn" popovertarget="spendingPopover" popovertargetaction="hide">
            <span class="btn-close ms-2 mb-1" aria-hidden=”true”>X</span>
            <span class="sr-only close-text"></span>
        </button>
        <!-- <p>This feature is currently being upgraded. Please check back soon.</p> -->
        <p>This features is currently in beta. While there is every belief the information is accurate, if something
            does not seem right, please contact your store support.</p>
        <label for="empID">Please enter your employee ID Number</label>
        <input type="number" name="empID" id="empID" maxlength="5" />
        <p>If the employee number entered is valid an email with Fiscal Year details will be sent to the email address
            associate with the employee ID.</p>
        <p>If you do not have an email address from Berkeley County Government, you can still request the information
            using this form and then contact store@berkeleycountysc.gov for information on how to get the report.</p>
        <button type="button" onclick=submitSpendingReq() id="submitButton" popovertarget="spendingPopover"
            popovertargetaction="hide" disabled>Submit</button>
    </div>

    <div name="requestConfirmToast" id="requestConfirmToast" class="requestConfirmToast" popover="auto">
        <p>Your request has been submitted.</p>
    </div>

    <script>
    const empIDInput = document.getElementById('empID');
    const submitButton = document.getElementById('submitButton');
    empIDInput.addEventListener('input', (event) => {
        // Allow only numbers and backspace key
        const validChars = /^\d|\b$/;
        const userInput = event.target.value;

        if (!validChars.test(userInput)) {
            event.target.value = userInput.slice(0, -1);
            return;
        }

        if (userInput.length >= 4) {
            submitButton.removeAttribute('disabled')
        }

        // Enforce maximum of 5 digits
        if (userInput.length > 5) {
            event.target.value = userInput.slice(0, 5);
        }
    });
    </script>
</body>
<footer>
    <?php include "footer.php" ?>
</footer>

</html>
<style>
.mb-4 {
    margin: 20px;
}

.form-control {
    color: black !important;
}

.navbar {
    display: flex;
    justify-content: center;
}

.clickable {
    cursor: pointer;
}

.spendingPopover {
    padding: 20px;
    background-color: white;
    color: black;
    border: 4px solid whitesmoke;
    border-radius: 10px;
}

::backdrop {
    backdrop-filter: blur(5px);
}

.close-btn {
    border: none;
    background: none;
    color: tomato;
    position: absolute;
    right: 0.25rem;
    top: 0.5rem;
    /* filter: grayscale() brightness(20); */
    cursor: pointer;
}

.close-text {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    transition: opacity 0.25s ease-in-out;
}

.requestConfirmToast {
    position: fixed;
    right: 0;
    bottom: 0;
    margin-right: 20px;
    margin-bottom: 20px;
    background-color: #789b48;
    color: #cbc8c7;
    padding: 40px;
    animation-name: slideUp;
    animation-duration: 1.5s;
    animation-timing-function: ease-in;
    border-radius: 10px;
    box-shadow: 0px 0px 44px -4px rgba(163, 160, 163, 1);
}

@keyframes slideUp {

    0%,
    50% {
        transform: translateY(100%);
        opacity: 0;
    }

    60%,
    100% {
        transform: translateY(0);
        opacity: 1;

    }
}

.slide-down-toast {
    animation-name: slideDown;
    animation-duration: 1.5s;
    animation-fill-mode: forwards;
    /* background-color: tomato; */
    /* Keep final state after animation */
}

@keyframes slideDown {

    100%,
    60% {
        transform: translateY(100%);
        opacity: 0;
    }

    50%,
    0% {
        transform: translateY(0);
        opacity: 1;

    }
}
</style>