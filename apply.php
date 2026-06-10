<?php
require_once 'config/database.php';

// Focused certificate types for online request
$active_templates = [
    'FORM 137 / SF10',
    'CERTIFICATE OF GRADUATION',
    'CERTIFICATION (ENGLISH AS MEDIUM OF INSTRUCTION)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCNHS | Online Certificate Request</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/apply.css">
    <link rel="stylesheet" href="assets/css/modals.css">
    <script src="assets/js/modals.js"></script>
</head>
<body>
    <div class="watermark-bg"></div>

    <div class="container">
        <div class="progress-container">
            <div class="progress-bar" id="formProgress"></div>
        </div>

        <div class="form-header">
            <img src="assets/img/OCNHS LOGO.png" alt="Logo" class="school-logo">
            <h1 class="form-title">Online Request System</h1>
            <p class="form-subtitle">Olongapo City National High School</p>

            <div style="margin-top: 20px;">
                <button type="button" class="prev-btn" onclick="toggleTracker()" style="padding: 10px 20px; font-size: 0.9rem;">Track My Request</button>
            </div>
        </div>

        <!-- TRACKER SECTION -->
        <div id="statusTracker" style="display: none; background: #f8f9fa; padding: 25px; border-radius: var(--radius-md); margin-bottom: 30px; border: 1px solid #e1e8ef; animation: slideIn 0.3s ease-out;">
            <h4 style="margin-top: 0;">Track Your Request Status</h4>
            <div class="form-group floating" style="margin-bottom: 15px;">
                <input type="text" id="track_ref" placeholder=" " style="background: white;">
                <label>Enter Reference Number (e.g. OCNHS-2026-XXXX)</label>
            </div>
            <button type="button" class="next-btn" onclick="checkStatus()" style="width: 100%; margin: 0;">Check Status</button>
            <div id="statusResult" style="margin-top: 20px; font-weight: 700; text-align: center; display: none; padding: 15px; border-radius: 8px;"></div>
        </div>

        <div class="step-indicator">
            <div class="step active" data-step="1">1</div>
            <div class="step" data-step="2">2</div>
            <div class="step" data-step="3">3</div>
        </div>

        <form id="requestForm" action="process_request.php" method="POST" enctype="multipart/form-data">
            <!-- SECTION 1: PERSONAL INFORMATION -->
            <div class="form-section active" id="step1">
                <h3>Personal Information</h3>
                <div class="info-card">
                    Siguraduhin na ang pangalan ay pareho sa iyong <strong>PSA Birth Certificate</strong> o <strong>Form 137</strong>.
                </div>

                <div class="form-group floating">
                    <input type="text" name="student_name" id="student_name" placeholder=" " required>
                    <label>Full Student Name (First Name, Middle Name, Last Name)</label>
                </div>

                <div class="grid-row">
                    <div class="form-group floating">
                        <input type="email" name="email" id="email" placeholder=" " required>
                        <label>Email Address</label>
                    </div>
                    <div class="form-group floating">
                        <input type="text" name="contact_number" id="contact_number" placeholder=" " required>
                        <label>Contact Number (Active)</label>
                    </div>
                </div>

                <div class="form-group floating">
                    <input type="text" name="address" id="address" placeholder=" " required>
                    <label>Address</label>
                </div>

                <div class="form-group floating">
                    <input type="date" name="DateofBirth" id="date_of_birth" placeholder=" " required>
                    <label>Date of Birth</label>
                </div>

                <div class="form-group floating">
                    <input type="text" name="Placeofbirth" id="place_of_birth" placeholder=" " required>
                    <label>Place of Birth</label>
                </div>

                <div class="btn-group">
                    <button type="button" class="next-btn" onclick="nextStep(2)">Next Step &rarr;</button>
                </div>
            </div>

            <!-- SECTION 2: ACADEMIC DETAILS -->
            <div class="form-section" id="step2">
                <h3>Academic Details</h3>

                <div class="form-group floating">
                    <select name="certificate_type" id="certificate_type" required>
                        <option value="" disabled selected hidden></option>
                        <?php foreach ($active_templates as $type): ?>
                            <option value="<?= $type ?>"><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Document to Request</label>
                </div>

                <div class="grid-row">
                    <div class="form-group floating">
                        <input type="text" name="grade_level" id="grade_level" placeholder=" ">
                        <label>Grade Level (e.g. Grade 10)</label>
                    </div>
                    <div class="form-group floating">
                        <input type="text" name="school_year" id="school_year" placeholder=" " required>
                        <label>School Year Graduated (e.g. 2023-2024)</label>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="prev-btn" onclick="prevStep(1)">&larr; Previous</button>
                    <button type="button" class="next-btn" onclick="nextStep(3)">Next Step &rarr;</button>
                </div>
            </div>

            <!-- SECTION 3: REQUIREMENTS & PURPOSE -->
            <div class="form-section" id="step3">
                <h3>Requirements & Purpose</h3>

                <div class="grid-row">
                    <div class="form-group" style="background: white; border: 2px dashed #e1e8ef; padding: 15px; border-radius: var(--radius-md);">
                        <label style="color: var(--primary-color);">1. Valid ID (Front)</label>
                        <input type="file" name="id_image" accept="image/*" required style="margin-top: 10px;">
                    </div>
                    <div class="form-group" style="background: white; border: 2px dashed #e1e8ef; padding: 15px; border-radius: var(--radius-md);">
                        <label style="color: var(--primary-color);">2. Selfie Photo</label>
                        <input type="file" name="selfie_image" accept="image/*" capture="user" required style="margin-top: 10px;">
                    </div>
                </div>

                <div class="form-group floating" style="margin-top: 20px;">
                    <textarea name="purpose" id="purpose" placeholder=" " required style="min-height: 100px;"></textarea>
                    <label>Purpose of Request</label>
                </div>

                <div class="info-card" style="background: rgba(255, 215, 0, 0.1); border-left-color: var(--accent-color);">
                    Mangyaring suriin nang maigi ang inyong input. Ang maling impormasyon ay maaaring maging sanhi ng delay sa inyong request.
                </div>

                <div class="btn-group">
                    <button type="button" class="prev-btn" onclick="prevStep(2)">&larr; Previous</button>
                    <button type="submit" class="submit-btn" style="width: auto; padding: 15px 60px; margin: 0; background: var(--primary-color); color: white; border: none; border-radius: var(--radius-md); font-weight: 700; cursor: pointer;">Submit Request</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Page Scripts -->
    <script src="assets/js/apply.js"></script>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>