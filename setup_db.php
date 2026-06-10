<?php
/**
 * One-time Database Setup Script for Render / Aiven
 */

require_once __DIR__ . '/config/environment.php';

// Handle case-sensitivity in environment variable names
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: getenv('DB_Name') ?: 'defaultdb';
$db_port = getenv('DB_PORT') ?: '3306';

echo "<div style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 50px auto; border: 1px solid #ddd; border-radius: 1rem;'>";
echo "<h3>Aiven Database Schema Setup</h3>";
echo "<p>Connecting to database <strong>$db_name</strong> on host <strong>$db_host</strong>...</p>";

$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Enable SSL (required for Aiven)
if ($db_port !== '3306') {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
}

$success = $conn->real_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port,
    NULL,
    ($db_port !== '3306' ? MYSQLI_CLIENT_SSL : 0)
);

if (!$success) {
    die("<p style='color: red;'><strong>Connection Failed:</strong> " . mysqli_connect_error() . "</p></div>");
}

echo "<p style='color: green;'><strong>Connected successfully!</strong></p>";

$sql_file = __DIR__ . '/setup_database.sql';
if (!file_exists($sql_file)) {
    die("<p style='color: red;'><strong>Error:</strong> setup_database.sql not found.</p></div>");
}

$sql = file_get_contents($sql_file);

// Strip database creation lines so we import directly into Aiven's defaultdb
$sql = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+\w+;/i', '-- Removed CREATE DATABASE', $sql);
$sql = preg_replace('/USE\s+\w+;/i', '-- Removed USE DATABASE', $sql);

echo "<p>Executing SQL schema queries...</p>";

if ($conn->multi_query($sql)) {
    $i = 0;
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
        $i++;
    } while ($conn->next_result());
    
    echo "<p style='color: green; font-weight: bold;'>Success! All tables have been created and initial data was populated in '$db_name'.</p>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 0.5rem 1rem; background: #2563eb; color: white; text-decoration: none; border-radius: 0.25rem;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red;'><strong>Error executing queries:</strong> " . $conn->error . "</p>";
}

$conn->close();
echo "</div>";
?>
