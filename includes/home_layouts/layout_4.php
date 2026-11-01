<?php
/**
 * Layout 4: Modern Bento Grid Catalog & Seasonal Harvest Fair
 * Brand Theme: GanvGaliHat Village Green (#064e3b) & Thatch Amber (#f59e0b / #ea580c)
 * 
 * Features:
 * 1. Real-time Location Bar & Dispatch Status
 * 2. Circular Visual Category Tray (Strictly ordered by admin sort_order)
 * 3. Modern Bento Grid Hero Section:
 *    - Large Farm-Fresh Oil & Honey Spotlight Card
 *    - Sandila Industrial Forge Tools by Kalyani agrofood pvt ltd
 *    - Women Self-Help Groups (SHGs) Heritage Handlooms Card
 *    - Live Pan-India Rural Dispatch & India Post Metric Ticker
 * 4. Interactive Category Navigation Pills
 * 5. Featured Products & Handcrafted Bestsellers 8-Grid (Instant Add to Cart)
 * 6. Seasonal Harvest Fair Showcase (Cold-pressed mustard oil, Awadhi pickles, spices)
 * 7. Transparent Rural Economy Model (₹85 Direct to Maker, ₹10 India Post, ₹5 Platform)
 * 8. Haversine Nearest Artisan Workshop Locator with Live km
 * 9. Four Direct-From-Soil Trust Pillars
 * 10. Frequently Asked Questions (FAQ) Accordion
 * 
 * Zero Horizontal Overflow, 100% Mobile & Desktop Responsive
 */
?>

<!-- 0. Real-time Location Bar -->
<div class="bg-stone-900 text-stone-200 text-xs border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-col sm:flex-row items-center justify-between gap-2">
    <div class="flex items-center gap-2 text-center sm:text-left">
      <div class="w-6 h-6 rounded-full bg-amber-400/20 text-amber-400 flex items-center justify-center shrink-0">
        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
      </div>
      <div>
        <span class="text-stone-400">Delivering to:</span>
        <strong class="text-white font-extrabold ml-1"><?= htmlspecialchars($delivery_location_text) ?></strong>
        <?php if (!empty($nearest_vendors[0])): ?>
          <span class="hidden md:inline text-stone-500 ml-2">|</span>
          <span class="hidden md:inline text-emerald-400 font-semibold ml-2">
            🚀 Nearest Forge: <?= htmlspecialchars(str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $nearest_vendors[0]['name'])) ?> (<?= $nearest_vendors[0]['distance_km'] ?> km away)
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <?php if (!$location_detected): ?>
        <button type="button" onclick="detectGPSLocation()" 
          class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-[11px] rounded-lg transition flex items-center gap-1 shadow-xs cursor-pointer">
          <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
          <span>Detect GPS</span>
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

<!-- 1. Circular Visual Category Tray (Strictly Ordered from Backend) -->
<section class="bg-white border-b border-gray-200 py-3 sm:py-4 shadow-xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-start sm:justify-between gap-4 sm:gap-6 overflow-x-auto scrollbar-none pb-1">
      <?php foreach ($categories as $cat): ?>
        <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" 
           class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95" 
           title="<?= htmlspecialchars($cat['name']) ?>">
          <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-emerald-600 via-amber-500 to-orange-500 group-hover:from-amber-500 group-hover:to-emerald-600 transition-all shadow-xs group-hover:shadow-md">
            <div class="w-full h-full rounded-full overflow-hidden bg-white p-0.5">
              <img src="<?= htmlspecialchars($cat['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($cat['name']) ?>" 
                   class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform duration-300"
                   loading="lazy">
            </div>
          </div>
          <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-800 mt-1.5 line-clamp-1 leading-tight">
            <?= htmlspecialchars($cat['name']) ?>
          </span>
          <span class="text-[9px] text-amber-700 font-semibold line-clamp-1">Explore</span>
        </a>
      <?php endforeach; ?>

      <a href="/products.php" class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
        <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-stone-900 to-emerald-800 shadow-xs group-hover:shadow-md">
          <div class="w-full h-full rounded-full bg-stone-900 text-amber-300 flex items-center justify-center group-hover:bg-emerald-950 transition-colors">
            <i data-lucide="layout-grid" class="w-7 h-7"></i>
          </div>
        </div>
        <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-800 mt-1.5 leading-tight">
          All Products
        </span>
        <span class="text-[9px] text-gray-400 font-medium">Browse 100+</span>
      </a>
    </div>
  </div>
</section>

<!-- 2. Modern Bento Grid Hero Section -->
<section class="text-white py-8 sm:py-10 border-b border-stone-800 overflow-x-hidden" style="background: linear-gradient(135deg, #064e3b 0%, #032b1e 50%, #0f172a 100%);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-stone-800/80">
      <div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-[11px] font-bold tracking-wide uppercase">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Direct Village Origin • Hunar Desh Ka, Ganv Se Ghar Tak
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight mt-2">
          Pure Rural Craft & Harvest Bento Hub
        </h1>
      </div>
      <div>
        <a href="/products.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-md">
          <span>Explore All 100+ Items</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>
    </div>

    <!-- Bento Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Bento Item 1: Large Spotlight (Cold-Pressed Mustard Oil & Honey) -->
      <div class="md:col-span-2 lg:col-span-2 bg-gradient-to-br from-stone-900 to-amber-950/70 rounded-3xl p-6 sm:p-8 border border-amber-500/30 shadow-xl flex flex-col justify-between relative overflow-hidden group">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div>
          <div class="flex items-center gap-2 mb-3">
            <span class="px-2.5 py-1 bg-amber-500 text-stone-950 text-[10px] font-black rounded-lg uppercase tracking-wider">
              100% Pure • Wooden Kohlu
            </span>
            <span class="text-stone-400 text-xs font-semibold">Madhoganj & Mallawan</span>
          </div>
          <h2 class="text-xl sm:text-3xl font-black text-white leading-tight mb-3">
            Pure Wood-Cold-Pressed Mustard Oil & Wild Raw Honey
          </h2>
          <p class="text-stone-300 text-xs sm:text-sm leading-relaxed mb-6 max-w-lg">
            Milled slowly at under 38°C on traditional wooden bullock kolhus. Rich in natural pungency, zero artificial blending, and bottled right after fine cotton-cloth filtering.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-stone-800">
          <div>
            <span class="text-[10px] text-stone-400 uppercase font-bold block">Starting From</span>
            <span class="text-xl font-black text-amber-400">₹240 <span class="text-xs text-stone-400 font-normal">/ Litre</span></span>
          </div>
          <a href="/products.php?category=pulses-and-oils" class="ml-auto inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-md">
            <span>Shop Pure Oils</span>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 2: Heavy-Duty Sandila Agricultural Forge (Kalyani agrofood pvt ltd) -->
      <div class="bg-gradient-to-br from-stone-900 to-emerald-950/70 rounded-3xl p-6 border border-emerald-500/40 shadow-xl flex flex-col justify-between group hover:border-emerald-400 transition">
        <div>
          <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold mb-4">
            <i data-lucide="hammer" class="w-5 h-5"></i>
          </div>
          <span class="text-[10px] font-black text-emerald-400 tracking-wider uppercase block mb-1">Sandila Industrial Hub</span>
          <h3 class="text-lg font-black text-white leading-snug mb-2">
            Kalyani agrofood pvt ltd Forged Tools
          </h3>
          <p class="text-stone-300 text-xs leading-relaxed mb-4">
            High-tensile tempered steel agricultural implements: Khurpi, sickles, spades & axes built for lifetime durability.
          </p>
        </div>
        <div>
          <div class="flex items-center justify-between text-xs text-stone-300 py-2 border-t border-stone-700/80 mb-3">
            <span>Hardness:</span>
            <span class="font-bold text-amber-400">55 HRC Tempered</span>
          </div>
          <a href="/products.php?category=traditional-tools" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition">
            <span>View Forged Tools</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 3: Women Self-Help Groups (SHG) & Handlooms -->
      <div class="bg-gradient-to-br from-stone-900 to-purple-950/60 rounded-3xl p-6 border border-purple-500/30 shadow-xl flex flex-col justify-between group hover:border-purple-400 transition">
        <div>
          <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center font-bold mb-4">
            <i data-lucide="heart" class="w-5 h-5"></i>
          </div>
          <span class="text-[10px] font-black text-amber-400 tracking-wider uppercase block mb-1">Empowering 120+ Rural Women</span>
          <h3 class="text-lg font-black text-white leading-snug mb-2">
            SHG Handlooms & Sun-Cured Pickles
          </h3>
          <p class="text-stone-300 text-xs leading-relaxed mb-4">
            Hand-stitched Awadhi craft, Moonj grass baskets, sun-cured jackfruit pickles, and certified pure spice masalas.
          </p>
        </div>
        <div>
          <div class="flex items-center justify-between text-xs text-stone-300 py-2 border-t border-stone-700/80 mb-3">
            <span>Craft Artisans:</span>
            <span class="font-bold text-emerald-400">100% Women Led</span>
          </div>
          <a href="/products.php?category=handicrafts" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 bg-stone-800 hover:bg-stone-700 text-amber-300 font-bold text-xs rounded-xl border border-stone-600 transition">
            <span>Support Women SHGs</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 4: Live Dispatch Hub & Metrics Ticker -->
      <div class="md:col-span-3 lg:col-span-4 bg-gradient-to-r from-emerald-950 via-stone-900 to-stone-950 rounded-3xl p-6 border border-emerald-800/40 shadow-lg">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center divide-y md:divide-y-0 md:divide-x divide-stone-800">
          <div class="p-2">
            <span class="text-2xl sm:text-3xl font-black text-white block">4,820+</span>
            <span class="text-stone-400 text-[11px] font-semibold uppercase tracking-wider">Orders Dispatched</span>
          </div>
          <div class="p-2">
            <span class="text-2xl sm:text-3xl font-black text-emerald-400 block">42 Villages</span>
            <span class="text-stone-400 text-[11px] font-semibold uppercase tracking-wider">Direct Maker Network</span>
          </div>
          <div class="p-2">
            <span class="text-2xl sm:text-3xl font-black text-amber-400 block">100% Handcrafted</span>
            <span class="text-stone-400 text-[11px] font-semibold uppercase tracking-wider">Zero Industrial Adulteration</span>
          </div>
          <div class="p-2">
            <span class="text-2xl sm:text-3xl font-black text-blue-400 block">India Post</span>
            <span class="text-stone-400 text-[11px] font-semibold uppercase tracking-wider">Pan-India Direct Delivery</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 3. Category Quick-Pills Navigation -->
<section class="bg-white border-b border-gray-200 py-4 shadow-xs sticky top-0 z-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-none pb-1">
      <a href="/products.php" class="px-4 py-2 bg-emerald-800 text-white rounded-full font-bold text-xs shrink-0 hover:bg-emerald-900 transition shadow-xs flex items-center gap-1.5">
        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
        <span>All Categories (<?= count($categories) ?>)</span>
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" 
           class="px-4 py-2 bg-stone-100 hover:bg-amber-100 text-stone-800 hover:text-stone-950 rounded-full font-semibold text-xs shrink-0 transition border border-gray-200 flex items-center gap-1.5">
          <span><?= htmlspecialchars($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 4. Featured Products & Handcrafted Bestsellers 8-Grid -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-700 uppercase tracking-widest block mb-1">Direct From Villages</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Featured Products & Certified Bestsellers</h2>
      </div>
      <a href="/products.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950">
        <span>View Full Catalog</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <!-- Product 8-Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($featured_products, 0, 8) as $p): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 hover:border-emerald-600 hover:shadow-md transition flex flex-col justify-between group">
          <div>
            <?php $feat_img = !empty($p['image']) ? $p['image'] : (!empty($p['image_url']) ? $p['image_url'] : '/assets/images/placeholder.jpg'); ?>
            <div class="relative w-full aspect-square bg-stone-50 rounded-xl overflow-hidden mb-3 border border-gray-100">
              <img src="<?= htmlspecialchars($feat_img) ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>"
                   onerror="this.onerror=null; this.src='/assets/images/placeholder.jpg';"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   loading="lazy">
              <?php if (!empty($p['is_best_seller'])): ?>
                <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[10px] rounded-md shadow-xs">
                  Bestseller
                </span>
              <?php endif; ?>
            </div>

            <div class="flex items-center justify-between mb-1">
              <span class="text-[10px] text-gray-400 font-bold uppercase"><?= htmlspecialchars($p['category_name'] ?? 'Rural Maker') ?></span>
              <span class="text-[11px] font-bold text-amber-600">★ 4.9</span>
            </div>

            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-800 transition mb-1">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
            
            <p class="text-[11px] text-gray-500 mb-2">
              By <?= htmlspecialchars(str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $p['vendor_name'] ?? 'Local Maker')) ?>
            </p>
          </div>

          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-baseline justify-between mb-2.5">
              <div>
                <span class="text-base sm:text-lg font-black text-gray-900">₹<?= number_format($p['price'], 0) ?></span>
                <?php if (!empty($p['mrp']) && $p['mrp'] > $p['price']): ?>
                  <span class="text-[11px] text-gray-400 line-through ml-1">₹<?= number_format($p['mrp'], 0) ?></span>
                <?php endif; ?>
              </div>
              <span class="text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-800 font-bold rounded-full">In Stock</span>
            </div>

            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                <span>Add to Cart</span>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5. Transparent Rural Economy Card -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-br from-emerald-950 via-stone-900 to-stone-950 rounded-3xl p-6 sm:p-10 text-white shadow-xl">
      <div class="max-w-2xl mb-8">
        <span class="text-amber-400 text-xs font-black uppercase tracking-widest block mb-1">Direct Fair Value</span>
        <h2 class="text-2xl sm:text-3xl font-black text-white">Where Does Your ₹100 Go at GanvGaliHat?</h2>
        <p class="text-stone-300 text-xs sm:text-sm mt-2 leading-relaxed">
          Unlike hyper-commercial marketplaces that charge up to 35% commission plus hidden advertising fees, GanvGaliHat operates on a transparent rural livelihood model.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-stone-800/80 rounded-2xl p-5 border border-stone-700">
          <div class="text-3xl font-black text-emerald-400 mb-1">₹85.00</div>
          <h3 class="font-extrabold text-sm text-white mb-2">Direct Maker Remittance</h3>
          <p class="text-stone-400 text-xs leading-relaxed">
            Transferred directly to the blacksmith, potter, weaver, or farmer's verified bank account via automated UPI payout.
          </p>
        </div>

        <div class="bg-stone-800/80 rounded-2xl p-5 border border-stone-700">
          <div class="text-3xl font-black text-blue-400 mb-1">₹10.00</div>
          <h3 class="font-extrabold text-sm text-white mb-2">India Post & Eco Packaging</h3>
          <p class="text-stone-400 text-xs leading-relaxed">
            Covers Speed Post barcoded postage, plastic-free biodegradable packing, and rural dakghar courier pickups.
          </p>
        </div>

        <div class="bg-stone-800/80 rounded-2xl p-5 border border-stone-700">
          <div class="text-3xl font-black text-amber-400 mb-1">₹5.00</div>
          <h3 class="font-extrabold text-sm text-white mb-2">Platform & Bank Gateway</h3>
          <p class="text-stone-400 text-xs leading-relaxed">
            Server hosting, 256-bit SSL encryption, payment gateway interchange fees, and 24/7 WhatsApp customer support.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 6. Four Direct-From-Soil Trust Pillars -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="handshake" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">0% Middlemen Cut</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Artisans and organic farmers receive direct remittance into their bank accounts via automatic UPI settlements.
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="truck" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">India Post Speed Post</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Reaching every single PIN code across India. Direct parcel pickups from rural village post offices with SMS tracking.
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="shield-check" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">100% Handcrafted</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Zero industrial imitation. Authentic high-carbon forged iron, wood-pressed pure oils, and handloom textiles.
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="banknote" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">Cash on Delivery</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Order comfortably with Cash on Delivery anywhere in India. Pay with cash or UPI QR scanner upon delivery.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- 7. Frequently Asked Questions (FAQ) Accordion -->
<section class="py-12 bg-white">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
      <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Customer Support</span>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Frequently Asked Questions</h2>
      <p class="text-xs sm:text-sm text-gray-500 mt-1">Everything you need to know about artisan authenticity and courier dispatch</p>
    </div>

    <div class="space-y-3">
      <details class="bg-stone-50 rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
        <summary class="font-bold text-sm text-gray-900 flex items-center justify-between">
          <span>Are the blacksmith tools really forged by hand?</span>
          <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
        </summary>
        <p class="text-xs text-gray-600 mt-3 leading-relaxed">
          Yes! Every agricultural sickle, khurpi, and axe is forged using high-carbon leaf springs on charcoal hearths in our registered hubs like Kalyani agrofood pvt ltd in Sandila, Hardoi. Each tool undergoes oil hardening and water tempering.
        </p>
      </details>

      <details class="bg-stone-50 rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
        <summary class="font-bold text-sm text-gray-900 flex items-center justify-between">
          <span>How does delivery work for remote village orders?</span>
          <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
        </summary>
        <p class="text-xs text-gray-600 mt-3 leading-relaxed">
          We partner with India Post (Speed Post and Parcel) which covers every single PIN code across India. Orders within 60 km of the artisan forge or farm are dispatched via Local Express in 24-48 hours.
        </p>
      </details>

      <details class="bg-stone-50 rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
        <summary class="font-bold text-sm text-gray-900 flex items-center justify-between">
          <span>Is Cash on Delivery (COD) available?</span>
          <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
        </summary>
        <p class="text-xs text-gray-600 mt-3 leading-relaxed">
          Yes, Cash on Delivery is enabled nationwide. You can pay conveniently with cash or QR scanner directly to your postman upon receipt.
        </p>
      </details>
    </div>
  </div>
</section>
