<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    $product = new Product();
    $colors = $product->getColors();

    echo json_encode([
        'success' => true,
        'colors' => $colors
    ]);
} catch (Exception $e) {
    Logger::logError('Error fetching color list: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch colors'
    ]);
}
