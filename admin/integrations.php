<?php
/**
 * GaunGaliHat Central Integrations Hub
 * Google Analytics 4, Search Console, Firebase Cloud Messaging & Payment Gateways
 */

$page_title = 'इंटीग्रेशन्स हब (Integrations Hub)';
require_once __DIR__ . '/admin_header.php';

// Strict Admin Clearance
require_admin();

$msg = '';
$msg_type = 'success';

// Handle Saving Integrations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_integrations'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/integrations.php');
        exit;
    }

    $allowed_keys = [
        // Google Services
        'ga4_measurement_id',
        'ga4_ecommerce_tracking',
        'ga4_consent_mode',
        'gsc_verification_tag',
        'gsc_property_url',
        // Firebase Cloud Messaging
        'firebase_enabled',
        'firebase_project_id',
        'firebase_fcm_server_key',
        'firebase_vapid_key',
        // Payment Gateways
        'razorpay_enabled',
        'razorpay_key_id',
        'razorpay_key_secret',
        'payu_enabled',
        'payu_merchant_key',
        'payu_merchant_salt',
        'payu_mode',
        'cod_enabled'
    ];

    $upd = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value");
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
        $upd = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    }

    foreach ($allowed_keys as $key) {
        if (isset($_POST[$key])) {
            $val = trim((string)$_POST[$key]);
            // If masked secret wasn't changed, don't overwrite with asterisks
            if (strpos($val, '••••') !== false) {
                continue;
            }
            $upd->execute([$key, $val]);
        }
    }

    log_audit_action('integrations_updated', 'integrations', 'settings', null, 'API integrations configuration saved');
    $msg = 'सभी API इंटीग्रेशन्स सेटिंग्स सफलतापूर्वक सुरक्षित कर दी गईं!';
}

// Handle Test Notification Trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_notification'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $test_recipient = clean_input($_POST['test_recipient'] ?? 'admin@ganvgalihat.com');
        $nid = queue_notification($_SESSION['user_id'] ?? null, $test_recipient, 'email', 'GaunGaliHat Integration Test', 'यह एक परीक्षण सूचना है। आपकी नोटिफिकेशन प्रणाली पूर्ण रूप से कार्यरत है।');
        log_audit_action('test_notification_sent', 'integrations', 'notification', $nid, 'Test notification queued to ' . $test_recipient);
        $msg = "टेस्ट नोटिफिकेशन कतार (Queue ID #$nid) में सफलतापूर्वक जोड़ दिया गया!";
    }
}

// Fetch Current Settings
$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

function mask_secret($secret) {
    if (empty($secret)) return '';
    $len = strlen($secret);
    if ($len <= 6) return '••••••';
    return substr($secret, 0, 3) . str_repeat('•', max(4, $len - 6)) . substr($secret, -3);
}
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30">
        <i class="fa-solid fa-network-wired"></i> Enterprise Integrations Hub
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">इंटीग्रेशन्स हब (API & Services Hub)</h1>
      <p class="text-indigo-200 text-xs sm:text-sm max-w-2xl leading-relaxed">
        Google Analytics 4 ई-कॉमर्स इवेंट ट्रैकिंग, Search Console, Firebase Cloud Messaging (FCM), और पेमेंट गेटवे क्रेडेंशियल्स का सुरक्षित केंद्रीय प्रबंधन।
      </p>
    </div>

    <div class="flex items-center gap-3">
      <a href="/feeds/google_merchant.php" target="_blank" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition border border-white/20 flex items-center justify-center gap-2">
        <i class="fa-brands fa-google text-amber-300"></i>
        गूगल मर्चेंट फ़ीड (Live XML)
      </a>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
      <i class="fa-solid fa-circle-check text-base"></i>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-6">
    <?= csrf_field() ?>
    <input type="hidden" name="save_integrations" value="1">

    <!-- 1. Google Analytics 4 (GA4) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
      <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-chart-simple"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-gray-900">Google Analytics 4 (GA4) ई-कॉमर्स ट्रैकिंग</h2>
            <p class="text-xs text-gray-500">उत्पाद पृष्ठ दर्शन (view_item), कार्ट जोड़ना (add_to_cart), चेकआउट और खरीद इवेंट्स</p>
          </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold <?= !empty($settings_raw['ga4_measurement_id']) ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-500' ?>">
          <?= !empty($settings_raw['ga4_measurement_id']) ? 'सक्रिय (Configured)' : 'अक्रिय (Inactive)' ?>
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            GA4 Measurement ID
          </label>
          <input type="text" name="ga4_measurement_id" value="<?= htmlspecialchars($settings_raw['ga4_measurement_id'] ?? $settings_raw['seo_google_analytics'] ?? '') ?>" placeholder="e.g. G-XXXXXXXXXX"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <p class="text-[11px] text-gray-400 mt-1">Google Analytics प्रॉपर्टी स्ट्रीम से प्राप्त ID दर्ज करें।</p>
        </div>

        <div class="space-y-3">
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            ई-कॉमर्स एवं गोपनीयता विकल्प
          </label>
          <div class="flex items-center gap-2">
            <input type="checkbox" name="ga4_ecommerce_tracking" value="1" id="ga4_ecom" <?= ($settings_raw['ga4_ecommerce_tracking'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
            <label for="ga4_ecom" class="text-xs font-bold text-gray-700">स्वचालित डी-डुप्लिकेटेड ई-कॉमर्स इवेंट्स सक्षम करें (Purchases & Cart)</label>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" name="ga4_consent_mode" value="1" id="ga4_consent" <?= ($settings_raw['ga4_consent_mode'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
            <label for="ga4_consent" class="text-xs font-medium text-gray-700">Google Consent Mode v2 समर्थन सक्षम करें</label>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Google Search Console & Sitemaps -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
      <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
            <i class="fa-brands fa-google"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-gray-900">Google Search Console (GSC) & Sitemap Index</h2>
            <p class="text-xs text-gray-500">स्वामित्व सत्यापन, इंडेक्सिंग मॉनिटर और स्वचालित XML साइटमैप</p>
          </div>
        </div>
        <a href="/sitemap.php" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
          <span>लाइव साइटमैप देखें</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            HTML Verification Tag
          </label>
          <input type="text" name="gsc_verification_tag" value="<?= htmlspecialchars($settings_raw['gsc_verification_tag'] ?? $settings_raw['seo_google_console'] ?? '') ?>" placeholder="e.g. google-site-verification=..."
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            प्रॉपर्टी URL (Canonical Domain)
          </label>
          <input type="text" name="gsc_property_url" value="<?= htmlspecialchars($settings_raw['gsc_property_url'] ?? $settings_raw['seo_canonical_url'] ?? 'https://ganvgalihat.com') ?>"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>
    </div>

    <!-- 3. Firebase Cloud Messaging (FCM) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
      <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg">
            <i class="fa-solid fa-fire"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-gray-900">Firebase Cloud Messaging (FCM Push Notifications)</h2>
            <p class="text-xs text-gray-500">आर्डर अलर्ट, कारीगर सूचनाएं, डिस्पैच ट्रैकिंग और वेब पुश अलर्ट्स</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="firebase_enabled" value="1" id="fcm_enable" <?= ($settings_raw['firebase_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          <label for="fcm_enable" class="text-xs font-bold text-gray-700">सक्षम करें</label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            Firebase Project ID
          </label>
          <input type="text" name="firebase_project_id" value="<?= htmlspecialchars($settings_raw['firebase_project_id'] ?? 'gaungalihat-rural-apps') ?>"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            Server API Key (Masked)
          </label>
          <input type="text" name="firebase_fcm_server_key" value="<?= htmlspecialchars(mask_secret($settings_raw['firebase_fcm_server_key'] ?? 'AIzaSyDWbEXAMPLE_SERVER_KEY')) ?>"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            Public VAPID Key (Web Push)
          </label>
          <input type="text" name="firebase_vapid_key" value="<?= htmlspecialchars($settings_raw['firebase_vapid_key'] ?? 'BEXAMPLE_PUBLIC_VAPID_KEY_RURAL_COMMERCE') ?>"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>
    </div>

    <!-- 4. Payment Gateways Status & Credentials -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
      <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-credit-card"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-gray-900">भुगतान गेटवे क्रेडेंशियल्स (Razorpay & PayU & COD)</h2>
            <p class="text-xs text-gray-500">सर्वर-साइड टोकन, वेबहुक सिग्नेचर सत्यापन और सुरक्षित ट्रांजेक्शन गेटवे</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Razorpay Box -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Razorpay Payments
            </span>
            <input type="checkbox" name="razorpay_enabled" value="1" <?= ($settings_raw['razorpay_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Key ID</label>
            <input type="text" name="razorpay_key_id" value="<?= htmlspecialchars($settings_raw['razorpay_key_id'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Key Secret (Masked)</label>
            <input type="text" name="razorpay_key_secret" value="<?= htmlspecialchars(mask_secret($settings_raw['razorpay_key_secret'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
        </div>

        <!-- PayU Box -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span> PayU Money / India
            </span>
            <input type="checkbox" name="payu_enabled" value="1" <?= ($settings_raw['payu_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Key</label>
            <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars($settings_raw['payu_merchant_key'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Salt (Masked)</label>
            <input type="text" name="payu_merchant_salt" value="<?= htmlspecialchars(mask_secret($settings_raw['payu_merchant_salt'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end gap-3 pt-2">
      <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-lg transition flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-floppy-disk text-sm"></i>
        <span>सभी इंटीग्रेशन्स सुरक्षित करें (Save Integrations)</span>
      </button>
    </div>
  </form>

  <!-- Test Notification Trigger Form -->
  <div class="bg-stone-50 rounded-3xl p-6 border border-stone-200">
    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800 mb-2 flex items-center gap-2">
      <i class="fa-solid fa-paper-plane text-indigo-600"></i>
      परीक्षण अधिसूचना भेजें (Send Test Notification)
    </h3>
    <form method="POST" class="flex flex-col sm:flex-row items-center gap-3">
      <?= csrf_field() ?>
      <input type="hidden" name="test_notification" value="1">
      <input type="email" name="test_recipient" value="admin@ssindustries.in" class="flex-1 w-full px-4 py-2.5 bg-white border border-stone-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl transition shadow-xs">
        परीक्षण सूचना भेजें (Trigger Test)
      </button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
