<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 08/09/2024
Purpose: View all the details about a single product to include available colors and sizes as well as prices for each. Users can select sizes, colors, quantities of this product and add to their cart as desired. 
Includes:   config.php for database connection (moving to js fetch), footer.php, viewHead.php
NOTES: this file started at 1284 lines before refactor
*/
session_start();
if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}

include_once 'Cart.class.php';
$cart = new Cart;

$product_id = $_REQUEST['product_id'];
include "./components/viewHead.php"
?>
<script src="./functions/isThisFiscalYear.js"></script>
<script src="./functions/renderProductDetails.js"></script>
<link href="./style/customProductDetails.css" rel="stylesheet" />
<script>
function removeCommasAndSpaces(str) {
    return str.replace(/,/g, '').replace(/\s/g, '');
}


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
    const transition = document.startViewTransition(() => {


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
    })
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
    const transition = document.startViewTransition(() => {


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
    });
}

function showMessageAndRedirect() {
    var errorPopover = document.getElementById('errorMessage');
    errorPopover.showPopover();
}

function fetchProductData(id) {
    fetch('fetchProductDetails.php?id=' + <?php echo $product_id ?>)
        .then((response) => response.json())
        .then((data) => {
            console.log(data['product_data'][0].product_type);
            if (data['product_data'][0].product_type === "0") {
                showMessageAndRedirect()
            } else {
                renderProductDetails(data);
                getCurrentProductPrice();
                matchHeights();
                updateColorImage(data['color_data'][0].color);
                updateCurrentQty();
                calculateSubTotal();
            }
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

    imageHolder.style.boxShadow = `0px 0px 15px -5px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;
    toastHolder.style.boxShadow = `0px 0px 15px 1px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;
    // container.style.boxShadow = `0px 0px 55px -25px rgba(${rgbColor.r},${rgbColor.g},${rgbColor.b},0.75)`;

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

function backToIndex() {
    window.location.replace('index.php')
}
</script>
</head>


<body class="body">
    <div class="container" id="container">
        <!-- // ? This is the div where the product name and code are rendered. Values are also used in some image update functions  -->
        <div class="product-name-holder" id="product-name-holder"></div>
        <div class="another-container" id="another-container">
            <!-- // ? this is where the product image gets rendered -->



            <div class="details-about-details">
                <!--//? This is the div where the submit form is rendered -->
                <div id='new-options-form'></div>
            </div>
            <span class='product-image-holder' id='product-image-holder'
                view-transition-group="image-transition"></span>
            <!-- // ?This is the div where the summary of the users selection is rendered -->
            <div class="select-summary" id="select-summary"></div>
        </div>
        <div class="button-holder">
            <a href=<?php echo $_SESSION['GOBACK'] ?>><button class="btn btn-success" type="button"> Continue
                    Shopping </button></a>
            <button type="submit" form="options" class="btn btn-primary"><span> Add to Cart
                </span></button>
        </div>


        <!-- This was originaly intended to be used as a notification for a product being added to the cart - but the page reloads when something as added to the cart so it is useless... I am keeping in the code because I WILL find some use for these toast messages! -->
        <div id="myToast">
            <div class="toast-header">
                ⚠️ Price Change Alert
                <small>1 sec ago</small>
                <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" onclick="eatToast()"></button>
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
        <button class="btn-close ms-2 mb-1" popovertarget="safetyProductsPopover" popovertargetaction="hide">
            <span aria-hidden=”true”></span>
            <span class="sr-only">Close</span>
        </button>
        <p>This has a minimum order quantity of 24. Currently the only department approved for ordering this product is
            Stormwater / Roads & Bridges. If interested in this product please contact store@berkeleycountysc.gov for
            more information.</p>
    </div>
    <div id="errorMessage" class="errorMessage" popover=manual>
        <button class="btn-close ms-2 mb-1" popovertarget="errorMessage" onclick="backToIndex()">
            <span aria-hidden=”true”></span>
            <span class="sr-only"></span>
        </button>
        <p id="errorMessageText">Sorry this product is no longer available</p>
    </div>
</body>

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