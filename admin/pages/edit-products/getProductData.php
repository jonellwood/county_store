<?php
// Created: 2025/04/10 09:07:25
// Last modified: 2025/04/10 12:24:09


include_once '../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

$productId = $_GET['product_id'];
Logger::logError("Get Product Data for Product ID: " . $productId);

$product = new Product();
$prodData = $product->getProductById($productId);
$colors = $product->getProductColorsByProductID($productId);
$sizes = $product->getProductSizesByProductID($productId);
$prices = $product->getProductPricesByProductID($productId);

$allColors = $product->getColors();
$allSizes = $product->getSizes();
$prodTypes = $product->getActiveProductTypes();

echo json_encode(array("prodData" => $prodData, "prodColors" => $colors, "prodSizes" => $sizes, "prodPrices" => $prices, "allColors" => $allColors, "allSizes" => $allSizes, "prodTypes" => $prodTypes));
