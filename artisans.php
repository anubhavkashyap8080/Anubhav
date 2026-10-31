<?php
/**
 * GaunGaliHat Nearby Artisans & Hyperlocal Workshop Directory
 * Discovers verified rural workshops, foundries, and direct-from-forge products based on visitor's location.
 */

$page_title = 'निकटतम ग्रामीण कारीगर एवं कार्यशालाएं (Nearby Artisans & Workshops)';
$page_desc = 'हरदोई एवं उत्तर प्रदेश के स्थानीय लोहारों, कुटीर कारीगरों और स्वयं सहायता समूहों से सीधे 24-48 घंटे में स्पीड पोस्ट डिलीवरी।';

require_once __DIR__ . '/includes/header.php';

// Location resolution from session (fallback to Hardoi central foundry)
$delivery_pincode = $_SESSION['shipping_pincode'] ?? '241126';
$delivery_city = $_SESSION['delivery_city'] ?? 'Hardoi';
$delivery_location_text = $_SESSION['delivery_location_text'] ?? ($delivery_city . ' (' . $delivery_pincode . ')');
$user_lat = floatval($_SESSION['user_lat'] ?? 27.1264);
$user_lng = floatval($_SESSION['user_lng'] ?? 80.1444);

// Filter by search or district if supplied
$search_artisan = clean_input($_GET['q'] ?? '');
$filter_craft = clean_input($_GET['craft'] ?? '');

// Fetch all verified artisan vendors
$all_vendors = [];
if ($pdo) {
    try {
        $vendors_stmt = $pdo->query("SELECT * FROM vendors WHERE verified = 1 ORDER BY id ASC");
        $all_vendors = $vendors_stmt->fetchAll() ?: [];
    } catch (Throwable $e) {}
}

// Calculate distance from user location for each vendor
foreach ($all_vendors as &$v) {
    if (isset($v['name'])) {
        $v['name'] = str_ireplace('S S Industries', 'Kalyani agrofood pvt ltd', $v['name']);
    }
    if (isset($v['business_name'])) {
        $v['business_name'] = str_ireplace('S S Industries', 'Kalyani agrofood pvt ltd', $v['business_name']);
    }
    $v_lat = 27.1264;
    $v_lng = 80.1444;
    if ($v['district'] === 'Hardoi') {
        if (strpos($v['name'], 'Sandila') !== false) {
            $v_lat = 27.0754; $v_lng = 80.5057;
        } elseif (strpos($v['village'] ?? '', 'Mallawan') !== false) {
            $v_lat = 27.0428; $v_lng = 80.1554;
        } elseif (strpos($v['village'] ?? '', 'Baghauli') !== false) {
            $v_lat = 27.2800; $v_lng = 80.2000;
        } else {
            $v_lat = 27.1400; $v_lng = 80.1600;
        }
    } elseif ($v['district'] === 'Varanasi') {
        $v_lat = 25.3176; $v_lng = 82.9739;
    } elseif ($v['district'] === 'Ramanagara') {
        $v_lat = 12.7209; $v_lng = 77.2799;
    } elseif ($v['district'] === 'Lucknow') {
        $v_lat = 26.8467; $v_lng = 80.9462;
    }

    $v['distance_km'] = calc_distance_km($user_lat, $user_lng, $v_lat, $v_lng);
    $v['is_local_express'] = ($v['distance_km'] <= 60);
    $v['dispatch_eta'] = ($v['distance_km'] <= 60) ? '24–48 घंटे में सीधी डिलीवरी (Local Express)' : '2–4 दिन में भारतीय डाक (Speed Post)';

    // Assign craft badge if not in DB
    $craft = 'पारंपरिक हस्तशिल्प';
    $name_lower = strtolower($v['name'] . ' ' . ($v['business_name'] ?? '') . ' ' . ($v['story'] ?? ''));
    if (strpos($name_lower, 'forge') !== false || strpos($name_lower, 'iron') !== false || strpos($name_lower, 'lohar') !== false || strpos($name_lower, 'tool') !== false) {
        $craft = 'लोहारी भट्टी एवं कृषि औजार';
    } elseif (strpos($name_lower, 'dal') !== false || strpos($name_lower, 'grain') !== false || strpos($name_lower, 'pulse') !== false) {
        $craft = 'शुद्ध देसी दालें एवं अनाज';
    } elseif (strpos($name_lower, 'pickle') !== false || strpos($name_lower, 'achar') !== false || strpos($name_lower, 'murabba') !== false) {
        $craft = 'बनारसी देसी अचार व मुरब्बा';
    } elseif (strpos($name_lower, 'women') !== false || strpos($name_lower, 'shg') !== false || strpos($name_lower, 'jute') !== false) {
        $craft = 'महिला SHG इको जूट शिल्प';
    }
    $v['craft_specialty'] = $craft;
}
unset($v);

// Filter if search query given
if (!empty($search_artisan)) {
    $q_lower = mb_strtolower($search_artisan);
    $all_vendors = array_filter($all_vendors, function($v) use ($q_lower) {
        $haystack = mb_strtolower(($v['name'] ?? '') . ' ' . ($v['business_name'] ?? '') . ' ' . ($v['village'] ?? '') . ' ' . ($v['district'] ?? '') . ' ' . ($v['craft_specialty'] ?? ''));
        return strpos($haystack, $q_lower) !== false;
    });
}

// Sort by distance ascending
usort($all_vendors, function($a, $b) {
    return ($a['distance_km'] ?? 9999) <=> ($b['distance_km'] ?? 9999);
});

// Fetch direct-from-forge products from these artisans
$nearby_products = [];
if (!empty($all_vendors) && $pdo) {
    $vendor_ids = array_slice(array_column($all_vendors, 'id'), 0, 8);
    if (!empty($vendor_ids)) {
        $in_clause = implode(',', array_map('intval', $vendor_ids));
        try {
            $stmt = $pdo->query("
                SELECT p.*, c.name as category_name, v.name as vendor_name, v.business_name, v.district as vendor_district, v.village as vendor_village
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN vendors v ON p.vendor_id = v.id
                WHERE p.vendor_id IN ($in_clause) AND p.stock > 0
                ORDER BY p.is_best_seller DESC, p.id DESC
                LIMIT 16
            ");
            $nearby_products = $stmt->fetchAll() ?: [];
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
        } catch (Throwable $e_np) {}
    }
}
?>

<!-- Breadcrumbs -->
<div class="bg-stone-100 border-b border-stone-200 py-3">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs text-gray-500 flex items-center gap-2">
    <a href="/" class="hover:text-emerald-800 font-medium">होम (Home)</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
    <span class="text-stone-900 font-bold">निकटतम ग्रामीण कारीगर (Nearby Artisans Directory)</span>
  </div>
</div>

<!-- Header Hero Banner with Location Bar -->
<section class="relative bg-gradient-to-br from-emerald-950 via-[#133827] to-stone-900 text-white py-10 sm:py-14 overflow-hidden border-b border-emerald-900/50">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-amber-500/15 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      
      <div class="space-y-3 max-w-2xl text-center md:text-left">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30">
          <i data-lucide="compass" class="w-4 h-4 text-amber-400"></i>
          <span>हाइपरलोकल कारीगर नेटवर्क • Hyperlocal Artisan Dispatch</span>
        </div>

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-display tracking-tight text-white leading-tight">
          आपके निकटतम ग्रामीण कारीगर एवं कार्यशालाएं
        </h1>

        <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
          बिचौलियों के बिना सीधे कुटीर कार्यशालाओं से स्वदेशी उत्पाद मंगाएं। सटीक दूरी एवं 24 से 48 घंटे में भारतीय डाक द्वारा सुरक्षित होम डिलीवरी।
        </p>
      </div>

      <!-- Live Location Card -->
      <div class="bg-white/10 backdrop-blur-md rounded-3xl p-5 border border-white/20 text-left shadow-xl max-w-sm w-full mx-auto md:mx-0 shrink-0 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            सक्रिय डिलीवरी स्थान
          </span>
          <button type="button" onclick="detectGPSLocation()" class="text-[10px] text-white hover:text-amber-300 underline font-bold cursor-pointer">
            री-स्कैन (Re-Scan)
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-500 text-stone-950 flex items-center justify-center shrink-0 text-lg shadow-sm">
            <i data-lucide="map-pin" class="w-5 h-5 text-stone-950"></i>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-base font-extrabold text-white truncate"><?= htmlspecialchars($delivery_city) ?></div>
            <div class="text-[11px] text-stone-300 font-mono">पिन कोड: <?= htmlspecialchars($delivery_pincode) ?></div>
          </div>
        </div>

        <div class="pt-2 border-t border-white/15 flex items-center gap-2">
          <button type="button" onclick="detectGPSLocation()" class="flex-1 py-2 px-3 bg-amber-400 hover:bg-amber-500 text-stone-950 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-95 cursor-pointer shadow-sm">
            <i data-lucide="crosshair" class="w-3.5 h-3.5 text-stone-900"></i>
            <span>GPS लोकेशन</span>
          </button>
          <button type="button" onclick="openPincodeModal()" class="flex-1 py-2 px-3 bg-white/20 hover:bg-white/30 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
            <span>पिनकोड बदलें</span>
          </button>
        </div>
      </div>

    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl p-2.5 sm:p-3 shadow-lg border border-gray-200">
      <form action="/artisans.php" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
        <div class="relative flex-1 w-full">
          <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($search_artisan) ?>"
            placeholder="कारीगर का नाम, गाँव, जिला या शिल्प खोजें (उदा. हरदोई, लोहार, दाल, जूट)..."
            class="w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:bg-white focus:outline-none focus:border-brand-green"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button type="submit" class="flex-1 sm:flex-initial px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
            <span>खोजें (Filter)</span>
          </button>

          <?php if (!empty($search_artisan)): ?>
            <a href="/artisans.php" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition">
              रीसेट
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

  </div>
</section>

<!-- Main Directory Content -->
<main class="bg-stone-50 py-10 sm:py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

    <!-- Section 1: Nearest Artisan Workshops Grid -->
    <div>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
        <div>
          <div class="inline-flex items-center gap-1 text-xs font-black text-amber-700 uppercase tracking-wider mb-1">
            <i data-lucide="store" class="w-3.5 h-3.5"></i>
            <span>सत्यापित कार्यशालाएं (Verified Village Workshops)</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display">
            निकटतम कारीगर कार्यशालाएं (<?= count($all_vendors) ?> पाए गए)
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            दूरी के अनुसार क्रमबद्ध (Sorted by distance from <?= htmlspecialchars($delivery_location_text) ?>)
          </p>
        </div>
      </div>

      <?php if (empty($all_vendors)): ?>
        <div class="bg-white rounded-3xl p-10 border border-gray-200 text-center space-y-3">
          <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl font-bold">
            <i data-lucide="map-pin-off" class="w-7 h-7"></i>
          </div>
          <h3 class="text-base font-bold text-gray-900">कोई कार्यशाला नहीं मिली</h3>
          <p class="text-xs text-gray-500 max-w-sm mx-auto">
            आपके खोज मापदंड "<?= htmlspecialchars($search_artisan) ?>" के लिए कोई कारीगर नहीं मिला। कृपया अन्य कीवर्ड या स्थान से पुनः प्रयास करें।
          </p>
          <a href="/artisans.php" class="inline-block px-5 py-2 bg-brand-green text-white text-xs font-bold rounded-xl shadow-xs">
            सभी कारीगर देखें
          </a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($all_vendors as $v): ?>
            <div class="bg-white rounded-3xl border border-gray-200 p-5 sm:p-6 shadow-xs hover:shadow-xl hover:border-emerald-600/40 transition-all duration-300 flex flex-col justify-between group relative">
              
              <!-- Top Badges -->
              <div class="space-y-4">
                <div class="flex items-start gap-4">
                  <div class="relative shrink-0">
                    <?php if (!empty($v['avatar'])): ?>
                      <img src="<?= htmlspecialchars($v['avatar']) ?>" alt="<?= htmlspecialchars($v['name']) ?>"
                        class="w-16 h-16 rounded-2xl object-cover border-2 border-amber-500 shadow-xs">
                    <?php else: ?>
                      <div class="w-16 h-16 rounded-2xl bg-amber-500 text-stone-900 flex items-center justify-center text-2xl font-bold shadow-xs">
                        <?= strtoupper(mb_substr($v['business_name'] ?? $v['name'] ?? 'A', 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] shadow" title="Verified Artisan">
                      ✓
                    </span>
                  </div>

                  <div class="min-w-0 flex-1">
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60 mb-1">
                      <?= htmlspecialchars($v['craft_specialty']) ?>
                    </span>
                    <h3 class="text-base font-extrabold text-gray-900 truncate">
                      <?= htmlspecialchars($v['business_name'] ?? $v['name']) ?>
                    </h3>
                    <p class="text-xs text-gray-500 truncate flex items-center gap-1 mt-0.5">
                      <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-orange shrink-0"></i>
                      <span><?= htmlspecialchars(($v['village'] ? $v['village'] . ', ' : '') . $v['district']) ?> (<?= htmlspecialchars($v['pincode'] ?? '241126') ?>)</span>
                    </p>
                  </div>
                </div>

                <!-- Distance & Dispatch Tag -->
                <div class="p-3 bg-stone-50 rounded-2xl border border-gray-100 flex items-center justify-between text-xs">
                  <div>
                    <span class="text-[10px] text-gray-400 font-bold block uppercase">दूरी (Distance)</span>
                    <span class="font-extrabold text-emerald-800 text-sm">
                      📍 <?= $v['distance_km'] ?> किमी
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-[10px] text-gray-400 font-bold block uppercase">वितरण समय</span>
                    <span class="text-[11px] font-bold text-gray-700">
                      🚀 <?= $v['is_local_express'] ? '24–48 घंटे' : '2–4 दिन' ?>
                    </span>
                  </div>
                </div>

                <!-- Story snippet -->
                <?php if (!empty($v['story'])): ?>
                  <p class="text-xs text-gray-600 line-clamp-2 italic leading-relaxed">
                    "<?= htmlspecialchars($v['story']) ?>"
                  </p>
                <?php endif; ?>
              </div>

              <!-- Action Buttons -->
              <div class="pt-4 border-t border-gray-100 mt-4 flex items-center gap-2">
                <a href="/products.php?q=<?= urlencode($v['name']) ?>" class="flex-1 py-2 px-3 bg-stone-900 hover:bg-black text-white text-xs font-bold rounded-xl text-center transition flex items-center justify-center gap-1.5 shadow-2xs">
                  <i data-lucide="package" class="w-3.5 h-3.5"></i>
                  <span>उत्पाद देखें</span>
                </a>

                <?php if (!empty($v['phone'])): ?>
                  <a href="tel:<?= preg_replace('/\s+/', '', $v['phone']) ?>" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl transition border border-emerald-200 text-xs font-bold flex items-center justify-center" title="Call Artisan">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                  </a>
                <?php endif; ?>

                <?php if (!empty($v['whatsapp'])): ?>
                  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $v['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl transition border border-emerald-200 text-xs font-bold flex items-center justify-center" title="WhatsApp Artisan">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i>
                  </a>
                <?php endif; ?>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Section 2: Direct-from-Forge Products from Nearby Artisans Grid -->
    <?php if (!empty($nearby_products)): ?>
      <div class="pt-6 border-t border-gray-200" id="nearby-products">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
          <div>
            <div class="inline-flex items-center gap-1 text-xs font-black text-emerald-800 uppercase tracking-wider mb-1">
              <i data-lucide="package-check" class="w-3.5 h-3.5 text-emerald-700"></i>
              <span>निकटतम कार्यशालाओं के उत्पाद (Direct From Nearby Artisans)</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display">
              सीधे भट्टी व खेत से — ताज़ा और टिकाऊ उत्पाद
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
              ये उत्पाद आपके सबसे नजदीकी कारीगरों द्वारा बनाए गए हैं — तुरंत कार्ट में जोड़ें।
            </p>
          </div>

          <a href="/products.php" class="inline-flex items-center gap-1 text-xs font-bold text-brand-green hover:underline">
            <span>पूरा कैटलॉग देखें</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
          <?php foreach ($nearby_products as $np): ?>
            <div class="bg-white rounded-3xl border border-gray-200 hover:border-emerald-600/50 hover:shadow-xl transition-all duration-300 p-3.5 sm:p-4 flex flex-col justify-between group relative overflow-hidden">
              
              <!-- Wishlist Button -->
              <button onclick="toggleWishlist(<?= $np['id'] ?>, this, event)" class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand-orange transition-colors cursor-pointer">
                <i data-lucide="heart" class="w-4 h-4"></i>
              </button>

              <a href="/product.php?id=<?= $np['id'] ?>" class="space-y-2.5 block">
                <!-- Image Container -->
                <div class="aspect-square rounded-2xl bg-stone-100 overflow-hidden relative border border-stone-100">
                  <img
                    src="<?= htmlspecialchars($np['image']) ?>"
                    alt="<?= htmlspecialchars($np['name']) ?>"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                  />
                  <?php if (!empty($np['discount']) && $np['discount'] > 0): ?>
                    <span class="absolute top-2 left-2 px-2 py-0.5 bg-brand-orange text-white text-[10px] font-black rounded-lg shadow-sm">
                      <?= $np['discount'] ?>% छूट
                    </span>
                  <?php endif; ?>
                  <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-stone-900/80 backdrop-blur-xs text-white text-[9px] font-bold rounded-md">
                    📍 <?= $np['distance_km'] ?> km
                  </span>
                </div>

                <!-- Title & Artisan info -->
                <div>
                  <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">
                    <?= htmlspecialchars($np['vendor_name'] ?? 'Village Artisan') ?>
                  </span>
                  <h4 class="font-extrabold text-xs sm:text-sm text-gray-900 line-clamp-2 group-hover:text-emerald-800 transition-colors">
                    <?= htmlspecialchars($np['name']) ?>
                  </h4>
                  <?php if (!empty($np['hindi_name'])): ?>
                    <p class="text-[11px] text-gray-500 truncate font-hindi">
                      <?= htmlspecialchars($np['hindi_name']) ?>
                    </p>
                  <?php endif; ?>
                </div>

                <!-- Price and ETA -->
                <div class="flex items-baseline gap-2 pt-1 border-t border-stone-100">
                  <span class="text-base sm:text-lg font-black text-gray-900 font-display">
                    <?= format_price($np['price']) ?>
                  </span>
                  <?php if ($np['original_price'] > $np['price']): ?>
                    <span class="text-xs text-gray-400 line-through">
                      <?= format_price($np['original_price']) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </a>

              <!-- Add to Cart Button -->
              <button
                type="button"
                onclick="addToCart(<?= $np['id'] ?>, 1, this, event)"
                class="mt-3 w-full py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer"
              >
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                <span>कार्ट में जोड़ें (Add to Cart)</span>
              </button>

            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Section 3: Artisan Cluster Hubs Showcase -->
    <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-stone-900 text-white rounded-3xl p-6 sm:p-10 border border-stone-700 shadow-xl space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-stone-700 pb-5">
        <div>
          <span class="text-amber-400 text-xs font-black uppercase tracking-wider block mb-1">
            <i class="fa-solid fa-map-location-dot"></i> प्रमुख ग्रामीण क्लस्टर (Major Artisan Clusters)
          </span>
          <h3 class="text-xl sm:text-2xl font-black text-white">
            उत्तर प्रदेश के प्रमुख कुटीर उद्योग केंद्र
          </h3>
        </div>
        <a href="/about.php" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/20 transition shrink-0">
          एस. एस. इंडस्ट्रीज के बारे में जानें →
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-stone-300">
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
          <div class="text-amber-400 font-bold text-sm flex items-center gap-2">
            <i class="fa-solid fa-hammer"></i> 1. हरदोई लोहारी क्लस्टर (Hardoi Iron Hub)
          </div>
          <p class="leading-relaxed">
            नेवादा, माधौगंज एवं संडीला के सैकड़ों पारंपरिक लोहार जो पत्ती स्प्रिंग से अटूट हंसिया, खुरपी, फावड़ा और कुल्हाड़ी बनाते हैं।
          </p>
          <span class="inline-block text-[10px] text-emerald-400 font-mono font-bold">24-48h Speed Post Dispatch</span>
        </div>

        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
          <div class="text-amber-400 font-bold text-sm flex items-center gap-2">
            <i class="fa-solid fa-wheat-awn"></i> 2. बुंदेलखंड दाल क्लस्टर (Pulse Farmers)
          </div>
          <p class="leading-relaxed">
            पत्थर चक्की से दली हुई शुद्ध बिना पॉलिश वाली अरहर दाल, देसी चना दाल और धुली उड़द। सीधे किसान समूहों द्वारा आपूर्ति।
          </p>
          <span class="inline-block text-[10px] text-emerald-400 font-mono font-bold">100% Chemical-Free Quality</span>
        </div>

        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
          <div class="text-amber-400 font-bold text-sm flex items-center gap-2">
            <i class="fa-solid fa-jar"></i> 3. बनारसी अचार व महिला SHG (Women SHG)
          </div>
          <p class="leading-relaxed">
            कच्ची घानी सरसों के तेल में धूप में पका देसी आम, लाल मिर्च और कटहल अचार, साथ ही हस्तनिर्मित पर्यावरण-अनुकूल जूट के झोले।
          </p>
          <span class="inline-block text-[10px] text-emerald-400 font-mono font-bold">120+ Rural Women Empowered</span>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
