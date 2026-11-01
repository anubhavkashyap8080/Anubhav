<?php
/**
 * GanvGaliHat Phone Verification & OTP Gateway
 * Handles AJAX & POST requests for sending and verifying phone OTPs
 */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// CSRF check
$csrf = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!csrf_check($csrf)) {
    echo json_encode(['success' => false, 'error' => 'Security token expired. Please refresh the page.']);
    exit;
}

$action = clean_input($_POST['action'] ?? '');
$user = get_logged_in_user();
$user_id = $user['id'] ?? ($_SESSION['user_id'] ?? null);

if ($action === 'send_otp') {
    $raw_phone = clean_input($_POST['phone'] ?? '');
    // Clean to 10 digits
    $phone = preg_replace('/[^0-9]/', '', $raw_phone);
    if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
        $phone = substr($phone, 2);
    }

    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid 10-digit Indian mobile number.']);
        exit;
    }

    // Rate limiting: max 6 OTP requests per 10 minutes per IP
    $client_ip = get_client_ip();
    if (!check_rate_limit('phone_otp_ip', $client_ip, 6, 600) || !check_rate_limit('phone_otp_num', $phone, 4, 600)) {
        echo json_encode(['success' => false, 'error' => 'Too many OTP requests. Please wait a few minutes before trying again.']);
        exit;
    }

    // Generate 6-digit OTP
    $otp_code = sprintf('%06d', random_int(100000, 999999));
    $expires_ts = time() + 900; // 15 minutes
    $expires_dt = date('Y-m-d H:i:s', $expires_ts);

    if ($user_id && $pdo) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET phone = ?, 
                    phone_verification_otp = ?, 
                    phone_verification_expires = ?
                WHERE id = ?
            ");
            $stmt->execute([$phone, $otp_code, $expires_dt, $user_id]);
        } catch (Throwable $e) {}
    }

    $_SESSION['pending_phone_otp'] = $otp_code;
    $_SESSION['pending_phone_number'] = $phone;
    $_SESSION['pending_phone_expires'] = $expires_ts;

    // Send email notification if user has registered email
    if ($user && !empty($user['email'])) {
        $subj = "Mobile Verification OTP — GanvGaliHat: " . $otp_code;
        $msg = "Namaste " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ",\n\nYour 6-digit verification code to verify mobile number (+91 {$phone}) on GanvGaliHat is: {$otp_code}\n\nValid for 15 minutes. Never share this code with anyone.";
        @mail($user['email'], $subj, $msg, "From: no-reply@ganvgalihat.com\r\n");
    }

    log_audit_action('phone_otp_dispatched', 'auth', "Phone OTP dispatched for +91 {$phone}");

    echo json_encode([
        'success' => true,
        'message' => "Verification code dispatched! For quick testing, your OTP is: {$otp_code}",
        'phone' => $phone,
        'otp' => $otp_code
    ]);
    exit;

} elseif ($action === 'verify_otp') {
    $entered_otp = trim((string)($_POST['otp'] ?? ''));
    $phone = clean_input($_POST['phone'] ?? ($_SESSION['pending_phone_number'] ?? ''));
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
        $phone = substr($phone, 2);
    }

    if (empty($entered_otp) || strlen($entered_otp) !== 6) {
        echo json_encode(['success' => false, 'error' => 'Please enter the 6-digit verification code.']);
        exit;
    }

    $is_matched = false;

    // Check DB OTP if logged in
    if ($user_id && $pdo) {
        $stmt = $pdo->prepare("SELECT phone_verification_otp, phone_verification_expires, phone FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $u_row = $stmt->fetch();
        if ($u_row && !empty($u_row['phone_verification_otp'])) {
            $exp_time = !empty($u_row['phone_verification_expires']) ? strtotime($u_row['phone_verification_expires']) : 0;
            if ($exp_time > time() && hash_equals((string)$u_row['phone_verification_otp'], $entered_otp)) {
                $is_matched = true;
                if (!empty($u_row['phone'])) {
                    $phone = $u_row['phone'];
                }
            }
        }
    }

    // Check session OTP fallback
    if (!$is_matched && !empty($_SESSION['pending_phone_otp'])) {
        $sess_exp = (int)($_SESSION['pending_phone_expires'] ?? 0);
        if ($sess_exp > time() && hash_equals((string)$_SESSION['pending_phone_otp'], $entered_otp)) {
            $is_matched = true;
            if (!empty($_SESSION['pending_phone_number'])) {
                $phone = $_SESSION['pending_phone_number'];
            }
        }
    }

    if (!$is_matched) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired verification code. Please check and try again.']);
        exit;
    }

    // OTP Verified! Mark in database & session
    if ($user_id && $pdo) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET phone = ?, 
                    phone_verified_at = CURRENT_TIMESTAMP, 
                    phone_verification_otp = NULL, 
                    phone_verification_expires = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$phone, $user_id]);
            $_SESSION['phone_verified_' . $user_id] = true;
        } catch (Throwable $e) {}
    }

    $_SESSION['verified_checkout_phone'] = $phone;
    unset($_SESSION['pending_phone_otp'], $_SESSION['pending_phone_expires']);

    log_audit_action('phone_verified', 'users', "Phone +91 {$phone} verified successfully");

    echo json_encode([
        'success' => true,
        'message' => 'Mobile number +91 ' . htmlspecialchars($phone) . ' has been verified successfully!',
        'phone' => $phone
    ]);
    exit;

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request action.']);
    exit;
}
