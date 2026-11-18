<?php
/**
 * Test Suite: Layout 7 (ToolsVilla-Style Rural & Agri Marketplace)
 * Verifies syntax, admin customizer integration, rendering, and English default language.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'superadmin',
    'role' => 'super_admin',
    'status' => 'active'
];

$base_dir = dirname(__DIR__);

// Mock PDO with in-memory SQLite BEFORE loading any files
$sqlite = new PDO('sqlite::memory:');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->exec("
    CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT
    );
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50),
        role VARCHAR(20),
        status VARCHAR(20)
    );
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100),
        slug VARCHAR(100),
        sort_order INTEGER DEFAULT 0
    );
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255),
        slug VARCHAR(255),
        price DECIMAL(10,2),
        sale_price DECIMAL(10,2),
        primary_image VARCHAR(255),
        is_featured INTEGER DEFAULT 0,
        status VARCHAR(50) DEFAULT 'active'
    );
    INSERT INTO users (id, username, role, status) VALUES (1, 'superadmin', 'super_admin', 'active');
");
$GLOBALS['pdo'] = $sqlite;
$pdo = $sqlite;

require_once $base_dir . '/includes/functions.php';

$test_count = 0;
$pass_count = 0;

function assert_test($description, $condition) {
    global $test_count, $pass_count;
    $test_count++;
    if ($condition) {
        $pass_count++;
        echo " [PASS] $description\n";
    } else {
        echo "![FAIL] $description\n";
    }
}

echo "=== Running Layout 7 & ToolsVilla Marketplace Test Suite ===\n\n";

// Test 1: layout_7.php file existence & syntax lint
$l7_file = $base_dir . '/includes/home_layouts/layout_7.php';
assert_test("layout_7.php exists", file_exists($l7_file));
$lint_out = shell_exec("php -l " . escapeshellarg($l7_file));
assert_test("layout_7.php passes php -l syntax check", strpos($lint_out, 'No syntax errors detected') !== false);

// Test 2: admin/toolsvilla_home.php existence, syntax & English policy
$admin_l7_file = $base_dir . '/admin/toolsvilla_home.php';
assert_test("admin/toolsvilla_home.php exists", file_exists($admin_l7_file));
$admin_lint = shell_exec("php -l " . escapeshellarg($admin_l7_file));
assert_test("admin/toolsvilla_home.php passes php -l syntax check", strpos($admin_lint, 'No syntax errors detected') !== false);

$admin_content = file_get_contents($admin_l7_file);
preg_match_all('/[\x{0900}-\x{097F}]/u', $admin_content, $matches);
assert_test("admin/toolsvilla_home.php contains 0 Devanagari characters (100% English)", count($matches[0]) === 0);
assert_test("admin/toolsvilla_home.php requires admin auth", strpos($admin_content, 'require_admin()') !== false);
assert_test("admin/toolsvilla_home.php enforces CSRF protection", strpos($admin_content, 'csrf_protect()') !== false);

// Test 3: index.php includes layout_7 in valid layouts
$index_content = file_get_contents($base_dir . '/index.php');
assert_test("index.php includes 'layout_7' in \$valid_layouts", strpos($index_content, "'layout_7'") !== false);
assert_test("index.php supports numeric shorthand '7'", strpos($index_content, "'7'") !== false);

// Test 4: admin/hero_customizer.php includes Layout 7 radio option
$hero_cust = file_get_contents($base_dir . '/admin/hero_customizer.php');
assert_test("admin/hero_customizer.php has Layout 7 radio option", strpos($hero_cust, 'value="layout_7"') !== false);
assert_test("admin/hero_customizer.php links to /admin/toolsvilla_home.php", strpos($hero_cust, '/admin/toolsvilla_home.php') !== false);

// Test 5: admin/admin_header.php includes navigation link
$admin_hdr = file_get_contents($base_dir . '/admin/admin_header.php');
assert_test("admin/admin_header.php links to toolsvilla_home.php", strpos($admin_hdr, 'toolsvilla_home.php') !== false);

// Test 6: Mock execution and layout 7 rendering
ob_start();
// Mock variables required by homepage
$products = [
    [
        'id' => 101,
        'name' => 'High Carbon Steel Sickle',
        'slug' => 'steel-sickle',
        'price' => 299,
        'sale_price' => 249,
        'primary_image' => 'https://via.placeholder.com/300',
        'is_featured' => 1
    ]
];
$categories = [];
$active_layout = 'layout_7';
$user_lat = 27.1264;
$user_lng = 80.1444;
$delivery_location_text = "Hardoi, Uttar Pradesh - 241126";

include $l7_file;
$rendered_html = ob_get_clean();

assert_test("Layout 7 renders non-empty HTML", !empty($rendered_html));
assert_test("Layout 7 contains top dispatch bar", strpos($rendered_html, 'Direct Dispatch') !== false);
assert_test("Layout 7 contains vertical department sidebar", (strpos($rendered_html, 'Farming &amp; Agricultural Tools') !== false || strpos($rendered_html, 'Farming &amp; Agriculture') !== false));
assert_test("Layout 7 contains central hero banner with pill badge", strpos($rendered_html, "One Stop Rural &amp; Agri Marketplace") !== false);
assert_test("Layout 7 contains 5 floating category badges", strpos($rendered_html, 'Farm &amp; Garden') !== false || strpos($rendered_html, 'Farm & Garden') !== false);
assert_test("Layout 7 contains 3 metrics bar", strpos($rendered_html, '70,000+ Products') !== false);
assert_test("Layout 7 contains right trust cards (Google 4.9 & 3-Day Returns)", strpos($rendered_html, 'Trusted by 10,000+ Farmers') !== false && strpos($rendered_html, 'Easy 3-Day Returns') !== false);
assert_test("Layout 7 contains 5-point value proposition bar", strpos($rendered_html, 'Free Shipping') !== false && strpos($rendered_html, 'GST Invoice') !== false);
assert_test("Layout 7 contains Department Spotlight 1 (Farming & Agriculture - Screenshot 2)", strpos($rendered_html, 'Your farm deserves the right equipment.') !== false);
assert_test("Layout 7 contains Department Spotlight 2 (Food & HoReCa - Screenshot 3)", strpos($rendered_html, 'Everything hotels, restaurants') !== false);
assert_test("Layout 7 contains Department Spotlight 3 (Pumps & Electrical - Screenshot 4)", strpos($rendered_html, 'Find water pumps and electrical essentials') !== false);
assert_test("Layout 7 contains B2B Wholesale Banner", strpos($rendered_html, 'B2B') !== false);

// Test 7: Button padding verification (no clipping)
assert_test("Hero CTA button has min-h-[50px] and px-8/px-10 padding", strpos($rendered_html, 'min-h-[50px]') !== false && strpos($rendered_html, 'px-8 sm:px-10') !== false);

echo "\nTest Results: $pass_count / $test_count passed.\n";
if ($pass_count === $test_count) {
    echo " All Layout 7 tests PASSED successfully!\n";
    exit(0);
} else {
    echo " Some tests FAILED!\n";
    exit(1);
}
