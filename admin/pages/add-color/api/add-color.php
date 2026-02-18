<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

include_once '../../../../rootConfig.php';
include_once APP_ROOT . '/classes/Product.php';
include_once APP_ROOT . '/classes/Logger.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON payload');
    }

    $colorName = isset($data['color']) ? trim($data['color']) : '';

    if ($colorName === '') {
        throw new Exception('Color name is required');
    }

    if (empty($data['p_hex'])) {
        throw new Exception('Primary hex is required');
    }

    $colorPayload = [
        'color' => $colorName,
        'p_hex' => $data['p_hex'],
        's_hex' => isset($data['s_hex']) ? $data['s_hex'] : null,
        't_hex' => isset($data['t_hex']) ? $data['t_hex'] : null
    ];

    $product = new Product();
    $result = $product->addColor($colorPayload);

    if ($result['success']) {
        echo json_encode($result);
    } else {
        throw new Exception($result['message']);
    }
} catch (Exception $e) {
    Logger::logError('Error in add-color API: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
