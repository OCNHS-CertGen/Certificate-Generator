<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'OCNHS CertGen'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php
    // Determine path prefix based on whether assets folder is in the current directory
    $path_prefix = file_exists('assets/css/style.css') ? '' : '../';
    ?>
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/modals.css">
    <script src="<?php echo $path_prefix; ?>assets/js/modals.js"></script>

</head>
<body>
    <div class="watermark-bg"></div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="<?php echo $path_prefix; ?>dashboard.php">📑 CertGen</a>
            </div>
            <div class="nav-links">
                <div class="user-greeting">
                    <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                </div>
                <a href="<?php echo $path_prefix; ?>settings.php" class="nav-item">Settings</a>
                <?php if (is_super_admin()): ?>
                    <div class="nav-dropdown">
                        <button class="nav-item dropbtn" style="color: #cbd5e1; outline: none; border: none; cursor: pointer; transition: color 0.3s; font-size: 0.95rem; font-weight: 500; background: none; padding: 0;">Admin Options ▾</button>
                        <div class="dropdown-content">
                            <a href="<?php echo $path_prefix; ?>admin_management.php">Admin Management</a>
                            <a href="<?php echo $path_prefix; ?>template_management.php">Templates</a>
                        </div>
                    </div>
                <?php endif; ?>
                <a href="<?php echo $path_prefix; ?>logout.php" class="nav-item logout">Logout</a>
            </div>
        </div>
    </nav>

    <style>
        .top-nav {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-logo a {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            background: linear-gradient(to right, #4f46e5, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .user-greeting {
            color: #94a3b8;
            font-size: 0.9rem;
            padding-right: 1.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-item {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }
        .nav-item:hover {
            color: white;
        }
        
        /* Dropdown Styles */
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: rgba(15, 23, 42, 0.95);
            min-width: 180px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1001;
            border-radius: 8px;
            overflow: hidden;
            top: 100%;
            margin-top: 15px;
            left: 50%;
            transform: translateX(-50%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 12px;
            height: 12px;
            background: rgba(15, 23, 42, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-content a {
            color: #cbd5e1;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            transition: background-color 0.2s, color 0.2s;
            position: relative;
            z-index: 1;
        }
        .dropdown-content a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        /* Invisible hover bridge */
        .nav-dropdown::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 15px;
        }
        .nav-dropdown:hover .dropdown-content {
            display: block;
            animation: fadeIn 0.2s ease-out;
        }
        .nav-dropdown:hover .dropbtn {
            color: white !important;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -10px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        .nav-item.logout {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        .nav-item.logout:hover {
            background: #ef4444;
            color: white;
        }
        
        /* Adjust body padding for fixed nav */
        body {
            padding-top: 0; /* Sticky nav handles this */
        }
    </style>
    <?php endif; ?>
