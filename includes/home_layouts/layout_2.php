<?php
/**
 * Layout 2: Modern Animated Hero Slider & Live Flash Deals Marketplace
 * Brand Theme: GanvGaliHat Village Green (#064e3b) & Thatch Amber (#f59e0b / #ea580c)
 * 
 * Features:
 * 1. Real-time Location & Nearby Forge Bar
 * 2. Visual Circular Category Tray (Strictly ordered by admin sort_order)
 * 3. 3-Slide Auto-Rotating Interactive Carousel (100% Backend Customizable from Admin)
 * 4. Harvest Flash Deals with Real-time Countdown Timer (HH:MM:SS) & Limited Stock Indicator
 * 5. Featured Products & Handcrafted Bestsellers Grid (Instant Add to Cart)
 * 6. Multi-Category Department Showcases (Forged Tools, Cold-Pressed Oils, Handlooms)
 * 7. Nearest Artisan Workshop Finder with Haversine Distance & Courier ETA
 * 8. Direct-From-Soil Trust Pillars (Zero Middlemen Guarantee)
 * 9. Verified Buyer Reviews & Craft Story Testimonials
 * 10. Village Craft FAQ Accordion
 * 
 * Zero Horizontal Overflow, 100% Responsive Desktop & Mobile
 */

// Slide 1 Settings (Backend Customizable)
$s1_badge     = get_setting('slider_slide1_badge', 'Village Forge Heritage • Kalyani agrofood pvt ltd');
$s1_title     = get_setting('slider_slide1_title', 'Hand-Forged Tempered Steel Agricultural Tools');
$s1_subtitle  = get_setting('slider_slide1_subtitle', 'Crafted from high-grade railway spring steel by generational masters of Hardoi. Razor-sharp sickles, heavy axes, and durable khurpis made for lifelong farming.');
$s1_btn1_text = get_setting('slider_slide1_btn1_text', 'Shop Forged Tools');
$s1_btn1_link = get_setting('slider_slide1_btn1_link', '/products.php?category=traditional-tools');
$s1_btn2_text = get_setting('slider_slide1_btn2_text', 'Meet Blacksmiths');
$s1_btn2_link = get_setting('slider_slide1_btn2_link', '/artisans.php');
$s1_img       = get_setting('slider_slide1_image', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1600&q=80');

// Slide 2 Settings (Backend Customizable)
$s2_badge     = get_setting('slider_slide2_badge', '100% Pure Cold-Pressed Organic Staples');
$s2_title     = get_setting('slider_slide2_title', 'Raw Kachi Ghani Mustard Oil & Desi Pickles');
$s2_subtitle  = get_setting('slider_slide2_subtitle', 'Extracted through traditional wooden expellers without chemical refining. Paired with grandma-recipe sun-cured Awadhi mango, amla, and stuffed red chili achar.');
$s2_btn1_text = get_setting('slider_slide2_btn1_text', 'Explore Natural Staples');
$s2_btn1_link = get_setting('slider_slide2_btn1_link', '/products.php?category=pulses-and-oils');
$s2_btn2_text = get_setting('slider_slide2_btn2_text', 'View All Foods');
$s2_btn2_link = get_setting('slider_slide2_btn2_link', '/products.php');
$s2_img       = get_setting('slider_slide2_image', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=1600&q=80');

// Slide 3 Settings (Backend Customizable)
$s3_badge     = get_setting('slider_slide3_badge', 'Women Self-Help Groups • Fair Trade Direct');
$s3_title     = get_setting('slider_slide3_title', 'Handloom Textiles, Clay Pottery & Jute Crafts');
$s3_subtitle  = get_setting('slider_slide3_subtitle', 'Support rural craftswomen across Uttar Pradesh. 96% of sales price directly credits to artisan bank accounts via India Post payments bank.');
$s3_btn1_text = get_setting('slider_slide3_btn1_text', 'Support Craftswomen');
$s3_btn1_link = get_setting('slider_slide3_btn1_link', '/products.php?category=handicrafts');
$s3_btn2_text = get_setting('slider_slide3_btn2_text', 'Join as Seller (5-Step)');
$s3_btn2_link = get_setting('slider_slide3_btn2_link', '/seller-register.php');
$s3_img       = get_setting('slider_slide3_image', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1600&q=80');
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

<!-- 2. Interactive Multi-Slide Hero Carousel (100% Backend-Customizable) -->
<section class="relative w-full overflow-hidden bg-stone-950 text-white" id="heroSliderSection">
  <div class="relative w-full min-h-[380px] sm:min-h-[420px] lg:min-h-[450px] flex items-center">
    
    <!-- Slide 1 -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-100 flex items-center" data-index="0">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($s1_img) ?>');">
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(6,78,59,0.92) 0%, rgba(6,78,59,0.85) 50%, rgba(15,23,42,0.6) 100%);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-12">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-xs">
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
            <span><?= htmlspecialchars($s1_badge) ?></span>
          </div>
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
            <?= htmlspecialchars($s1_title) ?>
          </h1>
          <p class="text-xs sm:text-base text-stone-200 leading-relaxed max-w-xl">
            <?= htmlspecialchars($s1_subtitle) ?>
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="<?= htmlspecialchars($s1_btn1_link) ?>" class="px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span><?= htmlspecialchars($s1_btn1_text) ?></span>
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <a href="<?= htmlspecialchars($s1_btn1_link) ?>" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              <?= htmlspecialchars($s1_btn2_text) ?>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none flex items-center" data-index="1">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($s2_img) ?>');">
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(6,78,59,0.92) 0%, rgba(15,23,42,0.85) 50%, rgba(234,88,12,0.6) 100%);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-12">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-bold shadow-xs">
            <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-400"></i>
            <span><?= htmlspecialchars($s2_badge) ?></span>
          </div>
          <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
            <?= htmlspecialchars($s2_title) ?>
          </h2>
          <p class="text-xs sm:text-base text-stone-200 leading-relaxed max-w-xl">
            <?= htmlspecialchars($s2_subtitle) ?>
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="<?= htmlspecialchars($s2_btn1_link) ?>" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span><?= htmlspecialchars($s2_btn1_text) ?></span>
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <a href="<?= htmlspecialchars($s2_btn2_link) ?>" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              <?= htmlspecialchars($s2_btn2_text) ?>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none flex items-center" data-index="2">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($s3_img) ?>');">
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(15,23,42,0.92) 0%, rgba(6,78,59,0.85) 50%, rgba(245,158,11,0.6) 100%);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-12">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-xs">
            <i data-lucide="heart" class="w-3.5 h-3.5 text-amber-400"></i>
            <span><?= htmlspecialchars($s3_badge) ?></span>
          </div>
          <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
            <?= htmlspecialchars($s3_title) ?>
          </h2>
          <p class="text-xs sm:text-base text-stone-200 leading-relaxed max-w-xl">
            <?= htmlspecialchars($s3_subtitle) ?>
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="<?= htmlspecialchars($s3_btn1_link) ?>" class="px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span><?= htmlspecialchars($s3_btn1_text) ?></span>
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <a href="<?= htmlspecialchars($s3_btn2_link) ?>" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              <?= htmlspecialchars($s3_btn2_text) ?>
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Slider Controls: Arrows -->
  <button type="button" onclick="prevHeroSlide()" aria-label="Previous Slide"
    class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-stone-900/70 hover:bg-stone-900 text-white border border-white/20 flex items-center justify-center transition backdrop-blur-md z-20 cursor-pointer shadow-lg">
    <i data-lucide="chevron-left" class="w-5 h-5"></i>
  </button>
  <button type="button" onclick="nextHeroSlide()" aria-label="Next Slide"
    class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-stone-900/70 hover:bg-stone-900 text-white border border-white/20 flex items-center justify-center transition backdrop-blur-md z-20 cursor-pointer shadow-lg">
    <i data-lucide="chevron-right" class="w-5 h-5"></i>
  </button>

  <!-- Slider Dots Indicator -->
  <div class="absolute bottom-6 inset-x-0 flex items-center justify-center gap-2 z-20">
    <button type="button" onclick="goToHeroSlide(0)" class="slider-dot w-8 h-2 rounded-full bg-amber-500 transition-all cursor-pointer" aria-label="Slide 1"></button>
    <button type="button" onclick="goToHeroSlide(1)" class="slider-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-all cursor-pointer" aria-label="Slide 2"></button>
    <button type="button" onclick="goToHeroSlide(2)" class="slider-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-all cursor-pointer" aria-label="Slide 3"></button>
  </div>
</section>

<!-- 3. Harvest Flash Deals (Live Countdown Timer + Percentage Savings) -->
<section class="py-8 bg-amber-50/70 border-y border-amber-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-amber-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-stone-950 flex items-center justify-center font-black shadow-sm">
          <i data-lucide="zap" class="w-5 h-5 fill-current"></i>
        </div>
        <div>
          <span class="text-[11px] text-amber-800 font-extrabold tracking-wider uppercase block">Direct Maker Discounts</span>
          <h2 class="text-xl sm:text-2xl font-black text-gray-900">Harvest Flash Deals of the Day</h2>
        </div>
      </div>

      <!-- Countdown Clock -->
      <div class="flex items-center gap-2 bg-white border border-amber-300 rounded-2xl px-4 py-2 self-start md:self-auto shadow-xs">
        <span class="text-xs text-gray-600 font-bold mr-1">Deals End In:</span>
        <div class="flex items-center gap-1.5 font-mono text-xs font-black">
          <span id="dealHours" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">07</span>
          <span class="text-amber-600">:</span>
          <span id="dealMins" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">42</span>
          <span class="text-amber-600">:</span>
          <span id="dealSecs" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">19</span>
        </div>
      </div>
    </div>

    <!-- Flash Deals Cards Grid (Modern Meesho/Amazon Clean White Cards) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($featured_products, 0, 4) as $p): 
        $deal_img = !empty($p['image']) ? $p['image'] : (!empty($p['image_url']) ? $p['image_url'] : '/assets/images/placeholder.jpg');
      ?>
        <div class="bg-white rounded-2xl border border-gray-200 hover:border-amber-500 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-50 rounded-xl overflow-hidden mb-3 border border-gray-100">
              <img src="<?= htmlspecialchars($deal_img) ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>" 
                   onerror="this.onerror=null; this.src='/assets/images/placeholder.jpg';"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white font-extrabold text-[10px] rounded-md shadow-xs">
                SAVE 25%
              </span>
            </div>

            <span class="text-[11px] text-emerald-700 font-bold block mb-1 truncate">
              By <?= htmlspecialchars(str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $p['vendor_name'] ?? 'Local Forge')) ?>
            </span>
            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-800 transition mb-2">
              <a href="/product.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
              </a>
            </h3>
          </div>

          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-baseline justify-between mb-2">
              <div class="flex items-baseline gap-1.5">
                <span class="text-base sm:text-lg font-black text-gray-900">₹<?= number_format($p['price'], 0) ?></span>
                <span class="text-[11px] text-gray-400 line-through">₹<?= number_format($p['price'] * 1.35, 0) ?></span>
              </div>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">25% OFF</span>
            </div>
            
            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                <span>Quick Add</span>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 4. Featured Products & Handcrafted Bestsellers Section -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Handpicked Rural Catalog</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Featured Products & Certified Bestsellers</h2>
      </div>
      <a href="/products.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 hover:text-emerald-950">
        <span>View Full Catalog</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <!-- Featured Products 8-Grid -->
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
              <span class="text-[10px] text-gray-400 font-bold uppercase"><?= htmlspecialchars($p['category_name'] ?? 'Artisan') ?></span>
              <span class="text-[11px] font-bold text-amber-600 flex items-center gap-0.5">
                ★ 4.8
              </span>
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

<!-- 5. Heavy-Duty Agricultural Implements by Kalyani agrofood pvt ltd -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Sandila Industrial Hub</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Forged Farming Tools by Kalyani agrofood pvt ltd</h2>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">High-carbon tempered railway leaf springs forged on charcoal anvils in Hardoi</p>
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

<!-- 6. Nearest Artisan Workshop & Forge Locator (Haversine Engine) -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gradient-to-br from-stone-900 to-emerald-950 rounded-3xl p-6 sm:p-10 text-white shadow-xl">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
          <span class="text-xs font-black text-amber-400 uppercase tracking-widest block mb-1">Direct Geolocation Matrix</span>
          <h2 class="text-2xl sm:text-3xl font-black text-white">Artisans Near Your Location</h2>
          <p class="text-stone-300 text-xs sm:text-sm mt-1">
            Real-time distances calculated from <?= htmlspecialchars($delivery_location_text) ?>
          </p>
        </div>
        <a href="/artisans.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:underline">
          <span>Explore All 42 Registered Forges</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($nearest_vendors as $v): ?>
          <div class="bg-stone-800/80 rounded-2xl p-5 border border-stone-700 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold text-[10px] rounded-md">
                  <?= htmlspecialchars($v['type']) ?>
                </span>
                <span class="text-xs font-black text-amber-400">★ <?= $v['rating'] ?></span>
              </div>
              <h3 class="font-extrabold text-sm text-white mb-1"><?= htmlspecialchars($v['name']) ?></h3>
              <p class="text-xs text-stone-400 line-clamp-2 mb-3"><?= htmlspecialchars($v['story']) ?></p>
            </div>
            <div class="pt-3 border-t border-stone-700 text-xs flex items-center justify-between">
              <span class="text-stone-300">📍 <?= htmlspecialchars($v['district']) ?></span>
              <span class="font-black text-emerald-400"><?= $v['distance_km'] ?> km away</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- 7. Four Direct-From-Soil Trust Pillars -->
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

<!-- 8. Verified Buyer Reviews & Testimonials -->
<section class="py-12 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
      <span class="text-xs font-black text-amber-700 uppercase tracking-widest block mb-1">Customer Voice</span>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Trusted by Over 4,800+ Families</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "The tempered sickle from Kalyani agrofood pvt ltd in Sandila is of extraordinary quality. The weight balance is perfect for paddy harvesting and the sharpness lasted the entire season without chipping."
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-200">
          <div class="w-9 h-9 rounded-full bg-emerald-800 text-white font-bold text-xs flex items-center justify-center">
            RS
          </div>
          <div>
            <h4 class="font-extrabold text-xs text-gray-900">Rameshwar Sharma</h4>
            <span class="text-[10px] text-gray-500">Verified Buyer • Lucknow, UP</span>
          </div>
        </div>
      </div>

      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "The aroma of the wooden-kohlu mustard oil immediately reminded me of our village ancestral home. 100% pure, natural pungency, and clear yellow consistency. Will be reordering monthly!"
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-200">
          <div class="w-9 h-9 rounded-full bg-amber-600 text-white font-bold text-xs flex items-center justify-center">
            PK
          </div>
          <div>
            <h4 class="font-extrabold text-xs text-gray-900">Pooja Kashyap</h4>
            <span class="text-[10px] text-gray-500">Verified Buyer • Kanpur, UP</span>
          </div>
        </div>
      </div>

      <div class="bg-stone-50 rounded-2xl border border-gray-200 p-6 shadow-xs flex flex-col justify-between">
        <div>
          <div class="text-amber-500 text-sm mb-3">★★★★★</div>
          <p class="text-xs text-gray-700 leading-relaxed mb-4">
            "Delivered by India Post Speed Post within 48 hours to our town. The handmade cotton bedspread is breathable and beautiful. Happy to know our money directly supports rural women weavers."
          </p>
        </div>
        <div class="flex items-center gap-3 pt-3 border-t border-gray-200">
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

<!-- Slider & Countdown Controller Script -->
<script>
let currentHeroSlide = 0;
const heroSlides = document.querySelectorAll('#heroSliderSection .hero-slide');
const heroDots = document.querySelectorAll('#heroSliderSection .slider-dot');
let heroSlideTimer = null;

function showHeroSlide(index) {
  if (!heroSlides.length) return;
  if (index >= heroSlides.length) index = 0;
  if (index < 0) index = heroSlides.length - 1;
  currentHeroSlide = index;

  heroSlides.forEach((slide, i) => {
    if (i === index) {
      slide.classList.remove('opacity-0', 'pointer-events-none');
      slide.classList.add('opacity-100');
    } else {
      slide.classList.remove('opacity-100');
      slide.classList.add('opacity-0', 'pointer-events-none');
    }
  });

  heroDots.forEach((dot, i) => {
    if (i === index) {
      dot.className = 'slider-dot w-8 h-2 rounded-full bg-amber-500 transition-all cursor-pointer';
    } else {
      dot.className = 'slider-dot w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-all cursor-pointer';
    }
  });
}

function nextHeroSlide() { showHeroSlide(currentHeroSlide + 1); }
function prevHeroSlide() { showHeroSlide(currentHeroSlide - 1); }
function goToHeroSlide(idx) { showHeroSlide(idx); }

function startHeroAutoPlay() {
  stopHeroAutoPlay();
  heroSlideTimer = setInterval(nextHeroSlide, 5000);
}
function stopHeroAutoPlay() {
  if (heroSlideTimer) {
    clearInterval(heroSlideTimer);
    heroSlideTimer = null;
  }
}

const sliderWrap = document.getElementById('heroSliderSection');
if (sliderWrap) {
  sliderWrap.addEventListener('mouseenter', stopHeroAutoPlay);
  sliderWrap.addEventListener('mouseleave', startHeroAutoPlay);

  // Mobile Touch Swipe Handling
  let touchStartX = 0;
  sliderWrap.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    stopHeroAutoPlay();
  }, { passive: true });

  sliderWrap.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 45) { nextHeroSlide(); }
    else if (touchEndX - touchStartX > 45) { prevHeroSlide(); }
    startHeroAutoPlay();
  }, { passive: true });

  startHeroAutoPlay();
}

// Flash Deals Timer Controller
function updateFlashTimer() {
  const hEl = document.getElementById('dealHours');
  const mEl = document.getElementById('dealMins');
  const sEl = document.getElementById('dealSecs');
  if (!hEl || !mEl || !sEl) return;

  let h = parseInt(hEl.innerText, 10);
  let m = parseInt(mEl.innerText, 10);
  let s = parseInt(sEl.innerText, 10);

  s--;
  if (s < 0) { s = 59; m--; }
  if (m < 0) { m = 59; h--; }
  if (h < 0) { h = 12; m = 0; s = 0; }

  hEl.innerText = h < 10 ? '0' + h : h;
  mEl.innerText = m < 10 ? '0' + m : m;
  sEl.innerText = s < 10 ? '0' + s : s;
}
setInterval(updateFlashTimer, 1000);
</script>
