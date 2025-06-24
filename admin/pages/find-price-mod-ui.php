<?php

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="./product-admin/favicons/favicon.ico">
</head>





<body>
    <p>Enter the price for each size for the product in question. IF the size does not exist (i.e. the do not sell 6XL
        leave as 0.00)</p>
    <hr>
    <form method="POST" action="price-mod-fetch.php">
        <div class="form">
            <label for="xsprice">XS Price:</label>
            <input type="text" id="xsprice" name="xsprice" value=0 required>

            <label for="sprice">S Price:</label>
            <input type="text" id="sprice" name="sprice" value=0 required>

            <label for="mprice">M Price:</label>
            <input type="text" id="mprice" name="mprice" value=0 required>

            <label for="lprice">L Price:</label>
            <input type="text" id="lprice" name="lprice" value=0 required>

            <label for="xlprice">XL Price:</label>
            <input type="text" id="xlprice" name="xlprice" value=0 required>

            <label for="xxlprice">2XL Price:</label>
            <input type="text" id="xxlprice" name="xxlprice" value=0 required>

            <label for="xxxlprice">3XL Price:</label>
            <input type="text" id="xxxlprice" name="xxxlprice" value=0 required>

            <label for="xxxxlprice">4XL Price:</label>
            <input type="text" id="xxxxlprice" name="xxxxlprice" value=0 required>

            <label for="xxxxxlprice">5XL Price:</label>
            <input type="text" id="xxxxxlprice" name="xxxxxlprice" value=0 required>

            <label for="xxxxxxlprice">6XL Price:</label>
            <input type="text" id="xxxxxxlprice" name="xxxxxxlprice" value=0 required>

            <label for="xxxxxxxlprice">7XL Price:</label>
            <input type="text" id="xxxxxxxlprice" name="xxxxxxxlprice" value=0 required>

            <label for="xxxxxxxxlprice">8XL Price:</label>
            <input type="text" id="xxxxxxxxlprice" name="xxxxxxxxlprice" value=0 required>

            <label for="xxxxxxxxxlprice">9XL Price:</label>
            <input type="text" id="xxxxxxxxxlprice" name="xxxxxxxxxlprice" value=0 required>

            <label for="xxxxxxxxxxlprice">10XL Price:</label>
            <input type="text" id="xxxxxxxxxxlprice" name="xxxxxxxxxxlprice" value=0 required>
        </div>
        <hr>
        <div class="form">
            <label for="ltprice">LT Price:</label>
            <input type="text" id="ltprice" name="ltprice" value=0 required>
            <label for="xltprice">XLT Price:</label>
            <input type="text" id="xltprice" name="xltprice" value=0 required>
            <label for="xxltprice">2XLT Price:</label>
            <input type="text" id="xxltprice" name="xxltprice" value=0 required>
            <label for="xxxltprice">3XLT Price:</label>
            <input type="text" id="xxxltprice" name="xxxltprice" value=0 required>
            <label for="xxxxltprice">4XLT Price:</label>
            <input type="text" id="xxxxltprice" name="xxxxltprice" value=0 required>
            <label for="naprice">NA Price:</label>
            <input type="text" id="naprice" name="naprice" value=0 required>
        </div>
        <hr>
        <input type="submit" name="submit" class="btn btn-primary mb-2">
        <input type="reset" class="btn btn-danger mb-2">
    </form>
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
    margin-top: 50px;
} */

.form {
    max-width: 1200px;
    display: grid;
    grid-template-columns: auto auto auto auto auto auto;
    gap: 20px;
}
</style>