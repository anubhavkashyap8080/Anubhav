<?php
/**
 * Test Suite: Super Admin Maintenance Mode
 * Verifies site lock, HTTP 503 headers, bypass rules, bilingual support, and Admin controls.
 */

define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

echo "============================================\n";
echo "GANVGALIHAT MAINTENANCE MODE TEST SUITE\n";
echo "============================================\n\n";

$pass_count = 0;
$fail_count = 0;

function assert_test($condition, $label) {
    global $pass_count, $fail_count;
    if ($condition) {
        echo "[PASS] $label\n";
        $pass_count++;
    } else {
        echo "[FAIL] $label\n";
        $fail_count++;
    }
}

// 1. PHP Syntax Check
$files = [
    'config/db.php',
    'includes/functions.php',
    'maintenance.php',
    'admin/maintenance.php',
    'admin/admin_header.php',
    'admin/site_settings.php',
    'includes/header.php'
];

echo "=== 1. Checking PHP Syntax of All Files ===\n";
foreach ($files as $f) {
    $path = dirname(__DIR__) . '/' . $f;
    assert_test(file_exists($path), "File exists: $f");
    $output = [];
    $ret = 0;
    exec("php -l " . escapeshellarg($path), $output, $ret);
    assert_test($ret === 0, "Syntax clean for $f");
}

// 2. Functional Definitions Check
require_once dirname(__DIR__) . '/includes/functions.php';

echo "\n=== 2. Checking Maintenance Functions in includes/functions.php ===\n";
assert_test(function_exists('is_maintenance_mode_active'), "Function is_maintenance_mode_active() exists");
assert_test(function_exists('get_maintenance_settings'), "Function get_maintenance_settings() exists");
assert_test(function_exists('can_bypass_maintenance_mode'), "Function can_bypass_maintenance_mode() exists");
assert_test(function_exists('check_maintenance_mode'), "Function check_maintenance_mode() exists");

// 3. Settings Retrieval & Defaults
echo "\n=== 3. Testing Settings Retrieval & Defaults ===\n";
$settings = get_maintenance_settings();
assert_test(is_array($settings), "get_maintenance_settings() returns an array");
assert_test(isset($settings['enabled']), "Settings array contains 'enabled'");
assert_test(isset($settings['title']), "Settings array contains 'title'");
assert_test(isset($settings['title_hi']), "Settings array contains 'title_hi'");
assert_test(isset($settings['message']), "Settings array contains 'message'");
assert_test(isset($settings['message_hi']), "Settings array contains 'message_hi'");
assert_test(isset($settings['end_time']), "Settings array contains 'end_time'");
assert_test(isset($settings['allowed_ips']), "Settings array contains 'allowed_ips'");
assert_test(isset($settings['secret_bypass']), "Settings array contains 'secret_bypass'");
assert_test(isset($settings['show_contact']), "Settings array contains 'show_contact'");

// 4. Testing Bypass Logic Under Different Conditions
echo "\n=== 4. Testing Maintenance Bypass Rules ===\n";

// A. Non-admin guest with no bypass
$_SESSION = [];
$_GET = [];
$_COOKIE = [];
$_SERVER['REMOTE_ADDR'] = '203.0.113.199'; // Simulated public IP

// Mock non-bypass state
$can_guest = can_bypass_maintenance_mode();
assert_test($can_guest === false, "Regular non-admin visitor is NOT allowed to bypass");

// B. Secret Token in GET parameter
$_GET['bypass'] = $settings['secret_bypass'];
$can_token = can_bypass_maintenance_mode();
assert_test($can_token === true, "Visitor with valid ?bypass=TOKEN is allowed to bypass");
unset($_GET['bypass']);

// C. Authorized Testing Cookie
$salt = defined('DB_PASS') ? DB_PASS : 'ganv_maint_key';
$_COOKIE['ganv_maint_bypass'] = hash_hmac('sha256', $settings['secret_bypass'], $salt);
$can_cookie = can_bypass_maintenance_mode();
assert_test($can_cookie === true, "Visitor with valid bypass cookie is allowed to bypass");
unset($_COOKIE['ganv_maint_bypass']);

// D. IP Whitelist
$ip_test = '198.51.100.55';
$_SERVER['REMOTE_ADDR'] = $ip_test;

// Simulate IP whitelist by temporarily setting site_settings if DB connected
if (isset($pdo) && $pdo) {
    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        $sql = $is_sqlite 
            ? "INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)"
            : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['maintenance_mode_allowed_ips', '127.0.0.1, ' . $ip_test . ', 10.0.0.1']);

        $can_ip = can_bypass_maintenance_mode();
        assert_test($can_ip === true, "Visitor with whitelisted IP ($ip_test) is allowed to bypass");

        // Clean up
        $stmt->execute(['maintenance_mode_allowed_ips', '']);
    } catch (Exception $e) {
        echo "[INFO] Skipped live DB whitelist write test: " . $e->getMessage() . "\n";
    }
}

// 5. Checking maintenance.php Screen Content & Elements
echo "\n=== 5. Checking Public Maintenance Page (maintenance.php) ===\n";
$maint_code = file_get_contents(dirname(__DIR__) . '/maintenance.php');

assert_test(strpos($maint_code, 'http_response_code(503)') !== false, "maintenance.php sends HTTP 503 Service Unavailable");
assert_test(strpos($maint_code, 'Retry-After: 3600') !== false, "maintenance.php sends Retry-After header for SEO");
assert_test(strpos($maint_code, 'lang-en') !== false, "maintenance.php has English content container");
assert_test(strpos($maint_code, 'lang-hi') !== false, "maintenance.php has Hindi content container");
assert_test(strpos($maint_code, 'switchLang') !== false, "maintenance.php contains bilingual language switcher");
assert_test(strpos($maint_code, 'countdown-card') !== false, "maintenance.php contains real-time countdown card");
assert_test(strpos($maint_code, 'contact-card') !== false, "maintenance.php contains contact channels card");
assert_test(strpos($maint_code, 'bypassBox') !== false, "maintenance.php contains staff/tester bypass modal");
assert_test(strpos($maint_code, '/login.php?tab=staff') !== false, "maintenance.php contains staff sign-in link");
assert_test(strpos($maint_code, 'preview-bar') !== false, "maintenance.php supports admin preview mode");

// 6. Checking admin/maintenance.php Super Admin Features
echo "\n=== 6. Checking Super Admin Maintenance Hub (admin/maintenance.php) ===\n";
$admin_maint_code = file_get_contents(dirname(__DIR__) . '/admin/maintenance.php');

assert_test(strpos($admin_maint_code, 'require_admin()') !== false, "admin/maintenance.php enforces require_admin()");
assert_test(strpos($admin_maint_code, 'csrf_check(') !== false, "admin/maintenance.php validates CSRF token");
assert_test(strpos($admin_maint_code, 'toggle_maintenance') !== false, "admin/maintenance.php supports 1-click toggle action");
assert_test(strpos($admin_maint_code, 'save_settings') !== false, "admin/maintenance.php supports full settings save");
assert_test(strpos($admin_maint_code, 'add_my_ip') !== false, "admin/maintenance.php supports 1-click Add My IP");
assert_test(strpos($admin_maint_code, 'generate_bypass_key') !== false, "admin/maintenance.php supports bypass token generation");
assert_test(strpos($admin_maint_code, 'maintenance_mode_title_hi') !== false, "admin/maintenance.php supports Hindi announcement input");
assert_test(strpos($admin_maint_code, 'maintenance_mode_end_time') !== false, "admin/maintenance.php supports estimated end-time picker");
assert_test(strpos($admin_maint_code, 'admin_header.php') !== false, "admin/maintenance.php includes admin header");
assert_test(strpos($admin_maint_code, 'admin_footer.php') !== false, "admin/maintenance.php includes admin footer");

// 7. Checking admin_header.php Integration
echo "\n=== 7. Checking admin_header.php Integration ===\n";
$admin_hdr = file_get_contents(dirname(__DIR__) . '/admin/admin_header.php');

assert_test(strpos($admin_hdr, 'is_maintenance_mode_active') !== false, "admin_header.php checks is_maintenance_mode_active");
assert_test(strpos($admin_hdr, 'Maintenance Mode Active') !== false, "admin_header.php renders sticky warning banner");
assert_test(strpos($admin_hdr, '/admin/maintenance.php') !== false, "admin_header.php links to /admin/maintenance.php");

// 8. Checking admin/site_settings.php Integration
echo "\n=== 8. Checking admin/site_settings.php Integration ===\n";
$site_set = file_get_contents(dirname(__DIR__) . '/admin/site_settings.php');

assert_test(strpos($site_set, 'maintenance_mode_enabled') !== false, "admin/site_settings.php includes maintenance_mode_enabled in allowed keys");
assert_test(strpos($site_set, '/admin/maintenance.php') !== false, "admin/site_settings.php contains link to Maintenance Control Center");

// 9. Checking includes/header.php Integration
echo "\n=== 9. Checking includes/header.php Integration ===\n";
$front_hdr = file_get_contents(dirname(__DIR__) . '/includes/header.php');

assert_test(strpos($front_hdr, 'is_maintenance_mode_active') !== false, "includes/header.php checks is_maintenance_mode_active");
assert_test(strpos($front_hdr, 'MAINTENANCE MODE IS ACTIVE') !== false, "includes/header.php displays floating admin reminder banner");

echo "\n============================================\n";
echo "SUMMARY: Passed: $pass_count, Failed: $fail_count\n";
if ($fail_count === 0) {
    echo "ALL MAINTENANCE MODE TESTS PASSED 100%!\n";
} else {
    echo "SOME TESTS FAILED! PLEASE REVIEW.\n";
    exit(1);
}
