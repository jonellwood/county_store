-- Create proper roles system with hierarchy and department associations
-- This will replace the current role_name column approach

-- 1. Create the roles table
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    role_description TEXT,
    department_id INT NULL, -- NULL means global role (like Administrator)
    hierarchy_level INT DEFAULT 0, -- Higher number = higher authority
    is_department_specific BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_role_dept (role_name, department_id),
    FOREIGN KEY (department_id) REFERENCES departments(dep_num) ON DELETE CASCADE
);

-- 2. Create the user_roles junction table
CREATE TABLE IF NOT EXISTS user_roles (
    user_role_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL, -- Who assigned this role
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Constraints
    UNIQUE KEY unique_user_role (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES user_ref(user_id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES user_ref(user_id) ON DELETE SET NULL
);

-- 3. Insert initial roles based on your current system
INSERT INTO roles (role_name, role_description, department_id, hierarchy_level, is_department_specific) VALUES
-- Global roles
('Administrator', 'Full system administrator with all permissions', NULL, 100, FALSE),

-- Department-specific roles (we'll need to get actual department IDs)
('Department Head', 'Head of department with full department permissions', NULL, 50, TRUE),
('Department Head with Logo', 'Head of department with logo management permissions', NULL, 55, TRUE),
('Assistant', 'Department assistant with limited permissions', NULL, 25, TRUE),
('Assistant with Logo', 'Department assistant with logo management permissions', NULL, 30, TRUE);

-- 4. Migrate existing role data from user_ref to new system
-- First, let's see what roles currently exist
SELECT DISTINCT role_name, COUNT(*) as user_count 
FROM user_ref 
WHERE role_name IS NOT NULL 
GROUP BY role_name;

-- 5. Create indexes for performance
CREATE INDEX idx_user_roles_user_id ON user_roles(user_id);
CREATE INDEX idx_user_roles_role_id ON user_roles(role_id);
CREATE INDEX idx_roles_department ON roles(department_id);
CREATE INDEX idx_roles_hierarchy ON roles(hierarchy_level);

-- 6. Create a view for easy role queries
CREATE VIEW user_role_details AS
SELECT 
    ur.user_id,
    ur.role_id,
    r.role_name,
    r.role_description,
    r.department_id,
    d.dep_name as department_name,
    r.hierarchy_level,
    r.is_department_specific,
    ur.assigned_date,
    ur.is_active as role_active
FROM user_roles ur
JOIN roles r ON ur.role_id = r.role_id
LEFT JOIN departments d ON r.department_id = d.dep_num
WHERE ur.is_active = TRUE AND r.is_active = TRUE;

-- 7. Add some helpful functions for role management
DELIMITER //

-- Function to check if user has specific role
CREATE FUNCTION user_has_role(p_user_id INT, p_role_name VARCHAR(50)) 
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE role_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO role_count
    FROM user_roles ur
    JOIN roles r ON ur.role_id = r.role_id
    WHERE ur.user_id = p_user_id 
    AND r.role_name = p_role_name 
    AND ur.is_active = TRUE 
    AND r.is_active = TRUE;
    
    RETURN role_count > 0;
END//

-- Function to get user's highest hierarchy level
CREATE FUNCTION user_max_hierarchy(p_user_id INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE max_level INT DEFAULT 0;
    
    SELECT COALESCE(MAX(r.hierarchy_level), 0) INTO max_level
    FROM user_roles ur
    JOIN roles r ON ur.role_id = r.role_id
    WHERE ur.user_id = p_user_id 
    AND ur.is_active = TRUE 
    AND r.is_active = TRUE;
    
    RETURN max_level;
END//

DELIMITER ;

-- 8. Migration script to populate user_roles from existing user_ref.role_name
-- This will need to be run after we determine the mapping
/*
INSERT INTO user_roles (user_id, role_id, assigned_date)
SELECT 
    u.user_id,
    r.role_id,
    NOW()
FROM user_ref u
JOIN roles r ON u.role_name = r.role_name
WHERE u.role_name IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM user_roles ur 
    WHERE ur.user_id = u.user_id AND ur.role_id = r.role_id
);
*/
