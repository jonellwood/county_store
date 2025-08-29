<?php
// Created: 2025/08/29
// Modern Edit Users Dashboard - Berkeley County Store Admin
include('../DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../sign-in.php");
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("Location: ../401.php");
    exit;
}

// Include our modern header instead of commonHead
include "../../../components/header.php";
?>

<link href="edit-users.css" rel="stylesheet" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - Berkeley County Store Admin</title>

<!-- Modern Edit Users Dashboard -->
<div class="modern-admin-container">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-users"></i>
                    User Management Dashboard
                </h1>
                <p class="page-subtitle">Manage user accounts, roles, and department assignments</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
                <button class="btn btn-success" id="addUserBtn">
                    <i class="fas fa-user-plus"></i>
                    Add User
                </button>
                <button class="btn btn-secondary" id="exportBtn">
                    <i class="fas fa-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalUsers">-</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalAdmins">-</div>
                <div class="stat-label">Administrators</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalDepartments">-</div>
                <div class="stat-label">Departments</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="activeUsers">-</div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-container">
        <div class="filter-group">
            <label for="roleFilter" class="filter-label">
                <i class="fas fa-user-tag"></i>
                Role
            </label>
            <select id="roleFilter" class="form-control">
                <option value="">All Roles</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="departmentFilter" class="filter-label">
                <i class="fas fa-building"></i>
                Department
            </label>
            <select id="departmentFilter" class="form-control">
                <option value="">All Departments</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="searchFilter" class="filter-label">
                <i class="fas fa-search"></i>
                Search
            </label>
            <input type="text" id="searchFilter" class="form-control" placeholder="Search by name or employee number">
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary" id="applyFilters">
                <i class="fas fa-filter"></i>
                Apply Filters
            </button>
            <button class="btn btn-secondary" id="clearFilters">
                <i class="fas fa-times"></i>
                Clear
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="content-container">
        <!-- Loading State -->
        <div id="loadingState" class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Loading users...</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="error-container" style="display: none;">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="error-title">Error Loading Users</h3>
            <p class="error-message" id="errorMessage">Unable to load user data</p>
            <button class="btn btn-primary" id="retryBtn">
                <i class="fas fa-redo"></i>
                Retry
            </button>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-container" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <h3 class="empty-title">No Users Found</h3>
            <p class="empty-message">No users match the current filter criteria</p>
        </div>

        <!-- Users Content -->
        <div id="usersContent" class="users-content" style="display: none;">
            <div id="usersList" class="users-list">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user"></i>
                    User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editUserBtn">
                    <i class="fas fa-edit"></i>
                    Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editUserModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveUserBtn">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i>
                    Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addUserModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="createUserBtn">
                    <i class="fas fa-plus"></i>
                    Create User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Users Dashboard JS -->
<script src="edit-users.js"></script>

</body>

</html>