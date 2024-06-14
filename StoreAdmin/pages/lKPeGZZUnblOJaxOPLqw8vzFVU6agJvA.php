<?php
include('DBConn.php');
?>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['setSessionValues'])) {
        setSessionValues();
    }
}
function setSessionValues()
{

    $_SESSION["loggedin"] = true;
    $_SESSION["department"] = '41515';
    $_SESSION["userName"] = "Cheryl Greenwood";
    $_SESSION["empName"] = "Cheryl Greenwood";
    $_SESSION["empNumber"] = "8685";
    $_SESSION['role_id'] = "1";
    $_SESSION["email"] = "cheryl.greenwood@berkeleycountysc.gov";
    $_SESSION["role_name"] = "Administrator";
}

$users = array();
$sql = "SELECT ur.emp_num, ur.empName
        FROM user_ref ur";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($users, $row);
    }
}
// var_dump($users);

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
    <script>
    function updateSessionData(empNum) {
        // alert(empNum);
        fetch('./iAeDJBFBpWYgEoIuulridvvfutrVDEC3.php?empNum=' + empNum)
            .then(window.location.reload())
    }
    </script>
</head>

<!-- -----------------ORDERED COUNTER and TOTAL ----------------------------- -->
<!-- returns all details regarding orders that are status = ordered and filtered by dept when needed-->
<?php
$DEPT = $_SESSION["department"];
$USER = $_SESSION["userName"];
$ROLE = $_SESSION["role_name"];
$empNumber = $_SESSION["empNumber"];


// var_dump($_SESSION);
?>


<body class="g-sidenav-show   bg-gray-100">
    <div class="setLoggedIn">
        <form method="post">
            <input type="submit" name="setSessionValues" value="Set Sherry as Logged In">
        </form>
        <?php
        if ($_SESSION["loggedin"]) {
            echo "<p>Logged In As: " . $_SESSION['empName'] . "</p>";
        } else {
            echo "<p>Session values are not set.</p>";
        }
        ?>
        <select id="userSelect" onchange="updateSessionData(this.value)">
            <option value="">Select a user</option>
            <?php foreach ($users as $user) { ?>
            <option value="<?php echo $user['emp_num']; ?>"><?php echo $user['empName']; ?></option>
            <?php } ?>
        </select>
    </div>
    <?php include "./sidenav.php" ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-xl-6 mb-xl-0 mb-4">
                        <div class="card bg-transparent shadow-xl">
                            <div class="overflow-hidden position-relative border-radius-xl"
                                style="background-image: url('../assets/img/jc.jpg');">
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
                                        <div
                                            class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
                                            <i class="fas fa-check opacity-10"></i>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0 p-3 text-center">
                                        <h6 class="text-center mb-0">Pending</h6>
                                        <span class="text-xs">Quick Link: Pending</span>
                                        <hr class="horizontal dark my-3">
                                        <h5 class="mb-0"><a href="requests.php">Pending Requests</a></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-md-0 mt-4">
                                <div class="card">
                                    <div class="card-header mx-4 p-3 text-center">
                                        <div
                                            class="icon icon-shape icon-lg bg-gradient-primary shadow text-center border-radius-lg">
                                            <i class="fas fa-thumbs-up opacity-10"></i>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0 p-3 text-center">
                                        <h6 class="text-center mb-0">Approved</h6>
                                        <span class="text-xs">Quick Link: Approved</span>
                                        <hr class="horizontal dark my-3">
                                        <h5 class="mb-0"><a href="orders.php">Approved Requests</a></h5>
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
                                        <div
                                            class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                                            <img class="w-10 me-3 mb-0" src="../assets/img/logos/budget.png" alt="logo">
                                            <h6 class="mb-0"><a
                                                    href="https://store.berkeleycountysc.gov/inventory/login-ldap.php">Manage
                                                    Your Assets</a></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div
                                            class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                                            <img class="w-10 me-3 mb-0" src="../assets/img/logos/spent.png" alt="logo">
                                            <h6 class="mb-0"><a href="https://store.berkeleycountysc.gov/">Visit the
                                                    County Store!</a></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <center>Departments are Listed in Alphabetical Order</center>
                            <center>

                                <!-- Department Look up TOOL -->
                                </break>
                                <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal"
                                    data-bs-target="#exampleModalLong">
                                    Department Look up
                                </button>
                                <a href="https://store.berkeleycountysc.gov/storeadmin/pages/invoices.php"
                                    class="btn btn-outline-dark" tabindex="-1" role="button"
                                    aria-disabled="true">Invoices</a>
                            </center>
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
                                <h6 class="mb-0"> <a href="pastReports.php">Data Search</h6></a>
                            </div>
                        </div>
                    </div>
                    <!-- MY BOOTSTRAP CLASS TO HAVE THE SEARCH OPTIONS SEPERATED BY TABS -->

                    <div class="card-body p-3 pb-0">
                        <span class="text-xs">Please enter Employee ID or Department Number to search all
                            Requests</span>
                        <input type="search" class="form-control rounded"
                            placeholder="Enter Employee ID or Department Number" name="empid" id="empid"
                            aria-label="Search" aria-describedby="search-addon" />
                        <center>
                            <button class="btn btn-outline-dark" type="button" onclick="EmpIDSearch()"
                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                aria-controls="offcanvasRight">Search By Employee ID</button>
                            <button class="btn btn-outline-dark" type="button" onclick="DeptNOSearch()"
                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                aria-controls="offcanvasRight">Search By Department</button>
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
                                    <a class="btn btn-lg btn-outline-dark" href="#" role="button" id="dropdownMenuLink"
                                        data-bs-toggle="dropdown" aria-expanded="false">
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

                        <!-- TRIGGER THE FORM -->
                        <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Berkeley County
                                            Department Information</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
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
                                                <td>41517</td>
                                                <td>Building and Code Enforcement</td>
                                            </tr>
                                            <tr>
                                                <td>41210</td>
                                                <td>Clerk of Court</td>
                                            </tr>
                                            <tr>
                                                <td>41209</td>
                                                <td>Clerk of Court DSS</td>
                                            </tr>
                                            <tr>
                                                <td>42103</td>
                                                <td>Communications</td>
                                            </tr>
                                            <tr>
                                                <td>42102</td>
                                                <td>Coroner</td>
                                            </tr>
                                            <tr>
                                                <td>41101</td>
                                                <td>County Council</td>
                                            </tr>
                                            <tr>
                                                <td>41301</td>
                                                <td>County Supervisor</td>
                                            </tr>
                                            <tr>
                                                <td>45201</td>
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
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
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
                            <a href="https://berkeleycountysc.gov/dept/it/" class="font-weight-bold" target="_blank">The
                                Berkeley County IT Team</a>
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

.setLoggedIn {
    position: absolute;
    right: 0;
    margin-right: 20px;
    z-index: 2;
    color: aliceblue;
}
</style>