<?php
if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" /> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="./favicons/favicon.ico">
</head>

<body>
    <?php include "nav.php" ?>
    <h1>WARNING: DO NOT USE THIS FORM UNLESS YOU ARE CERTAIN THERE IS NOT AN EXISTING MATCHING PRICE MOD</h1>
    <p>You can check <a href="https://store.berkeleycountysc.gov/product-admin/find-price-mod-ui.php"
            target="_blank">HERE</a></p>
    <p>Enter the price INCREASE for each size for the product in question. IF the price does not increase or if that
        size is not for
        sale (i.e. the do not sell 6XL leave as 0.00)</p>
    <hr>
    <form method="POST" action="price-mod-add.php">
        <div class="form">
            <label for="xsincrease">XS Increase:</label>
            <input type="number" id="xsincrease" name="xsincrease" value=0 required>

            <label for="sincrease">S Increase:</label>
            <input type="number" id="sincrease" name="sincrease" value=0 required>

            <label for="mincrease">M Increase:</label>
            <input type="number" id="mincrease" name="mincrease" value=0 required>

            <label for="lincrease">L Increase:</label>
            <input type="number" id="lincrease" name="lincrease" value=0 required>

            <label for="xlincrease">XL Increase:</label>
            <input type="number" id="xlincrease" name="xlincrease" value=0 required>

            <label for="xxlincrease">2XL Increase:</label>
            <input type="number" id="xxlincrease" name="xxlincrease" value=0 required>

            <label for="xxxlincrease">3XL Increase:</label>
            <input type="number" id="xxxlincrease" name="xxxlincrease" value=0 required>

            <label for="xxxxlincrease">4XL Increase:</label>
            <input type="number" id="xxxxlincrease" name="xxxxlincrease" value=0 required>

            <label for="xxxxxlincrease">5XL Increase:</label>
            <input type="number" id="xxxxxlincrease" name="xxxxxlincrease" value=0 required>

            <label for="xxxxxxlincrease">6XL Increase:</label>
            <input type="number" id="xxxxxxlincrease" name="xxxxxxlincrease" value=0 required>

            <label for="xxxxxxxlincrease">7XL Increase:</label>
            <input type="number" id="xxxxxxxlincrease" name="xxxxxxxlincrease" value=0 required>

            <label for="xxxxxxxxlincrease">8XL Increase:</label>
            <input type="number" id="xxxxxxxxlincrease" name="xxxxxxxxlincrease" value=0 required>

            <label for="xxxxxxxxxlincrease">9XL Increase:</label>
            <input type="number" id="xxxxxxxxxlincrease" name="xxxxxxxxxlincrease" value=0 required>

            <label for="xxxxxxxxxxlincrease">10XL Increase:</label>
            <input type="number" id="xxxxxxxxxxlincrease" name="xxxxxxxxxxlincrease" value=0 required>
        </div>
        <hr>
        <div class="form">
            <label for="ltincrease">LT Increase:</label>
            <input type="number" id="ltincrease" name="ltincrease" value=0 required>
            <label for="xltincrease">XLT Increase:</label>
            <input type="number" id="xltincrease" name="xltincrease" value=0 required>
            <label for="xxltincrease">2XLT Increase:</label>
            <input type="number" id="xxltincrease" name="xxltincrease" value=0 required>
            <label for="xxxltincrease">3XLT Increase:</label>
            <input type="number" id="xxxltincrease" name="xxxltincrease" value=0 required>
            <label for="xxxxltincrease">4XLT Increase:</label>
            <input type="number" id="xxxxltincrease" name="xxxxltincrease" value=0 required>
            <label for="naincrease">NA Increase:</label>
            <input type="number" id="naincrease" name="naincrease" value=0 required>
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
        font-family: 'Ubuntu', sans-serif;
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

    h1 {
        text-align: center;
        background-color: tomato;
        color: whitesmoke;
    }

    a {
        text-decoration: none;
        color: limegreen;
        font-weight: bold;
        cursor: pointer;
    }
</style>