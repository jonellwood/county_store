<?php


require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Add products to the store database" />
    <link rel="icon" href="./product-admin/favicons/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" id='test' href="https://berkeley-county.github.io/berkstrap/berkstrap-dark.css">

    <script>
        function getMods(e) {
            // e.preventDefault();
            console.log('getMods clicked - no submit');
            var modFind = document.getElementById('find-mods');
            var container = document.getElementById('container');
            modFind.style.display = "block";
            modFind.style.position = "absolute";
            modFind.style.top = "0";
            modFind.style.zIndex = "1";
            container.style.display = "none";
        }
    </script>
    <title>Title</title>
</head>

<body>
    <div class="container" id="container">

        <h4>Enter Product Code, Name, Price and short description (255 characters or less). </h4>
        <!-- <a href="find-price-mod-ui.php" target="_blank"><button>Find Price Mod</button></a> -->

        <form name="addProduct" id="addProduct" action="add-product-to-db.php" method="POST" enctype="multipart/form-data">
            <div class=" form-row align-items-center">
                <div class="col-auto">
                    <label class="sr-only" for="productCode">Product Code</label>
                    <input type="text" class="form-control mb-2" id="productCode" name="productCode" placeholder="Enter Product Code">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="productName">Product Name</label>
                    <input type="text" class="form-control mb-2" id="productName" name="productName" placeholder="Enter Product Name">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="price">Product Price</label>
                    <input type="text" class="form-control mb-2" id="price" name="price" placeholder="Enter Product Price">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="description">Product Description</label>
                    <input type="text" class="form-control mb-2" id="description" name="description" placeholder="Enter Product Description">
                </div>
                <div class="col-auto options">
                    <hr>
                    <h5>Select options for this product</h5>
                    <?php
                    $sql = "SELECT * from producttypes";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        echo "<label for='productType'>Product Category</label>";
                        echo "<select id='productType' name='productType'>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=" . $row['productType_id'] . ">" . $row['productType'] . "</option>";
                        }
                    }

                    ?>

                    </select>
                    <span>---></span>

                    <label for="isFeatured">Is Featured?</label>
                    <select id="isFeatured" name="isFeatured">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                    <span>---></span>
                    <label for="vendor_id">Vendor</label>
                    <select id="vendor_id" name="vendor_id">
                        <option value="1">Low Country Native</option>
                    </select>
                    <span>---></span>

                    <?php
                    $mod_sql = "SELECT price_mod from price_mods";
                    $mod_stmt = $conn->prepare($mod_sql);
                    $mod_stmt->execute();
                    $mod_result = $mod_stmt->get_result();

                    if ($mod_result->num_rows > 0) {
                        echo "<label for='price_size_mod'>Price Mod Group</label>";
                        echo "<select id='price_size_mod' name='price_size_mod'>";
                        while ($mod_row = $mod_result->fetch_array()) {
                            echo "<option value=" . $mod_row['price_mod'] . ">Group " . $mod_row['price_mod'] . "</option>";
                        }
                        echo "</select>";
                    }

                    ?>

                    </select>
                    <a href="find-price-mod-ui.php" target="_blank"><button type="button" class="find_mod_btn">Find
                            Price Mod</button></a>
                </div>
                <hr>
                <div>
                    <h5>Select Filter Tags for this Product. Every product must have one of these options or the world
                        will explode.</h3>
                        <?php
                        $g_filterSql = "SELECT id, filter FROM uniform_orders.filters_gender";
                        $g_filterStmt = $conn->prepare($g_filterSql);
                        $g_filterStmt->execute();
                        $g_filterResult = $g_filterStmt->get_result();
                        if ($g_filterResult->num_rows > 0) {
                            echo "<label for='gender-filter'>Clothing Gender</label>";
                            echo "<select id='g_filter' name='g_filter'>";
                            while ($g_filterRow = $g_filterResult->fetch_assoc()) {
                                echo "<option value='" . $g_filterRow['id'] . "'>" . $g_filterRow['filter'] . "</option>";
                            }
                            echo "</select>";
                        };

                        ?>
                        <span>---></span>
                        <?php
                        $t_filterSql = "SELECT id, filter FROM uniform_orders.filters_type";
                        $t_filterStmt = $conn->prepare($t_filterSql);
                        $t_filterStmt->execute();
                        $t_filterResult = $t_filterStmt->get_result();
                        if ($t_filterResult->num_rows > 0) {
                            echo "<label for='type-filter'>Item Type</label>";
                            echo "<select id='t_filter' name='t_filter'>";
                            while ($t_filterRow = $t_filterResult->fetch_assoc()) {
                                echo "<option value='" . $t_filterRow['id'] . "'>" . $t_filterRow['filter'] . "</option>";
                            }
                            echo "</select>";
                        };
                        ?>
                        <span>---></span>
                        <?php
                        $s_filterSql = "SELECT id, filter FROM uniform_orders.filters_size";
                        $s_filterStmt = $conn->prepare($s_filterSql);
                        $s_filterStmt->execute();
                        $s_filterResult = $s_filterStmt->get_result();
                        if ($s_filterResult->num_rows > 0) {
                            echo "<label for='size-filter'>Item Size Range</label>";
                            echo "<select id='s_filter' name='s_filter'>";
                            while ($s_filterRow = $s_filterResult->fetch_assoc()) {
                                echo "<option value='" . $s_filterRow['id'] . "'>" . $s_filterRow['filter'] . "</option>";
                            }
                            echo "</select>";
                        };
                        ?>
                        <span>---></span>
                        <?php
                        $a_filterSql = "SELECT id, filter FROM uniform_orders.filters_sleeve";
                        $a_filterStmt = $conn->prepare($a_filterSql);
                        $a_filterStmt->execute();
                        $a_filterResult = $a_filterStmt->get_result();
                        if ($a_filterResult->num_rows > 0) {
                            echo "<label for='sleve-filter'>Item Sleeve Option</label>";
                            echo "<select id='a_filter' name='a_filter'>";
                            while ($a_filterRow = $a_filterResult->fetch_assoc()) {
                                echo "<option value='" . $a_filterRow['id'] . "'>" . $a_filterRow['filter'] . "</option>";
                            }
                            echo "</select>";
                        };
                        ?>
                </div>

                <!-- </div> -->


                <div class="col-auto">
                    <hr>
                    <h5>Select all sizes available for this product</h5>
                    <!-- <hr> -->
                    <div class="custom-control custom-checkbox box-holder">
                        <?php
                        $sql = "SELECT * from sizes";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<p>";
                                echo "<input type='checkbox' name='sizes[]' class='custom-control-input' id='" . $row['size_id'] . "' value=" . $row['size_id'] . ">";
                                echo "<label class='custom-control-label' for='" . $row['size_id'] . "'>" . $row['size'] . "</label>";
                                echo "</p>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="col-auto">
                    <hr>
                    <h5>Select all colors available for this product</h5>
                    <p><i class="fa fa-hand-point-right"></i>Colors can be added to this page from the Product Admin
                        Dashboard. A page reload is requred after
                        adding a color.</p>
                    <p><i class="fa fa-lightbulb"></i> Use <code>ctrl+f</code> to search for colors. Colors are
                        in alphabetical order on this
                        page</p>
                    <!-- <hr> -->
                    <div class="custom-control custom-checkbox box-holder">
                        <?php
                        $sql = "SELECT * from colors ORDER BY color ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<p class='color-holder'>";
                                echo "<input type='checkbox'name='colors[]' class='custom-control-input' id='" . $row['color_id'] . "' value=" . $row['color_id'] . ">";
                                echo "<label class='custom-control-label' for='" . $row['color_id'] . "'>" . $row['color'] . "</label>";
                                // echo "<input type='checkbox' class='custom-control-input' id='" . $row['color_id'] . "value=" . $row['color_id'] . ">";
                                echo "</p>";
                            }
                        }
                        ?>
                    </div>
                    <button type="button" onclick="hideUnchecked()" id="verifyButton">Verify Size & Color
                        Selections</button>
                    <button type="button" onclick="showAllCheckboxes()" id="showallButton" class="hidden">Edit Size or
                        Color Selections </button>
                </div>
                <hr>
                <div class="form-group">
                    <p>Specsheet name can only be productCode.pdf</p>
                    <p>For example: Product #1717 would have an specsheet name of 1717.pdf</p>
                    <label for="formFileMultiple" class="form-label"><b>Upload Specsheet PDF:</b></label>
                    <input type="file" class="form-control" name="files[]" multiple="" />

                </div>
                </br>
                <hr>
                <div class="form-group">
                    <p>Product Image should be 1200 x 1800 when possible. Image name can only be productCode.jpg.</p>
                    <p>For example: Product #1717 would have an image name of 1717.jpg</p>
                    <label for="formFileMultiple" class="form-label"><b>Upload Product Image:</b></label>
                    <input type="file" name="productImage" class="form-control" value="" />

                </div>
                <div class="col-auto">
                    <button type="submit" name="submit" class="btn btn-primary mb-2">Submit</button>
                </div>
        </form>

    </div>

    <!-- </div> -->



    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script>
    <script src=" https://code.jquery.com/jquery-3.6.1.js"></script> -->

    <script>
        // function to hide all unselected colors for easy verification of selections
        function hideUnchecked() {
            var uc = document.getElementsByClassName('custom-control-input');
            var vb = document.getElementById('verifyButton');
            var sb = document.getElementById('showallButton');
            for (let i = 0; i < uc.length; i++) {
                if (!uc[i].checked) {
                    uc[i].parentElement.classList.add('hidden');
                }
            }
            vb.classList.add('hidden');
            sb.classList.remove('hidden');
        }

        // function to undo the above action
        function showAllCheckboxes() {
            var uc = document.getElementsByClassName('custom-control-input');
            var vb = document.getElementById('verifyButton');
            var sb = document.getElementById('showallButton');
            for (let i = 0; i < uc.length; i++) {
                uc[i].parentElement.classList.remove('hidden');
            }
            sb.classList.add('hidden');
            vb.classList.remove('hidden');
        }
    </script>
</body>

</html>
<style>
    body {
        position: relative;
        background: rgb(247, 195, 177) !important;
        background: radial-gradient(circle, rgba(247, 195, 177, 1) 0%, rgba(235, 101, 54, 1) 50%, rgba(132, 62, 100, 1) 100%) !important;
        color: #111111;

    }

    .form-row {

        display: grid;
        grid-template-columns: auto;
        gap: 20px;


    }

    .btn {
        margin-top: 20px;
    }

    .container {
        max-width: 90vw;
        /* padding-right: calc(var(--bs-gutter-x) * 0.5); */
        /* padding-left: calc(var(--bs-gutter-x) * 0.5); */
        margin-right: 20px;
        margin-left: 20px;
        overflow: hidden;
    }

    .box-holder {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;

    }


    .custom-control-input {
        padding-left: 10px;
        width: 20px;
        height: 20px;
    }

    .custom-control-label {
        margin-left: 10px;
    }

    input.larger {
        width: 20px;
        height: 20px;

    }

    .color-holder {
        display: flex;
        align-items: center;
    }


    .options select {
        margin: 10px;
    }

    label {
        padding-right: 10px;
    }

    .find_mod_btn {
        background-color: #53d769;
        background-image: url('radar_sm.jpg');
    }

    .fa {
        color: yellow;
        font-size: larger;
        padding-right: 10px;
    }

    code {
        color: #2C001E;
        font-weight: bolder;
        background-color: #ffffff50;
    }

    .btn-primary {
        background-color: #77216F;
    }

    .btn-primary:hover {
        background-color: #772953 !important;
    }

    input[type="checkbox"]:checked+.custom-control-label {
        background-color: #53d769;
    }

    .hidden {
        display: none;
    }
</style>