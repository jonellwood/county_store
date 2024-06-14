<?php
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
  $ldapDomain = "";
  $ldapHost = $GLOBALS['ldap_server'];

  $ldapConn = ldap_connect($ldapHost) or die("Could not connect to LDAP");
  if (@ldap_bind($ldapConn, $ldapUser . $ldapDomain, $password)) {

    return true;
  } else {
    return false;
  }
}

// get user information and set session variables upon auth function declartation
function user_auth($ldapUser)
{
  require_once "DBConn.php";

  $sql = "SELECT ur.emp_num, ur.role_id, ur.email, er.deptNumber, ur.role_name, ur.empName
        FROM user_ref ur
        JOIN emp_ref er on er.empNumber = ur.emp_num
        -- JOIN user_isadmin ia on er.empNumber = ia.emp_id
        WHERE ur.email = '$ldapUser'
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
      user_auth($ldapUser);
      header("location: employeeRequests.php");
    } else {
      $login_err = "Information does not match information on file.";
    }
  } else {
    $login_err = "Information does not match information on file.";
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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" />
</head>

<body class="">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">

            </div>
        </div>
    </div>
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <img src="../assets/img/bcg-hz (4).png" alt="Trulli" width="375">

                                <div class="card-body">

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <!-- Email/UN input -->
                                        <div class="form-outline mb-4">
                                            <label for="ldapUser">Email: </label>
                                            <input type="text" name="ldapUser"
                                                class="form-control <?php echo (!empty($ldapUser_err)) ? 'is-invalid' : ''; ?>"
                                                value="<?php echo $ldapUser; ?>">
                                            <span class="invalid-feedback"><?php echo $ldapUser_err; ?></span>
                                        </div>
                                        <!-- Password input -->
                                        <div class="form-outline mb-4">
                                            <label for="password">Password: </label>
                                            <input type="password" name="password"
                                                class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit"
                                                class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Sign in</button>
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
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('../assets/img/shopping2.png');
          background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">"I've been shopping all
                                    of my life and still have nothing to wear!"</h4>
                                <p class="text-white position-relative">Welcome to the Berkeley County Store -- Backend
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!--   Core JS Files   -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
    </script>

</body>

</html>