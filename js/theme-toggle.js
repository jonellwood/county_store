/**
 * Theme Toggle System
 * Berkeley County Store - Dark Mode Support
 * Created: October 2025
 */

(function () {
	'use strict';

	const STORAGE_KEY = 'bc-store-theme';
	const THEME_LIGHT = 'light';
	const THEME_DARK = 'dark';

	/**
	 * Get user's theme preference
	 * Priority: localStorage > system preference > default (light)
	 */
	function getThemePreference() {
		// Check localStorage first
		const stored = localStorage.getItem(STORAGE_KEY);
		console.log('Reading from localStorage:', stored);
		if (stored) {
			return stored;
		}

		// Check system preference
		if (
			window.matchMedia &&
			window.matchMedia('(prefers-color-scheme: dark)').matches
		) {
			console.log('Using system preference: dark');
			return THEME_DARK;
		}

		// Default to light
		console.log('Using default: light');
		return THEME_LIGHT;
	}

	/**
	 * Apply theme to document
	 */
	function applyTheme(theme) {
		const html = document.documentElement;
		const body = document.body;

		console.log('=== APPLY THEME ===');
		console.log('Theme to apply:', theme);
		console.log('Before - HTML data-theme:', html.getAttribute('data-theme'));
		console.log(
			'Before - HTML data-bs-theme:',
			html.getAttribute('data-bs-theme')
		);

		if (theme === THEME_DARK) {
			html.setAttribute('data-theme', 'dark');
			html.setAttribute('data-bs-theme', 'dark');
			if (body) body.setAttribute('data-bs-theme', 'dark');
		} else {
			html.removeAttribute('data-theme');
			html.setAttribute('data-bs-theme', 'light');
			if (body) body.setAttribute('data-bs-theme', 'light');
		}

		console.log('After - HTML data-theme:', html.getAttribute('data-theme'));
		console.log(
			'After - HTML data-bs-theme:',
			html.getAttribute('data-bs-theme')
		);

		// Store preference
		localStorage.setItem(STORAGE_KEY, theme);
		console.log('Saved to localStorage:', theme);
		console.log('Verify localStorage:', localStorage.getItem(STORAGE_KEY));

		// Update toggle button state if it exists
		updateToggleButton(theme);
		console.log('===================');
	}

	/**
	 * Toggle between themes
	 */
	function toggleTheme() {
		const html = document.documentElement;
		const current = html.hasAttribute('data-theme') ? THEME_DARK : THEME_LIGHT;
		const next = current === THEME_LIGHT ? THEME_DARK : THEME_LIGHT;
		console.log('Toggling from', current, 'to', next);
		applyTheme(next);
	}

	/**
	 * Update toggle button appearance
	 */
	function updateToggleButton(theme) {
		const toggleBtn = document.getElementById('theme-toggle');
		if (!toggleBtn) return;

		const icon = toggleBtn.querySelector('.theme-icon');
		const text = toggleBtn.querySelector('.theme-text');

		if (theme === THEME_DARK) {
			toggleBtn.setAttribute('aria-label', 'Switch to light mode');
			toggleBtn.setAttribute('title', 'Switch to light mode');
			if (icon) icon.textContent = '☀️';
			if (text) text.textContent = 'Light Mode';
		} else {
			toggleBtn.setAttribute('aria-label', 'Switch to dark mode');
			toggleBtn.setAttribute('title', 'Switch to dark mode');
			if (icon) icon.textContent = '🌙';
			if (text) text.textContent = 'Dark Mode';
		}
	}

	/**
	 * Initialize theme on page load
	 */
	function initTheme() {
		console.log('=== INIT THEME ===');
		const theme = getThemePreference();
		console.log('Theme preference:', theme);
		applyTheme(theme);
	}

	/**
	 * Listen for system theme changes
	 */
	function watchSystemTheme() {
		if (!window.matchMedia) return;

		const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');

		darkModeQuery.addEventListener('change', (e) => {
			// Only auto-switch if user hasn't manually set a preference
			const stored = localStorage.getItem(STORAGE_KEY);
			if (!stored) {
				applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
			}
		});
	}

	/**
	 * Setup event listeners
	 */
	function setupListeners() {
		// Toggle button click
		const toggleBtn = document.getElementById('theme-toggle');
		if (toggleBtn) {
			console.log('Theme toggle button found, attaching listener');
			toggleBtn.addEventListener('click', toggleTheme);
		} else {
			console.warn('Theme toggle button not found!');
		}

		// Keyboard shortcut: Alt + T
		document.addEventListener('keydown', (e) => {
			if (e.altKey && e.key === 't') {
				e.preventDefault();
				toggleTheme();
			}
		});
	}

	// Initialize immediately (before DOM ready) to prevent flash
	initTheme();

	// Setup listeners when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => {
			setupListeners();
			watchSystemTheme();
		});
	} else {
		setupListeners();
		watchSystemTheme();
	}

	// Expose API for manual control
	window.BCTheme = {
		toggle: toggleTheme,
		set: applyTheme,
		get: () =>
			document.documentElement.hasAttribute('data-theme')
				? THEME_DARK
				: THEME_LIGHT,
	};
})();
