<?php
/*
THE ULTIMATE VERSION - Best of Both Worlds!
- Chris's hero image (50vh, functional)
- Option B's beautiful category cards with descriptions
- Search bar from Option B (dual search is smart UX!)
- Centered/full-width popular products
- Cart notification for returning users
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

// Check if user has items in cart
$cartItemCount = $cart->total_items();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkeley County Employee Store</title>
    <link href="./style/global-variables.css" rel="stylesheet" />
</head>

<body>

    <div class="sticky">
        <div class="alert-banner" id="alert-banner"> - </div>
        <?php include "./components/viewHead.php" ?>
    </div>

    <!-- HERO SECTION WITH IMAGE -->
    <section class="hero-section">
        <div class="hero-image-container">
            <img src="./County-Store-Image.png" alt="Berkeley County Store" class="hero-image" />
            <div class="hero-overlay">
                <div class="hero-content">
                    <!-- <h1 class="hero-title">Berkeley County Employee Store</h1>
                    <p class="hero-subtitle">Quality uniforms and gear for our team</p> -->

                    <!-- SEARCH BAR (from Option B) -->
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

                    <!-- CATEGORY CARDS (Option B Style with descriptions) -->
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
                        <a href="boots-details.php" class="cat-card">
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

    <!-- RETURNING USER CART NOTIFICATION -->
    <?php if ($cartItemCount > 0): ?>
        <div class="cart-notification" id="cart-notification">
            <div class="notification-content">
                <div class="notification-icon">🛒</div>
                <div class="notification-text">
                    <strong>Welcome back!</strong> You have <?php echo $cartItemCount; ?> item<?php echo $cartItemCount > 1 ? 's' : ''; ?> waiting in your cart.
                </div>
                <button class="notification-view-cart" popovertarget="cart-slideout">
                    View Cart
                </button>
                <button class="notification-close" onclick="dismissCartNotification()">×</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- POPULAR PRODUCTS SECTION (Full Width/Centered) -->
    <section class="popular-section">
        <div class="section-container">
            <div class="section-header">
                <div class="header-badge">🔥 Trending</div>
                <h2>Popular This Month</h2>
                <p>Most ordered items by Berkeley County employees</p>
            </div>
            <div class="products-grid" id="hot-sellers">
                <!-- Products loaded via JS -->
            </div>
        </div>
    </section>

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

        // Cart notification dismiss function
        function dismissCartNotification() {
            const notification = document.getElementById('cart-notification');
            if (notification) {
                notification.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }
        }

        // Auto-dismiss cart notification after 10 seconds
        setTimeout(() => {
            dismissCartNotification();
        }, 10000);
    </script>

    <style>
        /* ===================================
       ULTIMATE HYBRID VERSION
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

        /* ===================================
       HERO SECTION WITH IMAGE
       ================================== */
        .hero-section {
            position: relative;
            height: 50vh;
            min-height: 500px;
            max-height: 700px;
            margin-top: 80px;
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
            filter: brightness(0.65);
        }

        [data-theme='dark'] .hero-image {
            filter: brightness(0.45);
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

        /* ===================================
       SEARCH BAR (from Option B)
       ================================== */
        .search-container {
            max-width: 700px;
            margin: 0 auto var(--spacing-6) auto;
            margin-top: 15px;
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

        /* ===================================
       CATEGORY GRID (Option B Style)
       ================================== */
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

        /* ===================================
       CART NOTIFICATION (Returning Users)
       ================================== */
        .cart-notification {
            position: fixed;
            bottom: var(--spacing-6);
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            animation: slideIn 0.4s ease-out;
            max-width: 600px;
            width: calc(100% - 2rem);
        }

        @keyframes slideIn {
            from {
                bottom: -100px;
                opacity: 0;
            }

            to {
                bottom: var(--spacing-6);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                bottom: var(--spacing-6);
                opacity: 1;
            }

            to {
                bottom: -100px;
                opacity: 0;
            }
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            background: var(--bg-elevated);
            border: 2px solid var(--color-success);
            border-radius: var(--radius-xl);
            padding: var(--spacing-4) var(--spacing-5);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .notification-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }

        .notification-text {
            flex: 1;
            color: var(--text-primary);
        }

        .notification-text strong {
            color: var(--color-success);
        }

        .notification-view-cart {
            background: var(--color-success);
            color: white;
            border: none;
            padding: var(--spacing-2) var(--spacing-4);
            border-radius: var(--radius-lg);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .notification-view-cart:hover {
            background: var(--color-success-dark);
            transform: scale(1.05);
        }

        .notification-close {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .notification-close:hover {
            background: var(--bg-surface);
            color: var(--text-primary);
        }

        /* ===================================
       POPULAR SECTION (Full Width/Centered)
       ================================== */
        .popular-section {
            padding: var(--spacing-10) var(--spacing-4);
            background: var(--bg-body);
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
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

        .section-header h2 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            margin: 0 0 var(--spacing-2) 0;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-6);
            width: 100%;
        }

        /* ===================================
       RESPONSIVE
       ================================== */
        @media (max-width: 768px) {
            .hero-section {
                height: 60vh;
                margin-top: 60px;
                min-height: 600px;
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

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: var(--spacing-4);
            }

            .cart-notification {
                bottom: var(--spacing-4);
                width: calc(100% - 1rem);
            }

            .notification-content {
                flex-direction: column;
                gap: var(--spacing-3);
                padding: var(--spacing-3);
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }

            .notification-view-cart {
                width: 100%;
            }
        }
    </style>

</body>

</html>