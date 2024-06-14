<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: sign-in.php");
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
        Previous Department Reports
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
</head>

<!-- NOT GOING TO USE ANY DARK ELEMENTS. CAN REMOVE JS DARK -->
<!-- -----------------APPROVED COUNTER and TOTAL ----------------------------- -->
<?php
$empNumber = $_SESSION["empNumber"];
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
if ($ROLE === "Administrator") {
    $sql = "SELECT COUNT(distinct order_inst_id) as TOTALREPORTS FROM uniform_orders.order_inst";
} else {
    echo "YOU DO NOT HAVE ACCESS TO THIS PAGE";
}
$TR = 0;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $TR = $row["TOTALREPORTS"];
    }
} else {
    echo "No Pending Requests";
}
?>

<body class="g-sidenav-show   bg-gray-100">
    <?php include "./sidenav.php" ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Total Number of Previous Reports Generated: <font color="green"> <?php echo $TR ?>
                            </font>
                            <div class="input-group">
                                <input type="date" name="startdate" id="startdate" class="form-control form-control-lg">
                                <input type="date" name="enddate" id="enddate" class="form-control form-control-lg">
                                <input type="text" name="dept" id="dept" placeholder="Enter Department Number" class="form-control form-control-lg">
                            </div>
                            <center> <button type="button" onclick="dateSearch()" class="btn btn-outline-dark">Search by
                                    Date and Department</button>
                            </center>

                        </h6>
                    </div>
                    <script>
                        function dateSearch() {
                            let datestart = document.getElementById("startdate").value
                            let dateend = document.getElementById("enddate").value
                            let dept = document.getElementById("dept").value
                            if (datestart == "" && dateend == "") {
                                alert("You have not selected a start or an end date")
                            } else if (datestart == "") {
                                alert("You have selected a End Date, but not an Start date. Please choose " +
                                    dateend)
                            } else if (dateend == "") {
                                alert("You have selected a Start Date, but not an End date. Please choose " +
                                    datestart)
                            } else if (dept == "") {
                                alert("You have selected dates, but not a department. Please choose a department ")
                            } else
                                location.replace("deptRec.php?datestart=" + datestart + "&dateend=" + dateend +
                                    "&dept=" + dept)
                        }
                    </script>


                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Report Number</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Department</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Date/Time Report was Generated</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>


                                <!-- POPULATE THE LATEST REQUESTS -->
                                <?php
                                $empNumber = $_SESSION["empNumber"];
                                $DEPT = $_SESSION["empNumber"];
                                $ROLE = $_SESSION["role_name"];
                                if ($ROLE == "Administrator") {
                                    $sql = "SELECT order_inst.order_inst_id, order_inst.created_by_emp_num, order_inst.order_for_dept, order_inst.order_inst_created,
                                        dep_ref.dep_name
                                        FROM uniform_orders.order_inst
                                        JOIN dep_ref ON order_inst.order_for_dept = dep_ref.dep_num";
                                } else {
                                    echo "YOU ARE NOT AN ADMIN";
                                }
                                $c = 1;
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $UID = $row["order_inst_id"];
                                        $CREATEDBY = $row["created_by_emp_num"];
                                        $DEPTORDER = $row["dep_name"];
                                        $REPORTCREATEDON = $row["order_inst_created"];

                                        echo "<tbody>";
                                        echo "<tr>";
                                        echo "<td>";
                                        echo "<div class='d-flex px-2 py-1'>";
                                        echo "<img src='../assets/img/bcg1.jpg' class='avatar avatar-sm me-3'>";
                                        echo "<div class='d-flex flex-column justify-content-center'>";
                                        // echo "<h6 class='mb-0 text-sm'>" . $row["first_name"] . "</span>" . " " . $row["last_name"] . " " . $row["id"] . "</h6>";
                                        echo "<h6 class='mb-0 text-sm'>$ID</h6>";
                                        // echo "<p class='text-xs text-secondary mb-0'>$EMAIL</p>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<p class='text-xs font-weight-bold mb-0'>$UID</p>";
                                        echo "<p class='text-xs text-secondary mb-0'></p>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<p class='text-xs font-weight-bold mb-0'>$DEPTORDER</p>";
                                        echo "<p class='text-xs text-secondary mb-0'></p>";
                                        echo "</td>";
                                        echo "<td class='align-middle text-center'>";
                                        echo "<span class='text-secondary text-xs font-weight-bold'>$REPORTCREATEDON</span>";
                                        echo "</td>";
                                        echo "<td class='align-middle'>";
                                        // <!-- Button trigger modal -->
                                        echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal$c'>";
                                        echo "Report Details";
                                        echo "</button>";

                                        // <!-- Modal -->
                                        echo "<div class='modal fade' id='exampleModal$c' tabindex='-1' aria-labelledby='exampleModalLabel' aria-hidden='true'>";
                                        echo "<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg'>";
                                        echo "<div class='modal-content'>";
                                        echo "<div class='modal-header'>";
                                        echo "<h5 class='modal-title' id='exampleModalLabel'>Report ID: $UID</h5>";
                                        echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                                        echo "</div>";
                                        echo "<div class='modal-body'>";
                                        echo "<div id='wrapper'>";
                                        echo "<div id='content'>";
                                        echo "<p>";
                                        echo "<table>";
                                        echo "<tr>";
                                        echo "<th width=75%><strong>Report Information</strong></th>";
                                        echo "<th width=75%><strong>Employee Information</strong></th>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td>Report Created by</td>";
                                        echo "<td>$CREATEDBY</td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "</table>";
                                        echo "</p>";
                                        echo "</div>";
                                        echo "</div>";
                                        echo "<div class='modal-footer'>";
                                        echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>";
                                        echo "<a class='btn btn-primary' <a href='pastReportsPrint.php?UID=$UID' role='button'>Go To Report</a>";
                                        // echo "<button  class='btn btn-success' value = '$ORDERDETAILSID' class='btn btn-outline-info' onclick='updateApproved(this.value)'>Change Status Back to Ordered</button>";
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

    .input-group {
        display: flex;
        justify-content: space-around;
        gap: 30px;
    }

    .input-group input {
        width: 15vw;
        border: 1px solid black;
    }
</style>