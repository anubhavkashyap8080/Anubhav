<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Item 15: CSRF Token Check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'सुरक्षा टोकन अमान्य है। कृपया पृष्ठ रीफ़्रेश करके पुनः प्रयास करें। (Invalid security token)';
    } else {
        $shop_name = clean_input($_POST['shop_name'] ?? '');
        $owner_name = clean_input($_POST['owner_name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $vendor_type = clean_input($_POST['vendor_type'] ?? 'Artisan');
        $village = clean_input($_POST['village'] ?? '');
        $district = clean_input($_POST['district'] ?? 'Hardoi');
        $pincode = clean_input($_POST['pincode'] ?? '241126');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $upi_id = clean_input($_POST['upi_id'] ?? '');
        $bank_name = clean_input($_POST['bank_name'] ?? '');
        $bank_account = clean_input($_POST['bank_account'] ?? '');
        $bank_ifsc = clean_input($_POST['bank_ifsc'] ?? '');
        $story = clean_input($_POST['story'] ?? '');

        $pwd_err = '';
        $client_ip = get_client_ip();
        if (!check_rate_limit('seller_register_ip', $client_ip, 5, 3600) || !check_rate_limit('seller_register_phone', $phone, 3, 3600)) {
            $error = 'बहुत अधिक पंजीकरण प्रयास। कृपया 1 घंटे बाद पुनः प्रयास करें। (Rate limit exceeded)';
        } elseif (!verify_turnstile_captcha()) {
            $error = 'CAPTCHA सुरक्षा सत्यापन विफल रहा। कृपया पुनः प्रयास करें।';
        } elseif (empty($shop_name) || empty($owner_name) || empty($phone) || empty($email) || empty($password)) {
            $error = 'कृपया सभी आवश्यक फ़ील्ड भरें।';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'कृपया एक मान्य ईमेल पता दर्ज करें।';
        } elseif ($password !== $confirm_password) {
            $error = 'पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
        } elseif (!is_strong_password($password, $pwd_err)) {
            // Item 22: Strong Password Policy Enforcement
            $error = $pwd_err;
        } else {
            try {
                $pdo->beginTransaction();

                // Check duplicate user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
                $stmt->execute([$email, $phone]);
                if ($stmt->fetch()) {
                    $error = 'इस ईमेल या फ़ोन नंबर से पहले से उपयोगकर्ता पंजीकृत है।';
                    $pdo->rollBack();
                } else {
                    // Item 11: Cryptographically secure random identifiers
                    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
                    $username = $base_username . random_int(10, 99);
                    $unique_code = 'GGH-ART-' . random_int(10000, 99999);
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Insert User
                    $insert_user = $pdo->prepare("
                        INSERT INTO users (unique_code, name, username, email, phone, password, role, department, city, pincode, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'vendor', ?, ?, ?, 'active')
                    ");
                    $insert_user->execute([
                        $unique_code, $owner_name, $username, $email, $phone, $hashed_password,
                        $shop_name . ' (' . $vendor_type . ')', $district, $pincode
                    ]);
                    $user_id = $pdo->lastInsertId();

                    // Create slug for vendor
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $shop_name)) . '-' . random_int(10, 99);

                    // Insert Vendor
                    $insert_vendor = $pdo->prepare("
                        INSERT INTO vendors (
                            user_id, name, business_name, slug, type, village, district, state,
                            pincode, phone, whatsapp, email, story, bank_beneficiary,
                            bank_name, bank_account, bank_ifsc, upi_id, verified, kyc_status, commission_rate
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Uttar Pradesh', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'Verified', 4.00)
                    ");
                    $insert_vendor->execute([
                        $user_id, $owner_name, $shop_name, $slug, $vendor_type, $village, $district,
                        $pincode, $phone, $phone, $email, $story, $owner_name,
                        $bank_name, $bank_account, $bank_ifsc, $upi_id
                    ]);

                    $pdo->commit();

                    // Item 10: Dispatch email verification link
                    send_email_verification_link($user_id, $email, $owner_name);

                    // Item 13: Session rotation upon sign-in
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }

                    // Login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $owner_name;
                    $_SESSION['user_role'] = 'vendor';
                    $_SESSION['user_email'] = $email;

                    set_flash('success', 'विक्रेता खाता सफलतापूर्वक बना! सत्यापन लिंक आपके ईमेल पर भेज दिया गया है।');
                    header('Location: /seller/dashboard.php');
                    exit;
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'पंजीकरण त्रुटि: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'कारीगर व विक्रेता पंजीकरण — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-screen py-10">
  <div class="max-w-3xl mx-auto px-4 sm:px-6">
    
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-600 text-white shadow-lg mb-3">
        <i class="fa-solid fa-hammer text-2xl"></i>
      </div>
      <span class="inline-block text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1 rounded-full mb-2">
        कारीगर एवं विक्रेता ऑनबोर्डिंग
      </span>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">गाँव गली हाट से जुड़ें और सीधे ग्राहक तक बेचें</h1>
      <p class="text-xs sm:text-sm text-gray-600 max-w-xl mx-auto mt-2">
        हरदोई, अवध व ग्रामीण भारत के लोहार, हथकरघा बुनकर, दाल उत्पादक व अचार बनाने वाले स्वयं सहायता समूहों का भरोसेमंद डिजिटल बाज़ार।
      </p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-10">
      
      <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2.5">
          <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form action="/seller-register.php" method="POST" class="space-y-6">
        <?= csrf_field() ?>
        
        <!-- Step 1: Basic Information -->
        <div>
          <h3 class="text-sm font-bold text-gray-900 pb-2 mb-4 border-b border-gray-100 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">1</span>
            कारीगर एवं कार्यशाला का विवरण (Workshop Details)
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">कार्यशाला / दुकान का नाम <span class="text-red-500">*</span></label>
              <input type="text" name="shop_name" required placeholder="उदा. रामकिशन लोहार टूल्स"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">कारीगर / स्वामी का नाम <span class="text-red-500">*</span></label>
              <input type="text" name="owner_name" required placeholder="उदा. रामकिशन लोहार"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">संस्थान का प्रकार (Entity Type) <span class="text-red-500">*</span></label>
              <select name="vendor_type" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
                <option value="Artisan">व्यक्तिगत कारीगर (Traditional Artisan)</option>
                <option value="Cottage Industry">कुटीर उद्योग / वर्कशॉप (Cottage Industry)</option>
                <option value="SHG">महिला स्वयं सहायता समूह (Women SHG)</option>
                <option value="Farmer">किसान उत्पादक समूह / दालें (Farmer / FPO)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">गाँव / कस्बा (Village / Town)</label>
              <input type="text" name="village" placeholder="उदा. बघौली, नेवादा"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">जिला (District)</label>
              <input type="text" name="district" value="Hardoi"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">पिन कोड (Pincode) <span class="text-red-500">*</span></label>
              <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="241126" required
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>
        </div>

        <!-- Step 2: Contact & Secure Password -->
        <div>
          <h3 class="text-sm font-bold text-gray-900 pb-2 mb-4 border-b border-gray-100 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">2</span>
            संपर्क एवं लॉगिन पासवर्ड (Contact & Password)
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर (WhatsApp समर्थित) <span class="text-red-500">*</span></label>
              <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10-अंकीय मोबाइल"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता (Email) <span class="text-red-500">*</span></label>
              <input type="email" name="email" required placeholder="artisan@ganvgalihat.com"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड बनाएं <span class="text-red-500">*</span></label>
              <input type="password" name="password" required minlength="10" placeholder="न्यूनतम 10 अक्षर (मजबूत पासवर्ड)"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">पासवर्ड दोबारा लिखें <span class="text-red-500">*</span></label>
              <input type="password" name="confirm_password" required minlength="10" placeholder="दोबारा लिखें"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>
        </div>

        <!-- Step 3: Payout / Banking Details -->
        <div>
          <h3 class="text-sm font-bold text-gray-900 pb-2 mb-4 border-b border-gray-100 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">3</span>
            भुगतान खाता (Bank / UPI for Payouts)
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">UPI ID (त्वरित भुगतान हेतु)</label>
              <input type="text" name="upi_id" placeholder="उदा. artisan@sbi या 9876543210@paytm"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">बैंक का नाम (Bank Name)</label>
              <input type="text" name="bank_name" placeholder="उदा. State Bank of India"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">खाता संख्या (Account Number)</label>
              <input type="text" name="bank_account" placeholder="खाता संख्या"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">IFSC कोड</label>
              <input type="text" name="bank_ifsc" placeholder="SBIN0001245"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>
        </div>

        <!-- Craft Heritage Story -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">आपकी कला व शिल्प की कहानी (About Your Craft)</label>
          <textarea name="story" rows="3" placeholder="आप कितने वर्षों से यह कार्य कर रहे हैं? आपके उत्पाद की क्या विशेषता है? (उदा. हाथ से कुटा हुआ लोहे का हंसिया, 100% शुद्ध सरसों तेल का अचार...)"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition"></textarea>
        </div>

        <!-- Security CAPTCHA -->
        <?= render_turnstile_widget() ?>

        <button type="submit"
          class="w-full py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-lg active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm">
          <i class="fa-solid fa-shop"></i>
          कारीगर विक्रेता के रूप में ऑनबोर्ड हों (Join as Artisan Vendor)
        </button>

      </form>

      <div class="mt-6 pt-5 border-t border-gray-200 text-center text-xs text-gray-600">
        पहले से विक्रेता खाता है? <a href="/login.php?tab=seller" class="text-amber-700 font-bold hover:underline">विक्रेता लॉगिन करें</a>
      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
