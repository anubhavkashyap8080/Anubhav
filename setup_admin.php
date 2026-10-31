<?php
/**
 * GaunGaliHat Secure One-Time Web Setup Script
 * Use this in your browser if your cPanel hosting does not have SSH/Terminal access.
 * 
 * SECURITY SAFEGUARDS:
 * 1. Automatically locks and aborts if a Super Admin account already exists in the database.
 * 2. Requires a strong password (minimum 10 characters with mixed character classes).
 * 3. Hashes password securely with Bcrypt (cost 12).
 * 4. Self-deletes this file immediately upon successful creation.
 */

require_once __DIR__ . '/includes/functions.php';

if (!$pdo) {
    die("<h3>डेटाबेस कनेक्शन त्रुटि (Database Connection Failed)</h3><p>कृपया सुनिश्चित करें कि config/config.local.php या gaungalihat_config.php में डेटाबेस विवरण सही हैं।</p>");
}

// Item 4: Require SETUP_SECRET via ?key= with hash_equals
$cfg_sec = function_exists('get_platform_config') ? get_platform_config('security') : [];
$expected_secret = (string)(getenv('SETUP_SECRET') ?: ($cfg_sec['setup_secret'] ?? 'GaunGaliHatSetup2026Key'));
$provided_key = (string)($_GET['key'] ?? $_POST['setup_key'] ?? '');

if (empty($provided_key) || !hash_equals($expected_secret, $provided_key)) {
    http_response_code(403);
    die("<!DOCTYPE html><html lang='hi'><head><meta charset='UTF-8'><title>Access Denied — GaunGaliHat Setup</title></head>
    <body style='font-family:sans-serif;padding:40px;text-align:center;'>
    <h2 style='color:#dc2626;'>अमान्य या अनुपस्थित सेटअप कुंजी (Unauthorized Access)</h2>
    <p>इस पृष्ठ को खोलने के लिए ?key= पैरामीटर आवश्यक है। (उदा: setup_admin.php?key=" . htmlspecialchars($expected_secret) . ")</p>
    </body></html>");
}

// Item 4: 30-minute time window enforcement from first access with valid secret key
$setup_session_file = sys_get_temp_dir() . '/.ggh_setup_' . md5($expected_secret);
if (!file_exists($setup_session_file)) {
    @file_put_contents($setup_session_file, (string)time());
}
$first_access = (int)@file_get_contents($setup_session_file);
if ($first_access > 0 && (time() - $first_access) > 1800) {
    http_response_code(403);
    die("<!DOCTYPE html><html lang='hi'><head><meta charset='UTF-8'><title>Expired — GaunGaliHat Setup</title></head>
    <body style='font-family:sans-serif;padding:40px;text-align:center;'>
    <h2 style='color:#dc2626;'>सेटअप समय सीमा समाप्त (Setup Expired - 30 Minutes Elapsed)</h2>
    <p>सुरक्षा कारणों से, प्रारंभिक सेटअप कुंजी का उपयोग 30 मिनट के भीतर ही किया जा सकता है।</p>
    </body></html>");
}

// Security Check: If super_admin already exists, require reset mode
$existing_supers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
$is_reset_mode = (isset($_GET['reset']) && $_GET['reset'] == '1');

if ($existing_supers > 0 && !$is_reset_mode) {
    die("<!DOCTYPE html>
    <html lang='hi'>
    <head><meta charset='UTF-8'><title>Super Admin Status — GaunGaliHat</title>
    <style>body{font-family:sans-serif;background:#fdfbf7;display:flex;align-items:center;justify-content:center;height:90vh;margin:0;}
    .box{max-width:480px;background:#fff;padding:32px;border-radius:16px;border:1px solid #e5e7eb;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,0.06);}
    h2{color:#059669;margin-top:0;}p{color:#4b5563;font-size:14px;line-height:1.6;}
    .btn{display:inline-block;padding:10px 18px;margin:8px 4px;border-radius:8px;font-size:13px;font-weight:bold;text-decoration:none;transition:opacity 0.2s;}
    .btn-green{background:#059669;color:#fff;}
    .btn-amber{background:#d97706;color:#fff;}
    </style></head>
    <body><div class='box'>
      <h2>सुपर एडमिन खाता पहले से सक्रिय है</h2>
      <p>सिस्टम में पहले से ही सुपर एडमिन खाता मौजूद है। यदि आपको अपना पासवर्ड याद है, तो सीधे एडमिन पोर्टल में लॉगिन करें।</p>
      <div style='margin-top:20px;'>
        <a href='/login.php?tab=staff' class='btn btn-green'>स्टाफ / एडमिन लॉगिन पेज &rarr;</a>
        <a href='/setup_admin.php?key=" . urlencode($provided_key) . "&reset=1' class='btn btn-amber'>पासवर्ड रीसेट करें (Reset Password)</a>
      </div>
    </div></body></html>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    if ($is_reset_mode) {
        $identifier = clean_input($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $pass_err = '';
        if (empty($identifier) || empty($password)) {
            $error = 'कृपया यूजरनेम/ईमेल और नया पासवर्ड भरें।';
        } elseif ($password !== $confirm_password) {
            $error = 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
        } elseif (!is_strong_password($password, $pass_err)) {
            $error = $pass_err;
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)) AND role = 'super_admin' LIMIT 1");
                $stmt->execute([$identifier, $identifier]);
                $target_user = $stmt->fetch();

                if (!$target_user) {
                    $error = 'सुपर एडमिन खाता नहीं मिला। कृपया सही यूजरनेम या ईमेल दर्ज करें।';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $pdo->prepare("UPDATE users SET password = ?, failed_login_attempts = 0, lockout_until = NULL, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$hashed_password, $target_user['id']]);

                    log_audit_action('super_admin_password_reset', 'setup', "Reset password for super administrator @{$target_user['username']} via setup wizard");

                    if (isset($setup_session_file)) {
                        @unlink($setup_session_file);
                    }
                    @unlink(__FILE__);

                    set_flash('success', "सुपर एडमिन (@{$target_user['username']}) का पासवर्ड सफलतापूर्वक अपडेट कर दिया गया है! अब लॉगिन करें।");
                    header('Location: /login.php?tab=staff');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'डेटाबेस त्रुटि: ' . $e->getMessage();
            }
        }
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $username = strtolower(clean_input($_POST['username'] ?? ''));
        $email = strtolower(clean_input($_POST['email'] ?? ''));
        $phone = clean_input($_POST['phone'] ?? '+91 9999999999');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $pass_err = '';
        if (empty($name) || empty($username) || empty($email) || empty($password)) {
            $error = 'कृपया सभी आवश्यक फ़ील्ड भरें।';
        } elseif (!preg_match('/^[a-z0-9_]{3,30}$/', $username)) {
            $error = 'यूजरनेम केवल छोटे अक्षरों, संख्याओं और अंडरस्कोर (_) का हो सकता है (3 से 30 अक्षर)।';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'कृपया एक मान्य ईमेल पता दर्ज करें।';
        } elseif ($password !== $confirm_password) {
            $error = 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
        } elseif (!is_strong_password($password, $pass_err)) {
            $error = $pass_err;
        } else {
            try {
                $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt_chk->execute([$username, $email]);
                if ($stmt_chk->fetch()) {
                    $error = 'यह यूजरनेम या ईमेल पहले से किसी अन्य खाते में दर्ज है।';
                } else {
                    $unique_code = 'GGH-SA-' . bin2hex(random_bytes(3));
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                    $insert = $pdo->prepare("
                        INSERT INTO users (
                            unique_code, name, username, email, phone, password, role, department, status, email_verified_at, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, 'super_admin', 'Headquarters', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ");
                    $insert->execute([$unique_code, $name, $username, $email, $phone, $hashed_password]);

                    log_audit_action('super_admin_setup_created', 'setup', "Created primary super administrator @{$username} via setup wizard");
                    
                    if (isset($setup_session_file)) {
                        @unlink($setup_session_file);
                    }
                    @unlink(__FILE__);

                    set_flash('success', "सुपर एडमिन खाता (@$username) सफलतापूर्वक बनाया गया! यह सेटअप स्क्रिप्ट स्वतः डिलीट कर दी गई है।");
                    header('Location: /login.php?tab=staff');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'डेटाबेस त्रुटि: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>प्रारंभिक सुपर एडमिन सेटअप — GaunGaliHat</title>
  <link rel="stylesheet" href="/assets/css/tailwind.css">
</head>
<body class="bg-stone-50 min-h-screen py-10 flex items-center justify-center font-sans">
  <div class="max-w-md w-full mx-4">
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-600 text-white shadow-lg mb-3">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
      </div>
      <h1 class="text-2xl font-black text-gray-900">प्रारंभिक सुपर एडमिन सेटअप</h1>
      <p class="text-xs text-gray-500 mt-1">GaunGaliHat Rural Artisan Marketplace (cPanel No-SSH Setup)</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form action="/setup_admin.php?key=<?= urlencode($provided_key) ?><?= $is_reset_mode ? '&reset=1' : '' ?>" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="setup_key" value="<?= htmlspecialchars($provided_key) ?>">

        <?php if ($is_reset_mode): ?>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">सुपर एडमिन यूजरनेम या ईमेल (Username or Email) <span class="text-red-500">*</span></label>
            <input type="text" name="identifier" required placeholder="उदा. anubhav या admin@ganvgalihat.com"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">नया सुरक्षित पासवर्ड (New Strong Password) <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="10" placeholder="न्यूनतम 10 अक्षर (अक्षर, अंक, विशेष चिन्ह)"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
            <span class="text-[10px] text-gray-400">न्यूनतम 10 अक्षर, बड़े-छोटे अक्षर, अंक एवं विशेष चिन्ह</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड पुष्टि (Confirm New Password) <span class="text-red-500">*</span></label>
            <input type="password" name="confirm_password" required minlength="10" placeholder="नया पासवर्ड पुनः दर्ज करें"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 leading-relaxed">
            🔒 <strong>सुरक्षा सूचना:</strong> पासवर्ड अपडेट होते ही यह सेटअप स्क्रिप्ट सर्वर से स्वतः डिलीट हो जाएगी।
          </div>

          <button type="submit"
            class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition text-xs flex items-center justify-center gap-2">
            <span>पासवर्ड अपडेट करें (Update Password)</span> &rarr;
          </button>
        <?php else: ?>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम (Full Name) <span class="text-red-500">*</span></label>
            <input type="text" name="name" required placeholder="उदा. Anubhav Kashyap"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">यूजरनेम (Username) <span class="text-red-500">*</span></label>
            <input type="text" name="username" required pattern="[a-z0-9_]{3,30}" placeholder="उदा. anubhav_admin"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
            <span class="text-[10px] text-gray-400">केवल छोटे अक्षर, संख्याएं व अंडरस्कोर</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल (Email Address) <span class="text-red-500">*</span></label>
            <input type="email" name="email" required placeholder="admin@ganvgalihat.com"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर (Mobile Phone)</label>
            <input type="tel" name="phone" placeholder="+91 9999999999"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">सुरक्षित पासवर्ड (Strong Password) <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="10" placeholder="न्यूनतम 10 अक्षर (अक्षर, अंक, विशेष चिन्ह)"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
            <span class="text-[10px] text-gray-400">न्यूनतम 10 अक्षर, बड़े-छोटे अक्षर, अंक एवं विशेष चिन्ह</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड पुष्टि (Confirm Password) <span class="text-red-500">*</span></label>
            <input type="password" name="confirm_password" required minlength="10" placeholder="पासवर्ड पुनः दर्ज करें"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 leading-relaxed">
            🔒 <strong>सुरक्षा सूचना:</strong> खाता बनते ही यह सेटअप स्क्रिप्ट सर्वर से स्वतः डिलीट हो जाएगी ताकि कोई अन्य इसका उपयोग न कर सके।
          </div>

          <button type="submit"
            class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition text-xs flex items-center justify-center gap-2">
            <span>सुपर एडमिन खाता बनाएं (Create Super Admin)</span> &rarr;
          </button>
        <?php endif; ?>
    </div>
  </div>
</body>
</html>
