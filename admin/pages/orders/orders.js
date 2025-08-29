/**
 * Modern Orders Dashboard JavaScript
 * Berkeley County Store Admin
 * Created: 2025/08/29
 */

class OrdersManager {
	constructor() {
		this.orders = [];
		this.filteredOrders = [];
		this.departments = new Set();
		this.vendors = new Set();
		this.stats = {
			totalOrders: 0,
			totalDepartments: 0,
			totalValue: 0,
			pendingOrders: 0,
		};

		this.init();
	}

	async init() {
		try {
			this.setupEventListeners();
			await this.loadOrders();
			this.populateFilters();
			this.renderOrders();
		} catch (error) {
			console.error('Error initializing OrdersManager:', error);
			this.showError('Failed to initialize dashboard');
		}
	}

	setupEventListeners() {
		// Refresh button
		document.getElementById('refreshBtn')?.addEventListener('click', () => {
			this.loadOrders();
		});

		// Export button
		document.getElementById('exportBtn')?.addEventListener('click', () => {
			this.exportOrders();
		});

		// Filter controls
		document.getElementById('applyFilters')?.addEventListener('click', () => {
			this.applyFilters();
		});

		document.getElementById('clearFilters')?.addEventListener('click', () => {
			this.clearFilters();
		});

		// Retry button
		document.getElementById('retryBtn')?.addEventListener('click', () => {
			this.loadOrders();
		});

		// Modal events
		document.getElementById('markOrderedBtn')?.addEventListener('click', () => {
			this.markDepartmentAsOrdered();
		});

		document
			.getElementById('generateReportBtn')
			?.addEventListener('click', () => {
				this.generateVendorReport();
			});
	}

	async loadOrders() {
		try {
			this.showLoading();

			const response = await fetch('./api.php');
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (data.error) {
				throw new Error(data.error);
			}

			this.orders = Array.isArray(data) ? data : [];
			this.processOrderData();
			this.updateStats();
			this.renderOrders();
		} catch (error) {
			console.error('Error loading orders:', error);
			this.showError(`Error loading orders: ${error.message}`);
		}
	}

	processOrderData() {
		// Group orders by department
		this.departmentGroups = this.orders.reduce((groups, order) => {
			const deptKey = `${order.department}-${order.dep_name}`;
			if (!groups[deptKey]) {
				groups[deptKey] = {
					department: order.department,
					dep_name: order.dep_name,
					orders: [],
					totalValue: 0,
					totalItems: 0,
				};
			}
			groups[deptKey].orders.push(order);
			groups[deptKey].totalValue += parseFloat(order.line_item_total || 0);
			groups[deptKey].totalItems += parseInt(order.quantity || 0);

			// Collect unique departments and vendors for filters
			this.departments.add(order.dep_name);
			if (order.vendor_number_finance) {
				this.vendors.add(order.vendor_number_finance);
			}

			return groups;
		}, {});

		this.filteredOrders = [...this.orders];
	}

	updateStats() {
		const departments = Object.keys(this.departmentGroups);

		this.stats = {
			totalOrders: this.orders.length,
			totalDepartments: departments.length,
			totalValue: this.orders.reduce(
				(sum, order) => sum + parseFloat(order.line_item_total || 0),
				0
			),
			pendingOrders: this.orders.filter((order) => !order.ordered_date).length,
		};

		// Update stat cards
		document.getElementById('totalOrders').textContent =
			this.stats.totalOrders.toLocaleString();
		document.getElementById('totalDepartments').textContent =
			this.stats.totalDepartments;
		document.getElementById(
			'totalValue'
		).textContent = `$${this.stats.totalValue.toLocaleString('en-US', {
			minimumFractionDigits: 2,
		})}`;
		document.getElementById('pendingOrders').textContent =
			this.stats.pendingOrders;
	}

	populateFilters() {
		// Populate department filter
		const departmentFilter = document.getElementById('departmentFilter');
		if (departmentFilter) {
			departmentFilter.innerHTML = '<option value="">All Departments</option>';
			[...this.departments].sort().forEach((dept) => {
				const option = document.createElement('option');
				option.value = dept;
				option.textContent = dept;
				departmentFilter.appendChild(option);
			});
		}

		// Populate vendor filter
		const vendorFilter = document.getElementById('vendorFilter');
		if (vendorFilter) {
			vendorFilter.innerHTML = '<option value="">All Vendors</option>';
			[...this.vendors].sort().forEach((vendor) => {
				const option = document.createElement('option');
				option.value = vendor;
				option.textContent = `Vendor ${vendor}`;
				vendorFilter.appendChild(option);
			});
		}
	}

	applyFilters() {
		const departmentFilter = document.getElementById('departmentFilter')?.value;
		const vendorFilter = document.getElementById('vendorFilter')?.value;
		const statusFilter = document.getElementById('statusFilter')?.value;

		this.filteredOrders = this.orders.filter((order) => {
			const matchesDepartment =
				!departmentFilter || order.dep_name === departmentFilter;
			const matchesVendor =
				!vendorFilter || order.vendor_number_finance === vendorFilter;
			const matchesStatus =
				!statusFilter || this.getOrderStatus(order) === statusFilter;

			return matchesDepartment && matchesVendor && matchesStatus;
		});

		// Reprocess with filtered data
		this.processFilteredData();
		this.renderOrders();
	}

	processFilteredData() {
		this.departmentGroups = this.filteredOrders.reduce((groups, order) => {
			const deptKey = `${order.department}-${order.dep_name}`;
			if (!groups[deptKey]) {
				groups[deptKey] = {
					department: order.department,
					dep_name: order.dep_name,
					orders: [],
					totalValue: 0,
					totalItems: 0,
				};
			}
			groups[deptKey].orders.push(order);
			groups[deptKey].totalValue += parseFloat(order.line_item_total || 0);
			groups[deptKey].totalItems += parseInt(order.quantity || 0);

			return groups;
		}, {});
	}

	clearFilters() {
		document.getElementById('departmentFilter').value = '';
		document.getElementById('vendorFilter').value = '';
		document.getElementById('statusFilter').value = '';

		this.filteredOrders = [...this.orders];
		this.processFilteredData();
		this.renderOrders();
	}

	getOrderStatus(order) {
		// Use the actual status field from the database
		if (order.status) {
			return order.status.toLowerCase();
		}

		// Fallback logic (shouldn't be needed now)
		if (order.ordered_date) return 'ordered';
		if (order.approved_date) return 'approved';
		return 'pending';
	}

	renderOrders() {
		const container = document.getElementById('ordersList');
		const ordersContent = document.getElementById('ordersContent');
		const loadingState = document.getElementById('loadingState');
		const errorState = document.getElementById('errorState');
		const emptyState = document.getElementById('emptyState');

		// Hide all states
		loadingState.style.display = 'none';
		errorState.style.display = 'none';
		emptyState.style.display = 'none';
		ordersContent.style.display = 'none';

		if (
			!this.departmentGroups ||
			Object.keys(this.departmentGroups).length === 0
		) {
			emptyState.style.display = 'flex';
			return;
		}

		ordersContent.style.display = 'block';

		const departmentCards = Object.values(this.departmentGroups)
			.sort((a, b) => a.dep_name.localeCompare(b.dep_name))
			.map((dept) => this.createDepartmentCard(dept))
			.join('');

		container.innerHTML = departmentCards;
		this.attachOrderEventListeners();
	}

	createDepartmentCard(department) {
		const orderRows = department.orders
			.map((order) => this.createOrderRow(order))
			.join('');

		return `
            <div class="dept-order-card">
                <div class="dept-card-header">
                    <div>
                        <h3 class="dept-title">
                            <i class="fas fa-building"></i>
                            ${this.escapeHtml(department.dep_name)}
                        </h3>
                    </div>
                    
                    <div class="dept-stats">
                        <div class="dept-stat">
                            <span class="dept-stat-number">${
															department.orders.length
														}</span>
                            <span class="dept-stat-label">Orders</span>
                        </div>
                        <div class="dept-stat">
                            <span class="dept-stat-number">${
															department.totalItems
														}</span>
                            <span class="dept-stat-label">Items</span>
                        </div>
                        <div class="dept-stat">
                            <span class="dept-stat-number">$${department.totalValue.toLocaleString(
															'en-US',
															{ minimumFractionDigits: 2 }
														)}</span>
                            <span class="dept-stat-label">Total</span>
                        </div>
                    </div>
                    
                    <div class="dept-actions">
                        <button class="btn btn-primary btn-sm" onclick="ordersManager.showDepartmentDetails('${
													department.department
												}')">
                            <i class="fas fa-eye"></i>
                            View Details
                        </button>
                        <button class="btn btn-success btn-sm" onclick="ordersManager.markDepartmentOrdered('${
													department.department
												}')">
                            <i class="fas fa-check"></i>
                            Order All
                        </button>
                        <!-- <button class="btn btn-warning btn-sm" onclick="ordersManager.generateDepartmentReport('${
													department.department
												}')">
                            <i class="fas fa-file-alt"></i>
                            Generate Report
                        </button> -->
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Requested For</th>
                                <th>Quantity</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Total</th>
                                <th>Vendor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${orderRows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
	}

	createOrderRow(order) {
		const status = this.getOrderStatus(order);
		const statusClass = `status-${status}`;

		return `
            <tr>
                <td>
                    <div class="product-info">
                        <div class="product-name">${this.escapeHtml(
													order.product_name || 'Unknown Product'
												)}</div>
                        <div class="product-details">Code: ${this.escapeHtml(
													order.product_code || 'N/A'
												)}</div>
                    </div>
                </td>
                <td>${this.escapeHtml(order.req_for || 'Unknown')}</td>
                <td>${order.quantity || 0}</td>
                <td>${this.escapeHtml(order.size_name || 'N/A')}</td>
                <td>${this.escapeHtml(order.color_name || 'N/A')}</td>
                <td>$${parseFloat(order.line_item_total || 0).toLocaleString(
									'en-US',
									{ minimumFractionDigits: 2 }
								)}</td>
                <td>Vendor ${order.vendor_number_finance || 'N/A'}</td>
                <td><span class="status-badge ${statusClass}">${status}</span></td>
            </tr>
        `;
	}

	attachOrderEventListeners() {
		// Event listeners for dynamically created elements are handled via onclick attributes
		// This could be refactored to use event delegation if needed
	}

	showDepartmentDetails(departmentId) {
		const department = Object.values(this.departmentGroups).find(
			(d) => d.department == departmentId
		);
		if (!department) return;

		const modal = new bootstrap.Modal(document.getElementById('orderModal'));
		const modalBody = document.getElementById('orderModalBody');

		const detailsHtml = `
            <h5>${department.dep_name} - Order Details</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Requested For</th>
                            <th>Quantity</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${department.orders
													.map(
														(order) => `
                            <tr>
                                <td>${this.escapeHtml(
																	order.product_name || 'Unknown'
																)}</td>
                                <td>${this.escapeHtml(
																	order.req_for || 'Unknown'
																)}</td>
                                <td>${order.quantity || 0}</td>
                                <td>${this.escapeHtml(
																	order.size_name || 'N/A'
																)}</td>
                                <td>${this.escapeHtml(
																	order.color_name || 'N/A'
																)}</td>
                                <td>$${parseFloat(
																	order.line_item_total || 0
																).toFixed(2)}</td>
                            </tr>
                        `
													)
													.join('')}
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <strong>Total Orders: ${department.orders.length}</strong><br>
                <strong>Total Items: ${department.totalItems}</strong><br>
                <strong>Total Value: $${department.totalValue.toFixed(
									2
								)}</strong>
            </div>
        `;

		modalBody.innerHTML = detailsHtml;
		modal.show();
	}

	markDepartmentOrdered(departmentId) {
		const department = Object.values(this.departmentGroups).find(
			(d) => d.department == departmentId
		);
		if (!department) return;

		// Show the PO number modal
		this.showPOModal(department);
	}

	showPOModal(department) {
		// Create and show a modal for PO number input
		const modalHtml = `
			<div class="modal fade" id="poModal" tabindex="-1" aria-labelledby="poModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="poModalLabel">
								<i class="fas fa-shopping-cart me-2"></i>
								Place Order for ${this.escapeHtml(department.dep_name)}
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<p>You are about to place an order for <strong>${
									department.orders.length
								} items</strong> totaling <strong>$${department.totalValue.toFixed(
			2
		)}</strong> for the <strong>${this.escapeHtml(
			department.dep_name
		)}</strong> department.</p>
							</div>
							<div class="mb-3">
								<label for="poNumber" class="form-label">
									<i class="fas fa-hashtag me-1"></i>
									PO Number <span class="text-muted">(optional)</span>
								</label>
								<input type="text" class="form-control" id="poNumber" placeholder="Enter PO number if applicable">
								<div class="form-text">Leave blank if no PO number is required</div>
							</div>
							<div id="poModalError" class="alert alert-danger d-none"></div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
								<i class="fas fa-times me-1"></i>
								Cancel
							</button>
							<button type="button" class="btn btn-success" id="confirmOrderBtn" onclick="ordersManager.confirmPlaceOrder('${
								department.department
							}')">
								<i class="fas fa-check me-1"></i>
								Place Order for Department ${department.department}
							</button>
						</div>
					</div>
				</div>
			</div>
		`;

		// Remove existing modal if present
		const existingModal = document.getElementById('poModal');
		if (existingModal) {
			existingModal.remove();
		}

		// Add modal to body
		document.body.insertAdjacentHTML('beforeend', modalHtml);

		// Show the modal
		const modal = new bootstrap.Modal(document.getElementById('poModal'));
		modal.show();

		// Focus on PO number input when modal is shown
		document
			.getElementById('poModal')
			.addEventListener('shown.bs.modal', () => {
				document.getElementById('poNumber').focus();
			});

		// Handle Enter key in PO number input
		document.getElementById('poNumber').addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				this.confirmPlaceOrder(department.department);
			}
		});

		// Clean up modal when closed
		document
			.getElementById('poModal')
			.addEventListener('hidden.bs.modal', () => {
				document.getElementById('poModal').remove();
			});
	}

	async confirmPlaceOrder(departmentId) {
		const poNumber = document.getElementById('poNumber').value.trim();
		const confirmBtn = document.getElementById('confirmOrderBtn');
		const errorDiv = document.getElementById('poModalError');

		// Hide any previous errors
		errorDiv.classList.add('d-none');

		// Show loading state
		confirmBtn.disabled = true;
		confirmBtn.innerHTML =
			'<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

		try {
			const response = await fetch('./api.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					action: 'placeOrder',
					departmentId: departmentId,
					poNumber: poNumber || null,
				}),
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const result = await response.json();

			if (result.success) {
				// Close the modal
				const modal = bootstrap.Modal.getInstance(
					document.getElementById('poModal')
				);
				modal.hide();

				// Show success message
				this.showSuccessMessage(
					`Successfully placed order for department ${departmentId}${
						poNumber ? ` with PO# ${poNumber}` : ''
					}`
				);

				// Refresh the data
				this.loadOrders();
			} else {
				throw new Error(result.error || 'Failed to place order');
			}
		} catch (error) {
			console.error('Error placing order:', error);

			// Show error in modal
			errorDiv.textContent = `Error placing order: ${error.message}`;
			errorDiv.classList.remove('d-none');

			// Reset button
			confirmBtn.disabled = false;
			confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Place Order';
		}
	}

	showSuccessMessage(message) {
		// Create a temporary success toast/alert
		const alertHtml = `
			<div class="alert alert-success alert-dismissible fade show position-fixed" 
				 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
				 role="alert">
				<i class="fas fa-check-circle me-2"></i>
				${message}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		`;

		document.body.insertAdjacentHTML('beforeend', alertHtml);

		// Auto-remove after 5 seconds
		setTimeout(() => {
			const alert = document.querySelector('.alert-success');
			if (alert) {
				alert.remove();
			}
		}, 5000);
	}

	generateDepartmentReport(departmentId) {
		const department = Object.values(this.departmentGroups).find(
			(d) => d.department == departmentId
		);
		if (!department) return;

		// This would typically generate a vendor report
		console.log(`Generating report for department ${departmentId}`);

		// For now, redirect to vendor report page
		const firstOrder = department.orders[0];
		if (firstOrder && firstOrder.order_id) {
			window.open(`../vendorReport.php?uid=${firstOrder.order_id}`, '_blank');
		}
	}

	async updateOrderStatus(departmentId, status) {
		try {
			const response = await fetch('./api.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					action: 'updateStatus',
					departmentId: departmentId,
					status: status,
				}),
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const result = await response.json();

			if (result.success) {
				this.loadOrders(); // Refresh data
			} else {
				throw new Error(result.error || 'Failed to update status');
			}
		} catch (error) {
			console.error('Error updating order status:', error);
			alert(`Error updating order status: ${error.message}`);
		}
	}

	exportOrders() {
		try {
			const csvContent = this.generateCSV();
			const blob = new Blob([csvContent], { type: 'text/csv' });
			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `department_orders_${
				new Date().toISOString().split('T')[0]
			}.csv`;
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			window.URL.revokeObjectURL(url);
		} catch (error) {
			console.error('Error exporting orders:', error);
			alert('Error exporting orders. Please try again.');
		}
	}

	generateCSV() {
		const headers = [
			'Department',
			'Product Name',
			'Product Code',
			'Requested For',
			'Quantity',
			'Size',
			'Color',
			'Total',
			'Vendor',
			'Status',
		];

		const rows = this.filteredOrders.map((order) => [
			order.dep_name || '',
			order.product_name || '',
			order.product_code || '',
			order.req_for || '',
			order.quantity || 0,
			order.size_name || '',
			order.color_name || '',
			order.line_item_total || 0,
			order.vendor_number_finance || '',
			this.getOrderStatus(order),
		]);

		return [headers, ...rows]
			.map((row) =>
				row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(',')
			)
			.join('\n');
	}

	showLoading() {
		document.getElementById('loadingState').style.display = 'flex';
		document.getElementById('errorState').style.display = 'none';
		document.getElementById('emptyState').style.display = 'none';
		document.getElementById('ordersContent').style.display = 'none';
	}

	showError(message) {
		document.getElementById('errorMessage').textContent = message;
		document.getElementById('errorState').style.display = 'flex';
		document.getElementById('loadingState').style.display = 'none';
		document.getElementById('emptyState').style.display = 'none';
		document.getElementById('ordersContent').style.display = 'none';
	}

	escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
}

// Initialize the OrdersManager when the page loads
let ordersManager;
document.addEventListener('DOMContentLoaded', () => {
	ordersManager = new OrdersManager();
});
