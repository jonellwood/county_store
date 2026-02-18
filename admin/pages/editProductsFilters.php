<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}

include "../../components/header.php";
?>

<link rel="stylesheet" href="edit-product-filters.css?v=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div id="app" class="filters-app">
    <div class="filters-layout">
        <header class="filters-header">
            <div>
                <p class="eyebrow"><i class="fas fa-filter"></i> Product Filters</p>
                <h1>Normalize Catalog Filters</h1>
                <p class="subtitle">This grid syncs gender, size, type, and sleeve tags across the entire product library.
                </p>
            </div>
            <div class="header-actions">
                <div class="status-chip" v-if="toast.visible" :class="toast.type">
                    <i :class="toast.icon"></i>
                    <span>{{ toast.message }}</span>
                </div>
                <button class="btn btn-outline" type="button" @click="loadProducts" :disabled="isLoading">
                    <i class="fas fa-rotate"></i>
                    Refresh Data
                </button>
            </div>
        </header>

        <section class="filters-meta">
            <div class="meta-card">
                <span class="meta-label">Products</span>
                <strong>{{ products.length }}</strong>
            </div>
            <div class="meta-card">
                <span class="meta-label">Last Sync</span>
                <strong>{{ lastUpdated }}</strong>
            </div>
            <div class="meta-card">
                <span class="meta-label">Search</span>
                <div class="search-box">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" v-model="search" placeholder="Search code, name, or tag">
                </div>
            </div>
            <div class="meta-card">
                <span class="meta-label">Filter</span>
                <div class="chip-grid">
                    <button
                        class="chip"
                        :class="{ active: !!filter.gender }"
                        @click="cycleFilter('gender')">
                        Gender
                        <span>{{ filter.gender || 'All' }}</span>
                    </button>
                    <button
                        class="chip"
                        :class="{ active: !!filter.size }"
                        @click="cycleFilter('size')">
                        Size
                        <span>{{ filter.size || 'All' }}</span>
                    </button>
                    <button
                        class="chip"
                        :class="{ active: !!filter.type }"
                        @click="cycleFilter('type')">
                        Type
                        <span>{{ filter.type || 'All' }}</span>
                    </button>
                    <button
                        class="chip"
                        :class="{ active: !!filter.sleeve }"
                        @click="cycleFilter('sleeve')">
                        Sleeve
                        <span>{{ filter.sleeve || 'All' }}</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="filters-table" v-if="filteredProducts.length">
            <div class="table-head">
                <div>ID</div>
                <div>Code</div>
                <div>Name</div>
                <div>Gender</div>
                <div>Size</div>
                <div>Type</div>
                <div>Sleeve</div>
            </div>
            <div class="table-body">
                <article class="table-row" v-for="product in filteredProducts" :key="product.product_id">
                    <div class="cell id">{{ product.product_id }}</div>
                    <div class="cell code">{{ product.code }}</div>
                    <div class="cell name">
                        <strong>{{ product.name }}</strong>
                        <small>{{ product.description }}</small>
                    </div>
                    <div class="cell picker">
                        <filter-select
                            label="Gender"
                            :value="product.gender"
                            :options="filters.gender"
                            @change="(val) => updateFilter(product.product_id, '1', val)" />
                    </div>
                    <div class="cell picker">
                        <filter-select
                            label="Size"
                            :value="product.size"
                            :options="filters.size"
                            @change="(val) => updateFilter(product.product_id, '2', val)" />
                    </div>
                    <div class="cell picker">
                        <filter-select
                            label="Type"
                            :value="product.type"
                            :options="filters.type"
                            @change="(val) => updateFilter(product.product_id, '3', val)" />
                    </div>
                    <div class="cell picker">
                        <filter-select
                            label="Sleeve"
                            :value="product.sleeve"
                            :options="filters.sleeve"
                            @change="(val) => updateFilter(product.product_id, '4', val)" />
                    </div>
                </article>
            </div>
        </section>

        <section v-else class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>No products match your filters yet.</p>
            <button class="btn btn-outline" type="button" @click="resetFilters">Reset Filters</button>
        </section>
    </div>
</div>

<template id="filter-select">
    <div class="select-field">
        <label>{{ label }}</label>
        <div class="select-wrapper">
            <select :value="value" @change="$emit('change', $event.target.value)">
                <option value="">N/A</option>
                <option v-for="option in options" :value="option.filter" :key="option.id">
                    {{ option.filter }}
                </option>
            </select>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</template>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="edit-product-filters.js?v=1"></script>