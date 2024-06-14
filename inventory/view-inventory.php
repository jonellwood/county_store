<?php
session_start();

if (!isset($_SESSION["im_loggedin"]) || $_SESSION["im_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}

require_once '../config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// This SQL query is used to retrieve data from the "inv_ref" table with specific columns
$sql = "SELECT order_details_id, inv_status, inv_UID, emp_inv_assigned_to, dep_inv_assigned_to, product_code, product_price, size, color, ordered_for, ordered_for_department, logo, assigned_to_empName, size_name
FROM uniform_orders.inv_ref";

$stmt = $conn->prepare($sql);  // preaparing the sql statement by binding it to the connection
$stmt->execute();  // executing the prepared statement
$result = $stmt->get_result(); // getting all rows obtained from the execution of the statement in result varible.

if ($result->num_rows > 0) {

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Inventory Management Interface</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://unpkg.com/carbon-components@latest/css/carbon-components.css">
        <script type="module" src="https://1.www.s81c.com/common/carbon/web-components/version/v1.21.0/modal.min.js">
        </script>


        <script>
            // this function creates the modal on demand with params for the specific piece of inventory and then shows the modal. intended to be intiated with onclick of the update button
            function makeModal(uid) {
                const html = `
        <bx-modal id="${uid}">
    <bx-modal-header>
        <button id="close-modal" value="${uid}">Close</button>
        <bx-modal-label>Assign, re-assign, return, or remove inventory</bx-modal-label>
        <bx-modal-heading>Inventory Management Modal</bx-modal-heading>
    </bx-modal-header>
    <bx-modal-body>
        <div id="left">
            <h5>Assign Item to an employee</h5>
            <form name="assign" id="assign" method="post" action="assign-to-emp-in-db.php">
                <label for "emp_pick_list">Employee: </label>
                <select name="emp_pick_list" id="emp_pick_list" title="Employee Number" form_id="assign"></select>
                <input name="inv_UID" type="hidden" id="uid-holder" />
                <button class="btn btn-primary" id="assign-btn" type="submit">Assign to Employee</button>
                </form>
                </div>
                <div id="center">
            <h5>Mark Item as returned from employee</h5>
            <form name="return" id="return" method="post" action="return-from-emp-in-db.php">
                <input name="inv_UID" type="hidden" id="uid-holder2" />
                <button class="btn btn-info" id="return-btn" type="submit">Return Item to Inventory</button>
                </form>
                </div>
                <div id="right">
                    <h5>Mark item as destroyed</h5>
                    <form name="destroy" id="destroy" method="post" action="mark-as-destroyed-in-db.php">
                <input name="inv_UID" type="hidden" id="uid-holder3" />
                <button class="btn btn-danger" id="return-btn" type="submit">Mark Item as Destroyed</button>
            </form>
        </div>
        </bx-modal-body>
        </bx-modal>
        `;
                // Get the element of the close button from the current active modal        
                document.getElementById("modal-holder").innerHTML = html;

                // Add an event listener to listen to the click event on the close button
                // Upon clicking, retrieve the element containing the passed unique id and remove it from the DOM
                const closeButton = document.getElementById('close-modal');
                closeButton.addEventListener('click', () => {
                    const modal = document.getElementById(uid);
                    modal.remove();
                });
            }


            // This async function called getEmpByDept that takes a unique ID (uid) and fetches data related to it.

            // The first line sets the given uid to a button in the HTML DOM.
            // Then, a fetch query is sent to the server with the uid as an argument.
            // Next, we add the response to the variable data in JSON format.
            // To create an HTML option list, we assign the variable html an initial value "<option>Pick employee</option>".
            // The for loop iterates over the response from the server, to generate each of the select options.
            // Finally, we use the innerHTML property to add the newly created HTML options to the DOM.

            async function getEmpByDept(uid) {
                makeModal(uid);
                openModal(uid);
                setUIDtoBtn(uid);
                const data = await fetch("../getEmpsByDept.php?uid=" + uid)
                    .then((response) => response.json())
                    .then(data => {
                        // console.table(data);
                        var html = "<option>Pick employee</option>";
                        for (var i = 0; i < data.length; i++) {
                            html += "<option value=" + data[i].empNumber + ">";
                            html += data[i].empName;
                            html += "</option>";
                        }
                        document.getElementById("emp_pick_list").innerHTML = html;

                    })
            };

            // This function takes in one parameter, uid, which is a unique identifier for an item or user. The function sets the value of three HTML elements to the same uid parameter that was passed in.

            // Set the value of element with id uid-holder equal to uid.
            // Set the value of element with id uid-holder2 equal to uid.
            // Set the value of element with id uid-holder3 equal to uid.
            function setUIDtoBtn(uid) {
                // console.log("setUID called with " + uid);
                document.getElementById("uid-holder").value = uid;
                document.getElementById("uid-holder2").value = uid;
                document.getElementById("uid-holder3").value = uid;


            }
        </script>

    </head>

    <body>

        <div class="one-div">
            <?php include "inv-nav.php" ?>
            <div class="main">
                <div class="container">

                    <table>
                        <caption>All inventory for your department</caption>
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
                                <!-- <th>Logo</th> -->
                                <th>Update Item</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($invInfo = $result->fetch_assoc()) {
                                // $price = number_format($invInfo['product_price'], 2);
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
                                        <button class="bx-btn" id="get-data<?php echo $invInfo['order_details_id'] ?> " value="<?php echo $invInfo['inv_UID'] ?>" onclick='getEmpByDept(this.value)'> Update
                                        </button>
                                    </td>
                                </tr>

                </div>

        <?php }
                        } ?>
        </tbody>
        </table>


        <div id="modal-holder">

        </div>


            </div>
        </div>




        <script type="text/javascript">
            function openModal(uid) {
                var modal = document.getElementById(uid);
                // console.log(modal);
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';

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
            margin-left: 15%;
            margin-top: 45px;
        }

        button {
            border-color: #005677;
            border-width: 1px;

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

        #close-modal {
            background-color: #da1e28;
            margin-bottom: 1em;
        }
    </style>