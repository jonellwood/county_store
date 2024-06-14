<?php

session_start();
include_once "config.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate and sanitize input
// function sanitizeInput($input) {
//     return isset($_POST[$input]) ? strip_tags($_POST[$input]) : 0;
// }

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die('Could not connect to the database server: ' . $conn->connect_error);
}
// $base_price = strip_tags($_POST['sprice']);

$xs_inc = strip_tags($_POST['xsincrease']);
$s_inc = strip_tags($_POST['sincrease']);
$m_inc = strip_tags($_POST['mincrease']);
$l_inc = strip_tags($_POST['lincrease']);
$xl_inc = strip_tags($_POST['xlincrease']);
$xxl_inc = strip_tags($_POST['xxlincrease']);
$xxxl_inc = strip_tags($_POST['xxxlincrease']);
$xxxxl_inc = strip_tags($_POST['xxxxlincrease']);
$xxxxxl_inc = strip_tags($_POST['xxxxxlincrease']);
$xxxxxxl_inc = strip_tags($_POST['xxxxxxlincrease']);
$xxxxxxxl_inc = strip_tags($_POST['xxxxxxxlincrease']);
$xxxxxxxxl_inc = strip_tags($_POST['xxxxxxxxlincrease']);
$xxxxxxxxxl_inc = strip_tags($_POST['xxxxxxxxxlincrease']);
$xxxxxxxxxxl_inc = strip_tags($_POST['xxxxxxxxxxlincrease']);
$lt_inc = strip_tags($_POST['ltincrease']);
$xlt_inc = strip_tags($_POST['xltincrease']);
$xxlt_inc = strip_tags($_POST['xxltincrease']);
$xxxlt_inc = strip_tags($_POST['xxxltincrease']);
$xxxxlt_inc = strip_tags($_POST['xxxxltincrease']);
$na_inc = strip_tags($_POST['naincrease']);



// echo "<pre>";
// echo "Increase";

// echo "<br>";

// var_dump("xs_inc = " . $xs_inc);
// var_dump("s_inc = " . $s_inc);
// var_dump("m_inc = " . $m_inc);
// var_dump("l_inc = " . $l_inc);
// var_dump("xl_inc = " . $xl_inc);
// var_dump("xxl_inc = " . $xxl_inc);
// var_dump("3xl_inc = " . $xxxl_inc);
// var_dump("4xl_inc = " . $xxxxl_inc);
// var_dump("5xl_inc = " . $xxxxxl_inc);
// var_dump("6xl_inc = " . $xxxxxxl_inc);
// var_dump("7xl_inc = " . $xxxxxxxl_inc);
// var_dump("8xl_inc = " . $xxxxxxxxl_inc);
// var_dump("9xl_inc = " . $xxxxxxxxxl_inc);
// var_dump("10xl_inc = " . $xxxxxxxxxxl_inc);
// var_dump("lt_inc = " . $lt_inc);
// var_dump("xlt_inc = " . $xlt_inc);
// var_dump("2xlt_inc = " . $xxlt_inc);
// var_dump("3xlt_inc = " . $xxxlt_inc);
// var_dump("4xlt_inc = " . $xxxxlt_inc);
// var_dump("na_inc = " . $na_inc);
// echo "</pre>";


$insert_sql = "INSERT into price_mods (xs_inc, s_inc, m_inc, l_inc, xl_inc, xxl_inc, xxxl_inc, xxxxl_inc, xxxxxl_inc, xxxxxxl_inc, lt_inc, xlt_inc, xxlt_inc, xxxlt_inc, xxxxlt_inc, na_inc, xxxxxxxl_inc, xxxxxxxxl_inc, xxxxxxxxxl_inc, xxxxxxxxxxl_inc) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($insert_sql);

$stmt->bind_param('dddddddddddddddddddd', $d_xs_inc, $d_s_inc, $d_m_inc, $d_l_inc, $d_xl_inc, $d_xxl_inc, $d_xxxl_inc, $d_xxxxl_inc, $d_xxxxxl_inc, $d_xxxxxxl_inc, $d_lt_inc, $d_xlt_inc, $d_xxlt_inc, $d_xxxlt_inc, $d_xxxxlt_inc, $d_na_inc, $d_xxxxxxxl_inc, $d_xxxxxxxxl_inc, $d_xxxxxxxxxl_inc, $d_xxxxxxxxxxl_inc);

$d_xs_inc = $xs_inc ;
$d_s_inc = $s_inc;
$d_m_inc = $m_inc;
$d_l_inc = $l_inc;
$d_xl_inc = $xl_inc;
$d_xxl_inc = $xxl_inc;
$d_xxxl_inc = $xxxl_inc;
$d_xxxxl_inc = $xxxxl_inc;
$d_xxxxxl_inc = $xxxxxl_inc;
$d_xxxxxxl_inc = $xxxxxxl_inc;
$d_xxxxxxxl_inc = $xxxxxxxl_inc;
$d_xxxxxxxxl_inc = $xxxxxxxxl_inc;
$d_xxxxxxxxxl_inc = $xxxxxxxxxl_inc;
$d_xxxxxxxxxxl_inc = $xxxxxxxxxxl_inc;
$d_lt_inc = $lt_inc;
$d_xlt_inc = $xlt_inc;
$d_xxlt_inc = $xxlt_inc;
$d_xxxlt_inc = $xxxlt_inc;
$d_xxxxlt_inc = $xxxxlt_inc;
$d_na_inc = $na_inc;
// echo "<pre>";
// echo "Increase params";
// echo "<br>";
// var_dump("d_xs_inc = " . $d_xs_inc);
// var_dump("d_s_inc = " . $d_s_inc);
// var_dump("d_m_inc = " . $d_m_inc);
// var_dump("d_l_inc = " . $d_l_inc);
// var_dump("d_xl_inc = " . $d_xl_inc);
// var_dump("d_xxl_inc = " . $d_xxl_inc);
// var_dump("d_3xl_inc = " . $d_xxxl_inc);
// var_dump("d_4xl_inc = " . $d_xxxxl_inc);
// var_dump("d_5xl_inc = " . $d_xxxxxl_inc);
// var_dump("d_6xl_inc = " . $d_xxxxxxl_inc);
// var_dump("d_7xl_inc = " . $d_xxxxxxxl_inc);
// var_dump("d_8xl_inc = " . $d_xxxxxxxxl_inc);
// var_dump("d_9xl_inc = " . $d_xxxxxxxxxl_inc);
// var_dump("d_10xl_inc = " . $d_xxxxxxxxxxl_inc);
// var_dump("d_lt_inc = " . $d_lt_inc);
// var_dump("d_xlt_inc = " . $d_xlt_inc);
// var_dump("d_2xlt_inc = " . $d_xxlt_inc);
// var_dump("d_3xlt_inc = " . $d_xxxlt_inc);
// var_dump("d_4xlt_inc = " . $d_xxxxlt_inc);
// var_dump("d_na_inc = " . $d_na_inc);
// echo "</pre>";


$stmt->execute();
// if(!$stmt->execute()){
//     die('Error: ' . $stmt->error);
// }
$inserted_id = $stmt->insert_id;
if($inserted_id) {
    
    $price_mod = ($inserted_id - 1);
    
    $update_sql = "UPDATE price_mods SET price_mod = $price_mod WHERE id = $inserted_id";
    $update_stmt = $conn->prepare($update_sql);
    
    $updated = $update_stmt->execute();
    if ($updated){
        echo ('SUCCESS The new price mod is: ' . $price_mod); 
        unset($xs_inc);
        unset($s_inc);
        unset($m_inc);
        unset($l_inc);
        unset($xl_inc);
        unset($xxl_inc);
        unset($xxxl_inc);
        unset($xxxxl_inc);
        unset($xxxxxl_inc);
        unset($xxxxxxl_inc);
        unset($xxxxxxxl_inc);
        unset($xxxxxxxxl_inc);
        unset($xxxxxxxxxl_inc);
        unset($xxxxxxxxxxl_inc);
        unset($lt_inc);
        unset($xlt_inc);
        unset($xxlt_inc);
        unset($xxxlt_inc);
        unset($xxxxlt_inc);
        unset($na_inc);
        unset($d_xs_inc);
        unset($d_s_inc);
        unset($d_m_inc);
        unset($d_l_inc);
        unset($d_xl_inc);
        unset($d_xxl_inc);
        unset($d_xxxl_inc);
        unset($d_xxxxl_inc);
        unset($d_xxxxxl_inc);
        unset($d_xxxxxxl_inc);
        unset($d_xxxxxxxl_inc);
        unset($d_xxxxxxxxl_inc);
        unset($d_xxxxxxxxxl_inc);
        unset($d_xxxxxxxxxxl_inc);
        unset($d_xxxxxxxxxxxl_inc);
        unset($d_lt_inc);
        unset($d_xlt_inc);
        unset($d_xxlt_inc);
        unset($d_xxxlt_inc);
        unset($d_xxxxlt_inc);
        unset($d_na_inc);
        unset($price_mod);
    } else {
        echo "Error adding price mod";
        exit();
    }
} else {
    echo "Error adding price mod";
    exit();
}

// Close the database connection
// $stmt->close();
// $conn->close();

// if($update_stmt->affected_rows > 0) {
//     echo "Price Mod added successfully";
// } else {
//     echo "Error adding price mod";
//     die('Error: ' . $update_stmt->error);
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" /> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
    <link rel="icon" href="favicons/favicon.ico">

    <title>Add Price Mod</title>
</head>

<body>
    <div class="container">

        <button>
            <a href="add-price-mod-ui.php">Add another price mod</a>
        </button>
        <button>
            <a href="index.php">Return to Admin Panel</a>
        </button>
</body>

</html>

<style>
html {
    background-color: #0d0e0e;
    background-image: linear-gradient(0deg, #0d0e0e 27%, #5e5e6a 100%);
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
    color: whitesmoke;

}

body {
    display: grid;
    grid-template-columns: auto;
    justify-items: center;
    margin-top: 40px;
    /* margin-left: 40px; */
    /* margin-right: 40px; */
}

/* body {
        margin: 20px;
    } */

.container {
    margin-left: 20px;
    margin-right: 20px;
}

button {
    margin-top: 20px;
}

a {
    text-decoration: none;
}
</style>