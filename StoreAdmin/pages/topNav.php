<?php

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
if (isset($_SESSION["role_id"]) && $_SESSION["role_id"] == 1) {    
echo "<nav>";
    echo "<a href='../index.php'>Old Dashboard &#128117;&#127996;</a>";
    echo "<a href='./orderSummaryView.php'>Order Summary &#10763;</a>";
    echo "<a href='./orders-by-dept-for-admin.php'>Approvals to be Ordered &#129527;</a>";
    echo "<a href='./orders-by-dept-pending-receiving.php'>Items Awaiting Receiving &#9878;&#65039;</a>";
    echo "<a href='./viewOrdersByDept.php'>Order Search by Deparment &#128270;</a>";
    echo "<a href='./employeeRequests.php'>Employee Requests &#128591; </a>";
    echo "<a href='./reports.php'>Reports &#129531;</a>";
echo "</nav>";

} else {
    
    echo "<nav>";
    echo "<a href='../index.php'>Home &#127968;</a>";
    echo "<a href='./viewOrdersByDept.php>Order Search by Deparment &#128270;</a>";
    // echo "<a href='./employeeRequests.php'>Employee Requests &#128591; </a>";
    echo "</nav>";
};
 ?>




<style>
nav {
    border-top: 2px solid #4285F4;
    border-bottom: 2px solid #4285F4;
    padding-left: 40px;
    padding-top: 15px;
    padding-bottom: 15px;
    width: 90vw;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    font-weight: bold;
    font-size: larger;
    display: flex;
    flex-wrap: wrap;

    justify-content: space-around;
    margin-left: auto;
    margin-right: auto;
    gap: 10px;
}

nav a {
    text-decoration: none;
}
</style>