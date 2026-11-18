<?php
/**
 * GanvGaliHat SEO & Webmaster Governance
 * Complete On-Page SEO, Homepage Title, Meta Tags & Google SERP Preview Controls
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Security Audit H2: Enforce CSRF protection for all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

// Handle Save SEO Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_seo'])) {
    $seo_keys = [
        'homepage_title', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
        'seo_robots_meta', 'seo_canonical_url', 'seo_google_analytics', 'seo_google_console',
        'seo_og_title', 'seo_og_description', 'seo_og_image', 'seo_twitter_handle',
        'seo_organization_name', 'seo_structured_data_enabled'
    ];

    // Ensure homepage_title and seo_meta_title stay in sync if one is empty
    if (!empty($_POST['homepage_title']) && empty($_POST['seo_meta_title'])) {
        $_POST['seo_meta_title'] = $_POST['homepage_title'];
    } elseif (!empty($_POST['seo_meta_title']) && empty($_POST['homepage_title'])) {
        $_POST['homepage_title'] = $_POST['seo_meta_title'];
    }

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        foreach ($seo_keys as $k) {
            $val = trim((string)($_POST[$k] ?? ''));
            if ($k === 'homepage_title' || $k === 'seo_meta_title') {
                $val = preg_replace('/^0\s*[-—_:]+\s*/u', 'GanvGaliHat — ', $val);
                $val = preg_replace('/^0\s+/u', '', $val);
                if (empty($val) || $val === '0') {
                    $val = 'GanvGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples';
                }
            }
            $stmt_up->execute([$k, $val]);
        }

        log_audit_action('admin_update_seo', 'site_settings', 'Updated On-Page SEO settings and homepage title');
        set_flash('success', 'SEO settings and meta tags updated successfully.');
        header('Location: /admin/seo_settings.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Error saving SEO settings. Please try again.');
    }
}

// Fetch current values
$stmt_set = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'seo_%' OR setting_key = 'homepage_title'");
$seo = [];
while ($r = $stmt_set->fetch()) {
    $seo[$r['setting_key']] = $r['setting_value'];
}

function get_seo_val($key, $default = '') {
    global $seo;
    return $seo[$key] ?? $default;
}

$cur_home_title = get_seo_val('homepage_title', get_seo_val('seo_meta_title', ''));
$cur_home_title = preg_replace('/^0\s*[-—_:]+\s*/u', 'GanvGaliHat — ', trim((string)$cur_home_title));
$cur_home_title = preg_replace('/^0\s+/u', '', trim((string)$cur_home_title));
if (empty($cur_home_title) || $cur_home_title === '0') {
    $cur_home_title = 'GanvGaliHat — Desh ki mitti ka swad | Rural Tools & Farm Staples';
}
$cur_meta_desc = get_seo_val('seo_meta_description', 'Direct from Indian village foundries & self-help groups. Authentic hand-forged agricultural sickles, khurpis, unpolished Awadh pulses, and Banarasi achar.');
$cur_meta_kw = get_seo_val('seo_meta_keywords', 'farming tools, sickle, hansiya, khurpi, unpolished dal, banarasi achar, rural artisans, Hardoi foundries');
$default_site_canonical = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://ganvgalihat.com';
$cur_robots = get_seo_val('seo_robots_meta', 'index, follow');
$cur_canonical = get_seo_val('seo_canonical_url', $default_site_canonical);
$cur_og_image = get_seo_val('seo_og_image', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200');

$page_title = 'SEO & On-Page Settings — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-magnifying-glass-chart text-indigo-600"></i>
      SEO & On-Page Governance (Search Engine Optimization)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Homepage browser & search title, meta description, robots indexing, Google Search Console & search snippet preview
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/sitemap.xml" target="_blank" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-sitemap text-amber-600"></i> Sitemap.xml
    </a>
    <a href="/robots.txt" target="_blank" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-robot text-blue-600"></i> Robots.txt
    </a>
    <a href="/" target="_blank" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Homepage
    </a>
  </div>
</div>

<form action="/admin/seo_settings.php" method="POST" class="space-y-8">
  <?= csrf_field() ?>
  <input type="hidden" name="save_seo" value="1">

  <!-- 1. Live Google Search Result Preview (Real-time dynamic simulation) -->
  <div class="bg-gradient-to-br from-stone-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-800/40">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-indigo-800/50 gap-2">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-white text-stone-900 flex items-center justify-center font-bold text-sm shadow">
          <i class="fa-brands fa-google text-red-500"></i>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white tracking-wide">
            Google Search SERP Live Preview
          </h3>
          <p class="text-[11px] text-indigo-200">How your homepage title and snippet will appear in Google search results</p>
        </div>
      </div>
      <div class="flex items-center gap-1 bg-stone-800/80 p-1 rounded-xl border border-stone-700 text-xs">
        <button type="button" id="previewDeviceMobile" onclick="setPreviewDevice('mobile')" class="px-2.5 py-1 rounded-lg font-bold bg-indigo-600 text-white transition">
          <i class="fa-solid fa-mobile-screen-button"></i> Mobile
        </button>
        <button type="button" id="previewDeviceDesktop" onclick="setPreviewDevice('desktop')" class="px-2.5 py-1 rounded-lg font-bold text-gray-400 hover:text-white transition">
          <i class="fa-solid fa-desktop"></i> Desktop
        </button>
      </div>
    </div>

    <!-- The Google Card Mockup -->
    <div id="googleCardWrapper" class="max-w-xl bg-white rounded-2xl p-4 sm:p-5 shadow-2xl text-left border border-gray-100 transition-all">
      <div class="flex items-center gap-2 mb-1.5">
        <div class="w-6 h-6 rounded-full bg-stone-100 border border-gray-200 flex items-center justify-center p-1">
          <img src="/logo.png" alt="favicon" class="w-full h-full object-contain">
        </div>
        <div class="leading-tight">
          <div class="text-[12px] font-bold text-gray-800 truncate" id="serpSiteName"><?= htmlspecialchars(APP_NAME) ?></div>
          <div class="text-[11px] text-gray-500 font-mono truncate" id="serpUrlDisplay"><?= htmlspecialchars($cur_canonical) ?></div>
        </div>
      </div>
      <a href="javascript:void(0)" class="text-base sm:text-lg font-medium text-blue-700 hover:underline leading-snug line-clamp-2 block mb-1" id="serpTitleDisplay">
        <?= htmlspecialchars($cur_home_title) ?>
      </a>
      <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed" id="serpDescDisplay">
        <?= htmlspecialchars($cur_meta_desc) ?>
      </p>
    </div>
  </div>

  <!-- 2. Homepage Title & Global On-Page Meta Tags -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-heading text-indigo-600"></i>
        1. Homepage Title & On-Page Meta Tags
      </h3>
      <p class="text-xs text-gray-500 mt-0.5">
        Configure primary homepage title tag (&lt;title&gt;) and search engine meta tags
      </p>
    </div>

    <div class="space-y-5">
      <!-- Homepage Title Tag -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-bold text-gray-800">
            Homepage Title Tag (&lt;title&gt;) <span class="text-red-500">*</span>
          </label>
          <span id="homeTitleCounter" class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 font-semibold">
            0 / 60 chars
          </span>
        </div>
        <input type="text" name="homepage_title" id="input_homepage_title" required maxlength="70"
          value="<?= htmlspecialchars($cur_home_title) ?>"
          oninput="updateSerpPreview()"
          class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        <p class="text-[11px] text-gray-400 mt-1">
          This title appears in the browser tab, social sharing, and as the main clickable link in Google search (50-60 chars recommended).
        </p>
      </div>

      <!-- Also syncs with seo_meta_title -->
      <input type="hidden" name="seo_meta_title" id="input_seo_meta_title" value="<?= htmlspecialchars($cur_home_title) ?>">

      <!-- Meta Description -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-bold text-gray-800">
            Meta Description (&lt;meta name="description"&gt;) <span class="text-red-500">*</span>
          </label>
          <span id="metaDescCounter" class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 font-semibold">
            0 / 160 chars
          </span>
        </div>
        <textarea name="seo_meta_description" id="input_meta_desc" rows="3" required maxlength="250"
          oninput="updateSerpPreview()"
          class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs"><?= htmlspecialchars($cur_meta_desc) ?></textarea>
        <p class="text-[11px] text-gray-400 mt-1">
          Snippet summary displayed beneath the title in search engine results (140 to 160 chars recommended).
        </p>
      </div>

      <!-- Keywords & Robots Directives -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-gray-800 mb-1">Meta Keywords (Comma Separated)</label>
          <input type="text" name="seo_meta_keywords" id="input_meta_kw"
            value="<?= htmlspecialchars($cur_meta_kw) ?>"
            class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
          <p class="text-[11px] text-gray-400 mt-1">Comma-separated target keywords.</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-800 mb-1">Search Engine Indexing Directive (Robots Meta)</label>
          <select name="seo_robots_meta" class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
            <option value="index, follow" <?= $cur_robots === 'index, follow' ? 'selected' : '' ?>>index, follow (Recommended - Allow indexing & link following)</option>
            <option value="noindex, follow" <?= $cur_robots === 'noindex, follow' ? 'selected' : '' ?>>noindex, follow (Hide from search, follow internal links)</option>
            <option value="index, nofollow" <?= $cur_robots === 'index, nofollow' ? 'selected' : '' ?>>index, nofollow (Index page, do not follow links)</option>
            <option value="noindex, nofollow" <?= $cur_robots === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow (Completely block indexing - Dev/Staging)</option>
          </select>
          <p class="text-[11px] text-gray-400 mt-1">Robots meta tag for Googlebot and other web crawlers.</p>
        </div>
      </div>

      <!-- Canonical URL -->
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Canonical Base URL</label>
        <input type="url" name="seo_canonical_url" id="input_canonical_url"
          oninput="updateSerpPreview()"
          value="<?= htmlspecialchars($cur_canonical) ?>"
          class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        <p class="text-[11px] text-gray-400 mt-1">Primary canonical domain to prevent duplicate content penalties (e.g. <?= htmlspecialchars($default_site_canonical) ?>).</p>
      </div>

    </div>
  </div>

  <!-- 3. Social OpenGraph & Twitter Cards -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-share-nodes text-blue-600"></i>
        2. Social Media Sharing Cards (OpenGraph & Twitter Cards)
      </h3>
      <p class="text-xs text-gray-500 mt-0.5">
        Image and preview details displayed when sharing links on WhatsApp, Facebook, LinkedIn, and Twitter/X
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Social Share Image URL (OG Image 1200x630)</label>
        <input type="url" name="seo_og_image" id="input_og_image"
          value="<?= htmlspecialchars($cur_og_image) ?>"
          placeholder="https://..."
          class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        <p class="text-[11px] text-gray-400 mt-1">Direct absolute URL to a high-resolution 1200x630 banner image.</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Twitter / X Handle (@handle)</label>
        <input type="text" name="seo_twitter_handle" placeholder="@ganvgalihat"
          value="<?= htmlspecialchars(get_seo_val('seo_twitter_handle', '@ganvgalihat')) ?>"
          class="w-full px-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        <p class="text-[11px] text-gray-400 mt-1">Official Twitter / X account handle.</p>
      </div>
    </div>
  </div>

  <!-- 4. Webmaster & Search Console Integration -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="border-b border-gray-100 pb-3">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-brands fa-google text-red-500"></i>
        3. Search Console & Web Analytics (Google Tools Integration)
      </h3>
      <p class="text-xs text-gray-500 mt-0.5">
        Google Search Console verification meta tag and Google Analytics 4 (GA4) Measurement ID
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Google Analytics 4 Measurement ID</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
            <i class="fa-solid fa-chart-line text-xs"></i>
          </span>
          <input type="text" name="seo_google_analytics" placeholder="G-XXXXXXXXXX"
            value="<?= htmlspecialchars(get_seo_val('seo_google_analytics', 'G-GGH9087TEST')) ?>"
            class="w-full pl-9 pr-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Google Analytics 4 Property Measurement ID (G-XXXXXXXXXX).</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Google Search Console Verification Tag / Code</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
            <i class="fa-solid fa-shield-halved text-xs"></i>
          </span>
          <input type="text" name="seo_google_console" placeholder="google-site-verification=xxxx or token code only"
            value="<?= htmlspecialchars(get_seo_val('seo_google_console', 'google-site-verification=ggh_rural_artisan_2026_verified')) ?>"
            class="w-full pl-9 pr-4 py-2.5 bg-stone-50 border border-gray-300 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition shadow-2xs">
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Google Search Console ownership verification token or meta tag.</p>
      </div>
    </div>
  </div>

  <!-- Submit Bar -->
  <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-200 shadow-sm">
    <div class="text-xs text-gray-500 font-medium">
      <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Settings will immediately take effect across the homepage and all store pages upon saving.
    </div>
    <button type="submit"
      class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer active:scale-95">
      <i class="fa-solid fa-floppy-disk"></i>
      Save All SEO Settings
    </button>
  </div>
</form>

<script>
function updateSerpPreview() {
  const t = document.getElementById('input_homepage_title').value || 'GanvGaliHat';
  const d = document.getElementById('input_meta_desc').value || 'Authentic rural artisan products';
  const u = document.getElementById('input_canonical_url').value || '<?= addslashes($default_site_canonical) ?>';

  document.getElementById('serpTitleDisplay').innerText = t;
  document.getElementById('serpDescDisplay').innerText = d;
  document.getElementById('serpUrlDisplay').innerText = u;

  // Sync hidden seo_meta_title
  document.getElementById('input_seo_meta_title').value = t;

  // Update counters
  const tLen = t.length;
  const dLen = d.length;

  const tCnt = document.getElementById('homeTitleCounter');
  tCnt.innerText = tLen + ' / 60 chars';
  if (tLen >= 50 && tLen <= 60) {
    tCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold';
  } else if (tLen > 60) {
    tCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold';
  } else {
    tCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 font-semibold';
  }

  const dCnt = document.getElementById('metaDescCounter');
  dCnt.innerText = dLen + ' / 160 chars';
  if (dLen >= 140 && dLen <= 160) {
    dCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold';
  } else if (dLen > 160) {
    dCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold';
  } else {
    dCnt.className = 'text-[11px] font-mono px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 font-semibold';
  }
}

function setPreviewDevice(device) {
  const wrap = document.getElementById('googleCardWrapper');
  const btnM = document.getElementById('previewDeviceMobile');
  const btnD = document.getElementById('previewDeviceDesktop');

  if (device === 'mobile') {
    wrap.className = 'max-w-sm bg-white rounded-2xl p-4 shadow-2xl text-left border border-gray-100 transition-all mx-auto sm:mx-0';
    btnM.className = 'px-2.5 py-1 rounded-lg font-bold bg-indigo-600 text-white transition';
    btnD.className = 'px-2.5 py-1 rounded-lg font-bold text-gray-400 hover:text-white transition';
  } else {
    wrap.className = 'max-w-2xl bg-white rounded-2xl p-5 shadow-2xl text-left border border-gray-100 transition-all';
    btnD.className = 'px-2.5 py-1 rounded-lg font-bold bg-indigo-600 text-white transition';
    btnM.className = 'px-2.5 py-1 rounded-lg font-bold text-gray-400 hover:text-white transition';
  }
}

// Initial run
document.addEventListener('DOMContentLoaded', updateSerpPreview);
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
