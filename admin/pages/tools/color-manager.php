<?php
// Color Manager - Berkeley County Store Admin
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
    <title>Color Manager - Berkeley County Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .color-manager-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .color-preview-large {
            width: 100%;
            height: 150px;
            border-radius: 12px;
            border: 3px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .color-preview-small {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            display: inline-block;
            margin-right: 10px;
        }

        .multi-color-preview {
            display: flex;
            height: 150px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .color-segment {
            flex: 1;
            transition: all 0.3s ease;
        }

        .color-segment:hover {
            flex: 1.2;
        }

        .color-input-group {
            position: relative;
        }

        .color-picker {
            position: absolute;
            right: 10px;
            top: 38px;
            width: 50px;
            height: 38px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .hex-input {
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
            padding-right: 70px;
        }

        .colors-table {
            font-size: 0.9em;
        }

        .color-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .color-row:hover {
            background-color: #f8f9fa;
        }

        .badge-color {
            font-size: 0.85em;
            padding: 0.4em 0.8em;
        }

        .search-box {
            max-width: 400px;
        }

        .form-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }

        .form-card input,
        .form-card select {
            background: rgba(255, 255, 255, 0.95);
        }

        .color-sample {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            vertical-align: middle;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="color-manager-container">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="fas fa-palette me-2"></i>Color Manager</h2>
                            <p class="text-muted">Manage product colors and hex values</p>
                        </div>
                        <a href="../orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Add Color Form -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card form-card">
                        <div class="card-header border-0">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Color</h5>
                        </div>
                        <div class="card-body">
                            <form id="colorForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="colorName" class="form-label">Color Name *</label>
                                            <input type="text"
                                                class="form-control"
                                                id="colorName"
                                                placeholder="e.g., Navy Blue, Silver/Black"
                                                required>
                                            <div class="form-text text-white-50">
                                                Enter the full color name as it should appear
                                            </div>
                                        </div>

                                        <div class="mb-3 color-input-group">
                                            <label for="primaryHex" class="form-label">Primary Color (p_hex) *</label>
                                            <input type="text"
                                                class="form-control hex-input"
                                                id="primaryHex"
                                                placeholder="#000000"
                                                pattern="^#[0-9A-Fa-f]{6}$"
                                                required>
                                            <input type="color"
                                                class="color-picker"
                                                id="primaryPicker"
                                                value="#000000">
                                            <div class="form-text text-white-50">
                                                Primary/main color value
                                            </div>
                                        </div>

                                        <div class="mb-3 color-input-group">
                                            <label for="secondaryHex" class="form-label">Secondary Color (s_hex)</label>
                                            <input type="text"
                                                class="form-control hex-input"
                                                id="secondaryHex"
                                                placeholder="#FFFFFF (optional)"
                                                pattern="^#[0-9A-Fa-f]{6}$|^$">
                                            <input type="color"
                                                class="color-picker"
                                                id="secondaryPicker"
                                                value="#FFFFFF">
                                            <div class="form-text text-white-50">
                                                For multi-color items (e.g., Silver/Black)
                                            </div>
                                        </div>

                                        <div class="mb-3 color-input-group">
                                            <label for="tertiaryHex" class="form-label">Tertiary Color (t_hex)</label>
                                            <input type="text"
                                                class="form-control hex-input"
                                                id="tertiaryHex"
                                                placeholder="#FF0000 (optional)"
                                                pattern="^#[0-9A-Fa-f]{6}$|^$">
                                            <input type="color"
                                                class="color-picker"
                                                id="tertiaryPicker"
                                                value="#FF0000">
                                            <div class="form-text text-white-50">
                                                For three-color items (rare)
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Live Preview</label>
                                        <div id="colorPreview" class="multi-color-preview mb-3">
                                            <div class="color-segment" id="previewPrimary" style="background-color: #000000;"></div>
                                        </div>
                                        <div class="text-white-50 small">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Preview updates as you select colors
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-light btn-lg">
                                        <i class="fas fa-save me-2"></i>Save Color
                                    </button>
                                    <button type="button" class="btn btn-outline-light ms-2" onclick="resetForm()">
                                        <i class="fas fa-redo me-1"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text"
                                class="form-control"
                                id="searchInput"
                                placeholder="Search colors...">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">Total Colors: <strong id="totalColors">0</strong></span>
                </div>
            </div>

            <!-- Colors List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Colors</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover colors-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px;">ID</th>
                                            <th>Color Name</th>
                                            <th style="width: 150px;">Preview</th>
                                            <th style="width: 100px;">Primary</th>
                                            <th style="width: 100px;">Secondary</th>
                                            <th style="width: 100px;">Tertiary</th>
                                            <th style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="colorsTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Loading colors...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="color-manager.js"></script>
</body>

</html>