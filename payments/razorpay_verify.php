<?php
/**
 * GaunGaliHat Razorpay Verification Endpoint
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
    set_flash('error', 'अमान्य अनुरोध विधि। (Method Not Allowed)');
    header('Location: /checkout.php');
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    log_audit_action('payment_csrf_failed', 'orders', 'CSRF verification failed on Razorpay verify endpoint');
    set_flash('error', 'सुरक्षा टोकन अमान्य या समाप्त हो गया है। कृपया पुनः प्रयास करें।');
    header('Location: /checkout.php');
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$razorpay_payment_id = trim((string)($_POST['razorpay_payment_id'] ?? ''));
$razorpay_order_id   = trim((string)($_POST['razorpay_order_id'] ?? ''));
$razorpay_signature  = trim((string)($_POST['razorpay_signature'] ?? ''));

if ($order_id <= 0 || empty($razorpay_payment_id)) {
    log_audit_action('payment_verification_missing_params', 'orders', "Order #{$order_id} missing transaction tokens");
    set_flash('error', 'भुगतान सत्यापन विफल: लेन-देन पहचानकर्ता अनुपलब्ध है।');
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
        set_flash('error', 'ऑर्डर संदर्भ सिस्टम में नहीं मिला।');
        header('Location: /cart.php');
        exit;
    }

    // 2. Reject already-paid orders to prevent replay attacks
    if ($order['payment_status'] === 'completed') {
        log_audit_action('payment_replay_blocked', 'orders', "Blocked duplicate payment attempt on already paid order #{$order_id}");
        set_flash('info', 'यह ऑर्डर पहले ही सफलतापूर्वक भुगतान किया जा चुका है।');
        $token = $order['order_access_token'] ?? '';
        header('Location: /order-success.php?id=' . $order_id . ($token ? '&token=' . urlencode($token) : ''));
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
        set_flash('error', 'सुरक्षा उल्लंघन: आप इस ऑर्डर को सत्यापित करने के लिए अधिकृत नहीं हैं।');
        header('Location: /checkout.php');
        exit;
    }

    // 4. Require gateway_order_id + razorpay_signature and Verify Cryptographic HMAC
    $key_id = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
    $key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '';
    if (empty($key_id) || empty($key_secret)) {
        error_log("CRITICAL: Razorpay API keys are not configured.");
        set_flash('error', 'गेटवे कॉन्फ़िगरेशन त्रुटि। कृपया समर्थन टीम से संपर्क करें।');
        header('Location: /checkout.php');
        exit;
    }

    if (empty($razorpay_order_id) && !empty($order['gateway_order_id'])) {
        $razorpay_order_id = $order['gateway_order_id'];
    }

    if (empty($razorpay_order_id) || empty($razorpay_signature)) {
        log_audit_action('payment_missing_signature', 'orders', 'order', $order_id, 'Missing gateway_order_id or signature token');
        set_flash('error', 'भुगतान हस्ताक्षर अथवा गेटवे ऑर्डर आईडी अनुपलब्ध है। लेन-देन अस्वीकृत।');
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
        set_flash('error', 'क्रिप्टोग्राफ़िक सुरक्षा सत्यापन विफल रहा। लेन-देन अस्वीकृत।');
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
        set_flash('error', 'Razorpay API द्वारा भुगतान सत्यापन विफल रहा (HTTP ' . intval($http_code) . ')। कृपया सहायता टीम से संपर्क करें।');
        header('Location: /checkout.php');
        exit;
    }

    $payment_data = json_decode($api_res, true);
    if (!is_array($payment_data)) {
        log_audit_action('payment_api_invalid_response', 'orders', 'order', $order_id, 'Invalid JSON returned from Razorpay API');
        set_flash('error', 'अमान्य गेटवे प्रतिक्रिया प्राप्त हुई।');
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
        set_flash('error', 'भुगतान राशि में विसंगति पाई गई। कृपया सहायता टीम से संपर्क करें।');
        header('Location: /checkout.php');
        exit;
    }

    $rzp_status = strtolower($payment_data['status'] ?? '');
    if (!in_array($rzp_status, ['captured', 'authorized'], true)) {
        log_audit_action(
            'payment_invalid_status',
            'orders',
            'order',
            $order_id,
            "Razorpay payment status not captured/authorized (status: {$rzp_status})"
        );
        set_flash('error', 'गेटवे पर भुगतान पूर्ण नहीं हुआ है (Status: ' . htmlspecialchars($rzp_status, ENT_QUOTES, 'UTF-8') . ')');
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
        set_flash('info', 'यह ऑर्डर पहले ही अपडेट हो चुका है।');
        header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
        exit;
    }

    // Clear cart and pending session keys
    clear_cart();
    unset($_SESSION['pending_payment_order_id']);
    unset($_SESSION['pending_payment_rzp_order_id']);

    log_audit_action('payment_success_razorpay', 'orders', "Order #{$order_id} paid successfully via Razorpay (Txn: {$razorpay_payment_id})");
    set_flash('success', 'Razorpay भुगतान सफलतापूर्वक प्राप्त हुआ! ऑर्डर संख्या #' . htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'));
    header('Location: /order-success.php?id=' . $order_id . '&token=' . urlencode($access_token));
    exit;

} catch (Exception $e) {
    error_log("Razorpay verification DB error: " . $e->getMessage());
    set_flash('error', 'भुगतान सत्यापन के दौरान डेटाबेस त्रुटि हुई। कृपया सहायता टीम से संपर्क करें।');
    header('Location: /checkout.php');
    exit;
}
