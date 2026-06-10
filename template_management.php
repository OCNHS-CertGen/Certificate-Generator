<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/templates.php';
require_super_admin();

$page_title = "Template Management - CertGen";
$message = '';
$error = '';

$types = get_certificate_types(true); // Fetch all types including disabled ones

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cert_type = $_POST['certificate_type'] ?? '';
    $title = $_POST['template_title'] ?? '';
    $body = $_POST['template_body'] ?? '';
    $action = $_POST['action'] ?? '';
    
    // For actions hitting a specific type (delete, enable, disable)
    $target_type = $_POST['target_type'] ?? $cert_type;

    if ($action === 'save') {
        if (!empty($cert_type) && !empty($title) && !empty($body)) {
            // Update or Insert template, ensuring we maintain 'status' if it already exists, or strictly insert 'active' if new
            $stmt = $conn->prepare("INSERT INTO templates (certificate_type, title, body, status) VALUES (?, ?, ?, 'active') ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body)");
            $stmt->bind_param("sss", $cert_type, $title, $body);
            if ($stmt->execute()) {
                $message = "Template for '$cert_type' saved successfully.";
                if (!in_array($cert_type, $types)) {
                    $types[] = $cert_type; // Immediately reflect added type in the current render array
                }
            } else {
                $error = "Failed to save template: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "All fields are required to save a template.";
        }
    } elseif ($action === 'restore') {
        if (!empty($target_type)) {
            $stmt = $conn->prepare("DELETE FROM templates WHERE certificate_type = ?");
            $stmt->bind_param("s", $target_type);
            if ($stmt->execute()) {
                $message = "Template for '$target_type' restored to default.";
            } else {
                $error = "Failed to restore template.";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
         if (!empty($target_type)) {
            $stmt = $conn->prepare("DELETE FROM templates WHERE certificate_type = ?");
            $stmt->bind_param("s", $target_type);
            if ($stmt->execute()) {
                $message = "Template '$target_type' deleted permanently.";
                $types = array_diff($types, [$target_type]); 
                // Don't auto-redirect, handle it via UI loop
            } else {
                $error = "Failed to delete template.";
            }
            $stmt->close();
        }
    } elseif ($action === 'disable') {
         if (!empty($target_type)) {
            $check = $conn->prepare("SELECT id FROM templates WHERE certificate_type = ?");
            $check->bind_param("s", $target_type);
            $check->execute();
            if ($check->get_result()->num_rows == 0) {
                 // It's a system default without DB entry, we need to inject it as 'disabled'
                 $dummy_data = [
                    'student_name' => '[sn]', 'lrn' => '[lrn]', 'grade_level' => '[grade]',
                    'section_track' => '[section]', 'curriculum' => '[curriculum]', 'school_year' => '[sy]',
                    'purpose' => '[purpose]', 'school_name' => '[school_name]', 'school_address' => '[school_address]',
                    'division' => '[division]', 'school_level' => 'SENIOR HIGH SCHOOL'
                ];
                $fallback = get_certificate_content($target_type, $dummy_data);
                
                $body_text = $fallback['body'];
                $body_text = str_replace("<strong>[sn]</strong> (<strong>LRN [lrn]</strong>)", "[student_info]", $body_text);
                $body_text = str_replace("<strong>[sn]</strong>", "[sn]", $body_text);
                $body_text = str_replace("SENIOR HIGH SCHOOL", "[level_wording]", $body_text);

                $stmt = $conn->prepare("INSERT INTO templates (certificate_type, title, body, status) VALUES (?, ?, ?, 'disabled')");
                $stmt->bind_param("sss", $target_type, $fallback['title'], $body_text);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("UPDATE templates SET status = 'disabled' WHERE certificate_type = ?");
                $stmt->bind_param("s", $target_type);
                $stmt->execute();
                $stmt->close();
            }
            $message = "Template '$target_type' disabled.";
        }
    } elseif ($action === 'enable') {
        if (!empty($target_type)) {
            $stmt = $conn->prepare("UPDATE templates SET status = 'active' WHERE certificate_type = ?");
            $stmt->bind_param("s", $target_type);
            $stmt->execute();
            $stmt->close();
            $message = "Template '$target_type' enabled.";
        }
    }
}

// Retrieve DB Statuses
$template_statuses = [];
$res_stat = $conn->query("SELECT certificate_type, status FROM templates");
if ($res_stat) {
    while($r = $res_stat->fetch_assoc()) {
        $template_statuses[$r['certificate_type']] = $r['status'];
    }
}

$is_new_mode = isset($_GET['new']) && $_GET['new'] == 1;

// Cleanup types after potential deletes
$types = array_values($types); 
$selected_type = $is_new_mode ? '' : ($_GET['type'] ?? ($types[0] ?? ''));

$current_title = '';
$current_body = '';
$is_custom = false;
$current_status = $template_statuses[$selected_type] ?? 'active';

if (!$is_new_mode && $selected_type !== '') {
    $stmt = $conn->prepare("SELECT title, body FROM templates WHERE certificate_type = ?");
    $stmt->bind_param("s", $selected_type);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $current_title = $row['title'];
        $current_body = $row['body'];
        $is_custom = true;
    } else {
        // Generate default template string by passing dummy data
        $dummy_data = [
            'student_name' => '[sn]',
            'lrn' => '[lrn]', 
            'grade_level' => '[grade]',
            'section_track' => '[section]',
            'curriculum' => '[curriculum]',
            'school_year' => '[sy]',
            'purpose' => '[purpose]',
            'school_name' => '[school_name]',
            'school_address' => '[school_address]',
            'division' => '[division]',
            'school_level' => 'SENIOR HIGH SCHOOL' 
        ];
        $fallback = get_certificate_content($selected_type, $dummy_data);
        $current_title = $fallback['title'];

        // Reverse engineer tags
        $body_text = $fallback['body'];
        $body_text = str_replace("<strong>[sn]</strong> (<strong>LRN [lrn]</strong>)", "[student_info]", $body_text);
        $body_text = str_replace("<strong>[sn]</strong>", "[sn]", $body_text);
        $body_text = str_replace("SENIOR HIGH SCHOOL", "[level_wording]", $body_text);

        $current_body = $body_text;
    }
    $stmt->close();
}

require_once 'includes/header.php';
?>

<div class="welcome-container fade-in">
    <div class="history-header">
        <div class="history-header-inner">
            <a href="dashboard.php" class="back-link" style="background: #e2e8f0; color: #475569; padding: 8px 20px; border-radius: 40px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; font-size: 0.95rem; margin-top: 20px; margin-left: 10px; margin-bottom: 20px; transition: all 0.3s; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" onmouseover="this.style.background='#cbd5e1'; this.style.transform='translateX(-3px)'; this.style.color='var(--primary-color)';" onmouseout="this.style.background='#e2e8f0'; this.style.transform='none'; this.style.color='#475569';">&larr; Back to Selection</a>
            <h1 class="history-title" style="color: white;">Template Management</h1>
            <p class="history-subtitle" style="color: white;">Customize the content format for different certificate types.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="settings-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 30px;">
        
        <!-- Sidebar Navigation for Templates -->
        <div class="settings-sidebar" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; color: var(--primary-color);">Certificates</h3>
                <a href="?new=1" style="background: linear-gradient(to right, #4f46e5, #818cf8); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; text-decoration: none; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3); transition: all 0.3s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">➕ Add</a>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 500px; overflow-y: auto; padding-right: 5px;">
                <?php foreach ($types as $type): 
                    $stat = $template_statuses[$type] ?? 'active';
                    $is_selected = (!$is_new_mode && $type === $selected_type);
                ?>
                    <a href="?type=<?php echo urlencode($type); ?>" 
                       style="padding: 10px 15px; border-radius: 8px; text-decoration: none; color: <?php echo $is_selected ? 'white' : ($stat === 'disabled' ? '#94a3b8' : '#475569'); ?>; background: <?php echo $is_selected ? 'var(--primary-color)' : '#f8f9fa'; ?>; font-size: 0.85rem; font-weight: 500; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="<?php echo htmlspecialchars($type); ?>">
                            <?php echo htmlspecialchars($type); ?>
                        </span>
                        <?php if ($stat === 'disabled'): ?>
                            <span style="font-size: 0.7rem; background: <?php echo $is_selected ? 'rgba(255,255,255,0.2)' : '#e2e8f0'; ?>; padding: 2px 6px; border-radius: 4px;">Disabled</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <?php if(empty($types)): ?>
                    <p style="color: #64748b; font-size: 0.85rem; font-style: italic;">No templates found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Editor Form -->
        <div class="settings-card" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <?php if ($is_new_mode): ?>
                
                <h2 style="color: var(--primary-color); margin: 0 0 20px 0;">Create New Certificate Template</h2>
                <form method="POST" action="?type=">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Certificate Identifier (Unique Name) *</label>
                        <input type="text" name="certificate_type" placeholder="e.g. CERTIFICATE OF ATTENDANCE" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Certificate Header Title (Printed Format) *</label>
                        <input type="text" name="template_title" placeholder="Enter header styling like CERTIFICATE OF COMPLETION" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Certificate Body (HTML Supported) *</label>
                        <textarea name="template_body" rows="10" placeholder="Type the HTML body here using placeholders..." required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; resize: vertical;"></textarea>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                        <strong style="color: #334155;">Available Placeholders:</strong><br>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; color: #475569;">
                            <div><code>[student_info]</code> - Name + LRN</div>
                            <div><code>[sn]</code> - Student Name (Bold)</div>
                            <div><code>[lrn]</code> - Just LRN String</div>
                            <div><code>[grade]</code> - Grade Level</div>
                            <div><code>[section]</code> - Section/Track</div>
                            <div><code>[curriculum]</code> - Curriculum</div>
                            <div><code>[sy]</code> - School Year</div>
                            <div><code>[purpose]</code> - Request Purpose</div>
                            <div><code>[school_name]</code> - School Name</div>
                            <div><code>[school_address]</code> - Address</div>
                            <div><code>[division]</code> - Division</div>
                            <div><code>[level_wording]</code> - JHS / SHS Output</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="action" value="save" style="background: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit;">Create Template</button>
                    </div>
                </form>

            <?php elseif ($selected_type !== ''): ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="color: var(--primary-color); margin: 0;">Edit Template: <?php echo htmlspecialchars($selected_type); ?></h2>
                    <div style="display: flex; gap: 10px;">
                        <?php if ($current_status === 'disabled'): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($selected_type); ?>">
                                <button type="submit" name="action" value="enable" style="background: #22c55e; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">✅ Enable</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($selected_type); ?>">
                                <button type="submit" name="action" value="disable" style="background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;" onclick="return confirm('Are you sure you want to disable this template? Users will not be able to request it.');">⏸ Disable</button>
                            </form>
                        <?php endif; ?>

                        <?php 
                            $is_sys = is_default_template($selected_type);
                            if ($is_sys && $is_custom): 
                        ?>
                            <span style="background: #e0e7ff; color: #3730a3; padding: 6px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">System (Overridden)</span>
                        <?php elseif ($is_sys): ?>
                            <span style="background: #f1f5f9; color: #64748b; padding: 6px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">System Default</span>
                        <?php else: ?>
                            <span style="background: #dcfce7; color: #166534; padding: 6px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">Custom Template</span>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="certificate_type" value="<?php echo htmlspecialchars($selected_type); ?>">
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Certificate Title (HTML Supported)</label>
                        <input type="text" name="template_title" value="<?php echo htmlspecialchars($current_title); ?>" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Certificate Body (HTML Supported)</label>
                        <textarea name="template_body" rows="10" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; resize: vertical;"><?php echo htmlspecialchars($current_body); ?></textarea>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                        <strong style="color: #334155;">Available Placeholders:</strong><br>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; color: #475569;">
                            <div><code>[student_info]</code> - Name + LRN</div>
                            <div><code>[sn]</code> - Student Name (Bold)</div>
                            <div><code>[lrn]</code> - Just LRN String</div>
                            <div><code>[grade]</code> - Grade Level</div>
                            <div><code>[section]</code> - Section/Track</div>
                            <div><code>[curriculum]</code> - Curriculum</div>
                            <div><code>[sy]</code> - School Year</div>
                            <div><code>[purpose]</code> - Request Purpose</div>
                            <div><code>[school_name]</code> - School Name</div>
                            <div><code>[school_address]</code> - Address</div>
                            <div><code>[division]</code> - Division</div>
                            <div><code>[level_wording]</code> - JHS / SHS Output</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="action" value="save" style="background: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit;">Save Changes</button>
                        
                        <?php if ($is_sys && $is_custom): ?>
                            <button type="submit" name="action" value="restore" onclick="return confirm('Are you sure you want to delete this custom template and restore the system default?');" style="background: white; color: #dc2626; border: 1px solid #dc2626; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit;">Restore Default</button>
                        <?php elseif (!$is_sys): ?>
                            <!-- Non-system custom templates can be permanently deleted -->
                            <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to permanently delete this custom template?');" style="background: white; color: #dc2626; border: 1px solid #dc2626; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit;">Delete Template</button>
                        <?php endif; ?>
                    </div>
                </form>

            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: #64748b;">
                    <h3>Please select or add a template</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
