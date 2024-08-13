function getFilterData() {
	fetch('fetchFilters.php')
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
			// console.log(data[3].type_filters[0].filter);
			var sizes = data[1].size_filters;
			var types = data[3].type_filters;
			var sleeves = data[2].sleeve_filters;
			var html = '';
			html += `<div class='filters-holder'>`;
			html += `<div class="filter-group" id="size-filter-group">`;
			html += `<span>Size Filters:</span>`;
			for (var i = 0; i < sizes.length; i++) {
				var prefix = 's';
				html += `<label for="${prefix + sizes[i].id}">${sizes[i].filter}`;
				html += `<input type="checkbox" name="${sizes[i].filter}" value="${
					sizes[i].id
				}" id="${
					prefix + sizes[i].id
				}" checked onchange="sendToSizesToRemoveArray(this.value)"/>`;
				html += `</label>`;
			}
			html += `</div>`;
			html += `<div class="filter-group" id="type-filter-group">`;
			html += `<span>Type Filters:</span>`;
			for (var j = 0; j < types.length; j++) {
				var prefix = 't';
				html += `<label for="${prefix + types[j].id}">${types[j].filter}`;
				html += `<input type="checkbox" name="${types[j].filter}" value="${
					types[j].id
				}" id="${
					prefix + types[j].id
				}" checked onchange="sendToTypesToRemoveArray(this.value)"/>`;
				html += `</label>`;
			}
			html += `</div>`;
			html += `<div class="filter-group" id="sleeve-filter-group">`;
			html += `<span>Sleeve Filters:</span>`;
			for (var k = 0; k < sleeves.length; k++) {
				var prefix = 'sl';
				html += `<label for="${prefix + sleeves[k].id}">${sleeves[k].filter}`;
				html += `<input type="checkbox" name="${sleeves[k].filter}" value="${
					sleeves[k].id
				}" id="${
					prefix + sleeves[k].id
				}" checked onchange="sendToSleevesToRemoveArray(this.value)"/>`;
				html += `</label>`;
			}
			html += `</div>`;
			html += `</div>`;
			document.getElementById('filters').innerHTML = html;
		});
}
