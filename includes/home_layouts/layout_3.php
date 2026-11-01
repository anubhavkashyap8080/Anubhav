<?php
/**
 * Layout 3: Cinematic YouTube Video Hero & Rural Craft Documentary Showcase
 * Features:
 * - Dynamic YouTube Video Embed Hero Section (Admin Manageable via site_settings)
 * - Behind-the-Scenes Forge & Traditional Oil Milling Documentary Story Cards
 * - Responsive 16:9 Aspect Ratio (Zero overflow on mobile or desktop)
 * - Circular Category Tray & Direct Forge Product Showcase
 * - India Post Speed Post Guaranteed Dispatch Reassurance
 */

$yt_setting = get_setting('homepage_youtube_url', 'https://www.youtube.com/watch?v=k4m3y1eH5Ww');
$yt_embed_url = get_youtube_embed_url($yt_setting);
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

<!-- 1. Circular Visual Category Tray -->
<section class="bg-white border-b border-gray-200 py-3 sm:py-4 shadow-xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-start sm:justify-between gap-4 sm:gap-6 overflow-x-auto scrollbar-none pb-1">
      <?php foreach ($categories as $cat): ?>
        <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" 
           class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
          <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-amber-500 via-rose-600 to-amber-400 group-hover:from-emerald-500 group-hover:to-brand-orange transition-all shadow-xs group-hover:shadow-md">
            <div class="w-full h-full rounded-full overflow-hidden bg-white p-0.5">
              <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" 
                   class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform duration-300">
            </div>
          </div>
          <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-amber-700 mt-1.5 line-clamp-1 leading-tight">
            <?= htmlspecialchars($cat['name']) ?>
          </span>
          <span class="text-[9px] text-gray-400 font-medium line-clamp-1">Explore</span>
        </a>
      <?php endforeach; ?>

      <a href="/products.php" class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
        <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-amber-600 to-red-600 shadow-xs group-hover:shadow-md">
          <div class="w-full h-full rounded-full bg-stone-900 text-amber-300 flex items-center justify-center group-hover:bg-amber-950 transition-colors">
            <i data-lucide="layout-grid" class="w-7 h-7"></i>
          </div>
        </div>
        <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-amber-700 mt-1.5 leading-tight">
          All Crafts
        </span>
        <span class="text-[9px] text-gray-400 font-medium">Browse All</span>
      </a>
    </div>
  </div>
</section>

<!-- 2. Cinematic Video Hero Section (Layout 3) -->
<section class="relative bg-gradient-to-br from-stone-950 via-stone-900 to-stone-950 text-white py-12 sm:py-16 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      
      <!-- Left Column: Video Story Brief & Headlines -->
      <div class="lg:col-span-6 space-y-5 text-center lg:text-left">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-600/20 border border-red-500/40 text-red-400 text-xs font-bold shadow-xs">
          <i class="fa-solid fa-circle-play text-red-500 animate-pulse"></i>
          <span>Live Rural Video Showcase • Kalyani agrofood pvt ltd</span>
        </div>
        
        <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight">
          Witness Generational Rural Craft in Action
        </h1>
        
        <p class="text-xs sm:text-sm text-stone-300 leading-relaxed max-w-xl">
          Watch our village blacksmiths forge agricultural tools at 1,000°C and witness cold-pressing of pure mustard oil in traditional wooden expellers. Handcrafted in Hardoi, Uttar Pradesh.
        </p>

        <!-- Trust Metric Counters -->
        <div class="grid grid-cols-3 gap-3 pt-2 text-center">
          <div class="p-3 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-sm">
            <div class="text-lg sm:text-2xl font-black text-amber-400">100%</div>
            <div class="text-[11px] text-stone-400 mt-0.5">Forged Steel</div>
          </div>
          <div class="p-3 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-sm">
            <div class="text-lg sm:text-2xl font-black text-emerald-400">0%</div>
            <div class="text-[11px] text-stone-400 mt-0.5">Middlemen</div>
          </div>
          <div class="p-3 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-sm">
            <div class="text-lg sm:text-2xl font-black text-blue-400">2-4 Days</div>
            <div class="text-[11px] text-stone-400 mt-0.5">Speed Post</div>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
          <a href="/products.php" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
            <span>Shop Documented Crafts</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </a>
          <a href="/artisans.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition border border-white/20 backdrop-blur-md">
            View All Artisans
          </a>
        </div>
      </div>

      <!-- Right Column: Embedded YouTube Video Container (16:9 Responsive) -->
      <div class="lg:col-span-6">
        <div class="relative bg-stone-900 rounded-3xl p-2 sm:p-3 border-2 border-amber-500/40 shadow-2xl overflow-hidden group">
          <div class="relative w-full rounded-2xl overflow-hidden bg-black pb-[56.25%] h-0">
            <iframe 
              src="<?= htmlspecialchars($yt_embed_url) ?>" 
              title="GanvGaliHat Village Forge & Artisan Documentary"
              class="absolute inset-0 w-full h-full border-0 rounded-2xl"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
              allowfullscreen>
            </iframe>
          </div>
          <div class="p-3 text-xs flex items-center justify-between text-stone-300">
            <span class="flex items-center gap-1.5 font-bold text-amber-300">
              <i class="fa-solid fa-film"></i> Official Artisan Documentary
            </span>
            <span class="text-[11px] text-stone-400">Hardoi Forge, Sandila Hub</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 3. Documentary Story Highlights Cards -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-10">
      <span class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1 rounded-full uppercase">Artisan Stories</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">The Real Hands Behind Your Products</h2>
      <p class="text-xs sm:text-sm text-gray-500 mt-1">Every sickle, bottle of cold-pressed oil, and jar of sun-cured pickle has a story of generational rural mastery.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Story 1 -->
      <div class="bg-stone-50 rounded-3xl border border-gray-200 p-6 shadow-xs hover:border-amber-500 transition space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center text-xl font-bold">
          <i class="fa-solid fa-hammer"></i>
        </div>
        <h3 class="text-base font-bold text-gray-900">1000°C Charcoal Anvil Forging</h3>
        <p class="text-xs text-gray-600 leading-relaxed">
          Ram Kishan and his sons in Baghauli heat recycled railway leaf-springs over red-hot charcoal, pounding them with 10-kg sledgehammers into perfectly balanced cutting tools.
        </p>
        <a href="/products.php?category=blacksmithing" class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 hover:underline">
          View Forged Sickles & Axes <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>

      <!-- Story 2 -->
      <div class="bg-stone-50 rounded-3xl border border-gray-200 p-6 shadow-xs hover:border-emerald-500 transition space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold">
          <i class="fa-solid fa-wheat-awn"></i>
        </div>
        <h3 class="text-base font-bold text-gray-900">Traditional Wooden Kohlu Milling</h3>
        <p class="text-xs text-gray-600 leading-relaxed">
          Mallawan oil expellers crush indigenous yellow mustard seeds slowly at room temperature, preserving the natural pungency, healthy omega fats, and raw vitamins.
        </p>
        <a href="/products.php?category=oil-ghee" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:underline">
          View Raw Mustard Oil <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>

      <!-- Story 3 -->
      <div class="bg-stone-50 rounded-3xl border border-gray-200 p-6 shadow-xs hover:border-purple-500 transition space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl font-bold">
          <i class="fa-solid fa-jar"></i>
        </div>
        <h3 class="text-base font-bold text-gray-900">Sun-Cured Awadhi Ceramic Pickles</h3>
        <p class="text-xs text-gray-600 leading-relaxed">
          Women self-help groups cure heirloom mango, amla, and stuffed red chilies on village rooftops in porcelain barnis for 45 days using raw spices and cold-pressed mustard oil.
        </p>
        <a href="/products.php?category=pickles-achar" class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 hover:underline">
          View Homemade Pickles <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- 4. Featured Bestsellers Grid -->
<section class="py-12 bg-stone-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-bold text-amber-700 uppercase tracking-wider bg-amber-100 px-3 py-1 rounded-full">Artisan Catalog</span>
        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-2">Documented Forge & Farm Goods</h2>
      </div>
      <a href="/products.php" class="text-xs font-bold text-brand-green hover:underline">View All Products</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($featured_products, 0, 8) as $prod): 
        $p_img = !empty($prod['image']) ? $prod['image'] : '/assets/images/placeholder.png';
      ?>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col group">
          <div class="relative overflow-hidden aspect-square bg-stone-100">
            <img src="<?= htmlspecialchars($p_img) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              onerror="this.src='/assets/images/placeholder.png'">
            <?php if (!empty($prod['is_best_seller'])): ?>
              <span class="absolute top-2.5 left-2.5 text-[10px] font-extrabold bg-amber-500 text-stone-950 px-2 py-0.5 rounded-full shadow-xs">
                BESTSELLER
              </span>
            <?php endif; ?>
          </div>
          <div class="p-4 flex-1 flex flex-col justify-between">
            <div>
              <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wide">
                <?= htmlspecialchars($prod['category_name'] ?? 'Rural Craft') ?>
              </p>
              <a href="/product.php?id=<?= $prod['id'] ?>" class="text-xs sm:text-sm font-bold text-gray-900 hover:text-brand-green transition line-clamp-2 mt-1">
                <?= htmlspecialchars($prod['name']) ?>
              </a>
              <p class="text-[11px] text-gray-500 mt-0.5">
                By <?= htmlspecialchars($prod['vendor_name'] ?? 'Kalyani agrofood pvt ltd') ?>
              </p>
            </div>
            <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between">
              <div>
                <span class="text-sm sm:text-base font-extrabold text-gray-900"><?= format_price($prod['price']) ?></span>
                <?php if (!empty($prod['mrp']) && $prod['mrp'] > $prod['price']): ?>
                  <span class="text-[11px] text-gray-400 line-through ml-1"><?= format_price($prod['mrp']) ?></span>
                <?php endif; ?>
              </div>
              <a href="/product.php?id=<?= $prod['id'] ?>" class="w-8 h-8 rounded-xl bg-brand-green hover:bg-emerald-800 text-white flex items-center justify-center transition shadow-xs">
                <i class="fa-solid fa-cart-plus text-xs"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
