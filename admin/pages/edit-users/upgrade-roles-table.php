<?php

/**
 * ALTER TABLE script to upgrade existing roles table
 * This adds the missing columns to your existing roles table
 */

require_once '../../../config.php';

echo "<h2>Roles Table Upgrade</h2>\n";
echo "<p>Adding missing columns to existing roles table...</p>\n";

try {
    $conn->begin_transaction();

    // Check current table structure
    echo "<h3>Current roles table structure:</h3>\n";
    $describeResult = $conn->query("DESCRIBE roles");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $describeResult->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . ($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    // Add missing columns one by one
    $alterStatements = [
        "ADD COLUMN role_description TEXT NULL AFTER role_name",
        "ADD COLUMN department_id INT NULL AFTER role_description",
        "ADD COLUMN hierarchy_level INT DEFAULT 0 AFTER department_id",
        "ADD COLUMN is_department_specific BOOLEAN DEFAULT FALSE AFTER hierarchy_level",
        "ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_department_specific",
        "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active",
        "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    echo "<h3>Adding missing columns:</h3>\n";
    foreach ($alterStatements as $alter) {
        try {
            $sql = "ALTER TABLE roles " . $alter;
            $conn->query($sql);
            echo "✅ " . $alter . "<br>\n";
        } catch (Exception $e) {
            // Column might already exist, that's okay
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "⚠️ Column already exists: " . $alter . "<br>\n";
            } else {
                throw $e;
            }
        }
    }

    // Add foreign key constraint if it doesn't exist
    echo "<h3>Adding foreign key constraint:</h3>\n";
    try {
        $conn->query("ALTER TABLE roles ADD CONSTRAINT fk_roles_department 
                     FOREIGN KEY (department_id) REFERENCES departments(dep_num) ON DELETE CASCADE");
        echo "✅ Foreign key constraint added<br>\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "⚠️ Foreign key constraint already exists<br>\n";
        } else {
            echo "⚠️ Could not add foreign key: " . $e->getMessage() . "<br>\n";
        }
    }

    // Add unique constraint
    try {
        $conn->query("ALTER TABLE roles ADD CONSTRAINT unique_role_dept UNIQUE (role_name, department_id)");
        echo "✅ Unique constraint added<br>\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "⚠️ Unique constraint already exists<br>\n";
        } else {
            echo "⚠️ Could not add unique constraint: " . $e->getMessage() . "<br>\n";
        }
    }

    // Update existing roles with proper hierarchy levels
    echo "<h3>Updating existing roles with hierarchy levels:</h3>\n";

    $roleUpdates = [
        ['Administrator', 'Full system administrator with all permissions', 100, FALSE],
        ['Department Head', 'Head of department with full department permissions', 50, TRUE],
        ['Department Head with Logo', 'Head of department with logo management permissions', 55, TRUE],
        ['Assistant', 'Department assistant with limited permissions', 25, TRUE],
        ['Assistant with Logo', 'Department assistant with logo management permissions', 30, TRUE],
        ['User', 'Standard user with basic permissions', 10, FALSE]
    ];

    $updateStmt = $conn->prepare("UPDATE roles SET role_description = ?, hierarchy_level = ?, is_department_specific = ? WHERE role_name = ?");

    foreach ($roleUpdates as $update) {
        $updateStmt->bind_param("siis", $update[1], $update[2], $update[3], $update[0]);
        $updateStmt->execute();
        if ($updateStmt->affected_rows > 0) {
            echo "✅ Updated: {$update[0]} (hierarchy: {$update[2]})<br>\n";
        } else {
            echo "ℹ️ Not found: {$update[0]}<br>\n";
        }
    }

    // Show current roles in system
    echo "<h3>Current roles in your system:</h3>\n";
    $rolesQuery = $conn->query("SELECT role_id, role_name, hierarchy_level, is_department_specific, is_active FROM roles ORDER BY hierarchy_level DESC");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Role Name</th><th>Hierarchy</th><th>Dept Specific</th><th>Active</th></tr>";
    while ($role = $rolesQuery->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$role['role_id']}</td>";
        echo "<td>{$role['role_name']}</td>";
        echo "<td>{$role['hierarchy_level']}</td>";
        echo "<td>" . ($role['is_department_specific'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($role['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    $conn->commit();
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Roles table upgraded successfully!</strong><br>";
    echo "Your existing roles table now has all the required columns for the new role system.";
    echo "</div>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Upgrade failed:</strong> " . $e->getMessage();
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
</style>