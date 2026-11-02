<?php
/**
 * Test Suite: Multi-Carrier Delivery Partners, Shipping Estimator, Dimensions & Mobile Stepper
 */

define('TESTING_MOCK_DB', true);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/shipping.php';

$passed = 0;
$failed = 0;

function assert_test($condition, $name) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

echo "=== 1. Checking PHP Syntax of All Modified Files ===\n";
$files = [
    'config/db.php',
    'config/shipping.php',
    'ajax/calculate_shipping.php',
    'admin/integrations.php',
    'seller/products.php',
    'admin/products.php',
    'product.php',
    'seller-register.php'
];

foreach ($files as $f) {
    exec('php -l ' . escapeshellarg(__DIR__ . '/../' . $f), $out, $ret);
    assert_test($ret === 0, "Syntax clean for $f");
}

echo "\n=== 2. Testing Multi-Carrier Shipping Engine & Volumetric Weight ===\n";
// Test Dead Weight > Volumetric
$q1 = get_all_shipping_partner_quotes('241204', 300, 2.0, 10, 10, 10);
assert_test(count($q1['partners']) >= 3, "Quotes returned >= 3 delivery partners");
assert_test($q1['billable_weight_kg'] == 2.0, "Billable weight equals dead weight (2.0 kg)");
assert_test($q1['volumetric_weight_kg'] == 0.2, "Volumetric weight is 0.2 kg for 10x10x10");

// Test Volumetric Weight > Dead Weight (Large light box: 50x40x30 = 60,000 / 5000 = 12.0 kg, dead 1.0 kg)
$q2 = get_all_shipping_partner_quotes('110001', 300, 1.0, 50, 40, 30);
assert_test($q2['volumetric_weight_kg'] == 12.0, "Volumetric weight is 12.0 kg for 50x40x30");
assert_test($q2['billable_weight_kg'] == 12.0, "Billable weight equals volumetric weight (12.0 kg)");

// Check India Post Partner
$has_indiapost = false;
$has_shiprocket_delhivery = false;
$has_shiprocket_bluedart = false;
foreach ($q1['partners'] as $p) {
    if ($p['partner_id'] === 'indiapost') $has_indiapost = true;
    if ($p['partner_id'] === 'shiprocket_delhivery') $has_shiprocket_delhivery = true;
    if ($p['partner_id'] === 'shiprocket_bluedart') $has_shiprocket_bluedart = true;
}
assert_test($has_indiapost, "India Post Speed Post partner present");
assert_test($has_shiprocket_delhivery, "Shiprocket Delhivery Surface partner present");
assert_test($has_shiprocket_bluedart, "Shiprocket Blue Dart Air partner present");

// Test Backward Compatibility of calculate_shipping_rate
$legacy_res = calculate_shipping_rate('241204', 300, 0.5);
assert_test(isset($legacy_res['shipping_fee']), "Legacy key 'shipping_fee' exists");
assert_test(isset($legacy_res['carrier']), "Legacy key 'carrier' exists");
assert_test(isset($legacy_res['delivery_partners']), "New key 'delivery_partners' included");

echo "\n=== 3. Testing Seller Products Dimensions & Volumetric Support ===\n";
$seller_prod = file_get_contents(__DIR__ . '/../seller/products.php');
assert_test(strpos($seller_prod, 'length_cm') !== false, "seller/products.php contains length_cm");
assert_test(strpos($seller_prod, 'width_cm') !== false, "seller/products.php contains width_cm");
assert_test(strpos($seller_prod, 'height_cm') !== false, "seller/products.php contains height_cm");
assert_test(strpos($seller_prod, 'volumetric_weight') !== false, "seller/products.php contains volumetric_weight");
assert_test(strpos($seller_prod, 'updateAddVolumetric') !== false, "seller/products.php contains updateAddVolumetric()");
assert_test(strpos($seller_prod, 'updateEditVolumetric') !== false, "seller/products.php contains updateEditVolumetric()");

echo "\n=== 4. Testing Admin Products Dimensions Support ===\n";
$admin_prod = file_get_contents(__DIR__ . '/../admin/products.php');
assert_test(strpos($admin_prod, 'length_cm') !== false, "admin/products.php contains length_cm");
assert_test(strpos($admin_prod, 'width_cm') !== false, "admin/products.php contains width_cm");
assert_test(strpos($admin_prod, 'height_cm') !== false, "admin/products.php contains height_cm");
assert_test(strpos($admin_prod, 'updateAdminVolumetric') !== false, "admin/products.php contains updateAdminVolumetric()");

echo "\n=== 5. Testing Admin Integrations Hub Multi-Carrier Settings ===\n";
$admin_int = file_get_contents(__DIR__ . '/../admin/integrations.php');
assert_test(strpos($admin_int, 'indiapost_enabled') !== false, "admin/integrations.php includes indiapost_enabled");
assert_test(strpos($admin_int, 'shiprocket_enabled') !== false, "admin/integrations.php includes shiprocket_enabled");
assert_test(strpos($admin_int, 'shiprocket_email') !== false, "admin/integrations.php includes shiprocket_email");
assert_test(strpos($admin_int, 'delhivery_enabled') !== false, "admin/integrations.php includes delhivery_enabled");
assert_test(strpos($admin_int, 'free_shipping_threshold') !== false, "admin/integrations.php includes free_shipping_threshold");

echo "\n=== 6. Testing Seller Register Horizontal Mobile Stepper ===\n";
$seller_reg = file_get_contents(__DIR__ . '/../seller-register.php');
assert_test(strpos($seller_reg, 'stepper-horizontal-container') !== false, "seller-register.php includes stepper-horizontal-container");
assert_test(strpos($seller_reg, 'stepper-horizontal-item') !== false, "seller-register.php includes stepper-horizontal-item");
assert_test(strpos($seller_reg, 'stepper-connector-line') !== false, "seller-register.php includes stepper-connector-line");
assert_test(strpos($seller_reg, 'flex-direction: row !important;') !== false, "Horizontal flex-row strictly enforced with !important");

echo "\n=== 7. Testing Product Detail Page Delivery Estimator Widget ===\n";
$prod_page = file_get_contents(__DIR__ . '/../product.php');
assert_test(strpos($prod_page, 'checkDetailPincode') !== false, "product.php contains checkDetailPincode()");
assert_test(strpos($prod_page, 'All-India Delivery Partners') !== false, "product.php contains All-India Delivery Partners header");
assert_test(strpos($prod_page, 'pincode-detail-result') !== false, "product.php contains pincode-detail-result");

echo "\n============================================\n";
echo "SUMMARY: Passed: $passed, Failed: $failed\n";
if ($failed === 0) {
    echo "ALL MULTI-CARRIER, DIMENSIONS & STEPPER TESTS PASSED 100%!\n";
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
