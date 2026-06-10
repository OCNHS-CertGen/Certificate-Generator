<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_login();

$page_title = "Welcome - OCNHS Digital Catalog";
require_once 'includes/header.php';

// Fetch pending count for the badge
$pending_result = $conn->query("SELECT COUNT(*) as total FROM certificate_requests WHERE status = 'Pending'");
$pending_count = $pending_result ? $pending_result->fetch_assoc()['total'] : 0;
?>

<!-- Custom Dashboard Styles -->
<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/notifications.css">

<div class="welcome-container fade-in">
    <!-- Functional Navigation -->
    <nav class="top-nav-selection no-print">
        <div style="display: flex; gap: 15px;">
            <a href="manage_requests.php" class="registry-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Online Requests
                <span id="pendingBadge"
                    style="background: #ff4757; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; margin-left: 5px; display: <?= $pending_count > 0 ? 'inline-block' : 'none' ?>;"><?= $pending_count ?></span>
            </a>

            <a href="history.php" class="history-btn-top">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Issuance History
            </a>
        </div>
    </nav>

    <!-- Branding Hero -->
    <header class="hero-selection">
        <div class="hero-logo-wrapper">
            <img src="assets/img/OCNHS LOGO.png" class="hero-logo-main" alt="OCNHS Seal">
        </div>
        <div class="hero-text">
            <h1>OCNHS Certification System</h1>
            <p>Select a certificate catalog below to generate official documents.</p>
        </div>
    </header>

    <!-- Template Selection Grid -->
    <main class="selection-grid">
        <div class="catalog-grid">

            <a href="request_form.php?type=CERTIFICATE OF ENROLLMENT" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <line x1="10" y1="9" x2="8" y2="9"></line>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Enrollment</h3>
                    <p>Current student status verification.</p>
                </div>
            </a>

            <div class="template-card interactive-group">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Good Moral Character</h3>
                    <div class="sub-options">
                        <a href="request_form.php?type=GOOD MORAL CHARACTER">Standard GMC</a>
                        <a href="request_form.php?type=GOOD MORAL CHARACTER (COLLEGE/SHS ADMISSION)">College/SHS
                            Admission</a>
                        <a href="request_form.php?type=GOOD MORAL CHARACTER (SCHOOL TRANSFER)">School Transfer</a>
                    </div>
                </div>
            </div>

            <a href="request_form.php?type=CERTIFICATE OF GRADUATION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Graduation</h3>
                    <p>Academic completion & diploma proof.</p>
                </div>
            </a>

            <a href="request_form.php?type=RECONSTRUCTED DIPLOMA" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Reconstructed Diploma</h3>
                    <p>Official Reconstructed Diploma copy.</p>
                </div>
            </a>

            <a href="request_form.php?type=CERTIFICATE OF COMPLETION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Completion</h3>
                    <p>JHS academic completion proof.</p>
                </div>
            </a>

            <a href="request_form.php?type=CERTIFICATE OF RANKING" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Ranking</h3>
                    <p>Official academic rank certifying.</p>
                </div>
            </a>

            <a href="request_form.php?type=TRANSFER CERTIFICATION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13"></path>
                        <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Transfer</h3>
                    <p>School transfer requirement documents.</p>
                </div>
            </a>

            <a href="request_form.php?type=SCHOLARSHIP RECOMMENDATION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 21v-4a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v4"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Scholarship</h3>
                    <p>Financial aid programs recommendation.</p>
                </div>
            </a>

            <a href="request_form.php?type=LOST ID CERTIFICATION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <circle cx="9" cy="10" r="2"></circle>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Lost ID</h3>
                    <p>Identity verification & replacement proof.</p>
                </div>
            </a>

            <a href="request_form.php?type=CERTIFICATE OF NON-ISSUANCE OF YEARBOOK" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>No Yearbook</h3>
                    <p>Verifies yearbook unavailability status.</p>
                </div>
            </a>

            <a href="request_form.php?type=RECORD DAMAGE CERTIFICATION" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Damaged Record</h3>
                    <p>Certification for record replacements.</p>
                </div>
            </a>

            <a href="request_form.php?type=SCHOOL ACCREDITATION CERTIFICATE" class="template-card">
                <div class="card-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon
                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                        </polygon>
                    </svg>
                </div>
                <div class="card-info">
                    <h3>Accreditation</h3>
                    <p>Official DepEd school status proof.</p>
                </div>
            </a>

        </div>
    </main>
</div>

<!-- Audio & Notification Toast -->
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

<!-- Dashboard Scripts -->
<script src="assets/js/dashboard.js"></script>

<?php require_once 'includes/footer.php'; ?>