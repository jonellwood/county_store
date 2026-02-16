// Product Image Downloader - JavaScript
// Database-driven approach: select product → load colors → paste sample URL → download

let allProducts = [];
let productColors = [];
let selectedProductId = null;
let selectedProductCode = '';

// ──────────────────────────────────────────
// Initialization
// ──────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
	loadProducts();
	setupSearch();
});

// ──────────────────────────────────────────
// Product Search & Selection
// ──────────────────────────────────────────

function loadProducts() {
	fetch('product-image-downloader-api.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ action: 'get_products' }),
	})
		.then((r) => r.json())
		.then((data) => {
			if (data.success) {
				allProducts = data.products;
			}
		})
		.catch((err) => console.error('Failed to load products:', err));
}

function setupSearch() {
	const input = document.getElementById('productSearchInput');
	const dropdown = document.getElementById('searchResultsDropdown');

	input.addEventListener('input', function () {
		const query = this.value.trim().toLowerCase();

		if (query.length < 2) {
			dropdown.classList.remove('show');
			return;
		}

		const filtered = allProducts
			.filter(
				(p) =>
					p.code.toLowerCase().includes(query) ||
					p.name.toLowerCase().includes(query),
			)
			.slice(0, 20); // Limit to 20 results

		if (filtered.length === 0) {
			dropdown.innerHTML =
				'<div class="search-result-item text-muted">No products found</div>';
		} else {
			dropdown.innerHTML = filtered
				.map(
					(p) => `
                <div class="search-result-item" onclick="selectProduct(${p.product_id}, '${escapeHtml(p.code)}', '${escapeHtml(p.name)}')">
                    <span class="product-code">${escapeHtml(p.code)}</span>
                    <span class="product-name ms-2">${escapeHtml(p.name)}</span>
                    ${p.product_type ? `<span class="product-type ms-2">(${escapeHtml(p.product_type)})</span>` : ''}
                </div>
            `,
				)
				.join('');
		}

		dropdown.classList.add('show');
	});

	// Close dropdown when clicking outside
	document.addEventListener('click', function (e) {
		if (!e.target.closest('#productSearch')) {
			dropdown.classList.remove('show');
		}
	});
}

function selectProduct(productId, code, name) {
	selectedProductId = productId;
	selectedProductCode = code;

	document.getElementById('productSearchInput').value = '';
	document.getElementById('searchResultsDropdown').classList.remove('show');
	document.getElementById('selectedProductInfo').style.display = 'block';
	document.getElementById('selectedProductText').textContent =
		`${code} — ${name}`;
	document.getElementById('selectedProductId').value = productId;
	document.getElementById('selectedProductCode').value = code;

	// Load colors for this product
	loadColors(productId);
}

function clearProduct() {
	selectedProductId = null;
	selectedProductCode = '';
	productColors = [];

	document.getElementById('selectedProductInfo').style.display = 'none';
	document.getElementById('selectedProductText').textContent = '';
	document.getElementById('selectedProductId').value = '';
	document.getElementById('selectedProductCode').value = '';
	document.getElementById('colorsSection').style.display = 'none';
	document.getElementById('urlSection').style.display = 'none';
	document.getElementById('resultsSection').style.display = 'none';
	document.getElementById('failedSection').style.display = 'none';
	document.getElementById('logSection').style.display = 'none';
	updateDownloadButton();
}

// ──────────────────────────────────────────
// Colors
// ──────────────────────────────────────────

function loadColors(productId) {
	fetch('product-image-downloader-api.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ action: 'get_colors', product_id: productId }),
	})
		.then((r) => r.json())
		.then((data) => {
			if (data.success) {
				productColors = data.colors.map((c) => ({ ...c, selected: true }));
				renderColorChips();
				document.getElementById('colorsSection').style.display = '';
				document.getElementById('urlSection').style.display = '';
				updateDownloadButton();
			} else {
				alert('Error loading colors: ' + (data.error || 'Unknown error'));
			}
		})
		.catch((err) => {
			console.error('Failed to load colors:', err);
			alert('Failed to load colors for this product');
		});
}

function renderColorChips() {
	const container = document.getElementById('colorChips');

	if (productColors.length === 0) {
		container.innerHTML =
			'<span class="text-muted">No colors assigned to this product.</span>';
		document.getElementById('colorCount').textContent = '0';
		return;
	}

	container.innerHTML = productColors
		.map((c, i) => {
			const hexStyle = c.p_hex ? `background:${c.p_hex};` : 'background:#ccc;';
			return `
            <span class="color-chip ${c.selected ? 'selected' : ''}"
                  onclick="toggleColor(${i})"
                  title="${c.color}">
                <span class="swatch" style="${hexStyle}"></span>
                ${escapeHtml(c.color)}
            </span>
        `;
		})
		.join('');

	const selectedCount = productColors.filter((c) => c.selected).length;
	document.getElementById('colorCount').textContent =
		`${selectedCount} of ${productColors.length}`;
}

function toggleColor(index) {
	productColors[index].selected = !productColors[index].selected;
	renderColorChips();
	updateDownloadButton();
}

function selectAllColors() {
	productColors.forEach((c) => (c.selected = true));
	renderColorChips();
	updateDownloadButton();
}

function deselectAllColors() {
	productColors.forEach((c) => (c.selected = false));
	renderColorChips();
	updateDownloadButton();
}

// ──────────────────────────────────────────
// URL Color Detection
// ──────────────────────────────────────────

function detectColorInUrl() {
	const url = document.getElementById('sampleUrl').value.trim();

	if (!url || !url.includes('cdnp.sanmar.com')) {
		document.getElementById('detectedColorSection').style.display = 'none';
		updateDownloadButton();
		return;
	}

	// Try to detect the color from the URL by matching against our product's colors
	const selectedColors = productColors.filter((c) => c.selected);
	let detectedColor = null;

	// Try matching each color name (without spaces) against the URL
	for (const color of selectedColors) {
		const noSpaceColor = color.color.replace(/\s+/g, '');
		if (url.includes(noSpaceColor)) {
			detectedColor = noSpaceColor;
			break;
		}
	}

	// If no match from DB colors, try to extract from common URL patterns
	// Pattern: .../624Wx724H_72574_Black-0-... or .../624Wx724H-72574-Black-0-...
	if (!detectedColor) {
		const match = url.match(/\d+[Wx]\d+H[_-]\d+[_-]([A-Za-z]+)/);
		if (match) {
			detectedColor = match[1];
		}
	}

	if (detectedColor) {
		document.getElementById('detectedColorSection').style.display = 'block';
		document.getElementById('detectedColorDisplay').innerHTML =
			`<i class="fas fa-palette me-1"></i> <strong>${escapeHtml(detectedColor)}</strong>`;
		document.getElementById('sampleColorOverride').value = detectedColor;
	} else {
		document.getElementById('detectedColorSection').style.display = 'none';
		document.getElementById('sampleColorOverride').value = '';
	}

	updateDownloadButton();
}

function updateDownloadButton() {
	const hasProduct = selectedProductId !== null;
	const hasUrl = document.getElementById('sampleUrl')?.value.trim().length > 10;
	const hasColor =
		document.getElementById('sampleColorOverride')?.value.trim().length > 0;
	const hasSelectedColors = productColors.some((c) => c.selected);

	const btn = document.getElementById('downloadBtn');
	if (btn) {
		btn.disabled = !(hasProduct && hasUrl && hasColor && hasSelectedColors);
	}
}

// ──────────────────────────────────────────
// Download
// ──────────────────────────────────────────

function startDownload() {
	const productId = selectedProductId;
	const sampleUrl = document.getElementById('sampleUrl').value.trim();
	const sampleColor = document
		.getElementById('sampleColorOverride')
		.value.trim();
	const namingFormat = document.getElementById('namingFormat').value;
	const selectedColorIds = productColors
		.filter((c) => c.selected)
		.map((c) => parseInt(c.color_id));

	if (!productId || !sampleUrl || !sampleColor) {
		alert('Please complete all steps before downloading.');
		return;
	}

	// Show progress, hide old results
	document.getElementById('progressSection').style.display = '';
	document.getElementById('resultsSection').style.display = 'none';
	document.getElementById('failedSection').style.display = 'none';
	document.getElementById('logSection').style.display = 'none';
	document.getElementById('downloadBtn').disabled = true;

	updateProgress(5, 'Sending download request...');

	fetch('product-image-downloader-api.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({
			action: 'download',
			product_id: productId,
			sample_url: sampleUrl,
			sample_color: sampleColor,
			naming_format: namingFormat,
			selected_colors: selectedColorIds,
		}),
	})
		.then((r) => r.json())
		.then((data) => {
			document.getElementById('downloadBtn').disabled = false;

			if (data.error && !data.success) {
				updateProgress(0, 'Error: ' + data.error);
				if (data.log) showLog(data.log);
				alert('Download failed: ' + data.error);
				return;
			}

			if (data.success) {
				updateProgress(100, 'Complete!');
				showResults(data);
				if (data.log) showLog(data.log);
			}
		})
		.catch((err) => {
			console.error('Download error:', err);
			document.getElementById('downloadBtn').disabled = false;
			updateProgress(0, 'Connection error: ' + err.message);
			alert('Error: ' + err.message);
		});
}

function updateProgress(pct, text) {
	const bar = document.getElementById('progressBar');
	const txt = document.getElementById('progressText');
	if (bar) bar.style.width = pct + '%';
	if (txt) txt.textContent = text;
}

// ──────────────────────────────────────────
// Display Results
// ──────────────────────────────────────────

function showResults(data) {
	// Hide progress
	setTimeout(() => {
		document.getElementById('progressSection').style.display = 'none';
	}, 1000);

	const { images, failed, summary } = data;

	// Summary
	const summaryEl = document.getElementById('resultsSummary');
	summaryEl.innerHTML = `
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-success text-white p-3">
                    <h3>${summary.totalImages}</h3>
                    <div>Downloaded</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card ${summary.totalFailed > 0 ? 'bg-danger' : 'bg-secondary'} text-white p-3">
                    <h3>${summary.totalFailed}</h3>
                    <div>Failed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white p-3">
                    <h3>${summary.totalColors}</h3>
                    <div>Colors</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3">
                    <h4>${escapeHtml(summary.productCode)}</h4>
                    <div>${escapeHtml(summary.productName)}</div>
                </div>
            </div>
        </div>
    `;

	// Image grid
	const imageGrid = document.getElementById('imageResults');
	if (images && images.length > 0) {
		imageGrid.innerHTML = images
			.map(
				(img) => `
            <div class="image-card">
                <img src="/product-images/${escapeHtml(img.filename)}"
                     alt="${escapeHtml(img.color)}"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vdCBGb3VuZDwvdGV4dD48L3N2Zz4='">
                <div class="mt-2">
                    <strong>${escapeHtml(img.filename)}</strong><br>
                    <small class="text-muted">${escapeHtml(img.color)} — ${escapeHtml(img.view)}</small>
                    ${img.size ? `<br><small class="text-success">${formatBytes(img.size)}</small>` : ''}
                </div>
            </div>
        `,
			)
			.join('');
		document.getElementById('resultsSection').style.display = '';
	} else {
		imageGrid.innerHTML =
			'<div class="text-muted">No images were downloaded.</div>';
		document.getElementById('resultsSection').style.display = '';
	}

	// Failed downloads
	if (failed && failed.length > 0) {
		const failedList = document.getElementById('failedList');
		failedList.innerHTML = failed
			.map(
				(f) => `
            <div class="mb-2">
                <strong class="text-danger">${escapeHtml(f.color)}</strong>
                <div class="small text-muted">URLs tried: ${
									f.urls_tried
										? Object.values(f.urls_tried)
												.map((u) => `<br>• <code>${escapeHtml(u)}</code>`)
												.join('')
										: 'None'
								}</div>
            </div>
        `,
			)
			.join('');
		document.getElementById('failedSection').style.display = '';
	}
}

function showLog(logLines) {
	const logEl = document.getElementById('logOutput');
	logEl.textContent = logLines.join('\n');
	document.getElementById('logSection').style.display = '';
	// Auto-scroll to bottom
	logEl.scrollTop = logEl.scrollHeight;
}

function toggleLog() {
	const logOutput = document.getElementById('logOutput');
	logOutput.style.display =
		logOutput.style.display === 'none' ? 'block' : 'none';
}

// ──────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────

function escapeHtml(text) {
	if (!text) return '';
	const div = document.createElement('div');
	div.textContent = String(text);
	return div.innerHTML;
}

function formatBytes(bytes) {
	if (bytes < 1024) return bytes + ' B';
	if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
	return (bytes / 1048576).toFixed(1) + ' MB';
}
