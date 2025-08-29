-- Manual MySQL statements to upgrade existing roles table
-- Run these one by one in Azure Data Studio

-- 1. Add missing columns to roles table
ALTER TABLE roles ADD COLUMN role_description TEXT NULL AFTER role_name;
ALTER TABLE roles ADD COLUMN department_id INT NULL AFTER role_description;
ALTER TABLE roles ADD COLUMN hierarchy_level INT DEFAULT 0 AFTER department_id;
ALTER TABLE roles ADD COLUMN is_department_specific BOOLEAN DEFAULT FALSE AFTER hierarchy_level;
ALTER TABLE roles ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_department_specific;
ALTER TABLE roles ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active;
ALTER TABLE roles ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- 2. Add foreign key constraint (if departments table exists)
-- Note: This might fail if departments table doesn't exist or dep_num column doesn't match
-- ALTER TABLE roles ADD CONSTRAINT fk_roles_department FOREIGN KEY (department_id) REFERENCES departments(dep_num) ON DELETE CASCADE;

-- 3. Add unique constraint
-- ALTER TABLE roles ADD CONSTRAINT unique_role_dept UNIQUE (role_name, department_id);

-- 4. Update existing roles with proper hierarchy levels and descriptions
UPDATE roles SET 
    role_description = 'Full system administrator with all permissions',
    hierarchy_level = 100,
    is_department_specific = FALSE
WHERE role_name = 'Administrator';

UPDATE roles SET 
    role_description = 'Head of department with full department permissions',
    hierarchy_level = 50,
    is_department_specific = TRUE
WHERE role_name = 'Department Head';

UPDATE roles SET 
    role_description = 'Head of department with logo management permissions',
    hierarchy_level = 55,
    is_department_specific = TRUE
WHERE role_name = 'Department Head with Logo';

UPDATE roles SET 
    role_description = 'Department assistant with limited permissions',
    hierarchy_level = 25,
    is_department_specific = TRUE
WHERE role_name = 'Assistant';

UPDATE roles SET 
    role_description = 'Department assistant with logo management permissions',
    hierarchy_level = 30,
    is_department_specific = TRUE
WHERE role_name = 'Assistant with Logo';

UPDATE roles SET 
    role_description = 'Standard user with basic permissions',
    hierarchy_level = 10,
    is_department_specific = FALSE
WHERE role_name = 'User';

-- 5. Set any remaining roles to default values
UPDATE roles SET 
    role_description = COALESCE(role_description, 'Standard role'),
    hierarchy_level = COALESCE(hierarchy_level, 10),
    is_department_specific = COALESCE(is_department_specific, FALSE),
    is_active = COALESCE(is_active, TRUE)
WHERE role_description IS NULL OR hierarchy_level IS NULL;

-- 6. View your updated roles table
SELECT role_id, role_name, role_description, hierarchy_level, is_department_specific, is_active 
FROM roles 
ORDER BY hierarchy_level DESC, role_name;
