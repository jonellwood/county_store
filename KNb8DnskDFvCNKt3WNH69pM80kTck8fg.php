<?php
session_start();

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-3.13.0.min.css" />
    <link href="./inventory/BCG/simple-line-icons/css/simple-line-icons.css" rel="stylesheet" type="text/css">
    <link rel="icon" href="./product-admin/favicons/favicon.ico">
    <title>Product Admin Dashoard</title>

    <script>
        async function addColor() {
            var color = document.getElementById('colorName').value;
            var resetBtn = document.getElementById('resetButton');
            var addBtn = document.getElementById('addColorButton');
            // console.log(color);
            const data = await fetch('add-color-database.php?colorName=' + color)
                .then((response) => response.json())
                .then(data => {
                    // console.log(data);
                    var html = "<p>" + data[0].res + "<p>";
                    document.getElementById('res-holder').innerHTML = html;
                    resetBtn.classList.remove('hidden');
                    addBtn.classList.add('hidden');
                })
        }

        function resetText() {
            // console.log('Resetting as ASAP as possible');
            var resetBtn = document.getElementById('resetButton');
            var colorForm = document.getElementById('colorForm');
            var toReset = document.getElementById('res-holder');
            var addBtn = document.getElementById('addColorButton');
            var resetText = " ";
            // console.log(toReset.innerHTML);
            toReset.innerHTML = resetText;
            colorForm.reset();
            resetBtn.classList.add('hidden');
            addBtn.classList.remove('hidden');

        }
    </script>
</head>

<body>
    <h4>County Store Products Admin Dashboard</h4>
    <div class="things-grid">
        <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./update-prod-status.php' target="_blank"><i class="icon-pencil m-auto text-primary"></i> Update Product Status</a>
                    </button>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button>
                        <a href='./KN6EVPXT3B9FHT1TEC22.php' target="_blank"><i class="icon-magnifier-add m-auto text-primary"></i> Add Product</a>
                    </button>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                <div class="features-icons-icon d-flex">
                    <button onclick="addColor()" id='addColorButton'><i class="icon-check m-auto text-primary"></i>
                        Check or Add
                        Color</button>
                </div>
            </div>
            <!-- <button onclick="addColor()" id='addColorButton'>Add Color</button> -->
            <form id='colorForm'>
                <label for='colorName'>Enter color to check</label>
                <input name='colorName' id='colorName'>
            </form>
            <p>If color is not in database it will be added</p>
            <button type="button" onclick="resetText()" class='hidden' id='resetButton'>Reset</button>
            <p id='res-holder'></p>
        </div>
    </div>

</body>

</html>
<style>
    html {
        background: rgb(247, 195, 177);
        background: radial-gradient(circle, rgba(247, 195, 177, 1) 0%, rgba(235, 101, 54, 1) 50%, rgba(132, 62, 100, 1) 100%);

    }

    body {
        display: grid;
        grid-template-columns: auto;
        justify-items: center;
        margin-top: 40px;
        /* margin-left: 40px; */
        /* margin-right: 40px; */
    }

    h4 {
        text-align: center;
    }

    .things-grid {
        display: grid;
        grid-template-columns: 400px 400px 400px;
        gap: 25px;
    }

    .card {

        padding: 20px;
        width: 390px;
        height: 390px;
        border: solid 1px #77216F;
    }

    button {
        color: #7d42b8;
    }

    a {
        text-decoration: none !important;

    }

    .hidden {
        visibility: hidden;
    }
</style>