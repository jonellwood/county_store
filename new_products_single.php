<?php

require_once "config.php";
($conn = new mysqli($host, $user, $password, $dbname, $port, $socket)) or
    die("Could not connect to the database server" . mysqli_connect_error());


$q = $_GET['q'];
// $product_id = $_REQUEST['product_id'];
$qint = intval($q);
// echo "q: " . htmlspecialchars($q) . "<br>";
// echo "qint: " . $qint . "<br>";
// echo "gettype(qint): " . gettype($qint) . "<br>";

$sql = "SELECT 
    p.product_id, 
    p.code, 
    p.name, 
    p.image, 
    p.description, 
    -- ps.size_id, 
    s.size_name,
    pr.price,
    pr.vendor_id,
    pr.size_id
FROM 
    products_new p 
-- JOIN 
--     products_sizes_new ps ON ps.product_id = p.product_id
-- JOIN 
--     sizes_new s ON s.size_id = ps.size_id
JOIN 
    (SELECT DISTINCT product_id, size_id, price, vendor_id FROM prices) pr ON pr.product_id = p.product_id 
    -- AND pr.size_id = ps.size_id
JOIN 
    sizes_new s ON s.size_id = pr.size_id
-- where p.product_id = $qint
where p.product_id = ?
-- where p.keep = 1
-- GROUP BY p.product_id
order by pr.vendor_id
    ";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $qint);

$stmt->execute();
if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}
$result = $stmt->get_result();
if ($result === false) {
    die('Get result failed: ' . htmlspecialchars($stmt->error));
}
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
            'vendor_id' => $row['vendor_id']

        ]);
    }
}
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
?>
<div class="container">
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Size</th>
                <th>Size ID</th>
                <th>Price</th>
                <th>Vendor ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($products as $product) {

                echo '<tr>';
                echo '<td>' . $product["product_id"] . '</td>';
                echo '<td>' . $product["code"] . '</td>';
                echo '<td>' . $product["name"] . '</td>';
                echo '<td>' . $product["size"] . '</td>';
                echo '<td>' . $product["size_id"] . '</td>';
                echo '<td>' . $fmt->formatCurrency($product["price"], 'USD') . '</td>';
                echo '<td>' . $product["vendor_id"] . '</td>';
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