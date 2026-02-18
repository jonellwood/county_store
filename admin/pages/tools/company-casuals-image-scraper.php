<?php

/**
 * Company Casuals Image Scraper UI
 * Extracts product images from Company Casuals pages
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../../../components/header.php";

// Optional auth check - disabled by default
if (!defined('CC_IMAGE_SCRAPER_REQUIRE_AUTH')) {
    define('CC_IMAGE_SCRAPER_REQUIRE_AUTH', false);
}

if (CC_IMAGE_SCRAPER_REQUIRE_AUTH) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('location: ../../signin/signin.php');
        exit;
    }
}
?>
<!-- <!DOCTYPE html> -->
<!-- <html lang="en"> -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Casuals Image Scraper</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: #f5f7fa;
            min-height: 100vh;
        }

        .scraper-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-card {
            background: var(--primary-gradient);
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .url-input {
            font-size: 1rem;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: border-color 0.2s;
        }

        .url-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .btn-scrape {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-scrape:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .progress-section {
            display: none;
        }

        .progress-section.active {
            display: block;
        }

        .results-section {
            display: none;
        }

        .results-section.active {
            display: block;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .image-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .image-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f8f9fa;
            padding: 10px;
        }

        .image-card .card-body {
            padding: 15px;
        }

        .color-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .image-url {
            font-size: 0.75rem;
            color: #718096;
            word-break: break-all;
            line-height: 1.4;
        }

        .copy-btn {
            font-size: 0.8rem;
            padding: 4px 12px;
        }

        .product-info {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .log-output {
            background: #1a1a2e;
            color: #21f379;
            font-family: 'Monaco', 'Menlo', 'Courier New', monospace;
            font-size: 11px;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 8px;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .download-all-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }

        .download-all-btn:hover {
            background: linear-gradient(135deg, #0d7d72 0%, #2ecc71 100%);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .main-image-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary-gradient);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .image-wrapper {
            position: relative;
        }

        .copy-feedback {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2d3748;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            display: none;
            z-index: 1000;
            animation: fadeInUp 0.3s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="scraper-container">
            <!-- Header -->
            <div class="header-card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">
                            <i class="fas fa-images me-2"></i>
                            Company Casuals Image Scraper
                        </h2>
                        <p class="mb-0 opacity-75">
                            Extract product images for all color variants from Company Casuals pages
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="product-scraper.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="fas fa-dollar-sign me-1"></i> Price Scraper
                        </a>
                        <a href="product-image-downloader.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-download me-1"></i> Image Downloader
                        </a>
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="scraperForm">
                        <div class="row align-items-end">
                            <div class="col-md-9">
                                <label for="productUrl" class="form-label fw-bold">
                                    <i class="fas fa-link me-1"></i> Product URL
                                </label>
                                <input
                                    type="url"
                                    class="form-control url-input"
                                    id="productUrl"
                                    name="url"
                                    placeholder="https://www.companycasuals.com/printcharleston/b.jsp?id=8893788"
                                    required>
                                <small class="text-muted mt-1 d-block">
                                    Paste any Company Casuals product page URL
                                </small>
                            </div>
                            <div class="col-md-3 mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-scrape w-100" id="scrapeBtn">
                                    <i class="fas fa-search me-2"></i> Scrape Images
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="progress-section" id="progressSection">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-border text-primary me-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span id="statusText" class="fw-bold">Fetching product page...</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                id="progressBar"
                                role="progressbar"
                                style="width: 0%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="results-section" id="resultsSection">
                <!-- Product Info -->
                <div class="product-info" id="productInfo">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-2" id="productName">Product Name</h4>
                            <p class="text-muted mb-0">
                                <span class="badge bg-secondary me-2" id="productCode">CODE</span>
                                <span class="stats-badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-palette"></i>
                                    <span id="colorCount">0</span> colors
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="action-buttons justify-content-md-end">
                                <button class="btn btn-success download-all-btn" id="downloadAllImagesBtn">
                                    <i class="fas fa-download me-2"></i> Download All Images
                                </button>
                                <button class="btn btn-outline-success" id="downloadAllBtn">
                                    <i class="fas fa-file-download me-2"></i> Download URLs (txt)
                                </button>
                                <button class="btn btn-outline-secondary" id="copyAllBtn">
                                    <i class="fas fa-copy me-2"></i> Copy All URLs
                                </button>
                                <button class="btn btn-outline-secondary" id="exportJsonBtn">
                                    <i class="fas fa-file-export me-2"></i> Export JSON
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Grid -->
                <div class="image-grid" id="imageGrid">
                    <!-- Images will be inserted here -->
                </div>

                <!-- Log Section -->
                <div class="card mt-4" id="logCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-terminal me-2"></i> Scrape Log</span>
                        <button class="btn btn-sm btn-outline-secondary" id="toggleLogBtn">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="card-body p-0" id="logBody" style="display: none;">
                        <div class="log-output" id="logOutput"></div>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            <div class="alert alert-danger d-none" id="errorAlert" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorMessage"></span>
            </div>
        </div>
    </div>

    <!-- Copy Feedback Toast -->
    <div class="copy-feedback" id="copyFeedback">
        <i class="fas fa-check me-2"></i> <span id="copyFeedbackText">Copied to clipboard!</span>
    </div>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="company-casuals-image-scraper.js"></script>
</body>

</html>