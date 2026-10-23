<?php
/**
 * GaunGaliHat Users CSV Importer
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'फ़ाइल अपलोड त्रुटि।');
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

    $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt_ins = $pdo->prepare("
        INSERT INTO users (
            unique_code, name, username, email, phone, password, role, department, city, pincode, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row) || count($row) < 4) continue;

        $name = trim($row[2] ?? '');
        $username = strtolower(trim($row[3] ?? ''));
        $email = strtolower(trim($row[4] ?? ''));
        $phone = trim($row[5] ?? '');
        $role = trim($row[6] ?? 'customer');
        $department = trim($row[7] ?? 'General');
        $city = trim($row[8] ?? '');
        $pincode = trim($row[9] ?? '');

        if (empty($name) || empty($username) || empty($email)) continue;

        // Skip if exists
        $stmt_chk->execute([$username, $email]);
        if ($stmt_chk->fetch()) continue;

        $code = 'GGH-USR-' . rand(100, 999);
        $hashed = password_hash('Ggh@12345', PASSWORD_BCRYPT);

        try {
            $stmt_ins->execute([$code, $name, $username, $email, $phone, $hashed, $role, $department, $city, $pincode]);
            $imported++;
        } catch (Exception $e) {}
    }

    fclose($handle);
    set_flash('success', "सफलतापूर्वक $imported उपयोगकर्ता CSV से जोड़े गए! डिफ़ॉल्ट पासवर्ड: Ggh@12345");
    header('Location: /admin/users.php');
    exit;
}

header('Location: /admin/users.php');
exit;
