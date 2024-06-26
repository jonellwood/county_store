<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 06/25/2024
Purpose: View all the details about a single product to include available colors and sizes as well as prices for each. Users can select sizes, colors, quantities of this product and add to their cart as desired. 
Includes:   config.php for database connection (moving to js fetch)
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
    <script>
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
            var storeCart = localStorage.getItem('store-cart')

            return JSON.parse(storeCart);
        }

        function getCurrentProductPrice() {
            var hiddenPriceInput = document.getElementById('productPrice')
            var priceDisplay = document.getElementById('price-in-summary')
            priceDisplay.textContent = makeDollar(hiddenPriceInput.value);
        }

        function updateHiddenPriceInput(radio) {
            var hiddenInput = document.getElementById('productPrice');
            hiddenInput.value = radio.dataset.priceval;
            getCurrentProductPrice();
            updateCurrentPrice();
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
        // ! PICK UP HERE GETTING THE CORRECT STRING FOR THE LOGO IMAGE 
        function updateLogoImage() {
            var logoImageInSummary = document.getElementById('logo-img-in-summary')
            console.log('logo image in summarry')
            console.log(logoImageInSummary);
            var selectedLogo = document.getElementById('logo').dataset.url;
            console.log('selectedLogo')
            console.log(selectedLogo)
            logoImageInSummary.src = selectedLogo
        }

        function updateCurrentQty() {
            var qtyInSummary = document.getElementById('qty-in-summary')
            var logoFeeInSummary = document.getElementById('logo-fee-in-summary')
            qtyInSummary.textContent = getCurrentQty();
            logoFeeInSummary.textContent = makeDollar((getCurrentQty() * 5))
            updateCurrentPrice();
            calculateSubTotal();
            calculateTax();
            calculateSelectedTotal();
            calculateNewTotal();
        }



        function fetchProductData(id) {
            fetch('reFetchProductDetails.php?id=' + <?php echo $product_id ?>)
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
                    <img src="product-images/${formatColorValueForUrl(data['color_data'][0].color)}_${data['product_data'][0].code}.jpg" alt="${data['product_data'][0].name}" class="product-image">
                    `
                    var html = '';
                    html += `<form name='option' method='post' is='options' action='cartAction' class='options-select-holder'>`;
                    html += `
                <div id='color-picker-holder'>
                    <legend>Pick a Color</legend>
                    <label for="color_id" class="legend"></label>
                    <select title="color_id" name="color_id" id="color_id" onchange="updateProductImage(this.value)">
            `;
                    for (var i = 0; i < data['color_data'].length; i++) {
                        html += `<option id="${data['color_data'][i].color}" value="${data['color_data'][i].color}" data-hex="${data['color_data'][i].p_hex}">${data['color_data'][i].color}</option>`;
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
                        html += `<input type='radio' id=${data['price_data'][j].price_id} value=${data['price_data'][j].price_id} name='size-price' data-priceval=${data['price_data'][j].price} onchange='updateHiddenPriceInput(this)'`;
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
                            <select title="logo" name="logo" id="logo" onchange="updateLogoImage()">
                    `
                    for (var k = 0; k < data['logo_data'].length; k++) {
                        html += `<option value=${data['logo_data'][k].id} data-url="${data['logo_data'][k].image}">
                    ${data['logo_data'][k].logo_name} </option>
                        `
                    }

                    html += `
                        </select>
                        </div>
                        <div class="dept-name-patch-holder">
                            <legend>Dept Name</legend>
                            <label for="deptNamePatch"><label>
                            <select title="deptNamePatch" name="deptNamePatch" id="deptNamePatch">
                                <option value='No Dept Name'>No Dept Name</option>
                                <option value='Below Logo' selected>Below Logo</option>
                                <option value='Left Sleeve'>Left Sleeve</option>
                                <option value='Back of Hat' class='hatback'>No Dept Name</option>
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
        function updateColorImage(val) {
            // console.log(val)
            var el = document.getElementById(val);
            var hexVal = el.dataset.hex;
            var rgbColor = hexToRgb(hexVal);
            var imageHolder = document.getElementById('product-image-holder')
            imageHolder.style.boxShadow = `0px 0px 25px 1px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;

        }

        // function to update the product image based on the color selected
        function updateProductImage(val) {
            updateColorImage(val);
            var productCode = document.getElementById('product-name').innerText;
            var productImage = document.querySelector('.product-image');
            var newProductImage = formatColorValueForUrl(val) + '_' + productCode + '.jpg';
            productImage.src = './product-images/' + newProductImage.toLowerCase();
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
            var select = document.getElementById('deptNamePatch')
            select.attributes.add('disabled')
        }
    </script>
</head>


<body>
    <?php include "./components/slider.php" ?>
    <div class="spacer23"> - </div>
    <div class=" container">

        <!-- // ? This is the div where the product name and code are rendered. Values are also used in some image update functions  -->
        <div class="product-name-holder-stretched" id="product-name-holder"></div>
        <div class="another-container" id="another-container">
            <!-- // ? this is where the product image gets rendered -->
            <span class='product-image-holder' id='product-image-holder'></span>

            <div class="details-about-details">
                <!--//? This is the div where the submit form is rendered -->
                <div id='new-options-form'></div>

            </div>
            <!-- // ?This is the div where the summary of the users selection is rendered -->
            <div class="select-summary" id="select-summary"></div>
        </div>
        <div class=" button-holder">
            <a href=<?php echo $_SESSION['GOBACK'] ?>><button class="btn btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                    Shopping </button></a>
            <!-- <button onclick="showCart()">My Cart</button> -->
            <!-- <button type="button" class="btn btn-secondary" id="toggle-button" onclick="toggleSlideout()">View Cart</button> -->
            <button type="submit" form="options" class="btn btn-primary custom-btn"><span><i class=" fa fa-cart-plus" aria-hidden="true"></i> Add to
                    Cart</span></button>
        </div>

        <!-- This was originaly intended to be used as a notification for a product being added to the cart - but the page reloads when something as added to the cart so it is useless... I am keeping in the code because I WILL find some use for these toast messages! -->
        <div id="myToast">
            <div class="toast-header">
                <i class="fa fa-info"></i>Price Change Alert
                <small>1 sec ago</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" onclick="eatToast()"></button>
            </div>
            <div class="toast-body">
                <p id="toast_message"></p>
            </div>
        </div>
        <!-- <div id='new-options-form'></div> -->
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
    <!-- <div class="cart-viewer theDevil" id="cart-viewer">
        </?php include "./viewCart.php" ?>
    </div> -->
</body>
<?php include "cartSlideout.php" ?>
<?php include "footer.php" ?>
<script>
    // function to make & show toast messags. No real use case for them... yet....
    // I should really move this its own file....
    function showToast(msg) {
        var toast = document.getElementById('myToast');
        var msgBlock = document.getElementById('toast_message');
        msgBlock.innerText = msg;
        toast.className = "show";
        setTimeout(function() {
            toast.className = toast.className.replace("show", "hideToast");
        }, 3000);
    }

    function eatToast() {
        // console.log('eating toast....')
        var toast = document.getElementById('myToast').classList.replace('show', 'eatToast');
    }

    function showHatBackText() {
        var deptNamePatch = document.querySelector('select[id="deptNamePatch"]');
        var hatBackText = document.querySelector('#hatBackText');
        deptNamePatch.addEventListener('change', function() {
            var selectedOption = deptNamePatch.options[deptNamePatch.selectedIndex];
            if (selectedOption.classList.contains('hatback')) {
                hatBackText.classList.remove('hidden');
                setStitchPrice();
                setProductPriceValue();
            } else {
                hatBackText.classList.add('hidden');
                resetStitchPrice();
                setProductPriceValue();
            }
        });
    }
    // showHatBackText();

    function showTruckerHatText() {
        var deptNamePatch = document.querySelector('select[id="deptNamePatch"]');
        var truckerHatText = document.querySelector('#truckerHatText');
        deptNamePatch.addEventListener('change', function() {
            var selectedOption = deptNamePatch.options[deptNamePatch.selectedIndex];
            if (selectedOption.classList.contains('truckerhattext')) {
                truckerHatText.classList.remove('hidden');
                setStitchPrice();
                setProductPriceValue();
            } else {
                truckerHatText.classList.add('hidden');
                resetStitchPrice();
                setProductPriceValue();
            }
        });
    }
    // showTruckerHatText();
    // This function is no longer needed with the pricing structures changes but is included in lots of logic so we keep it in place for now.... but value is set to $0.00 (06-12-2023: 1210 hrs)
    // UPDATE: The stitchCharge will now e $5.00 when the Dept Name is stitched on the left sleve. Otherwise it is $0.00. (06-12-2023: 1443 hours)
    function setStitchPrice() {
        // var deptNamePatch = document.querySelector('select[id="deptNamePatch"]');
        var selectedLocation = document.getElementById('deptNamePatch').value;
        var stitchCharge = document.getElementById('stitchCharge').value;
        // console.log('location is: ', selectedLocation);
        if (stitchCharge == 0 && selectedLocation == 'Left Sleeve') {
            stitchCharge = parseInt(5.00);
            document.getElementById('stitchCharge').value = parseFloat(stitchCharge).toFixed(2).replace(
                /\d(?=(\d{3})+\.)/g, '$&,');
            showToast('This option added $5.00 to your cost');
        } else {
            return
        }
    }

    // same comment as above. Leaving inplace for now...the function may be called but will never execute past the third line since it should NEVER evaluate to true as of right now
    function resetStitchPrice() {
        var selectedLocation = document.getElementById('deptNamePatch').value;
        var stitchCharge = document.getElementById('stitchCharge').value;
        if (stitchCharge == 5.00 && selectedLocation != 'Left Sleeve') {
            stitchCharge = parseInt(0.00);
            document.getElementById('stitchCharge').value = parseFloat(stitchCharge).toFixed(2).replace(
                /\d(?=(\d{3})+\.)/g, '$&,');
            // console.log(' reset stitch charge to : ' + stitchCharge)
        }
    }
    const logoImg = document.getElementById('logo');

    // logoImg.addEventListener('change', function() {
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


    function handleDeptNamePatch() {
        const selectElement = document.getElementById('deptNamePatch');
        const selectedValue = selectElement.value;
        //console.log("Dept Name Selected Value is: ", selectedValue);

        const imageElement = document.getElementById('lil-logo');
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
                    break;

                case 'Below Logo':
                    //console.log('Below Logo')
                    const fileNameWithoutNo = removeNoFromFileName(fileName);
                    const newSrcWithoutNo = oldSrc.replace(fileName, fileNameWithoutNo);
                    imageElement.src = newSrcWithoutNo;
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
        grid-template-columns: 40% 30% 30%;
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
        margin-left: 20px;
        margin-right: 20px;
    }

    .button-holder {
        margin-top: 5px;
        display: flex;
        justify-content: space-between;
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
        background-color: #bada55;
        color: black;
        display: flex;
        justify-content: space-between;
        padding: 10px;
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
        z-index: 1;
        -webkit-box-shadow: 0px 0px 18px 10px rgba(255, 255, 255, 1), inset 0px 0px 18px 10px rgba(0, 0, 0, 1);
        -moz-box-shadow: 0px 0px 18px 10px rgba(255, 255, 255, 1), inset 0px 0px 18px 10px rgba(0, 0, 0, 1);
        box-shadow: 0px 0px 18px 10px rgba(255, 255, 255, 1), inset 0px 0px 18px 10px rgba(0, 0, 0, 1);
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

        img {
            height: 200px;
            margin-left: auto;
            margin-right: auto;
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
</style>