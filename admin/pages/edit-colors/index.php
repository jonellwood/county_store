<?php
session_start();
if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
    header('Location: /admin/pages/401.php');
    exit;
}

include "../../../components/header.php";
?>

<link rel="stylesheet" href="edit-colors.css?v=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div id="app" class="colors-app">
    <div class="page-grid">
        <header class="page-hero">
            <div>
                <p class="eyebrow"><i class="fas fa-swatchbook"></i> Color Library</p>
                <h1>Curate & Standardize Palettes</h1>
                <p class="subtitle">Search, edit, merge, and delete color entries while keeping hex stacks perfectly aligned.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" type="button" @click="loadColors" :disabled="isLoading">
                    <i class="fas fa-rotate"></i> Refresh
                </button>
                <button class="btn btn-danger" type="button" :disabled="!selectedIds.length" @click="confirmBulkDelete">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </header>

        <section class="utility-grid">
            <div class="utility-card">
                <span class="label">Colors Indexed</span>
                <strong>{{ colors.length }}</strong>
            </div>
            <div class="utility-card">
                <span class="label">Search</span>
                <div class="search-box">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" v-model="search" placeholder="Graphite, Navy, Heather...">
                </div>
            </div>
            <div class="utility-card">
                <span class="label">Sort</span>
                <div class="sort-chips">
                    <button
                        class="chip"
                        :class="{ active: sortBy === 'name' }"
                        @click="sortBy = 'name'">
                        <i class="fas fa-font"></i> Name
                    </button>
                    <button
                        class="chip"
                        :class="{ active: sortBy === 'recent' }"
                        @click="sortBy = 'recent'">
                        <i class="fas fa-clock"></i> Recent
                    </button>
                    <button
                        class="chip"
                        :class="{ active: sortBy === 'usage' }"
                        @click="sortBy = 'usage'">
                        <i class="fas fa-layer-group"></i> Usage
                    </button>
                </div>
            </div>
            <div class="utility-card">
                <span class="label">Filters</span>
                <div class="filter-tags">
                    <button class="chip" :class="{ active: filter.multi }" @click="filter.multi = !filter.multi">
                        Multi Color
                    </button>
                    <button class="chip" :class="{ active: filter.single }" @click="filter.single = !filter.single">
                        Single Color
                    </button>
                    <button class="chip" :class="{ active: filter.hasImage }" @click="filter.hasImage = !filter.hasImage">
                        Has Image
                    </button>
                </div>
            </div>
        </section>

        <section class="color-table" v-if="filteredColors.length">
            <div class="table-head">
                <div>
                    <input type="checkbox" @change="toggleAll($event.target.checked)">
                </div>
                <div>Name</div>
                <div>Primary</div>
                <div>Secondary</div>
                <div>Tertiary</div>
                <div>Usage</div>
                <div>Actions</div>
            </div>
            <div class="table-body">
                <article class="color-row" v-for="color in filteredColors" :key="color.color_id">
                    <div>
                        <input type="checkbox" :value="color.color_id" v-model="selectedIds">
                    </div>
                    <div class="color-name">
                        <strong>{{ color.color }}</strong>
                        <small>ID: {{ color.color_id }}</small>
                    </div>
                    <color-chip :hex="color.p_hex" label="Primary"></color-chip>
                    <color-chip :hex="color.s_hex" label="Secondary"></color-chip>
                    <color-chip :hex="color.t_hex" label="Tertiary"></color-chip>
                    <div class="usage">
                        <span>{{ color.usage || 0 }}</span>
                        <small> products</small>
                    </div>
                    <div class="actions">
                        <button class="btn btn-mini" @click="openEditor(color)">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                        <button class="btn btn-mini danger" @click="confirmDelete(color)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </article>
            </div>
        </section>
        <section v-else class="empty-state">
            <i class="fas fa-sad-tear"></i>
            <p>No colors match your filters.</p>
        </section>
    </div>

    <div class="drawer" :class="{ open: drawer.open }">
        <div class="drawer-header">
            <h2>{{ drawer.color?.color || 'Edit Color' }}</h2>
            <button class="btn btn-icon" @click="closeDrawer"><i class="fas fa-xmark"></i></button>
        </div>
        <form class="drawer-body" @submit.prevent="saveColor">
            <label>
                Name
                <input type="text" v-model="drawer.form.color" required>
            </label>
            <label>
                Primary Hex
                <input type="text" v-model="drawer.form.p_hex" @blur="normalizeHex('p_hex')" required>
            </label>
            <label>
                Secondary Hex
                <input type="text" v-model="drawer.form.s_hex" @blur="normalizeHex('s_hex')">
            </label>
            <label>
                Tertiary Hex
                <input type="text" v-model="drawer.form.t_hex" @blur="normalizeHex('t_hex')">
            </label>
            <div class="drawer-actions">
                <button class="btn btn-outline" type="button" @click="closeDrawer">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>

    <div class="toast" :class="{ show: toast.visible, [toast.type]: true }">
        <i :class="toast.icon"></i>
        <span>{{ toast.message }}</span>
    </div>
</div>

<template id="color-chip">
    <div class="color-chip">
        <div class="swatch" :style="{ background: hex || 'transparent' }"></div>
        <div>
            <span>{{ label }}</span>
            <strong>{{ hex || 'N/A' }}</strong>
        </div>
    </div>
</template>

<!-- <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script> -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="edit-colors.js?v=1"></script>