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

$producttype = 2;
$sql = "SELECT * FROM uniform_orders.products p 
join (select * from producttypes) t on p.producttype=t.productType_id
join products_filters pf on pf.product = p.product_id
where p.producttype = $producttype
AND gender_filter = 2
AND p.isactive = true";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if (($producttype == 7) || ($producttype == 3) || ($producttype == 8)) {
    echo '
    <style>
    #get-data {
        visibility: hidden;
    }
    #restore {
        visibility: hidden;
    }
    </style>
    ';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Products by Category</title>
</head>

<body>
    <div class="container">
        <?php include "./components/slider.php" ?>
        <div class="spacer23"> - </div>
        <div class="container" id="btn-container">
            <!-- <button class='btn btn-primary' id='get-data' type='button' data-bs-toggle='offcanvas'
                data-bs-target='#offcanvasBottom' aria-controls='offcanvasBottom'> Filters</button>
            <button id="restore" type="button" form="filter-form" class='btn btn-warning'>Reset Filters</button> -->
            <button class="btn js-toggle-grid-gap">toggle <code>item-spacing</code></button>
            <button class="btn js-toggle-grid-columns">toggle <code>items-per-row</code></button>
        </div>

        <div class="row col-lg-12 products-container grid--big-columns" id="products-container">

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['product_id'];
                    $filterSql = "SELECT * from prod_filter_ref WHERE product=$product_id";
                    $filterStmt = $conn->prepare($filterSql);
                    $filterStmt->execute();
                    $filterResult = $filterStmt->get_result();
                    // $filterStmt->close();
                    $filterList = array();

                    foreach ($filterResult as $filterRow) {
                        $filterList[] = $filterRow['gender'];
                        $filterList[] = $filterRow['type'];
                        $filterList[] = $filterRow['size'];
                        $filterList[] = $filterRow['sleeve'];
                    };

                    // $sugerSql = "SELECT COUNT(ord._ref.product_id) as order_count from uniform_orders.ord_ref where product_id= $product_id";
                    $sugerStmt =
                        $proImage = !empty($row["image"]) ? $row["image"] : "demo-img.jpg";

            ?>
            <div class=product-card-holder>
                <div class="card home-product-info" id="featured-card" value="<?php echo $row['product_id'] ?>"
                    data-gender="<?php echo $filterRow['gender'] ?>" data-type="<?php echo $filterRow['type'] ?>"
                    data-size="<?php echo $filterRow['size'] ?>" data-sleeve="<?php echo $filterRow['sleeve'] ?>">

                    <img src="<?php echo $proImage; ?>" class="card-img-top" alt="...">
                    <div class="card-body featured">
                        <h6 class="card-title"><?php echo $row["name"]; ?> <br> Item #: <?php echo $row["code"] ?></h6>
                        <h6 class="card-subtitle mb-2">Starting at:
                            <?php echo CURRENCY_SYMBOL . number_format($row["price"], 2) . ' ' . CURRENCY; ?>
                        </h6>
                    </div>
                </div>
                <div class="button-holder">
                    <!-- <button href="product-details-form.php?product_id=<//?php echo $row["product_id"]; ?>" -->
                    <button class="btn btn-info goBtn"
                        value="product-details.php?product_id=<?php echo $row["product_id"]; ?>"
                        onclick="gotoPage(this.value)">Details</button>
                </div>
            </div>
            <?php }
            } else {
                include "product-type-not-found.php";
                ?>
            <!-- <p>Product(s) not found....</p> -->
            <?php }
            // $conn->close();
            ?>
        </div>
        <div class="button-holder">
            <a href="index.php"><button class="btn btn-secondary" type="button"><i class="fa fa-arrow-left"
                        aria-hidden="true"></i> Continue
                    Shopping </button></a>

        </div>
    </div>
    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
        <div class="offcanvas-body" id="offcanvas-body">
            <div class="offcanvas-title">Select what products you would like to see. Use filter button below to
                apply
                your selection(s)</div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"
                id="close-filters-box"></button>
            <div id='left'><?php include "filter-box.php" ?></div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
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
    <script>
    const grid = document.querySelector("#products-container");
    const card = document.querySelector(".card-body");
    const goBtn = document.querySelector(".goBtn");

    document.querySelector(".js-toggle-grid-columns").addEventListener("click", () => {
        // console.log('toggle grid clicked');
        if (document.startViewTransition) {
            document.startViewTransition(_ => grid.classList.toggle("grid--big-columns"));
        } else {
            grid.classList.toggle("grid--big-columns");
        }
    });
    document.querySelector(".js-toggle-grid-gap").addEventListener("click", () => {
        if (document.startViewTransition) {
            document.startViewTransition(_ => grid.classList.toggle("grid--big-gap"));
        } else {
            grid.classList.toggle("grid--big-gap");
        }
    })
    goBtn.addEventListener("click", e => {
        e.stopPropagation();
        let target = e.target;
        //console.log('e.target is: ');
        //console.log(e.target);
        // alert(' !!! Go Button Pressed');
        return;
    })
    grid.addEventListener("click", ev => {
        let target = ev.target;
        let parent = ev.target.parentElement;
        if (target.classList.contains("card")) {
            if (document.startViewTransition) {
                const direction = target.classList.contains('card--expanded') ? 'shrink' : 'grow';
                const origVtName = target.style.viewTransitionName;
                target.style.viewTransitionName = `img-${direction}`;
                document.startViewTransition(_ => {
                    parent.classList.toggle("card--expanded");
                    setTimeout(_ => target.style.viewTransitionName = origVtName, 0);
                });
            } else {
                parent.classList.toggle("card--expanded");
            }
            return;
        }
    });
    </script>
    <script>
    function gotoPage(val) {
        console.log('Bubbbling');;
        // e.stopPropagation()
        // alert('You will be directed to the details page for this product');
        document.location.replace(val);
    }
    </script>

</body>
<?php include "./cartSlideout.php" ?>
<?php include "./footer.php" ?>

</html>

<style>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

body {
    /* position: relative; */
    background-color: slategray;
    font-family: bcFont !important;
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
    position: absolute;
    bottom: 0;
    z-index: 2;
    background-color: #00000080;
    transition: background-color .5s linear;
    display: block;
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
</style>