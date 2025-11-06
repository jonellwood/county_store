<?php
/*
OPTION A: HERO IMAGE REDESIGN (Compromise Version)
- Keeps Chris's hero image (he'll love that)
- Reduces height to 50vh instead of 99vh (users see products faster)
- Adds functional category overlay buttons
- Modern parallax effect
- Dark mode ready
*/

require_once "config.php";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;

function checkMonthAndRedirect()
{
    if (date('F') === 'June') {
        header('Location: store-closed.php');
        exit();
    }
}
checkMonthAndRedirect();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkeley County Store - Option A</title>
    <link href="./style/global-variables.css" rel="stylesheet" />
</head>

<body>

    <div class="sticky">
        <div class="alert-banner" id="alert-banner"> - </div>
        <?php include "./components/viewHead.php" ?>
    </div>

    <!-- HERO SECTION WITH IMAGE + CATEGORY OVERLAY -->
    <section class="hero-section">
        <div class="hero-image-container">
            <img src="./County-Store-Image.png" alt="Berkeley County Store" class="hero-image" />
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1 class="hero-title">Berkeley County Employee Store</h1>
                    <p class="hero-subtitle">Quality uniforms and gear for our team</p>

                    <!-- Category Quick Links -->
                    <div class="category-grid">
                        <a href="mens-shirts-view.php" class="category-card">
                            <div class="category-icon">👕</div>
                            <div class="category-name">Shirts</div>
                        </a>
                        <a href="mens-outerwear-view.php" class="category-card">
                            <div class="category-icon">🧥</div>
                            <div class="category-name">Outerwear</div>
                        </a>
                        <a href="hats-view.php" class="category-card">
                            <div class="category-icon">🧢</div>
                            <div class="category-name">Hats</div>
                        </a>
                        <a href="boots-view.php" class="category-card">
                            <div class="category-icon">👢</div>
                            <div class="category-name">Boots</div>
                        </a>
                        <a href="accessories-view.php" class="category-card">
                            <div class="category-icon">🎒</div>
                            <div class="category-name">Accessories</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- POPULAR PRODUCTS SECTION -->
    <section class="popular-section">
        <div class="section-header">
            <h2>Popular This Month</h2>
            <p>Most ordered items by Berkeley County employees</p>
        </div>
        <div class="products-grid" id="hot-sellers">
            <!-- Products loaded via JS -->
        </div>
    </section>

    <?php include "cartSlideout.php" ?>
    <?php include "footer.php" ?>

    <script src="./functions/renderProduct.js"></script>
    <script src="functions/createIndexedDB.js"></script>
    <script src="functions/renderFiscalYearAlertBanner.js"></script>
    <script>
        renderBanner();

        async function fetchTopProducts() {
            await fetch('./API/fetchTopProducts.php')
                .then(response => response.json())
                .then(data => {
                    var productsHtml = '';
                    for (var i = 0; i < data.length; i++) {
                        productsHtml += renderProduct(data[i]);
                    }
                    document.getElementById('hot-sellers').innerHTML = productsHtml;
                })
                .catch(error => console.error(error));
        }
        fetchTopProducts();
    </script>

    <style>
        /* ===================================
       OPTION A: HERO IMAGE REDESIGN
       ================================== */

        body {
            margin: 0;
            padding: 0;
            background: var(--bg-body);
            color: var(--text-primary);
        }

        .sticky {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .alert-banner {
            position: sticky;
            top: 0;
            z-index: 20;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 50vh;
            min-height: 400px;
            max-height: 600px;
            margin-top: 80px;
            /* Account for sticky navbar */
            overflow: hidden;
        }

        .hero-image-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            filter: brightness(0.7);
            /* Darken for better text contrast */
        }

        /* Dark mode: slightly different filter */
        [data-theme='dark'] .hero-image {
            filter: brightness(0.5);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom,
                    rgba(0, 0, 0, 0.3) 0%,
                    rgba(0, 0, 0, 0.5) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-6);
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 1200px;
            width: 100%;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 700;
            margin: 0 0 var(--spacing-3) 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.25rem);
            margin: 0 0 var(--spacing-6) 0;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Category Grid */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: var(--spacing-4);
            max-width: 700px;
            margin: 0 auto;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .category-card:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-2);
        }

        .category-name {
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Popular Section */
        .popular-section {
            padding: var(--spacing-8) var(--spacing-4);
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: var(--spacing-6);
        }

        .section-header h2 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            margin: 0 0 var(--spacing-2) 0;
            color: var(--text-primary);
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-5);
            padding: var(--spacing-4) 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                height: 60vh;
                margin-top: 60px;
            }

            .category-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-3);
            }

            .category-card {
                padding: var(--spacing-3);
            }

            .category-icon {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: var(--spacing-4);
            }
        }

        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

</body>

</html>