function firstChar(str) {
	return str[0];
}
function renderRequestList(data) {
	let html = '';
	// console.log('First data')
	console.log(data);
	if (data.length == 0) {
		alert('No Requests Found');
	} else {
		html = `
			<div class='main-list-holder' id='main-list-holder'>
                    <table class='table table-striped' id='data-table'>
                        <thead>
			                <tr>
                                <th class='thead-dark'>ID</th>
                                <th class='thead-dark'>Total</th>
                                <th class='thead-dark'>Created</th>
                                <th class='thead-dark'>Requested For</th>
			
                            </tr>
                        </thead>
                        <tbody>
            `;
                for (var i = 0; i < data.length; i++) {
                    html +=`
                        <tr value=${data[i].order_id ? data[i].order_id : ''} onclick=getOrderDetails(${data[i].order_id ? data[i].order_id : ''}) data-currentfy=${data[i].bill_to_fy ? isThisFiscalYear(data[i].bill_to_fy) : ''}>
                    
                    <td> ${data[i].order_id ? data[i].order_id : ''}</td>
                    <td> ${data[i].grand_total ? money_format(data[i].grand_total) : ''} </td>
                    <td> ${data[i].created ? extractDate(data[i].created) : ''} </td>
                    <td> ${data[i].requested_for ? data[i].requested_for : ''} </td>
                    
                    </tr>
                    
                    `
                }
                html += '</tbody>';
                html += '</table>';
                html += '</div>';
                document.getElementById('main').innerHTML = html;
		// clickFirst();
	}
    // return html;
}
