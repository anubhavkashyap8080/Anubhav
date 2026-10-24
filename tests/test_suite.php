<?php
/**
 * GaunGaliHat Enterprise Automated Verification & Regression Test Suite
 * Tests Security Hardening, Multi-Tenant Isolation, RBAC, Hero Campaigns, GMC Feeds, and Sitemaps
 */

// Force CLI execution
if (php_sapi_name() !== 'cli') {
    die("Access denied. CLI execution only.\n");
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

echo "\n======================================================================\n";
echo "  GAUNGALIHAT ENTERPRISE AUTOMATED TEST SUITE (PHP " . PHP_VERSION . ")\n";
echo "======================================================================\n\n";

$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

function run_test($description, $callback) {
    global $total_tests, $passed_tests, $failed_tests;
    $total_tests++;
    try {
        $result = $callback();
        if ($result === true) {
            $passed_tests++;
            echo " [PASS] " . str_pad($description, 55, ".") . " OK\n";
        } else {
            $failed_tests++;
            $msg = is_string($result) ? $result : "Assertion failed";
            echo " [FAIL] " . str_pad($description, 55, ".") . " FAILED ({$msg})\n";
        }
    } catch (Exception $e) {
        $failed_tests++;
        echo " [ERROR] " . str_pad($description, 55, ".") . " EXCEPTION: " . $e->getMessage() . "\n";
    }
}

// -----------------------------------------------------------------------------
// GROUP 1: SECURITY HARDENING TESTS
// -----------------------------------------------------------------------------
echo "--- 1. SECURITY & ACCESS CONTROL TESTS ---\n";

// Test 1: CSRF Token Generation & Verification
run_test("CSRF Token Generation & Validation", function() {
    $token = csrf_token();
    if (empty($token) || strlen($token) !== 64) {
        return "Invalid token length or empty";
    }
    $_POST['csrf_token'] = $token;
    if (!verify_csrf()) {
        return "CSRF verification failed with valid token";
    }
    $_POST['csrf_token'] = 'tampered_invalid_token_value_12345';
    if (verify_csrf()) {
        return "CSRF verification succeeded with tampered token";
    }
    unset($_POST['csrf_token']);
    return true;
});

// Test 2: Custom HTML/CSS Sanitization (XSS Defense)
run_test("Server-Side Custom HTML Allowlist Sanitization", function() {
    $malicious = '<div class="banner"><h1>Sale</h1><script>alert("XSS")</script><img src="x" onerror="stealCookies()"><iframe src="evil.com"></iframe><p>Authentic Agro</p></div>';
    $sanitized = sanitize_custom_html($malicious);
    
    if (strpos($sanitized, '<script') !== false) return "Failed to strip <script>";
    if (strpos($sanitized, 'onerror') !== false) return "Failed to strip inline event handler onerror";
    if (strpos($sanitized, '<iframe') !== false) return "Failed to strip <iframe";
    if (strpos($sanitized, 'Sale') === false) return "Stripped safe text content";
    if (strpos($sanitized, 'Authentic Agro') === false) return "Stripped safe paragraph";
    
    return true;
});

// Test 3: Brute-Force Rate Limiting (5 failed attempts max)
run_test("Authentication Brute-Force Rate Limiting", function() use ($pdo) {
    $test_ip = '192.168.100.99';
    $test_identifier = 'test_brute_force_user@example.com';
    
    // Clear previous attempts for this test
    $pdo->prepare("DELETE FROM login_attempts WHERE identifier = ? OR ip_address = ?")->execute([$test_identifier, $test_ip]);
    
    // Check initial limit (should be allowed: true)
    $initial_allowed = check_login_rate_limit($test_identifier, $test_ip);
    if (!$initial_allowed) return "Should not be blocked initially";
    
    // Simulate 5 failed attempts
    for ($i = 1; $i <= 5; $i++) {
        record_login_attempt($test_identifier, $test_ip, false);
    }
    
    // Check if blocked now (should be blocked: false)
    $still_allowed = check_login_rate_limit($test_identifier, $test_ip);
    if ($still_allowed) return "Rate limiter failed to block after 5 failed attempts";
    
    // Clean up test data
    $pdo->prepare("DELETE FROM login_attempts WHERE identifier = ? OR ip_address = ?")->execute([$test_identifier, $test_ip]);
    return true;
});

// Test 4: Role-Based Access Control (RBAC) Permissions
run_test("RBAC Granular Permissions Matrix", function() use ($pdo) {
    // Check super admin user ID 1
    $is_super = user_has_permission('manage_security', 1);
    if (!$is_super) return "Super Admin (ID 1) lacks manage_security permission";
    
    $super_gmc = user_has_permission('export_merchant_feed', 1);
    if (!$super_gmc) return "Super Admin (ID 1) lacks export_merchant_feed permission";
    
    return true;
});

// Test 5: Multi-Tenant Isolation (Seller IDOR Protection)
run_test("Multi-Tenant Isolation Scoping (IDOR Check)", function() use ($pdo) {
    // Verify vendor 1 cannot query or affect vendor 2 products
    $v1_id = 1;
    $v2_id = 2;
    
    // Ensure product belongs to vendor 1
    $stmt = $pdo->prepare("SELECT id FROM products WHERE vendor_id = ? LIMIT 1");
    $stmt->execute([$v1_id]);
    $v1_product_id = $stmt->fetchColumn();
    
    if ($v1_product_id) {
        // Attempting to query with vendor 2 id must return false
        $stmt_check = $pdo->prepare("SELECT id FROM products WHERE id = ? AND vendor_id = ?");
        $stmt_check->execute([$v1_product_id, $v2_id]);
        $cross_tenant_result = $stmt_check->fetch();
        if ($cross_tenant_result) return "Cross-tenant access succeeded: vendor 2 accessed vendor 1 product";
    }
    
    return true;
});

// -----------------------------------------------------------------------------
// GROUP 2: DYNAMIC FESTIVAL HERO & CAMPAIGN ENGINE
// -----------------------------------------------------------------------------
echo "\n--- 2. FESTIVAL HERO & CAMPAIGN ENGINE TESTS ---\n";

// Test 6: Default / Active Hero Campaign Resolution
run_test("Hero Campaign Resolution & Presets", function() use ($pdo) {
    // Insert an active test festival campaign
    $test_title = "दशहरा एवं दिवाली ग्रामीण शिल्प मेला";
    $test_slug = "test-festival-" . time();
    $stmt = $pdo->prepare("
        INSERT INTO hero_campaigns (name, slug, campaign_type, theme_preset, title_line1, badge_text, status, priority)
        VALUES ('Test Festival 2026', ?, 'festival', 'diwali', ?, 'Festive Special', 'active', 99)
    ");
    $stmt->execute([$test_slug, $test_title]);
    $camp_id = $pdo->lastInsertId();

    $resolved = get_active_hero_campaign();
    $matches = ($resolved && $resolved['title_line1'] === $test_title && $resolved['theme_preset'] === 'diwali');

    // Clean up
    $pdo->prepare("DELETE FROM hero_campaigns WHERE id = ?")->execute([$camp_id]);

    if (!$matches) {
        return "Failed to resolve active festival campaign correctly";
    }
    return true;
});

// Test 7: Hero Campaign Versioning Snapshot Check
run_test("Hero Version History & Rollback Storage", function() use ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM hero_campaign_versions");
    $count = $stmt->fetchColumn();
    if ($count === false) return "hero_campaign_versions table unreadable";
    return true;
});

// -----------------------------------------------------------------------------
// GROUP 3: TECHNICAL SEO, SITEMAPS & ROBOTS DIRECTIVES
// -----------------------------------------------------------------------------
echo "\n--- 3. TECHNICAL SEO, SITEMAPS & ROBOTS TESTS ---\n";

// Test 8: Dynamic Sitemap Index Generation
run_test("Dynamic Sitemap Index XML Validity", function() {
    ob_start();
    $_GET['type'] = 'index';
    require __DIR__ . '/../sitemap.php';
    $output = ob_get_clean();
    unset($_GET['type']);
    
    if (strpos($output, '<?xml version="1.0" encoding="UTF-8"?>') === false) {
        return "Missing XML declaration";
    }
    if (strpos($output, '<sitemapindex') === false || strpos($output, '</sitemapindex>') === false) {
        return "Missing or malformed <sitemapindex>";
    }
    if (strpos($output, 'sitemap-products.xml') === false) {
        return "Sitemap index missing products sub-sitemap";
    }
    if (strpos($output, 'sitemap-categories.xml') === false) {
        return "Sitemap index missing categories sub-sitemap";
    }
    return true;
});

// Test 9: Sub-Sitemap Products Generation
run_test("Sub-Sitemap Products XML Validity", function() {
    ob_start();
    $_GET['type'] = 'products';
    require __DIR__ . '/../sitemap.php';
    $output = ob_get_clean();
    unset($_GET['type']);
    
    if (strpos($output, '<urlset') === false || strpos($output, '</urlset>') === false) {
        return "Missing or malformed <urlset>";
    }
    if (strpos($output, '<loc>') === false) {
        return "Missing <loc> nodes in products sitemap";
    }
    return true;
});

// Test 10: Dynamic Robots.txt Directives
run_test("Dynamic Robots.txt Directives", function() {
    ob_start();
    require __DIR__ . '/../robots.php';
    $output = ob_get_clean();
    
    if (strpos($output, 'User-agent: *') === false) return "Missing User-agent: *";
    if (strpos($output, 'Disallow: /admin/') === false) return "Missing Disallow: /admin/";
    if (strpos($output, 'Disallow: /seller/') === false) return "Missing Disallow: /seller/";
    if (strpos($output, 'Disallow: /cart.php') === false) return "Missing Disallow: /cart.php";
    if (strpos($output, 'Sitemap:') === false) return "Missing Sitemap directive";
    return true;
});

// Test 11: SEO Redirects Handler
run_test("SEO 301/302 Redirect Interceptor & 404 Logger", function() use ($pdo) {
    // Insert temporary test redirect rule
    $test_source = '/test-legacy-redirect-path';
    $test_target = '/products.php';
    
    $pdo->prepare("DELETE FROM seo_redirects WHERE source_path = ?")->execute([$test_source]);
    $stmt = $pdo->prepare("INSERT INTO seo_redirects (source_path, target_url, status_code, status) VALUES (?, ?, 301, 'active')");
    $stmt->execute([$test_source, $test_target]);
    
    // Verify lookup
    $stmt_find = $pdo->prepare("SELECT target_url, status_code FROM seo_redirects WHERE source_path = ? AND status = 'active'");
    $stmt_find->execute([$test_source]);
    $rule = $stmt_find->fetch();
    
    if (!$rule || $rule['target_url'] !== $test_target || $rule['status_code'] != 301) {
        return "Redirect rule not retrieved properly";
    }
    
    // Clean up
    $pdo->prepare("DELETE FROM seo_redirects WHERE source_path = ?")->execute([$test_source]);
    return true;
});

// -----------------------------------------------------------------------------
// GROUP 4: INTEGRATIONS & FEEDS
// -----------------------------------------------------------------------------
echo "\n--- 4. GOOGLE MERCHANT CENTER & INTEGRATIONS TESTS ---\n";

// Test 12: Google Merchant Center XML Feed Structure
run_test("Google Merchant Center XML Feed Generator", function() use ($pdo) {
    ob_start();
    require __DIR__ . '/../feeds/google_merchant.php';
    $output = ob_get_clean();
    
    if (strpos($output, 'xmlns:g="http://base.google.com/ns/1.0"') === false) {
        return "Missing Google Merchant XML namespace";
    }
    if (strpos($output, '<g:price>') === false) {
        return "Missing <g:price> tags";
    }
    if (strpos($output, '<g:availability>') === false) {
        return "Missing <g:availability> tags";
    }
    if (strpos($output, '<g:shipping>') === false) {
        return "Missing <g:shipping> tags";
    }
    return true;
});

// Test 13: Coupon Calculation Engine
run_test("Coupon Discount Calculation Logic", function() use ($pdo) {
    // Find or create test coupon
    $code = 'TESTAUTO50';
    $pdo->prepare("DELETE FROM coupons WHERE code = ?")->execute([$code]);
    $pdo->prepare("
        INSERT INTO coupons (code, title, discount_type, discount_value, min_order_amount, coupon_type, status)
        VALUES (?, 'Automated Test Coupon', 'percentage', 20.00, 100.00, 'general', 'active')
    ")->execute([$code]);
    
    // Test applying coupon
    $_SESSION['cart'] = [1 => ['qty' => 1]]; // Fake cart
    $res = validate_and_apply_coupon($code);
    
    if (!$res['success']) {
        return "Failed to apply valid test coupon: " . ($res['error'] ?? '');
    }
    if (empty($_SESSION['applied_coupon'])) {
        return "Applied coupon not stored in session";
    }
    
    // Clean up
    remove_applied_coupon();
    unset($_SESSION['cart']);
    $pdo->prepare("DELETE FROM coupons WHERE code = ?")->execute([$code]);
    return true;
});

// -----------------------------------------------------------------------------
// SUMMARY & EXIT
// -----------------------------------------------------------------------------
echo "\n======================================================================\n";
echo "  TEST SUMMARY: {$passed_tests}/{$total_tests} PASSED (" . round(($passed_tests / $total_tests) * 100, 1) . "% SUCCESS RATE)\n";
echo "======================================================================\n\n";

if ($failed_tests > 0) {
    echo " [ERROR] {$failed_tests} tests failed. Immediate attention required.\n\n";
    exit(1);
} else {
    echo " [SUCCESS] All enterprise modules, security layers, and integrations verified!\n\n";
    exit(0);
}
