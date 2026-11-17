<?php
/**
 * GanvGaliHat Razorpay Verification Endpoint
 * Security Hardened:
 * 1. Cryptographic HMAC-SHA256 signature verification (order_id|payment_id, secret)
 * 2. Session / user ownership confirmation
 * 3. Exact amount confirmation
 * 4. Rejection of already-paid orders
 * 5. Strict POST + CSRF validation
 */

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash('error', 'Method Not Allowed: Invalid request method.');
    header('Location: /checkout.php');
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    log_audit_action('payment_csrf_failed', 'orders', 'CSRF verification failed on Razorpay verify endpoint');
    set_flash('error', 'Security token expired. Please refresh the page and try again.');
    header('Location: /checkout.php');
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$razorpay_payment_id = trim((string)($_POST['razorpay_payment_id'] ?? ''));
$razorpay_order_id   = trim((string)($_POST['razorpay_order_id'] ?? ''));
$razorpay_signature  = trim((string)($_POST['razorpay_signature'] ?? ''));

if ($order_id <= 0 || empty($razorpay_payment_id)) {
    log_audit_action('payment_verification_missing_params', 'orders', "Order #{$order_id} missing transaction tokens");
    set_flash('error', 'Payment verification failed: Missing transaction tokens.');
    header('Location: /checkout.php');
    exit;
}

try {
    // 1. Fetch Order Record
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        log_audit_action('payment_order_not_found', 'orders', "Attempted verification on non-existent order #{$order_id}");
        set_flash('error', 'Order reference not found in system.');
        header('Location: /cart.php');
        exit;
    }

    // 2. Reject already-paid orders to prevent replay attacks
    if ($order['payment_status'] === 'completed') {
        log_audit_action('payment_replay_blocked', 'orders', "Blocked duplicate payment attempt on already paid order #{$order_id}");
        set_flash('info', 'This order has already been paid successfully.');
        $token = $order['order_access_token'] ?? '';
        header('Location: /order-success.php?id=' . $order_id . ($token ? '&token=' . urlencode($token) : ''));
        exit;
    }

    // Security Audit C3: Ensure razorpay_payment_id is unique across all orders (prevents cross-order replay)
    $chk_dup = $pdo->prepare("SELECT id FROM orders WHERE payment_txn_id = ? AND id != ? LIMIT 1");
    $chk_dup->execute([$razorpay_payment_id, $order_id]);
    if ($chk_dup->fetch()) {
        log_audit_action('payment_replay_blocked', 'orders', "Razorpay payment ID {$razorpay_payment_id} was already used on another order");
        set_flash('error', 'Security warning: This payment identifier has already been used on another order.');
        header('Location: /checkout.php');
        exit;
    }

    // 3. Confirm the order belongs to the current session or logged in user
    $current_user = get_logged_in_user();
    $session_order_id = (int)($_SESSION['pending_payment_order_id'] ?? 0);

    $is_authorized = false;
    if ($current_user && (int)$order['user_id'] === (int)$current_user['id']) {
        $is_authorized = true;
    } elseif ($session_order_id === (int)$order['id']) {
        $is_authorized = true;
    }

    if (!$is_authorized) {
        log_audit_action(
            'payment_ownership_violation',
            'orders',
            "Unauthorized payment verification attempt on order #{$order_id} from IP " . get_client_ip()
        );
        set_flash('error', 'Security violation: You are not authorized to verify this order.');
        header('Location: /checkout.php');
        exit;
    }

    // 4. Require gateway_order_id + razorpay_signature and Verify Cryptographic HMAC
    $key_id = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
    $key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '';
    if (empty($key_id) || empty($key_secret)) {
        error_log("CRITICAL: Razorpay API keys are not configured.");
        set_flash('error', 'Gateway configuration error. Please contact customer support.');
        header('Location: /checkout.php');
        exit;
    }

    if (empty($razorpay_order_id) && !empty($order['gateway_order_id'])) {
        $razorpay_order_id = $order['gateway_order_id'];
    }

    if (empty($razorpay_order_id) || empty($razorpay_signature)) {
        log_audit_action('payment_missing_signature', 'orders', 'order', $order_id, 'Missing gateway_order_id or signature token');
        set_flash('error', 'Missing gateway order ID or signature token. Transaction rejected.');
        header('Location: /checkout.php');
        exit;
    }

    $signature_payload = $razorpay_order_id . '|' . $razorpay_payment_id;
    $expected_signature = hash_hmac('sha256', $signature_payload, $key_secret);

    if (!hash_equals($expected_signature, $razorpay_signature)) {
        log_audit_action(
            'payment_signature_mismatch',
            'orders',
            'order',
            $order_id,
            'Razorpay cryptographic signature mismatch'
        );
        set_flash('error', 'Cryptographic signature verification failed. Transaction rejected.');
        header('Location: /checkout.php');
        exit;
    }

    // 5. Strict API Verification: Check payment details & amount against Razorpay API (Fail-Closed)
    $ch = curl_init('https://api.razorpay.com/v1/payments/' . urlencode($razorpay_payment_id));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $api_res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || empty($api_res)) {
        log_audit_action(
            'payment_api_verification_failed',
            'orders',
            'order',
            $order_id,
            "Razorpay API payment fetch failed with HTTP {$http_code}"
        );
        set_flash('error', 'Payment verification failed via Razorpay API (HTTP ' . intval($http_code) . '). Please contact support.');
        header('Location: /checkout.php');
        exit;
    }

    $payment_data = json_decode($api_res, true);
    if (!is_array($payment_data)) {
        log_audit_action('payment_api_invalid_response', 'orders', 'order', $order_id, 'Invalid JSON returned from Razorpay API');
        set_flash('error', 'Invalid gateway response received.');
        header('Location: /checkout.php');
        exit;
    }

    $expected_amount_paise = (int)round(((float)$order['total_amount']) * 100);
    $paid_amount_paise = (int)($payment_data['amount'] ?? 0);

    if ($paid_amount_paise < $expected_amount_paise) {
        log_audit_action(
            'payment_amount_tampering',
            'orders',
            'order',
            $order_id,
            'Paid amount is less than order total'
        );
        set_flash('error', 'Payment amount discrepancy detected. Please contact support.');
        header('Location: /checkout.php');
        exit;
    }

    // Security Audit C3: Verify gateway order id matches
    if (!empty($order['gateway_order_id']) && !empty($payment_data['order_id']) && $payment_data['order_id'] !== $order['gateway_order_id']) {
        log_audit_action('payment_order_id_mismatch', 'orders', 'order', $order_id, "Gateway order ID mismatch: expected {$order['gateway_order_id']}, got {$payment_data['order_id']}");
        set_flash('error', 'Gateway order ID discrepancy detected.');
        header('Location: /checkout.php');
        exit;
    }

    // Security Audit C3: Verify currency is strictly INR
    if (strtoupper($payment_data['currency'] ?? '') !== 'INR') {
        log_audit_action('payment_currency_mismatch', 'orders', 'order', $order_id, "Currency mismatch: " . ($payment_data['currency'] ?? ''));
        set_flash('error', 'Invalid currency. Only INR transactions are accepted.');
        header('Location: /checkout.php');
        exit;
    }

    $rzp_status = strtolower($payment_data['status'] ?? '');
    // Security Audit C3: Accept only fully captured payments
    if ($rzp_status !== 'captured') {
        log_audit_action(
            'payment_invalid_status',
            'orders',
            'order',
            $order_id,
            "Razorpay payment status not captured (status: {$rzp_status})"
        );
        set_flash('error', 'Payment was not fully captured at gateway (Status: ' . htmlspecialchars($rzp_status, ENT_QUOTES, 'UTF-8') . ').');
        header('Location: /checkout.php');
        exit;
    }

    // 6. Ensure order access token exists for secure guest verification
    $access_token = $order['order_access_token'] ?? '';
    if (empty($access_token)) {
        $access_token = bin2hex(random_bytes(32));
    }

    // 7. Update Order to Completed (Conditional on payment_status != 'completed' to prevent replay)
    $update = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'completed', 
            order_status = 'Confirmed', 
            payment_txn_id = ?,
            order_access_token = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND payment_status != 'completed'
    ");
    $update->execute([$razorpay_payment_id, $access_token, $order_id]);

    if ($update->rowCount() === 0) {
        log_audit_action('payment_replay_blocked', 'orders', 'order', $order_id, 'Duplicate replay attempt prevented on completed order');
        set_flash('info', 'This order has already been updated.');
        header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
        exit;
    }

    // Security Audit H6: Decrement product stock upon confirmed payment capture
    try {
        $stmt_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order_id]);
        while ($oi = $stmt_items->fetch()) {
            $p_qty = max(1, (int)$oi['quantity']);
            $p_id = (int)$oi['product_id'];
            $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$p_qty, $p_id]);
            $pdo->prepare("UPDATE products SET stock_status = 'Out of Stock' WHERE id = ? AND stock <= 0")->execute([$p_id]);
        }
    } catch (Throwable $e_stk) {
        error_log("Error decrementing stock on payment capture: " . $e_stk->getMessage());
    }

    // Clear cart and pending session keys
    clear_cart();
    unset($_SESSION['pending_payment_order_id']);
    unset($_SESSION['pending_payment_rzp_order_id']);

    log_audit_action('payment_success_razorpay', 'orders', "Order #{$order_id} paid successfully via Razorpay (Txn: {$razorpay_payment_id})");
    set_flash('success', 'Razorpay payment received successfully! Order #' . htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'));
    header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
    exit;

} catch (Exception $e) {
    error_log("Razorpay verification DB error: " . $e->getMessage());
    set_flash('error', 'A database error occurred during payment verification. Please contact support.');
    header('Location: /checkout.php');
    exit;
}
