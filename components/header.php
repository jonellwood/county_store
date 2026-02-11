<?php



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkeley County Store - Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Navbar Styles - Embedded for immediate effect -->
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --bg-primary: #0F1740;
            ;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: #1e293b;
            --bg-input: #334155;
            --bg-hover: #475569;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-inverse: #1e293b;
            --border-primary: #475569;
            --border-secondary: #64748b;
            --border-accent: #3b82f6;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.25);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.25), 0 2px 4px -2px rgb(0 0 0 / 0.25);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.25), 0 4px 6px -4px rgb(0 0 0 / 0.25);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.4), 0 10px 10px -5px rgb(0 0 0 / 0.2);
            --font-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Modern Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-primary);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-sm) 0;
            transition: all 0.3s ease;
            z-index: 1030;
        }

        .navbar.fixed-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .navbar .container-fluid {
            width: 100%;
            max-width: none;
            padding-left: var(--spacing-lg);
            padding-right: var(--spacing-lg);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--text-primary) !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--primary-light) !important;
            transform: translateY(-1px);
        }

        .navbar-brand img {
            height: 32px;
            width: auto;
            filter: brightness(1.1);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover img {
            filter: brightness(1.3);
            transform: scale(1.05);
        }

        .navbar-toggler {
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-md);
            padding: var(--spacing-xs) var(--spacing-sm);
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 2px var(--primary) !important;
            border-color: var(--primary);
        }

        .navbar-toggler:hover {
            background-color: var(--bg-hover);
            border-color: var(--border-accent);
        }

        .navbar-toggler .fas {
            color: var(--text-primary);
            font-size: 1rem;
        }

        .navbar-nav {
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .navbar-nav.logout-nav {
            flex: 0;
            margin-left: auto;
        }

        .nav-item {
            margin: 0 var(--spacing-xs);
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: var(--spacing-sm) var(--spacing-md) !important;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background-color: var(--bg-hover);
            transform: translateY(-1px);
        }

        .nav-link:focus {
            box-shadow: 0 0 0 2px var(--primary);
            outline: none;
        }

        .nav-link.active,
        .nav-link[aria-current="page"] {
            color: var(--text-primary) !important;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: var(--shadow-md);
        }

        .nav-link.active:hover,
        .nav-link[aria-current="page"]:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .nav-link[href="#"] {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: var(--text-primary) !important;
            font-weight: 600;
            padding: var(--spacing-sm) var(--spacing-lg) !important;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .nav-link[href="#"]:hover {
            background: linear-gradient(135deg, var(--secondary) 20%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .nav-link[href*="logout"] {
            color: var(--error) !important;
            font-weight: 600;
            border: 1px solid var(--error);
            background: rgba(239, 68, 68, 0.1);
            padding: var(--spacing-sm) var(--spacing-lg) !important;
            border-radius: var(--radius-lg);
        }

        .nav-link[href*="logout"]:hover {
            background: var(--error);
            color: var(--text-primary) !important;
            border-color: var(--error);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .nav-link[target="_blank"]:after {
            content: "↗";
            margin-left: var(--spacing-xs);
            font-size: 0.8rem;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .nav-link[target="_blank"]:hover:after {
            opacity: 1;
            transform: translateX(2px);
        }

        .d-flex.align-items-center {
            gap: var(--spacing-md);
        }

        body {
            padding-top: 80px !important;
        }

        /* Admin Dropdown Styling */
        .dropdown-menu.admin-dropdown {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-sm);
            margin-top: var(--spacing-xs);
            min-width: 250px;
            backdrop-filter: blur(20px);
        }

        .dropdown-item {
            color: var(--text-secondary) !important;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--bg-hover) !important;
            color: var(--text-primary) !important;
            transform: translateX(2px);
        }

        .dropdown-item i {
            width: 18px;
            text-align: center;
            opacity: 0.8;
        }

        .dropdown-divider {
            border-color: var(--border-primary);
            margin: var(--spacing-sm) 0;
        }

        .nav-item.dropdown .nav-link.dropdown-toggle {
            position: relative;
        }

        .nav-item.dropdown .nav-link.dropdown-toggle::after {
            margin-left: var(--spacing-xs);
            transition: transform 0.3s ease;
        }

        .nav-item.dropdown .nav-link.dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: var(--bg-card);
                border-radius: var(--radius-lg);
                margin-top: var(--spacing-md);
                padding: var(--spacing-md);
                border: 1px solid var(--border-primary);
                box-shadow: var(--shadow-lg);
            }

            .navbar-nav {
                gap: var(--spacing-xs);
                justify-content: flex-start !important;
            }

            .navbar-nav.logout-nav {
                margin-left: 0 !important;
                margin-top: var(--spacing-md);
                padding-top: var(--spacing-md);
                border-top: 1px solid var(--border-primary);
            }

            .nav-item {
                margin: var(--spacing-xs) 0;
            }

            .nav-link {
                padding: var(--spacing-md) !important;
                border-radius: var(--radius-md);
                text-align: left;
            }
        }

        @supports (backdrop-filter: blur(20px)) {
            .navbar {
                background: rgba(30, 41, 59, 0.8) !important;
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(71, 85, 105, 0.3);
            }

            @media (max-width: 991.98px) {
                .navbar-collapse {
                    background: rgba(30, 41, 59, 0.95);
                    backdrop-filter: blur(20px);
                }
            }
        }
    </style>
</head>
<!--Main Navigation-->
<header>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <!-- Container wrapper -->
        <div class="container-fluid">
            <!-- Navbar brand -->
            <a class="navbar-brand" href="/admin/pages/employeeRequests.php">

                <img src="/assets/images/the-logo.png" height="32" alt="Berkeley County Store" loading="lazy">
            </a>

            <!-- Toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Collapsible wrapper -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Center links -->
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/pages/employeeRequests.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://store.berkeleycountysc.gov/inventory" target="_blank">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://store.berkeleycountysc.gov/support.php" target="_blank">
                            <i class="fas fa-question-circle me-2"></i>Help
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://store.berkeleycountysc.gov/" target="_blank">
                            <i class="fas fa-store me-2"></i>Store
                        </a>
                    </li>
                    <?php
                    // Only show Admin dropdown for users with role_id = 1
                    if (isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1):
                    ?>
                        <!-- Admin Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-2"></i>Admin
                            </a>
                            <ul class="dropdown-menu admin-dropdown" aria-labelledby="adminDropdown">
                                <!-- <li><a class="dropdown-item" href="/store/admin/pages/edit-request-ui.php">
                                        <i class="fas fa-edit me-2"></i>Edit Requests
                                    </a></li> -->
                                <li><a class="dropdown-item" href="/admin/pages/orders/">
                                        <i class="fas fa-shopping-cart me-2"></i>Department Orders
                                    </a></li>
                                <li><a class="dropdown-item" href="/admin/pages/invoicestopay-new.php">
                                        <i class="fas fa-receipt me-2"></i>Invoices To Pay
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/admin/pages/reports/">
                                        <i class="fas fa-chart-bar me-2"></i>Reports
                                    </a></li>
                                <li><a class="dropdown-item" href="/admin/pages/departmentSummaryReport-new.php">
                                        <i class="fas fa-building me-2"></i>Dept Summary
                                    </a></li>
                                <li><a class="dropdown-item" href="/admin/pages/orders-to-be-received-new.php">
                                        <i class="fas fa-truck me-2"></i>Receiving
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/admin/pages/edit-users/">
                                        <i class="fas fa-users me-2"></i>Edit Users
                                    </a></li>
                                <li><a class="dropdown-item" href="/admin/pages/dept-admin-new.php">
                                        <i class="fas fa-sitemap me-2"></i>Edit Dept
                                    </a></li>
                                <li><a class="dropdown-item" href="editProductsFilters.php">
                                        <i class="fas fa-filter me-2"></i>Edit Filters
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/admin/pages/add-product/">
                                        <i class="fas fa-plus me-2"></i>Add Product
                                    </a></li>
                                <li>
                                    <a class="dropdown-item" href="/admin/pages/edit-products/">
                                        <i class="fas fa-pen me-2"></i>Edit Product
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/pages/tools/company-casuals-scraper.php">
                                        <i class="fas fa-spider me-2"></i>Scrape Products
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/pages/tools/product-scraper.php">
                                        <i class="fas fa-images me-2"></i>Scrape Images
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/overview.php">
                                        <i class="fas fa-th-large me-2"></i>Old Dashboard
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Right logout link -->
                <ul class="navbar-nav logout-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="https://store.berkeleycountysc.gov/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Collapsible wrapper -->
        </div>
        <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->
</header>
<!--Main Navigation-->

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<body>

</body>

</html>