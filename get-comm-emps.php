<script>
    function countdownTimer(seconds) {
        var timeLeft = seconds;
        var countdown = setInterval(function() {
            document.getElementById("countdown-timer").innerHTML = timeLeft + " seconds";
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(countdown);
                // redirect back to index
                window.location.href = "index.php";
            }
        }, 1000);
    }
    countdownTimer(); // Starts a second countdown timer
</script>

<?php

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function setTimeout($fn, $timeout)
{
    sleep(($timeout / 1000));
    $fn();
}

$showDeny = function () {
    echo "<div>";
    echo "<h3>Sorry you are not authorised to access this section</h3>";
    echo "<p>If you feel you have reached this in error please try again</p>";
    echo "</div>";
};

$redirect = function () {
    header("location: index.php");
};

$emp_num = $_GET['emp_num'];


$sql = "SELECT emp_ref.empNumber from uniform_orders.emp_ref where emp_ref.deptNumber = 42103 AND emp_ref.seperation_date is null";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row['empNumber']);
    }
}

// var_dump($emp_num);
echo "<br>";

if (in_array($emp_num, $data)) {
    $_SESSION['comm_emp_num'] = $emp_num;
    header('location: comm-login-ldap.php');
} else {
    echo "<div class='deny'>";
    echo "<div class='deny-sub'>";
    echo "<h3 class='deny-header'>Sorry you are not authorized to access this section</h3>";
    echo "<p class='deny-p'>If you feel you have reached this in error please try again</p>";
    echo "<span class='deny-countdown'>Page will redirect in <span id='countdown-timer'></span></span>";
    echo "</div>";
    echo "</div>";

    // echo "<script>setTimeout(function(){location.href = 'https://store.berkeleycountysc.gov/index.php#nav-container';}, 4000);</script>";
    // echo "<script>setTimeout(function(){location.href = 'index.php#nav-container';}, 3000);</script>";

    echo "<script>countdownTimer(4)</script>";
}

?>

<style>
    .deny {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        position: fixed;
        width: 100vw;
        height: 100vh;
        background-color: black;
        color: white;
        transition: all 0.3s ease;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #countdown-timer {
        text-transform: uppercase;
        font-size: 20px;
        padding-left: 1em;
        animation: flicker 1.5s infinite alternate;
        color: #fff;
        text-shadow: 0 0 7px #fff,
            0 0 10px #fff,
            0 0 21px #fff,
            0 0 42px #0fa,
            0 0 82px #0fa,
            0 0 92px #0fa,
            0 0 102px #0fa,
            0 0 151px #0fa;
    }

    @keyframes flicker {

        0%,
        18%,
        22%,
        25%,
        53%,
        57%,
        100% {
            text-shadow:
                0 0 4px #fff,
                0 0 11px #fff,
                0 0 19px #fff,
                0 0 40px #0fa,
                0 0 80px #0fa,
                0 0 90px #0fa,
                0 0 100px #0fa,
                0 0 150px #0fa;
        }

        20%,
        24%,
        55% {
            text-shadow: none;
        }
    }

    .deny-sub {
        display: grid;
        grid-template-rows: 1fr 1fr 1fr;
        align-items: center;
        margin: 100px;
    }

    .deny-header {
        display: flex;
        align-items: center;
        align-content: center;
        margin-left: 50px;
    }

    .deny-p {
        display: flex;
        align-items: center;
        align-content: center;
        margin-left: 50px;
    }

    .deny-countdown {
        display: flex;
        align-items: center;
        align-content: center;
        margin-left: 50px;
    }
</style>