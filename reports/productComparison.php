<?php

include_once "../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * from products where comparibleTo is not NULL ";
$stmt = $conn->prepare($sql);
$stmt->execute();
$departments = $stmt->get_result();
$data = array();
$comps = array();
while ($row = $departments->fetch_assoc()) {
    array_push($data, $row);
    // array_push($comps, $row['comparibleTo']);
}

?>
<div class="make-blurry">

    <div class="select-holder">

        <select id="productSelect">
            <option value="">Select a product</option>
            <?php foreach ($data as $item) : ?>
                <option value="<?php echo $item['product_id'] ?>" data-price="<?php echo $item['price'] ?>" data-comparable="<?php echo $item['comparibleTo'] ?>">
                    <?php echo $item['description'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="results-holder">
        <div class="reeds">
            <h1>Reeds Uniforms</h1>
            <img src="loser.png" id="reedsloser" alt="loser" class="hidden" />
            <p id="priceParagraph" class="money"></p>
        </div>
        <div class="product-image" id="product-image"></div>
        <div class="lcn">
            <h1>No Country Native</h1>
            <img src="loser.png" id="lncloser" alt="loser" class="hidden" />
            <p id="LCNPrice"></p>
        </div>
    </div>
    <div class="button-holder">
        <button onclick="getWinner()">Compare</button>
    </div>
</div>


<p id="comparableParagraph"></p>
<script>
    // Get references to the select element and the paragraph elements
    const productSelect = document.getElementById('productSelect');
    const priceParagraph = document.getElementById('priceParagraph');
    const comparableParagraph = document.getElementById('comparableParagraph');

    // Add an event listener to the select element
    productSelect.addEventListener('change', function() {
        // Get the selected option
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        // console.log(selectedOption.value);
        // Get the data attributes (price and comparableTo) from the selected option
        fetchProd(selectedOption.value);
        reset();

        // const price = selectedOption.getAttribute('data-price');
        const comparableTo = selectedOption.getAttribute('data-comparable');
        fetchComp(comparableTo);

        // Update the paragraph elements with the selected values
        // priceParagraph.textContent = `Price: $${price}`;
        comparableParagraph.textContent = `Comparable To: ${comparableTo}`;
    });
    async function fetchProd(id) {
        await fetch('getProductDetails.php?id=' + id)
            .then((response => response.json()))
            .then((data) => {
                // console.log('OG data');
                // console.log(data);
                var html = "";
                html += "<p id='reedsprice' value='" + data[0].price + "'>Base Price: " + formatAsCurrency(data[0]
                    .price) + "</p>";
                html += "<p>2XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxl_inc)) + "</p>";
                html += "<p>3XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxl_inc)) + "</p>";
                html += "<p>4XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxxl_inc)) + "</p>";
                html += "<p>5XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxxxl_inc)) + "</p>";

                html += "<p>Logo Fee: TBD </p>";
                html += "<p>Product Code: " + data[0].code + "</p>";

                document.getElementById('priceParagraph').innerHTML = html;
            })
    }



    async function fetchComp(id) {
        await fetch('getProductDetails.php?id=' + id)
            .then((response => response.json()))
            .then((data) => {
                // console.log(data);
                var html = ""
                var img = ""

                html += "<p id='loserprice' value='" + data[0].price + "'>Base Price: " + formatAsCurrency(data[0]
                        .price) +
                    "</p>";
                html += "<p>2XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxl_inc)) + "</p>";
                html += "<p>3XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxl_inc)) + "</p>";
                html += "<p>4XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxxl_inc)) + "</p>";
                html += "<p>5XL price: " + formatAsCurrency(parseFloat(data[0].price) + parseFloat(data[1]
                    .xxxxxl_inc)) + "</p>";
                html += "<p>Logo Fee: $5.00 </p>";
                html += "<p>Product Code: " + data[0].code + "</p>";
                img += "<img src='../" + data[0].image + "' alt='...'>";
                document.getElementById("LCNPrice").innerHTML = html;
                document.getElementById("product-image").innerHTML = img;
            })
        // getWinner();
    }

    function formatAsCurrency(number, locale = 'en-US', currency = 'USD') {
        return number.toLocaleString(locale, {
            style: 'currency',
            currency: currency,
        });
    }

    function getWinner() {
        var lcn = document.getElementById('loserprice').innerText;
        var reeds = document.getElementById('reedsprice').innerText;
        var lcnlost = document.getElementById('lncloser');
        var reedslost = document.getElementById('reedsloser');
        console.log(lcnlost);
        console.log(reedslost);
        if (extractPriceFromString(lcn) < extractPriceFromString(reeds)) {
            console.log('LCN Wins')
        } else {
            console.log('Reeds Wins')
            lcnlost.classList.remove('hidden');
        }


    }

    function extractPriceFromString(inputString) {
        const dollarIndex = inputString.indexOf('$');
        const priceString = inputString.substring(dollarIndex + 1);
        const priceFloat = parseFloat(priceString);
        return priceFloat;
    }

    function reset() {
        var lcnlost = document.getElementById('lncloser');
        var reedslost = document.getElementById('reedsloser');
        lcnlost.classList.add('hidden');
        reedslost.classList.add('hidden');
    }
</script>

<style>
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        background-image: url('sliderule.webp');
        background-size: cover;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-size: large;
    }

    .make-blurry {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: #ffffff95;
    }

    .select-holder {
        margin: 20px;
        margin-top: 40px;
        display: flex;
        justify-content: space-evenly;
    }

    .select-holder select {
        padding: 20px;
    }

    #comparableParagraph {
        display: none;
    }

    #priceParagraph,
    #LCNPrice {
        background-color: #80808095;
        margin-top: 90px;
        margin-left: 20px;
        margin-right: 20px;
        /* padding: 20px; */
        border: 2px black solid;
    }

    #LCNPrice p,
    #priceParagraph p {
        padding: 10px;
        color: whitesmoke;
    }

    .results-holder {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        justify-content: center;
        gap: 20px;
        margin-left: 20px;
        margin-right: 20px;
        background-color: #80808025;
        height: 75dvh;
    }

    .results-holder div {
        text-align: center;
    }

    .product-image img {
        width: 400px;
    }

    .reeds,
    .lcn {
        position: relative;
    }

    #reedsloser,
    #lncloser {
        width: 200px;
        position: absolute;
        z-index: 2;
    }

    #lncloser {
        right: 200px;
        top: -50px;
    }

    .hidden {
        visibility: hidden;
    }

    .button-holder {
        display: flex;
        justify-content: center;
    }

    .button-holder button {
        font-size: x-large;
    }
</style>