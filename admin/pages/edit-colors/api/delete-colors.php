<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (empty($payload['ids']) || !is_array($payload['ids'])) {
        throw new Exception('No IDs supplied');
    }

    $product = new Product();
    $result = $product->deleteColors($payload['ids']);

    if (!$result['success']) {
        throw new Exception($result['message']);
    }

    echo json_encode($result);
} catch (Exception $e) {
    Logger::logError('Delete colors failure: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
