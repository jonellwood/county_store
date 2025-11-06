<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 08/13/2024
Purpose: View boots. They are special so they get their own file.
Includes:   config.php for database connection (moving to js fetch) cartSlideout.php, footer.php, viewHead.php, slider.php
NOTES: this file started at 1284 lines before refactor
*/
session_start();
if ($_SESSION['GOBACK'] == '') {
    $_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
}

include_once 'Cart.class.php';
include_once './components/viewHead.php';
$cart = new Cart;

$product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 105;
?>
<link href="./style/global-variables.css" rel="stylesheet" />
<link href="./style/product-details-modern.css" rel="stylesheet" />
<script src="./functions/isThisFiscalYear.js"></script>
<script src="./functions/renderProductDetails-modern.js"></script>

<script>
    // ===================================
    // UTILITY FUNCTIONS
    // ===================================

    function getCartTotal() {
        var storeCart = <?php echo $cart->serializeCart(); ?>;
        if (storeCart) {
            return storeCart;
        }
        return;
    }

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
        var priceInSummary = document.getElementById('productPrice');
        var priceInSummaryHolder = document.getElementById('price-in-summary');
        if (priceInSummary && priceInSummaryHolder) {
            priceInSummaryHolder.textContent = makeDollar((priceInSummary.value * getCurrentQty()))
        }
    }

    function calculateSubTotal() {
        var subTotalInSummary = document.getElementById('sub-in-summary')
        var logoFeeInSummary = document.getElementById('logo-fee-in-summary')
        var priceInSummary = document.getElementById('price-in-summary')

        if (subTotalInSummary && logoFeeInSummary && priceInSummary) {
            var subTotal = ((unDollar(logoFeeInSummary.textContent) + (unDollar(priceInSummary.textContent))))
            subTotalInSummary.textContent = makeDollar(subTotal);
        }
    }

    function calculateTax() {
        var subTotalInSummary = document.getElementById('sub-in-summary');
        var taxInSummary = document.getElementById('tax-in-summary');

        if (subTotalInSummary && taxInSummary) {
            var taxRate = .09;
            var tax = (unDollar(subTotalInSummary.textContent) * taxRate);
            taxInSummary.textContent = makeDollar(tax);
        }
    }

    function calculateSelectedTotal() {
        var totalInSummary = document.getElementById('total-in-summary');
        var subTotalInSummary = document.getElementById('sub-in-summary');
        var taxInSummary = document.getElementById('tax-in-summary');

        if (totalInSummary && subTotalInSummary && taxInSummary) {
            var selectedTotal = (unDollar(subTotalInSummary.textContent) + unDollar(taxInSummary.textContent))
            totalInSummary.textContent = makeDollar(selectedTotal)
        }
    }

    function calculateNewTotal() {
        var newTotalInSummary = document.getElementById('new-total-in-summary');
        var totalInSummary = document.getElementById('total-in-summary');

        if (newTotalInSummary && totalInSummary) {
            var currentCart = getCartTotal();
            if (currentCart && currentCart.cart_total !== undefined) {
                var newTotal = unDollar(totalInSummary.textContent) + parseFloat(currentCart.cart_total);
                newTotalInSummary.textContent = makeDollar(newTotal);
            }
        }
    }

    function updateCurrentQty() {
        updateCurrentPrice();
        calculateSubTotal();
        calculateTax();
        calculateSelectedTotal();
        calculateNewTotal();
    }

    function validateForm() {
        return true;
    }

    function setFiscalYear() {
        newFiscalYear = fiscalYear()
        fyStart = newFiscalYear[0];
        fyEnd = newFiscalYear[1];
        thisFiscalYear = (fyStart + fyEnd);
        return thisFiscalYear;
    }

    function backToIndex() {
        window.location.href = '<?php echo $_SESSION['GOBACK']; ?>';
    }

    // Toast functionality
    let toastTimeout = null;

    function showToast(msg) {
        console.log('showToast called with:', msg);

        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        var toast = document.getElementById('myToast');
        var msgBlock = document.getElementById('price_toast_message');

        if (toast && msgBlock) {
            toast.className = "toast-notification";

            setTimeout(function() {
                msgBlock.innerText = msg;
                toast.className = "toast-notification show";

                toastTimeout = setTimeout(function() {
                    eatToast();
                }, 3000);
            }, 50);
        }
    }

    function eatToast() {
        var toast = document.getElementById('myToast');
        if (toast) {
            toast.classList.remove('show');
            toast.classList.add('eatToast');

            if (toastTimeout) {
                clearTimeout(toastTimeout);
                toastTimeout = null;
            }

            setTimeout(function() {
                toast.classList.remove('eatToast');
            }, 500);
        }
    }
</script>

<body class="product-details-page">

    <div class="product-details-container" id="container">

        <!-- LEFT COLUMN: Product Image & Info -->
        <div class="product-image-card">
            <div class="product-title-section">
                <h1 class="product-title" id="product-name">Loading...</h1>
                <span class="product-code" id="product-code-display">...</span>
            </div>

            <div class="product-image-wrapper">
                <img
                    id="product-image"
                    class="product-image-main"
                    src="../../product-images/placeholder.png"
                    alt="Product Image"
                    loading="lazy" />
            </div>

            <!-- Selection Summary - Desktop -->
            <div class="selection-summary-card desktop-summary">
                <h3 class="summary-title">Selection Summary</h3>

                <div class="summary-row">
                    <span class="summary-label">Current Cart</span>
                    <span class="summary-value" id="cart-subtotal-display">$0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Item Price</span>
                    <span class="summary-value" id="price-in-summary">$0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Logo Fee</span>
                    <span class="summary-value" id="logo-fee-in-summary">$0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value" id="sub-in-summary">$0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Tax (9%)</span>
                    <span class="summary-value" id="tax-in-summary">$0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">This Item Total</span>
                    <span class="summary-value" id="total-in-summary">$0.00</span>
                </div>

                <div class="summary-row summary-total">
                    <span class="summary-label">New Cart Total</span>
                    <span class="summary-value" id="new-total-in-summary">$0.00</span>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Product Options -->
        <div class="product-options-card">
            <form action="./cartAction.php" method="POST" id="product-form" onsubmit="return validateForm()">

                <!-- Hidden Inputs -->
                <input type="hidden" name="action" value="addToCart">
                <input type="hidden" name="product_id" id="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="code" id="product_code">
                <input type="hidden" name="name" id="product_name_hidden">
                <input type="hidden" name="productPrice" id="productPrice">
                <input type="hidden" name="size-price-id" id="price_id">
                <input type="hidden" name="hidden_color_id" id="color_id">
                <input type="hidden" name="color_name" id="color_name">
                <input type="hidden" name="size_id" id="size_id">
                <input type="hidden" name="size_name" id="size_name">
                <input type="hidden" name="logo" id="logo_id_legacy">
                <input type="hidden" name="logo-url" id="logo-url">
                <input type="hidden" name="logo_id" id="logo_id">
                <input type="hidden" name="logoCharge" id="logoFee" value="5">
                <input type="hidden" name="logo_upCharge" id="logo_upCharge" value="0">
                <input type="hidden" name="image-url" id="image-url">
                <input type="hidden" name="deptPatchPlace" id="deptPatchPlace-hidden">
                <input type="hidden" name="fy" id="fy">

                <!-- Color Selection -->
                <div class="option-section">
                    <label class="option-label">Pick a Color</label>
                    <div class="color-selector-wrapper" id="color-options">
                        <!-- Colors will be populated by JavaScript -->
                        <div class="skeleton-loader" style="height: 50px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Size Selection -->
                <div class="option-section">
                    <label class="option-label">Pick a Size</label>
                    <div class="size-selector-wrapper" id="size-options">
                        <!-- Sizes will be populated by JavaScript -->
                        <div class="skeleton-loader" style="height: 80px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="option-section">
                    <label class="option-label" for="itemQuantity">Quantity</label>
                    <div class="quantity-wrapper">
                        <input
                            type="number"
                            class="quantity-input"
                            id="itemQuantity"
                            name="itemQuantity"
                            min="1"
                            max="99"
                            value="1"
                            onchange="updateCurrentQty()" />
                    </div>
                </div>

                <!-- Action Buttons (Desktop) -->
                <div class="action-buttons">
                    <button type="button" class="btn-modern btn-secondary-modern" onclick="backToIndex()">
                        ← Continue Shopping
                    </button>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        Add to Cart 🛒
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- Mobile Sticky Summary Bar -->
    <div class="mobile-sticky-summary">
        <div class="mobile-summary-content">
            <div class="mobile-summary-info">
                <div class="mobile-summary-price" id="mobile-total">$0.00</div>
                <div class="mobile-summary-label">Total with Tax</div>
            </div>
            <div class="mobile-summary-button">
                <button
                    type="submit"
                    form="product-form"
                    class="btn-modern btn-primary-modern"
                    style="min-width: 140px;">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set fiscal year
            document.getElementById('fy').value = setFiscalYear();

            // Fetch and render product details
            fetchProductData(<?php echo $product_id; ?>);

            // Update mobile summary when values change
            function updateMobileSummary() {
                const total = document.getElementById('total-in-summary').textContent;
                document.getElementById('mobile-total').textContent = total;
            }

            // Add observers to update mobile summary
            const observer = new MutationObserver(updateMobileSummary);
            observer.observe(document.getElementById('total-in-summary'), {
                childList: true,
                characterData: true,
                subtree: true
            });
        });
    </script>

    <!-- Toast Notification -->
    <div id="myToast" class="toast-notification">
        <span class="toast-icon">💰</span>
        <div class="toast-content">
            <div class="toast-title">Price Updated</div>
            <span id="price_toast_message"></span>
        </div>
        <button class="toast-close" onclick="eatToast()" aria-label="Close notification">×</button>
    </div>

</body>

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
</script>

</html>