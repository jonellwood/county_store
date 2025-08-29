<?php

/**
 * Migration script to create the new roles system
 * Run this script once to set up the proper role hierarchy and department associations
 */

require_once '../../../config.php';

echo "<h2>Role System Migration</h2>\n";
echo "<p>This will create the new roles system with proper hierarchy and department associations.</p>\n";

try {
    $conn->begin_transaction();

    // 1. Create the roles table
    echo "<h3>Step 1: Creating roles table...</h3>\n";
    $createRolesTable = "
    CREATE TABLE IF NOT EXISTS roles (
        role_id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL,
        role_description TEXT,
        department_id INT NULL,
        hierarchy_level INT DEFAULT 0,
        is_department_specific BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY unique_role_dept (role_name, department_id),
        FOREIGN KEY (department_id) REFERENCES departments(dep_num) ON DELETE CASCADE
    )";

    $conn->query($createRolesTable);
    echo "✅ Roles table created successfully<br>\n";

    // 2. Create the user_roles junction table
    echo "<h3>Step 2: Creating user_roles table...</h3>\n";
    $createUserRolesTable = "
    CREATE TABLE IF NOT EXISTS user_roles (
        user_role_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        assigned_by INT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        
        UNIQUE KEY unique_user_role (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES user_ref(user_id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_by) REFERENCES user_ref(user_id) ON DELETE SET NULL
    )";

    $conn->query($createUserRolesTable);
    echo "✅ User_roles table created successfully<br>\n";

    // 3. Insert initial roles
    echo "<h3>Step 3: Inserting initial roles...</h3>\n";

    // First, check what roles currently exist in user_ref
    $existingRolesQuery = "SELECT DISTINCT role_name, COUNT(*) as user_count 
                          FROM user_ref 
                          WHERE role_name IS NOT NULL 
                          GROUP BY role_name";
    $existingRoles = $conn->query($existingRolesQuery);

    echo "<h4>Current roles in system:</h4>\n";
    $roleNames = [];
    while ($row = $existingRoles->fetch_assoc()) {
        echo "- {$row['role_name']} ({$row['user_count']} users)<br>\n";
        $roleNames[] = $row['role_name'];
    }

    // Insert roles based on what we found
    $rolesToInsert = [
        ['Administrator', 'Full system administrator with all permissions', NULL, 100, FALSE],
        ['Department Head', 'Head of department with full department permissions', NULL, 50, TRUE],
        ['Department Head with Logo', 'Head of department with logo management permissions', NULL, 55, TRUE],
        ['Assistant', 'Department assistant with limited permissions', NULL, 25, TRUE],
        ['Assistant with Logo', 'Department assistant with logo management permissions', NULL, 30, TRUE],
        ['User', 'Standard user with basic permissions', NULL, 10, FALSE]
    ];

    $insertRoleQuery = "INSERT IGNORE INTO roles (role_name, role_description, department_id, hierarchy_level, is_department_specific) 
                       VALUES (?, ?, ?, ?, ?)";
    $insertRoleStmt = $conn->prepare($insertRoleQuery);

    foreach ($rolesToInsert as $role) {
        $insertRoleStmt->bind_param("ssiii", $role[0], $role[1], $role[2], $role[3], $role[4]);
        $insertRoleStmt->execute();
    }

    echo "✅ Initial roles inserted<br>\n";

    // 4. Create indexes
    echo "<h3>Step 4: Creating indexes...</h3>\n";
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_user_roles_user_id ON user_roles(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_user_roles_role_id ON user_roles(role_id)",
        "CREATE INDEX IF NOT EXISTS idx_roles_department ON roles(department_id)",
        "CREATE INDEX IF NOT EXISTS idx_roles_hierarchy ON roles(hierarchy_level)"
    ];

    foreach ($indexes as $index) {
        $conn->query($index);
    }
    echo "✅ Indexes created<br>\n";

    // 5. Migrate existing user roles
    echo "<h3>Step 5: Migrating existing user roles...</h3>\n";

    $migrateQuery = "
    INSERT IGNORE INTO user_roles (user_id, role_id, assigned_date)
    SELECT 
        u.user_id,
        r.role_id,
        NOW()
    FROM user_ref u
    JOIN roles r ON u.role_name = r.role_name
    WHERE u.role_name IS NOT NULL
    ";

    $result = $conn->query($migrateQuery);
    $migratedCount = $conn->affected_rows;
    echo "✅ Migrated {$migratedCount} user role assignments<br>\n";

    // 6. Show final status
    echo "<h3>Step 6: Final Status</h3>\n";

    $rolesCount = $conn->query("SELECT COUNT(*) as count FROM roles")->fetch_assoc()['count'];
    $userRolesCount = $conn->query("SELECT COUNT(*) as count FROM user_roles WHERE is_active = TRUE")->fetch_assoc()['count'];

    echo "✅ {$rolesCount} roles created<br>\n";
    echo "✅ {$userRolesCount} active user role assignments<br>\n";

    $conn->commit();
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Migration completed successfully!</strong><br>";
    echo "The new role system is now active. You can now use advanced role management features including:<br>";
    echo "• Department-specific role assignments<br>";
    echo "• Role conflict detection<br>";
    echo "• Role hierarchy management<br>";
    echo "• Detailed role descriptions<br>";
    echo "</div>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Migration failed:</strong> " . $e->getMessage();
    echo "</div>";
}

// Show some helpful queries
echo "<h3>Helpful Queries</h3>\n";
echo "<h4>View all roles:</h4>\n";
echo "<code>SELECT * FROM roles ORDER BY hierarchy_level DESC;</code><br><br>\n";
echo "<h4>View user role assignments:</h4>\n";
echo "<code>SELECT u.emp_num, CONCAT(e.emp_fname, ' ', e.emp_lname) as name, r.role_name, d.dep_name<br>";
echo "FROM user_roles ur<br>";
echo "JOIN user_ref u ON ur.user_id = u.user_id<br>";
echo "JOIN emp_ref e ON u.emp_num = e.emp_num<br>";
echo "JOIN roles r ON ur.role_id = r.role_id<br>";
echo "LEFT JOIN departments d ON e.emp_dept = d.dep_num<br>";
echo "WHERE ur.is_active = TRUE;</code><br><br>\n";

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

    h4 {
        color: #34495e;
    }

    code {
        background: #f8f9fa;
        padding: 5px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>