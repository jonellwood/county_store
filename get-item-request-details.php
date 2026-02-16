<?php
/*
Created: 2026/02/11
Purpose: AJAX endpoint for loading item request details
Organization: Berkeley County IT Department
*/

// TODO: Add authentication check here
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     http_response_code(403);
//     exit('Unauthorized');
// }

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed');
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id <= 0) {
    http_response_code(400);
    exit('Invalid request ID');
}

$stmt = $conn->prepare("SELECT request_id, emp_number, emp_name, emp_email, dept_name, dept_number, reason, additional_notes, status, request_date, reviewed_by, review_date FROM item_requests WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit('Request not found');
}

$request = $result->fetch_assoc();
$stmt->close();

$itemsStmt = $conn->prepare("SELECT item_id, item_category, item_name, product_url, item_details, quantity_estimate, priority, item_status
    FROM request_items
    WHERE request_id = ?
    ORDER BY CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END, item_id ASC");
$itemsStmt->bind_param("i", $request_id);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$items = $itemsResult->fetch_all(MYSQLI_ASSOC);
$itemsStmt->close();
$conn->close();

// Format priority for display
$priority_display = [
    'low' => 'Low - Would be nice to have',
    'medium' => 'Medium - Needed soon',
    'high' => 'High - Urgent need'
];

// Determine highest priority across items
$priorityRanking = ['high' => 1, 'medium' => 2, 'low' => 3];
$topPriorityKey = null;
foreach ($items as $item) {
    $key = strtolower($item['priority']);
    if (!isset($priorityRanking[$key])) {
        continue;
    }
    if ($topPriorityKey === null || $priorityRanking[$key] < $priorityRanking[$topPriorityKey]) {
        $topPriorityKey = $key;
    }
}

$priority_class = [
    'high' => 'danger',
    'medium' => 'warning',
    'low' => 'success'
];

// Format status for display
$status_display = str_replace('_', ' ', ucwords($request['status'], '_'));
?>

<div class="modal-header">
    <h5 class="modal-title">Item Request #<?= $request['request_id'] ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row g-4">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Employee Information</h6>
            <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($request['emp_name']) ?></p>
            <p class="mb-2"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($request['emp_email']) ?>"><?= htmlspecialchars($request['emp_email']) ?></a></p>
            <p class="mb-2"><strong>Employee #:</strong> <?= htmlspecialchars($request['emp_number']) ?></p>
            <p class="mb-2"><strong>Department:</strong> <?= htmlspecialchars($request['dept_name']) ?><?php if (!empty($request['dept_number'])): ?><span class="text-muted"> (#<?= htmlspecialchars($request['dept_number']) ?>)</span><?php endif; ?></p>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Request Information</h6>
            <p class="mb-2"><strong>Date Submitted:</strong> <?= date('F j, Y \a\t g:i A', strtotime($request['request_date'])) ?></p>
            <p class="mb-2"><strong>Current Status:</strong>
                <?php
                $status_class = [
                    'pending' => 'secondary',
                    'under_review' => 'info',
                    'approved' => 'success',
                    'denied' => 'danger',
                    'completed' => 'dark'
                ];
                ?>
                <span class="badge bg-<?= $status_class[$request['status']] ?? 'secondary' ?>"><?= $status_display ?></span>
            </p>
            <p class="mb-2"><strong>Highest Priority:</strong>
                <?php if ($topPriorityKey): ?>
                    <span class="badge bg-<?= $priority_class[$topPriorityKey] ?>"><?= $priority_display[$topPriorityKey] ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary">Not provided</span>
                <?php endif; ?>
            </p>
            <?php if ($request['reviewed_by']): ?>
                <p class="mb-2"><strong>Reviewed By:</strong> <?= htmlspecialchars($request['reviewed_by']) ?></p>
                <p class="mb-2"><strong>Review Date:</strong> <?= date('F j, Y', strtotime($request['review_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="fw-bold text-primary mb-3">Requested Items (<?= count($items) ?>)</h6>
    <?php if (!empty($items)): ?>
        <?php foreach ($items as $index => $item): ?>
            <?php $itemPriorityKey = strtolower($item['priority']); ?>
            <div class="border rounded-3 p-3 mb-3 bg-dark bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold">Item #<?= $index + 1 ?></span>
                    <span class="badge bg-<?= $priority_class[$itemPriorityKey] ?? 'secondary' ?>"><?= ucfirst($item['priority']) ?> Priority</span>
                </div>
                <p class="mb-1"><strong>Category:</strong> <?= ucwords($item['item_category']) ?></p>
                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
                <p class="mb-1"><strong>Quantity:</strong> <?= htmlspecialchars($item['quantity_estimate']) ?></p>
                <p class="mb-1"><strong>Product Link:</strong>
                    <a href="<?= htmlspecialchars($item['product_url']) ?>" target="_blank" rel="noopener">Open product</a>
                </p>
                <?php if (!empty($item['item_details'])): ?>
                    <div class="mt-2 p-3 bg-secondary bg-opacity-10 rounded">
                        <?= nl2br(htmlspecialchars($item['item_details'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No items were captured for this request.</p>
    <?php endif; ?>

    <div class="mb-3">
        <strong>Reason for Request:</strong>
        <div class="mt-2 p-3 bg-secondary bg-opacity-10 rounded"><?= nl2br(htmlspecialchars($request['reason'])) ?></div>
    </div>

    <?php if (!empty($request['additional_notes'])): ?>
        <div class="mb-3">
            <strong>Additional Notes:</strong>
            <div class="mt-2 p-3 bg-secondary bg-opacity-10 rounded"><?= nl2br(htmlspecialchars($request['additional_notes'])) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($request['review_notes'])): ?>
        <div class="mb-3">
            <strong>Review Notes:</strong>
            <div class="mt-2 p-3 bg-warning bg-opacity-10 border border-warning rounded"><?= nl2br(htmlspecialchars($request['review_notes'])) ?></div>
        </div>
    <?php endif; ?>

    <hr class="my-4">

    <h6 class="fw-bold text-primary mb-3">Update Status</h6>
    <form method="POST" action="manage-item-requests.php">
        <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
        <input type="hidden" name="update_status" value="1">

        <div class="mb-3">
            <label for="status" class="form-label">New Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="under_review" <?= $request['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                <option value="approved" <?= $request['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="denied" <?= $request['status'] === 'denied' ? 'selected' : '' ?>>Denied</option>
                <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="review_notes" class="form-label">Review Notes</label>
            <textarea name="review_notes" id="review_notes" class="form-control" rows="3" placeholder="Add notes about this status change..."><?= htmlspecialchars($request['review_notes'] ?? '') ?></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
    </form>
</div>