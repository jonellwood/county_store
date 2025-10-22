<?php
session_start();
include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;

$param = $_POST['param'];
// var_dump($param);
include './components/viewHead.php';
?>


<script>
    function setFormat() {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        })
    }

    function setParam(val) {
        const button = document.getElementById("search-button");
        button.value = val;
    }

    function addQuotes(str) {
        return '"' + str + '"';
    }

    async function searchProducts(arg) {
        console.log(arg);
        const search = await fetch("./search-products.php?param=" + arg)
            .then((response) => response.json())
            .then(data => {
                console.log("Data dot code");
                console.log(data[0]);
                if (data[0].product_id === '0') {
                    console.log("No results found");
                    html = "<div class='no-results'>";
                    html += "<h4>We are sorry, <span class='search-query'>\"" + arg + "\"</span> returned 0 results.</h4>";
                    html += "<img src='./assets/images/sorry_we_love_you.jpg' alt='super cute picture to make sure you still love us'>";
                    html += "</div>";
                    document.getElementById("search-results").innerHTML = html;
                } else {
                    // Count results
                    var resultCount = data.length;
                    var html = "<div class='results-count'>";
                    html += "Found <strong>" + resultCount + "</strong> product" + (resultCount !== 1 ? "s" : "") + " matching <span class='search-query'>\"" + arg + "\"</span>";
                    html += "</div>";

                    // Create modern card grid
                    html += "<div class='results-grid'>";

                    for (let i = 0; i < data.length; i++) {
                        var image = data[i].image;
                        var detailsUrl = '';

                        // Determine detail page URL based on product type
                        if (data[i].product_type === "3") {
                            detailsUrl = './hat-details.php?product_id=' + data[i].product_id;
                        } else if (data[i].product_type === "7") {
                            detailsUrl = './boots-details.php?product_id=' + data[i].product_id;
                        } else {
                            detailsUrl = './product-details.php?product_id=' + data[i].product_id;
                        }

                        // Build product card
                        html += "<div class='product-card'>";
                        html += "<img src='./" + image + "' alt='" + data[i].name + "'>";
                        html += "<div class='product-code'>" + data[i].code + "</div>";
                        html += "<div class='product-name'>" + data[i].name + "</div>";
                        html += "<a href='" + detailsUrl + "' class='details-button'>View Details</a>";
                        html += "</div>";
                    }

                    html += "</div>";
                    document.getElementById("search-results").innerHTML = html;
                }
            })
    }

    function buttonClick() {
        const searchButton = document.getElementById("search-button");
        searchButton.click();
    }
</script>

<body onload="buttonClick()">
    <div class="container">
        <input type="text" name="search_param" id="search_param" onchange="setParam(this.value)"
            value="<?php echo $param ?>"></input>

        <button class="btn btn-success" id="search-button" value="<?php echo $param ?>" type="button"
            onclick="searchProducts(this.value)">Search

        </button><br>
        <p class='why-so-serious'>Text Search Product Code or Name</p>
        <hr>
        <div class="search-results" id="search-results"></div>
    </div>



    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script> -->
</body>
<script>
    function hideButton() {
        console.log('hide button called');
        var buttonInfo = document.getElementById("details-button");
        if (buttonInfo.value == "0") {
            buttonInfo.classList.add("hidden");
        }
    }

    function hidePrice() {
        console.log('hide Price called');
        var priceInfo = document.getElementById("details-price");
        if (priceInfo.value == "0") {
            priceInfo.classList.add("hidden");
        }
    }

    function hideImage() {
        console.log('hide Image called');
        var imageInfo = document.getElementById("details-image");
        if (imageInfo.value == "100") {
            imageInfo.classList.add("hidden");
        }
    }
</script>

</html>

<style>
    body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        min-height: 100vh;
        padding-bottom: 40px;
    }

    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .search-header {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        align-items: center;
    }

    #search_param {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    #search_param:focus {
        outline: none;
        border-color: #198754;
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.1);
    }

    #search_param::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    #search-button {
        padding: 15px 35px;
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }

    #search-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
    }

    #search-button:active {
        transform: translateY(0);
    }

    .why-so-serious {
        color: rgba(255, 255, 255, 0.5);
        font-size: 14px;
        margin: 10px 0 20px 0;
        font-style: italic;
    }

    .search-results {
        color: white;
    }

    /* Modern Card-based Results */
    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .product-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .product-card:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .product-card img {
        width: 100%;
        max-width: 180px;
        height: 180px;
        object-fit: contain;
        margin-bottom: 15px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.9);
        padding: 10px;
        transition: transform 0.3s ease;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    .product-code {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .product-name {
        font-size: 16px;
        color: white;
        font-weight: 600;
        margin-bottom: 15px;
        line-height: 1.4;
        min-height: 44px;
    }

    .details-button {
        width: 100%;
        padding: 12px 24px;
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        text-decoration: none;
        display: inline-block;
    }

    .details-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
    }

    /* No Results Styling */
    .no-results {
        text-align: center;
        padding: 60px 20px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        border: 2px dashed rgba(255, 255, 255, 0.2);
    }

    .no-results h4 {
        color: white;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 30px;
    }

    .no-results img {
        max-width: 400px;
        width: 80%;
        border-radius: 16px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        margin-top: 20px;
    }

    .search-query {
        color: #198754;
        font-weight: 700;
        font-style: italic;
    }

    .results-count {
        color: rgba(255, 255, 255, 0.7);
        font-size: 16px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .results-count strong {
        color: white;
        font-weight: 700;
    }

    /* Table fallback styling (if needed) */
    .table {
        width: 100%;
        margin-top: 20px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        overflow: hidden;
    }

    .table thead {
        background: rgba(255, 255, 255, 0.1);
    }

    .table th {
        padding: 15px;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 1px;
    }

    .table td {
        padding: 15px;
        color: rgba(255, 255, 255, 0.9);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .hidden {
        display: none;
    }

    /* Loading animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .product-card {
        animation: fadeIn 0.4s ease backwards;
    }

    .product-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .product-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .product-card:nth-child(3) {
        animation-delay: 0.15s;
    }

    .product-card:nth-child(4) {
        animation-delay: 0.2s;
    }

    .product-card:nth-child(5) {
        animation-delay: 0.25s;
    }

    .product-card:nth-child(6) {
        animation-delay: 0.3s;
    }
</style>