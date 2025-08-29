<?php

/**
 * Modern Edit Users API - Berkeley County Store Admin
 * Created: 2025/08/29
 * RESTful API for user management functionality
 */

// Include database connection
include('../DBConn.php');

// Start session and check authentication
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check admin role
if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

// Set JSON content type
header('Content-Type: application/json');

// Get the action parameter
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getUsers':
            getUsers($conn);
            break;

        case 'getFilters':
            getFilters($conn);
            break;

        case 'checkRoleConflicts':
            checkRoleConflictsEndpoint($conn);
            break;

        case 'updateUser':
            updateUser($conn);
            break;

        case 'createUser':
            createUser($conn);
            break;

        case 'deleteUser':
            deleteUser($conn);
            break;

        case 'exportUsers':
            exportUsers($conn);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Edit Users API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Get all users with their details, roles, and departments
 */
function getUsers($conn)
{
    $sql = "SELECT 
                u.emp_num as id,
                u.emp_num,
                u.empName,
                u.email,
                u.role_name,
                er.empNumber as employee_number,
                er.deptNumber as dept_id,
                er.email as emp_email,
                COALESCE(er.empName, u.empName) as first_name,
                '' as last_name,
                '' as phone,
                CASE 
                    WHEN u.role_name = 'Administrator' THEN 1
                    ELSE 2 
                END as role_id,
                1 as active,
                NOW() as created_at,
                d.dep_name as dept_name,
                -- Department leadership positions
                CASE 
                    WHEN d1.dep_num IS NOT NULL THEN CONCAT('Head: ', d1.dep_name)
                    WHEN d2.dep_num IS NOT NULL THEN CONCAT('Assistant: ', d2.dep_name)
                    WHEN d3.dep_num IS NOT NULL THEN CONCAT('Asset Mgr: ', d3.dep_name)
                    ELSE NULL
                END as leadership_role,
                GROUP_CONCAT(
                    DISTINCT CASE 
                        WHEN d1.dep_num IS NOT NULL THEN CONCAT('Head: ', d1.dep_name)
                        WHEN d2.dep_num IS NOT NULL THEN CONCAT('Assistant: ', d2.dep_name)
                        WHEN d3.dep_num IS NOT NULL THEN CONCAT('Asset Mgr: ', d3.dep_name)
                        ELSE NULL
                    END SEPARATOR ', '
                ) as all_leadership_roles
            FROM user_ref u
            LEFT JOIN emp_ref er ON u.emp_num = er.empNumber
            LEFT JOIN departments d ON CAST(er.deptNumber AS CHAR) = CAST(d.dep_num AS CHAR)
            LEFT JOIN departments d1 ON CAST(u.emp_num AS CHAR) = CAST(d1.dep_head AS CHAR)
            LEFT JOIN departments d2 ON CAST(u.emp_num AS CHAR) = CAST(d2.dep_assist AS CHAR)
            LEFT JOIN departments d3 ON CAST(u.emp_num AS CHAR) = CAST(d3.dep_asset_mgr AS CHAR)
            GROUP BY u.emp_num
            ORDER BY u.empName ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Split full name if it contains space
        $nameParts = explode(' ', trim($row['empName']));
        if (count($nameParts) > 1) {
            $row['first_name'] = $nameParts[0];
            $row['last_name'] = implode(' ', array_slice($nameParts, 1));
        } else {
            $row['first_name'] = $row['empName'];
            $row['last_name'] = '';
        }

        // Use employee email if available, otherwise use user_ref email
        $row['email'] = $row['emp_email'] ?: $row['email'];

        // Clean up unused fields
        unset($row['emp_email']);

        $users[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ]);
}

/**
 * Get filter options for roles and departments
 */
function getFilters($conn)
{
    // Check if roles table exists, if not fall back to old system
    $tableCheck = $conn->query("SHOW TABLES LIKE 'roles'");
    $useNewRoleSystem = $tableCheck->num_rows > 0;

    if ($useNewRoleSystem) {
        // Check if the roles table has the new columns
        $columnsCheck = $conn->query("SHOW COLUMNS FROM roles LIKE 'hierarchy_level'");
        $hasNewColumns = $columnsCheck->num_rows > 0;

        if ($hasNewColumns) {
            // New role system - Get roles from dedicated roles table with all columns
            $rolesSql = "SELECT 
                            r.role_id,
                            r.role_name,
                            COALESCE(r.role_description, '') as role_description,
                            r.department_id,
                            d.dep_name as department_name,
                            COALESCE(r.hierarchy_level, 0) as hierarchy_level,
                            COALESCE(r.is_department_specific, FALSE) as is_department_specific
                         FROM roles r
                         LEFT JOIN departments d ON r.department_id = d.dep_num
                         WHERE COALESCE(r.is_active, TRUE) = TRUE
                         ORDER BY COALESCE(r.hierarchy_level, 0) DESC, r.role_name";
        } else {
            // Roles table exists but doesn't have new columns yet - use basic query
            $rolesSql = "SELECT 
                            r.role_id,
                            r.role_name,
                            '' as role_description,
                            NULL as department_id,
                            '' as department_name,
                            0 as hierarchy_level,
                            FALSE as is_department_specific
                         FROM roles r
                         ORDER BY r.role_name";
        }
    } else {
        // Fallback to old system
        $rolesSql = "SELECT DISTINCT 
                        CASE 
                            WHEN role_name = 'Administrator' THEN 1
                            ELSE 2 
                        END as role_id,
                        role_name,
                        '' as role_description,
                        NULL as department_id,
                        '' as department_name,
                        0 as hierarchy_level,
                        FALSE as is_department_specific
                     FROM user_ref 
                     WHERE role_name IS NOT NULL
                     ORDER BY role_name";
    }

    $rolesStmt = $conn->prepare($rolesSql);
    $rolesStmt->execute();
    $rolesResult = $rolesStmt->get_result();

    $roles = [];
    while ($row = $rolesResult->fetch_assoc()) {
        $roles[] = $row;
    }

    // Get departments
    $deptsSql = "SELECT dep_num as dept_id, dep_name 
                 FROM departments 
                 ORDER BY dep_name";

    $deptsStmt = $conn->prepare($deptsSql);
    $deptsStmt->execute();
    $deptsResult = $deptsStmt->get_result();

    $departments = [];
    while ($row = $deptsResult->fetch_assoc()) {
        $departments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'roles' => $roles,
            'departments' => $departments,
            'useNewRoleSystem' => $useNewRoleSystem
        ]
    ]);
}

/**
 * Check for role conflicts before assignment
 */
function checkRoleConflicts($conn, $userId, $roleId, $userDeptId = null)
{
    $conflicts = [];

    // Check if roles table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'roles'");
    if ($tableCheck->num_rows === 0) {
        return $conflicts; // No conflicts to check with old system
    }

    // Get role details
    $roleQuery = "SELECT role_name, department_id, is_department_specific, hierarchy_level 
                  FROM roles WHERE role_id = ? AND is_active = TRUE";
    $roleStmt = $conn->prepare($roleQuery);
    $roleStmt->bind_param("i", $roleId);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();

    if ($roleResult->num_rows === 0) {
        return [['type' => 'error', 'message' => 'Role not found']];
    }

    $roleData = $roleResult->fetch_assoc();

    // If it's a department-specific role, check for existing holders
    if ($roleData['is_department_specific']) {
        $targetDeptId = $roleData['department_id'] ?: $userDeptId;

        if ($targetDeptId) {
            // Check if someone else already has this role in this department
            $conflictQuery = "SELECT 
                                u.user_id,
                                CONCAT(e.emp_fname, ' ', e.emp_lname) as full_name,
                                d.dep_name
                              FROM user_roles ur
                              JOIN roles r ON ur.role_id = r.role_id
                              JOIN user_ref u ON ur.user_id = u.user_id
                              JOIN emp_ref e ON u.emp_num = e.emp_num
                              JOIN departments d ON e.emp_dept = d.dep_num
                              WHERE ur.role_id = ? 
                              AND e.emp_dept = ?
                              AND ur.user_id != ?
                              AND ur.is_active = TRUE
                              AND r.is_active = TRUE";

            $conflictStmt = $conn->prepare($conflictQuery);
            $conflictStmt->bind_param("iii", $roleId, $targetDeptId, $userId);
            $conflictStmt->execute();
            $conflictResult = $conflictStmt->get_result();

            while ($conflict = $conflictResult->fetch_assoc()) {
                $conflicts[] = [
                    'type' => 'department_conflict',
                    'message' => "{$conflict['full_name']} is currently assigned as {$roleData['role_name']} for {$conflict['dep_name']} - Do you want to replace them?",
                    'current_holder' => $conflict['full_name'],
                    'department' => $conflict['dep_name'],
                    'role_name' => $roleData['role_name'],
                    'conflicting_user_id' => $conflict['user_id']
                ];
            }
        }
    }

    return $conflicts;
}

/**
 * API endpoint for checking role conflicts
 */
function checkRoleConflictsEndpoint($conn)
{
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);
    $userDeptId = !empty($_POST['user_dept_id']) ? (int)$_POST['user_dept_id'] : null;

    if (!$userId || !$roleId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID and Role ID are required'
        ]);
        return;
    }

    $conflicts = checkRoleConflicts($conn, $userId, $roleId, $userDeptId);

    echo json_encode([
        'success' => true,
        'conflicts' => $conflicts,
        'has_conflicts' => count($conflicts) > 0
    ]);
}

/**
 * Update an existing user (only role changes allowed)
 */
function updateUser($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $userId = $_POST['user_id'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 2);
    $forceUpdate = $_POST['force_update'] ?? false; // For overriding conflicts

    // Validation
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        return;
    }

    // Check if we're using the new role system
    $tableCheck = $conn->query("SHOW TABLES LIKE 'roles'");
    $useNewRoleSystem = $tableCheck->num_rows > 0;

    $conn->begin_transaction();

    try {
        if ($useNewRoleSystem) {
            // New role system - use user_roles table

            // Get user's actual user_id from user_ref
            $getUserIdSql = "SELECT user_id, emp_dept FROM user_ref u 
                            JOIN emp_ref e ON u.emp_num = e.emp_num 
                            WHERE u.emp_num = ?";
            $getUserIdStmt = $conn->prepare($getUserIdSql);
            $getUserIdStmt->bind_param("s", $userId);
            $getUserIdStmt->execute();
            $userResult = $getUserIdStmt->get_result();

            if ($userResult->num_rows === 0) {
                throw new Exception("User not found");
            }

            $userData = $userResult->fetch_assoc();
            $actualUserId = $userData['user_id'];
            $userDeptId = $userData['emp_dept'];

            // Check for conflicts unless force_update is true
            if (!$forceUpdate) {
                $conflicts = checkRoleConflicts($conn, $actualUserId, $roleId, $userDeptId);
                if (count($conflicts) > 0) {
                    $conn->rollback();
                    echo json_encode([
                        'success' => false,
                        'conflicts' => $conflicts,
                        'message' => 'Role conflicts detected. Please resolve conflicts or force update.'
                    ]);
                    return;
                }
            }

            // Remove existing roles for this user
            $removeRolesSql = "UPDATE user_roles SET is_active = FALSE WHERE user_id = ?";
            $removeRolesStmt = $conn->prepare($removeRolesSql);
            $removeRolesStmt->bind_param("i", $actualUserId);
            $removeRolesStmt->execute();

            // Add new role
            $addRoleSql = "INSERT INTO user_roles (user_id, role_id, assigned_by, assigned_date) 
                          VALUES (?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE is_active = TRUE, assigned_date = NOW()";
            $addRoleStmt = $conn->prepare($addRoleSql);
            $currentUserId = $_SESSION['user_id'] ?? null;
            $addRoleStmt->bind_param("iii", $actualUserId, $roleId, $currentUserId);
            $addRoleStmt->execute();

            // Update legacy role_name in user_ref for backward compatibility
            $getRoleNameSql = "SELECT role_name FROM roles WHERE role_id = ?";
            $getRoleNameStmt = $conn->prepare($getRoleNameSql);
            $getRoleNameStmt->bind_param("i", $roleId);
            $getRoleNameStmt->execute();
            $roleResult = $getRoleNameStmt->get_result();
            $roleName = $roleResult->fetch_assoc()['role_name'] ?? 'User';

            $updateLegacySql = "UPDATE user_ref SET role_name = ? WHERE user_id = ?";
            $updateLegacyStmt = $conn->prepare($updateLegacySql);
            $updateLegacyStmt->bind_param("si", $roleName, $actualUserId);
            $updateLegacyStmt->execute();
        } else {
            // Old role system - update role_name directly
            $roleName = ($roleId == 1) ? 'Administrator' : 'User';

            // Update user_ref table
            $userSql = "UPDATE user_ref SET role_name = ? WHERE emp_num = ?";
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param("ss", $roleName, $userId);
            $userStmt->execute();

            // Update or insert into users table (if it exists)
            $checkUsersTable = $conn->query("SHOW TABLES LIKE 'users'");
            if ($checkUsersTable->num_rows > 0) {
                $checkUserSql = "SELECT emp_num FROM users WHERE emp_num = ?";
                $checkStmt = $conn->prepare($checkUserSql);
                $checkStmt->bind_param("s", $userId);
                $checkStmt->execute();
                $exists = $checkStmt->get_result()->num_rows > 0;

                if ($exists) {
                    $updateUsersSql = "UPDATE users SET role_id = ? WHERE emp_num = ?";
                    $updateUsersStmt = $conn->prepare($updateUsersSql);
                    $updateUsersStmt->bind_param("is", $roleId, $userId);
                    $updateUsersStmt->execute();
                } else {
                    $insertUsersSql = "INSERT INTO users (emp_num, role_id) VALUES (?, ?)";
                    $insertUsersStmt = $conn->prepare($insertUsersSql);
                    $insertUsersStmt->bind_param("si", $userId, $roleId);
                    $insertUsersStmt->execute();
                }
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update user error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()]);
    }
}

/**
 * Create a new user
 */
function createUser($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $empNumber = trim($_POST['employee_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $deptId = $_POST['dept_id'] ?? null;
    $roleId = (int)($_POST['role_id'] ?? 2);
    $password = $_POST['password'] ?? 'default123';

    // Validation
    if (empty($firstName) || empty($empNumber)) {
        echo json_encode(['success' => false, 'message' => 'First name and employee number are required']);
        return;
    }

    // Check if employee number already exists
    $checkSql = "SELECT emp_num FROM user_ref WHERE emp_num = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $empNumber);
    $checkStmt->execute();

    if ($checkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Employee number already exists']);
        return;
    }

    $conn->begin_transaction();

    try {
        $fullName = trim($firstName . ' ' . $lastName);
        $roleName = ($roleId == 1) ? 'Administrator' : 'User';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into user_ref table
        $userSql = "INSERT INTO user_ref (emp_num, empName, email, role_name) VALUES (?, ?, ?, ?)";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("ssss", $empNumber, $fullName, $email, $roleName);
        $userStmt->execute();

        // Insert into emp_ref table if department is specified
        if ($deptId) {
            $empSql = "INSERT INTO emp_ref (empNumber, empName, email, deptNumber) VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE empName = VALUES(empName), email = VALUES(email), deptNumber = VALUES(deptNumber)";
            $empStmt = $conn->prepare($empSql);
            $empStmt->bind_param("ssss", $empNumber, $fullName, $email, $deptId);
            $empStmt->execute();
        }

        // Insert into users table (newer table)
        $insertUsersSql = "INSERT INTO users (emp_num, role_id) VALUES (?, ?)";
        $insertUsersStmt = $conn->prepare($insertUsersSql);
        $insertUsersStmt->bind_param("si", $empNumber, $roleId);
        $insertUsersStmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'User created successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Create user error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()]);
    }
}

/**
 * Delete a user (soft delete by marking inactive)
 */
function deleteUser($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $userId = $_POST['user_id'] ?? '';

    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        return;
    }

    // Check if user exists and is not the current admin
    if ($userId == $_SESSION["empNumber"]) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        return;
    }

    $conn->begin_transaction();

    try {
        // For now, we'll remove from user_ref (hard delete as per legacy behavior)
        // In production, you might want to implement soft delete
        $deleteSql = "DELETE FROM user_ref WHERE emp_num = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("s", $userId);
        $deleteStmt->execute();

        // Also remove from users table
        $deleteUsersSql = "DELETE FROM users WHERE emp_num = ?";
        $deleteUsersStmt = $conn->prepare($deleteUsersSql);
        $deleteUsersStmt->bind_param("s", $userId);
        $deleteUsersStmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Delete user error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
    }
}

/**
 * Export users to CSV
 */
function exportUsers($conn)
{
    $sql = "SELECT 
                u.emp_num as 'Employee Number',
                u.empName as 'Full Name',
                u.email as 'Email',
                u.role_name as 'Role',
                COALESCE(d.dep_name, 'No Department') as 'Department',
                GROUP_CONCAT(
                    DISTINCT CASE 
                        WHEN d1.dep_num IS NOT NULL THEN CONCAT('Head: ', d1.dep_name)
                        WHEN d2.dep_num IS NOT NULL THEN CONCAT('Assistant: ', d2.dep_name)
                        WHEN d3.dep_num IS NOT NULL THEN CONCAT('Asset Mgr: ', d3.dep_name)
                        ELSE NULL
                    END SEPARATOR '; '
                ) as 'Leadership Positions'
            FROM user_ref u
            LEFT JOIN emp_ref er ON u.emp_num = er.empNumber
            LEFT JOIN departments d ON er.deptNumber = d.dep_num
            LEFT JOIN departments d1 ON u.emp_num = d1.dep_head
            LEFT JOIN departments d2 ON u.emp_num = d2.dep_assist
            LEFT JOIN departments d3 ON u.emp_num = d3.dep_asset_mgr
            GROUP BY u.emp_num
            ORDER BY u.empName ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add CSV header
    if ($result->num_rows > 0) {
        $firstRow = $result->fetch_assoc();
        fputcsv($output, array_keys($firstRow));
        fputcsv($output, $firstRow);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
}
