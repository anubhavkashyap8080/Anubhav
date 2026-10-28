<?php
/**
 * GaunGaliHat Account Email Verification Handler (Item 10)
 */

require_once __DIR__ . '/includes/functions.php';

$token = trim((string)($_GET['token'] ?? ''));
$email = trim((string)($_GET['email'] ?? ''));

if (empty($token) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'अमान्य सत्यापन लिंक। कृपया पुनः प्रयास करें। (Invalid verification token)');
    header('Location: /account.php');
    exit;
}

try {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        SELECT id, name, email, email_verified_at, email_verification_expires 
        FROM users 
        WHERE email = ? AND email_verification_token = ?
        LIMIT 1
    ");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash('error', 'यह सत्यापन लिंक अमान्य है या पहले ही उपयोग किया जा चुका है। (Invalid or consumed token)');
        header('Location: /account.php');
        exit;
    }

    if (!empty($user['email_verification_expires']) && strtotime($user['email_verification_expires']) < time()) {
        set_flash('error', 'सत्यापन लिंक समाप्त (Expired) हो गया है। कृपया अपने खाते से नया लिंक भेजें।');
        header('Location: /account.php');
        exit;
    }

    // Mark verified
    $upd = $pdo->prepare("
        UPDATE users 
        SET email_verified_at = ?, email_verification_token = NULL, email_verification_expires = NULL 
        WHERE id = ?
    ");
    $upd->execute([$now, $user['id']]);

    log_audit_action('email_verified', 'auth', 'user', $user['id'], 'Email verified: ' . $email);
    set_flash('success', 'बधाई हो! आपका ईमेल पता (' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . ') सफलतापूर्वक सत्यापित हो गया है।');
    
    if (is_logged_in()) {
        header('Location: /account.php');
    } else {
        header('Location: /login.php');
    }
    exit;
} catch (Throwable $e) {
    error_log("[Email Verification Error] " . $e->getMessage());
    set_flash('error', 'सत्यापन के दौरान एक तकनीकी समस्या आई। कृपया पुनः प्रयास करें।');
    header('Location: /account.php');
    exit;
}
