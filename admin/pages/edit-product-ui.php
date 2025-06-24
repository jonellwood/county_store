<?php
// Created: 2025/04/09 11:34:08
// Last modified: 2025/04/09 13:17:35
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

require_once '../../rootConfig.php';


echo "<p>" . APP_ROOT . "</p>";

// include_once APP_ROOT . '/data/config.php';
include_once APP_ROOT . '/classes/Logger.php';
include_once APP_ROOT . '/classes/Product.php';

// Logger::logError("Edit products UI page loaded.");

$product = new Product();

$products = $product->getProducts();

Logger::logError("Products fetched clown! ");
// echo "<pre>";
// print_r($products);
// echo "</pre>";