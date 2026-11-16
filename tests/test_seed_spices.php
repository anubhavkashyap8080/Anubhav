<?php
/**
 * Test Suite: S S Industries Vendor & Kalyani Masala Spices Seeding
 */

define('TESTING_MOCK_DB', true);

$passed = 0;
$failed = 0;

function assert_test($cond, $name) {
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

echo "=== 1. Checking Syntax of Seeder & Migration Files ===\n";
$seeder_file = dirname(__DIR__) . '/database/seed_ss_industries_spices.php';
assert_test(file_exists($seeder_file), "Seeder file exists");
exec('php -l ' . escapeshellarg($seeder_file), $out, $ret);
assert_test($ret === 0, "No syntax errors in seed_ss_industries_spices.php");

echo "\n=== 2. Testing Database Seeding in SQLite Memory ===\n";
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Schema
$pdo->exec("
    CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        hindi_name TEXT,
        slug TEXT UNIQUE,
        description TEXT,
        image TEXT,
        sort_order INTEGER DEFAULT 0
    );

    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        unique_code TEXT,
        name TEXT,
        username TEXT UNIQUE,
        email TEXT UNIQUE,
        phone TEXT,
        password TEXT,
        role TEXT DEFAULT 'customer',
        city TEXT,
        pincode TEXT,
        address TEXT,
        business_name TEXT,
        gstin TEXT,
        status TEXT DEFAULT 'active'
    );

    CREATE TABLE vendors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        name TEXT,
        business_name TEXT,
        slug TEXT UNIQUE,
        type TEXT,
        address TEXT,
        village TEXT,
        district TEXT,
        state TEXT,
        pincode TEXT,
        phone TEXT,
        whatsapp TEXT,
        email TEXT,
        story TEXT,
        pan_number TEXT,
        gstin TEXT,
        verified INTEGER DEFAULT 0,
        kyc_status TEXT DEFAULT 'Pending',
        commission_rate REAL DEFAULT 4.0
    );

    CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE,
        vendor_id INTEGER,
        category_id INTEGER,
        name TEXT,
        hindi_name TEXT,
        slug TEXT UNIQUE,
        price REAL,
        original_price REAL,
        discount INTEGER,
        weight_kg REAL,
        length_cm REAL,
        width_cm REAL,
        height_cm REAL,
        volumetric_weight REAL,
        stock INTEGER DEFAULT 100,
        stock_status TEXT DEFAULT 'In Stock',
        rating REAL DEFAULT 5.0,
        review_count INTEGER DEFAULT 0,
        image TEXT,
        unit TEXT,
        description TEXT,
        seo_title TEXT,
        seo_description TEXT,
        seo_keywords TEXT,
        bulk_available INTEGER DEFAULT 1,
        bulk_moq INTEGER DEFAULT 10,
        bulk_price REAL DEFAULT 0,
        bulk_tier2_qty INTEGER DEFAULT 0,
        bulk_tier2_price REAL DEFAULT 0,
        bulk_packaging_desc TEXT,
        bulk_lead_time_days INTEGER DEFAULT 2
    );
");

require_once $seeder_file;
$res = seed_ss_industries_kalyani_spices($pdo);

assert_test($res['total'] === 19, "Seeder returned 19 products");
assert_test($res['inserted'] === 19, "19 products inserted on first run");

// Verify Category
$cat = $pdo->query("SELECT * FROM categories WHERE slug = 'spices'")->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($cat), "Category 'spices' created");
assert_test($cat['name'] === 'Spices', "Category name is 'Spices'");
assert_test(!empty($cat['hindi_name']), "Category has Hindi name: " . $cat['hindi_name']);

// Verify Vendor
$v = $pdo->query("SELECT * FROM vendors WHERE slug = 'ss-industries'")->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($v), "Vendor 'S S Industries' created");
assert_test($v['business_name'] === 'M/S S.S. INDUSTRIES', "Business name is M/S S.S. INDUSTRIES");
assert_test($v['phone'] === '+91 7897492100', "Phone is +91 7897492100");
assert_test($v['gstin'] === '09CHCPK0881J125', "GSTIN is 09CHCPK0881J125");
assert_test($v['kyc_status'] === 'Verified', "KYC status is Verified");
assert_test($v['district'] === 'Hardoi', "District is Hardoi");

// Verify Products
$prod_count = $pdo->query("SELECT COUNT(*) FROM products WHERE vendor_id = {$v['id']} AND category_id = {$cat['id']}")->fetchColumn();
assert_test((int)$prod_count === 19, "Exactly 19 products linked to S S Industries and Spices category");

// Verify Specific Products
$garam = $pdo->query("SELECT * FROM products WHERE code = 'GGH-SP-001'")->fetch(PDO::FETCH_ASSOC);
assert_test($garam['name'] === 'Kalyani Premium Garam Masala', "GGH-SP-001 is Kalyani Premium Garam Masala");
assert_test(floatval($garam['price']) === 48.00, "Garam Masala price is ₹48.00");
assert_test(intval($garam['bulk_moq']) === 20, "Garam Masala bulk MOQ is 20 pcs");
assert_test(floatval($garam['bulk_price']) === 38.00, "Garam Masala bulk price is ₹38.00");
assert_test(file_exists(dirname(__DIR__) . $garam['image']), "Image file exists on disk: " . $garam['image']);

$besan = $pdo->query("SELECT * FROM products WHERE code = 'GGH-SP-002'")->fetch(PDO::FETCH_ASSOC);
assert_test($besan['name'] === 'Kalyani Pure Chana Dal Besan', "GGH-SP-002 is Kalyani Pure Chana Dal Besan");
assert_test(floatval($besan['price']) === 120.00, "Besan price is ₹120.00");
assert_test(floatval($besan['weight_kg']) === 1.00, "Besan weight is 1.00 kg");
assert_test(file_exists(dirname(__DIR__) . $besan['image']), "Image file exists on disk: " . $besan['image']);

$chilli = $pdo->query("SELECT * FROM products WHERE code = 'GGH-SP-003'")->fetch(PDO::FETCH_ASSOC);
assert_test($chilli['name'] === 'Kalyani Pure Red Chilli Powder (Lal Mirch)', "GGH-SP-003 is Kalyani Pure Red Chilli Powder");
assert_test(floatval($chilli['price']) === 200.00, "Red Chilli price is ₹200.00");
assert_test(file_exists(dirname(__DIR__) . $chilli['image']), "Image file exists on disk: " . $chilli['image']);

// Verify Idempotency: Run second time
$res2 = seed_ss_industries_kalyani_spices($pdo);
assert_test($res2['updated'] === 19, "Second run updates 19 products without duplicating");
$final_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
assert_test((int)$final_count === 19, "Total products count remains 19 after re-seeding");

echo "\n============================================\n";
echo "SPICES & VENDOR SEED TEST RESULTS: Passed: $passed, Failed: $failed\n";
echo "============================================\n";
exit($failed > 0 ? 1 : 0);
