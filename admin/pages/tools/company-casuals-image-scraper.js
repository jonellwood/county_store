/**
 * Company Casuals Image Scraper JavaScript
 * Handles UI interactions and API calls for image extraction
 */

let scrapeResult = null;

// DOM Elements
const form = document.getElementById('scraperForm');
const urlInput = document.getElementById('productUrl');
const scrapeBtn = document.getElementById('scrapeBtn');
const progressSection = document.getElementById('progressSection');
const progressBar = document.getElementById('progressBar');
const statusText = document.getElementById('statusText');
const resultsSection = document.getElementById('resultsSection');
const productInfo = document.getElementById('productInfo');
const productName = document.getElementById('productName');
const productCode = document.getElementById('productCode');
const colorCount = document.getElementById('colorCount');
const imageGrid = document.getElementById('imageGrid');
const errorAlert = document.getElementById('errorAlert');
const errorMessage = document.getElementById('errorMessage');
const downloadAllBtn = document.getElementById('downloadAllBtn');
const copyAllBtn = document.getElementById('copyAllBtn');
const exportJsonBtn = document.getElementById('exportJsonBtn');
const toggleLogBtn = document.getElementById('toggleLogBtn');
const logBody = document.getElementById('logBody');
const logOutput = document.getElementById('logOutput');
const copyFeedback = document.getElementById('copyFeedback');
const copyFeedbackText = document.getElementById('copyFeedbackText');

// Additional DOM Elements
const downloadAllImagesBtn = document.getElementById('downloadAllImagesBtn');

// Event Listeners
form.addEventListener('submit', handleSubmit);
downloadAllBtn.addEventListener('click', downloadAllUrls);
if (downloadAllImagesBtn) {
	downloadAllImagesBtn.addEventListener('click', downloadAllImages);
}
copyAllBtn.addEventListener('click', copyAllUrls);
exportJsonBtn.addEventListener('click', exportJson);
toggleLogBtn.addEventListener('click', toggleLog);

async function handleSubmit(e) {
	e.preventDefault();

	const url = urlInput.value.trim();
	if (!url) {
		showError('Please enter a product URL');
		return;
	}

	hideError();
	hideResults();
	showProgress();

	try {
		updateProgress(20, 'Fetching product page...');

		const formData = new FormData();
		formData.append('action', 'scrape');
		formData.append('url', url);

		const response = await fetch('company-casuals-image-scraper-api.php', {
			method: 'POST',
			body: formData,
		});

		updateProgress(60, 'Parsing color swatches...');

		const result = await response.json();

		updateProgress(90, 'Processing results...');

		if (!result.success) {
			throw new Error(result.error || 'Unknown error occurred');
		}

		scrapeResult = result.data;

		updateProgress(100, 'Complete!');

		setTimeout(() => {
			hideProgress();
			displayResults(result.data, result.log || []);
		}, 300);
	} catch (error) {
		hideProgress();
		showError(error.message);
		console.error('Scrape error:', error);
	}
}

function displayResults(data, log) {
	// Update product info
	productName.textContent = data.product_name || 'Unknown Product';
	productCode.textContent = data.product_code || 'N/A';
	colorCount.textContent = data.color_count || data.colors?.length || 0;

	// Build image grid
	imageGrid.innerHTML = '';

	if (data.colors && data.colors.length > 0) {
		data.colors.forEach((color, index) => {
			const card = createImageCard(color, index);
			imageGrid.appendChild(card);
		});
	}

	// Display log
	if (log && log.length > 0) {
		logOutput.textContent = log
			.map((entry) => {
				const context = entry.context
					? ' ' + JSON.stringify(entry.context)
					: '';
				return `[${entry.timestamp}] ${entry.message}${context}`;
			})
			.join('\n');
	}

	showResults();
}

function createImageCard(color, index) {
	const card = document.createElement('div');
	card.className = 'image-card';

	const isMain = color.is_main === true;

	card.innerHTML = `
        <div class="image-wrapper">
            ${isMain ? '<span class="main-image-badge">Main</span>' : ''}
            <img src="${escapeHtml(color.image_url)}" 
                 alt="${escapeHtml(color.color_name)}"
                 loading="lazy"
                 onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22><rect fill=%22%23f8f9fa%22 width=%22200%22 height=%22200%22/><text x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22>Image not found</text></svg>'">
        </div>
        <div class="card-body">
            <div class="color-name">
                <i class="fas fa-palette me-1 text-muted"></i>
                ${escapeHtml(color.color_name)}
            </div>
            <div class="image-url mb-2">${escapeHtml(color.image_url)}</div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm" onclick="downloadImage('${escapeJs(color.image_url)}', '${escapeJs(color.color_name)}')">
                    <i class="fas fa-download me-1"></i> Download
                </button>
                <button class="btn btn-outline-primary btn-sm copy-btn" onclick="copyUrl('${escapeJs(color.image_url)}')">
                    <i class="fas fa-copy me-1"></i> Copy URL
                </button>
                <a href="${escapeHtml(color.image_url)}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Open
                </a>
            </div>
        </div>
    `;

	return card;
}

function copyUrl(url) {
	navigator.clipboard
		.writeText(url)
		.then(() => {
			showCopyFeedback('URL copied to clipboard!');
		})
		.catch((err) => {
			console.error('Copy failed:', err);
			// Fallback
			const textarea = document.createElement('textarea');
			textarea.value = url;
			document.body.appendChild(textarea);
			textarea.select();
			document.execCommand('copy');
			document.body.removeChild(textarea);
			showCopyFeedback('URL copied to clipboard!');
		});
}

function copyAllUrls() {
	if (!scrapeResult || !scrapeResult.colors) {
		showError('No data to copy');
		return;
	}

	const urls = scrapeResult.colors.map((c) => c.image_url).join('\n');

	navigator.clipboard
		.writeText(urls)
		.then(() => {
			showCopyFeedback(
				`${scrapeResult.colors.length} URLs copied to clipboard!`,
			);
		})
		.catch((err) => {
			console.error('Copy failed:', err);
		});
}

function downloadAllUrls() {
	if (!scrapeResult || !scrapeResult.colors) {
		showError('No data to download');
		return;
	}

	const content = scrapeResult.colors.map((c) => c.image_url).join('\n');
	const filename = `${scrapeResult.product_code || 'product'}_image_urls.txt`;

	downloadFile(content, filename, 'text/plain');
}

function exportJson() {
	if (!scrapeResult) {
		showError('No data to export');
		return;
	}

	const exportData = {
		scraper: 'company_casuals_images',
		exported_at: new Date().toISOString(),
		data: scrapeResult,
	};

	const filename = `${scrapeResult.product_code || 'product'}_images.json`;
	downloadFile(
		JSON.stringify(exportData, null, 2),
		filename,
		'application/json',
	);
}

function downloadFile(content, filename, mimeType) {
	const blob = new Blob([content], { type: mimeType });
	const url = URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	link.download = filename;
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	URL.revokeObjectURL(url);
}

function toggleLog() {
	const isHidden = logBody.style.display === 'none';
	logBody.style.display = isHidden ? 'block' : 'none';
	toggleLogBtn.innerHTML = isHidden
		? '<i class="fas fa-chevron-up"></i>'
		: '<i class="fas fa-chevron-down"></i>';
}

function showProgress() {
	progressSection.classList.add('active');
	progressBar.style.width = '0%';
}

function hideProgress() {
	progressSection.classList.remove('active');
}

function updateProgress(percent, text) {
	progressBar.style.width = `${percent}%`;
	statusText.textContent = text;
}

function showResults() {
	resultsSection.classList.add('active');
}

function hideResults() {
	resultsSection.classList.remove('active');
	imageGrid.innerHTML = '';
	scrapeResult = null;
}

function showError(message) {
	errorMessage.textContent = message;
	errorAlert.classList.remove('d-none');
}

function hideError() {
	errorAlert.classList.add('d-none');
}

function showCopyFeedback(message) {
	copyFeedbackText.textContent = message;
	copyFeedback.style.display = 'block';

	setTimeout(() => {
		copyFeedback.style.display = 'none';
	}, 2000);
}

function escapeHtml(str) {
	if (!str) return '';
	const div = document.createElement('div');
	div.textContent = str;
	return div.innerHTML;
}

function escapeJs(str) {
	if (!str) return '';
	return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

/**
 * Download a single image via the proxy API
 * @param {string} imageUrl - The full URL of the image
 * @param {string} colorName - The color name for the filename
 */
async function downloadImage(imageUrl, colorName) {
	const code = scrapeResult?.product_code || 'product';
	// Clean color name for filename: lowercase, replace spaces with underscores
	const cleanColor = (colorName || 'image')
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, '_')
		.replace(/^_+|_+$/g, '');
	const filename = `${cleanColor}_${code.toLowerCase()}.jpg`;

	try {
		showCopyFeedback(`Downloading ${colorName}...`);

		// Use the proxy API to download
		const formData = new FormData();
		formData.append('action', 'download_image');
		formData.append('image_url', imageUrl);
		formData.append('filename', filename);

		const response = await fetch('company-casuals-image-scraper-api.php', {
			method: 'POST',
			body: formData,
		});

		if (!response.ok) {
			throw new Error('Download failed');
		}

		// Check if response is JSON (error) or blob (image)
		const contentType = response.headers.get('content-type');
		if (contentType && contentType.includes('application/json')) {
			const error = await response.json();
			throw new Error(error.error || 'Download failed');
		}

		const blob = await response.blob();
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = url;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		URL.revokeObjectURL(url);

		showCopyFeedback(`Downloaded ${filename}`);
	} catch (error) {
		console.error('Download error:', error);
		showError(`Failed to download ${colorName}: ${error.message}`);
	}
}

/**
 * Download all images sequentially
 */
async function downloadAllImages() {
	if (
		!scrapeResult ||
		!scrapeResult.colors ||
		scrapeResult.colors.length === 0
	) {
		showError('No images to download');
		return;
	}

	const colors = scrapeResult.colors;
	const total = colors.length;
	let downloaded = 0;
	let failed = 0;

	downloadAllImagesBtn.disabled = true;
	downloadAllImagesBtn.innerHTML =
		'<i class="fas fa-spinner fa-spin me-2"></i> Downloading...';

	for (const color of colors) {
		try {
			await downloadImageSilent(color.image_url, color.color_name);
			downloaded++;
			downloadAllImagesBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i> ${downloaded}/${total}`;
			// Small delay between downloads to avoid overwhelming the server
			await new Promise((resolve) => setTimeout(resolve, 300));
		} catch (error) {
			failed++;
			console.error(`Failed to download ${color.color_name}:`, error);
		}
	}

	downloadAllImagesBtn.disabled = false;
	downloadAllImagesBtn.innerHTML =
		'<i class="fas fa-download me-2"></i> Download All Images';

	if (failed > 0) {
		showCopyFeedback(`Downloaded ${downloaded} images, ${failed} failed`);
	} else {
		showCopyFeedback(`Downloaded all ${downloaded} images!`);
	}
}

/**
 * Download image without UI feedback (for batch downloads)
 */
async function downloadImageSilent(imageUrl, colorName) {
	const code = scrapeResult?.product_code || 'product';
	const cleanColor = (colorName || 'image')
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, '_')
		.replace(/^_+|_+$/g, '');
	const filename = `${cleanColor}_${code.toLowerCase()}.jpg`;

	const formData = new FormData();
	formData.append('action', 'download_image');
	formData.append('image_url', imageUrl);
	formData.append('filename', filename);

	const response = await fetch('company-casuals-image-scraper-api.php', {
		method: 'POST',
		body: formData,
	});

	if (!response.ok) {
		throw new Error('Download failed');
	}

	const contentType = response.headers.get('content-type');
	if (contentType && contentType.includes('application/json')) {
		const error = await response.json();
		throw new Error(error.error || 'Download failed');
	}

	const blob = await response.blob();
	const url = URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	link.download = filename;
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	URL.revokeObjectURL(url);
}
