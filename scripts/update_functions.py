with open('includes/functions.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add is_retailer() and Bulk Cart helpers after is_super_admin()
target1 = """function is_super_admin() {
    $u = get_logged_in_user();
    return $u && $u['role'] === 'super_admin';
}"""

retailer_helpers = """function is_super_admin() {
    $u = get_logged_in_user();
    return $u && $u['role'] === 'super_admin';
}

function is_retailer() {
    $u = get_logged_in_user();
    return $u && in_array($u['role'], ['retailer', 'super_admin', 'admin'], true);
}

/**
 * Retailer B2B Wholesale Bulk Cart Helpers
 */
function get_bulk_cart() {
    return $_SESSION['bulk_cart'] ?? [];
}

function add_to_bulk_cart($product_id, $quantity) {
    global $pdo;
    $product_id = intval($product_id);
    $quantity = max(1, intval($quantity));

    if ($product_id <= 0 || !$pdo) {
        return ['success' => false, 'error' => 'Invalid product selected.'];
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'error' => 'Product not found in catalog.'];
    }

    $moq = max(1, intval($product['bulk_moq'] ?? 10));
    if ($quantity < $moq) {
        return ['success' => false, 'error' => "Minimum Wholesale Order Quantity (MOQ) for {$product['name']} is {$moq} units."];
    }

    // Determine unit price based on tiered bulk pricing
    $unit_price = floatval($product['bulk_price'] ?: ($product['price'] * 0.75));
    if (!empty($product['bulk_tier2_qty']) && !empty($product['bulk_tier2_price']) && $quantity >= intval($product['bulk_tier2_qty'])) {
        $unit_price = floatval($product['bulk_tier2_price']);
    }

    if (!isset($_SESSION['bulk_cart'])) {
        $_SESSION['bulk_cart'] = [];
    }

    $_SESSION['bulk_cart'][$product_id] = [
        'product_id'   => $product['id'],
        'name'         => $product['name'],
        'hindi_name'   => $product['hindi_name'] ?? '',
        'code'         => $product['code'] ?? '',
        'vendor_id'    => $product['vendor_id'] ?? 1,
        'image'        => $product['image'],
        'unit'         => $product['unit'] ?? 'piece',
        'retail_price' => floatval($product['price']),
        'bulk_price'   => $unit_price,
        'quantity'     => $quantity,
        'moq'          => $moq,
        'weight_kg'    => floatval($product['weight_kg'] ?? 0.5),
        'packaging'    => $product['bulk_packaging_desc'] ?? 'Master Carton',
        'lead_time'    => intval($product['bulk_lead_time_days'] ?? 3),
        'subtotal'     => round($unit_price * $quantity, 2)
    ];

    return ['success' => true, 'message' => "{$product['name']} ({$quantity} units) added to wholesale cart."];
}

function update_bulk_cart($product_id, $quantity) {
    global $pdo;
    $product_id = intval($product_id);
    $quantity = intval($quantity);

    if ($quantity <= 0) {
        unset($_SESSION['bulk_cart'][$product_id]);
        return ['success' => true, 'message' => 'Item removed from wholesale cart.'];
    }

    if (!isset($_SESSION['bulk_cart'][$product_id])) {
        return ['success' => false, 'error' => 'Item not in wholesale cart.'];
    }

    $moq = intval($_SESSION['bulk_cart'][$product_id]['moq'] ?? 10);
    if ($quantity < $moq) {
        return ['success' => false, 'error' => "Quantity cannot be less than Minimum Order Quantity ({$moq} units)."];
    }

    $unit_price = $_SESSION['bulk_cart'][$product_id]['bulk_price'];
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $p = $stmt->fetch();
        if ($p) {
            $unit_price = floatval($p['bulk_price'] ?: ($p['price'] * 0.75));
            if (!empty($p['bulk_tier2_qty']) && !empty($p['bulk_tier2_price']) && $quantity >= intval($p['bulk_tier2_qty'])) {
                $unit_price = floatval($p['bulk_tier2_price']);
            }
        }
    }

    $_SESSION['bulk_cart'][$product_id]['quantity'] = $quantity;
    $_SESSION['bulk_cart'][$product_id]['bulk_price'] = $unit_price;
    $_SESSION['bulk_cart'][$product_id]['subtotal'] = round($unit_price * $quantity, 2);

    return ['success' => true, 'message' => 'Wholesale cart updated.'];
}

function remove_from_bulk_cart($product_id) {
    $product_id = intval($product_id);
    unset($_SESSION['bulk_cart'][$product_id]);
    return ['success' => true];
}

function get_bulk_cart_count() {
    $cart = get_bulk_cart();
    $cnt = 0;
    foreach ($cart as $item) {
        $cnt += intval($item['quantity'] ?? 0);
    }
    return $cnt;
}

function get_bulk_cart_subtotal() {
    $cart = get_bulk_cart();
    $tot = 0.0;
    foreach ($cart as $item) {
        $tot += floatval($item['subtotal'] ?? ($item['bulk_price'] * $item['quantity']));
    }
    return round($tot, 2);
}

function clear_bulk_cart() {
    unset($_SESSION['bulk_cart']);
}"""

assert target1 in content, "target1 not found in includes/functions.php"
content = content.replace(target1, retailer_helpers, 1)

# 2. Add expected_role_group check for retailer in authenticate_user()
target2 = """    if ($expected_role_group === 'seller' && !in_array($user['role'], ['vendor', 'admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'seller']);
        return ['success' => false, 'error' => 'This account is not authorized for the artisan / vendor portal.'];
    }"""

retailer_auth = """    if ($expected_role_group === 'seller' && !in_array($user['role'], ['vendor', 'admin', 'super_admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'seller']);
        return ['success' => false, 'error' => 'This account is not authorized for the artisan / vendor portal.'];
    }
    if ($expected_role_group === 'retailer' && !in_array($user['role'], ['retailer', 'super_admin', 'admin'])) {
        record_security_event('login_unauthorized_portal_access', ['user_id' => $user['id'], 'expected' => 'retailer']);
        return ['success' => false, 'error' => 'This account is not authorized for the B2B Wholesale / Retailer portal. Please register as a retailer or sign in under customer tab.'];
    }"""

assert target2 in content, "target2 not found in includes/functions.php"
content = content.replace(target2, retailer_auth, 1)

with open('includes/functions.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("includes/functions.php updated successfully")
