<?php


include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$product_id = $_REQUEST['product_id'];

if ($product_id == 105) {
    echo '
    <style>
    .options-form-holder {
        visibility: hidden;
    }
    .logo-img-holder {
        visibility: hidden;
    }
    </style>
    ';
}
if ($product_id == 106) {
    echo '
    
    <style>
    .logo-info-holder {
        visibility: hidden;
    }
    .logo-img-holder {
        visibility: hidden;
    }
    .dept-name-patch-holder{
        visibility: hidden;
    }
    #size-picker-holder{
        visibility: hidden;
    }
    .logo-not-available-text {
        visibility: visible;
    }
    .size-not-available-text {
        visibility: visible;
    }    
    </style>
    ';
}
if ($product_id != 106) {
    echo '
    
    <style>
    
    .logo-not-available-text {
        display: none;
    }
    .size-not-available-text {
        display:none;
    }
    </style>
    ';
}
if (($product_id != 32) && ($product_id != 33)) {
    echo '
    <style>
    .hatback {
        display: none;
    }
    </style>
    ';
}

// if (($product_id == 106) || ($product_id == 32) || ($product_id == '33') || ($product_id == '105')) {
//     echo '
//     <style>

//     .product-image {
//         height: auto !important;
//     </style>
//     ';
// }

$sql = "SELECT p.product_id, p.code, p.name, p.image, p.price, p.description, p.producttype, p.price_size_mod,
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

// $stmt->close();
// init shopping cart class
include_once 'Cart.class.php';
$cart = new Cart;

$smod = array();
$smodSql = "SELECT 
        -- product_id AS product_id,
        -- price_size_mod AS price_size_mod,
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
        na_inc AS '9' 
        from prod_size_mod_ref WHERE product_id = $product_id";
$smodStmt = $conn->prepare($smodSql);
$smodStmt->execute();
$smodResult = $smodStmt->get_result();
if ($smodResult->num_rows > 0) {
    while ($smodRow = $smodResult->fetch_assoc()) {
        $smod[] = $smodRow;
    }
    // echo "<pre>";
    // print_r($smod);
    // echo "</pre>";
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Product Details" />
    <link href="style.css" rel="stylesheet" defer async>
    <link rel="stylesheet" id='test' href="berkstrap-dark.css" defer async>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" defer
        async>

    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <title>Product Details</title>

    <script>
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',

    })
    // This onchange hander uses the updateColor function to update the value of the element with an ID of color_id. It sets the current value to a new color based on the passed in parameter val. Then it assigns the new color to the element which is included in the form action.

    function updateColor(val) {
        c = document.getElementById('color_id').value;
        newc = val;
        document.getElementById("color_id").value = newc;
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
        // console.log('setVals called');
        updateSize('Small');
        updateColor('Black');
    }

    // This function calculates the final price of an item based on size and price modifier values. It uses a lookup table to retrieve the corresponding price change value for each combination of size and price modifier, and then adds this to the base price to get the final price. The final price is then set as innerHTML in a DOM element with an ID of "temp-price-holder". The calculated price is also logged to the console for now.

    function getModPrice(price, size) {
        var priceMods = <?php echo json_encode($smod); ?>;
        let priceMod = priceMods[0];

        let priceArray = [];
        priceArray.push(0.00);

        for (let i = 1; i < 17; i++) {
            let newPrice = parseFloat(priceMod[i]) + parseFloat(price);
            priceArray.push(newPrice);
        }
        newPrice = priceArray[size];
        return newPrice;
    }

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

    // the ONLY thing this function should update is the value of the element with ID 'logoCharge' (which is currently either 0.00 or 5.00). The item cost sent to the cart is the sum of this value and the product charge - which is set on page load and updated with setPrice function (above) that updates based on size.
    function logoPriceIncrease(logo, price) {
        // set value of productPrice to intial product value for items that do not fall into the switch cases.
        setProductPriceValue();
        // console.log('LOGO PRICE INCREASE FUNCTION CALLED');
        var newPriceLogo;
        var logoCharge = document.getElementById('logoCharge').value;
        var logo = document.getElementById('logo').value;
        // console.log('current logo charge in logoPriceIncrease function is: ' + logoCharge);
        // console.log('logo value is ' + logo);
        switch (true) {
            // !!! TODO !!!! these two cases can be combined into one
            case (logo == 'dept_logos/coroner.png'):
                if (newLogoCharge == 0) {
                    newLogoCharge = parseFloat(5.00);
                } else if (newLogoCharge == 5.00) {
                    newLogoCharge == parseFloat(0.00)
                };
                // showToast('This Logo has a $5 upcharge');
                // console.log('newLogoCharge with coroner logo ' +
                // parseFloat(newLogoCharge));
                document.getElementById('logo-price-text-holder').innerHTML =
                    "Update price above reflects a logo upcharge of: $";
                document.getElementById('logo-price-holder').innerHTML = parseFloat(newLogoCharge).toFixed(2).replace(
                    /\d(?=(\d{3})+\.)/g, '$&,') + ' USD';
                document.getElementById('logoCharge').value = parseFloat(newLogoCharge).toFixed(2).replace(
                    /\d(?=(\d{3})+\.)/g, '$&,');

                document.getElementById('logo-price-text-holder').style.visibility = 'visible';
                document.getElementById('logo-price-holder').style.visibility = 'visible';
                setProductPriceValue();
                break;
            case ((logo == 'dept_logos/emd.png') || (logo == 'dept_logos/ems.png') || (logo ==
                'dept_logos/animalshelter.png')):
                if (newLogoCharge == 0) {
                    newLogoCharge = parseFloat(5.00);
                } else if (newLogoCharge == 5.00) {
                    newLogoCharge == parseFloat(0.00)
                };
                // showToast('This Logo has a $5 upcharge');
                // console.log('newLogoCharge with emd logo ' +
                // parseFloat(newLogoCharge));
                document.getElementById('logo-price-text-holder').innerHTML =
                    "Update price above reflects a logo upcharge of: $";
                document.getElementById('logo-price-holder').innerHTML = parseFloat(newLogoCharge).toFixed(2).replace(
                        /\d(?=(\d{3})+\.)/g,
                        '$&,') +
                    ' USD';

                document.getElementById('logoCharge').value = parseFloat(newLogoCharge).toFixed(2).replace(
                    /\d(?=(\d{3})+\.)/g, '$&,');
                document.getElementById('logo-price-text-holder').style.visibility = 'visible';
                document.getElementById('logo-price-holder').style.visibility = 'visible';
                setProductPriceValue();
                break;
            case (logo != 'dept_logos/emd.png' && logo != 'dept_logos/emd.png'):
                newLogoCharge = parseFloat(0.00);

                // console.log('newLogoCharge with NO MATCH: ' +
                // parseFloat(newLogoCharge));
                document.getElementById('logo-price-text-holder').innerHTML =
                    "Update price above reflects a logo upcharge of: $";
                document.getElementById('logo-price-holder').innerHTML = parseFloat(newLogoCharge).toFixed(2).replace(
                        /\d(?=(\d{3})+\.)/g,
                        '$&,') +
                    ' USD';

                document.getElementById('logoCharge').value = parseFloat(newLogoCharge).toFixed(2).replace(
                    /\d(?=(\d{3})+\.)/g, '$&,');
                document.getElementById('logo-price-text-holder').style.visibility = 'hidden';
                document.getElementById('logo-price-holder').style.visibility = 'hidden';
                setProductPriceValue()
                break;

        }

    }

    function changeSizeVal(val) {
        setPrice(val, price, priceMod);
        var logo = document.getElementById('logo').value;
    }

    function changeLogo(img) {
        const logoImg = document.getElementById('logo-img');
        const currentPrice = document.getElementById('productPrice').value;
        logoImg.src = img;
        logoPriceIncrease();
    };
    // not sure if this is still relevent after moving comms to their own site - needs investigation
    function hideLogo(val) {
        const logoImg = document.getElementById('logo-img');
        const commLogoSrc = './dept_logos/comm.png';
        // console.log(val);
        if (val == true) {
            logoImg.classList.add('hidden');
            logoImg.src = commLogoSrc;
        }
    }
    </script>

</head>


<body onload='logoPriceIncrease()'>
    <?php include "nav.php" ?>
    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $price_mod = $row['price_size_mod'];
                $size = $row['size_id'];
                $price = $row['price'];
                $proImg = !empty($row["image"]) ? $row["image"] : "demo-img.jpg";
                echo "<h3>Product Details for " . $row["name"] . " - " . $row["code"] . "</h3>"; ?>
        <div class="another-container">
            <div>
                <img src="<?php echo $proImg; ?>" alt="<?php echo $row['name'] ?>" class="product-image">
            </div>
            <div class="details-about-details">
                <span>
                    <?php echo $row['description'] ?>
                    <h6 class="card-subtitle mb-2"><span id="price-text-holder">Starting at: </span>
                        <span id='price-holder'>
                            <?php echo CURRENCY_SYMBOL . number_format($row["price"], 2) . ' ' . CURRENCY; ?>
                        </span>
                    </h6>
                    <h6 class="card-subtitle mb-2">
                        <span id="logo-price-text-holder">Selected Logo has an upcharge of: $5.00</span>
                        <span id='logo-price-holder'></span>
                    </h6>
                    <!-- <h6 class="card-subtitle mb-2">Price for that size: <span id="temp-price-holder"></span></h6> -->
                    <a href="spec-sheets/<?php echo $row['code'] ?>.pdf"
                        download="<?php echo $row['code'] ?>.pdf"><b>Download Spec Sheet</b></a>
                </span>
                <div class='options-form-holder'>
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
                                    if ($result2->num_rows > 0) {
                                        echo "<label for='color_id'>Choose a color:</label>";
                                        echo "<select title='color_id' name='color_id' id='color_id'>";
                                        while ($crow = $result2->fetch_assoc()) {
                                            echo "<option value='" . $crow['color'] . "'>" . $crow['color'] . "</option>";
                                        }
                                        echo "</select>";
                                    }
                                    ?>
                            <!-- <input type="hidden" id="color_id" value="" name="color_id" /> -->
                            <input type="hidden" id="size" value="Small" name="size" />
                            <input type="hidden" id="action" value="addToCart" name="action" />
                            <input type="hidden" id="id" value="<?php echo $row["product_id"]; ?>" name="id" />
                            <!-- <input type="hidden" id="productPrice" value="<//?php echo $price ?>" name="productPrice"></input> -->
                            <!-- productPrice is the value written to the cart for the item. Refactoring to be the sum of the productCharge and logoCharge variables -->
                            <input type="hidden" id="productCharge" value="<?php echo $price ?>"
                                name="productCharge"></input>
                            <input type="hidden" id="logoCharge" value="0.00" />
                            <input type="hidden" id="stitchCharge" value="0.00" />
                            <input type="hidden" id="productPrice" value="0.00" name="productPrice"></input>
                            <script>
                            // Here we want to establish the variable used to calculate the product price and assign to the value of the html form element\
                            // this function should ONLY update the value of the element with id 'productPrice' -> which is the value written to the cart for this item. It is the sum of the productCharge (from setPrice function) and logoCharge (from the logoPriceIncrease function)
                            // TODO: this function will need to be modified to include the value for the yet to be named or created deptName charge to add additional costs when stitching on the back of two hats
                            function setProductPriceValue() {
                                // console.log('SET PRODUCT PRICE VALUE FUNCTION CALLED');
                                var productCharge = document.getElementById('productCharge').value;
                                // console.log('setProductPriceValue says productCharge is : ' + productCharge);
                                var logoCharge = document.getElementById('logoCharge').value;
                                // console.log('setProductPriceValue says logoCharge is : ' + logoCharge);
                                var stitchCharge = document.getElementById('stitchCharge').value;

                                var productPriceHolder = document.getElementById("productPrice");
                                var productPriceValue = productPriceHolder.value;

                                productPriceValue = (parseFloat(productCharge) + parseFloat(logoCharge) + parseFloat(
                                    stitchCharge));

                                console.log('setProductPriceValue says productPrice is : ' + productPriceValue);
                                document.getElementById("productPrice").value = parseFloat(productPriceValue).toFixed(2)
                                    .replace(/\d(?=(\d{3})+\.)/g,
                                        '$&,');
                                // var priceHolderTextVariableNameThingy = document.getElementById("price-holder")
                                //     .innerHTML;
                                var ugh = document.getElementById("price-holder").innerHTML = parseFloat(
                                        productPriceValue)
                                    .toFixed(2)
                                    .replace(/\d(?=(\d{3})+\.)/g,
                                        '$&,');

                            }
                            </script>
                        </div>
                        <!-- </br> -->
                        <div class="size-not-available-text">
                            This product only comes in one size.
                        </div>
                        <div id="size-picker-holder">
                            <legend>Product Size Details</legend>
                            <?php
                                    $sql3 = "SELECT sizes.size, sizes.size_id 
                                    FROM uniform_orders.products_sizes
                                    JOIN sizes on sizes.size_id=products_sizes.size_id
                                    JOIN products on products.product_id=products_sizes.product_id
                                    WHERE products.product_id = $product_id";
                                    $stmt3 = $conn->prepare($sql3);
                                    $stmt3->execute();
                                    $result3 = $stmt3->get_result();
                                    if ($result3->num_rows > 0) {
                                        echo "<label for='size_id'>Choose a size:</label>";
                                        echo "<select title='size_id' name='size_id' id='size_id' onchange='changeSizeVal(this.value)';>";
                                        // echo "<select title='size_id' name='size_id' id='size_id' onchange='updateSize(this.value)';>";
                                        while ($crow = $result3->fetch_assoc()) {
                                            echo "<option value=" . $crow['size_id'] . ">" . $crow['size'] . "</option>";
                                        }
                                        echo "</select>";
                                    }
                                    ?>

                        </div>

                        <!-- </br> -->
                        <div class="logo-not-available-text">This product can <em>not</em> be embroidered with a logo
                            and can ONLY be ordered with the patch shown in the image</div>
                        <div class='logo-info-holder'>
                            <legend>County Logo Details</legend>
                            <?php
                                    $logosql = "SELECT logo_name, image, id, description FROM uniform_orders.logos WHERE isactive = '1' AND iscomm = '0'";
                                    $logostmt = $conn->prepare($logosql);
                                    $logostmt->execute();
                                    $logoresult = $logostmt->get_result();
                                    if ($product_id == 105) {
                                        echo "<input type='hidden' name='logo' value='./dept_logos/NA.png' />";
                                    } elseif ($product_id == 106) {
                                        echo "<input type='hidden' name='logo' value='./dept_logos/hat-patch.png' />";
                                    } elseif ($_SESSION['isComm'] == true) {
                                        echo "<p>Your Logo is:<p>";
                                        echo "<img src='./dept_logos/comm.png' alt='Comm Logo' width='150px'/>";
                                        echo "<input type='hidden' name='logo' value='./dept_logos/comm.png' />";
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
                        <div class='dept-name-patch-holder'>
                            <legend>Department Name Details</legend>
                            <label for='deptNamePatch'>Select where to display Department Name</label>
                            <select title='deptNamePatch' name='deptNamePatch' id='deptNamePatch'>
                                <option value='No Dept Name'>Do not add Dept Name</option>
                                <option value='Below Logo'>Below Logo</option>
                                <option value='Left Sleeve'>Left Sleeve</option>
                                <option value='Back of Hat' class='hatback'>Back of Hat</option>
                            </select>
                            <p id="hatBackText" class="hidden">Department Name will not be embroidered on hat. Instead
                                "BERKELEY"
                                will be stitched across the back of hat</p>
                        </div>
                    </form>
                </div>
                <div class="logo-img-holder">
                    <figure>
                        <img src="./dept_logos/bc-bsqr.png" alt="bc logo" id="logo-img">
                        <p id="logo-desc"></p>
                        <figcaption>Logo shown is for display purposes only. Actual color will be black or white
                            depending on shirt color</figcaption>
                    </figure>
                </div>
                <!-- <p class='tiny-text'>Please note that logo images shown are approximations only. The actual
                    color(s) may
                    differ from the image below</p> -->
            </div>

            <?php }
        }
        $conn->close();
            ?>
        </div>
        <div class="button-holder">
            <a href=<?php echo $_SERVER['HTTP_REFERER'] ?>><button class="btn btn-secondary" type="button"><i
                        class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                    Shopping </button></a>
            <!-- <button onclick="showToast()">Yay Toast</button> -->
            <button type="submit" form="options" class="btn btn-primary custom-btn"><span><i class=" fa fa-cart-plus"
                        aria-hidden="true"></i> Add to
                    Cart</span></button>
        </div>
        <div>
            <script>
            var sizeIDHolder = document.getElementById("size_id");
            // console.log("sizeIDHolder.value is: " + sizeIDHolder.value);


            var size = sizeIDHolder.value;
            var price = <?php echo $price ?>;
            const priceMod = <?php echo $price_mod ?>;
            </script>
            <!-- <button type="button" id="thingy"
                onclick="setPrice(<//?php echo $size ?>, <//?php echo $price ?>, <//?php echo $price_mod ?>)">Update
                Price</button>
            <h6 id="temp-price-holder">Temp Price</h6> -->
        </div>
        <div id="myToast">
            <div class="toast-header">
                <i class="fa fa-info"></i>Price Change Alert
                <small>1 sec ago</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" onclick="eatToast()"></button>
            </div>
            <div class="toast-body">
                <!-- <p id="toast_message">This option added $3.00 to your cost</p> -->
                <p id="toast_message"></p>
            </div>
        </div>

</body>
<script>
// console.log('price is: ' + price);
// console.log('sizeMod is: ' + priceMod);
// console.log('size is: ' + size);
function showToast(msg) {
    var toast = document.getElementById('myToast');
    var msgBlock = document.getElementById('toast_message');
    msgBlock.innerText = msg;
    //console.log(toast);
    toast.className = "show";
    setTimeout(function() {
        toast.className = toast.className.replace("show", "hideToast");
    }, 3000);
}

function eatToast() {
    console.log('eating toast....')
    var toast = document.getElementById('myToast').classList.replace('show', 'eatToast');
}

// function to unhide a text box to let users know the department name will not be put on hat but the work "BERKELEY" instead
function showHatBackText() {
    var deptNamePatch = document.querySelector('select[id="deptNamePatch"]');
    var hatBackText = document.querySelector('#hatBackText');
    // var stitchCharge = document.getElementById('stitchCharge').value;
    // var newStitchCharge = stitchCharge;

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
showHatBackText();

// function showToastForLogo() {
//     var logoPicker = document.querySelector('select[id="logo"]');
//     logoPicker.addEventListener('change', function() {
//         var selectedOption = logoPicker.options[logoPicker.selectedIndex];

//         if (selectedOption.value = 'dept_logos/coroner.png') {
//             showToast('This logo cost $5 extra');
//         }
//     })
// }
// showToastForLogo();

function setStitchPrice() {
    var stitchCharge = document.getElementById('stitchCharge').value;
    // console.log('stitchCharge is: ' + stitchCharge);
    if (stitchCharge == 0) {
        stitchCharge = parseInt(3.00);
        document.getElementById('stitchCharge').value = parseFloat(stitchCharge).toFixed(2).replace(
            /\d(?=(\d{3})+\.)/g, '$&,');
        // showToast('This option added $3.00 to your cost');
    } else {
        return
    }
    console.log('stitchCharge is: ' + stitchCharge);
}

function resetStitchPrice() {
    var stitchCharge = document.getElementById('stitchCharge').value;
    if (stitchCharge == 3.00) {
        stitchCharge = parseInt(0.00);
        document.getElementById('stitchCharge').value = parseFloat(stitchCharge).toFixed(2).replace(
            /\d(?=(\d{3})+\.)/g, '$&,');
        console.log(' reset stitch charge to : ' + stitchCharge)
    }

}
</script>

</html>
<style>
body {
    background-color: #ffffff10;
}

.another-container {
    display: grid;
    grid-template-columns: auto auto;
}

.product-image {
    width: 400px;
    height: auto !important;
    border-radius: 5px;
}

.details-about-details {
    text-align: right;
    margin-left: 20px;
    margin-right: 20px;
}

.button-holder {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    margin-left: 20px;
    margin-right: 65px;
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

.navbar {
    display: flex;
    justify-content: center;
}

.cart-view {
    margin-left: 50px;
}

.logo-img-holder {
    display: flex;
    background-color: white;
}

#logo-img {
    width: 200px;
    margin-top: 30px;

}

.hidden {
    visibility: hidden;
}

.tiny-text {
    font-size: .75em;
}

.logo-not-available-text {
    visibility: visible;

}

#logo-price-text-holder {
    visibility: hidden;
    color: lightblue;
}

#color-picker-holder,
#size-picker-holder,
.dept-name-patch-holder,
.logo-info-holder {
    background: #b9cf6a;
    background: rgba(255, 255, 255, .3);
    border-color: #e3ebc3;
    border-color: rgba(255, 255, 255, .6);
    border-style: solid;
    border-width: 2px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    line-height: 30px;
    list-style: none;
    padding: 5px 10px;
    margin-bottom: 1em;
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
    /* font-weight: bolder; */
    color: wheat;
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