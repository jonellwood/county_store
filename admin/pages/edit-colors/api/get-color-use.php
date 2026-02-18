<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    $product = new Product();
    $usage = $product->getColorUsage();

    echo json_encode([
        'success' => true,
        'usage' => $usage
    ]);
} catch (Exception $e) {
    Logger::logError('Edit colors get-color-usage failure: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load colors'
    ]);
}
