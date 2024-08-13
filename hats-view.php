<?php

/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/03/2024
Purpose: View the items with type hats.
Includes:    viewHead.php, footer.php
*/
session_start();
if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// check if users isComm. If so redirect to comm only products
if (isset($_SESSION["isComm"]) && ($_SESSION["isComm"] === true)) {
    header("location: products-by-communications.php");
    exit;
}
// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;

include "./components/viewHead.php"
?>

<script src="functions/renderHat.js"></script>

<div id="products-target" class="d-grid-4 gap-3 m-4"></div>

<?php include "footer.php" ?>

<script>
function getFilteredProducts(typeID) {
    fetch('fetchFilteredProductsNoGender.php?type=' + typeID)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            var html = '';

            for (var i = 0; i < data.length; i++) {
                html += renderHat(data[i]);
            }

            document.getElementById('products-target').innerHTML = html;
            setTimeout(setGrid(), 2000);
        })
}
getFilteredProducts(3)
</script>

</body>

</html>
<script>
function setGrid() {
    var itemCount = document.getElementById('products-target').childElementCount;
    console.log('item count:', itemCount);
    var pt = document.getElementById('products-target')
    console.log(pt);
    if (itemCount == 5) {
        // pt.classList.remove('d-grid-4');
        pt.style.gridTemplateColumns = '1fr 1fr 1fr 1fr 1fr';
    } else if (itemCount == 4) {
        pt.style.gridTemplateColumns = '1fr 1fr 1fr 1fr';
    } else if (itemCount == 3) {
        pt.gridTemplateColumns = '1fr 1fr 1fr';
    } else if (itemCount == 2) {
        pt.gridTemplateColumns = '1fr 1fr';
    } else if (itemCount == 1) {
        pt.gridTemplateColumns = '1fr';
    } else {
        pt.gridTemplateColumns = '1fr 1fr 1fr 1fr 1fr 1fr';
    }
}
</script>