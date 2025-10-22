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

		const itemsCount = vendorGroup.summary.total_items;
		const totalQty = vendorGroup.summary.total_quantity;

		vendorElement.querySelector('.items-count').textContent = itemsCount;
		vendorElement.querySelector('.quantity-count').textContent = totalQty;
		vendorElement.querySelector('.value-count').textContent =
			this.formatCurrency(vendorGroup.summary.total_value);

		// Hide redundant "Total Qty" when it's the same as "Line Items"
		const totalQtyElement = vendorElement.querySelector('.total-qty-stat');
		if (itemsCount === totalQty) {
			totalQtyElement.style.display = 'none';
		} else {
			totalQtyElement.style.display = 'inline-flex';
		}

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
			logoImg.src = `/../../../${logoSrc}`;
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

		// Comments - combine order_details.comment with aggregated comments
		const commentsList = rowElement.querySelector('.comments-list');
		let commentsHtml = '';

		// Add order_details.comment if it exists (employee-specific note)
		if (item.comment && item.comment.trim()) {
			commentsHtml += `<div class="comment-item employee-note"><strong>Note:</strong> ${item.comment}</div>`;
		}

		// Add aggregated comments (status history)
		if (item.comments) {
			const comments = item.comments.split(' || ');
			const submitters = (item.comment_sub_name || '').split(' || ');
			const dates = (item.comment_submitted || '').split(' || ');

			comments.forEach((comment, index) => {
				if (comment.trim()) {
					const submitter = submitters[index] || 'Unknown';
					const date = dates[index] ? this.formatDate(dates[index]) : '';
					commentsHtml += `<div class="comment-item">${comment} - ${submitter} (${date})</div>`;
				}
			});
		}

		commentsList.innerHTML = commentsHtml;

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

		// 📝 LOG THE EXPORT EVENT (just like order placements!)
		this.logExportEvent();

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

	// 📝 LOG EXPORT EVENTS TO THE SAME LOG FILE AS ORDER PLACEMENTS
	async logExportEvent() {
		try {
			const response = await fetch('../../orders/api.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					action: 'logExport',
					orderInstanceId: this.orderInstanceId,
					exportType: 'HTML',
					recordCount: this.filteredData.length,
				}),
			});

			if (response.ok) {
				console.log('📝 Export event logged successfully');
			}
		} catch (error) {
			console.log('⚠️ Could not log export event:', error);
			// Don't block the export if logging fails
		}
	}

	printReport() {
		console.log('🖨️ Printing report...');
		window.print();
	}

	generateStaticHTML() {
		// THE LEGENDARY SELF-CONTAINED VENDOR REPORT! 🚀
		// Complete with filtering, sorting, and all the data embedded

		const reportData = JSON.stringify(this.filteredData);
		const currentDateTime = new Date().toLocaleString('en-US', {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
		});

		return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkeley County Store - Vendor Report ${this.orderInstanceId}</title>
    
    <style>
    /* 🎨 EMBEDDED CSS - Making this look PROFESSIONAL! */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #333;
        line-height: 1.6;
        min-height: 100vh;
        padding: 20px;
    }

    .report-container {
        max-width: 1850px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .report-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 30px;
        text-align: center;
        position: relative;
    }

    .report-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    }

    .report-title {
        font-size: 2.5em;
        font-weight: 700;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .report-subtitle {
        font-size: 1.2em;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    .report-meta {
        background: #f8f9fa;
        padding: 20px 30px;
        border-bottom: 3px solid #e9ecef;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .meta-item {
        text-align: center;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .meta-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .meta-value {
        font-size: 1.4em;
        font-weight: 700;
        color: #2a5298;
        margin-top: 5px;
    }

    .controls-section {
        padding: 25px 30px;
        background: #ffffff;
        border-bottom: 1px solid #e9ecef;
    }

    .controls-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 15px;
        align-items: end;
    }

    .control-group {
        display: flex;
        flex-direction: column;
    }

    .control-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #495057;
        font-size: 0.9em;
    }

    .control-input {
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
    }

    .control-input:focus {
        outline: none;
        border-color: #2a5298;
        box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9em;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
    }

    .table-container {
        padding: 0 30px 30px;
        background: white;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .data-table th {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        color: white;
        padding: 16px 12px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85em;
        cursor: pointer;
        transition: background 0.3s ease;
        position: relative;
    }

    .data-table th:hover {
        background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
    }

    .data-table th.sortable::after {
        content: ' ⇅';
        opacity: 0.5;
        margin-left: 8px;
    }

    .data-table th.sort-asc::after {
        content: ' ↑';
        opacity: 1;
        color: #28a745;
    }

    .data-table th.sort-desc::after {
        content: ' ↓';
        opacity: 1;
        color: #28a745;
    }

    .data-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9em;
        transition: background 0.2s ease;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .data-table tr:nth-child(even) {
        background: #fafbfc;
    }

    .vendor-badge {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
        display: inline-block;
    }

    .price-cell {
        font-weight: 700;
        color: #28a745;
        font-family: 'Courier New', monospace;
    }

    .quantity-badge {
        background: #6f42c1;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 600;
        min-width: 30px;
        text-align: center;
        display: inline-block;
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1em;
    }

    .footer {
        background: #f8f9fa;
        padding: 20px 30px;
        text-align: center;
        color: #6c757d;
        border-top: 1px solid #e9ecef;
    }

    .highlight {
        background: linear-gradient(120deg, #ffd700 0%, #ffed4e 100%);
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }

    @media print {
        body { background: white; padding: 0; }
        .report-container { box-shadow: none; }
        .controls-section { display: none; }
    }

    @media (max-width: 768px) {
        .controls-grid { 
            grid-template-columns: 1fr; 
            gap: 10px; 
        }
        .report-meta { 
            grid-template-columns: 1fr; 
        }
        .data-table {
            font-size: 0.8em;
        }
        .data-table th,
        .data-table td {
            padding: 8px 6px;
        }
        
        /* 🎯 Vendor-specific styling */
        .dept-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .logo-info .logo-type {
            color: #2e7d32;
            font-weight: 500;
        }
        
        .logo-info .logo-color {
            color: #666;
            font-size: 0.9em;
            font-style: italic;
        }
        
        .placement-info {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 0.85em;
        }
        
        .text-muted {
            color: #999;
            font-style: italic;
        }
        
        /* � Comment Row Styling */
        .comment-row {
            background: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }
        
        .comment-row:hover {
            background: #fff3cd !important;
        }
        
        .comment-cell {
            padding: 12px 16px !important;
        }
        
        .inline-comment {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
        }
        
        .comment-icon {
            font-size: 1.2em;
            flex-shrink: 0;
        }
        
        .comment-label {
            font-weight: 600;
            color: #856404;
        }
        
        .comment-text {
            color: #333;
            font-style: italic;
        }
        
        /* �📘 Vendor Guide Styling */
        .vendor-guide {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin: 20px 30px 20px 50px;
            padding: 0;
            overflow: hidden;
        }
        
        .guide-header {
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            margin: 0;
        }
        
        .guide-header h3 {
            margin: 0;
            font-size: 1.1em;
            font-weight: 600;
        }
        
        .guide-content {
            padding: 15px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
        }
        
        .guide-section {
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .guide-section strong {
            color: #495057;
        }
    }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- 🏆 PROFESSIONAL HEADER -->
        <div class="report-header">
            <h1 class="report-title">📋 Vendor Report</h1>
            <p class="report-subtitle">Berkeley County Store - Order Instance: ${this.orderInstanceId}</p>
        </div>

        <!-- 📊 REPORT METADATA -->
        <div class="report-meta" id="reportMeta">
            <div class="meta-item">
                <div class="meta-label">Generated</div>
                <div class="meta-value">${currentDateTime}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Total Items</div>
                <div class="meta-value" id="totalItems">-</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Total Value</div>
                <div class="meta-value" id="totalValue">-</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Vendors</div>
                <div class="meta-value" id="vendorCount">-</div>
            </div>
        </div>

        <!-- 📘 VENDOR GUIDE -->
        <div class="vendor-guide">
            <div class="guide-header">
                <h3>📘 Report Guide</h3>
            </div>
            <div class="guide-content">
                <div class="guide-section">
                    <strong>🏢 Department:</strong> Employee's department for this order
                </div>
                <div class="guide-section">
                    <strong>🎨 Logo:</strong> Logo name to be applied (from your logo library)
                </div>
                <div class="guide-section">
                    <strong>📍 Placement:</strong> Where the department name/logo should be positioned on the item
                </div>
                <div class="guide-section">
                    <strong>💡 Note:</strong> This report contains all details needed for order fulfillment
                </div>
            </div>
        </div>

        <!-- 🎛️ INTERACTIVE CONTROLS -->
        <div class="controls-section">
            <div class="controls-grid">
                <div class="control-group">
                    <label class="control-label">🔍 Search Products, Vendors, or Employees</label>
                    <input type="text" id="searchInput" class="control-input" 
                           placeholder="Type to filter results..." autocomplete="off">
                </div>
                <div class="control-group">
                    <label class="control-label">🏢 Filter by Vendor</label>
                    <select id="vendorFilter" class="control-input">
                        <option value="">All Vendors</option>
                    </select>
                </div>
                <div class="control-group">
                    <label class="control-label">&nbsp;</label>
                    <button class="btn btn-primary" onclick="clearFilters()">
                        🗑️ Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- 📋 DATA TABLE -->
        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th class="sortable" data-field="vendor_name">Vendor</th>
                        <th class="sortable" data-field="product_name">Product</th>
                        <th class="sortable" data-field="requested_for">Employee</th>
                        <th class="sortable" data-field="dep_name">Department</th>
                        <th class="sortable" data-field="quantity">Qty</th>
                        <th class="sortable" data-field="size_name">Size</th>
                        <th class="sortable" data-field="color_name">Color</th>
                        <th class="sortable" data-field="logo">Logo</th>
                        <th class="sortable" data-field="dept_patch_place">Dept Name Placement</th>
                        <th class="sortable" data-field="line_item_total">Total</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Dynamic content will be inserted here -->
                </tbody>
            </table>
            <div id="noResults" class="no-results" style="display: none;">
                🔍 No results found. Try adjusting your filters.
            </div>
        </div>

        <!-- 📄 FOOTER -->
        <div class="footer">
            <p><strong>Berkeley County Store</strong> | Generated: ${currentDateTime}</p>
            <p>This is an interactive offline report. Filter and sort data using the controls above.</p>
        </div>
    </div>

    <script>
    // 🚀 EMBEDDED JAVASCRIPT - THE MAGIC HAPPENS HERE!
    
    // 📊 EMBEDDED DATA (The actual report data)
    const REPORT_DATA = ${reportData};
    
    // 🎛️ STATE MANAGEMENT
    let filteredData = [...REPORT_DATA];
    let sortConfig = { field: 'vendor_name', direction: 'asc' };
    
    // 🏁 INITIALIZE THE REPORT
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing Legendary Vendor Report...');
        
        initializeFilters();
        updateMetadata();
        renderTable();
        bindEvents();
        
        console.log('✅ Report loaded successfully!', {
            totalRecords: REPORT_DATA.length,
            vendors: getUniqueVendors().length
        });
    });
    
    // 📋 POPULATE VENDOR FILTER DROPDOWN
    function initializeFilters() {
        const vendorSelect = document.getElementById('vendorFilter');
        const vendors = getUniqueVendors();
        
        vendors.forEach(vendor => {
            const option = document.createElement('option');
            option.value = vendor;
            option.textContent = vendor;
            vendorSelect.appendChild(option);
        });
    }
    
    // 📊 UPDATE METADATA CARDS
    function updateMetadata() {
        const totalItems = filteredData.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        const totalValue = filteredData.reduce((sum, item) => sum + (parseFloat(item.line_item_total) || 0), 0);
        const vendorCount = getUniqueVendors(filteredData).length;
        
        document.getElementById('totalItems').textContent = totalItems.toLocaleString();
        document.getElementById('totalValue').textContent = formatCurrency(totalValue);
        document.getElementById('vendorCount').textContent = vendorCount;
    }
    
    // 🎨 RENDER THE EPIC TABLE
    function renderTable() {
        const tbody = document.getElementById('tableBody');
        const noResults = document.getElementById('noResults');
        
        if (filteredData.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }
        
        noResults.style.display = 'none';
        
        tbody.innerHTML = filteredData.map(item => {
            // Build employee name
            const employeeName = \`\${item.rf_first_name || ''} \${item.rf_last_name || ''}\`.trim() || 'N/A';
            
            let html = \`
            <tr>
                <td><span class="vendor-badge">\${escapeHtml(item.vendor_name || 'Unknown')}</span></td>
                <td><strong>\${escapeHtml(item.product_name || 'N/A')}</strong><br>
                    <small style="color: #6c757d;">\${escapeHtml(item.product_code || '')}</small></td>
                <td>\${escapeHtml(employeeName)}</td>
                <td><span class="dept-badge">\${escapeHtml(item.dep_name || 'N/A')}</span></td>
                <td><span class="quantity-badge">\${item.quantity || 0}</span></td>
                <td>\${escapeHtml(item.size_name || 'N/A')}</td>
                <td>\${escapeHtml(item.color_name || 'N/A')}</td>
                <td><span class="logo-info">\${formatLogoInfo(item.logo_name)}</span></td>
                <td><span class="placement-info">\${formatPlacementInfo(item.dept_patch_place)}</span></td>
                <td class="price-cell">\${formatCurrency(item.line_item_total)}</td>
            </tr>\`;
            
            // Add comment row if order_details.comment exists
            if (item.comment && item.comment.trim()) {
                html += \`
            <tr class="comment-row">
                <td colspan="10" class="comment-cell">
                    <div class="inline-comment">
                        <i class="comment-icon">💬</i>
                        <span class="comment-label">Note:</span>
                        <span class="comment-text">\${escapeHtml(item.comment)}</span>
                    </div>
                </td>
            </tr>\`;
            }
            
            return html;
        }).join('');
        
        updateMetadata();
    }
    
    // 🔍 FILTERING MAGIC
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const vendorFilter = document.getElementById('vendorFilter').value;
        
        filteredData = REPORT_DATA.filter(item => {
            const matchesSearch = !searchTerm || 
                (item.product_name || '').toLowerCase().includes(searchTerm) ||
                (item.vendor_name || '').toLowerCase().includes(searchTerm) ||
                (item.requested_for || '').toLowerCase().includes(searchTerm) ||
                (item.product_code || '').toLowerCase().includes(searchTerm);
                
            const matchesVendor = !vendorFilter || (item.vendor_name || '') === vendorFilter;
            
            return matchesSearch && matchesVendor;
        });
        
        sortData();
        renderTable();
    }
    
    // 🔄 SORTING SUPERPOWERS
    function sortData() {
        filteredData.sort((a, b) => {
            let aVal = a[sortConfig.field] || '';
            let bVal = b[sortConfig.field] || '';
            
            // Handle different data types
            if (sortConfig.field === 'line_item_total' || sortConfig.field === 'quantity') {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else if (sortConfig.field === 'created') {
                aVal = new Date(aVal);
                bVal = new Date(bVal);
            } else {
                aVal = aVal.toString().toLowerCase();
                bVal = bVal.toString().toLowerCase();
            }
            
            if (aVal < bVal) return sortConfig.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return sortConfig.direction === 'asc' ? 1 : -1;
            return 0;
        });
    }
    
    // 🎛️ EVENT BINDING
    function bindEvents() {
        // Search input with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
        
        // Vendor filter
        document.getElementById('vendorFilter').addEventListener('change', applyFilters);
        
        // Sortable column headers
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function() {
                const field = this.dataset.field;
                
                if (sortConfig.field === field) {
                    sortConfig.direction = sortConfig.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    sortConfig.field = field;
                    sortConfig.direction = 'asc';
                }
                
                // Update header indicators
                document.querySelectorAll('.sortable').forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                this.classList.add(sortConfig.direction === 'asc' ? 'sort-asc' : 'sort-desc');
                
                sortData();
                renderTable();
            });
        });
    }
    
    // 🗑️ CLEAR ALL FILTERS
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('vendorFilter').value = '';
        filteredData = [...REPORT_DATA];
        sortConfig = { field: 'vendor_name', direction: 'asc' };
        
        // Reset sort indicators
        document.querySelectorAll('.sortable').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        document.querySelector('[data-field="vendor_name"]').classList.add('sort-asc');
        
        sortData();
        renderTable();
    }
    
    // 🛠️ UTILITY FUNCTIONS
    function getUniqueVendors(data = REPORT_DATA) {
        return [...new Set(data.map(item => item.vendor_name).filter(Boolean))].sort();
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount || 0);
    }
    
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: 'numeric',
        });
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return (text || '').toString().replace(/[&<>"']/g, m => map[m]);
    }
    
    function formatLogoInfo(logoName) {
        if (!logoName || logoName === 'No Logo') {
            return '<span class="text-muted">No Logo</span>';
        }
        
        // Display the logo name with proper formatting
        return '<span class="logo-type">' + escapeHtml(logoName) + '</span>';
    }
    
    function formatPlacementInfo(placement) {
        if (!placement) return '<span class="text-muted">Standard</span>';
        
        const placements = {
            'left': 'Left Chest',
            'right': 'Right Chest',
            'center': 'Center Chest',
            'back': 'Back',
            'sleeve': 'Sleeve',
            'custom': 'Custom Position'
        };
        
        return '<span class="placement-info">' + escapeHtml(placements[placement] || placement) + '</span>';
    }
    
    // 🎯 KEYBOARD SHORTCUTS
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'f':
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                    break;
                case 'r':
                    e.preventDefault();
                    clearFilters();
                    break;
            }
        }
    });
    
    console.log('🎉 LEGENDARY VENDOR REPORT ACTIVATED!');
    console.log('💡 Tip: Use Ctrl+F to focus search, Ctrl+R to clear filters');
    </script>
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
