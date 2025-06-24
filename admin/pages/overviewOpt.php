<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: pages/sign-in.php");

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../StoreAdmin/assets/img/apple-touch-icon-bcg.png">
    <link rel="icon" type="image/png" href="../../StoreAdmin/assets/img/bcg-favicon.ico">
    <title>
        Approvals and Denials Overview
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <script async defer src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="preload" as="style" href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" />
</head>

<!-- -----------------ORDERED COUNTER and TOTAL ----------------------------- -->
<!-- returns all details regarding orders that are status = ordered and filtered by dept when needed
<?php
$DEPT = $_SESSION["department"];
$USER = $_SESSION["userName"];
$ROLE = $_SESSION["role_name"];
$empNumber = $_SESSION["empNumber"];

// --------------------returns SUM of orders that are approved and filtered by dept when needed---------------------------
$APPROVED = 0;
$APPSUM = 0;
$sql = "SELECT SUM(line_item_total) AS APPSUM FROM ord_ref WHERE status = 'Approved'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $APPSUM1 = number_format($row["APPSUM"], 2);
} else {
    echo "No Pending Requests";
}
// end of returns count of orders that are approved


// -- -----------------APPROVED COUNTER and TOTAL FROM RECEIVED.PHP ----------------------------- -->
/* returns total qnty (sum of qty column) orderTotal (sum of line_item_total) and count of order_detail_id where status
is ordered - filtered by dep as needed */
$empNumber = $_SESSION["empNumber"];
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$sql = "SELECT SUM(quantity) as total_qnt, SUM(line_item_total) as ordertotal, COUNT(order_details_id) as orderCount
FROM ord_ref
-- INNER JOIN emp_ref ON ord_ref.emp_id = emp_ref.empNumber
INNER JOIN dep_ref dr on ord_ref.department = dr.dep_num
WHERE status = 'Ordered'";

if ($ROLE === "Administrator") {
    // No additional Where Clause needed
} else {
    $sql .= " AND dr.dep_head = $empNumber";
}


$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $APPROVEDR = $row['orderCount'];
        $TOTALwTAXESR = number_format($row['ordertotal'], 2);
        $ORDERS = $row['orderCount'];
    }
} else {
    echo "No Ordered Requests";
}
// -------------------------end of approved counters and total-----------------------------
// -------------------------get sum of denied requests ------------------------------------

$dssql = "SELECT SUM(line_item_total) as deniedtotal
FROM ord_ref
INNER JOIN dep_ref dr on ord_ref.department = dr.dep_num
WHERE status = 'Denied'";

if ($ROLE === "Administrator") {
    // No additional Where Clause needed
} else {
    $dssql .= " AND dr.dep_head = $empNumber";
}

$dsresult = mysqli_query($conn, $dssql);
$deniedSumResults = mysqli_fetch_row($dsresult)[0];

if ($deniedSumResults > 0) {
    $DENIED_TOTAL = $deniedSumResults;
} else {
    $DENIED_TOTAL = 0;
}


// --------------------PENDING COUNTER ----------------------------- -->

// $empNumber = $_SESSION["empNumber"];
$psql = "SELECT COUNT(*) from ord_ref
INNER JOIN dep_ref dr on ord_ref.department = dr.dep_num
WHERE status = 'Pending'
";

if ($ROLE === "Administrator") {
    // do nothing extra
} else {
    $psql .= "AND dr.dep_head = $empNumber";
}

$presult = mysqli_query($conn, $psql);
$pendingResults = mysqli_fetch_row($presult)[0];
var_dump($pendingResults);


if ($pendingResults > 0) {
    $PENDING = $pendingResults;
} else {
    $PENDING = "0";
}

// --------------------DENIED COUNTER -----------------------------
$dsql = "SELECT COUNT(*) from ord_ref
JOIN departments on CONVERT(ord_ref.submitted_by USING utf8) = CONVERT(departments.dep_head USING UTF8)
WHERE status = 'Denied'
";

if ($ROLE === "Administrator") {
    // do nothing extra
} else {
    $dsql .= "AND department.dep_head = $empNumber";
}

$dresult = mysqli_query($conn, $dsql);
$deniedResults = mysqli_fetch_row($dresult)[0];

if ($deniedResults > 0) {
    $DENIED = $deniedResults;
} else {
    $DENIED = "0";
}


// --------------------Approved Counters ----------------------------- -->
// returns sum of line item total from order ref page
// $empNumber = $_SESSION["empNumber"];
$asql = "SELECT SUM(line_item_total) as ASF FROM ord_ref
JOIN departments on CONVERT(ord_ref.submitted_by USING utf8) = CONVERT(departments.dep_head USING UTF8)
WHERE status = 'Approved'
";

if ($ROLE === "Administrator") {
    // do nothing else
} else {
    $asql .= "AND department.dep_head = $empNumber";
}

$aresult = mysqli_query($conn, $asql);
$approvedResults = mysqli_fetch_row($aresult)[0];

if ($approvedResults > 0) {
    $APPSUM = number_format($approvedResults, 2);
} else {
    echo "No Approved Requests";
}

// -------------------RECEIVED Counters ----------------------------- -->
//$empNumber = $_SESSION["empNumber"];
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];

$rsql = "SELECT ifnull(SUM(quantity), 0) as total_qnt, ifnull(SUM(line_item_total), 0) as ordertotal,
ifnull(COUNT(order_details_id), 0) as orderCount
FROM ord_ref
-- JOIN emp_ref ON ord_ref.emp_id = emp_ref.empNumber
JOIN dep_ref dr on ord_ref.department = dr.dep_num
WHERE status = 'Received'";

if ($ROLE !== "Administrator") {
    $rsql .= "AND dr.dep_head = $empNumber";
}

$RAPPSUM = 0;
$RINV = 0;
$rresult = mysqli_query($conn, $rsql);

if (mysqli_num_rows($rresult) > 0) {
    while ($row = mysqli_fetch_assoc($rresult)) {
        $RINV++;
        // calulate the Grand Total
        $RGRANDTOTAL = ($row["quantity"] * $row["product_price"]);
        // Aggrigate the sales values
        $RAPPSUM += $RGRANDTOTAL;
        // Get other data from row

        $RAPPSUM1 = number_format($RAPPSUM, 2);
        $RTOTALwTX = $row["line_item_total"];
        $RTOTALwTAXES = $row["ordertotal"];
        $RORDERS = $row["orderCount"];
    }
} else {
    $RTOTALwTAXES = 0;
}
?>
-->

<body class="g-sidenav-show   bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="../../StoreAdmin/index.php " target="_blank">
                <img src="../../StoreAdmin/assets/img/bcg12.png" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Berkeley County Store</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link " href="../index.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-laptop text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="requests.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-bulb-61 text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Employee Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="orders.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-sound-wave text-success text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Approvals To Be Ordered</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="received.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-spaceship text-secondary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Ordered Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="completed.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-satisfied text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">All Received Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="overview.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-bag-17 text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Requests Overview</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">-----</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-istanbul text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidenav-footer mx-3 ">
            <div class="card card-plain shadow-none" id="sidenavCard">
                <img class="w-50 mx-auto" src="../assets/img/illustrations/bcg1.svg" alt="sidebar_illustration">
                <div class="card-body text-center p-3 w-100 pt-0">
                    <div class="docs-info">
                        <h6 class="mb-0">Need help?</h6>
                        <p class="text-xs font-weight-bold mb-0"><a href="../assets/img/Berkeley County Employee Store.pdf" target="_blank"><u>County Store
                                    Manual</u></p></a>
                    </div>
                </div>
            </div>
            <!-- <a class="btn btn-primary btn-sm mb-0 w-100" href="mailto:help@berkeleycountysc.gov" type="button">Email IT Support</a> -->
        </div>

        <!-- CONTACT BUTTON HELP DESK SUPPORT -->
        <center>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Contact the Help Desk
            </button>
        </center>

        <!-- TRIGGER THE FORM -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Berkeley County IT Support</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="email-script2.php" method="post">
                            <textarea id="message" name="message" class="form-control" placeholder="Please Describe how we can assist in detail" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="sendMailBtn" class="btn btn-primary">Submit Your Ticket!</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- -------END MODAL FOR CONTACT FORM----- -->


    </aside>
    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <center>
                    <img src="./../assets/img//bcg-hz-lblue.png" class="navbar-brand-img h-75 w-75" alt="main_logo">
                </center>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                    </div>
                    <ul class="navbar-nav  justify-content-end">
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item px-3 d-flex align-items-center">
                        </li>
        </nav>
        <!-- End THE BCG Navbar -->




        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-xl-6 mb-xl-0 mb-4">
                            <div class="card bg-transparent shadow-xl">
                                <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../assets/img/jc.jpg');">
                                    <span class="mask bg-gradient-none"></span>
                                    <div class="card-body position-relative z-index-1 p-3">
                                        <!-- -------Just to make my image larger------------- -->
                                        <i class="fas fa-none text-white p-2"></i>
                                        <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                                        <h5 class="text-white mt-4 mb-5 pb-2"></h5>
                                        <!-- ----------Does Nothing just expands image------------------------- -->
                                        <div class="d-flex">
                                            <div class="d-flex">
                                            </div>
                                            <div class="ms-auto w-20 d-flex align-items-end justify-content-end">
                                                <img class="w-80 mt-2 rimage" src="../assets/img/1b.jpg" alt="....">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header mx-4 p-3 text-center">
                                            <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
                                                <i class="fas fa-thumbs-up opacity-10"></i>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0 p-3 text-center">
                                            <h6 class="text-center mb-0">Pending</h6>
                                            <span class="text-xs">Total Number of Pending Requests</span>
                                            <hr class="horizontal dark my-3">
                                            <h5 class="mb-0"><?php echo $PENDING ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-md-0 mt-4">
                                    <div class="card">
                                        <div class="card-header mx-4 p-3 text-center">
                                            <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
                                                <i class="fas fa-thumbs-down opacity-10"></i>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0 p-3 text-center">
                                            <h6 class="text-center mb-0">Denied</h6>
                                            <span class="text-xs">Total Amount Denied</span>
                                            <hr class="horizontal dark my-3">
                                            <h5 class="mb-0">$<?php echo $DENIED_TOTAL ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-lg-0 mb-4">
                            <div class="card mt-4">
                                <div class="card-header pb-0 p-3">
                                    <div class="row">
                                        <div class="col-6 d-flex align-items-center">
                                            <h6 class="mb-0">Welcome <?php echo $USER ?>!</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-md-0 mb-4">
                                            <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                                                <img class="w-10 me-3 mb-0" src="../assets/img/logos/budget.png" alt="logo">
                                                <h6 class="mb-0">Funds Allocated toward Approvals:   </h6>
                                                $<?php echo $APPSUM1 ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                                                <img class="w-10 me-3 mb-0" src="../assets/img/logos/spent.png" alt="logo">
                                                <h6 class="mb-0">Total Spending on Items Received:  </h6>
                                                $<?php echo $RTOTALwTAXES ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <i class="fas fa-heart"></i>
                                    <!-- <img class="w-10 me-3 mb-0" src="../assets/img/logos/heart.png" alt="logo"> -->
                                    <h6 class="mb-0"> Data Search</h6>
                                </div>
                            </div>
                        </div>
                        <!-- MY BOOTSTRAP CLASS TO HAVE THE SEARCH OPTIONS SEPERATED BY TABS -->

                        <div class="card-body p-3 pb-0">
                            <span class="text-xs">Please enter Employee ID or Department Number to search all
                                Requests</span>
                            <input type="search" class="form-control rounded" placeholder="Enter Employee ID or Department Number" name="empid" id="empid" aria-label="Search" aria-describedby="search-addon" />
                            <center>
                                <button class="btn btn-outline-dark" type="button" onclick="EmpIDSearch()" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Search By Employee ID</button>
                                <button class="btn btn-outline-dark" type="button" onclick="DeptNOSearch()" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Search By Department</button>
                            </center>


                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                <div class="d-flex flex-column">
                                    <h6 class="text-dark mb-1 font-weight-bold text-sm"> </h6>
                                    <span class="text-xs"> </span>
                                    <span class="text-xs"> </span>
                                    <span class="text-xs">Start Date <font color="whitesmoke">
                                            ..........................................................</font>End
                                        Date</span>
                                </div>
                            </li>
                            <div class="input-group">
                                <input type="date" name="startdate" id="startdate" class="form-control form-control-lg">
                                <input type="date" name="enddate" id="enddate" class="form-control form-control-lg">
                            </div>
                            <center>
                                <button type="button" onclick="dateSearch()" class="btn btn-outline-dark">Search by
                                    Date</button>
                                <ul class="list-group">
                                    Click Below for Reports based on Status
                                    <div class="dropdown">
                                        <a class="btn btn-lg btn-outline-dark" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                            Choose Reports
                                        </a>

                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <li><a class="dropdown-item" href="pendingReports.php">All Pending
                                                    Requests</a></li>
                                            <li><a class="dropdown-item" href="approvedReports.php">All Approved
                                                    Requests</a></li>
                                            <li><a class="dropdown-item" href="orderedReports.php">All Ordered
                                                    Requests</a></li>
                                            <li><a class="dropdown-item" href="receivedReports.php">All Received
                                                    Items</a></li>
                                        </ul>
                                    </div>
                            </center>
                            <center>
                                Departments are Listed in Alphabetical Order
                                <!-- Department Look up TOOL -->

                                <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#exampleModalLong">
                                    Department Look up
                                </button>
                            </center>

                            <!-- TRIGGER THE FORM -->
                            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLongTitle">Berkeley County
                                                Department Information</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>
                                            <table>
                                                <tr>
                                                    <th width=35%><strong>Dept. Number</strong></th>
                                                    <th width=45%><strong>Dept. Name</strong></th>
                                                </tr>
                                                <tr>
                                                    <td>41514</td>
                                                    <td>Administrative Services</td>
                                                </tr>
                                                <tr>
                                                    <td>47001</td>
                                                    <td>Airport Operations</td>
                                                </tr>
                                                <tr>
                                                    <td>44102</td>
                                                    <td>Animal Center</td>
                                                </tr>
                                                <tr>
                                                    <td>41506</td>
                                                    <td>Auditor</td>
                                                </tr>
                                                <tr>
                                                    <td>42183</td>
                                                    <td>BC Traffic Unit</td>
                                                </tr>
                                                <tr>
                                                    <td>48016</td>
                                                    <td>BCWS Billing</td>
                                                </tr>
                                                <tr>
                                                    <td>48034</td>
                                                    <td>BCWS Bldg and Fleet Maint</td>
                                                </tr>
                                                <tr>
                                                    <td>48015</td>
                                                    <td>BCWS Engineering</td>
                                                </tr>
                                                <tr>
                                                    <td>48011</td>
                                                    <td>BCWS Executive</td>
                                                </tr>
                                                <tr>
                                                    <td>48019</td>
                                                    <td>BCWS Laboratory</td>
                                                </tr>
                                                <tr>
                                                    <td>48030</td>
                                                    <td>BCWS Lower Treatment Plant</td>
                                                </tr>
                                                <tr>
                                                    <td>48020</td>
                                                    <td>BCWS Operations</td>
                                                </tr>
                                                <tr>
                                                    <td>48033</td>
                                                    <td>BCWS PPM Electrical</td>
                                                </tr>
                                                <tr>
                                                    <td>48032</td>
                                                    <td>BCWS PPM Mechanical</td>
                                                </tr>
                                                <tr>
                                                    <td>48044</td>
                                                    <td>BCWS Scalehouse</td>
                                                </tr>
                                                <tr>
                                                    <td>48043</td>
                                                    <td>BCWS Solid Waste Collections</td>
                                                </tr>
                                                <tr>
                                                    <td>48042</td>
                                                    <td>BCWS Solid Waste Disposal</td>
                                                </tr>
                                                <tr>
                                                    <td>48045</td>
                                                    <td>BCWS Solid Waste Landfill Gas</td>
                                                </tr>
                                                <tr>
                                                    <td>48041</td>
                                                    <td>BCWS Solid Waste Recycling</td>
                                                </tr>
                                                <tr>
                                                    <td>48023</td>
                                                    <td>BCWS Wastewater Collections</td>
                                                </tr>
                                                <tr>
                                                    <td>48025</td>
                                                    <td>BCWS Water Distribution</td>
                                                </tr>
                                                <tr>
                                                    <td>41519</td>
                                                    <td>Board of Assessment Appeals</td>
                                                </tr>
                                                <tr>
                                                    <td>41519</td>
                                                    <td>Building and Code Enforcement</td>
                                                </tr>
                                                <tr>
                                                    <td>41517</td>
                                                    <td>Clerk of Court</td>
                                                </tr>
                                                <tr>
                                                    <td>41210</td>
                                                    <td>Cleark of Court DSS</td>
                                                </tr>
                                                <tr>
                                                    <td>41209</td>
                                                    <td>Communications</td>
                                                </tr>
                                                <tr>
                                                    <td>42103</td>
                                                    <td>Coroner</td>
                                                </tr>
                                                <tr>
                                                    <td>42102</td>
                                                    <td>County Council</td>
                                                </tr>
                                                <tr>
                                                    <td>41101</td>
                                                    <td>County Supervisor</td>
                                                </tr>
                                                <tr>
                                                    <td>41301</td>
                                                    <td>Cypress Gardens</td>
                                                </tr>
                                                <tr>
                                                    <td>42301</td>
                                                    <td>Detention Center</td>
                                                </tr>
                                                <tr>
                                                    <td>42185</td>
                                                    <td>DUI Capacity</td>
                                                </tr>
                                                <tr>
                                                    <td>42184</td>
                                                    <td>DUI Pros</td>
                                                </tr>
                                                <tr>
                                                    <td>42501</td>
                                                    <td>E911 Emergency Telephone</td>
                                                </tr>
                                                <tr>
                                                    <td>43107</td>
                                                    <td>Economic Development</td>
                                                </tr>
                                                <tr>
                                                    <td>41403</td>
                                                    <td>Election Expenses</td>
                                                </tr>
                                                <tr>
                                                    <td>44104</td>
                                                    <td>Emergency Medical Services</td>
                                                </tr>
                                                <tr>
                                                    <td>47214</td>
                                                    <td>Emergency Preparedness</td>
                                                </tr>
                                                <tr>
                                                    <td>43104</td>
                                                    <td>Engineering</td>
                                                </tr>
                                                <tr>
                                                    <td>47002</td>
                                                    <td>Facilities & Grounds</td>
                                                </tr>
                                                <tr>
                                                    <td>44405</td>
                                                    <td>Farm and Land Services</td>
                                                </tr>
                                                <tr>
                                                    <td>41504</td>
                                                    <td>Finance Department</td>
                                                </tr>
                                                <tr>
                                                    <td>47013</td>
                                                    <td>Geographic Information Systems</td>
                                                </tr>
                                                <tr>
                                                    <td>47032</td>
                                                    <td>GIS-Non consortium Expenses</td>
                                                </tr>
                                                <tr>
                                                    <td>47003</td>
                                                    <td>HR Services Department</td>
                                                </tr>
                                                <tr>
                                                    <td>41501</td>
                                                    <td>Human Resources</td>
                                                </tr>
                                                <tr>
                                                    <td>41515</td>
                                                    <td>Information & Technology Services</td>
                                                </tr>
                                                <tr>
                                                    <td>41502</td>
                                                    <td>Legal</td>
                                                </tr>
                                                <tr>
                                                    <td>45526</td>
                                                    <td>Library - Cane Bay</td>
                                                </tr>
                                                <tr>
                                                    <td>45525</td>
                                                    <td>Library - Daniel Island</td>
                                                </tr>
                                                <tr>
                                                    <td>45521</td>
                                                    <td>Library - Goose Creek</td>
                                                </tr>
                                                <tr>
                                                    <td>45522</td>
                                                    <td>Library - Hanahan</td>
                                                </tr>
                                                <tr>
                                                    <td>45520</td>
                                                    <td>Library - Moncks Corner</td>
                                                </tr>
                                                <tr>
                                                    <td>45523</td>
                                                    <td>Library - Sangaree</td>
                                                </tr>
                                                <tr>
                                                    <td>45524</td>
                                                    <td>Library - St. Stephen</td>
                                                </tr>
                                                <tr>
                                                    <td>41206</td>
                                                    <td>Magistrates</td>
                                                </tr>
                                                <tr>
                                                    <td>43101</td>
                                                    <td>Maintenance Garage</td>
                                                </tr>
                                                <tr>
                                                    <td>41202</td>
                                                    <td>Master - In - Equity</td>
                                                </tr>
                                                <tr>
                                                    <td>44103</td>
                                                    <td>Mosquito Abatement</td>
                                                </tr>
                                                <tr>
                                                    <td>36001</td>
                                                    <td>One Cent Admin</td>
                                                </tr>
                                                <tr>
                                                    <td>41518</td>
                                                    <td>Permitting</td>
                                                </tr>
                                                <tr>
                                                    <td>41512</td>
                                                    <td>Planning and Zoning</td>
                                                </tr>
                                                <tr>
                                                    <td>41201</td>
                                                    <td>Probate Judge</td>
                                                </tr>
                                                <tr>
                                                    <td>41513</td>
                                                    <td>Procurement</td>
                                                </tr>
                                                <tr>
                                                    <td>41503</td>
                                                    <td>Public Information Officer</td>
                                                </tr>
                                                <tr>
                                                    <td>42210</td>
                                                    <td>Radio Shop</td>
                                                </tr>
                                                <tr>
                                                    <td>41507</td>
                                                    <td>Real Property Services</td>
                                                </tr>
                                                <tr>
                                                    <td>41510</td>
                                                    <td>Register of Deeds</td>
                                                </tr>
                                                <tr>
                                                    <td>41401</td>
                                                    <td>Registration & Elections</td>
                                                </tr>
                                                <tr>
                                                    <td>41520</td>
                                                    <td>Risk Management</td>
                                                </tr>
                                                <tr>
                                                    <td>43103</td>
                                                    <td>Roads & Bridges</td>
                                                </tr>
                                                <tr>
                                                    <td>47018</td>
                                                    <td>Sangaree Special Tax District</td>
                                                </tr>
                                                <tr>
                                                    <td>42109</td>
                                                    <td>School Resource Funds</td>
                                                </tr>
                                                <tr>
                                                    <td>42101</td>
                                                    <td>Sheriff</td>
                                                </tr>
                                                <tr>
                                                    <td>41203</td>
                                                    <td>Solicitor</td>
                                                </tr>
                                                <tr>
                                                    <td>41218</td>
                                                    <td>Solicitor Expungements</td>
                                                </tr>
                                                <tr>
                                                    <td>41204</td>
                                                    <td>Solicitor PTI</td>
                                                </tr>
                                                <tr>
                                                    <td>41208</td>
                                                    <td>Solicitor State Funds</td>
                                                </tr>
                                                <tr>
                                                    <td>42194</td>
                                                    <td>SRO Berkeley Academy</td>
                                                </tr>
                                                <tr>
                                                    <td>43111</td>
                                                    <td>Storm Water Management</td>
                                                </tr>
                                                <tr>
                                                    <td>43115</td>
                                                    <td>Storm Water Roads & Bridges</td>
                                                </tr>
                                                <tr>
                                                    <td>41508</td>
                                                    <td>Tax Collector</td>
                                                </tr>
                                                <tr>
                                                    <td>47090</td>
                                                    <td>Tourism</td>
                                                </tr>
                                                <tr>
                                                    <td>41505</td>
                                                    <td>Treasurer</td>
                                                </tr>
                                                <tr>
                                                    <td>44105</td>
                                                    <td>Veterans Services</td>
                                                </tr>
                                                <tr>
                                                    <td>44418</td>
                                                    <td>Victim Witness - Magistrate</td>
                                                </tr>
                                                <tr>
                                                    <td>44419</td>
                                                    <td>Victim Witnesss - Sheriff</td>
                                                </tr>
                                                <tr>
                                                    <td>44417</td>
                                                    <td>Victim Witness - Solicotor</td>
                                                </tr>
                                            </table>
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- -------END MODAL FOR CONTACT FORM----- -->




                            <!-- MY SCRIPTS!!!! -->
                            <!-- DATE SEARCH SCRIPT -->
                            <!-- SCRIPT FOR SEARCH BOX -->
                            <!-- SCRIPT FOR SEARCH BOX -->
                            <script>
                                function dateSearch() {
                                    let datestart = document.getElementById("startdate").value
                                    let dateend = document.getElementById("enddate").value
                                    // let url = ("details.php?dateend=" + dateend & "datestart=" + datestart)
                                    if (datestart == "" && dateend == "") {
                                        alert("You have not selected a start or an end date")
                                    } else if (datestart == "") {
                                        alert("You have selected a End Date, but not an Start date. Please choose " +
                                            dateend)
                                    } else if (dateend == "") {
                                        alert("You have selected a Start Date, but not an End date. Please choose " +
                                            datestart)
                                    } else
                                        location.replace("datesearch.php?datestart=" + datestart + "&dateend=" + dateend)
                                }
                            </script>

                            <!-- SCRIPT FOR EMPLOYEE ID SEARCH BOX -->
                            <script>
                                function EmpIDSearch() {
                                    let EmpID = document.getElementById("empid").value.length;
                                    let EmppID = document.getElementById("empid").value;
                                    // if (EmpID == "" || EmpID < 3 || EmpID > 7) {
                                    if (EmpID == "" || EmpID != 4) {
                                        alert("Please Enter a 4 Digit Employee ID ")
                                    } else
                                        location.replace("empidsearch.php?empidd=" + EmppID)
                                }
                            </script>

                            <!-- SCRIPT FOR DEPT SEARCH BOX -->
                            <script>
                                function DeptNOSearch() {
                                    let EmpID = document.getElementById("empid").value.length;
                                    let EmppID = document.getElementById("empid").value;
                                    // if (EmpID == "" || EmpID < 3 || EmpID > 7) {
                                    if (EmpID == "" || EmpID != 5) {
                                        alert("Please Enter a 5 Digit Department Number ")
                                    } else
                                        location.replace("deptsearch.php?empidd=" + EmppID)
                                }
                            </script>




                            </ul>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-md-6 mt-4">
                    <div class="card h-100 mb-4">
                        <div class="card-header pb-0 px-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-0">You have <font color="green">Ordered </font> a total of
                                        <?php echo $APPROVEDR ?> Items</h6>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end align-items-center">
                                    <i class="fas fa-solid fa-dollar-sign"></i>
                                    <small><?php echo $TOTALwTAXESR ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-4 p-3">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3"></h6>
                            <ul class="list-group">
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">

                                        <!-- POPULATE THE LATEST ORDERED REQUESTS -->
                                        <?php
                                        $empNumber = $_SESSION["empNumber"];
                                        $sql = "SELECT ord.created, ord.line_item_total,
                                        CONCAT(ord.rf_first_name, ' ', ord.rf_last_name) as ordered_for
                                        FROM ord_ref ord 
                                        JOIN dep_ref dr on ord.department = dr.dep_num
                                        WHERE status = 'Ordered'";

                                        if ($ROLE !== "Administrator") {
                                            $sql .= "AND dr.dep_head = $empNumber";
                                        }
                                        $c = 1;
                                        $result = mysqli_query($conn, $sql);
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // -----NEW DB -------
                                                $EMPNAME = $row["ordered_for"];
                                                $ORDERON = $row["created"];
                                                $TOTALwTX = $row["line_item_total"];
                                                echo "<li class='list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg'>";
                                                echo "<div class='d-flex align-items-center'>";
                                                echo "<button class='btn btn-icon-only btn-rounded btn-outline-success mb-0 me-3 btn-sm d-flex align-items-center justify-content-center'><i class='fas fa-thumbs-up'></i></button>";
                                                echo "<div class='d-flex flex-column'>";
                                                echo "<h6 class='mb-1 text-dark text-sm'>$EMPNAME</h6>";
                                                echo "<span class='text-xs'>You Ordered This Item From The vendor on: $ORDERON</span>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "<div class='d-flex align-items-center text-success text-gradient text-sm font-weight-bold'>";
                                                echo "$" . $TOTALwTX;
                                                echo "</div>";
                                                echo "</li>";
                                                echo "</ul>";
                                                $c++;
                                            }
                                        } else {
                                            echo "No Ordered Requests";
                                        }
                                        ?>


                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="card h-100 mb-4">
                        <div class="card-header pb-0 px-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-0">You have <font color="red">Denied </font> a total of
                                        <?php echo $DENIED ?> Requests</h6>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end align-items-center">
                                    <i class="fas fa-solid fa-dollar-sign"></i>
                                    <small><?php echo $DENIED_TOTAL ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-4 p-3">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3"></h6>
                            <ul class="list-group">
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">

                                        <!-- POPULATE THE LATEST DENIED REQUESTS -->
                                        <?php
                                        $empNumber = $_SESSION["empNumber"];
                                        if ($ROLE === "Administrator") {
                                            $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                                                            ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email 
                                                                            FROM ord_ref ord 
                                                                            JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                                                            JOIN dep_ref dr on ord.department = dr.dep_num
                                                                            JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                                                            WHERE status = 'Denied'";
                                        } else {
                                            $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                                                              ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email 
                                                                              FROM ord_ref ord 
                                                                              JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                                                              JOIN dep_ref dr on ord.department = dr.dep_num
                                                                              JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                                                              WHERE status = 'Denied' AND dr.dep_head = $empNumber";
                                        }
                                        $c = 1;
                                        $result = mysqli_query($conn, $sql);
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // -----NEW DB -------
                                                $SIZE = $row["size_id"];
                                                $COLOR = $row["color_id"];
                                                $EMPNAME = $row["ordered_for"];
                                                $EMPLOYEEID = $row["emp_id"];
                                                $PRODUCTNAME = $row["product_name"];
                                                $PRODUCTPRICE = $row["product_price"];
                                                $REQUESTDATE = $row["created"];
                                                $QNT = $row["quantity"];
                                                $PRODUCTNAME = $row["product_name"];
                                                $GRANDTOTAL = ($QNT * $PRODUCTPRICE);
                                                $TOTALwTX = $row["line_item_total"];
                                                echo "<li class='list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg'>";
                                                echo "<div class='d-flex align-items-center'>";
                                                echo "<button class='btn btn-icon-only btn-rounded btn-outline-danger mb-0 me-3 btn-sm d-flex align-items-center justify-content-center'><i class='fas fa-thumbs-down'></i></button>";
                                                echo "<div class='d-flex flex-column'>";
                                                echo "<h6 class='mb-1 text-dark text-sm'>$EMPNAME</h6>";
                                                echo "<span class='text-xs'>$REQUESTDATE | $QNT | $PRODUCTPRICE | $PRODUCTNAME | $SIZE | $COLOR</span>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "<div class='d-flex align-items-center text-danger text-gradient text-sm font-weight-bold'>";
                                                echo "$" . $TOTALwTX;
                                                echo "</div>";
                                                echo "</li>";
                                                echo "</ul>";
                                            }
                                        } else {
                                            echo "No Pending Requests";
                                        }
                                        ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- BCG FOOTER -->
            <footer class="footer pt-3  ">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                © <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                <a href="https://berkeleycountysc.gov/dept/it/" class="font-weight-bold" target="_blank">The Berkeley County IT Team</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </main>
    <div class="fixed-plugin">

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
<style>
    .rimage {
        border-radius: 50%;
    }
</style>