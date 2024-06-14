<?php

session_start();

$GLOBALS['ldap_server'] = '10.11.20.43';

// check for logged in. If yes send to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
};
// created this for testing returning the employee number from ldap

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
            header("location: index.php");
        } else {
            $login_err = "Your entry does not match information on file.";
        }
    } else {
        $login_err = "Your entry not match information on file.";
    }
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here" />

    <link rel="stylesheet" id='test' href="berkstrap-dark.css" async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <title>Log In</title>
</head>

<body class='log-in-body'>
    <div class="make-blurry">
        <div class="log-in-image">
            <img src="bg-lightblue.png" alt="bcg logo" width="500px">
        </div>
        <h1 class="log-in-h1">Welcome to the County Store</h1>
        <div class="main-log-in-div">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="logUserIn"
                class="log-usr-in">
                <fieldset>
                    <p class="log-in-p">
                        <label for "ldapUser">Email Address: </label>
                        <input type="text" name="ldapUser"
                            class="form-control <?php echo (!empty($ldapUser_err)) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $ldapUser; ?>">
                        <span class="invalid-feedback"><?php echo $ldapUser_err; ?></span>
                    </p>
                    <p class="log-in-p">
                        <label for "password">Password: </label>
                        <input type="password" name="password"
                            class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </p>

                    <button class="btn btn-secondary log-in-button button" type="submit" value="Log In"
                        form="logUserIn">Log In</button>
                </fieldset>
            </form>
            <?php
            if (!empty($login_err)) {
                echo '<div class="alert">' . $login_err . '</div>';
            }
            ?>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
</body>
<footer>
    <?php include "footer.php" ?>

</footer>

</html>
<style>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

body {
    /* background-image: url(gsquares.png); */
    font-family: bcFont;
    color: black;
    /* overflow: hidden; */
}

.log-in-body {
    background: rgb(2, 0, 36);
    background: linear-gradient(180deg, rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
    /* background-image: url('./old-computer.jpg'); */
    background-image: url('https://www.scpictureproject.org/wp-content/uploads/wateree-country-store-front.jpg');
    background-repeat: no-repeat;
    background-size: 100vw;
    margin: 2em;
    margin-top: 50px;
    width: 90%;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

a {
    text-decoration: none;
    color: inherit;
}

.bd {
    position: absolute;
    bottom: 0;
    left: 0;
}

.make-blurry {
    position: relative;
    box-shadow: 0px 0px 3px 3px rgba(143, 138, 138, 0.75), 1px -1px 3px 9px rgba(74, 71, 71, 0.75);
    margin-left: 15px;
    padding-top: 2em;
    padding-bottom: 2em;
    padding: 2em;
    border: 1px solid darkgrey;
    background-color: white;
}


.log-in-main-div {
    width: 42%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin-left: 10%;

}

.main-log-in-div {

    display: grid;
    grid-template-columns: 1fr 1fr;
    /* margin-left: 10%; */

}

.log-in-h1 {
    color: var(--bc-blue);
    /* color: aliceblue; */
    font-size: xx-large;
    /* text-align: center; */
    margin-bottom: 50px;
    /* text-shadow: 0px 0px 10px black; */
}

.log-in-h2 {
    /* color: aliceblue; */
    font-size: x-large;
    padding-bottom: 10px;
}


.log-in-p label {
    margin-right: 20px;
    font-size: larger;
    align-content: right;
    align-self: right;
}

.log-in-h2 {
    margin: 15px;
}



footer {
    position: absolute;
    bottom: 0;

}

.footer-div p {
    color: black;
}

/* 1025px — 1200px: Desktops, large screens. */
@media only screen and (max-width: 1025px) {
    .log-in-body {
        background-image: none;
        background-color: var(--bc-dark-blue);
    }

    .make-blurry {
        align-self: center;
    }

}

/* small screens and laptops  */
@media only screen and (max-width: 768px) {
    .log-in-image img {
        width: 200px;
    }

    .log-in-h1 {
        font-size: medium;
    }

    .make-blurry {
        width: 500px;
    }
}

/* 481px — 768px: iPads, Tablets. */
@media only screen and (max-width: 480px) {
    .log-in-image img {
        width: 100px;
    }

    .log-in-h1 {
        font-size: smaller;
    }

    .make-blurry {
        width: 250px;
    }
}

/* 320px — 480px: Mobile devices */
@media only screen and (max-width: 480px) {
    .log-in-image img {
        width: 100px;
    }

    .log-in-h1 {
        font-size: smaller;
    }

    .make-blurry {
        width: 200px;
    }

    .footer-div {
        font-size: smaller;
    }
}
</style>