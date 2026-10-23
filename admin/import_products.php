<?php
/**
 * GaunGaliHat Product CSV Importer
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'फ़ाइल अपलोड करने में त्रुटि उत्पन्न हुई।');
        header('Location: /admin/products.php');
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        set_flash('error', 'कृपया केवल वैध CSV (.csv) फ़ाइल अपलोड करें।');
        header('Location: /admin/products.php');
        exit;
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        set_flash('error', 'फ़ाइल खोली नहीं जा सकी।');
        header('Location: /admin/products.php');
        exit;
    }

    // Skip BOM if present
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Read header row
    $header = fgetcsv($handle);
    $imported_count = 0;

    $stmt_ins = $pdo->prepare("
        INSERT INTO products (
            code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
            discount, weight_kg, stock, stock_status, unit, image, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', ?, ?, ?)
    ");

    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row) || count($row) < 3) continue;

        // Extract columns safely
        $code = trim($row[1] ?? '');
        $name = trim($row[2] ?? '');
        $hindi_name = trim($row[3] ?? '');
        $cat_id = intval($row[4] ?? 1);
        $vendor_id = intval($row[6] ?? 1);
        $price = floatval($row[8] ?? 0);
        $original_price = floatval($row[9] ?? $price);
        $weight_kg = floatval($row[11] ?? 0.5);
        $stock = intval($row[12] ?? 50);
        $unit = trim($row[14] ?? 'piece');
        $image = trim($row[15] ?? '');
        $description = trim($row[16] ?? '');

        if (empty($name) || $price <= 0) continue;

        if (empty($code)) {
            $code = 'GGH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3)) . '-' . rand(100, 999);
        }
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . rand(100, 999);
        $discount = ($original_price > $price) ? round((($original_price - $price) / $original_price) * 100) : 0;

        try {
            $stmt_ins->execute([
                $code, $vendor_id, $cat_id, $name, $hindi_name, $slug, $price, $original_price,
                $discount, $weight_kg, $stock, $unit, $image, $description
            ]);
            $imported_count++;
        } catch (Exception $e) {
            // skip row if error
        }
    }

    fclose($handle);
    set_flash('success', "सफलतापूर्वक $imported_count उत्पाद CSV फ़ाइल से कैटलॉग में जोड़े गए!");
    header('Location: /admin/products.php');
    exit;
}

header('Location: /admin/products.php');
exit;
