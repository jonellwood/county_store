<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 08/09/2024
Purpose: Display order success notification to user with order details, trigger email to user, and wipe cart from local storage.
Includes:   
            viewHead.php is a common html head element with css, favicon, and metadata 
            footer.php is the footer element.

*/
if (empty($_REQUEST['id'])) {
    header("Location: index.php");
}
$order_id = base64_decode($_REQUEST['id']);
$emp_id = base64_decode($_REQUEST['emp_id']);


function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}
// init connect
require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include "./components/viewHead.php"
?>

<script>
// ! This is called inside the sendmail function on page load.
function wipeLocalCart() {
    localStorage.removeItem('store-cart');
}
</script>
<script src="functions/renderOrderDetails.js"></script>
<script src="functions/renderPersonForOrder.js"></script>
<script>
function getOrderDetailsFromOrdRef(id) {
    var phtml = '';
    var html = '';
    fetch('API/fetchOrderFromOrdRef.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            phtml += renderPerson(data[0]);
            for (var i = 0; i < data.length; i++) {
                html += renderOrderDetails(data[i]);
            }
            document.getElementById('orderPersonDetails').innerHTML = phtml;
            document.getElementById('orderProductDetails').innerHTML = html;
        })
        .catch(error => console.error(error));
}
</script>


<script>
function doStuff() {
    wipeLocalCart();
    getOrderDetailsFromOrdRef(<?php echo $order_id ?>);
    sendmail(<?php echo $order_id ?>, <?php echo $emp_id ?>)
}
</script>

</head>

<body onload='doStuff()'>
    <!-- uncomment line above, and remove this one, to reinstate email order confirmation feature after testing is completed -->
    <div class="container bg-light w-auto">
        <div class="col-md-12">
            <div class="alert alert-success">Your Request has been placed successfully. You should receive an email
                confirmation shortly.</div>
        </div>
        <!-- Order status and contact info -->
        <div class="d-flex" id="orderPersonDetails"></div>
        <!-- order items -->
        <div class="d-flex" id="orderProductDetails"></div>
        <div class="col mb-2 button-container">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <a href="index.php" class="btn btn-primary btn-block">
                        Continue
                        Shopping</a>

                </div>
            </div>
        </div>

    </div>
    <?php include "footer.php" ?>
</body>


</html>
<script>
function sendmail(id, emp_id) {
    fetch('./newSendOrderEmail.php?ord_id=' + id + '&emp_id=' + emp_id);
    wipeLocalCart();
}
</script>
<style>
.container {
    max-width: unset !important;
    margin-left: 5%;
    margin-right: 5%;
}

h1 {
    background-color: #00000090;
    padding: 10px;
    /* font-size: 26px; */
}

.alert {
    color: #000000;
    background-color: #4CAF50 !important;
    border: none;
}

.second-container {
    display: grid;
    grid-template-columns: 1fr 5fr;
    margin-bottom: 10px;
    padding-top: 10px;
    border-top: 1px dashed lightblue;
}

.order-container {
    padding: 10px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    gap: 5px;
    align-items: center;
    background-color: #00000090;
    color: #ffffff;
}

.order-container p {
    padding: 1px;
    margin: 0;
}

.bottomb {
    border-bottom: 1px dashed black;
}

.comment-container {
    margin-bottom: 10px;
    color: #FBFCFD;
    font-family: monospace;
    background-color: #731BC5;
    border-radius: 0.375rem;
}

.button-container {
    margin-top: 15px;
}

.ord-addr-info {
    padding-top: 10px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background-color: #00000090;
    color: #ffffff;
}

img {
    border-radius: 0.375rem;

}

.logo-image {
    margin-left: auto;
    margin-right: auto;
    width: 50px;
    max-width: 75px;
}

.product-image {
    margin-left: auto;
    margin-right: auto;
    width: 150px;
    max-width: 200px;
}

button {
    border-radius: 5px;
}

.button {
    margin: 5px;
}

.button {
    display: inline-block;
    padding: 5px 10px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    border: 2px solid #000000;
    border-radius: 5px;
    background-color: #4CAF50;
    color: #000000;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #4CAF50 !important;
    color: #000000 !important;
    font-weight: bold !important;
}

@media print {

    /* hide the print button when printing */
    .hide-from-printer {
        display: none;
    }

    body {
        width: 2500px;
        font-size: 12px;
    }

    img {
        display: block;
        z-index: 2;
        margin-left: 20px;
        height: 100px;

    }

    .report td {
        margin-top: 10px;
    }

}
</style>