<?php
session_start();
include_once "config.php";

if (!isset($_SESSION["cl_loggedin"]) || $_SESSION["cl_loggedin"] !== true) {

    header("location: comm-login-ldap.php");

    exit;
}
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;

$GLOBALS['ldap_server'] = '10.11.20.43';


// get Comm Employees Information and stick in SESSION variables 
function get_user_info($ldapUser)
{
    require_once 'config.php';
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());

    $sql = "SELECT empNumber, empName, fy_budget from comm_emps where email = '$ldapUser'";

    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // $_SESSION["loggedin"] = true;
            $_SESSION["isComm"] = true;
            $_SESSION["empNumber"] = $row["empNumber"];
            $_SESSION["empName"] = $row["empName"];
            $_SESSION["fy_budget"] = $row["fy_budget"];
        }
        $conn->close();
    }
}


// ldap auth function delcaration
function bcgov_ldap_authen($ldapUser, $password)
{
    $ldapDomain = "";
    $ldapHost = $GLOBALS['ldap_server'];

    $ldapConn = ldap_connect($ldapHost) or die("Could not connect to LDAP");
    if (@ldap_bind($ldapConn, $ldapUser . $ldapDomain, $password)) {

        return true;
    } else {
        return false;
    }
}

// declare variables with empty values

$ldapUser = $password = "";
$ldapUser_err = $password_err = "";
$redirect = "products-by-communications.php";
// header_remove();
// process form data when submitted 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // check for empty username and password fields
    if (empty(trim($_POST["ldapUser"]))) {
        $ldapUser_err = "Please enter your username.";
    } else {
        $ldapUser = trim($_POST['ldapUser']);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST['password']);
    }
    // validate credentials
    if (empty($ldapUser_err) && empty($password_err)) {
        if (bcgov_ldap_authen($ldapUser, $password)) {

            $_SESSION["loggedin"] = true;
            // header("location: products-by-communications.php");
        } else {
            $login_err = "Your entry does not match information on file.";
        }
    } else {
        $login_err = "Your entry not match information on file.";
    }
};



//$producttype = $_GET['productType'];
// $_SESSION['isComm'] = true;
$sql = "SELECT * FROM uniform_orders.products p 
where p.isComm = true AND p.isactive = true";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here" />

    <!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" async> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
    <link href="build/style.min.css" rel="stylesheet" defer async>
    <link rel="icon" type="image/x-icon" href="favicons/comm-favicon.ico">
    <title>Communications Products Page</title>
</head>

<body>

    <div class="container">
        <div class="container" id="nav-container">
            <!-- <//?php include "nav.php" ?> -->
            <?php include "comm-nav.php" ?>
        </div>
        <h4 class='silly-header'>Communications Department Approved Items</h4>

        <div class="row col-lg-12 products-container" id="products-container">

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['product_id'];
                    $sugerSql = "SELECT COUNT(ord._ref.product_id) as order_count from uniform_orders.ord_ref where product_id= $product_id";
                    $sugerStmt =
                        $proImage = !empty($row["image"]) ? $row["image"] : "demo-img.jpg";

            ?>
                    <!-- <img src="./dept_logos/comm.png" alt='comm-logo' class='logo-img'> -->
                    <div class="card" id="featured-card">
                        <img src="<?php echo $proImage; ?>" class="card-img-top" alt="...">
                        <div class="card-body featured">
                            <h6 class="card-title"><?php echo $row["name"]; ?> <br> Item #: <?php echo $row["code"] ?></h6>
                            <h6 class="card-subtitle mb-2">Starting at:
                                <?php echo CURRENCY_SYMBOL . number_format($row["price"], 2) . ' ' . CURRENCY; ?>
                            </h6>
                            <div class="button-holder">
                                <a href="comm-product-details-onefee.php?product_id=<?php echo $row["product_id"]; ?>" class="btn btn-info">Details</a>
                            </div>
                        </div>
                    </div>
                <?php }
            } else {
                include "product-type-not-found.php";
                ?>
                <!-- <p>Product(s) not found....</p> -->
            <?php }
            $conn->close();
            ?>
        </div>
        <div class="button-holder">
            <!-- <a href="index.php#nav-container"><button class="btn btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                    Shopping </button></a> -->
            <!-- <a class="navbar-brand" id="navbar-text" href="./comm-logout.php"><button class="btn btn-secondary"
                    type="button"><i class="fa fa-sign-out-alt" aria-hidden="true"></i> Logout</button></a> -->
        </div>
    </div>

    <!-- Form to enter employee number -->
    <div class="modal" id="modal-one">
        <div class="modal-bg modal-exit"></div>
        <div class="modal-container">
            <div class="login-image-container">
                <img src="./restricted-section.jpg" alt="This is a Restricted Section" width="30px" id="res-img"></img>
            </div>
            <br>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="logUserIn" class="log-usr-in">
                <fieldset>
                    <p class="log-in-p">
                        <label for "ldapUser">Email Address: </label>
                        <input type="text" name="ldapUser" class="form-control <?php echo (!empty($ldapUser_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ldapUser; ?>">
                        <span class="invalid-feedback"><?php echo $ldapUser_err; ?></span>
                    </p>
                    <p class="log-in-p">
                        <label for "password">Password: </label>
                        <input type="password" name="password" class="form-control <//?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </p>

                    <button class="btn btn-secondary log-in-button button" type="submit" value="Log In" form="logUserIn">Log
                        In</button>
                </fieldset>
            </form>
            <!-- <//?php include "comm-login-ldap.php" ?> -->
            <!-- <form id="employee-number-form" action="get-comm-emps.php">
                <label for="employee-number">Enter employee number:</label>
                <input type="text" id="emp_num" name="emp_num"><br><br>
                <input type="submit" value="Submit">
            </form>
            <button class="modal-close modal-exit">X</button> -->
        </div>
    </div>
    <script>
        const modals = document.querySelectorAll('#Communications');

        // console.log(modals)
        // const modals = document.querySelectorAll('[data-modal]');

        modals.forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                // console.log('cliked comm link');
                e.preventDefault();
                // getCommEmps();
                // const modal = document.getElementById(trigger.dataset.modal);
                const modal = document.getElementById('modal-one');
                // console.log(modal);
                modal.classList.add('open');
                const exits = modal.querySelectorAll('.modal-exit');
                exits.forEach(function(exit) {
                    exit.addEventListener('click', function(e) {
                        e.preventDefault();
                        modal.classList.remove('open');
                    })
                })
            })
        })
    </script>
</body>

</html>

<style>
    @font-face {
        font-family: bcFont;
        src: url(./fonts/Gotham-Medium.otf);
    }

    body {
        position: relative;
        background-color: slategray;
        font-family: bcFont !important;
        background-image: url(World\ Map.svg);
        background-repeat: no-repeat;
        background-size: cover;
    }

    .getBig {
        background-color: #93c !important;
        animation: createBox 1.0s;
        color: #93c !important;
    }

    @keyframes createBox {
        from {
            transform: scale(1);
        }

        to {
            transform: scale(100);
        }
    }

    /* .background-image {
        width: 100%;
        -webkit-mask-image: linear-gradient(transparent, black);
        mask-image: linear-gradient(transparent, black);
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        object-fit: contain;
    } */

    .products-container {
        /* display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr; */
        position: relative;
        z-index: 1;
    }

    .card {
        margin-top: 20px;
        margin-right: 20px;
        border-radius: 1px;
        box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    }

    .cart-view {
        position: relative;
        z-index: 1;
    }

    img {
        width: auto;
        /* height: 650px; */
        margin-left: auto;
        margin-right: auto;
        background-color: aliceblue;
    }

    .row>* {
        padding-right: 0px !important;
        padding-left: 0px !important;
    }

    .card-text {
        color: aliceblue;
    }

    .button-holder {
        margin-top: 20px;
        display: flex;
        align-items: flex-end;
    }


    .container h1 {
        text-align: center;
        background-color: #ffffff30;
    }

    .row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        margin-left: auto;


    }

    .navbar {
        display: flex;
        justify-content: center;
    }

    .cart-view {
        margin-left: 50px;
    }

    #featured-card {
        width: 20rem;
        position: relative;
        z-index: 0;
    }

    .featured {
        width: 20rem;
        height: 10rem;
        position: absolute;
        bottom: 0;
        z-index: 2;
        background-color: #00000080;
        transition: background-color .5s linear;
    }

    .featured:hover {
        background-color: #00000020;

        transition: background-color .5s linear;

    }

    .silly-header {
        transform: rotate(-90deg);
        position: absolute;
        left: 0;
        top: 0;
        margin-top: 400px;
        margin-left: -180px;
        color: yellow;
        font-weight: 400;
    }

    /* .logo-img {
        position: relative;
        width: 50px;
        z-index: 4;
    } */
</style>