<?php
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Berkeley County Store Support" />

    <link href="./style/global-variables.css" rel="stylesheet" />
    <link href="./style/storeLux.css" rel="stylesheet" />
    <link href="./style/custom.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <title>Contact Support - Berkeley County Store</title>
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

<body class="body">
    <?php include "components/viewHead.php" ?>

    <div class="container" style="margin-top: 2rem; margin-bottom: 4rem;">
        <section class="support-section">
            <h2 class="h1-responsive font-weight-bold mb-3">Contact Support</h2>
            <p class="text-muted mb-5">Find a bug? Something not working as expected? Please don't hesitate to contact us directly. Our team will respond as quickly as possible to assist you.</p>

            <div class="row">
                <!--Contact Form Column-->
                <div class="col-md-8 mb-md-0 mb-5">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="contact-form" name="contact-form" action="supportMail.php" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Your Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" id="subject" name="subject" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Your Message *</label>
                                    <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="sendMailBtn" class="btn btn-primary px-4">Send Message</button>
                                </div>
                            </form>
                            <div class="status mt-3"></div>
                        </div>
                    </div>
                </div>


                <!--Quick Links Column-->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Quick Links</h5>
                            <ul class="list-unstyled support-links">
                                <li class="mb-3">
                                    <a href="index.php" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/home-2.svg" alt="home" width="24" class="me-3">
                                        <span>Back to Main Page</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="#" onclick="seeSpending(); return false;" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/piggy-bank.svg" alt="spending" width="24" class="me-3">
                                        <span>See My Spending</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="request-items.php" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/add.svg" alt="request items" width="24" class="me-3">
                                        <span>Request Items</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="changelogView.php" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/change-log.svg" alt="change log" width="24" class="me-3">
                                        <span>Change Log</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="https://www.companycasuals.com/printcharleston/start.jsp" target="_blank" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/lcn.svg" alt="lcn" width="24" class="me-3">
                                        <span>Print Charleston</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="https://store.berkeleycountysc.gov/admin/signin/signin.php" target="_blank" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/admin-access.svg" alt="admin" width="24" class="me-3">
                                        <span>Admin Access</span>
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <a href="https://store.berkeleycountysc.gov/inventory/login-ldap.php" target="_blank" class="d-flex align-items-center text-decoration-none">
                                        <img src="assets/icons/inventory-mgmt.svg" alt="inventory" width="24" class="me-3">
                                        <span>Inventory Management</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
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

    <footer>
        <?php include "footer.php" ?>
    </footer>
</body>

</html>

<style>
    .support-section {
        max-width: 1200px;
        margin: 0 auto;
    }

    .support-links a {
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .support-links a:hover {
        color: var(--color-primary);
        transform: translateX(5px);
    }

    .support-links img {
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }

    .support-links a:hover img {
        opacity: 1;
    }

    .card {
        border: 1px solid var(--border-light);
        border-radius: 0.5rem;
    }

    .card-title {
        color: var(--text-primary);
        font-weight: 600;
        border-bottom: 2px solid var(--color-primary);
        padding-bottom: 0.5rem;
    }

    .form-control {
        background-color: #f7f7f9 !important;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-primary);
    }

    .btn-primary {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }

    .btn-primary:hover {
        background-color: var(--color-primary-hover);
        border-color: var(--color-primary-hover);
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