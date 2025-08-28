<?php
// API endpoint to add a new product
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['product']['code'])) {
        throw new Exception('Product code is required');
    }

    if (empty($data['product']['name'])) {
        throw new Exception('Product name is required');
    }

    if (empty($data['product']['productType'])) {
        throw new Exception('Product type is required');
    }

    if (empty($data['colors']) || !is_array($data['colors'])) {
        throw new Exception('At least one color must be selected');
    }

    if (empty($data['sizes']) || !is_array($data['sizes'])) {
        throw new Exception('At least one size with price must be selected');
    }

    // Validate sizes have prices
    foreach ($data['sizes'] as $size) {
        if (empty($size['sizeId']) || !isset($size['price']) || $size['price'] <= 0) {
            throw new Exception('All sizes must have valid prices');
        }
    }

    // Set default vendor ID if not provided
    if (empty($data['product']['vendorId'])) {
        $data['product']['vendorId'] = 1;
    }

    $product = new Product();

    // Check if product code already exists
    $existingProduct = $product->getProductByCode($data['product']['code']);
    if ($existingProduct) {
        throw new Exception('Product code already exists');
    }

    // Add the product
    $result = $product->addProduct($data['product'], $data['colors'], $data['sizes']);

    if ($result['success']) {
        Logger::logError("Successfully added product: " . $data['product']['name'] . " (ID: " . $result['productId'] . ")");
        echo json_encode($result);
    } else {
        throw new Exception($result['message']);
    }
} catch (Exception $e) {
    Logger::logError("Error in add-product API: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
