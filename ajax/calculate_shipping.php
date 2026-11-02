<?php
/**
 * AJAX Live Shipping Fee & Multi-Carrier Delivery Estimator
 * Real-time Indian PIN Code Serviceability & Volumetric Weight API
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/functions.php';

$pincode = trim($_POST['pincode'] ?? $_GET['pincode'] ?? '');
$clean_pin = preg_replace('/\D/', '', $pincode);

if (empty($clean_pin) || strlen($clean_pin) !== 6) {
    echo json_encode([
        'success' => false,
        'message' => 'कृपया 6 अंकों का सही भारतीय पिनकोड दर्ज करें (Please enter a valid 6-digit Indian PIN code).'
    ]);
    exit;
}

$product_id = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$length_cm = floatval($_POST['length_cm'] ?? $_GET['length_cm'] ?? 10.0);
$width_cm = floatval($_POST['width_cm'] ?? $_GET['width_cm'] ?? 10.0);
$height_cm = floatval($_POST['height_cm'] ?? $_GET['height_cm'] ?? 10.0);
$total_weight = floatval($_POST['total_weight'] ?? $_GET['total_weight'] ?? 0.5);
$subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0.0;
$cod = (!empty($_POST['cod']) && $_POST['cod'] !== '0');

// If product_id is given and DB available, load product's exact weight and dimensions
if ($product_id > 0 && $pdo) {
    try {
        $st_p = $pdo->prepare("SELECT price, weight_kg, length_cm, width_cm, height_cm FROM products WHERE id = ? LIMIT 1");
        $st_p->execute([$product_id]);
        $prod = $st_p->fetch();
        if ($prod) {
            if ($subtotal <= 0) $subtotal = (float)$prod['price'];
            if (!empty($prod['weight_kg'])) $total_weight = (float)$prod['weight_kg'];
            if (!empty($prod['length_cm'])) $length_cm = (float)$prod['length_cm'];
            if (!empty($prod['width_cm'])) $width_cm = (float)$prod['width_cm'];
            if (!empty($prod['height_cm'])) $height_cm = (float)$prod['height_cm'];
        }
    } catch (Throwable $e_p) {}
}

if ($subtotal <= 0) {
    $subtotal = get_cart_subtotal();
}
if ($total_weight <= 0) {
    $total_weight = get_cart_total_weight();
}

$shipping_data = calculate_shipping_rate($clean_pin, $subtotal, $total_weight, $length_cm, $width_cm, $height_cm);

// Handle specific courier selection
$selected_partner_id = trim($_POST['select_partner_id'] ?? '');
if (!empty($selected_partner_id) && !empty($shipping_data['delivery_partners'])) {
    foreach ($shipping_data['delivery_partners'] as $p) {
        if ($p['partner_id'] === $selected_partner_id) {
            $shipping_data['shipping_fee'] = $p['rate'];
            $shipping_data['carrier'] = $p['courier_name'];
            $shipping_data['eta_text'] = $p['eta_days'];
            $shipping_data['badge'] = $p['badge'];
            $shipping_data['selected_partner'] = $p;
            break;
        }
    }
}

// Cache in session for checkout continuity
$_SESSION['shipping_pincode'] = $shipping_data['pincode'];
$_SESSION['delivery_pincode'] = $shipping_data['pincode'];
$_SESSION['shipping_fee'] = $shipping_data['shipping_fee'];
$_SESSION['shipping_zone'] = $shipping_data['zone_name'];
$_SESSION['shipping_eta'] = $shipping_data['eta_text'];
$_SESSION['shipping_carrier'] = $shipping_data['carrier'];

$applied_coupon = function_exists('get_applied_coupon') ? get_applied_coupon() : null;
$discount_val = $applied_coupon ? floatval($applied_coupon['discount_amount'] ?? 0) : 0.0;
$grand_total = max(0, $subtotal + $shipping_data['shipping_fee'] - $discount_val);

echo json_encode([
    'success' => true,
    'status' => 'success',
    'shipping_fee' => $shipping_data['shipping_fee'],
    'subtotal' => $subtotal,
    'grand_total' => $grand_total,
    'zone_name' => $shipping_data['zone_name'],
    'eta' => $shipping_data['eta_text'],
    'carrier' => $shipping_data['carrier'],
    'delivery_partners' => $shipping_data['delivery_partners'],
    'data' => [
        'pincode' => $shipping_data['pincode'],
        'shipping_fee' => $shipping_data['shipping_fee'],
        'shipping_fee_formatted' => $shipping_data['is_free'] ? 'FREE Delivery' : format_price($shipping_data['shipping_fee']),
        'is_free' => $shipping_data['is_free'],
        'threshold' => $shipping_data['threshold'],
        'remaining_for_free' => $shipping_data['remaining_for_free'],
        'remaining_for_free_formatted' => format_price($shipping_data['remaining_for_free']),
        'zone_name' => $shipping_data['zone_name'],
        'eta_text' => $shipping_data['eta_text'],
        'carrier' => $shipping_data['carrier'],
        'dead_weight_kg' => $shipping_data['total_weight_kg'],
        'volumetric_weight_kg' => $shipping_data['volumetric_weight_kg'],
        'billable_weight_kg' => $shipping_data['billable_weight_kg'],
        'dimensions_formatted' => $shipping_data['dimensions_formatted'],
        'reach' => $shipping_data['reach'],
        'badge' => $shipping_data['badge'],
        'delivery_partners' => $shipping_data['delivery_partners']
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
