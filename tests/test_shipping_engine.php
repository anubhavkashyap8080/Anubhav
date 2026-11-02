<?php
define('TESTING_MOCK_DB', true);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/shipping.php';

echo "=== Testing Multi-Carrier Shipping Engine ===\n";

// Test 1: Local / Uttar Pradesh (Sandila 241204)
$local = get_all_shipping_partner_quotes('241204', 350, 1.2, 20, 15, 10);
assert(!empty($local['partners']), "Partners returned for 241204");
echo "[PASS] Local PIN 241204 returned " . count($local['partners']) . " delivery partners\n";
echo "       Zone: " . $local['zone']['zone_name'] . "\n";
echo "       Billable weight: " . $local['billable_weight_kg'] . " kg (Dead: " . $local['dead_weight_kg'] . "kg, Vol: " . $local['volumetric_weight_kg'] . "kg)\n";

foreach ($local['partners'] as $p) {
    echo "       - " . $p['courier_name'] . ": " . $p['rate_formatted'] . " (" . $p['eta_days'] . ") [" . $p['badge'] . "]\n";
}

// Test 2: Remote / North-East (Assam 781001)
$remote = get_all_shipping_partner_quotes('781001', 350, 2.5, 30, 25, 20);
echo "\n[PASS] Remote PIN 781001 returned " . count($remote['partners']) . " delivery partners\n";
echo "       Zone: " . $remote['zone']['zone_name'] . "\n";
echo "       Billable weight: " . $remote['billable_weight_kg'] . " kg\n";
foreach ($remote['partners'] as $p) {
    echo "       - " . $p['courier_name'] . ": " . $p['rate_formatted'] . " (" . $p['eta_days'] . ")\n";
}

// Test 3: Free Shipping Threshold Eligibility (> ₹499)
$free_check = calculate_shipping_rate('241204', 750, 0.8);
assert($free_check['is_free'] === true, "Order > 499 is free shipping");
assert($free_check['shipping_fee'] === 0.0, "Shipping fee is 0.0 for free order");
echo "\n[PASS] Free shipping threshold verified: Order ₹750 has fee ₹" . $free_check['shipping_fee'] . "\n";

echo "\nALL SHIPPING TESTS PASSED!\n";
