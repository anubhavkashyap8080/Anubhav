<?php
/**
 * GanvGaliHat Retailer & B2B Wholesale Registration
 * Connects Kirana stores, shopkeepers, resellers, and trade buyers
 * directly to rural artisan clusters and village manufacturers.
 */

require_once __DIR__ . '/includes/functions.php';

// If already logged in as retailer, redirect to retailer dashboard
if (is_retailer()) {
    header('Location: /retailer/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
    } else {
        $business_name = clean_input($_POST['business_name'] ?? '');
        $contact_name  = clean_input($_POST['contact_name'] ?? '');
        $business_type = clean_input($_POST['business_type'] ?? 'Kirana / Grocery');
        $email         = clean_input($_POST['email'] ?? '');
        $raw_phone     = clean_input($_POST['phone'] ?? '');
        $phone         = preg_replace('/[^0-9]/', '', $raw_phone);
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $gstin       = strtoupper(clean_input($_POST['gstin'] ?? ''));
        $pan_number  = strtoupper(clean_input($_POST['pan_number'] ?? ''));
        $address     = clean_input($_POST['address'] ?? '');
        $city        = clean_input($_POST['city'] ?? '');
        $state       = clean_input($_POST['state'] ?? 'Uttar Pradesh');
        $pincode     = clean_input($_POST['pincode'] ?? '');
        $agree_terms = !empty($_POST['agree_terms']);

        // Validation
        if (empty($business_name) || empty($contact_name)) {
            $error = 'कृपया अपनी दुकान/व्यवसाय का नाम और संपर्क व्यक्ति का नाम दर्ज करें।';
        } elseif (empty($phone) || strlen($phone) !== 10) {
            $error = 'कृपया वैध 10 अंकों का मोबाइल नंबर दर्ज करें।';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'कृपया वैध ईमेल पता दर्ज करें।';
        } elseif (strlen($password) < 6) {
            $error = 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।';
        } elseif ($password !== $confirm_password) {
            $error = 'पासवर्ड और कन्फर्म पासवर्ड मेल नहीं खाते।';
        } elseif (empty($address) || empty($city) || empty($pincode)) {
            $error = 'कृपया डिलीवरी/दुकान का पूरा पता, शहर और पिनकोड दर्ज करें।';
        } elseif (strlen($pincode) !== 6 || !is_numeric($pincode)) {
            $error = 'कृपया वैध 6 अंकों का भारतीय पिनकोड दर्ज करें।';
        } elseif (!$agree_terms) {
            $error = 'कृपया थोक विक्रेता नियम व शर्तों (B2B Terms) को स्वीकार करें।';
        } else {
            // Check if phone or email already in use
            $chk = $pdo->prepare("SELECT id, role FROM users WHERE phone = ? OR (email != '' AND email = ?)");
            $chk->execute([$phone, $email]);
            $existing = $chk->fetch();

            if ($existing) {
                $error = 'यह मोबाइल नंबर या ईमेल पहले से पंजीकृत है। कृपया लॉगिन करें या दूसरा नंबर उपयोग करें।';
            } else {
                try {
                    $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (
                            name, email, phone, password, role,
                            business_name, gstin, business_type, pan_number,
                            business_address, business_city, business_state, business_pincode,
                            created_at
                        ) VALUES (
                            ?, ?, ?, ?, 'retailer',
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            NOW()
                        )
                    ");
                    $stmt->execute([
                        $contact_name,
                        $email,
                        $phone,
                        $hashed_pwd,
                        $business_name,
                        $gstin,
                        $business_type,
                        $pan_number,
                        $address,
                        $city,
                        $state,
                        $pincode
                    ]);

                    $retailer_id = $pdo->lastInsertId();

                    // Start session
                    $_SESSION['user_id'] = $retailer_id;
                    $_SESSION['user_name'] = $contact_name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_role'] = 'retailer';
                    $_SESSION['business_name'] = $business_name;

                    log_audit_action('retailer_registered', 'users', "New B2B Retailer registered: {$business_name} (ID: {$retailer_id})");
                    set_flash('success', 'बधाई हो! आपका GanvGaliHat थोक विक्रेता (B2B Retailer) खाता सफलतापूर्वक बन गया है। थोक दरों पर उत्पाद खोजें!');
                    header('Location: /retailer/dashboard.php');
                    exit;
                } catch (Exception $e) {
                    $error = 'पंजीकरण में त्रुटि: ' . $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'थोक विक्रेता (B2B Retailer) खाता बनाएं — GanvGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gradient-to-b from-amber-50/60 via-stone-50 to-white py-10 sm:py-16">
  <div class="max-w-4xl mx-auto px-4 sm:px-6">
    
    <!-- Hero / Headline -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-xs mb-3">
        <i class="fa-solid fa-boxes-packing text-amber-600"></i>
        B2B थोक विक्रेता एवं दुकानदार रजिस्ट्रेशन
      </span>
      <h1 class="text-2xl sm:text-4xl font-black text-gray-900 tracking-tight">
        दुकान के लिए सीधे गांव से <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-orange-600">थोक भाव</span> पर खरीदें
      </h1>
      <p class="text-sm sm:text-base text-gray-600 mt-2 font-medium">
        बिना किसी बिचौलिए के सीधे कारीगरों और ग्रामीण उत्पादकों से होलसेल माल मंगवाएं। 20% से 45% तक का शुद्ध मुनाफा कमाएं।
      </p>
    </div>

    <!-- 4 Trust Highlights -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
      <div class="bg-white p-3 sm:p-4 rounded-2xl border border-amber-100 shadow-xs text-center">
        <div class="w-10 h-10 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-2">
          <i class="fa-solid fa-tags"></i>
        </div>
        <div class="text-xs sm:text-sm font-black text-gray-900">फैक्ट्री थोक दरें</div>
        <div class="text-[11px] text-gray-500">सीधे क्लस्टर से माल</div>
      </div>
      <div class="bg-white p-3 sm:p-4 rounded-2xl border border-amber-100 shadow-xs text-center">
        <div class="w-10 h-10 mx-auto rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg mb-2">
          <i class="fa-solid fa-file-invoice"></i>
        </div>
        <div class="text-xs sm:text-sm font-black text-gray-900">पक्का GST बिल</div>
        <div class="text-[11px] text-gray-500">Input Tax Credit (ITC)</div>
      </div>
      <div class="bg-white p-3 sm:p-4 rounded-2xl border border-amber-100 shadow-xs text-center">
        <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-2">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <div class="text-xs sm:text-sm font-black text-gray-900">डोरस्टेप फ्रेट डिलीवरी</div>
        <div class="text-[11px] text-gray-500">19,000+ पिनकोड कवरेज</div>
      </div>
      <div class="bg-white p-3 sm:p-4 rounded-2xl border border-amber-100 shadow-xs text-center">
        <div class="w-10 h-10 mx-auto rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-2">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="text-xs sm:text-sm font-black text-gray-900">100% असली उत्पाद</div>
        <div class="text-[11px] text-gray-500">ODOP व SHG प्रमाणित</div>
      </div>
    </div>

    <!-- Registration Card -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl overflow-hidden">
      <div class="px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-lg font-black flex items-center gap-2">
            <i class="fa-solid fa-store"></i>
            नया थोक खाता बनाएं (B2B Retailer Account)
          </h2>
          <p class="text-xs text-amber-100 mt-0.5">मात्र 2 मिनट में निःशुल्क खाता बनाएं और थोक कैटलॉग एक्सेस करें</p>
        </div>
        <div class="text-xs">
          पहले से खाता है?
          <a href="/login.php?tab=retailer" class="font-bold underline text-white hover:text-amber-200 ml-1">
            यहाँ लॉगिन करें &rarr;
          </a>
        </div>
      </div>

      <form method="POST" action="/retailer-register.php" class="p-6 sm:p-8 space-y-6">
        <?= csrf_field() ?>

        <?php if (!empty($error)): ?>
          <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5"></i>
            <div><?= htmlspecialchars($error) ?></div>
          </div>
        <?php endif; ?>

        <!-- Section 1: Business Details -->
        <div>
          <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 mb-3">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">1</span>
            दुकान व व्यापार विवरण (Business & Shop Details)
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                दुकान / फर्म का नाम (Shop / Business Name) <span class="text-red-500">*</span>
              </label>
              <input type="text" name="business_name" value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>" required
                placeholder="उदा. गुप्ता किराना स्टोर / श्री राम हैंडलूम"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                व्यापार प्रकार / श्रेणी (Business Category) <span class="text-red-500">*</span>
              </label>
              <select name="business_type" class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
                <option value="Kirana / Grocery">किराना व जनरल स्टोर (Kirana & Grocery)</option>
                <option value="Handloom / Apparel">हैंडलूम व वस्त्र विक्रेता (Handloom & Clothing)</option>
                <option value="Hardware / Agro Tools">हार्डवेयर व कृषि औजार (Agro & Hardware)</option>
                <option value="Supermarket / Departmental">सुपरमार्केट / डिपार्टमेंटल स्टोर (Supermarket)</option>
                <option value="Ayurveda / Organic Outlet">आयुर्वेदिक व जैविक उत्पाद (Organic / Wellness)</option>
                <option value="Wholesaler / Reseller">थोक डीलर / ऑनलाइन रीसेलर (Wholesale / Reseller)</option>
                <option value="Other">अन्य खुदरा व्यापार (Other Retail)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section 2: Contact Person Details -->
        <div>
          <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 mb-3">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">2</span>
            मालिक / संपर्क व्यक्ति विवरण (Contact & Login Details)
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                संपर्क व्यक्ति का नाम (Full Name) <span class="text-red-500">*</span>
              </label>
              <input type="text" name="contact_name" value="<?= htmlspecialchars($_POST['contact_name'] ?? '') ?>" required
                placeholder="उदा. रमेश कुमार गुप्ता"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                व्हाट्सएप / मोबाइल नंबर (10 Digits) <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3.5 top-2.5 text-xs font-bold text-gray-400">+91</span>
                <input type="tel" name="phone" maxlength="10" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required
                  placeholder="9876543210"
                  class="w-full pl-12 pr-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                ईमेल पता (Email Address)
              </label>
              <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                placeholder="shop@example.com (वैकल्पिक)"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                खाता पासवर्ड (Create Password - Min 6 chars) <span class="text-red-500">*</span>
              </label>
              <input type="password" name="password" required minlength="6"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                पासवर्ड कन्फर्म करें (Confirm Password) <span class="text-red-500">*</span>
              </label>
              <input type="password" name="confirm_password" required minlength="6"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>
          </div>
        </div>

        <!-- Section 3: Tax & Address Details -->
        <div>
          <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 mb-3">
            <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">3</span>
            टैक्स व डिलीवरी पता (GST, PAN & Godown Address)
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                GSTIN नंबर (15 Digits - यदि उपलब्ध हो)
              </label>
              <input type="text" name="gstin" maxlength="15" value="<?= htmlspecialchars($_POST['gstin'] ?? '') ?>"
                placeholder="उदा. 09ABCDE1234F1Z5"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-amber-600 transition">
              <p class="text-[10px] text-gray-500 mt-0.5">GST नंबर दर्ज करने पर पक्के टैक्स इनवॉइस में ITC इनपुट क्लेम मिलेगा।</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                PAN नंबर (10 Digits)
              </label>
              <input type="text" name="pan_number" maxlength="10" value="<?= htmlspecialchars($_POST['pan_number'] ?? '') ?>"
                placeholder="उदा. ABCDE1234F"
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                दुकान / गोदाम का पूरा पता (Shop / Godown Delivery Address) <span class="text-red-500">*</span>
              </label>
              <textarea name="address" rows="2" required
                placeholder="मकान/दुकान नं., सड़क, लैंडमार्क, बाजार..."
                class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:border-amber-600 transition"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  शहर / कस्बा (City / Town) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required
                  placeholder="उदा. हरदोई / लखनऊ"
                  class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  राज्य (State) <span class="text-red-500">*</span>
                </label>
                <select name="state" class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
                  <option value="Uttar Pradesh">उत्तर प्रदेश (Uttar Pradesh)</option>
                  <option value="Madhya Pradesh">मध्य प्रदेश (Madhya Pradesh)</option>
                  <option value="Bihar">बिहार (Bihar)</option>
                  <option value="Delhi">दिल्ली (Delhi NCR)</option>
                  <option value="Rajasthan">राजस्थान (Rajasthan)</option>
                  <option value="Haryana">हरियाणा (Haryana)</option>
                  <option value="Punjab">पंजाब (Punjab)</option>
                  <option value="Uttarakhand">उत्तराखंड (Uttarakhand)</option>
                  <option value="Gujarat">गुजरात (Gujarat)</option>
                  <option value="Maharashtra">महाराष्ट्र (Maharashtra)</option>
                  <option value="West Bengal">पश्चिम बंगाल (West Bengal)</option>
                  <option value="Other">अन्य राज्य (Other States)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  पिनकोड (6 Digits Pincode) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="pincode" maxlength="6" value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>" required
                  placeholder="उदा. 241126"
                  class="w-full px-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
              </div>
            </div>
          </div>
        </div>

        <!-- Section 4: Terms Checkbox -->
        <div class="pt-2 border-t border-gray-100">
          <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="agree_terms" value="1" required class="mt-1 w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
            <span class="text-xs text-gray-600">
              मैं प्रमाणित करता हूँ कि मैं एक दुकानदार/व्यापारी/रीसेलर हूँ और मैं GanvGaliHat के <a href="/terms.php" target="_blank" class="text-amber-600 font-bold underline">B2B थोक नियम व शर्तों</a> एवं <a href="/privacy.php" target="_blank" class="text-amber-600 font-bold underline">गोपनीयता नीति</a> से सहमत हूँ।
            </span>
          </label>
        </div>

        <!-- Submit Button -->
        <div>
          <button type="submit" class="w-full py-3.5 px-6 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-base rounded-2xl shadow-lg shadow-amber-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
            <i class="fa-solid fa-boxes-packing"></i>
            थोक खाता बनाएं एवं कैटलॉग देखें (Create Wholesale Account)
          </button>
          <div class="text-center text-xs text-gray-500 mt-3 flex items-center justify-center gap-2">
            <i class="fa-solid fa-lock text-emerald-600"></i>
            100% सुरक्षित डेटा एवं सीधे गांव के क्लस्टरों से विश्वसनीय आपूर्ति
          </div>
        </div>

      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
