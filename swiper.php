<?php

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$swiperSql = "SELECT * from uniform_orders.producttypes where isactive=true";
$swiperStmt = $conn->prepare($swiperSql);
$swiperStmt->execute();
$swiperResult = $swiperStmt->get_result();

$swiperStmt->close();
if ($swiperResult->num_rows > 0) {

?>




<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Swiper demo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />

    <!-- Demo styles -->
    <style>
    html,
    body {
        position: relative;
        height: 100%;
    }

    body {
        background: #eee;
        /* font-family: Helvetica Neue, Helvetica, Arial, sans-serif; */
        font-size: 14px;
        color: #000;
        margin: 0;
        padding: 0;
    }

    @font-face {
        font-family: Rye;
        src: url(./fonts/Rye-Regular.ttf)
    }

    .swiper {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;

        /* Center slide text vertically */
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        -webkit-justify-content: center;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        -webkit-align-items: center;
        align-items: center;
    }

    .swiper-slide img {
        display: block;
        width: 50%;
        height: 50%;
        object-fit: cover;
    }

    .swiper {
        margin-left: auto;
        margin-right: auto;
    }

    .fancy {
        font-family: Rye;
        color: var(--bc-black);

    }

    .container {
        --bs-gutter-x: 0;
        margin-top: 20px;
        height: 450px;
    }
    </style>
</head>

<body>
    <!-- Swiper -->
    <div class="container">

        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php
                $backgrounds = array("product-images/jeans_640_for_cat.jpg", "product-images/shirt_640_for_cat.jpg", "product-images/hat_640_for_cat.jpg", "product-images/police_raincoat_for_cat.jpg", "product-images/sweatshirts_640_for_cat.jpg");
                $i = 0;
                while ($swiperRow = $swiperResult->fetch_assoc()) {
                    echo "<div class='swiper-slide' style='background-image: url(" . $backgrounds[$i] . "); background-size: cover;'><a href=products-by-catagories.php?productType=" . $swiperRow['productType_id'] . "><h2 class='cat-card-title text-center fancy-light'>" . $swiperRow['productType'] . "</h2></a></div>";
                    $i++;
                }
            }
                ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>


    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 1,
        spaceBetween: 30,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
    </script>
</body>

</html>