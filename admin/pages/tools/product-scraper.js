/**
 * SanMar Product Scraper JavaScript - Berkeley County Store Admin
 * Handles the frontend interactions for the product scraping tool
 */

class ProductScraper {
	constructor() {
		this.isProcessing = false;
		this.currentLog = [];
		this.lastScrapedData = null; // Store last scraped data for export
		this.init();
	}

	init() {
		console.log('🚀 Initializing SanMar Product Scraper...');
		this.setupEventListeners();
		this.resetUI();
	}

	setupEventListeners() {
		const form = document.getElementById('scraperForm');
		if (form) {
			form.addEventListener('submit', (e) => this.handleSubmit(e));
		}

		// Auto-focus URL input
		const urlInput = document.getElementById('productUrl');
		if (urlInput) {
			urlInput.focus();
		}
	}

	async handleSubmit(e) {
		e.preventDefault();

		if (this.isProcessing) {
			this.showAlert(
				'⚠️ Scraping is already in progress. Please wait...',
				'warning'
			);
			return;
		}

		const url = document.getElementById('productUrl').value.trim();
		const namingFormat = document.getElementById('namingFormat').value;
		const productCode = document.getElementById('productCode').value.trim();

		if (!url) {
			this.showAlert('❌ Please enter a SanMar product URL', 'danger');
			return;
		}

		if (!url.includes('sanmar.com')) {
			this.showAlert('❌ Please enter a valid SanMar URL', 'danger');
			return;
		}

		await this.startScraping(url, namingFormat, productCode);
	}

	async startScraping(url, namingFormat, productCode) {
		this.isProcessing = true;
		this.resetResults();
		this.showProgress();
		this.updateProgress(10, 'Initializing scraper...');

		try {
			const formData = new FormData();
			formData.append('action', 'scrape');
			formData.append('url', url);
			formData.append('imageFolder', 'product-images'); // Fixed folder path
			formData.append('namingFormat', namingFormat);
			if (productCode) {
				formData.append('productCode', productCode);
			}

			this.updateProgress(30, 'Fetching product page...');

			const response = await fetch('product-scraper-api.php', {
				method: 'POST',
				body: formData,
			});

			this.updateProgress(60, 'Processing product data...');

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}

			const result = await response.json();

			this.updateProgress(90, 'Finalizing results...');

			if (result.success) {
				this.updateProgress(100, 'Scraping completed successfully!');
				await this.displayResults(result);
				this.showAlert('🎉 Scraping completed successfully!', 'success');
			} else {
				throw new Error(result.error || 'Unknown error occurred');
			}
		} catch (error) {
			console.error('Scraping error:', error);
			this.updateProgress(0, 'Error occurred');
			this.showAlert(`❌ Scraping failed: ${error.message}`, 'danger');
		} finally {
			this.isProcessing = false;
			setTimeout(() => this.hideProgress(), 2000);
		}
	}

	async displayResults(result) {
		const { productInfo, images, log, summary } = result;

		// Store the data for potential export
		this.lastScrapedData = result;

		// Display product information
		this.displayProductInfo(productInfo, summary);

		// Display downloaded images
		this.displayImages(images);

		// Display log
		this.displayLog(log);

		// Show all result sections
		this.showResultSections();
	}

	displayProductInfo(productInfo, summary) {
		const infoElement = document.getElementById('productInfo');
		if (!infoElement) return;

		const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-tag me-2"></i>Product Code</h6>
                    <p class="mb-3"><code>${this.escapeHtml(
											summary.productCode
										)}</code></p>
                    
                    <h6><i class="fas fa-box me-2"></i>Product Name</h6>
                    <p class="mb-3">${this.escapeHtml(summary.productName)}</p>
                    
                    ${
											productInfo.description
												? `
                        <h6><i class="fas fa-align-left me-2"></i>Description</h6>
                        <p class="mb-3">${this.escapeHtml(
													productInfo.description
												)}</p>
                    `
												: ''
										}
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-download me-2"></i>Download Summary</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Images:</strong> ${
													summary.totalImages
												}</li>
                        <li><strong>Colors Found:</strong> ${
													summary.colorsFound
												}</li>
                        <li><strong>Status:</strong> <span class="badge bg-success">Complete</span></li>
                    </ul>
                    
                    ${
											productInfo.colors && productInfo.colors.length > 0
												? `
                        <h6><i class="fas fa-palette me-2"></i>Available Colors</h6>
                        <div class="d-flex flex-wrap gap-1">
                            ${productInfo.colors
															.map(
																(color) =>
																	`<span class="badge bg-secondary">${this.escapeHtml(
																		color
																	)}</span>`
															)
															.join('')}
                        </div>
                    `
												: ''
										}
                </div>
            </div>
        `;

		infoElement.innerHTML = html;
		document.getElementById('productInfoSection').style.display = 'block';
	}

	displayImages(images) {
		const resultsElement = document.getElementById('imageResults');
		if (!resultsElement || !images.length) return;

		const html = images
			.map(
				(image) => `
            <div class="image-card">
                <img src="downloads/${image.path.split('/').pop()}" 
                     alt="${this.escapeHtml(image.color)} ${this.escapeHtml(
					image.view
				)}" 
                     class="image-preview"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display: none; padding: 20px; color: #999;">
                    <i class="fas fa-image fa-2x"></i><br>
                    Image not found
                </div>
                <div class="mt-2">
                    <small class="text-muted d-block">${this.escapeHtml(
											image.filename
										)}</small>
                    <small class="text-muted">${this.escapeHtml(
											image.color
										)} - ${this.escapeHtml(image.view)}</small>
                    <br><small class="text-success">${this.formatBytes(
											image.size
										)}</small>
                </div>
            </div>
        `
			)
			.join('');

		resultsElement.innerHTML = html;
		document.getElementById('resultsSection').style.display = 'block';
	}

	displayLog(log) {
		const logElement = document.getElementById('logOutput');
		if (!logElement || !log.length) return;

		const logText = log.join('\n');
		logElement.textContent = logText;

		// Auto-scroll to bottom
		logElement.scrollTop = logElement.scrollHeight;

		document.getElementById('logSection').style.display = 'block';
	}

	showProgress() {
		const progressSection = document.getElementById('progressSection');
		if (progressSection) {
			progressSection.classList.add('active');
		}
	}

	hideProgress() {
		const progressSection = document.getElementById('progressSection');
		if (progressSection) {
			progressSection.classList.remove('active');
		}
	}

	updateProgress(percentage, status) {
		const progressBar = document.getElementById('progressBar');
		const statusText = document.getElementById('statusText');

		if (progressBar) {
			progressBar.style.width = `${percentage}%`;
			progressBar.setAttribute('aria-valuenow', percentage);
		}

		if (statusText) {
			statusText.textContent = status;
		}
	}

	showResultSections() {
		['productInfoSection', 'resultsSection', 'logSection'].forEach((id) => {
			const element = document.getElementById(id);
			if (element) {
				element.style.display = 'block';
			}
		});
	}

	resetResults() {
		['productInfoSection', 'resultsSection', 'logSection'].forEach((id) => {
			const element = document.getElementById(id);
			if (element) {
				element.style.display = 'none';
			}
		});

		// Clear content
		const elements = ['productInfo', 'imageResults', 'logOutput'];
		elements.forEach((id) => {
			const element = document.getElementById(id);
			if (element) {
				element.innerHTML = '';
			}
		});
	}

	resetUI() {
		this.hideProgress();
		this.resetResults();
		this.updateProgress(0, 'Ready to start...');
		this.lastScrapedData = null;
	}

	async exportProductData() {
		if (!this.lastScrapedData) {
			this.showAlert(
				'❌ No data to export. Please scrape a product first.',
				'warning'
			);
			return;
		}

		try {
			const formData = new FormData();
			formData.append('action', 'export');
			formData.append('data', JSON.stringify(this.lastScrapedData));

			const response = await fetch('product-scraper-api.php', {
				method: 'POST',
				body: formData,
			});

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}

			const result = await response.json();

			if (result.success) {
				// Download the export file
				this.downloadJSON(result.export, result.filename);
				this.showAlert('📄 Product data exported successfully!', 'success');
			} else {
				throw new Error(result.error || 'Export failed');
			}
		} catch (error) {
			console.error('Export error:', error);
			this.showAlert(`❌ Export failed: ${error.message}`, 'danger');
		}
	}

	downloadJSON(data, filename) {
		const jsonString = JSON.stringify(data, null, 2);
		const blob = new Blob([jsonString], { type: 'application/json' });
		const url = URL.createObjectURL(blob);

		const link = document.createElement('a');
		link.href = url;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		URL.revokeObjectURL(url);
	}

	showAlert(message, type = 'info') {
		// Remove existing alerts
		const existingAlerts = document.querySelectorAll('.temp-alert');
		existingAlerts.forEach((alert) => alert.remove());

		// Create new alert
		const alertDiv = document.createElement('div');
		alertDiv.className = `alert alert-${type} alert-dismissible fade show temp-alert`;
		alertDiv.style.margin = '20px 0';
		alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

		// Safe insertion - try multiple strategies
		let inserted = false;

		// Strategy 1: Insert after header in scraper container
		const scraperContainer = document.querySelector('.scraper-container');
		if (scraperContainer && !inserted) {
			const rows = scraperContainer.querySelectorAll('.row');
			if (rows.length > 0) {
				rows[0].insertAdjacentElement('afterend', alertDiv);
				inserted = true;
			}
		}

		// Strategy 2: Insert at top of first container
		if (!inserted) {
			const container = document.querySelector('.container-fluid, .container');
			if (container) {
				container.insertAdjacentElement('afterbegin', alertDiv);
				inserted = true;
			}
		}

		// Strategy 3: Fallback to body
		if (!inserted) {
			document.body.insertAdjacentElement('afterbegin', alertDiv);
		}

		// Auto-remove after 5 seconds
		setTimeout(() => {
			if (alertDiv.parentNode) {
				alertDiv.remove();
			}
		}, 5000);
	}

	escapeHtml(unsafe) {
		return (unsafe || '')
			.toString()
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	formatBytes(bytes, decimals = 2) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const dm = decimals < 0 ? 0 : decimals;
		const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}
}

// Global functions
function clearResults() {
	if (window.scraper) {
		window.scraper.resetUI();
	}
}

function exportProductData() {
	if (window.scraper) {
		window.scraper.exportProductData();
	}
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function () {
	window.scraper = new ProductScraper();
	console.log('🎉 SanMar Product Scraper initialized!');
});
