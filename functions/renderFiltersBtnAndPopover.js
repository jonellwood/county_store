function renderFiltersBtnAndPopover() {
	var html = `
<div class="container">
    <div class="btn-container" id="btn-container">
        <button class="btn js-toggle-filter" popovertarget="filters-popover">filter <code>products</code></button>    
            <div id="filters-popover" class="filters-popover" popover>
                <button popovertarget="filters-popover" popovertargetaction="hide" class="btn-close ms-2 mb-1" role="button">
                <span aria-hidden="true"></span>
                </button>
            <div class="popover-description">
                <span>Uncheck items you don't want to see in results</span>
            </div>
            
    <button class="button" onclick="resetFilters()">Reset Filters</button>
</div>`;
	document.getElementById('filter-target').innerHTML = html;
	getFilterData();
}
