<?php
/**
 * GaunGaliHat Homepage
 */

require_once __DIR__ . '/includes/header.php';

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
$categories = $stmt->fetchAll();

// Fetch Bestsellers & Featured Products
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug, v.name as vendor_name, v.location as vendor_location 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    WHERE p.stock > 0 
    ORDER BY p.is_best_seller DESC, p.id DESC 
    LIMIT 12
");
$featured_products = $stmt->fetchAll();

// Fetch Verified Artisan Workshops
$stmt = $pdo->query("SELECT * FROM vendors WHERE verified = 1 LIMIT 4");
$verified_vendors = $stmt->fetchAll();

// Dynamic Hero Settings
$hero_theme = get_setting('hero_theme_color', 'emerald');
$hero_bg_class = 'from-emerald-950 via-brand-green to-emerald-900';
$hero_badge_class = 'bg-emerald-800/80 border-emerald-600/60 text-amber-300';
$hero_accent_text = 'text-amber-300';

if ($hero_theme === 'crimson') {
    $hero_bg_class = 'from-rose-950 via-red-900 to-amber-950';
    $hero_badge_class = 'bg-red-950/80 border-amber-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
} elseif ($hero_theme === 'amber') {
    $hero_bg_class = 'from-amber-950 via-orange-900 to-amber-900';
    $hero_badge_class = 'bg-orange-950/80 border-yellow-400/60 text-yellow-300';
    $hero_accent_text = 'text-yellow-300';
} elseif ($hero_theme === 'indigo') {
    $hero_bg_class = 'from-slate-950 via-indigo-950 to-blue-950';
    $hero_badge_class = 'bg-indigo-900/80 border-indigo-500/60 text-amber-300';
    $hero_accent_text = 'text-amber-300';
}
?>

<!-- 1. Hero Banner Section (Fully Customisable for Festivals & Sales) -->
<section class="relative bg-gradient-to-br <?= $hero_bg_class ?> text-white overflow-hidden py-12 sm:py-20">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="absolute -top-24 -right-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-brand-orange/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      
      <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full <?= $hero_badge_class ?> border text-xs font-semibold shadow-xs">
          <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
          <span><?= htmlspecialchars(get_setting('hero_badge_text', '🌾 S S Industries Enterprise • Empowering Rural Artisans, Dal Farmers & SHGs')) ?></span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold font-display tracking-tight text-white leading-tight">
          <?= htmlspecialchars(get_setting('hero_title_line1', 'Gaon ke hunar,')) ?><br />
          <span class="<?= $hero_accent_text ?> drop-shadow-sm"><?= htmlspecialchars(get_setting('hero_title_line2', 'ab har ghar tak')) ?></span>
        </h1>

        <p class="text-sm sm:text-base text-emerald-100/90 max-w-xl mx-auto lg:mx-0 leading-relaxed">
          <?= htmlspecialchars(get_setting('hero_subtitle', 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.')) ?>
        </p>

        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
          <a href="<?= htmlspecialchars(get_setting('hero_btn1_link', '/products.php')) ?>"
             class="px-5 sm:px-6 py-3 sm:py-3.5 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg hover:shadow-orange-600/30 transition-all flex items-center gap-2 cursor-pointer active:scale-95">
            <span><?= htmlspecialchars(get_setting('hero_btn1_text', 'Explore Marketplace')) ?></span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="<?= htmlspecialchars(get_setting('hero_btn2_link', '/products.php?category=farming-traditional-tools')) ?>"
             class="px-5 sm:px-6 py-3 sm:py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs sm:text-sm rounded-xl transition-all flex items-center gap-2">
            <span><?= htmlspecialchars(get_setting('hero_btn2_text', 'Farming & Garden Tools')) ?></span>
          </a>
          <a href="/about.php" class="px-5 sm:px-6 py-3 sm:py-3.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold text-xs sm:text-sm rounded-xl transition-all flex items-center gap-1.5 shadow-md">
            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-950"></i>
            <span>Nearby Artisans (Map)</span>
          </a>
        </div>

        <!-- Badges -->
        <div class="grid grid-cols-3 gap-2 sm:gap-4 pt-4 border-t border-white/15 text-left max-w-md mx-auto lg:mx-0">
          <div>
            <div class="text-lg sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat1_num', '100%')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat1_label', 'Forged Iron & Pure Dal')) ?></div>
          </div>
          <div>
            <div class="text-lg sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat2_num', '40+')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat2_label', 'Village Artisan Hubs')) ?></div>
          </div>
          <div>
            <div class="text-lg sm:text-2xl font-bold <?= $hero_accent_text ?> font-display"><?= htmlspecialchars(get_setting('hero_stat3_num', '₹499+')) ?></div>
            <div class="text-[10px] sm:text-[11px] text-white/80"><?= htmlspecialchars(get_setting('hero_stat3_label', 'Free India Delivery')) ?></div>
          </div>
        </div>
      </div>

      <!-- Right Hero Graphic Visual Showcase Card -->
      <div class="lg:col-span-5 relative mt-6 lg:mt-0 px-2 sm:px-0">
        <div class="relative mx-auto max-w-sm rounded-3xl bg-white/10 backdrop-blur-md p-3 sm:p-4 border border-white/20 shadow-2xl">
          <img 
            src="<?= htmlspecialchars(get_setting('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>" 
            alt="GaunGaliHat Artisan & Village Showcase" 
            class="w-full h-72 sm:h-80 object-cover rounded-2xl shadow-md"
            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80';"
          />

          <!-- Floating Pill 1 (Top Left) -->
          <div class="absolute -top-3 left-2 sm:-top-4 sm:-left-4 bg-white text-gray-900 rounded-xl p-2.5 sm:p-3 shadow-xl border border-gray-100 flex items-center gap-2.5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-orange-100 text-brand-orange flex items-center justify-center font-bold text-sm shrink-0">
              ⚒️
            </div>
            <div class="min-w-0">
              <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium truncate"><?= htmlspecialchars(get_setting('hero_tag1_title', 'High-Carbon Tempered Steel')) ?></p>
              <p class="text-xs font-bold text-gray-800 truncate"><?= htmlspecialchars(get_setting('hero_tag1_subtitle', 'Forged Desi Hansiya')) ?></p>
            </div>
          </div>

          <!-- Floating Pill 2 (Bottom Right) -->
          <div class="absolute -bottom-3 right-2 sm:-bottom-4 sm:-right-4 bg-brand-green text-white rounded-xl p-2.5 sm:p-3 shadow-xl border border-emerald-500 flex items-center gap-2.5 max-w-[90%] sm:max-w-none">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-emerald-800 flex items-center justify-center text-amber-300 font-bold text-xs sm:text-sm shrink-0">
              ★ 4.96
            </div>
            <div class="min-w-0">
              <p class="text-[9px] sm:text-[10px] text-emerald-200 font-medium truncate"><?= htmlspecialchars(get_setting('hero_tag2_title', 'Handcrafted Rating')) ?></p>
              <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars(get_setting('hero_tag2_subtitle', '★ 4.96 Verified')) ?></p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 2. Categories Showcase Grid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <span class="text-xs font-bold text-brand-green uppercase tracking-wider block">Shop by Village Speciality</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display mt-0.5">
        Explore Authentic Categories
      </h2>
    </div>
    <a href="/products.php" class="text-xs font-bold text-brand-orange hover:text-amber-700 flex items-center gap-1">
      <span>View Complete Catalog</span>
      <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
    </a>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    <?php foreach ($categories as $cat): ?>
      <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="group bg-white rounded-3xl p-4 border border-gray-200 hover:border-brand-green hover:shadow-lg transition-all flex flex-col justify-between space-y-3">
        <div class="w-full h-28 sm:h-32 rounded-2xl overflow-hidden bg-stone-100">
          <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
        <div>
          <h3 class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-brand-green transition-colors leading-snug">
            <?= htmlspecialchars($cat['name']) ?>
          </h3>
          <?php if (!empty($cat['hindi_name'])): ?>
            <p class="text-[11px] text-gray-500 font-hindi mt-0.5"><?= htmlspecialchars($cat['hindi_name']) ?></p>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3. Spotlight Banner: Organic Pulses & Banarasi Pickles (NEW!) -->
<section class="bg-gradient-to-r from-amber-50 via-emerald-50 to-amber-50 border-y border-amber-200/60 py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
      
      <!-- Pulses Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80" alt="Organic Pulses" class="w-32 h-32 object-cover rounded-2xl shadow-md shrink-0">
        <div class="space-y-2 text-center sm:text-left">
          <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full uppercase">100% Unpolished</span>
          <h3 class="text-xl font-bold font-display text-gray-900">Awadh Organic Pulses & Dal</h3>
          <p class="text-xs text-gray-600 leading-relaxed">Stone-ground Arhar/Toor dal, Chana dal, Moong, and indigenous Kala Chana directly from Mallawan farmer producer groups.</p>
          <a href="/products.php?category=pulses-organic-grains" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-green hover:underline pt-1">
            <span>Explore Desi Pulses</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

      <!-- Pickles Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-amber-200 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <img src="https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=400&q=80" alt="Banarasi Achar" class="w-32 h-32 object-cover rounded-2xl shadow-md shrink-0">
        <div class="space-y-2 text-center sm:text-left">
          <span class="px-2.5 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-bold rounded-full uppercase">Pure Mustard Oil</span>
          <h3 class="text-xl font-bold font-display text-gray-900">Banarasi Swad Pickles & Murabba</h3>
          <p class="text-xs text-gray-600 leading-relaxed">Ancestral sun-cured Ramkela aam ka achar, stuffed banarasi lal mirch, kathal achar, and raw khand amla murabba by women SHGs.</p>
          <a href="/products.php?category=pickles-village-murabba" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-orange hover:underline pt-1">
            <span>Explore Village Pickles</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 4. Featured Bestsellers Grid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
  <div class="flex items-center justify-between border-b border-gray-200 pb-4">
    <div>
      <span class="text-xs font-bold text-brand-green uppercase tracking-wider block">Handcrafted Excellence</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display mt-0.5">
        Bestselling Rural Implements & Produce
      </h2>
    </div>
    <span class="hidden sm:inline-flex items-center gap-1 text-xs text-gray-500 font-mono">
      <i data-lucide="truck" class="w-4 h-4 text-emerald-600"></i> Free Delivery ₹499+
    </span>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php foreach ($featured_products as $p): ?>
      <div class="bg-white rounded-3xl border border-gray-200 hover:border-emerald-600/50 hover:shadow-xl transition-all duration-300 p-4 flex flex-col justify-between group relative">
        
        <!-- Wishlist Button -->
        <button 
          onclick="toggleWishlist(<?= $p['id'] ?>, this, event)" 
          class="absolute top-6 right-6 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand-orange transition-colors cursor-pointer"
        >
          <i data-lucide="heart" class="w-4 h-4"></i>
        </button>

        <a href="/product.php?id=<?= $p['id'] ?>" class="space-y-3 block">
          <div class="relative w-full h-48 rounded-2xl overflow-hidden bg-stone-100">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <?php if ($p['discount'] > 0): ?>
              <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-brand-orange text-white text-[10px] font-bold rounded-lg shadow-xs">
                <?= $p['discount'] ?>% OFF
              </span>
            <?php endif; ?>
          </div>

          <div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 font-semibold mb-1">
              <span class="font-mono text-gray-600"><?= htmlspecialchars($p['code']) ?></span>
              <span class="text-emerald-700 font-bold"><?= htmlspecialchars($p['category_name']) ?></span>
            </div>
            <h3 class="font-bold text-sm text-gray-900 group-hover:text-brand-green transition-colors line-clamp-2">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
            <?php if (!empty($p['hindi_name'])): ?>
              <p class="text-[11px] text-gray-500 font-hindi truncate mt-0.5"><?= htmlspecialchars($p['hindi_name']) ?></p>
            <?php endif; ?>
          </div>
        </a>

        <!-- Price & Action -->
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-3">
          <div>
            <div class="flex items-baseline gap-1.5">
              <span class="text-lg font-extrabold text-gray-900 font-display"><?= format_price($p['price']) ?></span>
              <?php if ($p['original_price'] > $p['price']): ?>
                <span class="text-xs text-gray-400 line-through"><?= format_price($p['original_price']) ?></span>
              <?php endif; ?>
            </div>
            <span class="text-[10px] text-emerald-700 font-bold block">✓ <?= htmlspecialchars($p['stock_status']) ?></span>
          </div>

          <button 
            onclick="addToCart(<?= $p['id'] ?>, 1, event)" 
            class="px-3.5 py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 cursor-pointer transition-colors active:scale-95"
          >
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Add</span>
          </button>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 5. Verified Artisan Workshops -->
<section class="bg-stone-100 py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-2">
      <span class="text-xs font-bold text-brand-green uppercase tracking-wider">Direct Rural Impact</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
        Verified Rural Workshops & Foundries
      </h2>
      <p class="text-xs sm:text-sm text-gray-600">
        Every purchase directly settles into the verified bank accounts of Indian village blacksmiths, organic farmer producer groups, and self-help women artisans.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($verified_vendors as $v): ?>
        <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-sm space-y-4 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <img src="<?= htmlspecialchars($v['avatar']) ?>" alt="<?= htmlspecialchars($v['name']) ?>" class="w-12 h-12 rounded-2xl object-cover border border-emerald-200">
              <div>
                <h4 class="font-bold text-sm text-gray-900 line-clamp-1"><?= htmlspecialchars($v['name']) ?></h4>
                <span class="text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-800 font-bold rounded-full"><?= htmlspecialchars($v['type']) ?></span>
              </div>
            </div>
            <p class="text-xs text-gray-500 line-clamp-3 leading-relaxed"><?= htmlspecialchars($v['story']) ?></p>
          </div>
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
            <span>📍 <?= htmlspecialchars($v['district']) ?>, <?= htmlspecialchars($v['state']) ?></span>
            <span class="font-bold text-emerald-800">★ <?= $v['rating'] ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
