<?php
/**
 * GanvGaliHat - Premium Rural Artisan Marketplace Homepage
 * Integrated Geolocation, Nearby Artisan Dispatch Engine, 
 * E-Commerce Circular Visual Category Tray & 4-Box Deal Grid
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
    if (isset($v['name'])) {
        $v['name'] = str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $v['name']);
    }
    if (isset($v['business_name'])) {
        $v['business_name'] = str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $v['business_name']);
    }
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

// 2. Fetch 4-Box Department Products (Showcase Grid)
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

// Determine Active Homepage Layout (5 Distinct Handcrafted Layouts)
// Supports admin setting and real-time live preview via ?layout=layout_X or ?theme=X
$valid_layouts = ['layout_1', 'layout_2', 'layout_3', 'layout_4', 'layout_5'];
$requested_layout = $_GET['layout'] ?? $_GET['theme'] ?? null;

if ($requested_layout) {
    if (in_array($requested_layout, ['1', '2', '3', '4', '5'])) {
        $requested_layout = 'layout_' . $requested_layout;
    }
}

if ($requested_layout && in_array($requested_layout, $valid_layouts)) {
    $active_layout = $requested_layout;
} else {
    $active_layout = get_setting('homepage_layout', 'layout_1');
    if (!in_array($active_layout, $valid_layouts)) {
        $active_layout = 'layout_1';
    }
}

$layout_file = __DIR__ . '/includes/home_layouts/' . $active_layout . '.php';
if (file_exists($layout_file)) {
    include $layout_file;
} else {
    include __DIR__ . '/includes/home_layouts/layout_1.php';
}
?>
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
