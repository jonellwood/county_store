<div class="header-holder">
    <h3>Select the report you would like to view</h3>
</div>
<div class="tab">

    <!-- <a href="./viewOrdersByDept.php">
        <button class="tablinks">Orders |</button>
    </a> -->

    <a href="./viewProductsTableNoSizes.php">
        <button class=" tablinks">Products & Prices |</button>
    </a>

    <a href="./viewProductsTable.php">
        <button class="tablinks">Product Prices & Sizes |</button>
    </a>
    <a href="./viewProductsColors.php">
        <button class="tablinks">Products & Colors |</button>
    </a>
    <a href="./viewProductsSizes.php">
        <button class="tablinks">Products & Sizes |</button>
    </a>
    <a href="./">
        <button class="tablinks">Clear |</button>
    </a>
</div>
<style>
    html {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        /* background-color: #ffe53b; */
        background-color: #dcd9d4;
        background-image: linear-gradient(to bottom,
                rgba(255, 255, 255, 0.5) 0%,
                rgba(0, 0, 0, 0.5) 100%),
            radial-gradient(at 50% 0%,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(0, 0, 0, 0.5) 50%);
        background-blend-mode: soft-light, screen;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    }

    .body {
        margin: 20px;
        display: flex;
        justify-content: center;
    }

    h3 {
        text-align: center;
    }

    .body {
        display: flex;
        justify-content: center;
    }

    img {
        width: 50px !important;
    }

    .header-holder {
        display: flex;
        justify-content: space-around;
    }

    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;


    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
        /* display: none; */
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

    .tabcontent {
        animation: fadeEffect 1s;
        /* Fading effect takes 1 second */
    }

    /* Go from zero to full opacity */
    @keyframes fadeEffect {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>