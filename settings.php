<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

require_login();

// Handle Backup Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'files' && !class_exists('ZipArchive')) {
        die("Error: The ZipArchive PHP extension is not enabled on this server.");
    }

    if ($_GET['action'] === 'database') {
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) $tables[] = $row[0];

        $sql_content = "-- CertGen Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as $table) {
            $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
            $sql_content .= "\n\n" . $row2[1] . ";\n\n";
            $result = $conn->query("SELECT * FROM $table");
            while ($row = $result->fetch_row()) {
                $sql_content .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $result->field_count; $j++) {
                    $val = str_replace("\n", "\\n", $conn->real_escape_string($row[$j] ?? ''));
                    $sql_content .= isset($row[$j]) ? '"' . $val . '"' : 'NULL';
                    if ($j < ($result->field_count - 1)) $sql_content .= ',';
                }
                $sql_content .= ");\n";
            }
        }
        $sql_content .= "\nSET FOREIGN_KEY_CHECKS=1;";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="certgen_db_backup_' . date('Y-m-d') . '.sql"');
        echo $sql_content; exit();
    }

    if ($_GET['action'] === 'files') {
        $rootPath = realpath(__DIR__);
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                if (strpos($relativePath, 'vendor') === 0 || strpos($relativePath, '.git') === 0 || strpos($relativePath, '.gemini') === 0) continue;
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="certgen_files_backup_' . date('Y-m-d') . '.zip"');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile); unlink($tempFile); exit();
    }
}

$view = $_GET['view'] ?? 'menu';
$msg = '';
$error = '';

// Handle Database Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_db']) && $view === 'backup') {
    $file = $_FILES['import_db']['tmp_name'];
    if ($file && is_uploaded_file($file)) {
        $sql = file_get_contents($file);
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        if ($conn->multi_query($sql)) {
            while ($conn->next_result()) { if (!$conn->more_results()) break; }
            $msg = "Database imported successfully! Your system data has been updated.";
        } else {
            $error = "Import failed: " . $conn->error;
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    } else {
        $error = "Please select a valid .sql file to import.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $view === 'account') {
    $full_name = trim($_POST['full_name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($full_name)) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $full_name, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $full_name;
            $msg = "Profile updated successfully!";
        } else {
            $error = "System error updating profile.";
        }
    }

    if (!empty($current_password) || !empty($new_password)) {
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required to change password.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                if ($update_stmt->execute()) {
                    $msg = ($msg ? $msg . " and " : "") . "Password changed successfully!";
                } else {
                    $error = "System error updating password.";
                }
            } else {
                $error = "Incorrect current password.";
            }
        }
    }
}

$stmt = $conn->prepare("SELECT username, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

require_once 'includes/header.php';
?>

<div class="container" style="max-width: 1000px; margin: 40px auto; padding: 0 20px 20px;">
    
    <!-- Premium Header (Consistent with History) -->
    <div class="history-header" style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%); padding: 40px; border-radius: 24px; margin-bottom: 40px; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,45,114,0.15);">
        <div style="position: relative; z-index: 2;">
            <a href="<?= $view === 'menu' ? 'dashboard.php' : 'settings.php' ?>" class="history-back-link">
                &larr; Back to <?= $view === 'menu' ? 'Dashboard' : 'Settings Menu' ?>
            </a>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.4rem; font-weight: 800; margin: 15px 0 5px;"><?= $view === 'backup' ? 'System Maintenance' : ($view === 'account' ? 'Account Settings' : 'System Settings') ?></h1>
            <p style="color: rgba(255,255,255,0.8); margin: 0;"><?= $view === 'backup' ? 'Manage your system data exports and imports' : ($view === 'account' ? 'Manage your login credentials and personal information' : 'Select a configuration category to manage your system') ?></p>
        </div>
        <!-- Decorative Circle -->
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    </div>

    <?php if ($view === 'menu'): ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
            <a href="?view=account" class="settings-card">
                <div class="settings-icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">👤</div>
                <div class="settings-content">
                    <h3>Account Settings</h3>
                    <p>Update your display name, manage login security, and change your password.</p>
                </div>
            </a>

            <a href="?view=backup" class="settings-card">
                <div class="settings-icon" style="background: rgba(0, 184, 148, 0.1); color: #00a381;">🛡️</div>
                <div class="settings-content">
                    <h3>System Backup</h3>
                    <p>Export database records and project files to prevent accidental data loss.</p>
                </div>
            </a>
        </div>

    <?php elseif ($view === 'account'): ?>
        <!-- Account Settings View -->
        <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 24px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
            <?php if ($msg): ?>
                <div style="background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <span style="font-size: 18px; margin-right: 10px;">✅</span> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <span style="font-size: 18px; margin-right: 10px;">⚠️</span> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group floating" style="margin-bottom: 25px;">
                    <input type="text" value="<?php echo htmlspecialchars($current_user['username']); ?>" readonly style="background: #f8fafc; color: #64748b; cursor: not-allowed; border-color: #e2e8f0;">
                    <label>Username (Fixed)</label>
                </div>

                <div class="form-group floating" style="margin-bottom: 25px;">
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required placeholder=" ">
                    <label>Display Name / Full Name</label>
                </div>

                <h3 style="color: #475569; font-size: 16px; margin: 30px 0 15px; border-top: 1px solid #f1f5f9; padding-top: 25px;">Security Check</h3>

                <div class="form-group floating" style="margin-bottom: 20px;">
                    <input type="password" name="current_password" placeholder=" ">
                    <label>Current Password</label>
                </div>

                <div class="form-group floating" style="margin-bottom: 20px;">
                    <input type="password" name="new_password" placeholder=" ">
                    <label>New Password</label>
                </div>

                <div class="form-group floating" style="margin-bottom: 30px;">
                    <input type="password" name="confirm_password" placeholder=" ">
                    <label>Confirm New Password</label>
                </div>

                <button type="submit" class="submit-btn" style="width: 100%; padding: 16px; background: linear-gradient(to right, #4f46e5, #818cf8); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);">Update Credentials</button>
            </form>
        </div>

    <?php elseif ($view === 'backup'): ?>
        <!-- Backup View -->
        <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 24px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 650px; margin: 0 auto;">
            <?php if ($msg): ?>
                <div style="background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <span style="font-size: 18px; margin-right: 10px;">✅</span> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <span style="font-size: 18px; margin-right: 10px;">⚠️</span> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <h3 style="color: #475569; font-size: 16px; margin: 0 0 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Export Data</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 40px;">
                <a href="?action=database" class="backup-option" style="padding: 15px; flex-direction: column; text-align: center;">
                    <div class="backup-icon" style="font-size: 24px;">🗄️</div>
                    <div class="backup-text" style="text-align: center;">
                        <strong style="font-size: 0.9rem;">Export DB (.SQL)</strong>
                    </div>
                </a>
                <a href="?action=files" class="backup-option" style="padding: 15px; flex-direction: column; text-align: center;">
                    <div class="backup-icon" style="font-size: 24px;">📁</div>
                    <div class="backup-text" style="text-align: center;">
                        <strong style="font-size: 0.9rem;">Export Files (.ZIP)</strong>
                    </div>
                </a>
            </div>

            <h3 style="color: #475569; font-size: 16px; margin: 0 0 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Import Data</h3>
            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px;">Upload a previously exported .sql file to restore or transfer your database.</p>
            
            <form method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px dashed #cbd5e1; text-align: center;">
                <input type="file" name="import_db" id="import_db" accept=".sql" required style="margin-bottom: 20px; font-size: 0.9rem; color: #64748b;">
                <button type="submit" class="submit-btn" style="width: 100%; padding: 12px; background: #002D72; border: none; border-radius: 10px; color: white; font-weight: 700; cursor: pointer; transition: all 0.3s;">Start Database Import</button>
            </form>

            <div style="margin-top: 30px; padding: 15px; background: #fffbeb; border-radius: 12px; border: 1px solid #fef3c7; display: flex; gap: 12px;">
                <span style="font-size: 20px;">⚠️</span>
                <p style="margin: 0; color: #b45309; font-size: 0.8rem; line-height: 1.5;">
                    <strong>Caution:</strong> Importing a database will overwrite your current data. Please ensure you have a backup of your current state before proceeding.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Selection Cards */
    .settings-card {
        background: white;
        padding: 30px;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .settings-card:hover {
        transform: translateY(-8px);
        border-color: #4f46e5;
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.1);
    }
    .settings-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    .settings-content h3 {
        margin: 0 0 10px 0;
        color: #1e293b;
        font-size: 1.25rem;
        font-family: 'Outfit', sans-serif;
    }
    .settings-content p {
        margin: 0;
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Backup Options */
    .backup-option {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .backup-option:hover {
        border-color: #4f46e5;
        background: white;
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .backup-icon { font-size: 32px; }
    .backup-text strong { display: block; color: #1e293b; font-size: 1rem; }
    .backup-text span { color: #64748b; font-size: 0.85rem; }

    /* Forms */
    .form-group.floating { position: relative; }
    .form-group.floating input {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        padding: 1.5rem 1rem 0.6rem; border-radius: 12px;
        color: #1e293b; font-family: inherit; font-size: 1rem;
        transition: all 0.3s; box-sizing: border-box;
    }
    .form-group.floating label {
        position: absolute; top: 1rem; left: 1rem;
        color: #94a3b8; pointer-events: none; transition: all 0.3s;
    }
    .form-group.floating input:focus ~ label,
    .form-group.floating input:not(:placeholder-shown) ~ label {
        top: 0.4rem; font-size: 0.75rem; color: #4f46e5; font-weight: 600;
    }
    .form-group.floating input:focus {
        border-color: #4f46e5; background: white; outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3); }

    .history-back-link {
        display: inline-flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 22px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-family: 'Inter', sans-serif;
    }

    .history-back-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.25);
        transform: translateX(-5px);
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

</body>
</html>
