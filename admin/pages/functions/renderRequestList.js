function firstChar(str) {
	return str[0];
}

// Global variables for sorting and filtering
let globalData = [];
let currentSort = { column: null, direction: 'asc' };
let currentFilter = '';

function renderRequestList(data) {
	globalData = data; // Store data globally for sorting/filtering
	renderFilteredAndSortedData(data);
}

function renderFilteredAndSortedData(dataToRender) {
	let html = '';
	console.log(dataToRender);
	if (dataToRender.length == 0) {
		alert('No Requests Found');
	} else {
		html = `
			<div class='main-list-holder' id='main-list-holder'>
				<!-- Filter Controls -->
				<div class="filter-controls mb-3">
					<div class="row">
						<div class="col-md-6">
							<input type="text" 
								   class="form-control" 
								   id="employee-filter" 
								   placeholder="Filter by employee name (min 2 characters)..."
								   value="${currentFilter}">
						</div>
						<div class="col-md-6">
							<select class="form-control" id="employee-dropdown">
								<option value="">All Employees</option>
							</select>
						</div>
					</div>
				</div>
				
                <table class='styled-table table-striped' id='data-table'>
                    <thead>
		                <tr>
                            <th class='thead-dark'>ID</th>
                            <th class='thead-dark sortable' data-column='grand_total'>
								Total 
								<span class="sort-indicator">${getSortIndicator('grand_total')}</span>
							</th>
                            <th class='thead-dark sortable' data-column='created'>
								Created 
								<span class="sort-indicator">${getSortIndicator('created')}</span>
							</th>
                            <th class='thead-dark sortable' data-column='requested_for'>
								Requested For 
								<span class="sort-indicator">${getSortIndicator('requested_for')}</span>
							</th>
		
                        </tr>
                    </thead>
                    <tbody>
            `;
		for (var i = 0; i < dataToRender.length; i++) {
			html += `
                        <tr value=${
													dataToRender[i].order_id
														? dataToRender[i].order_id
														: ''
												} onclick=getOrderDetails(${
				dataToRender[i].order_id ? dataToRender[i].order_id : ''
			}) data-currentfy=${
				dataToRender[i].bill_to_fy
					? isThisFiscalYear(dataToRender[i].bill_to_fy)
					: ''
			} data-employee="${dataToRender[i].requested_for || ''}">
                    
                    <td> ${
											dataToRender[i].order_id ? dataToRender[i].order_id : ''
										}</td>
                    <td> ${
											dataToRender[i].grand_total
												? money_format(dataToRender[i].grand_total)
												: ''
										} </td>
                    <td> ${
											dataToRender[i].created
												? extractDate(dataToRender[i].created)
												: ''
										} </td>
                    <td> ${
											dataToRender[i].requested_for
												? dataToRender[i].requested_for
												: ''
										} </td>
                    
                    </tr>
                    
                    `;
		}
		html += '</tbody>';
		html += '</table>';
		html += '</div>';
		document.getElementById('main').innerHTML = html;

		// Populate employee dropdown
		populateEmployeeDropdown();

		// Attach event listeners
		attachSortingListeners();
		attachFilteringListeners();

		// Auto-click first row if no filter is active and this is initial load
		if (!currentFilter && dataToRender.length > 0) {
			setTimeout(() => clickFirst(), 100); // Small delay to ensure DOM is ready
		}
	}
	// return html;
}

function getSortIndicator(column) {
	if (currentSort.column === column) {
		return currentSort.direction === 'asc' ? '▲' : '▼';
	}
	return '▲▼';
}

function updateSortIndicators() {
	// Update sort indicators without re-rendering the entire structure
	const sortableHeaders = document.querySelectorAll('.sortable');
	sortableHeaders.forEach((header) => {
		const column = header.getAttribute('data-column');
		const indicator = header.querySelector('.sort-indicator');
		if (indicator) {
			indicator.textContent = getSortIndicator(column);
		}
	});
}

function attachSortingListeners() {
	const sortableHeaders = document.querySelectorAll('.sortable');
	sortableHeaders.forEach((header) => {
		header.addEventListener('click', function () {
			const column = this.getAttribute('data-column');
			sortData(column);
		});
		header.style.cursor = 'pointer';
	});
}

function sortData(column) {
	// Toggle direction if clicking same column, otherwise set to ascending
	if (currentSort.column === column) {
		currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
	} else {
		currentSort.column = column;
		currentSort.direction = 'asc';
	}

	// Sort the data
	let sortedData = [...globalData];
	sortedData.sort((a, b) => {
		let aVal = a[column];
		let bVal = b[column];

		// Handle different data types
		if (column === 'grand_total') {
			aVal = parseFloat(aVal) || 0;
			bVal = parseFloat(bVal) || 0;
		} else if (column === 'created') {
			aVal = new Date(aVal);
			bVal = new Date(bVal);
		} else {
			aVal = (aVal || '').toString().toLowerCase();
			bVal = (bVal || '').toString().toLowerCase();
		}

		if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
		if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
		return 0;
	});

	// Apply current filter and re-render just the table content
	const filteredData = applyFilter(sortedData);
	updateTableContent(filteredData);

	// Update sort indicators in headers
	updateSortIndicators();
}

function attachFilteringListeners() {
	const filterInput = document.getElementById('employee-filter');
	const dropdown = document.getElementById('employee-dropdown');

	if (filterInput) {
		filterInput.addEventListener('input', function () {
			// Store cursor position before filtering
			const cursorPosition = this.selectionStart;
			currentFilter = this.value;

			// Apply filter (this now only updates table content, not inputs)
			filterData();

			// Restore focus and cursor position
			setTimeout(() => {
				this.focus();
				this.setSelectionRange(cursorPosition, cursorPosition);
			}, 0);
		});
	}

	if (dropdown) {
		dropdown.addEventListener('change', function () {
			const selectedEmployee = this.value;
			if (selectedEmployee) {
				currentFilter = selectedEmployee;
				if (filterInput) filterInput.value = selectedEmployee;
			} else {
				currentFilter = '';
				if (filterInput) filterInput.value = '';
			}
			filterData();
		});
	}
}

function filterData() {
	const filteredData = applyFilter(globalData);
	// Instead of re-rendering everything, just update the table content
	updateTableContent(filteredData);
}

function updateTableContent(dataToRender) {
	// Only update the table tbody, preserving the filter controls and headers
	let html = '';

	for (var i = 0; i < dataToRender.length; i++) {
		html += `
                        <tr value=${
													dataToRender[i].order_id
														? dataToRender[i].order_id
														: ''
												} onclick=getOrderDetails(${
			dataToRender[i].order_id ? dataToRender[i].order_id : ''
		}) data-currentfy=${
			dataToRender[i].bill_to_fy
				? isThisFiscalYear(dataToRender[i].bill_to_fy)
				: ''
		} data-employee="${dataToRender[i].requested_for || ''}">
                    
                    <td> ${
											dataToRender[i].order_id ? dataToRender[i].order_id : ''
										}</td>
                    <td> ${
											dataToRender[i].grand_total
												? money_format(dataToRender[i].grand_total)
												: ''
										} </td>
                    <td> ${
											dataToRender[i].created
												? extractDate(dataToRender[i].created)
												: ''
										} </td>
                    <td> ${
											dataToRender[i].requested_for
												? dataToRender[i].requested_for
												: ''
										} </td>
                    
                    </tr>
                    
                    `;
	}

	// Only update the table body, not the entire structure
	const tbody = document.querySelector('#data-table tbody');
	if (tbody) {
		tbody.innerHTML = html;
	}
}

function applyFilter(data) {
	if (!currentFilter || currentFilter.length < 2) {
		return data;
	}

	return data.filter((item) => {
		const employeeName = (item.requested_for || '').toLowerCase();
		return employeeName.includes(currentFilter.toLowerCase());
	});
}

function populateEmployeeDropdown() {
	const dropdown = document.getElementById('employee-dropdown');
	if (!dropdown) return;

	// Get unique employee names
	const employees = [
		...new Set(
			globalData.map((item) => item.requested_for).filter((name) => name)
		),
	];
	employees.sort();

	// Clear existing options except "All Employees"
	dropdown.innerHTML = '<option value="">All Employees</option>';

	// Add employee options
	employees.forEach((employee) => {
		const option = document.createElement('option');
		option.value = employee;
		option.textContent = employee;
		dropdown.appendChild(option);
	});
}

function clickFirst() {
	const mainListHolder = document.getElementById('main-list-holder');
	if (mainListHolder) {
		const table = mainListHolder.querySelector('table');
		if (table) {
			const firstRow = table.querySelector('tbody tr:first-child');
			if (firstRow) {
				firstRow.click();
			}
		}
	}
}
