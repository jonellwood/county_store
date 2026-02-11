<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../signin/signin.php");
    exit;
}

try {
    // Check if empNumber is set in the session
    if (!isset($_SESSION['empNumber'])) {
        throw new Exception('Employee number is not set in the session.');
    }

    // Validate empNumber
    if ($_SESSION['empNumber'] !== '4438' && $_SESSION['empNumber'] !== '6865') {
        header("Location: 401.php");
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: 401.php");
    exit;
}

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

$sql = "SELECT * from dep_ref ORDER by dep_head IS NULL, dep_assist IS NULL, dep_asset_mgr IS NULL";

$deps = array();
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $deps[] = $row;
    }
}

$eSql = "SELECT empNumber, empName from emp_sync order by empName";
$emps = array();
$eStmt = $conn->prepare($eSql);
$eStmt->execute();
$eRes = $eStmt->get_result();
if ($eRes->num_rows > 0) {
    while ($eRow = $eRes->fetch_assoc()) {
        $emps[] = $eRow;
    }
}

// Count statistics
$totalDepts = count($deps);
$assignedHeads = 0;
$assignedAssistants = 0;
$assignedAssetMgrs = 0;

foreach ($deps as $dep) {
    if (!empty($dep['dep_head'])) $assignedHeads++;
    if (!empty($dep['dep_assist'])) $assignedAssistants++;
    if (!empty($dep['dep_asset_mgr'])) $assignedAssetMgrs++;
}

// Include our modern header
include "../../components/header.php";
?>

<!-- Page-specific CSS -->
<link href="dept-admin.css" rel="stylesheet" />

<!-- Modern Layout Container -->
<div class="admin-dashboard-container">
    <!-- Alert Banner -->
    <div class="alert-banner" id="alert-banner">
        <i class="fas fa-info-circle"></i>
        <span>Department Administration - Manage department heads, assistants, and asset managers</span>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="main">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-building"></i>
                Department Management
            </h1>
        </div>

        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalDepts ?></h3>
                    <p>Total Departments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $assignedHeads ?></h3>
                    <p>Assigned Heads</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalDepts - $assignedHeads ?></h3>
                    <p>Needs Assignment</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input type="text" class="search-input" id="searchInput"
                placeholder="Search departments by name or number..."
                onkeyup="filterTable()">
        </div>

        <!-- Departments Table -->
        <table class="styled-table" id="deptTable">
            <thead>
                <tr>
                    <th>Dept #</th>
                    <th>Department Name</th>
                    <th>Department Head</th>
                    <th>Action</th>
                    <th>Department Assistant</th>
                    <th>Action</th>
                    <th>Asset Manager</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Table will be rendered by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Department Head Assignment -->
<div class="modal-backdrop" id="dhModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-tie"></i> Assign Department Head</h2>
            <button class="modal-close" onclick="hideModal('dhModal')">&times;</button>
        </div>
        <div class="modal-body">
            <label>Select employee to assign as <span class="role-badge">Department Head</span></label>
            <select id="dhSelect" onchange="setSelectedEmployee('dh', this.value)">
                <option value="">-- Select Employee --</option>
                <?php foreach ($emps as $emp): ?>
                    <option value="<?= htmlspecialchars($emp['empNumber']) ?>">
                        <?= htmlspecialchars($emp['empName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn btn-clear" onclick="clearAssignment('dh')">
                <i class="fas fa-times-circle"></i> Clear Assignment
            </button>
            <button class="btn btn-cancel" onclick="hideModal('dhModal')">
                <i class="fas fa-ban"></i> Cancel
            </button>
            <button class="btn btn-assign" onclick="saveAssignment('dh')">
                <i class="fas fa-check"></i> Assign
            </button>
        </div>
    </div>
</div>

<!-- Modal for Department Assistant Assignment -->
<div class="modal-backdrop" id="daModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-friends"></i> Assign Department Assistant</h2>
            <button class="modal-close" onclick="hideModal('daModal')">&times;</button>
        </div>
        <div class="modal-body">
            <label>Select employee to assign as <span class="role-badge">Department Assistant</span></label>
            <select id="daSelect" onchange="setSelectedEmployee('da', this.value)">
                <option value="">-- Select Employee --</option>
                <?php foreach ($emps as $emp): ?>
                    <option value="<?= htmlspecialchars($emp['empNumber']) ?>">
                        <?= htmlspecialchars($emp['empName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn btn-clear" onclick="clearAssignment('da')">
                <i class="fas fa-times-circle"></i> Clear Assignment
            </button>
            <button class="btn btn-cancel" onclick="hideModal('daModal')">
                <i class="fas fa-ban"></i> Cancel
            </button>
            <button class="btn btn-assign" onclick="saveAssignment('da')">
                <i class="fas fa-check"></i> Assign
            </button>
        </div>
    </div>
</div>

<!-- Modal for Asset Manager Assignment -->
<div class="modal-backdrop" id="amModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-clipboard-list"></i> Assign Asset Manager</h2>
            <button class="modal-close" onclick="hideModal('amModal')">&times;</button>
        </div>
        <div class="modal-body">
            <label>Select employee to assign as <span class="role-badge">Asset Manager</span></label>
            <select id="amSelect" onchange="setSelectedEmployee('am', this.value)">
                <option value="">-- Select Employee --</option>
                <?php foreach ($emps as $emp): ?>
                    <option value="<?= htmlspecialchars($emp['empNumber']) ?>">
                        <?= htmlspecialchars($emp['empName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn btn-clear" onclick="clearAssignment('am')">
                <i class="fas fa-times-circle"></i> Clear Assignment
            </button>
            <button class="btn btn-cancel" onclick="hideModal('amModal')">
                <i class="fas fa-ban"></i> Cancel
            </button>
            <button class="btn btn-assign" onclick="saveAssignment('am')">
                <i class="fas fa-check"></i> Assign
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Saving changes...</div>
</div>

<script>
    // Department data from PHP
    const departments = <?= json_encode($deps) ?>;

    // Current selection state
    let currentDept = null;
    let selectedEmployees = {
        dh: null,
        da: null,
        am: null
    };

    // Render the table
    function renderTable(data) {
        const tbody = document.getElementById('tableBody');
        let html = '';

        data.forEach(dept => {
            const dhName = dept.dep_head_empName || null;
            const daName = dept.dep_assist_empName || null;
            const amName = dept.dep_asset_mgr_empName || null;

            html += `
                <tr>
                    <td>
                        <span class="dept-badge">${dept.dep_num}</span>
                    </td>
                    <td>
                        <span class="dept-name">${dept.dep_name}</span>
                    </td>
                    <td>
                        ${renderEmployeeName(dhName, 'Department Head')}
                    </td>
                    <td>
                        <button class="btn btn-change" onclick="showModal('dhModal', '${dept.dep_num}')" data-tooltip="Change Department Head">
                            <i class="fas fa-edit"></i> Change
                        </button>
                    </td>
                    <td>
                        ${renderEmployeeName(daName, 'Department Assistant')}
                    </td>
                    <td>
                        <button class="btn btn-change" onclick="showModal('daModal', '${dept.dep_num}')" data-tooltip="Change Department Assistant">
                            <i class="fas fa-edit"></i> Change
                        </button>
                    </td>
                    <td>
                        ${renderEmployeeName(amName, 'Asset Manager')}
                    </td>
                    <td>
                        <button class="btn btn-change" onclick="showModal('amModal', '${dept.dep_num}')" data-tooltip="Change Asset Manager">
                            <i class="fas fa-edit"></i> Change
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Helper function to render employee name with avatar
    function renderEmployeeName(name, role) {
        if (!name || name === 'null' || name === '') {
            return `
                <div class="emp-name unassigned">
                    <div class="avatar">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <span class="name-text">Unassigned</span>
                </div>
            `;
        }

        const initials = name.split(' ')
            .map(n => n.charAt(0).toUpperCase())
            .slice(0, 2)
            .join('');

        return `
            <div class="emp-name">
                <div class="avatar">${initials}</div>
                <span class="name-text">${name}</span>
            </div>
        `;
    }

    // Show modal
    function showModal(modalId, deptNum) {
        currentDept = deptNum;
        const modal = document.getElementById(modalId);
        modal.classList.add('active');

        // Reset selection
        const selectId = modalId.replace('Modal', 'Select');
        document.getElementById(selectId).value = '';
    }

    // Hide modal
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('active');
        currentDept = null;
    }

    // Set selected employee
    function setSelectedEmployee(type, empNumber) {
        selectedEmployees[type] = empNumber;
    }

    // Save assignment
    function saveAssignment(type) {
        const emp = selectedEmployees[type];
        if (!emp) {
            alert('Please select an employee');
            return;
        }

        showLoading();

        let endpoint = '';
        switch (type) {
            case 'dh':
                endpoint = './change-dept-head.php';
                break;
            case 'da':
                endpoint = './change-dept-assist.php';
                break;
            case 'am':
                endpoint = './change-asset-mgr.php';
                break;
        }

        fetch(`${endpoint}?dep=${currentDept}&emp=${emp}`)
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Failed to save assignment');
                }
            })
            .catch(error => {
                hideLoading();
                alert('Error saving assignment: ' + error.message);
            });
    }

    // Clear assignment
    function clearAssignment(type) {
        if (!confirm('Are you sure you want to clear this assignment?')) {
            return;
        }

        showLoading();

        let endpoint = '';
        switch (type) {
            case 'dh':
                endpoint = './change-dept-head.php';
                break;
            case 'da':
                endpoint = './change-dept-assist.php';
                break;
            case 'am':
                endpoint = './change-asset-mgr.php';
                break;
        }

        fetch(`${endpoint}?dep=${currentDept}&emp=null`)
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Failed to clear assignment');
                }
            })
            .catch(error => {
                hideLoading();
                alert('Error clearing assignment: ' + error.message);
            });
    }

    // Show loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').classList.add('active');
    }

    // Hide loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.remove('active');
    }

    // Filter table
    function filterTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        const filteredData = departments.filter(dept => {
            return dept.dep_name.toLowerCase().includes(searchTerm) ||
                dept.dep_num.toString().includes(searchTerm) ||
                (dept.dep_head_empName && dept.dep_head_empName.toLowerCase().includes(searchTerm)) ||
                (dept.dep_assist_empName && dept.dep_assist_empName.toLowerCase().includes(searchTerm)) ||
                (dept.dep_asset_mgr_empName && dept.dep_asset_mgr_empName.toLowerCase().includes(searchTerm));
        });

        renderTable(filteredData);
    }

    // Close modal when clicking outside
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                currentDept = null;
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(modal => {
                modal.classList.remove('active');
            });
            currentDept = null;
        }
    });

    // Initialize table
    renderTable(departments);
</script>

</body>

</html>