<?php
/**
 * Test verification for user requested fixes:
 * 1. Login page: No staff/admin options. Only Customer & Retailer and Artisan & Seller.
 * 2. Categories: Default to English, no forced Hindi text.
 * 3. Language Separation: Default strictly English, pure Hindi only when toggled.
 * 4. Super Admin Vendor Management: Super admin can update name, location, contact, commission, KYC.
 * 5. Company Name: Single update in admin updates everywhere via get_company_name() and vendor sync.
 * 6. Unified Registration: register.php has checkbox for Retailer B2B.
 */

define('TESTING_MOCK_DB', true);

$passed = 0;
$failed = 0;

function assert_test($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] $msg\n";
        $passed++;
    } else {
        echo "[FAIL] $msg\n";
        $failed++;
    }
}

echo "=== Testing User Requested Fixes ===\n";

// 1. Verify login.php
$login_content = file_get_contents(dirname(__DIR__) . '/login.php');
assert_test(strpos($login_content, 'tab-btn-staff') === false, "login.php has no tab-btn-staff");
assert_test(strpos($login_content, 'tab=staff') === false, "login.php has no tab=staff link");
assert_test(strpos($login_content, 'Staff & Admin Portal') === false, "login.php has no Staff & Admin portal heading");
assert_test(strpos($login_content, 'Customer & Retailer') !== false, "login.php has Customer & Retailer tab");
assert_test(strpos($login_content, 'Artisan & Seller') !== false, "login.php has Artisan & Seller tab");

// 2. Verify register.php unified registration
$reg_content = file_get_contents(dirname(__DIR__) . '/register.php');
assert_test(strpos($reg_content, 'name="is_retailer"') !== false, "register.php has is_retailer checkbox");
assert_test(strpos($reg_content, 'Register as Wholesale Retailer / Shopkeeper (B2B Bulk Buyer)') !== false, "register.php has Retailer B2B checkbox label");
assert_test(strpos($reg_content, 'id="retailer_fields"') !== false, "register.php has expandable retailer_fields");
assert_test(strpos($reg_content, 'name="business_name"') !== false, "register.php has business_name input");
assert_test(strpos($reg_content, 'name="gstin"') !== false, "register.php has gstin input");

// 3. Verify admin/vendors.php Super Admin editing
$vendors_content = file_get_contents(dirname(__DIR__) . '/admin/vendors.php');
assert_test(strpos($vendors_content, "edit_vendor") !== false, "admin/vendors.php handles edit_vendor action");
assert_test(strpos($vendors_content, "id=\"editVendorModal\"") !== false, "admin/vendors.php has editVendorModal");
assert_test(strpos($vendors_content, "openEditVendorModal") !== false, "admin/vendors.php has openEditVendorModal JS");
assert_test(strpos($vendors_content, "name=\"village\"") !== false, "admin/vendors.php edits village location");
assert_test(strpos($vendors_content, "name=\"district\"") !== false, "admin/vendors.php edits district location");

// 4. Verify functions.php get_company_name() and language functions
require_once dirname(__DIR__) . '/includes/functions.php';
assert_test(function_exists('get_company_name'), "get_company_name() exists");
assert_test(function_exists('get_current_lang'), "get_current_lang() exists");
assert_test(function_exists('t_lang'), "t_lang() exists");
assert_test(get_current_lang() === 'en', "get_current_lang() defaults strictly to 'en'");

// 5. Verify retailer header & footer no mixed bilingual text
$ret_h = file_get_contents(dirname(__DIR__) . '/retailer/retailer_header.php');
assert_test(strpos($ret_h, 'Retailer Hub (थोक बाजार)') === false, "retailer_header.php has no mashed 'Retailer Hub (थोक बाजार)' title");
assert_test(strpos($ret_h, 'render_bilingual_toggle') !== false, "retailer_header.php has bilingual switcher");

// 6. Verify includes/header.php no staff link
$hdr = file_get_contents(dirname(__DIR__) . '/includes/header.php');
assert_test(strpos($hdr, 'login.php?tab=staff') === false, "header.php has no staff login link");
assert_test(strpos($hdr, 'Retailer Hub (थोक खरीद)') === false, "header.php has pure English 'Retailer Hub'");

echo "\n============================================\n";
echo "TARGET FIXES TEST RESULTS: Passed: $passed, Failed: $failed\n";
echo "============================================\n";
exit($failed > 0 ? 1 : 0);
