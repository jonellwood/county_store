<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: pages/sign-in.php");

    exit;
}

// Get approved orders from requested department
$sql = "SELECT o.*, e.empName, r.empName as REQUESTOR, dr.dep_num, e.email, e.deptName, e.deptNumber, e.empNumber FROM uniform_orders.ord_ref o
        JOIN emp_ref e ON o.emp_id = e.empNumber 
        JOIN emp_ref r ON o.submitted_by = r.empNumber 
        JOIN dep_ref dr ON o.department = dr.dep_num
        WHERE status = 'Approved'";

// add additional conditions if user is Administrator
if ($ROLE === "Administrator") {
    $sql .= "  AND e.deptNumber = $DEPTID";
} else {
    $sql .= "  AND dr.dep_head = $DEPT";
}

$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // GENERAL INFORMATION ABOUT DRIVER
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
        $REQUESTOR = $row["REQUESTOR"];

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