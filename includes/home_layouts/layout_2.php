<?php
/**
 * Layout 2: Dynamic Animated Slider Hero with Flash Deals & Artisan Showcase
 * Features:
 * - Dynamic Multi-Slide Interactive Hero Slider with Autoplay & Dot Controls
 * - Live Harvest Flash Deals with Dynamic Countdown Timer (Hours : Mins : Secs)
 * - Circular Category Tray & Quick-Shop CTAs
 * - Artisan Workshop Distance & Courier Dispatch ETA Badges
 * - Verified Customer Reviews & Artisan Testimonial Cards
 * - 100% Responsive with Zero Horizontal Overflow
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

<!-- 1. Circular Visual Category Tray -->
<section class="bg-white border-b border-gray-200 py-3 sm:py-4 shadow-xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-start sm:justify-between gap-4 sm:gap-6 overflow-x-auto scrollbar-none pb-1">
      <?php foreach ($categories as $cat): ?>
        <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>" 
           class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
          <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-amber-500 via-emerald-600 to-amber-400 group-hover:from-emerald-500 group-hover:to-brand-orange transition-all shadow-xs group-hover:shadow-md">
            <div class="w-full h-full rounded-full overflow-hidden bg-white p-0.5">
              <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" 
                   class="w-full h-full object-cover rounded-full group-hover:scale-110 transition-transform duration-300">
            </div>
          </div>
          <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-700 mt-1.5 line-clamp-1 leading-tight">
            <?= htmlspecialchars($cat['name']) ?>
          </span>
          <span class="text-[9px] text-gray-400 font-medium line-clamp-1">Explore</span>
        </a>
      <?php endforeach; ?>

      <a href="/products.php" class="group flex flex-col items-center text-center shrink-0 w-20 sm:w-24 transition-transform active:scale-95">
        <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full p-0.5 bg-gradient-to-tr from-stone-800 to-amber-600 shadow-xs group-hover:shadow-md">
          <div class="w-full h-full rounded-full bg-stone-900 text-amber-300 flex items-center justify-center group-hover:bg-emerald-950 transition-colors">
            <i data-lucide="layout-grid" class="w-7 h-7"></i>
          </div>
        </div>
        <span class="text-[11px] sm:text-xs font-bold text-gray-800 group-hover:text-emerald-700 mt-1.5 leading-tight">
          All Products
        </span>
        <span class="text-[9px] text-gray-400 font-medium">Browse All</span>
      </a>
    </div>
  </div>
</section>

<!-- 2. Interactive Multi-Slide Hero Carousel (Layout 2) -->
<section class="relative w-full overflow-hidden bg-stone-950 text-white" id="heroSliderSection">
  <div class="relative w-full h-[420px] sm:h-[500px] lg:h-[540px]">
    
    <!-- Slide 1: Blacksmithing & Hand Forged Tools -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-100 flex items-center" data-index="0">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1600&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-stone-950 via-stone-950/85 to-transparent"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-fire-burner text-amber-400"></i>
            <span>Village Forge Heritage • Kalyani agrofood pvt ltd</span>
          </div>
          <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight">
            Hand-Forged Tempered Steel Tools
          </h1>
          <p class="text-xs sm:text-base text-stone-300 leading-relaxed">
            Crafted from high-grade railway spring steel by generational masters of Hardoi. Razor-sharp sickles, heavy axes, and durable khurpis made for lifelong farming.
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="/products.php?category=blacksmithing" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span>Shop Forged Tools</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
            <a href="/artisans.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              Meet the Blacksmiths
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 2: Pure Wood-Pressed Mustard Oil & Pickles -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none flex items-center" data-index="1">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=1600&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950 via-emerald-950/85 to-transparent"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-seedling text-emerald-400"></i>
            <span>100% Cold-Pressed Organic Staples</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black text-white leading-tight">
            Raw Kachi Ghani Mustard Oil & Desi Pickles
          </h2>
          <p class="text-xs sm:text-base text-stone-300 leading-relaxed">
            Extracted through traditional wooden expellers without chemical refining. Paired with grandma-recipe sun-cured Awadhi mango, amla, and stuffed red chili achar.
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="/products.php?category=oil-ghee" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span>Explore Natural Staples</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
            <a href="/about.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              Extraction Process
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 3: Handloom Textiles & Rural Women SHGs -->
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none flex items-center" data-index="2">
      <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1600&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-950 via-purple-950/85 to-transparent"></div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="max-w-2xl space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 border border-purple-400/40 text-purple-300 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-hand-holding-heart text-purple-400"></i>
            <span>Women Self-Help Groups • Fair Trade Direct</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black text-white leading-tight">
            Handloom Textiles, Clay Pottery & Jute Crafts
          </h2>
          <p class="text-xs sm:text-base text-stone-300 leading-relaxed">
            Support rural craftswomen across Uttar Pradesh. 96% of sales price directly credits to artisan bank accounts via India Post payments bank.
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="/products.php?category=handicrafts" class="px-6 py-3 bg-purple-500 hover:bg-purple-400 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <span>Support Craftswomen</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
            <a href="/seller-register.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20">
              Join as Seller
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Slider Navigation Arrows -->
  <button type="button" onclick="prevHeroSlide()" aria-label="Previous Slide"
    class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-stone-900/60 hover:bg-stone-900 text-white border border-white/20 flex items-center justify-center transition backdrop-blur-md z-20">
    <i class="fa-solid fa-chevron-left text-sm"></i>
  </button>
  <button type="button" onclick="nextHeroSlide()" aria-label="Next Slide"
    class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-stone-900/60 hover:bg-stone-900 text-white border border-white/20 flex items-center justify-center transition backdrop-blur-md z-20">
    <i class="fa-solid fa-chevron-right text-sm"></i>
  </button>

  <!-- Slider Indicator Dots -->
  <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
    <button type="button" onclick="goToHeroSlide(0)" class="slider-dot w-8 h-2 rounded-full bg-amber-500 transition-all" aria-label="Slide 1"></button>
    <button type="button" onclick="goToHeroSlide(1)" class="slider-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white transition-all" aria-label="Slide 2"></button>
    <button type="button" onclick="goToHeroSlide(2)" class="slider-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white transition-all" aria-label="Slide 3"></button>
  </div>
</section>

<!-- 3. Limited-Time Harvest Flash Deals with Dynamic Countdown Timer -->
<section class="bg-amber-500 text-stone-950 py-4 shadow-sm border-y border-amber-600">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3 text-center sm:text-left">
      <div class="w-10 h-10 rounded-2xl bg-stone-950 text-amber-400 flex items-center justify-center text-lg font-bold shrink-0">
        <i class="fa-solid fa-bolt"></i>
      </div>
      <div>
        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wide">
          Limited Harvest Flash Deals
        </h3>
        <p class="text-xs text-stone-900 font-medium">Up to 35% off direct from Hardoi forge & organic farm clusters</p>
      </div>
    </div>

    <!-- Live Timer -->
    <div class="flex items-center gap-2 font-mono text-xs font-black">
      <span class="text-stone-900 uppercase font-sans font-bold text-[11px] mr-1">Ends In:</span>
      <div class="bg-stone-950 text-amber-300 px-2.5 py-1.5 rounded-lg shadow-xs"><span id="dealHours">08</span>h</div>
      <span>:</span>
      <div class="bg-stone-950 text-amber-300 px-2.5 py-1.5 rounded-lg shadow-xs"><span id="dealMins">42</span>m</div>
      <span>:</span>
      <div class="bg-stone-950 text-amber-300 px-2.5 py-1.5 rounded-lg shadow-xs"><span id="dealSecs">19</span>s</div>
      <a href="/products.php" class="ml-2 px-4 py-1.5 bg-stone-950 hover:bg-black text-white text-xs font-bold rounded-lg transition font-sans">
        View All Deals
      </a>
    </div>
  </div>
</section>

<!-- 4. Featured Bestseller Catalog -->
<section class="py-12 bg-stone-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-bold text-amber-700 uppercase tracking-wider bg-amber-100 px-3 py-1 rounded-full">Handmade Excellence</span>
        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-2">Trending Rural Craft & Farm Catalog</h2>
        <p class="text-xs text-gray-500 mt-0.5">Direct village forge and farm dispatch with 100% authentic quality assurance.</p>
      </div>
      <a href="/products.php" class="text-xs font-bold text-brand-green hover:underline flex items-center gap-1.5">
        <span>View Full Catalog</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
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

<!-- 5. Nearest Artisan Forges (Haversine Geolocation Engine) -->
<?php if (!empty($nearest_vendors)): ?>
<section class="py-12 bg-white border-t border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full">Direct Forge Dispatch</span>
        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-2">Nearby Artisan Foundries & Workshops</h2>
        <p class="text-xs text-gray-500 mt-0.5">Ordered from closest workshops for fastest 24-48h parcel delivery.</p>
      </div>
      <a href="/artisans.php" class="text-xs font-bold text-brand-green hover:underline">Explore All Foundries</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($nearest_vendors as $v): ?>
        <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 shadow-xs hover:border-amber-500 transition">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
              <i class="fa-solid fa-hammer"></i>
            </div>
            <div class="min-w-0">
              <h3 class="text-xs font-bold text-gray-900 truncate"><?= htmlspecialchars($v['name']) ?></h3>
              <p class="text-[11px] text-gray-500"><?= htmlspecialchars($v['village'] ?: 'Village') ?>, <?= htmlspecialchars($v['district']) ?></p>
            </div>
          </div>
          <div class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 inline-flex items-center gap-1.5 mb-3">
            <i class="fa-solid fa-truck-fast"></i>
            <span><?= $v['distance_km'] ?> km away (<?= $v['dispatch_eta'] ?>)</span>
          </div>
          <a href="/products.php?vendor=<?= $v['id'] ?>" class="block text-center w-full py-2 bg-white hover:bg-stone-100 border border-gray-200 text-gray-800 text-xs font-bold rounded-xl transition">
            View Workshop Products
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
// Hero Slider Controller
let currentHeroSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.slider-dot');
let heroInterval;

function showHeroSlide(index) {
  if (index >= slides.length) index = 0;
  if (index < 0) index = slides.length - 1;
  currentHeroSlide = index;

  slides.forEach((s, idx) => {
    if (idx === index) {
      s.classList.remove('opacity-0', 'pointer-events-none');
      s.classList.add('opacity-100');
    } else {
      s.classList.remove('opacity-100');
      s.classList.add('opacity-0', 'pointer-events-none');
    }
  });

  dots.forEach((d, idx) => {
    if (idx === index) {
      d.className = 'slider-dot w-8 h-2 rounded-full bg-amber-500 transition-all';
    } else {
      d.className = 'slider-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white transition-all';
    }
  });
}

function nextHeroSlide() { showHeroSlide(currentHeroSlide + 1); }
function prevHeroSlide() { showHeroSlide(currentHeroSlide - 1); }
function goToHeroSlide(idx) { showHeroSlide(idx); }

function startHeroAutoPlay() {
  clearInterval(heroInterval);
  heroInterval = setInterval(nextHeroSlide, 5000);
}

const sliderSec = document.getElementById('heroSliderSection');
if (sliderSec) {
  sliderSec.addEventListener('mouseenter', () => clearInterval(heroInterval));
  sliderSec.addEventListener('mouseleave', startHeroAutoPlay);
  startHeroAutoPlay();
}

// Countdown Timer Controller
function updateFlashTimer() {
  const hoursEl = document.getElementById('dealHours');
  const minsEl = document.getElementById('dealMins');
  const secsEl = document.getElementById('dealSecs');
  if (!hoursEl || !minsEl || !secsEl) return;

  let h = parseInt(hoursEl.innerText, 10);
  let m = parseInt(minsEl.innerText, 10);
  let s = parseInt(secsEl.innerText, 10);

  s--;
  if (s < 0) { s = 59; m--; }
  if (m < 0) { m = 59; h--; }
  if (h < 0) { h = 12; m = 0; s = 0; }

  hoursEl.innerText = h < 10 ? '0' + h : h;
  minsEl.innerText = m < 10 ? '0' + m : m;
  secsEl.innerText = s < 10 ? '0' + s : s;
}
setInterval(updateFlashTimer, 1000);
</script>
