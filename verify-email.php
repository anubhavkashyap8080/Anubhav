<?php
/**
 * GanvGaliHat Account Email Verification Handler
 */

require_once __DIR__ . '/includes/functions.php';

$token = trim((string)($_GET['token'] ?? ''));
$email = trim((string)($_GET['email'] ?? ''));

if (empty($token) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Invalid verification link. Please check your email or request a new one.');
    header('Location: /login.php');
    exit;
}

try {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, status, email_verified_at, email_verification_token, email_verification_expires 
        FROM users 
        WHERE email = ? AND email_verification_token IS NOT NULL
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $token_hash = hash('sha256', $token);
    $is_valid_token = false;
    if ($user && !empty($user['email_verification_token'])) {
        if (hash_equals((string)$user['email_verification_token'], $token_hash)) {
            $is_valid_token = true;
        } elseif (hash_equals((string)$user['email_verification_token'], $token)) {
            // Backward compatibility for unhashed legacy tokens
            $is_valid_token = true;
        }
    }

    if (!$is_valid_token) {
        set_flash('error', 'This verification link is invalid or has already been used.');
        header('Location: /login.php');
        exit;
    }

    if (!empty($user['email_verification_expires']) && strtotime($user['email_verification_expires']) < time()) {
        set_flash('error', 'This verification link has expired. Please sign in to request a fresh verification link.');
        header('Location: /login.php');
        exit;
    }

    // Mark user active and email/phone verified
    $upd = $pdo->prepare("
        UPDATE users 
        SET status = 'active',
            email_verified_at = ?,
            phone_verified_at = COALESCE(phone_verified_at, ?),
            email_verification_token = NULL,
            email_verification_otp = NULL,
            phone_verification_otp = NULL,
            email_verification_expires = NULL,
            phone_verification_expires = NULL
        WHERE id = ?
    ");
    $upd->execute([$now, $now, $user['id']]);

    // Send Welcome Email
    send_welcome_email($user['id'], $user['email'], $user['name']);

    // Log user in automatically
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'] ?? 'customer';
    $_SESSION['user_email'] = $user['email'];

    unset($_SESSION['pending_verification_user_id'], $_SESSION['pending_verification_email'], $_SESSION['pending_verification_name']);

    log_audit_action('email_verified', 'auth', 'user', $user['id'], 'Email verified: ' . $email);
    set_flash('success', 'Congratulations! Your email (' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . ') has been verified successfully.');
    
    header('Location: /account.php');
    exit;
} catch (Throwable $e) {
    error_log("[Email Verification Error] " . $e->getMessage());
    set_flash('error', 'A technical issue occurred during verification. Please try again.');
    header('Location: /login.php');
    exit;
}
