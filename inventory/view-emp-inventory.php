<?php
session_start();

if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$emp_id = $_GET['emp_id'];

$sql = "SELECT order_details_id, inv_status, inv_UID, emp_inv_assigned_to, dep_inv_assigned_to, product_code, product_price, size, color, ordered_for, ordered_for_department, logo, assigned_to_empName, size_name
FROM uniform_orders.inv_ref
WHERE emp_inv_assigned_to = $emp_id
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<div class='one-div'>"; ?>
    <div class=" nav-container bx--cloud-header">
        <div class="nav-shift bx--cloud-header__wrapper">

        </div>
    </div><?php
            include "nothing-to-see-here.php";
        } else {

            $empSql = "SELECT empName from emp_ref WHERE empNumber = $emp_id";
            $empStmt = $conn->prepare($empSql);
            $empStmt->execute();
            $empResult = $empStmt->get_result();
            if ($empResult->num_rows > 0) {
                while ($empInfo = $empResult->fetch_assoc()) {
                    $emp_name = $empInfo['empName'];
                }
            }


            if ($result->num_rows > 0) {

            ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
            <script type="module" src="https://1.www.s81c.com/common/carbon/web-components/version/v1.21.0/modal.min.js">
            </script>
            <script src="https://unpkg.com/carbon-components@latest/scripts/carbon-components.js"></script>
            <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="./favicons/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="./favicons/favicon-16x16.png">

            <title>Employee Inventory Management Page</title>

            <script>
                // this function creates the modal on demand with params for the specific piece of inventory and then shows the modal. intended to be intiated with onclick of the update button
                //             function makeModal(uid) {
                //                 const html = `
                //     <bx-modal id="${uid}">
                // <bx-modal-header>
                //     <button id="close-modal" value="${uid}">Close</button>
                //     <bx-modal-label>Assign, re-assign, return, or remove inventory</bx-modal-label>
                //     <bx-modal-heading>Inventory Management Modal</bx-modal-heading>
                // </bx-modal-header>
                // <bx-modal-body>
                //     <div id="left">
                //         <h5>Assign Item to an employee</h5>
                //         <form name="assign" id="assign" method="post" action="assign-to-emp-in-db.php">
                //             <label for "emp_pick_list">Employee: </label>
                //             <select name="emp_pick_list" id="emp_pick_list" title="Employee Number" form_id="assign"></select>
                //             <input name="inv_UID" type="hidden" id="uid-holder" />
                //             <button class="btn btn-primary" id="assign-btn" type="submit">Assign to Employee</button>
                //             </form>
                //             </div>
                //             <div id="center">
                //         <h5>Mark Item as returned from employee</h5>
                //         <form name="return" id="return" method="post" action="return-from-emp-in-db.php">
                //             <input name="inv_UID" type="hidden" id="uid-holder2" />
                //             <button class="btn btn-info" id="return-btn" type="submit">Return Item to Inventory</button>
                //             </form>
                //             </div>
                //             <div id="right">
                //                 <h5>Mark item as destroyed</h5>
                //                 <form name="destroy" id="destroy" method="post" action="mark-as-destroyed-in-db.php">
                //             <input name="inv_UID" type="hidden" id="uid-holder3" />
                //             <button class="btn btn-danger" id="return-btn" type="submit">Mark Item as Destroyed</button>
                //         </form>
                //     </div>
                //     </bx-modal-body>
                //     </bx-modal>
                //     `;
                //                 document.getElementById("modal-holder").innerHTML = html;

                //                 const closeButton = document.getElementById('close-modal');
                //                 closeButton.addEventListener('click', () => {
                //                     const modal = document.getElementById(uid);
                //                     modal.remove();
                //                 });
                //             }


                // async function getEmpByDept(uid) {
                //     makeModal(uid);
                //     openModal(uid);
                //     setUIDtoBtn(uid);
                //     const data = await fetch("../getEmpsByDept.php?uid=" + uid)
                //         .then((response) => response.json())
                //         .then(data => {
                //             console.table(data);
                //             var html = "<option>Pick employee</option>";
                //             for (var i = 0; i < data.length; i++) {
                //                 html += "<option value=" + data[i].empNumber + ">";
                //                 html += data[i].empName;
                //                 html += "</option>";
                //             }
                //             document.getElementById("emp_pick_list").innerHTML = html;

                //         })
                // };

                // function setUIDtoBtn(uid) {
                //     // console.log("setUID called with " + uid);
                //     document.getElementById("uid-holder").value = uid;
                //     document.getElementById("uid-holder2").value = uid;
                //     document.getElementById("uid-holder3").value = uid;
                // }
            </script>


        </head>

        <body>

            <div class="one-div">

                <!-- <div class=" nav-container bx--cloud-header">
            <div class="nav-shift bx--cloud-header__wrapper">
                <ul class="bx--cloud-header-list">
                    <li class="bx--cloud-header-list__item"><a class="bx--cloud-header-list__link"
                            href="./index.php">Home</a></li>
                    <li class="bx--cloud-header-list__item"><a class="bx--cloud-header-list__link"
                            href="./inv-dashboard.php">Dashboard</a></li>
                    <li class="bx--cloud-header-list__item">Orders</li>
                    <li class="bx--cloud-header-list__item"><a class="bx--cloud-header-list__link"
                            href="./inv-customers.php">Customers</a></li>
                    <li class="bx--cloud-header-list__item"><a class="bx--cloud-header-list__link"
                            href="./view-inventory.php">Inventory</a></li>
                    <li class="bx--cloud-header-list__item">Returns</li>
                </ul>
            </div>
        </div> -->
                <div class="main">
                    <div class="container">
                        <table>
                            <caption>Inventory Report for <?php echo $emp_name ?></caption>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Item Status</th>
                                    <th>Ordered For</th>
                                    <th>Dep Ordered For</th>
                                    <th>Assigned To</th>
                                    <th>Product Code</th>
                                    <th>Price</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Update Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($invInfo = $result->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td><?php echo $invInfo['order_details_id'] ?></td>
                                        <td><?php echo $invInfo['inv_status'] ?></td>
                                        <td><?php echo $invInfo['ordered_for'] ?></td>
                                        <td>
                                            <?php echo $invInfo['ordered_for_department'] ?>
                                        </td>
                                        <td>
                                            <?php echo $invInfo['assigned_to_empName'] ?>
                                        </td>
                                        <td>
                                            <?php echo $invInfo['product_code'] ?>
                                        </td>
                                        <td>$ <?php echo $invInfo['product_price'] ?>
                                        </td>
                                        <td>
                                            <?php echo $invInfo['size_name'] ?>
                                        </td>
                                        <td>
                                            <?php echo $invInfo['color'] ?>
                                        </td>
                                        <td>
                                            <button class="bx-btn" id="get-data" value="<?php echo $invInfo['inv_UID'] ?>" onclick='getEmpByDept(this.value)'> Update
                                            </button>
                                        </td>
                                    </tr>
                    </div>
        <?php }
                            }
                        } ?>

        </tbody>
        </table>
        <div id="modal-holder"></div>

                </div>
                <div class="available-inventory-holder">
                    <?php

                    $ainvSql = "SELECT order_details_id, inv_status, inv_UID, emp_inv_assigned_to, dep_inv_assigned_to, product_code, product_name, product_price, size, color, ordered_for, ordered_for_department, logo, assigned_to_empName, size_name FROM uniform_orders.inv_ref WHERE inv_status = 'Available'";
                    $ainvStmt = $conn->prepare($ainvSql);
                    $ainvStmt->execute();
                    $ainvResult = $ainvStmt->get_result();
                    if ($ainvResult->num_rows > 0) {
                    ?>


                        <br>
                        <hr>
                        <br>
                        <div class="summary-holder">

                            <table>
                                <caption>Available Inventory</caption>
                                <thead>
                                    <tr>
                                        <!-- <th class='headrow'>Status</th> -->
                                        <th class='headrow'>Code</th>
                                        <th class='headrow'>Name</th>
                                        <th class='headrow'>Price</th>
                                        <th class='headrow'>Size</th>
                                        <th class='headrow'>Color</th>
                                        <th class='headrow'>Assign Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class='datarow'>
                                    <?php
                                    while ($ainvRow = $ainvResult->fetch_assoc()) {
                                        // echo "<td class='datarow'>" . $ainvRow['inv_status'] . "</td>";
                                        echo "<td class='datarow'>" . $ainvRow['product_code'] . "</td>";
                                        echo "<td class='datarow'>" . $ainvRow['product_name'] . "</td>";
                                        echo "<td class='datarow'>$" . $ainvRow['product_price'] . "</td>";
                                        echo "<td class='datarow'>" . $ainvRow['size'] . "</td>";
                                        echo "<td class='datarow'>" . $ainvRow['color'] . "</td>";
                                        echo "<td class='datarow'><button value='" . $ainvRow['inv_UID'] . "' onclick='assignToEmp(this.value," . $emp_id . ")'>Assign</button></td></tr>";
                                    }
                                }

                                    ?>
                        </div>
                </div>
                <!-- </div> -->



                <script>
                    function openModal(uid) {
                        var modal = document.getElementById(uid);
                        console.log(modal);
                        modal.style.visibility = 'visible';
                        modal.style.opacity = '1';

                    }

                    // function closeModal(uid) {
                    //     var modal = document.getElementById(uid);
                    //     var closeButton = document.getElementById('close-modal').addEventListener('click', () => {
                    //         modal.remove();

                    //     })
                    // }

                    function assignToEmp(uid, emp_id) {
                        fetch('./assign-to-emp-in-db-from-emp-view.php?inv_UID=' + uid + '&emp_pick_list=' + emp_id)
                            .then(() => {
                                window.location.reload();
                            })
                        // console.log(uid);
                        // console.log(emp_id);

                    }
                </script>

        </body>

        </html>
        <style>
            body {
                background-color: whitesmoke;
                margin-left: 15px;
                margin-right: 15px;

            }

            .nav-container {
                display: flex;
                position: absolute;
                margin-left: 20px;
                margin-top: 50px;
                max-width: 150px;
                margin-right: 20px;
            }

            .one-div {
                display: grid;
                grid-template-columns: 1fr;
            }

            li {
                text-decoration: none;
                list-style: none;
                font-size: x-large;
                text-transform: uppercase;
            }

            a {
                text-decoration: none;
                color: inherit;
            }

            a:hover {
                color: blue;
            }

            .main {
                /* position: relative; */
                margin-left: 10%;
                margin-top: 45px;
            }

            button {
                border-color: #005677;
                border-width: 1px;
                cursor: pointer;

            }

            thead tr th {
                color: #1e242b;
                padding: 15px;
                text-align: center;
                border-top: 3px solid #005677;
                /* border-left: 1px dashed #cbc8c7; */
                border-left: 1px dashed #005677;
            }

            tbody tr td {
                padding-left: 15px;
                padding-right: 15px;
                padding-top: 5px;
                padding-bottom: 5px;
                text-align: center;
                /* border-top: 1px solid #005677; */
                border-top: 1px solid #789b48;
                border-left: 1px dashed #cbc8c7;
                font-weight: 600;
            }

            thead tr th:last-child {
                /* border-right: 1px dashed #cbc8c7; */
                border-right: 1px dashed #005677;
            }

            tbody tr:last-child {
                border-bottom: 1px solid #789b48;
            }

            tbody tr td:last-child {
                border-right: 1px dashed #cbc8c7;
            }

            tbody tr:nth-child(even) {
                /* background-color: #00567770; */
                background-color: #3057D5;
                color: white;
            }

            code {
                color: #789b48;
            }

            .offcanvas {
                background-color: #cbc8c7;
                position: absolute !important;
                right: 0 !important;
                /* height: 75%; */
                color: #1e242b;
                display: block;
                overflow: scroll;
                height: 50vh !important;
                font-size: larger;
            }

            .offcanvas-start {
                right: 0 !important;
            }



            .btn-close {
                color: red;
                border: 1px solid red;
            }

            h5 {
                /* text-align: center; */
                color: #005677;
                border-bottom: #1e242b 1px solid;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }

            #left {

                margin-top: 20px;
                margin-left: 20px;
            }

            #left label {
                margin-right: 10px;
                font-size: larger;
            }

            #center {
                margin-top: 20px;
                margin-left: 20px;
            }

            #right {
                margin-top: 20px;
                margin-left: 20px;
            }

            .options-container {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
            }

            .button-span {
                width: 100%;
            }

            .btn-warning {
                /* background-color: #d5ca9e; */
                background-color: #f57f43;
                border: 1px solid #ff8800;
            }


            a:hover {
                color: blue;
            }



            .gimme-space {
                margin-bottom: 20px;
                text-align: justify;
            }

            th {
                text-align: center;
                font-weight: bolder;
            }

            /* tr {
        margin-bottom: 15px;
    } */

            .headrow {
                padding-top: 10px;
                margin-bottom: 20px;
                text-align: center;
            }

            .datarow {
                padding: 10px;
                text-align: center;
            }

            table {
                border-right: 2px solid #005677;
                box-shadow: 1px 0px #888888;
                margin-left: 15px;
            }

            caption {
                padding-bottom: 15px;
                font-weight: bold;
                font-size: x-large;
                border-bottom: 1px solid grey;
                box-shadow: 0px 1px #888888;
            }

            .move-over {
                margin-left: 80px;
            }

            #close-modal {
                background-color: #da1e28;
            }
        </style>