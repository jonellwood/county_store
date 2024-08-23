function renderDepartmentTotals(data) {
	var html = `
    <div class="totals-popover" id="totals-popover" popover>
    <button class="btn btn-outline-dark" popovertarget="totals-popover" popovertargetaction="hide">
		Close
	</button>
		<span class='table-title'>${data[4].dep_name} FY Totals</span>
		<div class='emp-info-holder'>
            <span> <b>Submitted: </b> ${money_format(
							data[0].dep_submitted
						)}</span>
            <span> <b>Approved: </b> ${money_format(
							data[1].dep_approved
						)}</span>
            <span> <b>Ordered: </b> ${money_format(data[2].dep_ordered)}</span>
            <span> <b>Completed: </b> ${money_format(
							data[3].dep_completed
						)}</span>
		</div>
		<div class='totals-slideout'  id='employeeTotals'>Emp Totals</div>
    </div>
        `;
	var target = document.getElementById('div6');
	console.log(target);
	document.getElementById('div6').innerHTML = html;
}
