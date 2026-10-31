<?php
/**
 * GaunGaliHat Password Reset Submission Handler
 */

require_once __DIR__ . '/includes/functions.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$email = trim((string)($_GET['email'] ?? $_POST['email'] ?? ''));

$error = '';
$is_token_valid = false;
$reset_record = null;

if (empty($token) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid or incomplete password reset request.';
} else {
    $token_hash = hash('sha256', $token);
    $now = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("
            SELECT pr.*, u.id AS user_id, u.name 
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.email = ? AND pr.token_hash = ? AND pr.used = 0 AND pr.expires_at >= ?
            LIMIT 1
        ");
        $stmt->execute([$email, $token_hash, $now]);
        $reset_record = $stmt->fetch();

        if ($reset_record) {
            $is_token_valid = true;
        } else {
            $error = 'This password reset link has expired or has already been used.';
        }
    } catch (Throwable $e) {
        $error = 'Technical error verifying reset request. Please try again.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_token_valid) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
    } else {
        $new_pass = $_POST['password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $pwd_err = '';
        if (empty($new_pass) || empty($confirm_pass)) {
            $error = 'Please fill in both password fields.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'Passwords do not match.';
        } elseif (!is_strong_password($new_pass, $pwd_err)) {
            $error = $pwd_err ?: 'Password must be at least 10 characters with uppercase, lowercase, number, and special character.';
        } else {
            try {
                $pdo->beginTransaction();

                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);

                // Update user password, clear forced change flag, and invalidate other sessions
                $upd_user = $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upd_user->execute([$new_hash, $reset_record['user_id']]);

                // Invalidate this token and all other tokens for this user
                $upd_pr = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ?");
                $upd_pr->execute([$reset_record['user_id']]);

                $pdo->commit();

                log_audit_action('password_reset_completed', 'auth', 'user', $reset_record['user_id'], 'Password reset successfully completed for ' . $email);
                set_flash('success', 'Your password has been changed successfully! Please sign in with your new password.');
                header('Location: /login.php');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Error updating password: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Reset Password — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[75vh] py-12 flex items-center justify-center">
  <div class="max-w-md w-full mx-4">
    
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-lock-open text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Set New Password</h1>
      <p class="text-xs text-gray-500 mt-1">Create a strong, secure password for your account</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($is_token_valid): ?>
        <form action="/reset-password.php" method="POST" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="10" placeholder="Minimum 10 characters (Strong password)"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
            <input type="password" name="confirm_password" required minlength="10" placeholder="Re-type new password"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>

          <button type="submit"
            class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-3">
            <i class="fa-solid fa-check"></i>
            Save New Password
          </button>
        </form>
      <?php else: ?>
        <div class="text-center py-4">
          <p class="text-xs text-gray-600 mb-4">If you cannot reset your password, please request a new reset link.</p>
          <a href="/forgot-password.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white font-bold rounded-xl text-xs hover:bg-emerald-800 transition shadow-sm">
            <i class="fa-solid fa-arrow-rotate-left"></i> Request New Reset Link
          </a>
        </div>
      <?php endif; ?>

      <div class="mt-6 pt-5 border-t border-gray-100 text-center text-xs text-gray-600">
        <a href="/login.php" class="text-brand-green font-bold hover:underline">
          <i class="fa-solid fa-arrow-left"></i> Return to Sign In
        </a>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
