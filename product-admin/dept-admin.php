<?php

session_start();
if (!isset($_SESSION["pa_loggedin"]) || $_SESSION["pa_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * from dep_ref ORDER by dep_head IS NULL, dep_assist IS NULL, dep_asset_mgr IS NULL";

$deps = array(); 
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $deps[] = $row;
    }
}
// var_dump($deps);

$eSql = "SELECT empNumber, empName from emp_ref WHERE seperation_date is NULL order by empName";
$emps = array();
$eStmt = $conn->prepare($eSql);
$eStmt->execute();
$eRes = $eStmt->get_result();
if ($eRes->num_rows > 0) {
    while ($eRow = $eRes->fetch_assoc()) {
        $emps[] = $eRow;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css">
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="icon" href="./favicons/favicon.ico">

    <title>Dept Admin Page</title>

    <script>
    // START Dept Head Change Section
    // function to close selected modal when cancel button is used
    function hideDHChange() {
        document.getElementById('dh_change').classList.add('hidden');
    }

    // function to show selected modal and set the dep_num as a value to be passed as param to db update script
    function showDHChange(dep) {
        var confirm_popup = document.getElementById('dh_change')
        // console.log(confirm_popup);
        confirm_popup.classList.remove('hidden');
        confirm_popup.setAttribute('data-dep', dep);
        // console.log('confirm_popup data-dep: ', confirm_popup.getAttribute('data-dep'));
    }

    function setDHValue(emp) {
        // console.log('setting DH to :', emp);
        var confirm_popup = document.getElementById('dh_change')
        confirm_popup.setAttribute('data-emp', emp);
        // console.log('confirm pop up second time ', confirm_popup);
    }

    function setDHValueToNull() {
        var confirm_popup = document.getElementById('dh_change')
        confirm_popup.setAttribute('data-emp', null);
        changeDeptHead();
    }

    function changeDeptHead() {
        showLoading();
        var popup = document.getElementById('dh_change');
        var dep = popup.getAttribute('data-dep')
        var emp = popup.getAttribute('data-emp')
        fetch('./change-dept-head.php?dep=' + dep + '&emp=' + emp)
            // .then(logDeptHeadChanged())
            .then(location.reload())
    }
    // START Dept Assistant Change Section
    // function to close selected modal when cancel button is used
    function hideDAChange() {
        document.getElementById('da_change').classList.add('hidden');
    }
    // function to show selected modal and set the dep_num as a value to be passed as param to db update script
    function showDAChange(dep) {
        var confirm_popup = document.getElementById('da_change')
        confirm_popup.classList.remove('hidden');
        confirm_popup.setAttribute('data-dep', dep);
    }

    function setDAValue(emp) {
        var confirm_popup = document.getElementById('da_change')
        confirm_popup.setAttribute('data-emp', emp);
    }

    function setDAValueToNull() {
        var confirm_popup = document.getElementById('da_change')
        confirm_popup.setAttribute('data-emp', null);
        changeDeptAssistant();
    }

    function changeDeptAssistant() {
        showLoading();
        var popup = document.getElementById('da_change');
        var dep = popup.getAttribute('data-dep')
        var emp = popup.getAttribute('data-emp')
        fetch('./change-dept-assist.php?dep=' + dep + '&emp=' + emp)
            .then(location.reload())
    }
    // START Dept Asset Manager Change Section
    // function to close selected modal when cancel button is used
    function hideAMChange() {
        document.getElementById('am_change').classList.add('hidden');
    }
    // function to show selected modal and set the dep_num as a value to be passed as param to db update script
    function showAMChange(dep) {
        var confirm_popup = document.getElementById('am_change')
        confirm_popup.classList.remove('hidden');
        confirm_popup.setAttribute('data-dep', dep);
    }

    function setAMValue(emp) {
        var confirm_popup = document.getElementById('am_change')
        confirm_popup.setAttribute('data-emp', emp);
    }

    function setAMValueToNull() {
        var confirm_popup = document.getElementById('am_change')
        confirm_popup.setAttribute('data-emp', null);
        changeDeptAssetMgr();
    }

    function changeDeptAssetMgr() {
        showLoading();
        var popup = document.getElementById('am_change');
        var dep = popup.getAttribute('data-dep')
        var emp = popup.getAttribute('data-emp')
        fetch('./change-asset-mgr.php?dep=' + dep + '&emp=' + emp)
            .then(location.reload())
    }

    function logDeptHeadChanged() {
        console.log('Department Head Changed');
    }

    function showLoading() {
        document.getElementById('loading').classList.remove('hidden');
    }
    </script>



</head>

<body>
    <?php include "nav.php" ?>
    <div class="header-holder">
        <h3>Department Admin Page</h3>
        <!-- <a href='index.php'><button>Back</button></a> -->
    </div>
    <div class="body">


        <table class="styled-table">
            <thead>
                <tr>
                    <th>Dep Number</th>
                    <th>Dep Name</th>
                    <th>Dep Head</th>
                    <th>DH Change</th>
                    <th>Dep Asst </th>
                    <th>DA Change</th>
                    <th>Dep Asset Mgr</th>
                    <th>AM Change</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($deps as $dep) {
                    echo "<tr>";
                    echo "<td>" . $dep['dep_num'] . "</td>";
                    echo "<td>" . $dep['dep_name'] . "</td>";
                    echo "<td>" . $dep['dep_head_empName'] . "</td>";
                    // echo "<td>" . $dep['dep_head_email'] . "</td>";
                    echo "<td><button id='dh_data_button' value='" . $dep['dep_num'] . "' onclick='showDHChange(this.value)'><i class=icon-arrow-left-circle m-auto text-primary'></i>Change</button>";
                    echo "<td>" . $dep['dep_assist_empName'] . "</td>";
                    // echo "<td>" . $dep['dep_assist_email'] . "</td>";
                    echo "<td><button value='" . $dep['dep_num'] . "' onclick='showDAChange(this.value)'><i class=icon-arrow-left-circle m-auto text-primary'></i>Change</button>";
                    echo "<td>" . $dep['dep_asset_mgr_empName'] . "</td>";
                    // echo "<td>" . $dep['dep_asset_mgr_email'] . "</td>";
                    echo "<td><button value='" . $dep['dep_num'] . "' onclick='showAMChange(this.value)'><i class=icon-arrow-left-circle m-auto text-primary'></i>Change</button>";
                    echo "</tr>";
                };

                ?>

            </tbody>
        </table>
        <div class="dh_confirm hidden" id="dh_change">
            <h1 class="dh_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department Head</mark></label>
            <select onchange='setDHValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "' >" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='dh_button' id="dh_1" onclick="changeDeptHead()">Assign</button>
                <button type='button' class='dh_button' id="hide" onclick="hideDHChange()">Cancel</button>
                <button type='button' class='dh_button' id="reset" onclick="setDHValueToNull()">Set to Blank</button>
            </div>
        </div>
        <div class="da_confirm hidden" id="da_change">
            <h1 class="da_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department Assistant</mark></label>
            <select onchange='setDAValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='da_button' id="da_1" onclick="changeDeptAssistant()">Assign</button>
                <button type='button' class='da_button' id="hide" onclick="hideDAChange()">Cancel</button>
                <button type='button' class='da_button' id="reset" onclick="setDAValueToNull()">Set to Blank</button>
            </div>
        </div>
        <div class="am_confirm hidden" id="am_change">
            <h1 class="am_h1">Change Assignment</h1>

            <label for="empList">Select the employee you wish to (re)assign to <mark>Department Asset
                    Manager</mark></label>
            <select onchange='setAMValue(this.value)'>
                <?php
                foreach ($emps as $emp) {
                    echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>";
                }
                ?>
            </select>

            <div class="button-holder">
                <button type='button' class='am_button' id="am_1" onclick="changeDeptAssetMgr()">Assign</button>
                <button type='button' class='am_button' id="hide" onclick="hideAMChange()">Cancel</button>
                <button type='button' class='am_button' id="reset" onclick="setAMValueToNull()">Set to Blank</button>
            </div>
        </div>
        <div class="loading hidden" id="loading">
            <h1>Loading...</h1>
        </div>
    </div>
</body>

</html>
<style>
body {
    margin: 0px;
    padding: 40px;
    /* color: whitesmoke; */
    /* background-color: #0d0e0e; */
    /* background-image: linear-gradient(0deg, #0d0e0e 27%, #5e5e6a 100%); */
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
}

button {
    font-size: smaller;
}

.dh_confirm,
.da_confirm,
.am_confirm {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    /* margin-top: 150px; */
    border: 1px solid #772953;
    border-top: 10px solid #E95420;
    border-radius: 10px;
    /* bottom: 0; */
    margin-bottom: 250px;
    /* margin-left: 35%; */
    /* right: 0; */
    /* margin-right: 35%; */
    z-index: 2;
    background-color: #f0f0f0;
    max-height: 600px;
    padding: 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    align-content: center;
    -webkit-box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
    -moz-box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
    box-shadow: 0px 0px 30px 2px rgba(65, 25, 52, 0.45);
}

.button-holder {
    display: inline-flex;
    gap: 20px;
    margin: auto;
}

.dh_button,
.da_button,
.am_button {
    font-size: medium;
    font-weight: bolder;
    margin-top: 20px;
    padding: 5px;
    border-width: 2px;
}


.dh_button:hover,
.da_button:hover,
.am_button:hover {
    transform: rotate(-2deg);
}

#dh_1,
#da_1,
#am_1 {
    color: #0F9D58;
    border-color: #0F9D58;
}

#hide {
    color: #DB4437;
    border-color: #E95420;
}

.dh_h1,
.da_h1,
.am_h1 {
    color: #E95420;
    font-weight: bolder;
    text-transform: uppercase;
}

.hidden {
    display: none;
}

.loading {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: url("https://png.pngtree.com/thumb_back/fh260/background/20200714/pngtree-loading-technology-screen-background-image_353489.jpg")
}
</style>