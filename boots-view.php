<?php
session_start();
// if ($_SESSION['GOBACK'] == '') {
$_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
// }
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

$producttype = 7;
$sql = "SELECT * FROM uniform_orders.products p 
join (select * from producttypes) t on p.producttype=t.productType_id
join products_filters pf on pf.product = p.product_id
where p.producttype = $producttype
AND p.isactive = true";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// if (($producttype == 7) || ($producttype == 3) || ($producttype == 8)) {
//     echo '
//     <style>
//     #get-data {
//         visibility: hidden;
//     }
//     #restore {
//         visibility: hidden;
//     }
//     </style>
//     ';
// }
include "./components/viewHead.php"
?>

<script src="functions/renderProduct.js"></script>
<script>
function getFilteredProducts(typeID) {
    fetch('fetchFilteredProductsNoGender.php?type=' + typeID)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            var html = '';

            for (var i = 0; i < data.length; i++) {
                html += renderProduct(data[i]);
            }

            document.getElementById('products-target').innerHTML = html;
            setTimeout(setGrid(), 2000);
        })
}
getFilteredProducts(7)
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script>
<script>
window.onscroll = function() {
    stickyButtons()
};

var btnHolder = document.getElementById("btn-container");
var sticky = navbar.offsetTop;

function stickyButtons() {
    if (window.pageYOffset >= sticky) {
        btnHolder.classList.add("sticky-btn")
    } else {
        btnHolder.classList.remove("sticky-btn");
    }
}
</script>
<div id="products-target" class="d-grid-4 gap-2"></div>
<script>
function gotoPage(val) {
    console.log('Bubbbling');;
    // e.stopPropagation()
    // alert('You will be directed to the details page for this product');
    document.location.replace(val);
}
</script>

<?php include "footer.php" ?>
</body>


</html>

<style>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

.getBig {
    background-color: #93c !important;
    animation: createBox 1.0s;
    color: #93c !important;
}

@keyframes createBox {
    from {
        transform: scale(1);
    }

    to {
        transform: scale(100);
    }
}

.pink {
    color: hotpink;
    font-size: x-large;
}

.background-image {
    width: 100%;
    -webkit-mask-image: linear-gradient(transparent, black);
    mask-image: linear-gradient(transparent, black);
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    object-fit: cover;
}

.products-container {
    position: relative;
    z-index: 1;
}

.card {
    /* margin-top: 20px; */
    margin-right: 20px;
    border-radius: 1px;
    box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);

}

.cart-view {
    position: relative;
    z-index: 1;
}

.card-img-holder {
    display: flex;
    align-self: center;
    align-content: center;
    height: auto;
    position: relative;

}

img {


    margin-left: auto;
    margin-right: auto;
    background-color: transparent;
}

.row>* {
    padding-right: 0px !important;
    padding-left: 0px !important;
}

.card-text {
    color: aliceblue;
}



.btn-info {
    position: relative;
    z-index: 5;
    cursor: crosshair !important;
}

.container h1 {
    text-align: center;
    background-color: #ffffff30;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    margin-left: auto;
    gap: .25vw;
}

.cart-view {
    margin-left: 50px;
}

#featured-card {
    position: relative;
    z-index: 0;
    cursor: crosshair;
}

.featured {
    width: 100%;
    height: 10rem;
    /* position: absolute; */
    bottom: 0;
    z-index: 2;
    background-color: #00000080;
    transition: background-color .5s linear;
    display: block;
    /* margin-top: -2%; */
}

.featured:hover {
    background-color: #00000020;
    transition: background-color .5s linear;
}

.card-title,
.card-subtitle {
    font-size: .85rem;
}

.hidden {
    display: none;
}

.offcanvas {
    background-color: lightgrey;
    color: black;
    display: block;
    overflow: scroll;
    height: 70vh !important;

}

.offcanvas-start {
    right: 0 !important;
}

.offcanvas-title {
    color: black;
    text-align: center;
}

.btn-close {
    border: 1px solid red;
}

.sticky-btn {
    z-index: 5;
    margin-bottom: 20px;
}

::view-transition-old(*),
::view-transition-new(*) {
    animation: none;
    mix-blend-mode: normal;
    height: 100%;
    overflow: clip;
}

::view-transition-old(*) {
    object-fit: contain;
}

::view-transition-new(*) {
    object-fit: cover;
}

/* layout */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(10vw, 1fr));
    grid-auto-rows: 40vw;
    grid-gap: 2vw;
    grid-auto-flow: dense;
}


.grid--big-columns {
    grid-template-columns: repeat(auto-fit, minmax(15vw, 2fr));
}

.grid--big-gap {
    grid-gap: 2vw;
}

/* styling */

.card--expanded {
    /* width: 40rem; */
    grid-column: span 2;
    grid-row: span 2;
    border-radius: 1px;
    /* box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -webkit-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75);
    -moz-box-shadow: 1px 1px 11px 1px rgba(0, 0, 0, 0.75); */
}

.card--expanded .card__img {
    transform: scale(1.03);
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: scale(0);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.fade-in {
    opacity: 0;
    animation: fadeIn 1.9s forwards;
    animation-delay: 0.9s;
}

.card {
    /* cursor: pointer; */
    /* overflow: hidden; */
    position: relative;
}

.card>* {
    pointer-events: none;
}

.card__img {
    transition: transform 1s;
}

.mb-4 {
    margin-bottom: 1rem;
}

.p-4 {
    padding: 1rem;
}

button {
    padding: 0.75rem;
    margin-right: 0.75rem;
    background-color: #191919;
    color: lightgray;
    border: 1px solid lightgray;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: hsla(0, 0%, 83%, 0.05);
}

button:focus {
    box-shadow: 0 0 0 3px #7396e4;
    outline: none;
}

/* a {
    font-weight: bold;
    color: #7396e4;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
} */

code {
    color: #7396e4;
}

.goBtn {
    /* background-color: hotpink; */
    position: relative;
    bottom: 0;
    z-index: 9;
    cursor: pointer !important;
    margin-top: -120px;
    margin-left: 12px;
}

#btn-container {
    margin-bottom: 20px;
}

/* .product-card-holder {
    height: 300px;
} */

/* .spacer {
        height: 130px;
    }

    .image-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 120px;
        z-index: -1;
    } */
</style>