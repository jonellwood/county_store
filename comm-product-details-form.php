<?php

// delete this if it is garbage
function checkisComm()
{
    // start the session
    session_start();

    // check if the session variable exists
    if (!isset($_SESSION['isComm'])) {
        $_SESSION['isComm'] = false;  // set it to false if not set
    } else {
        // do nothing as it is already set
    }
}
checkisComm();

// end garbage

// session_start();

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$product_id = $_REQUEST['product_id'];

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
    <link rel="icon" type="image/x-icon" href="favicons/comm-favicon.ico">

    <title>Product Details</title>
    <script>
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',

        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
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
        console.log('s in set function is: ' + s);
        const myArray = s.split(",");
        // news = val;
        document.getElementById("size").value = myArray[1];

    }


    // this function named setVals() calls two functions, updateSize(...) and updateColor(...). These function calls will set the size to 'Small' and the color to 'Black' to provide default values for the dropdowns. The function does not return any value; it simply performs the desired actions.
    function setVals() {
        console.log('setVals called');
        updateSize('Small');
        updateColor('Black');
    }

    // This function calculates the final price of an item based on size and price modifier values. It uses a lookup table to retrieve the corresponding price change value for each combination of size and price modifier, and then adds this to the base price to get the final price. The final price is then set as innerHTML in a DOM element with an ID of "temp-price-holder". The calculated price is also logged to the console for now.

    function setPrice(size, price, price_mod) {
        var newPrice;
        switch (true) {
            case (price_mod === 0):
                newPrice = price;
                break;
            case ((price_mod === 1) && (size <= 4)):
                newPrice = price;
                break;
            case ((price_mod === 1) && (size == 5)):
                newPrice = (price + 2);
                break;
            case ((price_mod === 1) && (size == 6)):
                newPrice = (price + 6);
                break;
            case ((price_mod === 1) && (size == 7)):
                newPrice = (price + 8);
                break;
            case ((price_mod === 1) && (size == 8)):
                newPrice = (price + 12);
                break;
            case ((price_mod === 1) && (size == 10)):
                newPrice = price;
                break;
            case ((price_mod === 1) && (size == 11)):
                newPrice = (price + 14);
                break;
            case ((price_mod === 2) && (size == 12)):
                newPrice = price;
                break;
            case ((price_mod === 2) && (size == 13)):
                newPrice = (price + 2);
                break;
            case ((price_mod === 2) && (size == 14)):
                newPrice = (price + 4);
                break;
            case ((price_mod === 2) && (size == 15)):
                newPrice = (price + 6);
                break;
            case ((price_mod === 2) && (size == 16)):
                newPrice = (price + 8);
                break;
            case ((price_mod === 3) && (size > 5)):
                newPrice = price;
                break;
            case ((price_mod === 3) && (size === 5)):
                newPrice = (price * 1.24);
                break;
            case ((price_mod === 3) && (size > 5)):
                newPrice = (price * 1.43);
                break;
            case ((price_mod === 4) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 4) && (size == 5)):
                newPrice = (price * 1.56);
                break;
            case ((price_mod === 4) && (size > 5)):
                newPrice = (price * 2);
                break;
            case ((price_mod === 5) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 5) && (size == 5)):
                newPrice = (price * 1.29);
                break;
            case ((price_mod === 5) && (size > 5)):
                newPrice = (price * 1.41);
                break;
            case ((price_mod === 6) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 6) && (size == 5)):
                newPrice = (price * 1.49);
                break;
            case ((price_mod === 6) && (size > 5)):
                newPrice = (price * 1.94);
                break;
            case ((price_mod === 7) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 7) && (size == 5)):
                newPrice = (price * 1.43);
                break;
            case ((price_mod === 7) && (size > 5)):
                newPrice = (price * 1.93);
                break;
            case ((price_mod === 8) && (size > 5)):
                newPrice = price;
                break;
            case ((price_mod === 8) && (size == 5)):
                newPrice = (price + 2);
                break;
            case ((price_mod === 8) && (size == 6)):
                newPrice = (price + 3);
                break;
            case ((price_mod === 8) && (size == 7)):
                newPrice = (price + 4);
                break;
            case ((price_mod === 9) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 9) && (size == 5)):
                newPrice = (price + 3);
                break;
            case ((price_mod === 9) && (size == 10)):
                newPrice = price;
                break;
            case ((price_mod === 9) && (size > 5)):
                newPrice = (price + 6);
                break;
            case ((price_mod === 10) && (size < 5)):
                newPrice = price;
                break;
            case ((price_mod === 10) && (size == 5)):
                newPrice = (price + 2.2);
                break;
            case ((price_mod === 10) && (size == 6)):
                newPrice = (price + 5.94);
                break;
        }

        // document.getElementById("temp-price-holder").innerHTML = newPrice;
        document.getElementById("price-text-holder").innerHTML = "Updated Price is: $";
        document.getElementById("price-holder").innerHTML = newPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') +
            ' USD';
        document.getElementById("productPrice").value = newPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        //     console.log("newPrice is: " + newPrice);
        //     console.log("Size val from with the switch is: " + size);
        //     console.log("Price from within switch is: " + price);
        //     console.log("Price mod within the swith is: " + priceMod);
        //     console.log("*************************************************");
    }

    function changeSizeVal(val) {
        // alert(val + "-*-" + price + "-*-" + priceMod);
        setPrice(val, price, priceMod);
        // updateSize(val);
    }

    function changeLogo(img) {
        const logoImg = document.getElementById('logo-img');
        logoImg.src = img;
        logoImg.classList.remove('hidden');
        //console.log(logoImg.src);

    };

    // function hideLogo(val) {
    //     const logoImg = document.getElementById('logo-img');
    //     const commLogoSrc = './dept_logos/comm.png';
    //     console.log(val);
    //     if (val == true) {
    //         logoImg.classList.add('hidden');
    //         logoImg.src = commLogoSrc;
    //     }
    // }
    // var product = <?php echo $product_id; ?>;

    function hideLogo(product) {

        const logoImg = document.getElementById('logo-img');
        const commLogoSrc = './dept_logos/comm.png';
        console.log(product);
        if (product === 26 || product === 107) {
            logoImg.classList.add('hidden');
            logoImg.src = commLogoSrc;
            changeLogo('dept_logos/Autism-Awareness-Heat-Press-Full.png')
        }
    }
    </script>

</head>


<body onload='hideLogo(<?php echo $product_id ?>)'>

    <!-- <body> -->

    <?php include "comm-nav.php" ?>
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
                    <h6 class="card-subtitle mb-2"><span id="price-text-holder">Starting at: </span> <span
                            id='price-holder'>
                            <?php echo CURRENCY_SYMBOL . number_format($row["price"], 2) . ' ' . CURRENCY; ?></span>
                    </h6>
                    <!-- <h6 class="card-subtitle mb-2">Price for that size: <span id="temp-price-holder"></span></h6> -->

                    <a href="spec-sheets/<?php echo $row['code'] ?>.pdf"
                        download="<?php echo $row['code'] ?>.pdf"><b>Download Spec
                            Sheet</b></a>

                </span>
                <div>

                    <form name=" options" method="post" id="options" action="cartAction.php"
                        class='options-select-holder'>
                        <div>

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
                                        echo "<select title='color_id' name='color_id' id='color_id' onchange='checkProduct($product_id)'>";
                                        while ($crow = $result2->fetch_assoc()) {
                                            echo "<option id='" . $crow['color'] . "' value='" . $crow['color'] . "'>" . $crow['color'] . "</option>";
                                        }
                                        echo "</select>";
                                    }
                                    ?>
                            <!-- <input type="hidden" id="color_id" value="" name="color_id" /> -->
                            <input type="hidden" id="size" value="Small" name="size" />
                            <input type="hidden" id="action" value="addToCart" name="action" />
                            <input type="hidden" id="id" value="<?php echo $row["product_id"]; ?>" name="id" />
                            <input type="hidden" id="productPrice" value="<?php echo $price ?>"
                                name="productPrice"></input>

                        </div>
                        </br>
                        <!-- </span> -->
                        <div>
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
                        </br>
                        <div>
                            <p class='tiny-text'>Please note that logo images shown are approximations only. The actual
                                color(s) may
                                differ from the image below</p>
                            <?php
                                    $logosql = "SELECT logo_name, image, id, description FROM uniform_orders.logos WHERE heatpress = '1'";
                                    $logostmt = $conn->prepare($logosql);
                                    $logostmt->execute();
                                    $logoresult = $logostmt->get_result();
                                    // if ($_SESSION['isComm'] == true) {
                                    if (($product_id == 107) || ($product_id == 26)) {
                                        if ($logoresult->num_rows > 0) {
                                            echo "<label for='logo'>Choose a Logo:</label>";
                                            echo "<select title='logo' name='logo' id='logo' onchange='changeLogo(this.value)';>";
                                            while ($logorow = $logoresult->fetch_assoc()) {
                                                echo "<option value=" . $logorow['image'] . ">"    . $logorow['logo_name'] . "</option>";
                                            }
                                            echo "</select>";
                                        }
                                    } else {

                                        echo "<p>Your Logo is:<p>";
                                        // echo "<img src='./dept_logos/comm.png' alt='Comm Logo' width='150px'/>";
                                        echo "<input type='hidden' name='logo' value='./dept_logos/comm.png' />";
                                    }

                                    ?>
                        </div>
                    </form>
                </div>
                <div class="logo-img-holder">
                    <img src="./dept_logos/comm.png" alt="bc logo" id="logo-img">
                    <p id="logo-desc"></p>
                </div>
            </div>


            <div class="button-holder">
                <!-- <a href="index.php#nav-container"><button class="btn btn-secondary" type="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                                Shopping </button></a> -->
                <a href='products-by-communications.php'><button class="btn btn-secondary" type="button"><i
                            class="fa fa-arrow-left" aria-hidden="true"></i> Continue
                        Shopping </button></a>
                <button type="submit" form="options" class="btn btn-primary" id="add-to-cart-button"><i
                        class=" fa fa-cart-plus" aria-hidden="true"></i> Add to
                    Cart</button>


                <?php }
        }
        $conn->close();
                ?>
            </div>

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
        <dialog>
            <p>The color you selected is not allowed</p>
            <p>The only colors approved for this product are Graphite Heather, Navy, Carolina Blue, Black, and Heliconia
            </p>
            <button class="btn-secondary ok">OK</button>
        </dialog>
</body>
<script>
// console.log('price is: ' + price);
// console.log('sizeMod is: ' + priceMod);
// console.log('size is: ' + size);

// function to limit the color choices when product_id=26 to only 'Graphite Heather, Navy, Carolina Blue, Black, and Heliconia' per Sam's request. UPDATE instead of limiting selection we are going to just cause an alert if a color that is not allowed is selected. Much easier and more maintainable in the long run

var product = <?php echo $product_id; ?>;
var listText =
    'The only colors approved for this product are Graphite Heather, Navy, Carolina Blue, Black, and Heliconia';

function checkProduct(product) {
    // console.log(product);
    if (product !== 26 && product !== 107) {
        return
    } else {
        alertIfColorIsRestricted();
    }
}

function alertIfColorIsRestricted() {
    const dialogEl = document.querySelector("dialog");
    const okBtn = document.querySelector(".ok");
    // Get the value of the input with ID 'color_id'
    const colorInput = document.getElementById('color_id').value;
    const addButton = document.getElementById('add-to-cart-button');
    // Define the array of allowed colors
    const allowedColors = ['Graphite Heather', 'Navy', 'Carolina Blue', 'Black', 'Heliconia'];

    // Check if the input value matches an allowed color
    if (allowedColors.includes(colorInput)) {
        addButton.disabled = false;
        return;
        // renable add to cart button here
    } else {
        // alert(colorInput + ' is not an approved color. ' + listText);

        dialogEl.showModal(colorInput);
        // disable add to cart button here
        addButton.disabled = true;

    }
    okBtn.addEventListener("click", () => {
        dialogEl.close();
    })
}
</script>

</html>
<style>
.container {
    margin: 20px;
}

.another-container {
    display: grid;
    grid-template-columns: 1fr 1fr;

}

.product-image {
    width: 400px;
    height: auto !important;
}

.details-about-details {
    text-align: right;
    margin-left: 20px;
    margin-right: 20px;
}

.button-holder {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}

.options-select-holder {
    margin-top: 25px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    justify-content: start;
    align-content: start;
    justify-items: start;
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

dialog {
    background-color: lightgrey;
    color: black;
}

#add-to-cart-button {
    transition: all .2s ease-in-out;
}



@keyframes add_to_cart {
    from {
        background-color: red;
    }

    to {
        background-color: yellow;
    }
}
</style>