<?php
/**
 * One-time Admin Password Reset Script
 * Resets admin password to: admin123
 * DELETE THIS FILE after use for security!
 */

require_once __DIR__ . '/config/environment.php';

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: getenv('DB_Name') ?: 'defaultdb';
$db_port = getenv('DB_PORT') ?: '3306';

echo "<div style='font-family: sans-serif; padding: 2rem; max-width: 500px; margin: 50px auto; border: 1px solid #ddd; border-radius: 1rem;'>";
echo "<h3>Admin Password Reset</h3>";

$conn = mysqli_init();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
if ($db_port !== '3306') {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
}
$success = $conn->real_connect(
    $db_host, $db_user, $db_pass, $db_name, (int)$db_port,
    NULL, ($db_port !== '3306' ? MYSQLI_CLIENT_SSL : 0)
);

if (!$success) {
    die("<p style='color:red'>Connection failed: " . mysqli_connect_error() . "</p></div>");
}

echo "<p style='color:green'>Connected to database: <strong>$db_name</strong></p>";

// Generate a fresh, verified hash for admin123
$new_hash = password_hash('admin123', PASSWORD_BCRYPT);

// Check if admin user exists
$check = $conn->query("SELECT id, username, password FROM users WHERE username = 'admin'");
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    echo "<p>Found admin user (ID: {$row['id']}). Updating password...</p>";

    $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'active' WHERE username = 'admin'");
    $stmt->bind_param("s", $new_hash);

    if ($stmt->execute()) {
        echo "<p style='color:green; font-weight:bold'>✅ Password updated successfully!</p>";
        echo "<p>You can now log in with:<br><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color:red'>❌ Update failed: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    // Admin doesn't exist — insert them fresh
    echo "<p>Admin user not found. Creating admin account...</p>";
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, status) VALUES ('admin', ?, 'System Administrator', 'super_admin', 'active')");
    $stmt->bind_param("s", $new_hash);
    if ($stmt->execute()) {
        echo "<p style='color:green; font-weight:bold'>✅ Admin account created!</p>";
        echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color:red'>❌ Insert failed: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
echo "<br><a href='login.php' style='display:inline-block; padding:0.5rem 1rem; background:#4f46e5; color:white; text-decoration:none; border-radius:0.5rem;'>Go to Login Page →</a>";
echo "<p style='color:#999; font-size:0.8rem; margin-top:1rem;'>⚠️ Please delete this file after use for security.</p>";
echo "</div>";
?>
