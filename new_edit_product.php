<?php

require_once "config.php";
($conn = new mysqli($host, $user, $password, $dbname, $port, $socket)) or
    die("Could not connect to the database server" . mysqli_connect_error());

$id = $_GET['id'];

$sql = "SELECT 
    p.product_id, 
    p.code, 
    p.name, 
    p.image, 
    p.description, 
    ps.size_id, 
    s.size_name,
    pr.price,
    pr.vendor_id,
    v.name as vendor_name,
    pr.price_id
FROM 
    products_new p 
JOIN 
    products_sizes_new ps ON ps.product_id = p.product_id
JOIN 
    sizes_new s ON s.size_id = ps.size_id

JOIN 
    (SELECT DISTINCT product_id, size_id, price, vendor_id, price_id FROM prices) pr ON pr.product_id = p.product_id AND pr.size_id = ps.size_id
JOIN
    vendors v on v.id = pr.vendor_id
WHERE p.product_id = $id
ORDER BY p.code, pr.vendor_id
    ";
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
            'size' => $row['size_name'],
            'size_id' => $row['size_id'],
            'price' => $row['price'],
            'vendor_id' => $row['vendor_id'],
            'vendor_name' => $row['vendor_name'],
            'price_id' => $row['price_id']
        ]);
    }
}
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
?>
<div class="container">
    <form action="new_update_prices_db.php" method="post">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Size ID</th>
                    <th>Price</th>
                    <th>Vendor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($products as $product) {
                    echo '<tr>';
                    echo '<td><input type="hidden" name="price_id[]" value="' . $product["price_id"] . '">' . $product["product_id"] . '</td>';
                    echo '<td>' . $product["code"] . '</td>';
                    echo '<td>' . $product["name"] . '</td>';
                    echo '<td>' . $product["size"] . '</td>';
                    echo '<td>' . $product["size_id"] . '</td>';
                    echo '<td><input type="text" name="price[]" value="' . $product["price"] . '"></td>';
                    echo '<td>' . $product["vendor_name"] . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <input type="submit" value="Update Prices">
    </form>
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

    .product-line {
        display: flex;
        flex-direction: row;
        line-height: 1.5;
        align-items: center;
        gap: 10px;
    }

    /* img {
        max-width: 100px;
    } */
</style>