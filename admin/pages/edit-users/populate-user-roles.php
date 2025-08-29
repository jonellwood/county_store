<?php

/**
 * Populate user_roles table after creating it
 * This migrates existing role assignments from user_ref to the new user_roles table
 */

require_once '../../../config.php';

echo "<h2>Populate User Roles</h2>\n";
echo "<p>Migrating existing role assignments to the new user_roles table...</p>\n";

try {
    $conn->begin_transaction();

    // Check if user_roles table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_roles'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception("user_roles table does not exist. Please create it first using the SQL script.");
    }

    // Show current state
    echo "<h3>Current situation:</h3>\n";

    // Count users in user_ref with roles
    $userRefCount = $conn->query("SELECT COUNT(*) as count FROM user_ref WHERE role_name IS NOT NULL")->fetch_assoc()['count'];
    echo "Users with roles in user_ref: {$userRefCount}<br>\n";

    // Count current entries in user_roles
    $userRolesCount = $conn->query("SELECT COUNT(*) as count FROM user_roles WHERE is_active = TRUE")->fetch_assoc()['count'];
    echo "Active entries in user_roles: {$userRolesCount}<br>\n";

    // Show roles available
    echo "<h4>Available roles:</h4>\n";
    $rolesQuery = $conn->query("SELECT role_id, role_name FROM roles ORDER BY hierarchy_level DESC");
    echo "<ul>";
    while ($role = $rolesQuery->fetch_assoc()) {
        echo "<li>ID {$role['role_id']}: {$role['role_name']}</li>";
    }
    echo "</ul>";

    // Show users that need migration
    echo "<h3>Users to migrate:</h3>\n";
    $usersToMigrateQuery = "
        SELECT 
            u.user_id,
            u.emp_num,
            u.role_name,
            CONCAT(e.emp_fname, ' ', e.emp_lname) as full_name,
            d.dep_name
        FROM user_ref u
        JOIN emp_ref e ON u.emp_num = e.emp_num
        LEFT JOIN departments d ON e.emp_dept = d.dep_num
        WHERE u.role_name IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM user_roles ur 
            WHERE ur.user_id = u.user_id AND ur.is_active = TRUE
        )
        ORDER BY u.role_name, full_name
    ";

    $usersToMigrate = $conn->query($usersToMigrateQuery);

    if ($usersToMigrate->num_rows === 0) {
        echo "✅ All users have already been migrated!<br>\n";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>User ID</th><th>Emp Num</th><th>Name</th><th>Current Role</th><th>Department</th></tr>";

        $migrationCount = 0;
        while ($user = $usersToMigrate->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td>{$user['emp_num']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['role_name']}</td>";
            echo "<td>" . ($user['dep_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Perform the migration
        echo "<h3>Performing migration:</h3>\n";

        $migrateQuery = "
        INSERT INTO user_roles (user_id, role_id, assigned_date, is_active)
        SELECT 
            u.user_id,
            r.role_id,
            NOW(),
            TRUE
        FROM user_ref u
        JOIN roles r ON u.role_name = r.role_name
        WHERE u.role_name IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM user_roles ur 
            WHERE ur.user_id = u.user_id AND ur.role_id = r.role_id
        )
        ";

        $result = $conn->query($migrateQuery);
        $migratedCount = $conn->affected_rows;

        echo "✅ Migrated {$migratedCount} user role assignments<br>\n";
    }

    // Show final status
    echo "<h3>Final Status:</h3>\n";

    $finalCount = $conn->query("SELECT COUNT(*) as count FROM user_roles WHERE is_active = TRUE")->fetch_assoc()['count'];
    echo "✅ Total active user role assignments: {$finalCount}<br>\n";

    // Show sample of migrated data
    echo "<h4>Sample of user role assignments:</h4>\n";
    $sampleQuery = "
        SELECT 
            CONCAT(e.emp_fname, ' ', e.emp_lname) as full_name,
            r.role_name,
            d.dep_name,
            ur.assigned_date
        FROM user_roles ur
        JOIN user_ref u ON ur.user_id = u.user_id
        JOIN emp_ref e ON u.emp_num = e.emp_num
        JOIN roles r ON ur.role_id = r.role_id
        LEFT JOIN departments d ON e.emp_dept = d.dep_num
        WHERE ur.is_active = TRUE
        ORDER BY r.hierarchy_level DESC, full_name
        LIMIT 10
    ";

    $sampleResult = $conn->query($sampleQuery);
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Name</th><th>Role</th><th>Department</th><th>Assigned</th></tr>";
    while ($sample = $sampleResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$sample['full_name']}</td>";
        echo "<td>{$sample['role_name']}</td>";
        echo "<td>" . ($sample['dep_name'] ?? 'N/A') . "</td>";
        echo "<td>{$sample['assigned_date']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    $conn->commit();
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Migration completed successfully!</strong><br>";
    echo "All existing user role assignments have been migrated to the new user_roles table.<br>";
    echo "The edit-users page should now work properly with the new role system.";
    echo "</div>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Migration failed:</strong> " . $e->getMessage();
    echo "</div>";
}

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    h2 {
        color: #2c3e50;
    }

    h3 {
        color: #3498db;
    }

    table {
        font-size: 14px;
    }

    th {
        background: #3498db;
        color: white;
        padding: 8px;
    }

    td {
        padding: 8px;
    }

    ul {
        margin: 10px 0;
    }

    li {
        margin: 5px 0;
    }
</style>