<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('TESTING_MOCK_DB', true);
define('SITE_URL', 'https://ganvgalihat.com');

require_once __DIR__ . '/../includes/functions.php';

// Test 1: calc_distance_km
$dist = calc_distance_km(28.6139, 77.2090, 19.0760, 72.8777);
if ($dist < 1000) throw new Exception('Distance failed: ' . $dist);
echo '[PASS] calc_distance_km works (' . $dist . ' km)' . PHP_EOL;

// Test 2: security_event_severity
if (security_event_severity('idor_attempt') !== 'critical') throw new Exception('Severity critical failed');
if (security_event_severity('login_rate_limit_triggered') !== 'high') throw new Exception('Severity high failed');
if (security_event_severity('login_failed_bad_password') !== 'medium') throw new Exception('Severity medium failed');
if (security_event_severity('user_profile_updated') !== 'info') throw new Exception('Severity info failed');
echo '[PASS] security_event_severity works' . PHP_EOL;

// Test 3: is_ip_blocked
if (is_ip_blocked('127.0.0.1') !== false) throw new Exception('IP blocked failed');
echo '[PASS] is_ip_blocked works' . PHP_EOL;

// Test 4: csrf_check alias
$_SESSION['csrf_token'] = 'testtoken1234567890';
if (!csrf_check('testtoken1234567890')) throw new Exception('csrf_check failed');
echo '[PASS] csrf_check alias works' . PHP_EOL;

echo 'ALL MERGED UNIT TESTS PASSED!' . PHP_EOL;
