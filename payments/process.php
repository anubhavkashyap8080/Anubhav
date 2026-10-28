<?php
/**
 * GaunGaliHat Payment Gateway Processor
 * Handles COD, Razorpay, and PayU Order Execution
 */

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /checkout.php');
    exit;
}

// Item 16: Central CSRF Protection
csrf_protect();

$cart_items = get_cart();
if (empty($cart_items)) {
    set_flash('error', 'Your shopping cart is empty.');
    header('Location: /cart.php');
    exit;
}

// Sanitize Customer Details
$customer_name = clean_input($_POST['full_name'] ?? '');
$customer_phone = clean_input($_POST['phone'] ?? '');
$customer_email = clean_input($_POST['email'] ?? '');
$shipping_street = clean_input($_POST['street'] ?? '');
$shipping_city = clean_input($_POST['city'] ?? '');
$shipping_state = clean_input($_POST['state'] ?? 'Uttar Pradesh');
$shipping_pincode = clean_input($_POST['pincode'] ?? '241126');
$payment_method = clean_input($_POST['payment_method'] ?? 'cod');

// Item 24: Whitelist Payment Method
$allowed_payment_methods = ['cod', 'razorpay', 'payu'];
if (!in_array($payment_method, $allowed_payment_methods, true)) {
    set_flash('error', 'Invalid payment method selected.');
    header('Location: /checkout.php');
    exit;
}

// Item 24: Per-IP and Per-Phone Checkout Rate Limiting
$client_ip = get_client_ip();
if (!check_rate_limit('checkout_ip', $client_ip, 10, 900) || !check_rate_limit('checkout_phone', $customer_phone, 5, 900)) {
    set_flash('error', 'Too many checkout attempts. Please wait a few minutes before trying again.');
    header('Location: /checkout.php');
    exit;
}

// Item 24: Cloudflare Turnstile CAPTCHA Verification
if (!verify_turnstile_captcha()) {
    set_flash('error', 'Security check (CAPTCHA) verification failed. Please complete the verification.');
    header('Location: /checkout.php');
    exit;
}

if (empty($customer_name) || empty($customer_phone) || empty($shipping_street) || empty($shipping_pincode)) {
    set_flash('error', 'Please complete all required delivery address fields.');
    header('Location: /checkout.php');
    exit;
}

$order_number = 'ORD-' . date('Ymd') . '-' . random_int(10000, 99999);
$consignment_no = 'EU' . random_int(100000000, 999999999) . 'IN';
$order_access_token = bin2hex(random_bytes(24));
$user_id = $_SESSION['user_id'] ?? null;

try {
    $pdo->beginTransaction();

    // Item 24: Recompute prices & check stock inside transaction using SELECT ... FOR UPDATE
    $order_items_to_insert = [];
    $subtotal = 0.0;
    $total_weight = 0.0;

    $stmt_prod_lock = $pdo->prepare("
        SELECT id, name, code, vendor_id, price, stock, stock_status, weight_kg 
        FROM products WHERE id = ? FOR UPDATE
    ");

    foreach ($cart_items as $item) {
        $p_id = (int)($item['product']['id'] ?? $item['product_id'] ?? 0);
        $qty = max(1, (int)($item['quantity'] ?? 1));

        $stmt_prod_lock->execute([$p_id]);
        $db_product = $stmt_prod_lock->fetch();

        if (!$db_product) {
            $pdo->rollBack();
            set_flash('error', 'One or more products in your cart are no longer available.');
            header('Location: /cart.php');
            exit;
        }

        $avail_stock = max(0, (int)$db_product['stock']);
        if ($avail_stock < $qty || ($db_product['stock_status'] ?? '') === 'Out of Stock') {
            $pdo->rollBack();
            set_flash('error', 'Sorry, "' . htmlspecialchars($db_product['name'], ENT_QUOTES, 'UTF-8') . '" is out of stock or does not have sufficient quantity (Available: ' . $avail_stock . ').');
            header('Location: /cart.php');
            exit;
        }

        // Price strictly from database
        $db_price = max(0, floatval($db_product['price']));
        $line_total = round($db_price * $qty, 2);
        $subtotal += $line_total;
        $total_weight += (max(0.01, floatval($db_product['weight_kg'])) * $qty);

        // Decrement product stock atomically
        $new_stock = $avail_stock - $qty;
        $new_stock_status = ($new_stock <= 0) ? 'Out of Stock' : 'In Stock';
        $pdo->prepare("UPDATE products SET stock = ?, stock_status = ? WHERE id = ?")->execute([$new_stock, $new_stock_status, $p_id]);

        $order_items_to_insert[] = [
            'product_id'   => $db_product['id'],
            'vendor_id'    => $db_product['vendor_id'],
            'product_name' => $db_product['name'],
            'product_code' => $db_product['code'],
            'price'        => $db_price,
            'quantity'     => $qty,
            'total'        => $line_total
        ];
    }

    // Precise India Post Shipping Calculation
    $shipping = calculate_shipping_rate($shipping_pincode, $subtotal, max(0.5, $total_weight));
    $shipping_fee = $shipping['shipping_fee'];

    // Item 25: Atomic Coupon Validation & Per-User Limit Verification inside Transaction
    $applied_coupon = get_applied_coupon();
    $coupon_discount = 0.0;
    $coupon_id_recorded = null;

    if ($applied_coupon) {
        $stmt_cp_lock = $pdo->prepare("SELECT * FROM coupons WHERE id = ? AND status = 'active' FOR UPDATE");
        $stmt_cp_lock->execute([$applied_coupon['id']]);
        $db_coupon = $stmt_cp_lock->fetch();

        $coupon_valid = false;
        if ($db_coupon) {
            $not_expired = empty($db_coupon['expiry_date']) || (strtotime($db_coupon['expiry_date']) >= strtotime(date('Y-m-d')));
            $min_met = ($subtotal >= floatval($db_coupon['min_order_amount']));
            $usage_limit_ok = ((int)$db_coupon['used_count'] < (int)$db_coupon['usage_limit']);

            if ($not_expired && $min_met && $usage_limit_ok) {
                // Verify per-user and new-user constraints
                $per_user_ok = true;
                if (in_array($db_coupon['coupon_type'], ['one_per_user', 'new_user_only'], true)) {
                    $stmt_chk_usage = $pdo->prepare("
                        SELECT COUNT(*) FROM coupon_usages 
                        WHERE coupon_id = ? AND (
                            (? IS NOT NULL AND user_id = ?) 
                            OR (? != '' AND customer_email = ?) 
                            OR (? != '' AND customer_phone = ?)
                        )
                    ");
                    $stmt_chk_usage->execute([
                        $db_coupon['id'],
                        $user_id, $user_id,
                        $customer_email, $customer_email,
                        $customer_phone, $customer_phone
                    ]);
                    if ($stmt_chk_usage->fetchColumn() > 0) {
                        $per_user_ok = false;
                    }
                }

                if ($per_user_ok) {
                    // Atomic increment with usage_limit guard
                    $stmt_inc_coupon = $pdo->prepare("
                        UPDATE coupons SET used_count = used_count + 1 
                        WHERE id = ? AND used_count < usage_limit
                    ");
                    $stmt_inc_coupon->execute([$db_coupon['id']]);

                    if ($stmt_inc_coupon->rowCount() > 0) {
                        $coupon_valid = true;
                        $coupon_id_recorded = $db_coupon['id'];
                        if ($db_coupon['discount_type'] === 'fixed') {
                            $coupon_discount = min($subtotal, floatval($db_coupon['discount_value']));
                        } else {
                            $pct = floatval($db_coupon['discount_value']);
                            $coupon_discount = round($subtotal * ($pct / 100), 2);
                            if ($db_coupon['max_discount'] > 0) {
                                $coupon_discount = min($coupon_discount, floatval($db_coupon['max_discount']));
                            }
                        }
                    }
                }
            }
        }

        if (!$coupon_valid) {
            unset($_SESSION['applied_coupon']);
            $coupon_discount = 0.0;
        }
    }

    $final_total = max(0, round($subtotal + $shipping_fee - $coupon_discount, 2));

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, customer_name, customer_phone, customer_email,
            shipping_street, shipping_city, shipping_state, shipping_pincode,
            subtotal, shipping_fee, total_amount, payment_method, payment_status,
            order_status, tracking_carrier, tracking_consignment_no, order_access_token
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $initial_payment_status = 'pending';
    $initial_order_status = ($payment_method === 'cod') ? 'Confirmed' : 'Order Placed';

    $stmt->execute([
        $order_number, $user_id, $customer_name, $customer_phone, $customer_email,
        $shipping_street, $shipping_city, $shipping_state, $shipping_pincode,
        $subtotal, $shipping_fee, $final_total, $payment_method, $initial_payment_status,
        $initial_order_status, 'India Post Express', $consignment_no, $order_access_token
    ]);

    $order_id = $pdo->lastInsertId();

    // Record Coupon Usage if coupon was applied
    if ($coupon_id_recorded) {
        $stmt_cu = $pdo->prepare("
            INSERT INTO coupon_usages (coupon_id, user_id, order_id, customer_email, customer_phone, discount_amount, used_at)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt_cu->execute([
            $coupon_id_recorded, $user_id, $order_id, $customer_email, $customer_phone, $coupon_discount
        ]);
        unset($_SESSION['applied_coupon']);
    }

    // Insert Order Items
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, vendor_id, product_name, product_code, price, quantity, total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($order_items_to_insert as $oi) {
        $stmt_item->execute([
            $order_id, $oi['product_id'], $oi['vendor_id'], $oi['product_name'], $oi['product_code'],
            $oi['price'], $oi['quantity'], $oi['total']
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', 'Order placement failed: ' . $e->getMessage());
    header('Location: /checkout.php');
    exit;
}

// 1. CASH ON DELIVERY (COD) FLOW (Item 11: Pass long access token for guest viewing)
if ($payment_method === 'cod') {
    clear_cart();
    $_SESSION['last_placed_order_id'] = $order_id;
    set_flash('success', 'Order #' . $order_number . ' successfully confirmed with Cash on Delivery!');
    header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($order_access_token));
    exit;
}

// 2. RAZORPAY GATEWAY CHECKOUT MODAL
if ($payment_method === 'razorpay') {
    $key_id = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
    $key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '';
    $amount_paisa = (int)round($final_total * 100);
    $rzp_order_id = '';

    if (empty($key_id) || empty($key_secret)) {
        log_audit_action('payment_rzp_unconfigured', 'orders', "Razorpay keys missing for order #{$order_id}");
        set_flash('error', 'Razorpay गेटवे कॉन्फ़िगरेशन अनुपलब्ध है। कृपया कैश ऑन डिलीवरी (COD) चुनें।');
        header('Location: /checkout.php');
        exit;
    }

    // Server-Side Razorpay Order Creation via API
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount'          => $amount_paisa,
        'currency'        => 'INR',
        'receipt'         => (string)$order_number,
        'payment_capture' => 1
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $rzp_res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && !empty($rzp_res)) {
        $rzp_data = json_decode($rzp_res, true);
        if (is_array($rzp_data) && !empty($rzp_data['id'])) {
            $rzp_order_id = $rzp_data['id'];
            $pdo->prepare("UPDATE orders SET gateway_order_id = ? WHERE id = ?")->execute([$rzp_order_id, $order_id]);
        }
    } else {
        error_log("Razorpay Order Creation API failed (HTTP {$http_code})");
    }

    // Fail-closed check: Never proceed without a verified gateway_order_id
    if (empty($rzp_order_id)) {
        try {
            // Restore inventory
            foreach ($order_items_to_insert as $oi) {
                $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$oi['quantity'], $oi['product_id']]);
            }
            // Cancel pending order
            $pdo->prepare("UPDATE orders SET order_status = 'Cancelled', payment_status = 'failed' WHERE id = ?")->execute([$order_id]);
        } catch (Throwable $t) {}

        log_audit_action('payment_rzp_order_create_failed', 'orders', 'order', $order_id, "Razorpay API returned HTTP {$http_code}");
        set_flash('error', 'Razorpay भुगतान गेटवे से संपर्क करने में असमर्थ। कृपया पुनः प्रयास करें या कैश ऑन डिलीवरी (COD) चुनें।');
        header('Location: /checkout.php');
        exit;
    }

    $_SESSION['pending_payment_order_id'] = $order_id;
    $_SESSION['pending_payment_rzp_order_id'] = $rzp_order_id;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Processing Payment — Razorpay</title>
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
      <link rel="stylesheet" href="/assets/css/tailwind.css">
    </head>
    <body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-xl max-w-md w-full text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto text-xl font-bold animate-pulse">
          ⚡
        </div>
        <h3 class="text-xl font-bold text-gray-900">Redirecting to Razorpay Gateway...</h3>
        <p class="text-xs text-gray-500">Order #<?= htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8') ?> • Amount: <?= format_price($final_total) ?></p>
        <button id="rzp-button" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700">Click if popup does not open</button>
      </div>

      <form id="rzp-form" action="/payments/razorpay_verify.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="<?= htmlspecialchars($rzp_order_id, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
      </form>

      <script>
      var options = {
          "key": "<?= htmlspecialchars($key_id, ENT_QUOTES, 'UTF-8') ?>",
          "amount": "<?= $amount_paisa ?>",
          "currency": "INR",
          "name": "<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>",
          "description": "Order #<?= htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars(APP_COMPANY, ENT_QUOTES, 'UTF-8') ?>",
          "image": "/logo.png",
          <?php if (!empty($rzp_order_id)): ?>
          "order_id": "<?= htmlspecialchars($rzp_order_id, ENT_QUOTES, 'UTF-8') ?>",
          <?php endif; ?>
          "handler": function (response){
              document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
              document.getElementById('razorpay_order_id').value = response.razorpay_order_id || '<?= htmlspecialchars($rzp_order_id, ENT_QUOTES, 'UTF-8') ?>';
              document.getElementById('razorpay_signature').value = response.razorpay_signature || '';
              document.getElementById('rzp-form').submit();
          },
          "prefill": {
              "name": "<?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8') ?>",
              "email": "<?= htmlspecialchars($customer_email, ENT_QUOTES, 'UTF-8') ?>",
              "contact": "<?= htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8') ?>"
          },
          "theme": {
              "color": "#1b4332"
          }
      };
      var rzp1 = new Razorpay(options);
      document.getElementById('rzp-button').onclick = function(e){
          rzp1.open();
          e.preventDefault();
      }
      window.onload = function() {
          rzp1.open();
      }
      </script>
    </body>
    </html>
    <?php
    exit;
}

// 3. PAYU GATEWAY REDIRECT FLOW
if ($payment_method === 'payu') {
    $merchant_key = defined('PAYU_MERCHANT_KEY') ? PAYU_MERCHANT_KEY : '';
    $salt = defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : '';
    $payu_url = defined('PAYU_URL') ? PAYU_URL : 'https://test.payu.in/_payment';

    if (empty($merchant_key) || empty($salt)) {
        log_audit_action('payment_payu_unconfigured', 'orders', "PayU keys missing for order #{$order_id}");
        set_flash('error', 'PayU गेटवे कॉन्फ़िगरेशन अनुपलब्ध है। कृपया कैश ऑन डिलीवरी (COD) चुनें।');
        header('Location: /checkout.php');
        exit;
    }

    $txnid = $order_number;
    $amount_str = number_format($final_total, 2, '.', '');
    $productinfo = "Order $order_number from GaunGaliHat";
    $firstname = $customer_name;
    $email = $customer_email ?: 'customer@ganvgalihat.com';
    $phone = $customer_phone;
    $udf1 = (string)$order_id;

    $base_site_url = defined('SITE_URL') ? SITE_URL : 'https://gaungalihat.womenvission.in';
    $surl = $base_site_url . '/payments/payu_callback.php';
    $furl = $base_site_url . '/payments/payu_callback.php';

    // Generate PayU SHA-512 Hash Sequence:
    // sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
    $hashSequence = "$merchant_key|$txnid|$amount_str|$productinfo|$firstname|$email|$udf1||||||||||$salt";
    $hash = strtolower(hash('sha512', $hashSequence));

    $_SESSION['pending_payment_order_id'] = $order_id;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Redirecting to PayU...</title>
      <link rel="stylesheet" href="/assets/css/tailwind.css">
    </head>
    <body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-xl max-w-md w-full text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-brand-green flex items-center justify-center mx-auto text-xl font-bold animate-pulse">
          🔒
        </div>
        <h3 class="text-xl font-bold text-gray-900">Connecting to PayU Money Gateway...</h3>
        <p class="text-xs text-gray-500">Amount: <?= format_price($final_total) ?></p>
        <p class="text-[11px] text-gray-400">Please do not refresh or press back button.</p>
      </div>

      <form id="payuForm" action="<?= htmlspecialchars($payu_url, ENT_QUOTES, 'UTF-8') ?>" method="POST">
        <input type="hidden" name="key" value="<?= htmlspecialchars($merchant_key, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="txnid" value="<?= htmlspecialchars($txnid, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount_str, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="firstname" value="<?= htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="productinfo" value="<?= htmlspecialchars($productinfo, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="udf1" value="<?= htmlspecialchars($udf1, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="surl" value="<?= htmlspecialchars($surl, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="furl" value="<?= htmlspecialchars($furl, ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="service_provider" value="payu_paisa" />
      </form>

      <script>
        window.onload = function() {
          document.getElementById('payuForm').submit();
        };
      </script>
    </body>
    </html>
    <?php
    exit;
}
