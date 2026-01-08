let allColorsData = [];
let allSizesData = [];

// Initialize product search when page loads
document.addEventListener('DOMContentLoaded', function () {
	initProductSearch();
});

function initProductSearch() {
	const searchInput = document.getElementById('productSearch');
	if (!searchInput) return;

	searchInput.addEventListener('input', function () {
		const searchTerm = this.value.toLowerCase().trim();
		filterProducts(searchTerm);
	});
}

function filterProducts(searchTerm) {
	const productRows = document.querySelectorAll('.product-row');

	productRows.forEach((row) => {
		const code = row.dataset.productCode || '';
		const name = row.dataset.productName || '';
		const desc = row.dataset.productDesc || '';

		// Check if search term matches code, name, or description
		const matches =
			code.includes(searchTerm) ||
			name.includes(searchTerm) ||
			desc.includes(searchTerm);

		if (matches || searchTerm === '') {
			row.classList.remove('hidden');
		} else {
			row.classList.add('hidden');
		}
	});
}

function editProduct(id) {
	console.log('editProduct', id);
	fetch('getProductData.php?product_id=' + id)
		.then((response) => response.json())
		.then((data) => renderProductOptionsForEdit(data, id));
}

function renderProductOptionsForEdit(data, id) {
	console.log('data', data);
	// push data.allColors to allColors to make available globally and sort alphabetically
	allColorsData = [...data.allColors].sort((a, b) =>
		a.color.localeCompare(b.color)
	);
	allSizesData.push(...data.allSizes);
	// console.log('id', id);
	var popover = document.getElementById('editProductPopover');
	popover.showPopover();

	var html = '';
	html += `<div class='modal-dialog modal-dialog-centered'>
                <div class='modal-content'>
                    <div class='modal-header popover-header'>
                        <div>
                            <h4 class="modal-title">${data.prodData.name}</h5>
                            <div class="product-code">Product Code: ${
															data.prodData.code
														}</div>
                        </div>
                        
                        <div class="d-flex gap-5 align-items-baseline">
                            <div class="status-toggle">
                                <label class="switch">
                                    <input type="checkbox" id="productStatus" ${
																			data.prodData.keep === 1 ? 'checked' : ''
																		}>
                                    <span class="slider"></span>
                                </label>
                                <span>Product Active</span>
                            </div>
                            <button type='button' class='btn-close' popovertarget='editProductPopover'
                                popovertargetaction='hide'></button>
                        </div>
                    </div>
                    <div class='modal-body'>
                        <div id='editProductModalBody'>
                            <div class='form-section'>
                                <div class='section-header'>
                                    <span>Available Colors</span>
                                    <button class='add-btn' onclick="addNewColor()">+ Add Color</button>
                                </div>
                            	<div id="colorsContainer">
                                	<!-- existing colors will be rendered here -->  
                            	</div>
                        	</div>
							<div class='form-section'>
								<div class='section-header'>
									<span>Available Sizes</span>
									<button class='add-btn' onclick="addNewSize()">+ Add Size</button>
								</div>
								<div id="sizesContainer">
									<!-- existing sizes will be rendered here -->
								</div>
                        </div>
                    	</div>
					</div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-info save-btn' popovertarget='editProductPopover' popovertargetaction='hide' onclick="saveProduct(${
													data.prodData.product_id
												})">Save</button>
                    </div>
                </div>
            </div>`;
	// console.log(html);
	document.getElementById('editProductPopover').innerHTML = html;
	// setTimeout(() => {
	renderExistingColors(data.prodColors);
	renderExistingSizes(data.prodPrices);
	// });
}
function renderExistingColors(prodColors) {
	const colorsContainer = document.getElementById('colorsContainer');
	colorsContainer.innerHTML = ''; // Clear any existing content

	prodColors.forEach((color) => {
		console.log('existing color', color);
		const colorOption = document.createElement('div');
		colorOption.className = 'color-option';
		colorOption.innerHTML = `
            <div class="color-preview" style="background-color: ${
							color.p_hex
						};"></div>
            <select class="color-selector" disabled>
                ${allColorsData
									.map(
										(c) => `
                    <option value="${c.color_id}" ${
											c.color_id === color.color_id ? 'selected' : ''
										}>
                        ${c.color}
                    </option>`
									)
									.join('')}
            </select>
            <button class="remove-btn" onclick="removeColor(this)">Remove</button>
        `;
		colorsContainer.appendChild(colorOption);
	});
}
function addNewColor() {
	const colorsContainer = document.getElementById('colorsContainer');
	const newColor = document.createElement('div');
	newColor.className = 'color-option';
	newColor.classList.add('new');

	// Generate a unique ID for this color input
	const uniqueId = 'color-search-' + Date.now();

	newColor.innerHTML = `
        <div class="color-preview" style="background-color: #000000;"></div>
        <div class="color-autocomplete-container">
            <input 
                type="text" 
                class="color-search-input" 
                placeholder="Search for a color..."
                autocomplete="off"
                data-color-id=""
                id="${uniqueId}"
            />
            <div class="color-autocomplete-dropdown"></div>
        </div>
        <button class="remove-btn" onclick="removeColor(this)">Remove</button>
    `;
	colorsContainer.appendChild(newColor);

	// Initialize autocomplete for this input
	const input = newColor.querySelector('.color-search-input');
	initColorAutocomplete(input);
}

function updateColorPreview(select) {
	console.log('select', select);
	const selectedColor = allColorsData.find(
		(color) => color.color_id == select.value
	);
	console.log('selectedColor', selectedColor);
	const preview = select.previousElementSibling;
	preview.style.backgroundColor = selectedColor
		? selectedColor.p_hex
		: '#000000';
}

function removeColor(button) {
	const colorOption = button.parentElement;

	function renderExistingSizes(prodPrices) {
		const sizesContainer = document.getElementById('sizesContainer');
		sizesContainer.innerHTML = ''; // Clear any existing content

		prodPrices.forEach((size) => {
			const sizeOption = document.createElement('div');
			sizeOption.className = 'size-option';
			sizeOption.innerHTML = `
            <div class="size-name">${size.size_name}</div>
            <input type="number" class="price-input" value="${size.price}" step="0.01" min="0" data-price-id="${size.price_id}">
            <button class="remove-btn" onclick="removeSize(this)">Remove</button>
        `;
			sizesContainer.appendChild(sizeOption);
		});
	}
	colorOption.remove();
}

function renderColorSelector(colors) {
	console.log('colors was a rad movie: ', colors);
	const colorSelector = document.getElementById('color-selector');
	console.log('colorSelector', colorSelector);
	for (const color of colors) {
		const option = document.createElement('option');
		option.value = color.color_id;
		option.textContent = color.color;
		colorSelector.appendChild(option);
	}
}

function renderExistingSizes(prodPrices) {
	const sizesContainer = document.getElementById('sizesContainer');
	sizesContainer.innerHTML = ''; // Clear any existing content

	prodPrices.forEach((size) => {
		const sizeOption = document.createElement('div');
		sizeOption.className = 'size-option';
		sizeOption.innerHTML = `
            <div class="size-name">${size.size_name}</div>
			<div class="price-input-container">
			<label>Price</label>
            <input type="number" class="price-input" value="${size.price.toFixed(
							2
						)}" step="0.01" min="0" data-price-id="${size.price_id}">
			</div>
            <button class="remove-btn" onclick="removeSize(this)">Remove</button>
        `;
		sizesContainer.appendChild(sizeOption);
	});
}
function addNewSize() {
	const sizesContainer = document.getElementById('sizesContainer');
	const newSize = document.createElement('div');
	newSize.className = 'size-option new';
	newSize.innerHTML = `
        <select class="size-selector">
            ${allSizesData
							.map(
								(size) => `
                <option value="${size.size_id}">${size.size_name}</option>
            `
							)
							.join('')}
        </select>
        <input type="number" class="price-input" value="0.00" step="0.01" min="0">
        <button class="remove-btn" onclick="removeSize(this)">Remove</button>
    `;
	sizesContainer.appendChild(newSize);
}

function removeSize(button) {
	const sizeOption = button.parentElement;
	sizeOption.remove();
}

function saveProduct(id) {
	const colors = [];
	const sizes = [];

	// Collect color data
	document
		.querySelectorAll('#colorsContainer .color-option')
		.forEach((colorOption) => {
			// Check for new autocomplete input first
			const colorInput = colorOption.querySelector('.color-search-input');
			if (colorInput && colorInput.dataset.colorId) {
				colors.push({
					color_id: colorInput.dataset.colorId,
				});
			} else {
				// Fall back to old select element for existing colors
				const colorSelector = colorOption.querySelector('.color-selector');
				if (colorSelector) {
					colors.push({
						color_id: colorSelector.value,
					});
				}
			}
		});

	// Collect size and price data
	document
		.querySelectorAll('#sizesContainer .size-option')
		.forEach((sizeOption) => {
			const sizeSelector = sizeOption.querySelector('.size-selector');
			const priceInput = sizeOption.querySelector('.price-input');
			if (sizeSelector && priceInput) {
				sizes.push({
					size_id: sizeSelector.value,
					price: parseFloat(priceInput.value),
				});
			} else if (priceInput) {
				// For existing sizes
				sizes.push({
					price_id: priceInput.dataset.priceId,
					price: parseFloat(priceInput.value),
				});
			}
		});

	// Send data to the backend
	console.log('Colors:', colors);
	console.log('Sizes:', sizes);
	console.log('Product ID:', id);

	fetch('saveProductData.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify({
			product_id: id,
			colors: colors,
			sizes: sizes,
		}),
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				alert('Product updated successfully!');
				// closePopover(); // Uncomment if you have this function
				// Refresh the page or update the display
				location.reload();
			} else {
				alert('Error: ' + data.message);
			}
		})
		.catch((error) => {
			console.error('Error saving product:', error);
			alert('Error saving product data');
		});
}

// Searchable Color Autocomplete Functions
function initColorAutocomplete(input) {
	const dropdown = input.nextElementSibling;
	let selectedIndex = -1;

	// Show dropdown and filter on input
	input.addEventListener('input', function () {
		const searchTerm = this.value.toLowerCase().trim();

		if (searchTerm.length === 0) {
			dropdown.classList.remove('active');
			return;
		}

		// Filter colors with smart sorting
		const filteredColors = allColorsData
			.filter((color) => color.color.toLowerCase().includes(searchTerm))
			.sort((a, b) => {
				const aLower = a.color.toLowerCase();
				const bLower = b.color.toLowerCase();

				// Exact match comes first
				if (aLower === searchTerm) return -1;
				if (bLower === searchTerm) return 1;

				// Starts with search term comes next
				const aStarts = aLower.startsWith(searchTerm);
				const bStarts = bLower.startsWith(searchTerm);
				if (aStarts && !bStarts) return -1;
				if (!aStarts && bStarts) return 1;

				// Otherwise alphabetical
				return a.color.localeCompare(b.color);
			});

		// Display results
		displayColorResults(dropdown, filteredColors, input);
		dropdown.classList.add('active');
		selectedIndex = -1;
	});

	// Handle keyboard navigation
	input.addEventListener('keydown', function (e) {
		const items = dropdown.querySelectorAll('.color-autocomplete-item');

		if (e.key === 'ArrowDown') {
			e.preventDefault();
			selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
			updateSelectedItem(items, selectedIndex);
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			selectedIndex = Math.max(selectedIndex - 1, -1);
			updateSelectedItem(items, selectedIndex);
		} else if (e.key === 'Enter') {
			e.preventDefault();
			if (selectedIndex >= 0 && items[selectedIndex]) {
				items[selectedIndex].click();
			}
		} else if (e.key === 'Escape') {
			dropdown.classList.remove('active');
			selectedIndex = -1;
		}
	});

	// Hide dropdown when clicking outside
	document.addEventListener('click', function (e) {
		if (!input.contains(e.target) && !dropdown.contains(e.target)) {
			dropdown.classList.remove('active');
			selectedIndex = -1;
		}
	});

	// Show all colors when focusing empty input
	input.addEventListener('focus', function () {
		if (this.value.trim().length === 0) {
			displayColorResults(dropdown, allColorsData.slice(0, 30), input);
			dropdown.classList.add('active');
		}
	});
}

function displayColorResults(dropdown, colors, input) {
	if (colors.length === 0) {
		dropdown.innerHTML =
			'<div class="color-autocomplete-no-results">No colors found</div>';
		return;
	}

	// Limit to first 30 results for performance
	const displayColors = colors.slice(0, 30);

	dropdown.innerHTML = displayColors
		.map(
			(color) => `
		<div class="color-autocomplete-item" data-color-id="${color.color_id}" data-color-name="${color.color}" data-color-hex="${color.p_hex}">
			<div class="color-swatch" style="background-color: ${color.p_hex};"></div>
			<span class="color-name">${color.color}</span>
			<span class="color-hex">${color.p_hex}</span>
		</div>
	`
		)
		.join('');

	// Add click handlers to items
	dropdown.querySelectorAll('.color-autocomplete-item').forEach((item) => {
		item.addEventListener('click', function () {
			selectColor(input, this);
		});
	});
}

function updateSelectedItem(items, index) {
	items.forEach((item, i) => {
		if (i === index) {
			item.classList.add('selected');
			item.scrollIntoView({ block: 'nearest' });
		} else {
			item.classList.remove('selected');
		}
	});
}

function selectColor(input, item) {
	const colorId = item.dataset.colorId;
	const colorName = item.dataset.colorName;
	const colorHex = item.dataset.colorHex;

	// Update input value and data
	input.value = colorName;
	input.dataset.colorId = colorId;

	// Update color preview
	const colorOption = input.closest('.color-option');
	const preview = colorOption.querySelector('.color-preview');
	preview.style.backgroundColor = colorHex;

	// Hide dropdown
	const dropdown = input.nextElementSibling;
	dropdown.classList.remove('active');
}
