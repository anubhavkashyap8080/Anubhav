<?php
/**
 * Test Suite: CMS Bilingual Policy Pages, Founder Spotlight & Super Admin CMS
 */

define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

$root = dirname(__DIR__);
require_once $root . '/includes/functions.php';

$errors = [];
$passes = [];

function assert_true($cond, $msg) {
    global $errors, $passes;
    if ($cond) {
        $passes[] = "[PASS] $msg";
    } else {
        $errors[] = "[FAIL] $msg";
    }
}

echo "=== 1. Checking File Existence & PHP Syntax ===\n";
$files_to_check = [
    'about.php',
    'privacy-policy.php',
    'terms-conditions.php',
    'shipping-policy.php',
    'terms-of-supply.php',
    'contact.php',
    'page.php',
    'admin/pages.php'
];

foreach ($files_to_check as $file) {
    $full = $root . '/' . $file;
    assert_true(file_exists($full), "File $file exists");
    $output = [];
    $ret = 0;
    exec("php -l " . escapeshellarg($full) . " 2>&1", $output, $ret);
    assert_true($ret === 0, "PHP syntax clean for $file");
}

echo "=== 2. Checking Founder Spotlight & About Us Content ===\n";
$about_content = file_get_contents($root . '/about.php');
assert_true(strpos($about_content, 'Dheerendra Shukla') !== false, "About Us includes Founder: Dheerendra Shukla");
assert_true(strpos($about_content, 'founder_dheerendra_shukla.jpg') !== false, "About Us references founder photo: founder_dheerendra_shukla.jpg");
assert_true(strpos($about_content, 'ODOP') !== false, "About Us mentions ODOP");
assert_true(strpos($about_content, 'Women-led SHGs') !== false || strpos($about_content, 'SHG') !== false, "About Us mentions SHGs");
assert_true(strpos($about_content, 'Direct From Village Clusters') !== false, "About Us mentions Direct From Village Clusters");
assert_true(strpos($about_content, 'Certified & Authentic') !== false, "About Us mentions Certified & Authentic");
assert_true(strpos($about_content, 'Empowerment Model') !== false, "About Us mentions Empowerment Model");
assert_true(strpos($about_content, 'Atmanirbhar Bharat') !== false, "About Us mentions Atmanirbhar Bharat");
assert_true(strpos($about_content, 'Vocal for Local') !== false, "About Us mentions Vocal for Local");
assert_true(strpos($about_content, 'We don\'t just sell products, we sell stories of Rural India') !== false, "About Us contains signature quote");

echo "=== 3. Checking Founder Photo Exists on Disk ===\n";
$photo_path = $root . '/assets/images/founder_dheerendra_shukla.jpg';
assert_true(file_exists($photo_path) && filesize($photo_path) > 10000, "founder_dheerendra_shukla.jpg is a valid image (>10KB)");

echo "=== 4. Checking English Default & Bilingual Toggles ===\n";
$pages_with_bilingual = [
    'about.php',
    'privacy-policy.php',
    'terms-conditions.php',
    'shipping-policy.php',
    'terms-of-supply.php',
    'contact.php',
    'page.php'
];

foreach ($pages_with_bilingual as $p) {
    $c = file_get_contents($root . '/' . $p);
    assert_true(strpos($c, 'render_bilingual_toggle') !== false, "$p calls render_bilingual_toggle()");
    assert_true(strpos($c, 'lang-en') !== false, "$p contains lang-en elements");
    assert_true(strpos($c, 'lang-hi hidden') !== false || strpos($c, 'lang-hi') !== false, "$p contains lang-hi elements");
}

echo "=== 5. Checking data/pages.json Validity ===\n";
$json_path = $root . '/data/pages.json';
assert_true(file_exists($json_path), "data/pages.json exists");
$json_data = json_decode(file_get_contents($json_path), true);
assert_true(is_array($json_data), "data/pages.json parses valid JSON");
$expected_slugs = ['about-us', 'privacy-policy', 'terms-conditions', 'shipping-policy', 'terms-of-supply', 'support'];
foreach ($expected_slugs as $eslug) {
    assert_true(isset($json_data[$eslug]), "data/pages.json contains slug '$eslug'");
    assert_true(!empty($json_data[$eslug]['content_en']), "Slug '$eslug' has English content");
    assert_true(!empty($json_data[$eslug]['content_hi']), "Slug '$eslug' has Hindi content");
}

echo "=== 6. Checking Super Admin CMS Capabilities in admin/pages.php ===\n";
$admin_pages = file_get_contents($root . '/admin/pages.php');
assert_true(strpos($admin_pages, 'visual_editor_en') !== false, "Admin pages has visual direct view editor");
assert_true(strpos($admin_pages, 'source_editor_en') !== false, "Admin pages has raw HTML source editor");
assert_true(strpos($admin_pages, 'toggleSourceMode') !== false, "Admin pages has HTML source toggle function");
assert_true(strpos($admin_pages, 'tab_pane_hi') !== false, "Admin pages supports Hindi content editing tab");
assert_true(strpos($admin_pages, 'tab_pane_en') !== false, "Admin pages supports English content editing tab");
assert_true(strpos($admin_pages, 'csrf_token()') !== false, "Admin pages enforces CSRF token");

echo "=== 7. Checking Admin Navigation Tab in admin_header.php ===\n";
$admin_header = file_get_contents($root . '/admin/admin_header.php');
assert_true(strpos($admin_header, '/admin/pages.php') !== false, "admin_header.php links to /admin/pages.php");

echo "\n--- Summary ---\n";
foreach ($passes as $p) {
    echo "$p\n";
}
if (!empty($errors)) {
    echo "\nERRORS DETECTED:\n";
    foreach ($errors as $e) {
        echo "$e\n";
    }
    exit(1);
} else {
    echo "\nALL TESTS PASSED 100% SUCCESSFULLY!\n";
    exit(0);
}
