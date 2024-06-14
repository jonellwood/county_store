<?php
session_start();

if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


$dep_nums = $_SESSION['dep_nums'];
$dep_inv_list = array(); // <- really just a list of employees from the departments array. Changed where I pulled it from but did not update the name of the array becuase....... //
$av_inv_list = array();

// this has a poorly named array it pushes into - but the data comes from emp_ref view
foreach ($dep_nums as $dep) {
    $sql = "SELECT empNumber as emp_inv_assigned_to, empName as assigned_to_empName
    FROM emp_ref
    WHERE deptNumber = $dep AND seperation_date is NULL
    ;";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($dep_inv_list, $row);
        }
    }
}
$emp_count = count($dep_inv_list);

foreach ($dep_nums as $dep) {
    $av_sql = "SELECT product_code, product_name, color, size_name, logo, inv_received, inv_UID from inv_ref WHERE dep_num = $dep AND inv_status = 'Available' GROUP BY product_code, color, size_name, logo;";
    $av_stmt = $conn->prepare($av_sql);
    $av_stmt->execute();
    $av_result = $av_stmt->get_result();
    if ($av_result->num_rows > 0) {
        while ($av_row = $av_result->fetch_assoc()) {
            array_push($av_inv_list, $av_row);
        }
    }
}

$avail_count = count($av_inv_list);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Inventory Management Interface</title>
    <meta charset="utf-8">
    </script>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon-16x16.png">
    <link rel="stylesheet" href="dept-inv.css">



    <!-- This function takes an employee number from a click event and retuns a table with inventory items assigned to them.  -->
    <script>
        async function getEmpInv(emp) {
            // console.log('Fetching inventory info...');
            openDefault(); // figure out why this is not working like I expect
            const data = await fetch("get-emp-inventory.php?emp_id=" + emp)
                .then((response) => response.json())
                .then(data => {
                    // console.log(data);

                    var html = "<table style='width:100%'><thead><tr>";
                    html +=
                        "<th style='width:22%'>Product Name</th><th style='width:8%'>Image</th><th style='width:10%'>Color</th><th style='width:10%'>Size</th><th style='width:10%'>Logo</th><th style='width:10%'>Price</th><th style='width:10%'>Received On</th><th style='width:12.5%'>Action</th></tr></thead>";
                    html += "<tbody>";
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].inv_received == 'kidding') {
                            justDate = 'kidding';
                        } else {
                            var timeStamp = data[i].inv_received
                            const date = new Date(timeStamp);
                            justDate = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
                        }
                        // justDate = date.getDate() + "/" + (date.getMonth() + 1) + "/" + date.getFullYear;
                        // console.log('The date is now: ' + justDate);
                        html += "<tr class='ass_inv_row'><td>" + data[i].product_name + "</td>";
                        // html += "<td>" + data[i].product_code + "</td>";
                        html += "<td><img class='prod_img' src='../product-images/" + data[i].product_code +
                            "_prod.jpg'></td>";
                        html += "<td>" + data[i].color + "</td>";
                        html += "<td>" + data[i].size_name + "</td>";
                        html += "<td><img src='../" + data[i].logo + "'></td>";
                        html += "<td>$" + data[i].product_price + "</td>";
                        html += "<td>" + justDate + "</td>";
                        html +=
                            "<td><button class='action-btn' type='button' onclick='setUIDtoBtn(this.value)' value='" +
                            data[i]
                            .inv_UID +
                            "'>Return to Inv<img src='./img/startrek.svg' alt='logo' class='return-img'></button><button class='action-btn' type='button' onclick='setUIDtoDumpsterBtn(this.value)' value='" +
                            data[i].inv_UID +
                            "'>Mark Destroyed<img src='./img/dumpsterfire.svg' alt='logo' class='return-img'></button></td>"
                        html += "</tr>";
                    }
                    html += "</tbody></table>";
                    html +=
                        "<p class='tiny-text'>Product image is placeholder only and may not...(<i>actually almost certainly</i>) does not reflect the actual item 100% accurately.<p>";
                    document.getElementById('emp_inv_holder').innerHTML = html;

                })
        }
    </script>

    <script>
        function markActive() {
            makeFirstEmpActive();
            var empList = document.getElementById('empInvTable');
            var tds = empList.getElementsByClassName('td');
            for (var i = 0; i < tds.length; i++) {
                tds[i].addEventListener("click", function() {
                    var current = document.getElementsByClassName("active");
                    if (current.length > 0) {
                        current[0].className = current[0].className.replace(" active", "");
                    }
                    this.className += " active";
                })
            }
        }
        // return item to inventory from an employee function
        async function returnProductToInv(uid) {
            await fetch('../storeadmin/pages/log-inventory-item-available.php?inv_UID=' + uid)
                .then(await fetch('./return-from-emp-in-db.php?inv_UID=' + uid))
                // .then(logInvMadeAvailable(uid))
                .then(location.reload())

        }
        // assign item to employee from available inventory. UID is passed in from button, emp_id is grabbed at runtime
        async function assignProductToEmp(uid) {
            var emp = document.getElementById('emps').value;
            await fetch('./assign-to-emp-in-db.php?emp_id=' + emp + '&inv_UID=' + uid)
                // .then(console.log('assigning ' + uid + ' to ' + emp))
                .then(logInvAssignedToEmp(uid, emp))
                .then(location.reload())
        }
        // mark product as destroyed in the database
        async function markProductAsDestroyed(uid) {

            await fetch('./mark-as-destroyed-in-db.php?inv_UID=' + uid)
                .then(logInvMarkedDestroyed(uid))
                .then(location.reload())
        }

        // function to give the confirm button the right UID to then call the related function above if the user clicks to do so.
        function setUIDtoBtn(uid) {
            var c_modal = document.getElementById('confirm_h');
            c_modal.classList.remove('hidden');
            document.getElementById('cb_1').value = uid;
        }

        function setUIDtoAssBtn(uid) {
            var a_modal = document.getElementById('assign_h');
            a_modal.classList.remove('hidden');
            document.getElementById('ab_1').value = uid;
        }

        function setUIDtoDumpsterBtn(uid) {
            var d_modal = document.getElementById('destroy_h');
            d_modal.classList.remove('hidden');
            document.getElementById('db_1').value = uid;
        }
        // I mean.... these are pretty well named. Tell ya what - try and figure out what they do and then if you can't ... let me know and I will comment each one m'kay?
        function hideConfirm() {
            document.getElementById('confirm_h').classList.add('hidden');
        }

        function hideAssign() {
            document.getElementById('assign_h').classList.add('hidden');
        }

        function hideDestroy() {
            document.getElementById('destroy_h').classList.add('hidden');
        }

        function logInvMadeAvailable(uid) {
            fetch("../storeadmin/pages/log-inventory-item-available.php?inv_UID=" + uid)
        }

        function logInvAssignedToEmp(uid, emp) {
            fetch("../storeadmin/pages/log-inventory-item-assigned.php?inv_UID=" + uid + "&emp_id=" + emp)
        }

        function logInvMarkedDestroyed(uid) {
            fetch("../storeadmin/pages/log-inventory-item-destroyed.php?inv_UID=" + uid)
        }
        // function to select the first employee in the list and load thier inventory and dispaly it on page load.
        function makeFirstEmpActive() {
            var emp_tbody = document.getElementById('emp_tbody').firstChild.nextSibling.firstChild;
            emp_tbody.click();
            emp_tbody.classList.add('active');
        }
    </script>

</head>



<body onload="markActive()">
    <div class="h1">
        <h1> Employee Inventory Management </h1><a href="index.php"><button class='back-button'> Back to Dashboard
            </button></a>
    </div>
    <div class="tab" id="tab">
        <button class="tablinks reactive" onclick="openTab(event, 'emp_inv_holder')">Employee
            Inventory </button>
        <button class="tablinks" onclick="openTab(event, 'avail_inv_holder')">Available
            Inventory (<?php echo $avail_count ?>)</button>

    </div>
    <div class="one-div">
        <div class='side-nav'>
            <table id='empInvTable' style="width:100%">
                <thead>
                    <tr>
                        <th>Employees (<?php echo $emp_count ?>)</th>
                    </tr>
                </thead>
                <tbody id='emp_tbody'>
                    <?php
                    foreach ($dep_inv_list as $emp) {
                        echo "<tr class='emp_row'><td class='td' onclick='getEmpInv(" . $emp['emp_inv_assigned_to'] . ")'>" . $emp['assigned_to_empName']
                            . "</td></tr>";
                    };
                    echo "</tbody></table>";
                    ?>
        </div>
        <div class="confirm-holder hidden" id="confirm_h">
            <div id="confirm">
                <h1 class="c_h1">Confirmation</h1>

                <h3>Are you sure you want to remove this item from this employee and place it back in inventory as
                    available?</h3>

                <button type='button' class='c_button' id="cb_1" onclick="returnProductToInv(this.value)">Confirm</button>
                <button type='button' class='c_button' id="cb_2" onclick="hideConfirm()">Cancel</button>
            </div>
        </div>

        <div class="destroy-holder hidden" id="destroy_h">
            <div id="destroy">
                <h1 class="d_h1">Confirmation</h1>

                <h3>Are you sure you want to mark this item as destroyed?</h3>
                <div class="button-holder">
                    <button type='button' class='d_button' id="db_1" onclick="markProductAsDestroyed(this.value)">Burn
                        It</button>
                    <button type='button' class='d_button' id="db_2" onclick="hideDestroy()">Nope</button>
                </div>
            </div>
        </div>

        <div class="emp-assign-holder hidden" id="assign_h">
            <div id="assign">
                <h1 class="a_h1">Product Assignment</h1>
                <!-- <h5>Select the employee you want to assign this product too</h5> -->
                <label for="emps">Select the employee to whom you wish to assign this product</label>
                <select name="emps" id="emps">
                    <?php
                    foreach ($dep_inv_list as $emp) {
                        echo "<option value=" . $emp['emp_inv_assigned_to'] . ">" . $emp['assigned_to_empName'] . "</input>";
                    }
                    ?>
                </select>

                <div class="button-holder">
                    <button type='button' class='c_button' id="ab_1" onclick="assignProductToEmp(this.value)">Assign</button>
                    <button type='button' class='c_button' id="ab_2" onclick="hideAssign()">Get outta here</button>
                </div>

            </div>
        </div>

        <div id="emp_inv_holder" class="tabcontent"></div>
        <div id="avail_inv_holder" class="tabcontent">
            <?php

            echo "<table><thead><tr><th>Product Code</th><th>Name</th><th>Color</th><th>Size</th><th>Logo</><th>Received</th><th>Assign</th></tr></thead>";
            foreach ($av_inv_list as $av_inv) {
                echo "<tr>";
                echo "<td>" . $av_inv['product_name'] . "</td>";
                echo "<td><img src='../product-images/" . $av_inv['product_code'] . "_prod.jpg'></td>";
                echo "<td>" . $av_inv['color'] . "</td>";
                echo "<td>" . $av_inv['size_name'] . "</td>";
                echo "<td><img src='../" . $av_inv['logo'] . "'></td>";
                echo "<td>" . $av_inv['inv_received'] . "</td>";
                echo "<td><button onclick='setUIDtoAssBtn(this.value)' value='" . $av_inv['inv_UID'] . "'>Assign Product<img class='return-img' src='./img/foodpanda.svg' alt='foodpanda'></button></td>";
                echo "</tr>";
            }
            ?>
        </div>

        <script>
            function openTab(evt, cityName) {
                // Declare all variables
                var i, tabcontent, tablinks;

                // Get all elements with class="tabcontent" and hide them
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }

                // Get all elements with class="tablinks" and remove the class "active"
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" reactive", "");
                }

                // Show the current tab, and add an "active" class to the button that opened the tab
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " reactive";
            }

            function openDefault() {
                var gtt = document.getElementById("tab").firstChild.nextSibling;
                document.getElementById("emp_inv_holder").click();
                console.log("Does that make me crazy, Possibly");
                gtt.click(); // goes back to the emp inventory tab when click an employee name
            }
            openDefault();
        </script>

</body>

</html>