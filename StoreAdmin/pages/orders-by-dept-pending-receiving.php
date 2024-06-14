<?php
include('DBConn.php');

session_start();
// if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

//     header("location: pages/sign-in.php");

//     exit;
// }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders by Department</title>

    <script>
        function orderAll(dept_id) {
            // alert("All items have been ordered for : " + dept_id);
            fetch('./change-all-to-ordered.php?dept_id=' + dept_id)
                .then(window.location.reload())
        }

        function formatDate(inputDate) {
            const dateObject = new Date(inputDate);
            const month = String(dateObject.getMonth() + 1).padStart(2, '0');
            const day = String(dateObject.getDate()).padStart(2, '0');
            const year = dateObject.getFullYear();

            return `${month}-${day}-${year}`;
        }

        function formatAsCurrency(value) {
            return value.toFixed(2);
        }

        function alertEmployee(reason, id) {
            alert('You you sure you want to email ${employee} that ' + reason + ' for order ' + id + '?');
        }


        async function getApprovedList() {
            await fetch('./orders-by-dept-pending-receiving-get.php')
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    const departments = {};
                    for (const item of data) {
                        const department = item.department;
                        const departmentName = item.dep_name;
                        if (!departments[department]) {
                            departments[department] = [];
                        }
                        departments[department].push(item);
                        // console.log(item.dep_name);
                    }
                    const tablesContainer = document.getElementById("orders-list");
                    for (const department in departments) {
                        const items = departments[department];
                        let tableHTML =
                            `<table class='styled-table' id='order-table'>
                                        <caption>Orders for ${departmentName} awating fulfillment</caption>
                                        <thead>
                                        <tr>
                                            <th width="5%">Order ID</th>
                                            <th width="2%">Qty</th>
                                            <th width="8%">Item Number</th>
                                            <th width="20%">Product Name</th>
                                            <th width="10%" onclick='sortTable(4)' class='sortable'>Color</th>
                                            <th width="5%" onclick='sortTable(5)' class='sortable'>Size</th>
                                            <th width="7%">Total w/Tax</th>
                                            <th width="10%">Requested For</th>
                                            
                                            <th width="7%">Size Not Avail</th>
                                            <th width="7%">Color Not Avail</th>
                                        </tr></thead><tbody>`;

                        for (const item of items) {
                            tableHTML += `<tr class=${item.status}>
                                            <td>${item.order_id}</td>
                                            <td>${item.quantity}</td>
                                            <td>${item.product_code}</td>
                                            <td>${item.product_name}</td>
                                            <td>${item.color_id}</td>
                                            <td>${item.size_name}</td>
                                            <td>$${formatAsCurrency(item.line_item_total)}</td>
                                            <td>${item.rf_first_name} ${item.rf_last_name}</td>
                                           
                                            
                                            <td><button popovertarget='confirm-size-email-action-${item.order_details_id}' popovertargetaction='show'>Send Email</button>  
                                            <td><button popovertarget='confirm-color-email-action-${item.order_details_id}' popovertargetaction='show'>Send Email</button>  
                                            </tr>
                                            <div id='confirm-size-email-action-${item.order_details_id}' class='confirm-email-popover' popover=manual> 
                                            <button class='close-btn' popovertarget='confirm-size-email-action-${item.order_details_id}' popovertargetaction='hide'>
                                            <span aria-hidden='true'> ❌ </span>
                                            <span class='sr-only'>Close</span>
                                            </button>
                                            <br>
                                            <p>Confirm you want to email ${item.rf_first_name} that ${item.size_name} is not available</p>
                                            <br>
                                            <button onclick='updateSize(this.value)' value=${item.order_details_id} id=${item.order_details_id}>Send Email &#9993;</button>
                                            </div>
                                            <div id='confirm-color-email-action-${item.order_details_id}' class='confirm-email-popover' popover=manual> 
                                            <button class='close-btn' popovertarget='confirm-color-email-action-${item.order_details_id}' popovertargetaction='hide'>
                                            <span aria-hidden='true'> ❌ </span>
                                            <span class='sr-only'>Close</span>
                                            </button>
                                            <br>
                                            <p>Confirm you want to email ${item.rf_first_name} that ${item.color_id} is not available</p>
                                            <br>
                                            <button onclick='updateColor(this.value)' value=${item.order_details_id} id=${item.order_details_id}>Send Email &#9993;</button>
                                            </div>
                                            
                                            `;
                        }
                        tableHTML += '</tbody></table>';
                        tablesContainer.innerHTML += tableHTML;
                        document.getElementById("loader").style.display = "none";
                    }

                })
        }
        getApprovedList();
        document.addEventListener("DOMContentLOaded", function() {
            document.getElementById("loader").style.display = "block";
        })
        async function updateSize(id) {
            await fetch('./add-comment-size.php?order_details_id=' + id)
            var buttonPressed = document.getElementById(id);
            var xHolder = buttonPressed.parentElement.previousElementSibling
                .then(alert('email has been sent'))
            // console.log(xHolder);
            // if (xHolder && !xHolder.classList.contains('marked')) {

            //     xHolder.innerHTML = '&#10062;';
            //     xHolder.classList.add('marked');
            // }
        }
        async function updateColor(id) {
            await fetch('./add-comment-color.php?order_details_id=' + id)
            var buttonPressed = document.getElementById(id);
            var xHolder = buttonPressed.parentElement.previousElementSibling
                .then(alert('email has been sent'))
            // console.log(xHolder);
            // if (xHolder && !xHolder.classList.contains('marked')) {

            //     xHolder.innerHTML = '&#10062;';
            //     xHolder.classList.add('marked');
            // }
        }
    </script>
</head>

<body>
    <?php include "topNav.php" ?>
    <div id="loader" class="loader"></div>
    <div class="main-container">
        <div class="left">
            <div id="orders-list"></div>
        </div>
        <!-- <div class="right">
            <div id="past-reports"></div>
        </div> -->
    </div>
    <!-- <div id="confirm-size-email-action" class="confirm-size-email-action" popover=manual>
        <p>Confirm email Size not available</p>
    </div>
    <div id="confirm-color-email-action" class="confirm-color-email-action" popover=manual>
        <p>Confirm email Color not available</p>
    </div> -->
    <script>
        function sortTable(column) {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById('order-table');
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < rows.length - 1; i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName('td')[column];
                    y = rows[i + 1].getElementsByTagName('td')[column];

                    // Check if the two rows should switch place based on the selected column's content
                    if (isNaN(x.innerHTML)) {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else {
                        if (parseInt(x.innerHTML) > parseInt(y.innerHTML)) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }

                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }
    </script>
    <script>
        // function markedWaiting() {
        //     console.log('waiting...... ');
        //     document.addEventListener("DOMContentLoaded", function() {
        //         const waitingCells = document.querySelectorAll(".Waiting");
        //         console.log(waitingCells);
        //         waitingCells.forEach(cell => {
        //             cell.innerHTML = "&#10062;" + cell.innerHTML;
        //         });
        //     });
        // }
        // markedWaiting();
    </script>
</body>

</html>
<style>
    html {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        /* background-color: #ffe53b; */
        background-color: #dcd9d4;
        background-image: linear-gradient(to bottom,
                rgba(255, 255, 255, 0.5) 0%,
                rgba(0, 0, 0, 0.5) 100%),
            radial-gradient(at 50% 0%,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(0, 0, 0, 0.5) 50%);
        background-blend-mode: soft-light, screen;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    }

    .body {
        margin: 20px;
        display: flex;
        justify-content: center;
    }

    h3 {
        text-align: center;
    }

    .body {
        display: flex;
        justify-content: center;
    }

    .header-holder {
        display: flex;
        justify-content: space-around;
    }

    .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.9em;
        /* font-family: sans-serif; */
        width: 98%;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        background-color: #0F9D58;
        color: #ffffff;
        /* text-align: center; */
    }

    .styled-table thead tr th {
        background-color: #0F9D58;
        color: #ffffff;
        text-align: left;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    /* .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    } */

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #009879;
    }

    a:link {
        text-decoration: none;
        color: black;
    }

    a:link:hover {
        background-color: #ffe53b;
    }

    .sortable {
        cursor: pointer;
    }

    caption {
        font-size: x-large;
        font-weight: bold;
        text-align: left;
        padding: 10px;
        margin-bottom: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        background-color: #4285F4;
        position: relative;
    }

    .order-button {
        background-color: #F4B400;
        border: 1px solid black;
        border-radius: 5px;
        position: absolute;
        right: 0;
        margin-right: 45px;
    }

    .main-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        /* margin: 20px; */
        padding: 20px;
    }

    .left {
        width: 95vw;
    }

    /* .right {
        width: 20vw;
    } */

    #reportsTable th,
    #reportsTable td {
        padding-top: 5px;
        padding-right: 7px;
        padding-bottom: 5px;
        padding-left: 7px;
    }

    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
        margin: 100px auto;
    }

    .Waiting {
        background-color: lightpink;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>