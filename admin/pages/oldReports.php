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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previous Department Reports</title>
</head>

<?php
$UID = filter_input(INPUT_GET, 'UID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
// QUERY TO FIND THE TOTAL NUMBER OF ITEMS TO BE ORDERED
if ($ROLE === "Administrator") {
    // $sql = "SELECT SUM(quantity) as TOTALQNT, e.deptName FROM  uniform_orders.order_inst_order_details_id
    // JOIN ord_ref o ON order_inst_order_details_id.order_details_id = o.order_details_id
    // JOIN emp_ref e on o.emp_id = e.empNumber
    // WHERE order_inst_id = '$UID'";
    $sql = "SELECT SUM(ord_ref.quantity) as TOTALQNT, e.deptName
    FROM uniform_orders.ord_ref 
    JOIN emp_ref e on ord_ref.emp_id = e.empNumber
    WHERE ord_ref.status = 'ordered' and ord_ref.department = 41504";
} else {
    echo "YOU ARE NOT AN ADMIN";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $TOTALQNT = $row["TOTALQNT"];
        $DEPTNAME = $row["deptName"];
    }
} else {
    // echo "No Pending Requests";
}
?>


<body>
    <div id="container">
        <div id="header">
            <img src="../assets/img/bcg-hz (6).png" width="40%" alt="">
            </br>
            _______________________________________________________________________________________
        </div>
        <div id="wrapper">
            <div id="content">
                <h2><strong><?php echo $DEPTNAME ?></strong></h2>
                <h2><strong>Total Approved Items ordered: <?php echo $TOTALQNT ?></strong></h2>
                <?php
                echo "<p>";
                echo "<table>";
                echo "<tr>";
                echo "<th width=20%>Style Number: </th>";
                echo "<th width=20%>Color: ";
                echo "<th width=20%>Size: </th>";
                echo "<th width=20%>Quantity: ";
                // echo "<th width=20%>Price Per Item: ";
                echo "</tr>";


                // <!-- QUERTY FOR PRODUCT SKUS ORDERED BY NUMBER OF ITEMS -->
                if ($ROLE === "Administrator") {
                    // $sql = "SELECT * FROM uniform_orders.order_inst_order_details_id
                    // JOIN ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
                    // WHERE order_inst_id = '$UID' GROUP BY product_code, size_name, color_id ORDER BY SUM(quantity) DESC";
                    $sql = "SELECT * FROM uniform_orders.ord_ref 
                    WHERE status = 'ordered' and department = 41504
                    GROUP BY product_code, size_name, color_id ORDER BY SUM(quantity) DESC";
                } else {
                    echo "YOU ARE NOT AN ADMIN";
                }
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // MY VARIABLES NEEDED TO DISPLAY REPORT
                        $PC = $row["product_code"];
                        $SN = $row["size_name"];
                        $CI = $row["color_id"];
                        $TALLY = $row["quantity"];
                        $PRICE = $row["product_price"];
                        $PRICE = number_format($PRICE, 2);

                        echo "<p>";
                        echo "<table>";
                        echo "<td width=20%>$PC</td>";
                        echo "<td width=20%>$CI</td>";
                        echo "<td width=20%>$SN</td>";
                        echo "<td width=20%>$TALLY</td>";
                        // echo "<td width=20%>$$PRICE</td>";
                    }
                }

                ?>

                </table>
                </p>
                <strong>
                    <center>__________
                        DETAILED EMPLOYEE INFORMATION
                        __________</center>
                </strong>


                <?php
                if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                if ($ROLE === "Administrator") {
                    $sql = "SELECT order_inst_order_details_id.order_inst_id, ord_ref.created, ord_ref.requested_by_name, ord_ref.product_name, ord_ref.product_code,
                    ord_ref.order_details_id, ord_ref.quantity, ord_ref.size_name, ord_ref.color_id, ord_ref.emp_id,
                    ord_ref.rf_first_name, ord_ref.rf_last_name, ord_ref.logo, dep_ref.dep_name
                    FROM uniform_orders.order_inst_order_details_id
                    JOIN ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
                    JOIN dep_ref ON ord_ref.department = dep_ref.dep_num
                    WHERE order_inst_id = '$UID'";
                } else {
                    echo "YOU ARE NOT AN ADMIN";
                }
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // NEED THE INFORMATION FOR PRINTING
                        $SIZE = $row["size_name"];
                        $COLOR = $row["color_id"];
                        $ORDERforLN = $row["rf_last_name"];
                        $ORDERforFN = $row["rf_first_name"];
                        $EMPLOYEEID = $row["emp_id"];
                        $PRODUCTNAME = $row["product_name"];
                        $REQUESTDATE = $row["created"];
                        $QNT = $row["quantity"];
                        $PRODUCTNAME = $row["product_name"];
                        $DEPTNAME = $row["dep_name"];
                        $EMPID = $row["empNumber"];
                        $DATE = $row["created"];
                        $LOGO = $row["logo"];
                        $CODE = $row["product_code"];
                        $REQUESTOR = $row["requested_by_name"];

                        echo "<p>";
                        echo "<table>";
                        echo "<tr>";
                        echo "<th width=50%>$DATE</th>";
                        echo "<th width=50%>$ORDERforFN $ORDERforLN";
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
                        echo "<td>Department</td>";
                        echo "<td>$DEPTNAME</td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td>Logo for Item</td>";
                        echo "<td><img src='../../../$LOGO'</td>";
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