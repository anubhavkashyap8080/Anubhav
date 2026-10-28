<?php
/**
 * GaunGaliHat Product CSV Importer
 * Hardened CSV Parser with 1MB Size Limit, 500-Row Boundary,
 * Numeric Validation, Category/Vendor Whitelisting, Formula Neutralization & CSRF.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF सत्यापन विफल रहा। (Invalid CSRF token)');
        header('Location: /admin/products.php');
        exit;
    }

    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'फ़ाइल अपलोड करने में त्रुटि उत्पन्न हुई।');
        header('Location: /admin/products.php');
        exit;
    }

    // Item 11: File size check (max 1 MB)
    if ($file['size'] > 1024 * 1024) {
        set_flash('error', 'CSV फ़ाइल का आकार 1MB से अधिक नहीं होना चाहिए।');
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
    $row_count = 0;
    $max_rows = 500; // Item 11: Max 500 rows limit

    // Whitelist Categories & Vendors from DB
    $valid_cats = $pdo->query("SELECT id FROM categories")->fetchAll(PDO::FETCH_COLUMN) ?: [1];
    $valid_vendors = $pdo->query("SELECT id FROM vendors")->fetchAll(PDO::FETCH_COLUMN) ?: [1];

    $stmt_ins = $pdo->prepare("
        INSERT INTO products (
            code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
            discount, weight_kg, stock, stock_status, unit, image, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', ?, ?, ?)
    ");

    while (($row = fgetcsv($handle, 4096, ',')) !== false) {
        if (empty($row) || count($row) < 3) continue;
        $row_count++;
        if ($row_count > $max_rows) {
            break;
        }

        // Extract and validate numeric fields
        $raw_price = trim($row[8] ?? '');
        if (!is_numeric($raw_price) || floatval($raw_price) <= 0) continue;
        $price = floatval($raw_price);

        $raw_orig = trim($row[9] ?? '');
        $original_price = (is_numeric($raw_orig) && floatval($raw_orig) >= $price) ? floatval($raw_orig) : $price;

        $raw_weight = trim($row[11] ?? '');
        $weight_kg = (is_numeric($raw_weight) && floatval($raw_weight) > 0) ? floatval($raw_weight) : 0.5;

        $raw_stock = trim($row[12] ?? '');
        $stock = (is_numeric($raw_stock) && intval($raw_stock) >= 0) ? intval($raw_stock) : 50;

        // Neutralize formula injection on text fields
        $name = neutralize_csv_formula(clean_input($row[2] ?? ''));
        $hindi_name = neutralize_csv_formula(clean_input($row[3] ?? ''));
        $unit = neutralize_csv_formula(clean_input($row[14] ?? 'piece'));
        $raw_image = trim($row[15] ?? '');
        $image = is_valid_product_image_url($raw_image) ? $raw_image : 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80';
        $description = neutralize_csv_formula(clean_input($row[16] ?? ''));

        if (empty($name)) continue;

        // Whitelist category and vendor
        $in_cat = intval($row[4] ?? 1);
        $cat_id = in_array($in_cat, $valid_cats) ? $in_cat : (int)$valid_cats[0];

        $in_ven = intval($row[6] ?? 1);
        $vendor_id = in_array($in_ven, $valid_vendors) ? $in_ven : (int)$valid_vendors[0];

        $code = trim($row[1] ?? '');
        if (empty($code)) {
            $code = 'GGH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3)) . '-' . random_int(100, 999);
        }
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . random_int(100, 999);
        $discount = ($original_price > $price) ? round((($original_price - $price) / $original_price) * 100) : 0;

        try {
            $stmt_ins->execute([
                $code, $vendor_id, $cat_id, $name, $hindi_name, $slug, $price, $original_price,
                $discount, $weight_kg, $stock, $unit, $image, $description
            ]);
            $imported_count++;
        } catch (Exception $e) {
            // skip row if duplicate code or constraint error
        }
    }

    fclose($handle);
    log_audit_action('admin_import_products', 'products', 'import', null, "Imported {$imported_count} products via CSV (parsed {$row_count} rows)");
    set_flash('success', "सफलतापूर्वक $imported_count उत्पाद CSV फ़ाइल से कैटलॉग में जोड़े गए!");
    header('Location: /admin/products.php');
    exit;
}

header('Location: /admin/products.php');
exit;
