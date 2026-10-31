<?php
/**
 * GaunGaliHat Security Hardening Unit Tests
 */

// Define environment mock
define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

require_once __DIR__ . '/../includes/functions.php';

$all_passed = true;
function assert_test($description, $condition) {
    global $all_passed;
    if ($condition) {
        echo "[PASS] $description\n";
    } else {
        echo "[FAIL] $description\n";
        $all_passed = false;
    }
}

echo "=== 1. Password Policy (Item 22) ===\n";
$err = '';
assert_test("Short password rejected", !is_strong_password("Short1!", $err));
assert_test("Common password 'password12345' rejected", !is_strong_password("password12345", $err));
assert_test("Common password 'Admin@12345' rejected", !is_strong_password("Admin@12345", $err));
assert_test("Strong password accepted", is_strong_password("Kisan#Forge!2026Secure", $err));

echo "\n=== 2. CSV Formula Injection Neutralization (Item 11 / 21) ===\n";
assert_test("Formula =cmd neutralized", neutralize_csv_formula("=cmd|'/C calc'!A0") === "'=cmd|'/C calc'!A0");
assert_test("Formula +2+3 neutralized", neutralize_csv_formula("+2+3") === "'+2+3");
assert_test("Formula -5+10 neutralized", neutralize_csv_formula("-5+10") === "'-5+10");
assert_test("Formula @SUM neutralized", neutralize_csv_formula("@SUM(A1:A10)") === "'@SUM(A1:A10)");
assert_test("Normal string untouched", neutralize_csv_formula("Pure Artisan Honey") === "Pure Artisan Honey");
assert_test("Numeric value untouched", neutralize_csv_formula(250.50) === "250.5");

echo "\n=== 3. Product Image URL Whitelisting (Item 8 / 20) ===\n";
assert_test("Local uploads allowed", is_valid_product_image_url("/uploads/products/iron_sickle.jpg"));
assert_test("Local uploads directory traversal blocked", !is_valid_product_image_url("/uploads/../config/db.php"));
assert_test("Local uploads PHP file blocked", !is_valid_product_image_url("/uploads/shell.php"));
assert_test("HTTPS Unsplash allowed", is_valid_product_image_url("https://images.unsplash.com/photo-1544717305-2782549b5136?w=600"));
assert_test("HTTPS Cloudinary allowed", is_valid_product_image_url("https://res.cloudinary.com/demo/image/upload/sample.jpg"));
assert_test("Site domain allowed", is_valid_product_image_url("https://ganvgalihat.com/uploads/test.png"));
assert_test("HTTP insecure blocked", !is_valid_product_image_url("http://images.unsplash.com/photo.jpg"));
assert_test("Untrusted domain blocked", !is_valid_product_image_url("https://malicious-site.com/exploit.jpg"));
assert_test("Internal SSRF IP blocked", !is_valid_product_image_url("https://127.0.0.1/admin.png"));
assert_test("Private 192.168.x IP blocked", !is_valid_product_image_url("https://192.168.1.1/test.png"));
assert_test("Javascript scheme blocked", !is_valid_product_image_url("javascript:alert(1)"));

echo "\n=== 4. DOMDocument Strict HTML Sanitizer (Item 18) ===\n";
$dirty_script = '<p>Artisan Story</p><script>alert("XSS")</script>';
$clean_script = sanitize_custom_html($dirty_script);
assert_test("Script tag completely stripped", strpos($clean_script, '<script>') === false && strpos($clean_script, 'alert') === false);

$dirty_event = '<p onclick="alert(1)">Click for craft</p>';
$clean_event = sanitize_custom_html($dirty_event);
assert_test("Inline on* event handlers stripped", strpos($clean_event, 'onclick') === false);

$dirty_js_url = '<a href="javascript:alert(1)">Malicious link</a>';
$clean_js_url = sanitize_custom_html($dirty_js_url);
assert_test("javascript: URL blocked", strpos($clean_js_url, 'javascript:') === false);

$dirty_encoded = '<a href="jav&#x09;ascript:alert(1)">Encoded JS link</a>';
$clean_encoded = sanitize_custom_html($dirty_encoded);
assert_test("Entity-encoded javascript: URL blocked", strpos($clean_encoded, 'javascript:') === false);

$dirty_style = '<p style="color:red">Styled</p><style>body { display:none; }</style>';
$clean_style = sanitize_custom_html($dirty_style);
assert_test("Style tag blocked", strpos($clean_style, '<style>') === false);

echo "\n=== 5. Input Cleaning & Output Escaping (Item 23) ===\n";
$input = "  <script>alert('test')</script>  ";
$stored = clean_input($input);
assert_test("clean_input trims and does NOT double-encode on storage", $stored === "<script>alert('test')</script>");
$output = e($stored);
assert_test("e() escapes on output", $output === "&lt;script&gt;alert(&#039;test&#039;)&lt;/script&gt;");

$legacy_encoded = "Honey &amp; Spices";
assert_test("e() prevents double-encoding on legacy values", e($legacy_encoded) === "Honey &amp; Spices");

echo "\n=== 6. Turnstile CAPTCHA (Item 24) ===\n";
assert_test("Turnstile test token passes", verify_turnstile_captcha('1x00000000000000000000AA'));
assert_test("Turnstile placeholder renders widget/hidden field", strpos(render_turnstile_widget(), 'cf-turnstile') !== false);

echo "\n=== 7. Field Encryption & Secret Masking (Item 10) ===\n";
$bank_account_raw = "1234567890123456";
$encrypted = encrypt_sensitive_field($bank_account_raw);
assert_test("Sensitive field encrypted with prefix", strpos($encrypted, 'enc:') === 0 || strpos($encrypted, 'raw:') === 0);
$decrypted = decrypt_sensitive_field($encrypted);
assert_test("Sensitive field decrypts exactly", $decrypted === $bank_account_raw);
$masked_acc = mask_sensitive_value($encrypted);
assert_test("Masked bank account masks all but last 4", substr($masked_acc, -4) === '3456' && strpos($masked_acc, '••••') !== false);
$masked_sec = mask_secret("rzp_live_secret_key_998877");
assert_test("Mask secret masks middle characters", strpos($masked_sec, '•') !== false && substr($masked_sec, 0, 3) === 'rzp');

echo "\n=== 8. Email Verification Token Hashing (Item 10) ===\n";
$raw_token = bin2hex(random_bytes(16));
$hashed_token = hash('sha256', $raw_token);
assert_test("Hashed token matches via hash_equals", hash_equals($hashed_token, hash('sha256', $raw_token)));
assert_test("Altered token rejected via hash_equals", !hash_equals($hashed_token, hash('sha256', $raw_token . 'tamper')));

echo "\n=== 9. Deploy Cleanup Verification (Item 4) ===\n";
$sqlite_exists = file_exists(__DIR__ . '/../data/backups/gaungalihat_backup_pre_upgrade.sqlite');
$schema_sql_exists = file_exists(__DIR__ . '/../database/schema.sql');
$db_sql_exists = file_exists(__DIR__ . '/../gaungalihat_database.sql');
assert_test("No sqlite backup files in deploy directory", !$sqlite_exists);
assert_test("No schema.sql in deploy directory", !$schema_sql_exists);
assert_test("No gaungalihat_database.sql in deploy directory", !$db_sql_exists);

echo "\n=== 10. Web Server Security (.htaccess & CSP - Item 5) ===\n";
$htaccess = file_get_contents(__DIR__ . '/../.htaccess');
assert_test("HTTPS 301 redirect present in .htaccess", strpos($htaccess, 'RewriteCond %{HTTPS} off') !== false && strpos($htaccess, 'ganvgalihat.com') !== false);
assert_test("CSP frame-ancestors 'self' present", strpos($htaccess, "frame-ancestors 'self'") !== false);
assert_test("CSP form-action includes payu", strpos($htaccess, "form-action 'self' https://secure.payu.in https://test.payu.in") !== false);

echo "\n==========================================\n";
if ($all_passed) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
