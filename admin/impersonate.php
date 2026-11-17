<?php
/**
 * GanvGaliHat Administrative Impersonation Gateway
 * Security Hardened:
 * 1. Strictly restricted to 'super_admin' role only
 * 2. Mandatory POST + CSRF validation (all GET endpoints disabled)
 * 3. Never permits impersonating another 'super_admin'
 * 4. Comprehensive audit logging for impersonate_start and impersonate_stop
 * 5. Session regeneration on privilege switch
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// 1. Strict POST Request Method Enforcement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash('error', 'Method Not Allowed: Only POST requests are accepted.');
    header('Location: /admin/users.php');
    exit;
}

// 2. Strict CSRF Token Verification
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    log_audit_action('impersonate_csrf_failed', 'users', 'CSRF token mismatch on impersonation gateway');
    set_flash('error', 'Security token expired. Please refresh the page and try again.');
    header('Location: /admin/users.php');
    exit;
}

$action = trim((string)($_POST['action'] ?? ''));

// =========================================================================
// ACTION: EXIT IMPERSONATION (Restore Original Super Admin Session)
// =========================================================================
if ($action === 'exit') {
    if (empty($_SESSION['impersonator_id'])) {
        header('Location: /admin/index.php');
        exit;
    }

    $orig_id = intval($_SESSION['impersonator_id']);
    $current_impersonated_id = intval($_SESSION['user_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'super_admin' AND status = 'active' LIMIT 1");
    $stmt->execute([$orig_id]);
    $orig_admin = $stmt->fetch();

    if ($orig_admin) {
        // Regenerate Session ID to prevent session hijacking
        session_regenerate_id(true);

        $_SESSION['user_id'] = $orig_admin['id'];
        $_SESSION['user_name'] = $orig_admin['name'];
        $_SESSION['user_role'] = $orig_admin['role'];
        $_SESSION['user_email'] = $orig_admin['email'];
        $_SESSION['last_activity'] = time();

        unset($_SESSION['impersonator_id']);
        unset($_SESSION['impersonator_name']);
        unset($_SESSION['impersonator_role']);

        log_audit_action(
            'impersonate_stop',
            'users',
            "Super Admin #{$orig_admin['id']} ({$orig_admin['email']}) exited impersonation of user #{$current_impersonated_id}"
        );

        set_flash('success', 'Welcome back ' . htmlspecialchars($orig_admin['name'], ENT_QUOTES, 'UTF-8') . ', you have returned to Super Admin control.');
        header('Location: /admin/users.php');
        exit;
    } else {
        log_audit_action('impersonate_exit_failed', 'users', "Original super admin #{$orig_id} not found or inactive on exit attempt");
        session_destroy();
        header('Location: /login.php');
        exit;
    }
}

// =========================================================================
// ACTION: LOGIN AS USER / SELLER (Start Impersonation)
// =========================================================================
if ($action === 'login_as') {
    // Only super_admin may initiate an impersonation session
    require_super_admin();

    $target_user_id = intval($_POST['target_user_id'] ?? 0);
    if ($target_user_id <= 0) {
        set_flash('error', 'Invalid account identifier.');
        header('Location: /admin/users.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch();

    if (!$target_user) {
        set_flash('error', 'Target user account not found.');
        header('Location: /admin/users.php');
        exit;
    }

    // SECURITY RULE: Never allow impersonating another super_admin!
    if ($target_user['role'] === 'super_admin') {
        log_audit_action(
            'impersonate_super_admin_blocked',
            'users',
            "Super Admin #{$_SESSION['user_id']} was blocked from impersonating another Super Admin #{$target_user_id}"
        );
        set_flash('error', 'Security restriction: Impersonating another Super Admin is not permitted.');
        header('Location: /admin/users.php');
        exit;
    }

    if ($target_user['id'] === $_SESSION['user_id']) {
        set_flash('error', 'You are already active in your own Super Admin account.');
        header('Location: /admin/users.php');
        exit;
    }

    // Save original super admin identity
    $current_admin_id = $_SESSION['user_id'];
    $current_admin_name = $_SESSION['user_name'];
    $current_admin_role = $_SESSION['user_role'];

    // Switch session context to target user
    session_regenerate_id(true);

    $_SESSION['impersonator_id']   = $current_admin_id;
    $_SESSION['impersonator_name'] = $current_admin_name;
    $_SESSION['impersonator_role'] = $current_admin_role;

    $_SESSION['user_id']       = $target_user['id'];
    $_SESSION['user_name']     = $target_user['name'];
    $_SESSION['user_role']     = $target_user['role'];
    $_SESSION['user_email']    = $target_user['email'];
    $_SESSION['last_activity'] = time();

    log_audit_action(
        'impersonate_start',
        'users',
        "Super Admin #{$current_admin_id} started impersonating {$target_user['role']} #{$target_user['id']} ({$target_user['email']})"
    );

    set_flash('success', 'Admin switch active: You are now browsing as ' . htmlspecialchars($target_user['name'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($target_user['role'], ENT_QUOTES, 'UTF-8') . ').');

    if ($target_user['role'] === 'vendor') {
        header('Location: /seller/dashboard.php');
        exit;
    } elseif ($target_user['role'] === 'admin') {
        header('Location: /admin/index.php');
        exit;
    } else {
        header('Location: /account.php');
        exit;
    }
}

// Fallback
header('Location: /admin/users.php');
exit;
