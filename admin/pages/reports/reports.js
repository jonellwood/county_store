// Modern Reports Dashboard JavaScript
// Berkeley County Store Admin - Enhanced functionality

class ReportsManager {
	constructor() {
		this.reports = [];
		this.filteredReports = [];
		this.departments = [];
		this.isLoading = false;

		this.init();
	}

	async init() {
		this.bindEvents();
		await this.loadReports();
		this.setupDateFilters();
	}

	bindEvents() {
		// Filter events
		document
			.getElementById('applyFilters')
			.addEventListener('click', () => this.applyFilters());
		document
			.getElementById('clearFilters')
			.addEventListener('click', () => this.clearFilters());

		// Header actions
		document
			.getElementById('refreshReports')
			.addEventListener('click', () => this.refreshData());
		document
			.getElementById('exportReports')
			.addEventListener('click', () => this.exportReports());

		// Table actions
		document
			.getElementById('toggleTableView')
			.addEventListener('click', () => this.toggleTableView());

		// Filter inputs
		['dateFrom', 'dateTo', 'departmentFilter'].forEach((id) => {
			document
				.getElementById(id)
				.addEventListener('change', () => this.applyFilters());
		});
	}

	setupDateFilters() {
		const today = new Date();
		const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
		const lastDayOfMonth = new Date(
			today.getFullYear(),
			today.getMonth() + 1,
			0
		);

		document.getElementById('dateFrom').value =
			this.formatDateForInput(firstDayOfMonth);
		document.getElementById('dateTo').value =
			this.formatDateForInput(lastDayOfMonth);
	}

	formatDateForInput(date) {
		return date.toISOString().split('T')[0];
	}

	formatDate(inputDate) {
		const dateObject = new Date(inputDate);
		const month = String(dateObject.getMonth() + 1).padStart(2, '0');
		const day = String(dateObject.getDate()).padStart(2, '0');
		const year = dateObject.getFullYear();
		return `${month}-${day}-${year}`;
	}

	formatCurrency(amount) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		}).format(amount);
	}

	showLoading() {
		this.isLoading = true;
		document.getElementById('loadingState').style.display = 'flex';
		document.getElementById('reportsTableContainer').style.display = 'none';
	}

	hideLoading() {
		this.isLoading = false;
		document.getElementById('loadingState').style.display = 'none';
		document.getElementById('reportsTableContainer').style.display = 'block';
	}

	showAlert(message, type = 'info') {
		const alertBanner = document.getElementById('alert-banner');
		const alertMessage = document.getElementById('alert-message');

		alertMessage.textContent = message;
		alertBanner.style.display = 'flex';

		// Auto-hide after 5 seconds
		setTimeout(() => {
			alertBanner.style.display = 'none';
		}, 5000);
	}

	async loadReports() {
		try {
			this.showLoading();
			this.showAlert('Loading reports data...', 'info');

			const response = await fetch('./api.php');

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (!Array.isArray(data)) {
				throw new Error('Invalid data format received');
			}

			this.reports = data;
			this.filteredReports = [...data];

			this.extractDepartments();
			this.populateDepartmentFilter();
			this.renderReportsTable();
			this.updateSummaryCards();

			this.hideLoading();
			this.showAlert(`Successfully loaded ${data.length} reports`, 'success');
		} catch (error) {
			console.error('Error loading reports:', error);
			this.hideLoading();
			this.showAlert('Error loading reports data. Please try again.', 'error');
		}
	}

	extractDepartments() {
		const deptSet = new Set();
		this.reports.forEach((report) => {
			if (report.dep_name) {
				deptSet.add(report.dep_name);
			}
		});
		this.departments = Array.from(deptSet).sort();
	}

	populateDepartmentFilter() {
		const select = document.getElementById('departmentFilter');
		select.innerHTML = '<option value="">All Departments</option>';

		this.departments.forEach((dept) => {
			const option = document.createElement('option');
			option.value = dept;
			option.textContent = dept;
			select.appendChild(option);
		});
	}

	applyFilters() {
		const dateFrom = document.getElementById('dateFrom').value;
		const dateTo = document.getElementById('dateTo').value;
		const department = document.getElementById('departmentFilter').value;

		this.filteredReports = this.reports.filter((report) => {
			let include = true;

			// Date filters
			if (dateFrom) {
				const reportDate = new Date(report.order_placed);
				const fromDate = new Date(dateFrom);
				if (reportDate < fromDate) include = false;
			}

			if (dateTo) {
				const reportDate = new Date(report.order_placed);
				const toDate = new Date(dateTo);
				toDate.setHours(23, 59, 59, 999); // End of day
				if (reportDate > toDate) include = false;
			}

			// Department filter
			if (department && report.dep_name !== department) {
				include = false;
			}

			return include;
		});

		this.renderReportsTable();
		this.updateSummaryCards();

		this.showAlert(
			`Filtered to ${this.filteredReports.length} reports`,
			'info'
		);
	}

	clearFilters() {
		document.getElementById('dateFrom').value = '';
		document.getElementById('dateTo').value = '';
		document.getElementById('departmentFilter').value = '';

		this.filteredReports = [...this.reports];
		this.renderReportsTable();
		this.updateSummaryCards();

		this.showAlert('Filters cleared', 'info');
	}

	renderReportsTable() {
		const container = document.getElementById('reportsTableContainer');

		if (this.filteredReports.length === 0) {
			container.innerHTML = `
                <div class="no-data-state">
                    <i class="fas fa-chart-bar" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h4>No Reports Found</h4>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
            `;
			return;
		}

		let tableHTML = `
            <table class="styled-table" id="reportsTable">
                <thead>
                    <tr>
                        <th>
                            <i class="fas fa-calendar me-2"></i>Date
                        </th>
                        <th>
                            <i class="fas fa-building me-2"></i>Department
                        </th>
                        <th>
                            <i class="fas fa-dollar-sign me-2"></i>Amount
                        </th>
                        <th width="2em">
                            <p><i class="fas fa-external-link-alt" title="View detailed report"></i></p>
                        </th>
                    </tr>
                </thead>
                <tbody>
        `;

		this.filteredReports.forEach((report) => {
			tableHTML += `
                <tr>
                    <td>${this.formatDate(report.order_placed)}</td>
                    <td>
                        <div class="dept-cell">
                            <strong>${report.dep_name}</strong>
                        </div>
                    </td>
                    <td>
                        <span class="amount-cell">${this.formatCurrency(
													parseFloat(report.total)
												)}</span>
                    </td>
                    <td class="text-center">
                        <a href="vendorreports/vendor-report.php?uid=${
													report.order_inst_id
												}" 
                           target="_blank" 
                           class="report-link"
                           title="View detailed report">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </td>
                </tr>
            `;
		});

		tableHTML += '</tbody></table>';
		container.innerHTML = tableHTML;
	}

	updateSummaryCards() {
		const totalAmount = this.filteredReports.reduce(
			(sum, report) => sum + parseFloat(report.total || 0),
			0
		);

		const departmentCount = new Set(
			this.filteredReports.map((report) => report.dep_name)
		).size;

		const currentMonth = new Date().getMonth();
		const currentYear = new Date().getFullYear();
		const monthlyReports = this.filteredReports.filter((report) => {
			const reportDate = new Date(report.order_placed);
			return (
				reportDate.getMonth() === currentMonth &&
				reportDate.getFullYear() === currentYear
			);
		});

		const monthlyAmount = monthlyReports.reduce(
			(sum, report) => sum + parseFloat(report.total || 0),
			0
		);

		const averageOrder =
			departmentCount > 0 ? totalAmount / departmentCount : 0;

		// Update the display
		document.getElementById('totalAmount').textContent =
			this.formatCurrency(totalAmount);
		document.getElementById('departmentCount').textContent = departmentCount;
		document.getElementById('monthlyAmount').textContent =
			this.formatCurrency(monthlyAmount);
		document.getElementById('averageOrder').textContent =
			this.formatCurrency(averageOrder);
	}

	async refreshData() {
		await this.loadReports();
	}

	exportReports() {
		if (this.filteredReports.length === 0) {
			this.showAlert('No data to export', 'warning');
			return;
		}

		// Create CSV content
		const headers = ['Date', 'Department', 'Amount', 'Order ID'];
		const csvContent = [
			headers.join(','),
			...this.filteredReports.map((report) =>
				[
					this.formatDate(report.order_placed),
					`"${report.dep_name}"`,
					parseFloat(report.total).toFixed(2),
					report.order_inst_id,
				].join(',')
			),
		].join('\n');

		// Create and download file
		const blob = new Blob([csvContent], { type: 'text/csv' });
		const url = window.URL.createObjectURL(blob);
		const a = document.createElement('a');

		const date = new Date().toISOString().split('T')[0];
		a.href = url;
		a.download = `reports-export-${date}.csv`;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		window.URL.revokeObjectURL(url);

		this.showAlert('Reports exported successfully', 'success');
	}

	toggleTableView() {
		// This could be enhanced to switch between table and card view
		this.showAlert('Table view toggle - feature coming soon!', 'info');
	}
}

// Helper functions (maintaining compatibility with existing code)
window.formatDate = function (inputDate) {
	const dateObject = new Date(inputDate);
	const month = String(dateObject.getMonth() + 1).padStart(2, '0');
	const day = String(dateObject.getDate()).padStart(2, '0');
	const year = dateObject.getFullYear();
	return `${month}-${day}-${year}`;
};

window.money_format = function (amount) {
	return new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
	}).format(amount);
};

// Initialize the reports manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	window.reportsManager = new ReportsManager();
});

// Add some additional CSS for dynamic content
const additionalStyles = `
<style>
.no-data-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
}

.dept-cell strong {
    color: var(--text-primary);
}

.amount-cell {
    font-weight: 600;
    color: var(--success);
}

.text-center {
    text-align: center;
}

.alert-banner.success {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
}

.alert-banner.warning {
    background: linear-gradient(135deg, var(--warning) 0%, #f97316 100%);
}

.alert-banner.error {
    background: linear-gradient(135deg, var(--error) 0%, #dc2626 100%);
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);
