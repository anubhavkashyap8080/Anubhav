<?php
/**
 * GaunGaliHat Platform Helper Functions
 */

if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/shipping.php';

// Sanitize user inputs
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Format INR Price
function format_price($amount) {
    return '₹' . number_format((float)$amount, 2);
}

// Generate / Get CSRF Token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Flash Message System
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Authentication Helpers
function get_logged_in_user() {
    global $pdo;
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function is_vendor() {
    return is_logged_in() && in_array($_SESSION['user_role'] ?? '', ['vendor', 'admin', 'super_admin']);
}

function is_admin() {
    return is_logged_in() && in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin']);
}

function is_super_admin() {
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'super_admin';
}

function get_vendor_for_current_user() {
    global $pdo;
    $user = get_logged_in_user();
    if (!$user) return null;
    
    // Check by user_id
    $stmt = $pdo->prepare("SELECT * FROM vendors WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $vendor = $stmt->fetch();
    
    if (!$vendor && ($user['role'] === 'super_admin' || $user['role'] === 'admin')) {
        // Master admin can manage vendor 1 by default
        $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id ASC LIMIT 1");
        $vendor = $stmt->fetch();
    }
    return $vendor;
}

// Authenticate user with password_verify
function authenticate_user($identifier, $password, $expected_role_group = null) {
    global $pdo;
    $identifier = trim((string)$identifier);
    
    // Check if identifier is numeric phone or has digits
    $clean_phone = preg_replace('/[^0-9]/', '', $identifier);
    $has_valid_phone = (strlen($clean_phone) >= 10);
    if (strlen($clean_phone) > 10) {
        $clean_phone = substr($clean_phone, -10);
    }

    if ($has_valid_phone) {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ? OR phone LIKE ?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier, "%$clean_phone"]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?))
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
    }
    
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'error' => 'यूजरनेम, ईमेल या मोबाइल नंबर नहीं मिला (Account not found).'];
    }

    if ($user['status'] === 'suspended') {
        return ['success' => false, 'error' => 'यह खाता निलंबित (Suspended) है। कृपया प्रशासन से संपर्क करें।'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'गलत पासवर्ड दर्ज किया गया (Invalid password).'];
    }

    // Role check if specific portal tab was chosen
    if ($expected_role_group === 'admin' && !in_array($user['role'], ['admin', 'super_admin'])) {
        return ['success' => false, 'error' => 'यह खाता स्टाफ या एडमिनिस्ट्रेशन पोर्टल के लिए अधिकृत नहीं है।'];
    }
    if ($expected_role_group === 'seller' && !in_array($user['role'], ['vendor', 'admin', 'super_admin'])) {
        return ['success' => false, 'error' => 'यह खाता विक्रेता / कारीगर पोर्टल के लिए अधिकृत नहीं है।'];
    }

    // Set Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];

    return ['success' => true, 'user' => $user];
}

// ==========================================
// CART FUNCTIONS (Session-based & Persistent)
// ==========================================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function get_cart() {
    global $pdo;
    $cart_data = [];
    if (empty($_SESSION['cart'])) return [];

    $product_ids = array_keys($_SESSION['cart']);
    if (empty($product_ids)) return [];

    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, v.name as vendor_name, v.location as vendor_location 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        WHERE p.id IN ($placeholders)
    ");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    foreach ($products as $prod) {
        $qty = $_SESSION['cart'][$prod['id']]['qty'] ?? 1;
        $cart_data[] = [
            'product' => $prod,
            'quantity' => $qty,
            'subtotal' => $prod['price'] * $qty,
            'total_weight' => ($prod['weight_kg'] ?? 0.5) * $qty
        ];
    }
    return $cart_data;
}

function add_to_cart($product_id, $qty = 1) {
    global $pdo;
    $product_id = (int)$product_id;
    $qty = max(1, (int)$qty);

    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $prod = $stmt->fetch();

    if ($prod) {
        if (!isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = ['qty' => 0];
        }
        $new_qty = $_SESSION['cart'][$product_id]['qty'] + $qty;
        $_SESSION['cart'][$product_id]['qty'] = min($prod['stock'], $new_qty);
        return true;
    }
    return false;
}

function update_cart_qty($product_id, $qty) {
    $product_id = (int)$product_id;
    $qty = (int)$qty;
    if ($qty <= 0) {
        remove_from_cart($product_id);
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty'] = $qty;
        }
    }
}

function remove_from_cart($product_id) {
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function clear_cart() {
    $_SESSION['cart'] = [];
}

function get_cart_count() {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)($item['qty'] ?? 1);
        }
    }
    return $count;
}

function get_cart_subtotal() {
    $items = get_cart();
    $total = 0.0;
    foreach ($items as $it) {
        $total += $it['subtotal'];
    }
    return $total;
}

function get_cart_total_weight() {
    $items = get_cart();
    $weight = 0.0;
    foreach ($items as $it) {
        $weight += $it['total_weight'];
    }
    return max(0.5, $weight);
}

// ==========================================
// WISHLIST FUNCTIONS
// ==========================================
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

function get_wishlist_count() {
    return count($_SESSION['wishlist']);
}

function is_in_wishlist($product_id) {
    return in_array((int)$product_id, $_SESSION['wishlist']);
}

function toggle_wishlist($product_id) {
    $product_id = (int)$product_id;
    $idx = array_search($product_id, $_SESSION['wishlist']);
    if ($idx !== false) {
        unset($_SESSION['wishlist'][$idx]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        return false; // removed
    } else {
        $_SESSION['wishlist'][] = $product_id;
        return true; // added
    }
}

// ==========================================
// COUPON & PROMO CODE ENGINE
// ==========================================

function validate_and_apply_coupon($code) {
    global $pdo;
    $code = strtoupper(trim($code));
    if (empty($code)) {
        return ['success' => false, 'error' => 'कृपया वैध कूपन कोड दर्ज करें।'];
    }

    $subtotal = get_cart_subtotal();
    if ($subtotal <= 0) {
        return ['success' => false, 'error' => 'कूपन लागू करने के लिए आपकी कार्ट में कम से कम 1 उत्पाद होना चाहिए।'];
    }

    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['success' => false, 'error' => "कूपन कोड '$code' अमान्य है या समाप्त हो चुका है।"];
    }

    // Check Expiry Date
    if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < strtotime(date('Y-m-d'))) {
        return ['success' => false, 'error' => "कूपन कोड '$code' की वैधता समाप्त हो चुकी है।"];
    }

    // Check Minimum Order Amount
    if ($subtotal < floatval($coupon['min_order_amount'])) {
        return [
            'success' => false,
            'error' => "यह कूपन न्यूनतम ₹" . number_format($coupon['min_order_amount'], 0) . " के आर्डर पर ही लागू होगा। (वर्तमान कार्ट: " . format_price($subtotal) . ")"
        ];
    }

    // Check Usage Limit
    if ($coupon['used_count'] >= $coupon['usage_limit']) {
        return ['success' => false, 'error' => "इस कूपन की कुल उपयोग सीमा पूरी हो चुकी है।"];
    }

    $curr_user = is_logged_in() ? get_logged_in_user() : null;
    $user_id = $curr_user['id'] ?? null;
    $user_email = $curr_user['email'] ?? '';

    // Check Type 1: New User Only (First order)
    if ($coupon['coupon_type'] === 'new_user_only') {
        if ($curr_user) {
            $stmt_ord = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? OR customer_email = ?");
            $stmt_ord->execute([$user_id, $user_email]);
            if ($stmt_ord->fetchColumn() > 0) {
                return ['success' => false, 'error' => "यह कूपन केवल नए ग्राहकों के पहले आर्डर पर ही मान्य है।"];
            }
        }
    }

    // Check Type 2: One Per User
    if ($coupon['coupon_type'] === 'one_per_user') {
        if ($curr_user) {
            $stmt_used = $pdo->prepare("SELECT id FROM coupon_usages WHERE coupon_id = ? AND (user_id = ? OR customer_email = ?)");
            $stmt_used->execute([$coupon['id'], $user_id, $user_email]);
            if ($stmt_used->fetch()) {
                return ['success' => false, 'error' => "आप इस कूपन कोड ($code) का उपयोग पहले ही कर चुके हैं। यह प्रति ग्राहक केवल 1 बार मान्य है।"];
            }
        }
    }

    // Calculate Discount Amount
    $discount = 0.0;
    if ($coupon['discount_type'] === 'fixed') {
        $discount = min($subtotal, floatval($coupon['discount_value']));
    } else { // percentage
        $pct = floatval($coupon['discount_value']);
        $discount = round($subtotal * ($pct / 100), 2);
        if ($coupon['max_discount'] > 0) {
            $discount = min($discount, floatval($coupon['max_discount']));
        }
    }

    $_SESSION['applied_coupon'] = [
        'id'              => $coupon['id'],
        'code'            => $coupon['code'],
        'title'           => $coupon['title'],
        'discount_type'   => $coupon['discount_type'],
        'discount_value'  => $coupon['discount_value'],
        'discount_amount' => $discount,
        'min_order'       => $coupon['min_order_amount']
    ];

    return [
        'success'         => true,
        'discount'        => $discount,
        'message'         => "बधाई! कूपन '$code' सफलतापूर्वक लागू हुआ। आपने " . format_price($discount) . " की बचत की!"
    ];
}

function get_applied_coupon() {
    if (empty($_SESSION['applied_coupon'])) {
        return null;
    }
    $c = $_SESSION['applied_coupon'];
    $subtotal = get_cart_subtotal();
    if ($subtotal < floatval($c['min_order'])) {
        unset($_SESSION['applied_coupon']);
        return null;
    }
    return $c;
}

function remove_applied_coupon() {
    unset($_SESSION['applied_coupon']);
}
