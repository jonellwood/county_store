function renderOrderDetails(order) {
	var html = `
        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="2">Product</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Logo</th>
                    <th>Dept Name</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">${order.product_name ? order.product_name : ''} ${order.product_code ? '(' + order.product_code + ')' : ''}</td>
                    <td>${order.size_name ? order.size_name : ''}</td>
                    <td>${order.color_name ? order.color_name : ''}</td>  
                    <td><img src="${order.logo}" alt="${order.product_name}" class="logo-image"></td>
                    <td>${order.dept_patch_place ? order.dept_patch_place : ''}</td>
                </tr>
                <tr>
                    <th>Qty</th>
                    <th>Product Price</th>
                    <th>Logo Fee</th>
                    <th>Tax</th>
                    <th>Line Item Total</th>
                    <th rowspan="2" class="align-middle"><img src="${order.product_image}" alt="${order.product_name}" class="product-image"></th>
                </tr>
                <tr>         
                    
                    <td>${order.quantity ? order.quantity : ''}</td>
                    <td>${order.product_price ? '$' + order.product_price : ''}</td>
                    <td>${order.logo_fee ? '$' + order.logo_fee : ''}</td>
                    <td>${order.tax ? '$' + order.tax : ''}</td>
                    <td>${order.line_item_total ? '$' + order.line_item_total : ''}</td>
                </tr>
            </tbody>
        </table>
    `;

	return html;
}
