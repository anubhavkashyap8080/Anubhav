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

// Security Check: Lock immediately if any super_admin already exists
$existing_supers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
if ($existing_supers > 0) {
    http_response_code(403);
    die("<!DOCTYPE html>
    <html lang='hi'>
    <head><meta charset='UTF-8'><title>Access Denied — GaunGaliHat Setup</title>
    <style>body{font-family:sans-serif;background:#fdfbf7;display:flex;align-items:center;justify-content:center;height:90vh;margin:0;}
    .box{max-width:480px;background:#fff;padding:30px;border-radius:16px;border:1px solid #e5e7eb;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.05);}
    h2{color:#dc2626;}p{color:#4b5563;font-size:14px;line-height:1.6;}</style></head>
    <body><div class='box'>
      <h2>सुरक्षा अवरोध (Setup Locked)</h2>
      <p>सिस्टम में पहले से ही सुपर एडमिन खाता मौजूद है। सुरक्षा कारणों से यह सेटअप टूल लॉक कर दिया गया है।</p>
      <p><a href='/login.php?tab=admin' style='color:#059669;font-weight:bold;'>एडमिन लॉगिन पेज पर जाएं &rarr;</a></p>
    </div></body></html>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

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
                
                // Self-delete this setup script for maximum security
                @unlink(__FILE__);

                // Redirect to login page
                set_flash('success', "सुपर एडमिन खाता (@$username) सफलतापूर्वक बनाया गया! यह सेटअप स्क्रिप्ट स्वतः डिलीट कर दी गई है।");
                header('Location: /login.php?tab=admin');
                exit;
            }
        } catch (Exception $e) {
            $error = 'डेटाबेस त्रुटि: ' . $e->getMessage();
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
  <script src="https://cdn.tailwindcss.com"></script>
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

      <form action="/setup_admin.php" method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

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
          <input type="email" name="email" required placeholder="admin@womenvission.in"
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
      </form>
    </div>
  </div>
</body>
</html>
