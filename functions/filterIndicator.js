/**
 * Filter Indicator Helper
 * Updates the filter active indicator when filters change
 */

function updateFilterIndicator() {
	const totalFilters =
		(typeof sizesToRemove !== 'undefined' ? sizesToRemove.length : 0) +
		(typeof typesToRemove !== 'undefined' ? typesToRemove.length : 0) +
		(typeof sleevesToRemove !== 'undefined' ? sleevesToRemove.length : 0);

	const indicator = document.getElementById('filter-active-indicator');
	const countEl = document.getElementById('filter-count');

	if (!indicator || !countEl) return;

	if (totalFilters > 0) {
		indicator.classList.add('active');
		countEl.textContent = totalFilters;
	} else {
		indicator.classList.remove('active');
	}
}

// Wrap the original filter functions to also update the indicator
document.addEventListener('DOMContentLoaded', function () {
	// Wait a tick for the original functions to be defined
	setTimeout(function () {
		if (typeof window.sendToSizesToRemoveArray === 'function') {
			const originalSizes = window.sendToSizesToRemoveArray;
			window.sendToSizesToRemoveArray = function (val) {
				originalSizes(val);
				updateFilterIndicator();
			};
		}

		if (typeof window.sendToTypesToRemoveArray === 'function') {
			const originalTypes = window.sendToTypesToRemoveArray;
			window.sendToTypesToRemoveArray = function (val) {
				originalTypes(val);
				updateFilterIndicator();
			};
		}

		if (typeof window.sendToSleevesToRemoveArray === 'function') {
			const originalSleeves = window.sendToSleevesToRemoveArray;
			window.sendToSleevesToRemoveArray = function (val) {
				originalSleeves(val);
				updateFilterIndicator();
			};
		}
	}, 100);
});
