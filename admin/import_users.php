<?php
/**
 * GaunGaliHat Users CSV Importer
 * Secure CSV Import Engine with Role Whitelisting, CSRF Protection,
 * 1MB Size Bound, 500-Row Boundary, Formula Neutralization, and Unique Random Credentials.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सत्यापन विफल रहा। (Invalid CSRF token)');
        header('Location: /admin/users.php');
        exit;
    }

    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'फ़ाइल अपलोड त्रुटि।');
        header('Location: /admin/users.php');
        exit;
    }

    // Item 11: File size check (max 1 MB)
    if ($file['size'] > 1024 * 1024) {
        set_flash('error', 'CSV फ़ाइल का आकार 1MB से अधिक नहीं होना चाहिए।');
        header('Location: /admin/users.php');
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        set_flash('error', 'कृपया केवल CSV (.csv) फ़ाइल चुनें।');
        header('Location: /admin/users.php');
        exit;
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        set_flash('error', 'फ़ाइल खोली नहीं जा सकी।');
        header('Location: /admin/users.php');
        exit;
    }

    // Skip BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    $header = fgetcsv($handle);
    $imported = 0;
    $skipped = 0;
    $row_count = 0;
    $max_rows = 500; // Item 11: Max 500 rows limit

    $is_super = is_super_admin();
    $allowed_roles = $is_super ? ['customer', 'vendor', 'admin', 'super_admin'] : ['customer', 'vendor'];

    $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt_ins = $pdo->prepare("
        INSERT INTO users (
            unique_code, name, username, email, phone, password, role, department, city, pincode, status, force_password_change, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, CURRENT_TIMESTAMP)
    ");

    while (($row = fgetcsv($handle, 4096, ',')) !== false) {
        if (empty($row) || count($row) < 4) continue;
        $row_count++;
        if ($row_count > $max_rows) {
            break;
        }

        $name = neutralize_csv_formula(trim($row[2] ?? ''));
        $username = strtolower(trim($row[3] ?? ''));
        $email = strtolower(trim($row[4] ?? ''));
        $phone = trim($row[5] ?? '');
        $raw_role = strtolower(trim($row[6] ?? 'customer'));
        $department = neutralize_csv_formula(trim($row[7] ?? 'General'));
        $city = neutralize_csv_formula(trim($row[8] ?? ''));
        $pincode = neutralize_csv_formula(trim($row[9] ?? ''));

        // Role whitelisting (Item 11)
        $role = in_array($raw_role, $allowed_roles, true) ? $raw_role : 'customer';

        if (empty($name) || empty($username) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $skipped++;
            continue;
        }

        // Validate username formatting
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
            $skipped++;
            continue;
        }

        // Skip if username or email already exists
        $stmt_chk->execute([$username, $email]);
        if ($stmt_chk->fetch()) {
            $skipped++;
            continue;
        }

        $code = 'GGH-USR-' . bin2hex(random_bytes(3));
        // Generate secure random password per user
        $random_temp_pass = bin2hex(random_bytes(8)) . '@' . random_int(10, 99);
        $hashed = password_hash($random_temp_pass, PASSWORD_BCRYPT);

        try {
            $stmt_ins->execute([$code, $name, $username, $email, $phone, $hashed, $role, $department, $city, $pincode]);
            $imported++;
        } catch (Exception $e) {
            $skipped++;
        }
    }

    fclose($handle);
    log_audit_action('admin_import_users', 'users', 'import', null, "Imported {$imported} users via CSV (skipped {$skipped}, processed {$row_count} rows)");
    set_flash('success', "सफलतापूर्वक {$imported} उपयोगकर्ता CSV से जोड़े गए! सुरक्षा कारणों से प्रत्येक उपयोगकर्ता के लिए यादृच्छिक (Random) पासवर्ड उत्पन्न किया गया है और प्रथम लॉगिन पर पासवर्ड बदलना अनिवार्य (Force Password Change) किया गया है।");
    header('Location: /admin/users.php');
    exit;
}

header('Location: /admin/users.php');
exit;
