<?php
// Created: 2025/08/29
// Modern Orders Dashboard - Berkeley County Store Admin
include('../DBConn.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../signin/signin.php");
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("Location: ../401.php");
    exit;
}

// Include our modern header instead of commonHead
include "../../../components/header.php";
?>

<link href="orders.css" rel="stylesheet" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders Dashboard - Berkeley County Store Admin</title>

<!-- Modern Orders Dashboard -->
<div class="modern-admin-container">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-shopping-cart"></i>
                    Department Orders Dashboard
                </h1>
                <p class="page-subtitle">Manage approved orders by department</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
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
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalOrders">-</div>
                <div class="stat-label">Total Orders</div>
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
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalValue">-</div>
                <div class="stat-label">Total Value</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="pendingOrders">-</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-container">
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
            <label for="vendorFilter" class="filter-label">
                <i class="fas fa-truck"></i>
                Vendor
            </label>
            <select id="vendorFilter" class="form-control">
                <option value="">All Vendors</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="statusFilter" class="filter-label">
                <i class="fas fa-tag"></i>
                Status
            </label>
            <select id="statusFilter" class="form-control">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="ordered">Ordered</option>
                <option value="pending">Pending</option>
            </select>
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
            <p class="loading-text">Loading department orders...</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="error-container" style="display: none;">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="error-title">Error Loading Orders</h3>
            <p class="error-message" id="errorMessage">Unable to load department orders</p>
            <button class="btn btn-primary" id="retryBtn">
                <i class="fas fa-redo"></i>
                Retry
            </button>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-container" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="empty-title">No Orders Found</h3>
            <p class="empty-message">No approved orders found for any departments</p>
        </div>

        <!-- Orders Content -->
        <div id="ordersContent" class="orders-content" style="display: none;">
            <div id="ordersList" class="orders-list">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i>
                    Order Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markOrderedBtn">
                    <i class="fas fa-check"></i>
                    Mark as Ordered
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vendor Report Modal -->
<div class="modal fade" id="vendorReportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt"></i>
                    Generate Vendor Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vendorReportBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="generateReportBtn">
                    <i class="fas fa-file-download"></i>
                    Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Orders Dashboard JS -->
<script src="orders.js"></script>

</body>

</html>