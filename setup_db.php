<?php
/**
 * One-time Database Setup Script for Render / Aiven (Self-Contained)
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

// Embedded SQL script (excluding CREATE DATABASE / USE statements)
$sql = "
CREATE TABLE IF NOT EXISTS certificate_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_type VARCHAR(100) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    lrn VARCHAR(20) DEFAULT '',
    grade_level VARCHAR(50) DEFAULT '',
    section_track VARCHAR(100) DEFAULT '',
    curriculum VARCHAR(255) DEFAULT '',
    school_year VARCHAR(20) DEFAULT '',
    purpose VARCHAR(255) DEFAULT '',
    date_issued VARCHAR(50) NOT NULL,
    principal_name VARCHAR(100) DEFAULT '',
    generated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS certificate_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_number VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50) DEFAULT '',
    lrn VARCHAR(20) DEFAULT '',
    certificate_type VARCHAR(100) NOT NULL,
    grade_level VARCHAR(50) DEFAULT '',
    school_year VARCHAR(20) DEFAULT '',
    section_track VARCHAR(100) DEFAULT '',
    curriculum VARCHAR(255) DEFAULT '',
    purpose VARCHAR(255) DEFAULT '',
    id_image VARCHAR(255) DEFAULT '',
    selfie_image VARCHAR(255) DEFAULT '',
    place_of_birth VARCHAR(255) DEFAULT '',
    birth_date VARCHAR(100) DEFAULT '',
    address TEXT,
    status VARCHAR(20) DEFAULT 'Pending',
    remarks TEXT,
    processed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (ref_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_type VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT '',
    role VARCHAR(50) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial default user: admin / admin123
INSERT INTO users (username, password, full_name, role, status) 
SELECT 'admin', '$2y$10$gfbYJ6zs8hrywM6y02TuMeC9haoX/xeYp40DW.lfOQTou8Qs33K3a', 'System Administrator', 'super_admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
";

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
