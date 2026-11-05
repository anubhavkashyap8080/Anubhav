<?php
/**
 * GanvGaliHat Site & Platform Settings Manager
 * Payment Gateways, Delivery Zones, Responsive Logo Sizing, and Company Identity
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Clearance
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid. Please refresh the page and try again.');
        header('Location: /admin/site_settings.php');
        exit;
    }

    // Handle Standard Logo File Upload
    if (isset($_FILES['standard_logo_file']) && $_FILES['standard_logo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('standard_logo_file', 'uploads/logos/');
        if ($upload_res['success']) {
            $_POST['site_logo_url'] = $upload_res['path'];
        } else {
            set_flash('error', 'Standard logo upload failed: ' . $upload_res['error']);
        }
    }

    // Handle Occasion / GIF Logo File Upload
    if (isset($_FILES['gif_logo_file']) && $_FILES['gif_logo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('gif_logo_file', 'uploads/logos/');
        if ($upload_res['success']) {
            $_POST['logo_gif_url'] = $upload_res['path'];
        } else {
            set_flash('error', 'GIF logo upload failed: ' . $upload_res['error']);
        }
    }

    // Checkbox Defaults
    $_POST['use_gif_logo'] = isset($_POST['use_gif_logo']) ? '1' : '0';
    $_POST['razorpay_enabled'] = isset($_POST['razorpay_enabled']) ? '1' : '0';
    $_POST['payu_enabled'] = isset($_POST['payu_enabled']) ? '1' : '0';
    $_POST['cod_enabled'] = isset($_POST['cod_enabled']) ? '1' : '0';
    $_POST['maintenance_mode_enabled'] = isset($_POST['maintenance_mode_enabled']) ? '1' : '0';

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        // SQLite fallback for ON DUPLICATE KEY
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        $allowed_keys = [
            'homepage_layout', 'homepage_youtube_url',
            'slider_slide1_badge', 'slider_slide1_title', 'slider_slide1_subtitle', 'slider_slide1_btn1_text', 'slider_slide1_btn1_link', 'slider_slide1_btn2_text', 'slider_slide1_btn2_link', 'slider_slide1_image',
            'slider_slide2_badge', 'slider_slide2_title', 'slider_slide2_subtitle', 'slider_slide2_btn1_text', 'slider_slide2_btn1_link', 'slider_slide2_btn2_text', 'slider_slide2_btn2_link', 'slider_slide2_image',
            'slider_slide3_badge', 'slider_slide3_title', 'slider_slide3_subtitle', 'slider_slide3_btn1_text', 'slider_slide3_btn1_link', 'slider_slide3_btn2_text', 'slider_slide3_btn2_link', 'slider_slide3_image',
            'brand_name', 'brand_tagline', 'company_name', 'contact_phone', 'contact_email',
            'factory_address', 'head_office_address', 'announcement_text',
            'site_logo_url', 'logo_gif_url', 'use_gif_logo', 'logo_height_mobile', 'logo_height_desktop',
            'razorpay_enabled', 'razorpay_key_id',
            'payu_enabled', 'payu_merchant_key', 'payu_mode',
            'cod_enabled', 'cod_extra_charge_percent',
            'shipping_free_threshold', 'shipping_zone1_rate', 'shipping_zone2_rate', 'shipping_zone3_rate', 'shipping_weight_slab_rate',
            'maintenance_mode_enabled', 'maintenance_mode_title', 'maintenance_mode_title_hi', 'maintenance_mode_message', 'maintenance_mode_message_hi', 'maintenance_mode_end_time', 'maintenance_mode_allowed_ips', 'maintenance_mode_secret_bypass', 'maintenance_mode_show_contact'
        ];

        foreach ($allowed_keys as $key) {
            if (!isset($_POST[$key])) {
                continue;
            }
            $val = trim((string)$_POST[$key]);
            if ($key === 'brand_name' && (empty($val) || $val === '0')) {
                $val = 'GanvGaliHat';
            }
            if ($key === 'brand_tagline' && (empty($val) || $val === '0')) {
                $val = 'Hunar desh ka, ganv se ghar tak';
            }
            $stmt_up->execute([$key, $val]);
        }

        set_flash('success', 'Logo sizes, payment gateways, and site settings saved successfully!');
        header('Location: /admin/site_settings.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Error saving settings: ' . $e->getMessage());
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

$page_title = 'Gateways & Platform Settings — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6">
  <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
    <i class="fa-solid fa-sliders text-indigo-600"></i>
    Payment Gateways & Shipping Configuration
  </h1>
  <p class="text-xs text-gray-500 mt-1">
    Razorpay, PayU Money, Cash on Delivery (COD), and pincode-based delivery rates
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
        Homepage Title & On-Page SEO
      </h4>
      <p class="text-[11px] text-gray-600 mt-0.5">
        Configure browser &lt;title&gt;, meta descriptions, keywords, Google search snippet preview, and social sharing cards.
      </p>
    </div>
  </div>
  <a href="/admin/seo_settings.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-xs">
    <i class="fa-solid fa-gear"></i> Open SEO Settings
  </a>
</div>

<!-- Quick Maintenance Mode Status & Control Card -->
<div class="mb-6 p-5 rounded-2xl border shadow-xs transition-all <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'bg-red-50/80 border-red-200' : 'bg-emerald-50/80 border-emerald-200' ?> flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div class="flex items-center gap-3.5">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-xs <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'bg-red-600 text-white animate-pulse' : 'bg-emerald-600 text-white' ?>">
      <i class="fa-solid <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'fa-screwdriver-wrench' : 'fa-globe' ?>"></i>
    </div>
    <div>
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'bg-red-600 text-white' : 'bg-emerald-700 text-white' ?>">
          <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'Maintenance Mode Active' : 'Store Live & Online' ?>
        </span>
        <h4 class="text-xs font-black text-gray-900">
          <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'Public Access is Blocked (HTTP 503)' : 'All Visitors Have Normal Access' ?>
        </h4>
      </div>
      <p class="text-[11px] text-gray-600 mt-1">
        Take site offline for updates, schedule maintenance countdown, or configure IP/secret bypass.
      </p>
    </div>
  </div>
  <div class="flex items-center gap-2 shrink-0">
    <a href="/admin/maintenance.php" class="px-4 py-2 <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-700 hover:bg-emerald-800' ?> text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-gear"></i>
      <?= (function_exists('is_maintenance_mode_active') && is_maintenance_mode_active()) ? 'Open Maintenance Control' : 'Configure Maintenance Mode' ?>
    </a>
  </div>
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
          1. Brand Logo & Festive GIF Logo Controls
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
          Control exact logo height on mobile and desktop, or enable animated GIF logos for festivals.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-gray-600">Currently Active:</span>
        <?php if (get_setting_val('use_gif_logo', '0') == '1' && !empty(get_setting_val('logo_gif_url'))): ?>
          <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-full flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            Festive GIF Logo Active
          </span>
        <?php else: ?>
          <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">
            Standard Logo Active
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- 1. Logo Sizing Controls -->
      <div class="space-y-5 bg-stone-50 p-5 rounded-2xl border border-gray-200">
        <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 flex items-center gap-2">
          <i class="fa-solid fa-arrows-up-down text-indigo-600"></i>
          Logo Heights in Pixels
        </h4>

        <!-- Mobile Logo Height -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
              <i class="fa-solid fa-mobile-screen text-emerald-600"></i>
              Mobile Logo Height
            </label>
            <span class="text-xs font-mono font-bold text-indigo-600 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-200" id="mobile_val_badge">
              <?= htmlspecialchars(get_setting_val('logo_height_mobile', '46')) ?>px
            </span>
          </div>
          <div class="flex items-center gap-3">
            <input type="range" min="28" max="64" step="1" name="logo_height_mobile" id="range_logo_mobile"
              value="<?= htmlspecialchars(get_setting_val('logo_height_mobile', '46')) ?>"
              oninput="document.getElementById('mobile_val_badge').innerText = this.value + 'px'; document.getElementById('num_logo_mobile').value = this.value; updateLogoPreview();"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            <input type="number" min="28" max="64" id="num_logo_mobile"
              value="<?= htmlspecialchars(get_setting_val('logo_height_mobile', '46')) ?>"
              oninput="document.getElementById('range_logo_mobile').value = this.value; document.getElementById('mobile_val_badge').innerText = this.value + 'px'; updateLogoPreview();"
              class="w-20 px-2.5 py-1 bg-white border border-gray-300 rounded-lg text-xs font-bold text-center">
          </div>
          <p class="text-[11px] text-gray-500">
            Recommended: 44px to 52px ensures clear brand visibility on mobile screens.
          </p>
        </div>

        <!-- Desktop Logo Height -->
        <div class="space-y-2 pt-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
              <i class="fa-solid fa-laptop text-blue-600"></i>
              Desktop Logo Height
            </label>
            <span class="text-xs font-mono font-bold text-indigo-600 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-200" id="desktop_val_badge">
              <?= htmlspecialchars(get_setting_val('logo_height_desktop', '62')) ?>px
            </span>
          </div>
          <div class="flex items-center gap-3">
            <input type="range" min="36" max="90" step="1" name="logo_height_desktop" id="range_logo_desktop"
              value="<?= htmlspecialchars(get_setting_val('logo_height_desktop', '62')) ?>"
              oninput="document.getElementById('desktop_val_badge').innerText = this.value + 'px'; document.getElementById('num_logo_desktop').value = this.value; updateLogoPreview();"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            <input type="number" min="36" max="90" id="num_logo_desktop"
              value="<?= htmlspecialchars(get_setting_val('logo_height_desktop', '62')) ?>"
              oninput="document.getElementById('range_logo_desktop').value = this.value; document.getElementById('desktop_val_badge').innerText = this.value + 'px'; updateLogoPreview();"
              class="w-20 px-2.5 py-1 bg-white border border-gray-300 rounded-lg text-xs font-bold text-center">
          </div>
          <p class="text-[11px] text-gray-500">
            Recommended: 58px to 68px looks balanced and crisp across desktop headers.
          </p>
        </div>

        <!-- Live Visual Preview Box -->
        <div class="pt-4 border-t border-gray-200">
          <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-2">Live Header Preview:</span>
          <div class="p-3 bg-white border border-gray-200 rounded-xl flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="text-gray-400 text-xs">☰</span>
              <img id="headerLivePreviewImg" src="<?= htmlspecialchars((get_setting_val('use_gif_logo', '0') == '1' && !empty(get_setting_val('logo_gif_url'))) ? get_setting_val('logo_gif_url') : get_setting_val('site_logo_url', '/logo.png')) ?>"
                alt="Logo Preview" style="height: <?= htmlspecialchars(get_setting_val('logo_height_mobile', '46')) ?>px;" class="w-auto object-contain transition-all">
            </div>
            <div class="flex items-center gap-2 text-gray-400 text-xs">
              <span>🔍</span>
              <span>🛒</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Logo Upload & URL Configuration -->
      <div class="space-y-5">
        
        <!-- Standard Logo -->
        <div class="p-4 bg-white rounded-xl border border-gray-200 space-y-3">
          <div class="flex items-center justify-between">
            <h5 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
              <i class="fa-regular fa-image text-emerald-600"></i> Standard Brand Logo
            </h5>
            <span class="text-[10px] text-gray-400">Default brand mark</span>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Direct Logo URL or Path</label>
            <input type="text" name="site_logo_url" value="<?= htmlspecialchars(get_setting_val('site_logo_url', '/logo.png')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Logo File (.png, .webp, .svg)</label>
            <input type="file" name="standard_logo_file" accept="image/png,image/webp,image/svg+xml,image/jpeg"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
          </div>
        </div>

        <!-- Festive Animated GIF Logo -->
        <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 space-y-3">
          <div class="flex items-center justify-between">
            <h5 class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
              <i class="fa-solid fa-gift text-amber-600"></i> Occasion & Festive GIF Logo
            </h5>
            <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded">Optional</span>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-800 mb-1">GIF Logo URL or Path (.gif)</label>
            <input type="text" name="logo_gif_url" value="<?= htmlspecialchars(get_setting_val('logo_gif_url', '')) ?>"
              placeholder="e.g. /assets/images/diwali_logo.gif or https://..."
              class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-800 mb-1">Upload New GIF Logo (.gif, .webp)</label>
            <input type="file" name="gif_logo_file" accept="image/gif,image/webp"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
          </div>
          <div class="pt-2 flex items-center gap-2">
            <input type="checkbox" name="use_gif_logo" value="1" id="chk_use_gif"
              <?= get_setting_val('use_gif_logo', '0') == '1' ? 'checked' : '' ?>
              class="rounded text-amber-600 focus:ring-amber-500">
            <label for="chk_use_gif" class="text-xs text-gray-800 cursor-pointer">
              <span class="font-bold block">Activate Festive GIF Logo</span>
              <span class="text-[11px] text-gray-500">When checked, the animated GIF logo will replace the standard logo site-wide.</span>
            </label>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Payment Gateways Section -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-credit-card text-emerald-600"></i>
      2. Payment Gateways Integration
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Razorpay Card -->
      <div class="p-5 rounded-2xl border border-gray-200 bg-stone-50 space-y-4">
        <div class="flex items-center justify-between">
          <div class="font-bold text-sm text-gray-900 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Razorpay
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="razorpay_enabled" value="1" <?= get_setting_val('razorpay_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-green"></div>
          </label>
        </div>
        <p class="text-xs text-gray-500 mb-4">UPI, Google Pay, PhonePe, Cards and NetBanking</p>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Key ID</label>
          <input type="text" name="razorpay_key_id" value="<?= htmlspecialchars(get_setting_val('razorpay_key_id', 'rzp_test_1DP5mmOlF5G5ag')) ?>"
            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>
      </div>

      <!-- PayU India Card -->
      <div class="p-5 rounded-2xl border border-gray-200 bg-stone-50 space-y-4">
        <div class="flex items-center justify-between">
          <div class="font-bold text-sm text-gray-900 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> PayU India
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="payu_enabled" value="1" <?= get_setting_val('payu_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-green"></div>
          </label>
        </div>
        <p class="text-xs text-gray-500 mb-4">RuPay, Credit/Debit Cards, NetBanking and Wallets</p>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Merchant Key</label>
          <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars(get_setting_val('payu_merchant_key', 'gtKFFx')) ?>"
            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Mode</label>
          <select name="payu_mode" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold">
            <option value="test" <?= get_setting_val('payu_mode', 'test') === 'test' ? 'selected' : '' ?>>Test / Sandbox</option>
            <option value="live" <?= get_setting_val('payu_mode') === 'live' ? 'selected' : '' ?>>Live Production</option>
          </select>
        </div>
      </div>

      <!-- Cash on Delivery (COD) Card -->
      <div class="p-5 rounded-2xl border border-gray-200 bg-stone-50 space-y-4">
        <div class="flex items-center justify-between">
          <div class="font-bold text-sm text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-hand-holding-dollar text-brand-green"></i> Cash on Delivery (COD)
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="cod_enabled" value="1" <?= get_setting_val('cod_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-green"></div>
          </label>
        </div>
        <p class="text-xs text-gray-500 mb-4">India Post or courier collection at the customer's doorstep</p>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Handling Surcharge (%)</label>
          <input type="number" step="0.1" name="cod_extra_charge_percent" value="<?= htmlspecialchars(get_setting_val('cod_extra_charge_percent', '0')) ?>"
            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          <span class="text-[10px] text-gray-400 mt-0.5 block">Optional COD fee percentage. Set 0 for free COD.</span>
        </div>
      </div>

    </div>
  </div>

  <!-- Shipping Price Calculator Rules -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-truck text-indigo-600"></i>
      3. Shipping Rate & Delivery Zones Rules
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Free Shipping Threshold (₹)</label>
        <input type="number" name="shipping_free_threshold" value="<?= htmlspecialchars(get_setting_val('shipping_free_threshold', '499')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">Free shipping on orders above this amount</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 1 (Awadh & UP) Rate (₹)</label>
        <input type="number" name="shipping_zone1_rate" value="<?= htmlspecialchars(get_setting_val('shipping_zone1_rate', '49')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">Pincodes starting 20xxxx to 28xxxx</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 2 (North India) Rate (₹)</label>
        <input type="number" name="shipping_zone2_rate" value="<?= htmlspecialchars(get_setting_val('shipping_zone2_rate', '79')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">Delhi NCR, Haryana, Punjab, Rajasthan, MP, Bihar</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Zone 3+ (Rest of India) Rate (₹)</label>
        <input type="number" name="shipping_zone3_rate" value="<?= htmlspecialchars(get_setting_val('shipping_zone3_rate', '99')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">South, West and Northeast states</p>
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">Heavy Weight Slab Rate (>2kg, per 0.5kg extra in ₹)</label>
        <input type="number" name="shipping_weight_slab_rate" value="<?= htmlspecialchars(get_setting_val('shipping_weight_slab_rate', '20')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
        <p class="text-[10px] text-gray-400 mt-1">Fair freight adjustment for heavy forged tools and iron implements</p>
      </div>
    </div>
  </div>

  <!-- Company Identity & Factory Information -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-base font-bold text-gray-900 pb-3 mb-6 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-building text-indigo-600"></i>
      4. Organization & Contact Information (Kalyani agrofood pvt ltd)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Brand Name</label>
        <input type="text" name="brand_name" value="<?= htmlspecialchars(get_setting_val('brand_name', 'GanvGaliHat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Brand Tagline</label>
        <input type="text" name="brand_tagline" value="<?= htmlspecialchars(get_setting_val('brand_tagline', 'Hunar desh ka, ganv se ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Company Name</label>
        <input type="text" name="company_name" value="<?= htmlspecialchars(get_setting_val('company_name', 'Kalyani agrofood pvt ltd')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Helpline Phone</label>
        <input type="text" name="contact_phone" value="<?= htmlspecialchars(get_setting_val('contact_phone', '+91 7897492100')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Official Email</label>
        <?php $settings_site_host = parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'ganvgalihat.com'; ?>
        <input type="email" name="contact_email" value="<?= htmlspecialchars(get_setting_val('contact_email', 'namaste@' . $settings_site_host)) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Works / Factory Address</label>
        <input type="text" name="factory_address" value="<?= htmlspecialchars(get_setting_val('factory_address', 'Works / Factory: Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">Industrial Plant / Head Office Address</label>
        <input type="text" name="head_office_address" value="<?= htmlspecialchars(get_setting_val('head_office_address', 'Madhoganj, Baghauli road Newada, Pahutera, Hardoi, Uttar Pradesh')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">Announcement Ticker Text</label>
        <textarea name="announcement_text" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs"><?= htmlspecialchars(get_setting_val('announcement_text', '🌾 Kalyani agrofood pvt ltd Tools, Awadh Pulses, Banarasi Pickles & Village Crafts: Direct from Rural Artisans | Free Delivery Over ₹499 | Cash on Delivery Available')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Save Button -->
  <div class="flex items-center justify-end gap-3 pb-8">
    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center gap-2 cursor-pointer">
      <i class="fa-solid fa-floppy-disk"></i>
      Save All Settings
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
