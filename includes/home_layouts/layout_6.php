<?php
/**
 * Layout 6: "Video Story Home"
 * - Section 1 (HERO ONLY): Full-width, full-height edge-to-edge background YouTube video (muted, autoplay, loop, controls=0).
 * - Sections 2-6 (BELOW HERO): Clean, modern high-contrast e-commerce sections on clean white/light backgrounds.
 */

require_once __DIR__ . '/../video_helpers.php';

// Global options
$v6_data_saver = get_setting('v6_save_data_pause', '1') === '1';

// Load configurations for all 6 sections
$sections = [];
for ($i = 1; $i <= 6; $i++) {
    $sections[$i] = ggh_v6_get_section_config($i);
}

// Fetch products for Sections 3 & 4 (Featured products first per user requirement)
try {
    // S3: Bestsellers / Popular
    $s3_limit = $sections[3]['prod_limit'] ?? 8;
    $s3_stmt = $pdo->prepare("
        SELECT p.*, v.business_name as vendor_business_name, v.village as vendor_village, v.district as vendor_district
        FROM products p 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        WHERE (p.status = 'active' OR p.status IS NULL) AND (p.stock > 0 OR p.stock IS NULL)
        ORDER BY p.is_featured DESC, p.is_best_seller DESC, p.id DESC 
        LIMIT ?
    ");
    $s3_stmt->bindValue(1, $s3_limit, PDO::PARAM_INT);
    $s3_stmt->execute();
    $s3_products = $s3_stmt->fetchAll();

    // S4: Curated Spotlight / Featured
    $s4_limit = $sections[4]['prod_limit'] ?? 5;
    $s4_stmt = $pdo->prepare("
        SELECT p.*, v.business_name as vendor_business_name, v.village as vendor_village, v.district as vendor_district
        FROM products p 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        WHERE (p.status = 'active' OR p.status IS NULL) AND (p.stock > 0 OR p.stock IS NULL)
        ORDER BY p.is_featured DESC, p.id DESC 
        LIMIT ?
    ");
    $s4_stmt->bindValue(1, $s4_limit, PDO::PARAM_INT);
    $s4_stmt->execute();
    $s4_products = $s4_stmt->fetchAll();
} catch (Exception $e) {
    error_log("Layout 6 Product Query Error: " . $e->getMessage());
    $s3_products = [];
    $s4_products = [];
}

// Categories for Section 2
$s2_limit = $sections[2]['cat_limit'] ?? 6;
$active_categories = array_slice($categories ?? [], 0, $s2_limit);

// Artisans for Section 5
$s5_limit = $sections[5]['artisan_limit'] ?? 4;
$active_artisans = array_slice($all_vendors ?? [], 0, $s5_limit);
?>

<style>
/* Section 1: Hero-Only Background Video Styles */
.vh-hero-section {
  position: relative;
  width: 100%;
  min-height: calc(100vh - 72px);
  min-height: 88vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: #0c0a09;
}

.vh-hero-media {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
  z-index: 1;
}

.vh-hero-poster {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: opacity 0.8s ease;
  z-index: 2;
}

.vh-hero-player-wrap {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
  opacity: 0;
  transition: opacity 0.9s ease-in-out;
}

.vh-hero-player-wrap.vh-loaded {
  opacity: 1;
}

/* Cover scaling: forces 16:9 YouTube video to cover 100% of hero without letterbox */
.vh-hero-player-wrap iframe,
.vh-hero-player-wrap video {
  position: absolute;
  top: 50%;
  left: 50%;
  width: max(100vw, 177.78vh);
  height: max(100vh, 56.25vw);
  transform: translate(-50%, -50%);
  border: 0;
  pointer-events: none;
  object-fit: cover;
}

.vh-hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 3;
  pointer-events: none;
}

.vh-hero-content {
  position: relative;
  z-index: 4;
  width: 100%;
  max-width: 80rem;
  margin-left: auto;
  margin-right: auto;
  padding: 5rem 1.5rem 4rem 1.5rem;
}

@media (min-width: 640px) {
  .vh-hero-content {
    padding: 6rem 2rem 5rem 2rem;
  }
}
</style>

<!-- ========================================================================= -->
<!-- SECTION 1: HERO SECTION (FULL-WIDTH & FULL-HEIGHT YOUTUBE BACKGROUND)     -->
<!-- ========================================================================= -->
<?php if ($sections[1]['enabled'] === '1'): 
  $s1 = $sections[1];
  $s1_yt = $s1['yt_id'];
?>
<section id="v6-sec-1" class="vh-hero-section" 
  data-source="<?= htmlspecialchars($s1['source']) ?>" 
  data-yt="<?= htmlspecialchars($s1_yt) ?>" 
  data-mp4="<?= $s1['source'] === 'mp4' ? htmlspecialchars($s1['video']) : '' ?>"
  data-start="<?= $s1['start'] ?>" 
  data-end="<?= $s1['end'] ?>">

  <!-- Full-Width Edge-to-Edge Video Media Layer -->
  <div class="vh-hero-media" aria-hidden="true">
    <img class="vh-hero-poster" src="<?= htmlspecialchars($s1['poster']) ?>" alt="Village Craft Heritage" fetchpriority="high" width="1920" height="1080">
    <div class="vh-hero-player-wrap"></div>
  </div>

  <!-- Ambient Dark Gradient Overlay for Maximum Text Contrast -->
  <div class="vh-hero-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,<?= $s1['overlay']/100 * 0.85 ?>) 0%, rgba(12,10,9,<?= $s1['overlay']/100 ?>) 50%, rgba(12,10,9,<?= min(0.95, $s1['overlay']/100 * 1.25) ?>) 100%);"></div>

  <!-- Hero Content -->
  <div class="vh-hero-content text-<?= $s1['align'] ?> text-white">
    <div class="max-w-3xl <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?> space-y-5">
      
      <!-- Eyebrow Pill -->
      <?php if (!empty($s1['eyebrow'])): ?>
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/25 border border-amber-400/50 text-amber-300 text-xs font-bold tracking-wide shadow-md backdrop-blur-md">
        <i class="fa-solid fa-sparkles text-amber-400"></i>
        <span><?= htmlspecialchars($s1['eyebrow']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Real H1 Heading -->
      <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-display text-white leading-tight tracking-tight drop-shadow-lg">
        <?= htmlspecialchars($s1['title']) ?>
      </h1>

      <!-- Subtitle Description -->
      <p class="text-sm sm:text-lg text-stone-200 leading-relaxed font-medium drop-shadow-md max-w-2xl <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?>">
        <?= htmlspecialchars($s1['subtitle']) ?>
      </p>

      <!-- CTA Buttons (Task B Show/Hide & Open-In-New-Tab) -->
      <?php
      $s1_b1_show = ($s1['btn1_show'] === '1' && trim($s1['btn1_text']) !== '');
      $s1_b2_show = ($s1['btn2_show'] === '1' && trim($s1['btn2_text']) !== '');
      $s1_b1_target = ($s1['btn1_target'] === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
      $s1_b2_target = ($s1['btn2_target'] === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
      ?>
      <?php if ($s1_b1_show || $s1_b2_show): ?>
      <div class="flex flex-wrap items-center <?= $s1['align'] === 'center' ? 'justify-center' : 'justify-start' ?> gap-3.5 pt-3">
        <?php if ($s1_b1_show): ?>
        <a href="<?= htmlspecialchars($s1['btn1_link']) ?>"<?= $s1_b1_target ?>
           class="px-7 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-2xl shadow-xl transition-all duration-200 active:scale-95 flex items-center gap-2 cursor-pointer">
          <span><?= htmlspecialchars($s1['btn1_text']) ?></span>
          <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
        <?php endif; ?>

        <?php if ($s1_b2_show): ?>
        <a href="<?= htmlspecialchars($s1['btn2_link']) ?>"<?= $s1_b2_target ?>
           class="px-6 py-3.5 bg-white/15 hover:bg-white/25 text-white font-bold text-xs sm:text-sm rounded-2xl border border-white/30 transition-all backdrop-blur-md flex items-center gap-2">
          <span><?= htmlspecialchars($s1['btn2_text']) ?></span>
        </a>
        <?php endif; ?>

        <button type="button" onclick="detectGPSLocation()" class="px-5 py-3.5 bg-stone-900/85 hover:bg-stone-900 text-amber-300 font-bold text-xs sm:text-sm rounded-2xl border border-amber-400/40 transition-all flex items-center gap-2 cursor-pointer shadow-md">
          <i class="fa-solid fa-crosshairs text-amber-400"></i>
          <span>Artisans Near Me</span>
        </button>
      </div>
      <?php endif; ?>

      <!-- Trust Badges Strip -->
      <div class="grid grid-cols-3 gap-3 pt-6 border-t border-white/20 max-w-md <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?>">
        <div>
          <span class="block text-xl sm:text-2xl font-black text-amber-400 font-display"><?= htmlspecialchars(get_setting('hero_stat1_num', '100%')) ?></span>
          <span class="text-[11px] text-stone-300 font-medium"><?= htmlspecialchars(get_setting('hero_stat1_label', 'Hand-Forged Craft')) ?></span>
        </div>
        <div>
          <span class="block text-xl sm:text-2xl font-black text-amber-400 font-display"><?= htmlspecialchars(get_setting('hero_stat2_num', '40+')) ?></span>
          <span class="text-[11px] text-stone-300 font-medium"><?= htmlspecialchars(get_setting('hero_stat2_label', 'Generational Forges')) ?></span>
        </div>
        <div>
          <span class="block text-xl sm:text-2xl font-black text-amber-400 font-display"><?= htmlspecialchars(get_setting('hero_stat3_num', '₹0')) ?></span>
          <span class="text-[11px] text-stone-300 font-medium"><?= htmlspecialchars(get_setting('hero_stat3_label', 'Middleman Commission')) ?></span>
        </div>
      </div>

    </div>

    <!-- Audio Mute / Unmute Toggle Button -->
    <button type="button" id="v6-audio-toggle" onclick="toggleHeroAudio()" class="absolute bottom-6 right-6 z-10 px-3.5 py-1.5 rounded-full bg-black/60 hover:bg-black/80 text-white text-xs font-bold border border-white/30 backdrop-blur-md transition flex items-center gap-2 cursor-pointer shadow-lg">
      <i class="fa-solid fa-volume-xmark text-amber-400"></i>
      <span>Sound</span>
    </button>

    <!-- Scroll Down Hint Indicator -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-1.5 opacity-80 hover:opacity-100 transition cursor-pointer" onclick="document.getElementById('v6-sec-2')?.scrollIntoView({behavior:'smooth'})">
      <span class="text-[10px] font-bold uppercase tracking-wider text-stone-300">Explore Catalog</span>
      <i class="fa-solid fa-chevron-down text-amber-400 animate-bounce text-xs"></i>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 2: ARTISANAL CATEGORIES (CLEAN E-COMMERCE SECTION)                -->
<!-- ========================================================================= -->
<?php if ($sections[2]['enabled'] === '1'): 
  $s2 = $sections[2];
?>
<section id="v6-sec-2" class="py-12 sm:py-16 bg-stone-50 border-b border-stone-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
      <div>
        <?php if (!empty($s2['eyebrow'])): ?>
        <span class="text-xs font-black text-emerald-700 uppercase tracking-widest block mb-1"><?= htmlspecialchars($s2['eyebrow']) ?></span>
        <?php endif; ?>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display"><?= htmlspecialchars($s2['title']) ?></h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-1"><?= htmlspecialchars($s2['subtitle']) ?></p>
      </div>

      <?php if ($s2['btn1_show'] === '1' && trim($s2['btn1_text']) !== ''): ?>
      <a href="<?= htmlspecialchars($s2['btn1_link']) ?>" class="text-xs font-bold text-brand-green hover:underline flex items-center gap-1.5 shrink-0">
        <span><?= htmlspecialchars($s2['btn1_text']) ?></span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
      <?php endif; ?>
    </div>

    <!-- Category Tiles Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-5">
      <?php foreach ($active_categories as $cat): ?>
      <a href="/products.php?category=<?= urlencode($cat['slug']) ?>" 
         class="bg-white rounded-2xl p-4 border border-stone-200/90 shadow-xs hover:shadow-md hover:border-amber-400 transition-all flex flex-col items-center text-center group cursor-pointer">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-amber-50 border border-amber-200/60 p-2 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform overflow-hidden">
          <?php if (!empty($cat['image_url'])): ?>
            <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover rounded-xl" loading="lazy" width="80" height="80">
          <?php else: ?>
            <i class="fa-solid fa-boxes-stacked text-2xl text-amber-600"></i>
          <?php endif; ?>
        </div>
        <h3 class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-amber-600 transition-colors line-clamp-1">
          <span class="lang-en"><?= htmlspecialchars($cat['name']) ?></span>
          <?php if (!empty($cat['hindi_name'])): ?>
            <span class="lang-hi hidden"><?= htmlspecialchars($cat['hindi_name']) ?></span>
          <?php endif; ?>
        </h3>
        <span class="text-[10px] text-gray-500 mt-0.5">Explore Products &rarr;</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 3: BESTSELLERS RAIL (CLEAN PRODUCT GRID)                          -->
<!-- ========================================================================= -->
<?php if ($sections[3]['enabled'] === '1'): 
  $s3 = $sections[3];
?>
<section id="v6-sec-3" class="py-12 sm:py-16 bg-white border-b border-stone-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
      <div>
        <?php if (!empty($s3['eyebrow'])): ?>
        <span class="text-xs font-black text-amber-600 uppercase tracking-widest block mb-1"><?= htmlspecialchars($s3['eyebrow']) ?></span>
        <?php endif; ?>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display"><?= htmlspecialchars($s3['title']) ?></h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-1"><?= htmlspecialchars($s3['subtitle']) ?></p>
      </div>

      <?php if ($s3['btn1_show'] === '1' && trim($s3['btn1_text']) !== ''): ?>
      <a href="<?= htmlspecialchars($s3['btn1_link']) ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 shrink-0">
        <span><?= htmlspecialchars($s3['btn1_text']) ?></span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
      <?php endif; ?>
    </div>

    <!-- Product Grid (High-Contrast Clean E-Commerce Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach ($s3_products as $p): ?>
      <div class="bg-white rounded-2xl border border-stone-200 shadow-xs hover:shadow-lg transition-all p-3 sm:p-4 flex flex-col justify-between group">
        <div>
          <!-- Product Image -->
          <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-stone-100 mb-3">
            <img src="<?= htmlspecialchars($p['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" width="280" height="280">
            
            <?php if (!empty($p['is_featured'])): ?>
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[9px] rounded-md uppercase tracking-wider shadow-sm">
                ⭐ Featured
              </span>
            <?php elseif (!empty($p['is_best_seller'])): ?>
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-rose-500 text-white font-black text-[9px] rounded-md uppercase tracking-wider shadow-sm">
                Bestseller
              </span>
            <?php endif; ?>

            <button type="button" onclick="toggleWishlist(<?= $p['id'] ?>, this)" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 text-gray-600 hover:text-rose-500 shadow-sm flex items-center justify-center transition">
              <i class="fa-regular fa-heart text-xs"></i>
            </button>
          </div>

          <!-- Artisan & Title -->
          <span class="text-[10px] text-amber-700 font-bold block mb-0.5 line-clamp-1">
            <i class="fa-solid fa-store text-[9px]"></i> <?= htmlspecialchars($p['vendor_business_name'] ?? 'Rural Artisan') ?>
          </span>
          <h3 class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-green line-clamp-2 leading-tight transition-colors">
            <a href="/product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
          </h3>
        </div>

        <!-- Pricing & Add to Cart -->
        <div class="pt-3 border-t border-gray-100 mt-3 flex items-center justify-between gap-2">
          <div>
            <span class="text-sm sm:text-base font-black text-gray-900"><?= format_price($p['price']) ?></span>
            <?php if (!empty($p['mrp']) && $p['mrp'] > $p['price']): ?>
              <span class="text-[10px] text-gray-400 line-through ml-1"><?= format_price($p['mrp']) ?></span>
            <?php endif; ?>
          </div>
          <button type="button" onclick="addToCart(<?= $p['id'] ?>, 1, this)" class="px-3 py-1.5 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-lg transition active:scale-95 flex items-center gap-1 cursor-pointer shadow-xs">
            <i class="fa-solid fa-cart-plus text-xs"></i>
            <span>Add</span>
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 4: CURATED SPOTLIGHT (CLEAN SPOTLIGHT SHOWCASE)                    -->
<!-- ========================================================================= -->
<?php if ($sections[4]['enabled'] === '1'): 
  $s4 = $sections[4];
  $spotlight_item = $s4_products[0] ?? null;
  $grid_items = array_slice($s4_products, 1, 4);
?>
<section id="v6-sec-4" class="py-12 sm:py-16 bg-stone-50 border-b border-stone-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-8 space-y-1">
      <?php if (!empty($s4['eyebrow'])): ?>
      <span class="text-xs font-black text-amber-600 uppercase tracking-widest block"><?= htmlspecialchars($s4['eyebrow']) ?></span>
      <?php endif; ?>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display"><?= htmlspecialchars($s4['title']) ?></h2>
      <p class="text-xs sm:text-sm text-gray-600"><?= htmlspecialchars($s4['subtitle']) ?></p>
    </div>

    <!-- Asymmetric Spotlight Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
      
      <!-- Big Hero Spotlight Card (5 cols) -->
      <?php if ($spotlight_item): ?>
      <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-amber-300 shadow-md flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 rounded-full bg-amber-500 text-stone-950 font-black text-[11px] uppercase tracking-wider">
              ⭐ Artisan Master Spotlight
            </span>
            <span class="text-xs text-amber-600 font-bold flex items-center gap-1">
              <i class="fa-solid fa-star text-amber-500"></i>
              <?= number_format($spotlight_item['rating'] ?? 5.0, 1) ?> Rating
            </span>
          </div>

          <div class="w-full aspect-video rounded-2xl overflow-hidden bg-stone-100 mb-4 shadow-xs">
            <img src="<?= htmlspecialchars($spotlight_item['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($spotlight_item['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="480" height="270">
          </div>

          <span class="text-xs text-amber-700 font-semibold block mb-1">
            📍 Village Workshop: <?= htmlspecialchars($spotlight_item['vendor_business_name'] ?? 'Hardoi Cluster') ?>
          </span>
          <h3 class="text-lg sm:text-xl font-black text-gray-900 leading-snug">
            <?= htmlspecialchars($spotlight_item['name']) ?>
          </h3>
          <p class="text-xs text-gray-600 mt-2 line-clamp-3 leading-relaxed">
            <?= htmlspecialchars($spotlight_item['description'] ?? 'Generational master craft forged with high-grade leaf-spring carbon steel. Built for unmatched durability.') ?>
          </p>
        </div>

        <div class="pt-5 border-t border-gray-100 mt-5 flex items-center justify-between">
          <div>
            <span class="text-2xl font-black text-gray-900"><?= format_price($spotlight_item['price']) ?></span>
            <span class="text-xs text-emerald-700 font-bold block">✓ Express Village Dispatch</span>
          </div>
          <button type="button" onclick="addToCart(<?= $spotlight_item['id'] ?>, 1, this)" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl shadow-md transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-cart-plus"></i>
            <span>Add to Cart</span>
          </button>
        </div>
      </div>
      <?php endif; ?>

      <!-- Right 4 Smaller Grid Cards (7 cols) -->
      <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($grid_items as $item): ?>
        <div class="bg-white rounded-2xl p-4 border border-stone-200 shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
          <div class="flex items-start gap-3">
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-stone-100 shrink-0">
              <img src="<?= htmlspecialchars($item['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="80" height="80">
            </div>
            <div>
              <span class="text-[10px] text-amber-700 font-bold block"><?= htmlspecialchars($item['vendor_business_name'] ?? 'Artisan Guild') ?></span>
              <h4 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-tight mt-0.5">
                <a href="/product.php?id=<?= $item['id'] ?>" class="hover:text-brand-green"><?= htmlspecialchars($item['name']) ?></a>
              </h4>
              <div class="text-xs font-black text-gray-900 mt-1"><?= format_price($item['price']) ?></div>
            </div>
          </div>
          <div class="pt-2.5 border-t border-gray-100 mt-3 flex items-center justify-between">
            <a href="/product.php?id=<?= $item['id'] ?>" class="text-[11px] text-gray-500 hover:text-gray-900 flex items-center gap-1">
              <span>Details</span> <i class="fa-solid fa-chevron-right text-[9px]"></i>
            </a>
            <button type="button" onclick="addToCart(<?= $item['id'] ?>, 1, this)" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs rounded-lg transition active:scale-95 cursor-pointer">
              + Cart
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 5: MEET OUR MASTER ARTISANS (CLEAN ARTISAN PROFILES)              -->
<!-- ========================================================================= -->
<?php if ($sections[5]['enabled'] === '1'): 
  $s5 = $sections[5];
?>
<section id="v6-sec-5" class="py-12 sm:py-16 bg-white border-b border-stone-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-<?= $s5['align'] ?> max-w-2xl <?= $s5['align'] === 'center' ? 'mx-auto' : '' ?> mb-10 space-y-2">
      <?php if (!empty($s5['eyebrow'])): ?>
      <span class="text-xs font-black text-amber-600 uppercase tracking-widest block"><?= htmlspecialchars($s5['eyebrow']) ?></span>
      <?php endif; ?>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display"><?= htmlspecialchars($s5['title']) ?></h2>
      <p class="text-xs sm:text-sm text-gray-600"><?= htmlspecialchars($s5['subtitle']) ?></p>
    </div>

    <!-- Artisan Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php foreach ($active_artisans as $artisan): ?>
      <div class="bg-stone-50 rounded-2xl p-6 border border-stone-200/90 shadow-xs flex flex-col justify-between text-center hover:shadow-md transition-shadow">
        <div>
          <div class="w-20 h-20 rounded-full overflow-hidden bg-amber-100 border-2 border-amber-400 mx-auto mb-3 shadow-sm">
            <img src="<?= htmlspecialchars($artisan['avatar'] ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= htmlspecialchars($artisan['business_name'] ?? $artisan['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="80" height="80">
          </div>
          <div>
            <h3 class="font-extrabold text-sm text-gray-900"><?= htmlspecialchars($artisan['business_name'] ?? $artisan['name']) ?></h3>
            <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-bold mt-1">
              📍 <?= htmlspecialchars($artisan['village'] ?? 'Sandila') ?>, <?= htmlspecialchars($artisan['district'] ?? 'Hardoi') ?>
            </span>
            <p class="text-[11px] text-gray-600 mt-2 line-clamp-3 leading-relaxed">
              <?= htmlspecialchars($artisan['story'] ?? 'Generational master blacksmith crafting hand-forged agricultural tools from tempered leaf-spring steel.') ?>
            </p>
          </div>
        </div>

        <div class="pt-4 border-t border-stone-200 mt-4">
          <a href="/artisans.php" class="text-xs font-bold text-brand-green hover:underline flex items-center justify-center gap-1">
            <span>View Workshop</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($s5['btn1_show'] === '1' && trim($s5['btn1_text']) !== ''): ?>
    <div class="text-center mt-8">
      <a href="<?= htmlspecialchars($s5['btn1_link']) ?>" class="inline-flex items-center gap-2 px-7 py-3 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition">
        <span><?= htmlspecialchars($s5['btn1_text']) ?></span>
        <i class="fa-solid fa-arrow-right text-xs"></i>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 6: TRUST & FINAL CTA (CLEAN IMPACT GUARANTEE)                     -->
<!-- ========================================================================= -->
<?php if ($sections[6]['enabled'] === '1'): 
  $s6 = $sections[6];
?>
<section id="v6-sec-6" class="py-12 sm:py-16 bg-stone-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- 4 Trust Badges -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-10">
      <div class="bg-white rounded-2xl p-5 text-center border border-stone-200 shadow-xs">
        <i class="fa-solid fa-shield-halved text-2xl text-emerald-600 mx-auto mb-2 block"></i>
        <h4 class="font-bold text-xs text-gray-900">100% Village Authentic</h4>
        <p class="text-[10px] text-gray-500 mt-0.5">Directly forged and harvested in rural Bharat</p>
      </div>

      <div class="bg-white rounded-2xl p-5 text-center border border-stone-200 shadow-xs">
        <i class="fa-solid fa-truck-fast text-2xl text-amber-600 mx-auto mb-2 block"></i>
        <h4 class="font-bold text-xs text-gray-900">India Post Express</h4>
        <p class="text-[10px] text-gray-500 mt-0.5">Delivered to 19,000+ PIN codes across India</p>
      </div>

      <div class="bg-white rounded-2xl p-5 text-center border border-stone-200 shadow-xs">
        <i class="fa-solid fa-hand-holding-dollar text-2xl text-yellow-600 mx-auto mb-2 block"></i>
        <h4 class="font-bold text-xs text-gray-900">Direct Fair Livelihood</h4>
        <p class="text-[10px] text-gray-500 mt-0.5">0% middlemen cut directly to maker's account</p>
      </div>

      <div class="bg-white rounded-2xl p-5 text-center border border-stone-200 shadow-xs">
        <i class="fa-solid fa-rotate-left text-2xl text-rose-600 mx-auto mb-2 block"></i>
        <h4 class="font-bold text-xs text-gray-900">7-Day Easy Replacement</h4>
        <p class="text-[10px] text-gray-500 mt-0.5">100% genuine village craft satisfaction</p>
      </div>
    </div>

    <!-- Final Call-to-Action Card -->
    <div class="bg-gradient-to-r from-stone-900 via-stone-850 to-emerald-950 text-white rounded-3xl p-8 sm:p-12 text-center max-w-4xl mx-auto space-y-4 border border-stone-800 shadow-xl">
      <?php if (!empty($s6['eyebrow'])): ?>
      <span class="inline-block px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 font-bold text-xs">
        <?= htmlspecialchars($s6['eyebrow']) ?>
      </span>
      <?php endif; ?>

      <h2 class="text-2xl sm:text-4xl font-black font-display text-white">
        <?= htmlspecialchars($s6['title']) ?>
      </h2>

      <p class="text-xs sm:text-base text-stone-300 max-w-xl mx-auto leading-relaxed">
        <?= htmlspecialchars($s6['subtitle']) ?>
      </p>

      <?php
      $s6_b1_show = ($s6['btn1_show'] === '1' && trim($s6['btn1_text']) !== '');
      $s6_b2_show = ($s6['btn2_show'] === '1' && trim($s6['btn2_text']) !== '');
      $s6_b1_target = ($s6['btn1_target'] === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
      $s6_b2_target = ($s6['btn2_target'] === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
      ?>
      <?php if ($s6_b1_show || $s6_b2_show): ?>
      <div class="flex flex-wrap items-center justify-center gap-3.5 pt-3">
        <?php if ($s6_b1_show): ?>
        <a href="<?= htmlspecialchars($s6['btn1_link']) ?>"<?= $s6_b1_target ?>
           class="px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-95 flex items-center gap-2 cursor-pointer">
          <i class="fa-solid fa-bag-shopping text-sm"></i>
          <span><?= htmlspecialchars($s6['btn1_text']) ?></span>
        </a>
        <?php endif; ?>

        <?php if ($s6_b2_show): ?>
        <a href="<?= htmlspecialchars($s6['btn2_link']) ?>"<?= $s6_b2_target ?>
           class="px-7 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2 shadow-lg">
          <i class="fa-brands fa-whatsapp text-sm"></i>
          <span><?= htmlspecialchars($s6['btn2_text']) ?></span>
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: YouTube IFrame API for Hero Section ONLY                      -->
<!-- ========================================================================= -->
<script>
(function() {
  'use strict';

  var heroSec = document.getElementById('v6-sec-1');
  if (!heroSec) return;

  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  var isDataSaver = conn && (conn.saveData === true || conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g');

  if (prefersReducedMotion || (<?= $v6_data_saver ? 'true' : 'false' ?> && isDataSaver)) {
    // Only poster image displayed; zero YouTube requests made
    return;
  }

  var heroPlayer = null;
  var ytApiReady = false;

  window.toggleHeroAudio = function() {
    var btn = document.getElementById('v6-audio-toggle');
    if (!heroPlayer || typeof heroPlayer.isMuted !== 'function') return;
    try {
      if (heroPlayer.isMuted()) {
        heroPlayer.unMute();
        heroPlayer.setVolume(85);
        if (btn) btn.innerHTML = '<i class="fa-solid fa-volume-high text-amber-400"></i> <span>Mute Audio</span>';
      } else {
        heroPlayer.mute();
        if (btn) btn.innerHTML = '<i class="fa-solid fa-volume-xmark text-amber-400"></i> <span>Sound</span>';
      }
    } catch(e) {}
  };

  function initHeroPlayer() {
    if (!heroSec) return;
    var source = heroSec.getAttribute('data-source') || 'youtube';
    var wrap = heroSec.querySelector('.vh-hero-player-wrap');
    if (!wrap) return;

    if (source === 'mp4') {
      var mp4Url = heroSec.getAttribute('data-mp4');
      if (!mp4Url) return;
      var vid = document.createElement('video');
      vid.src = mp4Url;
      vid.muted = true;
      vid.loop = true;
      vid.autoplay = true;
      vid.playsInline = true;
      vid.setAttribute('playsinline', '');
      vid.setAttribute('aria-hidden', 'true');
      vid.onplaying = function() {
        wrap.classList.add('vh-loaded');
      };
      wrap.appendChild(vid);
      return;
    }

    // YouTube Source
    var ytId = heroSec.getAttribute('data-yt');
    if (!ytId || !window.YT || !window.YT.Player) return;

    var startSec = parseInt(heroSec.getAttribute('data-start') || '0', 10);
    var endSec = parseInt(heroSec.getAttribute('data-end') || '0', 10);

    var mountDiv = document.createElement('div');
    wrap.appendChild(mountDiv);

    var playerVars = {
      autoplay: 1,
      mute: 1,
      controls: 0,
      loop: 1,
      playlist: ytId,
      playsinline: 1,
      modestbranding: 1,
      rel: 0,
      disablekb: 1,
      iv_load_policy: 3,
      fs: 0,
      cc_load_policy: 0,
      enablejsapi: 1,
      origin: window.location.origin
    };
    if (startSec > 0) playerVars.start = startSec;
    if (endSec > 0) playerVars.end = endSec;

    heroPlayer = new YT.Player(mountDiv, {
      host: 'https://www.youtube-nocookie.com',
      videoId: ytId,
      playerVars: playerVars,
      events: {
        onReady: function(event) {
          try {
            event.target.mute();
            if (typeof event.target.setPlaybackQuality === 'function') {
              event.target.setPlaybackQuality('hd720');
            }
            event.target.playVideo();
          } catch(e){}
        },
        onStateChange: function(event) {
          // 1 = PLAYING: Fade in iframe & smoothly transition from poster
          if (event.data === YT.PlayerState.PLAYING) {
            wrap.classList.add('vh-loaded');
          }
          // 0 = ENDED: Loop glitch fix
          if (event.data === YT.PlayerState.ENDED) {
            try {
              event.target.seekTo(startSec || 0);
              event.target.playVideo();
            } catch(e){}
          }
        }
      }
    });
  }

  window.onYouTubeIframeAPIReady = function() {
    ytApiReady = true;
    initHeroPlayer();
  };

  function loadYouTubeScript() {
    if (window.YT && window.YT.Player) {
      ytApiReady = true;
      initHeroPlayer();
      return;
    }
    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    tag.async = true;
    document.head.appendChild(tag);
  }

  if (document.readyState === 'complete') {
    loadYouTubeScript();
  } else {
    window.addEventListener('load', loadYouTubeScript);
  }

  // Pause video when tab is hidden to conserve resources
  document.addEventListener('visibilitychange', function() {
    if (!heroPlayer) return;
    try {
      if (document.hidden) {
        if (typeof heroPlayer.pauseVideo === 'function') heroPlayer.pauseVideo();
      } else {
        if (typeof heroPlayer.playVideo === 'function') heroPlayer.playVideo();
      }
    } catch(e){}
  });

})();
</script>
