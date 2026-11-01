<?php
/**
 * Layout 5: Artisan Split Showcase & Regional Forge Hub with Video Spotlight
 * Features:
 * - Dual-Column Split Hero (Artisan Mission & Values on Left, Responsive YouTube Video Spotlight Card on Right)
 * - Regional Forge & Artisan Hub Directory (Sandila, Mallawan, Madhoganj, Varanasi)
 * - Workshop Showcase with Direct-From-Maker Products & Haversine Distance
 * - Kalyani agrofood pvt ltd Sandila Heavy Implements Special Showcase
 * - 100% Mobile & Desktop Responsive with Zero Horizontal Overflow
 */

$yt_setting = get_setting('homepage_youtube_url', 'https://www.youtube.com/watch?v=k4m3y1eH5Ww');
$yt_embed_url = get_youtube_embed_url($yt_setting, false);
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
            🚀 Nearest Forge: <?= htmlspecialchars($nearest_vendors[0]['name']) ?> (<?= $nearest_vendors[0]['distance_km'] ?> km away)
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

<!-- 1. Split Hero Section (Artisan Story & Video Card) -->
<section class="bg-gradient-to-b from-stone-950 via-stone-900 to-stone-950 text-white py-10 sm:py-16 border-b border-stone-800 overflow-x-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      
      <!-- Left Column: Mission & Value Proposition (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold tracking-wide">
          <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <span>Regional Forge & Heritage Handicraft Guild</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
          Empowering Village Makers, <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-emerald-400">One Forge at a Time</span>
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

      <!-- Right Column: Interactive Video Spotlight Card (5 cols) -->
      <div class="lg:col-span-5">
        <div class="bg-stone-900 rounded-3xl p-3 sm:p-4 border border-stone-700 shadow-2xl relative overflow-hidden group">
          <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-inner">
            <iframe 
              src="<?= htmlspecialchars($yt_embed_url) ?>" 
              title="GanvGaliHat Artisan Craft Documentary"
              class="w-full h-full border-0" 
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
              allowfullscreen>
            </iframe>
          </div>

          <div class="p-4 bg-stone-900/90 rounded-2xl mt-3 border border-stone-800">
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest flex items-center gap-1">
                <i data-lucide="video" class="w-3 h-3"></i>
                Documentary Spotlight
              </span>
              <span class="text-[10px] text-stone-400">Sandila & Hardoi</span>
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

<!-- 2. Regional Forge & Artisan Hub Directory -->
<section class="py-12 bg-white border-b border-gray-200">
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
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
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
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
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
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
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
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-5 hover:border-amber-400 hover:shadow-md transition">
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

<!-- 3. Heavy Farm Implements by Kalyani agrofood pvt ltd Showcase -->
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

    <!-- Heavy Tools Grid -->
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

<!-- 4. Zero Middlemen Transparency Pledge -->
<section class="py-12 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-r from-emerald-900 to-stone-900 rounded-3xl p-6 sm:p-10 text-white">
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
          <a href="/products.php" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl text-center transition shadow-md">
            Order Farm-Fresh & Forged
          </a>
          <a href="/track-order.php" class="px-5 py-3 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl text-center border border-stone-700 transition">
            Track Existing India Post Parcel
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
