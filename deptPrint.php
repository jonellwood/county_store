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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Reports</title>
    <script>
        // This function fires a request to the PHP script that marks all items in a department as `Ordered`
        async function markOrdered(deptNum) {
            await fetch('./change-all-to-ordered.php?dept_id=' + deptNum)
        }
    </script>
</head>

<?php
$DEPTID = filter_input(INPUT_GET, 'demptid', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
if ($ROLE === "Administrator") {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
    o.quantity, o.product_name, e.email, e.deptName, e.deptNumber, e.empNumber, o.line_item_total, o.order_id, o.order_details_id, o.customer_id, o.created, o.logo, o.product_code,
    COUNT(o.order_details_id) as TotalDeptReqs, SUM(o.line_item_total) as TotalDeptSum
    FROM uniform_orders.ord_ref o
    join emp_ref e on o.emp_id = e.empNumber
    JOIN dep_ref dr on o.department = dr.dep_num
    WHERE status = 'Approved' and e.deptNumber = $DEPTID";
} else {
    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created,
o.quantity, o.product_name, e.email, e.deptName, e.empNumber, o.line_item_total, o.order_id, o.order_details_id, o.customer_id, o.created, 
SUM(line_item_total) as sumapp, COUNT(order_details_id) as appcount
FROM uniform_orders.ord_ref o
join emp_ref e on o.emp_id = e.empNumber
JOIN dep_ref dr on o.department = dr.dep_num
WHERE (status = 'Approved' AND dr.dep_head = $DEPT)";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $APPROVEDREQ = $row["TotalDeptReqs"];
        $TotalWithTaxes = $row["TotalDeptSum"];
        $TWTs = number_format($TotalWithTaxes, 2);
        $DEPT = $row["deptName"];
    }
} else {
    // echo "No Pending Requests";
}
?>


<body onload='markOrdered(<?php echo $DEPTID ?>)'>
    <div id="container">
        <div id="header">
            <img src="../assets/img/bcg-hz (6).png" width="40%" alt="">
            </br>
            _______________________________________________________________________________________
        </div>
        <div id="wrapper">
            <div id="content">
                <h2><strong>Approved Requests to order for: <?php echo $DEPT ?></strong></h2>
                <h2><strong>Total Number of Approved Requests: <?php echo $APPROVEDREQ ?> </strong></h2>
                <h2><strong>Total Value of Approved Requests: $<?php echo $TWTs ?> </strong></h2>
                <!-- <p> -->
                <!-- <table>
                    <tr>
                        <th>Request Details</th>
                        <th>Employee Response</th>
                    </tr> -->

                <?php
                if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                if ($ROLE === "Administrator") {
                    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created, o.requested_by_name, 
                    o.quantity, o.product_name, e.email, e.deptName, e.deptNumber, e.empNumber, o.line_item_total, o.order_id, o.order_details_id, o.customer_id, o.created, o.logo, o.product_code
                    FROM uniform_orders.ord_ref o
                    join emp_ref e on o.emp_id = e.empNumber
                    JOIN dep_ref dr on o.department = dr.dep_num
                    WHERE status = 'Approved' and e.deptNumber = $DEPTID";
                } else {
                    $sql = "SELECT o.size_id, o.status, o.color_id, e.empName, o.emp_id, o.product_name, o.product_price, o.created, o.requested_by_name,
                    o.quantity, o.product_name, e.email, e.deptName, e.deptNumber, e.empNumber, o.line_item_total, o.order_id, o.order_details_id, o.customer_id, o.created, o.logo, o.product_code
                    FROM uniform_orders.ord_ref o
                    join emp_ref e on o.emp_id = e.empNumber
                    JOIN dep_ref dr on o.department = dr.dep_num
                    WHERE (status = 'Approved' AND dr.dep_head = $DEPT)";
                }
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // NEED THE INFORMATION FOR PRINTING
                        $SIZE = $row["size_id"];
                        $COLOR = $row["color_id"];
                        $EMPNAME = $row["empName"];
                        $EMPLOYEEID = $row["emp_id"];
                        $PRODUCTNAME = $row["product_name"];
                        $PRODUCTPRICE = $row["product_price"];
                        $REQUESTDATE = $row["created"];
                        $QNT = $row["quantity"];
                        $PRODUCTNAME = $row["product_name"];
                        $GRANDTOTAL = ($QNT * $PRODUCTPRICE);
                        $EMAIL = $REQUESTDATE = $row["email"];
                        $DEPTNAME = $row["deptName"];
                        $DEPTNUMBER = $row["deptNumber"];
                        $EMPID = $row["empNumber"];
                        $ORDERID = $row["order_id"];
                        $ORDERDETAILSID = $row['order_details_id'];
                        $TOT = $row["line_item_total"];
                        $CUSTOMERID = $row["customer_id"];
                        $DATE = $row["created"];
                        $STATUS = $row["status"];
                        $LOGO = $row["logo"];
                        $CODE = $row["product_code"];
                        $REQUESTOR = $row["requested_by_name"];

                        echo "<p>";
                        echo "<table>";
                        echo "<tr>";
                        echo "<th width=50%>$DATE</th>";
                        echo "<th width=50%>$EMPNAME";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Employee ID</td>";
                        echo "<td>$EMPLOYEEID</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Contact for Order</td>";
                        echo "<td>$REQUESTOR</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Number of Items</td>";
                        echo "<td>$QNT</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Price per Item</td>";
                        echo "<td>$$PRODUCTPRICE</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Grand Total of Request</td>";
                        echo "<td>$$TOT</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Item being Requested</td>";
                        echo "<td>$PRODUCTNAME</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Size being Requested</td>";
                        echo "<td>$SIZE</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Color being Requested</td>";
                        echo "<td>$COLOR</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Product Code</td>";
                        echo "<td>$CODE</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Logo for Item</td>";
                        echo "<td>$LOGO</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Department</td>";
                        echo "<td>$DEPTNAME</td>";
                        echo "</tr>";
                        echo "<tr><td><hr></td><td><hr></td></tr>";
                    }
                }
                ?>
                </table>
                </p>
            </div>
        </div>

        <script type="text/javascript">
            //-->
        </script>
        <div id="footer">
            <center>
                <button class="hide-from-printer pulse-button" onclick="printpage()" type="submit" value="Print" role="button" id="btn">Click Here to Print Report</button>
            </center>
            <script>
                function printpage() {
                    window.print();
                }
            </script>
        </div>
    </div>
</body>

</html>

<style type="text/css">
    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    img {
        width: 100% !important;
        height: auto;
    }

    body {
        color: #292929;
        font: 90% Roboto, Arial, sans-serif;
        font-weight: 300;
    }

    p {
        padding: 0 10px;
        line-height: 1.8;
    }

    ul {
        padding-inline-start: 15px;
    }

    ul li {
        padding-right: 10px;
        line-height: 1.6;
        padding-inline-start: -25px;
    }

    h3 {
        padding: 5px 20px;
        margin: 0;
    }

    div#header {
        position: relative;
    }

    div#header h1 {
        height: 80px;
        line-height: 80px;
        margin: 0;
        padding-left: 10px;
        background: #e0e0e0;
        color: #292929;
    }

    div#header a {
        position: absolute;
        right: 0;
        top: 23px;
        padding: 10px;
        color: #006;
    }

    div#navigation {
        background: white;
    }

    div#navigation li {
        list-style: none;
    }

    div#extra {
        background: white;
    }

    div#footer {
        background: white;
    }

    div#footer p {
        padding: 20px 10px;
    }

    div#container {
        width: 700px;
        margin: 0 auto;
    }

    div#content {
        float: right;
        width: 700px;
    }

    div#navigation {
        float: left;
        width: 200px;
    }

    div#extra {
        float: left;
        clear: left;
        width: 200px;
    }

    div#footer {
        clear: both;
        width: 100%;
    }

    /* TABLE CSS */
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    tr:nth-child(even) {
        background-color: #dddddd;
    }

    td.no-padding {
        border: none;
        background-color: white;
    }

    th.no-padding {
        border: none;
        background-color: white;
    }

    td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    /* BUTTON CSS STARTS HERE */

    .noprint {
        display: none;
    }

    @media print {

        /* hide the print button when printing */
        .hide-from-printer {
            display: none;
        }
    }

    .print {
        visibility: visible;
    }

    /* Button resets and style */
    button {
        margin: 15px auto;
        font-family: "Montserrat";
        font-size: 28px;
        color: #ffffff;
        cursor: pointer;
        border-radius: 100px;
        padding: 15px 20px;
        border: 0px solid #000;
    }

    /* Initiate Auto-Pulse animations */
    button.pulse-button {
        animation: borderPulse 1000ms infinite ease-out, colorShift 10000ms infinite ease-in;
    }

    /* Initiate color change for pulse-on-hover */
    button.pulse-button-hover {
        animation: colorShift 10000ms infinite ease-in;
    }

    /* Continue animation and add shine on hover */
    button:hover,
    button:focus {
        animation: borderPulse 1000ms infinite ease-out, colorShift 10000ms infinite ease-in, hoverShine 200ms;
    }

    /* Declate color shifting animation */
    @keyframes colorShift {

        0%,
        100% {
            background: #0045e6;
        }

        33% {
            background: #fb3e3e;
        }

        66% {
            background: #0dcc00;
        }
    }

    /* Declare border pulse animation */
    @keyframes borderPulse {
        0% {
            box-shadow: inset 0px 0px 0px 5px rgba(255, 255, 255, .4), 0px 0px 0px 0px rgba(255, 255, 255, 1);
        }

        100% {
            box-shadow: inset 0px 0px 0px 3px rgba(117, 117, 255, .2), 0px 0px 0px 10px rgba(255, 255, 255, 0);
        }
    }

    /* Declare shine on hover animation */
    @keyframes hoverShine {
        0% {
            background-image: linear-gradient(135deg, rgba(255, 255, 255, .4) 0%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0) 100%);
        }

        50% {
            background-image: linear-gradient(135deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .4) 50%, rgba(255, 255, 255, 0) 100%);
        }

        100% {
            background-image: linear-gradient(135deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, .4) 100%);
        }
    }
</style>