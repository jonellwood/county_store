<?php
/*
Created: 2026/02/11
Purpose: Validate employee information against emp_sync table
Organization: Berkeley County IT Department
*/

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Get and sanitize input
// $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$emp_number = isset($_POST['emp_number']) ? trim($_POST['emp_number']) : '';

// Validate input
if (empty($emp_number)) {
    echo json_encode(['success' => false, 'error' => 'Employee number is required']);
    exit();
}

// Query emp_sync table
$stmt = $conn->prepare("SELECT empNumber, empName, email, deptName, deptNumber, separation_date
                        FROM uniform_orders.emp_sync 
                        WHERE empNumber = ?");
$stmt->bind_param("s", $emp_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Employee not found or no longer active. Please verify your employee number or contact the help desk directly.'
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Check for separation date - if not null, employee is separated
if (!is_null($employee['separation_date'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Your request could not be processed. Please contact the help desk directly at store@berkeleycountysc.gov.'
    ]);
    $conn->close();
    exit();
}

$conn->close();

// Return employee information
echo json_encode([
    'success' => true,
    'data' => [
        'emp_number' => $employee['empNumber'],
        'full_name' => trim($employee['empName']),
        'email' => $employee['email'] ?? '',
        'dept_name' => $employee['deptName'] ?? '',
        'dept_number' => $employee['deptNumber'] ?? '',
        'has_email' => !empty($employee['email'])
    ]
]);
