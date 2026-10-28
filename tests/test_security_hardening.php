<?php
/**
 * GaunGaliHat Security Hardening Unit Tests
 */

// Define environment mock
define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://gaungalihat.womenvission.in');

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

echo "\n=== 2. CSV Formula Injection Neutralization (Item 21) ===\n";
assert_test("Formula =cmd neutralized", escape_csv_cell("=cmd|'/C calc'!A0") === "'=cmd|'/C calc'!A0");
assert_test("Formula +2+3 neutralized", escape_csv_cell("+2+3") === "'+2+3");
assert_test("Formula -5+10 neutralized", escape_csv_cell("-5+10") === "'-5+10");
assert_test("Formula @SUM neutralized", escape_csv_cell("@SUM(A1:A10)") === "'@SUM(A1:A10)");
assert_test("Normal string untouched", escape_csv_cell("Pure Artisan Honey") === "Pure Artisan Honey");
assert_test("Numeric value untouched", escape_csv_cell(250.50) === "250.5");

echo "\n=== 3. Product Image URL Whitelisting (Item 20) ===\n";
assert_test("Local uploads allowed", is_valid_product_image_url("/uploads/products/iron_sickle.jpg"));
assert_test("Local uploads directory traversal blocked", !is_valid_product_image_url("/uploads/../config/db.php"));
assert_test("Local uploads PHP file blocked", !is_valid_product_image_url("/uploads/shell.php"));
assert_test("HTTPS Unsplash allowed", is_valid_product_image_url("https://images.unsplash.com/photo-1544717305-2782549b5136?w=600"));
assert_test("HTTPS Cloudinary allowed", is_valid_product_image_url("https://res.cloudinary.com/demo/image/upload/sample.jpg"));
assert_test("Site domain allowed", is_valid_product_image_url("https://gaungalihat.womenvission.in/uploads/test.png"));
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

echo "\n==========================================\n";
if ($all_passed) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
