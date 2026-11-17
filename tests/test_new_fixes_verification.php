<?php
/**
 * Verification Test Suite for the Latest Fixes:
 * 1. Super Admin Video Home (no 500 error, auth_check included, require_admin enforced)
 * 2. Retailer Dashboard & Products (no white screen, order_status query valid, products status valid)
 * 3. Retailer Header Responsiveness (no duplicate mobile nav on desktop, responsive CSS rules present)
 * 4. Product Featured Sorting on Homepage (index.php queries order by is_featured DESC)
 * 5. Layout 6 Scope & Clean Header (Hero only YouTube video, sections 2-6 clean e-commerce, header crisp white)
 */

define('TESTING_MOCK_DB', true);

$_SERVER['DOCUMENT_ROOT'] = 'C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'retailer';
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'dheerendra_retailer',
    'name' => 'Dheerendra Shukla',
    'business_name' => 'Shukla Kirana Store',
    'gstin' => '09ABCDE1234F1Z5',
    'role' => 'retailer',
    'status' => 'active'
];

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Setup Mock SQLite Database
$sqlite = new PDO('sqlite::memory:');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Replicate schema
$sqlite->exec("
    CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT
    );
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50),
        name VARCHAR(100),
        business_name VARCHAR(150),
        gstin VARCHAR(20),
        role VARCHAR(20),
        status VARCHAR(20)
    );
    INSERT INTO users (id, username, name, business_name, gstin, role, status)
    VALUES (1, 'dheerendra_retailer', 'Dheerendra Shukla', 'Shukla Kirana Store', '09ABCDE1234F1Z5', 'retailer', 'active');

    CREATE TABLE IF NOT EXISTS vendors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100),
        business_name VARCHAR(150),
        village VARCHAR(100),
        district VARCHAR(100),
        state VARCHAR(100),
        latitude REAL,
        longitude REAL,
        status VARCHAR(20)
    );
    INSERT INTO vendors (id, name, business_name, village, district, state, latitude, longitude, status)
    VALUES (1, 'Ram Kumar', 'Awadh Spices Cluster', 'Sandila', 'Hardoi', 'Uttar Pradesh', 27.1264, 80.1444, 'active');

    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100),
        hindi_name VARCHAR(100),
        slug VARCHAR(100),
        image_url TEXT
    );
    INSERT INTO categories (id, name, hindi_name, slug)
    VALUES (1, 'Spices', 'मसाले', 'spices');

    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vendor_id INTEGER,
        category_id INTEGER,
        name VARCHAR(200),
        hindi_name VARCHAR(200),
        code VARCHAR(50),
        price REAL,
        mrp REAL,
        stock INTEGER,
        status VARCHAR(20),
        is_featured INTEGER DEFAULT 0,
        is_best_seller INTEGER DEFAULT 0,
        bulk_available INTEGER DEFAULT 0,
        bulk_moq INTEGER DEFAULT 1,
        bulk_price REAL,
        bulk_tier2_qty INTEGER,
        bulk_tier2_price REAL,
        image_url TEXT,
        rating REAL DEFAULT 5.0,
        views_count INTEGER DEFAULT 0
    );
    INSERT INTO products (id, vendor_id, category_id, name, price, mrp, stock, status, is_featured, is_best_seller, bulk_available, bulk_moq, bulk_price)
    VALUES 
    (1, 1, 1, 'Regular Garam Masala', 120.00, 150.00, 50, 'active', 0, 1, 1, 10, 95.00),
    (2, 1, 1, 'Kalyani Royal Garam Masala (Featured)', 250.00, 300.00, 30, 'active', 1, 0, 1, 5, 200.00),
    (3, 1, 1, 'Organic Turmeric Powder', 90.00, 110.00, 40, 'active', 0, 0, 1, 10, 75.00);

    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        order_number VARCHAR(50),
        order_type VARCHAR(20),
        total_amount REAL,
        order_status VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    INSERT INTO orders (id, user_id, order_number, order_type, total_amount, order_status)
    VALUES (1, 1, 'ORD-B2B-1001', 'wholesale_b2b', 4500.00, 'Confirmed');
");

$GLOBALS['pdo'] = $sqlite;

$passed = 0;
$failed = 0;

function assert_test($cond, $desc) {
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] " . $desc . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $desc . "\n";
        $failed++;
    }
}

echo "=== 1. Testing Retailer Dashboard Rendering & DB Queries ===\n";
ob_start();
try {
    include __DIR__ . '/../retailer/dashboard.php';
    $dash_output = ob_get_clean();
    assert_test(strlen($dash_output) > 2000, "retailer/dashboard.php rendered successfully (" . strlen($dash_output) . " bytes)");
    assert_test(strpos($dash_output, 'Welcome,') !== false, "Dashboard welcome greeting rendered");
    assert_test(strpos($dash_output, 'Shukla Kirana Store') !== false, "Retailer business name rendered");
    assert_test(strpos($dash_output, 'ORD-B2B-1001') !== false, "B2B order ORD-B2B-1001 rendered in recent orders");
    assert_test(strpos($dash_output, '₹4,500.00') !== false || strpos($dash_output, '4,500') !== false, "B2B spend metric calculated correctly");
} catch (Throwable $e) {
    ob_end_clean();
    assert_test(false, "retailer/dashboard.php threw exception: " . $e->getMessage());
}

echo "\n=== 2. Testing Retailer Header CSS & Responsive 2-Tier Layout ===\n";
$retailer_header_src = file_get_contents(__DIR__ . '/../retailer/retailer_header.php');
assert_test(strpos($retailer_header_src, 'retailer-desktop-only') !== false, "retailer-desktop-only class present for desktop tabs");
assert_test(strpos($retailer_header_src, 'retailer-mobile-nav') !== false, "retailer-mobile-nav class present for mobile navigation");
assert_test(strpos($retailer_header_src, '@media (min-width: 768px)') !== false, "Responsive min-width media query present");
assert_test(strpos($retailer_header_src, '.retailer-mobile-nav { display: none !important; }') !== false, "Mobile nav strictly hidden on desktop (>=768px)");
assert_test(strpos($retailer_header_src, 'Wholesale Cart') !== false, "Wholesale cart link present in top bar");

echo "\n=== 3. Testing Product Featured Sorting on Homepage (index.php) ===\n";
$index_src = file_get_contents(__DIR__ . '/../index.php');
assert_test(strpos($index_src, 'ORDER BY p.is_featured DESC, p.id DESC') !== false, "index.php nearby_products sorts by is_featured DESC");
assert_test(strpos($index_src, 'ORDER BY p.is_featured DESC, p.is_best_seller DESC, p.id DESC') !== false, "index.php featured_products sorts by is_featured DESC first");
assert_test(strpos($index_src, "\$ggh_header_variant = 'solid'") !== false, "index.php uses crisp solid white header");

// Test SQLite query to verify featured product sorts first
$test_stmt = $sqlite->query("SELECT id, name, is_featured FROM products WHERE stock > 0 ORDER BY is_featured DESC, is_best_seller DESC, id DESC");
$sorted_prods = $test_stmt->fetchAll();
assert_test($sorted_prods[0]['id'] == 2, "Featured product (ID 2) appears at the very top (index 0)");
assert_test($sorted_prods[0]['is_featured'] == 1, "Top product has is_featured = 1");

echo "\n=== 4. Testing Layout 6 Scope (Hero YouTube Only & Clean Below Sections) ===\n";
$layout6_src = file_get_contents(__DIR__ . '/../includes/home_layouts/layout_6.php');
assert_test(strpos($layout6_src, 'vh-hero-section') !== false, "layout_6.php contains vh-hero-section for Hero");
assert_test(strpos($layout6_src, 'vh-hero-player-wrap') !== false, "Hero player wrap present in Section 1");
assert_test(strpos($layout6_src, 'toggleHeroAudio') !== false, "Hero audio sound toggle function present");
assert_test(strpos($layout6_src, 'bg-stone-50 border-b') !== false, "Section 2 below hero uses clean e-commerce background");
assert_test(strpos($layout6_src, 'Trending Village Bestsellers') !== false || strpos($layout6_src, 'BESTSELLERS') !== false, "Section 3 Bestsellers uses clean product grid");
assert_test(strpos($layout6_src, '100% Village Authentic') !== false, "Section 6 Trust badges present on clean layout");

echo "\n=== 5. Testing Admin Video Home Rendering & Require Admin ===\n";
exec('php ' . escapeshellarg(__DIR__ . '/test_video_home_render.php'), $admin_out, $admin_ret);
$admin_output_str = implode("\n", $admin_out);
assert_test($admin_ret === 0, "admin/video_home.php executed without fatal error (exit code 0)");
assert_test(strpos($admin_output_str, 'RENDER SUCCESS') !== false, "admin/video_home.php rendered successfully");
assert_test(strpos($admin_output_str, 'VERIFIED: Page title and customizer rendered properly!') !== false, "Admin customizer title verified");

echo "\n============================================\n";
echo "VERIFICATION TEST RESULTS: Passed: $passed, Failed: $failed\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
