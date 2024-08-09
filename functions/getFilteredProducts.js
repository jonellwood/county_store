async function getFilteredProducts(typeID, genderFilter) {
	await fetch(
		'fetchFilteredProducts.php?type=' + typeID + '&gender=' + genderFilter
	)
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
			var html = '';
			// html += `<div class="container" id="products-container">`;
			for (var i = 0; i < data.length; i++) {
				html += renderProduct(data[i]);
			}
			// html += `</div>`;

			document.getElementById('products-target').innerHTML = html;

			getFilterData();
		});
}
