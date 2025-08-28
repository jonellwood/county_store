let allColorsData = [];
let allSizesData = [];

function editProduct(id) {
	console.log('editProduct', id);
	fetch('getProductData.php?product_id=' + id)
		.then((response) => response.json())
		.then((data) => renderProductOptionsForEdit(data, id));
}

function renderProductOptionsForEdit(data, id) {
	console.log('data', data);
	// push data.allColors to allColors to make available globally
	allColorsData.push(...data.allColors);
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
	newColor.innerHTML = `
        <div class="color-preview" style="background-color: #000000;"></div>
        <select class="color-selector" onchange="updateColorPreview(this)">
            ${allColorsData
							.map(
								(color) => `
                <option value="${color.color_id}">${color.color}</option>
            `
							)
							.join('')}
        </select>
        <button class="remove-btn" onclick="removeColor(this)">Remove</button>
    `;
	colorsContainer.appendChild(newColor);
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
			const colorSelector = colorOption.querySelector('.color-selector');
			if (colorSelector) {
				colors.push({
					color_id: colorSelector.value,
				});
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
