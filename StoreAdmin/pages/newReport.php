<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Report</title>
</head>

<?php
$UID = filter_input(INPUT_GET, 'UID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$DEPT = $_SESSION["empNumber"];
$ROLE = $_SESSION["role_name"];
// QUERY TO FIND THE TOTAL NUMBER OF ITEMS TO BE ORDERED
if ($ROLE === "Administrator") {
    $sql = "SELECT SUM(ord_ref.quantity) as QNT, dep_ref.dep_name
    FROM uniform_orders.order_inst_order_details_id
    JOIN ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
    JOIN dep_ref ON ord_ref.department = dep_ref.dep_num
    RIGHT JOIN order_details ON ord_ref.order_id = order_details.order_id
    WHERE order_inst_id = '188966da27d60ad8e30dec0fac0643e16fadb438e18'";
} else {
    echo "YOU ARE NOT AN ADMIN";
}
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $TOTALQNT = $row["QNT"];
        $DEPTNAME = $row["dep_name"];
    }
} else {
    // echo "No Pending Requests";
}
?>

<body>

    <div id="container">
        <div id="header">
            <img src="../assets/img/bcg-hz (6).png" width="100%" alt="">
            </br>
            _______________________________________________________________________________________
            <h2><strong><?php echo $DEPTNAME ?></strong></h2>
            <h2><strong>Total Approved Items to be ordered: <?php echo $TOTALQNT ?></strong></h2>
            </br>
        </div>


        <table>
            <colgroup>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
            </colgroup>

            <tr>
                <th>Style #</th>
                <th>Color</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price</th>
                <th colspan="2">Logo</th>
            </tr>
            <?php
            // <!-- QUERTY FOR PRODUCT SKUS ORDERED BY NUMBER OF ITEMS -->
            if ($ROLE === "Administrator") {
                $sql = "SELECT order_inst_order_details_id.order_inst_id, ord_ref.product_code, ord_ref.size_name, ord_ref.quantity, ord_ref.color_id,ord_ref.line_item_total,
                    ord_ref.logo, ord_ref.dept_patch_place
                    FROM uniform_orders.order_inst_order_details_id
                    JOIN ord_ref ON order_inst_order_details_id.order_details_id = ord_ref.order_details_id
                    JOIN dep_ref ON ord_ref.department = dep_ref.dep_num
                    RIGHT JOIN order_details ON ord_ref.order_id = order_details.order_id
                    WHERE order_inst_id = '188966da27d60ad8e30dec0fac0643e16fadb438e18'
ORDER BY ord_ref.color_id";
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
                    $PRICE = $row["line_item_total"];
                    $LOGO = $row["logo"];
                    $DEPT_PATCH = $row["dept_patch_place"];
                    $PRICE = number_format($PRICE, 2);

                    echo "<tr>";
                    echo "<td>$PC</td>";
                    echo "<td>$CI</td>";
                    echo "<td>$SN</td>";
                    echo "<td>$TALLY</td>";
                    echo "<td>$$PRICE</td>";
                    echo "<td><img src='../../../$LOGO'</td>";
                    // echo "<td><strong>Display Department Name:</strong> $DEPT_PATCH</td>";
                    echo "<td>$DEPTNAME -- $DEPT_PATCH</td>";
                    echo "</tr>";
                }
            }

            ?>
        </table>
</body>

</html>


<style>
table,
th,
td {
    border: 1px solid black;
}

th,
td {
    padding: 10px;
}

th {
    background-color: #FDDF95;
}

colgroup {

    width: 225px;
}

#header {
    text-align: center;
}

img {
    width: 75% !important;
    height: auto;
}
</style>