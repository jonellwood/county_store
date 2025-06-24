function renderProduct(product) {
	var html = `
	<div class="card align-content-end shadow-md pointer" value="${product.product_id}" data-gender="${product.gender_filter}" data-type="${product.type_filter}" data-size="${product.size_filter}" data-sleeve="${product.sleeve_filter}">
        <a href="product-details.php?product_id=${product.product_id}" class="card-body border-0">
			<img class="card-img-top mx-auto" src="${product.image ? product.image : 'https://via.placeholder.com/150'}" alt="${product.name ? product.name : 'Product Name'}">
			<h5 class="card-title d-flex justify-content-start align-items-start text-justify">${product.name ? product.name : 'Product Name'}</h5>
			<h6 class="card-subtitle mb-2 text-muted">${product.count ? product.count + ' sold' : product.code} </h6>
		</a>
	</div>
`;
	return html;
}
