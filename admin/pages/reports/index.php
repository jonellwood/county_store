<?php
// Created: 2025/08/29
// Modern Reports Dashboard - Berkeley County Store Admin
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

<link href="reports.css" rel="stylesheet" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports Dashboard - Berkeley County Store Admin</title>

<!-- Modern Reports Dashboard -->
<div class="modern-admin-container">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1><i class="fas fa-chart-bar me-3"></i>Reports Dashboard</h1>
                <p class="header-subtitle">View and manage department reports and analytics</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="refreshReports">
                    <i class="fas fa-sync-alt me-2"></i>Refresh Data
                </button>
                <button class="btn btn-secondary" id="exportReports">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner" id="alert-banner" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>
        <span id="alert-message">Loading reports...</span>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Filter Controls -->
        <div class="filter-section">
            <div class="filter-card">
                <h3><i class="fas fa-filter me-2"></i>Filter Reports</h3>
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="dateFrom">From Date:</label>
                        <input type="date" id="dateFrom" class="form-control">
                    </div>
                    <div class="filter-group">
                        <label for="dateTo">To Date:</label>
                        <input type="date" id="dateTo" class="form-control">
                    </div>
                    <div class="filter-group">
                        <label for="departmentFilter">Department:</label>
                        <select id="departmentFilter" class="form-control">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-primary" id="applyFilters">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <button class="btn btn-outline-secondary" id="clearFilters">
                            <i class="fas fa-times me-2"></i>Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="reports-section">
            <div class="reports-card">
                <div class="card-header">
                    <h3><i class="fas fa-table me-2"></i>Past Reports</h3>
                    <div class="table-actions">
                        <button class="btn btn-sm btn-outline-primary" id="toggleTableView">
                            <i class="fas fa-th me-2"></i>Toggle View
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <div class="loading-state" id="loadingState">
                        <div class="spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <p>Loading reports data...</p>
                    </div>
                    <div class="table-responsive" id="reportsTableContainer" style="display: none;">
                        <!-- Table will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="summary-content">
                        <h4>Total Amount</h4>
                        <p class="summary-value" id="totalAmount">$0.00</p>
                        <span class="summary-label">All Departments</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="summary-content">
                        <h4>Departments</h4>
                        <p class="summary-value" id="departmentCount">0</p>
                        <span class="summary-label">Active Departments</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="summary-content">
                        <h4>This Month</h4>
                        <p class="summary-value" id="monthlyAmount">$0.00</p>
                        <span class="summary-label">Current Month</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h4>Average Order</h4>
                        <p class="summary-value" id="averageOrder">$0.00</p>
                        <span class="summary-label">Per Department</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Report Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReport">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="reports.js"></script>

</body>

</html>