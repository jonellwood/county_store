/**
 * Modern Product Details Renderer
 * Updated: October 2025
 * Purpose: Renders product data into the new modern HTML structure
 */

/**
 * Fetch product data from the API
 * @param {number} productId - The product ID to fetch
 */
function fetchProductData(productId) {
	console.log('Fetching product data for ID:', productId);

	// Determine the correct path to fetchProductDetails.php
	const apiPath = window.location.pathname.includes('/utils/style_refactor/')
		? '../../fetchProductDetails.php'
		: './fetchProductDetails.php';

	fetch(`${apiPath}?id=${productId}`)
		.then((response) => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then((data) => {
			console.log('Product data received:', data);

			if (data.product_message || data.error) {
				console.log(
					'Product unavailable or error:',
					data.product_message || data.error
				);
				// Show error message to user
				showProductUnavailable();
			} else if (!data.product_data || !data.product_data[0]) {
				console.error('No product data found');
				showProductUnavailable();
			} else {
				renderProductDetails(data);
				// Initialize calculations
				if (typeof calculateSubTotal === 'function') {
					calculateSubTotal();
				}
				if (typeof calculateTax === 'function') {
					calculateTax();
				}
				if (typeof calculateSelectedTotal === 'function') {
					calculateSelectedTotal();
				}
				if (typeof calculateNewTotal === 'function') {
					calculateNewTotal();
				}
			}
		})
		.catch((error) => {
			console.error('Error fetching product data:', error);
			showProductUnavailable();
		});
}

/**
 * Show product unavailable message
 */
function showProductUnavailable() {
	const errorMessage = document.getElementById('errorMessage');
	if (errorMessage) {
		errorMessage.showPopover();
	} else {
		alert('Sorry, this product is currently unavailable.');
	}
}

function renderProductDetails(data) {
	console.log('Rendering product details:', data);

	if (!data || !data.product_data || !data.product_data[0]) {
		console.error('Invalid product data');
		return;
	}

	const product = data.product_data[0];
	const colors = data.color_data || [];
	const prices = data.price_data || [];
	const logos = data.logo_data || [];

	console.log('Product object:', product);
	console.log('Product code:', product.code);

	// Update product title and code
	document.getElementById('product-name').textContent =
		product.name || 'Product Name';
	document.getElementById('product-code-display').textContent =
		product.code || 'N/A';

	// Update hidden form fields
	document.getElementById('product_code').value = product.code || '';
	document.getElementById('product_name_hidden').value = product.name || '';

	// Set initial product image
	if (colors.length > 0 && product.code) {
		const firstColor = colors[0];
		const formattedColor = formatColorValueForUrl(firstColor.color);
		const formattedCode = product.code.toLowerCase(); // Don't use formatColorValueForUrl on code - it's already clean

		// Detect if we're in root or subdirectory
		const pathPrefix = window.location.pathname.includes(
			'/utils/style_refactor/'
		)
			? '../../'
			: './';
		const imageUrl = `${pathPrefix}product-images/${formattedColor}_${formattedCode}.jpg`;

		console.log('Setting initial image:', {
			color: firstColor.color,
			formattedColor: formattedColor,
			code: product.code,
			formattedCode: formattedCode,
			imageUrl: imageUrl,
		});

		const imgElement = document.getElementById('product-image');
		if (imgElement) {
			imgElement.src = imageUrl;
		}

		const hiddenImgElement = document.getElementById('image-url');
		if (hiddenImgElement) {
			hiddenImgElement.value = imageUrl;
		}
	}

	// Render Color Options
	renderColorOptions(colors, product.code);

	// Render Size Options
	renderSizeOptions(prices);

	// Render Logo Options (only if logo selector exists on page)
	const logoSelector = document.getElementById('logo-selector');
	if (logoSelector) {
		renderLogoOptions(logos);
	}

	// Initialize summary with cart total
	updateCartSummary();

	// Set initial values (with null checks)
	if (prices.length > 0) {
		const priceEl = document.getElementById('productPrice');
		const priceIdEl = document.getElementById('price_id');
		const sizeIdEl = document.getElementById('size_id');
		const sizeNameEl = document.getElementById('size_name');

		if (priceEl) priceEl.value = prices[0].price;
		if (priceIdEl) priceIdEl.value = prices[0].price_id;
		if (sizeIdEl) sizeIdEl.value = prices[0].size_id;
		if (sizeNameEl) sizeNameEl.value = prices[0].size_name;
	}

	if (colors.length > 0) {
		const colorIdEl = document.getElementById('color_id');
		const colorNameEl = document.getElementById('color_name');

		if (colorIdEl) colorIdEl.value = colors[0].color_id;
		if (colorNameEl) colorNameEl.value = colors[0].color;
	}

	if (logos.length > 0) {
		const logoUrlEl = document.getElementById('logo-url');
		const logoIdEl = document.getElementById('logo_id');
		const logoIdLegacyEl = document.getElementById('logo_id_legacy');

		if (logoUrlEl) logoUrlEl.value = logos[0].image;
		if (logoIdEl) logoIdEl.value = logos[0].id;
		if (logoIdLegacyEl) logoIdLegacyEl.value = logos[0].id;
	}

	// Set department patch place default
	const deptEl = document.getElementById('deptPatchPlace');
	const deptHiddenEl = document.getElementById('deptPatchPlace-hidden');
	if (deptEl) deptEl.value = 'Below Logo';
	if (deptHiddenEl) deptHiddenEl.value = 'Below Logo';

	// Initialize all calculations
	if (typeof getCurrentProductPrice === 'function') {
		getCurrentProductPrice();
	}
	if (typeof updateCurrentQty === 'function') {
		updateCurrentQty();
	}
}

function renderColorOptions(colors, productCode) {
	const container = document.getElementById('color-options');

	if (!colors || colors.length === 0) {
		container.innerHTML =
			'<p style="color: var(--text-muted);">No colors available</p>';
		return;
	}

	let html = '';

	colors.forEach((color, index) => {
		const colorId = `color-${color.color_id}`;
		const isChecked = index === 0 ? 'checked' : '';

		html += `
            <input 
                type="radio" 
                class="color-option-input" 
                id="${colorId}" 
                name="color-select" 
                value="${color.color_id}"
                data-name="${escapeHtml(color.color)}"
                data-hex="${color.p_hex || '#cccccc'}"
                data-color-id="${color.color_id}"
                onchange="updateColorImage('${colorId}')"
                ${isChecked}
            />
            <label class="color-option-label" for="${colorId}">
                <span class="color-swatch" style="background-color: ${
									color.p_hex || '#cccccc'
								};"></span>
                <span>${escapeHtml(color.color)}</span>
            </label>
        `;
	});

	container.innerHTML = html;
}

function renderSizeOptions(prices) {
	const container = document.getElementById('size-options');

	if (!prices || prices.length === 0) {
		container.innerHTML =
			'<p style="color: var(--text-muted);">No sizes available</p>';
		return;
	}

	let html = '';

	prices.forEach((price, index) => {
		const priceId = `size-${price.price_id}`;
		const isChecked = index === 0 ? 'checked' : '';

		html += `
            <input 
                type="radio" 
                class="size-option-input" 
                id="${priceId}" 
                name="size-select" 
                value="${price.price_id}"
                data-price="${price.price}"
                data-size-id="${price.size_id}"
                data-size-name="${escapeHtml(price.size_name)}"
                onchange="updateSizeSelection('${priceId}')"
                ${isChecked}
            />
            <label class="size-option-label" for="${priceId}">
                <span class="size-name">${escapeHtml(price.size_name)}</span>
                <span class="size-price">${makeDollar(price.price)}</span>
            </label>
        `;
	});

	container.innerHTML = html;
}

function renderLogoOptions(logos) {
	const select = document.getElementById('logo-selector');

	console.log('renderLogoOptions called');
	console.log('Select element:', select);
	console.log('Logos data:', logos);

	if (!select) {
		console.error('Logo selector element not found!');
		return;
	}

	if (!logos || logos.length === 0) {
		console.warn('No logos available');
		select.innerHTML = '<option value="">No logos available</option>';
		return;
	}

	// Check if browser supports customizable select (Chrome 126+)
	const supportsCustomizableSelect = CSS.supports('appearance', 'base-select');
	console.log('Supports customizable select:', supportsCustomizableSelect);

	let html = '';

	if (supportsCustomizableSelect) {
		// Modern customizable select with images
		html = `
            <button type="button">
                <selectedcontent></selectedcontent>
            </button>
            <option value="">
                <span class="logo-option-content">Select Logo...</span>
            </option>
        `;

		logos.forEach((logo, index) => {
			html += `
                <option 
                    id="logo-${logo.id}" 
                    value="${logo.id}"
                    data-url="${escapeHtml(logo.image)}"
                    data-name="${escapeHtml(logo.logo_name)}"
                    data-fee="5"
                    ${index === 0 ? 'selected' : ''}
                >
                    <span class="logo-option-content">
                        <img src="${escapeHtml(
													logo.image
												)}" alt="" class="logo-preview-icon" />
                        <span class="logo-option-text">${escapeHtml(
													logo.logo_name
												)}</span>
                    </span>
                </option>
            `;
		});
	} else {
		// Fallback standard select for Safari/Firefox
		html = '<option value="">Select Logo...</option>';
		logos.forEach((logo, index) => {
			html += `
                <option 
                    id="logo-${logo.id}" 
                    value="${logo.id}"
                    data-url="${escapeHtml(logo.image)}"
                    data-name="${escapeHtml(logo.logo_name)}"
                    data-fee="5"
                    ${index === 0 ? 'selected' : ''}
                >
                    ${escapeHtml(logo.logo_name)}
                </option>
            `;
		});
	}

	select.innerHTML = html;
}

function updateSizeSelection(radioId) {
	const radio = document.getElementById(radioId);
	const price = radio.dataset.price;
	const sizeId = radio.dataset.sizeId;
	const sizeName = radio.dataset.sizeName;

	// Update hidden inputs
	document.getElementById('productPrice').value = price;
	document.getElementById('price_id').value = radio.value;
	document.getElementById('size_id').value = sizeId;
	document.getElementById('size_name').value = sizeName;

	// Update displays
	getCurrentProductPrice();
	updateCurrentPrice();
	calculateSubTotal();
	calculateTax();
	calculateSelectedTotal();
	calculateNewTotal();
}

function updateCartSummary() {
	console.log('updateCartSummary called');
	const cartTotal = getCartTotal();
	console.log('cartTotal from getCartTotal():', cartTotal);
	if (cartTotal && (cartTotal.cart_total || cartTotal.total_logo_fees)) {
		// Cart total includes item prices + logo fees (already calculated in PHP)
		// We need to add tax to get the ACTUAL total the user will pay
		const subtotalWithFees =
			(cartTotal.cart_total || 0) + (cartTotal.total_logo_fees || 0);
		const cartTotalWithTax = subtotalWithFees * 1.09;
		const displayValue = makeDollar(cartTotalWithTax);
		console.log('Cart calculation:', {
			cart_total: cartTotal.cart_total,
			total_logo_fees: cartTotal.total_logo_fees,
			subtotalWithFees: subtotalWithFees,
			cartTotalWithTax: cartTotalWithTax,
			displayValue: displayValue,
		});
		document.getElementById('cart-subtotal-display').textContent = displayValue;
	} else {
		console.warn('No cart total found or cart is empty');
		document.getElementById('cart-subtotal-display').textContent = '$0.00';
	}
}

function escapeHtml(text) {
	if (!text) return '';
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

// Helper to format color names for URLs
function formatColorValueForUrl(str) {
	if (!str) return '';
	return str.replace(/[^a-zA-Z]/g, '').toLowerCase();
}
