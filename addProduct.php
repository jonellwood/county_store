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
    <meta name="Description" content="Enter your description here" />
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script> -->
    <!-- <link rel="stylesheet" id='test' href="https://berkeley-county.github.io/berkstrap/berkstrap-dark.css"> -->
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" defer async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <title>Title</title>
</head>

<body>
    <div class="container">

        <h4>This page will have to be behind some kind of log in</h4>

        <form name="addProduct" id="addProduct" action="add-product-to-db.php" method="POST">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <label class="sr-only" for="productCode">Product Code</label>
                    <input type="text" class="form-control mb-2" id="productCode" name="productCode"
                        placeholder="Enter Product Code">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="productName">Product Name</label>
                    <input type="text" class="form-control mb-2" id="productName" name="productName"
                        placeholder="Enter Product Name">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="price">Product Price</label>
                    <input type="text" class="form-control mb-2" id="price" name="price"
                        placeholder="Enter Product Price">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="description">Product Description</label>
                    <input type="text" class="form-control mb-2" id="description" name="description"
                        placeholder="Enter Product Description">
                </div>
                <div class="col-auto">
                    <hr>
                    <p>Select category for this product</p>
                    <?php
                    $sql = "SELECT * from producttypes";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        echo "<select id='productType' name='productType'>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=" . $row['productType_id'] . " '>" . $row['productType'] . "</option>";
                        }
                    }

                    ?>

                    </select>
                </div>

            </div>

            <div class="col-auto">
                <hr>
                <p>Select all sizes available for this product</p>
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
                <p>Select all colors available for this product</p>
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
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </div>
        </form>
    </div>


    </div>




    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script>
    <script src=" https://code.jquery.com/jquery-3.6.1.js"></script> -->
</body>

</html>
<style>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

body {
    position: relative;
    background-color: slategray;
    font-family: bcFont !important;
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
}

.box-holder {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;

}


.custom-control-input {
    padding-left: 10px;
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

}
</style>