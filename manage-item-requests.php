<?php
/*
Created: 2026/02/11
Purpose: Admin interface for viewing and managing item requests
Organization: Berkeley County IT Department
Note: This page should be protected with proper authentication
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: admin/signin/signin.php');
    exit();
}

if (!isset($_SESSION['role_id']) || intval($_SESSION['role_id']) !== 1) {
    header('Location: admin/pages/401.php');
    exit();
}

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

// Dashboard stats
$stats = [
    'total_requests' => 0,
    'pending_requests' => 0,
    'under_review_requests' => 0,
    'completed_requests' => 0,
    'department_count' => 0,
    'total_items' => 0,
    'awaiting_review' => 0,
];

$statsResult = $conn->query("SELECT 
        COUNT(*) AS total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_requests,
        SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) AS under_review_requests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_requests,
        COUNT(DISTINCT dept_name) AS department_count
    FROM item_requests");

if ($statsResult && $statsRow = $statsResult->fetch_assoc()) {
    $stats = array_merge($stats, array_map('intval', $statsRow));
}

$itemCountResult = $conn->query("SELECT COUNT(*) AS total_items FROM request_items");
if ($itemCountResult && $itemCountRow = $itemCountResult->fetch_assoc()) {
    $stats['total_items'] = intval($itemCountRow['total_items']);
}

$stats['awaiting_review'] = $stats['pending_requests'] + $stats['under_review_requests'];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = intval($_POST['request_id']);
    $new_status = $_POST['status'];
    $review_notes = $_POST['review_notes'] ?? '';
    $reviewed_by = $_SESSION['admin_name'] ?? 'Admin'; // TODO: Get from session

    $stmt = $conn->prepare("UPDATE item_requests SET status = ?, reviewed_by = ?, review_date = NOW(), review_notes = ? WHERE request_id = ?");
    $stmt->bind_param("sssi", $new_status, $reviewed_by, $review_notes, $request_id);
    $stmt->execute();
    $stmt->close();

    // TODO: Send notification email to employee

    header('Location: manage-item-requests.php?updated=1');
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : 'all';

// Build query
$query = "SELECT r.*, COALESCE(isummary.total_items, 0) AS total_items, isummary.priority_rank
    FROM item_requests r
    LEFT JOIN (
        SELECT request_id,
               COUNT(*) AS total_items,
               MIN(CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END) AS priority_rank
        FROM request_items
        GROUP BY request_id
    ) AS isummary ON isummary.request_id = r.request_id
    WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND r.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if ($priority_filter !== 'all') {
    $priorityRanks = ['high' => 1, 'medium' => 2, 'low' => 3];
    if (isset($priorityRanks[$priority_filter])) {
        $query .= " AND isummary.priority_rank = " . intval($priorityRanks[$priority_filter]);
    }
}

$query .= " ORDER BY r.request_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Item Requests - Berkeley County Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-VkTXQb6l5k0U8nyCLlcHO9bHhtq/Y5cHIzoVmr18bODsPwhj/Kg2GNJUcRglN9aB" crossorigin="anonymous">
    <link href="./style/global-variables.css" rel="stylesheet" />
    <link href="./style/storeLux.css" rel="stylesheet" />
    <link href="./style/custom.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">
</head>

<body class="admin-shell">
    <div class="admin-wrapper container-xl py-4">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-start mb-4">
            <div>
                <p class="eyebrow text-uppercase mb-2">Store Admin</p>
                <h1 class="page-title">Item Requests Management</h1>
                <p class="page-subtitle">Monitor submissions, prioritize reviews, and keep departments in the loop.</p>
            </div>
            <div class="header-actions d-flex flex-wrap gap-2">
                <a href="support.php" class="btn btn-outline-light">Back to Support</a>
                <a href="request-items.php" class="btn btn-primary">Open Request Form</a>
            </div>
        </div>

        <div class="stats-grid mb-4">
            <div class="stat-card">
                <span class="stat-label">Total Requests</span>
                <span class="stat-value"><?= number_format($stats['total_requests']) ?></span>
                <span class="stat-caption text-success">All time</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Awaiting Review</span>
                <span class="stat-value"><?= number_format($stats['awaiting_review']) ?></span>
                <span class="stat-caption">Pending + Under Review</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Decisions</span>
                <span class="stat-value"><?= number_format($stats['pending_requests']) ?></span>
                <span class="stat-caption">Need action</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Items</span>
                <span class="stat-value"><?= number_format($stats['total_items']) ?></span>
                <span class="stat-caption">Across all requests</span>
            </div>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Request status updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="under_review" <?= $status_filter === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="denied" <?= $status_filter === 'denied' ? 'selected' : '' ?>>Denied</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="all" <?= $priority_filter === 'all' ? 'selected' : '' ?>>All Priorities</option>
                            <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="low" <?= $priority_filter === 'low' ? 'selected' : '' ?>>Low</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="manage-item-requests.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Items</th>
                                <th>Top Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $priorityBadgeMap = [
                                1 => ['class' => 'danger', 'label' => 'High'],
                                2 => ['class' => 'warning', 'label' => 'Medium'],
                                3 => ['class' => 'success', 'label' => 'Low'],
                            ];
                            ?>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['request_id'] ?></td>
                                        <td><?= date('m/d/Y', strtotime($row['request_date'])) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['emp_name']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['emp_email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['dept_name']) ?></td>
                                        <td>
                                            <?php $itemCount = intval($row['total_items']); ?>
                                            <div class="items-pill">
                                                <strong><?= $itemCount ?></strong> <?= $itemCount === 1 ? 'Item' : 'Items' ?>
                                            </div>
                                            <small class="text-muted">View details for breakdown</small>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['priority_rank']) && isset($priorityBadgeMap[$row['priority_rank']])): ?>
                                                <?php $priorityMeta = $priorityBadgeMap[$row['priority_rank']]; ?>
                                                <span class="badge bg-<?= $priorityMeta['class'] ?>"><?= $priorityMeta['label'] ?> Priority</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pending' => 'secondary',
                                                'under_review' => 'info',
                                                'approved' => 'success',
                                                'denied' => 'danger',
                                                'completed' => 'dark'
                                            ];
                                            $status_display = str_replace('_', ' ', ucwords($row['status'], '_'));
                                            ?>
                                            <span class="badge bg-<?= $status_class[$row['status']] ?? 'secondary' ?>"><?= $status_display ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="viewDetails(<?= $row['request_id'] ?>)">View</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No item requests found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing/editing request details -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modalContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        function viewDetails(requestId) {
            // Load request details via AJAX
            fetch('get-item-request-details.php?id=' + requestId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    modal.show();
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-XwEiHx8RJWb1x1oGf4bm/FjSX8eK3opgDdBrYqKqRR3YKHyCuXapnwYfJOLLm1A" crossorigin="anonymous"></script>
</body>

</html>

<style>
    body.admin-shell {
        min-height: 100vh;
        background: radial-gradient(120% 120% at 10% -20%, #1f4ed8 0%, rgba(14, 30, 64, 0) 55%),
            radial-gradient(80% 120% at 100% 0%, rgba(76, 29, 149, 0.7) 0%, rgba(3, 7, 18, 0) 60%),
            #050918;
        color: #f8fafc;
        font-family: 'Manrope', 'Segoe UI', sans-serif;
    }

    .admin-wrapper {
        max-width: 1200px;
    }

    .eyebrow {
        letter-spacing: 0.2em;
        color: rgba(248, 250, 252, 0.6);
        font-size: 0.75rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #f8fafc;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: rgba(248, 250, 252, 0.75);
        max-width: 540px;
    }

    .header-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .btn-outline-light {
        border-color: rgba(248, 250, 252, 0.4);
        color: #f8fafc;
    }

    .btn-outline-light:hover {
        border-color: #f8fafc;
        color: #050918;
        background-color: #f8fafc;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        border: none;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #7c3aed, #6366f1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
    }

    .stat-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 15px 35px rgba(2, 6, 23, 0.45);
    }

    .stat-label {
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.75rem;
        color: rgba(148, 163, 184, 0.9);
    }

    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        margin: 0.35rem 0;
    }

    .stat-caption {
        font-size: 0.85rem;
        color: rgba(148, 163, 184, 0.9);
    }

    .card {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(148, 163, 184, 0.1);
        border-radius: 24px;
        color: #e2e8f0;
    }

    .form-label,
    .table th {
        color: #c7d2fe;
        font-weight: 600;
    }

    .form-select,
    .form-control {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(99, 102, 241, 0.4);
        color: #f8fafc;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }

    .table {
        color: #e2e8f0;
    }

    .table thead {
        background: rgba(99, 102, 241, 0.08);
    }

    .table tbody tr {
        border-color: rgba(148, 163, 184, 0.08);
    }

    .table tbody tr:hover {
        background: rgba(79, 70, 229, 0.12);
    }

    .table> :not(caption)>*>* {
        color: unset !important;
        background-color: unset !important;

    }

    .items-pill {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(79, 70, 229, 0.15);
        font-weight: 600;
        color: #c7d2fe;
        margin-bottom: 0.25rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 999px;
    }

    .modal-content {
        background: #0f172a;
        color: #f8fafc;
        border-radius: 20px;
        border: 1px solid rgba(148, 163, 184, 0.25);
    }

    .modal-header {
        border-bottom-color: rgba(148, 163, 184, 0.2);
    }

    .modal-body strong {
        color: #c7d2fe;
    }

    @media (max-width: 768px) {
        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<?php $conn->close(); ?>