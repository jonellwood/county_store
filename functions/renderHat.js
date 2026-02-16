function renderHat(product) {
	var html = `
	<div class="product-card card shadow-md pointer">
        <a href="hat-details.php?product_id=${product.product_id}" class="product-card-link">
			<div class="product-card-image">
				<img src="${product.image ? product.image : 'https://via.placeholder.com/150'}" alt="${product.name ? product.name : 'Product Name'}">
			</div>
			<div class="product-card-info">
				<h5 class="product-card-title">${product.name ? product.name : 'Product Name'}</h5>
				<h6 class="product-card-code">${product.count ? product.count + ' sold' : product.code}</h6>
			</div>
		</a>
	</div>
	`;
	return html;
}
