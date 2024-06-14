<?php
header("Location: employeeRequests.php")

// get database connection
// require_once 'config.php';
// $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
//     or die('Could not connect to the database server' . mysqli_connect_error());

// // init shopping cart class
// include_once 'Cart.class.php';
// $cart = new Cart;

// // fetch products from db
// $sql = "SELECT * from products WHERE products.featured=1 AND products.isActive=True";
// // $sql = "SELECT * from products WHERE products.isActive=True ORDER BY products.product_id DESC LIMIT 6";
// $stmt = $conn->prepare($sql);
// $stmt->execute();
// $result = $stmt->get_result();
// // $conn is closed down around line 92
// function checkisComm()
// {
//     // check if the session variable exists
//     if (!isset($_SESSION['isComm'])) {
//         $_SESSION['isComm'] = false;  // set it to false if not set
//     } else {
//         // do nothing as it is already set
//     }
// }
// checkisComm();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Berkeley County - The Store!</title>
    <meta charset="utf-8">
    <meta name="viewport">

    <!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" defer async> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" defer async> -->
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <!-- <link href="style.css" rel="stylesheet" defer async> -->
    <link href="build/style.min.css" rel="stylesheet" defer async>
    <!-- ideally this script will check local storage for a stored cart and load it as the php cart object if so - otherwise it does nothing -->
    <script>
    // var storedCart = Storage.prototype.getObject = function(key) {
    //     return JSON.parse(this.getItem('savedCart'));
    // }
    // if (storedCart) {
    //     alert('storedCart found');
    //     var cartValues = JSON.parse(localStorage.getItem("savedCart"));
    //     console.log('cartValues');
    //     document.cookie = "storedCart =" + cartValues;
    //     <//?php $_SESSION['cart_contents'] = $_COOKIE['storedCart']; ?>
    //     destroy local storage
    // } else {
    //     alert('No storeCart found');
    // };
    // 
    </script>


</head>

<body>

    <!-- Hero -->
    <div class="make-blurry">
        <div data-section-id="slideshow" data-section-type="slideshow-section">
            <div class="hero-slideshow hero hero--full-height hero--first" id="Hero" data-fullscreen="true"
                data-parallax="true" data-autoplay="true" data-autoplayspeed="5000">
                <div class="hero__slide slide--slideshow-0" data-color="#ffffff" style="color: #ffffff;">
                    <img class="hero__image hero__image--slideshow-0 fade-in" src="./County-Store-Image.png"
                        alt="store">
                    <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-white upfront">
                                <div class="content">
                                    <div class="content__container">
                                        <p class="content__container__text">
                                            Made
                                        </p>

                                        <ul class="content__container__list">
                                            <li class="content__container__list__item">for County Employees</li>
                                            <li class="content__container__list__item">by County Employees</li>
                                            <li class="content__container__list__item">for County Employees</li>
                                            <li class="content__container__list__item">by County Employees</li>

                                        </ul>
                                    </div>
                                </div>
                                <h1 class="fancy mb-3">Berkeley County Employee Store</h1>
                                <!-- <h4 class="fancy mb-3">for Berkeley County Employees by Berkeley County Employees</h4> -->
                                <div class="col text-center shop-btn-holder">
                                    <a class="btn btn-outline-light btn-lg btn-primary fancy" href="#nav-container"
                                        role="button">Shop
                                        Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Hero -->
        <div class="container">
            <?php include "nav.php" ?>
            </ /?php include "swiper.php" ?>
            <?php include "product-type-grid.php" ?>
            <?php include "stats.php" ?>
            <div class="alert alert-danger text-center fire" role="alert">
                Featured Items
            </div>
            <div class="row col-lg-12 products-container" id="products-container">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $proImg = !empty($row["image"]) ?  $row['image'] : 'demo-img.jpg'; ?>
                <div class="card" id="featured-card">
                    <img src="<?php echo $proImg; ?>" class="card-img-top" alt="...">
                    <div class="card-body featured">
                        <h6 class="card-title"><?php echo $row["name"]; ?></h6>
                        <h6 class="card-subtitle mb-2 ">Starting at:
                            <?php echo CURRENCY_SYMBOL . $row["price"] . ' ' . CURRENCY; ?></h6>

                        <div class="button-holder">
                            <a href="product-details-form.php?product_id=<?php echo $row["product_id"]; ?>"
                                class="btn btn-info">Details</a>

                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <p>No hot deals today! Check back soon! </p>
                <?php }
                // $conn->close();
                ?>
            </div>
            <div>
                <div class='alert alert-info text-center' role='alert'>
                    <i class='fa fa-bell' aria-hidden='true'></i> To submit an order request you will need to have your
                    Employee ID Number. <i class='fa fa-bell' aria-hidden='true'></i>

                </div>
            </div>
            <div>
                <div class='alert alert-secondary text-center' role='alert'>
                    <p><b><i class="fa fa-info-circle" aria-hidden="true"></i>Does anyone read these?</b></p>
                    <p><b>I went into a pet shop and told the owner that I wanted twelve bees.</p>
                    <p><b>He handed me thirteen and said, “The last one is a freebie.”</p>

                    <!-- <p><i class="fa fa-phone" aria-hidden="true"></i> Hang on let me get her cell phone and home phone
                    numbers. <i class="fa fa-phone" aria-hidden="true"></i></p> -->
                </div>
            </div>
        </div>
    </div>
</body>



</html>
<style defer>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

@font-face {
    font-family: Rye;
    src: url(./fonts/Rye-Regular.ttf)
}

body {
    position: relative;
    background-color: slategray;
    background-image: url('./Icon\ Grid.svg');
    /* background-image: url('spring-icon-bg.svg'); */
    background-size: cover;
    background-position: center;

    font-family: bcFont !important;
    box-sizing: border-box;
    margin: 0;
    padding: 0;

}

.container {
    display: grid;
    grid-template-columns: 1fr;
    position: relative;
}

.fancy {
    font-family: Rye;
    color: var(--bc-black);

}

.fancy-light {
    font-family: Rye;
    font-weight: 200;
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
    /* display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr; */
    position: relative;
    z-index: 1;
}

.card {
    margin-top: 20px;
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

img {
    width: auto;
    height: 650px;
    margin-left: auto;
    margin-right: auto;
    background-color: aliceblue;
}

.row>* {
    padding-right: 0px !important;
    padding-left: 0px !important;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    margin-left: 1rem;

}

.card-text {
    color: aliceblue;
}

.button-holder {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    align-content: flex-end;
    justify-content: flex-end;
    position: absolute;
    bottom: 0;
    margin-bottom: 10px;
}


.container h1 {
    text-align: center;
    background-color: #ffffff30;
}


.hero--full-height .hero__image {
    height: 100%;
    width: 100%;
    object-fit: cover;
    font-family: "object-fit: cover";
    position: relative;
    z-index: 2;
}

.slide--slideshow-0 .slideshow__overlay:before {
    background-color: #3a1409;
    opacity: 0.3;
}

.upfront {
    border: 1px solid black;
    border-radius: 5px;
    padding: 25px;
    /* background-color: #77889999; */
    background-color: #ffffff99;
    position: absolute;
    top: 0;
    margin-top: 200px;
    z-index: 3;
    /* height: fit-content; */
}

.stroke {
    -webkit-text-stroke-width: 2px;
    -webkit-text-stroke-color: var(--bc-black);
}

.stroke-light {
    -webkit-text-stroke-width: .5px;
    -webkit-text-stroke-color: var(--bs-white);
}

.cart-view {
    margin-left: 50px;
}

.hidden {
    display: none;
}

.alert {
    margin-top: 20px;
    margin-bottom: 20px;
}

.cat-card {
    /* background-image: url(https://placebeard.it/640x360); */
    /* background-image: url(https://placeimg.com/640/480/tech); */
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    border-radius: 50%;
    filter: grayscale(80%);
}

.cat-card:hover {
    transform: rotate(2deg);
    filter: grayscale(0%);
}

/* .cat-card img:hover {
    filter: grayscale(0%)
} */

.cat-card-title {
    background-color: #00000050;
    /* margin-top: 10rem; */
    margin-bottom: auto;
    transform: rotate(-10deg);
    /* color: var(--bs-red); */
    color: var(--bc-blue);
    -webkit-text-stroke-width: .5px;
    -webkit-text-stroke-color: black;
    box-shadow: 0px 0px 15px 1px rgba(255, 255, 255, 0.48);
    -webkit-box-shadow: 0px 0px 15px 1px rgba(255, 255, 255, 0.48);
    -moz-box-shadow: 0px 0px 15px 1px rgba(255, 255, 255, 0.48);

}

.fire {
    font-size: xx-large;
    color: #FFFFFF;
    background: #333333;
    /* text-shadow: 0 -1px 4px #FFF, 0 -2px 10px #ff0, 0 -10px 20px #ff8000, 0 -18px 40px #F00; */
}


.card-img-top {
    width: 20rem;
    height: auto;
    border-radius: 50%;

}

#featured-card {
    width: 20rem;
    position: relative;
    z-index: 0;
    background-color: transparent;
    border-radius: 50%;
    filter: grayscale(30%);
}

#featured-card:hover {
    filter: greyscale(0%);
}

.featured {
    width: 20rem;
    height: 10rem;
    position: absolute;
    bottom: 0;
    z-index: 2;
    background-color: #00000080;
    transition: background-color .5s linear;

}


.featured:hover {
    background-color: #00000030;
    transition: background-color .5s linear;

}

.make-blurry {
    /* display: none; */
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    background-color: hotpink;
}

.shop-btn-holder {
    margin-top: 40px;
}

/* Here comes the poop storm */
.content {
    position: absolute;
    top: 55%;
    left: 50%;
    transform: translate(-50%, -50%);
    /* height: 160px; */
    overflow: hidden;

    /* font-family: 'Lato', sans-serif; */
    font-family: Rye;
    font-weight: 200;
    font-size: 30px;
    line-height: 40px;
    /* color: #ecf0f1; */
    color: black;
    /* margin-bottom: 20px; */
}

.content__container {
    font-weight: 600;
    overflow: hidden;
    height: 40px;
    padding: 0 40px;
}


.content__container:after,
.content__container:before {
    position: absolute;
    top: 0;

    color: #16a085;
    font-size: 42px;
    line-height: 40px;

    -webkit-animation-name: opacity;
    -webkit-animation-duration: 2s;
    -webkit-animation-iteration-count: infinite;
    animation-name: opacity;
    animation-duration: 2s;
    animation-iteration-count: infinite;
}

.content__container__text {
    display: inline;
    float: left;
    margin: 0;
}

.content__container__list {
    margin-top: 0;
    padding-left: 100px;
    text-align: left;
    list-style: none;

    -webkit-animation-name: change;
    -webkit-animation-duration: 10s;
    -webkit-animation-iteration-count: infinite;
    animation-name: change;
    animation-duration: 10s;
    animation-iteration-count: infinite;
}

.content__container__item {
    line-height: 40px;
    margin: 0;
}




@-webkit-keyframes opacity {

    0%,
    100% {
        opacity: 0;
    }

    50% {
        opacity: 1;
    }
}

@-webkit-keyframes change {

    0%,
    12.66%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    16.66%,
    29.32% {
        transform: translate3d(0, -25%, 0);
    }

    33.32%,
    45.98% {
        transform: translate3d(0, -50%, 0);
    }

    49.98%,
    62.64% {
        transform: translate3d(0, -75%, 0);
    }

    66.64%,
    79.3% {
        transform: translate3d(0, -50%, 0);
    }

    83.3%,
    95.96% {
        transform: translate3d(0, -25%, 0);
    }
}

@-o-keyframes opacity {

    0%,
    100% {
        opacity: 0;
    }

    50% {
        opacity: 1;
    }
}

@-o-keyframes change {

    0%,
    12.66%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    16.66%,
    29.32% {
        transform: translate3d(0, -25%, 0);
    }

    33.32%,
    45.98% {
        transform: translate3d(0, -50%, 0);
    }

    49.98%,
    62.64% {
        transform: translate3d(0, -75%, 0);
    }

    66.64%,
    79.3% {
        transform: translate3d(0, -50%, 0);
    }

    83.3%,
    95.96% {
        transform: translate3d(0, -25%, 0);
    }
}

@-moz-keyframes opacity {

    0%,
    100% {
        opacity: 0;
    }

    50% {
        opacity: 1;
    }
}

@-moz-keyframes change {

    0%,
    12.66%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    16.66%,
    29.32% {
        transform: translate3d(0, -25%, 0);
    }

    33.32%,
    45.98% {
        transform: translate3d(0, -50%, 0);
    }

    49.98%,
    62.64% {
        transform: translate3d(0, -75%, 0);
    }

    66.64%,
    79.3% {
        transform: translate3d(0, -50%, 0);
    }

    83.3%,
    95.96% {
        transform: translate3d(0, -25%, 0);
    }
}

@keyframes opacity {

    0%,
    100% {
        opacity: 0;
    }

    50% {
        opacity: 1;
    }
}

@keyframes change {

    0%,
    12.66%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    16.66%,
    29.32% {
        transform: translate3d(0, -25%, 0);
    }

    33.32%,
    45.98% {
        transform: translate3d(0, -50%, 0);
    }

    49.98%,
    62.64% {
        transform: translate3d(0, -75%, 0);
    }

    66.64%,
    79.3% {
        transform: translate3d(0, -50%, 0);
    }

    83.3%,
    95.96% {
        transform: translate3d(0, -25%, 0);
    }
}
</style>