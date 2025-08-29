# User Management System Documentation

## Overview

The Edit Users system uses a **dual role architecture** that separates system permissions from actual job assignments.

## 🔄 Two Role Systems Explained

### 1. **"Role" Column - System Permissions**

- **Source**: `user_ref.role_name` field
- **Purpose**: Determines what the user can do in the system
- **Type**: Simple, single role per user
- **Examples**:
  - `Administrator` - Full system access
  - `Department Head` - Department management permissions
  - `Assistant` - Limited department permissions
  - `User` - Basic user permissions

**This is what you edit in the modal form**

### 2. **"Leadership Roles" Column - Department Assignments**

- **Source**: Department table relationships (`departments.dep_head`, `dep_assist`, `dep_asset_mgr`)
- **Purpose**: Shows actual job assignments/positions in departments
- **Type**: Can have multiple roles across different departments
- **Examples**:
  - `Head: Parks & Recreation`
  - `Assistant: Public Works`
  - `Asset Mgr: Fire Department`

**This is automatically determined by department table assignments**

## 🎯 Real-World Example

**User: John Smith**

- **Role**: "Department Head" ← *System Permission Level*
- **Leadership Roles**: "Assistant: Parks & Rec" ← *Actual Job Assignment*

**Translation**: John has department head permissions in the system, but his actual job is assistant for Parks & Recreation.

## 📊 Database Structure

### Current System Tables

```sql
-- Basic role assignment
user_ref.role_name → "Administrator", "Department Head", etc.

-- Department leadership positions
departments.dep_head → Employee who heads this department
departments.dep_assist → Employee who assists this department  
departments.dep_asset_mgr → Employee who manages assets for this department
```

### New Role System (Optional Enhancement)

```sql
-- Enhanced role definitions
roles.role_id, role_name, hierarchy_level, is_department_specific

-- User-to-role assignments  
user_roles.user_id, role_id, assigned_date
```

## 🔧 How It Works in Code

### Leadership Roles Query Logic

```sql
CASE 
    WHEN d1.dep_num IS NOT NULL THEN CONCAT('Head: ', d1.dep_name)
    WHEN d2.dep_num IS NOT NULL THEN CONCAT('Assistant: ', d2.dep_name)
    WHEN d3.dep_num IS NOT NULL THEN CONCAT('Asset Mgr: ', d3.dep_name)
    ELSE NULL
END as leadership_role
```

### The JOINs

```sql
LEFT JOIN departments d1 ON CAST(u.emp_num AS CHAR) = CAST(d1.dep_head AS CHAR)      -- Check if user heads any dept
LEFT JOIN departments d2 ON CAST(u.emp_num AS CHAR) = CAST(d2.dep_assist AS CHAR)    -- Check if user assists any dept
LEFT JOIN departments d3 ON CAST(u.emp_num AS CHAR) = CAST(d3.dep_asset_mgr AS CHAR) -- Check if user manages assets
```

## 🛠️ System Features

### Current Features

- ✅ Role-based access control
- ✅ Department assignment display
- ✅ Read-only security fields
- ✅ Modern UI with filtering
- ✅ Backward compatibility

### Enhanced Features (After user_roles table setup)

- 🆕 Role conflict detection
- 🆕 Department-specific role validation
- 🆕 Role hierarchy enforcement
- 🆕 Assignment history tracking

## 🚨 Important Notes

### Why Two Systems?

1. **Flexibility**: Someone can have system permissions that don't match their exact job title
2. **Security**: System roles control access, department roles show responsibilities
3. **Scalability**: Can assign different permission levels without changing job assignments

### Common Scenarios

- **Temporary Coverage**: Give someone "Department Head" permissions while they cover for absent head
- **Cross-Department**: Someone assists multiple departments but only heads one
- **Training**: New employee has "User" permissions but is assigned as "Assistant" to learn

### Editing Rules

- ✅ **Can Edit**: Role (system permissions)
- ❌ **Cannot Edit**: Name, Employee Number, Email, Phone, Department, Leadership Roles
- 🔒 **Security**: Only Administrators can edit user roles

## 🔍 Troubleshooting

### Common Issues

- **Department showing "undefined"**: Check CAST operations in JOINs
- **Role conflicts**: Implement user_roles table for advanced conflict detection
- **Permission errors**: Verify session role_id = 1 for admin access

### Files to Check

- `api.php` - Database queries and role logic
- `edit-users.js` - Frontend functionality and modal handling
- `index.php` - HTML structure and modal definitions
- `edit-users.css` - Styling and readonly field appearance

---
*Last Updated: August 29, 2025*  
*System Version: Enhanced Role Management v2.0*
