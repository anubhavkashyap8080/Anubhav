<?php
/**
 * AJAX Live Shipping Fee & Delivery ETA Calculator
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$pincode = trim($_POST['pincode'] ?? $_GET['pincode'] ?? '');
$subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : get_cart_subtotal();
$total_weight = isset($_POST['total_weight']) ? (float)$_POST['total_weight'] : get_cart_total_weight();

if (empty($pincode) || strlen(preg_replace('/\D/', '', $pincode)) !== 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid 6-digit Indian PIN code.'
    ]);
    exit;
}

$shipping_data = calculate_shipping_rate($pincode, $subtotal, $total_weight);

// Cache in session for checkout process
$_SESSION['shipping_pincode'] = $shipping_data['pincode'];
$_SESSION['shipping_fee'] = $shipping_data['shipping_fee'];
$_SESSION['shipping_zone'] = $shipping_data['zone_name'];
$_SESSION['shipping_eta'] = $shipping_data['eta_text'];

echo json_encode([
    'success' => true,
    'data' => [
        'pincode' => $shipping_data['pincode'],
        'shipping_fee' => $shipping_data['shipping_fee'],
        'shipping_fee_formatted' => $shipping_data['is_free'] ? 'FREE' : format_price($shipping_data['shipping_fee']),
        'is_free' => $shipping_data['is_free'],
        'threshold' => $shipping_data['threshold'],
        'remaining_for_free' => $shipping_data['remaining_for_free'],
        'remaining_for_free_formatted' => format_price($shipping_data['remaining_for_free']),
        'zone_name' => $shipping_data['zone_name'],
        'eta_text' => $shipping_data['eta_text'],
        'carrier' => $shipping_data['carrier'],
        'total_weight_kg' => $shipping_data['total_weight_kg']
    ]
]);
