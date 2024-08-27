function renderProductDetails(data) {
	console.log(data);
	// if (data['product_data'][0].product_type === '0') {
	// window.location.replace = 'index.php';
	// } else {
	var nameHtml = `
                        <h3 class="name-code-holder">
                            <p id="product-name">${data['product_data'][0].code}</p> 
                            <p class="mx-2"> | </p>
                            <p>${data['product_data'][0].name}</p>
                        </h3>
                    `;
	var imageHtml = `
                    <img src="product-images/${formatColorValueForUrl(
											data['color_data'][0].color
										)}_${formatColorValueForUrl(
		data['product_data'][0].code
	)}.jpg" alt="${
		data['product_data'][0].name
	}" class="product-image" view-transition-new="image-transition">
                    `;
	var html = '';
	html += `<form name='option' method='post' id='options' action='cartAction.php' class='options-select-holder'>`;
	html += `<input type="hidden" name="product_id" id="product_id" value=${
		data['product_data'][0].product_id ? data['product_data'][0].product_id : ''
	} />`;
	html += `<input type="hidden" name="name" id="name" value="${
		data['product_data'][0].name ? data['product_data'][0].name : ''
	}" />`;
	html += `<input type="hidden" name="code" id="code" value=${
		data['product_data'][0].code ? data['product_data'][0].code : ''
	} />`;
	html += `<input type="hidden" name="action" id="action" value="addToCart" />`;
	html += `<input type="hidden" name="logo-url" id="logo-url" value=${
		data['logo_data'][0].image ? data['logo_data'][0].image : ''
	} />`;
	html += `<input type="hidden" name="logo_id" id="logo_id" value=${
		data['logo_data'][0].id ? data['logo_data'][0].id : 0
	} />`;
	html += `<input type="hidden" name="logoCharge" id="logoCharge" value="5.00" />`;
	html += `<input type="hidden" name="color_name" id="color_name" value="${
		data['color_data'][0].color ? data['color_data'][0].color : ''
	} " />`;
	html += `<input type="hidden" name="hidden_color_id" id="hidden_color_id" value="${
		data['color_data'][0].color_id ? data['color_data'][0].color_id : ''
	}" />`;
	html += `<input type="hidden" name="size_id" id="size_id" value=${
		data['price_data'][0].size_id ? data['price_data'][0].size_id : ''
	} />`;
	html += `<input type="hidden" name="size_name" id="size_name" value="${
		data['price_data'][0].size_name ? data['price_data'][0].size_name : ''
	}" />`;
	html += `<input type="hidden" name="logo_upCharge" id="logo_upCharge" value=0 />`;
	html += `<inout type="hidden" name="comment" id="comment" value="farts" />`;
	html += `<input type="hidden" name="image-url" id="image-url" value="product-images/${formatColorValueForUrl(
		data['color_data'][0].color
	)}_${formatColorValueForUrl(data['product_data'][0].code)}.jpg" />`;
	//! I really dont understand this one... why I have to call this first otherwise we get commas... time to move on
	let sillFYValue = setFiscalYear();
	html += `<input type="hidden" name="fy" id="fy" value=${sillFYValue} />
                <div id='color-picker-holder'>
                    <legend>Pick a Color</legend>
                    <label for="color_id" class="legend"></label>
                    <select title="color_id" name="color_id" id="color_id" onchange="updateProductImage(this.value)">
            `;
	for (var i = 0; i < data['color_data'].length; i++) {
		html += `<option id="${data['color_data'][i].color}" value="${data['color_data'][i].color}" data-hex="${data['color_data'][i].p_hex}" data-colorname="${data['color_data'][i].color}" data-colorid="${data['color_data'][i].color_id}">${data['color_data'][i].color}  </option>`;
	}
	html += `
                    </select>
                </div>
            `;
	html += `
                    <div id='size-picker-holder'>
                        <legend>Pick a Size</legend>
                        <div class="size-price-picker-holder">
                        `;

	for (var j = 0; j < data['price_data'].length; j++) {
		html += `<label for='${data['price_data'][j].price_id}'>`;
		html += `<input type='radio' id=${data['price_data'][j].price_id} value=${data['price_data'][j].price_id} name='size-price-id' data-priceval=${data['price_data'][j].price} data-sizeid=${data['price_data'][j].size_id} data-sizename="${data['price_data'][j].size_name}" onchange='updateHiddenPriceInput(this)'`;
		if (j === 0) {
			html += `checked`;
		}
		html += ` />
                        ${data['price_data'][j].size_name}
                        - ${makeDollar(data['price_data'][j].price)}
                    </label>`;
	}
	html += `
                        <input type="hidden" name="productPrice" id="productPrice" value=${data['price_data'][0].price} />
                        </div>
                    </div>
                    `;

	html += `
                        <div id="logo-info-holder" class="logo-info-holder">
                            <legend>Pick a Logo</legend>
                            <select title="logo" name="logo" id="logo" onchange="updateLogoImage(this.value)">
                    `;
	for (var k = 0; k < data['logo_data'].length; k++) {
		html += `<option id=${data['logo_data'][k].id} value=${data['logo_data'][k].id} data-url="${data['logo_data'][k].image}">
                    ${data['logo_data'][k].logo_name} </option>
                        `;
	}

	html += `
                        </select>
                        </div>
                        <div class="dept-name-patch-holder">
                            <legend>Dept Name</legend>
                            <label for="deptPatchPlace"><label>
                            <select title="deptPatchPlace" name="deptPatchPlace" id="deptPatchPlace" onchange="updateLogoFeeAddOn(this.value)">
                                <option value='No Dept Name' id='p1'>No Dept Name</option>
                                <option value='Below Logo' selected id='p2'>Below Logo</option>
                                <option value='Left Sleeve' id='p3'>Left Sleeve</option>
                            </select>
                        </div>
                    `;
	html += `
                        <div class="quantity-select">
                            <legend>Pick quantity</legend>
                            <input title="itemQuantity" name="itemQuantity" id="itemQuantity" type="number" min="1" max="100" value="1" required onchange="updateCurrentQty()"/>
                        </div>
                    
                    `;
	// TODO make all selectors are hidden for boots.... actually probably just make their own page... it would be easier
	// TODO if product is from safety products hide county name selector and show info box that it can only be below county seal
	// make department name a checkbox option in teh logo holder

	html += ` </form></div></div>`;
	summaryHtml = `<div id="selection-summary">
                               <legend>Selection Summary</legend>
                               <table class="selection-summary-table">
                               <tr> 
                                <th>Current Cart Sub-Total: </th><td  class="amount-column">${makeDollar(
																	getCartTotal().cart_total
																)}</td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Current Cart Tax: </th><td class="amount-column" id='cart-total-in-summary'>${makeDollar(
																	getCartTotal().cart_total * 0.09
																)}</td>
                               </tr>
                               <tr>
                                <th>Current Selection: </th><td class="amount-column" id='price-in-summary'><td>
                               </tr>
                               <tr>
                                <th>Logo Fee: </th><td class="amount-column" id='logo-fee-in-summary'></td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Quantity: </th><td class="amount-column" id='qty-in-summary'></td>
                               </tr>
                               <tr>
                                <th>Selection Sub-Total: </th> <td class="amount-column" id='sub-in-summary'>0</td>
                               </tr>
                               <tr>
                                <th>Selection Tax: </th><td class="amount-column" id='tax-in-summary'>0</td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Select Total: </th> <td class="amount-column" id='total-in-summary'>0</td>
                               </tr>
                               <tr>
                                <th>New Cart Total: </th> <td class="amount-column" id='new-total-in-summary'></td>
                               </tr>
                               </table>
                               <div id='selected-logo-in-summary'>
                                <img src=${data['logo_data'][0].image} alt="${
		data['logo_data'][0].description
	}" id='logo-img-in-summary'/>

                               </div>
                    
                    </div>`;
	document.getElementById('new-options-form').innerHTML = html;
	document.getElementById('product-image-holder').innerHTML = imageHtml;
	document.getElementById('product-name-holder').innerHTML = nameHtml;
	document.getElementById('select-summary').innerHTML = summaryHtml;
}
// }
