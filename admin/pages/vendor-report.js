/**
 * Modern Vendor Report - Berkeley County Store Admin
 * Created: 2025/09/30
 * Advanced filtering, sorting, and export functionality
 */

class VendorReportManager {
	constructor() {
		this.orderInstanceId = null;
		this.rawData = [];
		this.filteredData = [];
		this.filters = {
			search: '',
			vendor: '',
			department: '',
			status: '',
			product: '',
			size: '',
			logo: '',
		};
		this.sortConfig = {
			primary: { field: 'vendor_name', direction: 'asc' },
			secondary: { field: '', direction: 'asc' },
			tertiary: { field: '', direction: 'asc' },
		};

		this.init();
	}

	async init() {
		console.log('🏗️ Initializing Vendor Report Manager...');

		// Get order instance ID from URL or page
		this.orderInstanceId =
			document.getElementById('orderInstanceId')?.textContent;

		if (!this.orderInstanceId) {
			this.showAlert('Missing order instance ID', 'error');
			return;
		}

		this.bindEvents();
		await this.loadData();
	}

	bindEvents() {
		// Refresh button
		document.getElementById('refreshReport')?.addEventListener('click', () => {
			this.loadData();
		});

		// Export buttons
		document.getElementById('exportPDF')?.addEventListener('click', () => {
			this.exportToPDF();
		});

		document.getElementById('exportHTML')?.addEventListener('click', () => {
			this.exportToHTML();
		});

		document.getElementById('printReport')?.addEventListener('click', () => {
			this.printReport();
		});

		// Filter inputs
		document.getElementById('searchInput')?.addEventListener('input', (e) => {
			this.filters.search = e.target.value;
			this.applyFilters();
		});

		document.getElementById('vendorFilter')?.addEventListener('change', (e) => {
			this.filters.vendor = e.target.value;
			this.applyFilters();
		});

		document
			.getElementById('departmentFilter')
			?.addEventListener('change', (e) => {
				this.filters.department = e.target.value;
				this.applyFilters();
			});

		document.getElementById('statusFilter')?.addEventListener('change', (e) => {
			this.filters.status = e.target.value;
			this.applyFilters();
		});

		document
			.getElementById('productFilter')
			?.addEventListener('change', (e) => {
				this.filters.product = e.target.value;
				this.applyFilters();
			});

		document.getElementById('sizeFilter')?.addEventListener('change', (e) => {
			this.filters.size = e.target.value;
			this.applyFilters();
		});

		document.getElementById('logoFilter')?.addEventListener('change', (e) => {
			this.filters.logo = e.target.value;
			this.applyFilters();
		});

		// Clear filters
		document.getElementById('clearFilters')?.addEventListener('click', () => {
			this.clearFilters();
		});

		// Sortable table headers
		document.querySelectorAll('.sortable').forEach((header) => {
			header.addEventListener('click', (e) => {
				const field = e.currentTarget.dataset.sort;
				this.handleHeaderSort(field);
			});
		});

		// Sort controls
		document.getElementById('primarySort')?.addEventListener('change', (e) => {
			this.sortConfig.primary.field = e.target.value;
			this.applySorting();
		});

		document
			.getElementById('primaryDirection')
			?.addEventListener('change', (e) => {
				this.sortConfig.primary.direction = e.target.value;
				this.applySorting();
			});

		document
			.getElementById('secondarySort')
			?.addEventListener('change', (e) => {
				this.sortConfig.secondary.field = e.target.value;
				this.applySorting();
			});

		document
			.getElementById('secondaryDirection')
			?.addEventListener('change', (e) => {
				this.sortConfig.secondary.direction = e.target.value;
				this.applySorting();
			});

		document.getElementById('tertiarySort')?.addEventListener('change', (e) => {
			this.sortConfig.tertiary.field = e.target.value;
			this.applySorting();
		});

		document
			.getElementById('tertiaryDirection')
			?.addEventListener('change', (e) => {
				this.sortConfig.tertiary.direction = e.target.value;
				this.applySorting();
			});
	}

	async loadData() {
		console.log('📊 Loading vendor report data...');
		this.showLoading(true);

		try {
			const response = await fetch(
				`vendor-report-api.php?uid=${encodeURIComponent(this.orderInstanceId)}`
			);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}

			const data = await response.json();

			if (!data.success) {
				throw new Error(data.error || 'Failed to load data');
			}

			console.log('✅ Data loaded successfully:', data);
			this.rawData = data;
			this.updateHeader();
			this.populateFilters();
			this.applyFilters();
			this.updateSummary();
			this.showLoading(false);
			this.showFiltersAndContent(true);
		} catch (error) {
			console.error('❌ Error loading data:', error);
			this.showAlert(`Error loading data: ${error.message}`, 'error');
			this.showLoading(false);
		}
	}

	updateHeader() {
		console.log('📋 Updating header information...');

		if (this.rawData.order_info) {
			// Update department display
			const departmentDisplay = document.getElementById('departmentDisplay');
			if (departmentDisplay) {
				departmentDisplay.textContent =
					this.rawData.order_info.department_display;
			}

			// Update order date
			const orderDate = document.getElementById('orderDate');
			if (orderDate && this.rawData.order_info.order_date) {
				orderDate.textContent = this.formatDate(
					this.rawData.order_info.order_date
				);
			}

			// Update PO number
			const poNumber = document.getElementById('poNumber');
			if (poNumber && this.rawData.order_info.po_number) {
				poNumber.textContent = this.rawData.order_info.po_number;
			}
		}

		// Update generated timestamp
		const generatedAt = document.getElementById('generatedAt');
		if (generatedAt && this.rawData.generated_at) {
			generatedAt.textContent = this.formatDateTime(this.rawData.generated_at);
		}
	}

	populateFilters() {
		console.log('🔧 Populating filter options...');

		// Extract unique values for filters
		const allItems = this.getAllItems();

		const vendors = [
			...new Set(allItems.map((item) => item.vendor_name)),
		].sort();
		const departments = [
			...new Set(allItems.map((item) => item.dep_name)),
		].sort();
		const statuses = [...new Set(allItems.map((item) => item.status))]
			.filter(Boolean)
			.sort();
		const products = [
			...new Set(allItems.map((item) => item.product_name)),
		].sort();
		const sizes = [...new Set(allItems.map((item) => item.size_name))].sort();
		const logos = [...new Set(allItems.map((item) => item.logo))]
			.filter(Boolean)
			.sort();

		// Populate filters
		this.populateSelect('vendorFilter', vendors);
		this.populateSelect('departmentFilter', departments);
		this.populateSelect('statusFilter', statuses);
		this.populateSelect('productFilter', products);
		this.populateSelect('sizeFilter', sizes);
		this.populateSelect('logoFilter', logos);
	}

	populateSelect(selectId, options) {
		const select = document.getElementById(selectId);
		if (!select) return;

		// Keep the "All" option and add others
		const allOption = select.querySelector('option[value=""]');
		select.innerHTML = '';
		select.appendChild(allOption);

		options.forEach((option) => {
			const optionElement = document.createElement('option');
			optionElement.value = option;
			optionElement.textContent = option;
			select.appendChild(optionElement);
		});
	}

	getAllItems() {
		const items = [];
		this.rawData.vendor_groups?.forEach((vendorGroup) => {
			vendorGroup.items.forEach((item) => {
				items.push({
					...item,
					vendor_name: vendorGroup.vendor_info.vendor_name,
				});
			});
		});
		return items;
	}

	applyFilters() {
		console.log('🔍 Applying filters:', this.filters);

		const allItems = this.getAllItems();

		this.filteredData = allItems.filter((item) => {
			// Search filter
			if (this.filters.search) {
				const searchTerm = this.filters.search.toLowerCase();
				const searchableText = [
					item.product_name,
					item.product_code,
					item.color_name,
					item.size_name,
					item.vendor_name,
					item.dep_name,
					item.rf_first_name,
					item.rf_last_name,
				]
					.join(' ')
					.toLowerCase();

				if (!searchableText.includes(searchTerm)) {
					return false;
				}
			}

			// Vendor filter
			if (this.filters.vendor && item.vendor_name !== this.filters.vendor) {
				return false;
			}

			// Department filter
			if (
				this.filters.department &&
				item.dep_name !== this.filters.department
			) {
				return false;
			}

			// Status filter
			if (this.filters.status && item.status !== this.filters.status) {
				return false;
			}

			// Product filter
			if (this.filters.product && item.product_name !== this.filters.product) {
				return false;
			}

			// Size filter
			if (this.filters.size && item.size_name !== this.filters.size) {
				return false;
			}

			// Logo filter
			if (this.filters.logo && item.logo !== this.filters.logo) {
				return false;
			}

			return true;
		});

		this.applySorting();
		this.renderReport();
		this.updateFilteredSummary();
	}

	applySorting() {
		console.log('📊 Applying sorting:', this.sortConfig);

		this.filteredData.sort((a, b) => {
			// Primary sort
			let result = this.compareValues(
				a,
				b,
				this.sortConfig.primary.field,
				this.sortConfig.primary.direction
			);
			if (result !== 0) return result;

			// Secondary sort
			if (this.sortConfig.secondary.field) {
				result = this.compareValues(
					a,
					b,
					this.sortConfig.secondary.field,
					this.sortConfig.secondary.direction
				);
				if (result !== 0) return result;
			}

			// Tertiary sort
			if (this.sortConfig.tertiary.field) {
				result = this.compareValues(
					a,
					b,
					this.sortConfig.tertiary.field,
					this.sortConfig.tertiary.direction
				);
			}

			return result;
		});

		this.renderReport();
	}

	compareValues(a, b, field, direction) {
		let aVal = a[field] || '';
		let bVal = b[field] || '';

		// Handle numeric fields
		if (
			field === 'quantity' ||
			field === 'line_item_total' ||
			field === 'pre_tax_price'
		) {
			aVal = parseFloat(aVal) || 0;
			bVal = parseFloat(bVal) || 0;
		} else {
			// String comparison
			aVal = aVal.toString().toLowerCase();
			bVal = bVal.toString().toLowerCase();
		}

		let result = 0;
		if (aVal < bVal) result = -1;
		else if (aVal > bVal) result = 1;

		return direction === 'desc' ? -result : result;
	}

	renderReport() {
		console.log('🎨 Rendering report...');

		const reportContent = document.getElementById('report-content');
		if (!reportContent) return;

		// Group filtered data by vendor
		const vendorGroups = this.groupByVendor(this.filteredData);

		reportContent.innerHTML = '';

		if (vendorGroups.length === 0) {
			reportContent.innerHTML = `
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search fa-3x"></i>
                    </div>
                    <h3>No results found</h3>
                    <p>Try adjusting your filters to see more results.</p>
                </div>
            `;
			return;
		}

		vendorGroups.forEach((vendorGroup) => {
			const vendorElement = this.createVendorGroupElement(vendorGroup);
			reportContent.appendChild(vendorElement);
		});
	}

	groupByVendor(items) {
		const groups = {};

		items.forEach((item) => {
			const vendorName = item.vendor_name;
			if (!groups[vendorName]) {
				groups[vendorName] = {
					vendor_info: {
						vendor_name: vendorName,
						vendor_id: item.vendor_id,
					},
					items: [],
					summary: {
						total_items: 0,
						total_quantity: 0,
						total_value: 0,
					},
				};
			}

			groups[vendorName].items.push(item);
			groups[vendorName].summary.total_items++;
			groups[vendorName].summary.total_quantity += parseInt(item.quantity) || 0;
			groups[vendorName].summary.total_value +=
				parseFloat(item.line_item_total) || 0;
		});

		return Object.values(groups);
	}

	createVendorGroupElement(vendorGroup) {
		const template = document.getElementById('vendor-group-template');
		const vendorElement = template.content.cloneNode(true);

		// Populate vendor info
		vendorElement
			.querySelector('.vendor-group')
			.setAttribute('data-vendor-id', vendorGroup.vendor_info.vendor_id);
		vendorElement.querySelector('.vendor-name').textContent =
			vendorGroup.vendor_info.vendor_name;
		vendorElement.querySelector('.items-count').textContent =
			vendorGroup.summary.total_items;
		vendorElement.querySelector('.quantity-count').textContent =
			vendorGroup.summary.total_quantity;
		vendorElement.querySelector('.value-count').textContent =
			this.formatCurrency(vendorGroup.summary.total_value);

		// Setup collapse functionality
		const collapseId = `vendor-${vendorGroup.vendor_info.vendor_id}-items`;
		const toggleButton = vendorElement.querySelector('.toggle-vendor');
		const itemsContainer = vendorElement.querySelector('.vendor-items');

		itemsContainer.id = collapseId;
		toggleButton.setAttribute('data-bs-target', `#${collapseId}`);
		toggleButton.setAttribute('aria-expanded', 'true');
		toggleButton.setAttribute('aria-controls', collapseId);

		// Add click event for toggle icon rotation
		toggleButton.addEventListener('click', function () {
			const icon = this.querySelector('i');
			const isExpanded = this.getAttribute('aria-expanded') === 'true';

			// Toggle the icon
			if (isExpanded) {
				icon.className = 'fas fa-chevron-right';
				this.setAttribute('aria-expanded', 'false');
			} else {
				icon.className = 'fas fa-chevron-down';
				this.setAttribute('aria-expanded', 'true');
			}
		});

		// Populate items table
		const tbody = vendorElement.querySelector('.vendor-items-body');
		vendorGroup.items.forEach((item) => {
			const itemRow = this.createItemRowElement(item);
			tbody.appendChild(itemRow);
		});

		return vendorElement;
	}

	createItemRowElement(item) {
		const template = document.getElementById('item-row-template');
		const rowElement = template.content.cloneNode(true);

		// Populate item data
		rowElement
			.querySelector('.item-row')
			.setAttribute('data-item-id', item.order_details_id);
		rowElement.querySelector('.product-name').textContent = item.product_name;
		rowElement.querySelector('.product-code').textContent = item.product_code;
		rowElement.querySelector('.department-name').textContent = item.dep_name;
		rowElement.querySelector('.color-name').textContent = item.color_name;
		rowElement.querySelector('.size-name').textContent = item.size_name;
		rowElement.querySelector('.quantity-badge').textContent = item.quantity;

		// Pricing information
		rowElement.querySelector('.unit-price').textContent = this.formatCurrency(
			item.pre_tax_price
		);
		rowElement.querySelector('.logo-fee').textContent = this.formatCurrency(
			item.logo_fee
		);
		rowElement.querySelector('.tax-amount').textContent = this.formatCurrency(
			item.tax
		);
		rowElement.querySelector('.total-amount').textContent = this.formatCurrency(
			item.line_item_total
		);

		// Logo
		const logoImg = rowElement.querySelector('.logo-image');
		if (item.logo) {
			// Replace 'Black' with 'White' for better visibility on dark background
			let logoSrc = item.logo;
			if (logoSrc.includes('_Black_')) {
				logoSrc = logoSrc.replace('_Black_', '_White_');
			}
			logoImg.src = `../../${logoSrc}`;
			logoImg.style.display = 'block';
		} else {
			logoImg.style.display = 'none';
		}
		rowElement.querySelector('.logo-placement').textContent =
			item.dept_patch_place || 'No placement specified';

		// Employee info
		rowElement.querySelector(
			'.employee-name'
		).textContent = `${item.rf_first_name} ${item.rf_last_name}`;
		rowElement.querySelector('.employee-comment').textContent =
			item.comment || '';

		// Comments
		if (item.comments) {
			const commentsList = rowElement.querySelector('.comments-list');
			const comments = item.comments.split(' || ');
			const submitters = (item.comment_sub_name || '').split(' || ');
			const dates = (item.comment_submitted || '').split(' || ');

			let commentsHtml = '';
			comments.forEach((comment, index) => {
				if (comment.trim()) {
					const submitter = submitters[index] || 'Unknown';
					const date = dates[index] ? this.formatDate(dates[index]) : '';
					commentsHtml += `<div class="comment-item">${comment} - ${submitter} (${date})</div>`;
				}
			});
			commentsList.innerHTML = commentsHtml;
		}

		// Status
		const statusBadge = rowElement.querySelector('.status-badge');
		statusBadge.textContent = item.status || 'Unknown';
		statusBadge.className = `status-badge ${(item.status || '').toLowerCase()}`;

		return rowElement;
	}

	clearFilters() {
		console.log('🧹 Clearing all filters...');

		this.filters = {
			search: '',
			vendor: '',
			department: '',
			status: '',
			product: '',
			size: '',
			logo: '',
		};

		// Reset form elements
		document.getElementById('searchInput').value = '';
		document.getElementById('vendorFilter').value = '';
		document.getElementById('departmentFilter').value = '';
		document.getElementById('statusFilter').value = '';
		document.getElementById('productFilter').value = '';
		document.getElementById('sizeFilter').value = '';
		document.getElementById('logoFilter').value = '';

		// Reset table header sorting
		this.clearHeaderSorting();

		this.applyFilters();
	}

	updateSummary() {
		document.getElementById('vendorCount').textContent =
			this.rawData.summary?.total_vendors || 0;
		document.getElementById('itemCount').textContent =
			this.rawData.summary?.total_items || 0;
		document.getElementById('totalValue').textContent = this.formatCurrency(
			this.rawData.summary?.total_value || 0
		);
	}

	updateFilteredSummary() {
		document.getElementById('filteredCount').textContent =
			this.filteredData.length;
	}

	// Header Sorting Functions
	handleHeaderSort(field) {
		console.log('🔄 Header sort clicked:', field);

		// Clear existing header sorting visuals
		this.clearHeaderSorting();

		// Check current sort direction for this field
		let direction = 'asc';
		if (this.sortConfig.primary.field === field) {
			direction = this.sortConfig.primary.direction === 'asc' ? 'desc' : 'asc';
		}

		// Reset multi-level sorting and set this as primary
		this.sortConfig = {
			primary: { field: field, direction: direction },
			secondary: { field: '', direction: 'asc' },
			tertiary: { field: '', direction: 'asc' },
		};

		// Update header visual state
		this.updateHeaderSortVisual(field, direction);

		// Update multi-level sort controls to reflect the change
		this.updateSortControls();

		// Apply the new sorting
		this.applySorting();
	}

	clearHeaderSorting() {
		document.querySelectorAll('.sortable').forEach((header) => {
			header.classList.remove('sorted-asc', 'sorted-desc');
			const icon = header.querySelector('.sort-icon');
			if (icon) {
				icon.className = 'fas fa-sort sort-icon';
				icon.setAttribute('data-direction', 'none');
			}
		});
	}

	updateHeaderSortVisual(field, direction) {
		const header = document.querySelector(`[data-sort="${field}"]`);
		if (header) {
			header.classList.add(`sorted-${direction}`);
			const icon = header.querySelector('.sort-icon');
			if (icon) {
				icon.setAttribute('data-direction', direction);
			}
		}
	}

	updateSortControls() {
		// Update the multi-level sort dropdowns to reflect current state
		const primarySort = document.getElementById('primarySort');
		const primaryDirection = document.getElementById('primaryDirection');

		if (primarySort) primarySort.value = this.sortConfig.primary.field;
		if (primaryDirection)
			primaryDirection.value = this.sortConfig.primary.direction;
	}

	// Export Functions
	exportToPDF() {
		console.log('📄 Exporting to PDF...');
		this.showAlert('PDF export functionality coming soon!', 'info');
	}

	exportToHTML() {
		console.log('🌐 Exporting to static HTML...');

		const reportHtml = this.generateStaticHTML();
		const blob = new Blob([reportHtml], { type: 'text/html' });
		const url = URL.createObjectURL(blob);

		const a = document.createElement('a');
		a.href = url;
		a.download = `vendor-report-${this.orderInstanceId}.html`;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);

		this.showAlert('Static HTML report downloaded!', 'success');
	}

	printReport() {
		console.log('🖨️ Printing report...');
		window.print();
	}

	generateStaticHTML() {
		// This would generate a complete standalone HTML file
		// with embedded CSS and JavaScript for client-side filtering
		return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Report - ${this.orderInstanceId}</title>
    <style>/* Embedded CSS would go here */</style>
</head>
<body>
    <div class="report-container">
        <h1>Vendor Report - ${this.orderInstanceId}</h1>
        ${document.getElementById('report-content').innerHTML}
    </div>
    <script>/* Embedded filtering JavaScript would go here */</script>
</body>
</html>`;
	}

	// Utility Functions
	formatCurrency(amount) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		}).format(amount || 0);
	}

	formatDate(dateString) {
		if (!dateString) return '';
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', {
			month: '2-digit',
			day: '2-digit',
			year: 'numeric',
		});
	}

	formatDateTime(dateTimeString) {
		if (!dateTimeString) return '';
		const date = new Date(dateTimeString);
		return date.toLocaleString('en-US', {
			month: '2-digit',
			day: '2-digit',
			year: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
		});
	}

	showLoading(show) {
		const loadingContainer = document.getElementById('loading-container');
		if (loadingContainer) {
			loadingContainer.style.display = show ? 'flex' : 'none';
		}
	}

	showFiltersAndContent(show) {
		const filtersSection = document.getElementById('filters-section');
		const reportContent = document.getElementById('report-content');
		const technicalFooter = document.getElementById('technical-footer');

		if (filtersSection) {
			filtersSection.style.display = show ? 'block' : 'none';
		}

		if (reportContent) {
			reportContent.style.display = show ? 'block' : 'none';
		}

		if (technicalFooter) {
			technicalFooter.style.display = show ? 'block' : 'none';
		}
	}

	showAlert(message, type = 'info') {
		const alertBanner = document.getElementById('alert-banner');
		const alertMessage = document.getElementById('alert-message');

		if (alertBanner && alertMessage) {
			alertMessage.textContent = message;
			alertBanner.className = `alert-banner alert-${type}`;
			alertBanner.style.display = 'flex';

			// Auto-hide after 5 seconds
			setTimeout(() => {
				alertBanner.style.display = 'none';
			}, 5000);
		}
	}
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	new VendorReportManager();
});
