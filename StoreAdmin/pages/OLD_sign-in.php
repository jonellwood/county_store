<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/05/2024
Purpose: Log in page for users to access the store backend admin pages.
Includes:     viewHead.php, footer.php
*/
// init session
session_start();

// set global for ldap server IP
// $ldap_server = '10.11.20.43';
$GLOBALS['ldap_server'] = '10.11.20.43';

// check for logged in. If yes send to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  header("location: employeeRequests.php");
  exit;
};


// ldap auth function delcaration
function bcgov_ldap_authen($ldapUser, $password)
{
  $ldapDomain = "@berkeleycountysc.gov";
  $ldapHost = $GLOBALS['ldap_server'];

  $ldapConn = ldap_connect($ldapHost) or die("Could not connect to LDAP");
  if (@ldap_bind($ldapConn, $ldapUser, $password)) {
    header("location: employeeRequests.php");
    return true;
  } else {
    header("location: topNav.php");
    return false;
  }
}

// get user information and set session variables upon auth function declartation
function user_auth($username)
{
  require_once "DBConn.php";
  // echo $ldapUser;
  $sql = "SELECT u.emp_num, u.role_id, u.user_name, er.deptNumber, er.email, er.empName, r.role_name
        FROM users u
        JOIN emp_ref er on er.empNumber = u.emp_num
        JOIN roles r on r.role_id = u.role_id
        WHERE u.user_name = '$username'
        ";
  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $_SESSION["loggedin"] = true;
      $_SESSION["department"] = $row['deptNumber'];
      $_SESSION["userName"] = $row['empName'];
      $_SESSION["empNumber"] = $row["emp_num"];
      // leaving isAdmin in place for code that is already in place. idAdmin will be replaced by roles moving forward
      // $_SESSION['isAdmin'] = $row["isAdmin"];
      $_SESSION['role_id'] = $row["role_id"];
      $_SESSION["email"] = $row["email"];
      $_SESSION["role_name"] = $row["role_name"];
      $conn->close();
    }
  } else {
    header("location: sign-in.php");
  }
}


// declare variables with empty values

$username = $password = "";
$username_err = $password_err = "";

// process form data when submitted 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // check for empty username and password fields
  if (empty(trim($_POST["ldapUser"]))) {
    $username_err = "Please enter your username.";
  } else {
    $ldapDomain = "@berkeleycountysc.gov";
    $username = trim($_POST['username']);
  }

  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST['password']);
  }
  // validate credentials
  if (empty($username_err) && empty($password_err)) {
    if (bcgov_ldap_authen($username . $ldapDomain, $password)) {
      // user_auth($username);
      // echo "success with ldap";
      header("location: employeeRequests.php");
    } else {
      $login_err = $username . "Information does not match information on file.";
    }
  } else {
    $login_err = $password .  "Information does not match information on file.";
  }
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../StoreAdmin/assets/img/apple-touch-icon-bcg.png">
    <link rel="icon" type="image/png" href="../../StoreAdmin/assets/img/bcg-favicon.ico">
    <title>
        Berkeley County Store Login
    </title>
    <!--     Fonts and icons     -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" /> -->
    <!-- Nucleo Icons -->
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <!-- <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script> -->
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <!-- <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" /> -->
    <link href="prod-admin-style.css" rel="stylesheet" />
    <link href="../../build/style.max.css" rel="stylesheet" />
    <link href="../../index23.css" rel="stylesheet" />
</head>

<!-- <main> -->
<section>
    <!-- <div class="">
      <div class="">
        <div class="">
          <div class=""> -->
    <div class="left">
        <img src="../assets/img/bcg-hz (4).png" alt="Trulli" />
        <div>
            <h4><strong>Berkeley County Store Admin </strong></h2>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <!-- Email/UN input -->
                    <div class="">
                        <label for="ldapUser">Email: </label>
                        <input type="text" name="ldapUser"
                            class=" <?php echo (!empty($ldapUser_err)) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $ldapUser; ?>">
                        <span class="invalid-feedback"><?php echo $ldapUser_err; ?></span>
                    </div>
                    <!-- Password input -->
                    <div class="">
                        <label for="password">Password: </label>
                        <input type="password" name="password"
                            class=" <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="">
                        <button type="submit" class="">Sign in</button>
                    </div>
                </form>
                <?php
        if (!empty($login_err)) {
          echo '<div class="alert">' . $login_err . '</div>';
        }
        ?>
        </div>
    </div>
    </div>
    <div class="right">
        <div class="right-content">
            <h4 class="">Behind Every Great Team, is a Great Store.</h4>
            <p class="">Welcome to the Berkeley County Store -- Backend</p>
        </div>
    </div>
</section>

<?php include "../../footer.php"  ?>
</body>

</html>
<style>
@font-face {
    font-family: 'bcg';
    src: url('../../fonts/Gotham-Medium.otf');
}


section {
    display: grid;
    grid-template-columns: 30% 70%;
    font-family: bcg;
}

.left {
    background-color: #000000;
    height: 100dvh;
    padding: 20px;
    margin: 0;

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
        color: #FFFFFF;

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

.right {
    height: 100dvh;
    padding: 0;
    margin: 0;
    background-image: url('../assets/img/shopping_bags.jpg');
    background-size: cover;
    backdrop-filter: blur(5px);
}

.right-content {
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
</style>