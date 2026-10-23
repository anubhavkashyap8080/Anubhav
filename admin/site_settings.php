<?php
$page_title = 'गेटवे एवं प्लेटफ़ॉर्म सेटिंग्स (Gateways & Shipping Settings)';
require_once __DIR__ . '/admin_header.php';

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $allowed_keys = [
        'brand_name', 'company_name', 'brand_tagline', 'announcement_text',
        'contact_phone', 'contact_email', 'factory_address', 'head_office_address',
        'free_shipping_threshold', 'shipping_base_up', 'shipping_base_north', 'shipping_base_other', 'shipping_heavy_per_kg',
        'razorpay_enabled', 'razorpay_key_id', 'razorpay_key_secret',
        'payu_enabled', 'payu_merchant_key', 'payu_merchant_salt', 'payu_mode',
        'cod_enabled', 'commission_rate_default'
    ];

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        // SQLite fallback for ON DUPLICATE KEY
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        foreach ($allowed_keys as $key) {
            $val = $_POST[$key] ?? '0';
            $stmt_up->execute([$key, trim((string)$val)]);
        }

        set_flash('success', 'पेमेंट गेटवे, शिपिंग दरें एवं साइट सेटिंग्स सफलतापूर्वक सहेजी गईं!');
        header('Location: /admin/site_settings.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'सेटिंग्स सहेजने में त्रुटि: ' . $e->getMessage());
    }
}

// Fetch all current settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$curr_settings = [];
while ($row = $stmt_settings->fetch()) {
    $curr_settings[$row['setting_key']] = $row['setting_value'];
}

function get_setting_val($key, $default = '') {
    global $curr_settings;
    return $curr_settings[$key] ?? $default;
}
?>

<!-- Title Bar -->
<div class="mb-6">
  <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
    <i class="fa-solid fa-sliders text-indigo-600"></i>
    पेमेंट गेटवे एवं सटीक शिपिंग कॉन्फ़िगरेशन
  </h1>
  <p class="text-xs text-gray-500 mt-1">
    Razorpay, PayU Money, Cash on Delivery (COD), एवं पिनकोड आधारित डिलीवरी दरें
  </p>
</div>

<form action="/admin/site_settings.php" method="POST" class="space-y-8">
  <input type="hidden" name="save_settings" value="1">

  <!-- Payment Gateways Section -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-credit-card text-emerald-600"></i>
      1. भुगतान गेटवे एकीकरण (Payment Gateways Integration)
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Razorpay Card -->
      <div class="bg-stone-50 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
              <i class="fa-solid fa-bolt text-blue-600"></i> Razorpay
            </span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="razorpay_enabled" value="1" <?= get_setting_val('razorpay_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          <p class="text-xs text-gray-500 mb-4">UPI, Google Pay, PhonePe, Cards और NetBanking</p>

          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Razorpay Key ID</label>
              <input type="text" name="razorpay_key_id" value="<?= htmlspecialchars(get_setting_val('razorpay_key_id', 'rzp_test_1DP5mmOlF5G5ag')) ?>"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Razorpay Key Secret</label>
              <input type="password" name="razorpay_key_secret" value="<?= htmlspecialchars(get_setting_val('razorpay_key_secret', 's9F2xKlY02jK3991lmxO92aa')) ?>"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
            </div>
          </div>
        </div>
        <span class="text-[10px] text-gray-400">Webhook: /payments/razorpay_verify.php</span>
      </div>

      <!-- PayU Card -->
      <div class="bg-stone-50 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
              <i class="fa-solid fa-lock text-emerald-600"></i> PayU Money
            </span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="payu_enabled" value="1" <?= get_setting_val('payu_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
          </div>
          <p class="text-xs text-gray-500 mb-4">RuPay, Credit/Debit Cards, NetBanking और Wallets</p>

          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Merchant Key</label>
              <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars(get_setting_val('payu_merchant_key', 'gtKFFx')) ?>"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">Merchant Salt</label>
              <input type="password" name="payu_merchant_salt" value="<?= htmlspecialchars(get_setting_val('payu_merchant_salt', 'eCwWELxi')) ?>"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">गेटवे मोड</label>
              <select name="payu_mode" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold">
                <option value="test" <?= get_setting_val('payu_mode', 'test') === 'test' ? 'selected' : '' ?>>Test Mode (Sandbox)</option>
                <option value="live" <?= get_setting_val('payu_mode') === 'live' ? 'selected' : '' ?>>Production (Live)</option>
              </select>
            </div>
          </div>
        </div>
        <span class="text-[10px] text-gray-400">Callback: /payments/payu_callback.php</span>
      </div>

      <!-- Cash on Delivery (COD) Card -->
      <div class="bg-stone-50 p-5 rounded-2xl border border-gray-200 flex flex-col justify-between space-y-4">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
              <i class="fa-solid fa-hand-holding-dollar text-brand-green"></i> कैश ऑन डिलीवरी (COD)
            </span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="cod_enabled" value="1" <?= get_setting_val('cod_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-green"></div>
            </label>
          </div>
          <p class="text-xs text-gray-500 mb-4">भारतीय डाक (India Post) या कूरियर द्वारा घर पर नकद भुगतान</p>

          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">डिफ़ॉल्ट कमीशन दर (%)</label>
              <input type="number" step="0.1" name="commission_rate_default" value="<?= htmlspecialchars(get_setting_val('commission_rate_default', '4.00')) ?>"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold">
            </div>
          </div>
        </div>
        <span class="text-[10px] text-gray-400">COD आर्डर स्वतः कन्फर्म होकर डिस्पैच कतार में आते हैं</span>
      </div>

    </div>
  </div>

  <!-- Automatic Shipping Calculator Section -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-truck text-red-600"></i>
      2. स्वचालित सटीक शिपिंग मूल्य कैलकुलेटर (Shipping Price Calculator Rules)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुफ़्त डिलीवरी थ्रेसहोल्ड (₹)</label>
        <input type="number" name="free_shipping_threshold" value="<?= htmlspecialchars(get_setting_val('free_shipping_threshold', '499')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">₹499 या अधिक के आर्डर पर मुफ़्त डिलीवरी</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 1 (उत्तर प्रदेश) बेस दर (₹)</label>
        <input type="number" name="shipping_base_up" value="<?= htmlspecialchars(get_setting_val('shipping_base_up', '40')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">पिनकोड 20xxxx से 28xxxx</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 2 (उत्तर भारत) बेस दर (₹)</label>
        <input type="number" name="shipping_base_north" value="<?= htmlspecialchars(get_setting_val('shipping_base_north', '60')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">दिल्ली, हरियाणा, पंजाब, राजस्थान, MP, बिहार</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 3+ (शेष भारत) बेस दर (₹)</label>
        <input type="number" name="shipping_base_other" value="<?= htmlspecialchars(get_setting_val('shipping_base_other', '80')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">दक्षिण, पश्चिम एवं पूर्वोत्तर राज्य</p>
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">भारी कृषि औजार अधिभार (>2kg होने पर प्रति 0.5kg दर ₹)</label>
        <input type="number" name="shipping_heavy_per_kg" value="<?= htmlspecialchars(get_setting_val('shipping_heavy_per_kg', '15')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">लोहे के फावड़ा, कुल्हाड़ी व हथौड़ों पर स्वचालित रूप से जोड़ा जाएगा</p>
      </div>
    </div>
  </div>

  <!-- Company & Contact Details -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-building text-indigo-600"></i>
      3. संस्था एवं संपर्क जानकारी (S S Industries Enterprise)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">कंपनी का नाम</label>
        <input type="text" name="company_name" value="<?= htmlspecialchars(get_setting_val('company_name', 'S S Industries')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">हेल्पलाइन फ़ोन</label>
        <input type="text" name="contact_phone" value="<?= htmlspecialchars(get_setting_val('contact_phone', '+91 7897492100')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">आधिकारिक ईमेल</label>
        <input type="email" name="contact_email" value="<?= htmlspecialchars(get_setting_val('contact_email', 'namaste@ganvgalihat.com')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">फ़ैक्टरी का पता (Works / Manufacturing)</label>
        <input type="text" name="factory_address" value="<?= htmlspecialchars(get_setting_val('factory_address', 'Works / Factory: Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">हेडर घोषणा पट्टी (Announcement Ticker)</label>
        <textarea name="announcement_text" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs"><?= htmlspecialchars(get_setting_val('announcement_text', '🌾 S S Industries Tools, Awadh Pulses, Banarasi Pickles & Village Crafts: Direct from Rural Artisans | Free Delivery Over ₹499 | Cash on Delivery Available')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Save Button -->
  <div class="flex items-center justify-end gap-3 pb-8">
    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      सभी सेटिंग्स सहेजें (Save All Settings)
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
