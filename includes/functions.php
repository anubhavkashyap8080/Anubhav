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
