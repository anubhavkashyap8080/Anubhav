<?php
/**
 * GaunGaliHat - Premium Rural Artisan Marketplace Homepage
 * Integrated Geolocation, Nearby Artisan Dispatch Engine, 
 * Amazon/Flipkart Circular Visual Category Tray & 4-Box Deal Grid
 */

require_once __DIR__ . '/includes/header.php';

// 1. User Location & Artisan Haversine Distance Engine
$user_lat = $_SESSION['user_lat'] ?? 27.1264; // Default to Hardoi / Madhoganj cluster
$user_lng = $_SESSION['user_lng'] ?? 80.1444;
$delivery_pincode = $_SESSION['delivery_pincode'] ?? '241126';
$delivery_city = $_SESSION['delivery_city'] ?? 'Hardoi';
$delivery_state = $_SESSION['delivery_state'] ?? 'Uttar Pradesh';
$delivery_location_text = $_SESSION['delivery_location_text'] ?? "$delivery_city, $delivery_state - $delivery_pincode";
$location_detected = !empty($_SESSION['delivery_location_detected']);

if (!function_exists('calc_distance_km')) {
    function calc_distance_km($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Earth's mean radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth_radius * $c, 1);
    }
}

// Fetch all active categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
$categories = $stmt->fetchAll();

// Fetch verified artisan vendors and calculate distance from user
$vendors_stmt = $pdo->query("SELECT * FROM vendors WHERE verified = 1");
$all_vendors = $vendors_stmt->fetchAll();

foreach ($all_vendors as &$v) {
    // Village / District coordinates
    $v_lat = 27.1264;
    $v_lng = 80.1444;
    if ($v['district'] === 'Hardoi') {
        if (strpos($v['name'], 'Sandila') !== false) {
            $v_lat = 27.0754; $v_lng = 80.5057;
        } elseif (strpos($v['village'], 'Mallawan') !== false) {
            $v_lat = 27.0428; $v_lng = 80.1554;
        } elseif (strpos($v['village'], 'Baghauli') !== false) {
            $v_lat = 27.2800; $v_lng = 80.2000;
        } else {
            $v_lat = 27.1400; $v_lng = 80.1600;
        }
    } elseif ($v['district'] === 'Varanasi') {
        $v_lat = 25.3176; $v_lng = 82.9739;
    } elseif ($v['district'] === 'Ramanagara') {
        $v_lat = 12.7209; $v_lng = 77.2799;
    }

    $v['distance_km'] = calc_distance_km($user_lat, $user_lng, $v_lat, $v_lng);
    $v['is_local_express'] = ($v['distance_km'] <= 60);
    $v['dispatch_eta'] = ($v['distance_km'] <= 60) ? 'Local Express: 24 to 48 hrs' : 'India Post: 2 to 4 days';
}
unset($v);

// Sort vendors by distance
usort($all_vendors, function($a, $b) {
    return $a['distance_km'] <=> $b['distance_km'];
});

$nearest_vendors = array_slice($all_vendors, 0, 4);
$nearest_vendor_ids = array_column($nearest_vendors, 'id');

// Fetch products from nearest artisans
$nearby_products = [];
if (!empty($nearest_vendor_ids)) {
    $in_clause = implode(',', array_map('intval', $nearest_vendor_ids));
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, v.name as vendor_name, v.district as vendor_district, v.village as vendor_village
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN vendors v ON p.vendor_id = v.id
        WHERE p.vendor_id IN ($in_clause) AND p.stock > 0
        ORDER BY p.is_best_seller DESC, p.id DESC
        LIMIT 8
    ");
    $nearby_products = $stmt->fetchAll();
    foreach ($nearby_products as &$np) {
        foreach ($all_vendors as $v) {
            if ($v['id'] == $np['vendor_id']) {
                $np['distance_km'] = $v['distance_km'];
                $np['is_local_express'] = $v['is_local_express'];
                $np['dispatch_eta'] = $v['dispatch_eta'];
                break;
            }
        }
    }
    unset($np);
}

// 2. Fetch 4-Box Department Products (Flipkart Style 2x2 Showcase)
$box1_tools = $pdo->query("SELECT * FROM products WHERE category_id = 1 AND stock > 0 ORDER BY is_best_seller DESC, id ASC LIMIT 4")->fetchAll();
$box2_pulses = $pdo->query("SELECT * FROM products WHERE category_id = 2 AND stock > 0 ORDER BY is_best_seller DESC, id ASC LIMIT 4")->fetchAll();
$box3_pickles = $pdo->query("SELECT * FROM products WHERE category_id = 3 AND stock > 0 ORDER BY is_best_seller DESC, id ASC LIMIT 4")->fetchAll();
$box4_crafts = $pdo->query("SELECT * FROM products WHERE category_id IN (5, 6, 7) AND stock > 0 ORDER BY is_best_seller DESC, id ASC LIMIT 4")->fetchAll();

// 3. Fetch General Bestsellers & Featured Catalog
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug, v.name as vendor_name, v.location as vendor_location 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    WHERE p.stock > 0 
    ORDER BY p.is_best_seller DESC, p.id DESC 
    LIMIT 12
");
$featured_products = $stmt->fetchAll();

// Hero Customiser Settings
$hero_theme = get_setting('hero_theme_color', 'emerald');
$hero_bg_class = 'from-emerald-950 via-brand-green to-emerald-900';
$hero_badge_class = 'bg-emerald-800/80 border-emerald-600/60 text-amber-300';
$hero_accent_text = 'text-amber-300';

if ($hero_theme === 'crimson') {
    $hero_bg_class = 'from-rose-950 via-red-900 to-amber-950';
    $hero_badge_class = 'bg-red-950/80 border-amber-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
} elseif ($hero_theme === 'amber') {
    $hero_bg_class = 'from-amber-950 via-orange-900 to-amber-900';
    $hero_badge_class = 'bg-orange-950/80 border-yellow-400/60 text-yellow-300';
    $hero_accent_text = 'text-yellow-300';
} elseif ($hero_theme === 'indigo') {
    $hero_bg_class = 'from-slate-950 via-indigo-950 to-blue-950';
    $hero_badge_class = 'bg-indigo-900/80 border-indigo-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
}
?>

<!-- 0. Real-time Location Bar & Dispatch Notification (Amazon/Flipkart Style) -->
<div class="bg-stone-900 text-stone-200 text-xs border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
    
    <div class="flex items-center gap-2 text-center sm:text-left">
      <div class="w-6 h-6 rounded-full bg-amber-400/20 text-amber-400 flex items-center justify-center shrink-0">
        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
      </div>
      <div>
        <span class="text-stone-400">Delivering to:</span>
        <strong class="text-white font-extrabold ml-1"><?= htmlspecialchars($delivery_location_text) ?></strong>
        <?php if (!empty($nearest_vendors[0])): ?>
          <span class="hidden md:inline text-stone-400 ml-2">|</span>
          <span class="hidden md:inline text-emerald-400 font-semibold ml-2">
            🚀 Nearest Forge: <?= htmlspecialchars($nearest_vendors[0]['name']) ?> (<?= $nearest_vendors[0]['distance_km'] ?> km away)
          </span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Location Action Buttons -->
    <div class="flex items-center gap-2 shrink-0">
      <?php if (!$location_detected): ?>
        <button type="button" onclick="detectGPSLocation()" 
          class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-[11px] rounded-lg transition flex items-center gap-1 shadow-xs cursor-pointer">
          <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
          <span>Detect Location (GPS)</span>
        </button>
      <?php endif; ?>
      <button type="button" onclick="openPincodeModal()" 
        class="px-2.5 py-1 bg-stone-800 hover:bg-stone-700 text-stone-200 text-[11px] font-bold rounded-lg transition border border-stone-700 flex items-center gap-1 cursor-pointer">
        <span><?= $location_detected ? 'Change Location' : 'Enter Pincode' ?></span>
        <i data-lucide="chevron-down" class="w-3 h-3 text-stone-400"></i>
      </button>
    </div>

  </div>
</div>

<!-- 1. Amazon / Flipkart Style Circular Category Visual Tray -->
<section class="bg-white border-b border-gray-200 py-3 sm:py-4 shadow-xs">
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
    <div class="flex items-center justify-start sm:justify-between gap-4 sm:gap-6 overflow-x-auto scrollbar-none pb-1">
      
      <?php foreach ($categories as $cat): ?>
        <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" 
           class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95" 
           title="<?= htmlspecialchars($cat['name']) ?>">
          <!-- Circular Thumbnail with Gradient Border -->
          <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-amber-400 via-emerald-600 to-amber-300 group-hover:from-emerald-500 group-hover:to-brand-orange transition-all shadow-sm group-hover:shadow-md">
            <div class="w-full h-full rounded-full overflow-hidden bg-white p-0.5">
              <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" 
                   class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform duration-300">
            </div>
          </div>
          <!-- Title -->
          <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-700 mt-1.5 line-clamp-1 leading-tight">
            <?= htmlspecialchars($cat['name']) ?>
          </span>
          <span class="text-[9px] text-gray-400 group-hover:text-brand-orange font-medium line-clamp-1">
            <?= !empty($cat['hindi_name']) ? htmlspecialchars($cat['hindi_name']) : 'Explore' ?>
          </span>
        </a>
      <?php endforeach; ?>

      <!-- All Categories Circle Link -->
      <a href="/products.php" class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
        <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-indigo-500 to-emerald-500 shadow-sm group-hover:shadow-md">
          <div class="w-full h-full rounded-full bg-stone-900 text-amber-300 flex items-center justify-center group-hover:bg-emerald-950 transition-colors">
            <i data-lucide="layout-grid" class="w-7 h-7"></i>
          </div>
        </div>
        <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-700 mt-1.5 leading-tight">
          All Categories
        </span>
        <span class="text-[9px] text-gray-400 font-medium">Complete Catalog</span>
      </a>

    </div>
  </div>
</section>

<!-- 2. Hero Banner Section (Festival & Sale Customisable / Sandboxed Custom HTML Support) -->
<?php if (get_setting('hero_has_custom_code') === '1' && !empty(get_setting('hero_custom_html'))): ?>
  <div id="hero-custom-container" class="relative overflow-hidden w-full">
    <?php if (!empty(get_setting('hero_custom_css'))): ?>
      <style>
        <?= get_setting('hero_custom_css') ?>
      </style>
    <?php endif; ?>
    <?= get_setting('hero_custom_html') ?>
  </div>
<?php else: ?>
<section class="relative bg-gradient-to-br <?= $hero_bg_class ?> text-white overflow-hidden py-10 sm:py-16">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="absolute -top-24 -right-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-brand-orange/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      
      <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full <?= $hero_badge_class ?> border text-xs font-semibold shadow-xs">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
          <span><?= htmlspecialchars(get_setting('hero_badge_text', '🌾 S S Industries Enterprise • Empowering Rural Artisans, Dal Farmers & SHGs')) ?></span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-display tracking-tight text-white leading-tight">
          <?= htmlspecialchars(get_setting('hero_title_line1', 'Gaon ke hunar,')) ?><br />
          <span class="<?= $hero_accent_text ?> drop-shadow-sm"><?= htmlspecialchars(get_setting('hero_title_line2', 'ab har ghar tak')) ?></span>
        </h1>

        <p class="text-xs sm:text-base text-emerald-100/90 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
          <?= htmlspecialchars(get_setting('hero_subtitle', 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.')) ?>
        </p>

        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-1">
          <a href="<?= htmlspecialchars(get_setting('hero_btn1_link', '/products.php')) ?>"
             class="px-5 sm:px-6 py-3 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg hover:shadow-orange-600/30 transition-all flex items-center gap-2 cursor-pointer active:scale-95">
            <span><?= htmlspecialchars(get_setting('hero_btn1_text', 'Explore Marketplace')) ?></span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="<?= htmlspecialchars(get_setting('hero_btn2_link', '/products.php?category=farming-traditional-tools')) ?>"
             class="px-5 sm:px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs sm:text-sm rounded-xl transition-all flex items-center gap-2">
            <span><?= htmlspecialchars(get_setting('hero_btn2_text', 'Farming & Garden Tools')) ?></span>
          </a>
          <button type="button" onclick="detectGPSLocation()" class="px-5 py-3 bg-amber-400 hover:bg-amber-500 text-stone-950 font-bold text-xs sm:text-sm rounded-xl transition-all flex items-center gap-1.5 shadow-md cursor-pointer">
            <i data-lucide="crosshair" class="w-4 h-4 text-stone-900"></i>
            <span>Nearby Artisans Near Me</span>
          </button>
        </div>

        <!-- Badges -->
        <div class="grid grid-cols-3 gap-2 sm:gap-4 pt-3 border-t border-white/15 text-left max-w-md mx-auto lg:mx-0">
          <div>
            <div class="text-base sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat1_num', '100%')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat1_label', 'Forged Iron & Pure Dal')) ?></div>
          </div>
          <div>
            <div class="text-base sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat2_num', '40+')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat2_label', 'Village Artisan Hubs')) ?></div>
          </div>
          <div>
            <div class="text-base sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat3_num', '₹499+')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat3_label', 'Free India Delivery')) ?></div>
          </div>
        </div>
      </div>

      <!-- Hero Graphic Visual Showcase Card -->
      <div class="lg:col-span-5 relative mt-4 lg:mt-0 px-2 sm:px-0">
        <div class="relative mx-auto max-w-sm rounded-3xl bg-white/10 backdrop-blur-md p-3 sm:p-4 border border-white/20 shadow-2xl">
          <img 
            src="<?= htmlspecialchars(get_setting('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>" 
            alt="GaunGaliHat Artisan & Village Showcase" 
            class="w-full h-64 sm:h-76 object-cover rounded-2xl shadow-md"
            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80';"
          />

          <!-- Floating Pill 1 (Top Left) -->
          <div class="absolute -top-3 left-2 sm:-top-4 sm:-left-4 bg-white text-gray-900 rounded-xl p-2.5 sm:p-3 shadow-xl border border-gray-100 flex items-center gap-2.5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-orange-100 text-brand-orange flex items-center justify-center font-bold text-sm shrink-0">
              ⚒️
            </div>
            <div class="min-w-0">
              <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium truncate"><?= htmlspecialchars(get_setting('hero_tag1_title', 'High-Carbon Tempered Steel')) ?></p>
              <p class="text-xs font-bold text-gray-800 truncate"><?= htmlspecialchars(get_setting('hero_tag1_subtitle', 'Forged Desi Hansiya')) ?></p>
            </div>
          </div>

          <!-- Floating Pill 2 (Bottom Right) -->
          <div class="absolute -bottom-3 right-2 sm:-bottom-4 sm:-right-4 bg-brand-green text-white rounded-xl p-2.5 sm:p-3 shadow-xl border border-emerald-500 flex items-center gap-2.5 max-w-[90%] sm:max-w-none">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-emerald-800 flex items-center justify-center text-amber-300 font-bold text-xs sm:text-sm shrink-0">
              ★ 4.96
            </div>
            <div class="min-w-0">
              <p class="text-[9px] sm:text-[10px] text-emerald-200 font-medium truncate"><?= htmlspecialchars(get_setting('hero_tag2_title', 'Handcrafted Rating')) ?></p>
              <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars(get_setting('hero_tag2_subtitle', '★ 4.96 Verified')) ?></p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<?php endif; ?>

<!-- 3. Flipkart / Amazon Signature 4-Box Department Multi-Product Showcase Grid -->
<section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-8 sm:py-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-2 border-b border-gray-200 pb-3">
    <div>
      <span class="text-xs font-extrabold text-brand-green uppercase tracking-wider block">Featured Departments & Offers</span>
      <h2 class="text-xl sm:text-2xl font-black text-gray-900 font-display">
        Curated Village Collections • Up to 40% Off
      </h2>
    </div>
    <span class="text-xs text-gray-500 font-medium">Direct Settlement to Rural Makers</span>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    
    <!-- BOX 1: Traditional Farm & Garden Implements -->
    <div class="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 line-clamp-1">Farm Tools & Forges</h3>
          <span class="px-2 py-0.5 bg-orange-100 text-brand-orange text-[10px] font-black rounded-full uppercase">Min 25% Off</span>
        </div>
        <p class="text-[11px] text-gray-500 mb-3 line-clamp-1">High-carbon steel sickles, khurpi & spades</p>
        
        <!-- 2x2 Mini Product Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-4">
          <?php foreach ($box1_tools as $item): ?>
            <a href="/product.php?id=<?= $item['id'] ?>" class="group block p-2 rounded-2xl bg-stone-50 hover:bg-emerald-50 transition border border-stone-100">
              <div class="w-full h-24 rounded-xl overflow-hidden bg-white mb-1.5">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              </div>
              <div class="text-[11px] font-bold text-gray-800 truncate group-hover:text-emerald-700"><?= htmlspecialchars($item['name']) ?></div>
              <div class="text-[11px] font-extrabold text-emerald-800">₹<?= number_format($item['price'], 0) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="/products.php?category=farming-traditional-tools" class="text-xs font-bold text-brand-orange hover:text-amber-700 flex items-center justify-between pt-2 border-t border-gray-100">
        <span>See all farming implements</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>

    <!-- BOX 2: Awadh Farm Fresh Organic Pulses & Dal -->
    <div class="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 line-clamp-1">Awadh Desi Pulses</h3>
          <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase">100% Unpolished</span>
        </div>
        <p class="text-[11px] text-gray-500 mb-3 line-clamp-1">Stone-ground Arhar, Chana & Moong Dal</p>
        
        <!-- 2x2 Mini Product Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-4">
          <?php foreach ($box2_pulses as $item): ?>
            <a href="/product.php?id=<?= $item['id'] ?>" class="group block p-2 rounded-2xl bg-stone-50 hover:bg-emerald-50 transition border border-stone-100">
              <div class="w-full h-24 rounded-xl overflow-hidden bg-white mb-1.5">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              </div>
              <div class="text-[11px] font-bold text-gray-800 truncate group-hover:text-emerald-700"><?= htmlspecialchars($item['name']) ?></div>
              <div class="text-[11px] font-extrabold text-emerald-800">₹<?= number_format($item['price'], 0) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="/products.php?category=pulses-organic-grains" class="text-xs font-bold text-brand-green hover:underline flex items-center justify-between pt-2 border-t border-gray-100">
        <span>Explore pure unpolished dal</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>

    <!-- BOX 3: Banarasi Heritage Pickles & Murabba -->
    <div class="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 line-clamp-1">Banarasi Swad Achar</h3>
          <span class="px-2 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-black rounded-full uppercase">Mustard Oil</span>
        </div>
        <p class="text-[11px] text-gray-500 mb-3 line-clamp-1">Sun-cured Ramkela Aam, Lal Mirch & Amla</p>
        
        <!-- 2x2 Mini Product Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-4">
          <?php foreach ($box3_pickles as $item): ?>
            <a href="/product.php?id=<?= $item['id'] ?>" class="group block p-2 rounded-2xl bg-stone-50 hover:bg-emerald-50 transition border border-stone-100">
              <div class="w-full h-24 rounded-xl overflow-hidden bg-white mb-1.5">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              </div>
              <div class="text-[11px] font-bold text-gray-800 truncate group-hover:text-emerald-700"><?= htmlspecialchars($item['name']) ?></div>
              <div class="text-[11px] font-extrabold text-emerald-800">₹<?= number_format($item['price'], 0) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="/products.php?category=pickles-village-murabba" class="text-xs font-bold text-brand-orange hover:text-amber-700 flex items-center justify-between pt-2 border-t border-gray-100">
        <span>Taste authentic pickles</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>

    <!-- BOX 4: Village Jute, Wood Crafts & Handlooms -->
    <div class="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 line-clamp-1">Eco Crafts & Jute</h3>
          <span class="px-2 py-0.5 bg-indigo-100 text-indigo-900 text-[10px] font-black rounded-full uppercase">Cottage Guilds</span>
        </div>
        <p class="text-[11px] text-gray-500 mb-3 line-clamp-1">Natural golden jute totes, wood decor & toys</p>
        
        <!-- 2x2 Mini Product Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-4">
          <?php foreach ($box4_crafts as $item): ?>
            <a href="/product.php?id=<?= $item['id'] ?>" class="group block p-2 rounded-2xl bg-stone-50 hover:bg-emerald-50 transition border border-stone-100">
              <div class="w-full h-24 rounded-xl overflow-hidden bg-white mb-1.5">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              </div>
              <div class="text-[11px] font-bold text-gray-800 truncate group-hover:text-emerald-700"><?= htmlspecialchars($item['name']) ?></div>
              <div class="text-[11px] font-extrabold text-emerald-800">₹<?= number_format($item['price'], 0) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="/products.php?category=jute-bags-cloth-jholas" class="text-xs font-bold text-indigo-700 hover:underline flex items-center justify-between pt-2 border-t border-gray-100">
        <span>Discover eco village crafts</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>

  </div>
</section>

<!-- 4. Nearby Rural Artisans & Express 24h Dispatch Section -->
<section class="bg-stone-100/90 py-10 sm:py-14 border-y border-stone-200">
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider mb-1">
          <i data-lucide="compass" class="w-3.5 h-3.5"></i>
          <span>Hyperlocal Artisan Dispatch</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display">
          Artisans & Products Near <?= htmlspecialchars($delivery_city) ?>
        </h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-0.5">
          Order directly from workshops within transit proximity of <strong class="text-gray-900"><?= htmlspecialchars($delivery_location_text) ?></strong> for fastest 24-48h dispatch.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button type="button" onclick="detectGPSLocation()" class="px-3.5 py-2 bg-white hover:bg-stone-50 text-stone-900 rounded-xl text-xs font-bold border border-stone-300 shadow-2xs flex items-center gap-1.5 transition cursor-pointer">
          <i data-lucide="crosshair" class="w-3.5 h-3.5 text-amber-600"></i>
          <span>Re-Scan Nearby</span>
        </button>
      </div>
    </div>

    <!-- Closest Artisan Hub Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <?php foreach ($nearest_vendors as $v): ?>
        <div class="bg-white rounded-2xl p-3.5 border border-stone-200 shadow-2xs flex items-center gap-3">
          <img src="<?= htmlspecialchars($v['avatar']) ?>" alt="<?= htmlspecialchars($v['name']) ?>" class="w-11 h-11 rounded-xl object-cover border border-stone-200 shrink-0">
          <div class="min-w-0 flex-1">
            <h4 class="font-extrabold text-xs text-gray-900 truncate"><?= htmlspecialchars($v['name']) ?></h4>
            <div class="flex items-center gap-1.5 text-[10px] text-gray-500 font-medium">
              <span>📍 <?= htmlspecialchars($v['village'] ?? $v['district']) ?></span>
              <span>•</span>
              <span class="font-bold text-emerald-700"><?= $v['distance_km'] ?> km away</span>
            </div>
            <span class="text-[9px] px-2 py-0.5 rounded-md font-bold mt-1 inline-block <?= $v['is_local_express'] ? 'bg-emerald-50 text-emerald-800' : 'bg-stone-100 text-stone-600' ?>">
              🚀 <?= $v['dispatch_eta'] ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Products from Nearby Workshops -->
    <?php if (!empty($nearby_products)): ?>
      <div class="pt-4">
        <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i data-lucide="package-check" class="w-4 h-4 text-emerald-700"></i>
          <span>Direct-from-Forge Products from Nearby Artisans</span>
        </h3>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach ($nearby_products as $np): ?>
            <div class="bg-white rounded-3xl border border-gray-200 hover:border-emerald-600/50 hover:shadow-xl transition-all duration-300 p-3.5 flex flex-col justify-between group relative overflow-hidden">
              
              <!-- Wishlist Button -->
              <button onclick="toggleWishlist(<?= $np['id'] ?>, this, event)" class="absolute top-5 right-5 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand-orange transition-colors cursor-pointer">
                <i data-lucide="heart" class="w-4 h-4"></i>
              </button>

              <a href="/product.php?id=<?= $np['id'] ?>" class="space-y-2.5 block">
                <div class="relative w-full h-40 sm:h-44 rounded-2xl overflow-hidden bg-stone-100">
                  <img src="<?= htmlspecialchars($np['image']) ?>" alt="<?= htmlspecialchars($np['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  
                  <!-- Local Express Badge -->
                  <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-stone-900/90 text-amber-300 text-[10px] font-bold rounded-lg shadow-xs flex items-center gap-1 backdrop-blur-xs">
                    <i data-lucide="zap" class="w-3 h-3 text-amber-400"></i>
                    <span><?= $np['distance_km'] ?> km away</span>
                  </span>
                </div>

                <div>
                  <div class="flex items-center justify-between text-[10px] text-gray-400 font-semibold mb-0.5">
                    <span class="font-mono text-gray-600"><?= htmlspecialchars($np['code']) ?></span>
                    <span class="text-emerald-700 font-bold"><?= htmlspecialchars($np['category_name']) ?></span>
                  </div>
                  <h3 class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-green transition-colors line-clamp-1">
                    <?= htmlspecialchars($np['name']) ?>
                  </h3>
                  <p class="text-[10px] text-gray-500 truncate">By <?= htmlspecialchars($np['vendor_name']) ?></p>
                </div>
              </a>

              <!-- Price & Action (Strictly Non-Overflowing Grid) -->
              <div class="pt-2.5 border-t border-gray-100 mt-2.5 space-y-1">
                <div class="flex items-center justify-between gap-1">
                  <span class="text-sm sm:text-base font-black text-gray-900 font-display whitespace-nowrap shrink-0"><?= format_price($np['price']) ?></span>
                  <button onclick="addToCart(<?= $np['id'] ?>, 1, event)" class="px-2.5 sm:px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1 shrink-0 cursor-pointer transition-colors active:scale-95">
                    <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i>
                    <span>Add</span>
                  </button>
                </div>
                <div class="text-[10px] text-emerald-700 font-bold truncate flex items-center gap-1">
                  <span>✓</span>
                  <span class="truncate"><?= htmlspecialchars($np['dispatch_eta']) ?></span>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- 5. Spotlight Banner: Awadh Organic Pulses & Banarasi Pickles -->
<section class="bg-gradient-to-r from-amber-50 via-emerald-50 to-amber-50 border-y border-amber-200/60 py-10 sm:py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 items-center">
      
      <!-- Pulses Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80" alt="Organic Pulses" class="w-28 sm:w-32 h-28 sm:h-32 object-cover rounded-2xl shadow-md shrink-0">
        <div class="space-y-2 text-center sm:text-left">
          <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full uppercase">100% Unpolished</span>
          <h3 class="text-lg sm:text-xl font-bold font-display text-gray-900">Awadh Organic Pulses & Dal</h3>
          <p class="text-xs text-gray-600 leading-relaxed">Stone-ground Arhar/Toor dal, Chana dal, Moong, and indigenous Kala Chana directly from Mallawan farmer producer groups.</p>
          <a href="/products.php?category=pulses-organic-grains" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-green hover:underline pt-1">
            <span>Explore Desi Pulses</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Pickles Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-amber-200 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <img src="https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=400&q=80" alt="Banarasi Achar" class="w-28 sm:w-32 h-28 sm:h-32 object-cover rounded-2xl shadow-md shrink-0">
        <div class="space-y-2 text-center sm:text-left">
          <span class="px-2.5 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-bold rounded-full uppercase">Pure Mustard Oil</span>
          <h3 class="text-lg sm:text-xl font-bold font-display text-gray-900">Banarasi Swad Pickles & Murabba</h3>
          <p class="text-xs text-gray-600 leading-relaxed">Ancestral sun-cured Ramkela aam ka achar, stuffed banarasi lal mirch, kathal achar, and raw khand amla murabba by women SHGs.</p>
          <a href="/products.php?category=pickles-village-murabba" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-orange hover:underline pt-1">
            <span>Explore Village Pickles</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 6. Featured Bestsellers Grid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 space-y-8">
  <div class="flex items-center justify-between border-b border-gray-200 pb-4">
    <div>
      <span class="text-xs font-bold text-brand-green uppercase tracking-wider block">Handcrafted Excellence</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display mt-0.5">
        Bestselling Rural Implements & Produce
      </h2>
    </div>
    <span class="hidden sm:inline-flex items-center gap-1 text-xs text-gray-500 font-mono">
      <i data-lucide="truck" class="w-4 h-4 text-emerald-600"></i> Free Delivery ₹499+
    </span>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php foreach ($featured_products as $p): ?>
      <div class="bg-white rounded-3xl border border-gray-200 hover:border-emerald-600/50 hover:shadow-xl transition-all duration-300 p-4 flex flex-col justify-between group relative">
        
        <!-- Wishlist Button -->
        <button 
          onclick="toggleWishlist(<?= $p['id'] ?>, this, event)" 
          class="absolute top-6 right-6 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand-orange transition-colors cursor-pointer"
        >
          <i data-lucide="heart" class="w-4 h-4"></i>
        </button>

        <a href="/product.php?id=<?= $p['id'] ?>" class="space-y-3 block">
          <div class="relative w-full h-48 rounded-2xl overflow-hidden bg-stone-100">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <?php if ($p['discount'] > 0): ?>
              <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-brand-orange text-white text-[10px] font-bold rounded-lg shadow-xs">
                <?= $p['discount'] ?>% OFF
              </span>
            <?php endif; ?>
          </div>

          <div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 font-semibold mb-1">
              <span class="font-mono text-gray-600"><?= htmlspecialchars($p['code']) ?></span>
              <span class="text-emerald-700 font-bold"><?= htmlspecialchars($p['category_name']) ?></span>
            </div>
            <h3 class="font-bold text-sm text-gray-900 group-hover:text-brand-green transition-colors line-clamp-2">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
            <?php if (!empty($p['hindi_name'])): ?>
              <p class="text-[11px] text-gray-500 font-hindi truncate mt-0.5"><?= htmlspecialchars($p['hindi_name']) ?></p>
            <?php endif; ?>
          </div>
        </a>

        <!-- Price & Action (Strictly Non-Overflowing Grid) -->
        <div class="pt-3 border-t border-gray-100 mt-3 space-y-1">
          <div class="flex items-center justify-between gap-1">
            <div class="flex items-baseline gap-1 shrink-0 whitespace-nowrap">
              <span class="text-sm sm:text-base font-extrabold text-gray-900 font-display whitespace-nowrap shrink-0"><?= format_price($p['price']) ?></span>
              <?php if ($p['original_price'] > $p['price']): ?>
                <span class="text-[10px] sm:text-xs text-gray-400 line-through shrink-0"><?= format_price($p['original_price']) ?></span>
              <?php endif; ?>
            </div>

            <button 
              onclick="addToCart(<?= $p['id'] ?>, 1, event)" 
              class="px-2.5 sm:px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1 shrink-0 cursor-pointer transition-colors active:scale-95"
            >
              <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i>
              <span>Add</span>
            </button>
          </div>
          <span class="text-[10px] text-emerald-700 font-bold block truncate">✓ <?= htmlspecialchars($p['stock_status']) ?></span>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 7. Verified Artisan Workshops -->
<section class="bg-stone-100 py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-2">
      <span class="text-xs font-bold text-brand-green uppercase tracking-wider">Direct Rural Impact</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
        Verified Rural Workshops & Foundries
      </h2>
      <p class="text-xs sm:text-sm text-gray-600">
        Every purchase directly settles into the verified bank accounts of Indian village blacksmiths, organic farmer producer groups, and self-help women artisans.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($all_vendors as $v): ?>
        <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-sm space-y-4 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <img src="<?= htmlspecialchars($v['avatar']) ?>" alt="<?= htmlspecialchars($v['name']) ?>" class="w-12 h-12 rounded-2xl object-cover border border-emerald-200">
              <div>
                <h4 class="font-bold text-sm text-gray-900 line-clamp-1"><?= htmlspecialchars($v['name']) ?></h4>
                <span class="text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-800 font-bold rounded-full"><?= htmlspecialchars($v['type']) ?></span>
              </div>
            </div>
            <p class="text-xs text-gray-500 line-clamp-3 leading-relaxed"><?= htmlspecialchars($v['story']) ?></p>
          </div>
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
            <span>📍 <?= htmlspecialchars($v['district']) ?>, <?= htmlspecialchars($v['state']) ?></span>
            <span class="font-bold text-emerald-800">★ <?= $v['rating'] ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Automatic First-Time Geolocation Prompt Modal (Slide-in) -->
<?php if (!$location_detected): ?>
<div id="firstTimeLocationPrompt" class="fixed z-40 bottom-20 inset-x-3 sm:bottom-6 sm:left-6 sm:right-auto sm:inset-x-auto max-w-sm mx-auto sm:mx-0 w-auto bg-stone-900/95 backdrop-blur-md text-white rounded-3xl p-4 sm:p-5 shadow-2xl border border-stone-700">
  <div class="flex items-start justify-between gap-3 mb-2">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center font-bold shrink-0">
        <i data-lucide="map-pin" class="w-4 h-4"></i>
      </div>
      <div>
        <h4 class="font-black text-xs text-white">Find Nearby Artisans</h4>
        <p class="text-[10px] text-stone-400">Discover local workshops & 24h delivery</p>
      </div>
    </div>
    <button type="button" onclick="dismissFirstTimePrompt()" class="text-stone-400 hover:text-white p-1 rounded-lg" aria-label="Close">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <p class="text-[11px] text-stone-300 mb-3 leading-relaxed">
    Allow location access to show nearby rural foundries, exact India Post shipping charges, and 24-48h express dispatch.
  </p>
  <div class="flex items-center gap-2">
    <button type="button" onclick="detectGPSLocation(); dismissFirstTimePrompt();" class="flex-1 py-2 px-3 bg-amber-400 hover:bg-amber-300 text-stone-950 font-black rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
      <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
      <span>Allow GPS</span>
    </button>
    <button type="button" onclick="openPincodeModal(); dismissFirstTimePrompt();" class="py-2 px-3 bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold rounded-xl text-xs transition border border-stone-700 cursor-pointer">
      Enter PIN
    </button>
  </div>
</div>
<script>
if (typeof localStorage !== 'undefined' && localStorage.getItem('ggh_dismissed_nearby_prompt') === '1') {
  const el = document.getElementById('firstTimeLocationPrompt');
  if (el) el.style.display = 'none';
}
function dismissFirstTimePrompt() {
  const el = document.getElementById('firstTimeLocationPrompt');
  if (el) el.remove();
  try { localStorage.setItem('ggh_dismissed_nearby_prompt', '1'); } catch(e){}
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
