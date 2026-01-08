<?php
// Color Manager API - Berkeley County Store Admin
session_start();
header('Content-Type: application/json');

// Security checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
    exit;
}

// Database connection
require_once '../../../config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        handleAddColor($conn);
        break;
    case 'list':
        handleListColors($conn);
        break;
    case 'delete':
        handleDeleteColor($conn);
        break;
    case 'update':
        handleUpdateColor($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$conn->close();

function handleAddColor($conn)
{
    $color = trim($_POST['color'] ?? '');
    $p_hex = trim($_POST['p_hex'] ?? '');
    $s_hex = trim($_POST['s_hex'] ?? '');
    $t_hex = trim($_POST['t_hex'] ?? '');

    // Validation
    if (empty($color)) {
        echo json_encode(['success' => false, 'message' => 'Color name is required']);
        return;
    }

    if (empty($p_hex) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $p_hex)) {
        echo json_encode(['success' => false, 'message' => 'Valid primary hex color is required']);
        return;
    }

    // Validate secondary hex if provided
    if (!empty($s_hex) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $s_hex)) {
        echo json_encode(['success' => false, 'message' => 'Invalid secondary hex color']);
        return;
    }

    // Validate tertiary hex if provided
    if (!empty($t_hex) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $t_hex)) {
        echo json_encode(['success' => false, 'message' => 'Invalid tertiary hex color']);
        return;
    }

    // Check if color already exists
    $checkStmt = $conn->prepare("SELECT color_id FROM colors WHERE color = ?");
    $checkStmt->bind_param("s", $color);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A color with this name already exists']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();

    // Insert new color
    $stmt = $conn->prepare("INSERT INTO colors (color, p_hex, s_hex, t_hex) VALUES (?, ?, ?, ?)");

    // Convert empty strings to NULL for optional fields
    $s_hex_value = !empty($s_hex) ? $s_hex : null;
    $t_hex_value = !empty($t_hex) ? $t_hex : null;

    $stmt->bind_param("ssss", $color, $p_hex, $s_hex_value, $t_hex_value);

    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Color added successfully',
            'color_id' => $newId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add color: ' . $stmt->error
        ]);
    }

    $stmt->close();
}

function handleListColors($conn)
{
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build query
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt = $conn->prepare("
            SELECT color_id, color, p_hex, s_hex, t_hex 
            FROM colors 
            WHERE color LIKE ? OR p_hex LIKE ? OR s_hex LIKE ? OR t_hex LIKE ?
            ORDER BY color ASC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $limit, $offset);
    } else {
        $stmt = $conn->prepare("
            SELECT color_id, color, p_hex, s_hex, t_hex 
            FROM colors 
            ORDER BY color ASC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $colors = [];
    while ($row = $result->fetch_assoc()) {
        $colors[] = $row;
    }

    echo json_encode([
        'success' => true,
        'colors' => $colors,
        'count' => count($colors)
    ]);

    $stmt->close();
}

function handleDeleteColor($conn)
{
    $color_id = (int)($_POST['color_id'] ?? 0);

    if ($color_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid color ID']);
        return;
    }

    // Check if color is in use (you might want to add this check)
    // For now, we'll allow deletion

    $stmt = $conn->prepare("DELETE FROM colors WHERE color_id = ?");
    $stmt->bind_param("i", $color_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Color deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Color not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete color: ' . $stmt->error
        ]);
    }

    $stmt->close();
}

function handleUpdateColor($conn)
{
    // TODO: Implement update functionality
    echo json_encode(['success' => false, 'message' => 'Update functionality not yet implemented']);
}
