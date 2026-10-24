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

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function csrf_protect() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!verify_csrf($token)) {
            record_security_event('csrf_validation_failed', 'Invalid or missing CSRF token in POST request from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Security Error: Invalid CSRF Token']);
                exit;
            }
            set_flash('error', 'सुरक्षा त्रुटि: अमान्य CSRF सुरक्षा टोकन। कृपया पुनः प्रयास करें।');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }
}

// Security Event Logger
function record_security_event($event_type, $details = null, $user_id = null) {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $uid = $user_id ?: ($_SESSION['user_id'] ?? null);
        $stmt = $pdo->prepare("INSERT INTO security_logs (event_type, ip_address, user_id, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$event_type, $ip, $uid, is_array($details) ? json_encode($details) : (string)$details]);
    } catch (Exception $e) {}
}

// Login Rate Limiter (Brute Force Protection)
function check_login_rate_limit($identifier, $ip) {
    global $pdo;
    try {
        $time_window = date('Y-m-d H:i:s', time() - 900); // 15 minutes window
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE (identifier = ? OR ip_address = ?) AND success = 0 AND attempted_at >= ?
        ");
        $stmt->execute([$identifier, $ip, $time_window]);
        $failed_count = (int)$stmt->fetchColumn();
        return ($failed_count < 5); // Allow max 5 failed attempts per 15 mins
    } catch (Exception $e) {
        return true;
    }
}

function record_login_attempt($identifier, $ip, $success) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?, ?, ?)");
        $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
        // Periodic cleanup of attempts older than 48 hours
        if (rand(1, 100) <= 5) {
            $old_time = date('Y-m-d H:i:s', time() - 172800);
            $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$old_time]);
        }
    } catch (Exception $e) {}
}

// Administrative Audit Trail Logger
function log_audit_action($action, $module, $entity_type = null, $entity_id = null, $details = null) {
    global $pdo;
    try {
        $user = get_logged_in_user();
        $user_id = $user['id'] ?? ($_SESSION['user_id'] ?? null);
        $user_name = $user['name'] ?? ($_SESSION['user_name'] ?? 'System');
        $user_role = $user['role'] ?? ($_SESSION['user_role'] ?? 'guest');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';

        $stmt = $pdo->prepare("
            INSERT INTO admin_audit_logs (user_id, user_name, user_role, action, module, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $user_name,
            $user_role,
            $action,
            $module,
            $entity_type,
            (string)$entity_id,
            is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : (string)$details,
            $ip,
            $ua
        ]);
    } catch (Exception $e) {}
}

// Granular Role & Permission Check
function user_has_permission($permission_slug, $user_id = null) {
    global $pdo;
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['user_role'] ?? '';
    } else {
        $stmt_r = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt_r->execute([$user_id]);
        $role = $stmt_r->fetchColumn() ?: '';
    }

    if ($role === 'super_admin') {
        return true; // Super Admin has all privileges
    }
    if (empty($user_id)) {
        return false;
    }

    try {
        // Check if role has permission
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            JOIN roles r ON rp.role_id = r.id
            WHERE r.slug = ? AND p.slug = ?
        ");
        $stmt->execute([$role, $permission_slug]);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }

        // Check if user has direct assigned role
        $stmt_u = $pdo->prepare("
            SELECT COUNT(*)
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND p.slug = ?
        ");
        $stmt_u->execute([$user_id, $permission_slug]);
        return ($stmt_u->fetchColumn() > 0);
    } catch (Exception $e) {
        return false;
    }
}

// Safe Custom HTML Sanitizer (For festival & hero campaign blocks)
function sanitize_custom_html($html) {
    if (empty($html)) return '';
    
    // Remove script tags and embedded content
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
    $html = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $html);
    $html = preg_replace('/<embed\b[^>]*>(.*?)<\/embed>/is', '', $html);
    $html = preg_replace('/<applet\b[^>]*>(.*?)<\/applet>/is', '', $html);
    $html = preg_replace('/<meta\b[^>]*>/is', '', $html);
    $html = preg_replace('/<link\b[^>]*>/is', '', $html);

    // Remove inline event handlers (onclick, onload, onerror, onmouseover, etc.)
    $html = preg_replace('/\s*on[a-z]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $html);

    // Remove javascript: and data: URLs (except images)
    $html = preg_replace('/href\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', 'href="#"', $html);
    $html = preg_replace('/src\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', 'src=""', $html);

    return $html;
}

// Secure Image Upload Validator
function secure_upload_image($file_input, $dest_dir = 'uploads/') {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($_FILES[$file_input]['error'] ?? 'UNKNOWN')];
    }

    $file = $_FILES[$file_input];
    $max_bytes = 5 * 1024 * 1024; // 5 MB

    if ($file['size'] > $max_bytes) {
        return ['success' => false, 'error' => 'फ़ाइल साइज़ 5MB से अधिक नहीं होना चाहिए (File size exceeds 5MB limit).'];
    }

    // Strict MIME check via Fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];

    if (!array_key_exists($mime, $allowed_mimes)) {
        record_security_event('suspicious_file_upload_attempt', ['mime' => $mime, 'original_name' => $file['name']]);
        return ['success' => false, 'error' => 'अमान्य फ़ाइल प्रकार! केवल JPG, PNG या WebP चित्र ही अनुमत हैं (Invalid image type).'];
    }

    $ext = $allowed_mimes[$mime];
    $target_dir = rtrim(__DIR__ . '/../' . $dest_dir, '/') . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $random_name = 'ggh_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = $target_dir . $random_name;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'फ़ाइल सहेजने में विफल (Failed to save uploaded file).'];
    }

    $relative_path = '/' . trim($dest_dir, '/') . '/' . $random_name;
    return ['success' => true, 'path' => $relative_path];
}

// Central Notification Dispatch Queue
function queue_notification($user_id, $recipient, $channel, $title, $content) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, recipient, channel, title, content, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $recipient, $channel, $title, $content]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        return false;
    }
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

// Authenticate user with password_verify and Brute-force rate limiting
function authenticate_user($identifier, $password, $expected_role_group = null) {
    global $pdo;
    $identifier = trim((string)$identifier);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // 1. Rate Limit Gate
    if (!check_login_rate_limit($identifier, $ip)) {
        record_security_event('login_rate_limit_triggered', ['identifier' => $identifier, 'ip' => $ip]);
        return [
            'success' => false, 
            'error' => 'सुरक्षा अवरोध: बार-बार गलत पासवर्ड दर्ज करने के कारण यह प्रयास रोक दिया गया है। कृपया 15 मिनट बाद पुनः प्रयास करें।'
        ];
    }
    
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
        record_login_attempt($identifier, $ip, false);
        record_security_event('login_failed_unknown_user', ['identifier' => $identifier]);
        return ['success' => false, 'error' => 'यूजरनेम, ईमेल या मोबाइल नंबर नहीं मिला (Account not found).'];
    }

    if ($user['status'] === 'suspended') {
        record_security_event('login_attempt_suspended_account', ['user_id' => $user['id']]);
        return ['success' => false, 'error' => 'यह खाता निलंबित (Suspended) है। कृपया प्रशासन से संपर्क करें।'];
    }

    if (!password_verify($password, $user['password'])) {
        record_login_attempt($identifier, $ip, false);
        record_security_event('login_failed_bad_password', ['user_id' => $user['id']]);
        return ['success' => false, 'error' => 'गलत पासवर्ड दर्ज किया गया (Invalid password).'];
    }

    // Role check if specific portal tab was chosen
    if ($expected_role_group === 'admin' && !in_array($user['role'], ['admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'admin']);
        return ['success' => false, 'error' => 'यह खाता स्टाफ या एडमिनिस्ट्रेशन पोर्टल के लिए अधिकृत नहीं है।'];
    }
    if ($expected_role_group === 'seller' && !in_array($user['role'], ['vendor', 'admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'seller']);
        return ['success' => false, 'error' => 'यह खाता विक्रेता / कारीगर पोर्टल के लिए अधिकृत नहीं है।'];
    }

    // Record Successful Login
    record_login_attempt($identifier, $ip, true);

    // Rotate Session ID upon authentication (Prevents Session Fixation)
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Set Session Variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];

    log_audit_action('user_login', 'auth', 'user', $user['id'], 'Successful sign-in from ' . $ip);

    return ['success' => true, 'user' => $user];
}

// Active Festival / Campaign Hero Resolver (Deterministic Asia/Kolkata Scheduling)
function get_active_hero_campaign() {
    global $pdo;
    date_default_timezone_set('Asia/Kolkata');
    $now = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM hero_campaigns 
            WHERE status = 'active'
              AND (start_datetime IS NULL OR start_datetime <= ?)
              AND (end_datetime IS NULL OR end_datetime >= ?)
            ORDER BY priority DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([$now, $now]);
        $campaign = $stmt->fetch();
        if ($campaign) {
            return $campaign;
        }
    } catch (Exception $e) {}

    // Fallback to null (uses site_settings default)
    return null;
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
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart']) || !$pdo) return [];

    $product_ids = array_values(array_filter(array_map('intval', array_keys($_SESSION['cart']))));
    if (empty($product_ids)) return [];

    try {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, v.name as vendor_name, v.location as vendor_location 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vendors v ON p.vendor_id = v.id 
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll() ?: [];

        foreach ($products as $prod) {
            $qty = (int)($_SESSION['cart'][$prod['id']]['qty'] ?? 1);
            $cart_data[] = [
                'product' => $prod,
                'quantity' => $qty,
                'subtotal' => (float)$prod['price'] * $qty,
                'total_weight' => (float)($prod['weight_kg'] ?? 0.5) * $qty
            ];
        }
    } catch (Throwable $e_cart) {
        return [];
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
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        return false;
    }
    return in_array((int)$product_id, $_SESSION['wishlist']);
}

function toggle_wishlist($product_id) {
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    $product_id = (int)$product_id;
    $idx = array_search($product_id, $_SESSION['wishlist']);
    if ($idx !== false) {
        unset($_SESSION['wishlist'][$idx]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        return false; // removed
    } else {
        $_SESSION['wishlist'][] = $product_id;
        $_SESSION['wishlist'] = array_values(array_unique($_SESSION['wishlist']));
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

/**
 * Get Site Setting with Memory Cache
 */
function get_site_setting($key, $default = '') {
    global $pdo;
    static $settings_cache = null;
    if ($settings_cache === null) {
        $settings_cache = [];
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $val = $row['setting_value'];
                if (function_exists('fix_utf8_mojibake')) {
                    $val = fix_utf8_mojibake($val);
                }
                $settings_cache[$row['setting_key']] = $val;
            }
        } catch (Throwable $e) {}
    }
    $res = $settings_cache[$key] ?? $default;
    return function_exists('fix_utf8_mojibake') ? fix_utf8_mojibake($res) : $res;
}

/**
 * SEO 301/302 Redirect Interceptor
 * Checks current request URI against seo_redirects rules
 */
function handle_seo_redirects() {
    global $pdo;
    if (php_sapi_name() === 'cli' || empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (empty($uri) || $uri === '/' || str_starts_with($uri, '/admin/') || str_starts_with($uri, '/seller/')) {
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, target_url, status_code FROM seo_redirects WHERE source_path = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$uri]);
        $rule = $stmt->fetch();
        if ($rule) {
            $pdo->prepare("UPDATE seo_redirects SET hits = hits + 1 WHERE id = ?")->execute([$rule['id']]);
            header("Location: " . $rule['target_url'], true, (int)$rule['status_code']);
            exit;
        }
    } catch (Exception $e) {}
}

/**
 * Log 404 Crawl / Access Failure
 */
function record_404_hit($path = null) {
    global $pdo;
    if (php_sapi_name() === 'cli') return;
    $path = $path ?: parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (empty($path)) return;
    $ip = get_client_ip();
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    try {
        $stmt = $pdo->prepare("SELECT id FROM seo_404_logs WHERE request_path = ? AND resolved = 0 LIMIT 1");
        $stmt->execute([$path]);
        $exist = $stmt->fetch();
        if ($exist) {
            $pdo->prepare("UPDATE seo_404_logs SET hit_count = hit_count + 1, last_occurred_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$exist['id']]);
        } else {
            $stmt_ins = $pdo->prepare("INSERT INTO seo_404_logs (request_path, referrer, ip_address, user_agent, hit_count, resolved) VALUES (?, ?, ?, ?, 1, 0)");
            $stmt_ins->execute([$path, $referrer, $ip, $ua]);
        }
    } catch (Exception $e) {}
}

// Automatically check active SEO redirects on page load
if (php_sapi_name() !== 'cli') {
    handle_seo_redirects();
}
