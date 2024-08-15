function formatColorValueForUrl(str) {
    var noSpaces = str.replace(/[\s/]/g, '');
    var lowercaseString = noSpaces.toLowerCase();
    return lowercaseString;
}
function formatValueForUrl(str) {
	return str.toLowerCase();
}


function renderOrderDetails(order) {
    console.log(order);
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
                    
                    <td>${order.product_id !== 105 ? `<img src="${order.logo}" alt="logo" class="logo-image">` : '<img src="" alt="logo-logo" class="hidden">'}</td>
                    <td>${order.dept_patch_place ? order.dept_patch_place : ''}</td>
                </tr>
                <tr>
                    <th>Qty</th>
                    <th>Product Price</th>
                    <th>Logo Fee</th>
                    <th>Tax</th>
                    <th>Line Item Total</th>
                    <th rowspan="2" class="align-middle"><img src="product-images/${formatColorValueForUrl(order.color_name)}_${formatValueForUrl(order.product_code)}.jpg" alt="${order.product_name}" class="product-image"></th>
                </tr>
                <tr>         
                    
                    <td>${order.quantity ? order.quantity : ''}</td>
                    <td>${order.product_price ? formatAsCurrency(order.product_price) : ''}</td>
                    <td>${order.logo_fee ? formatAsCurrency(order.logo_fee) : ''}</td>
                    <td>${order.tax ? formatAsCurrency(order.tax) : ''}</td>
                    <td>${order.line_item_total ? formatAsCurrency(order.line_item_total) : ''}</td>
                </tr>
            </tbody>
        </table>
    `;

	return html;
}


//   <td><img src="${order.logo}" alt="${order.product_name}" class="logo-image"></td>
// <td>${order.product_code !== 105 ? `img src={order.logo} alt="logo"` : ''}</td>