<?php
/*
Created: 2025/08/28 14:30:00
Last modified: 2025/08/28 15:14:51
Purpose: Save product data (colors and sizes) for edit-products route
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';
include_once APP_ROOT . '/classes/Layout.php';

// Check if user is logged in
$loggedIn = Layout::confirmLoggedIn();
if (!$loggedIn) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$product_id = $data['product_id'] ?? null;
$colors = $data['colors'] ?? [];
$sizes = $data['sizes'] ?? [];

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

try {
    $product = new Product();

    // Log the update attempt
    Logger::logError("Product update attempt for ID: " . $product_id . " by user: " . ($_SESSION['user_id'] ?? 'unknown'));

    // Use the Product class method to update
    $result = $product->updateProduct($product_id, $colors, $sizes);

    if ($result['success']) {
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'product_id' => $product_id
        ]);
    } else {
        // Return error response
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    // Log the error
    Logger::logError("Product update failed for ID: " . $product_id . " - Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected error: ' . $e->getMessage()
    ]);
}
