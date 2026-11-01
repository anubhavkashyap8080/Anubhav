<?php
/**
 * GanvGaliHat v2 Feature Verification Suite
 */

define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

echo "=== GanvGaliHat v2 Feature Verification Suite ===\n";

// Test 1: 5 Layouts
$layouts_dir = __DIR__ . '/../includes/home_layouts';
$layouts = ['layout_1.php', 'layout_2.php', 'layout_3.php', 'layout_4.php', 'layout_5.php'];

foreach ($layouts as $l) {
    $fpath = $layouts_dir . '/' . $l;
    if (!file_exists($fpath)) {
        echo "[FAIL] Layout file missing: $l\n";
        exit(1);
    }
    // Check syntax
    exec("php -l " . escapeshellarg($fpath), $out, $ret);
    if ($ret !== 0) {
        echo "[FAIL] Syntax error in $l\n";
        exit(1);
    }
    echo "[PASS] $l exists and syntax is clean\n";
}

// Test 2: YouTube Embed Parser
require_once __DIR__ . '/../includes/functions.php';

$test_urls = [
    'https://www.youtube.com/watch?v=k4m3y1eH5Ww' => 'https://www.youtube-nocookie.com/embed/k4m3y1eH5Ww',
    'https://youtu.be/k4m3y1eH5Ww' => 'https://www.youtube-nocookie.com/embed/k4m3y1eH5Ww',
    'https://www.youtube.com/embed/k4m3y1eH5Ww' => 'https://www.youtube-nocookie.com/embed/k4m3y1eH5Ww',
    'k4m3y1eH5Ww' => 'https://www.youtube-nocookie.com/embed/k4m3y1eH5Ww'
];

foreach ($test_urls as $input => $expected_prefix) {
    $res = get_youtube_embed_url($input, false);
    if (strpos($res, $expected_prefix) === 0) {
        echo "[PASS] YouTube parser handled '$input'\n";
    } else {
        echo "[FAIL] YouTube parser failed for '$input' -> got '$res'\n";
        exit(1);
    }
}

// Test 3: Seller Register 5-Step File Check
$seller_reg = file_get_contents(__DIR__ . '/../seller-register.php');
$required_tokens = [
    'stepPanel1', 'stepPanel2', 'stepPanel3', 'stepPanel4', 'stepPanel5',
    'owner_name', 'shop_name', 'vendor_type',
    'address', 'village', 'district', 'state', 'pincode',
    'pan_number', 'aadhaar_number', 'kyc_doc_file',
    'bank_beneficiary', 'bank_name', 'bank_account', 'bank_ifsc', 'upi_id',
    'agree_terms', 'agree_authenticity', 'agree_privacy'
];

foreach ($required_tokens as $t) {
    if (strpos($seller_reg, $t) !== false) {
        echo "[PASS] Seller registration contains '$t'\n";
    } else {
        echo "[FAIL] Seller registration missing '$t'\n";
        exit(1);
    }
}

// Test 4: Brand name check in index.php & settings
$idx = file_get_contents(__DIR__ . '/../index.php');
if (strpos($idx, 'GaunGaliHat') !== false) {
    echo "[FAIL] index.php still contains GaunGaliHat\n";
    exit(1);
}
echo "[PASS] index.php has zero GaunGaliHat occurrences\n";

echo "==========================================\n";
echo "ALL V2 FEATURES VERIFIED SUCCESSFULLY!\n";
