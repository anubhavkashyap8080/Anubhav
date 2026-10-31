<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $allowed_keys = [
        'brand_name', 'company_name', 'brand_tagline', 'announcement_text',
        'contact_phone', 'contact_email', 'factory_address', 'head_office_address',
        'free_shipping_threshold', 'shipping_base_up', 'shipping_base_north', 'shipping_base_other', 'shipping_heavy_per_kg',
        'razorpay_enabled', 'razorpay_key_id', 'razorpay_key_secret',
        'payu_enabled', 'payu_merchant_key', 'payu_merchant_salt', 'payu_mode',
        'cod_enabled', 'commission_rate_default',
        'logo_height_mobile', 'logo_height_desktop', 'site_logo_url', 'logo_gif_url', 'use_gif_logo'
    ];

    // Handle Standard Logo File Upload
    if (isset($_FILES['standard_logo_file']) && $_FILES['standard_logo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('standard_logo_file', 'uploads/logos/');
        if ($upload_res['success']) {
            $_POST['site_logo_url'] = $upload_res['path'];
        } else {
            set_flash('error', 'स्टैंडर्ड लोगो अपलोड विफल: ' . $upload_res['error']);
        }
    }

    // Handle Occasion / GIF Logo File Upload
    if (isset($_FILES['gif_logo_file']) && $_FILES['gif_logo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('gif_logo_file', 'uploads/logos/');
        if ($upload_res['success']) {
            $_POST['logo_gif_url'] = $upload_res['path'];
        } else {
            set_flash('error', 'GIF लोगो अपलोड विफल: ' . $upload_res['error']);
        }
    }

    // Checkbox Defaults
    $_POST['use_gif_logo'] = isset($_POST['use_gif_logo']) ? '1' : '0';
    $_POST['razorpay_enabled'] = isset($_POST['razorpay_enabled']) ? '1' : '0';
    $_POST['payu_enabled'] = isset($_POST['payu_enabled']) ? '1' : '0';
    $_POST['cod_enabled'] = isset($_POST['cod_enabled']) ? '1' : '0';

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        // SQLite fallback for ON DUPLICATE KEY
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        foreach ($allowed_keys as $key) {
            if (!isset($_POST[$key])) {
                continue; // Never overwrite unsubmitted settings with '0'
            }
            $val = trim((string)$_POST[$key]);
            if ($key === 'brand_name' && (empty($val) || $val === '0')) {
                $val = 'GaunGaliHat';
            }
            if ($key === 'brand_tagline' && (empty($val) || $val === '0')) {
                $val = 'Desh ki mitti ka swad | Ganv se ghar tak';
            }
            $stmt_up->execute([$key, $val]);
        }

        set_flash('success', 'लोगो साइज़, GIF लोगो, पेमेंट गेटवे एवं साइट सेटिंग्स सफलतापूर्वक सहेजी गईं!');
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

$page_title = 'गेटवे एवं प्लेटफ़ॉर्म सेटिंग्स (Gateways & Shipping Settings)';
require_once __DIR__ . '/admin_header.php';
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

<!-- Quick Links Navigation Banner for SEO -->
<div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 via-purple-50 to-amber-50 rounded-2xl border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
      <i class="fa-solid fa-magnifying-glass-chart"></i>
    </div>
    <div>
      <h4 class="text-xs font-black text-gray-900 flex items-center gap-2">
        होमपेज टाइटल एवं ऑन-पेज SEO (Homepage Title & On-Page SEO)
      </h4>
      <p class="text-[11px] text-gray-600 mt-0.5">
        होमपेज का ब्राउज़र टाइटल (&lt;title&gt;), मेटा विवरण, कीवर्ड्स, Google सर्च स्निपेट प्रिव्यू एवं सोशल कार्ड्स कॉन्फ़िगर करें।
      </p>
    </div>
  </div>
  <a href="/admin/seo_settings.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-xs">
    <i class="fa-solid fa-gear"></i> On-Page SEO सेटिंग्स खोलें
  </a>
</div>

<form action="/admin/site_settings.php" method="POST" enctype="multipart/form-data" class="space-y-8">
  <?= csrf_field() ?>
  <input type="hidden" name="save_settings" value="1">

  <!-- Logo & Festive GIF Branding Settings Section -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-6 border-b border-gray-100 gap-2">
      <div>
        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
          1. ब्रांड लोगो एवं फेस्टिव GIF लोगो प्रबंधन (Brand & Festive Logo Controls)
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
          मोबाइल एवं डेस्कटॉप पर लोगो का सटीक आकार (Height) नियंत्रित करें, तथा मुख्य त्योहारों / अवसरों पर एनिमेटेड GIF लोगो चालू करें।
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-gray-600">वर्तमान सक्रिय:</span>
        <?php if (get_setting_val('use_gif_logo', '0') == '1' && !empty(get_setting_val('logo_gif_url'))): ?>
          <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-full flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            फेस्टिव GIF लोगो सक्रिय
          </span>
        <?php else: ?>
          <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">
            स्टैंडर्ड लोगो सक्रिय
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- 1. Logo Sizing Controls -->
      <div class="space-y-5 bg-stone-50 p-5 rounded-2xl border border-gray-200">
        <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 flex items-center gap-2">
          <i class="fa-solid fa-arrows-up-down text-indigo-600"></i>
          लोगो साइज़ नियंत्रण (Logo Heights in Pixels)
        </h4>

        <!-- Mobile Logo Height -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
              <i class="fa-solid fa-mobile-screen text-emerald-600"></i>
              मोबाइल स्क्रीन पर लोगो ऊंचाई (Mobile Logo Height)
            </label>
            <span class="text-xs font-mono font-bold text-indigo-600 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-200" id="mobile_val_badge">
              <?= htmlspecialchars(get_setting_val('logo_height_mobile', '38')) ?>px
            </span>
          </div>
          <div class="flex items-center gap-3">
            <input type="range" min="24" max="64" step="1" name="logo_height_mobile" id="range_logo_mobile"
              value="<?= htmlspecialchars(get_setting_val('logo_height_mobile', '38')) ?>"
              oninput="document.getElementById('mobile_val_badge').innerText = this.value + 'px'; document.getElementById('num_logo_mobile').value = this.value; updateLogoPreview();"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            <input type="number" min="24" max="64" id="num_logo_mobile"
              value="<?= htmlspecialchars(get_setting_val('logo_height_mobile', '38')) ?>"
              oninput="document.getElementById('range_logo_mobile').value = this.value; document.getElementById('mobile_val_badge').innerText = this.value + 'px'; updateLogoPreview();"
              class="w-20 px-2.5 py-1 bg-white border border-gray-300 rounded-lg text-xs font-bold text-center">
          </div>
          <p class="text-[11px] text-gray-500">
            सुझाव: 36px से 42px मोबाइल डिवाइसेस के लिए सबसे अनुकूल और स्पष्ट रहता है।
          </p>
        </div>

        <!-- Desktop Logo Height -->
        <div class="space-y-2 pt-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
              <i class="fa-solid fa-laptop text-blue-600"></i>
              डेस्कटॉप स्क्रीन पर लोगो ऊंचाई (Desktop Logo Height)
            </label>
            <span class="text-xs font-mono font-bold text-indigo-600 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-200" id="desktop_val_badge">
              <?= htmlspecialchars(get_setting_val('logo_height_desktop', '48')) ?>px
            </span>
          </div>
          <div class="flex items-center gap-3">
            <input type="range" min="30" max="80" step="1" name="logo_height_desktop" id="range_logo_desktop"
              value="<?= htmlspecialchars(get_setting_val('logo_height_desktop', '48')) ?>"
              oninput="document.getElementById('desktop_val_badge').innerText = this.value + 'px'; document.getElementById('num_logo_desktop').value = this.value; updateLogoPreview();"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            <input type="number" min="30" max="80" id="num_logo_desktop"
              value="<?= htmlspecialchars(get_setting_val('logo_height_desktop', '48')) ?>"
              oninput="document.getElementById('range_logo_desktop').value = this.value; document.getElementById('desktop_val_badge').innerText = this.value + 'px'; updateLogoPreview();"
              class="w-20 px-2.5 py-1 bg-white border border-gray-300 rounded-lg text-xs font-bold text-center">
          </div>
          <p class="text-[11px] text-gray-500">
            सुझाव: 48px से 56px डेस्कटॉप हेडर के लिए संतुलित रहता है।
          </p>
        </div>

        <!-- Live Visual Preview Box -->
        <div class="pt-4 border-t border-gray-200">
          <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-2">मोबाइल दृश्य प्रिव्यू (Header Preview):</span>
          <div class="p-3 bg-white border border-gray-200 rounded-xl flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="text-gray-400 text-xs">☰</span>
              <img id="headerLivePreviewImg" src="<?= htmlspecialchars((get_setting_val('use_gif_logo', '0') == '1' && !empty(get_setting_val('logo_gif_url'))) ? get_setting_val('logo_gif_url') : get_setting_val('site_logo_url', '/logo.png')) ?>"
                alt="Logo Preview" style="height: <?= htmlspecialchars(get_setting_val('logo_height_mobile', '38')) ?>px;" class="w-auto object-contain transition-all">
            </div>
            <div class="flex items-center gap-2 text-gray-400 text-xs">
              <span>♡</span>
              <span>🛒</span>
              <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-[10px] flex items-center justify-center font-bold">A</span>
            </div>
          </div>
        </div>

      </div>

      <!-- 2. Standard Logo & Occasion GIF Logo Selector -->
      <div class="space-y-6">
        
        <!-- Standard Logo Box -->
        <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 flex items-center gap-2">
              <i class="fa-solid fa-image text-emerald-600"></i>
              प्राथमिक स्टैंडर्ड लोगो (Standard Logo)
            </h4>
            <span class="text-[11px] text-gray-400 font-mono">Default</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">लोगो इमेज URL या पाथ</label>
            <input type="text" name="site_logo_url" id="site_logo_url" value="<?= htmlspecialchars(get_setting_val('site_logo_url', '/logo.png')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-xl text-xs font-mono">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">नया लोगो अपलोड करें (.png, .webp, .svg)</label>
            <input type="file" name="standard_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
              class="w-full text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-gray-200 rounded-xl p-1 bg-white">
          </div>
        </div>

        <!-- Festive / Occasion GIF Logo Box -->
        <div class="p-5 bg-gradient-to-br from-amber-50 to-orange-50/60 rounded-2xl border border-amber-200 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-bold text-xs uppercase tracking-wider text-amber-900 flex items-center gap-2">
                <i class="fa-solid fa-wand-magic text-amber-600"></i>
                अवसर / त्योहार GIF लोगो (Occasion GIF Logo)
              </h4>
              <p class="text-[11px] text-amber-700 mt-0.5">दिवाली, होली, स्वतंत्रता दिवस आदि मुख्य अवसरों पर विशेष एनिमेटेड लोगो</p>
            </div>
            
            <!-- Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
              <input type="checkbox" name="use_gif_logo" value="1" <?= get_setting_val('use_gif_logo', '0') == '1' ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
            </label>
          </div>

          <div class="pt-2 border-t border-amber-200/60 space-y-3">
            <div>
              <label class="block text-xs font-bold text-gray-800 mb-1">GIF लोगो URL या पाथ (.gif)</label>
              <input type="text" name="logo_gif_url" id="logo_gif_url" value="<?= htmlspecialchars(get_setting_val('logo_gif_url', '')) ?>"
                placeholder="उदा. /assets/images/diwali_logo.gif या https://..."
                class="w-full px-3.5 py-2 bg-white border border-amber-300 rounded-xl text-xs font-mono">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-800 mb-1">नया GIF लोगो अपलोड करें (.gif, .webp)</label>
              <input type="file" name="gif_logo_file" accept="image/gif,image/webp,image/png"
                class="w-full text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer border border-amber-200 rounded-xl p-1 bg-white">
            </div>

            <?php if (!empty(get_setting_val('logo_gif_url'))): ?>
              <div class="p-2.5 bg-white/80 rounded-xl border border-amber-200 flex items-center gap-3">
                <img src="<?= htmlspecialchars(get_setting_val('logo_gif_url')) ?>" alt="GIF Logo" class="h-10 w-auto object-contain border border-amber-100 rounded-lg p-1 bg-white">
                <div class="text-[11px] text-amber-900">
                  <span class="font-bold block">अपलोडेड GIF लोगो उपलब्ध</span>
                  <span class="text-gray-500 font-mono text-[10px] truncate max-w-[200px] block"><?= htmlspecialchars(get_setting_val('logo_gif_url')) ?></span>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Payment Gateways Section -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-credit-card text-emerald-600"></i>
      2. भुगतान गेटवे एकीकरण (Payment Gateways Integration)
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
        <label class="block text-xs font-bold text-gray-700 mb-1">ब्रांड का नाम (Brand Name)</label>
        <input type="text" name="brand_name" value="<?= htmlspecialchars(get_setting_val('brand_name', 'GaunGaliHat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">ब्रांड टैगलाइन (Brand Tagline)</label>
        <input type="text" name="brand_tagline" value="<?= htmlspecialchars(get_setting_val('brand_tagline', 'Desh ki mitti ka swad | Ganv se ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

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
        <?php $settings_site_host = parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'ganvgalihat.com'; ?>
        <input type="email" name="contact_email" value="<?= htmlspecialchars(get_setting_val('contact_email', 'namaste@' . $settings_site_host)) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">फ़ैक्टरी का पता (Works / Manufacturing)</label>
        <input type="text" name="factory_address" value="<?= htmlspecialchars(get_setting_val('factory_address', 'Works / Factory: Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">प्रधान कार्यालय का पता (Head Office Address)</label>
        <input type="text" name="head_office_address" value="<?= htmlspecialchars(get_setting_val('head_office_address', 'G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, UP')) ?>"
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

<script>
function updateLogoPreview() {
  const h = document.getElementById('range_logo_mobile').value;
  const img = document.getElementById('headerLivePreviewImg');
  if (img) {
    img.style.height = h + 'px';
  }
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
