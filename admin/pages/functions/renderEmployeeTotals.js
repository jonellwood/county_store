function renderEmployeeTotals(data, target) {
	console.log('data in employee totals: ');
	console.log(data);

	// Check if data is valid and has required properties
	if (!data || !Array.isArray(data) || data.length < 9) {
		console.warn('Invalid data provided to renderEmployeeTotals:', data);
		return;
	}

	// Check if target element exists
	const targetElement = document.getElementById(target);
	if (!targetElement) {
		console.warn(
			`Target element #${target} not found for renderEmployeeTotals`
		);
		return;
	}

	var html = `
		<span class='table-title'>${
			data[8]?.empName || 'Employee'
		} Clothing FY Totals</span>
		 <div class='emp-info-holder'>
			<span> <b class='heading'>Submitted:</b>
			${money_format(data[0]?.emp_submitted || 0)}
			</span>
		
			<span> <b>Approved:</b>
			${money_format(data[1]?.emp_approved || 0)}
			</span>
		
			<span> <b>Ordered:</b>
			${money_format(data[2]?.emp_ordered || 0)}
			</span>
		
			<span> <b>Completed:</b>
			${money_format(data[3]?.emp_completed || 0)}
			</span>
		 </div>
		
			<span class='table-title'>
			    ${data[8]?.empName || 'Employee'} Boots FY Totals
            </span>
		 <div class='emp-info-holder'>
			<span> <b>Submitted:</b>
			${money_format(data[4]?.emp_boots_submitted || 0)}
			</span>
		
			<span> <b>Approved:</b>
			${money_format(data[5]?.emp_boots_approved || 0)}
			</span>
		
			<span> <b>Ordered:</b>
			${money_format(data[6]?.emp_boots_ordered || 0)}
			</span>
		
			<span> <b>Completed:</b>
			${money_format(data[7]?.emp_boots_completed || 0)}
			</span>
		 </div>
			
		 </div>`;

	console.log('Rendering employee totals to target:', targetElement);
	targetElement.innerHTML = html;
}

// <p class='receipt'>FY Start:
// 			${extractDateFromDB(data[9].fy_start)}
// 			</p>
//             <p class="receipt">'FY End:
// 			${extractDateFromDB(data[10].fy_end)}
// 			</p>
