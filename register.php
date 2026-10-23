<?php
require_once __DIR__ . '/includes/functions.php';

// Fetch Google Auth Settings
$google_enabled = false;
$google_client_id = '';
try {
    $stmt_g = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('google_auth_enabled', 'google_client_id')");
    while ($r = $stmt_g->fetch()) {
        if ($r['setting_key'] === 'google_auth_enabled') $google_enabled = ($r['setting_value'] == '1');
        if ($r['setting_key'] === 'google_client_id') $google_client_id = $r['setting_value'];
    }
} catch (Exception $e) {}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city = clean_input($_POST['city'] ?? 'Hardoi');
    $pincode = clean_input($_POST['pincode'] ?? '241001');
    $address = clean_input($_POST['address'] ?? '');

    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'कृपया सभी आवश्यक फ़ील्ड भरें।';
    } elseif ($password !== $confirm_password) {
        $error = 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
    } elseif (strlen($password) < 6) {
        $error = 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।';
    } else {
        try {
            // Check existing user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetch()) {
                $error = 'इस ईमेल या मोबाइल नंबर से पहले से ही एक खाता मौजूद है। कृपया लॉगिन करें।';
            } else {
                // Generate username and unique code
                $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
                $username = $base_username . rand(10, 99);
                $unique_code = 'GGH-CUST-' . rand(10000, 99999);
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $insert = $pdo->prepare("
                    INSERT INTO users (unique_code, name, username, email, phone, password, role, city, pincode, address, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', ?, ?, ?, 'active')
                ");
                $insert->execute([$unique_code, $name, $username, $email, $phone, $hashed_password, $city, $pincode, $address]);
                $user_id = $pdo->lastInsertId();

                // Auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'customer';
                $_SESSION['user_email'] = $email;

                set_flash('success', 'खाता सफलतापूर्वक बनाया गया! गाँव गली हाट में आपका स्वागत है।');
                header('Location: /account.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'पंजीकरण त्रुटि: ' . $e->getMessage();
        }
    }
}

$page_title = 'नया ग्राहक पंजीकरण — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[85vh] py-12 flex items-center justify-center">
  <div class="max-w-xl w-full mx-4">
    
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-user-plus text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">नया ग्राहक पंजीकरण</h1>
      <p class="text-xs text-gray-500 mt-1">हथकरघा, देसी दाल, अचार और ग्रामीण औजार सीधे घर मंगाएं</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form action="/register.php" method="POST" class="space-y-4">
        
        <!-- Name -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम (Full Name) <span class="text-red-500">*</span></label>
          <input type="text" name="name" required placeholder="उदा. अनुज कुमार"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
        </div>

        <!-- Phone & Email Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर (Mobile) <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10 अंकीय नंबर"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता (Email) <span class="text-red-500">*</span></label>
            <input type="email" name="email" required placeholder="you@example.com"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Passwords -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड बनाएं <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="6" placeholder="न्यूनतम 6 अक्षर"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड की पुष्टि करें <span class="text-red-500">*</span></label>
            <input type="password" name="confirm_password" required minlength="6" placeholder="दोबारा लिखें"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Delivery Address -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">घर/गाँव का पता (Delivery Address)</label>
          <textarea name="address" rows="2" placeholder="मकान नंबर, मोहल्ला/टोला, निकटतम लैंडमार्क..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"></textarea>
        </div>

        <!-- City & Pincode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">शहर / जिला (City/District)</label>
            <input type="text" name="city" value="Hardoi"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पिन कोड (PIN Code)</label>
            <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="241001"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit"
          class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-3">
          <i class="fa-solid fa-check"></i>
          खाता बनाएं (Register Free)
        </button>
      </form>

      <?php if ($google_enabled): ?>
        <div class="mt-6 pt-5 border-t border-gray-100">
          <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="flex-shrink mx-3 text-[11px] font-bold text-gray-400 uppercase">या Google से 1-क्लिक में शुरू करें</span>
            <div class="flex-grow border-t border-gray-200"></div>
          </div>

          <div class="mt-2 flex flex-col items-center justify-center space-y-2">
            <div id="g_id_onload"
                 data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                 data-login_uri="/auth/google-callback.php"
                 data-auto_prompt="false">
            </div>
            <div class="g_id_signin w-full flex justify-center"
                 data-type="standard"
                 data-size="large"
                 data-theme="outline"
                 data-text="signup_with"
                 data-shape="rectangular"
                 data-logo_alignment="left"
                 data-width="100%">
            </div>

            <a href="https://accounts.google.com/o/oauth2/v2/auth?client_id=<?= urlencode($google_client_id) ?>&redirect_uri=<?= urlencode(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/auth/google-callback.php') ?>&response_type=code&scope=openid%20profile%20email"
               class="w-full py-2.5 px-4 bg-white border border-gray-300 hover:bg-stone-50 text-gray-700 font-bold rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-xs">
              <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-4 h-4" alt="Google">
              <span>Google द्वारा पंजीकरण करें</span>
            </a>
          </div>
        </div>
      <?php endif; ?>

      <div class="mt-6 pt-5 border-t border-gray-200 text-center text-xs text-gray-600">
        पहले से खाता मौजूद है? <a href="/login.php" class="text-brand-green font-bold hover:underline">सीधे लॉगिन करें</a>
      </div>
    </div>

  </div>
</div>

<?php if ($google_enabled): ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
