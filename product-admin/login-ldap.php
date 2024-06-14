<?php
// init session
session_start();

// set global for ldap server IP

$GLOBALS['ldap_server'] = '10.11.20.43';

// check for logged in. If yes send to dashboard
if (isset($_SESSION["pa_loggedin"]) && $_SESSION["pa_loggedin"] === true) {
    header("location: index.php");
    exit;
};
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


function get_user_deets($ldapUser)
{
    require_once 'config.php';
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());

    // First query to get user details
    $sql = "SELECT er.empNumber, er.empName, er.deptNumber, ur.role_id
    FROM emp_ref er
    JOIN user_ref ur on ur.emp_num = er.empNumber
    WHERE er.email = '$ldapUser'";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {

        $_SESSION["pa_loggedin"] = true;
        $_SESSION["name"] = $row['empName'];
        $_SESSION["empNumber"] = $row['empNumber'];
        $_SESSION["role_id"] = $row['role_id'];
        // Second query to get department info based on dept_head
        $dep_sql = "SELECT dep_num
        FROM departments
        WHERE dep_head = '" . $row['empNumber'] . "' OR dep_assist = '" . $row['empNumber'] . "' OR dep_asset_mgr = '" . $row['empNumber'] . "'";

        $dep_result = mysqli_query($conn, $dep_sql);
        $dep_num_array = array();

        while ($dep_row = mysqli_fetch_assoc($dep_result)) {
            $dep_num_array[] = $dep_row['dep_num'];
        }

        $_SESSION["dep_nums"] = $dep_num_array;
    }


    $conn->close();
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
            get_user_deets($ldapUser);
            // log_user_logging_in($ldapUser);
            // update_log($ldapuser);
            $_SESSION["pa_loggedin"] = true;
            $_SESSION["user"] = $ldapUser;
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
    <meta name="Description" content="BCBCRMS Log In" />

    <link rel="stylesheet" id='test' href="../assets/css/berkstrap.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">  -->
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon.png">

    <title>Log in Screen</title>
</head>

<body class="log-in-body">
    <div class="make-blurry">
        <div class="log-in-image">
            <img src="../dept_logos/blue-seal.png" alt="bcg logo" width="150px">
        </div>
        <h1 class="log-in-h1">Store Product Management</h1>
        <div class="main-log-in-div">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> " method="post" id="logUserIn"
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
                        form="logUserIn">Log
                        In</button>
                </fieldset>
            </form>
            <?php
            if (!empty($login_err)) {
                echo '<div class="alert">' . $login_err . '</div>';
            }
            ?>
        </div>
        <div class="log-in-disclaimer">

        </div>
    </div>
</body>
<footer>
    <p class='footer-p'>Copyright &copy; 1682 - <script>
        document.write(new Date().getFullYear())
        </script> Berkeley County Government</p>
</footer>

</html>
<style>
@font-face {
    font-family: bcFont;
    /* src: url(./fonts/Gotham-Medium.otf); */
    src: url(../fonts/Gotham-Medium.otf);
}

body {
    font-family: bcFont;
    color: black;
}

.log-in-body {
    background: rgb(2, 0, 36);
    background: linear-gradient(180deg, rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
    background-image: url(./product-admin.jpg);
    background-repeat: no-repeat;
    background-size: 100vw;
    margin: 2em;
    margin-top: 50px;
    width: 90%;
    /* overflow: hidden; */
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
    background-color: whitesmoke;
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

.log-in-button:hover {
    box-shadow: 0px 0px 10px 5px rgba(220, 220, 255, 0.75);
    /* box-shadow: 0px 0px 10px 5px rgba(0, 0, 255, 0.75); */
}

.form-control {
    border: 1px solid lightblue;
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