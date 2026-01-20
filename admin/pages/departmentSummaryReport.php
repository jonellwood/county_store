<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../signin/signin.php");
    exit;
}
if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}

// Include our modern header
include "../../components/header.php";
?>

<!-- Page-specific CSS -->
<link href="departmentSummaryReport.css" rel="stylesheet" />

<!-- Modern Layout Container -->
<div class="admin-dashboard-container">
    <!-- Alert Banner -->
    <div class="alert-banner">
        <i class="fas fa-chart-bar"></i>
        <span>Department Summary Report - View spending by department and fiscal year</span>
    </div>

    <!-- Controls Section -->
    <div class="controls-section">
        <div class="department-selector">
            <label>
                <i class="fas fa-building"></i>
                Select Department:
            </label>
            <select id="departmentSelect" onchange="departmentSummary(this.value)">
                <option value="">-- Loading departments... --</option>
            </select>
        </div>
        <button class="btn btn-print hide-from-printer" onclick="printPage()">
            <i class="fas fa-print"></i>
            Print Report
        </button>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="main">
        <!-- Initial State -->
        <div class="no-data-placeholder" id="placeholder">
            <i class="fas fa-file-invoice-dollar"></i>
            <h3>Select a Department</h3>
            <p>Choose a department from the dropdown above to generate a summary report</p>
        </div>

        <!-- Report Container (hidden initially) -->
        <div class="report-grid" id="reportGrid" style="display: none;">
            <div class="report-card" id="currentFYCard">
                <div class="report-card-header">
                    <h2><i class="fas fa-calendar-alt"></i> <span id="currentFYTitle">Current Fiscal Year</span></h2>
                </div>
                <div class="report-card-body">
                    <table class="report-table" id="currentFYTable">
                        <tbody id="currentFYBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="report-card" id="prevFYCard">
                <div class="report-card-header">
                    <h2><i class="fas fa-history"></i> <span id="prevFYTitle">Previous Fiscal Year</span></h2>
                </div>
                <div class="report-card-body">
                    <table class="report-table" id="prevFYTable">
                        <tbody id="prevFYBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Loading State (hidden initially) -->
        <div class="loading-state" id="loadingState" style="display: none;">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading report data...</div>
        </div>
    </div>
</div>

<script>
    // Print functionality
    function printPage() {
        window.print();
    }

    // Currency formatter
    function currencyFormat(number) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(number);
    }

    // Get status badge HTML
    function getStatusBadge(status) {
        const statusLower = status.toLowerCase();
        const icons = {
            'pending': 'fas fa-clock',
            'approved': 'fas fa-check-circle',
            'denied': 'fas fa-times-circle',
            'ordered': 'fas fa-shopping-cart',
            'received': 'fas fa-box-open'
        };
        const icon = icons[statusLower] || 'fas fa-circle';
        return `<span class="status-badge ${statusLower}"><i class="${icon}"></i> ${status}</span>`;
    }

    // Get employee initials
    function getInitials(name) {
        return name.split(' ')
            .map(n => n.charAt(0).toUpperCase())
            .slice(0, 2)
            .join('');
    }

    // Load departments
    async function getDepartments() {
        try {
            const response = await fetch('fetch-departments.php');
            const data = await response.json();
            
            const select = document.getElementById('departmentSelect');
            let options = '<option value="">-- Select a Department --</option>';
            
            data.forEach(dept => {
                options += `<option value="${dept.department}">${dept.dep_name} (${dept.department})</option>`;
            });
            
            select.innerHTML = options;
        } catch (error) {
            console.error('Error loading departments:', error);
            document.getElementById('departmentSelect').innerHTML = 
                '<option value="">Error loading departments</option>';
        }
    }

    // Load department summary
    async function departmentSummary(dep) {
        if (!dep) {
            document.getElementById('placeholder').style.display = 'flex';
            document.getElementById('reportGrid').style.display = 'none';
            return;
        }

        // Show loading state
        document.getElementById('placeholder').style.display = 'none';
        document.getElementById('reportGrid').style.display = 'none';
        document.getElementById('loadingState').style.display = 'flex';

        try {
            const response = await fetch('fetchDepartmentSummaryReportData.php?dept=' + dep);
            const data = await response.json();

            const years = data[2];
            const currentFYData = data[0];
            const prevFYData = data[1];

            // Update titles
            document.getElementById('currentFYTitle').textContent = 
                `FY ${years.current_fy_start_year} - ${years.current_fy_end_year}`;
            document.getElementById('prevFYTitle').textContent = 
                `FY ${years.prev_fy_start_year} - ${years.prev_fy_end_year}`;

            // Render current FY data
            document.getElementById('currentFYBody').innerHTML = buildTableContent(currentFYData);

            // Render previous FY data
            document.getElementById('prevFYBody').innerHTML = buildTableContent(prevFYData);

            // Show report
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('reportGrid').style.display = 'grid';

        } catch (error) {
            console.error('Error fetching data:', error);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('placeholder').style.display = 'flex';
            document.getElementById('placeholder').innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error Loading Report</h3>
                <p>There was a problem loading the report data. Please try again.</p>
            `;
        }
    }

    // Build table content for fiscal year data
    function buildTableContent(fyData) {
        let html = '';
        
        if (!fyData || Object.keys(fyData).length === 0) {
            return `
                <tr>
                    <td colspan="2" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                        No data available for this fiscal year
                    </td>
                </tr>
            `;
        }

        for (const empId in fyData) {
            const employee = fyData[empId];
            const initials = getInitials(employee.name);
            
            // Employee header row
            html += `
                <tr class="employee-header">
                    <td>
                        <span class="emp-avatar">${initials}</span>
                        ${employee.name}
                    </td>
                    <td style="text-align: right;">#${employee.emp_id}</td>
                </tr>
            `;

            // Data rows for each status
            if (employee.totals && employee.totals.length > 0) {
                employee.totals.forEach(order => {
                    html += `
                        <tr class="data-row">
                            <td>${getStatusBadge(order.status)}</td>
                            <td class="amount">${currencyFormat(order.total_line_item_total)}</td>
                        </tr>
                    `;
                });
            }
        }

        return html;
    }

    // Initialize
    getDepartments();
</script>

</body>
</html>
