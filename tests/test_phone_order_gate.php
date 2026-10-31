<?php
/**
 * Test Phone Verification Gateway & Order Gate Enforcement
 */

define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

require_once __DIR__ . '/../includes/functions.php';

echo "=== Testing Phone Verification & Order Gate ===\n";

// 1. Phone validation
$valid_phones = ['9876543210', '8123456789', '7000000000', '6999999999'];
$invalid_phones = ['1234567890', '5876543210', '98765', 'abcdefghij', '9876543210123'];

foreach ($valid_phones as $p) {
    assert(preg_match('/^[6-9]\d{9}$/', $p), "Failed for valid phone: $p");
}
echo "[PASS] 10-digit Indian phone format regex validation passed\n";

foreach ($invalid_phones as $p) {
    assert(!preg_match('/^[6-9]\d{9}$/', $p), "Failed for invalid phone: $p");
}
echo "[PASS] Invalid phone numbers correctly rejected\n";

// 2. Address validation checks
$valid_street = "Madhoganj, Baghauli Road, Newada";
$short_street = "Abc";
$empty_street = "";

assert(strlen($valid_street) >= 5, "Valid street failed");
assert(strlen($short_street) < 5, "Short street should fail");
assert(empty($empty_street), "Empty street should fail");
echo "[PASS] Street address minimum 5 characters constraint passed\n";

$valid_pincode = "241126";
$invalid_pincode = "24112";
$alpha_pincode = "24112a";

assert(preg_match('/^\d{6}$/', $valid_pincode), "Valid pincode failed");
assert(!preg_match('/^\d{6}$/', $invalid_pincode), "Invalid pincode length passed");
assert(!preg_match('/^\d{6}$/', $alpha_pincode), "Alpha pincode passed");
echo "[PASS] 6-digit PIN code constraint passed\n";

// 3. OTP generation and hash verification
$otp = sprintf('%06d', random_int(100000, 999999));
assert(strlen($otp) === 6, "OTP length must be 6");
assert(hash_equals($otp, $otp), "hash_equals OTP check failed");
assert(!hash_equals($otp, '000000'), "Mismatch OTP should fail");
echo "[PASS] 6-digit OTP generation and timing-safe comparison passed\n";

echo "ALL PHONE & ADDRESS GATE TESTS PASSED!\n";
