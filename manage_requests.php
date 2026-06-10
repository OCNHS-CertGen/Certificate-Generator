<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/mailer.php';
require_login();

// Handle Status Updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];
    $status = '';

    if ($action === 'reject') {
        $status = 'Rejected';
        $remarks = $_GET['remarks'] ?? '';
        $remarksEscaped = $conn->real_escape_string($remarks);
        $processedBy = $_SESSION['user_id'];
        $conn->query("UPDATE certificate_requests SET remarks = '$remarksEscaped', processed_by = $processedBy WHERE id = $id");

        $req = $conn->query("SELECT student_name, email FROM certificate_requests WHERE id = $id")->fetch_assoc();
        if ($req) {
            $to = $req['email'];
            $subject = "Update: Your OCNHS Certificate Request was Rejected";
            $message = "Hello " . $req['student_name'] . ",\n\nUnfortunately, your certificate request has been rejected.\n\nReason: " . ($remarks ?: 'No specific remarks provided.') . "\n\nThank you,\nOCNHS EMIS Office";
            sendEmail($to, $subject, $message);
        }
    }
    if ($action === 'ready') {
        $status = 'Ready';
        $req = $conn->query("SELECT student_name, email, certificate_type FROM certificate_requests WHERE id = $id")->fetch_assoc();
        if ($req) {
            $to = $req['email'];
            $subject = "Ready for Pickup: Your OCNHS Certificate";
            $message = "Hello " . $req['student_name'] . ",\n\nYour requested document (" . $req['certificate_type'] . ") is now ready for pickup at the OCNHS EMIS Office.\n\nThank you,\nOCNHS EMIS Office";
            sendEmail($to, $subject, $message);
        }
    }
    if ($action === 'released') {
        $status = 'Released';
        $req = $conn->query("SELECT * FROM certificate_requests WHERE id = $id")->fetch_assoc();
        if ($req) {
            $checkLog = $conn->query("SELECT id FROM certificate_logs WHERE student_name = '" . $conn->real_escape_string($req['student_name']) . "' AND certificate_type = '" . $conn->real_escape_string($req['certificate_type']) . "' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")->num_rows;
            if ($checkLog == 0) {
                $today = date('F j, Y');
                $generatedBy = $_SESSION['user_id'];
                $stmt = $conn->prepare("INSERT INTO certificate_logs (certificate_type, student_name, lrn, grade_level, section_track, curriculum, school_year, purpose, date_issued, principal_name, generated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $principal = "N/A (Manual Process)";
                $stmt->bind_param("ssssssssssi", $req['certificate_type'], $req['student_name'], $req['lrn'], $req['grade_level'], $req['section_track'], $req['curriculum'], $req['school_year'], $req['purpose'], $today, $principal, $generatedBy);
                $stmt->execute();
            }
        }
    }
    if ($action === 'process_sf10') {
        $status = 'Processing';
        $req = $conn->query("SELECT student_name, email FROM certificate_requests WHERE id = $id")->fetch_assoc();
        if ($req) {
            $to = $req['email'];
            $subject = "Update: Your SF10 Request is now Processing";
            $message = "Hello " . $req['student_name'] . ",\n\nYour request for Form 137 / SF10 is now being processed manually.\n\nThank you,\nOCNHS EMIS Office";
            sendEmail($to, $subject, $message);
        }
    }

    if ($status) {
        $processedBy = $_SESSION['user_id'];
        $conn->query("UPDATE certificate_requests SET status = '$status', processed_by = $processedBy WHERE id = $id");
        header("Location: manage_requests.php?msg=Status updated to $status");
        exit();
    }
}

// Status Filter Logic
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "";
if ($status_filter) {
    $where_clause = " WHERE cr.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Pagination Logic
$items_per_page = 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $items_per_page;

// Count total records for pagination
$total_records = $conn->query("SELECT COUNT(*) FROM certificate_requests cr" . $where_clause)->fetch_row()[0];
$total_pages = ceil($total_records / $items_per_page);

$sql = "SELECT cr.*, u.full_name as processed_by_name FROM certificate_requests cr LEFT JOIN users u ON cr.processed_by = u.id $where_clause ORDER BY cr.created_at DESC LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests | OCNHS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/history.css">
    <link rel="stylesheet" href="assets/css/manage_requests.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/modals.css">
    <script src="assets/js/modals.js"></script>
</head>

<body>
    <div class="watermark-bg"></div>
    <div class="history-wrapper">
        <div class="history-header">
            <div class="history-header-inner">
                <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
                <h1 class="history-title">Online Certificate Requests</h1>
                <p class="history-subtitle">Process incoming requests from students • <span id="refreshStatus"
                        style="font-size: 0.8rem; color: #2ed573; font-weight: 600;">Monitoring Live...</span></p>
                <div id="pendingCounter" style="display: none;">
                    <span id="pendingCountNum">0</span> Pending Requests
                </div>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div
                style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Status Filter Tabs -->
        <div class="status-tabs">
            <a href="manage_requests.php" class="status-tab tab-all <?= $status_filter === '' ? 'active' : '' ?>">
                <span class="tab-dot"></span> All Requests
            </a>
            <a href="?status=Pending" class="status-tab tab-pending <?= $status_filter === 'Pending' ? 'active' : '' ?>">
                <span class="tab-dot"></span> Pending
            </a>
            <a href="?status=Processing"
                class="status-tab tab-processing <?= $status_filter === 'Processing' ? 'active' : '' ?>">
                <span class="tab-dot"></span> Processing
            </a>
            <a href="?status=Ready" class="status-tab tab-ready <?= $status_filter === 'Ready' ? 'active' : '' ?>">
                <span class="tab-dot"></span> Ready for Pickup
            </a>
            <a href="?status=Released" class="status-tab tab-released <?= $status_filter === 'Released' ? 'active' : '' ?>">
                <span class="tab-dot"></span> Released
            </a>
            <a href="?status=Rejected" class="status-tab tab-rejected <?= $status_filter === 'Rejected' ? 'active' : '' ?>">
                <span class="tab-dot"></span> Rejected
            </a>

            <div style="margin-left: auto; display: flex; align-items: center;">
                <span class="results-count">
                    <span class="count-number"><?= $total_records ?></span>
                    <?= $status_filter ?: 'Total' ?> Request<?= $total_records !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>

        <div class="table-container">
            <table class="history-table requests-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference #</th>
                        <th>Student Details</th>
                        <th>Certificate Type</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="<?= $row['status'] === 'Pending' ? 'pending-row' : '' ?>">
                                <td class="cell-timestamp"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td class="cell-mono"><?= $row['ref_number'] ?></td>
                                <td>
                                    <div class="student-info-main"><?= $row['student_name'] ?></div>
                                    <div class="student-info-sub">
                                        <span><?= $row['email'] ?></span>
                                        <span><?= $row['contact_number'] ?></span>
                                    </div>
                                </td>
                                <td><span class="cert-badge"><?= $row['certificate_type'] ?></span></td>
                                <td style="max-width: 200px; font-size: 0.85rem; color: #64748b;"><?= $row['purpose'] ?></td>
                                <td>
                                    <span
                                        class="status-pill status-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span>
                                </td>
                                <td class="actions-cell">
                                    <div class="btn-container">
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <button class="action-btn-p btn-verify-p" onclick='showVerifyModal(this)'
                                                data-row="<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>">Verify
                                                Request</button>
                                        <?php elseif ($row['status'] === 'Approved' || $row['status'] === 'Processing'): ?>
                                            <a href="?action=ready&id=<?= $row['id'] ?>" class="action-btn-p btn-ready-p">Ready for
                                                Pickup</a>
                                        <?php elseif ($row['status'] === 'Ready'): ?>
                                            <a href="?action=released&id=<?= $row['id'] ?>"
                                                class="action-btn-p btn-release-p">Release Document</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 50px;">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                    class="pagination-link <?= ($page <= 1) ? 'disabled' : '' ?>">&laquo; First</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>"
                    class="pagination-link <?= ($page <= 1) ? 'disabled' : '' ?>">&lsaquo; Prev</a>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1)
                    echo '<span class="pagination-ellipsis">...</span>';

                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                        class="pagination-link <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor;

                if ($end_page < $total_pages)
                    echo '<span class="pagination-ellipsis">...</span>';
                ?>

                <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, $page + 1)])) ?>"
                    class="pagination-link <?= ($page >= $total_pages) ? 'disabled' : '' ?>">Next &rsaquo;</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>"
                    class="pagination-link <?= ($page >= $total_pages) ? 'disabled' : '' ?>">Last &raquo;</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Verification Modal - RESTORED ORIGINAL -->
    <div id="verifyModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-close" onclick="closeVerifyModal()">&times;</div>
            <h2 style="color: var(--primary-color); margin-top: 0; font-family: 'Outfit', sans-serif;">Request
                Verification</h2>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <!-- Modal Body (Scrollable) -->
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding-right: 15px; margin-bottom: 20px;">
                <div style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.85rem;">Student Name:</p>
                            <p id="modal_name"
                                style="margin: 5px 0 0 0; font-weight: 800; color: #222; font-size: 1.1rem;"></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.85rem;">Certificate Requested:</p>
                            <p id="modal_type"
                                style="margin: 5px 0 0 0; font-weight: 800; color: var(--primary-color); font-size: 1.1rem;">
                            </p>
                        </div>
                        <div style="grid-column: span 2;">
                            <p style="margin: 0; color: #777; font-size: 0.85rem;">Purpose:</p>
                            <p id="modal_purpose" style="margin: 5px 0 0 0; font-weight: 600; color: #444;"></p>
                        </div>
                    </div>

                    <div
                        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; border-top: 1px dashed #ddd; padding-top: 20px;">
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">LRN:</p>
                            <p id="modal_lrn"
                                style="margin: 2px 0 0 0; font-weight: 700; font-family: monospace; font-size: 1.1rem;">
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Grade Level:</p>
                            <p id="modal_grade" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Section/Strand:</p>
                            <p id="modal_section" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Curriculum:</p>
                            <p id="modal_curriculum" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;">
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">S.Y. Graduated:</p>
                            <p id="modal_sy" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Birth Date:</p>
                            <p id="modal_bdate" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                        <div style="grid-column: span 2;">
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Birth Place:</p>
                            <p id="modal_bplace" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                        <div style="grid-column: span 3;">
                            <p style="margin: 0; color: #777; font-size: 0.9rem;">Address:</p>
                            <p id="modal_address" style="margin: 2px 0 0 0; font-weight: 700; font-size: 1.1rem;"></p>
                        </div>
                    </div>
                </div>

                <div class="verification-grid">
                    <div class="verification-item">
                        <h4 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--primary-color);">1. Valid ID
                            Submitted</h4>
                        <img id="modal_id_img" src="" class="verification-img" alt="Valid ID"
                            style="max-height: 450px; width: 100%; object-fit: contain; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <div class="verification-item">
                        <h4 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--primary-color);">2. Selfie for
                            Comparison</h4>
                        <img id="modal_selfie_img" src="" class="verification-img" alt="Selfie"
                            style="max-height: 450px; width: 100%; object-fit: contain; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Actions) -->
            <div
                style="padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 15px; justify-content: center;">
                <button id="modal_reject_btn" class="btn-reject">Reject Request</button>
                <a id="modal_approve_btn" href="" class="btn-approve">Verify & Approve</a>
            </div>
        </div>

        <!-- Image Lightbox Modal -->
        <div id="imageLightbox" class="modal-overlay"
            style="z-index: 10001; background: rgba(0,0,0,0.9); display: none; justify-content: center; align-items: flex-start; padding: 5vh 40px 40px 40px;"
            onclick="closeLightbox()">
            <span
                style="position: absolute; top: 20px; right: 30px; font-size: 50px; color: white; cursor: pointer; line-height: 1; z-index: 10002;">&times;</span>
            <img id="lightboxImg" src=""
                style="max-width: 85vw; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 50px rgba(0,0,0,0.8); object-fit: contain; border: 4px solid rgba(255,255,255,0.1);">
        </div>

        <!-- Notification Sound -->
        <audio id="notificationSound" preload="auto">
            <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
        </audio>

        <div id="newRequestToast">
            <div class="toast-content">
                <span class="toast-icon">🔔</span>
                <div>
                    <div class="toast-title">New Request!</div>
                    <div class="toast-msg">A student has submitted a new certificate request.</div>
                </div>
            </div>
        </div>

        <!-- Page Scripts -->
        <script src="assets/js/manage_requests.js"></script>
        <script>
            // Pass initial data to the external JS
            document.addEventListener('DOMContentLoaded', function () {
                const initialId = <?= ($result->num_rows > 0) ? $conn->query("SELECT MAX(id) FROM certificate_requests")->fetch_row()[0] : 0 ?>;
                initRequestsPage(initialId);
            });
        </script>
</body>

</html>