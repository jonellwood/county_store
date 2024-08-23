<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

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
        Department Reports
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- SideBar Icons -->
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" />
    <?php
    $empidd = filter_input(INPUT_GET, 'empidd', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
    ?>
    <script>
    function updateOrdered(id, EmpIDD) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/itemordered.php?id=" + id, true);
        ajax.send();

        alert("Marked as Ordered");
        window.location.href = '../pages/deptsearch.php?empidd=' + <?php echo $empidd; ?>;
    }
    </script>
    <script>
    function updateApproved(id) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "approved.php?id=" + id, true);
        ajax.send();

        alert("Marked As Approved");
        window.location.href = '../pages/deptsearch.php?empidd=' + <?php echo $empidd; ?>;
    }
    </script>
    <script>
    function updateDenied(id) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/denied.php?id=" + id, true);
        ajax.send();

        ajax.onreadystatechange = function() {

            if (this.readyState == 4 && this.status == 200) {

                alert("Changed to Denied Status");
                window.location.href = '../pages/deptsearch.php?empidd=' + <?php echo $empidd; ?>;

            }
        }
    }
    </script>
    <script>
    function addRecToInv(id) {
        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/write-to-inv.php?id=" + id, true);
        ajax.send();
    }

    function updateRec(id) {
        addRecToInv(id);
        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/itemrec.php?id=" + id, true);
        ajax.send();

        alert("Item Marked as Received!!");
        window.location.href = '../pages/received.php';
    }
    </script>

    <!-- SCRIPT FOR SIZE -->
    <script>
    function updateSize(id) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/add-comment-size.php?order_details_id=" + id, true);
        ajax.send();

        alert(
            "Item is still Approved, but not available due to non-avaialble size. E-Mail has been sent to the Requestor"
        );
        window.location.href = '../pages/orders.php';
    }
    </script>

    <!-- SCRIPT FOR COLOR -->
    <script>
    function updateColor(id) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/add-comment-color.php?order_details_id=" + id, true);
        ajax.send();

        alert(
            "Item is still Approved, but not available due to non-avaialble color. E-Mail has been sent to the Requestor"
        );
        window.location.href = '../pages/orders.php';
    }
    </script>

    <!-- SCRIPT FOR BACKORDER -->
    <script>
    function updateBO(id) {

        var ajax = new XMLHttpRequest();
        ajax.open("POST", "../pages/add-comment-backordered.php?order_details_id=" + id, true);
        ajax.send();

        alert("Item is still Approved, but not available as it is on BackOrder. E-Mail has been sent to the Requestor");
        window.location.href = '../pages/orders.php';
    }
    </script>
</head>


<!-- -----------------DEPT REQUEST TOTALS and NAMING OF DEPT ----------------------------- -->
<?php
$empidd = filter_input(INPUT_GET, 'empidd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$sql = "SELECT e.deptName, COUNT(o.order_details_id) as TREQS
FROM uniform_orders.ord_ref o
join emp_ref e on o.emp_id = e.empNumber
JOIN departments dr on o.department = dr.dep_num
WHERE e.deptNumber = $empidd";
if ($ROLE == "Administrator") {
    // FAST ADMIN SEARCH
} else {
    $sql .= "AND dr.dep_head = $DEPT OR dr.dep_assist = $DEPT)";
}
$DEPTID = 'DEPARTMENT NOT FOUND. Please check your Spelling';
$DEPTNAME = 'DEPARTMENT NOT FOUND. Please Check your spelling.';
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $TREQS = $row["TREQS"];
        $DEPTNAME = $row["deptName"];
    }
} else {
    echo "No Pending Requests";
}
?>

<!-- -----------------APPROVED COUNTER and TOTAL ----------------------------- -->
<?php
$APPROVEDREQ = 0;
$APPROVEDSUM = 0;
$sql = "SELECT SUM(o.line_item_total) as APPSUM, COUNT(o.order_details_id) as APPCOUNT
FROM uniform_orders.ord_ref o
JOIN emp_ref e on o.emp_id = e.empNumber
JOIN departments dr on o.department = dr.dep_num
WHERE status = 'Approved' AND e.deptNumber  = $empidd";
if ($ROLE === "Administrator") {
    // FAST ADMIN SEARCH
} else {
    $sql .= " AND dr.dep_head = $DEPT OR dr.dep_assist = $DEPT";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $APPROVEDREQ = $row["APPCOUNT"];
        $APPROVEDSUM = $row["APPSUM"];
        $ASF = number_format($APPROVEDSUM, 2);
    }
} else {
    // echo "No Approved Requests";
}
?>

<!-- -----------------DENIED COUNTER and TOTAL ----------------------------- -->
<?php
if ($ROLE === "Administrator") {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as DENSUM, COUNT(o.order_details_id) as DENCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
WHERE status = 'Denied' AND e.deptNumber  = $empidd";
} else {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as DENSUM, COUNT(o.order_details_id) as DENCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
    WHERE e.deptNumber = $empidd AND dr.dep_head = $DEPT AND status = 'Denied' OR dr.dep_assist = $DEPT AND status = 'Denied'";
}
$DENIEDREQ = 0;
$DENIEDSUM = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $DENIEDREQ = $row["DENCOUNT"];
        $DENIEDSUM = $row["DENSUM"];
        $DSF = number_format($DENIEDSUM, 2);
    }
} else {
    // echo "No Approved Requests";
}
?>

<!-- -----------------PENDING COUNTER and TOTAL ----------------------------- -->
<?php
if ($ROLE === "Administrator") {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as PENSUM, COUNT(o.order_details_id) as PENCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
WHERE status = 'Pending' AND e.deptNumber  = $empidd";
} else {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as PENSUM, COUNT(o.order_details_id) as PENCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
    WHERE e.deptNumber = $empidd AND dr.dep_head = $DEPT AND status = 'Pending' OR dr.dep_assist = $DEPT AND status = 'Pending'";
}
$PENDINGREQ = 0;
$PENDINGSUM = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $PENDINGREQ = $row["PENCOUNT"];
        $PENDINGSUM = $row["PENSUM"];
        $PSF = number_format($PENDINGSUM, 2);
    }
} else {
    // echo "No Approved Requests";
}
?>



<!-- -----------------ORDERED COUNTER and TOTAL ----------------------------- -->
<?php
if ($ROLE === "Administrator") {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as ORDSUM, COUNT(o.order_details_id) as ORDCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
WHERE status = 'Ordered' AND e.deptNumber  = $empidd";
} else {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as ORDSUM, COUNT(o.order_details_id) as ORDCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
    WHERE e.deptNumber = $empidd AND dr.dep_head = $DEPT AND status = 'Ordered' OR dr.dep_assist = $DEPT AND status = 'Ordered'";
}
$ORDEREDREQ = 0;
$ORDEREDSUM = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ORDEREDREQ = $row["ORDCOUNT"];
        $ORDEREDSUM = $row["ORDSUM"];
        $OSF = number_format($ORDEREDSUM, 2);
    }
} else {
    // echo "No Approved Requests";
}
?>

<!-- -----------------RECEIVED COUNTER and TOTAL ----------------------------- -->
<?php
if ($ROLE === "Administrator") {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as RECSUM, COUNT(o.order_details_id) as RECCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
WHERE status = 'Received' AND e.deptNumber  = $empidd";
} else {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.order_id, o.order_details_id, o.customer_id, o.created, e.deptNumber, o.line_item_total,
    SUM(o.line_item_total) as RECSUM, COUNT(o.order_details_id) as RECCOUNT
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN departments dr on o.department = dr.dep_num
    WHERE e.deptNumber = $empidd AND dr.dep_head = $DEPT AND status = 'Received' OR dr.dep_assist = $DEPT AND status = 'Received'";
}
$RECEIVEDREQ = 0;
$RECEIVEDSUM = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $RECEIVEDREQ = $row["RECCOUNT"];
        $RECEIVEDSUM = $row["RECSUM"];
        $RSF = number_format($RECEIVEDSUM, 2);
    }
} else {
    // echo "No Approved Requests";
}

$EMP_TOTAL = ($RECEIVEDSUM + $ORDEREDSUM);
$EMP_COUNT = ($ORDEREDREQ + $RECEIVEDREQ);
$EMP_TOTAL = number_format($EMP_TOTAL, 2);
?>

<body class="g-sidenav-show   bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <aside
        class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
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
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-laptop text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="requests.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-bulb-61 text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Employee Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="orders.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-sound-wave text-success text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Approvals To Be Ordered</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="received.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-spaceship text-secondary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Ordered Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="completed.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-satisfied text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">All Received Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="overview.php">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
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
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
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
                        <p class="text-xs font-weight-bold mb-0"><a
                                href="../assets/img/Berkeley County Employee Store.pdf" target="_blank"><u>County Store
                                    Manual</u></p></a>
                    </div>
                </div>
            </div>
            <a class="btn btn-primary btn-sm mb-0 w-100" href="mailto:help@berkeleycountysc.gov" type="button">Email IT
                Support</a>
        </div>
    </aside>
    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur"
            data-scroll="false">
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

                        </li>
                    </ul>
                    </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <!-- ONCLICK WARNING FOR PRINTING -->
        <script>
        function myAlert(evt) {
            event.preventDefault();
            alert('Link was clicked but page was not open');
        }
        </script>
        <div class="container-fluid py-4">
            <div class="col-12">
                <div class="card mb-4">
                    <a href="deptPrint.php?demptid=<?php echo $empidd ?>" class="btn btn-primary" role="button"
                        onclick="return confirm('WARNING: Clicking this button will change ALL Approved requests to ordered. Confirm your choice by clicking the OK button')">Print
                        or Save this as PDF to submit to Vendor</a>
                    <a href="deptPrintApproved.php?demptid=<?php echo $empidd ?>" class="btn btn-primary" role="button">
                        ORDERERED REPORTS </a>
                    <div class="card-header pb-0">
                        <h6>There has been a total of <strong>
                                <font color="black"> <?php echo $TREQS ?></font>
                            </strong> Requests from: <font color="black"><strong><?php echo $DEPTNAME ?></strong>
                            </font>
                            <h6><?php echo $APPROVEDREQ ?> Requests Have Been <font color="green">Approved:
                                    $<?php echo $ASF ?> </font>But Not Yet Ordered | <?php echo $DENIEDREQ ?>
                                Request Has Been <font color="red">Denied: $<?php echo $DSF ?></font> |
                                <?php echo $PENDINGREQ ?> Requests Are Still <font color="gray">Pending:
                                    $<?php echo $PSF ?></font>
                            </h6>
                            <h6>
                                <font color="whitesmoke">-----</font>
                            </h6>

                            <h6><strong><?php echo $EMP_COUNT ?></strong> Requests <strong>
                                    <font color="#518ffa"><?php echo $EMPNAME ?></font>
                                </strong> Submitted Have Been Ordered and/or Received from the Vendor. <strong>
                                    <font color="#518ffa"><?php echo $EMPNAME ?></font>
                                </strong><strong><?php echo $DEPTNAME ?> </strong> Has Spent a total of <strong>
                                    <font color="#518ffa"> $<?php echo $EMP_TOTAL ?></font>
                                </strong> This Year.
                            </h6>

                    </div>


                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Employee Who Submitted the Request</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Status of Request</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Number of Items in Request</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Total Cost of Request</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Date Requested</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>


                                <!-- POPULATE THE LATEST REQUESTS -->
                                <?php
                                if ($ROLE === "Administrator") {
                                    $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.size_name, ord.product_code, ord.comment, ord.created, ord.quantity, ord.line_item_total, ord.product_name, ord.product_price,
                                        ord.submitted_by, d.dep_name AS dept_name, d.dep_num, er.empName as ordered_for, er.email, ord.logo, comments.comment AS ORDERSTATUS,
                                        ord.order_details_id
                                        FROM ord_ref ord 
                                        JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                        LEFT JOIN comments ON comments.order_details_id = ord.order_details_id
                                        JOIN departments dr on ord.department = dr.dep_num
                                        JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                        WHERE d.dep_num = $empidd";
                                } else {
                                    $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.size_name, ord.product_code, ord.comment, ord.created, ord.quantity, ord.line_item_total, ord.product_name, ord.product_price,
                                        ord.submitted_by, d.dep_name AS dept_name, d.dep_num, er.empName as ordered_for, er.email, ord.logo, comments.comment AS ORDERSTATUS,
                                        ord.order_details_id
                                        FROM ord_ref ord 
                                        JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                        LEFT JOIN comments ON comments.order_details_id = ord.order_details_id
                                        JOIN departments dr on ord.department = dr.dep_num
                                        JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                        WHERE d.dep_num = $empidd AND dr.dep_head = $DEPT OR dr.dep_assist = $DEPT";
                                }
                                $c = 1;
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $SIZE = $row["size_name"];
                                        $STATUS = $row["status"];
                                        $COLOR = $row["color_id"];
                                        $SKU = $row["product_code"];
                                        $EMPNAMEE = $row["ordered_for"];
                                        $PRODUCTPRICE = $row["product_price"];
                                        $REQUESTDATE = $row["created"];
                                        $QNT = $row["quantity"];
                                        $PRODUCTNAME = $row["product_name"];
                                        $EMAILL = $row["email"];
                                        $DEPTNAME = $row["dept_name"];
                                        $DATE = $row["created"];
                                        $STAT = $row["status"];
                                        $LOGO = $row["logo"];
                                        $ORDSTATUS = $row["ORDERSTATUS"];
                                        $TOTALwithTAXES = $row["line_item_total"];
                                        $ORDERDETAILSID = $row['order_details_id'];

                                        echo "<tbody>";
                                        echo "<tr>";
                                        echo "<td>";
                                        echo "<div class='d-flex px-2 py-1'>";
                                        echo "<img src='../assets/img/bcg1.jpg' class='avatar avatar-sm me-3'>";
                                        echo "<div class='d-flex flex-column justify-content-center'>";
                                        // echo "<h6 class='mb-0 text-sm'>" . $row["first_name"] . "</span>" . " " . $row["last_name"] . " " . $row["id"] . "</h6>";
                                        echo "<h6 class='mb-0 text-sm'>$EMPNAMEE</h6>";
                                        echo "<p class='text-xs text-secondary mb-0'>$EMAILL</p>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<p class='text-xs font-weight-bold mb-0'>$STATUS</p>";
                                        echo "<p class='text-xs text-secondary mb-0'></p>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<p class='text-xs font-weight-bold mb-0'>$QNT</p>";
                                        echo "<p class='text-xs text-secondary mb-0'></p>";
                                        echo "</td>";
                                        echo "<td class='align-middle text-center text-xxl'>";
                                        echo "<span class='badge badge-sm bg-gradient-success'>" . "$" .  $TOTALwithTAXES;
                                        echo "</td>";
                                        echo "<td class='align-middle text-center'>";
                                        echo "<span class='text-secondary text-xs font-weight-bold'>$DATE</span>";
                                        echo "</td>";
                                        echo "<td class='align-middle'>";
                                        // <!-- Button trigger modal -->
                                        echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal$c'>";
                                        echo "Change Status of Request";
                                        echo "</button>";

                                        // <!-- Modal -->
                                        echo "<div class='modal fade' id='exampleModal$c' tabindex='-1' aria-labelledby='exampleModalLabel' aria-hidden='true'>";
                                        echo "<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg'>";
                                        echo "<div class='modal-content'>";
                                        echo "<div class='modal-header'>";
                                        echo "<h5 class='modal-title' id='exampleModalLabel'>This Request is from $EMPNAMEE</h5>";
                                        echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                                        echo "</div>";
                                        echo "<div class='modal-body'>";
                                        echo "<div id='wrapper'>";
                                        echo "<div id='content'>";
                                        echo "<p>";
                                        echo "<table>";
                                        echo "<tr>";
                                        echo "<th width=75%><strong>Approval Details</strong></th>";
                                        echo "<th width=75%><strong>Employee Has Submitted</strong></th>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Department</td>";
                                        echo "<td>$DEPTNAME</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Date of Request</td>";
                                        echo "<td>$DATE</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Status of Request</td>";
                                        echo "<td>$STAT</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Order Details ID</td>";
                                        echo "<td>$ORDERDETAILSID</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Number of Items in Request</td>";
                                        echo "<td>$QNT</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Price Per Item</td>";
                                        echo "<td>$PRODUCTPRICE</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Total Cost for All Items w/ tax</td>";
                                        echo "<td><strong><font color = 'red'>$TOTALwithTAXES</strong></font></td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Items Being Requested</td>";
                                        echo "<td>$PRODUCTNAME</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Product Code</td>";
                                        echo "<td>$SKU</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Size Employee Has Chosen</td>";
                                        echo "<td>$SIZE</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Color Employee has Chosen</td>";
                                        echo "<td>$COLOR</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Status of Item</td>";
                                        echo "<td>$ORDSTATUS</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Logo Selected by Employee</td>";
                                        echo "<td><img src='../../../$LOGO'</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "</table>";
                                        echo "</p>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "<div class='modal-footer'>";
                                        echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>";
                                        echo "<button  class='btn btn-warning' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateRec(this.value)'>Mark Item as Received</button>";
                                        echo "<button  class='btn btn-dark' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateOrdered(this.value)'>Mark Item as Ordered</button>";
                                        echo "<button  class='btn btn-danger' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateDenied(this.value)'>Mark Item as Denied</button>";
                                        echo "<button  class='btn btn-success' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateApproved(this.value)'>Mark Item as Approved</button>";
                                        echo "<button  class='btn btn-light' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateSize(this.value)'>Size Not Available</button>";
                                        echo "<button  class='btn btn-light' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateColor(this.value)'>Color Not Available</button>";
                                        echo "<button  class='btn btn-light' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateBO(this.value)'>Item On Backorder</button>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                        $c++;
                                    }
                                } else {
                                    echo "No Pending Requests";
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- END THAT HERE. THE COLUMNS THING -->






                <!-- BCG FOOTER -->
                <footer class="footer pt-3  ">
                    <div class="container-fluid">
                        <div class="row align-items-center justify-content-lg-between">
                            <div class="col-lg-6 mb-lg-0 mb-4">
                                <div class="copyright text-center text-sm text-muted text-lg-start">
                                    © <script>
                                    document.write(new Date().getFullYear())
                                    </script>,
                                    <a href="https://berkeleycountysc.gov/dept/it/" class="font-weight-bold"
                                        target="_blank">The Berkeley County IT Team</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
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
<!-- LINE SEPERATOR BETWEEN EACH ENTRY -->
<style>
.table> :not(:first-child) {
    border-top: 1px groove whitesmoke;
    /* border-top: thin inset currentColor; */
}

td img {
    width: 75px;
}
</style>