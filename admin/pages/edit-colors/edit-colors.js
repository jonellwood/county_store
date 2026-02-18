const { createApp, defineComponent } = Vue;

const ColorChip = defineComponent({
	template: '#color-chip',
	props: {
		hex: String,
		label: String,
	},
});

createApp({
	components: { ColorChip },
	data() {
		return {
			colors: [],
			search: '',
			sortBy: 'name',
			filter: {
				multi: false,
				single: false,
				hasImage: false,
			},
			selectedIds: [],
			drawer: {
				open: false,
				color: null,
				form: {
					color: '',
					p_hex: '',
					s_hex: '',
					t_hex: '',
				},
			},
			toast: {
				visible: false,
				type: 'success',
				icon: 'fas fa-circle-check',
				message: '',
			},
			isLoading: false,
		};
	},
	computed: {
		filteredColors() {
			const query = this.search.trim().toLowerCase();
			return this.colors
				.filter((color) => {
					const matchesSearch =
						!query || color.color.toLowerCase().includes(query);
					const multi = Boolean(color.s_hex) || Boolean(color.t_hex);
					const matchesMulti = !this.filter.multi || multi;
					const matchesSingle = !this.filter.single || !multi;
					return matchesSearch && matchesMulti && matchesSingle;
				})
				.sort((a, b) => {
					if (this.sortBy === 'recent') {
						return (b.updated_at || 0) - (a.updated_at || 0);
					}
					if (this.sortBy === 'usage') {
						return (b.usage || 0) - (a.usage || 0);
					}
					return a.color.localeCompare(b.color);
				});
		},
	},
	mounted() {
		this.loadColors();
	},
	methods: {
		async loadColors() {
			this.isLoading = true;
			try {
				const [colorsResponse, usageResponse] = await Promise.all([
					fetch('api/get-colors.php'),
					fetch('api/get-color-use.php'),
				]);

				const [colorsResult, usageResult] = await Promise.all([
					colorsResponse.json(),
					usageResponse.json(),
				]);

				if (!colorsResult.success) {
					throw new Error(colorsResult.message || 'Failed to load colors');
				}

				if (!usageResult.success) {
					throw new Error(
						usageResult.message || 'Failed to load usage metrics',
					);
				}

				const usageMap = (usageResult.usage || []).reduce((acc, entry) => {
					if (entry && typeof entry.color_id !== 'undefined') {
						acc[entry.color_id] = Number(entry.product_count) || 0;
					}
					return acc;
				}, {});

				this.colors = colorsResult.colors.map((color) => ({
					...color,
					usage: usageMap[color.color_id] ?? 0,
				}));
				console.log(this.colors);
			} catch (error) {
				this.showToast(error.message, 'error');
			} finally {
				this.isLoading = false;
			}
		},
		openEditor(color) {
			this.drawer.open = true;
			this.drawer.color = color;
			this.drawer.form = {
				color: color.color,
				p_hex: color.p_hex,
				s_hex: color.s_hex,
				t_hex: color.t_hex,
			};
		},
		closeDrawer() {
			this.drawer.open = false;
			this.drawer.color = null;
		},
		normalizeHex(field) {
			let value = (this.drawer.form[field] || '').trim();
			if (!value) {
				this.drawer.form[field] = '';
				return;
			}
			if (!value.startsWith('#')) {
				value = '#' + value;
			}
			value = value.toUpperCase();
			if (/^#([0-9A-F]{3})$/.test(value)) {
				value =
					'#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3];
			}
			if (!/^#([0-9A-F]{6})$/.test(value)) {
				this.showToast('Invalid hex value', 'error');
				return;
			}
			this.drawer.form[field] = value;
		},
		async saveColor() {
			try {
				const payload = { ...this.drawer.form, id: this.drawer.color.color_id };
				const response = await fetch('api/update-color.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload),
				});
				const result = await response.json();
				if (!result.success) {
					throw new Error(result.message || 'Failed to save color');
				}
				Object.assign(this.drawer.color, payload);
				this.showToast('Color updated');
				this.closeDrawer();
			} catch (error) {
				this.showToast(error.message, 'error');
			}
		},
		confirmDelete(color) {
			if (!confirm(`Delete ${color.color}?`)) return;
			this.deleteColors([color.color_id]);
		},
		confirmBulkDelete() {
			if (!this.selectedIds.length) return;
			if (!confirm(`Delete ${this.selectedIds.length} colors?`)) return;
			this.deleteColors(this.selectedIds);
		},
		async deleteColors(ids) {
			try {
				const response = await fetch('api/delete-colors.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ ids }),
				});
				const result = await response.json();
				if (!result.success) {
					throw new Error(result.message || 'Deletion failed');
				}
				this.colors = this.colors.filter(
					(color) => !ids.includes(color.color_id),
				);
				this.selectedIds = [];
				this.showToast('Colors removed');
			} catch (error) {
				this.showToast(error.message, 'error');
			}
		},
		toggleAll(state) {
			this.selectedIds = state
				? this.filteredColors.map((color) => color.color_id)
				: [];
		},
		showToast(message, type = 'success') {
			this.toast.message = message;
			this.toast.type = type;
			this.toast.icon =
				type === 'success'
					? 'fas fa-circle-check'
					: 'fas fa-triangle-exclamation';
			this.toast.visible = true;
			setTimeout(() => {
				this.toast.visible = false;
			}, 2500);
		},
	},
}).mount('#app');
