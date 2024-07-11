<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: View all the details about a single product to include available colors and sizes as well as prices for each. Users can select sizes, colors, quantities of this product and add to their cart as desired. 
Includes:   config.php for database connection (moving to js fetch) cartSlideout.php, footer.php, viewHead.php, slider.php
NOTES: this file started at 1284 lines before refactor
*/
session_start();
if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}

include_once 'Cart.class.php';
$cart = new Cart;

$product_id = $_REQUEST['product_id'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Product Details</title>
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
    <script src="./functions/isThisFiscalYear.js"></script>
    <script>
        async function localStorageGetSet() {
            var localStorageCartData = JSON.parse(localStorage.getItem('store-cart')) || {};
            if (!localStorageCartData) {
                cartData = {
                    total_items: 0,
                    cart_total: 0,
                    timestamp: Date.now()
                };
                await localStorage.setItem('store-cart', JSON.stringify(cartData));
            }
        }
        // localStorageGetSet()
        function removeCommasAndSpaces(str) {
            return str.replace(/,/g, '').replace(/\s/g, '');
        }
        // console.log(removeCommasAndSpaces('24,25'));

        function setFiscalYear() {
            // console.log("Checking FY function")
            newFiscalYear = fiscalYear()
            fyStart = newFiscalYear[0];
            fyEnd = newFiscalYear[1];
            thisFiscalYear = (fyStart + fyEnd);
            return thisFiscalYear;
        }
        // console.log(setFiscalYear());

        function makeDollar(str) {
            let amount = parseFloat(str);
            return `$${amount.toFixed(2)}`;
        }

        function unDollar(str) {
            var numStr = str.replace(/[$,]/g, '');
            var numFloat = parseFloat(numStr);
            return numFloat;
        }

        function getCartTotal() {
            var storeCart = <?php echo $cart->serializeCart(); ?>;
            if (storeCart) {
                return storeCart;
            }
            return;

        }

        function getCurrentProductPrice() {
            var hiddenPriceInput = document.getElementById('productPrice')
            var priceDisplay = document.getElementById('price-in-summary')
            priceDisplay.textContent = makeDollar(hiddenPriceInput.value);
        }

        function updateHiddenPriceInput(radio) {
            var hiddenInput = document.getElementById('productPrice');
            hiddenInput.value = radio.dataset.priceval;
            var sizeHiddenInput = document.getElementById('size_id');
            sizeHiddenInput.value = radio.dataset.sizeid;
            var sizeNameHiddenInput = document.getElementById('size_name');
            sizeNameHiddenInput.value = radio.dataset.sizename;
            getCurrentProductPrice();
            updateCurrentPrice();
            calculateSubTotal();
            calculateTax();
            calculateSelectedTotal();
            calculateNewTotal();
        }

        function getCurrentQty() {
            var currentQty = document.getElementById('itemQuantity').value;
            return currentQty;
        }

        function updateCurrentPrice() {
            var priceInSummary = document.getElementById('productPrice').value;
            var priceInSummaryHolder = document.getElementById('price-in-summary');
            priceInSummaryHolder.textContent = makeDollar((priceInSummary * getCurrentQty()))
        }

        function calculateSubTotal() {
            var subTotalInSummary = document.getElementById('sub-in-summary')
            var logoFeeInSummary = document.getElementById('logo-fee-in-summary').textContent
            var priceInSummary = document.getElementById('price-in-summary').textContent;
            var subTotal = ((unDollar(logoFeeInSummary) + (unDollar(priceInSummary))))
            subTotalInSummary.textContent = makeDollar(subTotal);
        }

        function calculateTax() {
            var subTotalInSummary = document.getElementById('sub-in-summary').textContent;
            var taxInSummary = document.getElementById('tax-in-summary');
            var taxRate = .09;
            var tax = (unDollar(subTotalInSummary) * taxRate);
            taxInSummary.textContent = makeDollar(tax);
        }

        function calculateSelectedTotal() {
            var totalInSummary = document.getElementById('total-in-summary');
            var subTotalInSummary = document.getElementById('sub-in-summary').textContent;
            var taxInSummary = document.getElementById('tax-in-summary').textContent;
            var selectedTotal = (unDollar(subTotalInSummary) + unDollar(taxInSummary))
            totalInSummary.textContent = makeDollar(selectedTotal)
        }

        function calculateNewTotal() {
            var newTotalInSummary = document.getElementById('new-total-in-summary');
            var cartTotal = (getCartTotal().cart_total * 1.09);
            var totalInSummary = document.getElementById('total-in-summary').textContent;
            newTotal = (cartTotal + unDollar(totalInSummary));
            newTotalInSummary.textContent = makeDollar(newTotal);
        }

        function updateLogoImage(val) {
            var logoImageInSummary = document.getElementById('logo-img-in-summary')
            var logoFormInput = document.getElementById('logo-url')
            var selectedLogo = document.getElementById(val).dataset.url
            logoImageInSummary.src = selectedLogo
            logoFormInput.value = selectedLogo;
        }

        function updateLogoFeeAddOn(val) {
            var logoFeeUpChargeHiddenInput = document.getElementById('logo_upCharge')
            if (val === 'Left Sleeve') {
                logoFeeUpChargeHiddenInput.value = 5.00
                showToast('This option added $5.00 per item to your cost')
                handleDeptNamePatch()
            } else if (val === 'No Dept Name') {
                handleDeptNamePatch()
            } else {
                logoFeeUpChargeHiddenInput.value = 0.00
                handleDeptNamePatch()
            }
            updateCurrentQty();
        }
        // updates the qty in the summary display as wel as the logo fees
        function updateCurrentQty() {
            var qtyInSummary = document.getElementById('qty-in-summary')
            var logoFeeInSummary = document.getElementById('logo-fee-in-summary')
            var logoUpCharge = document.getElementById('logo_upCharge').value;
            qtyInSummary.textContent = getCurrentQty();
            var totalLogoFee = (5 + parseInt(logoUpCharge));
            logoFeeInSummary.textContent = makeDollar((getCurrentQty() * totalLogoFee))
            updateCurrentPrice();
            calculateSubTotal();
            calculateTax();
            calculateSelectedTotal();
            calculateNewTotal();
        }



        function fetchProductData(id) {
            fetch('fetchProductDetails.php?id=' + <?php echo $product_id ?>)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    var nameHtml = `
                        <h3 class="name-code-holder">
                            <p id="product-name">${data['product_data'][0].code}</p> 
                            <p> - </p>
                            <p>${data['product_data'][0].name}</p>
                        </h3>
                    `
                    var imageHtml = `
                    <img src="product-images/${formatColorValueForUrl(data['color_data'][0].color)}_${formatColorValueForUrl(data['product_data'][0].code)}.jpg" alt="${data['product_data'][0].name}" class="product-image" view-transition-new="image-transition">
                    `
                    var html = '';
                    html += `<form name='option' method='post' id='options' action='cartAction.php' class='options-select-holder'>`;
                    html += `<input type="hidden" name="product_id" id="product_id" value=${data['product_data'][0].product_id} />`;
                    html += `<input type="hidden" name="name" id="name" value="${data['product_data'][0].name}" />`
                    html += `<input type="hidden" name="code" id="code" value=${data['product_data'][0].code} />`
                    html += `<input type="hidden" name="action" id="action" value="addToCart" />`
                    html += `<input type="hidden" name="logo-url" id="logo-url" value=${data['logo_data'][0].image} />`
                    html += `<input type="hidden" name="logoCharge" id="logoCharge" value="5.00" />`
                    html += `<input type="hidden" name="color_name" id="color_name" value="${data['color_data'][0].color}" />`
                    html += `<input type="hidden" name="hidden_color_id" id="hidden_color_id" value="${data['color_data'][0].color_id}" />`
                    html += `<input type="hidden" name="size_id" id="size_id" value=${data['price_data'][0].size_id} />`
                    html += `<input type="hidden" name="size_name" id="size_name" value="${data['price_data'][0].size_name}" />`
                    html += `<input type="hidden" name="logo_upCharge" id="logo_upCharge" value=0 />`
                    html += `<inout type="hidden" name="comment" id="comment" value="farts" />`
                    html += `<input type="hidden" name="image-url" id="image-url" value="product-images/${formatColorValueForUrl(data['color_data'][0].color)}_${formatColorValueForUrl(data['product_data'][0].code)}.jpg" />`
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
                    `
                    for (var k = 0; k < data['logo_data'].length; k++) {
                        html += `<option id=${data['logo_data'][k].id} value=${data['logo_data'][k].id} data-url="${data['logo_data'][k].image}">
                    ${data['logo_data'][k].logo_name} </option>
                        `
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
                    `
                    html += `
                        <div class="quantity-select">
                            <legend>Pick quantity</legend>
                            <input title="itemQuantity" name="itemQuantity" id="itemQuantity" type="number" min="1" max="100" value="1" required onchange="updateCurrentQty()"/>
                        </div>
                    
                    `
                    // TODO make all selectors are hidden for boots.... actually probably just make their own page... it would be easier
                    // TODO if product is from safety products hide county name selector and show info box that it can only be below county seal 
                    // make department name a checkbox option in teh logo holder

                    html += ` </form></div></div>`;
                    summaryHtml = `<div id="selection-summary">
                               <legend>Selection Summary</legend>
                               <table>
                               <tr> 
                                <th>Current Cart Sub-Total: </th><td>${makeDollar(getCartTotal().cart_total)}</td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Current Cart Tax: </th><td id='cart-total-in-summary'>${makeDollar((getCartTotal().cart_total) * .09)}</td>
                               </tr>
                               <tr>
                                <th>Current Selection: </th><td id='price-in-summary'><td>
                               </tr>
                               <tr>
                                <th>Logo Fee: </th><td id='logo-fee-in-summary'></td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Quantity: </th><td id='qty-in-summary'></td>
                               </tr>
                               <tr>
                                <th>Selection Sub-Total: </th> <td id='sub-in-summary'>0</td>
                               </tr>
                               <tr>
                                <th>Selection Tax: </th><td id='tax-in-summary'>0</td>
                               </tr>
                               <tr class='dotted-bottom'>
                                <th>Select Total: </th> <td id='total-in-summary'>0</td>
                               </tr>
                               <tr>
                                <th>New Cart Total: </th> <td id='new-total-in-summary'></td>
                               </tr>
                               </table>
                               <div id='selected-logo-in-summary'>
                                <img src=${data['logo_data'][0].image} alt="${data['logo_data'][0].description}" id='logo-img-in-summary'/>

                               </div>
                    
                    </div>`
                    document.getElementById('new-options-form').innerHTML = html;
                    document.getElementById('product-image-holder').innerHTML = imageHtml;
                    document.getElementById('product-name-holder').innerHTML = nameHtml;
                    document.getElementById('select-summary').innerHTML = summaryHtml;
                    getCurrentProductPrice();
                    matchHeights();
                    updateColorImage(data['color_data'][0].color);
                    updateCurrentQty();
                    calculateSubTotal();
                    // getCurrentQty();

                });
        }
        fetchProductData();
        // Does anyone who would have access to read this code really need a comment to explain what this does?


        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        // This onchange hander uses the updateColor function to update the value of the element with an ID of color_id. It sets the current value to a new color based on the passed in parameter val. Then it assigns the new color to the element which is included in the form action.

        function updateColor(val) {
            c = document.getElementById('color_id').value;
            newc = val;
            document.getElementById("color_id").value = newc;
        }
        // The function formatColorValueForUrl takes in the value of the name of a color and removes characters other than letters and then makes them all lower case. This is then inserted into the "src" for the color swatch image
        function formatColorValueForUrl(str) {
            var noSpaces = str.replace(/[\s/]/g, '');
            var lowercaseString = noSpaces.toLowerCase();
            return lowercaseString;
        }
        // console.log(formatColorValueForUrl('Biscuit / True Blue'));
        // Function to convert hex color to RGB
        function hexToRgb(hex) {

            hex = hex.replace('#', '');
            // convert to stupid rgb
            var bigint = parseInt(hex, 16);
            var r = (bigint >> 16) & 255;
            var g = (bigint >> 8) & 255;
            var b = bigint & 255;

            return {
                r: r,
                g: g,
                b: b
            };
        }
        // the function updateColorImage takes the value from the above function and updates the box shadow around the product image
        // also updates hidden inputs of color name and color id
        function updateColorImage(val) {
            // console.log(val)
            var el = document.getElementById(val);
            // console.log(el)
            var hiddenColorNameInput = document.getElementById('color_name');
            hiddenColorNameInput.value = el.dataset.colorname;
            var hiddenColorIdInput = document.getElementById('hidden_color_id');
            hiddenColorIdInput.value = el.dataset.colorid;
            var hexVal = el.dataset.hex;
            var rgbColor = hexToRgb(hexVal);
            var imageHolder = document.getElementById('product-image-holder')
            var toastHolder = document.getElementById('myToast')
            var container = document.getElementById('container')
            imageHolder.style.boxShadow = `0px 0px 25px -5px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;
            toastHolder.style.boxShadow = `0px 0px 25px 1px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;
            container.style.boxShadow = `0px 0px 55px -25px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;
        }


        // function to update the product image based on the color selected
        function updateProductImage(val) {
            updateColorImage(val);
            // updateHiddenColorInput(val);
            var productImage = document.querySelector('.product-image');
            var imageHiddenInput = document.getElementById('image-url');
            var productCode = document.getElementById('product-name').innerText;
            var newProductImage = formatColorValueForUrl(val) + '_' + productCode + '.jpg';
            productImage.src = './product-images/' + newProductImage.toLowerCase();
            imageHiddenInput.value = './product-images/' + newProductImage.toLowerCase();

        }



        function changeLogo(img) {
            const logoImg = document.getElementById('logo-img');
            const onShirtLogo = document.getElementById('lil-logo');
            const currentPrice = document.getElementById('productPrice').value;
            logoImg.src = img;
            onShirtLogo.src = img;
            // unhideLogo();
            //logoPriceIncrease();
        };

        function forBootsView() {
            const logoDetailsHolder = document.getElementById('logo-info-holder')
            const logoImage = document.getElementById('lil-logo')
            logoDetailsHolder.classList.add('hidden')
            logoImage.classList.add('hidden')
        }

        function showPopover(target) {
            const popovertarget = document.getElementById(target);
            // console.log(target)
            // console.log(popovertarget)
            popovertarget.showPopover()
        }

        function isSafetyProducts() {
            const itemQuantity = document.getElementById('itemQuantity')
            itemQuantity.min = 24;
            itemQuantity.value = 24;
            showPopover('safetyProductsPopover')
        }

        function disableSelectDepName() {
            var select = document.getElementById('deptPatchPlace')
            select.attributes.add('disabled')
        }
    </script>
</head>


<body>
    <?php include "./components/slider.php" ?>
    <div class="spacer23"> - </div>
    <div class="container" id="container">

        <!-- // ? This is the div where the product name and code are rendered. Values are also used in some image update functions  -->
        <div class="product-name-holder-stretched" id="product-name-holder"></div>
        <div class="another-container" id="another-container">
            <!-- // ? this is where the product image gets rendered -->
            <span class='product-image-holder card' id='product-image-holder' view-transition-group="image-transition"></span>

            <div class="details-about-details">
                <!--//? This is the div where the submit form is rendered -->
                <div id='new-options-form'></div>

            </div>
            <!-- // ?This is the div where the summary of the users selection is rendered -->
            <div class="select-summary" id="select-summary"></div>
        </div>
        <div class=" button-holder">
            <a href=<?php echo $_SESSION['GOBACK'] ?>><button class="button btn btn-secondary" type="button">← Continue Shopping </button></a>
            <!-- <button onclick="showCart()">My Cart</button> -->
            <!-- <button type="button" class="btn btn-secondary" id="toggle-button" onclick="toggleSlideout()">View Cart</button> -->
            <button type="submit" form="options" class="button btn btn-primary custom-btn"><span> Add to Cart ➕</span></button>
        </div>

        <!-- This was originaly intended to be used as a notification for a product being added to the cart - but the page reloads when something as added to the cart so it is useless... I am keeping in the code because I WILL find some use for these toast messages! -->
        <div id="myToast">
            <div class="toast-header">
                ⚠️ Price Change Alert
                <small>1 sec ago</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" onclick="eatToast()"></button>
            </div>
            <div class="toast-body">
                <p id="price_toast_message"></p>
            </div>
        </div>
        <!-- <div class="size-chart-holder">
                    <?php include "./components/size-chart-womens.php" ?>
                </div> -->
    </div>
    <div id="safetyProductsPopover" class="safetyProductsPopover" popover=manual>
        <button class="close-btn" popovertarget="safetyProductsPopover" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <p>This has a minimum order quantity of 24. Currently the only department approved for ordering this product is
            Stormwater / Roads & Bridges. If interested in this product please contact store@berkeleycountysc.gov for
            more information.</p>
    </div>
</body>
<?php include "cartSlideout.php" ?>
<?php include "footer.php" ?>
<script>
    // function to make & show toast messags. No real use case for them... yet....
    // I should really move this its own file....
    function showToast(msg) {
        console.log(msg);
        var toast = document.getElementById('myToast');
        var msgBlock = document.getElementById('price_toast_message');
        console.log(msgBlock);
        msgBlock.innerText = msg;
        toast.className = "show";
        setTimeout(function() {
            toast.className = toast.className.replace("show", "hideToast");
        }, 3000);
    }

    function eatToast() {
        var toast = document.getElementById('myToast').classList.replace('show', 'eatToast');
    }
</script>
<script>
    // Function to add "_NO" to the end of the filename
    function addNoToFileName(filename) {
        if (filename.includes('_NO')) {
            return filename; // "_NO" is already present, no need to modify ... except when there is.... hmmmm
        } else {
            const dotIndex = filename.lastIndexOf('.');
            const baseName = filename.substring(0, dotIndex); // Get base name without extension
            const extension = filename.substring(dotIndex); // Grab the extension
            return baseName + '_NO' + extension;
        }
    }

    // Remove "_NO" from the end of the filename when needed.... maybe this all lives in a switch statement based on the state of the department name details?? Grab the current value of that and then case|switch ? 
    function removeNoFromFileName(filename) {
        return filename.replace(/_NO(\.[^.]+)$/, '$1');
    }
    // helper function to parse the image url to push into the hidden 
    // input as relative instead of absolute. its a little hacky but what ya gonna do?
    function convertAbsToRel(url) {
        const parsedUrl = new URL(url);
        let relativePath = parsedUrl.pathname.split('/').filter(segment => segment !== 'county_store');
        relativePath.shift();
        return relativePath.join('/');
    }


    function handleDeptNamePatch() {
        const selectElement = document.getElementById('deptPatchPlace');
        const selectedValue = selectElement.value;
        //console.log("Dept Name Selected Value is: ", selectedValue);

        // const imageElement = document.getElementById('lil-logo');
        const imageElement = document.getElementById('logo-img-in-summary');
        const imageUrlHiddenInput = document.getElementById('logo-url');
        //console.log('ie11 ', imageElement);

        const oldSrc = imageElement.src;
        //console.log('oldSrc is: ', oldSrc);
        const fileName = oldSrc.split('/').pop();
        //console.log("File Name ", fileName);

        if (imageElement.complete && imageElement.src) {
            const oldSrc = imageElement.src;
            //console.log('oldSrc is: ', oldSrc);
            const fileName = oldSrc.split('/').pop();
            //console.log("File Name ", fileName);

            switch (selectedValue) {
                case 'No Dept Name':
                case 'Back of Hat':
                case 'Left Sleeve':
                    //console.log('left sleeve');
                    const newFileName = addNoToFileName(fileName);
                    const newSrc = oldSrc.replace(fileName, newFileName);
                    imageElement.src = newSrc;
                    imageUrlHiddenInput.value = convertAbsToRel(newSrc)
                    break;

                case 'Below Logo':
                    //console.log('Below Logo')
                    const fileNameWithoutNo = removeNoFromFileName(fileName);
                    const newSrcWithoutNo = oldSrc.replace(fileName, fileNameWithoutNo);
                    imageElement.src = newSrcWithoutNo;
                    imageUrlHiddenInput.value = convertAbsToRel(newSrcWithoutNo)
                    break;

                    // case 'Back of Hat':
                    // Do nothing
                    // break;

                default:
                    // console.log('Invalid selection');
                    break;
            }
        }
    }
    // we're gonna try this and see if we can make the element heights match dynamically
    function matchHeights() {
        // get target height
        var newOptionsForm = document.getElementById('new-options-form');
        var newOptionsFormHeight = newOptionsForm.offsetHeight;

        // Set the height tp match target
        var selectionSummary = document.getElementById('selection-summary');
        selectionSummary.style.height = newOptionsFormHeight + 'px';

        // set image holder height to the same why dont we
        var imageHolder = document.getElementById('product-image-holder');
        imageHolder.style.height = newOptionsFormHeight + 'px';
    }

    // Call the function initially to set the height in the render function to avoid race condition


    // This is in case the height changes dynamically... i dont think it will other than window resize ...
    // this doesnt seem to be firing ... stupid Google .... come back to it later 
    new MutationObserver(matchHeights).observe(document.getElementById('another-container'), {
        childList: true,
        subtree: true
    });
</script>

</html>
<style>
    body {
        background-color: #ffffff10;
    }

    .container {
        max-width: unset !important;
    }

    .another-container {
        display: grid;
        grid-template-columns: 33% 33% 33%;
        position: relative;

    }

    .product-image-holder {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: fit-content;
        max-height: 90vh;
        width: fit-content;
        background-color: transparent;
        margin-left: auto;
        margin-right: auto;
        margin-top: 20px;
    }

    .product-image {
        border-radius: 5px;
        padding: 15px;
        clip: rect(0, auto, 700px, 0);
    }

    .details-about-details {
        text-align: right;
        /* margin-left: 20px; */
        margin-right: 20px;
        min-height: 500px;
        min-width: fit-content;
    }

    .button-holder {
        margin-top: 5px;
        display: flex;
        /* justify-content: flex-start; */
        justify-content: space-around;
        /* gap: 6em; */
        margin-left: 5em;
        margin-right: 5em;
    }

    .options-select-holder {
        margin-top: 25px;
    }

    label {
        margin-right: 15px;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    #logo-img {
        width: 200px;
    }

    .hidden {
        visibility: hidden;
    }

    .tiny-text {
        font-size: .75em;
    }

    dialog {
        background-color: lightgrey;
        color: black;
    }

    .logo-not-available-text {
        visibility: visible;
    }

    #logo-price-text-holder {
        color: lightblue;
    }

    #color-picker-holder,
    #size-picker-holder,
    .dept-name-patch-holder,
    .logo-info-holder,
    .quantity-select,
    #selection-summary {
        background: rgba(0, 0, 0, .5);
        border-color: rgba(255, 255, 255, .3);
        border-style: solid;
        border-width: 2px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        line-height: 30px;
        list-style: none;
        padding: 5px 10px;
        margin-bottom: 1em;
        color: aliceblue;
    }

    #color-picker-holder,
    #logo-info-holder,
    .dept-name-patch-holder,
    .quantity-select {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%
    }

    #selection-summary {
        margin-top: 5.5%;
        display: flex;
        flex-direction: column;

        table {
            width: 100%;
        }
    }

    #selection-summary th,
    td {
        color: aliceblue;
    }

    .quantity-select input {
        line-height: normal;
    }

    #logo-picker-holder select {
        width: 100%;
    }


    .size-price-picker-holder {
        margin-top: 5px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 2px;
        padding: 10px;
        justify-items: start;

        label {
            font-size: 1rem;

        }

    }

    legend {
        text-shadow: 0 1px 2px #195f80;
        text-align: left;
        margin-bottom: 0 !important;
        font-size: large;
    }

    .toast-header {
        /* background-color: #f57f43; */
        background-color: #00000050;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        padding: 10px;
        font-size: large;
    }

    .toast-body {
        padding-top: 5px;
        text-align: center;
    }

    #myToast {
        width: 25%;
        visibility: hidden;
        background-color: slategray;
        color: aliceblue;
        text-align: center;
        border-radius: 5px;
        padding: 16px;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 5;
        border: 2px solid #005677;
        border-radius: 10px;
        /* box-shadow: 0px 0px 15px -10px rgba(255, 255, 255, 1); */
    }

    .show {
        visibility: visible !important;
        opacity: 1;
        transition: opacity 2s linear;
    }

    .hideToast {
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s 2s, opacity 2s linear;
    }

    .eatToast {
        visibility: hidden;
    }

    .fa-info {
        padding-left: 10px;
    }

    .btn-close {
        color: black;
        border: 1px solid black;
    }

    figcaption {
        font-size: smaller;
    }

    #hatBackText {
        background-color: rebeccapurple;
        text-align: center;
        color: wheat;
    }

    .product-name-holder-stretched {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: center;
        width: 99%;
    }

    .product-name-holder-stretched h3 {
        font-size: 1.5vw;
    }

    .spec-sheet-download-link {
        padding-top: 1.5em;
        color: aliceblue;
    }

    .product-description-text-holder {
        background-color: #00000095;
        color: white;
        /* position: fixed; */
        bottom: -7;
        width: 80%;
        text-align: center;
        padding: 5px;
    }

    .square {
        position: relative;
        float: right;
        width: 25px;
        height: 25px;
        margin-left: 20px;
        border: .5px solid white;
    }

    #colorImageSrc {
        visibility: hidden;
    }


    .red {
        background-color: red;
    }

    .black {
        background-color: black;
    }

    .blue {
        background-color: blue;
    }

    .green {
        background-color: green;
    }

    .cart-viewer {
        background-color: #000;
        border: 5px solid #fff;
        position: absolute;
        z-index: 5;
        top: 0;
        margin-top: 100px;
        border: 1px solid #772953;
        border-top: 10px solid #E95420;
        border-radius: 10px;
        bottom: 0;
        margin-bottom: 100px;
        left: 0;
        margin-left: 10%;
        right: 0;
        margin-right: 10%;
        max-height: 800px;
        padding: 20px;
        justify-content: center;
        align-content: center;
        overflow: scroll;
    }

    #cart-logo-img {
        width: 75px;
    }

    .cart-h1 {
        display: none;
    }

    .theDevil {
        display: none;
    }

    figure {
        display: table;
    }

    figcaption {
        position: relative;
        display: table-caption;
        caption-side: bottom;
        color: aliceblue;

    }

    #square {
        display: none;

    }

    #lil-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        width: 150px;
        background-color: #d5ca9e;
        box-shadow: 5px 5px 7px #00000040, inset -3px -3px 5px #00000080;
        border-radius: 5px;
    }

    .name-code-holder {
        display: flex;
        gap: 2px;
    }

    .price-size-row {
        display: flex;
        flex-wrap: nowrap;
        flex-direction: column;
        align-content: flex-start;
    }

    label:has(> input[type="radio"]:checked) {
        background-color: #80808080;
        border-radius: 5px;
    }

    /* This has to be in this file because cascades are hard m'kay */
    .cart-slideout td {
        color: black !important;
    }

    .dotted-bottom {
        border-bottom: 1px aliceblue dotted;
    }

    #selected-logo-in-summary {
        background-color: #80808090;
        display: flex;
        flex-wrap: wrap;
        align-content: center;
        align-items: center;

        img {
            width: 100px;
            height: 100px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
            padding-top: 2%;
            padding-bottom: 2%;
            object-fit: scale-down;
        }
    }

    #safetyProductsWarning {
        display: flex;
        justify-content: center;
        align-content: center;
        background-color: #E95420;
        color: white;
        width: 90%;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 5px;
        margin-top: 70px;
    }

    #safetyProductsPopover {
        margin: auto;
        position: fixed;
        width: 40%;
        height: 20%;
        border: 5px solid tomato;
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
        border-radius: 5px;
        padding: 10px;
    }

    #safetyProductsPopover::backdrop {
        backdrop-filter: blur(5px);
    }

    .button {
        margin: 5px;
    }

    .button {
        display: inline-block;
        padding: 5px 10px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border: 2px solid #000000;
        border-radius: 5px;
        background-color: #4CAF50;
        color: #000000;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #4CAF50 !important;
        color: #000000 !important;
        font-weight: bold !important;
    }


    /* @view-transition {
        navigation: auto;
    } */

    /* .card {
        view-transition-name: card-transition;
    } */

    @keyframes grow-x {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    @keyframes shrink-x {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }

    ::view-transition-group(card-transition) {
        height: auto;
        right: 0;
        left: auto;
        transform-origin: right center;
    }

    ::view-transition-old(card-transition) {
        animation: 0.25s linear both shrink-x;
    }

    ::view-transition-new(card-transition) {
        animation: 0.25s 0.25s linear both grow-x;
    }

    @keyframes move-out {
        from {
            transform: translateY(0%);
        }

        to {
            transform: translateY(-100%);
        }
    }

    @keyframes move-in {
        from {
            transform: translateX(100%);
        }

        to {
            transform: translateX(0%);
        }
    }



    @-webkit-keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30 px;
            opacity: 1;
        }
    }

    @keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30 px;
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeout {
        from {
            bottom: 30 px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    @keyframes fadeout {
        from {
            bottom: 30 px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    @view-transition {
        navigation: auto;
    }

    /* ::view-transition-old(image) {
        animation: 0.75s ease-in both fadeout;
    }

    ::view-transition-new(image) {
        animation: 0.75s ease-in both fadein;
    } */

    /* ::view-transition-old(root) {
        animation: 1.2s ease-in both fadeout;
    }

    ::view-transition-new(root) {
        animation: 1.2s ease-in both fadein;
    } */

    ::view-transition-group(root) {
        animation-duration: 0.5s;
    }

    @keyframes grow-and-move {
        from {
            transform: scale(0) translateY(0);
        }

        to {
            transform: scale(1) translateY(-100%);
        }
    }


    ::view-transition-group(image-transition) {
        height: auto;
        position: absolute;
        top: 0;
        left: 0;
        transform-origin: top left;
    }

    ::view-transition-old(image-transition) {
        animation: 0.5s ease-in-out both grow-and-move;
    }

    ::view-transition-new(image-transition) {
        animation: 0.5s ease-in-out both grow-and-move;
    }
</style>