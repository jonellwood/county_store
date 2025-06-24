<?php
// session_start();

require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$pgridSql = "SELECT * from uniform_orders.producttypes where isactive=true";
$pgridStmt = $conn->prepare($pgridSql);
$pgridStmt->execute();
$pgridResult = $pgridStmt->get_result();

$pgridStmt->close();
if ($pgridResult->num_rows > 0) {

?>

<div class="row col-lg-12 categories-container" style="position: relative;">

    <?php
    $backgrounds = array("product-images/shirt_640_for_cat.jpg", "product-images/hat_640_for_cat.jpg", "product-images/raincoat_640_for_cat.jpg", "product-images/sweatshirts_640_for_cat.jpg", "product-images/Boots.jpg", "product-images/accessories.jpg");

    $i = 0;
    while ($prgidRow = $pgridResult->fetch_assoc()) {

        echo "<a id=" . $prgidRow["productType"] . " href=products-by-catagories.php?productType=" . $prgidRow['productType_id'] . ">";
        echo "<div class='cat-card-container'>";
        echo "<div class='card cat-card' id='" . $prgidRow['productType_id'] . "' style='width: 25rem; height: 25rem; background-image: url(" . $backgrounds[$i] . ");'>";
        echo "<div class='card-body'>";
        echo "<div class='button-holder'>";
        echo "</div>";
        echo "<h2 class='cat-card-title text-center fancy-light'>" . $prgidRow["productType"] . "</h2>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</a>";
        $i++;
    }
}

    ?>
</div>

<!-- Old method before moving to ldap -->
<!-- Form to enter employee number -->
<!-- <div class="modal" id="modal-one">
        <div class="modal-bg modal-exit"></div>
        <div class="modal-container">
            <div class="login-image-container">
                <img src="./restricted-section.jpg" alt="This is a Restricted Section" width="30px" id="res-img"></img>
            </div>
            <br>

            <form action="<//?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="logUserIn" class="log-usr-in">
                <fieldset>
                    <p class="log-in-p">
                        <label for "ldapUser">Email Address: </label>
                        <input type="text" name="ldapUser" class="form-control <//?php echo (!empty($ldapUser_err)) ? 'is-invalid' : ''; ?>" value="<//?php echo $ldapUser; ?>">
                        <span class="invalid-feedback"><//?php echo $ldapUser_err; ?></span>
                    </p>
                    <p class="log-in-p">
                        <label for "password">Password: </label>
                        <input type="password" name="password" class="form-control <//?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><//?php echo $password_err; ?></span>
                    </p>

                    <button class="btn btn-secondary log-in-button button" type="submit" value="Log In" form="logUserIn">Log
                        In</button>
                </fieldset>
            </form> -->
<!-- <//?php include "comm-login-ldap.php" ?> -->
<!-- <form id="employee-number-form" action="get-comm-emps.php">
                <label for="employee-number">Enter employee number:</label>
                <input type="text" id="emp_num" name="emp_num"><br><br>
                <input type="submit" value="Submit">
            </form>
            <button class="modal-close modal-exit">X</button>
        </div>
    </div>-->






<script>
// function checkArray(arr, userInput) {
//     if (arr.includes(userInput)) {
//         // do something when the user input is found in the array
//         console.log("Emp Number was found in the array!");
//     } else {
//         // do something when the user input is not found in the array
//         console.log("Emp Number was not found in the array.");
//     }
// }

// save this thing just in case....
// function getCommEmps() {
//     const empList = [];
//     fetch('get-comm-emps.php')
//         .then(console.log('fetch requested....'))
//         .then(response => response.json())
//         .then(console.log('response received ... '))
//         .then(data => {
//             for (var i = 0; i < data.length; i++) {
//                 empList.push(data[i].empNumber);

//             }
//             console.log('emplist array: ' + empList);

//             const userInput = '4438';
//             console.log(empList[0]);
//             console.log(userInput);

//             checkArray(empList, userInput);
//         })
// }


//const userInput = "4707";

//checkArray(data, userInput);
// Output: "User input was found in the array!"



// const modals = document.querySelectorAll('#Communications');


// console.log(modals)
// const modals = document.querySelectorAll('[data-modal]');

// modals.forEach(function(trigger) {
//     trigger.addEventListener('click', function(e) {
//         // console.log('cliked comm link');
//         e.preventDefault();
//         // getCommEmps();
//         // const modal = document.getElementById(trigger.dataset.modal);
//         const modal = document.getElementById('modal-one');
//         // console.log(modal);
//         modal.classList.add('open');
//         const exits = modal.querySelectorAll('.modal-exit');
//         exits.forEach(function(exit) {
//             exit.addEventListener('click', function(e) {
//                 e.preventDefault();
//                 modal.classList.remove('open');
//             })
//         })
//     })
// })
</script>

<style>
.cat-card-title {
    margin-top: 10rem;
}

#employee-number-form {
    position: relative;
    z-index: 3;
}

.temp-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

.temp-container a {
    padding: 30px;
    background: teal;
    color: #fff;
    font-weight: bold;
    font-size: 24px;
    border-radius: 10px;
    cursor: pointer;
}

.modal {
    position: fixed;
    width: 100vw;
    height: 100vh;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.open {
    visibility: visible;
    opacity: 1;
    transition-delay: 0s;
}

.modal-bg {
    position: absolute;
    background: #00000090;
    width: 100%;
    height: 100%;
}

.modal-container {
    border-radius: 10px;
    background: #ffffff95;
    position: relative;
    padding: 30px;
    width: 25em;
}

.modal-close {
    position: absolute;
    right: 15px;
    top: 15px;
    outline: none;
    appearance: none;
    color: red;
    background: none;
    border: 0px;
    font-weight: bold;
    cursor: pointer;
}

.login-image-container {
    display: flex;
}

#res-img {
    width: 200px;
    height: auto;
    margin-left: auto;
    margin-right: auto !important;
    margin-bottom: 15px;
    background-color: transparent;
}

.log-in-p {
    color: black;
}

.form-control {
    color: black;
}
</style>