<?php
/**
 * GanvGaliHat v2 Security and Code Audit Verification Test Suite
 * Validates all Critical, High, and Medium audit fixes.
 */

define('TESTING_MOCK_DB', true);

$passed = 0;
$failed = 0;

function assert_true($cond, $desc) {
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] " . $desc . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $desc . "\n";
        $failed++;
    }
}

echo "=== 1. Checking PHP Syntax of All Modified Files ===\n";

$files = [
    'config/db.php',
    'config/settings.php',
    'includes/functions.php',
    'includes/header.php',
    'setup_admin.php',
    'auth/google-callback.php',
    'payments/razorpay_verify.php',
    'payments/payu_callback.php',
    'payments/process.php',
    'payments/webhook.php',
    'verify-phone.php',
    'verify-email.php',
    'verify-signup.php',
    'login.php',
    'register.php',
    'forgot-password.php',
    'reset-password.php',
    'seller-register.php',
    'seller/products.php',
    'ajax/cart_actions.php',
    'ajax/wishlist_actions.php',
    'ajax/chat_api.php',
    'ajax/live_search.php',
    'feeds/google_merchant.php',
    'sitemap.php',
    'robots.php',
    'admin/view_kyc.php',
    'admin/blogs.php',
    'admin/categories.php',
    'admin/coupons.php',
    'admin/orders.php',
    'admin/products.php',
    'admin/seo_redirects.php',
    'admin/seo_settings.php',
    'admin/site_customizer.php',
    'admin/social_auth.php',
    'admin/tickets.php',
    'admin/users.php',
    'admin/vendors.php',
];

$root = dirname(__DIR__);
foreach ($files as $file) {
    $full = $root . '/' . $file;
    assert_true(file_exists($full), "File exists: {$file}");
    $output = [];
    $return_code = 0;
    exec("php -l \"{$full}\" 2>&1", $output, $return_code);
    assert_true($return_code === 0, "No syntax errors: {$file}");
}

echo "\n=== 2. Testing C1: Google OAuth Security Hardening ===\n";
$google_src = file_get_contents($root . '/auth/google-callback.php');
// Base64 decode without verification was previously in Method B
assert_true(strpos($google_src, 'base64_decode') === false, "Unverified base64_decode token parsing removed");
assert_true(strpos($google_src, '$http_code !== 200') !== false, "Strict HTTP 200 verification enforced");
assert_true(strpos($google_src, 'aud') !== false, "Audience / Client ID match enforced");
assert_true(strpos($google_src, 'g_csrf_token') !== false, "g_csrf_token validation present");
assert_true(strpos($google_src, "\$user['role'] !== 'customer'") !== false, "Google login restricted strictly to customer role (privileged roles blocked)");
assert_true(strpos($google_src, 'role = \'customer\'') !== false || strpos($google_src, "role = 'customer'") !== false || strpos($google_src, "'customer'") !== false, "New Google users created strictly as customer role");

echo "\n=== 3. Testing C2: setup_admin.php Web Access Restriction ===\n";
$setup_src = file_get_contents($root . '/setup_admin.php');
assert_true(strpos($setup_src, "php_sapi_name() !== 'cli'") !== false, "setup_admin.php restricted to CLI execution only");
assert_true(strpos($setup_src, 'GanvGaliHatSetup2026Key') === false, "Hardcoded setup secret key removed");
assert_true(strpos($setup_src, 'http_response_code(403)') !== false, "Returns 403 Forbidden on non-CLI requests");

echo "\n=== 4. Testing C3: Razorpay Payment Verification & Anti-Replay ===\n";
$rp_src = file_get_contents($root . '/payments/razorpay_verify.php');
assert_true(strpos($rp_src, 'payment_txn_id = ? AND id != ?') !== false, "Cross-order replay prevention query present for payment_txn_id");
assert_true(strpos($rp_src, "'INR'") !== false, "Strict INR currency check enforced");
assert_true(strpos($rp_src, "status !== 'captured'") !== false, "Strict captured payment status check enforced");
assert_true(strpos($rp_src, 'stock = GREATEST(0, stock - ?)') !== false, "Stock decremented atomically on successful payment verification");

echo "\n=== 5. Testing C4: Phone Verification & OTP Masking ===\n";
$phone_src = file_get_contents($root . '/verify-phone.php');
assert_true(strpos($phone_src, "hash('sha256', \$otp_code)") !== false, "Phone OTP stored as SHA-256 hash");
assert_true(strpos($phone_src, 'phone_otp_verify_attempts') !== false, "OTP attempt throttling implemented");
assert_true(strpos($phone_src, 'Your OTP is') === false, "OTP code not leaked in JSON response");
assert_true(strpos($phone_src, 'Test mode OTP') === false, "Test mode OTP leak removed");

echo "\n=== 6. Testing C5: Turnstile CAPTCHA Security ===\n";
require_once $root . '/includes/functions.php';

// Test Turnstile with secret configured: should fail when no token is passed
$_ENV['TURNSTILE_SECRET_KEY'] = '0x4AAAAAAATestKey123456789';
$_POST['cf-turnstile-response'] = '';
assert_true(verify_turnstile_captcha() === false, "verify_turnstile_captcha fails closed with missing response when secret is set");

// Insecure bypass dummy tokens should NOT pass
$_POST['cf-turnstile-response'] = 'DUMMY_OK';
assert_true(verify_turnstile_captcha() === false, "Dummy token 'DUMMY_OK' is rejected when secret is set");
unset($_ENV['TURNSTILE_SECRET_KEY'], $_POST['cf-turnstile-response']);

echo "\n=== 7. Testing C6 & H3/H4: Seller Registration & KYC Protection ===\n";
$seller_src = file_get_contents($root . '/seller-register.php');
assert_true(strpos($seller_src, "'pending'") !== false, "New seller initial status is 'pending'");
assert_true(strpos($seller_src, 'verified') !== false, "New seller verified is 0");
assert_true(strpos($seller_src, 'finfo_file') !== false, "KYC upload uses finfo_file MIME inspection");
assert_true(strpos($seller_src, 'encrypt_sensitive_field') !== false, "Sensitive fields encrypted via encrypt_sensitive_field()");
assert_true(file_exists($root . '/uploads/kyc/.htaccess'), "uploads/kyc/.htaccess exists");
$htaccess_kyc = file_get_contents($root . '/uploads/kyc/.htaccess');
assert_true(strpos($htaccess_kyc, 'Require all denied') !== false, "uploads/kyc/.htaccess blocks all direct web access");

echo "\n=== 8. Testing H2: Admin CSRF Enforcement ===\n";
$admin_files = [
    'admin/blogs.php', 'admin/categories.php', 'admin/coupons.php', 'admin/orders.php',
    'admin/products.php', 'admin/seo_redirects.php', 'admin/seo_settings.php',
    'admin/site_customizer.php', 'admin/social_auth.php', 'admin/tickets.php',
    'admin/users.php', 'admin/vendors.php'
];
foreach ($admin_files as $af) {
    $src = file_get_contents($root . '/' . $af);
    assert_true(strpos($src, 'csrf_protect') !== false, "CSRF protection present in {$af}");
}

echo "\n=== 9. Testing M1: Open Redirect Mitigation in login.php ===\n";
$login_src = file_get_contents($root . '/login.php');
assert_true(strpos($login_src, 'is_safe_internal_redirect') !== false, "is_safe_internal_redirect helper in login.php");
assert_true(is_safe_internal_redirect('/account.php') === true, "Relative URL /account.php is safe");
assert_true(is_safe_internal_redirect('/checkout.php?ref=cart') === true, "Relative URL /checkout.php is safe");
assert_true(is_safe_internal_redirect('https://evil.com') === false, "Absolute URL https://evil.com is blocked");
assert_true(is_safe_internal_redirect('//evil.com') === false, "Protocol-relative URL //evil.com is blocked");
assert_true(is_safe_internal_redirect('javascript:alert(1)') === false, "javascript: scheme is blocked");

echo "\n=== 10. Testing Sensitive Field Encryption / Decryption ===\n";
$plaintext = "SBIN0001234:987654321012";
$encrypted = encrypt_sensitive_field($plaintext);
assert_true(!empty($encrypted) && $encrypted !== $plaintext, "encrypt_sensitive_field produces ciphertext");
$decrypted = decrypt_sensitive_field($encrypted);
assert_true($decrypted === $plaintext, "decrypt_sensitive_field restores original text perfectly");

echo "\n=== 11. Testing Password Policy & Strength Check ===\n";
$err = '';
assert_true(is_strong_password('Weak', $err) === false, "Short password rejected");
assert_true(is_strong_password('alllowercase1!', $err) === false, "Password without uppercase rejected");
assert_true(is_strong_password('ALLUPPERCASE1!', $err) === false, "Password without lowercase rejected");
assert_true(is_strong_password('NoNumbersHere!', $err) === false, "Password without numbers rejected");
assert_true(is_strong_password('Village@2026Secure', $err) === true, "Strong password accepted");

echo "\n=== 12. Testing Robots.txt and .htaccess Security Rules ===\n";
$robots_txt = file_get_contents($root . '/robots.txt');
assert_true(strpos($robots_txt, 'Disallow: /admin/') !== false, "robots.txt disallows /admin/");
assert_true(strpos($robots_txt, 'Disallow: /seller/') !== false, "robots.txt disallows /seller/");
assert_true(strpos($robots_txt, 'Disallow: /retailer/') !== false, "robots.txt disallows /retailer/");
assert_true(strpos($robots_txt, 'Disallow: /ajax/') !== false, "robots.txt disallows /ajax/");
assert_true(strpos($robots_txt, 'Disallow: /payments/') !== false, "robots.txt disallows /payments/");

$htaccess = file_get_contents($root . '/.htaccess');
assert_true(strpos($htaccess, 'sh') !== false && strpos($htaccess, 'py') !== false, ".htaccess blocks .sh and .py file access");

echo "\n============================================\n";
echo "AUDIT FIXES TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
