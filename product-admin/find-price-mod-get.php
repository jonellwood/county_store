<?php

session_start();
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function array_equal($a, $b)
{
    return (is_array($a)
        && is_array($b)
        && count($a) == count($b)
        && array_diff($a, $b) === array_diff($b, $a)
    );
}

$mods_from_db = array();
$size_mod_array = array();

// $count_sql = "SELECT count(price_mod) as mod_count from price_mods";
// $count_stmt = $conn->prepare($count_sql);
// $count_stmt->execute();
// $count_result = $count_stmt->get_result();
// $count_row = $count_result->fetch_assoc();

$i = 0;
// echo "<pre>";
// echo "<p> starting nummber is: ";
// print_r($i);
// echo "</p>";
// print_r($count_row['mod_count']);
// echo "<p> count equals: ";
$c = ($count_row['mod_count'] - 1); //subtract one to account for count starts at 1 but value of price_mods starts at 0.
// print_r($c);
// echo "</p>";
// echo "</pre>";

$xs_price = isset($_POST['xsprice']) ? strip_tags($_POST['xsprice']) : 0;
$s_price = isset($_POST['sprice']) ? strip_tags($_POST['$s_price']) : 0;
$m_price = isset($_POST['mprice']) ? strip_tags($_POST['$m_price']) : 0;
$l_price = isset($_POST['lprice']) ? strip_tags($_POST['$l_price']) : 0;
$xl_price = isset($_POST['xlprice']) ? strip_tags($_POST['$xl_price']) : 0;
$xxl_price = isset($_POST['xxlprice']) ? strip_tags($_POST['$xxl_price']) : 0;
$xxxl_price = isset($_POST['xxxlprice']) ? strip_tags($_POST['$xxxl_price']) : 0;
$xxxxl_price = isset($_POST['xxxxlprice']) ? strip_tags($_POST['$xxxxl_price']) : 0;
$xxxxxl_price = isset($_POST['xxxxxlprice']) ? strip_tags($_POST['$xxxxxl_price']) : 0;
$xxxxxxl_price = isset($_POST['xxxxxxlprice']) ? strip_tags($_POST['$xxxxxxl_price']) : 0;
$xxxxxxxl_price = isset($_POST['xxxxxxxlprice']) ? strip_tags($_POST['$xxxxxxxl_price']) : 0;
$xxxxxxxxl_price = isset($_POST['xxxxxxxxlprice']) ? strip_tags($_POST['$xxxxxxxxl_price']) : 0;
$xxxxxxxxxl_price = isset($_POST['xxxxxxxxxlprice']) ? strip_tags($_POST['$xxxxxxxxxl_price']) : 0;
$xxxxxxxxxxl_price = isset($_POST['xxxxxxxxxxlprice']) ? strip_tags($_POST['$xxxxxxxxxxl_price']) : 0;


$lt_price = isset($_POST['ltprice']) ? strip_tags($_POST['$lt_price']) : 0;
$xlt_price = isset($_POST['xltprice']) ? strip_tags($_POST['$xlt_price']) : 0;
$xxlt_price = isset($_POST['xxltprice']) ? strip_tags($_POST['$xxlt_price']) : 0;
$xxxlt_price = isset($_POST['xxxltprice']) ? strip_tags($_POST['$xxxlt_price']) : 0;
$xxxxlt_price = isset($_POST['xxxxltprice']) ? strip_tags($_POST['$xxxxlt_price']) : 0;
$na_price = isset($_POST['naprice']) ? strip_tags($_POST['$na_price']) : 0;

$price_mod = ($i);
array_push($size_mod_array, $price_mod);
$xs_increase = number_format(($xs_price - $s_price), 2);
array_push($size_mod_array, $xs_increase);
$s_increase = number_format(($s_price - $s_price), 2);
array_push($size_mod_array, $s_increase);
$m_increase = number_format(($m_price - $s_price), 2);
array_push($size_mod_array, $m_increase);
$l_increase = number_format(($l_price - $s_price), 2);
array_push($size_mod_array, $l_increase);
$xl_increase = number_format(($xl_price - $s_price), 2);
array_push($size_mod_array, $xl_increase);
// 2XL
$xxl_increase = number_format(($xxl_price - $s_price), 2);
array_push($size_mod_array, $xxl_increase);
// 3XL
$xxxl_increase = number_format(($xxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxl_increase);
// 4xL
$xxxxl_increase = number_format(($xxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxl_increase);
// 5XL
$xxxxxl_increase = number_format(($xxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxl_increase);
// 6XL
$xxxxxxl_increase = number_format(($xxxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxxl_increase);
// 7XL
$xxxxxxxl_increase = number_format(($xxxxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxxxl_increase);
// 8XL
$xxxxxxxxl_increase = number_format(($xxxxxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxxxxl_increase);
// 9XL
$xxxxxxxxxl_increase = number_format(($xxxxxxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxxxxxl_increase);
// 10XL
$xxxxxxxxxxl_increase = number_format(($xxxxxxxxxxl_price - $s_price), 2);
array_push($size_mod_array, $xxxxxxxxxxl_increase);

$lt_increase = number_format(($lt_price - $lt_price), 2);
array_push($size_mod_array, $lt_increase);
$xlt_increase = number_format(($xlt_price - $lt_price), 2);
array_push($size_mod_array, $xlt_increase);
$xxlt_increase = number_format(($xxlt_price - $lt_price), 2);
array_push($size_mod_array, $xxlt_increase);
$xxxlt_increase = number_format(($xxxlt_price - $lt_price), 2);
array_push($size_mod_array, $xxxlt_increase);
$xxxxlt_increase = number_format(($xxxxlt_price - $lt_price), 2);
array_push($size_mod_array, $xxxxlt_increase);
$na_increase = number_format(($na_price - $na_price), 2);
array_push($size_mod_array, $na_increase);

// $size_mod_array = array();
var_dump($size_mod_array);
$size_labels = array('xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl', 'xxxxl', 'xxxxxl', 'xxxxxxl', 'xxxxxxxl', 'xxxxxxxxl', 'xxxxxxxxxl', 'xxxxxxxxxxl', 'lt', 'xlt', 'xxlt', 'xxxlt', 'xxxxlt', 'na');

$count_sql = "SELECT count(price_mod) as mod_count from price_mods";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();

$i = 0;

// Retrieve first row from table
$sql = "SELECT xs_inc, s_inc, m_inc, l_inc, xl_inc, xxl_inc, xxxl_inc, xxxxl_inc, xxxxxl_inc, xxxxxxl_inc, xxxxxxxl_inc, xxxxxxxxl_inc, xxxxxxxxxl_inc, xxxxxxxxxxl_inc, lt_inc, xlt_inc, xxlt_inc, xxxlt_inc, xxxxlt_inc, na_inc FROM price_mods LIMIT 1";
$result = $conn->query($sql);

// foreach ($size_labels as $label) {
//     $price = strip_tags($_POST[$label . 'price']);
//     $increase = number_format(($price - $s_price), 2);
//     array_push($size_mod_array, $increase);
// }
// Loop until match is found or no more rows
// $i = 0;
while ($row = $result->fetch_assoc()) {
    // Convert row to array
    $row_array = array_values($row);

    // echo "<pre> In function array: ";
    // print_r($row_array);
    // echo "</pre>";
    // Compare row array to form array
    if (array_equal($row_array, $size_mod_array)) {
        // Match found, do something
        echo "<div class='container'>";
        echo "<h5>Price Mod Group is " .  $i . "<h5>";
        echo "<br>";
        // echo "<button onclick='close()' class='btn btn-primary mb-2'>Close</button>";
        echo "</div>";
        break;
    } else {
        // No match, increment counter and retrieve next row
        $i++;

        $sql = "SELECT xs_inc, s_inc, m_inc, l_inc, xl_inc, xxl_inc, xxxl_inc, xxxxl_inc, xxxxxl_inc, xxxxxxl_inc, xxxxxxxl_inc, xxxxxxxxl_inc, xxxxxxxxxl_inc, xxxxxxxxxxl_inc, lt_inc, xlt_inc, xxlt_inc, xxxlt_inc, xxxxlt_inc, na_inc FROM price_mods LIMIT 1 OFFSET $i";
        $result = $conn->query($sql);

        // Check if there are more rows
        if ($result->num_rows == 0) {
            // No more rows, exit loop
            echo "<div id='not-list'>";
            // echo "<pre>";
            // print_r($size_mod_array);
            // echo "</pre>";
            echo "<h5>No mod found. Contact Ellwood to create a price mod for this product. Send him the following information along with the product number. Use copy button below.</h5>";
            echo "</div>";
            echo "<div id='list'>";
            echo "<br>";
            echo "<span>XS Price: " . number_format($xs_price, 2) . " - " . "XS Increase: " . max($xs_increase, 0) .  " * </span>";
            echo "<hr>";
            echo "<span>S Price: " . number_format($s_price, 2) . " - " . "S Increase: " . max($s_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>M Price: " . number_format($m_price, 2) . " - " . "M Increase: " . max($m_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>L Price: " . number_format($l_price, 2) . " - " . "L Increase: " . max($l_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>XL Price: " . number_format($xl_price, 2) . " - " . "XL Increase: " . max($xl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>2X Price: " . number_format($xxl_price, 2) . " - " . "2X Increase: " . max($xxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>3X Price: " . number_format($xxxl_price, 2) . " - " . "3X Increase: " . max($xxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>4x Price: " . number_format($xxxxl_price, 2) . " - " . "4X Increase: " . max($xxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>5X Price: " . number_format($xxxxxl_price, 2) . " - " . "5X Increase: " . max($xxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>6X Price: " . number_format($xxxxxxl_price, 2) . " - " . "6X Increase: " . max($xxxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>7X Price: " . number_format($xxxxxxxl_price, 2) . " - " . "7X Increase: " . max($xxxxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>8X Price: " . number_format($xxxxxxxxl_price, 2) . " - " . "8X Increase: " . max($xxxxxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>9X Price: " . number_format($xxxxxxxxxl_price, 2) . " - " . "9X Increase: " . max($xxxxxxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>10X Price: " . number_format($xxxxxxxxxxl_price, 2) . " - " . "10X Increase: " . max($xxxxxxxxxxl_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>LT Price: " . number_format($l_price, 2) . " - " . "LT Increase: " . max($lt_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>XLT Price: " . number_format($xlt_price, 2) . " - " . "XLT Increase: " . max($xlt_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>2XLT Price: " . number_format($xxlt_price, 2) . " - " . "2XLT Increase: " . max($xxlt_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>3XLT Price: " . number_format($xxxlt_price, 2) . " - " . "3XLT Increase: " . max($xxxlt_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>4XLT Price: " . number_format($xxxxlt_price, 2) . " - " . "4XLT Increase: " . max($xxxxlt_increase, 0) . " * </span>";

            echo "<hr>";
            echo "<span>N/A Price: " . number_format($na_price, 2) . " - " . "N/A Increase: " . max($na_increase, 0) . " * </span>";

            echo "<hr>";

            echo "</div>";
            echo "<button id='clickCopy'>Copy to clipboard</button>";
            break;
        }
    }
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