<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../../../components/header.php";
?>

<link rel="stylesheet" href="add-product.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div id="app">
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <!-- <a href="../edit-products/" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Products
                    </a> -->
                    <h1 class="page-title">Add New Product</h1>
                </div>
                <div class="header-right">
                    <div class="status-indicator" :class="{ 'success': savedSuccessfully, 'error': hasError }">
                        <span v-if="savedSuccessfully"><i class="fas fa-check"></i> Product Saved</span>
                        <span v-else-if="hasError"><i class="fas fa-exclamation-triangle"></i> {{ errorMessage }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <form @submit.prevent="saveProduct" class="product-form">

                <!-- Basic Information Card -->
                <div class="form-card">
                    <div class="card-header">
                        <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="code">Product Code *</label>
                                <input
                                    type="text"
                                    id="code"
                                    v-model="product.code"
                                    required
                                    class="form-input"
                                    placeholder="Enter product code">
                            </div>
                            <div class="form-group">
                                <label for="productType">Product Type *</label>
                                <select
                                    id="productType"
                                    v-model="product.productType"
                                    required
                                    class="form-select">
                                    <option value="">Select product type</option>
                                    <option
                                        v-for="type in productTypes"
                                        :key="type.productType_id"
                                        :value="type.productType_id">
                                        {{ type.productType }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input
                                type="text"
                                id="name"
                                v-model="product.name"
                                required
                                class="form-input"
                                placeholder="Enter product name">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea
                                id="description"
                                v-model="product.description"
                                class="form-textarea"
                                placeholder="Enter product description"
                                rows="3"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="image">Image Path</label>
                                <input
                                    type="text"
                                    id="image"
                                    v-model="product.image"
                                    class="form-input"
                                    placeholder="path/to/image.jpg">
                            </div>
                            <div class="form-group">
                                <label for="vendor">Vendor</label>
                                <select
                                    id="vendor"
                                    v-model="product.vendorId"
                                    class="form-select">
                                    <option v-for="n in 5" :key="n" :value="n">Vendor {{ n }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Filters Card -->
                <div class="form-card">
                    <div class="card-header">
                        <h2><i class="fas fa-filter"></i> Product Filters</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="genderFilter">Gender Filter</label>
                                <select
                                    id="genderFilter"
                                    v-model="filters.gender"
                                    class="form-select">
                                    <option value="">Select gender</option>
                                    <option
                                        v-for="gender in filterOptions.gender"
                                        :key="gender.id"
                                        :value="gender.id">
                                        {{ gender.filter }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="typeFilter">Type Filter</label>
                                <select
                                    id="typeFilter"
                                    v-model="filters.type"
                                    class="form-select">
                                    <option value="">Select type</option>
                                    <option
                                        v-for="type in filterOptions.type"
                                        :key="type.id"
                                        :value="type.id">
                                        {{ type.filter }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sizeFilter">Size Filter</label>
                                <select
                                    id="sizeFilter"
                                    v-model="filters.size"
                                    class="form-select">
                                    <option value="">Select size</option>
                                    <option
                                        v-for="size in filterOptions.size"
                                        :key="size.id"
                                        :value="size.id">
                                        {{ size.filter }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sleeveFilter">Sleeve Filter</label>
                                <select
                                    id="sleeveFilter"
                                    v-model="filters.sleeve"
                                    class="form-select">
                                    <option value="">Select sleeve</option>
                                    <option
                                        v-for="sleeve in filterOptions.sleeve"
                                        :key="sleeve.id"
                                        :value="sleeve.id">
                                        {{ sleeve.filter }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colors Card -->
                <div class="form-card">
                    <div class="card-header">
                        <h2><i class="fas fa-palette"></i> Available Colors</h2>
                        <button
                            type="button"
                            @click="addColor"
                            class="add-btn">
                            <i class="fas fa-plus"></i> Add Color
                        </button>
                    </div>
                    <div class="card-body">
                        <div v-if="selectedColors.length === 0" class="empty-state">
                            <i class="fas fa-palette"></i>
                            <p>No colors selected. Click "Add Color" to get started.</p>
                        </div>
                        <div v-else class="colors-grid">
                            <div
                                v-for="(color, index) in selectedColors"
                                :key="index"
                                class="color-item">
                                <div
                                    class="color-preview"
                                    :style="{ backgroundColor: color.hex }"></div>

                                <!-- Enhanced Color Selector with Search -->
                                <div class="color-selector-wrapper">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-search search-icon"></i>
                                        <input
                                            type="text"
                                            v-model="color.searchQuery"
                                            @input="updateColorSearch(index)"
                                            @focus="openColorDropdown(index)"
                                            @blur="handleColorBlur(index)"
                                            @keydown="handleKeydown(index, $event)"
                                            :placeholder="color.selectedColorName || 'Search colors...'"
                                            class="color-search-input"
                                            autocomplete="off"
                                            required>
                                        <i
                                            class="fas fa-chevron-down dropdown-arrow"
                                            :class="{ 'rotated': color.isDropdownOpen }"
                                            @click="toggleColorDropdown(index)"></i>
                                    </div>

                                    <div
                                        v-if="color.isDropdownOpen && color.filteredColors.length > 0"
                                        class="color-dropdown"
                                        @mousedown.prevent>
                                        <div
                                            v-for="(availableColor, colorIndex) in color.filteredColors"
                                            :key="availableColor.color_id"
                                            @click="selectColor(index, availableColor)"
                                            class="color-option"
                                            :class="{ 
                                                    'disabled': isColorAlreadySelected(availableColor.color_id, index),
                                                    'highlighted': color.highlightedIndex === colorIndex
                                                }">
                                            <div
                                                class="color-option-preview"
                                                :style="{ backgroundColor: availableColor.p_hex }"></div>
                                            <span class="color-option-name">{{ availableColor.color }}</span>
                                            <span v-if="isColorAlreadySelected(availableColor.color_id, index)" class="already-selected">
                                                Already Selected
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        v-if="color.isDropdownOpen && color.filteredColors.length === 0 && color.searchQuery"
                                        class="no-results">
                                        <i class="fas fa-search"></i>
                                        No colors found for "{{ color.searchQuery }}"
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="removeColor(index)"
                                    class="remove-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sizes and Pricing Card -->
                <div class="form-card">
                    <div class="card-header">
                        <h2><i class="fas fa-ruler"></i> Sizes & Pricing</h2>
                        <button
                            type="button"
                            @click="addSize"
                            class="add-btn">
                            <i class="fas fa-plus"></i> Add Size
                        </button>
                    </div>
                    <div class="card-body">
                        <div v-if="selectedSizes.length === 0" class="empty-state">
                            <i class="fas fa-ruler"></i>
                            <p>No sizes selected. Click "Add Size" to get started.</p>
                        </div>
                        <div v-else class="sizes-grid">
                            <div
                                v-for="(size, index) in selectedSizes"
                                :key="index"
                                class="size-item">
                                <div class="size-details">
                                    <select
                                        v-model="size.sizeId"
                                        class="size-select"
                                        required>
                                        <option value="">Select size</option>
                                        <option
                                            v-for="availableSize in availableSizes"
                                            :key="availableSize.size_id"
                                            :value="availableSize.size_id">
                                            {{ availableSize.size_name }}
                                        </option>
                                    </select>
                                    <div class="price-input-group">
                                        <span class="currency">$</span>
                                        <input
                                            type="number"
                                            v-model="size.price"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="price-input"
                                            required>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeSize(index)"
                                    class="remove-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="form-actions">
                    <button
                        type="button"
                        @click="resetForm"
                        class="btn btn-secondary"
                        :disabled="isLoading">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="isLoading || !isFormValid">
                        <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
                        <i class="fas fa-save" v-else></i>
                        {{ isLoading ? 'Saving...' : 'Save Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vue 3 CDN -->
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                product: {
                    code: '',
                    name: '',
                    description: '',
                    image: '',
                    productType: '',
                    vendorId: 1
                },
                filters: {
                    gender: '',
                    type: '',
                    size: '',
                    sleeve: ''
                },
                selectedColors: [],
                selectedSizes: [],
                availableColors: [],
                availableSizes: [],
                filterOptions: {
                    gender: [],
                    type: [],
                    size: [],
                    sleeve: []
                },
                productTypes: [],
                isLoading: false,
                savedSuccessfully: false,
                hasError: false,
                errorMessage: ''
            }
        },
        computed: {
            isFormValid() {
                return this.product.code &&
                    this.product.name &&
                    this.product.productType &&
                    this.selectedColors.length > 0 &&
                    this.selectedSizes.length > 0 &&
                    this.selectedColors.every(c => c.colorId) &&
                    this.selectedSizes.every(s => s.sizeId && s.price);
            }
        },
        async mounted() {
            await this.loadOptions();
        },
        methods: {
            async loadOptions() {
                try {
                    const response = await fetch('api/get-options.php');
                    const data = await response.json();

                    if (data.success) {
                        // Sort colors alphabetically by color name
                        this.availableColors = data.colors.sort((a, b) =>
                            a.color.toLowerCase().localeCompare(b.color.toLowerCase())
                        );
                        this.availableSizes = data.sizes;
                        this.productTypes = data.productTypes;
                        const sortByFilter = (a, b) => a.filter.toLowerCase().localeCompare(b.filter.toLowerCase());
                        this.filterOptions.gender = (data.genderFilters || []).sort(sortByFilter);
                        this.filterOptions.type = (data.typeFilters || []).sort(sortByFilter);
                        this.filterOptions.size = (data.sizeFilters || []).sort(sortByFilter);
                        this.filterOptions.sleeve = (data.sleeveFilters || []).sort(sortByFilter);
                    } else {
                        this.showError('Failed to load options');
                    }
                } catch (error) {
                    this.showError('Error loading options: ' + error.message);
                }
            },

            addColor() {
                this.selectedColors.push({
                    colorId: '',
                    hex: '#000000',
                    searchQuery: '',
                    selectedColorName: '',
                    isDropdownOpen: false,
                    filteredColors: [...this.availableColors],
                    highlightedIndex: -1
                });
            },

            removeColor(index) {
                this.selectedColors.splice(index, 1);
            },

            updateColorSearch(index) {
                const color = this.selectedColors[index];
                const query = color.searchQuery.toLowerCase().trim();

                if (query === '') {
                    color.filteredColors = [...this.availableColors];
                } else {
                    color.filteredColors = this.availableColors.filter(c =>
                        c.color.toLowerCase().includes(query)
                    );
                }

                color.highlightedIndex = -1;

                // Always ensure dropdown is open when typing
                if (!color.isDropdownOpen) {
                    color.isDropdownOpen = true;
                }
            },

            openColorDropdown(index) {
                const color = this.selectedColors[index];
                color.isDropdownOpen = true;

                // If no search query, show all colors
                if (!color.searchQuery.trim()) {
                    color.filteredColors = [...this.availableColors];
                }
            },

            toggleColorDropdown(index) {
                const color = this.selectedColors[index];
                color.isDropdownOpen = !color.isDropdownOpen;

                if (color.isDropdownOpen && !color.searchQuery.trim()) {
                    color.filteredColors = [...this.availableColors];
                }
            },

            handleKeydown(index, event) {
                const color = this.selectedColors[index];

                if (!color.isDropdownOpen) return;

                switch (event.key) {
                    case 'ArrowDown':
                        event.preventDefault();
                        color.highlightedIndex = Math.min(color.highlightedIndex + 1, color.filteredColors.length - 1);
                        break;
                    case 'ArrowUp':
                        event.preventDefault();
                        color.highlightedIndex = Math.max(color.highlightedIndex - 1, -1);
                        break;
                    case 'Enter':
                        event.preventDefault();
                        if (color.highlightedIndex >= 0 && color.filteredColors[color.highlightedIndex]) {
                            this.selectColor(index, color.filteredColors[color.highlightedIndex]);
                        }
                        break;
                    case 'Escape':
                        event.preventDefault();
                        color.isDropdownOpen = false;
                        break;
                    case 'Tab':
                        // Let tab work normally, but close dropdown
                        color.isDropdownOpen = false;
                        break;
                }
            },

            selectColor(index, selectedColor) {
                // Check if color is already selected
                if (this.isColorAlreadySelected(selectedColor.color_id, index)) {
                    this.showError(`Color "${selectedColor.color}" is already selected`);
                    return;
                }

                const color = this.selectedColors[index];
                color.colorId = selectedColor.color_id;
                color.hex = selectedColor.p_hex || '#000000';
                color.selectedColorName = selectedColor.color;
                color.searchQuery = selectedColor.color;
                color.isDropdownOpen = false;
                color.highlightedIndex = -1;
            },

            isColorAlreadySelected(colorId, currentIndex) {
                return this.selectedColors.some((color, index) =>
                    index !== currentIndex && color.colorId === colorId
                );
            },

            handleColorBlur(index) {
                // Increased delay to allow for click events on dropdown items
                setTimeout(() => {
                    if (this.selectedColors[index]) {
                        this.selectedColors[index].isDropdownOpen = false;
                    }
                }, 300);
            },

            addSize() {
                this.selectedSizes.push({
                    sizeId: '',
                    price: ''
                });
            },

            removeSize(index) {
                this.selectedSizes.splice(index, 1);
            },

            async saveProduct() {
                if (!this.isFormValid) return;

                this.isLoading = true;
                this.hasError = false;
                this.savedSuccessfully = false;

                try {
                    const formData = {
                        product: this.product,
                        colors: this.selectedColors.map(c => c.colorId),
                        sizes: this.selectedSizes.map(s => ({
                            sizeId: s.sizeId,
                            price: parseFloat(s.price)
                        })),
                        filters: this.filters
                    };

                    const response = await fetch('api/add-product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.savedSuccessfully = true;
                        setTimeout(() => {
                            this.resetForm();
                            this.savedSuccessfully = false;
                        }, 3000);
                    } else {
                        this.showError(result.message || 'Failed to save product');
                    }
                } catch (error) {
                    this.showError('Error saving product: ' + error.message);
                } finally {
                    this.isLoading = false;
                }
            },

            resetForm() {
                this.product = {
                    code: '',
                    name: '',
                    description: '',
                    image: '',
                    productType: '',
                    vendorId: 1
                };
                this.filters = {
                    gender: '',
                    type: '',
                    size: '',
                    sleeve: ''
                };
                this.selectedColors = [];
                this.selectedSizes = [];
                this.hasError = false;
                this.savedSuccessfully = false;
            },

            showError(message) {
                this.hasError = true;
                this.errorMessage = message;
                setTimeout(() => {
                    this.hasError = false;
                }, 5000);
            }
        }
    }).mount('#app');
</script>