function renderOrderCostSummary(order) {
	let totalQty = 0;
	let totalPrice = 0;
	let totalLogoFee = 0;
	let totalTax = 0;
	let grandTotal = 0;
	for (var i = 0; i < order.length; i++) {
		totalQty += order[i].quantity;
		totalPrice += parseFloat(order[i].pre_tax_price);
		totalLogoFee += order[i].logo_fee;
		totalTax += order[i].tax;
	}
	console.log(totalQty, totalPrice, totalLogoFee, totalTax);
	var html = `

        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center">Total Items</th>
                    <th class="text-center">Total Price</th>
                    <th class="text-center">Total Logo Fees</th>
                    <th class="text-center">Total Taxes</th>
                    <th class="text-center">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">${totalQty ? totalQty : '0'}</td>
                    <td class="text-center">${totalPrice ? formatAsCurrency(totalPrice) : '0'}</td>
                    <td class="text-center">${totalLogoFee ? formatAsCurrency(totalLogoFee) : '0' }</td>
                    <td class="text-center">${totalTax ? formatAsCurrency(totalTax) : '0'}</td>  
                    <td class="text-center">${grandTotal ? formatAsCurrency(grandTotal) : '0'}</td>
                </tr>
                

    `;
	return html;
}
