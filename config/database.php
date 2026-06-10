<?php
/**
 * Database connection for CertGen
 */

require_once __DIR__ . '/environment.php';

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'certgen';
$db_port = getenv('DB_PORT') ?: '3306';

$conn = mysqli_init();

// Set connection timeout to 5 seconds to prevent hanging
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// If we are on a custom port (like Aiven's 11655), enable SSL
if (getenv('DB_PORT') && getenv('DB_PORT') !== '3306') {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
}

try {
    // Connect
    $success = $conn->real_connect(
        $db_host,
        $db_user,
        $db_pass,
        $db_name,
        (int)$db_port,
        NULL,
        (getenv('DB_PORT') && getenv('DB_PORT') !== '3306' ? MYSQLI_CLIENT_SSL : 0)
    );

    if (!$success) {
        throw new Exception(mysqli_connect_error());
    }
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    // Provide a more helpful error message instead of a fatal crash
    die("
    <div style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 50px auto; background: #fff1f2; border: 1px solid #fda4af; border-radius: 1rem; color: #9f1239;'>
        <h3 style='margin-top: 0;'>Database Connection Error</h3>
        <p>The system could not connect to the database. This usually happens if the database hasn't been created yet.</p>
        <p><strong>Error Details:</strong> " . $e->getMessage() . "</p>
        <hr style='border: 0; border-top: 1px solid #fecdd3; margin: 1.5rem 0;'>
        <p style='font-size: 0.9rem;'><strong>How to fix:</strong></p>
        <ol style='font-size: 0.9rem; line-height: 1.6;'>
            <li>Open <strong>phpMyAdmin</strong> in your browser.</li>
            <li>Create a new database named <code>$db_name</code>.</li>
            <li>Import the file <code>setup_database.sql</code> located in your project folder.</li>
            <li>Ensure your MySQL service is running in XAMPP.</li>
        </ol>
    </div>");
}

$conn->set_charset("utf8mb4");
?>