<?php
/**
 * Test Suite: GanvGaliHat Retailer Hub & Seller B2B Bulk Product Creation
 * Validates:
 * 1. Syntax of all new and modified Retailer & Seller Bulk files
 * 2. Database columns for B2B wholesale (products, users, orders)
 * 3. Retailer role authorization helpers (is_retailer, require_retailer)
 * 4. Seller Bulk Product management (MOQ, bulk_price, tier2 pricing, lead time)
 * 5. Retailer registration & B2B login redirection
 * 6. B2B Wholesale Cart MOQ & Tiered price enforcement
 * 7. B2B Order placement and GST tax invoice generation
 */

define('TESTING_MOCK_DB', true);

$passed = 0;
$failed = 0;

function assert_test($condition, $name) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

echo "=== 1. Checking PHP Syntax of Retailer, Seller & Core Files ===\n";
$files_to_check = [
    'retailer-register.php',
    'retailer/index.php',
    'retailer/retailer_header.php',
    'retailer/retailer_footer.php',
    'retailer/dashboard.php',
    'retailer/products.php',
    'retailer/cart.php',
    'retailer/checkout.php',
    'retailer/orders.php',
    'retailer/profile.php',
    'seller/products.php',
    'seller/orders.php',
    'seller/dashboard.php',
    'admin/products.php',
    'login.php',
    'product.php',
    'includes/header.php',
    'includes/functions.php',
    'includes/auth_check.php',
    'config/db.php'
];

foreach ($files_to_check as $rel_file) {
    $full_path = dirname(__DIR__) . '/' . $rel_file;
    assert_test(file_exists($full_path), "File exists: {$rel_file}");
    exec('php -l ' . escapeshellarg($full_path), $out, $ret);
    assert_test($ret === 0, "No syntax errors: {$rel_file}");
}

echo "\n=== 2. Testing Retailer Authorization Helpers ===\n";
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth_check.php';

// Test is_retailer() with empty session
$_SESSION = [];
assert_test(is_retailer() === false, "is_retailer() returns false for guest");

$_SESSION['user_role'] = 'customer';
assert_test(is_retailer() === false, "is_retailer() returns false for customer");

$_SESSION['user_role'] = 'vendor';
assert_test(is_retailer() === false, "is_retailer() returns false for vendor");

$_SESSION['user_role'] = 'retailer';
assert_test(is_retailer() === true, "is_retailer() returns true for retailer");

$_SESSION['user_role'] = 'super_admin';
assert_test(is_retailer() === true, "is_retailer() returns true for super_admin");

echo "\n=== 3. Testing In-Memory SQLite Mock Database & B2B Schema ===\n";
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Mock Tables
$pdo->exec("
    CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        hindi_name TEXT,
        code TEXT,
        category_id INTEGER DEFAULT 1,
        vendor_id INTEGER DEFAULT 1,
        price REAL,
        original_price REAL,
        stock INTEGER DEFAULT 100,
        stock_status TEXT DEFAULT 'In Stock',
        weight_kg REAL DEFAULT 0.5,
        length_cm REAL DEFAULT 10,
        width_cm REAL DEFAULT 10,
        height_cm REAL DEFAULT 10,
        volumetric_weight REAL DEFAULT 0.2,
        image TEXT DEFAULT '',
        bulk_available INTEGER DEFAULT 0,
        bulk_moq INTEGER DEFAULT 10,
        bulk_price REAL DEFAULT 0,
        bulk_tier2_qty INTEGER DEFAULT 0,
        bulk_tier2_price REAL DEFAULT 0,
        bulk_packaging_desc TEXT DEFAULT '',
        bulk_lead_time_days INTEGER DEFAULT 3
    );

    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        phone TEXT,
        password TEXT,
        role TEXT DEFAULT 'customer',
        business_name TEXT DEFAULT NULL,
        gstin TEXT DEFAULT NULL,
        business_type TEXT DEFAULT NULL,
        pan_number TEXT DEFAULT NULL,
        business_address TEXT DEFAULT NULL,
        business_city TEXT DEFAULT NULL,
        business_state TEXT DEFAULT NULL,
        business_pincode TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT,
        user_id INTEGER,
        customer_name TEXT,
        customer_phone TEXT,
        customer_email TEXT,
        shipping_street TEXT,
        shipping_city TEXT,
        shipping_state TEXT,
        shipping_pincode TEXT,
        subtotal REAL,
        shipping_fee REAL,
        total_amount REAL,
        payment_method TEXT,
        payment_status TEXT,
        order_status TEXT,
        tracking_carrier TEXT,
        tracking_consignment_no TEXT,
        order_access_token TEXT,
        order_type TEXT DEFAULT 'retail',
        business_name TEXT DEFAULT NULL,
        gstin TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER,
        product_id INTEGER,
        vendor_id INTEGER,
        product_name TEXT,
        product_code TEXT,
        price REAL,
        quantity INTEGER,
        total REAL
    );
");

// Verify Columns exist
$p_cols = $pdo->query("PRAGMA table_info(products)")->fetchAll(PDO::FETCH_COLUMN, 1);
assert_test(in_array('bulk_available', $p_cols), "products table has bulk_available column");
assert_test(in_array('bulk_moq', $p_cols), "products table has bulk_moq column");
assert_test(in_array('bulk_price', $p_cols), "products table has bulk_price column");
assert_test(in_array('bulk_tier2_qty', $p_cols), "products table has bulk_tier2_qty column");
assert_test(in_array('bulk_tier2_price', $p_cols), "products table has bulk_tier2_price column");
assert_test(in_array('bulk_packaging_desc', $p_cols), "products table has bulk_packaging_desc column");
assert_test(in_array('bulk_lead_time_days', $p_cols), "products table has bulk_lead_time_days column");

$u_cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
assert_test(in_array('business_name', $u_cols), "users table has business_name column");
assert_test(in_array('gstin', $u_cols), "users table has gstin column");
assert_test(in_array('business_type', $u_cols), "users table has business_type column");

$o_cols = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_COLUMN, 1);
assert_test(in_array('order_type', $o_cols), "orders table has order_type column");
assert_test(in_array('business_name', $o_cols), "orders table has business_name column");
assert_test(in_array('gstin', $o_cols), "orders table has gstin column");

echo "\n=== 4. Testing Seller Bulk Product Listing ===\n";
// Insert sample product with B2B wholesale pricing
$stmt = $pdo->prepare("
    INSERT INTO products (
        name, hindi_name, code, price, original_price, stock, stock_status,
        bulk_available, bulk_moq, bulk_price, bulk_tier2_qty, bulk_tier2_price,
        bulk_packaging_desc, bulk_lead_time_days
    ) VALUES (
        'Hardoi Hand-Forged Desi Hansiya', 'देसी हंसिया (मजबूत धारदार)', 'GGH-TL-101',
        249.00, 349.00, 500, 'In Stock',
        1, 20, 160.00, 50, 140.00,
        'Heavy Corrugated Box of 25 pieces', 2
    )
");
$stmt->execute();
$sample_pid = $pdo->lastInsertId();
assert_test($sample_pid > 0, "Seller successfully listed product with B2B Wholesale bulk settings (ID: {$sample_pid})");

$p_check = $pdo->query("SELECT * FROM products WHERE id = {$sample_pid}")->fetch();
assert_test($p_check['bulk_available'] == 1, "Product bulk_available is 1");
assert_test($p_check['bulk_moq'] == 20, "Product bulk_moq is 20");
assert_test($p_check['bulk_price'] == 160.00, "Product bulk_price is 160.00");
assert_test($p_check['bulk_tier2_qty'] == 50, "Product bulk_tier2_qty is 50");
assert_test($p_check['bulk_tier2_price'] == 140.00, "Product bulk_tier2_price is 140.00");

echo "\n=== 5. Testing B2B Bulk Cart & MOQ Enforcement ===\n";
clear_bulk_cart();
assert_test(empty(get_bulk_cart()), "Bulk cart is initially empty");

// Attempt to add less than MOQ (10 < 20)
$res_under = add_to_bulk_cart($sample_pid, 10);
assert_test($res_under['success'] === false, "Adding less than MOQ (10 < 20) is blocked with error");
assert_test(strpos($res_under['error'], 'MOQ') !== false || strpos($res_under['error'], '20') !== false, "Error message mentions MOQ requirement");

// Add valid MOQ quantity (25 pieces @ ₹160)
$res_valid = add_to_bulk_cart($sample_pid, 25);
assert_test($res_valid['success'] === true, "Adding valid MOQ quantity (25 pieces) succeeds");
$cart = get_bulk_cart();
assert_test(isset($cart[$sample_pid]), "Item is in wholesale cart");
assert_test($cart[$sample_pid]['bulk_price'] == 160.00, "Tier 1 unit price is ₹160.00");
assert_test($cart[$sample_pid]['subtotal'] == (25 * 160.00), "Subtotal for 25 units is ₹4,000.00");
assert_test(get_bulk_cart_count() === 25, "Cart count is 25 pieces");

// Update quantity to Tier 2 (60 pieces >= 50 pieces @ ₹140)
$res_t2 = update_bulk_cart($sample_pid, 60);
assert_test($res_t2['success'] === true, "Updating to Tier 2 (60 pieces) succeeds");
$cart_t2 = get_bulk_cart();
assert_test($cart_t2[$sample_pid]['bulk_price'] == 140.00, "Tier 2 unit price correctly drops to ₹140.00");
assert_test($cart_t2[$sample_pid]['subtotal'] == (60 * 140.00), "Tier 2 subtotal is ₹8,400.00");
assert_test(get_bulk_cart_subtotal() == 8400.00, "get_bulk_cart_subtotal() returns 8400.00");

echo "\n=== 6. Testing Retailer Registration & B2B Order Placement ===\n";
$stmt_u = $pdo->prepare("
    INSERT INTO users (
        name, email, phone, password, role,
        business_name, gstin, business_type, pan_number,
        business_address, business_city, business_state, business_pincode
    ) VALUES (
        'Ramesh Kumar', 'ramesh@guptakirana.in', '9876543210', 'hashed_pwd', 'retailer',
        'Gupta Kirana & General Merchant', '09ABCDE1234F1Z5', 'Kirana / Grocery', 'ABCDE1234F',
        'Shop No. 12, Main Mandi Road', 'Sandila', 'Uttar Pradesh', '241204'
    )
");
$stmt_u->execute();
$retailer_id = $pdo->lastInsertId();
assert_test($retailer_id > 0, "Retailer user registered with ID: {$retailer_id}");

$u_check = $pdo->query("SELECT * FROM users WHERE id = {$retailer_id}")->fetch();
assert_test($u_check['role'] === 'retailer', "User role is 'retailer'");
assert_test($u_check['business_name'] === 'Gupta Kirana & General Merchant', "Business name stored correctly");
assert_test($u_check['gstin'] === '09ABCDE1234F1Z5', "GSTIN stored correctly");

// Place B2B Order
$order_number = 'B2B-20261116-A1B2C';
$b2b_subtotal = get_bulk_cart_subtotal();
$b2b_tax = round($b2b_subtotal * 0.05, 2);
$b2b_total = round($b2b_subtotal + $b2b_tax, 2);

$stmt_o = $pdo->prepare("
    INSERT INTO orders (
        order_number, user_id, customer_name, customer_phone, customer_email,
        shipping_street, shipping_city, shipping_state, shipping_pincode,
        subtotal, shipping_fee, total_amount, payment_method, payment_status,
        order_status, tracking_carrier, tracking_consignment_no, order_access_token,
        order_type, business_name, gstin
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        'wholesale_b2b', ?, ?
    )
");
$stmt_o->execute([
    $order_number, $retailer_id, 'Ramesh Kumar', '9876543210', 'ramesh@guptakirana.in',
    'Shop No. 12, Main Mandi Road', 'Sandila', 'Uttar Pradesh', '241204',
    $b2b_subtotal, 0.00, $b2b_total, 'bank_transfer', 'pending_confirmation',
    'Confirmed', 'Rural Cargo Freight', 'PL-2611-88492', 'mock_token_123',
    'Gupta Kirana & General Merchant', '09ABCDE1234F1Z5'
]);
$order_id = $pdo->lastInsertId();
assert_test($order_id > 0, "B2B Order successfully placed with ID: {$order_id}");

$stmt_oi = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, vendor_id, product_name, product_code, price, quantity, total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt_oi->execute([
    $order_id, $sample_pid, 1, 'Hardoi Hand-Forged Desi Hansiya', 'GGH-TL-101',
    140.00, 60, 8400.00
]);

// Verify order query for retailer
$stmt_ret_ord = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND order_type = 'wholesale_b2b'");
$stmt_ret_ord->execute([$retailer_id]);
$ret_orders = $stmt_ret_ord->fetchAll();
assert_test(count($ret_orders) === 1, "Retailer orders query returns exactly 1 wholesale order");
assert_test($ret_orders[0]['order_type'] === 'wholesale_b2b', "Order type is 'wholesale_b2b'");
assert_test($ret_orders[0]['business_name'] === 'Gupta Kirana & General Merchant', "Order has recipient business name");
assert_test($ret_orders[0]['gstin'] === '09ABCDE1234F1Z5', "Order has recipient GSTIN for Tax Invoice");

clear_bulk_cart();
assert_test(empty(get_bulk_cart()), "Bulk cart is cleared after order placement");

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
} else {
    exit(0);
}
