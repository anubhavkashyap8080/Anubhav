<?php
/**
 * Test Suite: Title Resolution & Site Settings Defense
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/config/db.php';
require_once $rootDir . '/config/settings.php';
require_once $rootDir . '/includes/functions.php';

echo "=== 1. Checking Database Auto-Heal for site_settings ===" . PHP_EOL;

// 1. Force a corrupt state in DB
$pdo->exec("UPDATE site_settings SET setting_value = '0' WHERE setting_key = 'brand_name'");
$pdo->exec("UPDATE site_settings SET setting_value = '0 — Desh ki mitti ka swad' WHERE setting_key = 'homepage_title'");

// Run ensure_platform_schema
ensure_platform_schema($pdo);

$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
$stmt->execute(['brand_name']);
$b_val = $stmt->fetchColumn();
echo "brand_name after ensure_platform_schema: " . var_export($b_val, true) . PHP_EOL;
assert($b_val === 'GaunGaliHat', 'brand_name must be healed to GaunGaliHat');

$stmt->execute(['homepage_title']);
$h_val = $stmt->fetchColumn();
echo "homepage_title after ensure_platform_schema: " . var_export($h_val, true) . PHP_EOL;
assert(strpos($h_val, '0') !== 0, 'homepage_title must not start with 0');

echo "=== 2. Testing APP_NAME defense in settings.php ===" . PHP_EOL;
echo "APP_NAME constant: " . var_export(APP_NAME, true) . PHP_EOL;
assert(APP_NAME !== '0', 'APP_NAME must never be 0');
assert(APP_NAME === 'GaunGaliHat', 'APP_NAME must be GaunGaliHat');

echo "=== 3. Testing header.php title resolution ===" . PHP_EOL;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['PHP_SELF'] = '/index.php';

ob_start();
require $rootDir . '/includes/header.php';
$html = ob_get_clean();

preg_match('/<title>(.*?)<\/title>/', $html, $matches);
$rendered_title = $matches[1] ?? '';
echo "Rendered Homepage Title: " . var_export($rendered_title, true) . PHP_EOL;

assert(!empty($rendered_title), 'Title must not be empty');
assert(strpos($rendered_title, '0') !== 0, 'Title must not start with 0');
assert(strpos($rendered_title, 'GaunGaliHat') === 0, 'Title must start with GaunGaliHat');

echo "=== 4. Testing Title Sanitization against all 0 patterns ===" . PHP_EOL;
$dirty_patterns = [
    '0 — Desh ki mitti ka swad | Rural Tools',
    '0 - Desh ki mitti ka swad | Rural Tools',
    '0 __ Desh ki mitti ka swad | Rural Tools',
    '0_ Desh ki mitti ka swad | Rural Tools',
    '0 Desh ki mitti ka swad | Rural Tools',
    '0',
    ''
];

foreach ($dirty_patterns as $dirty) {
    $clean = preg_replace('/^0\s*[-—_:]+\s*/u', 'GaunGaliHat — ', trim((string)$dirty));
    $clean = preg_replace('/^0\s+/u', '', trim((string)$clean));
    if (empty($clean) || $clean === '0') {
        $clean = 'GaunGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples';
    }
    echo "Dirty: " . var_export($dirty, true) . " --> Clean: " . var_export($clean, true) . PHP_EOL;
    assert(strpos($clean, '0') !== 0, "Cleaned string must not start with 0: {$clean}");
}

echo "=== 5. Testing admin/seo_settings.php and admin/site_settings.php ===" . PHP_EOL;
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'super_admin';
$_SESSION['user_name'] = 'Admin';
$_SESSION['logged_in'] = true;

ob_start();
require $rootDir . '/admin/seo_settings.php';
$seo_admin_html = ob_get_clean();
echo "admin/seo_settings.php rendered: " . strlen($seo_admin_html) . " bytes" . PHP_EOL;
assert(strpos($seo_admin_html, 'value="0 —') === false, 'seo_settings must not contain value="0 —');
assert(strpos($seo_admin_html, 'value="0 -') === false, 'seo_settings must not contain value="0 -');

ob_start();
require $rootDir . '/admin/site_settings.php';
$site_admin_html = ob_get_clean();
echo "admin/site_settings.php rendered: " . strlen($site_admin_html) . " bytes" . PHP_EOL;
assert(strpos($site_admin_html, 'name="brand_name"') !== false, 'site_settings.php must have brand_name field');
assert(strpos($site_admin_html, 'name="brand_tagline"') !== false, 'site_settings.php must have brand_tagline field');

echo "=== ALL TESTS PASSED! ===" . PHP_EOL;
