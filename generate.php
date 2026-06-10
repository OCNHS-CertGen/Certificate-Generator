<?php
require_once 'includes/auth.php';
require_login();

if (!isset($_POST['generate'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/certificate_logic.php';

// Sanitize and prepare data
$data = [
    'region' => $_POST['region'] ?? SCHOOL_INFO['region'],
    'division' => $_POST['division'] ?? SCHOOL_INFO['division'],
    'school_name' => $_POST['school_name'] ?? SCHOOL_INFO['school_name'],
    'school_address' => $_POST['school_address'] ?? SCHOOL_INFO['school_address'],
    'date_issued' => $_POST['date_issued'] ?? date('F j, Y'),
    'student_name' => strtoupper($_POST['student_name']),
    'lrn' => $_POST['lrn'],
    'grade_level' => trim($_POST['grade_level']),
    'school_level' => $_POST['school_level'] ?? '',
    'section_track' => strtoupper($_POST['section_track']),
    'curriculum' => strtoupper($_POST['curriculum']),
    'school_year' => $_POST['school_year'],
    'purpose' => strtoupper($_POST['purpose']),
    'certificate_type' => strtoupper($_POST['certificate_type']),
    'principal_name' => $_POST['principal_name'],
    'principal_title' => strtoupper($_POST['principal_title']),
    'rcc_code' => $_POST['rcc_code'] ?? '',
    'original_principal' => $_POST['original_principal'] ?? '',
    'original_superintendent' => $_POST['original_superintendent'] ?? '',
    'superintendent_name' => $_POST['superintendent_name'] ?? '',
    'superintendent_title' => $_POST['superintendent_title'] ?? '',
    'request_id' => $_POST['request_id'] ?? null,
    'place_of_birth' => strtoupper($_POST['place_of_birth'] ?? ''),
    'birth_date' => $_POST['birth_date'] ?? '',
    'address' => strtoupper($_POST['address'] ?? ''),
];

$is_diploma = ($data['certificate_type'] === 'RECONSTRUCTED DIPLOMA');

// Process Grade text
$data['grade'] = is_numeric($data['grade_level']) ? "GRADE " . $data['grade_level'] : strtoupper($data['grade_level']);

// Get formatted date
$formatted_date = date('d F, Y', strtotime($data['date_issued']));

// Fetch Certificate Content
$cert_content = getCertificateContent($data['certificate_type'], $data);
$title_text = $cert_content['title'];
$body_content = $cert_content['body'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo str_replace('<br>', ' ', $title_text); ?></title>
    <link rel="stylesheet" href="assets/css/certificate.css">
    <?php if ($is_diploma): ?>
        <style>
            @media print {
                @page {
                    size: A4 landscape !important;
                }

                .certificate-container.diploma-layout {
                    width: 11.23in !important;
                    height: 7.9in !important;
                }
            }
        </style>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
</head>

<body>
    <div class="watermark-bg"></div>

    <div class="no-print actions-toolbar">
        <a href="dashboard.php" class="floating-home-btn" title="Go Home">
            <svg viewBox="0 0 24 24">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="white" />
            </svg>
        </a>
        <button class="action-btn print-btn" id="printBtn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Certificate
        </button>
        <button class="action-btn edit-btn" onclick="history.back()">
            <i class="fas fa-edit"></i> Edit Data
        </button>
    </div>

    <div class="certificate-container <?php echo $is_diploma ? 'diploma-layout' : ''; ?>">
        <?php if ($is_diploma): ?>

            <div class="header">
                <div class="diploma-logos">
                    <img src="assets/img/deped_seal_new.png" class="header-logo logo-left" alt="DepEd Seal">
                    <img src="assets/img/OCNHS LOGO.png" class="header-logo logo-right" alt="School Logo">
                </div>
                <div
                    style="font-family: 'Times New Roman', serif; font-size: 15pt; font-weight: bold; letter-spacing: 2px; margin-bottom: 2px;">
                    RECONSTRUCTED</div>
                <p class="republic-text"
                    style="font-size: 11pt; font-weight: bold; letter-spacing: 2px; margin-bottom: 2px;">Republika ng
                    Pilipinas</p>
                <p class="republic-text-en" style="font-size: 9pt; font-style: italic; margin-top: 2px;">Republic of the
                    Philippines</p>
                <p class="dept-text" style="font-family: 'Times New Roman', serif; font-weight: bold; font-size: 14pt;">
                    KAGAWARAN NG EDUKASYON</p>
                <p class="dept-text-en" style="font-size: 9pt; font-style: italic; margin-top: 2px;">Department of
                    Education</p>
                <p class="region-text" style="font-weight: bold; margin-top: 5px;">PANREHIYONG TANGGAPAN III</p>
                <p class="region-text-en" style="font-size: 9pt; font-style: italic; margin-top: 1px;">Region III</p>
                <p class="division-text">Lungsod ng Olongapo</p>
                <p class="division-text-en" style="font-size: 9pt; font-style: italic; margin-top: 1px;">Olongapo City
                    Division</p>
                <p class="school-name" style="font-weight: 900; font-size: 12pt;">PAMBANSANG MATAAS NA PAARALAN NG LUNGSOD
                    NG OLONGAPO</p>
                <p class="school-name-en" style="font-size: 10pt; font-style: italic; margin-top: 2px;">Olongapo City
                    National High School</p>
            </div>

            <div class="content">
                <?php echo $body_content; ?>
                <div class="date-line">
                    Nilagdaan sa Lungsod ng Olongapo, Pilipinas ngayong
                    <?php echo date('j', strtotime($data['date_issued'])); ?> ng
                    <?php echo date('F, Y', strtotime($data['date_issued'])); ?>.
                    <br>
                    <span style="font-style: italic; font-size: 0.9em;">Signed in Olongapo City, Philippines on this
                        <?php echo date('jS', strtotime($data['date_issued'])); ?> day of
                        <?php echo date('F, Y', strtotime($data['date_issued'])); ?>.</span>
                </div>
            </div>

            <div class="signatory-section diploma-signatures">
                <!-- Row 1: Names -->
                <div class="sig-name left"><?php echo h($data['original_principal']); ?> (Originally signed)</div>
                <div class="sig-name center"><?php echo h($data['principal_name']); ?></div>
                <div class="sig-name right"><?php echo h($data['superintendent_name']); ?> (Originally signed)</div>

                <!-- Row 2: Titles -->
                <div class="sig-title left">Punong-guro<br>Principal</div>
                <div class="sig-title center">Punong-guro<br>Principal</div>
                <div class="sig-title right">Tagapamahala ng mga Paaralan<br><?php echo h($data['superintendent_title']); ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Header -->
            <div class="header">
                <img src="assets/img/deped_seal_new.png" class="header-logo" alt="DepEd Seal">
                <p class="republic-text old-english">Republic of the Philippines</p>
                <p class="dept-text old-english">Department of Education</p>
                <p class="region-text"><?php echo h($data['region']); ?></p>
                <p class="division-text"><?php echo h($data['division']); ?></p>
                <p class="school-name"><?php echo h($data['school_name']); ?></p>
                <p class="school-address"><?php echo h($data['school_address']); ?></p>

                <div class="header-line"></div>
                <div class="date-line"><?php echo $formatted_date; ?></div>
            </div>

            <!-- Title -->
            <div class="title-section">
                <span class="cert-label"><?php echo $title_text; ?></span>
            </div>

            <div class="salutation">TO WHOM IT MAY CONCERN:</div>

            <!-- Body -->
            <div class="content <?php echo (strpos($data['certificate_type'], 'GOOD MORAL CHARACTER') !== false) ? 'double-spaced' : ''; ?>">
                <?php echo $body_content; ?>
            </div>

            <!-- Signatory -->
            <div class="signatory-section">
                <div class="dry-seal-box">
                    <?php if (!empty($data['rcc_code'])): ?>
                        <div class="rcc-code"><?php echo h($data['rcc_code']); ?></div>
                    <?php endif; ?>
                    dry seal here
                </div>

                <div class="principal-box">
                    <div class="principal-name"><?php echo h($data['principal_name']); ?></div>
                    <div class="principal-title"><?php echo h($data['principal_title']); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$is_diploma): ?>
            <!-- Footer -->
            <div class="footer-section">
                <div class="footer-logos-left">
                    <img src="assets/img/DepED MATATAG.png" alt="DepEd MATATAG" style="height: 70px; width: auto;">
                    <div class="footer-logo"><img src="assets/img/OCNHS LOGO.png" alt="School Logo"></div>
                </div>

                <div class="footer-info-right">
                    <div class="footer-text">
                        <div><strong>Address:</strong> <?php echo h($data['school_address']); ?></div>
                        <div><strong>Contact No.:</strong> (047) 223-3744</div>
                        <div><strong>Email Address:</strong> olongapocitynationalhighschool@gmail.com</div>
                        <div><strong>Official Website:</strong> https://ocnhs.edu.ph</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let hasLogged = false;

        function celebrate() {
            const duration = 3 * 1000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) { return Math.random() * (max - min) + min; }

            const interval = setInterval(function () {
                const timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) return clearInterval(interval);

                const particleCount = 50 * (timeLeft / duration);
                confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }, colors: ['#002D72', '#FFD700'] });
                confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }, colors: ['#0056b3', '#ffffff'] });
            }, 250);
        }

        window.addEventListener('afterprint', function () {
            if (hasLogged) return;
            if (confirm('Was the certificate successfully printed or saved?\nClick OK to save to history.')) {
                hasLogged = true;
                celebrate();

                fetch('api/save_log.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(<?php echo json_encode(array_merge($data, ['date_issued' => $formatted_date])); ?>)
                })
                    .then(async response => {
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Invalid server response: ' + text.substring(0, 100));
                        }
                    })
                    .then(result => {
                        console.log('Log response:', result);
                        if (result.status === 'success') {
                            celebrate();
                        } else {
                            alert('Error saving history: ' + result.message);
                        }
                    })
                    .catch(err => {
                        console.error('Save Error:', err);
                        alert('System Error: ' + err.message + '\n\nPlease check if your database is connected and refreshed.');
                    });
            }
        });

        // Magnetic effects
        document.querySelectorAll('.floating-home-btn, .action-btn').forEach(btn => {
            btn.addEventListener('mousemove', function (e) {
                const rect = this.getBoundingClientRect();
                const x = (e.clientX - rect.left) - (rect.width / 2);
                const y = (e.clientY - rect.top) - (rect.height / 2);
                this.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
            });
            btn.addEventListener('mouseleave', function () {
                this.style.transform = 'translate(0px, 0px)';
            });
        });
    </script>
</body>

</html>