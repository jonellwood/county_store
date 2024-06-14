<?php
include('DBConn.php');
?>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

if (isset($_GET['empNum'])) {
    $empNum = $_GET['empNum'];

    $sql = "SELECT ur.emp_num, ur.role_id, ur.email, er.deptNumber, ur.role_name, ur.empName
            FROM user_ref ur
            JOIN emp_ref er on er.empNumber = ur.emp_num
            WHERE emp_num = '$empNum'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $selectedUser = mysqli_fetch_assoc($result);
    } else {
        $selectedUser = null;
    }

    if ($selectedUser) {
        // Set session variables
        $_SESSION['emp_num'] = $selectedUser['emp_num'];
        $_SESSION['empName'] = $selectedUser['empName'];
        $_SESSION['username'] = $selectedUser['empName'];
        $_SESSION['email'] = $selectedUser['email'];
        $_SESSION['department'] = $selectedUser['deptNumber'];
        $_SESSION['role_id'] = $selectedUser['role_id'];
        $_SESSION['role_name'] = $selectedUser['role_name'];
        $_SESSION['loggedin'] = true;

        echo 'Session variables updated';
    } else {
        echo 'User not found';
    }
} else {
    echo 'Invalid parameters';
}