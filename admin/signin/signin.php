<?php

session_start();
include_once "config.php";

$loginfailure = false;
$GLOBALS['ldap_server'] = '10.11.20.43';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: ../pages/employeeRequests.php");
    exit;
}
// come back to this later 
// $csrf_token = bin2hex(random_bytes(32));
// $_SESSION["csrf_token"] = $csrf_token;
include("func.php");

function checkUser($username)
{
    include_once "dbConfig.php";
    $db = new dbConfig;
    $serverName = $db->serverName;
    $database = $db->database;
    $uid = $db->uid;
    $pwd = $db->pwd;

    $conn = mysqli_connect($serverName, $uid, $pwd, $database);

    $sql = "SELECT u.emp_num, u.role_id, u.user_name, er.deptNumber, er.email, er.empName, 
        r.role_name
        FROM users u
        JOIN emp_ref er on er.empNumber = u.emp_num
        JOIN roles r on r.role_id = u.role_id
        WHERE u.user_name = '$username'
        ";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $_SESSION["loggedin"] = true;
            $_SESSION["loggedinuser"] = $row['user_name'];
            $_SESSION["department"] = $row['deptNumber'];
            $_SESSION["userName"] = $row['empName'];
            $_SESSION["empNumber"] = $row["emp_num"];
            $_SESSION['role_id'] = $row["role_id"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["role_name"] = $row["role_name"];
            $conn->close();
            return true;
        }
    } else {
        header("location: signin.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     var_dump($_POST['csrf_token']);
    //     var_dump($_SESSION['csrf_token']);
    //     exit('CSRF attack detected');
    // }

    if (isset($_POST['username'])) {
        if (validateCredentials($_POST['username'] . $GLOBALS['ldap_domain'], $_POST['password'])) {
            $_SESSION['happy'] = "maybe";
            $_SESSION['validUser'] = true;

            if (checkUser($_POST['username'])) {
                header("Location: ../pages/employeeRequests.php");
            } else {
                $loginfailure = true;
                $_SESSION['loggedin'] = false;
                unset($_SESSION['loggedinuser']);
                header("Location: signin.php");
            }
        } else {
            $loginfailure = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="icon" type="image/png" href="../../favicons/favicon-32x32.png" sizes="32x32" />

    <title>Admin Access Login</title>
    <link rel="stylesheet" href="../pages/style/backend.css">
    <link rel="stylesheet" href="../pages/style/custom.css">
    <script src="../pages/functions/logAction.js"></script>
</head>

<body>
    <div class="main-container">
        <div class="form-container">
            <img src="../assets/img/bcg-tan.png" alt="BCG logo" class="logo" />
            <form method="post">
                <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="inputUserName">User Name</label>
                    <input type="text" id="inputUserName" type="username" name="username" class="form-control"
                        placeholder="first.last" aria-label="Enter your username" tabindex="1" />

                </div>

                <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="inputPassword">Password</label>
                    <input type="password" name="password" id="inputPassword" class="form-control"
                        placeholder="password" tabindex="2" />
                </div>

                <div class="text-center pt-1 mb-5 pb-1">
                    <button data-mdb-button-init data-mdb-ripple-init class="btn btn-success" type="submit" tabindex="3"
                        id="submitButton" disabled>Login
                        to your account</button>
                </div>
                <p id='errorMessage' class='errorMessage'></p>
                <?php if ($loginfailure) {
                    echo "<div style='color: darkred;'>The information provided does not match information on file.</div>";
                } ?>
            </form>
        </div>
        <div class="image-container">
            <div class="image-container-content">
                <blockquote class="quote">Behind every great team, there is a great web store.</blockquote>
                <cite class="attribution">- Abraham Lincoln</cite>
            </div>
        </div>

    </div>
    <?php include "../../footer.php"  ?>
</body>

</html>
<script>
const input = document.getElementById('inputUserName');
const submitButton = document.getElementById('submitButton');
const errHolder = document.getElementById('errorMessage');
input.addEventListener('keyup', () => {
    console.log('key up');
    if (input.value.includes('@')) {
        input.classList.add('error');
        submitButton.setAttribute('disabled', 'disabled');
        errHolder.innerText =
            'Please enter a valid username. The @ symbol not allowed. Your user name is the first.last format.'

    } else {
        input.classList.remove('error');
        submitButton.removeAttribute('disabled');
        errHolder.innerText = ''
    }
})
</script>
<style>
@font-face {
    font-family: 'bcg';
    src: url('../../fonts/Gotham-Medium.otf');
}

.main-container {
    display: grid;
    grid-template-columns: 30% 70%;
    font-family: bcg;
}

.form-container {
    background-color: #fff;
    height: 90dvh;
    padding: 20px;
    margin: 0;
    margin-top: 50px;

    h4 {
        margin-top: 40px;
        color: #FFFFFF;
    }

    img {
        width: 100%;
    }

    form {
        margin-top: 40px;
        padding: 30px;
        width: 100%;
        /* background-color: #ffffff50; */
        color: #000;

        input {
            margin: 10px;
            width: 100%;
        }

        button {
            margin-top: 20px;
        }

        button:hover {

            background-color: #256141;
        }
    }
}


.image-container {
    height: 100dvh;
    padding: 0;
    margin: 0;
    background-image: url('../assets/img/shopping_bags.jpg');
    background-size: cover;
    backdrop-filter: blur(5px);
}

.image-container-content {
    background-color: #00000090;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 10%;

    p {
        color: antiquewhite;
    }
}

.quote {
    font-style: italic;
    font-size: 2.2em;
    margin-bottom: 1em;
    color: antiquewhite;
}

.attribution {
    font-style: italic;
    font-size: 1.8em;
    color: #999;
}

.footer-holder {
    background-color: #9c9c9c !important;
    color: #000 !important;
}

.error {
    background-color: #ff000050;
}

.errorMessage {
    color: darkred;
    font-size: medium;
}
</style>