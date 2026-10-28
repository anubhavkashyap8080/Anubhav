<?php
/**
 * GaunGaliHat PayU Gateway Callback Handler
 * Security Hardened:
 * 1. Strict POST only (rejects GET requests)
 * 2. Cryptographic SHA-512 Reverse Hash Verification (salt, status, udfs, email, name, product, amount, txnid, key)
 * 3. Never trusts status parameter without verified hash
 * 4. Exact order amount confirmation
 * 5. Replay attack rejection (already-paid orders)
 * 6. Cryptographically secure transaction ID fallback (no rand())
 */

require_once __DIR__ . '/../includes/functions.php';

// 1. Strict Method Restriction: Only accept POST from PayU gateway
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    log_audit_action('payu_get_rejected', 'orders', 'Rejected non-POST callback to PayU endpoint');
    set_flash('error', 'अमान्य अनुरोध विधि। केवल POST अनुरोध स्वीकार्य हैं।');
    header('Location: /checkout.php');
    exit;
}

$status      = strtolower(trim((string)($_POST['status'] ?? '')));
$firstname   = trim((string)($_POST['firstname'] ?? ''));
$amount      = trim((string)($_POST['amount'] ?? ''));
$txnid       = trim((string)($_POST['txnid'] ?? ''));
$posted_hash = strtolower(trim((string)($_POST['hash'] ?? '')));
$key         = trim((string)($_POST['key'] ?? ''));
$productinfo = trim((string)($_POST['productinfo'] ?? ''));
$email       = trim((string)($_POST['email'] ?? ''));
$udf1        = trim((string)($_POST['udf1'] ?? '')); // order_id
$udf2        = trim((string)($_POST['udf2'] ?? ''));
$udf3        = trim((string)($_POST['udf3'] ?? ''));
$udf4        = trim((string)($_POST['udf4'] ?? ''));
$udf5        = trim((string)($_POST['udf5'] ?? ''));
$udf6        = trim((string)($_POST['udf6'] ?? ''));
$udf7        = trim((string)($_POST['udf7'] ?? ''));
$udf8        = trim((string)($_POST['udf8'] ?? ''));
$udf9        = trim((string)($_POST['udf9'] ?? ''));
$udf10       = trim((string)($_POST['udf10'] ?? ''));

// Secure transaction ID (no rand() fallback)
$payu_money_id = trim((string)($_POST['mihpayid'] ?? $_POST['payuMoneyId'] ?? ''));
if (empty($payu_money_id)) {
    $payu_money_id = 'PAYU-' . bin2hex(random_bytes(8));
}

// 2. Cryptographic Reverse Hash Verification
$salt = defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : '';
if (empty($salt)) {
    error_log("CRITICAL: PAYU_MERCHANT_SALT is not configured.");
    set_flash('error', 'PayU गेटवे कॉन्फ़िगरेशन त्रुटि।');
    header('Location: /checkout.php');
    exit;
}

if (isset($_POST['additionalCharges']) && !empty($_POST['additionalCharges'])) {
    $additionalCharges = trim((string)$_POST['additionalCharges']);
    $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
} else {
    $retHashSeq = $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
}

$calculated_hash = strtolower(hash('sha512', $retHashSeq));

if (empty($posted_hash) || !hash_equals($calculated_hash, $posted_hash)) {
    log_audit_action(
        'payu_hash_verification_failed',
        'orders',
        'order',
        null,
        "PayU SHA-512 cryptographic hash mismatch for txnid {$txnid}"
    );
    set_flash('error', 'PayU सुरक्षा हैश सत्यापन विफल रहा। लेन-देन अस्वीकृत।');
    header('Location: /checkout.php');
    exit;
}

// 3. Find Order by txnid (Order Number) or udf1 (Order ID)
try {
    $order = null;
    if (!empty($txnid)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$txnid]);
        $order = $stmt->fetch();
    }
    if (!$order && !empty($udf1) && is_numeric($udf1)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([intval($udf1)]);
        $order = $stmt->fetch();
    }

    if (!$order) {
        log_audit_action('payu_order_not_found', 'orders', "PayU callback order not found for txnid {$txnid}");
        set_flash('error', 'ऑर्डर संदर्भ सिस्टम में नहीं मिला।');
        header('Location: /cart.php');
        exit;
    }

    $order_id = (int)$order['id'];

    // 4. Reject replay attack on already completed order
    if ($order['payment_status'] === 'completed') {
        log_audit_action('payu_duplicate_ignored', 'orders', "PayU callback ignored for already paid order #{$order_id}");
        set_flash('info', 'यह ऑर्डर पहले ही भुगतान हो चुका है।');
        $token = $order['order_access_token'] ?? '';
        header('Location: /order-success.php?id=' . $order_id . ($token ? '&token=' . urlencode($token) : ''));
        exit;
    }

    // 5. Verify Exact Amount
    $order_amount_expected = (float)$order['total_amount'];
    $paid_amount_float = (float)$amount;

    if (abs($order_amount_expected - $paid_amount_float) > 0.05) {
        log_audit_action(
            'payu_amount_mismatch',
            'orders',
            "PayU amount mismatch on order #{$order_id}. Expected: {$order_amount_expected}, Received: {$paid_amount_float}"
        );
        set_flash('error', 'भुगतान राशि में विसंगति पाई गई।');
        header('Location: /checkout.php');
        exit;
    }

    // 6. Process Verified Status
    if ($status === 'success') {
        // Ensure access token exists for secure order-success view
        $access_token = $order['order_access_token'] ?? '';
        if (empty($access_token)) {
            $access_token = bin2hex(random_bytes(32));
        }

        $update = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'completed', 
                order_status = 'Confirmed', 
                payment_txn_id = ?,
                order_access_token = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND payment_status != 'completed'
        ");
        $update->execute([$payu_money_id, $access_token, $order_id]);

        if ($update->rowCount() === 0) {
            log_audit_action('payu_replay_blocked', 'orders', 'order', $order_id, 'Duplicate replay attempt prevented on completed order');
            set_flash('info', 'यह ऑर्डर पहले ही अपडेट हो चुका है।');
            header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
            exit;
        }

        clear_cart();
        unset($_SESSION['pending_payment_order_id']);

        log_audit_action('payu_payment_success', 'orders', "Order #{$order_id} verified & completed via PayU (Txn: {$payu_money_id})");
        set_flash('success', 'PayU भुगतान सफलतापूर्वक प्राप्त हुआ! ऑर्डर संख्या #' . htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'));
        header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
        exit;

    } else {
        // Payment failed or was cancelled
        $update = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'failed', 
                order_status = 'Payment Failed',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $update->execute([$order_id]);

        log_audit_action('payu_payment_failure', 'orders', "PayU reported status '{$status}' for order #{$order_id}");
        set_flash('error', 'PayU लेन-देन पूर्ण नहीं हुआ अथवा रद्द कर दिया गया। (Status: ' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . ')');
        header('Location: /checkout.php');
        exit;
    }

} catch (Exception $e) {
    error_log("PayU callback database error: " . $e->getMessage());
    set_flash('error', 'भुगतान प्रसंस्करण के दौरान डेटाबेस त्रुटि हुई।');
    header('Location: /cart.php');
    exit;
}
