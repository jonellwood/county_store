<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    session_start();
    if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
        header("location: login-ldap.php");
        exit;
    }
    require_once '../config.php';
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die('Could not connect to the database server' . mysqli_connect_error());

    //$emp_id = $_SESSION['emp_id'];
    $emp_id = $_SESSION['empNumber'];
    // var_dump($emp_id);
    // var_dump($_SESSION['loggedin']);
    $usql = "SELECT dep_num, dep_name FROM uniform_orders.departments where dep_head = $emp_id OR dep_assist = $emp_id OR dep_asset_mgr = $emp_id ";
    $uresult = mysqli_query($conn, $usql);
    $ulist = array();
    while ($urow = mysqli_fetch_assoc($uresult)) {
        array_push($ulist, $urow);
    }
    function showUnauthorizedAlert()
    {
        $url = $_SERVER['REQUEST_URI'];
        $query = parse_url($url, PHP_URL_QUERY);

        if ($query === 'error=unauthorized') {
            echo '<script>alert("You are not authorized to view that department.");</script>';
        }
    }
    showUnauthorizedAlert();
    ?>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>BCG Inventory Management System</title>

    <!-- Bootstrap core CSS -->
    <link href="./BCG/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    <!-- Include the normal fonts and styles -->
    <link href="./BCG/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="./BCG/simple-line-icons/css/simple-line-icons.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
    <!-- MY CSS -->
    <link href="css/Berkstrap-wpk.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon.png">

</head>

<body>

    <!-- NAV NAV NAV -->
    <nav class="navbar navbar-light bg-light static-top">
        <div class="container">
            <a class="navbar-brand" href="#">Welcome <?php echo $_SESSION['name'] ?></a>

            <p>Your Departments &#9758</p>
            <?php
            //var_dump($ulist);
            foreach ($ulist as $dep) {
                echo "<a class='dep-links' href='dept-inv.php?deptNumber=" . $dep['dep_num']  . "'>" . $dep['dep_name'] . "</a>";
            }
            ?>
        </div>
        <a class="btn btn-primary" href="./sign-out.php">Sign Out</a>
    </nav>

    <header class="masthead text-white text-center">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h1 class="mb-5">Berkeley County Inventory Management System</h1>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                    <form>
                        <div class="form-row">
                            <div class="col-12 col-md-9 mb-2 mb-md-0">
                                <input type="search" class="form-control rounded" placeholder="Enter Department Number" name="empid" id="empid" aria-label="Search" aria-describedby="search-addon" />
                            </div>
                            <div class="col-12 col-md-3">
                                <button class="btn btn-outline-light" type="button" onclick="EmpIDSearch()" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Search By Dept Number </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- SCRIPT FOR EMPLOYEE ID SEARCH BOX -->
    <script>
        function EmpIDSearch() {
            let EmpID = document.getElementById("empid").value.length;
            let emp_id = document.getElementById("empid").value;
            if (EmpID == "" || EmpID != 5) {
                alert("Please Enter a 5 Digit Department Number ")
            } else
                location.replace("dept-inv-search.php?deptNumber=" + emp_id)
        }
    </script>
    <!-- <script>
        function EmpIDSearch() {
            let EmpID = document.getElementById("empid").value.length;
            let emp_id = document.getElementById("empid").value;
            if (EmpID == "" || EmpID != 5) {
                alert("Please Enter a 5 Digit Dept ID ")
            } else
                location.replace("dept-inv.php?deptNumber=" + emp_id)
        }
    </script> -->

    <!-- Icons GALORE -->
    <section class="features-icons bg-light text-center">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                        <div class="features-icons-icon d-flex">
                            <i class="icon-screen-desktop m-auto text-primary"></i>
                        </div>
                        <h3>PC Asset Tracking</h3>
                        <p class="lead mb-0">Look up PC information</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                        <div class="features-icons-icon d-flex">
                            <i class="icon-info m-auto text-primary"></i>
                        </div>
                        <h3>Home</h3>
                        <p class="lead mb-0">There is no place like 127.0.0.1</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mb-0 mb-lg-3">
                        <div class="features-icons-icon d-flex">
                            <i class="icon-phone m-auto text-primary"></i>
                        </div>
                        <h3>Mobile Devices</h3>
                        <p class="lead mb-0">View all Mobile Device Information</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="showcase">
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="row no-gutters">
                    <div class="col-lg-6 text-white showcase-img" style="background-image: url('./img/shopping2.png');">
                    </div>
                    <div class="col-lg-6 my-auto showcase-text">
                        <h2><a href="https://store.berkeleycountysc.gov/storeadmin/pages/sign-in.php">Updated For BCG
                                County Store</h2></a>
                        <p class="lead mb-0">Currently Offering Options to Assign, Un-Assign, Mark as Damaged, or
                            Transfer any item purchased from the BCG County Store. Future Items will be added in later
                            relases.</p>
                    </div>
                </div>
            </div>
    </section>


    <!-- Call to Action -->
    <!-- <section class="call-to-action text-white text-center">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h2 class="mb-4">
                        <font color="whitesmoke">Need Help? Contact the Help Desk!
                    </h2>
                    </font>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                    <form>
                        <div class="form-row">
                            <div class="col-12 col-md-9 mb-2 mb-md-0">
                                <input type="email" class="form-control form-control-lg" placeholder="Enter message...">
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-block btn-lg btn-primary">Submit Ticket</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section> -->

    <!-- BCG Footer -->
    <footer class="footer bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 h-100 text-center text-lg-left my-auto">
                    <ul class="list-inline mb-2">
                    </ul>
                    <p class="text-muted small mb-4 mb-lg-0">&copy; BCG 2023. All Rights Reserved.</p>
                </div>

            </div>
        </div>
    </footer>

    <!-- Bootstrap core JavaScript -->
    <script src="./BCG/jquery/jquery.min.js"></script>
    <script src="./BCG/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!--   Core JS Files   -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

</body>

</html>
<style>
    .dep-links {
        text-decoration: none;
        text-transform: uppercase;
        /* color: hotpink; */
        font-weight: bolder;
        margin-left: -10px;
    }
</style>