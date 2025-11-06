<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: October 2025
Purpose: MODERN REDESIGN - Product details page with updated styling
NOTE: This is a TEST page for the style refactor. Will replace product-details.php once approved.
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
        console.log('Cart from PHP session:', storeCart);
        if (storeCart) {
            return storeCart;
        }
        return {
            cart_total: 0,
            total_items: 0,
            total_logo_fees: 0
        };
    }

    function removeCommasAndSpaces(str) {
        return str.replace(/,/g, '').replace(/\s/g, '');
    }

    function formatColorValueForUrl(str) {
        var noSpaces = str.replace(/[\s/]/g, '');
        var lowercaseString = noSpaces.toLowerCase();
        return lowercaseString;
    }

    function setFiscalYear() {
        newFiscalYear = fiscalYear()
        fyStart = newFiscalYear[0];
        fyEnd = newFiscalYear[1];
        thisFiscalYear = (fyStart + fyEnd);
        return thisFiscalYear;
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
        if (hiddenPriceInput && priceDisplay) {
            priceDisplay.textContent = makeDollar(hiddenPriceInput.value);
        }
    }

    function updateHiddenPriceInput(radio) {
        const transition = document.startViewTransition(() => {
            var productPrice = document.getElementById('productPrice')
            productPrice.value = radio.value
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
            // Calculate current cart total including logo fees and tax (same as updateCartSummary)
            var cart = getCartTotal();
            var subtotalWithFees = (cart.cart_total || 0) + (cart.total_logo_fees || 0);
            var cartTotalWithTax = subtotalWithFees * 1.09;

            // Add this item's total to get new cart total
            newTotal = (cartTotalWithTax + unDollar(totalInSummary.textContent));
            newTotalInSummary.textContent = makeDollar(newTotal);
        }
    }

    function updateLogoImage(val) {
        var logoFormInput = document.getElementById('logo-url');
        var hiddenLogoIdInput = document.getElementById('logo_id');

        // Get the selected option from the logo selector
        var logoSelector = document.getElementById('logo-selector');
        var selectedOption = logoSelector.options[logoSelector.selectedIndex];

        if (!selectedOption || !selectedOption.dataset.url) {
            console.warn('No logo selected or logo data not found');
            return;
        }

        var selectedLogoUrl = selectedOption.dataset.url;
        var selectedLogoName = selectedOption.dataset.name;

        // Update form inputs only (no image preview in modern design)
        logoFormInput.value = selectedLogoUrl;
        hiddenLogoIdInput.value = val;

        console.log('Logo updated:', selectedLogoName, 'ID:', val);
    }

    function updateLogoFeeAddOn(val) {
        console.log('updateLogoFeeAddOn called with:', val);
        var logoFeeUpChargeHiddenInput = document.getElementById('logo_upCharge')
        console.log('logo_upCharge input:', logoFeeUpChargeHiddenInput);
        if (!logoFeeUpChargeHiddenInput) {
            console.error('logo_upCharge hidden input not found!');
            return;
        }

        if (val === 'Left Sleeve') {
            logoFeeUpChargeHiddenInput.value = 5.00
            console.log('Set logo_upCharge to 5.00');
            showToast('This option added $5.00 per item to your cost')
            if (typeof handleDeptNamePatch === 'function') {
                handleDeptNamePatch()
            }
        } else if (val === 'No Dept Name') {
            logoFeeUpChargeHiddenInput.value = 0.00
            console.log('Set logo_upCharge to 0.00 (No Dept Name)');
            if (typeof handleDeptNamePatch === 'function') {
                handleDeptNamePatch()
            }
        } else {
            logoFeeUpChargeHiddenInput.value = 0.00
            console.log('Set logo_upCharge to 0.00 (default)');
            if (typeof handleDeptNamePatch === 'function') {
                handleDeptNamePatch()
            }
        }
        updateCurrentQty();
    }

    function updateDeptPatchHidden(val) {
        var hiddenDeptInput = document.getElementById('deptPatchPlace-hidden');
        if (hiddenDeptInput) {
            hiddenDeptInput.value = val;
        }
    }

    function updateCurrentQty() {
        const transition = document.startViewTransition(() => {
            var qtyInSummary = document.getElementById('qty-in-summary')
            var logoFeeInSummary = document.getElementById('logo-fee-in-summary')
            var logoUpCharge = document.getElementById('logo_upCharge');
            var currentQty = getCurrentQty();

            if (qtyInSummary) {
                qtyInSummary.textContent = currentQty;
            }

            if (logoFeeInSummary && logoUpCharge) {
                var totalLogoFee = (5 + parseInt(logoUpCharge.value));
                logoFeeInSummary.textContent = makeDollar((currentQty * totalLogoFee))
                console.log('Updated logo fee:', {
                    baseLogFee: 5,
                    upCharge: logoUpCharge.value,
                    totalLogoFee: totalLogoFee,
                    quantity: currentQty,
                    displayed: makeDollar((currentQty * totalLogoFee))
                });
            }

            updateCurrentPrice();
            calculateSubTotal();
            calculateTax();
            calculateSelectedTotal();
            calculateNewTotal();
        });
    }

    function updateColor(elementId) {
        var colorIdInput = document.getElementById('color_id');
        var colorElement = document.getElementById(elementId);
        if (colorElement && colorElement.dataset.colorId) {
            colorIdInput.value = colorElement.dataset.colorId;
        }
    }

    // Toast notification functions
    let toastTimeout = null;

    function showToast(msg) {
        console.log('showToast called with:', msg);

        // Clear any existing timeout
        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        var toast = document.getElementById('myToast');
        var msgBlock = document.getElementById('price_toast_message');
        console.log('Toast element:', toast);
        console.log('Message block:', msgBlock);

        if (toast && msgBlock) {
            // First hide any existing toast
            toast.className = "toast-notification";

            // Small delay to allow CSS transition to reset
            setTimeout(function() {
                msgBlock.innerText = msg;
                toast.className = "toast-notification show";
                console.log('Toast should be visible now');

                // Auto-hide after 3 seconds
                toastTimeout = setTimeout(function() {
                    eatToast();
                }, 3000);
            }, 50);
        } else {
            console.error('Toast elements not found!', {
                toast,
                msgBlock
            });
        }
    }

    function eatToast() {
        var toast = document.getElementById('myToast');
        if (toast) {
            toast.classList.remove('show');
            toast.classList.add('eatToast');

            // Clear the timeout if user manually closes
            if (toastTimeout) {
                clearTimeout(toastTimeout);
                toastTimeout = null;
            }

            // Reset classes after animation completes
            setTimeout(function() {
                toast.classList.remove('eatToast');
            }, 500);
        }
    }

    // Helper functions for logo file naming
    function addNoToFileName(filename) {
        if (filename.includes('_NO')) {
            return filename;
        } else {
            const dotIndex = filename.lastIndexOf('.');
            const baseName = filename.substring(0, dotIndex);
            const extension = filename.substring(dotIndex);
            return baseName + '_NO' + extension;
        }
    }

    function removeNoFromFileName(filename) {
        return filename.replace('_NO', '');
    }

    function convertAbsToRel(url) {
        const urlObj = new URL(url);
        return urlObj.pathname.substring(1);
    }

    // Handle department name patch logo changes
    function handleDeptNamePatch() {
        const selectElement = document.getElementById('deptPatchPlace');
        const imageElement = document.getElementById('logo-img-in-summary');
        const imageUrlHiddenInput = document.getElementById('logo-url');

        if (!selectElement || !imageElement || !imageUrlHiddenInput) return;

        const selectedValue = selectElement.value;

        if (imageElement.complete && imageElement.src) {
            const oldSrc = imageElement.src;
            const fileName = oldSrc.split('/').pop();

            switch (selectedValue) {
                case 'No Dept Name':
                case 'Back of Hat':
                case 'Left Sleeve':
                    const newFileName = addNoToFileName(fileName);
                    const newSrc = oldSrc.replace(fileName, newFileName);
                    imageElement.src = newSrc;
                    imageUrlHiddenInput.value = convertAbsToRel(newSrc)
                    break;

                case 'Below Logo':
                    const fileNameWithoutNo = removeNoFromFileName(fileName);
                    const newSrcWithoutNo = oldSrc.replace(fileName, fileNameWithoutNo);
                    imageElement.src = newSrcWithoutNo;
                    imageUrlHiddenInput.value = convertAbsToRel(newSrcWithoutNo)
                    break;

                default:
                    break;
            }
        }
    }

    function validateForm() {
        console.log('=== FORM VALIDATION ===');

        const requiredFields = {
            'product_code': 'code',
            'product_name_hidden': 'name',
            'productPrice': 'productPrice',
            'price_id': 'size-price-id',
            'color_id': 'hidden_color_id',
            'size_id': 'size_id',
            'logo_id': 'logo_id',
            'logo-url': 'logo-url',
            'image-url': 'image-url',
            'deptPatchPlace-hidden': 'deptPatchPlace'
        };

        let allValid = true;

        for (const [id, name] of Object.entries(requiredFields)) {
            const field = document.getElementById(id);
            if (!field) {
                console.error(`Missing field with id="${id}"`);
                allValid = false;
            } else if (!field.value || field.value.trim() === '') {
                console.error(`Empty value for ${name} (id="${id}")`);
                allValid = false;
            } else {
                console.log(`✓ ${name}: ${field.value}`);
            }
        }

        if (!allValid) {
            alert('Please select all required options (color, size, logo)');
            return false;
        }

        console.log('=== FORM VALID - SUBMITTING ===');
        return true;
    }

    function formatColorValueForUrl(str) {
        return str.replace(/[^a-zA-Z]/g, '').toLowerCase();
    }

    function hexToRgb(hex) {
        hex = hex.replace(/^#/, '');
        if (hex.length === 3) {
            hex = hex.split('').map(char => char + char).join('');
        }
        const bigint = parseInt(hex, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `${r}, ${g}, ${b}`;
    }

    function updateColorImage(val) {
        var colorNameInput = document.getElementById('color_name')
        var selectedColorName = document.getElementById(val).dataset.name
        var selectedColorHex = document.getElementById(val).dataset.hex
        var productImage = document.getElementById('product-image')
        var productImageWrapper = document.querySelector('.product-image-wrapper')
        colorNameInput.value = selectedColorName
        updateColor(val)
        updateProductImage(val)

        // Add subtle color glow effect
        if (selectedColorHex) {
            const rgb = hexToRgb(selectedColorHex);
            productImageWrapper.style.boxShadow = `0 0 30px rgba(${rgb}, 0.3)`;
        }
    }

    function updateProductImage(val) {
        var productImage = document.getElementById('product-image')
        var productCode = document.getElementById('product_code').value.toLowerCase()
        var selectedColorName = document.getElementById(val).dataset.name
        var formattedColor = formatColorValueForUrl(selectedColorName)
        productImage.src = `../../product-images/${formattedColor}_${productCode}.jpg`
    }

    function backToIndex() {
        window.location.href = '../../index.php';
    }
</script>
</head>

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

                <!-- Logo Selection -->
                <div class="option-section">
                    <label class="option-label" for="logo-selector">Pick a Logo</label>
                    <select
                        class="logo-selector-dropdown"
                        id="logo-selector"
                        onchange="updateLogoImage(this.value); updateLogoFeeAddOn(this.value);">
                        <option value="">Select Logo...</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>

                <!-- Department Name Selection -->
                <div class="option-section">
                    <label class="option-label" for="deptPatchPlace">Department Name</label>
                    <select
                        title="deptPatchPlace"
                        id="deptPatchPlace"
                        class="dept-selector-dropdown"
                        onchange="updateLogoFeeAddOn(this.value); updateDeptPatchHidden(this.value)">
                        <option value="No Dept Name" id="p1">No Dept Name</option>
                        <option value="Below Logo" selected id="p2">Below Logo</option>
                        <option value="Left Sleeve" id="p3">Left Sleeve</option>
                    </select>
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

    <?php include "footer.php" ?>