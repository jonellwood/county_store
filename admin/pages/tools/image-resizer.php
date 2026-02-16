<?php
session_start();
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
    <title>Batch Image Resizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .resizer-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .image-table img {
            max-height: 50px;
            border-radius: 4px;
        }

        .badge-needs-resize {
            background: #ffc107;
            color: #333;
        }

        .badge-ok {
            background: #28a745;
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
        <div class="resizer-container">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-compress-arrows-alt me-2"></i>Batch Image Resizer</h2>
                <a href="../tools.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Tools
                </a>
            </div>

            <!-- Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Folder Path</label>
                            <input type="text" class="form-control" id="folderPath"
                                placeholder="Leave blank for /product-images/">
                            <div class="form-text">Leave blank to use the default product-images folder</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Width (px)</label>
                            <input type="number" class="form-control" id="targetWidth" value="337">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Height (px)</label>
                            <input type="number" class="form-control" id="targetHeight" value="506">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quality</label>
                            <input type="number" class="form-control" id="quality" value="90" min="1" max="100">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Overwrite?</label>
                            <select class="form-control" id="overwrite">
                                <option value="1" selected>Yes</option>
                                <option value="0">No (save as _resized)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-info me-2" onclick="previewFolder()">
                            <i class="fas fa-search me-1"></i>Preview / Scan Folder
                        </button>
                        <button class="btn btn-primary" id="resizeBtn" onclick="runResize()" disabled>
                            <i class="fas fa-compress-arrows-alt me-1"></i>Resize All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Table -->
            <div id="previewSection" class="card mb-4" style="display:none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Images Found</h5>
                    <span id="previewSummary" class="text-muted"></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 image-table">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Filename</th>
                                    <th>Current Size</th>
                                    <th>File Size</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div id="resultsSection" class="card mb-4" style="display:none;">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Resize Results</h5>
                </div>
                <div class="card-body">
                    <div id="resultsSummary" class="mb-3"></div>
                    <div id="resultsLog" class="log-output"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFolder = '';

        function formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function previewFolder() {
            const folder = document.getElementById('folderPath').value.trim();

            fetch('image-resizer-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'preview',
                        folder: folder
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    currentFolder = data.folder;
                    const tbody = document.getElementById('previewBody');
                    const tgtW = parseInt(document.getElementById('targetWidth').value);
                    const tgtH = parseInt(document.getElementById('targetHeight').value);

                    tbody.innerHTML = data.images.map(img => {
                        const needsResize = img.width !== tgtW || img.height !== tgtH;
                        return `<tr>
                            <td><img src="/product-images/${img.filename}" alt="" onerror="this.style.display='none'"></td>
                            <td>${img.filename}</td>
                            <td>${img.width} × ${img.height}</td>
                            <td>${formatBytes(img.filesize)}</td>
                            <td>${needsResize
                                ? '<span class="badge badge-needs-resize">Needs Resize</span>'
                                : '<span class="badge badge-ok">OK</span>'
                            }</td>
                        </tr>`;
                    }).join('');

                    document.getElementById('previewSummary').textContent =
                        `${data.total} images — ${data.needs_resize} need resizing`;
                    document.getElementById('previewSection').style.display = '';
                    document.getElementById('resizeBtn').disabled = data.needs_resize === 0;
                })
                .catch(err => alert('Error: ' + err.message));
        }

        function runResize() {
            if (!confirm(`Resize all images in folder to ${document.getElementById('targetWidth').value}×${document.getElementById('targetHeight').value}?`)) {
                return;
            }

            document.getElementById('resizeBtn').disabled = true;
            document.getElementById('resizeBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Resizing...';

            fetch('image-resizer-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'resize',
                        folder: currentFolder,
                        width: parseInt(document.getElementById('targetWidth').value),
                        height: parseInt(document.getElementById('targetHeight').value),
                        quality: parseInt(document.getElementById('quality').value),
                        overwrite: document.getElementById('overwrite').value === '1'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('resizeBtn').disabled = false;
                    document.getElementById('resizeBtn').innerHTML = '<i class="fas fa-compress-arrows-alt me-1"></i>Resize All';

                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    const s = data.summary;
                    document.getElementById('resultsSummary').innerHTML = `
                        <div class="row text-center">
                            <div class="col-3"><div class="card bg-success text-white p-2"><h4>${s.resized}</h4>Resized</div></div>
                            <div class="col-3"><div class="card bg-secondary text-white p-2"><h4>${s.skipped}</h4>Skipped</div></div>
                            <div class="col-3"><div class="card ${s.failed > 0 ? 'bg-danger' : 'bg-secondary'} text-white p-2"><h4>${s.failed}</h4>Failed</div></div>
                            <div class="col-3"><div class="card bg-info text-white p-2"><h4>${s.target}</h4>Target</div></div>
                        </div>`;

                    const logLines = data.results.map(r => {
                        if (r.status === 'resized') return `✓ ${r.filename}: ${r.from} → ${r.to} (${formatBytes(r.filesize)})`;
                        if (r.status === 'skipped') return `— ${r.filename}: ${r.reason}`;
                        return `✗ ${r.filename}: ${r.error}`;
                    });
                    document.getElementById('resultsLog').textContent = logLines.join('\n');
                    document.getElementById('resultsSection').style.display = '';

                    // Refresh preview
                    previewFolder();
                })
                .catch(err => {
                    document.getElementById('resizeBtn').disabled = false;
                    document.getElementById('resizeBtn').innerHTML = '<i class="fas fa-compress-arrows-alt me-1"></i>Resize All';
                    alert('Error: ' + err.message);
                });
        }
    </script>
</body>

</html>