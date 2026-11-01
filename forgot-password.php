<?php
/**
 * GanvGaliHat Password Reset Request Gateway
 */

require_once __DIR__ . '/includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
    } else {
        $email = clean_input($_POST['email'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Rate limit: Max 3 reset requests per IP / email per 15 minutes
            $window = date('Y-m-d H:i:s', time() - 900);
            $stmt_cnt = $pdo->prepare("
                SELECT COUNT(*) FROM password_resets 
                WHERE email = ? AND created_at >= ?
            ");
            $stmt_cnt->execute([$email, $window]);
            $recent_resets = (int)$stmt_cnt->fetchColumn();

            if ($recent_resets >= 3) {
                $message = 'If this email address exists in our system, password reset instructions have been sent.';
            } else {
                // Check if user exists
                $stmt_u = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND status != 'suspended' LIMIT 1");
                $stmt_u->execute([$email]);
                $user = $stmt_u->fetch();

                if ($user) {
                    $plain_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $plain_token);
                    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour validity

                    $ins = $pdo->prepare("
                        INSERT INTO password_resets (user_id, email, token_hash, expires_at, used, created_at)
                        VALUES (?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
                    ");
                    $ins->execute([$user['id'], $email, $token_hash, $expires_at]);

                    $reset_url = SITE_URL . '/reset-password.php?token=' . $plain_token . '&email=' . urlencode($email);

                    $email_domain = parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'ganvgalihat.com';
                    $subject = "Reset your GanvGaliHat Password";
                    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: GanvGaliHat Security <no-reply@" . $email_domain . ">\r\n";
                    $body = "
                    <div style='font-family: -apple-system, BlinkMacSystemFont, Roboto, sans-serif; max-width: 540px; margin: 0 auto; padding: 28px; border: 1px solid #e5e7eb; border-radius: 16px; background: #ffffff;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #065f46; margin: 0; font-size: 22px;'>GanvGaliHat — Password Reset</h2>
                            <p style='color: #d97706; font-size: 13px; font-weight: bold; margin: 4px 0 0 0;'>Hunar desh ka, ganv se ghar tak</p>
                        </div>
                        <p style='color: #374151; font-size: 14px;'>Namaste <strong>" . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                        <p style='color: #4b5563; font-size: 14px; line-height: 1.6;'>We received a request to reset your password. Click the button below to choose a new password:</p>
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='" . htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8') . "' style='background: #059669; color: white; padding: 12px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 14px; display: inline-block;'>Reset My Password</a>
                        </div>
                        <p style='color: #6b7280; font-size: 12px; line-height: 1.5;'>If the button does not work, copy and paste this link into your browser:<br><a href='" . htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8') . "' style='color: #059669; word-break: break-all;'>" . htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8') . "</a></p>
                        <hr style='border: none; border-top: 1px solid #f3f4f6; margin: 20px 0;'>
                        <p style='color: #9ca3af; font-size: 11px; text-align: center;'>This single-use link expires in 60 minutes. If you did not request this, you can safely ignore this email.</p>
                    </div>";

                    @mail($email, $subject, $body, $headers);
                    log_audit_action('password_reset_requested', 'auth', 'user', $user['id'], 'Password reset link sent to ' . $email);
                }

                // Generic message to prevent account enumeration
                $message = 'If this email address exists in our records, a password reset link has been dispatched. Please check your inbox and spam folder.';
            }
        }
    }
}

$page_title = 'Forgot Password — GanvGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[75vh] py-12 flex items-center justify-center">
  <div class="max-w-md w-full mx-4">
    
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-key text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Forgot Password</h1>
      <p class="text-xs text-gray-500 mt-1">Receive secure reset instructions on your registered email</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($message)): ?>
        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-start gap-3">
          <i class="fa-solid fa-circle-check text-base text-emerald-600 mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="font-bold">Request Received!</p>
            <p class="mt-1"><?= htmlspecialchars($message) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <form action="/forgot-password.php" method="POST" class="space-y-4">
        <?= csrf_field() ?>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Registered Email Address <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i class="fa-regular fa-envelope text-xs"></i>
            </span>
            <input type="email" name="email" required autofocus placeholder="you@example.com"
              class="w-full pl-10 pr-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <button type="submit"
          class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-3">
          <i class="fa-solid fa-paper-plane"></i>
          Send Password Reset Link
        </button>
      </form>

      <div class="mt-6 pt-5 border-t border-gray-100 text-center text-xs text-gray-600 flex items-center justify-between">
        <a href="/login.php" class="text-brand-green font-bold hover:underline">
          <i class="fa-solid fa-arrow-left"></i> Back to Sign In
        </a>
        <a href="/register.php" class="text-gray-500 hover:text-gray-800">
          Create New Account
        </a>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
