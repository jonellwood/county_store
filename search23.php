<?php
session_start();
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;
$searchTerm = $_POST['param'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here" />

    <!-- <link rel="stylesheet" id='test' href="berkstrap-dark.css" async> -->
    <!-- <link rel="stylesheet" href="style.css" async> -->
    <link href="build/style.min.css" rel="stylesheet" defer async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <title>Search</title>


    <script>
        function setFormat() {
            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            })
        }

        function setParam(val) {
            const button = document.getElementById("search-button");
            button.value = val;
        }

        async function searchProducts(arg) {

            const search = await fetch("./search-products.php?param=" + arg)
                .then((response) => response.json())
                .then(data => {
                    console.log(data);
                    if (data[0].code == 'NO') {
                        html = "<p>No results found</p>"
                    } else {
                        var html = "<div>";
                        html +=
                            "<table><tr class='head-row'><th>Product Code</th><th>Name</th><th>Price</th><th></th><th></th>"
                        for (let i = 0; i < data.length; i++) {
                            var image = data[i].image;
                            console.log(image);
                            let rawPrice = data[i].price;

                            html += "<tr class='body-row'><td>" + data[i].code + "</td>";
                            html += "<td>" + data[i].name + "</td>";
                            // html += "<td>" + data[i].price + "</td>";
                            html += "<td id='details-price' value='" + rawPrice + "'><p> $ " + parseFloat(rawPrice)
                                .toFixed(2).replace(
                                    /\d(?=(\d{3})+\.)/g,
                                    '$&,') +
                                "</p></td>";
                            html += "<td><img src='./" + image +
                                "' width='100px' id='details-image'  value='" + image + "'></td>";
                            html += "<td><a href='./product-details-onefee.php?product_id=" +
                                data[i].product_id +
                                "'><button id='details-button' value='" + data[i].product_id +
                                "' class='btn btn-primary' type='button'><i class='fa fa-eye'></i> Details</button></td>";

                        }
                        html += "</table>";
                        html += "</div>";
                        document.getElementById("search-results").innerHTML = html;
                        hideButton();
                        hidePrice();
                        hideImage();
                    }
                })
        }
    </script>


</head>
</?php include "nav.php" ?>

<body onload="searchProducts('<?php echo $searchTerm ?>')">
    <!-- <div class="container"> -->
    <?php include "./components/slider.php" ?>

    <!-- <input type="text" name="search_param" id="search_param" onchange="setParam(this.value)"></input>

        <button class="btn btn-info" id="search-button" value="polo" type="button"
            onclick="searchProducts(this.value)">Search

        </button><br>
        <p class='why-so-serious'>Text Search Product Code or Name</p> -->
    <hr>
    <div class="search-results" id="search-results"></div>
    <?php include "cartSlideout.php" ?>
    <!-- </div> -->



    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script> -->
</body>
<script>
    function hideButton() {
        console.log('hide button called');
        var buttonInfo = document.getElementById("details-button");
        if (buttonInfo.value == "0") {
            buttonInfo.classList.add("hidden");
        }
    }

    function hidePrice() {
        console.log('hide Price called');
        var priceInfo = document.getElementById("details-price");
        if (priceInfo.value == "0") {
            priceInfo.classList.add("hidden");
        }
    }

    function hideImage() {
        console.log('hide Image called');
        var imageInfo = document.getElementById("details-image");
        if (imageInfo.value == "100") {
            imageInfo.classList.add("hidden");
        }
    }
</script>

</html>

<style>
    @font-face {
        font-family: RoboCondensed;
        src: url('./fonts/RobotoCondensed-Regular.ttf');
    }

    body {
        overflow: hidden;
        font-family: RoboCondensed;
    }

    /* .navbar {
    display: flex;
    justify-content: center;
} */

    .search-results {
        /* background-color: #06060650; */
        background-color: #00000080;
        color: aliceblue;
        position: relative;
        z-index: 5;
        top: 150px;
        width: 100%;
        margin-left: 5px;
        margin-right: 5px;
        height: 100vh;
        overflow: auto;
    }

    .container {
        margin: 10px;
        margin-left: 25px;
    }

    table {
        padding: 20px 20px;
        color: aliceblue;
        /* min-height: 6rem; */
        font-size: 1.5rem;
        font-weight: 400;
        line-height: 1.25;
        margin-left: auto;
        margin-right: auto;
    }

    .head-row th {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid var(--bc-grey);
    }

    .body-row {
        text-align: center;

    }

    .body-row td {
        padding-top: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ffffff20;

    }

    .hidden {
        display: none;
    }

    img {
        margin-right: 15px;
        margin-left: 15px;
        transition: all .2s ease-in-out;
    }

    table tr td {
        padding: 20px;
    }

    /* img:hover {
    transform: scale(5.5);
    border: 1px solid var(--bc-grey);
    border-radius: 5px;
} */

    /* .why-so-serious {
    color: #adafae;
} */
</style>