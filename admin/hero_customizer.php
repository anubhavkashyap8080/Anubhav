<?php
/**
 * GaunGaliHat Festival Hero Section & Safe Campaign Builder
 * 11 Indian Festival Presets, Asia/Kolkata Datetime Scheduler,
 * Sandboxed Custom HTML/CSS Engine with Allowlist Sanitizer & 1-Click Rollback
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
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
            set_flash('success', 'हीरो सेक्शन को सफलतापूर्वक संस्करण (Version #' . $ver['version_num'] . ') पर रीस्टोर कर दिया गया!');
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
        'hero_festive_preset', 'hero_theme_color', 'hero_badge_text',
        'hero_title_line1', 'hero_title_line2', 'hero_subtitle',
        'hero_btn1_text', 'hero_btn1_link', 'hero_btn2_text', 'hero_btn2_link',
        'hero_stat1_num', 'hero_stat1_label', 'hero_stat2_num', 'hero_stat2_label',
        'hero_stat3_num', 'hero_stat3_label',
        'hero_tag1_title', 'hero_tag1_subtitle', 'hero_tag2_title', 'hero_tag2_subtitle',
        'hero_has_custom_code', 'hero_custom_css'
    ];

    $image_url = clean_input($_POST['hero_image_url'] ?? '');

    // Handle File Upload if provided via secure_upload_image
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_res = secure_upload_image('hero_image_file', 'uploads/banners/');
        if ($upload_res['success']) {
            $image_url = $upload_res['path'];
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

        set_flash('success', 'हीरो सेक्शन व त्योहार अभियान सफलतापूर्वक लाइव व सुरक्षित कर दिया गया! (Version #' . $next_v . ')');
        header('Location: /admin/hero_customizer.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
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

$page_title = 'हीरो सेक्शन व त्योहार सेल कस्टमाइज़र — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2">
      <h1 class="text-2xl sm:text-3xl font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
        त्योहार एवं सेल हीरो कैंपेन बिल्डर (Campaign Builder)
      </h1>
      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
        11 Indian Festival Presets
      </span>
    </div>
    <p class="text-xs sm:text-sm text-gray-500 mt-1">
      दीपावली, होली, नवरात्रि, मकर संक्रांति, ईद, 15 अगस्त, 26 जनवरी, फसल कटाई एवं मानसून किसान सेल हेतु पूर्ण रूप से अनुकूलन योग्य
    </p>
  </div>

  <div class="flex items-center gap-2.5">
    <button type="button" onclick="openPreviewModal()" class="px-4 py-2.5 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
      <i class="fa-solid fa-eye text-amber-400"></i> सैंडबॉक्स प्रीव्यू देखें
    </button>
    <a href="/" target="_blank" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-indigo-200">
      <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> लाइव स्टोर
    </a>
  </div>
</div>

<!-- 11 Ready-Made Indian Festival Presets Tray -->
<div class="mb-8 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-3xl p-5 sm:p-6 text-white shadow-lg">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-3">
    <div>
      <h3 class="font-extrabold text-sm sm:text-base flex items-center gap-2">
        <i class="fa-solid fa-bolt text-yellow-200"></i> 1-क्लिक भारतीय त्योहार व शॉपिंग उत्सव प्रीसेट (Instant Festival Presets):
      </h3>
      <p class="text-xs text-amber-100 mt-0.5">किसी भी त्योहार पर क्लिक करते ही रंग, घोषणा पट्टी, शीर्षक, और आंकड़े स्वतः लोड हो जाएंगे।</p>
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 pt-1">
    <button type="button" onclick="loadPreset('diwali')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🪔 दीपावली महासेल
    </button>
    <button type="button" onclick="loadPreset('holi')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🎨 होली रंगोत्सव
    </button>
    <button type="button" onclick="loadPreset('navratri')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🔱 नवरात्रि व पूजा
    </button>
    <button type="button" onclick="loadPreset('harvest')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌾 मकर संक्रांति मेला
    </button>
    <button type="button" onclick="loadPreset('raksha')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🎀 रक्षाबंधन विशेष
    </button>
    <button type="button" onclick="loadPreset('eid')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌙 ईद मुबारक सेल
    </button>
    <button type="button" onclick="loadPreset('independence')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🇮🇳 स्वतंत्रता दिवस (15 Aug)
    </button>
    <button type="button" onclick="loadPreset('republic')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🇮🇳 गणतंत्र दिवस (26 Jan)
    </button>
    <button type="button" onclick="loadPreset('kisan_mela')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      ⚒️ देसी किसान मेला
    </button>
    <button type="button" onclick="loadPreset('monsoon')" class="p-2.5 bg-white text-orange-950 font-bold text-xs rounded-2xl hover:bg-amber-50 transition shadow-2xs text-center">
      🌧️ मानसून किसान सेल
    </button>
    <button type="button" onclick="loadPreset('general')" class="p-2.5 bg-orange-950/80 text-amber-200 border border-amber-400/40 font-bold text-xs rounded-2xl hover:bg-orange-950 transition col-span-2 sm:col-span-1 text-center">
      🏛️ सामान्य देसी हाट
    </button>
  </div>
</div>

<form action="/admin/hero_customizer.php" method="POST" enctype="multipart/form-data" class="space-y-6">
  <?= csrf_field() ?>
  <input type="hidden" name="save_hero_settings" value="1">
  <input type="hidden" name="hero_festive_preset" id="hero_festive_preset" value="<?= htmlspecialchars(get_h_val('hero_festive_preset', 'general')) ?>">

  <!-- Section 1: Themes & Colors -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-palette text-indigo-600"></i>
      1. थीम रंग एवं हेडर घोषणा पट्टी (Theme Color & Announcement Badge)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-2">पृष्ठभूमि रंग थीम (Theme Color)</label>
        <select name="hero_theme_color" id="hero_theme_color" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
          <option value="emerald" <?= get_h_val('hero_theme_color', 'emerald') === 'emerald' ? 'selected' : '' ?>>🌲 एमराल्ड ग्रीन (देसी खेती व प्रकृति)</option>
          <option value="crimson" <?= get_h_val('hero_theme_color') === 'crimson' ? 'selected' : '' ?>>🪔 फेस्टिव क्रिमसन (दीपावली व करवा चौथ)</option>
          <option value="amber" <?= get_h_val('hero_theme_color') === 'amber' ? 'selected' : '' ?>>🌾 सुनहरा अम्बर (होली व फसल कटाई)</option>
          <option value="indigo" <?= get_h_val('hero_theme_color') === 'indigo' ? 'selected' : '' ?>>🌌 रॉयल इंडिगो (कारीगर एक्सक्लूसिव)</option>
        </select>
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-bold text-gray-700 mb-2">उत्सव/सेल घोषणा बैज (Festive Top Badge Text)</label>
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
      2. मुख्य शीर्षक एवं प्रचार विवरण (Headlines & Promotion Text)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य शीर्षक लाइन 1 (Title Line 1)</label>
        <input type="text" name="hero_title_line1" id="hero_title_line1" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line1', 'Gaon ke hunar,')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य शीर्षक लाइन 2 (हाईलाइटेड - Title Line 2)</label>
        <input type="text" name="hero_title_line2" id="hero_title_line2" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line2', 'ab har ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold text-amber-600">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">प्रचार विवरण / उप-शीर्षक (Subheadline Description)</label>
        <textarea name="hero_subtitle" id="hero_subtitle" rows="3" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('hero_subtitle', 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Section 3: Call to Action Buttons -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-computer-mouse text-orange-500"></i>
      3. कॉल टू एक्शन बटन (CTA Buttons)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 1 टेक्स्ट (Primary Button)</label>
        <input type="text" name="hero_btn1_text" id="hero_btn1_text"
          value="<?= htmlspecialchars(get_h_val('hero_btn1_text', 'Explore Marketplace')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 1 लिंक (Primary Link)</label>
        <input type="text" name="hero_btn1_link" id="hero_btn1_link"
          value="<?= htmlspecialchars(get_h_val('hero_btn1_link', '/products.php')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 2 टेक्स्ट (Secondary Button)</label>
        <input type="text" name="hero_btn2_text" id="hero_btn2_text"
          value="<?= htmlspecialchars(get_h_val('hero_btn2_text', 'Farming & Garden Tools')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 2 लिंक (Secondary Link)</label>
        <input type="text" name="hero_btn2_link" id="hero_btn2_link"
          value="<?= htmlspecialchars(get_h_val('hero_btn2_link', '/products.php?category=farming-traditional-tools')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>
    </div>
  </div>

  <!-- Section 4: Image & Floating Cards -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-image text-blue-600"></i>
      4. हीरो ग्राफिक छवि एवं फ्लोटिंग कार्ड्स (Hero Graphic & Cards)
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
      <div class="lg:col-span-7 space-y-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">बैनर छवि URL (Banner Image URL)</label>
          <input type="url" name="hero_image_url" id="hero_image_url"
            value="<?= htmlspecialchars(get_h_val('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">या नई छवि सुरक्षित अपलोड करें (MIME Check)</label>
          <input type="file" name="hero_image_file" accept="image/jpeg,image/png,image/webp"
            class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          <p class="text-[11px] text-gray-400 mt-1">अधिकतम 5MB। JPG, PNG या WebP प्रारूप।</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">फ्लोटिंग कार्ड 1 (शीर्षक)</label>
            <input type="text" name="hero_tag1_title" id="hero_tag1_title"
              value="<?= htmlspecialchars(get_h_val('hero_tag1_title', 'High-Carbon Tempered Steel')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">फ्लोटिंग कार्ड 1 (उप-शीर्षक)</label>
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
          <p class="text-[11px] text-gray-400 mt-2">वर्तमान सक्रिय हीरो ग्राफिक</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5: Stats Counter Badges -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-extrabold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-chart-simple text-emerald-600"></i>
      5. आंकड़े एवं गारंटी बैज (Stats Badges)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 1 (संख्या)</label>
        <input type="text" name="hero_stat1_num" id="hero_stat1_num" value="<?= htmlspecialchars(get_h_val('hero_stat1_num', '100%')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 1 (लेबल)</label>
        <input type="text" name="hero_stat1_label" id="hero_stat1_label" value="<?= htmlspecialchars(get_h_val('hero_stat1_label', 'Forged Iron & Pure Dal')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 2 (संख्या)</label>
        <input type="text" name="hero_stat2_num" id="hero_stat2_num" value="<?= htmlspecialchars(get_h_val('hero_stat2_num', '40+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 2 (लेबल)</label>
        <input type="text" name="hero_stat2_label" id="hero_stat2_label" value="<?= htmlspecialchars(get_h_val('hero_stat2_label', 'Village Artisan Hubs')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 3 (संख्या)</label>
        <input type="text" name="hero_stat3_num" id="hero_stat3_num" value="<?= htmlspecialchars(get_h_val('hero_stat3_num', '₹499+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 3 (लेबल)</label>
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
        6. सुरक्षित कस्टम HTML / CSS ब्लॉक (Sandboxed Custom Campaign Code)
      </h3>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="hero_has_custom_code" id="hero_has_custom_code" value="1" 
          <?= get_h_val('hero_has_custom_code') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
        <label for="hero_has_custom_code" class="text-xs font-extrabold text-gray-800">कस्टम कोड हीरो सक्रिय करें</label>
      </div>
    </div>

    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 leading-relaxed flex items-center gap-2.5">
      <i class="fa-solid fa-shield-halved text-base text-amber-600 shrink-0"></i>
      <span><strong>सुरक्षा नीति:</strong> सभी कस्टम मार्कअप सर्वर-साइड allowlist फ़िल्टर द्वारा सुरक्षित किए जाते हैं। स्क्रिप्ट्स व दुर्भावनापूर्ण इवेंट्स ब्लॉक रहेंगे।</span>
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
      <span>हीरो कैंपेन सुरक्षित एवं लाइव करें (Save & Publish Hero)</span>
    </button>
  </div>
</form>

<!-- Revision History & 1-Click Rollback Console -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-4">
  <div class="flex items-center justify-between border-b border-gray-100 pb-3">
    <div>
      <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
        अभियान संस्करण इतिहास एवं 1-क्लिक रोलबैक (Version Rollback Engine)
      </h3>
      <p class="text-xs text-gray-500">यदि नया बैनर या अभियान सही न लगे, तो किसी भी पिछले सुरक्षित संस्करण पर तुरंत वापस लौटें।</p>
    </div>
    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl">कुल संस्करण: <?= count($version_history) ?></span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
      <thead>
        <tr class="bg-stone-50 text-gray-500 font-bold uppercase text-[10px] border-b border-gray-100">
          <th class="py-2.5 px-4">संस्करण</th>
          <th class="py-2.5 px-4">परिवर्तन कारण</th>
          <th class="py-2.5 px-4">समय (Asia/Kolkata)</th>
          <th class="py-2.5 px-4 text-right">कार्य (Rollback)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($version_history)): ?>
          <tr><td colspan="4" class="py-6 text-center text-gray-400">कोई पूर्व संस्करण उपलब्ध नहीं है।</td></tr>
        <?php else: ?>
          <?php foreach ($version_history as $vh): ?>
            <tr class="hover:bg-stone-50/70">
              <td class="py-2.5 px-4 font-mono font-bold text-indigo-900">Version #<?= $vh['version_num'] ?></td>
              <td class="py-2.5 px-4 text-gray-700"><?= htmlspecialchars($vh['reason'] ?: 'Manual save') ?></td>
              <td class="py-2.5 px-4 text-gray-500 font-mono"><?= date('d M Y, h:i A', strtotime($vh['created_at'])) ?></td>
              <td class="py-2.5 px-4 text-right">
                <a href="/admin/hero_customizer.php?action=rollback&version_id=<?= $vh['id'] ?>" 
                  onclick="return confirm('क्या आप निश्चित रूप से संस्करण #<?= $vh['version_num'] ?> पर वापस लौटना चाहते हैं?');"
                  class="px-3 py-1 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-lg text-[11px] transition inline-flex items-center gap-1">
                  <i class="fa-solid fa-rotate-left text-[10px]"></i> रीस्टोर करें
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
    badge: '🪔 शुभ दीपावली महाबचत मेला • 50% तक छूट सीधे ग्रामीण कारीगरों से',
    title1: 'दीपावली का असली देसी स्वाद,',
    title2: 'गाँव के हुनर हर घर तक',
    subtitle: 'इस दीपावली पर पारंपरिक मिट्टी के दीये, पूजा-पाठ हेतु तांबे व पीतल के बर्तन, देसी गुड़, अरहर की शुद्ध दाल और बनारसी आचार सीधे गाँव से मंगाएं।',
    btn1: 'दीपावली स्पेशल उत्पाद',
    btn2: 'हथकरघा व कुटीर सामान',
    stat1_num: '50% छूट',
    stat1_label: 'दीपावली महासेल',
    stat2_num: '100% शुद्ध',
    stat2_label: 'देसी अवध दालें',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त एक्सप्रेस डाक',
    img: 'https://images.unsplash.com/photo-1514195037031-83d60ed3b448?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'हस्तनिर्मित पूजा सामग्री',
    tag1_sub: 'बनारसी पीतल व मिट्टी',
    tag2_title: 'दीपावली रेटिंग',
    tag2_sub: '★ 4.98 सत्यापित'
  },
  holi: {
    theme: 'amber',
    badge: '🎨 रंगोत्सव होली विशेष • प्राकृतिक गुलाल व ग्रामीण स्वाद की बहार',
    title1: 'होली के रंग, खुशियों के संग,',
    title2: 'माटी की सौंधी महक',
    subtitle: 'प्राकृतिक फूलों से बने ऑर्गेनिक हर्बल गुलाल, होली की शुद्ध अरहर दाल, बनारसी कचौरी मसाले और घर का पारंपरिक कटहल व आम का आचार।',
    btn1: 'होली स्पेशल व्यंजन',
    btn2: 'प्राकृतिक हर्बल गुलाल',
    stat1_num: '30% छूट',
    stat1_label: 'होली स्पेशल डिस्काउंट',
    stat2_num: 'हर्बल गुलाल',
    stat2_label: '100% केमिकल-मुक्त',
    stat3_num: '₹499+',
    stat3_label: 'फ्री होम डिलीवरी',
    img: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'पारंपरिक कटहल आचार',
    tag1_sub: 'शुद्ध सरसों का तेल',
    tag2_title: 'होली विशेष स्वाद',
    tag2_sub: '★ 4.95 सत्यापित'
  },
  navratri: {
    theme: 'crimson',
    badge: '🔱 शक्ति आराधना नवरात्रि उत्सव • व्रत आहार एवं शुद्ध पूजा सामग्री',
    title1: 'माता रानी की असीम कृपा,',
    title2: 'पवित्रता एवं समर्पण',
    subtitle: 'नौ दिनों के पावन व्रत हेतु कुट्टू-सिंघाड़े का आटा, सेंधा नमक, शुद्ध शहद, आवंला मुरब्बा और तांबे-पीतल की हस्तनिर्मित पूजा थाली।',
    btn1: 'व्रत आहार उत्पाद',
    btn2: 'पीतल पूजा सामग्री',
    stat1_num: '100% शुद्ध',
    stat1_label: 'सात्विक व्रत आहार',
    stat2_num: 'हस्तनिर्मित',
    stat2_label: 'पीतल धूपदानी व दीप',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त एक्सप्रेस डाक',
    img: 'https://images.unsplash.com/photo-1609137144822-44a307c0303b?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'शुद्ध सेंधा नमक व मखाना',
    tag1_sub: 'प्राकृतिक अवध कृषि',
    tag2_title: 'पवित्रता प्रमाणन',
    tag2_sub: '★ 4.99 सत्यापित'
  },
  harvest: {
    theme: 'amber',
    badge: '🌾 मकर संक्रांति व पोंगल • नई फसल का अन्नदाता उत्सव',
    title1: 'खेतों की नई ताज़ा फसल,',
    title2: 'अन्नदाता को सीधा सम्मान',
    subtitle: 'संक्रांति की ताज़ा कुटी दालें, देसी काला चना, गुड़ की शुद्ध भेली, पारंपरिक तिल व कारीगरों द्वारा गढ़ी गई नई फसल कटाई की हंसिया।',
    btn1: 'नई फसल की दालें',
    btn2: 'मजबूत कृषि औजार',
    stat1_num: 'नई फसल',
    stat1_label: 'ताज़ा बिना पॉलिश दाल',
    stat2_num: 'स्प्रिंग स्टील',
    stat2_label: 'टिकाऊ देसी हंसिया',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त इंडिया डिलीवरी',
    img: 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'हाई-कार्बन स्टील हंसिया',
    tag1_sub: 'मल्लावां लोहार शिल्प',
    tag2_title: 'किसान रेटिंग',
    tag2_sub: '★ 4.96 सत्यापित'
  },
  raksha: {
    theme: 'crimson',
    badge: '🎀 रक्षाबंधन स्नेह उत्सव • ग्रामीण बहनों द्वारा हस्तनिर्मित राखियां व उपहार',
    title1: 'भाई-बहन का अटूट स्नेह,',
    title2: 'देसी उपहारों का उपहार',
    subtitle: 'महिला स्वयं सहायता समूहों द्वारा रेशमी धागों व बीजों से निर्मित इको-फ्रेंडली राखियां, बनारसी मीठे मुरब्बे और शुद्ध सूती वस्त्र।',
    btn1: 'हस्तनिर्मित राखियां',
    btn2: 'पारंपरिक उपहार',
    stat1_num: 'महिला SHG',
    stat1_label: '100% हस्तनिर्मित',
    stat2_num: 'इको फ्रेंडली',
    stat2_label: 'प्लास्टिक मुक्त राखी',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त एक्सप्रेस डिलीवरी',
    img: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'रेशम धागा राखी',
    tag1_sub: 'महिला समूह संडीला',
    tag2_title: 'स्नेह गारंटी',
    tag2_sub: '★ 4.97 रेटिंग'
  },
  eid: {
    theme: 'emerald',
    badge: '🌙 ईद मुबारक विशेष • बनारसी खादी, हस्तशिल्प एवं पारंपरिक सौगात',
    title1: 'बरकत और खुशहाली का पर्व,',
    title2: 'हथकरघा की नफ़ासत',
    subtitle: 'हाथ से बुने शुद्ध सूती कुर्ते, बनारसी हस्तकला, खुशबूदार मसाले और कुटीर उद्योग की प्रामाणिक दस्तकारी हर घर तक।',
    btn1: 'खादी वस्त्र संग्रह',
    btn2: 'हस्तशिल्प उपहार',
    stat1_num: 'शुद्ध खादी',
    stat1_label: 'हथकरघा बुनाई',
    stat2_num: 'हस्तशिल्प',
    stat2_label: 'प्राचीन कारीगरी',
    stat3_num: '₹499+',
    stat3_label: 'एक्सप्रेस डाक सेवा',
    img: 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'काशी बुनकर खादी',
    tag1_sub: 'हथकरघा बुनाई',
    tag2_title: 'कारीगर रेटिंग',
    tag2_sub: '★ 4.95 सत्यापित'
  },
  independence: {
    theme: 'amber',
    badge: '🇮🇳 स्वतंत्रता दिवस विशेष • स्वदेशी अपनाएं, ग्रामीण भारत को आत्मनिर्भर बनाएं',
    title1: 'स्वदेशी का सच्चा गौरव,',
    title2: 'हर भारतीय का अभिमान',
    subtitle: '100% भारत में निर्मित कृषि औजार, बिना पॉलिश दालें, खादी वस्त्र और प्राकृतिक जूट बैग अपनाकर ग्रामीण कारीगरों का मान बढ़ाएं।',
    btn1: 'स्वदेशी उत्पाद',
    btn2: 'खादी एवं हथकरघा',
    stat1_num: '100% स्वदेशी',
    stat1_label: 'ग्रामीण भारत में निर्मित',
    stat2_num: 'सीधा लाभ',
    stat2_label: 'गांव के कारीगरों तक',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त डाक डिलीवरी',
    img: 'https://images.unsplash.com/photo-1532375810709-75b1da00537c?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'भारतीय इस्पात औजार',
    tag1_sub: 'एस एस इंडस्ट्रीज हरदोई',
    tag2_title: 'स्वदेशी रेटिंग',
    tag2_sub: '★ 5.00 स्वाभिमान'
  },
  republic: {
    theme: 'indigo',
    badge: '🇮🇳 गणतंत्र दिवस उत्सव • ग्रामीण कौशल एवं स्वदेशी भारत की शक्ति',
    title1: 'गणतंत्र की मूल शक्ति,',
    title2: 'हमारे गाँव और कारीगर',
    subtitle: 'भारत के कोने-कोने से जुड़े लोहार, बुनकर और अन्नदाता किसान। उच्च-गुणवत्ता वाले औजार व शुद्ध खाद्य पदार्थ घर बैठे पाएं।',
    btn1: 'गणतंत्र विशेष सेल',
    btn2: 'किसान उपयोगी औजार',
    stat1_num: 'सशक्त भारत',
    stat1_label: 'गाँव से सीधे घर तक',
    stat2_num: '40+ क्लस्टर',
    stat2_label: 'सत्यापित कार्यशालाएं',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त एक्सप्रेस डाक',
    img: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'रेल स्प्रिंग स्टील',
    tag1_sub: 'उच्च टिकाऊपन औजार',
    tag2_title: 'सत्यापित कारीगर',
    tag2_sub: '★ 4.98 रेटिंग'
  },
  kisan_mela: {
    theme: 'emerald',
    badge: '⚒️ राष्ट्रीय देसी किसान मेला • औजारों व उन्नत खेती उपकरणों पर भारी छूट',
    title1: 'खेतों का सच्चा साथी,',
    title2: 'मजबूत और टिकाऊ औजार',
    subtitle: 'एस एस इंडस्ट्रीज द्वारा लीफ-स्प्रिंग स्टील से गढ़ी गई भारी हंसिया, खुरपी, फावड़ा, कुदाल व कटाई औजार न्यूनतम दरों पर सीधे कारखाने से।',
    btn1: 'खेती व बागवानी औजार',
    btn2: 'थोक बुकिंग पूछताछ',
    stat1_num: 'लाइफटाइम टिकाऊ',
    stat1_label: 'हाई-कार्बन स्टील',
    stat2_num: 'सीधा कारखाना',
    stat2_label: 'बिचौलियों से मुक्ति',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त पार्सल डाक',
    img: 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'देसी मजबूत हंसिया',
    tag1_sub: 'शीशम लकड़ी का मुठ्ठा',
    tag2_title: 'किसान पसंद',
    tag2_sub: '★ 4.97 रेटिंग'
  },
  monsoon: {
    theme: 'emerald',
    badge: '🌧️ मानसून किसान सेल • बारिश में टिकाऊपन की पक्की गारंटी',
    title1: 'सावन की रिमझिम फुहारें,',
    title2: 'खेतों में हरियाली की बहार',
    subtitle: 'बरसात में जंग-रोधी उच्च कार्बन स्टील औजार, जल-रोधी प्राकृतिक जूट बैग और ताज़ा भुना अवध का सत्तू व बनारसी भरवां लाल मिर्च अचार।',
    btn1: 'मानसून स्पेशल ऑफर',
    btn2: 'इको जूट स्टोरेज',
    stat1_num: 'जंग प्रतिरोधी',
    stat1_label: 'ऑयल-टेम्पर्ड स्टील',
    stat2_num: '100% शुद्ध',
    stat2_label: 'बनारसी अचार व सत्तू',
    stat3_num: '₹499+',
    stat3_label: 'सुरक्षित वाटरप्रूफ पैकिंग',
    img: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
    tag1_title: 'वाटरप्रूफ जूट बैग',
    tag1_sub: 'महिला स्वयं सहायता समूह',
    tag2_title: 'मानसून रेटिंग',
    tag2_sub: '★ 4.94 रेटिंग'
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

  alert('प्रीसेट (' + key.toUpperCase() + ') लोड हो गया! कृपया नीचे "सहेजें" बटन पर क्लिक करें।');
}

function openPreviewModal() {
  window.open('/', '_blank');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
