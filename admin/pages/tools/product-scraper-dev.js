// SanMar Product Scraper JavaScript - DEVELOPMENT VERSION
// This version works with the no-auth development API

let currentProductData = null;

document.getElementById('scraperForm').addEventListener('submit', function (e) {
	e.preventDefault();
	startScraping();
});

function startScraping() {
	// Get form data
	const url = document.getElementById('productUrl').value.trim();
	const imageFolder = document.getElementById('imageFolder').value.trim();
	const namingFormat = document.getElementById('namingFormat').value;

	if (!url) {
		alert('Please enter a product URL');
		return;
	}

	// Validate URL
	if (!url.includes('sanmar.com')) {
		alert('Please enter a valid SanMar product URL');
		return;
	}

	// Reset UI
	resetUI();

	// Show progress section
	document.getElementById('progressSection').classList.add('active');

	// Prepare request data
	const requestData = {
		url: url,
		imageFolder: imageFolder,
		namingFormat: namingFormat,
	};

	// Start scraping with fetch
	fetch('product-scraper-api-dev.php', {
		// Use development API
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify(requestData),
	})
		.then((response) => {
			const reader = response.body.getReader();
			const decoder = new TextDecoder();
			let buffer = '';

			function processText({ done, value }) {
				if (done) {
					// Process any remaining buffer
					if (buffer.trim()) {
						try {
							const finalData = JSON.parse(buffer.trim());
							handleFinalResult(finalData);
						} catch (e) {
							console.error('Error parsing final result:', e);
							updateStatus(100, 'Error processing final result');
						}
					}
					return;
				}

				// Add new chunk to buffer
				buffer += decoder.decode(value, { stream: true });

				// Process complete JSON objects
				const lines = buffer.split('\n');
				buffer = lines.pop() || ''; // Keep incomplete line in buffer

				lines.forEach((line) => {
					if (line.trim()) {
						try {
							const data = JSON.parse(line.trim());

							if (data.progress !== undefined) {
								updateStatus(data.progress, data.message);
							}

							if (data.success || data.error) {
								handleFinalResult(data);
							}
						} catch (e) {
							console.log('Non-JSON line:', line);
						}
					}
				});

				return reader.read().then(processText);
			}

			return reader.read().then(processText);
		})
		.catch((error) => {
			console.error('Fetch error:', error);
			updateStatus(0, 'Connection error: ' + error.message);

			// Show alert in a way that won't cause DOM errors
			setTimeout(() => {
				try {
					alert('Error connecting to scraper API: ' + error.message);
				} catch (e) {
					console.error('Error showing alert:', e);
				}
			}, 100);
		});
}

function updateStatus(progress, message) {
	try {
		const progressBar = document.getElementById('progressBar');
		const statusText = document.getElementById('statusText');

		if (progressBar) {
			progressBar.style.width = progress + '%';
			progressBar.setAttribute('aria-valuenow', progress);
		}

		if (statusText) {
			statusText.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                <strong>${message}</strong>
                <span class="text-muted ms-2">(${progress}%)</span>
            `;
		}

		// Update log
		addToLog(`[${new Date().toLocaleTimeString()}] ${message} (${progress}%)`);
	} catch (error) {
		console.error('Error updating status:', error);
	}
}

function handleFinalResult(data) {
	try {
		if (data.error) {
			updateStatus(0, 'Error: ' + data.error);
			showLog();

			setTimeout(() => {
				try {
					alert('Scraping failed: ' + data.error);
				} catch (e) {
					console.error('Error showing alert:', e);
				}
			}, 100);

			return;
		}

		if (data.success && data.product) {
			currentProductData = data.product;

			// Hide progress section
			document.getElementById('progressSection').classList.remove('active');

			// Show product info
			showProductInfo(data.product, data.stats);

			// Show results
			showResults(data.product, data.stats);

			addToLog(
				`[${new Date().toLocaleTimeString()}] Scraping completed successfully!`,
			);
			addToLog(
				`Downloaded ${data.stats.downloaded_images} images for ${data.stats.colors_found} colors`,
			);
		}
	} catch (error) {
		console.error('Error handling result:', error);
		updateStatus(0, 'Error processing results');
	}
}

function showProductInfo(product, stats) {
	try {
		const productInfoSection = document.getElementById('productInfoSection');
		const productInfo = document.getElementById('productInfo');

		if (productInfo) {
			productInfo.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h4><strong>${
													product.name || 'Unknown Product'
												}</strong></h4>
                        <p><strong>Product Code:</strong> ${product.code}</p>
                        <p><strong>Description:</strong> ${
													product.description || 'No description available'
												}</p>
                        <p><strong>Colors Found:</strong> ${
													stats.colors_found
												}</p>
                        <p><strong>Images Downloaded:</strong> ${
													stats.downloaded_images
												} of ${stats.total_images}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="badge bg-light text-dark fs-6 p-3">
                            <i class="fas fa-download me-2"></i>
                            Downloaded to:<br>
                            <code>${product.download_folder}</code>
                        </div>
                    </div>
                </div>
            `;
		}

		if (productInfoSection) {
			productInfoSection.style.display = 'block';
		}
	} catch (error) {
		console.error('Error showing product info:', error);
	}
}

function showResults(product, stats) {
	try {
		const resultsSection = document.getElementById('resultsSection');
		const imageResults = document.getElementById('imageResults');

		if (imageResults && product.images) {
			let html = '';

			product.images.forEach((image, index) => {
				// Create local URL for image preview
				const localImageUrl = `downloads/${product.download_folder}/${image.filename}`;

				html += `
                    <div class="image-card">
                        <img src="${localImageUrl}" 
                             alt="${image.color} - ${image.view}" 
                             class="image-preview"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIE5vdCBGb3VuZDwvdGV4dD48L3N2Zz4='">
                        <div class="mt-2">
                            <strong>${image.filename}</strong><br>
                            <small class="text-muted">
                                ${image.color} - ${image.view}
                                ${image.size ? `<br>${(image.size / 1024).toFixed(1)} KB` : ''}
                            </small>
                        </div>
                    </div>
                `;
			});

			imageResults.innerHTML = html;
		}

		if (resultsSection) {
			resultsSection.style.display = 'block';
		}
	} catch (error) {
		console.error('Error showing results:', error);
	}
}

function exportProductData() {
	if (!currentProductData) {
		alert('No product data to export');
		return;
	}

	try {
		const dataStr = JSON.stringify(currentProductData, null, 2);
		const dataBlob = new Blob([dataStr], { type: 'application/json' });
		const url = URL.createObjectURL(dataBlob);

		const link = document.createElement('a');
		link.href = url;
		link.download = `${currentProductData.code}_product_data.json`;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);

		URL.revokeObjectURL(url);

		addToLog(
			`[${new Date().toLocaleTimeString()}] Product data exported as ${
				currentProductData.code
			}_product_data.json`,
		);
	} catch (error) {
		console.error('Error exporting data:', error);
		alert('Error exporting product data');
	}
}

function addToLog(message) {
	try {
		const logOutput = document.getElementById('logOutput');
		if (logOutput) {
			logOutput.textContent += message + '\n';
			logOutput.scrollTop = logOutput.scrollHeight;
		}

		// Auto-show log if there are errors
		if (message.toLowerCase().includes('error')) {
			showLog();
		}
	} catch (error) {
		console.error('Error adding to log:', error);
	}
}

function showLog() {
	try {
		const logSection = document.getElementById('logSection');
		if (logSection) {
			logSection.style.display = 'block';
		}
	} catch (error) {
		console.error('Error showing log:', error);
	}
}

function clearResults() {
	try {
		// Reset all sections
		document.getElementById('progressSection').classList.remove('active');
		document.getElementById('productInfoSection').style.display = 'none';
		document.getElementById('resultsSection').style.display = 'none';
		document.getElementById('logSection').style.display = 'none';

		// Clear content
		const progressBar = document.getElementById('progressBar');
		if (progressBar) {
			progressBar.style.width = '0%';
		}

		const statusText = document.getElementById('statusText');
		if (statusText) {
			statusText.textContent = 'Ready to start scraping...';
		}

		const logOutput = document.getElementById('logOutput');
		if (logOutput) {
			logOutput.textContent = '';
		}

		const imageResults = document.getElementById('imageResults');
		if (imageResults) {
			imageResults.innerHTML = '';
		}

		const productInfo = document.getElementById('productInfo');
		if (productInfo) {
			productInfo.innerHTML = '';
		}

		currentProductData = null;
	} catch (error) {
		console.error('Error clearing results:', error);
	}
}

function resetUI() {
	try {
		// Reset progress
		const progressBar = document.getElementById('progressBar');
		if (progressBar) {
			progressBar.style.width = '0%';
		}

		const statusText = document.getElementById('statusText');
		if (statusText) {
			statusText.textContent = 'Initializing scraper...';
		}

		// Hide results sections
		document.getElementById('productInfoSection').style.display = 'none';
		document.getElementById('resultsSection').style.display = 'none';

		// Clear log
		const logOutput = document.getElementById('logOutput');
		if (logOutput) {
			logOutput.textContent = '';
		}
	} catch (error) {
		console.error('Error resetting UI:', error);
	}
}
