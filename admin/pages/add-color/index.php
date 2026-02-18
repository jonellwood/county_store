<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../../../components/header.php";
?>

<link rel="stylesheet" href="add-color.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div id="app">
    <div class="page-shell">
        <header class="page-header">
            <div>
                <h1>Add Color Palette</h1>
                <p>Keep product imagery and naming conventions perfectly in sync.</p>
            </div>
            <div class="status-chip" :class="{ success: savedSuccessfully, error: hasError }">
                <template v-if="savedSuccessfully">
                    <i class="fas fa-check"></i>
                    <span>{{ statusMessage || 'Color saved' }}</span>
                </template>
                <template v-else-if="hasError">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>{{ errorMessage }}</span>
                </template>
                <template v-else>
                    <i class="fas fa-layer-group"></i>
                    <span>{{ existingColors.length }} colors indexed</span>
                </template>
            </div>
        </header>

        <form @submit.prevent="saveColor" class="form-layout">
            <div class="content-grid">
                <section class="form-card">
                    <div class="card-header">
                        <div>
                            <h2><i class="fas fa-pen-nib"></i> Color Identity</h2>
                            <p class="card-description">Type the marketing-facing color label. We will surface live matches so you avoid duplicates.</p>
                        </div>
                        <span class="chip" v-if="colorNameExists">
                            <i class="fas fa-clone"></i>
                            Duplicate name
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="colorName">Color Name *</label>
                        <div class="autocomplete-wrapper">
                            <input
                                id="colorName"
                                class="form-input"
                                type="text"
                                v-model="form.color"
                                @input="handleNameInput"
                                @focus="handleNameFocus"
                                @blur="handleNameBlur"
                                @keydown="handleNameKeydown"
                                placeholder="e.g., Graphite / White"
                                autocomplete="off"
                                required>

                            <div
                                class="suggestion-list"
                                v-if="nameDropdownOpen && filteredNameSuggestions.length">
                                <div
                                    class="suggestion-item"
                                    v-for="(suggestion, index) in filteredNameSuggestions"
                                    :key="suggestion.color_id"
                                    :class="{ active: highlightedSuggestion === index }"
                                    @mousedown.prevent="applySuggestion(suggestion)">
                                    <div
                                        class="color-pill"
                                        :style="{ background: suggestion.p_hex || '#222' }"></div>
                                    <div>
                                        <strong>{{ suggestion.color }}</strong>
                                        <div class="chip" style="margin-top: 4px;">
                                            {{ [suggestion.p_hex, suggestion.s_hex, suggestion.t_hex].filter(Boolean).join(' · ') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small v-if="colorNameExists" style="color: var(--danger);">This color label already exists. Adjust the name or reuse the existing entry.</small>
                    </div>

                    <div class="preview-grid">
                        <div class="preview-tile" v-for="slot in colorPreview" :key="slot.label">
                            <span class="form-label">{{ slot.label }}</span>
                            <div class="preview-color" :style="{ background: slot.value || '#111' }"></div>
                            <small>{{ slot.value || 'Not set' }}</small>
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="card-header">
                        <div>
                            <h2><i class="fas fa-droplet"></i> Hex Channels</h2>
                            <p class="card-description">Pick the swatch or type a hex value. We enforce six-digit uppercase format.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Primary Hex *</label>
                        <div class="hex-field">
                            <input type="color" v-model="form.p_hex" @input="normalizeHex('p_hex', false)">
                            <input
                                type="text"
                                class="form-input"
                                v-model="form.p_hex"
                                @blur="normalizeHex('p_hex', false)"
                                placeholder="#000000"
                                required>
                        </div>
                    </div>

                    <div class="toggle-row">
                        <label>
                            <input type="checkbox" v-model="toggles.secondary" @change="handleToggle('s_hex')">
                            Include secondary hex (multi-color)
                        </label>
                        <span class="chip" v-if="toggles.secondary">Active</span>
                    </div>

                    <div class="form-group" v-if="toggles.secondary">
                        <label class="form-label">Secondary Hex</label>
                        <div class="hex-field">
                            <input type="color" v-model="form.s_hex" @input="normalizeHex('s_hex')">
                            <input
                                type="text"
                                class="form-input"
                                v-model="form.s_hex"
                                @blur="normalizeHex('s_hex')"
                                placeholder="#FFFFFF">
                        </div>
                    </div>

                    <div class="toggle-row">
                        <label>
                            <input type="checkbox" v-model="toggles.tertiary" @change="handleToggle('t_hex')">
                            Include tertiary accent
                        </label>
                        <span class="chip" v-if="toggles.tertiary">Active</span>
                    </div>

                    <div class="form-group" v-if="toggles.tertiary">
                        <label class="form-label">Tertiary Hex</label>
                        <div class="hex-field">
                            <input type="color" v-model="form.t_hex" @input="normalizeHex('t_hex')">
                            <input
                                type="text"
                                class="form-input"
                                v-model="form.t_hex"
                                @blur="normalizeHex('t_hex')"
                                placeholder="#CCCCCC">
                        </div>
                    </div>
                </section>

                <section class="form-card helper-card" v-if="segmentSuggestions.length">
                    <div class="card-header">
                        <div>
                            <h2><i class="fas fa-bezier-curve"></i> Consistency Helper</h2>
                            <p class="card-description">We broke apart the color name and found matching swatches so graphite always stays graphite.</p>
                        </div>
                    </div>
                    <div class="segment-grid">
                        <div class="segment-card" v-for="segment in segmentSuggestions" :key="segment.segment">
                            <span class="segment-label">{{ segment.segment }}</span>
                            <div class="swatch-row">
                                <div class="swatch-option" v-for="hex in segment.hexes" :key="hex.value">
                                    <div class="swatch-preview" :style="{ background: hex.value }"></div>
                                    <strong>{{ hex.value }}</strong>
                                    <small>Seen in {{ hex.sources.join(', ') }}</small>
                                    <div class="swatch-actions">
                                        <button type="button" @click="applySuggestedHex(hex.value, 'p_hex')">Primary</button>
                                        <button type="button" @click="applySuggestedHex(hex.value, 's_hex')">Secondary</button>
                                        <button type="button" @click="applySuggestedHex(hex.value, 't_hex')">Tertiary</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="form-card">
                    <div class="card-header">
                        <div>
                            <h2><i class="fas fa-books"></i> Existing Color Library</h2>
                            <p class="card-description">Search ~1400 swatches and copy their hex stack in one tap.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="librarySearch">Search colors</label>
                        <input
                            id="librarySearch"
                            type="text"
                            class="form-input"
                            v-model="librarySearch"
                            placeholder="Try 'Graphite', 'Heather', 'Navy'">
                    </div>
                    <div class="library-list">
                        <div class="library-item" v-for="color in libraryResults" :key="color.color_id">
                            <div class="color-pill" :style="{ background: color.p_hex || '#222' }"></div>
                            <div>
                                <strong>{{ color.color }}</strong>
                                <div class="chip" style="margin-top: 4px;">
                                    {{ [color.p_hex, color.s_hex, color.t_hex].filter(Boolean).join(' · ') || 'No hex data' }}
                                </div>
                            </div>
                            <button type="button" @click="copyHexSet(color)">
                                <i class="fas fa-copy"></i> Copy hex set
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div class="actions">
                <button type="button" class="btn btn-outline" @click="resetForm" :disabled="isLoading">
                    <i class="fas fa-rotate"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary" :disabled="!canSubmit">
                    <i class="fas" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                    {{ isLoading ? 'Saving...' : 'Save Color' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                form: {
                    color: '',
                    p_hex: '#5EE4A7',
                    s_hex: '',
                    t_hex: ''
                },
                toggles: {
                    secondary: false,
                    tertiary: false
                },
                existingColors: [],
                librarySearch: '',
                nameDropdownOpen: false,
                highlightedSuggestion: -1,
                isLoading: false,
                savedSuccessfully: false,
                hasError: false,
                statusMessage: '',
                errorMessage: ''
            };
        },
        computed: {
            normalizedName() {
                return this.form.color.trim();
            },
            colorNameExists() {
                if (!this.normalizedName) return false;
                return this.existingColors.some(color => color.color.toLowerCase() === this.normalizedName.toLowerCase());
            },
            filteredNameSuggestions() {
                if (!this.existingColors.length) return [];
                const query = this.normalizedName.toLowerCase();
                const matches = query ?
                    this.existingColors.filter(color => color.color.toLowerCase().includes(query)) :
                    this.existingColors;
                return matches.slice(0, 8);
            },
            libraryResults() {
                const query = this.librarySearch.trim().toLowerCase();
                const matches = query ?
                    this.existingColors.filter(color => color.color.toLowerCase().includes(query)) :
                    this.existingColors;
                return matches.slice(0, 14);
            },
            segmentSuggestions() {
                const rawSegments = this.extractSegments(this.normalizedName);
                if (!rawSegments.length) return [];

                return rawSegments.map(segment => {
                    const matches = this.existingColors.filter(color =>
                        color.color.toLowerCase().includes(segment.toLowerCase())
                    );
                    const hexMap = {};

                    matches.forEach(match => {
                        ['p_hex', 's_hex', 't_hex'].forEach(slot => {
                            const value = (match[slot] || '').trim();
                            if (!value) return;
                            const normalized = value.toUpperCase();
                            if (!hexMap[normalized]) {
                                hexMap[normalized] = {
                                    value: normalized,
                                    sources: []
                                };
                            }
                            if (hexMap[normalized].sources.length < 3) {
                                hexMap[normalized].sources.push(match.color);
                            }
                        });
                    });

                    return {
                        segment,
                        hexes: Object.values(hexMap).slice(0, 4)
                    };
                }).filter(entry => entry.hexes.length);
            },
            colorPreview() {
                return [{
                        label: 'Primary',
                        value: this.form.p_hex
                    },
                    {
                        label: 'Secondary',
                        value: this.toggles.secondary ? this.form.s_hex : null
                    },
                    {
                        label: 'Tertiary',
                        value: this.toggles.tertiary ? this.form.t_hex : null
                    }
                ];
            },
            canSubmit() {
                if (!this.normalizedName || !this.isValidHex(this.form.p_hex)) {
                    return false;
                }
                if (this.toggles.secondary && !this.isValidHex(this.form.s_hex)) {
                    return false;
                }
                if (this.toggles.tertiary && !this.isValidHex(this.form.t_hex)) {
                    return false;
                }
                return !this.colorNameExists && !this.isLoading;
            }
        },
        async mounted() {
            await this.loadColors();
        },
        methods: {
            async loadColors() {
                try {
                    const response = await fetch('api/get-colors.php');
                    const result = await response.json();

                    if (result.success) {
                        this.existingColors = result.colors.sort((a, b) =>
                            a.color.toLowerCase().localeCompare(b.color.toLowerCase())
                        );
                    } else {
                        this.showError(result.message || 'Unable to load colors');
                    }
                } catch (error) {
                    this.showError('Error loading colors: ' + error.message);
                }
            },
            handleNameInput() {
                this.nameDropdownOpen = true;
                this.highlightedSuggestion = 0;
            },
            handleNameFocus() {
                if (this.filteredNameSuggestions.length) {
                    this.nameDropdownOpen = true;
                }
            },
            handleNameBlur() {
                setTimeout(() => {
                    this.nameDropdownOpen = false;
                }, 150);
            },
            handleNameKeydown(event) {
                if (!this.nameDropdownOpen || !this.filteredNameSuggestions.length) return;

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.highlightedSuggestion = (this.highlightedSuggestion + 1) % this.filteredNameSuggestions.length;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.highlightedSuggestion = (this.highlightedSuggestion - 1 + this.filteredNameSuggestions.length) % this.filteredNameSuggestions.length;
                } else if (event.key === 'Enter') {
                    if (this.highlightedSuggestion >= 0) {
                        event.preventDefault();
                        const choice = this.filteredNameSuggestions[this.highlightedSuggestion];
                        if (choice) {
                            this.applySuggestion(choice);
                        }
                    }
                }
            },
            applySuggestion(color) {
                this.form.color = color.color;
                this.form.p_hex = color.p_hex || this.form.p_hex;
                if (color.s_hex) {
                    this.toggles.secondary = true;
                    this.form.s_hex = color.s_hex;
                } else {
                    this.toggles.secondary = false;
                    this.form.s_hex = '';
                }
                if (color.t_hex) {
                    this.toggles.tertiary = true;
                    this.form.t_hex = color.t_hex;
                } else {
                    this.toggles.tertiary = false;
                    this.form.t_hex = '';
                }
                this.nameDropdownOpen = false;
            },
            handleToggle(field) {
                if (field === 's_hex') {
                    if (this.toggles.secondary) {
                        if (!this.isValidHex(this.form.s_hex)) {
                            this.form.s_hex = '#FFFFFF';
                        }
                    } else {
                        this.form.s_hex = '';
                    }
                }
                if (field === 't_hex') {
                    if (this.toggles.tertiary) {
                        if (!this.isValidHex(this.form.t_hex)) {
                            this.form.t_hex = '#000000';
                        }
                    } else {
                        this.form.t_hex = '';
                    }
                }
            },
            normalizeHex(field, allowEmpty = true) {
                let value = (this.form[field] || '').trim();
                if (!value) {
                    if (allowEmpty) {
                        this.form[field] = '';
                        return;
                    }
                    this.form[field] = '#000000';
                    value = '#000000';
                }

                if (!value.startsWith('#')) {
                    value = '#' + value;
                }

                value = value.toUpperCase();

                const shortPattern = /^#([0-9A-F]{3})$/;
                const longPattern = /^#([0-9A-F]{6})$/;

                if (shortPattern.test(value)) {
                    const [, triplet] = shortPattern.exec(value);
                    value = '#' + triplet.split('').map(char => char + char).join('');
                }

                if (!longPattern.test(value)) {
                    return;
                }

                this.form[field] = value;
            },
            isValidHex(value) {
                return /^#([0-9A-F]{6})$/.test((value || '').trim().toUpperCase());
            },
            applySuggestedHex(hex, targetField) {
                if (targetField === 's_hex' && !this.toggles.secondary) {
                    this.toggles.secondary = true;
                    if (!this.form.s_hex) {
                        this.form.s_hex = '#FFFFFF';
                    }
                }
                if (targetField === 't_hex' && !this.toggles.tertiary) {
                    this.toggles.tertiary = true;
                    if (!this.form.t_hex) {
                        this.form.t_hex = '#000000';
                    }
                }
                this.form[targetField] = hex;
                this.normalizeHex(targetField, targetField !== 'p_hex');
            },
            copyHexSet(color) {
                this.form.p_hex = color.p_hex || this.form.p_hex;
                if (color.s_hex) {
                    this.toggles.secondary = true;
                    this.form.s_hex = color.s_hex;
                } else {
                    this.toggles.secondary = false;
                    this.form.s_hex = '';
                }
                if (color.t_hex) {
                    this.toggles.tertiary = true;
                    this.form.t_hex = color.t_hex;
                } else {
                    this.toggles.tertiary = false;
                    this.form.t_hex = '';
                }
                this.statusMessage = `Hex values copied from ${color.color}`;
                this.savedSuccessfully = true;
                setTimeout(() => {
                    this.savedSuccessfully = false;
                    this.statusMessage = '';
                }, 1500);
            },
            extractSegments(value) {
                if (!value) return [];
                const raw = value.split(/[\/,&+\-]/).map(part => part.trim()).filter(Boolean);
                const unique = Array.from(new Set(raw.map(segment => segment.toLowerCase())));
                return unique.filter(segment => segment.length >= 3);
            },
            buildPayload() {
                return {
                    color: this.normalizedName,
                    p_hex: this.form.p_hex,
                    s_hex: this.toggles.secondary ? this.form.s_hex : null,
                    t_hex: this.toggles.tertiary ? this.form.t_hex : null
                };
            },
            async saveColor() {
                if (!this.canSubmit) return;

                this.isLoading = true;
                this.hasError = false;
                this.savedSuccessfully = false;

                try {
                    const payload = this.buildPayload();
                    const response = await fetch('api/add-color.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();

                    if (result.success) {
                        this.savedSuccessfully = true;
                        this.statusMessage = result.message;
                        this.existingColors.unshift({
                            color_id: result.colorId,
                            color: payload.color,
                            p_hex: payload.p_hex,
                            s_hex: payload.s_hex,
                            t_hex: payload.t_hex
                        });
                        this.resetForm();
                        setTimeout(() => {
                            this.savedSuccessfully = false;
                            this.statusMessage = '';
                        }, 2500);
                    } else {
                        this.showError(result.message || 'Unable to save color');
                    }
                } catch (error) {
                    this.showError('Error saving color: ' + error.message);
                } finally {
                    this.isLoading = false;
                }
            },
            resetForm() {
                this.form = {
                    color: '',
                    p_hex: '#5EE4A7',
                    s_hex: '',
                    t_hex: ''
                };
                this.toggles.secondary = false;
                this.toggles.tertiary = false;
                this.nameDropdownOpen = false;
            },
            showError(message) {
                this.errorMessage = message;
                this.hasError = true;
                setTimeout(() => {
                    this.hasError = false;
                    this.errorMessage = '';
                }, 4000);
            }
        }
    }).mount('#app');
</script>