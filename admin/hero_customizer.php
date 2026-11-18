<?php
/**
 * GanvGaliHat Festival Hero Section & Safe Campaign Builder
 * 11 Indian Festival Presets, Asia/Kolkata Datetime Scheduler,
 * Sandboxed Custom HTML/CSS Engine with Allowlist Sanitizer & 1-Click Rollback
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/video_helpers.php';
require_admin();

date_default_timezone_set('Asia/Kolkata');

// Handle Campaign Rollback to Previous Version
if (isset($_GET['action']) && $_GET['action'] === 'rollback' && !empty($_GET['version_id'])) {
    $vid = (int)$_GET['version_id'];
    $stmt_v = $pdo->prepare("SELECT * FROM hero_campaign_versions WHERE id = ?");
    $stmt_v->execute([$vid]);
    $ver = $stmt_v->fetch();

    if ($ver) {
        $data = json_decode($ver['snapshot_data'], true);
        if ($data) {
            $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $upd = $is_sqlite
                ? $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)")
                : $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

            foreach ($data as $k => $v) {
                $upd->execute([$k, (string)$v]);
            }
            log_audit_action('hero_campaign_rollback', 'campaigns', 'hero_campaign_versions', $vid, 'Rolled back to version #' . $ver['version_num']);
            set_flash('success', 'Hero section restored to Version #' . $ver['version_num'] . ' successfully.');
            header('Location: /admin/hero_customizer.php');
            exit;
        }
    }
}

// Handle Save Hero Customizer & Campaign
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero_settings'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/hero_customizer.php');
        exit;
    }

    $keys = [
        'homepage_layout', 'homepage_youtube_url',
        'slider_slide1_badge', 'slider_slide1_title', 'slider_slide1_subtitle', 'slider_slide1_btn1_text', 'slider_slide1_btn1_link', 'slider_slide1_btn2_text', 'slider_slide1_btn2_link', 'slider_slide1_image',
        'slider_slide2_badge', 'slider_slide2_title', 'slider_slide2_subtitle', 'slider_slide2_btn1_text', 'slider_slide2_btn1_link', 'slider_slide2_btn2_text', 'slider_slide2_btn2_link', 'slider_slide2_image',
        'slider_slide3_badge', 'slider_slide3_title', 'slider_slide3_subtitle', 'slider_slide3_btn1_text', 'slider_slide3_btn1_link', 'slider_slide3_btn2_text', 'slider_slide3_btn2_link', 'slider_slide3_image',
        'hero_festive_preset', 'hero_theme_color', 'hero_badge_text',
        'hero_title_line1', 'hero_title_line2', 'hero_subtitle',
        'hero_btn1_text', 'hero_btn1_link', 'hero_btn1_show', 'hero_btn1_target',
        'hero_btn2_text', 'hero_btn2_link', 'hero_btn2_show', 'hero_btn2_target',
        'hero_stat1_num', 'hero_stat1_label', 'hero_stat2_num', 'hero_stat2_label',
        'hero_stat3_num', 'hero_stat3_label',
        'hero_tag1_title', 'hero_tag1_subtitle', 'hero_tag2_title', 'hero_tag2_subtitle',
        'hero_has_custom_code', 'hero_custom_css'
    ];

    // TASK B: Hero button show/hide toggles & targets
    $_POST['hero_btn1_show'] = isset($_POST['hero_btn1_show']) ? '1' : '0';
    $_POST['hero_btn2_show'] = isset($_POST['hero_btn2_show']) ? '1' : '0';
    $_POST['hero_btn1_target'] = !empty($_POST['hero_btn1_target']) ? '_blank' : '_self';
    $_POST['hero_btn2_target'] = !empty($_POST['hero_btn2_target']) ? '_blank' : '_self';

    // TASK B: Link validation for hero buttons
    foreach (['hero_btn1_link', 'hero_btn2_link'] as $btn_key) {
        $link = trim((string)($_POST[$btn_key] ?? ''));
        if ($link !== '') {
            $is_valid = (preg_match('/^(\/|https:\/\/)/i', $link) && !preg_match('/^(\/\/|\\\\|javascript:|data:)/i', $link));
            if (!$is_valid) {
                set_flash('error', 'Invalid button link URL. Only relative paths starting with "/" or full "https://" URLs are allowed.');
                header('Location: /admin/hero_customizer.php');
                exit;
            }
        }
    }

    $image_url = clean_input($_POST['hero_image_url'] ?? '');

    // Handle File Upload if provided via secure_upload_image
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('hero_image_file', 'uploads/banners/');
        if ($upload_res['success']) {
            $image_url = $upload_res['path'];
        }
    }

    // Handle Slide 1-3 Image Uploads
    for ($s = 1; $s <= 3; $s++) {
        $f_key = "slider_slide{$s}_file";
        if (isset($_FILES[$f_key]) && $_FILES[$f_key]['error'] === UPLOAD_ERR_OK) {
            $up = secure_upload_image($f_key, 'uploads/banners/');
            if ($up['success']) {
                $_POST["slider_slide{$s}_image"] = $up['path'];
            }
        }
    }

    // Sanitize Custom HTML using Server-Side Allowlist Sanitizer
    $raw_html = $_POST['hero_custom_html'] ?? '';
    $sanitized_html = sanitize_custom_html($raw_html);

    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        }

        $snapshot = [];
        foreach ($keys as $k) {
            $val = trim((string)($_POST[$k] ?? ''));
            $stmt->execute([$k, $val]);
            $snapshot[$k] = $val;
        }

        if (!empty($image_url)) {
            $stmt->execute(['hero_image_url', $image_url]);
            $snapshot['hero_image_url'] = $image_url;
        }

        $stmt->execute(['hero_custom_html', $sanitized_html]);
        $snapshot['hero_custom_html'] = $sanitized_html;

        // Save Version History for Rollback Capability
        $stmt_next_v = $pdo->query("SELECT COALESCE(MAX(version_num), 0) + 1 FROM hero_campaign_versions WHERE campaign_id = 1");
        $next_v = (int)$stmt_next_v->fetchColumn();

        $ins_v = $pdo->prepare("
            INSERT INTO hero_campaign_versions (campaign_id, version_num, snapshot_data, reason, created_by)
            VALUES (1, ?, ?, ?, ?)
        ");
        $ins_v->execute([
            $next_v,
            json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            'Updated campaign: ' . ($_POST['hero_festive_preset'] ?? 'General'),
            $_SESSION['user_id'] ?? null
        ]);

        log_audit_action('hero_campaign_saved', 'campaigns', 'hero_campaigns', 1, 'Hero settings updated with preset: ' . ($_POST['hero_festive_preset'] ?? 'General'));

        set_flash('success', 'Hero section settings updated successfully (Version #' . $next_v . ').');
        header('Location: /admin/hero_customizer.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Error saving hero settings. Please try again.');
    }
}

// Fetch all settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$curr_settings = [];
while ($row = $stmt_settings->fetch()) {
    $curr_settings[$row['setting_key']] = $row['setting_value'];
}

function get_h_val($key, $default = '') {
    global $curr_settings;
    return $curr_settings[$key] ?? $default;
}

// Fetch Version History (Last 10 Revisions)
$stmt_vers = $pdo->query("SELECT * FROM hero_campaign_versions WHERE campaign_id = 1 ORDER BY id DESC LIMIT 10");
$version_history = $stmt_vers->fetchAll();

$page_title = 'Hero Section & Festival Customizer — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2">
      <h1 class="text-2xl sm:text-3xl font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
        Festival & Seasonal Hero Campaign Builder
      </h1>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
        11 Indian Festival Presets
      </span>
    </div>
    <p class="text-xs sm:text-sm text-gray-500 mt-1">
      Full campaign customization for Diwali, Holi, Navratri, Makar Sankranti, Eid, Independence Day, Republic Day, Harvest, and Monsoon seasonal campaigns.
    </p>
  </div>

  <div class="flex items-center gap-2.5">
    <button type="button" onclick="openPreviewModal()" class="px-4 py-2.5 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
      <i class="fa-solid fa-eye text-amber-400"></i> View Sandbox Preview
    </button>
    <a href="/" target="_blank" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-indigo-200">
      <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Live Store
    </a>
  </div>
</div>

<!-- 11 Ready-Made Indian Festival Presets Tray -->
<div class="mb-8 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-3xl p-5 sm:p-6 text-white shadow-lg">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-3">
    <div>
      <h3 class="font-extrabold text-sm sm:text-base flex items-center gap-2">
        <i class="fa-solid fa-bolt text-yellow-200"></i> 1-Click Indian Festival & Shopping Event Presets:
      </h3>
      <p class="text-xs text-amber-100 mt-0.5">Click any festival preset to instantly load thematic colors, badges, headlines, and metrics.</p>
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 pt-1">
    <button type="button" onclick="loadPreset('diwali')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🪔 Diwali Mega Sale
    </button>
    <button type="button" onclick="loadPreset('holi')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🎨 Holi Festival
    </button>
    <button type="button" onclick="loadPreset('navratri')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🔱 Navratri & Puja
    </button>
    <button type="button" onclick="loadPreset('harvest')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌾 Makar Sankranti
    </button>
    <button type="button" onclick="loadPreset('raksha')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🎀 Raksha Bandhan
    </button>
    <button type="button" onclick="loadPreset('eid')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌙 Eid Mubarak Sale
    </button>
    <button type="button" onclick="loadPreset('independence')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🇮🇳 Independence Day (15 Aug)
    </button>
    <button type="button" onclick="loadPreset('republic')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🇮🇳 Republic Day (26 Jan)
    </button>
    <button type="button" onclick="loadPreset('kisan_mela')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      ⚒️ Desi Kisan Fair
    </button>
    <button type="button" onclick="loadPreset('monsoon')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌧️ Monsoon Special
    </button>
    <button type="button" onclick="loadPreset('general')" class="p-2.5 bg-orange-950/80 text-amber-200 border border-amber-400/40 font-bold text-xs rounded-2xl hover:bg-orange-950 transition col-span-2 sm:col-span-1 text-center">
      🏛️ Default Desi Haat
    </button>
  </div>
</div>

<form action="/admin/hero_customizer.php" method="POST" enctype="multipart/form-data" class="space-y-6">
  <?= csrf_field() ?>
  <input type="hidden" name="save_hero_settings" value="1">
  <input type="hidden" name="hero_festive_preset" id="hero_festive_preset" value="<?= htmlspecialchars(get_h_val('hero_festive_preset', 'general')) ?>">

  <!-- Section 0: Homepage Layout & Multi-Design Selector (5 Distinct Designs) -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
      <div>
        <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-indigo-600"></i>
          0. Homepage Layout & Multi-Design Selector (6 Unique Experiences)
        </h3>
        <p class="text-xs text-gray-500 mt-1">Select which handcrafted design to serve on the homepage. You can preview all 6 options before saving.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="/" target="_blank" class="px-3.5 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-emerald-200">
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Live Store
        </a>
      </div>
    </div>

    <?php $current_layout = get_h_val('homepage_layout', 'layout_1'); ?>

    <!-- 5 Distinct Layout Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      
      <!-- Option 1: Classic Rural Marketplace & 4-Box Department Grid -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_1' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-gray-100 text-gray-800 font-bold text-[10px] rounded-md uppercase">Layout 1</span>
            <input type="radio" name="homepage_layout" value="layout_1" <?= $current_layout === 'layout_1' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Classic Rural Marketplace</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features circular visual categories, 4-box department grid (tools, pulses, pickles, crafts), and Haversine nearest forge locator.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-gray-400">Department Grid</span>
          <a href="/?layout=layout_1" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 2: Modern Animated Slider Hero with Live Flash Deals Timer -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_2' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-md uppercase">Layout 2</span>
            <input type="radio" name="homepage_layout" value="layout_2" <?= $current_layout === 'layout_2' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Animated Slider & Flash Deals</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features dynamic auto-rotating 3-slide hero slider with navigation dots, live countdown flash deals timer (HH:MM:SS), and urgent dispatch indicators.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-amber-600">Hero Slider + Deals</span>
          <a href="/?layout=layout_2" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 3: Cinematic YouTube Video Hero & Rural Craft Documentary -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_3' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-red-100 text-red-800 font-bold text-[10px] rounded-md uppercase">Layout 3</span>
            <input type="radio" name="homepage_layout" value="layout_3" <?= $current_layout === 'layout_3' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Cinematic YouTube Video Hero</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Features full-width responsive 16:9 embedded YouTube documentary hero, behind-the-scenes forge stories, and rural craft maker highlights.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-red-600">YouTube Video Hero</span>
          <a href="/?layout=layout_3" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 4: Modern Bento Grid Catalog & Seasonal Harvest Fair -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_4' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-md uppercase">Layout 4</span>
            <input type="radio" name="homepage_layout" value="layout_4" <?= $current_layout === 'layout_4' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Modern Bento Grid Catalog</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Contemporary bento-card hero showcasing farm-fresh mustard oil, Women SHG handicrafts, live dispatch metrics, and transparent fair-value price breakdown.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-emerald-600">Bento Grid + FAQ</span>
          <a href="/?layout=layout_4" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 5: Artisan Split Showcase & Regional Forge Hub with Video Spotlight -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_5' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 font-bold text-[10px] rounded-md uppercase">Layout 5</span>
            <input type="radio" name="homepage_layout" value="layout_5" <?= $current_layout === 'layout_5' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Artisan Split & Regional Hub</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Dual-column split hero with interactive YouTube video spotlight card, regional artisan clusters directory (Sandila, Mallawan, Varanasi), and heavy implements showcase.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-purple-600">Split Hero + Hubs</span>
          <a href="/?layout=layout_5" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
            <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        </div>
      </label>

      <!-- Option 6: Video Story Home (Full-Screen Edge-to-Edge Video Sections) -->
      <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer <?= $current_layout === 'layout_6' ? 'border-indigo-600 bg-indigo-50/30 ring-2 ring-indigo-600/20' : 'border-gray-200 bg-white hover:border-gray-300' ?>">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold text-[10px] rounded-md uppercase">Layout 6</span>
            <input type="radio" name="homepage_layout" value="layout_6" <?= $current_layout === 'layout_6' ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
          </div>
          <h4 class="font-extrabold text-sm text-gray-900 mb-1">Video Story Home</h4>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Cinematic full-screen edge-to-edge video backgrounds for every section. Autoplays muted on desktop & mobile, lazy loaded with transparent header and sleek glass-morphism overlay.
          </p>
        </div>
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-[11px] font-bold text-rose-600">Cinematic Full Video</span>
          <div class="flex items-center gap-2">
            <a href="/admin/video_home.php" class="text-xs font-bold text-emerald-700 hover:underline">Configure</a>
            <span class="text-gray-300">•</span>
            <a href="/?layout=layout_6" target="_blank" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
              <span>Preview</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
          </div>
        </div>
      </label>

    </div>

    <!-- YouTube Video URL Setting -->
    <div class="pt-5 border-t border-gray-100">
      <label class="block text-xs font-bold text-gray-800 mb-1.5 flex items-center gap-2">
        <i class="fa-brands fa-youtube text-red-600 text-sm"></i>
        <span>YouTube Video URL or Video ID (Used by Layout 3 and Layout 5)</span>
      </label>
      <div class="flex flex-col sm:flex-row gap-2">
        <input type="text" name="homepage_youtube_url" id="homepage_youtube_url"
          value="<?= htmlspecialchars(get_h_val('homepage_youtube_url', 'https://www.youtube.com/watch?v=k4m3y1eH5Ww')) ?>"
          placeholder="e.g. https://www.youtube.com/watch?v=k4m3y1eH5Ww or https://youtu.be/..."
          class="flex-1 px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>
      <p class="text-[11px] text-gray-500 mt-1.5">
        Supports full URLs (<code class="text-stone-700">youtube.com/watch?v=...</code>), short URLs (<code class="text-stone-700">youtu.be/...</code>), embed URLs, and standalone video IDs. The video plays in the background automatically, muted, without YouTube controls and with a clean dark overlay.
      </p>
    </div>

  </div>

  <!-- Section 0B: Multi-Slide Carousel Customizer (3 Slides for Layout 2) -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="pb-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-sliders text-amber-600"></i>
          0B. Multi-Slide Hero Carousel (3 Slides for Layout 2)
        </h3>
        <p class="text-xs text-gray-500 mt-1">
          Customize all 3 slides that auto-rotate on Homepage Layout 2. Each slide has its own badge, title, subtitle, 2 CTA buttons, and background banner image.
        </p>
      </div>
      <a href="/?layout=layout_2" target="_blank" class="px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-200 flex items-center gap-1.5 transition">
        <span>Preview Slider Live</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
      </a>
    </div>

    <!-- 3 Slides Accordion / Cards Grid -->
    <div class="space-y-6">
      
      <!-- Slide 1 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-amber-600 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 1 (Default Hero)</span>
          <span class="text-xs text-gray-500 font-medium">Primary Featured Product or Guild</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide1_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_badge', 'Village Forge Heritage • Kalyani agrofood pvt ltd')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide1_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_title', 'Hand-Forged Tempered Steel Agricultural Tools')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide1_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide1_subtitle', 'Crafted from high-grade railway spring steel by generational masters of Hardoi. Razor-sharp sickles, heavy axes, and durable khurpis made for lifelong farming.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide1_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn1_text', 'Shop Forged Tools')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide1_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn1_link', '/products.php?category=traditional-tools')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide1_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn2_text', 'Meet Blacksmiths')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide1_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_btn2_link', '/artisans.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide1_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide1_image', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 1 Image</label>
            <input type="file" name="slider_slide1_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
          </div>
        </div>
      </div>

      <!-- Slide 2 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-emerald-700 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 2</span>
          <span class="text-xs text-gray-500 font-medium">Cold-Pressed Oils, Pulses & Pickles</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide2_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_badge', '100% Pure Cold-Pressed Organic Staples')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide2_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_title', 'Raw Kachi Ghani Mustard Oil & Desi Pickles')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide2_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide2_subtitle', 'Extracted through traditional wooden expellers without chemical refining. Paired with grandma-recipe sun-cured Awadhi mango, amla, and stuffed red chili achar.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide2_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn1_text', 'Explore Natural Staples')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide2_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn1_link', '/products.php?category=pulses-and-oils')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide2_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn2_text', 'View All Foods')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide2_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_btn2_link', '/products.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide2_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide2_image', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 2 Image</label>
            <input type="file" name="slider_slide2_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
          </div>
        </div>
      </div>

      <!-- Slide 3 Customizer -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="px-3 py-1 bg-purple-700 text-white font-black text-xs rounded-lg uppercase tracking-wider">Slide 3</span>
          <span class="text-xs text-gray-500 font-medium">Handlooms, Clay Pottery & Women SHGs</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Text (Top Pill)</label>
            <input type="text" name="slider_slide3_badge" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_badge', 'Women Self-Help Groups • Fair Trade Direct')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Slide Heading (Title)</label>
            <input type="text" name="slider_slide3_title" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_title', 'Handloom Textiles, Clay Pottery & Jute Crafts')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Slide Subtitle / Description</label>
          <textarea name="slider_slide3_subtitle" rows="2"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('slider_slide3_subtitle', 'Support rural craftswomen across Uttar Pradesh. 96% of sales price directly credits to artisan bank accounts via India Post payments bank.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="slider_slide3_btn1_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn1_text', 'Support Craftswomen')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL</label>
            <input type="text" name="slider_slide3_btn1_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn1_link', '/products.php?category=handicrafts')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="slider_slide3_btn2_text" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn2_text', 'Join as Seller (5-Step)')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL</label>
            <input type="text" name="slider_slide3_btn2_link" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_btn2_link', '/seller-register.php')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Background Image URL</label>
            <input type="text" name="slider_slide3_image" 
              value="<?= htmlspecialchars(get_h_val('slider_slide3_image', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1600&q=80')) ?>"
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Slide 3 Image</label>
            <input type="file" name="slider_slide3_file" accept="image/*"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-800 hover:file:bg-purple-200">
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Section 1: Themes & Colors -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-palette text-indigo-600"></i>
      1. Theme Color & Announcement Badge
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-2">Background Theme Color</label>
        <select name="hero_theme_color" id="hero_theme_color" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
          <option value="emerald" <?= get_h_val('hero_theme_color', 'emerald') === 'emerald' ? 'selected' : '' ?>>🌲 Emerald Green (Organic Agriculture & Nature)</option>
          <option value="crimson" <?= get_h_val('hero_theme_color') === 'crimson' ? 'selected' : '' ?>>🪔 Festive Crimson (Diwali & Festive Special)</option>
          <option value="amber" <?= get_h_val('hero_theme_color') === 'amber' ? 'selected' : '' ?>>🌾 Golden Amber (Holi & Harvest Season)</option>
          <option value="indigo" <?= get_h_val('hero_theme_color') === 'indigo' ? 'selected' : '' ?>>🌌 Royal Indigo (Artisan Exclusive)</option>
        </select>
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-bold text-gray-700 mb-2">Festive Announcement Badge Text</label>
        <input type="text" name="hero_badge_text" id="hero_badge_text" required
          value="<?= htmlspecialchars(get_h_val('hero_badge_text', '🌾 Kalyani agrofood pvt ltd • Hunar desh ka, ganv se ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>
    </div>
  </div>

  <!-- Section 2: Headlines & Description -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-heading text-brand-green"></i>
      2. Primary Headlines & Promotion Text
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Headline Line 1</label>
        <input type="text" name="hero_title_line1" id="hero_title_line1" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line1', 'Gaon ke hunar,')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Headline Line 2 (Highlighted Gradient)</label>
        <input type="text" name="hero_title_line2" id="hero_title_line2" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line2', 'ab har ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold text-amber-600">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">Subheadline Description</label>
        <textarea name="hero_subtitle" id="hero_subtitle" rows="3" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('hero_subtitle', 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Section 3: Call to Action Buttons -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 gap-2">
      <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-computer-mouse text-orange-500"></i>
        3. Call to Action Buttons (CTA Buttons)
      </h3>
      <span class="text-xs text-gray-500 font-medium">Configure button text, links, and show/hide toggles</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Button 1 Box -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="font-bold text-xs text-gray-900 flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-brand-orange"></span> Button 1 (Primary CTA)
          </span>
          <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="hero_btn1_show" value="1" <?= get_h_val('hero_btn1_show', '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 text-brand-orange rounded border-gray-300 focus:ring-brand-orange">
            <span class="text-xs font-bold text-gray-700">Show this button</span>
          </label>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Text</label>
            <input type="text" name="hero_btn1_text" id="hero_btn1_text"
              value="<?= htmlspecialchars(get_h_val('hero_btn1_text', 'Explore Marketplace')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 1 Link URL (Relative or https://)</label>
            <input type="text" name="hero_btn1_link" id="hero_btn1_link"
              value="<?= htmlspecialchars(get_h_val('hero_btn1_link', '/products.php')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div class="pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="hero_btn1_target" value="_blank" <?= get_h_val('hero_btn1_target', '_self') === '_blank' ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
              <span class="text-xs text-gray-600">Open in new tab (<code class="text-[11px]">_blank</code>)</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Button 2 Box -->
      <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="font-bold text-xs text-gray-900 flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-stone-500"></span> Button 2 (Secondary CTA)
          </span>
          <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="hero_btn2_show" value="1" <?= get_h_val('hero_btn2_show', '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 text-brand-orange rounded border-gray-300 focus:ring-brand-orange">
            <span class="text-xs font-bold text-gray-700">Show this button</span>
          </label>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Text</label>
            <input type="text" name="hero_btn2_text" id="hero_btn2_text"
              value="<?= htmlspecialchars(get_h_val('hero_btn2_text', 'Farming & Garden Tools')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Button 2 Link URL (Relative or https://)</label>
            <input type="text" name="hero_btn2_link" id="hero_btn2_link"
              value="<?= htmlspecialchars(get_h_val('hero_btn2_link', '/products.php?category=farming-traditional-tools')) ?>"
              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
          <div class="pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="hero_btn2_target" value="_blank" <?= get_h_val('hero_btn2_target', '_self') === '_blank' ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
              <span class="text-xs text-gray-600">Open in new tab (<code class="text-[11px]">_blank</code>)</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 4: Image & Floating Cards -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-image text-blue-600"></i>
      4. Hero Graphic & Floating Cards
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
      <div class="lg:col-span-7 space-y-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Banner Image URL</label>
          <input type="url" name="hero_image_url" id="hero_image_url"
            value="<?= htmlspecialchars(get_h_val('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Or Securely Upload New Image (MIME Verified)</label>
          <input type="file" name="hero_image_file" accept="image/jpeg,image/png,image/webp"
            class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          <p class="text-[11px] text-gray-400 mt-1">Max 5MB. JPG, PNG, or WebP format.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Floating Card 1 (Title)</label>
            <input type="text" name="hero_tag1_title" id="hero_tag1_title"
              value="<?= htmlspecialchars(get_h_val('hero_tag1_title', 'High-Carbon Tempered Steel')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Floating Card 1 (Subtitle)</label>
            <input type="text" name="hero_tag1_subtitle" id="hero_tag1_subtitle"
              value="<?= htmlspecialchars(get_h_val('hero_tag1_subtitle', 'Forged Desi Hansiya')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs">
          </div>
        </div>
      </div>

      <!-- Preview Image Box -->
      <div class="lg:col-span-5 text-center">
        <div class="p-3 bg-stone-50 rounded-2xl border border-gray-200 inline-block">
          <img id="imageDisplayPreview"
            src="<?= htmlspecialchars(get_h_val('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>"
            alt="Hero Preview" class="w-64 h-48 object-cover rounded-xl shadow-md border border-gray-200">
          <p class="text-[11px] text-gray-400 mt-2">Current Active Hero Graphic</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5: Stats Counter Badges -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-chart-simple text-emerald-600"></i>
      5. Key Metrics & Guarantee Badges
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">Badge 1 (Number)</label>
        <input type="text" name="hero_stat1_num" id="hero_stat1_num" value="<?= htmlspecialchars(get_h_val('hero_stat1_num', '100%')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">Badge 1 (Label)</label>
        <input type="text" name="hero_stat1_label" id="hero_stat1_label" value="<?= htmlspecialchars(get_h_val('hero_stat1_label', 'Forged Iron & Pure Dal')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">Badge 2 (Number)</label>
        <input type="text" name="hero_stat2_num" id="hero_stat2_num" value="<?= htmlspecialchars(get_h_val('hero_stat2_num', '40+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">Badge 2 (Label)</label>
        <input type="text" name="hero_stat2_label" id="hero_stat2_label" value="<?= htmlspecialchars(get_h_val('hero_stat2_label', 'Village Artisan Hubs')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">Badge 3 (Number)</label>
        <input type="text" name="hero_stat3_num" id="hero_stat3_num" value="<?= htmlspecialchars(get_h_val('hero_stat3_num', '₹499+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">Badge 3 (Label)</label>
        <input type="text" name="hero_stat3_label" id="hero_stat3_label" value="<?= htmlspecialchars(get_h_val('hero_stat3_label', 'Free India Delivery')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>
    </div>
  </div>

  <!-- Section 6: Safe Custom HTML/CSS Hero Engine -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-code text-indigo-600"></i>
        6. Sandboxed Custom Campaign Code (HTML / CSS)
      </h3>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="hero_has_custom_code" id="hero_has_custom_code" value="1" 
          <?= get_h_val('hero_has_custom_code') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
        <label for="hero_has_custom_code" class="text-xs font-extrabold text-gray-800">Enable Custom Code Hero Block</label>
      </div>
    </div>

    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 leading-relaxed flex items-center gap-2.5">
      <i class="fa-solid fa-shield-halved text-base text-amber-600 shrink-0"></i>
      <span><strong>Security Policy:</strong> All custom markup is strictly sanitized using server-side allowlist filters. Scripts and malicious events are automatically blocked.</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Custom HTML (Sanitized & Isolated)</label>
        <textarea name="hero_custom_html" id="hero_custom_html" rows="6" placeholder="<div class='hero-custom-promo'>...</div>"
          class="w-full px-4 py-3 bg-stone-900 text-amber-300 font-mono text-xs rounded-2xl border border-stone-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars(get_h_val('hero_custom_html', '')) ?></textarea>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Custom CSS (Scope Isolated)</label>
        <textarea name="hero_custom_css" id="hero_custom_css" rows="6" placeholder="#hero-custom-container { ... }"
          class="w-full px-4 py-3 bg-stone-900 text-indigo-300 font-mono text-xs rounded-2xl border border-stone-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars(get_h_val('hero_custom_css', '')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Save Button -->
  <div class="flex items-center justify-end gap-3 pb-4">
    <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-2xl text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer">
      <i class="fa-solid fa-floppy-disk text-sm"></i>
      <span>Save & Publish Hero Campaign</span>
    </button>
  </div>
</form>

<!-- Revision History & 1-Click Rollback Console -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-4">
  <div class="flex items-center justify-between border-b border-gray-100 pb-3">
    <div>
      <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
        Campaign Version History & 1-Click Rollback
      </h3>
      <p class="text-xs text-gray-500">Instantly restore any previously published hero configuration if changes need to be rolled back.</p>
    </div>
    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl">Total Versions: <?= count($version_history) ?></span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
      <thead>
        <tr class="bg-stone-50 text-gray-500 font-bold uppercase text-[10px] border-b border-gray-100">
          <th class="py-2.5 px-4">Version</th>
          <th class="py-2.5 px-4">Change Summary</th>
          <th class="py-2.5 px-4">Timestamp (Asia/Kolkata)</th>
          <th class="py-2.5 px-4 text-right">Rollback</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($version_history)): ?>
          <tr><td colspan="4" class="py-6 text-center text-gray-400">No previous versions found.</td></tr>
        <?php else: ?>
          <?php foreach ($version_history as $vh): ?>
            <tr class="hover:bg-stone-50/70">
              <td class="py-2.5 px-4 font-mono font-bold text-indigo-900">Version #<?= $vh['version_num'] ?></td>
              <td class="py-2.5 px-4 text-gray-700"><?= htmlspecialchars($vh['reason'] ?: 'Manual save') ?></td>
              <td class="py-2.5 px-4 text-gray-500 font-mono"><?= date('d M Y, h:i A', strtotime($vh['created_at'])) ?></td>
              <td class="py-2.5 px-4 text-right">
                <a href="/admin/hero_customizer.php?action=rollback&version_id=<?= $vh['id'] ?>" 
                  onclick="return confirm('Are you sure you want to restore Version #<?= $vh['version_num'] ?>?');"
                  class="px-3 py-1 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-lg text-[11px] transition inline-flex items-center gap-1">
                  <i class="fa-solid fa-rotate-left text-[10px]"></i> Restore
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const presets = {
  diwali: {
    theme: 'crimson',
    badge: '🪔 Shubh Deepawali Mega Savings Fair • Up to 50% Off Direct From Village Artisans',
    title1: 'Authentic Desi Flavors of Diwali,',
    title2: 'Handcrafted Village Heritage Delivered',
    subtitle: 'Celebrate Diwali with traditional terracotta diyas, copper & brass puja utensils, pure desi jaggery, unpolished Awadh pulses, and sun-cured pickles direct from village makers.',
    btn1: 'Diwali Specials',
    btn2: 'Handloom & Cottage Crafts',
    stat1_num: '50% OFF',
    stat1_label: 'Diwali Mega Sale',
    stat2_num: '100% Pure',
    stat2_label: 'Organic Awadh Pulses',
    stat3_num: '₹499+',
    stat3_label: 'Free Express Shipping',
    img: 'https://images.unsplash.com/photo-1514195037031-83d60ed3b448?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Handcrafted Puja Essentials',
    tag1_sub: 'Pure Brass & Terracotta',
    tag2_title: 'Diwali Rating',
    tag2_sub: '★ 4.98 Verified'
  },
  holi: {
    theme: 'amber',
    badge: '🎨 Rangotsav Holi Special • Natural Gulal & Rural Delights',
    title1: 'Vibrant Colors of Holi,',
    title2: 'Fragrance of Native Soil',
    subtitle: 'Organic herbal gulal made from flower extracts, pure festive arhar dal, traditional Banarasi spices, and homemade jackfruit & mango pickles.',
    btn1: 'Holi Festive Delicacies',
    btn2: 'Organic Herbal Gulal',
    stat1_num: '30% OFF',
    stat1_label: 'Holi Special Discount',
    stat2_num: 'Herbal Gulal',
    stat2_label: '100% Chemical-Free',
    stat3_num: '₹499+',
    stat3_label: 'Free Home Delivery',
    img: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Traditional Achar',
    tag1_sub: 'Pure Mustard Oil Cured',
    tag2_title: 'Festive Rating',
    tag2_sub: '★ 4.95 Verified'
  },
  navratri: {
    theme: 'crimson',
    badge: '🔱 Divine Navratri Festival • Fasting Staples & Pure Puja Utensils',
    title1: 'Blessings of the Divine Mother,',
    title2: 'Purity & Sacred Traditions',
    subtitle: 'Pure buckwheat & water chestnut flour, Himalayan rock salt, raw forest honey, amla murabba, and handcrafted brass puja thalis for nine holy days.',
    btn1: 'Fasting Essentials',
    btn2: 'Brass Puja Utensils',
    stat1_num: '100% Pure',
    stat1_label: 'Saatvik Fasting Staples',
    stat2_num: 'Handcrafted',
    stat2_label: 'Brass Diyas & Burners',
    stat3_num: '₹499+',
    stat3_label: 'Free Express Shipping',
    img: 'https://images.unsplash.com/photo-1609137144822-44a307c0303b?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Pure Rock Salt & Makhana',
    tag1_sub: 'Natural Awadh Harvest',
    tag2_title: 'Purity Certified',
    tag2_sub: '★ 4.99 Verified'
  },
  makar_sankranti: {
    theme: 'amber',
    badge: '🌾 Makar Sankranti & Pongal • Harvest Celebration for Farmers',
    title1: 'Fresh Harvest from the Fields,',
    title2: 'Direct Respect for Cultivators',
    subtitle: 'Fresh unpolished pulses, organic desi black gram, pure cane jaggery, traditional sesame sweets, and artisan forged farm implements.',
    btn1: 'Fresh Harvest Pulses',
    btn2: 'Durable Farming Tools',
    stat1_num: 'New Harvest',
    stat1_label: 'Unpolished Fresh Dals',
    stat2_num: 'Spring Steel',
    stat2_label: 'Durable Desi Sickles',
    stat3_num: '₹499+',
    stat3_label: 'Free India Delivery',
    img: 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Pure Cane Jaggery',
    tag1_sub: 'Chemical-Free Village Bheli',
    tag2_title: 'Sankranti Rating',
    tag2_sub: '★ 4.96 Verified'
  },
  rakshabandhan: {
    theme: 'crimson',
    badge: '🎀 Sacred Raksha Bandhan • Handcrafted Rakhis & Desi Village Sweets',
    title1: 'Sacred Bond of Siblings,',
    title2: 'Love and Traditional Heritage',
    subtitle: 'Eco-friendly seed rakhis by rural women SHGs, handmade brass gift sets, organic dry fruits, and pure desi cow ghee sweets delivered nationwide.',
    btn1: 'Eco Seed Rakhis',
    btn2: 'Brass Gift Hampers',
    stat1_num: '100% Eco',
    stat1_label: 'Cotton Seed Rakhis',
    stat2_num: 'SHG Women',
    stat2_label: 'Direct Financial Support',
    stat3_num: '₹499+',
    stat3_label: 'Guaranteed Express Courier',
    img: 'https://images.unsplash.com/photo-1597075687490-8f673c6c17f6?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Eco Seed Rakhi Set',
    tag1_sub: 'Plantable Sacred Thread',
    tag2_title: 'Customer Love',
    tag2_sub: '★ 4.99 Verified'
  },
  eid: {
    theme: 'emerald',
    badge: '🌙 Eid Mubarak Special • Traditional Attars, Pure Ghee & Handloom',
    title1: 'Festive Light of Eid,',
    title2: 'Rich Heritage & Sweetness',
    subtitle: 'Pure brass sevaiyan machines, Banarasi handloom fabrics, pure desi ghee, organic dry fruits, and Kannauj natural attars.',
    btn1: 'Eid Festive Collection',
    btn2: 'Kannauj Pure Attars',
    stat1_num: 'Pure Craft',
    stat1_label: 'Banarasi Handloom',
    stat2_num: 'Kannauj Attar',
    stat2_label: '100% Alcohol-Free',
    stat3_num: '₹499+',
    stat3_label: 'Fast Courier Dispatch',
    img: 'https://images.unsplash.com/photo-1564507592333-c60657eea523?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Brass Sevaiyan Press',
    tag1_sub: 'Traditional Heavy Build',
    tag2_title: 'Eid Rating',
    tag2_sub: '★ 4.97 Verified'
  },
  independence: {
    theme: 'indigo',
    badge: '🇮🇳 Swadeshi Freedom Festival • Support Rural Artisans & Farmers',
    title1: 'Power of Swadeshi Pride,',
    title2: 'Pride of Every Indian',
    subtitle: '100% made-in-India agricultural tools, unpolished organic pulses, handloom khadi textiles, and natural golden jute bags direct from the makers.',
    btn1: 'Swadeshi Products',
    btn2: 'Khadi & Handloom',
    stat1_num: '100% Desi',
    stat1_label: 'Crafted in Rural India',
    stat2_num: 'Direct Benefit',
    stat2_label: 'To Village Artisans',
    stat3_num: '₹499+',
    stat3_label: 'Free Postal Delivery',
    img: 'https://images.unsplash.com/photo-1532375810709-75b1da00537c?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Indian Steel Implements',
    tag1_sub: 'S S Industries Hardoi',
    tag2_title: 'Swadeshi Rating',
    tag2_sub: '★ 5.00 Pride'
  },
  republic: {
    theme: 'indigo',
    badge: '🇮🇳 Republic Day Special • Rural Skills & Indigenous Strength',
    title1: 'Foundation of the Republic,',
    title2: 'Our Villages & Artisans',
    subtitle: 'Connecting generational blacksmiths, weavers, and farmers across rural India. High-durability iron tools, pure pantry staples, and regional crafts.',
    btn1: 'Republic Day Specials',
    btn2: 'Farmer Essential Tools',
    stat1_num: 'Self-Reliant',
    stat1_label: 'Village to Doorstep',
    stat2_num: '40+ Clusters',
    stat2_label: 'Verified Workshops',
    stat3_num: '₹499+',
    stat3_label: 'Free Express Shipping',
    img: 'https://images.unsplash.com/photo-1566837945700-30057527ade0?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Railway Spring Steel',
    tag1_sub: 'Lifetime Durability',
    tag2_title: 'Verified Artisans',
    tag2_sub: '★ 4.98 Rating'
  },
  kisan_mela: {
    theme: 'emerald',
    badge: '⚒️ National Desi Kisan Fair • Massive Discounts on Tools & Farming Gear',
    title1: 'True Companion of Farmers,',
    title2: 'Rugged and Resilient Implements',
    subtitle: 'Heavy hand-forged sickles, khurpis, axes, spades, and pickaxes crafted from leaf-spring carbon steel by S S Industries.',
    btn1: 'Farming & Garden Tools',
    btn2: 'Wholesale B2B Enquiries',
    stat1_num: 'Lifetime Durability',
    stat1_label: 'High-Carbon Steel',
    stat2_num: 'Direct Factory',
    stat2_label: 'Zero Middlemen Markup',
    stat3_num: '₹499+',
    stat3_label: 'Free Parcel Dispatch',
    img: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Heavy Forged Sickle',
    tag1_sub: 'Solid Sheesham Handle',
    tag2_title: 'Farmer Choice',
    tag2_sub: '★ 4.97 Rating'
  },
  monsoon: {
    theme: 'emerald',
    badge: '🌧️ Monsoon Special • Rust-Resistant & Waterproof Assurances',
    title1: 'Monsoon Showers Arrive,',
    title2: 'Green Fields Across Bharat',
    subtitle: 'Rust-resistant oil-tempered carbon steel farm tools, waterproof natural jute sacks, and freshly ground roasted sattu & pickles.',
    btn1: 'Monsoon Special Offers',
    btn2: 'Eco Jute Storage',
    stat1_num: 'Rust-Proof',
    stat1_label: 'Oil-Tempered Steel',
    stat2_num: '100% Pure',
    stat2_label: 'Banarasi Pickles & Sattu',
    stat3_num: '₹499+',
    stat3_label: 'Secure Waterproof Packing',
    img: 'https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'Waterproof Jute Bag',
    tag1_sub: 'Women SHG Made',
    tag2_title: 'Monsoon Rating',
    tag2_sub: '★ 4.94 Rating'
  },
  general: {
    theme: 'emerald',
    badge: '🌾 Kalyani agrofood pvt ltd • Hunar desh ka, ganv se ghar tak',
    title1: 'Gaon ke hunar,',
    title2: 'ab har ghar tak',
    subtitle: 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.',
    btn1: 'Explore Marketplace',
    btn2: 'Farming & Garden Tools',
    stat1_num: '100%',
    stat1_label: 'Forged Iron & Pure Dal',
    stat2_num: '40+',
    stat2_label: 'Village Artisan Hubs',
    stat3_num: '₹499+',
    stat3_label: 'Free India Delivery',
    img: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'High-Carbon Tempered Steel',
    tag1_sub: 'Forged Desi Hansiya',
    tag2_title: 'Handcrafted Rating',
    tag2_sub: '★ 4.96 Verified'
  }
};

function loadPreset(key) {
  const p = presets[key];
  if (!p) return;

  document.getElementById('hero_festive_preset').value = key;
  document.getElementById('hero_theme_color').value = p.theme;
  document.getElementById('hero_badge_text').value = p.badge;
  document.getElementById('hero_title_line1').value = p.title1;
  document.getElementById('hero_title_line2').value = p.title2;
  document.getElementById('hero_subtitle').value = p.subtitle;
  document.getElementById('hero_btn1_text').value = p.btn1;
  document.getElementById('hero_btn2_text').value = p.btn2;
  document.getElementById('hero_stat1_num').value = p.stat1_num;
  document.getElementById('hero_stat1_label').value = p.stat1_label;
  document.getElementById('hero_stat2_num').value = p.stat2_num;
  document.getElementById('hero_stat2_label').value = p.stat2_label;
  document.getElementById('hero_stat3_num').value = p.stat3_num;
  document.getElementById('hero_stat3_label').value = p.stat3_label;
  document.getElementById('hero_image_url').value = p.img;
  document.getElementById('imageDisplayPreview').src = p.img;
  document.getElementById('hero_tag1_title').value = p.tag1_title;
  document.getElementById('hero_tag1_subtitle').value = p.tag1_sub;

  alert('Preset (' + key.toUpperCase() + ') loaded! Please click "Save Changes" below to apply.');
}

function openPreviewModal() {
  window.open('/', '_blank');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
