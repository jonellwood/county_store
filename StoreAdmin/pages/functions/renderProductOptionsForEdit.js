function money_format(amount) {
	let USDollar = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
	});
	return USDollar.format(amount);
}
function formatColorValueForUrl(str) {
	var noSpaces = str.replace(/[\s/]/g, '');
	var lowercaseString = noSpaces.toLowerCase();
	return lowercaseString;
}
function formatValueForUrl(str) {
	return str.toLowerCase();
}
function updateImage() {
    var image = document.getElementById('product-image');
    var color = document.getElementById('colorSelect').value;
    var productCode = document.getElementById('currentProductCode').innerText;
    // console.log('color', color);
    // console.log(formatColorValueForUrl(color));
    // console.log(productCode);
    image.src = '../../../product-images/' + formatColorValueForUrl(color).toString() + '_'+ formatValueForUrl(productCode) + '.jpg'
}

function updateLogoImage(val) {
    var logoImage = document.getElementById('logo-image');
    var selectedLogo = document.getElementById(val).dataset.url;
    //console.log(selectedLogo);
    logoImage.src = '../../../' + selectedLogo
}

function renderProductOptionsForEdit(data, order_det_id) { 
    // console.log('In render function')
    // console.log(data.color[0][0].color)
    var eHtml = `
    <p>Next up display and update current prices</p>
    <div class="form-holder">
        <form action='updateOrder.php' method='post'>    
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" max="999999" required>

            <label for="color">Color:</label>
            <select id="colorSelect" name="color" required onchange='updateImage()'>
                `

                for (var i = 0; i < data.color[0].length; i++) {
                    eHtml += `
                        <option value="${data.color[0][i].color ? data.color[0][i].color : ''}">${data.color[0][i].color ? data.color[0][i].color : 'Error'}</option>
                    `
                }

                eHtml += `
                </select>
                <label for="size">Size:</label>
                <select id="sizeSelect" name="size" required>
                `

                for (var j = 0; j < data.size[0].length; j++) {
                    eHtml += `
                        <option value=${data.size[0][j].id ? data.size[0][j].id : ''}>${data.size[0][j].size_name ? data.size[0][j].size_name : 'Error'}</option>
                    `
                }

                eHtml += `
                </select>
                <label for="logo">Logo:</label>
                <select id="logoSelect" name="logo" required onchange="updateLogoImage(this.value)">
                `
                for (var k = 0; k < data.logo[0].length; k++) {
                    eHtml += `
                        <option id=${data.logo[0][k].id ? data.logo[0][k].id : ''} value=${data.logo[0][k].id ? data.logo[0][k].id : ''} data-url=${data.logo[0][k].image ? data.logo[0][k].image : ''}>${data.logo[0][k].logo_name ? data.logo[0][k].logo_name : 'Error'}</option>
                    `
                }
    
    
                eHtml += `
                </select>
                <label for="deptPlacement">Dept Name Placement:</label>
                <select id="deptPlacementSelect" name="deptPlacement" required>
                    <option value='N/A'>No Dept Name </option>
                    <option value='Top'>Below Logo</option>
                    <option value='Bottom'>Left Sleeve</option>
                </select>


                <label for="bill_to">Bill To:</label>
                <input type='text'
                    name='bill_to'
                    rows='1'
                    maxlength='5'
                    maxlength='5'
                    id='bill_to'>
                </input>
                <label for='comment'>Comment</label>
                <textarea name='comment' cols=50 rows=4 oninput='makeButtonActive()'></textarea>
                <p>A comment is required when submitting a change</p>
            </select>
            <div class='styled-table bottom-row'>
            <button type='submit' id='update-button' disabled class='btn btn-approve'>Update</button>
            <button type='button' class='btn btn-deny' onclick='createCancelOrderPopover(${order_det_id})' id='cancel-button' popovertarget='cancel-confirm' popovertargetaction='show'>Cancel Order</button>
            </div>
            </form>
             `
            //  var selectedColor = document.getElementById('colorSelect').value;
            var selectedColor = document.getElementById('currentColor').innerText;
            var selectedProduct = document.getElementById('currentProductCode').innerText;
            var selectedLogo = document.getElementById('currentLogo').dataset.url;
            eHtml += `
                <div class='image-logo-stack'>
                 <img src="../../../product-images/${selectedColor ? formatColorValueForUrl(selectedColor) : ''}_${selectedProduct ? formatValueForUrl(selectedProduct) : ''}.jpg" alt="${data.product[0][0].name}" class="product-image" id="product-image">
                 <img src="../../../${selectedLogo ? selectedLogo : ''}" alt='logo' class='med-logo-img' id='logo-image'/>
                 
               </div>
             
            
            </div>
            `
    
                // console.log('selectedColor', selectedColor)
        
    
    const target = document.getElementById('details');
    // console.log(target);
    target.innerHTML = eHtml;
    setDefaultSizeOption();
    setDefaultColorOption();
    setDefaultQtyOption();
    setDeptNamePlacementOption();
    setDefaultLogoOption();
    setDefaultBillToOption();
    // updateImage(data.color);
    
       

}

//   ${money_format(data.size[0][j].price) ? money_format(data.size[0][j].price) : 'Error'}

// <input type='hidden' name='color_name' value='${color_id}'>
// <input type='hidden' name='size_name' value='${size_name}'>            
// <input type='hidden' name='bill_to' value='${bill_to}'>
// <input type='hidden' id='currentColor' name='currentColor' value='${currentColor}'>
// <input type='hidden' id='currentSize'  name='currentSize' value='${currentSize}'> 


// from function call color_id, size_name, 