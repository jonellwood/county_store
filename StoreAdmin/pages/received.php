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
        All Ordered Items
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- SideBar Icons -->
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" />
    <script>
        function updateApproved(id) {

            var ajax = new XMLHttpRequest();
            ajax.open("POST", "../pages/approved.php?id=" + id, true);
            ajax.send();

            alert("Request Has Been Changed Back Approved!!");
            document.location.reload();
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
</head>

<!-- NOT GOING TO USE ANY DARK ELEMENTS. CAN REMOVE JS DARK -->
<!-- -----------------APPROVED COUNTER and TOTAL ----------------------------- -->
<?php
$empNumber = $_SESSION["empNumber"];
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
if ($ROLE === "Administrator") {
    $sql = "SELECT *, SUM(quantity) as total_qnt, SUM(line_item_total) as ordertotal, COUNT(order_details_id) as orderCount 
    FROM ord_ref 
    JOIN emp_ref ON ord_ref.emp_id = emp_ref.empNumber 
    JOIN departments dr on ord_ref.department = dr.dep_num
    WHERE status = 'Ordered'";
} else {
    $sql = "SELECT SUM(quantity) as total_qnt, SUM(line_item_total) as ordertotal, COUNT(order_details_id) as orderCount 
    FROM ord_ref 
    JOIN emp_ref ON ord_ref.emp_id = emp_ref.empNumber 
    JOIN departments dr on ord_ref.department = dr.dep_num
    WHERE status = 'Ordered' AND dr.dep_head = $empNumber
    OR
    status = 'Ordered' AND dr.dep_assist = $empNumber";
}
$APPROVED = 0;
$APPSUM = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $TOTALwTAXES = $row["ordertotal"];
        $TOTALwTAXES = number_format($TOTALwTAXES, 2);
        $ORDERS = $row["orderCount"];
    }
} else {
    echo "No Pending Requests";
}
?>

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

                        </li>
                    </ul>
                    </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Pending Orders placed with Vendor: <font color="green"> <?php echo $ORDERS ?></font>
                            </h6>
                            <h6>These Items Ordered From the Vendor have a grand total of: <font color="blue">
                                    $<?php echo $TOTALwTAXES ?></h6>
                            </font>
                        </div>


                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Ordered Item For</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Description of Item</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Number of Items in Order</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Total Cost of Order</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Date Requested</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>


                                    <!-- POPULATE THE LATEST REQUESTS -->
                                    <?php
                                    $empNumber = $_SESSION["empNumber"];
                                    if ($ROLE === "Administrator") {
                                        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.size_name, ord.comment, ord.emp_id, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                    ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email 
                                    FROM ord_ref ord 
                                    JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                    -- JOIN dep_ref dr on ord.department = dr.dep_num
                                    JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                    WHERE status = 'Ordered'";
                                    } else {
                                        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.size_name, ord.comment, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                      ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email 
                                      FROM ord_ref ord 
                                      JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                    --   JOIN dep_ref dr on ord.department = dr.dep_num
                                      JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                      WHERE status = 'Ordered' AND d.dep_head = $empNumber
                                      OR
                                      status = 'Ordered' AND d.dep_assist = $empNumber";
                                    }
                                    $c = 1;
                                    $result = mysqli_query($conn, $sql);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $SIZE = $row["size_name"];
                                            $COLOR = $row["color_id"];
                                            $EMPNAME = $row["ordered_for"];
                                            $EMPLOYEEID = $row["emp_id"];
                                            $PRODUCTNAME = $row["product_name"];
                                            $PRODUCTPRICE = $row["product_price"];
                                            $REQUESTDATE = $row["created"];
                                            $QNT = $row["quantity"];
                                            $PRODUCTNAME = $row["product_name"];
                                            $GRANDTOTAL = ($QNT * $PRODUCTPRICE);
                                            $EMAIL = $REQUESTDATE = $row["email"];
                                            $DEPTNAME = $row["dept_name"];
                                            $ORDERDETAILSID = $row['order_details_id'];
                                            $DATE = $row["created"];
                                            $TOTALwTX = $row["line_item_total"];
                                            $COMMENT = $row["comment"];

                                            echo "<tbody>";
                                            echo "<tr>";
                                            echo "<td>";
                                            echo "<div class='d-flex px-2 py-1'>";
                                            echo "<img src='../assets/img/bcg1.jpg' class='avatar avatar-sm me-3'>";
                                            echo "<div class='d-flex flex-column justify-content-center'>";
                                            // echo "<h6 class='mb-0 text-sm'>" . $row["first_name"] . "</span>" . " " . $row["last_name"] . " " . $row["id"] . "</h6>";
                                            echo "<h6 class='mb-0 text-sm'>$EMPNAME</h6>";
                                            echo "<p class='text-xs text-secondary mb-0'>$EMAIL</p>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "<td>";
                                            echo "<p class='text-xs font-weight-bold mb-0'>$PRODUCTNAME</p>";
                                            echo "<p class='text-xs text-secondary mb-0'></p>";
                                            echo "</td>";
                                            echo "<td>";
                                            echo "<p class='text-xs font-weight-bold mb-0'>$QNT</p>";
                                            echo "<p class='text-xs text-secondary mb-0'></p>";
                                            echo "</td>";
                                            echo "<td class='align-middle text-center text-xxl'>";
                                            echo "<span class='badge badge-sm bg-gradient-success'>" . "$" .  $TOTALwTX;
                                            echo "</td>";
                                            echo "<td class='align-middle text-center'>";
                                            echo "<span class='text-secondary text-xs font-weight-bold'>$DATE</span>";
                                            echo "</td>";
                                            echo "<td class='align-middle'>";
                                            // <!-- Button trigger modal -->
                                            echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal$c'>";
                                            echo "Mark as Item Received";
                                            echo "</button>";

                                            // <!-- Modal -->
                                            echo "<div class='modal fade' id='exampleModal$c' tabindex='-1' aria-labelledby='exampleModalLabel' aria-hidden='true'>";
                                            echo "<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg'>";
                                            echo "<div class='modal-content'>";
                                            echo "<div class='modal-header'>";
                                            echo "<h5 class='modal-title' id='exampleModalLabel'>You Have Approved This Request from $EMPNAME</h5>";
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
                                            echo "<td>Number of Items Employee is Requesting</td>";
                                            echo "<td>$QNT</td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "<td>Price Per Item</td>";
                                            echo "<td>$PRODUCTPRICE</td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "<td>Total Cost for All Items w/ Taxes</td>";
                                            echo "<td><strong><font color = 'red'>$TOTALwTX</strong></font></td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "<td>Items Being Requested</td>";
                                            echo "<td>$PRODUCTNAME</td>";
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
                                            echo "<td>Comment from User</td>";
                                            echo "<td>$COMMENT</td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "</table>";
                                            echo "</p>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "<div class='modal-footer'>";
                                            echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>";
                                            echo "<button  class='btn btn-warning' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateRec(this.value)'>Mark Item as Received from Vendor. Have it in Hand</button>";
                                            echo "<button  class='btn btn-success' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateApproved(this.value)'>Change Status Back to Approved</button>";
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
                                        <a href="https://berkeleycountysc.gov/dept/it/" class="font-weight-bold" target="_blank">The Berkeley County IT Team</a>
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
</style>