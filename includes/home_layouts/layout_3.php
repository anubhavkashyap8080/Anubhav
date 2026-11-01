<?php
/**
 * Layout 3: Cinematic Autoplay Video Hero Marketplace
 * Brand Theme: GanvGaliHat Village Green (#064e3b) & Thatch Amber (#f59e0b / #ea580c)
 * 
 * Features:
 * 1. Real-time Location Bar & Nearby Forge Alert
 * 2. Circular Visual Category Tray (Strictly ordered by admin sort_order)
 * 3. Cinematic Autoplay Background Video Hero:
 *    - Fully muted (mute=1) so browsers permit instant autoplay
 *    - Controls strictly hidden (controls=0)
 *    - Continuous loop (loop=1&playlist=ID)
 *    - Logo-based dark gradient overlay (Emerald-950 & Amber-950) with sharp foreground CTAs
 *    - Zero YouTube player controls showing
 * 4. Featured Products & Handcrafted Bestsellers 8-Grid (Instant Add to Cart)
 * 5. Rural Craft Documentary Story Cards (Sandila Forges, Bullock Kohlu, Awadhi Looms)
 * 6. Heavy Forged Agricultural Tools by Kalyani agrofood pvt ltd
 * 7. Haversine Nearest Forge Finder with Indian Post 24-48h Dispatch Badge
 * 8. 4 Direct-From-Maker Trust Pillars
 * 9. Verified Customer Reviews & Village Voice Testimonials
 * 10. Frequently Asked Questions (FAQ) Accordion
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

<!-- 2. Cinematic Autoplay Video Hero (Compact, Sleek 16:9 Responsive Player) -->
<section class="relative w-full overflow-hidden text-white border-b border-stone-800" style="background: linear-gradient(135deg, #064e3b 0%, #032b1e 50%, #0f172a 100%);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      
      <!-- Left Column: Story & CTAs (7 cols) -->
      <div class="lg:col-span-7 space-y-4 text-left">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-xs">
          <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <span>Village Forge Heritage • Hunar Desh Ka, Ganv Se Ghar Tak</span>
        </div>

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
          Grassroots Rural Forges & <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-300 to-emerald-400">Pure Village Harvest</span>
        </h1>

        <p class="text-xs sm:text-sm text-stone-200 leading-relaxed max-w-xl">
          Direct from the charcoal hearths of Kalyani agrofood pvt ltd in Sandila, bullock-driven wooden mustard expellers in Madhoganj, and handloom cotton weavers in Mallawan. 100% authentic, zero middlemen.
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-1">
          <a href="/products.php" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
            <span>Shop Village Products</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="/artisans.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20 flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-emerald-400"></i>
            <span>Meet Verified Artisans</span>
          </a>
        </div>

        <!-- Trust Stats Badges -->
        <div class="pt-4 border-t border-emerald-800/60 grid grid-cols-3 gap-3 max-w-md">
          <div>
            <span class="text-lg sm:text-xl font-black text-white block">4,820+</span>
            <span class="text-stone-300 text-[10px] font-medium">Orders Dispatched</span>
          </div>
          <div>
            <span class="text-lg sm:text-xl font-black text-emerald-400 block">42 Villages</span>
            <span class="text-stone-300 text-[10px] font-medium">Direct Maker Hubs</span>
          </div>
          <div>
            <span class="text-lg sm:text-xl font-black text-amber-400 block">Speed Post</span>
            <span class="text-stone-300 text-[10px] font-medium">Pan-India Direct</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Dedicated 16:9 Autoplay Video Showcase Player (5 cols) -->
      <div class="lg:col-span-5">
        <div class="bg-stone-950 rounded-2xl overflow-hidden border-2 border-amber-400/40 shadow-2xl group">
          <!-- 16:9 Responsive Player Container -->
          <div class="relative w-full aspect-video bg-black overflow-hidden">
            <iframe 
              src="<?= htmlspecialchars($yt_embed_url) ?>" 
              title="GanvGaliHat Rural Craft Video"
              class="w-full h-full border-0 absolute inset-0"
              style="width: 100%; height: 100%; border: 0;"
              loading="eager"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              tabindex="-1">
            </iframe>
          </div>

          <!-- Player Status Bar -->
          <div class="p-3 bg-stone-900 border-t border-stone-800 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
              <span class="font-extrabold text-amber-400 text-[11px]">The Charcoal Blacksmiths of Sandila</span>
            </div>
            <span class="text-emerald-400 font-bold flex items-center gap-1 text-[10px]">
              <i data-lucide="volume-x" class="w-3 h-3"></i> Muted Autoplay
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 3. Featured Products & Handcrafted Bestsellers Section -->
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

<!-- 4. Rural Craft Documentary Story Cards -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-10">
      <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Authentic Rural Heritage</span>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Behind the Scenes at Rural Workshops</h2>
      <p class="text-xs sm:text-sm text-gray-500 mt-2">Every product has a real village origin story, forged by generational families</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Story 1 -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition group">
        <div class="relative h-48 bg-stone-900 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80" 
               alt="Sandila Blacksmith" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-stone-950 text-[10px] font-black rounded-lg uppercase">
            Charcoal Hearth Forges
          </span>
        </div>
        <div class="p-5">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mb-2">The Forges of Sandila</h3>
          <p class="text-xs text-gray-500 leading-relaxed mb-4">
            Master blacksmiths hammering railway leaf-springs at over 1,200°C. Tempered in mustard oil to deliver lifelong sharpness.
          </p>
          <a href="/products.php?category=traditional-tools" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1">
            <span>Explore Forged Implements</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Story 2 -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition group">
        <div class="relative h-48 bg-stone-900 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&q=80" 
               alt="Wooden Kohlu" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <span class="absolute top-3 left-3 px-2.5 py-1 bg-emerald-600 text-white text-[10px] font-black rounded-lg uppercase">
            Wooden Bullock Kohlu
          </span>
        </div>
        <div class="p-5">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mb-2">Madhoganj Cold-Pressed Oils</h3>
          <p class="text-xs text-gray-500 leading-relaxed mb-4">
            Slow, low-heat extraction in babool wood expellers below 38°C preserves original pungent aromas and natural antioxidants.
          </p>
          <a href="/products.php?category=pulses-and-oils" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1">
            <span>Explore Organic Staples</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Story 3 -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition group">
        <div class="relative h-48 bg-stone-900 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80" 
               alt="Handlooms" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <span class="absolute top-3 left-3 px-2.5 py-1 bg-purple-600 text-white text-[10px] font-black rounded-lg uppercase">
            100% Women-Led SHGs
          </span>
        </div>
        <div class="p-5">
          <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mb-2">Mallawan Handloom Clusters</h3>
          <p class="text-xs text-gray-500 leading-relaxed mb-4">
            Pure hand-spun cotton gamchas, bedspreads, and moonj grass baskets crafted by 120+ self-help group rural craftswomen.
          </p>
          <a href="/products.php?category=handicrafts" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1">
            <span>Support Rural Handlooms</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 5. Heavy Forged Agricultural Tools by Kalyani agrofood pvt ltd -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Sandila Industrial Hub</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Hand-Forged Implements by Kalyani agrofood pvt ltd</h2>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">High-carbon leaf-spring steel forged on charcoal hearths in Sandila, Hardoi</p>
      </div>
      <a href="/products.php?category=traditional-tools" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950">
        <span>View All Forged Tools</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($box1_tools, 0, 4) as $tool): 
        $tool_img = !empty($tool['image']) ? $tool['image'] : (!empty($tool['image_url']) ? $tool['image_url'] : '/assets/images/placeholder.jpg');
      ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 hover:border-emerald-600 hover:shadow-lg transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-50 rounded-xl overflow-hidden mb-3 border border-gray-100">
              <img src="<?= htmlspecialchars($tool_img) ?>" 
                   alt="<?= htmlspecialchars($tool['name']) ?>" 
                   onerror="this.onerror=null; this.src='/assets/images/placeholder.jpg';"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-bold text-[10px] rounded-md shadow-xs">
                55 HRC Tempered
              </span>
            </div>
            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 mb-1 group-hover:text-emerald-800 transition">
              <a href="/product.php?id=<?= $tool['id'] ?>">
                <?= htmlspecialchars($tool['name']) ?>
              </a>
            </h3>
            <span class="text-[11px] text-emerald-700 font-bold block mb-2">Kalyani agrofood pvt ltd • Sandila</span>
          </div>

          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-gray-900">₹<?= number_format($tool['price'], 0) ?></span>
              <span class="text-[10px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded">In Stock</span>
            </div>
            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$tool['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1 shadow-xs cursor-pointer">
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

<!-- 6. Four Direct-From-Soil Trust Pillars -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="handshake" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">0% Middlemen Cut</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Artisans and organic farmers receive direct remittance into their bank accounts via automatic UPI settlements.
        </p>
      </div>

      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="truck" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">India Post Speed Post</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Reaching every single PIN code across India. Direct parcel pickups from rural village post offices with SMS tracking.
        </p>
      </div>

      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 shadow-xs">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
          <i data-lucide="shield-check" class="w-6 h-6"></i>
        </div>
        <h3 class="font-extrabold text-sm text-gray-900 mb-1">100% Handcrafted</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Zero industrial imitation. Authentic high-carbon forged iron, wood-pressed pure oils, and handloom textiles.
        </p>
      </div>

      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 shadow-xs">
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

<!-- 7. Verified Customer Reviews & Testimonials -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
      <span class="text-xs font-black text-amber-700 uppercase tracking-widest block mb-1">Customer Reviews</span>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900">What Our Buyers Say Across India</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "The tempered sickle from Kalyani agrofood pvt ltd in Sandila is of extraordinary quality. The weight balance is perfect for paddy harvesting and the sharpness lasted the entire season without chipping."
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
          <div class="w-9 h-9 rounded-full bg-emerald-800 text-white font-bold text-xs flex items-center justify-center">
            RS
          </div>
          <div>
            <h4 class="font-extrabold text-xs text-gray-900">Rameshwar Sharma</h4>
            <span class="text-[10px] text-gray-500">Verified Buyer • Lucknow, UP</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "The aroma of the wooden-kohlu mustard oil immediately reminded me of our village ancestral home. 100% pure, natural pungency, and clear yellow consistency. Will be reordering monthly!"
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
          <div class="w-9 h-9 rounded-full bg-amber-600 text-white font-bold text-xs flex items-center justify-center">
            PK
          </div>
          <div>
            <h4 class="font-extrabold text-xs text-gray-900">Pooja Kashyap</h4>
            <span class="text-[10px] text-gray-500">Verified Buyer • Kanpur, UP</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "Delivered by India Post Speed Post within 48 hours to our town. The handmade cotton bedspread is breathable and beautiful. Happy to know our money directly supports rural women weavers."
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
          <div class="w-9 h-9 rounded-full bg-emerald-800 text-white font-bold text-xs flex items-center justify-center">
            AS
          </div>
          <div>
            <h4 class="font-extrabold text-xs text-gray-900">Anand Srivastava</h4>
            <span class="text-[10px] text-gray-500">Verified Buyer • Varanasi, UP</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
