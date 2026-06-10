<?php
// If logged in, we could redirect to dashboard, but let's allow them to see the landing page too.
// Or just auto-redirect if they are already logged in to the admin side.
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCNHS | Digital Certification Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
    <meta name="description" content="Official Certification Portal for Olongapo City National High School. Request certificates online or manage records.">
</head>
<body>
    <div class="watermark-bg"></div>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="container">
        <header class="header">
            <div class="logo-container">
                <img src="assets/img/OCNHS LOGO.png" alt="OCNHS Logo" class="school-logo">
            </div>
            <h1 class="title">OCNHS Digital Portal</h1>
            <p class="subtitle">Welcome to the Olongapo City National High School Online Services. Select an option below to proceed.</p>
        </header>

        <main class="portal-grid">
            <!-- Student/Public Request -->
            <a href="apply.php" class="portal-card">
                <div class="icon-wrapper">🎓</div>
                <h2 class="card-title">Online Request</h2>
                <p class="card-desc">For students and alumni. Request for Form 137, Certifications, and other school documents online.</p>
                <div class="action-btn">Request Now</div>
            </a>

            <!-- Admin Login -->
            <a href="login.php" class="portal-card">
                <div class="icon-wrapper">🔐</div>
                <h2 class="card-title">Admin Login</h2>
                <p class="card-desc">For authorized personnel only. Manage certificate requests, history, and system settings.</p>
                <div class="action-btn">Sign In</div>
            </a>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Olongapo City National High School. All Rights Reserved.</p>
    </footer>

    <script>
        // Subtle hover effect persistence for mobile
        document.querySelectorAll('.portal-card').forEach(card => {
            card.addEventListener('touchstart', function() {}, {passive: true});
        });
    </script>
</body>
</html>
