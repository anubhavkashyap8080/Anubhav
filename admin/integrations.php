<?php
/**
 * GanvGaliHat Central Integrations Hub
 * Google Analytics 4, Search Console, Firebase Cloud Messaging & Payment Gateways
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Clearance
require_admin();

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
        'payu_enabled',
        'payu_merchant_key',
        'payu_mode',
        'cod_enabled',
        // Multi-Carrier Delivery Partners & Logistics Hub
        'indiapost_enabled',
        'shipping_base_local',
        'shipping_base_up',
        'shipping_base_north',
        'shipping_base_other',
        'shipping_base_remote',
        'shipping_heavy_per_kg',
        'free_shipping_threshold',
        'shiprocket_enabled',
        'shiprocket_email',
        'shiprocket_password',
        'shiprocket_pickup_pincode',
        'delhivery_enabled',
        'delhivery_token',
        'delhivery_client_id',
        'nimbuspost_enabled',
        'nimbuspost_secret'
    ];

    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $upd = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value");
        } else {
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
        set_flash('success', 'All API integrations settings saved successfully!');
        header('Location: /admin/integrations.php');
        exit;
    } catch (Throwable $e) {
        set_flash('error', 'Error saving integrations: ' . $e->getMessage());
        header('Location: /admin/integrations.php');
        exit;
    }
}

// Handle Test Notification Trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_notification'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $default_admin_mail = 'info@' . (parse_url(SITE_URL, PHP_URL_HOST) ?: 'ganvgalihat.com');
        $test_recipient = clean_input($_POST['test_recipient'] ?? $default_admin_mail);
        $nid = queue_notification($_SESSION['user_id'] ?? null, $test_recipient, 'email', 'GanvGaliHat Integration Test', 'This is a test notification. Your notification system is fully operational.');
        log_audit_action('test_notification_sent', 'integrations', 'notification', $nid, 'Test notification queued to ' . $test_recipient);
        set_flash('success', "Test notification queued successfully (Queue ID #$nid)!");
        header('Location: /admin/integrations.php');
        exit;
    }
}

// Fetch Current Settings
$settings_raw = [];
try {
    $stmt_set = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    if ($stmt_set) {
        while ($r = $stmt_set->fetch(PDO::FETCH_ASSOC)) {
            $settings_raw[$r['setting_key']] = $r['setting_value'];
        }
    }
} catch (Throwable $e_set) {}

if (!function_exists('mask_secret')) {
    function mask_secret($secret) {
        if (empty($secret)) return '';
        $len = strlen($secret);
        if ($len <= 6) return '••••••';
        return substr($secret, 0, 3) . str_repeat('•', max(4, $len - 6)) . substr($secret, -3);
    }
}

$page_title = 'Integrations Hub — Master Admin';
require_once __DIR__ . '/admin_header.php';
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30">
        <i class="fa-solid fa-network-wired"></i> Enterprise Integrations Hub
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">API & Services Integrations Hub</h1>
      <p class="text-indigo-200 text-xs sm:text-sm max-w-2xl leading-relaxed">
        Centralized management of Google Analytics 4, Search Console, Firebase Cloud Messaging (FCM), and Payment Gateways.
      </p>
    </div>

    <div class="flex items-center gap-3">
      <a href="/feeds/google_merchant.php" target="_blank" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition border border-white/20 flex items-center justify-center gap-2">
        <i class="fa-brands fa-google text-amber-300"></i>
        Google Merchant Feed (Live XML)
      </a>
    </div>
  </div>

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
            <h2 class="text-base font-extrabold text-gray-900">Google Analytics 4 (GA4) E-Commerce Tracking</h2>
            <p class="text-xs text-gray-500">Track product views, cart updates, checkouts, and purchase events automatically.</p>
          </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold <?= !empty($settings_raw['ga4_measurement_id']) ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-500' ?>">
          <?= !empty($settings_raw['ga4_measurement_id']) ? 'Active (Configured)' : 'Inactive' ?>
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            GA4 Measurement ID
          </label>
          <input type="text" name="ga4_measurement_id" value="<?= htmlspecialchars($settings_raw['ga4_measurement_id'] ?? $settings_raw['seo_google_analytics'] ?? '') ?>" placeholder="e.g. G-XXXXXXXXXX"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <p class="text-[11px] text-gray-400 mt-1">Enter your Measurement ID from your Google Analytics web data stream.</p>
        </div>

        <div class="space-y-3">
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            E-Commerce & Privacy Preferences
          </label>
          <div class="flex items-center gap-2">
            <input type="checkbox" name="ga4_ecommerce_tracking" value="1" id="ga4_ecom" <?= ($settings_raw['ga4_ecommerce_tracking'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
            <label for="ga4_ecom" class="text-xs font-bold text-gray-700">Enable automatic de-duplicated e-commerce events (Purchases & Cart)</label>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" name="ga4_consent_mode" value="1" id="ga4_consent" <?= ($settings_raw['ga4_consent_mode'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
            <label for="ga4_consent" class="text-xs font-medium text-gray-700">Enable Google Consent Mode v2 support</label>
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
            <p class="text-xs text-gray-500">Ownership verification, indexing status, and automatic XML sitemaps.</p>
          </div>
        </div>
        <a href="/sitemap.php" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
          <span>View Live Sitemap</span>
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
            Property URL (Canonical Domain)
          </label>
          <input type="text" name="gsc_property_url" value="<?= htmlspecialchars($settings_raw['gsc_property_url'] ?? $settings_raw['seo_canonical_url'] ?? (defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com')) ?>"
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
            <p class="text-xs text-gray-500">Order alerts, artisan updates, dispatch tracking, and web push notifications.</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="firebase_enabled" value="1" id="fcm_enable" <?= ($settings_raw['firebase_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          <label for="fcm_enable" class="text-xs font-bold text-gray-700">Enable FCM</label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 block mb-1.5">
            Firebase Project ID
          </label>
          <input type="text" name="firebase_project_id" value="<?= htmlspecialchars($settings_raw['firebase_project_id'] ?? 'ganvgalihat-rural-apps') ?>"
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
            <h2 class="text-base font-extrabold text-gray-900">Payment Gateway Credentials (Razorpay, PayU & COD)</h2>
            <p class="text-xs text-gray-500">Server-side keys, webhook verification signatures, and transaction settings.</p>
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
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Key Secret (Masked, Config/Env)</label>
            <input type="text" name="razorpay_key_secret" readonly value="<?= htmlspecialchars(mask_secret(defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : ($settings_raw['razorpay_key_secret'] ?? ''))) ?>" class="w-full px-3 py-2 bg-stone-100 border border-stone-300 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
            <span class="text-[10px] text-gray-400 mt-0.5 block">Managed via config/env for security</span>
          </div>
        </div>

        <!-- PayU Box -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span> PayU India
            </span>
            <input type="checkbox" name="payu_enabled" value="1" <?= ($settings_raw['payu_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Key</label>
            <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars($settings_raw['payu_merchant_key'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Salt (Masked, Config/Env)</label>
            <input type="text" name="payu_merchant_salt" readonly value="<?= htmlspecialchars(mask_secret(defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : ($settings_raw['payu_merchant_salt'] ?? ''))) ?>" class="w-full px-3 py-2 bg-stone-100 border border-stone-300 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
            <span class="text-[10px] text-gray-400 mt-0.5 block">Managed via config/env for security</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Multi-Carrier Delivery Partners & Logistics Hub (Last-Mile Rural Reach) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
            All-India Logistics & Rural Delivery
          </span>
          <h2 class="text-base font-extrabold text-gray-900 mt-1 flex items-center gap-2">
            <i class="fa-solid fa-truck-fast text-amber-600"></i>
            Delivery Partners Hub (19,000+ PIN Codes Reach)
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Real-time shipping rate estimation, volumetric weight calculation & deep rural last-mile reach.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Multi-Carrier Active
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Partner 1: India Post Speed Post -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-envelope-open-text"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">India Post (Speed Post / पार्सल)</h3>
                <p class="text-[10px] text-gray-500 font-semibold">100% Pan-India Reach (1.55 Lakh Post Offices & Every Village)</p>
              </div>
            </div>
            <input type="checkbox" name="indiapost_enabled" value="1" <?= ($settings_raw['indiapost_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-amber-600">
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Local / Sandila Base (₹)</label>
              <input type="number" step="1" name="shipping_base_local" value="<?= htmlspecialchars($settings_raw['shipping_base_local'] ?? '30') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Within UP Base (₹)</label>
              <input type="number" step="1" name="shipping_base_up" value="<?= htmlspecialchars($settings_raw['shipping_base_up'] ?? '40') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">North India Base (₹)</label>
              <input type="number" step="1" name="shipping_base_north" value="<?= htmlspecialchars($settings_raw['shipping_base_north'] ?? '55') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Rest of India Base (₹)</label>
              <input type="number" step="1" name="shipping_base_other" value="<?= htmlspecialchars($settings_raw['shipping_base_other'] ?? '65') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
          </div>
        </div>

        <!-- Partner 2: Shiprocket Multi-Carrier API -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-rocket"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Shiprocket API (14+ Couriers)</h3>
                <p class="text-[10px] text-gray-500 font-semibold">Delhivery, Blue Dart, Xpressbees, Shadowfax, DTDC</p>
              </div>
            </div>
            <input type="checkbox" name="shiprocket_enabled" value="1" <?= ($settings_raw['shiprocket_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>

          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Shiprocket Registered Email</label>
            <input type="email" name="shiprocket_email" value="<?= htmlspecialchars($settings_raw['shiprocket_email'] ?? '') ?>" placeholder="e.g. logistics@ganvgalihat.com" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Shiprocket Password / Key</label>
              <input type="password" name="shiprocket_password" value="<?= htmlspecialchars($settings_raw['shiprocket_password'] ?? '') ?>" placeholder="••••••••" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
            </div>
            <div>
              <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Pickup PIN Code</label>
              <input type="text" maxlength="6" name="shiprocket_pickup_pincode" value="<?= htmlspecialchars($settings_raw['shiprocket_pickup_pincode'] ?? '241204') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono font-bold">
            </div>
          </div>
        </div>

        <!-- Partner 3: Delhivery Direct -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-red-700 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-truck-ramp-box"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Delhivery Direct Express</h3>
                <p class="text-[10px] text-gray-500 font-semibold">18,500+ PIN Codes B2C Surface Fleet</p>
              </div>
            </div>
            <input type="checkbox" name="delhivery_enabled" value="1" <?= ($settings_raw['delhivery_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-red-600">
          </div>

          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Delhivery Client Token / API Key</label>
            <input type="text" name="delhivery_token" value="<?= htmlspecialchars($settings_raw['delhivery_token'] ?? '') ?>" placeholder="e.g. delh_live_api_token" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Client ID / Warehouse Name</label>
            <input type="text" name="delhivery_client_id" value="<?= htmlspecialchars($settings_raw['delhivery_client_id'] ?? 'GanvGaliHat_Sandila') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
          </div>
        </div>

        <!-- Global Thresholds & Volumetric Rules -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-tags"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Free Shipping & Heavy Parcel Rules</h3>
                <p class="text-[10px] text-gray-500 font-semibold">Automatic Threshold Discounts & Volumetric Weight Formula</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Free Shipping Threshold (₹)</label>
              <input type="number" step="1" name="free_shipping_threshold" value="<?= htmlspecialchars($settings_raw['free_shipping_threshold'] ?? '499') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold text-emerald-700">
              <span class="text-[10px] text-gray-400 mt-0.5 block">Orders above this get free delivery</span>
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Heavy Parcel Surcharge (₹ / kg)</label>
              <input type="number" step="1" name="shipping_heavy_per_kg" value="<?= htmlspecialchars($settings_raw['shipping_heavy_per_kg'] ?? '15') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
              <span class="text-[10px] text-gray-400 mt-0.5 block">Applied for parcels exceeding 1 kg</span>
            </div>
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed">
            <i class="fa-solid fa-cube text-amber-700 mr-1"></i>
            <strong>Volumetric Weight Rule:</strong> <code>(L × W × H) / 5000</code>. Couriers bill on whichever is higher between dead weight and volumetric weight.
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end gap-3 pt-2">
      <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-lg transition flex items-center gap-2 cursor-pointer">
        <i class="fa-solid fa-floppy-disk text-sm"></i>
        <span>Save All Integrations</span>
      </button>
    </div>
  </form>

  <!-- Test Notification Trigger Form -->
  <div class="bg-stone-50 rounded-3xl p-6 border border-stone-200">
    <h3 class="text-xs font-black uppercase tracking-wider text-gray-800 mb-2 flex items-center gap-2">
      <i class="fa-solid fa-paper-plane text-indigo-600"></i>
      Send Test Email / Notification
    </h3>
    <form method="POST" class="flex flex-col sm:flex-row items-center gap-3">
      <?= csrf_field() ?>
      <input type="hidden" name="test_notification" value="1">
      <input type="email" name="test_recipient" value="<?= htmlspecialchars(defined('OWNER_ALERT_EMAIL') && OWNER_ALERT_EMAIL ? OWNER_ALERT_EMAIL : 'info@ganvgalihat.com') ?>" class="flex-1 w-full px-4 py-2.5 bg-white border border-stone-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl transition shadow-xs">
        Trigger Test Notification
      </button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
