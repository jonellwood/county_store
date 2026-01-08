<?php
// SanMar Product Scraper - Berkeley County Store Admin
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../signin/signin.php");
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("location: ../../");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SanMar Product Scraper - Berkeley County Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .scraper-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .status-card {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }

        .progress-section {
            display: none;
        }

        .progress-section.active {
            display: block;
        }

        .image-preview {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin: 5px;
        }

        .product-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .image-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: white;
        }

        .url-input {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .log-output {
            background: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="scraper-container">
            <!-- 🏆 HEADER -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-download me-2"></i>SanMar Product Scraper</h2>
                        <a href="../orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Orders
                        </a>
                    </div>
                    <p class="text-muted">Automatically extract product information and download images from SanMar product pages</p>
                </div>
            </div>

            <!-- 🎯 URL INPUT SECTION -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-link me-2"></i>Product URL</h5>
                        </div>
                        <div class="card-body">
                            <form id="scraperForm">
                                <div class="mb-3">
                                    <label for="productUrl" class="form-label">SanMar Product URL</label>
                                    <input type="url"
                                        class="form-control url-input"
                                        id="productUrl"
                                        placeholder="https://www.sanmar.com/p/5682_DkGreenNv"
                                        value="https://www.sanmar.com/p/5682_DkGreenNv"
                                        required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Paste any SanMar product page URL. The tool will extract all available colors and images.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="imageFolder" class="form-label">Image Download Folder</label>
                                        <input type="text"
                                            class="form-control"
                                            id="imageFolder"
                                            value="product_images"
                                            placeholder="product_images">
                                        <div class="form-text">Folder name where images will be saved</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="namingFormat" class="form-label">File Naming Format</label>
                                        <select class="form-control" id="namingFormat">
                                            <option value="code_color_view">{code}_{color}_{view}.jpg</option>
                                            <option value="code_color">{code}_{color}.jpg</option>
                                            <option value="color_code">{color}_{code}.jpg</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-magic me-2"></i>Start Scraping
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="clearResults()">
                                        <i class="fas fa-trash me-1"></i>Clear Results
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📊 PROGRESS SECTION -->
            <div id="progressSection" class="progress-section">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card status-card">
                            <div class="card-body">
                                <h5><i class="fas fa-cog fa-spin me-2"></i>Processing...</h5>
                                <div class="progress mb-3">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                                <div id="statusText">Initializing scraper...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📋 PRODUCT INFO SECTION -->
            <div id="productInfoSection" style="display: none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card product-info-card">
                            <div class="card-body">
                                <h5><i class="fas fa-box me-2"></i>Product Information</h5>
                                <div id="productInfo"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🖼️ RESULTS SECTION -->
            <div id="resultsSection" style="display: none;">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-images me-2"></i>Downloaded Images</h5>
                                <button id="exportBtn" class="btn btn-success btn-sm" onclick="exportProductData()">
                                    <i class="fas fa-download me-1"></i>Export Product Data
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="imageResults" class="results-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📝 LOG SECTION -->
            <div id="logSection" style="display: none;">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Process Log</h5>
                            </div>
                            <div class="card-body p-0">
                                <div id="logOutput" class="log-output"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="product-scraper.js"></script>
</body>

</html>