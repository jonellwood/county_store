<?php
session_start();
// if ($_SESSION['GOBACK'] == '') {
$_SESSION['GOBACK'] = $_SERVER['HTTP_REFERER'];
// }
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;

$product_id = $_REQUEST['product_id'];
// $product_id = 150;
// get product details
$sql = "SELECT p.product_id, p.code, p.name, p.image, p.price, p.description, p.producttype, p.price_size_mod, p.vendor_id,
c.color_id, s.size_id
from products p 
INNER JOIN (SELECT * from products_colors) c on p.product_id=c.product_id 
INNER JOIN (SELECT * from colors) c2 on c.color_id=c2.color_id
INNER JOIN (SELECT * from products_sizes) s on p.product_id=s.product_id
JOIN (SELECT * from sizes) s2 on s.size_id=s2.size_id 
WHERE p.product_id=$product_id
GROUP BY p.product_id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// this is a hacky way of getting the price_mod rate (i.e. the price increase for each size) to apply to the specific product the user us viewing. We are basically grabbing all the values - including $0.00 - and throwing them in array to be referenced as the user changes the size of the product in the dropdown. IF NEW SIZES ARE ADDED TO THE DATABASE add them to this sketchy ass function and make sure the new sizes are reflected in the view this is referencing.
$smod = array();
$smodSql = "SELECT 
        xs_inc AS '10',
        s_inc AS '1',
        m_inc AS '2',
        l_inc AS '3',
        xl_inc AS '4',
        xxl_inc AS '5',
        xxxl_inc AS '6',
        xxxxl_inc AS '7',
        xxxxxl_inc AS '8',
        xxxxxxl_inc AS '11',
        lt_inc AS '12',
        xlt_inc AS '13',
        xxlt_inc AS '14',
        xxxlt_inc AS '15',
        xxxxlt_inc AS '16',
        xxxxxxxl_inc AS '18',
        xxxxxxxxl_inc AS '19',
        xxxxxxxxxl_inc AS '20',
        xxxxxxxxxxl_inc AS '21',
        na_inc AS '9' 
        from prod_size_mod_ref WHERE product_id = $product_id";
$smodStmt = $conn->prepare($smodSql);
$smodStmt->execute();
$smodResult = $smodStmt->get_result();
if ($smodResult->num_rows > 0) {
    while ($smodRow = $smodResult->fetch_assoc()) {
        $smod[] = $smodRow;
    }
}
// echo "<pre>";
// var_dump($smod);
// echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./components/viewHead.php" ?>
    <title>Product Details</title>
    <script>
    // Does anyone who would have access to read this code really need a comment to explain what the function does?
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
    // the function updateColorImage takes the value from the above function and replaces the img src and the alt tag with its value
    function updateColorImage(val) {
        i = document.getElementById('color_id').value;
        newi = val;
        document.getElementById('square').src = './color-images/' + formatColorValueForUrl(val) + '.gif';
        document.getElementById('square').alt = 'Color swatch: ' + val;
    }

    // function to update the product image based on the color selected
    function updateProductImage(val) {
        var productCode = document.getElementById('product-name').innerText;
        var productImage = document.querySelector('.product-image');
        // var productCode = <?php echo 112; ?>;

        // var newProductImage = productCode + '_' + formatColorValueForUrl(val) + '.jpg';
        var newProductImage = formatColorValueForUrl(val) + '_' + productCode + '.jpg';
        console.log('newProductImage is: ', newProductImage);
        productImage.src = './product-images/' + newProductImage;
    }

    // The updateSize() function follows the same logic as the updateColor() function, except it gets the existing size value from the size_id element and sets the new size value to the size_id element which is included in the form action.
    function updateSize(val) {
        s = document.getElementById('size').value;
        // console.log('s in set function is: ' + s);
        const myArray = s.split(",");
        // news = val;
        document.getElementById("size").value = myArray[1];

    }
    // this function named setVals() calls two functions, updateSize(...) and updateColor(...). These function calls will set the size to 'Small' and the color to 'Black' to provide default values for the dropdowns. The function does not return any value; it simply performs the desired actions.
    function setVals() {
        updateSize('Small');
        updateColor('Black');
    }

    // TODO the first for loop says "i < 23" which (i think) was used becuase that was the number of price mods there were. We now have like 20. Update this to something more like priceMods.length. (UPDATED VALUE TO 20 UNTIL I CAN SORT OUT BEST WAY TO DO THIS BUT NEED TO MOVE FORWARD FOR NOW)
    function getModPrice(price, size) {
        var priceMods = <?php echo json_encode($smod); ?>;
        let priceMod = priceMods[0];
        let priceArray = [];
        priceArray.push(0.00);

        for (let i = 1; i < 23; i++) {
            let newPrice = parseFloat(priceMod[i]) + parseFloat(price);
            priceArray.push(newPrice);
        }
        newPrice = priceArray[size];
        return newPrice;
    }
    // This function calculates the final price of an item based on size and price modifier values. It uses a lookup table to retrieve the corresponding price change value for each combination of size and price modifier, and then adds this to the base price to get the final price. The final price is then set as innerHTML in a DOM element with an ID of "temp-price-holder". The calculated price is also logged to the console for now.
    function setPrice(size, price, price_mod) {
        var newPrice = getModPrice(price, size);

        document.getElementById("price-text-holder").innerHTML = "Updated Price is: $";
        document.getElementById("price-holder").innerHTML = parseFloat(newPrice).toFixed(2).replace(/\d(?=(\d{3})+\.)/g,
                '$&,') +
            ' USD';
        document.getElementById("productCharge").value = parseFloat(newPrice).toFixed(2).replace(/\d(?=(\d{3})+\.)/g,
            '$&,');
        var updatedPrice = document.getElementById('productCharge').value;
        var currentLogo = document.getElementById('logoCharge').value;
        logoPriceIncrease(currentLogo, updatedPrice);
        setProductPriceValue();
        setStitchPrice();
    }

    function logoPriceIncrease(price) {
        // set value of productPrice to initial product value.
        var productId = <?php echo $product_id ?>;
        console.log('productId is: ', productId);
        if (productId == 185) {
            newLogoCharge = parseFloat(0.00);
        } else {
            newLogoCharge = parseFloat(5.00);
        }
        setProductPriceValue();
    }

    function changeSizeVal(val) {
        setPrice(val, price, priceMod);
        var logo = document.getElementById('logo').value;
    }

    function unhideLogo() {
        const logoImgHolder = document.getElementById('logo-img-holder');
        logoImgHolder.classList.add('logo-show');

        setTimeout(function() {
            logoImgHolder.classList.remove('logo-show');
            logoImgHolder.classList.add('logo-hide');
        }, 3000);
    };

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
        console.log(target)
        console.log(popovertarget)
        popovertarget.showPopover()
    }

    function isSafetyProducts() {
        const itemQuantity = document.getElementById('itemQuantity')
        itemQuantity.min = 24;
        itemQuantity.value = 24;
        showPopover('safetyProductsPopover')
    }

    function onLoadLoadThis() {
        setProductPriceValue();
        <?php if ($product_id == 33) : ?>
        updateProductImage('black');
        <?php elseif ($product_id == 191) : ?>
        updateProductImage('blackblack');
        <?php elseif ($product_id == 211) : ?>
        updateProductImage('biscuittrueblue');
        <?php elseif ($product_id == 185) : ?>
        changeLogo('dept_logos/sw-rnb.png');
        isSafetyProducts('safetyProductsPopover');
        <?php elseif ($product_id == 105) : ?>
        forBootsView();
        <?php else : ?>
        changeLogo('dept_logos/Horiz_White_Dept.png');
        <?php endif; ?>
    }

    function disableSelectDepName() {
        var select = document.getElementById('deptNamePatch')
        select.attributes.add('disabled')
    }
    </script>
</head>

<body onload="onLoadLoadThis()">

    <div class="spacer23"> - </div>
    <div class=" container">

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $price_mod = $row['price_size_mod'];
                $size = $row['size_id'];
                $price = $row['price'];
                $code = $row['code'];
                $proImg = !empty($row["image"]) ? $row["image"] : "./product-images/demo-img.jpg";
                $vendor = $row['vendor_id'];
        ?>

        <div class="product-name-holder-stretched">
            <h3 class='name-code-holder'>
                <p id='product-name'> <?php echo $row['code'] ?> </p>
                <p> - </p>
                <p> <?php echo $row["name"] ?></p>
            </h3>
            <!-- <p class="spec-sheet-download-link"><a href="spec-sheets/<?php //echo $row['code'] 
                                                                                    ?>.pdf" download="<?php //echo $row['code'] 
                                                                                                        ?>.pdf"><b>Download Spec Sheet</b></a></p> -->

        </div>
        <div class="another-container">
            <!-- <img src="<?php echo $proImg; ?>" alt="<?php echo $row['name'] ?>" class="product-image"> -->
            <span class='product-image-holder'>
                <!-- <figure> -->
                <img src="product-images/<?php echo strtolower($code) ?>.jpg" alt="<?php echo $row['name'] ?>"
                    class="product-image">
                <img src="dept_logos/bc-circ.png" alt="..." class="lil-logo" id="lil-logo" />
                <!-- <figcaption>The product and logo are just two images on top of eachother to give you an idea of what
                        the product
                        will look like. If it looks a bit wonky it is the progammers fault, not the fault of the
                        product.</figcaption> -->
                <!-- </figure> -->
                <p class="product-description-text-holder"><?php echo $row['description'] ?></p>
            </span>

            <div class="details-about-details">
                <span>
                    <h6 class="card-subtitle mb-2"><span id="price-text-holder">Starting at: </span>
                        <span id="price-holder">
                            <?php echo CURRENCY_SYMBOL . number_format($row["price"], 2) . ' ' . CURRENCY; ?>
                        </span>
                    </h6>
                    <h6 class="card-subtitle mb-2">
                        <?php if ($vendor == 3) { ?>
                        <span id="logo-price-text-holder" class="hidden">Price includes a Logo fee of: $0.00</span>
                        <span id="logo-price-holder" class="hidden"></span>
                        <?php } else { ?>
                        <span id="logo-price-text-holder">Price includes a Logo fee of: $5.00</span>
                        <span id="logo-price-holder"></span>
                        <?php } ?>
                    </h6>
                </span>
                <div class="options-form-holder">
                    <form name="options" method="post" id="options" action="cartAction.php"
                        class='options-select-holder'>
                        <div id="color-picker-holder">
                            <legend>Product Color Details</legend>
                            <?php

                                    $sql2 = "SELECT colors.color, colors.color_id
                                        FROM uniform_orders.products_colors
                                        JOIN colors on colors.color_id=products_colors.color_id
                                        JOIN products on products.product_id=products_colors.product_id
                                        WHERE products.product_id = $product_id
                                        ORDER BY color ASC";
                                    $stmt2 = $conn->prepare($sql2);
                                    $stmt2->execute();
                                    $result2 = $stmt2->get_result();
                                    if ($result->num_rows > 0) {
                                        echo "<label for='color_id'>Choose a color:</label>";
                                        echo "<select title='color_id' name='color_id' id='color_id' onchange='updateProductImage(this.value)' >";
                                        while ($crow = $result2->fetch_assoc()) {
                                            echo "<option value='" . $crow['color'] . "'>" . $crow['color'] . "</option>";
                                        }
                                        echo "</select>";
                                    }
                                    ?>
                            <!-- whole bunch of hidden inputs to submit data to the create_function -->

                            <input type="hidden" id="size" value="Small" name="size" />
                            <input type="hidden" id="action" value="addToCart" name="action" />
                            <input type="hidden" id="id" value="<?php echo $row["product_id"]; ?>" name="id" />
                            <input type="hidden" id="productCharge" value="<?php echo $price ?>"
                                name="productCharge"></input>
                            <!-- I wonder if I could just skip all the logic for manipulating the price adn set this value to a static $8.00 and be done with it? -->
                            <?php if ($vendor == 3) { ?>
                            <input type="hidden" id="logoCharge" value="0.00" name="logoCharge" />
                            <?php } else { ?>
                            <input type="hidden" id="logoCharge" value="5.00" name="logoCharge" />
                            <?php } ?>
                            <!-- TODO: remove stitch charge from form, cart, database and all other logic..... or maybe leave alone for now at $0.00... -->
                            <input type="hidden" id="stitchCharge" value="0.00" name="stitchCharge" />
                            <input type="hidden" id="productPrice" value="0.00" name="productPrice"></input>
                            <img src='./color-images/black.gif' class='square' id='square' alt='Color swatch: black' />

                            <script>
                            // Here we want to establish the variable used to calculate the product price and assign to the value of the html form element\
                            // this function should ONLY update the value of the element with id 'productPrice' -> which is the value written to the cart for this item. It is the sum of the productCharge (from setPrice function) and logoCharge (from the logoPriceIncrease function)
                            // TODO: this function will need to be modified to include the value for the yet to be named or created deptName charge to add additional costs when stitching on the back of two hats - UPDATE: I DON'T THINK THIS COMMENT IS RELEVENT ANYMORE... BUT MAYBE.
                            function setProductPriceValue() {
                                var productCharge = document.getElementById('productCharge').value;
                                var logoCharge = document.getElementById('logoCharge').value;
                                var stitchCharge = document.getElementById('stitchCharge').value;
                                var productPriceHolder = document.getElementById('productPrice');
                                var productPriceValue = productPriceHolder.value;

                                // productPriceValue = (parseFloat(productCharge) + parseFloat(stitchCharge));
                                productPriceValue = (parseFloat(productCharge) + parseFloat(logoCharge) + parseFloat(
                                    stitchCharge));
                                // document.getElementById('productPrice').value = parseFloat(productPriceValue).toFixed(2)
                                //     .replace(/\d(?=(\d{3})+\.)/g,
                                //         '$&,');
                                document.getElementById('productPrice').value = parseFloat(productPriceValue).toFixed(
                                    2);
                                // var ugh = document.getElementById('price-holder').innerHTML = parseFloat(
                                //     productPriceValue).toFixed(2).replace(/\d(?=(\d{3})+\.)/g,
                                //     '$&,');
                                var ugh = document.getElementById('price-holder').innerHTML = parseFloat(
                                    productPriceValue).toFixed(2);
                                // this is to load the correct color swatch image on page load
                                var firstColor = document.getElementById('color_id').value;
                                // console.log('first color', firstColor);
                                updateColorImage(firstColor);
                                updateProductImage(firstColor)

                            }
                            </script>
                        </div>
                        <div class="size-not-available-text">
                            This product is only available in one size
                        </div>
                        <div id="size-picker-holder">
                            <legend>Product Size Details</legend>
                            <?php
                                    $sql3 = "SELECT sizes.size, sizes.size_id 
                                        FROM uniform_orders.products_sizes
                                        JOIN sizes on sizes.size_id=products_sizes.size_id
                                        JOIN products on products.product_id=products_sizes.product_id
                                        WHERE products.product_id = $product_id ORDER BY sizes.size_index";
                                    $stmt3 = $conn->prepare($sql3);
                                    $stmt3->execute();
                                    $result3 = $stmt3->get_result();
                                    if ($result3->num_rows > 0) {
                                        echo "<label for='size_id'>Choose a size:</label>";
                                        echo "<select title='size_id' name='size_id' id='size_id' onchange='changeSizeVal(this.value)';>";
                                        while ($crow = $result3->fetch_assoc()) {
                                            echo "<option value=" . $crow['size_id'] . ">" . $crow['size'] . "</option>";
                                        }
                                        echo "</select>";
                                    }
                                    ?>
                        </div>
                        <!-- TODO: JEEZE it's time to refactor this and abstract alot of this  into its own files..... -->
                        <!-- <div class="logo-not-available-text">
                                    This product can <em>not</em> be embroidered with a logo and can ONLY be ordered with the
                                    patch shown in the image
                                </div> -->
                        <div class="logo-info-holder" id="logo-info-holder">
                            <legend>County Logo Details</legend>
                            <!-- <img src="dept_logos/bc-circ.png" alt="..." class="lil-logo" id="lil-logo" /> -->
                            <p style="text-align: left" ;><a href="https://store.berkeleycountysc.gov/logos.php"
                                    target="_blank">To see all logos
                                    use this link</a></p>
                            <?php
                                    $logosql = "SELECT logo_name, image, id, description FROM uniform_orders.logos WHERE isactive = '1' AND iscomm = '0' ORDER By id DESC";
                                    $logostmt = $conn->prepare($logosql);
                                    $logostmt->execute();
                                    $logoresult = $logostmt->get_result();
                                    if ($product_id == 105) {
                                        echo "<input type='hidden' id='logo' name='logo' value='./dept_logos/NA.png' />";
                                    } elseif ($product_id == 185) {
                                        echo "<input type='hidden' id='logo' name='logo' value='./dept_logos/sw-rnb.png' />";
                                        // } elseif ($product_id == 106) {
                                        //     echo "<input type='hidden' id='logo' name='logo' value-'./dept_logos/hat-patch.png' />";
                                    } else {
                                        if ($logoresult->num_rows > 0) {
                                            echo "<label for='logo'>Choose a Logo:</label>";
                                            echo "<select title='logo' name='logo' id='logo' onchange='changeLogo(this.value)';>";
                                            while ($logorow = $logoresult->fetch_assoc()) {
                                                echo "<option value=" . $logorow['image'] . ">"    . $logorow['logo_name'] . "</option>";
                                            }
                                            echo "</select>";
                                        }
                                    }
                                    ?>
                            <br />
                        </div>

                        <div class="dept-name-patch-holder">
                            <legend>Department Name Details</legend>
                            <?php if ($vendor == 3) { ?>
                            <input type="hidden" id="deptNamePatch" name="deptNamePatch" value="Below Logo" />
                            <p>Department name will be below County Seal</p>
                            <?php } else { ?>
                            <label for='deptNamePatch'>Select where to display Department Name</label>
                            <select title='deptNamePatch' name='deptNamePatch' id='deptNamePatch'
                                onchange="setStitchPrice()">
                                <option value='No Dept Name'>Do not add Dept Name</option>
                                <option value='Below Logo' selected>Below Logo</option>
                                <option value='Left Sleeve'>Left Sleeve</option>
                                <option value='Back of Hat' class='hatback'>Back of Hat</option>
                                if (product_id == 106){
                                <input type="hidden" id="deptNamePatch" name="deptNamePatch" value="No Dept Name" />


                            </select>
                            <p id="hatBackText" class="hidden">Department name will <b>not</b> be embroidered on hat.
                                Instead "BERKELEY" will be stitched across the back of the hat</p>
                            <p id="truckerHatText" class="hidden">Department name can not be added to this hat.</p>
                            <?php } ?>
                        </div>
                        <div class="quantity-select">
                            <legend>Select Quantity</legend>
                            <label for='itemQuantity'>Select how many of this item you would like</label>
                            <input title='itemQuantity' name='itemQuantity' id='itemQuantity' type='number' min='1'
                                max='100' value='1' required />
                        </div>
                    </form>
                </div>
                <div class="logo-img-holder logo-hide" id="logo-img-holder">
                    <figure>
                        <img src="./dept_logos/county_Seal_White_Dept.png" alt="bc logo" id="logo-img">
                        <p id="logo-desc"></p>
                        <figcaption>Logo show is for display purposes only. Actual color will be either black or white -
                            depending on the shirt color</figcaption>
                    </figure>
                </div>
                <!-- <div class="product-with-logo-holder" id="product-with-logo-holder">
                    <img src="product-images/</?php echo $code ?>_prod.jpg" alt="..." />
                    <img src="dept_logos/bc-circ.png" alt="..." class="lil-logo" id="lil-logo" />
                </div> -->
            </div>
            <?php }
        }
        $conn->close();
            ?>
        </div>
        <div class=" button-holder">
            <a href=<?php echo $_SESSION['GOBACK'] ?>><button class="btn btn-secondary" type="button"><i
                        class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                    Shopping </button></a>
            <!-- <button onclick="showCart()">My Cart</button> -->
            <!-- <button type="button" class="btn btn-secondary" id="toggle-button" onclick="toggleSlideout()">View Cart</button> -->
            <button type="submit" form="options" class="btn btn-primary custom-btn"><span><i class=" fa fa-cart-plus"
                        aria-hidden="true"></i> Add to
                    Cart</span></button>
        </div>
        <div>
            <script>
            // I wish I had commented why this function exists ... I mean I assume it is important.... but..
            // In the original page there was a button below that called a "setPrice" function - but the button and logic is commented out.... maybe this isn't important.... 
            var sizeIDHolder = document.getElementById("size_id");
            var size = sizeIDHolder.value;
            var price = <?php echo $price ?>;
            var priceMod = <?php echo $price_mod ?>;
            </script>
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
    console.log('eating toast....')
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
        console.log(' reset stitch charge to : ' + stitchCharge)
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
    console.log("Dept Name Selected Value is: ", selectedValue);

    const imageElement = document.getElementById('lil-logo');
    console.log('ie11 ', imageElement);

    const oldSrc = imageElement.src;
    console.log('oldSrc is: ', oldSrc);
    const fileName = oldSrc.split('/').pop();
    console.log("File Name ", fileName);

    if (imageElement.complete && imageElement.src) {
        const oldSrc = imageElement.src;
        console.log('oldSrc is: ', oldSrc);
        const fileName = oldSrc.split('/').pop();
        console.log("File Name ", fileName);

        switch (selectedValue) {
            case 'No Dept Name':
            case 'Back of Hat':
            case 'Left Sleeve':
                console.log('left sleeve');
                const newFileName = addNoToFileName(fileName);
                const newSrc = oldSrc.replace(fileName, newFileName);
                imageElement.src = newSrc;
                break;

            case 'Below Logo':
                console.log('Below Logo')
                const fileNameWithoutNo = removeNoFromFileName(fileName);
                const newSrcWithoutNo = oldSrc.replace(fileName, fileNameWithoutNo);
                imageElement.src = newSrcWithoutNo;
                break;

                // case 'Back of Hat':
                // Do nothing
                // break;

            default:
                console.log('Invalid selection');
                break;
        }
    }
}

const deptNamePatchSelect = document.getElementById('deptNamePatch');
deptNamePatchSelect.addEventListener('change', handleDeptNamePatch);

// Call the function initially to set the initial state
// handleDeptNamePatch();
</script>

</html>
<!-- SWEET BABY BUDDAH THIS CSS IS A MESS!!!! TODO: Clean this up!!!!!! -->
<style>
body {
    background-color: #ffffff10;
}

.container {
    max-width: unset !important;
}

.another-container {
    display: grid;
    grid-template-columns: 55% auto;
    position: relative;

}

.product-image-holder {
    height: 700px;
    overflow: hidden;
}

.product-image {
    /* width: 80%; */
    height: auto !important;
    border-radius: 5px;
    /* margin-left: 15%; */
    /* margin-right: 15%; */
    margin-bottom: 10px;
    clip: rect(0, auto, 900px, 0);
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
    float: left;
    margin-right: 20px;
}

label {
    margin-right: 15px;
}

a {
    text-decoration: none;
    color: inherit;
}





/* .logo-img-holder {
        display: flex;
        background-color: white;
    } */

#logo-img {
    width: 200px;
    /* margin-top: 30px; */
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
    /* visibility: hidden; */
    color: lightblue;
}

#color-picker-holder,
#size-picker-holder,
.dept-name-patch-holder,
.logo-info-holder,
.quantity-select {
    /* background: #b9cf6a; */
    background: rgba(0, 0, 0, .5);
    /* border-color: #e3ebc3; */
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

legend {
    text-shadow: 0 1px 2px #195f80;
    text-align: left;
    margin-bottom: -10px;
}

/* .toast {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: slategray;
        color: aliceblue
    } */

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
    /* background-color: red; */
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
    /* z-index: 2; */
    /* background-color: #f0f0f0; */
    max-height: 800px;
    padding: 20px;
    /* display: flex; */
    /* flex-wrap: wrap;
    gap: 20px; */
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
    /* position: fixed; */
    /* bottom: 0; */
    /* margin-bottom: 100px; */
    display: flex;
    justify-content: center;
    align-items: center;
    /* margin-top: -50px; */
    /* margin-left: -50px; */
    padding: 10px;
    width: 150px;
    /* background-color: #195f80; */
    background-color: #d5ca9e;
    /* z-index: 2; */
    box-shadow: 5px 5px 7px #00000040, inset -3px -3px 5px #00000080;
    border-radius: 5px;
}

.name-code-holder {
    display: flex;
    gap: 2px;
}

.dept-name-patch-holder {
    display: none;
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