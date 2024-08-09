function renderProduct(product) {
	var html = `
	<div class="card align-content-end shadow-md pointer">
        <a href="product-details.php?product_id=${
					product.product_id
				}" class="card-body border-0">
        
                
                <img class="card-img-top mx-auto" src="${
									product.image
										? product.image
										: 'https://via.placeholder.com/150'
								}" alt="${product.name ? product.name : 'Product Name'}">
								<h5 class="card-title d-flex justify-content-start align-items-start text-justify">${
									product.name ? product.name : 'Product Name'
								}</h4>
                <h6 class="card-subtitle mb-2 text-muted">${
									product.count ? product.count + ' sold' : product.code
								} </h6>
								
					</a>
				</div>
        `;
	return html;
}
