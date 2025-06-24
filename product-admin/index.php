<?php
session_start();

if (!isset($_SESSION["pa_loggedin"]) || $_SESSION["pa_loggedin"] !== true) {
    header("location: login-ldap.php");
    exit;
}



require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$empList = array();
$empSql = "SELECT empName, empNumber from emp_ref WHERE seperation_date is NULL order by empName ASC";
$empStmt = $conn->prepare($empSql);
$empStmt->execute();
$empRes = $empStmt->get_result();
if ($empRes->num_rows > 0) {
    while ($empRow = $empRes->fetch_assoc()) {
        $empList[] = $empRow;
    }
}

$userList = array();
$usrSql = "SELECT emp_num from users";
$usrStmt = $conn->prepare($usrSql);
$usrStmt->execute();
$usrRes = $usrStmt->get_result();
if ($usrRes->num_rows > 0) {
    while ($usrRow = $usrRes->fetch_assoc()) {
        $userList[] = $usrRow;
        foreach (array_keys($userList, $usrRow['emp_num'], true) as $key) {
            unset($empList[$key]);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" /> -->
    <link rel="stylesheet" href="prod-admin-style.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css">
    <link rel="icon" href="./favicons/favicon.ico">
    <title>Product Admin Dashoard</title>

    <script>
    async function addUser() {
        var user = document.getElementById('userName').value;
        var resetBtn = document.getElementById('user-resetButton');
        var addBtn = document.getElementById('addUserButton');

        const data = await fetch('add-store-user.php?userID=' + user)
            .then((response) => response.json())
            .then(data => {
                console.log(data);
                var html = "<p>" + data[0].res + "</p>";
                document.getElementById('user-res-holder').innerHTML = html;
                resetBtn.classList.remove('hidden');
                addBtn.classList.add('hidden');
            })
    }


    async function addColor() {
        var color = document.getElementById('colorName').value;
        var resetBtn = document.getElementById('resetButton');
        var addBtn = document.getElementById('addColorButton');
        var ifStmt = document.getElementById('ifStmt');
        // console.log(color);
        const data = await fetch('add-color-database.php?colorName=' + color)
            .then((response) => response.json())
            .then(data => {
                // console.log(data);
                var html = "<p>" + data[0].res + "<p>";
                document.getElementById('res-holder').innerHTML = html;
                resetBtn.classList.remove('hidden');
                addBtn.classList.add('hidden');
                ifStmt.classList.add('hidden');
            })
    }

    function resetText() {
        // console.log('Resetting as ASAP as possible');
        var resetBtn = document.getElementById('resetButton');
        var colorForm = document.getElementById('colorForm');
        var toReset = document.getElementById('res-holder');
        var addBtn = document.getElementById('addColorButton');
        var ifStmt = document.getElementById('ifStmt');
        var resetText = " ";
        // console.log(toReset.innerHTML);
        toReset.innerHTML = resetText;
        colorForm.reset();
        resetBtn.classList.add('hidden');
        addBtn.classList.remove('hidden');
        ifStmt.classList.remove('hidden');

    }

    function resetUserText() {
        // console.log('Resetting as ASAP as possible');
        var resetBtn = document.getElementById('user-resetButton');
        var userForm = document.getElementById('userForm');
        var toReset = document.getElementById('user-res-holder');
        var addBtn = document.getElementById('addUserButton');
        var resetText = " ";
        // console.log(toReset.innerHTML);
        toReset.innerHTML = resetText;
        userForm.reset();
        resetBtn.classList.add('hidden');
        addBtn.classList.remove('hidden');

    }
    </script>
</head>

<body>
    <h4>County Store Products Admin Dashboard</h4>
    <?php if ($_SESSION["role_id"] == 1) { ?>


    <table class="styled-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Desc</th>
                <th colspan="2">Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><button>
                        <a href='./update-prod-status.php' target="_blank"><i
                                class="icon-pencil m-auto text-primary"></i> Update Product Status</a>
                    </button>
                </td>
                <td colspan="2">Change product status in database - active / inactive</td>

            </tr>
            <tr>
                <td><button onclick="addColor()" id='addColorButton'><i class="icon-check m-auto text-primary"></i>
                        Check or Add
                        Color</button>
                </td>
                <td>
                    <form id='colorForm'>
                        <label for='colorName'>Enter color to check</label>
                        <input name='colorName' id='colorName'>
                    </form>
                </td>
                <td>
                    <p id="ifStmt">If color is not is database it will be added</p>
                </td>
                <td><button type="button" onclick="resetText()" class='hidden' id='resetButton'>Reset</button>
                    <p id='res-holder'></p>
                </td>
            </tr>
            <tr>
                <td colspan="1"><button>
                        <a href='./add-product-ui.php' target="_blank"><i
                                class="icon-magnifier-add m-auto text-primary"></i> Add Product</a>
                    </button></td>
                <td colspan="1"><button>
                        <a href='./add-price-mod-ui.php' target="_blank"><i class="icon-plus m-auto text-primary"></i>
                            Add Price Mod</button></td>
            </tr>
            <tr>
                <td colspan="1"><button>
                        <a href='./dept-admin.php' target="_blank"><i class="icon-mustache m-auto text-primary"></i>
                            Edit Departments</a>
                    </button></td>
                <!-- </tr> -->
                <!-- <tr>
                <td>
                    <button onclick="addUser()" id='addUserButton'>
                        <i class="icon-user m-auto text-primary"></i>
                        Add Backend User
                    </button>
                </td>
                <td colspan="2">
                    <form id="userForm">
                        <label for='userName'>Select a User to Add</label>
                       
                        </?php
                            echo "<select name='userName' id='userName'>";
                            foreach ($empList as $emp) {
                                echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>";
                            }
                            echo "</select>";
                            ?>
                        <button type="button" onclick="resetUserText()" class='hidden' id='user-resetButton'>
                            Reset</button>
                        <p id='user-res-holder'></p>
                    </form>
                </td>

            </tr> -->
                <!-- <tr> -->
                <td colspan="2">
                    <button>
                        <a href="edit-users.php" target="_blank"><i class="icon-mustache m-auto text-primary"></i> Edit
                            Users</button></a>
                </td>
            </tr>
            <tr>
                <td>
                    <button>
                        <a href='./event-log.php' target="_blank"><i class="icon-event m-auto text-primary"></i>
                            Event Log Viewer</a>
                    </button>
                </td>
                <td colspan="3">View the event log for orders placed, received, and inventory assignments</td>
            </tr>
        </tbody>
    </table>



    <!-- <div class="things-grid"> -->
    <!-- <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./update-prod-status.php' target="_blank"><i class="icon-pencil m-auto text-primary"></i> Update Product Status</a>
                    </button>
                </div>
            </div>
        </div> -->
    <!-- <div class="card"> -->
    <!-- <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button onclick="addColor()" id='addColorButton'><i class="icon-check m-auto text-primary"></i>
                        Check or Add
                        Color</button>

                </div>
            </div> -->
    <!-- <button onclick="addColor()" id='addColorButton'>Add Color</button> -->
    <!-- <form id='colorForm'>
                <label for='colorName'>Enter color to check</label>
                <input name='colorName' id='colorName'>
            </form> -->
    <!-- <p>If color is not in database it will be added</p>
            <p>TODO: disable "check or add" button if input is blank</p>
            <button type="button" onclick="resetText()" class='hidden' id='resetButton'>Reset</button>
            <p id='res-holder'></p> -->
    <!-- </div> -->
    <!-- <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./add-product-ui.php' target="_blank"><i
                                class="icon-magnifier-add m-auto text-primary"></i> Add Product</a>
                    </button>
                </div>
            </div>
        </div> -->

    <!-- <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./dept-admin.php' target="_blank"><i class="icon-mustache m-auto text-primary"></i>
                            Edit Departments</a>
                    </button>
                </div>
            </div>
        </div> -->
    <!-- <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex"> -->
    <!-- <button onclick="addUser()" id='addUserButton'>
                        <i class="icon-user m-auto text-primary"></i>
                        Add Backend User
                    </button> -->
    <!-- </div> -->
    <!-- </div> -->
    <!-- <form id="userForm"> -->
    <!-- <label for='userName'>Select a User to Add</label> -->

    <!-- <//?php -->
    <!-- echo "<select name='userName' id='userName'>"; -->
    <!-- foreach ($empList as $emp) { -->
    <!-- echo "<option value='" . $emp['empNumber'] . "'>" . $emp['empName'] . "</option>"; -->
    <!-- } -->
    <!-- echo "</select>"; -->
    <!-- ?> -->
    <!-- <button type="button" onclick="resetUserText()" class='hidden' id='user-resetButton'>Reset</button> -->
    <!-- <p id='user-res-holder'></p> -->
    <!-- </form> -->
    <!-- <a href="edit-users.php" target="_blank"><button>Edit Users</button></a> -->
    <!-- </div> -->
    <!-- <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./event-log.php' target="_blank"><i class="icon-event m-auto text-primary"></i>
                            Event Log Viewer</a>
                    </button>
                </div>
            </div>
        </div> -->
    <!-- </div> -->
    <?php } else {
        echo "<h1>There is nothing here you are authorised to use.</h1>";
        echo $_SESSION['role_id'];
    } ?>
</body>

</html>
<style>
body {
    display: grid;
    grid-template-columns: auto;
    justify-items: center;
    margin-top: 40px;
    margin-bottom: 40px;
    /* height: 100%; */
    /* margin-left: 40px; */
    /* margin-right: 40px; */
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    background-color: #DBDBDB;
    /* background-image: linear-gradient(0deg, #0d0e0e 27%, #5e5e6a 100%); */
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
}

h4 {
    text-align: center;
}

.things-grid {
    display: grid;
    grid-template-columns: 400px 400px 400px;
    gap: 25px;
}

.card {

    padding: 20px;
    width: 390px;
    height: 390px;
    border: solid 1px #77216F;
}

button {
    color: #7d42b8;
}

a {
    text-decoration: none !important;

}

.hidden {
    visibility: hidden;
}
</style>