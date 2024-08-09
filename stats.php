<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: Component file to load some products onto the home page for the user to see when first going to index.php. In the past it has loaded the 4 best selling items and had loaded 4 "random" items just for a change of scenery. 
Includes:   config.php for database connection
*/

session_start();

require_once 'config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());
// ! This query returns 4 "random" products 
// $idsql = "SELECT products_new.product_id, products_new.name, COUNT(order_details.product_id) AS count 
// FROM (
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
//     UNION
//     SELECT FLOOR(RAND() * 200) + 1 AS random_id 
//     FROM order_details
// ) AS random_products
// LEFT JOIN products_new ON products_new.product_id = random_products.random_id
// LEFT JOIN order_details ON order_details.product_id = random_products.random_id

// GROUP BY products_new.product_id, products_new.name
// ORDER BY RAND()
// LIMIT 4";

// ! This query is top 4 products with most orders
// $idsql = "SELECT order_details.product_id, products_new.name, COUNT(*) as count FROM order_details JOIN products_new on products_new.product_id=order_details.product_id GROUP BY product_id order by count DESC LIMIT 4";
// ! this query is top 4 products with most orders excluding hats because they make the page look weird
$idsql = "SELECT od.product_id, pn.name, COUNT(*) as count 
FROM order_details od
JOIN products_new pn on pn.product_id=od.product_id 
JOIN products_producttype pt on pt.product_id = od.product_id
WHERE pt.producttype_id != 3
GROUP BY od.product_id order by count DESC LIMIT 4";
$idstmt = $conn->prepare($idsql);
$idstmt->execute();
$idresult = $idstmt->get_result();

$felist = array();
if ($idresult->num_rows > 0) {
    while ($idrow = $idresult->fetch_array()) {
        // $felist[$idrow['product_id']] = $idrow['count'];
        array_push($felist, [
            'product_id' => $idrow['product_id'],
            'name' => $idrow['name'],
            'count' => $idrow['count'],
        ]);
    }
}
?>


<div class="container-fluid" id="products-container">
    <div class="card" id="featured-card" view-transition-group="image-transition">
        <a href="product-details.php?product_id=<?php echo $prorow["product_id"]; ?>">
            <img src="<?php echo $proImage; ?>" alt="product name" class="card-img-top" view-transition-old="image-transition">
            <div class="card-body featured">
                <p class="card-title"><?php echo $prorow["name"]; ?></p>

            </div>
            <p class="hot-item"><img alt="Custom badge" src="https://img.shields.io/badge/<?php echo $felist[$c]['count'] ?>-Ordered-red?style=social&logo=Clubhouse">
            </p>
        </a>
        <!-- <p class="hot-item"><?php echo $felist[$c]['count'] ?> ordered so far </p> -->
    </div>

</div>