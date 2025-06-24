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
        Pending Employee Requests
    </title>
    <!--     Fonts and icons     -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" /> -->
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/berkstrap.css" rel="stylesheet" />

    <!-- -----------------PENDING COUNTER ----------------------------- -->
    <?php
    $DEPT = $_SESSION["department"];
    $USER = $_SESSION["userName"];
    $ROLE = $_SESSION["role_name"];
    $empNumber = $_SESSION["empNumber"];
    if ($ROLE === "Administrator") {
        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                    ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email, 
                                    COUNT(ord.order_details_id) as PENDINGCOUNT, COUNT(DISTINCT ord.emp_id) as EMPLOYEES, COUNT(DISTINCT d.dep_name) as DEPTT
                                    FROM ord_ref ord 
                                    JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                    JOIN dep_ref dr on ord.department = dr.dep_num
                                    JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                    WHERE status = 'Pending'";
    } else {
        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                                      ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email, COUNT(ord.order_details_id) as PENDINGCOUNT
                                      FROM ord_ref ord 
                                      JOIN emp_ref er ON ord.emp_id = er.empNumber 
                                      JOIN dep_ref dr on ord.department = dr.dep_num
                                      JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                                      WHERE status = 'Pending' AND dr.dep_head = $empNumber";
    }
    $PENDING = 0;
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $PENDING++;
            $PENDINGCOUNT = $row["PENDINGCOUNT"];
            $DEPTT = $row["DEPTT"];
            $EMPLOYEES = $row["EMPLOYEES"];
        }
    } else {
        echo "No Pending Requests";
    }
    ?>


    <script>
        function updateApproved(id) {

            var ajax = new XMLHttpRequest();
            ajax.open("POST", "../pages/approved.php?id=" + id, true);
            ajax.send();

            alert("Request Has Been Approved!!");
            window.location.href = '../pages/requests.php';
        }
    </script>
    <script>
        function grabTotal(ORDID, EMPID) {
            var ajax = new XMLHttpRequest();
            var gToats = 0;
            ajax.open("POST", "emptotal.php?ORDID=" + ORDID + "&EMPID=" + EMPID, true);
            ajax.send();
            console.log('Ajax sent with ID: ' + ORDID);
            console.log('Ajax sent with EmpID: ' + EMPID);
            ajax.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    var response = JSON.parse(this.responseText)
                    console.log(response);
                    // console.log('total is ' + response[0].total);
                    var html = "<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg'>"
                    html += "<div class='modal-content'>"
                    html += "<div class='modal-header'>";
                    html += "<h5 class='modal-title' id='exampleModalLabel'>Uniform Requests for " + response[0]
                        .empName + "</h5>";
                    html +=
                        "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button></div>";
                    html += "<div class='modal-body'><div id='wrapper'><div id='content'>"

                    html += "<table><tr>";
                    html += "<th width=75%><strong>Request Details</strong></th>";
                    html += "<th width=75%><strong>Employee Has Submitted</strong></th>";
                    html += "</tr>";
                    html += "<tr><td>Current Status: </td>";
                    html += "<td>" + response[0].status + "</td></tr>";
                    // end row
                    html += "<tr><td>Spent On Employee FY23: </td>";
                    html += "<td><font color='green'><strong>$ " + response[1].gToats.toFixed(2) +
                        "</td></tr></font></strong>"
                    // end row
                    html += "<tr><td>Approved So Far: </td>";
                    html += "<td><font color='green'><strong>$ " + response[2].gToats1.toFixed(2) +
                        "</td></tr></font></strong>"
                    // end row
                    html += "<tr><td>Department</td>";
                    html += "<td>" + response[0].deptName + "</td></tr>";
                    // end row
                    html += "<tr><td>Order ID</td>";
                    html += "<td>" + response[0].order_id + "</td></tr>";
                    // end row

                    html += "<tr><td>Date of Request</td>";
                    html += "<td>" + response[0].created + "</td></tr>";
                    // end row
                    html += "<tr><td>Number of Items Employee is Requesting</td>";
                    html += "<td>" + response[0].quantity + "</td></tr>";
                    // end row
                    html += "<tr><td>Price per Item</td>";
                    html += "<td>$ " + response[0].product_price + "</td><tr>";
                    // end row
                    html += "<tr><td>Total Cost of ALL Items</td>";
                    html += "<td><strong><font color = 'red'> $ " + response[0].line_item_total +
                        "</strong></font></td></tr>";
                    // end row
                    html += "<tr><td>Items Being Requested</td>";
                    html += "<td>" + response[0].product_name + "</td></tr>";
                    // end row
                    html += "<tr><td>Size Employee Has Chosen</td>";
                    html += "<td>" + response[0].size_name + "</td></tr>";
                    // end row
                    html += "<tr><td>Color Employee has Chosen</td>";
                    html += "<td>" + response[0].color_id + "</td></tr>";
                    // end row
                    html += "<tr><td>Comment from Employee</td>";
                    html += "<td>" + response[0].comment + "</td></tr>";
                    // end row
                    html += "</table>";
                    // include options only for those departments that have thier own logo
                    <?php if ($_SESSION['role_id'] == 4 || $_SESSION['role_id'] == 5 || $_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) { ?>
                        html += "<select name='logo' id='logo'>";
                        // html += "<option id='logoPick' value='{'Logo':'County','id':'" + response[0].order_details_id +
                        //     "'}>Berkeley County Logo</option>"
                        // html += "<option id='logoPick' value='{'Logo':'Department','id':'" + response[0].order_details_id +
                        //     "'}'>Department Logo</option>"
                        html += "<option value='-'>Select Logo</option>";
                        html += "<option value='" + response[0].order_details_id +
                            "|Berkeley'>Berkeley County Logo</option>";
                        html += "<option value='" + response[0].order_details_id +
                            "|Department'>Department Logo</option>";
                        html += "</select>";

                    <?php } ?>


                    // end of special options
                    html += "</div>";
                    html += "</div>";
                    html += "<div class='modal-footer'>";
                    html +=
                        "<button type='button' class='btn btn-secondary' data-bs-toggle='modal' data-bs-target='#exampleModal'>Close</button>";
                    html += "<button  class='btn btn-success' value='" + response[0].order_details_id +
                        "' class='btn btn-outline-info' onclick='updateApproved(this.value)'>Approve</button>"
                    html += "<button  class='btn btn-danger' value='" + response[0].order_details_id +
                        "' class='btn btn-outline-info' onclick='updateDenied(this.value)'>Deny</button>";

                    html += "</div>";
                    html += "</div>";
                    html += "</div>";
                }
                document.getElementById("exampleModal").innerHTML = html


            }
        }
    </script>

    <script>
        function updateDenied(id) {

            var ajax = new XMLHttpRequest();
            ajax.open("POST", "../pages/denied.php?id=" + id, true);
            ajax.send();

            ajax.onreadystatechange = function() {

                if (this.readyState == 4 && this.status == 200) {

                    alert("Request Has Been Denied");
                    window.location.href = '../pages/requests.php';

                }
            }
        }
    </script>
</head>



<!-- NOT GOING TO USE ANY DARK ELEMENTS. CAN REMOVE JS DARK -->

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
                            <h6>
                                <p id="gTotal"></p><?php echo $PENDINGCOUNT ?> Pending Requests from
                                <?php echo $EMPLOYEES ?> Employees Across <?php echo $DEPTT ?> Departments
                            </h6>
                        </div>

                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Employee Request is For</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Description of Item</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Number of Items in Request</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Total Cost of Request</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Date Requested</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Requestor</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>


                                    <!-- POPULATE THE LATEST REQUESTS -->
                                    <?php
                                    $empNumber = $_SESSION["empNumber"];
                                    $DEPT = $_SESSION["empNumber"];
                                    $ROLE = $_SESSION["role_name"];
                                    if ($ROLE == "Administrator") {
                                        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.size_name, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                  ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email
                  FROM ord_ref ord 
                  JOIN emp_ref er ON ord.emp_id = er.empNumber 
                  JOIN dep_ref dr on ord.department = dr.dep_num
                  JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                  WHERE status = 'Pending'";
                                    } else {
                                        $sql = "SELECT ord.status, ord.size_id, ord.color_id, ord.emp_id, ord.size_name, ord.created, ord.quantity, ord.order_details_id, ord.line_item_total, ord.product_name, ord.product_price, ord.product_code, ord.vendor,
                    ord.submitted_by, d.dep_name AS dept_name, er.empName as ordered_for, er.email
                    FROM ord_ref ord 
                    JOIN emp_ref er ON ord.emp_id = er.empNumber 
                    JOIN dep_ref dr on ord.department = dr.dep_num
                    JOIN (SELECT * from departments) d on d.dep_num = er.deptNumber
                    WHERE status = 'Pending' AND dr.dep_head = $empNumber ";
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
                                            $EMAIL = $REQUESTDATE = $row["email"];
                                            $ORDERDETAILSID = $row['order_details_id'];
                                            $DATE = $row["created"];
                                            $STATUS = $row["status"];
                                            $PMTR = $row["submitted_by"];
                                            $TOTALwTX = $row["line_item_total"];

                                            echo "<tbody>";
                                            echo "<tr  class = '$STATUS'>";
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
                                            echo "<td class='align-middle text-center text-xxl'>";
                                            echo "<span class='text-secondary text-xs font-weight-bold'>$DATE</span>";
                                            echo "</td>";
                                            echo "<td class='align-middle text-center'>";
                                            echo "<span class='text-secondary text-xs font-weight-bold'>$PMTR</span>";
                                            echo "<p id='gTotal'></p>";
                                            echo "</td>";
                                            echo "<td class='align-middle'>";
                                            // <!-- Button trigger modal -->
                                            echo "<button type='button' onclick = 'grabTotal($ORDERDETAILSID , $EMPLOYEEID)' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal'>";
                                            echo "Approve or Deny";
                                            echo "</button>";
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
                                        <p id="gTotal"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ...
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>


    </main>
    <div class="fixed-plugin">
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0"></h5>
                </div>

            </div>
        </div>
    </div>
    </div>

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
    <script src="https://code.jquery.com/jquery-3.6.3.js" integrity="sha256-nQLuAZGRRcILA+6dMBOvcRh5Pe310sBpanc6+QBmyVM=" crossorigin="anonymous"></script>

    <script>
        $(document).on('change', '#logo', function() {
            var id = $('#logo').val().split('|')[0];
            // console.log('id is... ' + id);
            var logo = $('#logo').val().split('|')[1];
            // console.log('Logo is.... ' + logo);

            $.ajax({
                url: '../pages/setLogo.php?id=' + id + '&logo=' + String(logo),
                method: 'GET',
                success: console.log('request sent'),
            })
        });
    </script>


</html>



<style>
    .Approved {
        display: none !important;
    }

    .Denied {
        display: none !important;
    }

    .Ordered {
        display: none !important;
    }
</style>