<?php
/**
 * auth.php - Session management and authentication checks
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login for a page
 */
function require_login() {
    if (!is_logged_in()) {
        // Find path to login.php
        $path_prefix = file_exists('login.php') ? '' : (file_exists('../login.php') ? '../' : '../../');
        header("Location: {$path_prefix}login.php");
        exit();
    }
}

/**
 * Get current logged in user details
 */
function get_user() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Check if user is super admin
 */
function is_super_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

/**
 * Require super admin role for a page
 */
function require_super_admin() {
    require_login();
    if (!is_super_admin()) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}
?>
