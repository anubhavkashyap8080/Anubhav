<?php
/**
 * GaunGaliHat Administrative Impersonation Gateway
 * Allows Super Admin and Admin to inspect any user / seller account directly
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Only authorized administrators or active impersonators can use this gateway
if (!is_admin() && empty($_SESSION['impersonator_id'])) {
    set_flash('error', 'प्रशासनिक विशेषाधिकार आवश्यक हैं। (Administrative clearance required)');
    header('Location: /login.php');
    exit;
}

$action = clean_input($_GET['action'] ?? 'login_as');

// 1. EXIT IMPERSONATION: Restore original administrator session
if ($action === 'exit') {
    if (!empty($_SESSION['impersonator_id'])) {
        $orig_id = intval($_SESSION['impersonator_id']);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$orig_id]);
        $orig_admin = $stmt->fetch();

        if ($orig_admin) {
            $_SESSION['user_id'] = $orig_admin['id'];
            $_SESSION['user_name'] = $orig_admin['name'];
            $_SESSION['user_role'] = $orig_admin['role'];
            $_SESSION['user_email'] = $orig_admin['email'];

            unset($_SESSION['impersonator_id']);
            unset($_SESSION['impersonator_name']);
            unset($_SESSION['impersonator_role']);

            set_flash('success', 'नमस्ते ' . htmlspecialchars($orig_admin['name']) . ', आप केंद्रीय प्रशासनिक नियंत्रण में वापस लौट आए हैं।');
            header('Location: /admin/index.php');
            exit;
        }
    }
    header('Location: /admin/index.php');
    exit;
}

// 2. LOGIN AS USER / SELLER
if ($action === 'login_as') {
    $target_user_id = intval($_GET['user_id'] ?? 0);
    if ($target_user_id <= 0) {
        set_flash('error', 'अमान्य खाता पहचानकर्ता (Invalid User ID).');
        header('Location: /admin/users.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch();

    if (!$target_user) {
        set_flash('error', 'लक्षित उपयोगकर्ता खाता नहीं मिला।');
        header('Location: /admin/users.php');
        exit;
    }

    // Save current admin identity if not already stored
    if (empty($_SESSION['impersonator_id'])) {
        $_SESSION['impersonator_id'] = $_SESSION['user_id'];
        $_SESSION['impersonator_name'] = $_SESSION['user_name'];
        $_SESSION['impersonator_role'] = $_SESSION['user_role'];
    }

    // Switch session to target user
    $_SESSION['user_id'] = $target_user['id'];
    $_SESSION['user_name'] = $target_user['name'];
    $_SESSION['user_role'] = $target_user['role'];
    $_SESSION['user_email'] = $target_user['email'];

    set_flash('success', 'सुरक्षित प्रशासनिक स्विच: अब आप ' . htmlspecialchars($target_user['name']) . ' (' . htmlspecialchars($target_user['role']) . ') के रूप में कार्य कर रहे हैं।');

    if ($target_user['role'] === 'vendor') {
        header('Location: /seller/dashboard.php');
        exit;
    } elseif (in_array($target_user['role'], ['admin', 'super_admin'])) {
        header('Location: /admin/index.php');
        exit;
    } else {
        header('Location: /account.php');
        exit;
    }
}
