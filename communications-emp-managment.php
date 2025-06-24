<?php

session_start();
// init cart class


if (($_SESSION["empNumber"] !== '5453') && ($_SESSION["empNumber"] !== '4707')) {
    echo '<script>alert("YOU SHALL NOT PASS - by order of the Council of Elrond");
            location.href = "products-by-communications.php";
    </script>';
    // header("location: products-by-communications.php");
    exit;
}


require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());


// this is just to get the initial values in the table and set the timestamp
// include_once "copy-comm-emps.php";
// copyEmpNumberToCommEmp($conn);


// this will check for dif and apply changes (in theory)
// include_once "update-comm-emps.php";
// copyEmpNumbers($conn);

$sql = "SELECT empNumber, empName, fy_budget
FROM uniform_orders.comm_emps
-- WHERE deptNumber = 42103 AND seperation_date is null";
$perEmpBudget = 150;

$data = array();
$bdata = array();
$cdata = array();

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['empNumber'];
        $bdata[] = $row['empName'];
        $cdata[] = $row['fy_budget'];
    }
};


$empNumList = array();
$empNameList = array();
$empInfo = array();
$empBudgetList = array();
$allInfo = array();

foreach ($data as $empNumber) {
    $query = "SELECT ifnull(SUM(line_item_total), 0.00) as empTotal
    FROM ord_ref WHERE emp_id = $empNumber";
    $eresult = mysqli_query($conn, $query);

    if (mysqli_num_rows($eresult) > 0) {
        while ($erow = mysqli_fetch_array($eresult)) {
            $empNumList[] = [
                'empTotal' => $erow['empTotal'],
                'empNumber' => $empNumber,
            ];
        }
    }
};



foreach ($data as $key => $value) {
    $empInfo[$key] = array($value, $bdata[$key]);
}
foreach ($bdata as $key => $value) {
    $empInfo[$key] = array($value, $cdata[$key]);
}
foreach ($empNumList as $key => $value) {
    $allInfo[$key] = array($value, $empInfo[$key]);
}
// foreach ($cdata as $key => $value) {
//     $empBudgetList[$key] = array($value, $allInfo[$key]);
// }

// echo "<pre>";
// echo var_dump($allInfo);
// echo "</pre>";
// start test optimized code TODO!!! WORK ON MAKING THIS WORK. I KNOW IT"S CLOSE
// $sql = "SELECT e.empNumber, e.empName, ifnull(SUM(o.line_item_total), 0.00) as empTotal
// FROM uniform_orders.emp_ref e 
// LEFT JOIN ord_ref o ON e.empNumber = o.emp_id 
// WHERE e.deptNumber = 41515 AND e.seperation_date is null 
// GROUP BY e.empNumber";
// $perEmpBudget = 150;

// $data = array();
// $result = $conn->query($sql);
// if ($result->num_rows > 0) {
//     $data = $result->fetch_all(MYSQLI_ASSOC);
// };

// $allInfo = array();

// foreach ($data as $key => $value) {
//     $empNumber = $value['empNumber'];
//     $empTotal = $value['empTotal'];
//     $empInfo = array('empName' => $value['empName']);
//     $budgetRemaining = $perEmpBudget - $empTotal;
//     $allInfo[$key] = array(
//         'empTotal' => $empTotal,
//         'empNumber' => $empNumber,
//         'empInfo' => $empInfo,
//         'budgetRemaining' => $budgetRemaining
//     );
// }

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
    <script type="module" src="https://1.www.s81c.com/common/carbon/web-components/version/v1.21.0/modal.min.js">
    </script>
    <script src="https://unpkg.com/carbon-components@latest/scripts/carbon-components.js"></script>
    <link rel="icon" type="image/x-icon" href="favicons/comm-favicon.ico">

    <title>Just for Sam</title>

    <script>
        (function(document) {
            'use strict';

            var LightTableFilter = (function(Arr) {

                var _input;

                function _onInputEvent(e) {

                    _input = e.target;
                    var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
                    Arr.forEach.call(tables, function(table) {
                        Arr.forEach.call(table.tBodies, function(tbody) {
                            Arr.forEach.call(tbody.rows, _filter);
                        });
                    });
                }

                function _filter(row) {
                    var text = row.textContent.toLowerCase(),
                        val = _input.value.toLowerCase();
                    row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
                }

                return {
                    init: function() {
                        var inputs = document.getElementsByClassName('light-table-filter');
                        Arr.forEach.call(inputs, function(input) {
                            input.oninput = _onInputEvent;
                        });
                    }
                };
            })(Array.prototype);

            document.addEventListener('readystatechange', function() {
                if (document.readyState === 'complete') {
                    LightTableFilter.init();
                }
            });

        })(document);
    </script>
    <script>
        function makeModal(empID) {
            // alert('clicked');
            const html = ` 
        <bx-modal id="${empID}">
            <bx-modal-header>
           <button id="close-modal" value="${empID}">Close</button>
        <bx-modal-label><span class='i'>Increase</span> or <span class='r'>Reduce</span> individual employee allotment</bx-modal-label>
        <bx-modal-heading>Employee Budget Management Modal</bx-modal-heading> 
        </bx-modal-header>
        <bx-modal-body>
        <div id="left">
            <h5>Change Allotment</h5>
            <form name="change-amount" id="change-amount" method="post" action="change-emp-amount.php">
                <label for "new_amount">New Amount: </label>
                <input name='new_amount' placeholder-'Enter new Amount' />
                <input name="emp_id" type="hidden" id="empID-holder" value="${empID}"/>
                <button id="change-btn" type="submit">Set Amount</button>
                </div>
            </form>
        </div>
        </bx-modal-body>
        </bx-modal>
        `;
            document.getElementById("modal-holder").innerHTML = html;
            openModal(empID);
            const closeButton = document.getElementById('close-modal');
            closeButton.addEventListener('click', () => {
                const modal = document.getElementById(empID);
                modal.remove();
            });

        }
    </script>
</head>

<body>
    <div class="nav">
        <ul>
            <li><a href="products-by-communications.php"> Back </a></li>
        </ul>
    </div>
    <div class="header-holder">
        <h2>Communications Employee Page</h2>
        <!-- <h5>Per employee budget is set to: $ <//?php echo $perEmpBudget ?></h5> -->
    </div>
    <input type="search" class="light-table-filter" data-table="order-table" placeholder="Filter" />
    <div class="limiter">

        <div class="container-table100">
            <div class="wrap-table100">
                <table class="order-table">
                    <thead>
                        <tr class="table100-head">
                            <th>Employee Name</th>
                            <th>Employee Number</th>
                            <th>Alotted budget</th>
                            <th>Spending this FY</th>
                            <th>Amount Remaining</th>
                            <th>Edit Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($allInfo as $emp) {
                            echo "<tr>";

                            echo "<td>" . $emp[1][0] . "</td>";
                            echo "<td>" . $emp[0]['empNumber'] . "</td>";
                            // echo "<td>$ " . number_format($perEmpBudget, 2) . "</td>";
                            echo "<td>$ " . number_format($emp[1][1], 2) . "</td>";
                            echo "<td>$ " . number_format($emp[0]['empTotal'], 2) . "</td>";
                            echo "<td>$ " . number_format($emp[1][1] - $emp[0]['empTotal'], 2)   . "</td>";
                            echo "<td><button class='bx-btn' id='get-data' value='" . $emp[0]['empNumber'] . "' onclick='makeModal(this.value)'>Edit</button>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div id="modal-holder"></div>
                <?php

                ?>
            </div>
        </div>
    </div>


    <script>
        function openModal(empID) {
            var modal = document.getElementById(empID);
            console.log(modal);
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            // modal.style.boxShadow = '0px 0px 36px 9px rgba(0, 0, 0, 0.75)';
            modal.style.border = 'solid black 1em';

        }
    </script>

</body>

</html>
<style>
    @font-face {
        font-family: bcFont;
        src: url(./fonts/Gotham-Medium.otf);
    }

    body {
        width: 100vw;
        background-color: whitesmoke;
        margin: 2em;
        font-family: bcFont;
        background: rgb(2, 0, 36);
        background: linear-gradient(207deg, rgba(2, 0, 36, 1) 0%, rgba(209, 204, 7, 1) 0%, rgba(27, 98, 180, 1) 100%);
        background-image: url('flag.jpg');
        background-size: cover;
    }

    h2 {
        padding-top: 1em;
        padding-bottom: 1em;
    }

    .header-holder {
        width: 75%;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: baseline;
    }

    .table-container {
        width: 100%;
        min-height: 100vh;
        display: -webkit-box;
        display: -webkit-flex;
        display: -moz-box;
        display: -ms-flexbox;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        padding: 2em 2em;
    }

    thead tr th {
        color: #1e242b;
        padding: 1em;
        text-align: center;
        border-top: .25em solid #005677;
    }

    .table100-head tr {
        font-size: 18px;
        line-height: 1.5;
        font-weight: unset;
    }

    .table100-head th {
        color: #fff;
    }


    table thead tr {
        height: 60px;
        background: #3b3b6d;
    }

    tbody tr td {
        padding-left: 1em;
        padding-right: 1em;
        padding-top: .5em;
        padding-bottom: .5em;
        text-align: center;
    }

    thead tr th:last-child {

        border-right: 1px dashed #005677;
    }

    tbody tr:last-child {
        border-bottom: 1px solid #789b48;
    }

    tbody tr td:last-child {
        border-right: 1px dashed #cbc8c7;
    }

    tbody tr:nth-child(even) {
        background: #b32134;
        color: #fff;
    }

    tbody tr:nth-child(odd) {
        background: #fff;
    }

    .limiter {
        margin-top: 1em;
    }

    #close-modal {
        background-color: #b32134;
        margin-bottom: 1em;
    }

    .i {
        text-shadow: 0px -3px 3px rgba(17, 225, 55, 0.3);
    }

    .r {
        text-shadow: 0px 3px 3px rgba(236, 11, 6, 0.3);
    }

    #left h5 {
        margin-bottom: 1em;
    }
</style>