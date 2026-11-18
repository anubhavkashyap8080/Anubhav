<?php
/**
 * GanvGaliHat - Layout 7: "ToolsVilla Style B2B & Agri Marketplace"
 * Inspired by ToolsVilla.com
 * Features:
 *  - Persistent Department / Vertical Category Navigation Sidebar
 *  - High-Impact Central Hero Banner with Floating Machinery Tags & Key Metrics
 *  - Stacked Right Trust / Rating Cards
 *  - 5-Point Value Proposition Strip (Free Shipping, 3-Day Returns, 100% Genuine, GST Invoice, Secure Payment)
 *  - Shop by Category Carousel
 *  - 3 Category Showcase Blocks (Farming & Agriculture, Food & HoReCa, Pumps & Electrical)
 *    matching Toolsvilla screenshots with soft pastel accents and spotlight cards
 *  - B2B Bulk Wholesale Banner with GST Input Credit Highlight
 *  - Featured Live Products Rail with is_featured DESC sorting
 *  - Artisan Haversine Workshop Finder
 * 
 * Customizable via /admin/toolsvilla_home.php
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../functions.php';
}

// Helper for safe HTML escaping
if (!function_exists('l7_e')) {
    function l7_e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// ---------------- Settings with Sensible Defaults ----------------
$l7_hero_badge     = get_setting('l7_hero_badge', "India's One Stop Rural & Agri Marketplace 🇮🇳");
$l7_hero_title     = get_setting('l7_hero_title', "Everything Your Business & Farm Needs");
$l7_hero_subtitle  = get_setting('l7_hero_subtitle', "Professional Hand-Forged Tools, Farm Machinery & Authentic Village Produce Direct From 40+ Cottage Forges");
$l7_hero_btn_text  = get_setting('l7_hero_btn_text', "Explore Products");
$l7_hero_btn_link  = get_setting('l7_hero_btn_link', "/products.php");
$l7_hero_bg_img    = get_setting('l7_hero_bg_img', "https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1400&q=80");

$l7_hero_tag1      = get_setting('l7_hero_tag1', "Farm & Garden");
$l7_hero_tag2      = get_setting('l7_hero_tag2', "Food Processing");
$l7_hero_tag3      = get_setting('l7_hero_tag3', "Pumps & Motors");
$l7_hero_tag4      = get_setting('l7_hero_tag4', "Packaging Solutions");
$l7_hero_tag5      = get_setting('l7_hero_tag5', "Workshop Tools");

$l7_hero_stat1     = get_setting('l7_hero_stat1', "70,000+ Products");
$l7_hero_stat2     = get_setting('l7_hero_stat2', "10,000+ Farmers");
$l7_hero_stat3     = get_setting('l7_hero_stat3', "50+ Forges");

$l7_card1_title    = get_setting('l7_card1_title', "Trusted by 10,000+ Farmers");
$l7_card1_rating   = get_setting('l7_card1_rating', "4.9");
$l7_card1_sub      = get_setting('l7_card1_sub', "Certified Hand-Forged Quality & Verified Artisans");

$l7_card2_title    = get_setting('l7_card2_title', "Easy 3-Day Returns");
$l7_card2_sub      = get_setting('l7_card2_sub', "Hassle-free replacement for all farm tools & implements");
$l7_card2_link     = get_setting('l7_card2_link', "/terms.php");

// 5 Value Propositions
$l7_trust1_t = get_setting('l7_trust1_t', 'Free Shipping');
$l7_trust1_s = get_setting('l7_trust1_s', 'On all orders above ₹499');
$l7_trust2_t = get_setting('l7_trust2_t', '3-Day Returns');
$l7_trust2_s = get_setting('l7_trust2_s', 'Easy hassle-free');
$l7_trust3_t = get_setting('l7_trust3_t', '100% Genuine');
$l7_trust3_s = get_setting('l7_trust3_s', 'Authentic artisan forges');
$l7_trust4_t = get_setting('l7_trust4_t', 'GST Invoice');
$l7_trust4_s = get_setting('l7_trust4_s', 'Save up to 18% with ITC');
$l7_trust5_t = get_setting('l7_trust5_t', 'Secure Payment');
$l7_trust5_s = get_setting('l7_trust5_s', '100% safe checkout');

// Category Block 1 (Farming & Tools)
$l7_b1_title      = get_setting('l7_b1_title', 'Farming & Agriculture');
$l7_b1_spot_title = get_setting('l7_b1_spot_title', 'Your farm deserves the right equipment.');
$l7_b1_spot_desc  = get_setting('l7_b1_spot_desc', 'Everything you need to keep your crops growing with generational steel endurance.');
$l7_b1_spot_img   = get_setting('l7_b1_spot_img', 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=800&q=80');
$l7_b1_spot_btn   = get_setting('l7_b1_spot_btn', 'Browse Farming Tools');
$l7_b1_spot_link  = get_setting('l7_b1_spot_link', '/products.php?category=farming-traditional-tools');

// Category Block 2 (Food & HoReCa)
$l7_b2_title      = get_setting('l7_b2_title', 'Food & HoReCa');
$l7_b2_spot_title = get_setting('l7_b2_spot_title', 'Everything hotels, restaurants & caterers need pack it, serve it, cook it and keep it spotless.');
$l7_b2_spot_desc  = get_setting('l7_b2_spot_desc', 'Pure unpolished Awadh pulses, raw cold-pressed mustard oil, and authentic grandma pickles.');
$l7_b2_spot_img   = get_setting('l7_b2_spot_img', 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80');
$l7_b2_spot_btn   = get_setting('l7_b2_spot_btn', 'Shop all Food & HoReCa');
$l7_b2_spot_link  = get_setting('l7_b2_spot_link', '/products.php?category=pulses-organic-grains');

// Category Block 3 (Pumps & Electrical)
$l7_b3_title      = get_setting('l7_b3_title', 'Pumps and Electrical Component');
$l7_b3_spot_title = get_setting('l7_b3_spot_title', 'Find water pumps and electrical essentials');
$l7_b3_spot_desc  = get_setting('l7_b3_spot_desc', 'For farms, homes and workshops. Rugged leaf-spring forges, anvil tools & heavy machinery.');
$l7_b3_spot_img   = get_setting('l7_b3_spot_img', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80');
$l7_b3_spot_btn   = get_setting('l7_b3_spot_btn', 'Shop all Pump & Electrical');
$l7_b3_spot_link  = get_setting('l7_b3_spot_link', '/products.php?category=rural-utility-machinery');

// Department Menu Items
$l7_departments = [
    ['icon' => 'fa-solid fa-wheat-awn', 'color' => 'text-emerald-700', 'name' => 'Farming & Agriculture', 'hi' => 'कृषि एवं खेती उपकरण', 'link' => '/products.php?category=farming-traditional-tools'],
    ['icon' => 'fa-solid fa-utensils', 'color' => 'text-amber-700', 'name' => 'Food & HoReCa', 'hi' => 'खाद्य प्रसंस्करण व दालें', 'link' => '/products.php?category=pulses-organic-grains'],
    ['icon' => 'fa-solid fa-bolt', 'color' => 'text-blue-600', 'name' => 'Pumps and Electrical Component', 'hi' => 'पंप व मोटर उपकरण', 'link' => '/products.php?category=rural-utility-machinery'],
    ['icon' => 'fa-solid fa-industry', 'color' => 'text-red-700', 'name' => 'Industrials & MRO', 'hi' => 'वर्कशॉप व औजार', 'link' => '/products.php?category=farming-traditional-tools'],
    ['icon' => 'fa-solid fa-kitchen-set', 'color' => 'text-orange-600', 'name' => 'QSR & Commercial Kitchen', 'hi' => 'किचन व रेस्टोरेंट उपकरण', 'link' => '/products.php?category=village-home-decor-toys'],
    ['icon' => 'fa-solid fa-flask-vial', 'color' => 'text-teal-600', 'name' => 'Lab & Testing Supplies', 'hi' => 'परीक्षण व सुरक्षा किट', 'link' => '/products.php'],
    ['icon' => 'fa-solid fa-screwdriver-wrench', 'color' => 'text-indigo-700', 'name' => 'Industrial Tools & Spares', 'hi' => 'स्पेयर पार्ट्स व हार्डवेयर', 'link' => '/products.php?category=rural-utility-machinery'],
    ['icon' => 'fa-solid fa-tractor', 'color' => 'text-purple-700', 'name' => 'Automotive & Agri EV', 'hi' => 'ट्रैक्टर व कृषि वाहन', 'link' => '/products.php'],
    ['icon' => 'fa-solid fa-seedling', 'color' => 'text-green-700', 'name' => 'Horticulture Tools', 'hi' => 'बागवानी व खुरपी औजार', 'link' => '/products.php?category=farming-traditional-tools'],
    ['icon' => 'fa-solid fa-box-open', 'color' => 'text-sky-700', 'name' => 'Agri- Supplies & Sacks', 'hi' => 'कृषि बोरियां व सुरक्षा जाल', 'link' => '/products.php?category=jute-bags-cloth-jholas'],
    ['icon' => 'fa-solid fa-cow', 'color' => 'text-amber-800', 'name' => 'Animal Husbandry', 'hi' => 'पशुपालन व डेयरी उत्पाद', 'link' => '/products.php'],
    ['icon' => 'fa-solid fa-faucet-drip', 'color' => 'text-cyan-700', 'name' => 'Plumbing & Irrigation', 'hi' => 'पाइप व जल सिंचाई', 'link' => '/products.php?category=rural-utility-machinery']
];
?>

<div class="l7-wrap bg-[#f8fafc] text-slate-800 font-sans pb-16">

  <!-- ==================== 0. TOP DISPATCH & LOCATION BAR ==================== -->
  <div class="bg-[#0b291e] text-white text-xs border-b border-emerald-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-emerald-300 font-medium">
          <span class="lang-en">Direct Dispatch from Hardoi Industrial Clusters:</span>
          <span class="lang-hi hidden">हरदोई क्लस्टर से सीधा प्रेषण:</span>
        </span>
        <strong class="text-white"><?= l7_e($delivery_location_text ?? 'Pan-India 24-48h Delivery') ?></strong>
      </div>
      <div class="flex items-center gap-4 text-emerald-200 text-[11px]">
        <a href="/retailer-register.php" class="hover:text-amber-300 transition font-bold flex items-center gap-1">
          <i class="fa-solid fa-store text-amber-400"></i>
          <span class="lang-en">Become a B2B Retailer</span>
          <span class="lang-hi hidden">थोक विक्रेता बनें</span>
        </a>
        <span>•</span>
        <a href="/track-order.php" class="hover:text-amber-300 transition">
          <span class="lang-en">Track Order</span>
          <span class="lang-hi hidden">ऑर्डर ट्रैक करें</span>
        </a>
        <span>•</span>
        <a href="mailto:support@ganvgalihat.com" class="hover:text-amber-300 transition">support@ganvgalihat.com</a>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">

    <!-- ==================== 1. HERO 3-COLUMN GRID (SCREENSHOT 1) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch mb-6">

      <!-- Column 1: Persistent Department Sidebar (24% desktop) -->
      <aside class="hidden lg:block lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
        <div class="p-3.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
          <span class="font-black text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-bars-staggered text-emerald-700"></i>
            <span class="lang-en">All Categories</span>
            <span class="lang-hi hidden">सभी श्रेणियां</span>
          </span>
          <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Explore</span>
        </div>
        <nav class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[460px]" aria-label="Departments">
          <?php foreach ($l7_departments as $dept): ?>
            <a href="<?= l7_e($dept['link']) ?>" class="group px-3.5 py-2.5 flex items-center justify-between text-xs font-bold text-slate-700 hover:text-emerald-700 hover:bg-emerald-50/50 transition">
              <span class="flex items-center gap-2.5 line-clamp-1">
                <i class="<?= $dept['icon'] ?> <?= $dept['color'] ?> text-sm w-4 text-center"></i>
                <span class="lang-en"><?= l7_e($dept['name']) ?></span>
                <span class="lang-hi hidden"><?= l7_e($dept['hi']) ?></span>
              </span>
              <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
          <?php endforeach; ?>
        </nav>
        <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
          <a href="/categories.php" class="text-[11px] font-extrabold text-emerald-700 hover:underline">
            <span class="lang-en">View Complete Directory &rarr;</span>
            <span class="lang-hi hidden">पूरी निर्देशिका देखें &rarr;</span>
          </a>
        </div>
      </aside>

      <!-- Column 2: Center Big Hero Banner (52% desktop) -->
      <div class="lg:col-span-6 bg-gradient-to-br from-emerald-950 via-[#0a3826] to-[#042016] rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden flex flex-col justify-between shadow-md border border-emerald-900/50 min-h-[440px]">
        
        <!-- High-Contrast Background Machinery Backdrop -->
        <div class="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-luminosity pointer-events-none" style="background-image: url('<?= l7_e($l7_hero_bg_img) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-950/70 to-transparent pointer-events-none"></div>

        <!-- Top Pill Badge -->
        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-amber-300 text-xs font-black tracking-wide uppercase shadow-xs mb-4">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span><?= l7_e($l7_hero_badge) ?></span>
          </div>

          <h1 class="text-2xl sm:text-4xl font-black text-white leading-tight tracking-tight mb-3">
            <?= l7_e($l7_hero_title) ?>
          </h1>
          <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed max-w-xl mb-4 font-medium">
            <?= l7_e($l7_hero_subtitle) ?>
          </p>

          <!-- Floating Machinery Tags (Matching Screenshot 1) -->
          <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ([$l7_hero_tag1, $l7_hero_tag2, $l7_hero_tag3, $l7_hero_tag4, $l7_hero_tag5] as $tag): if (!empty($tag)): ?>
              <span class="px-3 py-1 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-[11px] font-bold text-emerald-200 backdrop-blur-xs transition cursor-default">
                <?= l7_e($tag) ?>
              </span>
            <?php endif; endforeach; ?>
          </div>
        </div>

        <!-- Hero Bottom Action & Trust Stats -->
        <div class="relative z-10 pt-4 border-t border-emerald-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <a href="<?= l7_e($l7_hero_btn_link) ?>" class="px-8 sm:px-10 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition inline-flex items-center justify-center gap-2.5 whitespace-nowrap min-h-[50px] box-border active:scale-95 cursor-pointer">
            <span><?= l7_e($l7_hero_btn_text) ?></span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </a>

          <!-- Live Trust Numbers -->
          <div class="flex items-center gap-4 text-xs font-bold text-emerald-200/90">
            <div>
              <div class="text-white font-black text-sm"><?= l7_e($l7_hero_stat1) ?></div>
              <div class="text-[10px] text-emerald-300">Catalog</div>
            </div>
            <span class="text-emerald-700">•</span>
            <div>
              <div class="text-white font-black text-sm"><?= l7_e($l7_hero_stat2) ?></div>
              <div class="text-[10px] text-emerald-300">Direct Users</div>
            </div>
            <span class="text-emerald-700">•</span>
            <div>
              <div class="text-white font-black text-sm"><?= l7_e($l7_hero_stat3) ?></div>
              <div class="text-[10px] text-emerald-300">Artisan Hubs</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Column 3: Stacked Right Cards (24% desktop - Matching Screenshot 1) -->
      <div class="lg:col-span-3 flex flex-col gap-4">

        <!-- Card 1: Trusted by 10,000+ Farmers (Google 4.9 Rating) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex-1 flex flex-col justify-between hover:border-emerald-300 transition">
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 font-extrabold text-[10px] rounded-md uppercase">Google Certified</span>
              <div class="flex items-center text-amber-500 text-xs">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
                <span class="ml-1 font-bold text-slate-800 text-[11px]"><?= l7_e($l7_card1_rating) ?></span>
              </div>
            </div>
            <h3 class="font-black text-sm text-slate-900 leading-snug mb-1.5">
              <?= l7_e($l7_card1_title) ?>
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
              <?= l7_e($l7_card1_sub) ?>
            </p>
          </div>
          <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] font-bold text-emerald-700 flex items-center gap-1">
              <i class="fa-solid fa-circle-check text-emerald-600"></i>
              <span>100% Genuine Forges</span>
            </span>
            <a href="/artisans.php" class="text-xs font-extrabold text-slate-700 hover:text-emerald-700 hover:underline">
              <span>Verify &rarr;</span>
            </a>
          </div>
        </div>

        <!-- Card 2: 3-Day Easy Returns -->
        <div class="bg-gradient-to-br from-amber-50/80 to-amber-100/50 rounded-2xl p-5 border border-amber-200 shadow-xs flex-1 flex flex-col justify-between hover:border-amber-400 transition">
          <div>
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-lg mb-3 shadow-xs">
              <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="font-black text-sm text-amber-950 leading-snug mb-1.5">
              <?= l7_e($l7_card2_title) ?>
            </h3>
            <p class="text-xs text-amber-900/80 leading-relaxed">
              <?= l7_e($l7_card2_sub) ?>
            </p>
          </div>
          <div class="pt-3 border-t border-amber-200/60">
            <a href="<?= l7_e($l7_card2_link) ?>" class="text-xs font-black text-amber-950 hover:underline flex items-center justify-between">
              <span>Read Return Guarantee</span>
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
          </div>
        </div>

      </div>

    </div><!-- /Hero Grid -->

    <!-- ==================== 2. FIVE-POINT VALUE PROPOSITION STRIP (SCREENSHOT 1) ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 mb-8">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
        
        <!-- 1. Free Shipping -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-3">
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-truck-fast"></i>
          </div>
          <div>
            <h4 class="font-black text-xs text-slate-900 leading-tight"><?= l7_e($l7_trust1_t) ?></h4>
            <p class="text-[11px] text-slate-500"><?= l7_e($l7_trust1_s) ?></p>
          </div>
        </div>

        <!-- 2. 3-Day Returns -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-3">
          <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-rotate-left"></i>
          </div>
          <div>
            <h4 class="font-black text-xs text-slate-900 leading-tight"><?= l7_e($l7_trust2_t) ?></h4>
            <p class="text-[11px] text-slate-500"><?= l7_e($l7_trust2_s) ?></p>
          </div>
        </div>

        <!-- 3. 100% Genuine -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-3">
          <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <h4 class="font-black text-xs text-slate-900 leading-tight"><?= l7_e($l7_trust3_t) ?></h4>
            <p class="text-[11px] text-slate-500"><?= l7_e($l7_trust3_s) ?></p>
          </div>
        </div>

        <!-- 4. GST Invoice -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-3">
          <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </div>
          <div>
            <h4 class="font-black text-xs text-slate-900 leading-tight"><?= l7_e($l7_trust4_t) ?></h4>
            <p class="text-[11px] text-slate-500"><?= l7_e($l7_trust4_s) ?></p>
          </div>
        </div>

        <!-- 5. Secure Payment -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-3">
          <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-lock"></i>
          </div>
          <div>
            <h4 class="font-black text-xs text-slate-900 leading-tight"><?= l7_e($l7_trust5_t) ?></h4>
            <p class="text-[11px] text-slate-500"><?= l7_e($l7_trust5_s) ?></p>
          </div>
        </div>

      </div>
    </div>

    <!-- ==================== 3. SHOP BY CATEGORY CAROUSEL (SCREENSHOT 1 BOTTOM) ==================== -->
    <div class="mb-10">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900">
            <span class="lang-en">Shop by Category</span>
            <span class="lang-hi hidden">श्रेणी अनुसार खरीदारी करें</span>
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">Explore authentic tools, pulses, pickles & regional crafts</p>
        </div>
        <a href="/categories.php" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-xs rounded-xl border border-slate-200 transition shadow-2xs flex items-center gap-1.5">
          <span class="lang-en">All Categories &rarr;</span>
          <span class="lang-hi hidden">सभी श्रेणियां &rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
        <?php foreach (array_slice($categories ?? [], 0, 6) as $c): ?>
          <a href="/products.php?category=<?= l7_e($c['slug']) ?>" class="group bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-emerald-300 transition text-center flex flex-col items-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2 mb-3 group-hover:scale-105 transition-transform">
              <?php if (!empty($c['image'])): ?>
                <img src="<?= l7_e($c['image']) ?>" alt="<?= l7_e($c['name']) ?>" class="w-full h-full object-contain">
              <?php else: ?>
                <i class="fa-solid fa-toolbox text-2xl text-emerald-700"></i>
              <?php endif; ?>
            </div>
            <h3 class="font-extrabold text-xs text-slate-800 group-hover:text-emerald-700 transition line-clamp-1 mb-1">
              <span class="lang-en"><?= l7_e($c['name']) ?></span>
              <?php if (!empty($c['hindi_name'])): ?><span class="lang-hi hidden"><?= l7_e($c['hindi_name']) ?></span><?php endif; ?>
            </h3>
            <span class="text-[10px] text-slate-400 font-medium">Explore &rarr;</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ==================== 4. CATEGORY BLOCK 1: FARMING & AGRICULTURE (SCREENSHOT 2) ==================== -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl sm:text-2xl font-black text-slate-900">
          <?= l7_e($l7_b1_title) ?>
        </h2>
        <a href="<?= l7_e($l7_b1_spot_link) ?>" class="text-xs font-extrabold text-slate-700 hover:text-emerald-700 border border-slate-200 bg-white px-3 py-1.5 rounded-xl transition shadow-2xs">
          <span class="lang-en">View Categories &rarr;</span>
          <span class="lang-hi hidden">श्रेणियां देखें &rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
        
        <!-- Left Feature Spotlight Card (Matching Screenshot 2 Left) -->
        <div class="lg:col-span-4 bg-gradient-to-br from-emerald-50 via-teal-50/50 to-white rounded-2xl p-6 sm:p-7 border border-emerald-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
          <div class="relative z-10">
            <span class="px-2.5 py-1 bg-emerald-600 text-white font-extrabold text-[10px] rounded-md uppercase tracking-wider">Spotlight</span>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight mt-3 mb-2">
              <?= l7_e($l7_b1_spot_title) ?>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed mb-6 font-medium">
              <?= l7_e($l7_b1_spot_desc) ?>
            </p>
          </div>
          <div class="relative z-10">
            <img src="<?= l7_e($l7_b1_spot_img) ?>" alt="<?= l7_e($l7_b1_spot_title) ?>" class="w-full h-44 object-cover rounded-xl shadow-xs mb-4">
            <a href="<?= l7_e($l7_b1_spot_link) ?>" class="w-full px-6 py-3.5 bg-emerald-800 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition inline-flex items-center justify-center gap-2 whitespace-nowrap min-h-[48px] box-border">
              <span><?= l7_e($l7_b1_spot_btn) ?></span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

        <!-- Right 3x2 Grid of 6 Soft Pastel Department Cards (Matching Screenshot 2 Right) -->
        <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-3 gap-3.5">
          
          <!-- Card 1: Horticulture Tools (Soft Mint) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-emerald-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-seedling"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Horticulture Tools</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Garden Khurpi, Pruning Shears, Trowels</p>
            </div>
            <a href="/products.php?category=farming-traditional-tools" class="mt-4 text-xs font-black text-emerald-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 2: Agri- Supplies & Nets (Soft Yellow) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-amber-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Agri- Supplies</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Farm Protection Nets, Mulch, Tarpaulins</p>
            </div>
            <a href="/products.php?category=farming-traditional-tools" class="mt-4 text-xs font-black text-amber-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 3: Agricultural Equipment & Sickles (Soft Peach) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-rose-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-wheat-awn"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Forged Sickles</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Railway Spring Steel Desi Hansiya, Axes</p>
            </div>
            <a href="/products.php?category=farming-traditional-tools" class="mt-4 text-xs font-black text-rose-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 4: Animal Husbandry & Dairy (Soft Lavender) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-purple-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-cow"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Animal Husbandry</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Chaff Cutter Blades, Dairy Ironware</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-purple-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 5: Outdoor Power Equipment (Soft Sky) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-sky-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-gear"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Power Equipment</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Knapsack Sprayers, Manual Seeders</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-sky-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 6: Solar & Farm Utility (Soft Teal) -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-teal-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-solar-panel"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Solar & Utility</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Farm Solar Lights, Fencing, Storage Bags</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-teal-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 5. CATEGORY BLOCK 2: FOOD & HORECA / VILLAGE STAPLES (SCREENSHOT 3) ==================== -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl sm:text-2xl font-black text-slate-900">
          <?= l7_e($l7_b2_title) ?>
        </h2>
        <a href="<?= l7_e($l7_b2_spot_link) ?>" class="text-xs font-extrabold text-slate-700 hover:text-amber-700 border border-slate-200 bg-white px-3 py-1.5 rounded-xl transition shadow-2xs">
          <span class="lang-en">View Categories &rarr;</span>
          <span class="lang-hi hidden">श्रेणियां देखें &rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
        
        <!-- Left 3x2 Grid of 6 Department Cards (Matching Screenshot 3 Left) -->
        <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-3 gap-3.5 order-2 lg:order-1">
          
          <!-- Card 1: Unpolished Awadh Pulses -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-amber-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-bowl-rice"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Unpolished Dals</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Pure Desi Arhar, Chana & Urad Chilka</p>
            </div>
            <a href="/products.php?category=pulses-organic-grains" class="mt-4 text-xs font-black text-amber-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 2: Packaging & Jute Sacks -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-yellow-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-yellow-50 text-yellow-800 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-bag-shopping"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Jute Sacks & Bags</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Heavy Grain Sacks, Eco Shopping Bags</p>
            </div>
            <a href="/products.php?category=jute-bags-cloth-jholas" class="mt-4 text-xs font-black text-yellow-800 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 3: Pure Mustard Oil & Ghee -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-orange-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-bottle-droplet"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Kachi Ghani Oils</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Wood-Pressed Mustard Oil, Desi Ghee</p>
            </div>
            <a href="/products.php?category=spices" class="mt-4 text-xs font-black text-orange-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 4: Traditional Pickles & Achar -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-rose-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-jar"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Banarasi Achar</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Sun-Cured Mango, Stuffed Red Chili</p>
            </div>
            <a href="/products.php?category=spices" class="mt-4 text-xs font-black text-rose-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 5: Wild Forest Murabbas -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-emerald-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-lemon"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Wild Murabbas</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Amla Murabba in Khand, Pure Honey</p>
            </div>
            <a href="/products.php?category=spices" class="mt-4 text-xs font-black text-emerald-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 6: Food Processing Tools -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-purple-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-mortar-pestle"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Processing Tools</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Brass Sevaiyan Press, Stone Chakki</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-purple-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

        </div>

        <!-- Right Feature Spotlight Card (Matching Screenshot 3 Right) -->
        <div class="lg:col-span-4 bg-gradient-to-br from-amber-50 via-orange-50/50 to-white rounded-2xl p-6 sm:p-7 border border-amber-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden order-1 lg:order-2">
          <div class="relative z-10">
            <span class="px-2.5 py-1 bg-amber-600 text-white font-extrabold text-[10px] rounded-md uppercase tracking-wider">Kitchen & HoReCa</span>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight mt-3 mb-2">
              <?= l7_e($l7_b2_spot_title) ?>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed mb-6 font-medium">
              <?= l7_e($l7_b2_spot_desc) ?>
            </p>
          </div>
          <div class="relative z-10">
            <img src="<?= l7_e($l7_b2_spot_img) ?>" alt="<?= l7_e($l7_b2_spot_title) ?>" class="w-full h-44 object-cover rounded-xl shadow-xs mb-4">
            <a href="<?= l7_e($l7_b2_spot_link) ?>" class="w-full px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl shadow-md transition inline-flex items-center justify-center gap-2 whitespace-nowrap min-h-[48px] box-border">
              <span><?= l7_e($l7_b2_spot_btn) ?></span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

      </div>
    </section>

    <!-- ==================== 6. CATEGORY BLOCK 3: PUMPS & ELECTRICAL / WORKSHOP (SCREENSHOT 4) ==================== -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl sm:text-2xl font-black text-slate-900">
          <?= l7_e($l7_b3_title) ?>
        </h2>
        <a href="<?= l7_e($l7_b3_spot_link) ?>" class="text-xs font-extrabold text-slate-700 hover:text-blue-700 border border-slate-200 bg-white px-3 py-1.5 rounded-xl transition shadow-2xs">
          <span class="lang-en">View Categories &rarr;</span>
          <span class="lang-hi hidden">श्रेणियां देखें &rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
        
        <!-- Left Feature Spotlight Card (Matching Screenshot 4 Left) -->
        <div class="lg:col-span-4 bg-gradient-to-br from-sky-50 via-blue-50/50 to-white rounded-2xl p-6 sm:p-7 border border-sky-200/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
          <div class="relative z-10">
            <span class="px-2.5 py-1 bg-sky-600 text-white font-extrabold text-[10px] rounded-md uppercase tracking-wider">Industrial & Farm</span>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight mt-3 mb-2">
              <?= l7_e($l7_b3_spot_title) ?>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed mb-6 font-medium">
              <?= l7_e($l7_b3_spot_desc) ?>
            </p>
          </div>
          <div class="relative z-10">
            <img src="<?= l7_e($l7_b3_spot_img) ?>" alt="<?= l7_e($l7_b3_spot_title) ?>" class="w-full h-44 object-cover rounded-xl shadow-xs mb-4">
            <a href="<?= l7_e($l7_b3_spot_link) ?>" class="w-full px-6 py-3.5 bg-sky-700 hover:bg-sky-600 text-white font-black text-xs rounded-xl shadow-md transition inline-flex items-center justify-center gap-2 whitespace-nowrap min-h-[48px] box-border">
              <span><?= l7_e($l7_b3_spot_btn) ?></span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

        <!-- Right 3x2 Grid of 6 Department Cards (Matching Screenshot 4 Right) -->
        <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-3 gap-3.5">
          
          <!-- Card 1: Electrical Components -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-sky-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-bolt"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Electrical Components</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Motor Starters, Switches & Panels</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-sky-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 2: Self Priming Pumps -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-blue-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-water"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Self Priming Pumps</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Domestic Water Lifting, Monoblocks</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-blue-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 3: Centrifugal Farm Pumps -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-indigo-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-fan"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Centrifugal Pumps</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">High-Discharge Agricultural Pumps</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-indigo-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 4: Plumbing & Pipe Fittings -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-slate-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-faucet"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Plumbing & Valves</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">GI Unions, Brass Foot Valves, Couplers</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-slate-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 5: Heavy Leaf-Spring Forges -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-emerald-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-hammer"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Blacksmith Forges</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">Anvils, 3kg Sledges, Leaf-Spring Steel</p>
            </div>
            <a href="/products.php?category=farming-traditional-tools" class="mt-4 text-xs font-black text-emerald-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

          <!-- Card 6: Booster & Jet Pumps -->
          <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-6 -right-6 w-20 h-20 bg-amber-100/60 rounded-full pointer-events-none"></div>
            <div>
              <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-gauge-high"></i>
              </div>
              <h4 class="font-extrabold text-sm text-slate-900 mb-1">Booster & Jet Pumps</h4>
              <p class="text-[11px] text-slate-500 line-clamp-2">High Pressure Washers & Jet Units</p>
            </div>
            <a href="/products.php?category=rural-utility-machinery" class="mt-4 text-xs font-black text-amber-700 group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              <span>Browse &rarr;</span>
            </a>
          </div>

        </div>

      </div>
    </section>

    <!-- ==================== 7. B2B WHOLESALE BANNER ==================== -->
    <div class="mb-12 bg-gradient-to-r from-slate-900 via-stone-900 to-emerald-950 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[11px] font-black uppercase tracking-wider mb-2">
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>B2B Wholesale & Bulk Supply</span>
        </div>
        <h3 class="text-xl sm:text-2xl font-black text-white leading-tight mb-2">
          Buy in Bulk for Your Agri Store, Forge Depot or Retail Business
        </h3>
        <p class="text-xs text-slate-300 leading-relaxed">
          Tiered wholesale rates from 10 to 500+ units, official GST Tax Invoices for up to 18% input credit, and direct courier dispatch across India.
        </p>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <a href="/retailer-register.php" class="px-8 sm:px-10 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition inline-flex items-center justify-center gap-2 whitespace-nowrap min-h-[50px] box-border">
          <span>Apply for Wholesale Account</span>
          <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
      </div>
    </div>

    <!-- ==================== 8. LIVE FEATURED PRODUCTS SHOWCASE ==================== -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900">
            <span class="lang-en">Featured Equipment & Best Sellers</span>
            <span class="lang-hi hidden">फ़ीचर्ड उपकरण व सर्वाधिक बिकने वाले उत्पाद</span>
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">Prioritized by popularity and verified artisan stock</p>
        </div>
        <a href="/products.php" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-xs rounded-xl border border-slate-200 transition shadow-2xs flex items-center gap-1.5">
          <span class="lang-en">View All Products &rarr;</span>
          <span class="lang-hi hidden">सभी उत्पाद देखें &rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4">
        <?php foreach (array_slice($featured_products ?? [], 0, 8) as $p): 
          $p_img = !empty($p['image']) ? $p['image'] : '/assets/images/placeholder.jpg';
          $p_price = floatval($p['price']);
          $p_orig = floatval($p['original_price'] ?? 0);
          $p_disc = intval($p['discount'] ?? 0);
        ?>
          <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition p-3.5 flex flex-col justify-between group">
            <div>
              <div class="relative w-full aspect-square rounded-xl bg-slate-50 overflow-hidden mb-3">
                <img src="<?= l7_e($p_img) ?>" alt="<?= l7_e($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <?php if ($p_disc > 0): ?>
                  <span class="absolute top-2 left-2 px-2 py-0.5 bg-rose-600 text-white font-black text-[10px] rounded-md shadow-xs">
                    <?= $p_disc ?>% OFF
                  </span>
                <?php endif; ?>
                <?php if (!empty($p['is_featured'])): ?>
                  <span class="absolute top-2 right-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[10px] rounded-md shadow-xs">
                    ★ Featured
                  </span>
                <?php endif; ?>
              </div>
              <div class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-wider mb-1 line-clamp-1">
                <?= l7_e($p['category_name'] ?? 'Artisan Hardware') ?>
              </div>
              <h3 class="font-extrabold text-xs text-slate-900 group-hover:text-emerald-700 transition line-clamp-2 mb-2 leading-snug">
                <a href="/product.php?id=<?= (int)$p['id'] ?>">
                  <span class="lang-en"><?= l7_e($p['name']) ?></span>
                  <?php if (!empty($p['hindi_name'])): ?><span class="lang-hi hidden"><?= l7_e($p['hindi_name']) ?></span><?php endif; ?>
                </a>
              </h3>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
              <div>
                <span class="text-sm font-black text-slate-900">₹<?= number_format($p_price, 2) ?></span>
                <?php if ($p_orig > $p_price): ?>
                  <span class="text-[10px] text-slate-400 line-through ml-1">₹<?= number_format($p_orig, 2) ?></span>
                <?php endif; ?>
              </div>
              <button type="button" onclick="addToCart(<?= (int)$p['id'] ?>, 1, this, event)" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-lg transition flex items-center gap-1 shrink-0 cursor-pointer">
                <i class="fa-solid fa-cart-plus text-[10px]"></i>
                <span>Add</span>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div><!-- /max-w-7xl -->

</div><!-- /l7-wrap -->
