<?php
// API endpoint to fetch all options needed for the add product form
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    $product = new Product();

    // Fetch all required data
    $colors = $product->getColors();
    $sizes = $product->getSizes();
    $productTypes = $product->getActiveProductTypes();

    // Return success response
    echo json_encode([
        'success' => true,
        'colors' => $colors,
        'sizes' => $sizes,
        'productTypes' => $productTypes
    ]);
} catch (Exception $e) {
    Logger::logError("Error fetching options: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch options',
        'error' => $e->getMessage()
    ]);
}
