<?php

session_start();

require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
?>
<form action='write-sizes-to-db.php' method='POST'>
    <div class="col-auto">
        <p>Select Product to be updated</p>
        <?php
        $sql = "SELECT products.product_id, products.code, products.name from products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $products_result = $stmt->get_result();
        if ($products_result->num_rows > 0) {
            echo "<select name='product'>";
            while ($row = $products_result->fetch_assoc()) {
                echo "<option value='" . $row['product_id'] . "'>" . $row['code'] . " " . $row['name'] . "</option>";
            }
            echo "</select>";
        }
        ?>
    </div>
    <hr>
    <div class="col-auto">

        <p>Please select the available sizes for this product</p>
        <div class="custom-control custom-checkbox box-holder">
            <?php
            $sql = "SELECT * from sizes order by size ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $size_result = $stmt->get_result();
            if ($size_result->num_rows > 0) {
                while ($row = $size_result->fetch_assoc()) {
                    echo "<div class='color-holder'>";
                    echo "<input type='checkbox' name='sizes[]' class='custom-control-input' id='" . $row['size_id'] . "' value=" . $row['size_id'] . ">";
                    echo "<label class='custom-control-lael' for='" . $row['size_id'] . "'>" . $row['size'] . "</label>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    <button type="submit">Update</button>
</form>
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
    grid-template-rows: 1fr 1fr 1fr 1fr 1fr 1fr;

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