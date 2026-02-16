<?php
// Product Image Downloader - Database-driven approach
// Select a product from the DB, paste a sample image URL, download all color variants
session_start();

// Auth check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: ../../login.php");
    exit;
}
if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Image Downloader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .downloader-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .color-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            margin: 3px;
            border-radius: 20px;
            font-size: 0.85em;
            border: 1px solid #dee2e6;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .color-chip:hover {
            border-color: #0d6efd;
        }

        .color-chip.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        .color-chip .swatch {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            border: 1px solid #ccc;
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
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 5px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: white;
        }

        .image-card img {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
            border-radius: 4px;
        }

        .image-card.failed {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .product-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }

        .sample-url-help {
            background: #f0f7ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.9em;
        }

        .sample-url-help code {
            word-break: break-all;
        }

        #productSearch {
            position: relative;
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-results-dropdown.show {
            display: block;
        }

        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .search-result-item:hover {
            background: #f0f7ff;
        }

        .search-result-item .product-code {
            font-weight: 600;
            color: #0d6efd;
        }

        .search-result-item .product-name {
            color: #333;
        }

        .search-result-item .product-type {
            font-size: 0.8em;
            color: #999;
        }

        .detected-color {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 8px 14px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="downloader-container">

            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-images me-2"></i>Product Image Downloader</h2>
                        <a href="../tools.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Tools
                        </a>
                    </div>
                    <p class="text-muted">Select a product, paste a sample image URL, and download images for all colors.</p>
                </div>
            </div>

            <!-- Step 1: Select Product -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Step 1: Select Product</h5>
                        </div>
                        <div class="card-body">
                            <div id="productSearch" class="position-relative">
                                <label for="productSearchInput" class="form-label">Search for a product</label>
                                <input type="text"
                                    class="form-control"
                                    id="productSearchInput"
                                    placeholder="Type product code or name..."
                                    autocomplete="off">
                                <div id="searchResultsDropdown" class="search-results-dropdown"></div>
                            </div>

                            <div id="selectedProductInfo" class="mt-3" style="display:none;">
                                <div class="alert alert-success mb-0">
                                    <strong>Selected:</strong>
                                    <span id="selectedProductText"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="clearProduct()">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                    <input type="hidden" id="selectedProductId" value="">
                                    <input type="hidden" id="selectedProductCode" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Colors (auto-loaded) -->
            <div id="colorsSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Step 2: Product Colors</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="selectAllColors()">Select All</button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="deselectAllColors()">Deselect All</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="colorChips"></div>
                            <div class="mt-2 text-muted small">
                                <span id="colorCount">0</span> colors assigned to this product. Click to deselect any you don't need.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Sample URL -->
            <div id="urlSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-link me-2"></i>Step 3: Sample Image URL</h5>
                        </div>
                        <div class="card-body">
                            <div class="sample-url-help mb-3">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                Go to the SanMar product page, right-click on a product image, and copy the image URL.
                                Paste it below. The script will detect the color name and swap it for each color in the database.
                                <br><br>
                                <strong>Example:</strong><br>
                                <code>https://cdnp.sanmar.com/medias/sys_master/images/.../624Wx724H_72574_Black-0-112BlackHatLeft/624Wx724H-72574-Black-0-112BlackHatLeft.jpg</code>
                            </div>

                            <div class="mb-3">
                                <label for="sampleUrl" class="form-label">Sample Image URL</label>
                                <input type="url"
                                    class="form-control url-input"
                                    id="sampleUrl"
                                    placeholder="Paste a SanMar CDN image URL here..."
                                    oninput="detectColorInUrl()">
                            </div>

                            <div id="detectedColorSection" style="display:none;" class="mb-3">
                                <label class="form-label">Detected Color in URL</label>
                                <div>
                                    <span id="detectedColorDisplay" class="detected-color"></span>
                                    <div class="form-text mt-1">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        If this doesn't look right, you can override it below.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="sampleColorOverride" class="form-label">Color Name in URL <small class="text-muted">(auto-detected, or type to override)</small></label>
                                <input type="text"
                                    class="form-control"
                                    id="sampleColorOverride"
                                    placeholder="e.g., Black, AmberGold, DeepNavy">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="namingFormat" class="form-label">File Naming Format</label>
                                    <select class="form-control" id="namingFormat">
                                        <option value="code_color_view">{code}_{color}_{view}.jpg</option>
                                        <option value="code_color">{code}_{color}.jpg</option>
                                        <option value="color_code">{color}_{code}.jpg</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-primary btn-lg" id="downloadBtn" onclick="startDownload()" disabled>
                                    <i class="fas fa-download me-2"></i>Download All Images
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress -->
            <div id="progressSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5><i class="fas fa-cog fa-spin me-2"></i>Downloading images...</h5>
                            <div class="progress mb-2">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                            </div>
                            <div id="progressText" class="text-muted">Starting...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div id="resultsSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Download Results</h5>
                        </div>
                        <div class="card-body">
                            <div id="resultsSummary" class="mb-3"></div>
                            <div id="imageResults" class="results-grid"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed downloads -->
            <div id="failedSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Failed Downloads</h5>
                        </div>
                        <div class="card-body">
                            <div id="failedList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log -->
            <div id="logSection" class="row mb-4" style="display:none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Process Log</h5>
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleLog()">
                                <i class="fas fa-eye-slash"></i> Toggle
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div id="logOutput" class="log-output"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="product-image-downloader.js"></script>
</body>

</html>