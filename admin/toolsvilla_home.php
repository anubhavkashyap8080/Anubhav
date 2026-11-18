<?php
/**
 * GanvGaliHat - Admin: Tools & Agri Marketplace (Layout 7) Customizer
 * Customizes all sections of the ToolsVilla-style homepage layout:
 * Hero banner, machinery badges, metrics, trust cards, 5-point value proposition bar,
 * and modular category spotlights for Farming, HoReCa, and Pumps & Electrical.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

$page_title = "Tools & Agri Marketplace (Layout 7) Customizer";
$current_page = "toolsvilla_home.php";

$is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
$upsert = $is_sqlite
    ? $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)")
    : $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

// Default configuration dictionary
$l7_defaults = [
    // Hero Banner
    'l7_hero_badge'     => "India's One Stop Rural & Agri Marketplace 🇮🇳",
    'l7_hero_title'     => "Everything Your Business & Farm Needs",
    'l7_hero_subtitle'  => "Professional Hand-Forged Tools, Farm Machinery & Authentic Village Produce Direct From 40+ Cottage Forges",
    'l7_hero_btn_text'  => "Explore Products",
    'l7_hero_btn_link'  => "/products.php",
    'l7_hero_bg_img'    => "https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1400&q=80",
    
    // Machinery Pills
    'l7_hero_tag1'      => "Farm & Garden",
    'l7_hero_tag2'      => "Food Processing",
    'l7_hero_tag3'      => "Pumps & Motors",
    'l7_hero_tag4'      => "Packaging Solutions",
    'l7_hero_tag5'      => "Workshop Tools",
    
    // Hero Stats
    'l7_hero_stat1'     => "70,000+ Products",
    'l7_hero_stat2'     => "10,000+ Farmers",
    'l7_hero_stat3'     => "50+ Forges",
    
    // Right Trust Cards
    'l7_card1_title'    => "Trusted by 10,000+ Farmers",
    'l7_card1_rating'   => "4.9",
    'l7_card1_sub'      => "Certified Hand-Forged Quality & Verified Artisans",
    'l7_card2_title'    => "Easy 3-Day Returns",
    'l7_card2_sub'      => "Hassle-free replacement for all farm tools & implements",
    'l7_card2_link'     => "/terms.php",
    
    // 5 Value Proposition Points
    'l7_trust1_t'       => "Free Shipping",
    'l7_trust1_s'       => "On all orders above ₹499",
    'l7_trust2_t'       => "3-Day Returns",
    'l7_trust2_s'       => "Easy hassle-free",
    'l7_trust3_t'       => "100% Genuine",
    'l7_trust3_s'       => "Authentic artisan forges",
    'l7_trust4_t'       => "GST Invoice",
    'l7_trust4_s'       => "Save up to 18% with ITC",
    'l7_trust5_t'       => "Secure Payment",
    'l7_trust5_s'       => "100% safe checkout",
    
    // Category Spotlight 1 (Farming & Agriculture)
    'l7_b1_title'       => "Farming & Agriculture",
    'l7_b1_spot_title'  => "Your farm deserves the right equipment.",
    'l7_b1_spot_desc'   => "Everything you need to keep your crops growing with generational steel endurance.",
    'l7_b1_spot_img'    => "https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=800&q=80",
    'l7_b1_spot_btn'    => "Browse Farming Tools",
    'l7_b1_spot_link'   => "/products.php?category=farming-traditional-tools",
    
    // Category Spotlight 2 (Food & HoReCa)
    'l7_b2_title'       => "Food & HoReCa",
    'l7_b2_spot_title'  => "Everything hotels, restaurants & caterers need pack it, serve it, cook it and keep it spotless.",
    'l7_b2_spot_desc'   => "Pure unpolished Awadh pulses, raw cold-pressed mustard oil, and authentic grandma pickles.",
    'l7_b2_spot_img'    => "https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80",
    'l7_b2_spot_btn'    => "Shop all Food & HoReCa",
    'l7_b2_spot_link'   => "/products.php?category=pulses-organic-grains",
    
    // Category Spotlight 3 (Pumps & Electrical)
    'l7_b3_title'       => "Pumps and Electrical Component",
    'l7_b3_spot_title'  => "Find water pumps and electrical essentials",
    'l7_b3_spot_desc'   => "For farms, homes and workshops. Rugged leaf-spring forges, anvil tools & heavy machinery.",
    'l7_b3_spot_img'    => "https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80",
    'l7_b3_spot_btn'    => "Shop all Pump & Electrical",
    'l7_b3_spot_link'   => "/products.php?category=rural-utility-machinery",
];

// Handle Reset to Defaults
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_defaults'])) {
    $del = $pdo->prepare("DELETE FROM site_settings WHERE setting_key = ?");
    foreach (array_keys($l7_defaults) as $k) {
        $del->execute([$k]);
    }
    log_audit_action('toolsvilla_home_reset', 'content', 'site_settings', null, 'Reset Layout 7 Tools & Agri Marketplace settings to defaults');
    set_flash('success', 'Layout 7 settings have been reset to factory defaults.');
    header('Location: /admin/toolsvilla_home.php');
    exit;
}

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_l7_settings'])) {
    foreach ($l7_defaults as $k => $default_val) {
        if (isset($_POST[$k])) {
            $val = trim((string)$_POST[$k]);
            $upsert->execute([$k, $val]);
        }
    }
    
    // Activate layout if requested
    if (!empty($_POST['activate_layout'])) {
        $upsert->execute(['homepage_layout', 'layout_7']);
    }
    
    log_audit_action('toolsvilla_home_update', 'content', 'site_settings', null, 'Updated Layout 7 Tools & Agri Marketplace settings');
    set_flash('success', 'Layout 7 settings updated successfully.');
    header('Location: /admin/toolsvilla_home.php');
    exit;
}

$active_layout = get_setting('homepage_layout', 'layout_1');

function l7_val($key, $default) {
    return get_setting($key, $default);
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="max-w-6xl mx-auto space-y-6 pb-20">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-teal-900 via-slate-900 to-stone-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-teal-800/40">
    <div>
      <div class="flex items-center gap-3 mb-2">
        <span class="px-3 py-1 bg-teal-500/20 text-teal-300 border border-teal-500/30 font-bold text-xs rounded-full uppercase tracking-wider">
          Layout 7 Customizer
        </span>
        <?php if ($active_layout === 'layout_7'): ?>
          <span class="px-3 py-1 bg-emerald-500 text-white font-black text-xs rounded-full flex items-center gap-1.5 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> Active Homepage
          </span>
        <?php else: ?>
          <span class="px-3 py-1 bg-stone-800 text-stone-300 font-bold text-xs rounded-full border border-stone-700">
            Current Active: <?= htmlspecialchars(strtoupper($active_layout)) ?>
          </span>
        <?php endif; ?>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2.5">
        <i class="fa-solid fa-screwdriver-wrench text-teal-400"></i>
        <span>Tools & Agri Marketplace Layout</span>
      </h1>
      <p class="text-stone-300 text-sm mt-1 max-w-2xl">
        Configure the ToolsVilla-style homepage: persistent 12-department navigation, high-impact equipment hero, verified trust badges, 5-point value bar, and modular department showcase blocks.
      </p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
      <a href="/?layout=layout_7" target="_blank" class="px-4 py-2.5 bg-white hover:bg-stone-100 text-stone-900 font-bold text-xs rounded-xl shadow transition inline-flex items-center gap-2">
        <i class="fa-solid fa-arrow-up-right-from-square text-teal-600"></i>
        <span>Preview Layout 7</span>
      </a>
      <form method="POST" onsubmit="return confirm('Are you sure you want to reset all Layout 7 settings to defaults?');" class="inline">
        <?= csrf_field() ?>
        <button type="submit" name="reset_defaults" value="1" class="px-4 py-2.5 bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white font-bold text-xs rounded-xl border border-stone-700 transition inline-flex items-center gap-1.5">
          <i class="fa-solid fa-rotate-left"></i>
          <span>Reset Defaults</span>
        </button>
      </form>
    </div>
  </div>

  <form method="POST" action="/admin/toolsvilla_home.php" class="space-y-6">
    <?= csrf_field() ?>

    <!-- Section 1: Hero Banner & Machinery Tags (Center 50% Column) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">1</span>
          <span>Central Equipment Hero Banner</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Controls the center 50% showcase banner seen alongside the vertical category menu.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">Top Pill Badge Text</label>
          <input type="text" name="l7_hero_badge" value="<?= htmlspecialchars(l7_val('l7_hero_badge', $l7_defaults['l7_hero_badge'])) ?>" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Headline Title</label>
          <input type="text" name="l7_hero_title" value="<?= htmlspecialchars(l7_val('l7_hero_title', $l7_defaults['l7_hero_title'])) ?>" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Background Image URL</label>
          <input type="url" name="l7_hero_bg_img" value="<?= htmlspecialchars(l7_val('l7_hero_bg_img', $l7_defaults['l7_hero_bg_img'])) ?>" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">Subtitle / Supporting Description</label>
          <textarea name="l7_hero_subtitle" rows="2" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:bg-white transition"><?= htmlspecialchars(l7_val('l7_hero_subtitle', $l7_defaults['l7_hero_subtitle'])) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Text</label>
          <input type="text" name="l7_hero_btn_text" value="<?= htmlspecialchars(l7_val('l7_hero_btn_text', $l7_defaults['l7_hero_btn_text'])) ?>" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Link URL</label>
          <input type="text" name="l7_hero_btn_link" value="<?= htmlspecialchars(l7_val('l7_hero_btn_link', $l7_defaults['l7_hero_btn_link'])) ?>" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
        </div>
      </div>

      <!-- 5 Floating Machinery Category Pills -->
      <div class="pt-4 border-t border-stone-100">
        <label class="block text-xs font-bold text-stone-800 mb-2">5 Machinery Floating Badges (Overlaid on Hero)</label>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <?php for ($i = 1; $i <= 5; $i++): $k = "l7_hero_tag$i"; ?>
            <div>
              <span class="text-[10px] font-bold text-stone-500 mb-0.5 block">Badge #<?= $i ?></span>
              <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(l7_val($k, $l7_defaults[$k])) ?>" class="w-full px-3 py-2 bg-stone-50 border border-stone-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
            </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- 3 Metrics -->
      <div class="pt-4 border-t border-stone-100">
        <label class="block text-xs font-bold text-stone-800 mb-2">3 Hero Trust Metrics (Bottom Bar of Hero)</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <?php for ($i = 1; $i <= 3; $i++): $k = "l7_hero_stat$i"; ?>
            <div>
              <span class="text-[10px] font-bold text-stone-500 mb-0.5 block">Metric #<?= $i ?></span>
              <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(l7_val($k, $l7_defaults[$k])) ?>" class="w-full px-3 py-2 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <!-- Section 2: Stacked Trust Cards (Right 25% Column) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">2</span>
          <span>Right Column Trust Cards (Stacked Widgets)</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Controls the 2 stacked cards on the right side of the hero section (Google 4.9 Rating & 3-Day Returns).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1 -->
        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/60 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-amber-950 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-star text-amber-500"></i> Card 1: Google Rating & Proof
            </h3>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-stone-700 mb-1">Title</label>
            <input type="text" name="l7_card1_title" value="<?= htmlspecialchars(l7_val('l7_card1_title', $l7_defaults['l7_card1_title'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs font-bold">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-bold text-stone-700 mb-1">Rating Score</label>
              <input type="text" name="l7_card1_rating" value="<?= htmlspecialchars(l7_val('l7_card1_rating', $l7_defaults['l7_card1_rating'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs font-bold">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-stone-700 mb-1">Subtitle</label>
              <input type="text" name="l7_card1_sub" value="<?= htmlspecialchars(l7_val('l7_card1_sub', $l7_defaults['l7_card1_sub'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs">
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/60 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-rotate-left text-emerald-600"></i> Card 2: 3-Day Returns Policy
            </h3>
          </div>
          <div>
            <label class="block text-[11px] font-bold text-stone-700 mb-1">Title</label>
            <input type="text" name="l7_card2_title" value="<?= htmlspecialchars(l7_val('l7_card2_title', $l7_defaults['l7_card2_title'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs font-bold">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-bold text-stone-700 mb-1">Subtitle</label>
              <input type="text" name="l7_card2_sub" value="<?= htmlspecialchars(l7_val('l7_card2_sub', $l7_defaults['l7_card2_sub'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-stone-700 mb-1">Link URL</label>
              <input type="text" name="l7_card2_link" value="<?= htmlspecialchars(l7_val('l7_card2_link', $l7_defaults['l7_card2_link'])) ?>" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: 5-Point Value Proposition Bar -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">3</span>
          <span>5-Point Value Proposition Bar</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Horizontal trust guarantees displayed across desktop and mobile.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <?php
        $icons = ['fa-truck-fast', 'fa-rotate-left', 'fa-shield-halved', 'fa-file-invoice-dollar', 'fa-lock'];
        for ($i = 1; $i <= 5; $i++):
            $kt = "l7_trust{$i}_t";
            $ks = "l7_trust{$i}_s";
        ?>
          <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-2">
            <span class="text-[10px] font-black uppercase text-stone-400 flex items-center gap-1.5">
              <i class="fa-solid <?= $icons[$i - 1] ?> text-teal-600"></i> Item <?= $i ?>
            </span>
            <div>
              <label class="block text-[10px] font-bold text-stone-600 mb-0.5">Title</label>
              <input type="text" name="<?= $kt ?>" value="<?= htmlspecialchars(l7_val($kt, $l7_defaults[$kt])) ?>" class="w-full px-2.5 py-1.5 bg-white border border-stone-200 rounded-lg text-xs font-bold">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-stone-600 mb-0.5">Subtitle</label>
              <input type="text" name="<?= $ks ?>" value="<?= htmlspecialchars(l7_val($ks, $l7_defaults[$ks])) ?>" class="w-full px-2.5 py-1.5 bg-white border border-stone-200 rounded-lg text-[11px]">
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Section 4: Category Spotlight 1 - Farming & Agriculture -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-black">4</span>
          <span>Department Spotlight 1: Farming & Agriculture (Screenshot 2)</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Features high-impact left banner + 6 pastel-accented subcategory tiles (Horticulture, Agri Supplies, Forged Sickles, Animal Husbandry, Power Equipment, Solar & Utility).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Section Title</label>
          <input type="text" name="l7_b1_title" value="<?= htmlspecialchars(l7_val('l7_b1_title', $l7_defaults['l7_b1_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Headline</label>
          <input type="text" name="l7_b1_spot_title" value="<?= htmlspecialchars(l7_val('l7_b1_spot_title', $l7_defaults['l7_b1_spot_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Description</label>
          <textarea name="l7_b1_spot_desc" rows="2" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs"><?= htmlspecialchars(l7_val('l7_b1_spot_desc', $l7_defaults['l7_b1_spot_desc'])) ?></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Banner Image URL</label>
          <input type="url" name="l7_b1_spot_img" value="<?= htmlspecialchars(l7_val('l7_b1_spot_img', $l7_defaults['l7_b1_spot_img'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-mono">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Text</label>
          <input type="text" name="l7_b1_spot_btn" value="<?= htmlspecialchars(l7_val('l7_b1_spot_btn', $l7_defaults['l7_b1_spot_btn'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Link URL</label>
          <input type="text" name="l7_b1_spot_link" value="<?= htmlspecialchars(l7_val('l7_b1_spot_link', $l7_defaults['l7_b1_spot_link'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs">
        </div>
      </div>
    </div>

    <!-- Section 5: Category Spotlight 2 - Food & HoReCa -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-black">5</span>
          <span>Department Spotlight 2: Food & HoReCa (Screenshot 3)</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Features 6 pastel-accented subcategories on left + high-impact chef/kitchen spotlight on right.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Section Title</label>
          <input type="text" name="l7_b2_title" value="<?= htmlspecialchars(l7_val('l7_b2_title', $l7_defaults['l7_b2_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Headline</label>
          <input type="text" name="l7_b2_spot_title" value="<?= htmlspecialchars(l7_val('l7_b2_spot_title', $l7_defaults['l7_b2_spot_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Description</label>
          <textarea name="l7_b2_spot_desc" rows="2" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs"><?= htmlspecialchars(l7_val('l7_b2_spot_desc', $l7_defaults['l7_b2_spot_desc'])) ?></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Banner Image URL</label>
          <input type="url" name="l7_b2_spot_img" value="<?= htmlspecialchars(l7_val('l7_b2_spot_img', $l7_defaults['l7_b2_spot_img'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-mono">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Text</label>
          <input type="text" name="l7_b2_spot_btn" value="<?= htmlspecialchars(l7_val('l7_b2_spot_btn', $l7_defaults['l7_b2_spot_btn'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Link URL</label>
          <input type="text" name="l7_b2_spot_link" value="<?= htmlspecialchars(l7_val('l7_b2_spot_link', $l7_defaults['l7_b2_spot_link'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs">
        </div>
      </div>
    </div>

    <!-- Section 6: Category Spotlight 3 - Pumps & Electrical -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4">
        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-800 flex items-center justify-center text-xs font-black">6</span>
          <span>Department Spotlight 3: Pumps & Electrical Component (Screenshot 4)</span>
        </h2>
        <p class="text-xs text-stone-500 mt-1">
          Features water pump action image on left + 6 pastel subcategories on right.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Section Title</label>
          <input type="text" name="l7_b3_title" value="<?= htmlspecialchars(l7_val('l7_b3_title', $l7_defaults['l7_b3_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Headline</label>
          <input type="text" name="l7_b3_spot_title" value="<?= htmlspecialchars(l7_val('l7_b3_spot_title', $l7_defaults['l7_b3_spot_title'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Description</label>
          <textarea name="l7_b3_spot_desc" rows="2" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs"><?= htmlspecialchars(l7_val('l7_b3_spot_desc', $l7_defaults['l7_b3_spot_desc'])) ?></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Spotlight Banner Image URL</label>
          <input type="url" name="l7_b3_spot_img" value="<?= htmlspecialchars(l7_val('l7_b3_spot_img', $l7_defaults['l7_b3_spot_img'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-mono">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Text</label>
          <input type="text" name="l7_b3_spot_btn" value="<?= htmlspecialchars(l7_val('l7_b3_spot_btn', $l7_defaults['l7_b3_spot_btn'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold">
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-stone-700 mb-1">CTA Button Link URL</label>
          <input type="text" name="l7_b3_spot_link" value="<?= htmlspecialchars(l7_val('l7_b3_spot_link', $l7_defaults['l7_b3_spot_link'])) ?>" class="w-full px-3.5 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs">
        </div>
      </div>
    </div>

    <!-- Sticky Bottom Actions -->
    <div class="sticky bottom-6 z-20 bg-stone-900/95 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-stone-700 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
      <label class="flex items-center gap-3 cursor-pointer text-stone-200 text-xs font-bold">
        <input type="checkbox" name="activate_layout" value="1" <?= $active_layout === 'layout_7' ? 'checked' : '' ?> class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 bg-stone-800 border-stone-600">
        <span>Make Layout 7 the Active Live Homepage upon saving</span>
      </label>

      <div class="flex items-center gap-3 w-full sm:w-auto">
        <a href="/?layout=layout_7" target="_blank" class="flex-1 sm:flex-none px-5 py-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-bold text-xs text-center border border-stone-700 transition">
          Preview
        </a>
        <button type="submit" name="save_l7_settings" value="1" class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-stone-950 font-black text-xs text-center shadow-lg transition">
          <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save Settings
        </button>
      </div>
    </div>

  </form>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
