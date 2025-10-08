<?php
// Order Logs Viewer - Berkeley County Store Admin
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../signin/signin.php");
    exit;
}

if (!isset($_SESSION["role_id"]) || (int)$_SESSION["role_id"] !== 1) {
    header("location: ../../");
    exit;
}

$logFile = '/tmp/orders.log';
$logExists = file_exists($logFile);
$logContent = $logExists ? file_get_contents($logFile) : '';
$lineCount = $logExists ? count(file($logFile)) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Processing Logs - Berkeley County Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .log-content {
            background: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid #333;
            padding: 15px;
        }

        .log-stats {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-alt me-2"></i>Order Processing Logs</h2>
                    <div>
                        <a href="../orders/" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Orders
                        </a>
                        <button onclick="location.reload()" class="btn btn-primary">
                            <i class="fas fa-sync me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <?php if ($logExists): ?>
                    <div class="log-stats">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Log File:</strong> <?= $logFile ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Lines:</strong> <?= number_format($lineCount) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>File Size:</strong> <?= number_format(filesize($logFile)) ?> bytes
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Last Modified:</strong> <?= date('Y-m-d H:i:s', filemtime($logFile)) ?>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="clearLogs" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to clear the log file?')">
                                        <i class="fas fa-trash me-1"></i>Clear Logs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-terminal me-2"></i>Log Content
                                <small class="text-muted">(Most recent entries at bottom)</small>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="log-content"><?= htmlspecialchars($logContent) ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No log file found yet. Logs will appear here after the first order placement attempt.
                        <br><strong>Expected location:</strong> <?= $logFile ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-scroll to bottom of log content
        document.addEventListener('DOMContentLoaded', function() {
            const logContent = document.querySelector('.log-content');
            if (logContent) {
                logContent.scrollTop = logContent.scrollHeight;
            }
        });
    </script>
</body>

</html>

<?php
// Handle log clearing
if (isset($_POST['clearLogs']) && $logExists) {
    file_put_contents($logFile, '');
    header("Location: view-logs.php");
    exit;
}
?>