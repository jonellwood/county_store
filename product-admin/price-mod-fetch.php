<?php

session_start();
include_once "config.php";

// Validate and sanitize input
// function sanitizeInput($input) {
//     return isset($_POST[$input]) ? strip_tags($_POST[$input]) : 0;
// }

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die('Could not connect to the database server: ' . $conn->connect_error);
}
$base_price = strip_tags($_POST['sprice']);

$xs_price = strip_tags($_POST['xsprice']);
$s_price = strip_tags($_POST['sprice']);
$m_price = strip_tags($_POST['mprice']);
$l_price = strip_tags($_POST['lprice']);
$xl_price = strip_tags($_POST['xlprice']);
$xxl_price = strip_tags($_POST['xxlprice']);
$xxxl_price = strip_tags($_POST['xxxlprice']);
$xxxxl_price = strip_tags($_POST['xxxxlprice']);
$xxxxxl_price = strip_tags($_POST['xxxxxlprice']);
$xxxxxxl_price = strip_tags($_POST['xxxxxxlprice']);
$xxxxxxxl_price = strip_tags($_POST['xxxxxxxlprice']);
$xxxxxxxxl_price = strip_tags($_POST['xxxxxxxxlprice']);
$xxxxxxxxxl_price = strip_tags($_POST['xxxxxxxxxlprice']);
$xxxxxxxxxxl_price = strip_tags($_POST['xxxxxxxxxxlprice']);

$lt_price = strip_tags($_POST['ltprice']);
$xlt_price = strip_tags($_POST['xltprice']);
$xxlt_price = strip_tags($_POST['xxltprice']);
$xxxlt_price = strip_tags($_POST['xxxltprice']);
$xxxxlt_price = strip_tags($_POST['xxxxltprice']);
$na_price = strip_tags($_POST['naprice']);

$xs_inc = max(0, number_format(( $xs_price - $base_price), 2));
$s_inc = max(0, number_format(( $s_price - $base_price), 2));
$m_inc = max(0, number_format(( $m_price - $base_price), 2));
$l_inc = max(0, number_format(( $l_price - $base_price), 2));
$xl_inc = max(0, number_format(( $xl_price - $base_price), 2));
$xxl_inc = max(0, number_format(( $xxl_price - $base_price), 2));
$xxxl_inc = max(0, number_format(( $xxxl_price - $base_price), 2));
$xxxxl_inc = max(0, number_format(( $xxxxl_price - $base_price), 2));
$xxxxxl_inc = max(0, number_format(( $xxxxxl_price - $base_price), 2));
$xxxxxxl_inc = max(0, number_format(( $xxxxxxl_price - $base_price), 2));
$xxxxxxxl_inc = max(0, number_format(( $xxxxxxxl_price - $base_price), 2));
$xxxxxxxxl_inc = max(0, number_format(( $xxxxxxxxl_price - $base_price), 2));
$xxxxxxxxxl_inc = max(0, number_format(( $xxxxxxxxxl_price - $base_price), 2));
$xxxxxxxxxxl_inc = max(0, number_format(( $xxxxxxxxxxl_price - $base_price), 2));

$lt_inc =max(0, number_format(($lt_price - $base_price), 2));
$xlt_inc =max(0, number_format(($xlt_price - $base_price), 2));
$xxlt_inc =max(0, number_format(($xxlt_price - $base_price), 2));
$xxxlt_inc =max(0, number_format(($xxxlt_price - $base_price), 2));
$xxxxlt_inc =max(0, number_format(($xxxxlt_price - $base_price), 2));
$na_inc = max(0, number_format(($na_price - $base_price), 2));


// echo "<pre>";
// echo "Price";
// var_dump("xs_price = " . $xs_price);
// var_dump("s_price = " . $s_price);
// var_dump("m_price = " . $m_price);
// var_dump("l_price = " . $l_price);
// var_dump("xl_price = " . $xl_price);
// var_dump("xxl_price = " . $xxl_price);
// var_dump("3xl_price = " . $xxxl_price);
// var_dump("4xl_price = " . $xxxxl_price);
// var_dump("5xl_price = " . $xxxxxl_price);
// var_dump("6xl_price = " . $xxxxxxl_price);
// var_dump("7xl_price = " . $xxxxxxxl_price);
// var_dump("8xl_price = " . $xxxxxxxxl_price);
// var_dump("9xl_price = " . $xxxxxxxxxl_price);
// var_dump("10xl_price = " . $xxxxxxxxxxl_price);
// var_dump("lt_price = " . $lt_price);
// var_dump("xlt_price = " . $xlt_price);
// var_dump("2xlt_price = " . $xxlt_price);
// var_dump("3xlt_price = " . $xxxlt_price);
// var_dump("4xlt_price = " . $xxxxlt_price);
// var_dump("na_price = " . $na_price);
// echo "<br>";
// echo "Base Price:";
// var_dump($base_price);
// echo "<br>";
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

// Retrieve the size mod information from the database
$sql = "SELECT price_mod FROM price_mods WHERE 
xs_inc = $xs_inc AND 
s_inc = $s_inc AND
m_inc = $m_inc AND
l_inc = $l_inc AND
xl_inc = $xl_inc AND
xxl_inc = $xxl_inc AND
xxxl_inc = $xxxl_inc AND
xxxxl_inc = $xxxxl_inc AND
xxxxxl_inc = $xxxxxl_inc AND
xxxxxxl_inc = $xxxxxxl_inc AND
xxxxxxxl_inc = $xxxxxxxl_inc AND
xxxxxxxxl_inc = $xxxxxxxxl_inc AND
xxxxxxxxxl_inc = $xxxxxxxxxl_inc AND
xxxxxxxxxxl_inc = $xxxxxxxxxxl_inc AND
lt_inc = $lt_inc AND
xlt_inc = $xlt_inc AND
xxlt_inc = $xxlt_inc AND
xxxlt_inc = $xxxlt_inc AND
xxxxlt_inc = $xxxxlt_inc AND
na_inc = $na_inc";

$stmt = $conn->prepare($sql);
// $stmt->bind_param('i', $i);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0){
    // var_dump(json_encode($result));
    echo "We have a match";
    while ($row = $result->fetch_assoc()) {
        echo "<p>";
        echo "Price Mod: " . $row['price_mod'] . "<br>";
        echo "</p>";
    }
} else {
    echo "No match found";
    $no_match = true;
}

// No match found
if ($no_match) {
    echo "<div id='not-list'>";
    echo "<h5>No mod found. Contact Ellwood to create a price mod for this product. Use copy button below to send him this information along with the product number and a link to the vendors page for the product.</h5>";
            echo "</div>";
            echo "<div id='list'>";
            echo "<br>";
            echo "<span>XS Price: " . number_format($xs_price, 2) . " - " . "XS Increase: " . max($xs_inc, 0) .  " * </span>";
            echo "<hr>";
            echo "<span>S Price: " . number_format($s_price, 2) . " - " . "S Increase: " . max($s_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>M Price: " . number_format($m_price, 2) . " - " . "M Increase: " . max($m_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>L Price: " . number_format($l_price, 2) . " - " . "L Increase: " . max($l_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>XL Price: " . number_format($xl_price, 2) . " - " . "XL Increase: " . max($xl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>2X Price: " . number_format($xxl_price, 2) . " - " . "2X Increase: " . max($xxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>3X Price: " . number_format($xxxl_price, 2) . " - " . "3X Increase: " . max($xxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>4x Price: " . number_format($xxxxl_price, 2) . " - " . "4X Increase: " . max($xxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>5X Price: " . number_format($xxxxxl_price, 2) . " - " . "5X Increase: " . max($xxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>6X Price: " . number_format($xxxxxxl_price, 2) . " - " . "6X Increase: " . max($xxxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>7X Price: " . number_format($xxxxxxxl_price, 2) . " - " . "7X Increase: " . max($xxxxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>8X Price: " . number_format($xxxxxxxxl_price, 2) . " - " . "8X Increase: " . max($xxxxxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>9X Price: " . number_format($xxxxxxxxxl_price, 2) . " - " . "9X Increase: " . max($xxxxxxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>10X Price: " . number_format($xxxxxxxxxxl_price, 2) . " - " . "10X Increase: " . max($xxxxxxxxxxl_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>LT Price: " . number_format($l_price, 2) . " - " . "LT Increase: " . max($lt_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>XLT Price: " . number_format($xlt_price, 2) . " - " . "XLT Increase: " . max($xlt_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>2XLT Price: " . number_format($xxlt_price, 2) . " - " . "2XLT Increase: " . max($xxlt_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>3XLT Price: " . number_format($xxxlt_price, 2) . " - " . "3XLT Increase: " . max($xxxlt_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>4XLT Price: " . number_format($xxxxlt_price, 2) . " - " . "4XLT Increase: " . max($xxxxlt_inc, 0) . " * </span>";

            echo "<hr>";
            echo "<span>N/A Price: " . number_format($na_price, 2) . " - " . "N/A Increase: " . max($na_inc, 0) . " * </span>";

            echo "<hr>";
    echo "</div>";
    echo "<button id='clickCopy'>Copy to clipboard</button>";
}

// Close the database connection
$stmt->close();
$conn->close();

// Function to compare arrays
function array_equal($a, $b) {
    return (is_array($a)
        && is_array($b)
        && count($a) == count($b)
        && array_diff($a, $b) === array_diff($b, $a)
    );
}

?>
<script>
copyToClipboard(document.getElementById("not-list"));

document.getElementById("clickCopy").onclick = function() {
    copyToClipboard(document.getElementById('list'));
    alert('Copied to clipboard');
}

function copyToClipboard(e) {
    var tempItem = document.createElement('input');

    tempItem.setAttribute('type', 'text');
    tempItem.setAttribute('display', 'none');

    let content = e;
    if (e instanceof HTMLElement) {
        content = e.innerHTML.replace(/(<([^>]+)>)/gi, "");
    }

    tempItem.setAttribute('value', content);
    document.body.appendChild(tempItem);

    tempItem.select();
    document.execCommand('Copy');

    tempItem.parentElement.removeChild(tempItem);
}
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="./product-admin/favicons/favicon.ico">

    <title>Document</title>
</head>

<body>

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
</style>