/**
 * Color Manager JavaScript - Berkeley County Store Admin
 */

class ColorManager {
	constructor() {
		this.colors = [];
		this.filteredColors = [];
		this.init();
	}

	init() {
		console.log('🎨 Initializing Color Manager...');
		this.setupEventListeners();
		this.loadColors();
	}

	setupEventListeners() {
		// Form submission
		const form = document.getElementById('colorForm');
		if (form) {
			form.addEventListener('submit', (e) => this.handleSubmit(e));
		}

		// Color picker sync with hex input
		this.syncColorPickers();

		// Search functionality
		const searchInput = document.getElementById('searchInput');
		if (searchInput) {
			searchInput.addEventListener('input', (e) =>
				this.filterColors(e.target.value)
			);
		}

		// Auto-focus color name input
		const nameInput = document.getElementById('colorName');
		if (nameInput) {
			nameInput.focus();
		}
	}

	syncColorPickers() {
		// Primary color
		this.syncPicker('primaryPicker', 'primaryHex', 'previewPrimary');

		// Secondary color
		this.syncPicker('secondaryPicker', 'secondaryHex', 'previewSecondary');

		// Tertiary color
		this.syncPicker('tertiaryPicker', 'tertiaryHex', 'previewTertiary');
	}

	syncPicker(pickerId, inputId, previewId) {
		const picker = document.getElementById(pickerId);
		const input = document.getElementById(inputId);

		if (!picker || !input) return;

		// Picker changes input
		picker.addEventListener('input', (e) => {
			input.value = e.target.value.toUpperCase();
			this.updatePreview();
		});

		// Input changes picker
		input.addEventListener('input', (e) => {
			const value = e.target.value;
			if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
				picker.value = value;
			}
			this.updatePreview();
		});

		// Update preview on blur
		input.addEventListener('blur', (e) => {
			let value = e.target.value.trim();
			if (value && !value.startsWith('#')) {
				value = '#' + value;
				e.target.value = value;
			}
			if (value) {
				e.target.value = value.toUpperCase();
			}
			this.updatePreview();
		});
	}

	updatePreview() {
		const primary = document.getElementById('primaryHex').value || '#000000';
		const secondary = document.getElementById('secondaryHex').value;
		const tertiary = document.getElementById('tertiaryHex').value;

		const preview = document.getElementById('colorPreview');
		preview.innerHTML = '';

		// Always show primary
		const primaryDiv = document.createElement('div');
		primaryDiv.className = 'color-segment';
		primaryDiv.id = 'previewPrimary';
		primaryDiv.style.backgroundColor = primary;
		preview.appendChild(primaryDiv);

		// Add secondary if provided
		if (secondary && /^#[0-9A-Fa-f]{6}$/.test(secondary)) {
			const secondaryDiv = document.createElement('div');
			secondaryDiv.className = 'color-segment';
			secondaryDiv.id = 'previewSecondary';
			secondaryDiv.style.backgroundColor = secondary;
			preview.appendChild(secondaryDiv);
		}

		// Add tertiary if provided
		if (tertiary && /^#[0-9A-Fa-f]{6}$/.test(tertiary)) {
			const tertiaryDiv = document.createElement('div');
			tertiaryDiv.className = 'color-segment';
			tertiaryDiv.id = 'previewTertiary';
			tertiaryDiv.style.backgroundColor = tertiary;
			preview.appendChild(tertiaryDiv);
		}
	}

	async handleSubmit(e) {
		e.preventDefault();

		const colorName = document.getElementById('colorName').value.trim();
		const primaryHex = document.getElementById('primaryHex').value.trim();
		const secondaryHex = document.getElementById('secondaryHex').value.trim();
		const tertiaryHex = document.getElementById('tertiaryHex').value.trim();

		// Validation
		if (!colorName) {
			this.showAlert('Please enter a color name', 'danger');
			return;
		}

		if (!primaryHex || !/^#[0-9A-Fa-f]{6}$/.test(primaryHex)) {
			this.showAlert('Please enter a valid primary hex color', 'danger');
			return;
		}

		if (secondaryHex && !/^#[0-9A-Fa-f]{6}$/.test(secondaryHex)) {
			this.showAlert('Secondary hex color is invalid', 'danger');
			return;
		}

		if (tertiaryHex && !/^#[0-9A-Fa-f]{6}$/.test(tertiaryHex)) {
			this.showAlert('Tertiary hex color is invalid', 'danger');
			return;
		}

		await this.saveColor({
			color: colorName,
			p_hex: primaryHex.toUpperCase(),
			s_hex: secondaryHex ? secondaryHex.toUpperCase() : null,
			t_hex: tertiaryHex ? tertiaryHex.toUpperCase() : null,
		});
	}

	async saveColor(colorData) {
		this.showLoading(true);

		try {
			const formData = new FormData();
			formData.append('action', 'add');
			formData.append('color', colorData.color);
			formData.append('p_hex', colorData.p_hex);
			if (colorData.s_hex) formData.append('s_hex', colorData.s_hex);
			if (colorData.t_hex) formData.append('t_hex', colorData.t_hex);

			const response = await fetch('color-manager-api.php', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				this.showAlert(
					`✓ Color "${colorData.color}" added successfully!`,
					'success'
				);
				this.resetForm();
				this.loadColors(); // Reload the list
			} else {
				this.showAlert(`Error: ${result.message}`, 'danger');
			}
		} catch (error) {
			console.error('Save error:', error);
			this.showAlert('Failed to save color: ' + error.message, 'danger');
		} finally {
			this.showLoading(false);
		}
	}

	async loadColors() {
		try {
			const response = await fetch(
				'color-manager-api.php?action=list&limit=1000'
			);
			const result = await response.json();

			if (result.success) {
				this.colors = result.colors;
				this.filteredColors = this.colors;
				this.renderColors();
				this.updateTotal();
			} else {
				console.error('Failed to load colors:', result.message);
			}
		} catch (error) {
			console.error('Load error:', error);
			this.showAlert('Failed to load colors', 'danger');
		}
	}

	filterColors(searchTerm) {
		const term = searchTerm.toLowerCase().trim();

		if (!term) {
			this.filteredColors = this.colors;
		} else {
			this.filteredColors = this.colors.filter(
				(color) =>
					color.color.toLowerCase().includes(term) ||
					color.color_id.toString().includes(term) ||
					(color.p_hex && color.p_hex.toLowerCase().includes(term)) ||
					(color.s_hex && color.s_hex.toLowerCase().includes(term)) ||
					(color.t_hex && color.t_hex.toLowerCase().includes(term))
			);
		}

		this.renderColors();
	}

	renderColors() {
		const tbody = document.getElementById('colorsTableBody');
		if (!tbody) return;

		if (this.filteredColors.length === 0) {
			tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-palette me-2"></i>No colors found
                    </td>
                </tr>
            `;
			return;
		}

		tbody.innerHTML = this.filteredColors
			.map((color) => this.renderColorRow(color))
			.join('');
	}

	renderColorRow(color) {
		const previewHtml = this.generatePreviewHtml(color);
		const secondaryBadge = color.s_hex
			? `<span class="badge badge-color" style="background-color: ${
					color.s_hex
			  }; color: ${this.getContrastColor(color.s_hex)};">${color.s_hex}</span>`
			: '<span class="text-muted">—</span>';
		const tertiaryBadge = color.t_hex
			? `<span class="badge badge-color" style="background-color: ${
					color.t_hex
			  }; color: ${this.getContrastColor(color.t_hex)};">${color.t_hex}</span>`
			: '<span class="text-muted">—</span>';

		return `
            <tr class="color-row" data-color-id="${color.color_id}">
                <td class="align-middle">${color.color_id}</td>
                <td class="align-middle"><strong>${this.escapeHtml(
									color.color
								)}</strong></td>
                <td class="align-middle">${previewHtml}</td>
                <td class="align-middle">
                    <span class="badge badge-color" style="background-color: ${
											color.p_hex
										}; color: ${this.getContrastColor(color.p_hex)};">${
			color.p_hex
		}</span>
                </td>
                <td class="align-middle">${secondaryBadge}</td>
                <td class="align-middle">${tertiaryBadge}</td>
                <td class="align-middle">
                    <button class="btn btn-sm btn-outline-primary" onclick="colorManager.editColor(${
											color.color_id
										})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="colorManager.deleteColor(${
											color.color_id
										}, '${this.escapeHtml(color.color)}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
	}

	generatePreviewHtml(color) {
		const colors = [color.p_hex];
		if (color.s_hex) colors.push(color.s_hex);
		if (color.t_hex) colors.push(color.t_hex);

		if (colors.length === 1) {
			return `<div class="color-sample" style="background-color: ${colors[0]}; width: 100px;"></div>`;
		} else {
			const segments = colors
				.map(
					(c) =>
						`<div class="color-segment" style="background-color: ${c};"></div>`
				)
				.join('');
			return `<div class="multi-color-preview" style="height: 40px; width: 120px;">${segments}</div>`;
		}
	}

	getContrastColor(hexColor) {
		// Convert hex to RGB
		const r = parseInt(hexColor.substr(1, 2), 16);
		const g = parseInt(hexColor.substr(3, 2), 16);
		const b = parseInt(hexColor.substr(5, 2), 16);

		// Calculate luminance
		const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

		return luminance > 0.5 ? '#000000' : '#FFFFFF';
	}

	updateTotal() {
		const totalEl = document.getElementById('totalColors');
		if (totalEl) {
			totalEl.textContent = this.colors.length;
		}
	}

	resetForm() {
		document.getElementById('colorForm').reset();
		document.getElementById('primaryPicker').value = '#000000';
		document.getElementById('secondaryPicker').value = '#FFFFFF';
		document.getElementById('tertiaryPicker').value = '#FF0000';
		this.updatePreview();
		document.getElementById('colorName').focus();
	}

	async editColor(colorId) {
		// TODO: Implement edit functionality
		this.showAlert('Edit functionality coming soon!', 'info');
	}

	async deleteColor(colorId, colorName) {
		if (!confirm(`Are you sure you want to delete the color "${colorName}"?`)) {
			return;
		}

		this.showLoading(true);

		try {
			const formData = new FormData();
			formData.append('action', 'delete');
			formData.append('color_id', colorId);

			const response = await fetch('color-manager-api.php', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				this.showAlert(
					`✓ Color "${colorName}" deleted successfully!`,
					'success'
				);
				this.loadColors();
			} else {
				this.showAlert(`Error: ${result.message}`, 'danger');
			}
		} catch (error) {
			console.error('Delete error:', error);
			this.showAlert('Failed to delete color: ' + error.message, 'danger');
		} finally {
			this.showLoading(false);
		}
	}

	showLoading(show) {
		const overlay = document.getElementById('loadingOverlay');
		if (overlay) {
			overlay.classList.toggle('active', show);
		}
	}

	showAlert(message, type) {
		// Create Bootstrap alert
		const alertDiv = document.createElement('div');
		alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
		alertDiv.style.zIndex = '10000';
		alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

		document.body.appendChild(alertDiv);

		// Auto-remove after 5 seconds
		setTimeout(() => {
			alertDiv.remove();
		}, 5000);
	}

	escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
}

// Initialize on page load
let colorManager;
document.addEventListener('DOMContentLoaded', () => {
	colorManager = new ColorManager();
});

// Make resetForm available globally
function resetForm() {
	if (colorManager) {
		colorManager.resetForm();
	}
}
