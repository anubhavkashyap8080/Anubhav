<?php
/**
 * GaunGaliHat SEO & Webmaster Governance
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save SEO Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_seo'])) {
    $seo_keys = [
        'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
        'seo_canonical_url', 'seo_google_analytics', 'seo_google_console',
        'seo_og_image', 'seo_twitter_handle', 'seo_organization_name',
        'seo_structured_data_enabled'
    ];

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        foreach ($seo_keys as $k) {
            $val = trim((string)($_POST[$k] ?? ''));
            $stmt_up->execute([$k, $val]);
        }

        set_flash('success', 'SEO मेटा टैग, सर्च कंसोल एवं एनालिटिक्स सेटिंग्स सफलतापूर्वक अपडेट की गईं!');
        header('Location: /admin/seo_settings.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Fetch current values
$stmt_set = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'seo_%'");
$seo = [];
while ($r = $stmt_set->fetch()) {
    $seo[$r['setting_key']] = $r['setting_value'];
}

function get_seo_val($key, $default = '') {
    global $seo;
    return $seo[$key] ?? $default;
}

$page_title = 'SEO एवं सर्च कंसोल सेटिंग्स (SEO Governance)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-magnifying-glass-chart text-indigo-600"></i>
      सर्च इंजन ऑप्टिमाइज़ेशन (SEO & Webmaster Tools)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Google Search Console, Google Analytics, OpenGraph सोशल कार्ड्स एवं XML साइटमैप
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/sitemap.xml" target="_blank" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-sitemap text-amber-600"></i> लाइव Sitemap.xml देखें
    </a>
    <a href="/robots.txt" target="_blank" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-robot text-blue-600"></i> Robots.txt देखें
    </a>
  </div>
</div>

<form action="/admin/seo_settings.php" method="POST" class="space-y-6">
  <input type="hidden" name="save_seo" value="1">

  <!-- 1. Meta Titles & Descriptions -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-tags text-indigo-600"></i> ग्लोबल मेटा टैग्स (Global Meta Tags)
    </h2>

    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          ग्लोबल मेटा शीर्षक (Meta Title - Max 60 chars) <span class="text-red-500">*</span>
        </label>
        <input type="text" name="seo_meta_title" required maxlength="70"
          value="<?= htmlspecialchars(get_seo_val('seo_meta_title', 'GaunGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        <p class="text-[11px] text-gray-400 mt-1">Google सर्च रिजल्ट में प्रदर्शित होने वाला प्राथमिक शीर्षक।</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          मेटा विवरण (Meta Description - 150 to 160 chars) <span class="text-red-500">*</span>
        </label>
        <textarea name="seo_meta_description" rows="3" required maxlength="250"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"><?= htmlspecialchars(get_seo_val('seo_meta_description', 'Direct from Indian village foundries & self-help groups. Authentic hand-forged agricultural sickles, khurpis, unpolished Awadh pulses, and Banarasi achar.')) ?></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">मेटा कीवर्ड्स (Keywords, comma-separated)</label>
          <input type="text" name="seo_meta_keywords"
            value="<?= htmlspecialchars(get_seo_val('seo_meta_keywords', 'farming tools, sickle, hansiya, khurpi, unpolished dal, banarasi achar, rural artisans, Hardoi foundries')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कैनोनिकल यूआरएल (Canonical Base URL)</label>
          <input type="url" name="seo_canonical_url"
            value="<?= htmlspecialchars(get_seo_val('seo_canonical_url', 'https://ganvgalihat.com')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Webmaster & Analytics Integration -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-brands fa-google text-red-500"></i> Google Search Console & Analytics
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Google Analytics 4 Measurement ID (G-XXXXXXX)</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <i class="fa-solid fa-chart-line text-xs"></i>
          </span>
          <input type="text" name="seo_google_analytics" placeholder="G-XXXXXXXXXX"
            value="<?= htmlspecialchars(get_seo_val('seo_google_analytics', 'G-GGH9087TEST')) ?>"
            class="w-full pl-9 pr-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Google Search Console HTML Verification Meta Tag</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <i class="fa-solid fa-shield-check text-xs"></i>
          </span>
          <input type="text" name="seo_google_console" placeholder="google-site-verification=xxxx"
            value="<?= htmlspecialchars(get_seo_val('seo_google_console', 'google-site-verification=ggh_rural_artisan_2026_verified')) ?>"
            class="w-full pl-9 pr-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Social OpenGraph Cards -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-share-nodes text-blue-600"></i> सोशल शेयर कार्ड्स (OpenGraph & Twitter Card)
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">सोशल शेयर इमेज यूआरएल (OG Image 1200x630)</label>
        <input type="url" name="seo_og_image"
          value="<?= htmlspecialchars(get_seo_val('seo_og_image', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Twitter / X हैंडल (@handle)</label>
        <input type="text" name="seo_twitter_handle" placeholder="@ganvgalihat"
          value="<?= htmlspecialchars(get_seo_val('seo_twitter_handle', '@ganvgalihat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>
    </div>
  </div>

  <!-- Submit Button -->
  <div class="flex items-center justify-end">
    <button type="submit"
      class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      SEO सेटिंग्स सहेजें (Save SEO Config)
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
