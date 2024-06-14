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
    <title>Dept Invoice Reports</title>
</head>

<!-- QUERTY FOR TOTAL NUMBER OF ITEMS -->
<?php
$dept = filter_input(INPUT_GET, 'dept', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
$sql = "SELECT SUM(quantity) as TOTALQNT, dep_name
                FROM uniform_orders.ord_ref o
                JOIN dep_ref dr on o.department = dr.dep_num
                WHERE status = 'Received' and department = $dept";
if ($ROLE === "Administrator") {
    // Fast Query if your are an admin
} else {
    echo "<strong>**** YOU DO NOT HAVE ACCESS TO THIS DATA ****</strong>";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // MY VARIABLES NEEDED TO DISPLAY REPORT
        $TOTALQNT = $row["TOTALQNT"];
        $DEPTNAME = $row["dep_name"];
    }
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
                <h2><strong><?php echo $DEPTNAME ?>- On Hand Items</strong></h2>
                <h2><strong>Total Number of Items Received: <?php echo $TOTALQNT ?></strong></h2>
                <?php
                echo "<p>";
                echo "<table>";
                echo "<tr>";
                echo "<th width=10%>SKU#: </th>";
                echo "<th width=15%>Color: ";
                echo "<th width=10%>Size: </th>";
                echo "<th width=5%>Q: ";
                echo "<th width=40%>Product Description: ";
                echo "<th width=40%>Employee: ";
                echo "</tr>";


                // <!-- QUERTY FOR PRODUCT SKUS ORDERED BY NUMBER OF ITEMS -->
                $datestart = filter_input(INPUT_GET, 'datestart', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $dateend = filter_input(INPUT_GET, 'dateend', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $dept = filter_input(INPUT_GET, 'dept', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $sql = "SELECT rf_first_name, rf_last_name, color_id, product_name, product_code, size_name, quantity, product_name
                FROM uniform_orders.ord_ref 
                WHERE order_placed BETWEEN STR_TO_DATE('$datestart','%Y-%m-%d') AND STR_TO_DATE('$dateend','%Y-%m-%d') AND status = 'Received' and department = $dept";
                if ($ROLE === "Administrator") {
                    // Fast Query if your are an admin
                } else {
                    $sql .= "AND dr.dep_head OR dr.dep_assist = $DEPT";
                }
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // MY VARIABLES NEEDED TO DISPLAY REPORT
                        $PC = $row["product_code"];
                        $SN = $row["size_name"];
                        $CI = $row["color_id"];
                        $TALLY = $row["quantity"];
                        $DESC = $row["product_name"];
                        $FNAME = $row["rf_first_name"];
                        $LNAME = $row["rf_last_name"];

                        echo "<p>";
                        echo "<table>";
                        echo "<td width=10%>$PC</td>";
                        echo "<td width=15%>$CI</td>";
                        echo "<td width=10%>$SN</td>";
                        echo "<td width=5%>$TALLY</td>";
                        echo "<td width=40%>$DESC</td>";
                        echo "<td width=40%>$FNAME $LNAME</td>";
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