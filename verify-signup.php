<?php
/**
 * GaunGaliHat Mandatory Signup OTP & Email Verification Screen
 */

require_once __DIR__ . '/includes/functions.php';

// If already fully logged in and verified, redirect to account
if (is_logged_in()) {
    $current_user = get_logged_in_user();
    if ($current_user && ($current_user['status'] ?? '') === 'active') {
        header('Location: /account.php');
        exit;
    }
}

$pending_user_id = $_SESSION['pending_verification_user_id'] ?? null;
$error = '';
$user = null;

if ($pending_user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$pending_user_id]);
    $user = $stmt->fetch();
}

// Fallback: Check if email is passed via GET or POST
if (!$user && isset($_REQUEST['email'])) {
    $lookup_email = clean_input($_REQUEST['email']);
    if (filter_var($lookup_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$lookup_email]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['pending_verification_user_id'] = $user['id'];
            $_SESSION['pending_verification_email'] = $user['email'];
            $_SESSION['pending_verification_name'] = $user['name'];
        }
    }
}

// If no pending user can be resolved, redirect to login
if (!$user) {
    set_flash('info', 'Please sign in or create an account to begin verification.');
    header('Location: /login.php');
    exit;
}

// If user is already active, log in and send to account
if (($user['status'] ?? '') === 'active') {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'] ?? 'customer';
    $_SESSION['user_email'] = $user['email'];
    unset($_SESSION['pending_verification_user_id'], $_SESSION['pending_verification_email'], $_SESSION['pending_verification_name']);
    header('Location: /account.php');
    exit;
}

// Handle Resend Verification Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_code') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh the page.';
    } else {
        $client_ip = get_client_ip();
        if (!check_rate_limit('resend_otp_ip', $client_ip, 5, 3600)) {
            $error = 'Too many resend attempts. Please wait a few minutes before trying again.';
        } else {
            send_email_verification_link($user['id'], $user['email'], $user['name'], $user['phone'] ?? '');
            set_flash('success', 'A fresh 6-digit verification code has been sent to your email.');
            header('Location: /verify-signup.php');
            exit;
        }
    }
}

// Handle OTP Verification Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'verify_otp')) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh the page.';
    } else {
        $client_ip = get_client_ip();
        if (!check_rate_limit('verify_otp_ip', $client_ip, 10, 900)) {
            $error = 'Too many incorrect attempts. Please wait 15 minutes before trying again.';
        } else {
            $entered_otp = trim((string)($_POST['otp_code'] ?? ''));
            // Remove spaces or hyphens if entered
            $entered_otp = preg_replace('/[^0-9]/', '', $entered_otp);

            if (empty($entered_otp) || strlen($entered_otp) !== 6) {
                $error = 'Please enter a valid 6-digit verification code.';
            } else {
                $now = date('Y-m-d H:i:s');
                $is_otp_match = false;

                // Match against email OTP
                if (!empty($user['email_verification_otp']) && hash_equals((string)$user['email_verification_otp'], $entered_otp)) {
                    $is_otp_match = true;
                }
                // Match against phone OTP
                if (!$is_otp_match && !empty($user['phone_verification_otp']) && hash_equals((string)$user['phone_verification_otp'], $entered_otp)) {
                    $is_otp_match = true;
                }

                // Check expiry if exists
                if ($is_otp_match && !empty($user['email_verification_expires']) && strtotime($user['email_verification_expires']) < time()) {
                    $error = 'This verification code has expired. Please click "Resend Code" to receive a new one.';
                } elseif ($is_otp_match) {
                    // Activate Account
                    try {
                        $upd = $pdo->prepare("
                            UPDATE users 
                            SET status = 'active', 
                                email_verified_at = CURRENT_TIMESTAMP, 
                                phone_verified_at = CURRENT_TIMESTAMP,
                                email_verification_otp = NULL, 
                                phone_verification_otp = NULL,
                                email_verification_token = NULL,
                                email_verification_expires = NULL,
                                phone_verification_expires = NULL
                            WHERE id = ?
                        ");
                        $upd->execute([$user['id']]);

                        // Send Official Welcome Email
                        send_welcome_email($user['id'], $user['email'], $user['name']);

                        // Session Login & Regeneration
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            session_regenerate_id(true);
                        }
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'] ?? 'customer';
                        $_SESSION['user_email'] = $user['email'];

                        unset($_SESSION['pending_verification_user_id'], $_SESSION['pending_verification_email'], $_SESSION['pending_verification_name']);

                        log_audit_action('user_signup_verified', 'auth', 'user', $user['id'], 'Account verified via OTP code');
                        set_flash('success', 'Account verified successfully! Welcome to GaunGaliHat.');
                        header('Location: /account.php');
                        exit;
                    } catch (Exception $e) {
                        $error = 'Verification error: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Invalid 6-digit verification code. Please check your email inbox (and spam folder) or request a new code.';
                }
            }
        }
    }
}

// Helper to mask email & phone
function mask_email_display($email) {
    if (empty($email) || strpos($email, '@') === false) return $email;
    $parts = explode('@', $email);
    $name = $parts[0];
    $len = strlen($name);
    $masked_name = ($len <= 2) ? $name[0] . '*' : substr($name, 0, 2) . str_repeat('*', max(2, $len - 3)) . substr($name, -1);
    return $masked_name . '@' . $parts[1];
}

function mask_phone_display($phone) {
    if (empty($phone)) return '';
    $len = strlen($phone);
    if ($len <= 4) return '••••';
    return substr($phone, 0, 2) . '••••••' . substr($phone, -2);
}

$page_title = 'Verify Your Account — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[85vh] py-12 flex items-center justify-center">
  <div class="max-w-md w-full mx-4">

    <!-- Brand Emblem -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-envelope-circle-check text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Verify Your Account</h1>
      <p class="text-xs text-gray-500 mt-1">Please enter the 6-digit verification code to activate your account</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      
      <!-- Target Recipient Notice -->
      <div class="p-3.5 mb-5 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-gray-600 flex items-start gap-3">
        <i class="fa-solid fa-shield-halved text-brand-green text-base mt-0.5 flex-shrink-0"></i>
        <div>
          <span class="font-bold text-gray-900 block">Verification code sent to:</span>
          <div class="font-mono text-gray-800 text-[11px] mt-0.5">
            <?= htmlspecialchars(mask_email_display($user['email'])) ?>
            <?php if (!empty($user['phone'])): ?>
              <span class="text-gray-400">•</span> +91 <?= htmlspecialchars(mask_phone_display($user['phone'])) ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0 text-base"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- OTP Form -->
      <form action="/verify-signup.php" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="verify_otp">

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1 text-center">
            Enter 6-Digit OTP Code
          </label>
          <input type="text" name="otp_code" required autofocus pattern="[0-9]{6}" maxlength="6" placeholder="000000"
            class="w-full text-center text-2xl font-black font-mono tracking-[0.5em] px-4 py-3 bg-stone-50 border border-gray-300 rounded-2xl focus:bg-white focus:border-brand-green focus:outline-none transition shadow-inner">
          <p class="text-[11px] text-gray-400 text-center mt-1.5">Check your inbox or spam folder for your email from GaunGaliHat.</p>
        </div>

        <button type="submit"
          class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm">
          <i class="fa-solid fa-shield-check"></i>
          Verify & Activate Account
        </button>
      </form>

      <!-- Resend & One-Click Link Actions -->
      <div class="mt-6 pt-5 border-t border-gray-200 space-y-3">
        <form action="/verify-signup.php" method="POST" class="text-center">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="resend_code">
          <span class="text-xs text-gray-500">Didn't receive the email code? </span>
          <button type="submit" class="text-xs font-bold text-brand-green hover:underline cursor-pointer">
            Resend Verification Code
          </button>
        </form>

        <div class="text-center text-[11px] text-gray-400">
          <i class="fa-solid fa-circle-info mr-1"></i> You can also click the 1-click verification button inside the email to activate instantly.
        </div>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
