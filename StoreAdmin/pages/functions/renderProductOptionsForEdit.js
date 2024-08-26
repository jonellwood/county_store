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

function updateTotal() { 
    var currentTotal = document.getElementById('currentTotal').innerText;
    var currentQuantity = document.getElementById('currentQuantity').value;
    var selectedQuantity = document.getElementById('quantity').value;
    var currentDeptPlacement = document.getElementById('currentDeptPlacement').innerText;
    var selectedDeptPlacement = document.getElementById('deptPlacementSelect').value;
    var currentLogoFee = document.getElementById('currentLogoFee').dataset.logofee;
    var currentTax = document.getElementById('currentTax').dataset.tax;
    var calcTotalDisplay = document.getElementById('n-total');
    var hiddenNewTotal = document.getElementById('newLineItemTotal');
    var hiddenPriceIdHolder = document.getElementById('hidden_price_id');
    var selectedPriceIdOption = document.getElementById('sizeSelect').options[document.getElementById('sizeSelect').selectedIndex];
    var selectedPriceId = selectedPriceIdOption.getAttribute('data-priceid');
    // console.log("selectedPricePID", selectedPriceId);
    var hiddenColorIdHolder = document.getElementById('hidden_color_id');
    var selectedColorIdOption = document.getElementById('colorSelect').options[document.getElementById('colorSelect').selectedIndex];
    var selectedColorId = selectedColorIdOption.getAttribute('data-colorid');
    // console.log("selectedColorId", selectedColorId)
    var hiddenDeptPlaceIdHolder = document.getElementById('hidden_dept_place_id');
    var selectedDeptPlaceId = document.getElementById('deptPlacementSelect').options[document.getElementById('deptPlacementSelect').selectedIndex];
    var hiddenPriceHolderId = selectedDeptPlaceId.getAttribute('data-pid');
    // console.log("hiddenPriceHolderId", hiddenPriceHolderId)
    var itemPrice = document.getElementById('currentItemPrice').dataset.itemprice;
    var nLogoFee = document.getElementById('n-logo-fee');
    var nTax = document.getElementById('n-tax');
    var nPrice = document.getElementById('n-item-price');
    var selectedPrice = document.getElementById('sizeSelect').value;
    
    // console.log(selectedPrice)
    // var selectedPriceId = document.getElementById('sizeSelect').dataset.id;

    if (selectedDeptPlacement == "Below Logo") {
        currentLogoFee = 5;
        nLogoFee.innerText = money_format(currentLogoFee);
        var newTax = ((parseFloat(currentLogoFee) + parseFloat(itemPrice)) * .09);
        nTax.innerText = money_format(newTax);
        var newPrice = parseFloat(selectedPrice)
        // console.log('NEW PRICE: ', newPrice);
        nPrice.innerText = money_format(newPrice); 
    } else if (selectedDeptPlacement == "Left Sleeve") {
        currentLogoFee = 10;
        nLogoFee.innerText = money_format(currentLogoFee);
        var newTax = ((parseFloat(currentLogoFee) + parseFloat(itemPrice)) * .09);
        nTax.innerText = money_format(newTax);
        var newPrice = parseFloat(selectedPrice)
        // console.log('NEW PRICE: ', newPrice);
        nPrice.innerText = money_format(newPrice);
    } else if (selectedDeptPlacement == "No Dept Name") {
        currentLogoFee = 5;
        nLogoFee.innerText = money_format(currentLogoFee);
        var newTax = ((parseFloat(currentLogoFee) + parseFloat(itemPrice)) * .09);
        nTax.innerText = money_format(newTax);
        var newPrice = parseFloat(selectedPrice)
        // console.log('NEW PRICE: ', newPrice);
        nPrice.innerText = money_format(newPrice);
    }
    var lineItemTotal = parseFloat(selectedQuantity) * (parseFloat(currentLogoFee) + parseFloat(newTax) + parseFloat(newPrice));
    calcTotalDisplay.innerText = money_format(lineItemTotal);
    hiddenNewTotal.value = lineItemTotal;
    // var updatedTax = money_format(((parseFloat(nLogoFee) + parseInt(currentItemPrice)) * .09));
    hiddenPriceIdHolder.value = selectedPriceId;
    hiddenColorIdHolder.value = selectedColorId;
    hiddenDeptPlaceIdHolder.value = hiddenPriceHolderId;

}
function displayCurrentTotal() { 
    var currentTotal = document.getElementById('currentTotal').innerText;
    return currentTotal
}


function renderProductOptionsForEdit(data, order_det_id) { 
    
    var eHtml = `
    <div class="form-holder">
        <form action='./API/managerEditOrderUpdateDB.php' method='post'>    
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" max="999999" required onchange='updateTotal()'>

            <label for="color">Color:</label>
            <select id="colorSelect" name="color" required>
                `

                for (var i = 0; i < data.color[0].length; i++) {
                    eHtml += `
                        <option value="${data.color[0][i].color ? data.color[0][i].color : ''}" data-colorid="${data.color[0][i].color_id ? data.color[0][i].color_id : ''}">${data.color[0][i].color ? data.color[0][i].color : 'Error'}</option>
                    `
                }

                eHtml += `
                </select>
                <label for="size">Size:</label>
                <select id="sizeSelect" name="size" required onchange='updateTotal()'>
                `

    for (var j = 0; j < data.size[0].length; j++) {
        console.log(data);
                    eHtml += `
                        <option
                            value='${data.size[0][j].price ? data.size[0][j].price : ''}'
                            data-price='${data.size[0][j].price ? data.size[0][j].price : ''}'
                            data-priceid='${data.size[0][j].price_id ? data.size[0][j].price_id : ''}'
                            data-sizeid='${data.size[0][j].size_id ? data.size[0][j].size_id : ''}'
                        >${data.size[0][j].size_name ? data.size[0][j].size_name : 'Error'}</option>
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
                <select id="deptPlacementSelect" name="deptPlacement" required" onchange="updateTotal()">
                    <option value='No Dept Name' id='p1' data-pid='p1'>No Dept Name </option>
                    <option value='Below Logo' id='p2' data-pid='p2'>Below Logo</option>
                    <option value='Left Sleeve' id='p3' data-pid='p3'>Left Sleeve</option>
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
                <input type='hidden' name="newLineItemTotal" id="newLineItemTotal" value=''>
                <input type='hidden' name='hidden_price_id' id='hidden_price_id' value=''>
                <input type='hidden' name='hidden_color_id' id='hidden_color_id' value=''>
                <input type='hidden' name='hidden_dept_place_id' id='hidden_dept_place_id' value=''>
                <input type='hidden' name='order_details_id' id='order_details_id' value=${order_det_id}>
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
            var currentItemPrice = document.getElementById('currentItemPrice').dataset.itemprice;
            var currentLogoFee = document.getElementById('currentLogoFee').dataset.logofee;
            var currentTax = document.getElementById('currentTax').dataset.tax;
            // var updatedTax = money_format((( parseFloat(currentLogoFee) + parseInt(currentItemPrice)) * .09))
            eHtml += `
                <div class='image-logo-stack'>
                 <img src="../../../product-images/${selectedColor ? formatColorValueForUrl(selectedColor) : ''}_${selectedProduct ? formatValueForUrl(selectedProduct) : ''}.jpg" alt="${data.product[0][0].name}" class="product-image" id="product-image">
                 <img src="../../../${selectedLogo ? selectedLogo : ''}" alt='logo' class='med-logo-img' id='logo-image'/>
                 
               </div>
               <div class='order-total-display'>
                    <span>
                        <p><b>Original Item Price: </b></p>
                        <p id='-o-item-price' name='o-item-price'>${money_format(currentItemPrice)}</p>
                    </span>
                    <span>
                        <p><b>Original Logo Fee: </b></p>
                        <p id='o-logo-fee' name='o-logo-fee'>${money_format(currentLogoFee)}</p>
                    </span>
                    <span>
                        <p><b>Original Tax: </b></p>
                        <p id='o-tax' name='o-tax'>${money_format(currentTax)}</p>
                    </span>
                    <span>  
                        <p><b>Original Item Total: </b></p>
                        <p id='o-total' name='o-total'>${displayCurrentTotal()}</p>
                    </span>
                    <hr/>
                    <span>
                        <p><b>Updated Item Price: </b></p>
                        <p id='n-item-price' name='n-item-price'></p>
                    </span>
                    <span>
                        <p><b>Updated Logo Fee: </b></p>
                        <p id='n-logo-fee' name='n-logo-fee'>${money_format(currentLogoFee)}</p>
                    </span>
                    <span>
                        <p><b>Updated Tax: </b></p>
                        <p id='n-tax' name='n-tax'>0.00</p>        
                    </span>
                    <span>      
                        <p><b>Updated Item Total: </b></p>
                        <p id='n-total' name='n-total'>${setTimeout(() => {updateTotal()}, 150)}</p>
                    </span>
                    
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
    // setTimeout(() => {updateTotal()}, 50); 
    // updateImage(data.color);
       

}

//   ${money_format(data.size[0][j].price) ? money_format(data.size[0][j].price) : 'Error'}

// <input type='hidden' name='color_name' value='${color_id}'>
// <input type='hidden' name='size_name' value='${size_name}'>            
// <input type='hidden' name='bill_to' value='${bill_to}'>
// <input type='hidden' id='currentColor' name='currentColor' value='${currentColor}'>
// <input type='hidden' id='currentSize'  name='currentSize' value='${currentSize}'> 


// from function call color_id, size_name, 