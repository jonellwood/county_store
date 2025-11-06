<?php
/*
OPTION B: REVOLUTIONARY GRADIENT HERO (The Mind-Changer)
- NO static image! Modern animated gradient background
- Search bar front and center
- Clean category cards with icons
- Feels like a modern SaaS app
- Fully dark mode compatible
- Will blow Chris's mind
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
    <title>Berkeley County Store - Option B</title>
    <link href="./style/global-variables.css" rel="stylesheet" />
</head>

<body>

    <div class="sticky">
        <div class="alert-banner" id="alert-banner"> - </div>
        <?php include "./components/viewHead.php" ?>
    </div>

    <!-- REVOLUTIONARY HERO SECTION -->
    <section class="hero-gradient">
        <div class="hero-container">
            <div class="hero-content-wrapper">
                <h1 class="hero-title-modern">
                    <span class="title-main">Berkeley County</span>
                    <span class="title-sub">Employee Store</span>
                </h1>
                <p class="hero-tagline">Quality uniforms and gear at your fingertips</p>

                <!-- Search Bar -->
                <div class="search-container">
                    <form action="search.php" method="GET" class="search-form">
                        <input
                            type="text"
                            name="q"
                            class="search-input"
                            placeholder="Search for shirts, boots, accessories..."
                            autocomplete="off" />
                        <button type="submit" class="search-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                            </svg>
                            Search
                        </button>
                    </form>
                </div>

                <!-- Category Cards -->
                <div class="categories-container">
                    <h2 class="categories-title">Shop by Category</h2>
                    <div class="categories-grid">
                        <a href="mens-shirts-view.php" class="cat-card">
                            <div class="cat-icon">👕</div>
                            <h3 class="cat-title">Shirts</h3>
                            <p class="cat-desc">Polos, T-shirts & More</p>
                        </a>
                        <a href="mens-outerwear-view.php" class="cat-card">
                            <div class="cat-icon">🧥</div>
                            <h3 class="cat-title">Outerwear</h3>
                            <p class="cat-desc">Jackets & Vests</p>
                        </a>
                        <a href="hats-view.php" class="cat-card">
                            <div class="cat-icon">🧢</div>
                            <h3 class="cat-title">Hats</h3>
                            <p class="cat-desc">Caps & Beanies</p>
                        </a>
                        <a href="boots-view.php" class="cat-card">
                            <div class="cat-icon">👢</div>
                            <h3 class="cat-title">Boots</h3>
                            <p class="cat-desc">Work Boots</p>
                        </a>
                        <a href="ladies-shirts-view.php" class="cat-card">
                            <div class="cat-icon">👚</div>
                            <h3 class="cat-title">Ladies</h3>
                            <p class="cat-desc">Women's Apparel</p>
                        </a>
                        <a href="accessories-view.php" class="cat-card">
                            <div class="cat-icon">🎒</div>
                            <h3 class="cat-title">Accessories</h3>
                            <p class="cat-desc">Bags, Belts & More</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- POPULAR PRODUCTS SECTION -->
    <section class="trending-section">
        <div class="section-container">
            <div class="section-header-modern">
                <div class="header-badge">🔥 Trending</div>
                <h2 class="section-title-modern">Popular This Month</h2>
                <p class="section-subtitle">Most ordered items by your colleagues</p>
            </div>
            <div class="products-grid-modern" id="hot-sellers">
                <!-- Products loaded via JS -->
            </div>
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
       OPTION B: REVOLUTIONARY GRADIENT
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

        /* Animated Gradient Hero */
        .hero-gradient {
            position: relative;
            min-height: 85vh;
            margin-top: 80px;
            background: linear-gradient(135deg,
                    var(--color-primary) 0%,
                    var(--color-info) 50%,
                    var(--color-primary-dark) 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            overflow: hidden;
        }

        /* Subtle pattern overlay */
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Dark mode: different gradient */
        [data-theme='dark'] .hero-gradient {
            background: linear-gradient(135deg,
                    #1a1f35 0%,
                    #2d3748 50%,
                    #1a1f35 100%);
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-8) var(--spacing-4);
        }

        .hero-content-wrapper {
            text-align: center;
            color: white;
        }

        /* Modern Title */
        .hero-title-modern {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
            margin: 0 0 var(--spacing-3) 0;
        }

        .title-main {
            font-size: clamp(2rem, 6vw, 4rem);
            font-weight: 800;
            letter-spacing: -1px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .title-sub {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 300;
            opacity: 0.95;
        }

        .hero-tagline {
            font-size: clamp(1rem, 2vw, 1.25rem);
            margin: 0 0 var(--spacing-8) 0;
            opacity: 0.9;
        }

        /* Search Bar */
        .search-container {
            max-width: 700px;
            margin: 0 auto var(--spacing-8) auto;
        }

        .search-form {
            display: flex;
            gap: var(--spacing-3);
            background: rgba(255, 255, 255, 0.95);
            padding: var(--spacing-2);
            border-radius: var(--radius-xl);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .search-form:focus-within {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        .search-input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1.1rem;
            padding: var(--spacing-3) var(--spacing-4);
            color: #2d3748;
            outline: none;
        }

        .search-input::placeholder {
            color: #718096;
        }

        .search-button {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            background: var(--color-primary);
            color: white;
            border: none;
            padding: var(--spacing-3) var(--spacing-5);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .search-button:hover {
            background: var(--color-primary-dark);
            transform: scale(1.05);
        }

        /* Categories Section */
        .categories-container {
            margin-top: var(--spacing-8);
        }

        .categories-title {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            font-weight: 600;
            margin: 0 0 var(--spacing-5) 0;
            color: white;
            opacity: 0.95;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--spacing-4);
            max-width: 1200px;
            margin: 0 auto;
        }

        .cat-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-xl);
            padding: var(--spacing-5);
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
        }

        .cat-card:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .cat-icon {
            font-size: 3rem;
            margin-bottom: var(--spacing-3);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .cat-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 var(--spacing-2) 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cat-desc {
            font-size: 0.9rem;
            margin: 0;
            opacity: 0.85;
        }

        /* Trending Section */
        .trending-section {
            padding: var(--spacing-10) var(--spacing-4);
            background: var(--bg-body);
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header-modern {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }

        .header-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-warning), var(--color-danger));
            color: white;
            padding: var(--spacing-2) var(--spacing-4);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--spacing-3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .section-title-modern {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            margin: 0 0 var(--spacing-2) 0;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .products-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-gradient {
                min-height: 95vh;
                margin-top: 60px;
            }

            .search-form {
                flex-direction: column;
                gap: var(--spacing-2);
            }

            .search-button {
                width: 100%;
                justify-content: center;
            }

            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-3);
            }

            .cat-card {
                padding: var(--spacing-4);
            }

            .cat-icon {
                font-size: 2.5rem;
            }

            .products-grid-modern {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: var(--spacing-4);
            }
        }

        @media (max-width: 480px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

</body>

</html>