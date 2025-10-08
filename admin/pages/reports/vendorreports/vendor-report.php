<?php
// Modern Vendor Report - Berkeley County Store Admin
// Created: 2025/09/30
// Replaces the legacy vendorReport.php with modern filterable interface

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../signin/signin.php");
    exit;
}

// Get UID parameter
$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
if (empty($uid)) {
    header("Location: ../../orders/");
    exit;
}

// Include our modern header
include "../../../../components/header.php";
?>

<link href="vendor-report.css" rel="stylesheet" />
<!-- Bootstrap Override Styles - Critical for dark theme -->
<style>
    /* Ensure our modern container works with the admin layout */
    .modern-admin-container {
        position: relative;
        z-index: 1;
    }

    /* Override any conflicting styles */
    body {
        background: var(--bg-primary) !important;
        color: var(--text-primary) !important;
    }

    /* Critical Table Overrides - Bootstrap is interfering */
    .items-table {
        background: transparent !important;
        color: var(--text-primary) !important;
    }

    .items-table thead th {
        background: var(--bg-tertiary) !important;
        color: var(--text-primary) !important;
        border: none !important;
        border-bottom: 2px solid var(--border-primary) !important;
    }

    .items-table tbody tr {
        background: transparent !important;
        border-bottom: 1px solid var(--border-primary) !important;
    }

    .items-table tbody tr:hover {
        background: var(--bg-hover) !important;
    }

    .items-table tbody td {
        background: transparent !important;
        color: var(--text-primary) !important;
        border: none !important;
    }

    /* Fix any other Bootstrap table overrides */
    .table> :not(caption)>*>* {
        background: transparent !important;
        border-bottom-width: 1px !important;
        border-color: var(--border-primary) !important;
    }

    .table-hover>tbody>tr:hover>* {
        background-color: var(--bg-hover) !important;
        color: var(--text-primary) !important;
    }

    /* Fix Bootstrap button and form overrides */
    .btn {
        border: none !important;
    }

    .btn-primary {
        background: var(--primary) !important;
        color: white !important;
    }

    .btn-secondary {
        background: var(--bg-tertiary) !important;
        color: var(--text-primary) !important;
    }

    .form-control,
    .form-select {
        background: var(--bg-input) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-primary) !important;
    }

    .form-control:focus,
    .form-select:focus {
        background: var(--bg-input) !important;
        color: var(--text-primary) !important;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
    }

    /* Fix dropdown menu styling */
    .dropdown-menu {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-primary) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-xl) !important;
    }

    .dropdown-item {
        color: var(--text-primary) !important;
        background: transparent !important;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        background: var(--bg-hover) !important;
        color: var(--text-primary) !important;
    }

    /* Ensure navigation dropdown works */
    .navbar .dropdown-menu {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-primary) !important;
    }

    /* Fix footer positioning conflicts */
    .footer-holder {
        z-index: 10 !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
    }

    /* Ensure our content is above the footer */
    .modern-admin-container {
        z-index: 10 !important;
        position: relative !important;
    }
</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vendor Report - Berkeley County Store Admin</title>

<!-- Modern Vendor Report Dashboard -->
<div class="modern-admin-container">
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1><i class="fas fa-file-invoice me-3"></i>Vendor Report</h1>
                <p class="header-subtitle">
                    <i class="fas fa-building me-2"></i>
                    <span id="departmentDisplay">Loading...</span>
                </p>
                <div class="order-metadata">
                    <span class="metadata-item">
                        <i class="fas fa-calendar me-1"></i>
                        <span id="orderDate">-</span>
                    </span>
                    <span class="metadata-item">
                        <i class="fas fa-receipt me-1"></i>
                        PO: <span id="poNumber">-</span>
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="refreshReport">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="exportPDF"><i class="fas fa-file-pdf me-2"></i>PDF Report</a></li>
                        <li><a class="dropdown-item" href="#" id="exportHTML"><i class="fas fa-file-code me-2"></i>Static HTML</a></li>
                        <li><a class="dropdown-item" href="#" id="printReport"><i class="fas fa-print me-2"></i>Print</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner" id="alert-banner" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>
        <span id="alert-message">Loading vendor report...</span>
    </div>

    <!-- Loading State -->
    <div class="loading-container" id="loading-container">
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
        <p class="loading-text">Loading vendor report data...</p>
    </div>

    <!-- Filters and Controls -->
    <div class="filters-section" id="filters-section" style="display: none;">
        <div class="section-header">
            <h3><i class="fas fa-filter me-2"></i>Filters & Sorting</h3>
            <button class="btn btn-outline-secondary btn-sm" id="clearFilters">
                <i class="fas fa-times me-2"></i>Clear All
            </button>
        </div>

        <div class="filters-grid">
            <!-- Search -->
            <div class="filter-group">
                <label for="searchInput" class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Product, color, size...">
            </div>

            <!-- Vendor Filter -->
            <div class="filter-group">
                <label for="vendorFilter" class="form-label">Vendor</label>
                <select class="form-select" id="vendorFilter">
                    <option value="">All Vendors</option>
                </select>
            </div>

            <!-- Department Filter -->
            <div class="filter-group">
                <label for="departmentFilter" class="form-label">Department</label>
                <select class="form-select" id="departmentFilter">
                    <option value="">All Departments</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="filter-group">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                </select>
            </div>

            <!-- Product Type Filter -->
            <div class="filter-group">
                <label for="productFilter" class="form-label">Product Type</label>
                <select class="form-select" id="productFilter">
                    <option value="">All Products</option>
                </select>
            </div>

            <!-- Size Filter -->
            <div class="filter-group">
                <label for="sizeFilter" class="form-label">Size</label>
                <select class="form-select" id="sizeFilter">
                    <option value="">All Sizes</option>
                </select>
            </div>

            <!-- Logo Filter -->
            <div class="filter-group">
                <label for="logoFilter" class="form-label">Logo</label>
                <select class="form-select" id="logoFilter">
                    <option value="">All Logos</option>
                </select>
            </div>
        </div>

        <!-- Advanced Sorting -->
        <div class="sorting-section">
            <h4><i class="fas fa-sort me-2"></i>Multi-Level Sorting</h4>
            <div class="sort-controls">
                <div class="sort-level">
                    <label>Primary Sort:</label>
                    <select class="form-select" id="primarySort">
                        <option value="vendor_name">Vendor</option>
                        <option value="product_code">Product Code</option>
                        <option value="dep_name">Department</option>
                        <option value="size_name">Size</option>
                        <option value="color_name">Color</option>
                        <option value="status">Status</option>
                    </select>
                    <select class="form-select sort-direction" id="primaryDirection">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>

                <div class="sort-level">
                    <label>Secondary Sort:</label>
                    <select class="form-select" id="secondarySort">
                        <option value="">None</option>
                        <option value="vendor_name">Vendor</option>
                        <option value="product_code">Product Code</option>
                        <option value="dep_name">Department</option>
                        <option value="size_name">Size</option>
                        <option value="color_name">Color</option>
                        <option value="status">Status</option>
                    </select>
                    <select class="form-select sort-direction" id="secondaryDirection">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>

                <div class="sort-level">
                    <label>Tertiary Sort:</label>
                    <select class="form-select" id="tertiarySort">
                        <option value="">None</option>
                        <option value="vendor_name">Vendor</option>
                        <option value="product_code">Product Code</option>
                        <option value="dep_name">Department</option>
                        <option value="size_name">Size</option>
                        <option value="color_name">Color</option>
                        <option value="status">Status</option>
                    </select>
                    <select class="form-select sort-direction" id="tertiaryDirection">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="results-summary">
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label">Vendors:</span>
                    <span class="stat-value" id="vendorCount">-</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Items:</span>
                    <span class="stat-value" id="itemCount">-</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Value:</span>
                    <span class="stat-value" id="totalValue">-</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Filtered Items:</span>
                    <span class="stat-value" id="filteredCount">-</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="report-content" id="report-content" style="display: none;">
        <!-- Vendor groups will be populated here -->
    </div>

    <!-- Technical Footer -->
    <div class="technical-footer" id="technical-footer" style="display: none;">
        <div class="footer-content">
            <div class="technical-info">
                <span class="tech-label">Order Instance ID:</span>
                <span class="tech-value" id="orderInstanceId"><?php echo htmlspecialchars($uid); ?></span>
            </div>
            <div class="technical-info">
                <span class="tech-label">Generated:</span>
                <span class="tech-value" id="generatedAt">-</span>
            </div>
            <div class="technical-info">
                <span class="tech-label">System:</span>
                <span class="tech-value">Berkeley County Store Admin</span>
            </div>
        </div>
    </div>
</div>

<!-- Vendor Group Template -->
<template id="vendor-group-template">
    <div class="vendor-group" data-vendor-id="">
        <div class="vendor-header">
            <div class="vendor-info">
                <h3 class="vendor-name"></h3>
                <div class="vendor-stats">
                    <span class="vendor-stat">
                        <i class="fas fa-list me-2"></i>
                        <span class="items-count">0</span> <span class="stat-label">Line Items</span>
                    </span>
                    <span class="vendor-stat total-qty-stat">
                        <i class="fas fa-calculator me-2"></i>
                        <span class="quantity-count">0</span> <span class="stat-label">Total Qty</span>
                    </span>
                    <span class="vendor-stat">
                        <i class="fas fa-dollar-sign me-2"></i>
                        <span class="value-count">$0.00</span>
                    </span>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm toggle-vendor" data-bs-toggle="collapse">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="vendor-items collapse show">
            <div class="items-table-container">
                <table class="table table-hover items-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="product_code">
                                Product
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="department">
                                Department
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="color">
                                Details
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="emp_quantity">
                                Quantity
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="employee_cost">
                                Pricing
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="logo">
                                Logo
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="emp_name">
                                Employee
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                            <th class="sortable" data-sort="emp_status">
                                Status
                                <i class="fas fa-sort sort-icon" data-direction="none"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="vendor-items-body">
                        <!-- Items will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<!-- Item Row Template -->
<template id="item-row-template">
    <tr class="item-row" data-item-id="">
        <td class="product-cell">
            <div class="product-info">
                <strong class="product-name"></strong>
                <div class="product-code"></div>
            </div>
        </td>
        <td class="department-cell">
            <span class="department-name"></span>
        </td>
        <td class="details-cell">
            <div class="item-details">
                <span class="detail-item color-detail">
                    <i class="fas fa-palette me-1"></i>
                    <span class="color-name"></span>
                </span>
                <span class="detail-item size-detail">
                    <i class="fas fa-ruler me-1"></i>
                    <span class="size-name"></span>
                </span>
            </div>
        </td>
        <td class="quantity-cell">
            <span class="quantity-badge"></span>
        </td>
        <td class="pricing-cell">
            <div class="pricing-info">
                <div class="price-line">
                    <span class="price-label">Unit:</span>
                    <span class="unit-price"></span>
                </div>
                <div class="price-line">
                    <span class="price-label">Logo:</span>
                    <span class="logo-fee"></span>
                </div>
                <div class="price-line">
                    <span class="price-label">Tax:</span>
                    <span class="tax-amount"></span>
                </div>
                <div class="price-line total-line">
                    <span class="price-label">Total:</span>
                    <span class="total-amount"></span>
                </div>
            </div>
        </td>
        <td class="logo-cell">
            <div class="logo-container">
                <img class="logo-image" src="" alt="Logo" onerror="this.style.display='none'">
                <div class="logo-placement"></div>
            </div>
        </td>
        <td class="employee-cell">
            <div class="employee-info">
                <span class="employee-name"></span>
                <div class="employee-comment"></div>
                <div class="comments-list"></div>
            </div>
        </td>
        <td class="status-cell">
            <span class="status-badge"></span>
        </td>
    </tr>
</template>

<script src="vendor-report.js"></script>

<!-- Note: Bootstrap JS is already loaded in header.php, no need to load again -->
<!-- Ensure Bootstrap dropdowns are properly initialized -->
<script>
    // Initialize any dropdowns that might need manual initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure Bootstrap dropdowns work properly
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });

        console.log('Bootstrap dropdowns initialized:', dropdownList.length);
    });
</script>

<?php include "../../../../components/footer.php"; ?>