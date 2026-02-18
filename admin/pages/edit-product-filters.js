const { createApp, defineComponent } = Vue;

const FilterSelect = defineComponent({
	template: '#filter-select',
	props: {
		label: String,
		value: String,
		options: Array,
	},
});

createApp({
	components: {
		FilterSelect,
	},
	data() {
		return {
			products: [],
			filters: {
				gender: [],
				size: [],
				type: [],
				sleeve: [],
			},
			search: '',
			filter: {
				gender: '',
				size: '',
				type: '',
				sleeve: '',
			},
			lastUpdated: '—',
			isLoading: false,
			toast: {
				visible: false,
				message: '',
				type: 'success',
				icon: 'fas fa-circle-check',
			},
		};
	},
	computed: {
		filteredProducts() {
			return this.products.filter((product) => {
				const query = this.search.trim().toLowerCase();
				const matchesSearch =
					!query ||
					product.code.toLowerCase().includes(query) ||
					product.name.toLowerCase().includes(query) ||
					product.gender?.toLowerCase().includes(query) ||
					product.size?.toLowerCase().includes(query) ||
					product.type?.toLowerCase().includes(query) ||
					product.sleeve?.toLowerCase().includes(query);

				const matchesGender =
					!this.filter.gender || product.gender === this.filter.gender;
				const matchesSize =
					!this.filter.size || product.size === this.filter.size;
				const matchesType =
					!this.filter.type || product.type === this.filter.type;
				const matchesSleeve =
					!this.filter.sleeve || product.sleeve === this.filter.sleeve;

				return (
					matchesSearch &&
					matchesGender &&
					matchesSize &&
					matchesType &&
					matchesSleeve
				);
			});
		},
	},
	mounted() {
		this.loadProducts();
	},
	methods: {
		async loadProducts() {
			this.isLoading = true;
			try {
				const response = await fetch('./API/fetchProductsAndFilters.php');
				const data = await response.json();

				if (!Array.isArray(data) || data.length < 5) {
					throw new Error('Unexpected response shape');
				}

				this.products = data[0].product;
				this.filters.gender = data[1].gender_filters;
				this.filters.size = data[2].size_filters;
				this.filters.type = data[3].type_filters;
				this.filters.sleeve = data[4].sleeve_filters;
				this.lastUpdated = new Date().toLocaleString();
			} catch (error) {
				this.showToast('Failed to load filter data', 'error');
				console.error(error);
			} finally {
				this.isLoading = false;
			}
		},
		async updateFilter(productId, filterType, newValue) {
			try {
				const params = new URLSearchParams({
					p: productId,
					f: filterType,
					n: newValue || '',
				});
				const response = await fetch(
					`./API/updateProductsAndFilters.php?${params.toString()}`,
				);
				const result = await response.json();

				if (result.success === false) {
					throw new Error(result.message || 'Unknown error');
				}

				this.showToast('Filter updated', 'success');
			} catch (error) {
				console.error(error);
				this.showToast('Unable to update filter', 'error');
			}
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
			}, 2000);
		},
		cycleFilter(key) {
			const options = this.filters[key].map((option) => option.filter);
			const currentIndex = options.indexOf(this.filter[key]);
			const nextIndex = (currentIndex + 1) % (options.length + 1);
			this.filter[key] = nextIndex === options.length ? '' : options[nextIndex];
		},
		resetFilters() {
			this.filter = { gender: '', size: '', type: '', sleeve: '' };
			this.search = '';
		},
	},
}).mount('#app');
