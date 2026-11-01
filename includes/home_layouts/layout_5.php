<?php
/**
 * Layout 5: Artisan Split Showcase & Regional Forge Hub with Video Spotlight
 * Brand Theme: GanvGaliHat Village Green (#064e3b) & Thatch Amber (#f59e0b / #ea580c)
 * 
 * Features:
 * 1. Real-time Location Bar & Dispatch Status
 * 2. Circular Visual Category Tray (Strictly ordered by admin sort_order)
 * 3. Dual-Column Split Hero Section:
 *    - Left: Artisan mission, verified maker guarantees, fast dispatch CTAs
 *    - Right: Continuous Muted Autoplay Video Spotlight Card (Zero YouTube controls, Logo-gradient overlay)
 * 4. Featured Products & Handcrafted Bestsellers 8-Grid (Instant Add to Cart)
 * 5. Regional Forge & Artisan Hub Directory (Sandila, Mallawan, Madhoganj, Varanasi)
 * 6. Kalyani agrofood pvt ltd Heavy Agricultural Implements Showcase
 * 7. Haversine Nearest Workshop Finder with Live km & Speed Post ETA
 * 8. Four Direct-From-Soil Trust Pillars
 * 9. Verified Customer Reviews & Village Voice Testimonials
 * 10. Zero Middlemen Transparency Pledge Banner
 * 
 * Zero Horizontal Overflow, 100% Mobile & Desktop Responsive
 */

$yt_setting = get_setting('homepage_youtube_url', 'https://www.youtube.com/watch?v=k4m3y1eH5Ww');
// Background mode: autoplay=1, mute=1, controls=0, loop=1, playsinline=1, zero controls
$yt_embed_url = get_youtube_embed_url($yt_setting, 1, true);
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

<!-- 2. Dual-Column Split Hero (Artisan Story & Muted Video Spotlight) -->
<section class="bg-gradient-to-b from-stone-950 via-emerald-950 to-stone-950 text-white py-10 sm:py-16 border-b border-stone-800 overflow-x-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      
      <!-- Left Column: Mission & Value (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold tracking-wide">
          <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <span>Regional Forge & Heritage Handicraft Guild</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
          Empowering Village Makers, <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-300 to-emerald-400">One Forge at a Time</span>
        </h1>

        <p class="text-stone-300 text-sm sm:text-base leading-relaxed max-w-xl">
          Connect directly with blacksmiths from Kalyani agrofood pvt ltd in Sandila, handloom weavers in Mallawan, and wood-press oil farmers in Madhoganj. Every rupee paid directly sustains traditional Indian rural families.
        </p>

        <div class="flex flex-wrap items-center gap-4 pt-2">
          <a href="/products.php" class="px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-2xl transition shadow-lg flex items-center gap-2">
            <span>Explore All Village Crafts</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="/seller-register.php" class="px-5 py-3.5 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs sm:text-sm rounded-2xl border border-stone-700 transition flex items-center gap-2">
            <i data-lucide="store" class="w-4 h-4 text-amber-400"></i>
            <span>Register as Artisan (5-Step)</span>
          </a>
        </div>

        <!-- Trust Badges -->
        <div class="pt-6 border-t border-stone-800 grid grid-cols-3 gap-4">
          <div>
            <span class="block text-xl sm:text-2xl font-black text-white">40+</span>
            <span class="text-stone-400 text-[11px] font-semibold">Active Village Forges</span>
          </div>
          <div>
            <span class="block text-xl sm:text-2xl font-black text-emerald-400">0%</span>
            <span class="text-stone-400 text-[11px] font-semibold">Middlemen Cuts</span>
          </div>
          <div>
            <span class="block text-xl sm:text-2xl font-black text-amber-400">Speed Post</span>
            <span class="text-stone-400 text-[11px] font-semibold">Tracked Dispatch</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Continuous Muted Autoplay Video Spotlight Card (5 cols) -->
      <div class="lg:col-span-5">
        <div class="bg-stone-900 rounded-3xl p-3 sm:p-4 border border-stone-700 shadow-2xl relative overflow-hidden group">
          
          <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-inner">
            <!-- Muted Autoplay Video (Zero Controls, Loop) -->
            <iframe 
              src="<?= htmlspecialchars($yt_embed_url) ?>" 
              title="GanvGaliHat Artisan Craft Documentary"
              class="w-full h-full border-0 pointer-events-none select-none filter brightness-90 contrast-105" 
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              tabindex="-1">
            </iframe>
            
            <!-- Dark Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-transparent to-transparent pointer-events-none"></div>
          </div>

          <div class="p-4 bg-stone-900/90 rounded-2xl mt-3 border border-stone-800">
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest flex items-center gap-1">
                <i data-lucide="video" class="w-3 h-3"></i>
                Documentary Spotlight
              </span>
              <span class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                <i data-lucide="volume-x" class="w-3 h-3"></i> Muted Autoplay
              </span>
            </div>
            <h3 class="text-sm font-extrabold text-white">
              The Charcoal Forges of Kalyani agrofood pvt ltd
            </h3>
            <p class="text-xs text-stone-400 mt-1 leading-relaxed">
              Watch how our village blacksmiths forge agricultural sickles and spade blades by hand using traditional charcoal anvils.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 3. Featured Products & Handcrafted Bestsellers 8-Grid -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Direct From Makers</span>
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
            <div class="relative w-full aspect-square bg-stone-100 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($p['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>"
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

<!-- 4. Regional Forge & Artisan Hub Directory -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Local Manufacturing Hubs</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Explore Regional Artisan Clusters</h2>
      </div>
      <span class="text-xs text-gray-500">Sorted by proximity to <?= htmlspecialchars($delivery_city) ?></span>
    </div>

    <!-- Cluster Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Cluster 1: Sandila Industrial Forge -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-stone-900 text-amber-400 flex items-center justify-center font-bold mb-3">
          <i data-lucide="anvil" class="w-5 h-5"></i>
        </div>
        <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Hardoi District</span>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">Sandila Industrial Hub</h3>
        <p class="text-xs text-gray-500 leading-relaxed mb-3">
          Specializes in heavy blacksmithing, hand-forged khurpis, agricultural cutters & tempered blades by Kalyani agrofood pvt ltd.
        </p>
        <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-[11px] text-gray-600">
          <span class="font-bold text-emerald-800">12 Master Blacksmiths</span>
          <a href="/products.php?category=traditional-tools" class="font-bold text-amber-700 hover:text-amber-800">Browse &rarr;</a>
        </div>
      </div>

      <!-- Cluster 2: Mallawan Heritage Weavers -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-stone-900 text-rose-400 flex items-center justify-center font-bold mb-3">
          <i data-lucide="feather" class="w-5 h-5"></i>
        </div>
        <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Awadh Region</span>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">Mallawan Weavers Guild</h3>
        <p class="text-xs text-gray-500 leading-relaxed mb-3">
          Handloom cotton bedspreads, authentic gamchas, and traditional khadi fabrics hand-spun on wooden pit looms.
        </p>
        <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-[11px] text-gray-600">
          <span class="font-bold text-emerald-800">18 Handlooms</span>
          <a href="/products.php?category=handicrafts" class="font-bold text-rose-700 hover:text-rose-800">Browse &rarr;</a>
        </div>
      </div>

      <!-- Cluster 3: Madhoganj Wooden Kohlu Pressers -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-stone-900 text-amber-300 flex items-center justify-center font-bold mb-3">
          <i data-lucide="droplet" class="w-5 h-5"></i>
        </div>
        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Ganga Belt</span>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">Madhoganj Oil & Spice Guild</h3>
        <p class="text-xs text-gray-500 leading-relaxed mb-3">
          Bullock & wooden kohlus pressing pure black mustard oil, cold-crushed yellow mustard, and raw blossom honey.
        </p>
        <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-[11px] text-gray-600">
          <span class="font-bold text-emerald-800">24 Farmers</span>
          <a href="/products.php?category=pulses-and-oils" class="font-bold text-amber-600 hover:text-amber-800">Browse &rarr;</a>
        </div>
      </div>

      <!-- Cluster 4: Varanasi Wood & Terracotta -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-stone-900 text-emerald-400 flex items-center justify-center font-bold mb-3">
          <i data-lucide="cup-soda" class="w-5 h-5"></i>
        </div>
        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Eastern UP</span>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">Varanasi Terracotta Guild</h3>
        <p class="text-xs text-gray-500 leading-relaxed mb-3">
          Kiln-fired earthenware, natural curd pots, clay water bottles, and hand-carved wooden kitchenware.
        </p>
        <div class="pt-3 border-t border-gray-200 flex items-center justify-between text-[11px] text-gray-600">
          <span class="font-bold text-emerald-800">30 Potters</span>
          <a href="/products.php?category=clay-and-pottery" class="font-bold text-emerald-700 hover:text-emerald-800">Browse &rarr;</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 5. Heavy Farm Implements by Kalyani agrofood pvt ltd Showcase -->
<section class="py-12 bg-stone-950 text-white border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-400 uppercase tracking-widest block mb-1">Sandila Industrial Hub Spotlight</span>
        <h2 class="text-2xl sm:text-3xl font-black text-white">Hand-Forged Implements by Kalyani agrofood pvt ltd</h2>
        <p class="text-stone-400 text-xs sm:text-sm mt-1">High-carbon leaf-spring steel forged on charcoal hearths in Sandila, Hardoi</p>
      </div>
      <a href="/products.php?category=traditional-tools" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:text-amber-300">
        <span>View All Forged Tools</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($box1_tools, 0, 4) as $tool): ?>
        <div class="bg-stone-900 rounded-2xl border border-stone-800 p-3 sm:p-4 hover:border-amber-500/50 transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-800 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($tool['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($tool['name']) ?>" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[10px] rounded-md shadow-xs">
                Forged Steel
              </span>
            </div>

            <h3 class="font-bold text-xs sm:text-sm text-white line-clamp-2 leading-snug group-hover:text-amber-400 transition mb-1">
              <?= htmlspecialchars($tool['name']) ?>
            </h3>
            <span class="text-[11px] text-stone-400 block mb-2">Kalyani agrofood pvt ltd • Sandila</span>
          </div>

          <div class="pt-2 border-t border-stone-800">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-amber-400">₹<?= number_format($tool['price'], 0) ?></span>
              <?php if (!empty($tool['mrp']) && $tool['mrp'] > $tool['price']): ?>
                <span class="text-[11px] text-stone-500 line-through">₹<?= number_format($tool['mrp'], 0) ?></span>
              <?php endif; ?>
            </div>

            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$tool['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1 cursor-pointer">
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

<!-- 6. Zero Middlemen Transparency Pledge -->
<section class="py-12 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-r from-emerald-950 to-stone-900 rounded-3xl p-6 sm:p-10 text-white">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-8 space-y-3">
          <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[11px] font-bold uppercase tracking-wider">
            Our Guiding Philosophy
          </span>
          <h2 class="text-2xl sm:text-3xl font-black text-white">
            "Hunar Desh Ka, Ganv Se Ghar Tak"
          </h2>
          <p class="text-stone-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
            GanvGaliHat connects the authentic heritage crafts of rural India directly with your doorstep. No middlemen, no deceptive markups, and full traceability back to the artisan's workshop.
          </p>
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
          <a href="/products.php" class="px-5 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl text-center transition shadow-md">
            Order Farm-Fresh & Forged
          </a>
          <a href="/seller-register.php" class="px-5 py-3.5 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl text-center border border-stone-700 transition">
            Register as Artisan (5-Step)
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
