function renderEmployeeTotals(data, target) {
	console.log('data in employee totals: ');
	console.log(data);
	var html = `
		<span class='table-title'>${data[8].empName} Clothing FY Totals</span>
		 <div class='emp-info-holder'>
			<span> <b>Submitted:</b>
			${money_format(data[0].emp_submitted)}
			</span>
		
			<span> <b>Approved:</b>
			${money_format(data[1].emp_approved)}
			</span>
		
			<span> <b>Ordered:</b>
			${money_format(data[2].emp_ordered)}
			</span>
		
			<span> <b>Completed:</b>
			${money_format(data[3].emp_completed)}
			</span>
		 </div>
		
			<span class='table-title'>
			    ${data[8].empName} Boots FY Totals
            </span>
		 <div class='emp-info-holder'>
			<span> <b>Submitted:</b>
			${money_format(data[4].emp_boots_submitted)}
			</span>
		
			<span> <b>Approved:</b>
			${money_format(data[5].emp_boots_approved)}
			</span>
		
			<span> <b>Ordered:</b>
			${money_format(data[6].emp_boots_ordered)}
			</span>
		
			<span> <b>Completed:</b>
			${money_format(data[7].emp_boots_completed)}
			</span>
		 </div>
			
		 </div>`;
	document.getElementById(target).innerHTML = html;
}

// <p class='receipt'>FY Start:
// 			${extractDateFromDB(data[9].fy_start)}
// 			</p>
//             <p class="receipt">'FY End:
// 			${extractDateFromDB(data[10].fy_end)}
// 			</p>
