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
?>

<!-- 1. Hero Banner Section -->
<section class="relative bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-900 text-white overflow-hidden py-12 sm:py-20">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      
      <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-800/80 border border-emerald-600/40 text-amber-300 text-xs font-semibold shadow-inner">
          <i data-lucide="sparkles" class="w-4 h-4 text-amber-300"></i>
          <span>Direct from Village Foundries, Awadh Farms & Women SHGs</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold font-display tracking-tight text-white leading-tight">
          High-Durability Farming Tools, <span class="text-amber-400">Pure Desi Dal</span> & Village Achar.
        </h1>

        <p class="text-sm sm:text-base text-emerald-100/90 max-w-xl mx-auto lg:mx-0 leading-relaxed">
          Manufactured by S S Industries in Hardoi & curated from authentic rural artisan clusters. Hand-forged carbon steel sickles, stone-milled unpolished pulses, and sun-cured mustard oil pickles delivered across India via India Post.
        </p>

        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
          <a href="/products.php" class="px-6 py-3.5 bg-brand-orange hover:bg-amber-600 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg hover:shadow-amber-900/30 transition-all flex items-center gap-2 cursor-pointer">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Explore All Products</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="/seller-register.php" class="px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-2xl border border-white/20 backdrop-blur-xs transition-all flex items-center gap-2 cursor-pointer">
            <i data-lucide="store" class="w-4 h-4 text-amber-300"></i>
            <span>Register as Artisan / Farmer</span>
          </a>
        </div>

        <!-- Badges -->
        <div class="pt-4 grid grid-cols-3 gap-2 sm:gap-4 text-left max-w-md mx-auto lg:mx-0 text-slate-300 text-[11px] font-medium">
          <div class="p-2.5 bg-white/5 rounded-xl border border-white/10">
            <p class="text-amber-300 font-bold text-sm">₹499+</p>
            <p class="text-[10px] text-slate-400">Free India Delivery</p>
          </div>
          <div class="p-2.5 bg-white/5 rounded-xl border border-white/10">
            <p class="text-emerald-400 font-bold text-sm">100%</p>
            <p class="text-[10px] text-slate-400">Raw Desi Produce</p>
          </div>
          <div class="p-2.5 bg-white/5 rounded-xl border border-white/10">
            <p class="text-white font-bold text-sm">COD</p>
            <p class="text-[10px] text-slate-400">Cash on Delivery</p>
          </div>
        </div>
      </div>

      <!-- Right Hero Graphic -->
      <div class="lg:col-span-5 relative">
        <div class="relative mx-auto max-w-md rounded-3xl overflow-hidden border-2 border-emerald-700/50 shadow-2xl bg-slate-900">
          <img 
            src="https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80" 
            alt="Rural Artisan Forge" 
            class="w-full h-80 sm:h-96 object-cover opacity-90"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent flex flex-col justify-end p-6 space-y-2">
            <span class="px-2.5 py-1 bg-amber-500 text-slate-950 font-extrabold text-[10px] rounded-full w-max uppercase tracking-wider">
              S S Industries Works • Hardoi
            </span>
            <h3 class="text-lg font-bold text-white font-display">
              Generational Forge Metallurgy & Pure Village Harvest
            </h3>
            <p class="text-xs text-slate-300">
              Unbreakable high-carbon agricultural sickles and 100% unpolished desi farm grains delivered to your doorstep.
            </p>
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
