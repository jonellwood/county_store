<?php
// Company Casuals Product Scraper UI
session_start();

if (!defined('COMPANY_CASUALS_REQUIRE_AUTH')) {
    define('COMPANY_CASUALS_REQUIRE_AUTH', false);
}

if (COMPANY_CASUALS_REQUIRE_AUTH) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('location: ../../signin/signin.php');
        exit;
    }

    if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
        header('location: ../../');
        exit;
    }
}

require_once '../../../config.php';

$codes = [];
$connection = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($connection->connect_errno === 0) {
    $sql = "SELECT DISTINCT code FROM products_new WHERE code IS NOT NULL AND code <> '' ORDER BY code";
    if ($result = $connection->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $code = trim($row['code']);
            if ($code !== '') {
                $codes[] = $code;
            }
        }
        $result->free();
    }
    $connection->close();
}

$encodedCodes = json_encode($codes);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Casuals Price Scraper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .scraper-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .status-card {
            border-left: 4px solid #17a2b8;
            background: #f8f9fa;
        }

        .progress-section {
            display: none;
        }

        .progress-section.active {
            display: block;
        }

        .log-output {
            background: #111;
            color: #21f379;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 280px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 5px;
        }

        .product-info-card {
            background: linear-gradient(135deg, #1f8ef1 0%, #5e72e4 100%);
            color: white;
            border-radius: 12px;
        }

        .prices-table td,
        .prices-table th {
            text-align: center;
            vertical-align: middle;
        }

        .prices-table th:first-child,
        .prices-table td:first-child {
            text-align: left;
        }

        .code-normalized {
            font-family: "Courier New", monospace;
        }

        .import-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .drop-zone {
            border: 2px dashed rgba(33, 37, 41, 0.4);
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            transition: border-color 0.2s ease, background 0.2s ease;
            background: rgba(248, 249, 250, 0.65);
            cursor: pointer;
        }

        .drop-zone.dragover {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.08);
        }

        .drop-zone.disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .drop-zone small {
            display: block;
            color: #6c757d;
        }

        #importStatus {
            font-size: 0.95rem;
            margin-top: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="scraper-container">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><i class="fas fa-tags me-2"></i>Company Casuals Price Scraper</h2>
                            <p class="text-muted mb-0">Pull size-level pricing (and optional color list) for CompanyCasuals products.</p>
                        </div>
                        <div class="text-end">
                            <a class="btn btn-secondary" href="../orders.php"><i class="fas fa-arrow-left me-1"></i>Back</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Select Product</h5>
                </div>
                <div class="card-body">
                    <form id="companyCasualsForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="productCodeSelect" class="form-label">Choose Code from Database</label>
                                <select id="productCodeSelect" class="form-select" data-placeholder="Select product code">
                                    <option value="">-- Select Product Code --</option>
                                    <?php foreach ($codes as $code) : ?>
                                        <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($code) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Codes pulled from <code>products_new.code</code>. Leading <strong>SM-</strong> is removed automatically.</div>
                            </div>
                            <div class="col-md-3">
                                <label for="productCodeInput" class="form-label">Or enter code manually</label>
                                <input type="text" class="form-control" id="productCodeInput" placeholder="e.g. K525">
                            </div>
                            <div class="col-md-4">
                                <label for="productUrlInput" class="form-label">Or use full product URL</label>
                                <input type="url" class="form-control" id="productUrlInput" placeholder="https://www.companycasuals.com/...">
                                <div class="form-text">URL overrides product code when provided.</div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="includeColors" checked>
                                    <label class="form-check-label" for="includeColors">
                                        Include color list (bonus)
                                    </label>
                                </div>
                                <div class="form-text">Always use the first color’s price grid for size pricing.</div>
                            </div>
                            <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play me-2"></i>Run Scrape
                                </button>
                                <button type="button" id="clearBtn" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-eraser me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="progressSection" class="progress-section mb-4">
                <div class="card status-card">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-spinner fa-spin me-2"></i>Processing request...</h5>
                        <div class="progress mb-2">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 20%"></div>
                        </div>
                        <div id="statusText">Initializing...</div>
                    </div>
                </div>
            </div>

            <div id="resultsSection" style="display: none;">
                <div class="card product-info-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 id="resultProductName" class="mb-1">Product Name</h4>
                                <p class="mb-2">
                                    <span class="badge bg-light text-dark me-2">Original Code: <span id="resultOriginalCode" class="code-normalized">—</span></span>
                                    <span class="badge bg-light text-dark">Normalized Code: <span id="resultNormalizedCode" class="code-normalized">—</span></span>
                                </p>
                                <p class="mb-1">
                                    <strong>Reference color:</strong> <span id="resultReferenceColor">—</span>
                                </p>
                                <p class="mb-0">
                                    <strong>Source URL:</strong> <a href="#" target="_blank" id="resultSourceUrl" class="text-white text-decoration-underline">View on CompanyCasuals</a>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <p class="mb-2" id="resultTimestamp">Scraped at —</p>
                                <button type="button" id="exportBtn" class="btn btn-outline-light">
                                    <i class="fas fa-download me-1"></i>Export JSON
                                </button>
                                <button type="button" id="pushBtn" class="btn btn-warning ms-2">
                                    <i class="fas fa-database me-1"></i>Push to Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="resultPricesCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Size Pricing</h5>
                        <span class="text-muted" id="priceNote">Using first color’s price grid</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped prices-table" id="pricesTable">
                                <thead id="pricesHead"></thead>
                                <tbody id="pricesBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="colorsCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Available Colors</h5>
                    </div>
                    <div class="card-body">
                        <div id="colorsList" class="row g-2"></div>
                    </div>
                </div>

                <div class="card mb-4" id="importCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import Pricing into Database</h5>
                        <span class="text-muted">Vendor fixed to ID 5</span>
                    </div>
                    <div class="card-body">
                        <div class="import-actions mb-3">
                            <div class="flex-grow-1 drop-zone" id="dropZone">
                                <strong>Drag & drop a JSON file</strong>
                                <small>Accepts exported scraper files and batch runner outputs.</small>
                                <small class="fw-light">Or click to browse.</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" id="browseBtn">
                                    <i class="fas fa-folder-open me-1"></i>Select JSON
                                </button>
                            </div>
                        </div>
                        <input type="file" id="jsonFileInput" accept="application/json" style="display: none;">
                        <div id="importStatus" class="text-muted"></div>
                    </div>
                </div>
            </div>

            <div id="logSection" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Scrape Log</h5>
                        <button class="btn btn-sm btn-outline-secondary" id="toggleLogBtn"><i class="fas fa-compress me-1"></i>Collapse</button>
                    </div>
                    <div class="card-body p-0">
                        <div id="logOutput" class="log-output"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.COMPANY_CASUALS_CODES = <?= $encodedCodes ?: '[]' ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="company-casuals-scraper.js"></script>
</body>

</html>