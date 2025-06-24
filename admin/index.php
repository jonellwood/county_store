<?php
include('./pages/DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ./pages/sign-in.php");
} else {
    header("Location: ./pages/employeeRequests.php");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-touch-icon-bcg.png">
    <link rel="icon" type="image/png" href="./assets/img/bcg-favicon.ico">
    <title>
        BCG County Store Admin Panel
    </title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- BCG Icons -->
    <link href="./assets/css/icons.css" rel="stylesheet" />
    <link href="./assets/css/svg.css" rel="stylesheet" />
    <!-- Font Awesome is AWESOME -->
    <link href="./assets/css/svg.css" rel="stylesheet" />
    <!-- Da BeRkStRaP CSS -->
    <link id="pagestyle" href="./assets/css/berkstrap.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php include "./indexSidenav.php" ?>

    <!-------------------------------------------------- MAIN LOGO TOP OF DASHBOARD------------------------------------>
    <div class="container-fluid py-4">
        <a class="navbar-brand m-0" href="index.php" target="_blank">
            <center>
                <img src="./assets/img/bcg-hz-lblue.png" class="navbar-brand-img h-75 w-75" alt="main_logo">
            </center>
            <span class="ms-1 font-weight-bold"></span>
        </a>

        <div class="row">

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">

                <div class="card">

                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">


                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">

                    <div class="col-4 text-end">

                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">

                    <div class="col-4 text-end">
                    </div>
                </div>
            </div>

            <!-- BEGIN THE BCG CHART TO TRACK SPENDING  -->
            <!-- Converting the PHP Variables to JS below, near the scripts. -->
            <div class="row mt-4">
                <div class="col-lg-7 mb-lg-0 mb-4">
                    <!-- <div class="card z-index-2 h-100"> -->
                    <!-- <div class="card-header pb-0 pt-3 bg-transparent">
                            <h6 class="text-capitalize">Department Spending Overview</h6>
                            <p class="text-sm mb-0">
                                <span class="font-weight-bold">Month - To - Month for the year <script>
                                    document.write(new Date().getFullYear())
                                    </script>
                            </p>
                        </div> -->
                    <!-- <div class="card-body p-3"> -->
                    <!-- <div class="chart">
                
                </?php include "chart-for-index.php" ?>
              </div> -->
                    <!-- </div> -->
                    <!-- </div> -->
                </div>
                <div class="col-lg-5">
                    <div class="card card-carousel overflow-hidden h-100 p-0">
                        <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                            <div class="carousel-inner border-radius-lg h-100">
                                <!-- <div class="carousel-item h-100 active" style="background-image: url('./assets/img/bcg-logos1.jpg');
      background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                    </div>
                  </div>
                </div> -->

                                <!-- ***************************************Homepage BCG CAROUSEL****************************************************** -->
                                <!-- IMAGE1 -->
                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos2.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 2 -->

                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos3.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 3 -->

                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos4.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 4 -->

                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos5.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 5 -->

                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos8.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 6 -->

                                <div class="carousel-item h-100" style="background-image: url('./assets/img/bcg-logos10.jpg');
      background-size: cover;">
                                    <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                        <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        </div>
                                    </div>
                                </div>
                                <!-- IMAGE 7 -->

                            </div>
                            <button class="carousel-control-prev w-5 me-3" type="button"
                                data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next w-5 me-3" type="button"
                                data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!---------------------------- 3RD CONTAINER. MOST RECENT REQUESTS----------------- -->
            <div class="row mt-4">
                <div class="col-lg-7 mb-lg-0 mb-4">
                    <div class="card ">
                        <div class="card-header pb-0 p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-2">
                                    Most Recent Employee Uniform Requests
                                </h6>
                                <a href="./pages/requests.php" class="btn btn-outline-info" role="button">Click Here to
                                    View/Approve/Deny All Requests</a>
                            </div>
                        </div>
                        <!-- POPULATE THE LATEST REQUESTS WITH SOME WOLFPACK PHPSQL MAGIK -->
                        <?php
            $empNumber = $_SESSION["empNumber"];
            $DEPT = $_SESSION["department"];
            $ROLE = $_SESSION["role_name"];
            if ($ROLE === "Administrator") {
              $sql = "SELECT ord.status, ord.quantity, ord.order_details_id, ord.line_item_total, ord.rf_first_name, ord.rf_last_name, ord.requested_by_name
                FROM ord_ref ord 
                JOIN dep_ref dr on ord.department = dr.dep_num
                WHERE status = 'Pending' LIMIT 6";
            } else {
              $sql = "SELECT 
                ord.status,
                ord.quantity,
                ord.order_details_id,
                ord.line_item_total,
                ord.rf_first_name,
                ord.rf_last_name,
                ord.requested_by_name
            FROM
                ord_ref ord
                    JOIN
                dep_ref dr ON ord.department = dr.dep_num
            WHERE
                status = 'Pending'
                    AND (dr.dep_head = $empNumber
                    OR dr.dep_assist = $empNumber)";
            }

            $c = 1;
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                $QNT = $row["quantity"];
                $TOTALwTAXES = $row["line_item_total"];
                $EMP_FN = $row["rf_first_name"];
                $EMP_LN = $row["rf_last_name"];
                $REQUESTOR = $row["requested_by_name"];

                echo "<div class='table-responsive'>";
                echo "<table class='table align-items-center'>";
                echo "<tbody>";
                echo "<tr>";
                echo "<td class='w-20'>";
                echo "<div class='d-flex px-2 py-1 align-items-center'>";
                echo "<i class='ni ni-atom text-info text-sm opacity-10'></i>";
                echo "<div class='ms-4'>";
                echo "<p class='text-xs font-weight-bold mb-0'>Employee Name:</p>";
                echo "<h6 class='text-sm mb-0'>$EMP_FN $EMP_LN</h6>";
                echo "</div>";
                echo "</div>";
                echo "</td>";
                echo "<td class='w-20'>";
                echo "<div class='text-center'>";
                echo "<p class='text-xs font-weight-bold mb-0'>Number of Items Requested:</p>";
                echo "<h6 class='text-sm mb-0'>$QNT</h6>";
                echo "</div>";
                echo "</td>";
                echo "<td class='w-20'>";
                echo "<div class='text-center'>";
                echo "<p class='text-xs font-weight-bold mb-0'>Cost of Request:</p>";
                echo "<h6 class='text-sm mb-0'>$$TOTALwTAXES</h6>";
                echo "</div>";
                echo "</td>";
                echo "<td class='align-middle text-sm w-25'>";
                echo "<div class='col text-center'>";
                echo "<p class='text-xs font-weight-bold mb-0'>Requested By:</p>";
                echo "<h6 class='text-sm mb-0'>$REQUESTOR</h6>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "</div>";
                echo "</div>";
                echo "</td>";
                // $c++;
              }
            } else {
              // echo "No Pending Requests";
              echo "<div class='table-responsive'>";
              echo "<table class='table align-items-center'>";
              echo "<tbody>";
              echo "<tr>";
              echo "<td class='w-20'>";
              echo "<div class='d-flex px-2 py-1 align-items-center'>";
              echo "<i class='ni ni-atom text-info text-sm opacity-10'></i>";
              echo "<div class='ms-4'>";
              echo "<p class='text-xs font-weight-bold mb-0'>Employee Name:</p>";
              echo "<h6 class='text-sm mb-0'></h6>";
              echo "</div>";
              echo "</div>";
              echo "</td>";
              echo "<td class='w-20'>";
              echo "<div class='text-center'>";
              echo "<p class='text-xs font-weight-bold mb-0'>Number of Items Requested:</p>";
              echo "<h6 class='text-sm mb-0'></h6>";
              echo "</div>";
              echo "</td>";
              echo "<td class='w-20'>";
              echo "<div class='text-center'>";
              echo "<p class='text-xs font-weight-bold mb-0'>Cost of Request:</p>";
              echo "<h6 class='text-sm mb-0'></h6>";
              echo "</div>";
              echo "</td>";
              echo "<td class='align-middle text-sm w-25'>";
              echo "<div class='col text-center'>";
              echo "<p class='text-xs font-weight-bold mb-0'>Requested By:</p>";
              echo "<h6 class='text-sm mb-0'></h6>";
              echo "</div>";
              echo "</td>";
              echo "</tr>";
              echo "<tr>";
              echo "</div>";
              echo "</div>";
              echo "</td>";
            }
            ?>


                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Pending Approvals Needing to Be Ordered from Vendor</h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group">
                            <!-- BEGIN 4TH CONTAINER FOR MOST REQUESTED ITEMS -->
                            <!-- POPULATE THE LATEST REQUESTS -->
                            <?php
              if ($ROLE === "Administrator") {
                $sql = "SELECT ord.status, ord.quantity, ord.order_details_id, ord.line_item_total, ord.rf_first_name, ord.rf_last_name, ord.requested_by_name
                  FROM ord_ref ord 
                  JOIN dep_ref dr on ord.department = dr.dep_num
                  WHERE status = 'Approved' LIMIT 7";
              } else {
                $sql = "SELECT ord.status, ord.quantity, ord.order_details_id, ord.line_item_total, ord.rf_first_name, ord.rf_last_name, ord.requested_by_name
                  FROM ord_ref ord 
                  JOIN dep_ref dr on ord.department = dr.dep_num
                  WHERE status = 'Approved' AND dr.dep_head  = $empNumber AND dr.dep_num = $DEPT OR dr.dep_assist = $empNumber AND status = 'Approved' and dr.dep_num = $DEPT LIMIT 6";
              }

              $c = 1;
              $result = mysqli_query($conn, $sql);
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $QNT = $row["quantity"];
                  $TOTALwTAXES = $row["line_item_total"];
                  $EMP_FN = $row["rf_first_name"];
                  $EMP_LN = $row["rf_last_name"];
                  $REQUESTOR = $row["requested_by_name"];

                  echo "<li class='list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg'>";
                  echo "<div class='d-flex align-items-center'>";
                  echo "<div class='icon icon-shape icon-sm me-3 bg-gradient-none'>";
                  echo "<i class='ni ni-send text-danger text-sm opacity-10'></i>";
                  echo "</div>";
                  echo "<div class='d-flex flex-column'>";
                  echo "<h6 class='mb-1 text-dark text-sm'>$EMP_FN $EMP_LN</h6>";
                  echo "<span class='text-xs'>Requested By: $REQUESTOR | $QNT Items | $TOTALwTAXES | <a href='./pages/orders-by-dept-for-admin.php'>Go To Approvals</span></a>";
                  echo "</div>";
                  echo "</li>";
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
        <footer class="footer pt-3  ">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            © <script>
                            document.write(new Date().getFullYear())
                            </script>,
                            <a href="https://berkeleycountysc.gov/dept/it/" class="font-weight-bold" target="_blank">The
                                Berkeley County IT Team</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    </main>


    <!--   Core JS Files   -->
    <!-- <script src="./assets/js/core/popper.min.js"></script> -->
    <!-- <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script> -->
    <!-- <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script> -->
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugins/chartjs.min.js"></script>


    <!-- <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script> -->

</body>

</html>