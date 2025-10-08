let currentScrapeResult = null;
let currentScrapeLog = [];

const form = document.getElementById('companyCasualsForm');
const selectEl = document.getElementById('productCodeSelect');
const codeInput = document.getElementById('productCodeInput');
const urlInput = document.getElementById('productUrlInput');
const includeColorsInput = document.getElementById('includeColors');
const progressSection = document.getElementById('progressSection');
const progressBar = document.getElementById('progressBar');
const statusText = document.getElementById('statusText');
const resultsSection = document.getElementById('resultsSection');
const resultProductName = document.getElementById('resultProductName');
const resultOriginalCode = document.getElementById('resultOriginalCode');
const resultNormalizedCode = document.getElementById('resultNormalizedCode');
const resultReferenceColor = document.getElementById('resultReferenceColor');
const resultSourceUrl = document.getElementById('resultSourceUrl');
const resultTimestamp = document.getElementById('resultTimestamp');
const pricesHead = document.getElementById('pricesHead');
const pricesBody = document.getElementById('pricesBody');
const colorsCard = document.getElementById('colorsCard');
const colorsList = document.getElementById('colorsList');
const exportBtn = document.getElementById('exportBtn');
const clearBtn = document.getElementById('clearBtn');
const logSection = document.getElementById('logSection');
const logOutput = document.getElementById('logOutput');
const toggleLogBtn = document.getElementById('toggleLogBtn');
const pushBtn = document.getElementById('pushBtn');
const dropZone = document.getElementById('dropZone');
const browseBtn = document.getElementById('browseBtn');
const jsonFileInput = document.getElementById('jsonFileInput');
const importStatusEl = document.getElementById('importStatus');

if (toggleLogBtn) {
	toggleLogBtn.addEventListener('click', () => {
		if (logOutput.style.display === 'none') {
			logOutput.style.display = 'block';
			toggleLogBtn.innerHTML = '<i class="fas fa-compress me-1"></i>Collapse';
		} else {
			logOutput.style.display = 'none';
			toggleLogBtn.innerHTML = '<i class="fas fa-expand me-1"></i>Expand';
		}
	});
}

if (clearBtn) {
	clearBtn.addEventListener('click', () => {
		form.reset();
		selectEl.value = '';
		codeInput.value = '';
		urlInput.value = '';
		includeColorsInput.checked = true;
		hideProgress();
		hideResults();
		clearLog();
		currentScrapeResult = null;
		currentScrapeLog = [];
		updateImportStatus('Upload a JSON file or run a scrape to enable import.');
	});
}

if (selectEl) {
	selectEl.addEventListener('change', () => {
		if (selectEl.value) {
			codeInput.value = selectEl.value;
		}
	});
}

if (form) {
	form.addEventListener('submit', (event) => {
		event.preventDefault();
		runScrape();
	});
}

if (exportBtn) {
	exportBtn.addEventListener('click', () => {
		if (!currentScrapeResult) {
			alert('No data available to export yet.');
			return;
		}

		const payload = {
			scraper: 'company_casuals',
			generated_at: new Date().toISOString(),
			result: currentScrapeResult,
			log: currentScrapeLog,
		};

		const filename =
			currentScrapeResult.suggestedFilename ||
			`${
				currentScrapeResult.normalized_code || 'product'
			}_price_scrape_${timestampString()}.json`;

		const blob = new Blob([JSON.stringify(payload, null, 2)], {
			type: 'application/json',
		});
		const url = window.URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = url;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		window.URL.revokeObjectURL(url);
	});
}

if (pushBtn) {
	pushBtn.addEventListener('click', () => {
		if (!currentScrapeResult) {
			alert(
				'Run a scrape or upload a JSON file before pushing to the database.'
			);
			return;
		}
		submitImportPayload({
			source: 'ui_push',
			scraper: 'company_casuals',
			generated_at: new Date().toISOString(),
			data: currentScrapeResult,
			log: currentScrapeLog,
		});
	});
}

if (browseBtn && jsonFileInput) {
	browseBtn.addEventListener('click', () => jsonFileInput.click());
	jsonFileInput.addEventListener('change', () => {
		const file = jsonFileInput.files?.[0];
		if (!file) {
			return;
		}
		handleJsonFileUpload(file);
		jsonFileInput.value = '';
	});
}

if (dropZone) {
	const resetDrag = () => dropZone.classList.remove('dragover');
	dropZone.addEventListener('click', () => browseBtn?.click());
	dropZone.addEventListener('dragover', (event) => {
		event.preventDefault();
		dropZone.classList.add('dragover');
	});
	dropZone.addEventListener('dragleave', resetDrag);
	dropZone.addEventListener('dragend', resetDrag);
	dropZone.addEventListener('drop', (event) => {
		event.preventDefault();
		resetDrag();
		const file = event.dataTransfer?.files?.[0];
		if (!file) {
			return;
		}
		handleJsonFileUpload(file);
	});
}

function runScrape() {
	const selectedCode = selectEl ? selectEl.value.trim() : '';
	const manualCode = codeInput ? codeInput.value.trim() : '';
	const fullUrl = urlInput ? urlInput.value.trim() : '';
	const includeColors = includeColorsInput ? includeColorsInput.checked : true;

	if (!selectedCode && !manualCode && !fullUrl) {
		alert(
			'Please choose a product code, enter one manually, or provide a full URL.'
		);
		return;
	}

	const payload = new FormData();
	payload.append('action', 'scrape');

	if (fullUrl) {
		payload.append('url', fullUrl);
	}

	const codeToUse = fullUrl ? '' : manualCode || selectedCode;
	if (codeToUse) {
		payload.append('code', codeToUse);
	}

	payload.append('includeColors', includeColors ? '1' : '0');

	showProgress('Contacting Company Casuals...', 25);
	clearLog();
	hideResults();

	fetch('company-casuals-scraper-api.php', {
		method: 'POST',
		body: payload,
	})
		.then(async (response) => {
			const data = await response.json();
			if (!response.ok || !data.success) {
				throw data;
			}
			return data;
		})
		.then((data) => {
			currentScrapeLog = data.log || [];
			const result = data.data || {};
			currentScrapeResult = {
				...result,
				suggestedFilename: data.suggestedFilename,
			};

			renderResult(result, data.log || []);
			hideProgress();
		})
		.catch((error) => {
			hideProgress();
			showError(error);
		});
}

function renderResult(result, log) {
	if (!result) {
		return;
	}

	resultsSection.style.display = 'block';
	logSection.style.display = 'block';
	renderLog(log, 'success');

	resultProductName.textContent = result.product_name || 'Unknown Product';
	resultOriginalCode.textContent = result.original_code || '—';
	resultNormalizedCode.textContent = result.normalized_code || '—';
	resultReferenceColor.textContent =
		result.price_reference_color || 'First color not detected';
	resultTimestamp.textContent = `Scraped at ${formatDisplayDate(
		result.scraped_at
	)}`;

	if (result.final_url || result.source_url) {
		resultSourceUrl.href = result.final_url || result.source_url;
		resultSourceUrl.textContent = result.final_url || result.source_url;
	}

	renderPrices(result);
	renderColors(result.colors || []);
	updateImportStatus('Ready to push scraped data into the database.');
}

function renderPrices(result) {
	const sizes = result.sizes || [];
	const prices = result.prices || [];

	pricesHead.innerHTML = '';
	pricesBody.innerHTML = '';

	if (sizes.length === 0 || prices.length === 0) {
		pricesBody.innerHTML =
			'<tr><td class="text-center text-muted">No pricing data returned.</td></tr>';
		return;
	}

	const headerRow = document.createElement('tr');
	headerRow.innerHTML = '<th scope="col">Size</th><th scope="col">Price</th>';
	pricesHead.appendChild(headerRow);

	prices.forEach((entry) => {
		const tr = document.createElement('tr');
		const size = document.createElement('td');
		size.textContent = entry.size || '';
		const price = document.createElement('td');
		price.textContent = formatPrice(entry.price, entry.raw);
		tr.appendChild(size);
		tr.appendChild(price);
		pricesBody.appendChild(tr);
	});
}

function renderColors(colors) {
	if (!colors || colors.length === 0) {
		colorsCard.style.display = 'none';
		colorsList.innerHTML = '';
		updateImportStatus('Upload a JSON file or run a scrape to enable import.');
		return;
	}

	colorsCard.style.display = 'block';
	colorsList.innerHTML = '';

	colors.forEach((color) => {
		const col = document.createElement('div');
		col.className = 'col-auto';

		const badge = document.createElement('span');
		badge.className = 'badge bg-secondary fs-6';
		badge.textContent = color;

		col.appendChild(badge);
		colorsList.appendChild(col);
	});
}

function handleJsonFileUpload(file) {
	if (!file || file.type !== 'application/json') {
		updateImportStatus(
			'Please select a JSON file exported from the scraper.',
			'text-danger'
		);
		return;
	}

	const reader = new FileReader();
	reader.onload = (event) => {
		try {
			const text = event.target?.result;
			if (typeof text !== 'string') {
				throw new Error('Unable to read file contents.');
			}
			submitImportPayload({
				source: 'file_upload',
				payload: text,
				filename: file.name,
			});
		} catch (error) {
			console.error('Import read error', error);
			updateImportStatus(
				`Failed to read JSON file: ${error.message}`,
				'text-danger'
			);
		}
	};
	reader.onerror = () => {
		updateImportStatus('Failed to read the selected file.', 'text-danger');
	};
	reader.readAsText(file);
}

async function submitImportPayload(payloadWrapper) {
	try {
		setImportBusy(true);
		if (payloadWrapper.payload) {
			updateImportStatus('Uploading JSON file...');
		} else {
			updateImportStatus('Sending scraped pricing to the database...');
		}

		const formData = new FormData();
		formData.append('action', 'import');

		if (payloadWrapper.payload) {
			formData.append('payload', payloadWrapper.payload);
			if (payloadWrapper.filename) {
				formData.append('filename', payloadWrapper.filename);
			}
		} else {
			formData.append('payload', JSON.stringify(payloadWrapper));
		}

		const response = await fetch('company-casuals-scraper-api.php', {
			method: 'POST',
			body: formData,
		});

		const data = await response.json();
		if (!response.ok || data.success === false) {
			throw data;
		}

		handleImportSuccess(data);
	} catch (error) {
		handleImportFailure(error);
	} finally {
		setImportBusy(false);
	}
}

function handleImportSuccess(response) {
	const summary = response.summary || {};
	const parts = [];
	if (typeof summary.inserted === 'number')
		parts.push(`${summary.inserted} inserted`);
	if (typeof summary.updated === 'number')
		parts.push(`${summary.updated} updated`);
	if (typeof summary.unchanged === 'number')
		parts.push(`${summary.unchanged} unchanged`);
	if (typeof summary.skipped === 'number' && summary.skipped > 0)
		parts.push(`${summary.skipped} skipped`);

	const baseMessage = parts.length > 0 ? parts.join(', ') : 'Import completed';
	const productInfo = summary.processed_codes?.length
		? `for codes ${summary.processed_codes.join(', ')}`
		: '';
	updateImportStatus(`✅ ${baseMessage} ${productInfo}`.trim(), 'text-success');

	if (Array.isArray(response.warnings) && response.warnings.length > 0) {
		const details = response.warnings
			.map(
				(warning) =>
					`• ${warning.reason}${warning.size ? ` (${warning.size})` : ''}`
			)
			.join('\n');
		console.warn('Import warnings:\n' + details);
	}
}

function handleImportFailure(error) {
	console.error('Import failure', error);
	const message =
		error?.error || error?.message || 'Failed to import pricing data.';
	updateImportStatus(`❌ ${message}`, 'text-danger');
	if (error?.details) {
		console.error('Import error details:', error.details);
	}
}

function setImportBusy(isBusy) {
	if (pushBtn) {
		pushBtn.disabled = isBusy;
		pushBtn.innerHTML = isBusy
			? '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Working...'
			: '<i class="fas fa-database me-1"></i>Push to Database';
	}
	if (browseBtn) {
		browseBtn.disabled = isBusy;
		browseBtn.innerHTML = isBusy
			? '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Uploading...'
			: '<i class="fas fa-folder-open me-1"></i>Select JSON';
	}
	if (dropZone) {
		dropZone.classList.toggle('disabled', isBusy);
	}
}

function updateImportStatus(message, modifierClass = 'text-muted') {
	if (!importStatusEl) {
		return;
	}
	importStatusEl.className = modifierClass;
	importStatusEl.textContent = message;
}

updateImportStatus('Upload a JSON file or run a scrape to enable import.');

function renderLog(entries, status = 'info') {
	logSection.style.display = 'block';
	logOutput.style.display = 'block';

	if (!entries || entries.length === 0) {
		logOutput.textContent = 'No log entries available.';
		return;
	}

	const lines = entries.map((entry) => {
		const ts = entry.timestamp || new Date().toISOString();
		const msg = entry.message || 'Log entry';
		const context = entry.context ? JSON.stringify(entry.context) : '';
		return `[${ts}] ${msg}${context ? ` :: ${context}` : ''}`;
	});

	logOutput.textContent = lines.join('\n');
}

function showProgress(message, progressValue = 40) {
	progressSection.classList.add('active');
	statusText.textContent = message;
	progressBar.style.width = `${Math.min(100, Math.max(10, progressValue))}%`;
}

function hideProgress() {
	progressSection.classList.remove('active');
	progressBar.style.width = '0%';
	statusText.textContent = '';
}

function hideResults() {
	resultsSection.style.display = 'none';
	colorsCard.style.display = 'none';
	pricesHead.innerHTML = '';
	pricesBody.innerHTML = '';
	updateImportStatus('Upload a JSON file or run a scrape to enable import.');
}

function clearLog() {
	logSection.style.display = 'none';
	logOutput.textContent = '';
}

function showError(error) {
	console.error('Scrape error', error);
	const message = error?.error || error?.message || 'Scrape failed.';
	alert(message);

	const log = error?.log || [];
	currentScrapeLog = log;
	if (log.length > 0) {
		renderLog(log, 'error');
	}
}

function formatPrice(price, fallback) {
	if (typeof price === 'number' && !Number.isNaN(price)) {
		return `$${price.toFixed(2)}`;
	}
	if (fallback) {
		return fallback;
	}
	return '—';
}

function formatDisplayDate(timestamp) {
	if (!timestamp) {
		return 'unknown time';
	}
	const date = new Date(timestamp);
	if (Number.isNaN(date.getTime())) {
		return timestamp;
	}
	return date.toLocaleString();
}

function timestampString() {
	const now = new Date();
	const pad = (n) => n.toString().padStart(2, '0');
	return `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(
		now.getDate()
	)}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
}
