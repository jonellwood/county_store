function renderDepartmentTotals(data) {
	// Check if data is valid and has required properties
	if (!data || !Array.isArray(data) || data.length < 5) {
		console.warn('Invalid data provided to renderDepartmentTotals:', data);
		return;
	}

	var html = `
    <div class="totals-popover" id="totals-popover" popover>
    <button class="btn btn-outline-dark" popovertarget="totals-popover" popovertargetaction="hide">
		Close
	</button>
		<span class='table-title'>${data[4].dep_name || 'Department'} FY Totals</span>
		<div class='emp-info-holder'>
            <span> <b>Submitted: </b> ${money_format(
							data[0]?.dep_submitted || 0
						)}</span>
            <span> <b>Approved: </b> ${money_format(
							data[1]?.dep_approved || 0
						)}</span>
            <span> <b>Ordered: </b> ${money_format(
							data[2]?.dep_ordered || 0
						)}</span>
            <span> <b>Completed: </b> ${money_format(
							data[3]?.dep_completed || 0
						)}</span>
		</div>
		<div class='totals-slideout'  id='employeeTotals'>Emp Totals</div>
    </div>
        `;

	// Check if target element exists
	var target = document.getElementById('div6');
	if (!target) {
		console.warn('Target element #div6 not found for renderDepartmentTotals');
		return;
	}

	console.log('Rendering department totals to target:', target);
	target.innerHTML = html;

	// Keep the container visible but don't auto-show the popover
	target.style.display = 'block';

	// The popover will only show when user clicks the "See Totals" button
	// which triggers the popovertarget attribute on the button
}
