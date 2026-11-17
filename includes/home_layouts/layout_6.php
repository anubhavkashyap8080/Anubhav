<?php
/**
 * Layout 6: "Video Story Home"
 * Cinematic Full-Screen Edge-to-Edge Background Videos (YouTube nocookie + MP4)
 * Muted, looping, zero visible controls, lazy IntersectionObserver lifecycle,
 * transparent header variant, glass-morphism cards, and mobile data-saver fallback.
 */

require_once __DIR__ . '/../video_helpers.php';

// Check global options
$v6_dot_nav     = get_setting('v6_dot_nav', '1') === '1';
$v6_scroll_snap = get_setting('v6_scroll_snap', '1') === '1';
$v6_data_saver  = get_setting('v6_save_data_pause', '1') === '1';

// Load configurations for all 6 sections
$sections = [];
for ($i = 1; $i <= 6; $i++) {
    $sections[$i] = ggh_v6_get_section_config($i);
}

// Fetch products for Sections 3 & 4
try {
    // S3: Bestsellers / Popular
    $s3_limit = $sections[3]['prod_limit'] ?? 8;
    $s3_stmt = $pdo->prepare("
        SELECT p.*, v.business_name as vendor_business_name, v.village as vendor_village
        FROM products p 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        WHERE p.status = 'active'
        ORDER BY p.is_bestseller DESC, p.views_count DESC, p.id DESC 
        LIMIT ?
    ");
    $s3_stmt->bindValue(1, $s3_limit, PDO::PARAM_INT);
    $s3_stmt->execute();
    $s3_products = $s3_stmt->fetchAll();

    // S4: Featured / Spotlight
    $s4_limit = $sections[4]['prod_limit'] ?? 5;
    $s4_stmt = $pdo->prepare("
        SELECT p.*, v.business_name as vendor_business_name, v.village as vendor_village
        FROM products p 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        WHERE p.status = 'active'
        ORDER BY p.is_featured DESC, p.rating DESC, p.id DESC 
        LIMIT ?
    ");
    $s4_stmt->bindValue(1, $s4_limit, PDO::PARAM_INT);
    $s4_stmt->execute();
    $s4_products = $s4_stmt->fetchAll();
} catch (Exception $e) {
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
/* Layout 6: Video Story Home Base Styles */
html {
  <?= $v6_scroll_snap ? 'scroll-snap-type: y proximity;' : '' ?>
  scroll-behavior: smooth;
}

.vh-sec {
  position: relative;
  width: 100%;
  min-height: 100vh;
  min-height: 100svh;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  <?= $v6_scroll_snap ? 'scroll-snap-align: start;' : '' ?>
  background-color: #0c0a09;
}

/* Background Media & Video Layer */
.vh-media {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
  z-index: 1;
}

.vh-poster {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 2;
}

.vh-player-wrap {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
  opacity: 0;
  transition: opacity 0.8s ease-in-out;
}

.vh-player-wrap.vh-loaded {
  opacity: 1;
}

/* Cover scaling: forces 16:9 video to fill 100% of viewport without letterboxing */
.vh-player-wrap iframe,
.vh-player-wrap video {
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

/* Dark Gradient Overlay for optimal text contrast (WCAG AAA) */
.vh-overlay {
  position: absolute;
  inset: 0;
  z-index: 3;
  pointer-events: none;
}

/* Content Container on Top of Video */
.vh-content {
  position: relative;
  z-index: 4;
  width: 100%;
  max-width: 80rem;
  margin-left: auto;
  margin-right: auto;
  padding: 6rem 1rem 4rem 1rem;
}

@media (min-width: 640px) {
  .vh-content {
    padding: 7rem 1.5rem 5rem 1.5rem;
  }
}

/* Glass-morphism Cards with Solid Color Fallbacks */
.vh-glass {
  background: rgba(18, 18, 18, 0.78);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
}

.vh-glass-card {
  background: rgba(28, 25, 23, 0.72);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.vh-glass-card:hover {
  transform: translateY(-4px);
  border-color: rgba(245, 158, 11, 0.4);
  box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.6);
}

/* Floating Right Dot Navigation */
.vh-dots-nav {
  position: fixed;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  z-index: 45;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.vh-dot {
  width: 11px;
  height: 11px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.6);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.vh-dot:hover {
  background: rgba(245, 158, 11, 0.8);
  transform: scale(1.3);
}

.vh-dot.active {
  background: #f59e0b;
  box-shadow: 0 0 12px #f59e0b;
  transform: scale(1.4);
  border-color: #ffffff;
}

@media (max-width: 768px) {
  .vh-dots-nav {
    display: none;
  }
}

/* Smooth Horizontal Snap Scroll for Rails */
.vh-snap-rail {
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: rgba(245, 158, 11, 0.4) transparent;
  padding-bottom: 1rem;
}

.vh-snap-item {
  scroll-snap-align: start;
  flex-shrink: 0;
}
</style>

<!-- Floating Right-Side Dot Navigation -->
<?php if ($v6_dot_nav): ?>
<nav class="vh-dots-nav" aria-label="Section Navigation">
  <?php for ($i = 1; $i <= 6; $i++): 
    if ($sections[$i]['enabled'] !== '1') continue;
  ?>
  <a href="#v6-sec-<?= $i ?>" class="vh-dot" data-sec="<?= $i ?>" aria-label="Jump to Section <?= $i ?>" title="Section <?= $i ?>"></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 1: HERO STORY BANNER (Video 1)                                    -->
<!-- ========================================================================= -->
<?php if ($sections[1]['enabled'] === '1'): 
  $s1 = $sections[1];
  $s1_yt = $s1['yt_id'];
?>
<section id="v6-sec-1" class="vh-sec" 
  data-source="<?= htmlspecialchars($s1['source']) ?>" 
  data-yt="<?= htmlspecialchars($s1_yt) ?>" 
  data-mp4="<?= $s1['source'] === 'mp4' ? htmlspecialchars($s1['video']) : '' ?>"
  data-start="<?= $s1['start'] ?>" 
  data-end="<?= $s1['end'] ?>">

  <!-- Video Background & High-Priority Poster -->
  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s1['poster']) ?>" alt="Village Craft Heritage" fetchpriority="high" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <!-- Ambient Dark Gradient Overlay -->
  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,<?= $s1['overlay']/100 * 0.9 ?>) 0%, rgba(12,10,9,<?= $s1['overlay']/100 ?>) 50%, rgba(12,10,9,<?= min(0.95, $s1['overlay']/100 * 1.2) ?>) 100%);"></div>

  <!-- Hero Content -->
  <div class="vh-content text-<?= $s1['align'] ?> text-white">
    <div class="max-w-3xl <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?> space-y-5">
      
      <!-- Eyebrow Pill -->
      <?php if (!empty($s1['eyebrow'])): ?>
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold tracking-wide shadow-xs backdrop-blur-md">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
        <span><?= htmlspecialchars($s1['eyebrow']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Real H1 Heading -->
      <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-display text-white leading-tight tracking-tight drop-shadow-md">
        <?= htmlspecialchars($s1['title']) ?>
      </h1>

      <!-- Subtitle Description -->
      <p class="text-sm sm:text-lg text-stone-200 leading-relaxed font-medium drop-shadow-sm max-w-2xl <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?>">
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
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>

        <?php if ($s1_b2_show): ?>
        <a href="<?= htmlspecialchars($s1['btn2_link']) ?>"<?= $s1_b2_target ?>
           class="px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-2xl border border-white/25 transition-all backdrop-blur-md flex items-center gap-2">
          <span><?= htmlspecialchars($s1['btn2_text']) ?></span>
        </a>
        <?php endif; ?>

        <button type="button" onclick="detectGPSLocation()" class="px-5 py-3.5 bg-stone-900/80 hover:bg-stone-900 text-amber-300 font-bold text-xs sm:text-sm rounded-2xl border border-amber-400/30 transition-all flex items-center gap-2 cursor-pointer">
          <i data-lucide="crosshair" class="w-4 h-4 text-amber-400"></i>
          <span>Artisans Near Me</span>
        </button>
      </div>
      <?php endif; ?>

      <!-- Trust Badges Strip -->
      <div class="grid grid-cols-3 gap-3 pt-6 border-t border-white/15 max-w-md <?= $s1['align'] === 'center' ? 'mx-auto' : '' ?>">
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
          <span class="text-[11px] text-stone-300 font-medium"><?= htmlspecialchars(get_setting('hero_stat3_label', 'Direct Artisan Fee')) ?></span>
        </div>
      </div>

    </div>

    <!-- Scroll Down Hint Indicator -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-1.5 opacity-80 hover:opacity-100 transition cursor-pointer" onclick="document.getElementById('v6-sec-2')?.scrollIntoView({behavior:'smooth'})">
      <span class="text-[10px] font-bold uppercase tracking-wider text-stone-300">Explore Story</span>
      <i data-lucide="chevron-down" class="w-4 h-4 text-amber-400 animate-bounce"></i>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 2: ARTISANAL CATEGORIES (Video 2)                                 -->
<!-- ========================================================================= -->
<?php if ($sections[2]['enabled'] === '1'): 
  $s2 = $sections[2];
  $s2_yt = $s2['yt_id'];
?>
<section id="v6-sec-2" class="vh-sec" 
  data-source="<?= htmlspecialchars($s2['source']) ?>" 
  data-yt="<?= htmlspecialchars($s2_yt) ?>" 
  data-mp4="<?= $s2['source'] === 'mp4' ? htmlspecialchars($s2['video']) : '' ?>"
  data-start="<?= $s2['start'] ?>" 
  data-end="<?= $s2['end'] ?>">

  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s2['poster']) ?>" alt="Categories Background" loading="lazy" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,0.7) 0%, rgba(12,10,9,<?= $s2['overlay']/100 ?>) 50%, rgba(12,10,9,0.85) 100%);"></div>

  <div class="vh-content text-white">
    <!-- Header -->
    <div class="text-<?= $s2['align'] ?> max-w-2xl <?= $s2['align'] === 'center' ? 'mx-auto' : '' ?> mb-8 space-y-2">
      <?php if (!empty($s2['eyebrow'])): ?>
      <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block"><?= htmlspecialchars($s2['eyebrow']) ?></span>
      <?php endif; ?>
      <h2 class="text-2xl sm:text-4xl font-black font-display text-white"><?= htmlspecialchars($s2['title']) ?></h2>
      <p class="text-xs sm:text-sm text-stone-300"><?= htmlspecialchars($s2['subtitle']) ?></p>
    </div>

    <!-- Category Tiles Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
      <?php foreach ($active_categories as $cat): ?>
      <a href="/products.php?category=<?= urlencode($cat['slug']) ?>" 
         class="vh-glass-card rounded-2xl p-4 flex flex-col items-center text-center group cursor-pointer block">
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-amber-500/10 border border-amber-400/20 p-2 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform overflow-hidden">
          <?php if (!empty($cat['image_url'])): ?>
            <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover rounded-xl" loading="lazy" width="80" height="80">
          <?php else: ?>
            <i data-lucide="package" class="w-8 h-8 text-amber-400"></i>
          <?php endif; ?>
        </div>
        <h3 class="font-extrabold text-xs sm:text-sm text-white group-hover:text-amber-400 transition-colors line-clamp-1">
          <?= htmlspecialchars($cat['name']) ?>
        </h3>
        <span class="text-[10px] text-stone-400 mt-0.5">Explore Guild</span>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($s2['btn1_show'] === '1' && trim($s2['btn1_text']) !== ''): ?>
    <div class="text-center mt-8">
      <a href="<?= htmlspecialchars($s2['btn1_link']) ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
        <span><?= htmlspecialchars($s2['btn1_text']) ?></span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 3: BESTSELLERS RAIL (Video 3)                                     -->
<!-- ========================================================================= -->
<?php if ($sections[3]['enabled'] === '1'): 
  $s3 = $sections[3];
  $s3_yt = $s3['yt_id'];
?>
<section id="v6-sec-3" class="vh-sec" 
  data-source="<?= htmlspecialchars($s3['source']) ?>" 
  data-yt="<?= htmlspecialchars($s3_yt) ?>" 
  data-mp4="<?= $s3['source'] === 'mp4' ? htmlspecialchars($s3['video']) : '' ?>"
  data-start="<?= $s3['start'] ?>" 
  data-end="<?= $s3['end'] ?>">

  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s3['poster']) ?>" alt="Bestsellers Background" loading="lazy" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,0.7) 0%, rgba(12,10,9,<?= $s3['overlay']/100 ?>) 50%, rgba(12,10,9,0.85) 100%);"></div>

  <div class="vh-content text-white">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 gap-3">
      <div>
        <?php if (!empty($s3['eyebrow'])): ?>
        <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block"><?= htmlspecialchars($s3['eyebrow']) ?></span>
        <?php endif; ?>
        <h2 class="text-2xl sm:text-4xl font-black font-display text-white"><?= htmlspecialchars($s3['title']) ?></h2>
        <p class="text-xs sm:text-sm text-stone-300 mt-1"><?= htmlspecialchars($s3['subtitle']) ?></p>
      </div>

      <?php if ($s3['btn1_show'] === '1' && trim($s3['btn1_text']) !== ''): ?>
      <a href="<?= htmlspecialchars($s3['btn1_link']) ?>" class="text-xs font-bold text-amber-400 hover:text-amber-300 flex items-center gap-1.5 shrink-0">
        <span><?= htmlspecialchars($s3['btn1_text']) ?></span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
      <?php endif; ?>
    </div>

    <!-- Horizontal Snap Scroll Rail -->
    <div class="vh-snap-rail gap-4">
      <?php foreach ($s3_products as $p): ?>
      <div class="vh-snap-item w-60 sm:w-68 vh-glass-card rounded-2xl p-3.5 flex flex-col justify-between">
        <div>
          <!-- Product Image -->
          <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-stone-900 mb-3">
            <img src="<?= htmlspecialchars($p['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover transition-transform group-hover:scale-105" loading="lazy" width="260" height="260">
            <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[9px] rounded-md uppercase tracking-wider">Bestseller</span>
            <button type="button" onclick="toggleWishlist(<?= $p['id'] ?>, this)" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-stone-950/60 text-white hover:text-rose-400 flex items-center justify-center transition">
              <i data-lucide="heart" class="w-3.5 h-3.5"></i>
            </button>
          </div>

          <span class="text-[10px] text-amber-400 font-bold block mb-0.5 line-clamp-1"><?= htmlspecialchars($p['vendor_business_name'] ?? 'Rural Artisan') ?></span>
          <h3 class="font-bold text-xs sm:text-sm text-white line-clamp-2 leading-tight">
            <a href="/product.php?id=<?= $p['id'] ?>" class="hover:text-amber-300 transition-colors"><?= htmlspecialchars($p['name']) ?></a>
          </h3>
        </div>

        <div class="pt-3 border-t border-white/10 mt-3 flex items-center justify-between">
          <div>
            <span class="text-sm font-black text-white"><?= format_price($p['price']) ?></span>
            <?php if (!empty($p['mrp']) && $p['mrp'] > $p['price']): ?>
              <span class="text-[10px] text-stone-400 line-through ml-1"><?= format_price($p['mrp']) ?></span>
            <?php endif; ?>
          </div>
          <button type="button" onclick="addToCart(<?= $p['id'] ?>, 1, this)" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-lg transition active:scale-95 flex items-center gap-1 cursor-pointer">
            <i data-lucide="shopping-bag" class="w-3 h-3"></i>
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
<!-- SECTION 4: CURATED SPOTLIGHT (Asymmetric Grid, Video 4)                   -->
<!-- ========================================================================= -->
<?php if ($sections[4]['enabled'] === '1'): 
  $s4 = $sections[4];
  $s4_yt = $s4['yt_id'];
  $spotlight_item = $s4_products[0] ?? null;
  $grid_items = array_slice($s4_products, 1, 4);
?>
<section id="v6-sec-4" class="vh-sec" 
  data-source="<?= htmlspecialchars($s4['source']) ?>" 
  data-yt="<?= htmlspecialchars($s4_yt) ?>" 
  data-mp4="<?= $s4['source'] === 'mp4' ? htmlspecialchars($s4['video']) : '' ?>"
  data-start="<?= $s4['start'] ?>" 
  data-end="<?= $s4['end'] ?>">

  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s4['poster']) ?>" alt="Spotlight Background" loading="lazy" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,0.7) 0%, rgba(12,10,9,<?= $s4['overlay']/100 ?>) 50%, rgba(12,10,9,0.9) 100%);"></div>

  <div class="vh-content text-white">
    <div class="mb-6 space-y-1">
      <?php if (!empty($s4['eyebrow'])): ?>
      <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block"><?= htmlspecialchars($s4['eyebrow']) ?></span>
      <?php endif; ?>
      <h2 class="text-2xl sm:text-4xl font-black font-display text-white"><?= htmlspecialchars($s4['title']) ?></h2>
      <p class="text-xs sm:text-sm text-stone-300"><?= htmlspecialchars($s4['subtitle']) ?></p>
    </div>

    <!-- Asymmetric Spotlight Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
      <!-- Left Spotlight Hero Card (5 cols) -->
      <?php if ($spotlight_item): ?>
      <div class="lg:col-span-5 vh-glass rounded-3xl p-6 sm:p-7 flex flex-col justify-between border-amber-400/30">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2.5 py-1 rounded-full bg-amber-500 text-stone-950 font-black text-[10px] uppercase tracking-wider">
              Artisan Master Spotlight
            </span>
            <span class="text-xs text-amber-300 font-bold flex items-center gap-1">
              <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
              <?= number_format($spotlight_item['rating'] ?? 5.0, 1) ?> Rating
            </span>
          </div>

          <div class="w-full aspect-video rounded-2xl overflow-hidden bg-stone-900 mb-4">
            <img src="<?= htmlspecialchars($spotlight_item['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($spotlight_item['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="480" height="270">
          </div>

          <span class="text-xs text-amber-400 font-semibold block mb-1">
            Village Workshop: <?= htmlspecialchars($spotlight_item['vendor_business_name'] ?? 'Hardoi Forge') ?>
          </span>
          <h3 class="text-lg sm:text-xl font-black text-white leading-snug">
            <?= htmlspecialchars($spotlight_item['name']) ?>
          </h3>
          <p class="text-xs text-stone-300 mt-2 line-clamp-3 leading-relaxed">
            <?= htmlspecialchars($spotlight_item['description'] ?? 'Generational master craft forged with railway leaf-spring carbon steel. Built for unmatched field durability.') ?>
          </p>
        </div>

        <div class="pt-5 border-t border-white/10 mt-5 flex items-center justify-between">
          <div>
            <span class="text-2xl font-black text-white"><?= format_price($spotlight_item['price']) ?></span>
            <span class="text-xs text-stone-400 block">Free Express Shipping</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" onclick="addToCart(<?= $spotlight_item['id'] ?>, 1, this)" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl shadow-lg transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
              <i data-lucide="shopping-bag" class="w-4 h-4"></i>
              <span>Add to Cart</span>
            </button>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Right 4 Smaller Grid Cards (7 cols) -->
      <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($grid_items as $item): ?>
        <div class="vh-glass-card rounded-2xl p-4 flex flex-col justify-between">
          <div class="flex items-start gap-3">
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-stone-900 shrink-0">
              <img src="<?= htmlspecialchars($item['image_url'] ?? '/placeholder.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="80" height="80">
            </div>
            <div>
              <span class="text-[10px] text-amber-400 font-bold block"><?= htmlspecialchars($item['vendor_business_name'] ?? 'Artisan Guild') ?></span>
              <h4 class="font-bold text-xs sm:text-sm text-white line-clamp-2 leading-tight mt-0.5">
                <a href="/product.php?id=<?= $item['id'] ?>" class="hover:text-amber-300"><?= htmlspecialchars($item['name']) ?></a>
              </h4>
              <div class="text-xs font-black text-white mt-1"><?= format_price($item['price']) ?></div>
            </div>
          </div>
          <div class="pt-2.5 border-t border-white/10 mt-3 flex items-center justify-between">
            <a href="/product.php?id=<?= $item['id'] ?>" class="text-[11px] text-stone-300 hover:text-white flex items-center gap-1">
              <span>Details</span> <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </a>
            <button type="button" onclick="addToCart(<?= $item['id'] ?>, 1, this)" class="px-3 py-1 bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-stone-950 font-bold text-xs rounded-lg transition active:scale-95 cursor-pointer">
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
<!-- SECTION 5: ARTISANS & VILLAGE STORY (Video 5)                             -->
<!-- ========================================================================= -->
<?php if ($sections[5]['enabled'] === '1'): 
  $s5 = $sections[5];
  $s5_yt = $s5['yt_id'];
?>
<section id="v6-sec-5" class="vh-sec" 
  data-source="<?= htmlspecialchars($s5['source']) ?>" 
  data-yt="<?= htmlspecialchars($s5_yt) ?>" 
  data-mp4="<?= $s5['source'] === 'mp4' ? htmlspecialchars($s5['video']) : '' ?>"
  data-start="<?= $s5['start'] ?>" 
  data-end="<?= $s5['end'] ?>">

  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s5['poster']) ?>" alt="Artisans Background" loading="lazy" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,0.7) 0%, rgba(12,10,9,<?= $s5['overlay']/100 ?>) 50%, rgba(12,10,9,0.9) 100%);"></div>

  <div class="vh-content text-white">
    <div class="text-<?= $s5['align'] ?> max-w-2xl <?= $s5['align'] === 'center' ? 'mx-auto' : '' ?> mb-8 space-y-2">
      <?php if (!empty($s5['eyebrow'])): ?>
      <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block"><?= htmlspecialchars($s5['eyebrow']) ?></span>
      <?php endif; ?>
      <h2 class="text-2xl sm:text-4xl font-black font-display text-white"><?= htmlspecialchars($s5['title']) ?></h2>
      <p class="text-xs sm:text-sm text-stone-300"><?= htmlspecialchars($s5['subtitle']) ?></p>
    </div>

    <!-- Overlapping Artisan Portrait Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($active_artisans as $artisan): ?>
      <div class="vh-glass-card rounded-2xl p-5 flex flex-col justify-between">
        <div>
          <div class="w-20 h-20 rounded-full overflow-hidden bg-amber-500/20 border-2 border-amber-400/40 mx-auto mb-3 shadow-lg">
            <img src="<?= htmlspecialchars($artisan['avatar'] ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= htmlspecialchars($artisan['business_name'] ?? $artisan['name']) ?>" class="w-full h-full object-cover" loading="lazy" width="80" height="80">
          </div>
          <div class="text-center">
            <h3 class="font-bold text-sm text-white"><?= htmlspecialchars($artisan['business_name'] ?? $artisan['name']) ?></h3>
            <span class="inline-block px-2 py-0.5 rounded-md bg-stone-900 text-amber-300 text-[10px] font-bold mt-1">
              📍 <?= htmlspecialchars($artisan['village'] ?? 'Sandila') ?>, <?= htmlspecialchars($artisan['district'] ?? 'Hardoi') ?>
            </span>
            <p class="text-[11px] text-stone-300 mt-2 line-clamp-3 leading-relaxed">
              <?= htmlspecialchars($artisan['story'] ?? 'Generational blacksmith crafting hand-forged sickles, khurpis, and spades from tempered leaf-spring steel.') ?>
            </p>
          </div>
        </div>

        <div class="pt-3 border-t border-white/10 mt-4 text-center">
          <a href="/artisans.php" class="text-xs font-bold text-amber-400 hover:text-amber-300 flex items-center justify-center gap-1">
            <span>View Workshop</span> <i data-lucide="arrow-right" class="w-3 h-3"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($s5['btn1_show'] === '1' && trim($s5['btn1_text']) !== ''): ?>
    <div class="text-center mt-8">
      <a href="<?= htmlspecialchars($s5['btn1_link']) ?>" class="inline-flex items-center gap-2 px-7 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl shadow-lg transition">
        <span><?= htmlspecialchars($s5['btn1_text']) ?></span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECTION 6: TRUST & FINAL CTA (Video 6)                                    -->
<!-- ========================================================================= -->
<?php if ($sections[6]['enabled'] === '1'): 
  $s6 = $sections[6];
  $s6_yt = $s6['yt_id'];
?>
<section id="v6-sec-6" class="vh-sec" 
  data-source="<?= htmlspecialchars($s6['source']) ?>" 
  data-yt="<?= htmlspecialchars($s6_yt) ?>" 
  data-mp4="<?= $s6['source'] === 'mp4' ? htmlspecialchars($s6['video']) : '' ?>"
  data-start="<?= $s6['start'] ?>" 
  data-end="<?= $s6['end'] ?>">

  <div class="vh-media" aria-hidden="true">
    <img class="vh-poster" src="<?= htmlspecialchars($s6['poster']) ?>" alt="Trust Background" loading="lazy" width="1920" height="1080">
    <div class="vh-player-wrap"></div>
  </div>

  <div class="vh-overlay" style="background: linear-gradient(180deg, rgba(12,10,9,0.75) 0%, rgba(12,10,9,<?= $s6['overlay']/100 ?>) 50%, rgba(12,10,9,0.95) 100%);"></div>

  <div class="vh-content text-white">
    <!-- 4 Trust Badges -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-10">
      <div class="vh-glass-card rounded-2xl p-4 text-center">
        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-400 mx-auto mb-2"></i>
        <h4 class="font-bold text-xs text-white">100% Village Authentic</h4>
        <p class="text-[10px] text-stone-400 mt-0.5">Forged and harvested in rural Bharat</p>
      </div>

      <div class="vh-glass-card rounded-2xl p-4 text-center">
        <i data-lucide="truck" class="w-6 h-6 text-amber-400 mx-auto mb-2"></i>
        <h4 class="font-bold text-xs text-white">India Post Express</h4>
        <p class="text-[10px] text-stone-400 mt-0.5">Delivered to 19,000+ PIN codes</p>
      </div>

      <div class="vh-glass-card rounded-2xl p-4 text-center">
        <i data-lucide="coins" class="w-6 h-6 text-yellow-400 mx-auto mb-2"></i>
        <h4 class="font-bold text-xs text-white">Direct Fair Livelihood</h4>
        <p class="text-[10px] text-stone-400 mt-0.5">0% middlemen commission</p>
      </div>

      <div class="vh-glass-card rounded-2xl p-4 text-center">
        <i data-lucide="rotate-ccw" class="w-6 h-6 text-rose-400 mx-auto mb-2"></i>
        <h4 class="font-bold text-xs text-white">Easy Replacement</h4>
        <p class="text-[10px] text-stone-400 mt-0.5">Dedicated customer support</p>
      </div>
    </div>

    <!-- Final Big Call-to-Action Card -->
    <div class="vh-glass rounded-3xl p-8 sm:p-12 text-center max-w-3xl mx-auto space-y-4 border-amber-500/30">
      <?php if (!empty($s6['eyebrow'])): ?>
      <span class="inline-block px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 font-bold text-xs">
        <?= htmlspecialchars($s6['eyebrow']) ?>
      </span>
      <?php endif; ?>

      <h2 class="text-2xl sm:text-4xl font-black font-display text-white">
        <?= htmlspecialchars($s6['title']) ?>
      </h2>

      <p class="text-xs sm:text-base text-stone-200 max-w-xl mx-auto leading-relaxed">
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
           class="px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-xl transition active:scale-95 flex items-center gap-2 cursor-pointer">
          <i data-lucide="shopping-bag" class="w-4 h-4"></i>
          <span><?= htmlspecialchars($s6['btn1_text']) ?></span>
        </a>
        <?php endif; ?>

        <?php if ($s6_b2_show): ?>
        <a href="<?= htmlspecialchars($s6['btn2_link']) ?>"<?= $s6_b2_target ?>
           class="px-7 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
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
<!-- JAVASCRIPT: YouTube IFrame API Lazy Observer Engine                       -->
<!-- Loaded ONCE, max 1-2 active players, poster-first fade-in, loop glitch fix -->
<!-- ========================================================================= -->
<script>
(function() {
  'use strict';

  // 1. Data-saver and accessibility checks
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  const isDataSaver = conn && (conn.saveData === true || conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g');

  if (prefersReducedMotion || (<?= $v6_data_saver ? 'true' : 'false' ?> && isDataSaver)) {
    // Only poster images shown; zero YouTube requests made
    return;
  }

  const sections = Array.from(document.querySelectorAll('.vh-sec'));
  if (!sections.length) return;

  const players = new Map(); // sectionEl -> { player, state, wrapEl }
  let ytApiReady = false;

  // Global YouTube API ready callback
  window.onYouTubeIframeAPIReady = function() {
    ytApiReady = true;
    initLazyObserver();
  };

  // Inject YouTube IFrame API script once after initial paint
  function loadYouTubeScript() {
    if (window.YT && window.YT.Player) {
      ytApiReady = true;
      initLazyObserver();
      return;
    }
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    tag.async = true;
    tag.onerror = function() {
      // Offline or blocked fallback: gracefully keep posters
    };
    document.head.appendChild(tag);
  }

  if (document.readyState === 'complete') {
    loadYouTubeScript();
  } else {
    window.addEventListener('load', loadYouTubeScript);
  }

  // Create YouTube player for a specific section
  function createPlayerForSection(secEl) {
    if (players.has(secEl)) return players.get(secEl);

    const source = secEl.getAttribute('data-source') || 'youtube';
    const wrap = secEl.querySelector('.vh-player-wrap');
    if (!wrap) return null;

    if (source === 'mp4') {
      const mp4Url = secEl.getAttribute('data-mp4');
      if (!mp4Url) return null;

      const vid = document.createElement('video');
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
      const entry = { type: 'mp4', element: vid, wrap: wrap };
      players.set(secEl, entry);
      return entry;
    }

    // YouTube Source
    const ytId = secEl.getAttribute('data-yt');
    if (!ytId || !ytApiReady || !window.YT || !window.YT.Player) return null;

    const startSec = parseInt(secEl.getAttribute('data-start') || '0', 10);
    const endSec = parseInt(secEl.getAttribute('data-end') || '0', 10);

    const mountDiv = document.createElement('div');
    wrap.appendChild(mountDiv);

    const playerVars = {
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

    const player = new YT.Player(mountDiv, {
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
          // 1 = PLAYING: Fade in iframe & hide poster flash
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

    const entry = { type: 'youtube', player: player, wrap: wrap };
    players.set(secEl, entry);
    return entry;
  }

  // Destroy player to free memory (ensuring max 1-2 players exist in DOM)
  function destroyPlayerForSection(secEl) {
    if (!players.has(secEl)) return;
    const entry = players.get(secEl);
    if (entry.type === 'youtube' && entry.player && typeof entry.player.destroy === 'function') {
      try { entry.player.destroy(); } catch(e){}
    } else if (entry.type === 'mp4' && entry.element) {
      entry.element.pause();
      entry.element.remove();
    }
    if (entry.wrap) {
      entry.wrap.classList.remove('vh-loaded');
      entry.wrap.innerHTML = '';
    }
    players.delete(secEl);
  }

  function initLazyObserver() {
    if (!('IntersectionObserver' in window)) {
      // Fallback: create players for all
      sections.forEach(createPlayerForSection);
      return;
    }

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        const secEl = entry.target;
        const playerEntry = players.get(secEl);

        if (entry.isIntersecting) {
          // Section in viewport -> Play
          if (!playerEntry) {
            createPlayerForSection(secEl);
          } else {
            if (playerEntry.type === 'youtube' && playerEntry.player && typeof playerEntry.player.playVideo === 'function') {
              try { playerEntry.player.playVideo(); } catch(e){}
            } else if (playerEntry.type === 'mp4' && playerEntry.element) {
              playerEntry.element.play();
            }
          }

          // Preload next section
          const idx = sections.indexOf(secEl);
          if (idx !== -1 && idx + 1 < sections.length) {
            const nextSec = sections[idx + 1];
            if (!players.has(nextSec)) {
              createPlayerForSection(nextSec);
            }
          }
        } else {
          // Off-screen -> Pause
          if (playerEntry) {
            if (playerEntry.type === 'youtube' && playerEntry.player && typeof playerEntry.player.pauseVideo === 'function') {
              try { playerEntry.player.pauseVideo(); } catch(e){}
            } else if (playerEntry.type === 'mp4' && playerEntry.element) {
              playerEntry.element.pause();
            }
          }

          // If more than 2 players alive, destroy far-away sections
          if (players.size > 2) {
            const currentIdx = sections.findIndex(s => {
              const r = s.getBoundingClientRect();
              return r.top < window.innerHeight && r.bottom > 0;
            });
            sections.forEach(function(s, i) {
              if (Math.abs(i - currentIdx) > 1) {
                destroyPlayerForSection(s);
              }
            });
          }
        }
      });
    }, { rootMargin: '200px 0px' });

    sections.forEach(s => observer.observe(s));
  }

  // Dot Navigation Highlight on Scroll
  const dots = Array.from(document.querySelectorAll('.vh-dot'));
  if (dots.length) {
    const dotObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          const num = id.replace('v6-sec-', '');
          dots.forEach(d => {
            if (d.getAttribute('data-sec') === num) {
              d.classList.add('active');
            } else {
              d.classList.remove('active');
            }
          });
        }
      });
    }, { threshold: 0.5 });

    sections.forEach(s => dotObserver.observe(s));
  }

  // Document visibility change: pause everything when tab is hidden
  document.addEventListener('visibilitychange', function() {
    const isHidden = document.hidden;
    players.forEach(function(entry) {
      if (entry.type === 'youtube' && entry.player) {
        try {
          if (isHidden) {
            if (typeof entry.player.pauseVideo === 'function') entry.player.pauseVideo();
          } else {
            if (typeof entry.player.playVideo === 'function') entry.player.playVideo();
          }
        } catch(e){}
      } else if (entry.type === 'mp4' && entry.element) {
        if (isHidden) entry.element.pause();
        else entry.element.play();
      }
    });
  });

})();
</script>
