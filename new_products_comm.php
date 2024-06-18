<?php

require_once "config.php";
($conn = new mysqli($host, $user, $password, $dbname, $port, $socket)) or
    die("Could not connect to the database server" . mysqli_connect_error());

$sql = "SELECT p.product_id, p.code, p.name, p.image, p.description 
from products_new p 
JOIN products_active a on a.product_id = p.product_id 
JOIN products_communications c on c.product_id = p.product_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$products = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        array_push($products, [
            'product_id' => $row['product_id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'image' => $row['image'],
            'description' => $row['description'],

        ]);
    }
}

?>
<div class="container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <!-- <th>Description</th> -->

                <!-- <th>Image</th> -->
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($products as $product) {
                echo '<tr>';
                echo '<td>' . $product["product_id"] . '</td>';
                echo '<td>' . $product["code"] . '</td>';
                echo '<td>' . $product["name"] . '</td>';
                // echo '<td>' . $product["description"] . '</td>';

                // echo '<td><img src="' . $product['image'] . '"/></td>';
                echo '</tr>';
            }

            ?>
        </tbody>
    </table>

</div>

<style>
    .container {
        margin: 20px;
    }

    table {
        max-width: 90%;
    }

    table tr td {
        /* text-align: center; */
        padding: 5px;
    }

    /* img {
        max-width: 100px;
    } */
</style>