<?php
/**
 * Layout 4: Modern Bento Grid Catalog & Seasonal Harvest Fair
 * Features:
 * - Dynamic Bento-Style Modular Hero Section (Farm-Fresh Spotlight, SHG Crafts, Kalyani agrofood Forges, Live Dispatch Metrics)
 * - Interactive Visual Category Pills Bar
 * - Seasonal Harvest Spotlight Product Grid
 * - Transparent Rural Economy Breakdown (Direct Artisan Remittance Model)
 * - Rural Artisan Craft & Delivery FAQ Accordion
 * - 100% Mobile & Desktop Responsive with Zero Horizontal Overflow
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

<!-- 1. Modern Bento Grid Hero Section -->
<section class="bg-stone-950 text-white py-8 sm:py-12 border-b border-stone-800 overflow-x-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Top Bar Tagline -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-stone-800/80">
      <div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-[11px] font-bold tracking-wide uppercase">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Direct Village Origin • Hunar Desh Ka, Ganv Se Ghar Tak
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight mt-2">
          Pure Rural Craft & Harvest Marketplace
        </h1>
      </div>
      <div class="flex items-center gap-3">
        <a href="/products.php" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-md">
          <span>Browse All 100+ Village Items</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>
    </div>

    <!-- Bento Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Bento Item 1: Large Spotlight (Cold-Pressed Mustard Oil & Honey) -->
      <div class="md:col-span-2 lg:col-span-2 bg-gradient-to-br from-stone-900 to-amber-950/60 rounded-3xl p-6 sm:p-8 border border-amber-500/30 shadow-xl flex flex-col justify-between relative overflow-hidden group">
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
          <a href="/products.php?category=pulses-and-oils" class="ml-auto inline-flex items-center gap-2 px-4 py-2.5 bg-white text-stone-950 hover:bg-amber-100 font-black text-xs rounded-xl transition shadow-md">
            <span>Shop Pure Oils</span>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 2: Heavy-Duty Sandila Agricultural Forge (Kalyani agrofood pvt ltd) -->
      <div class="bg-gradient-to-br from-stone-900 to-stone-800 rounded-3xl p-6 border border-stone-700 shadow-xl flex flex-col justify-between group hover:border-emerald-500/50 transition">
        <div>
          <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold mb-4">
            <i data-lucide="hammer" class="w-5 h-5"></i>
          </div>
          <span class="text-[10px] font-black text-emerald-400 tracking-wider uppercase block mb-1">Sandila Industrial Hub</span>
          <h3 class="text-lg font-black text-white leading-snug mb-2">
            Kalyani agrofood pvt ltd Forged Tools
          </h3>
          <p class="text-stone-400 text-xs leading-relaxed mb-4">
            High-tensile tempered steel agricultural implements: Khurpi, sickles, spades & plough shares built to last a generation.
          </p>
        </div>
        <div>
          <div class="flex items-center justify-between text-xs text-stone-300 py-2 border-t border-stone-700/80 mb-3">
            <span>Hardness:</span>
            <span class="font-bold text-emerald-400">55 HRC Tempered</span>
          </div>
          <a href="/products.php?category=traditional-tools" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl border border-stone-600 transition">
            <span>View Forged Tools</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 3: Women Self-Help Groups (SHG) & Handlooms -->
      <div class="bg-gradient-to-br from-stone-900 to-rose-950/40 rounded-3xl p-6 border border-rose-900/40 shadow-xl flex flex-col justify-between group hover:border-rose-500/50 transition">
        <div>
          <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold mb-4">
            <i data-lucide="heart" class="w-5 h-5"></i>
          </div>
          <span class="text-[10px] font-black text-rose-400 tracking-wider uppercase block mb-1">Empowering 120+ Rural Women</span>
          <h3 class="text-lg font-black text-white leading-snug mb-2">
            SHG Handlooms & Homemade Pickles
          </h3>
          <p class="text-stone-400 text-xs leading-relaxed mb-4">
            Hand-stitched Awadhi craft, Moonj grass baskets, sun-cured jackfruit pickles, and certified pure spice masalas.
          </p>
        </div>
        <div>
          <div class="flex items-center justify-between text-xs text-stone-300 py-2 border-t border-stone-700/80 mb-3">
            <span>Craft Artisans:</span>
            <span class="font-bold text-rose-400">100% Women Led</span>
          </div>
          <a href="/products.php?category=handicrafts" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-rose-950/80 hover:bg-rose-900 text-rose-200 font-bold text-xs rounded-xl border border-rose-800/80 transition">
            <span>Support Women SHGs</span>
            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Bento Item 4: Live Dispatch Hub & Speed Post Metrics -->
      <div class="md:col-span-3 lg:col-span-4 bg-gradient-to-r from-emerald-950 via-stone-900 to-stone-900 rounded-3xl p-6 border border-emerald-800/40 shadow-lg">
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

<!-- 2. Interactive Category Pills Bar -->
<section class="bg-white border-b border-gray-200 py-4 shadow-xs sticky top-0 z-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-none pb-1">
      <a href="/products.php" class="px-4 py-2 bg-stone-900 text-white rounded-full font-bold text-xs shrink-0 hover:bg-stone-800 transition shadow-xs flex items-center gap-1.5">
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

<!-- 3. Seasonal Harvest Fair & Top Catalog Grid -->
<section class="py-10 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-700 uppercase tracking-widest block mb-1">Direct From Villages</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Seasonal Harvest & Handcrafted Bestsellers</h2>
      </div>
      <a href="/products.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950">
        <span>View Full Catalog</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($featured_products, 0, 8) as $p): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 shadow-xs hover:shadow-md transition flex flex-col justify-between group">
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

            <div class="mb-1">
              <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><?= htmlspecialchars($p['category_name'] ?? 'Rural Artisan') ?></span>
              <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-800 transition">
                <?= htmlspecialchars($p['name']) ?>
              </h3>
            </div>
            
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
              <span class="text-[10px] px-1.5 py-0.5 bg-emerald-50 text-emerald-800 font-bold rounded">In Stock</span>
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

<!-- 4. Transparent Rural Economy Card -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-br from-stone-900 via-stone-900 to-amber-950 rounded-3xl p-6 sm:p-10 text-white shadow-xl">
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

<!-- 5. Rural Artisan & Delivery FAQ Accordion -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Frequently Asked Questions</h2>
      <p class="text-xs sm:text-sm text-gray-500 mt-1">Everything you need to know about rural artisan authenticity and dispatch</p>
    </div>

    <div class="space-y-3">
      <details class="bg-white rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
        <summary class="font-bold text-sm text-gray-900 flex items-center justify-between">
          <span>Are the blacksmith tools really forged by hand?</span>
          <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
        </summary>
        <p class="text-xs text-gray-600 mt-3 leading-relaxed">
          Yes! Every agricultural sickle, khurpi, and axe is forged using high-carbon leaf springs on charcoal hearths in our registered hubs like Kalyani agrofood pvt ltd in Sandila, Hardoi. Each tool undergoes oil hardening and water tempering.
        </p>
      </details>

      <details class="bg-white rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
        <summary class="font-bold text-sm text-gray-900 flex items-center justify-between">
          <span>How does delivery work for remote village orders?</span>
          <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
        </summary>
        <p class="text-xs text-gray-600 mt-3 leading-relaxed">
          We partner with India Post (Speed Post and Parcel) which covers every single PIN code across India. Orders within 60 km of the artisan forge or farm are dispatched via Local Express in 24-48 hours.
        </p>
      </details>

      <details class="bg-white rounded-2xl border border-gray-200 p-4 group cursor-pointer shadow-xs">
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
