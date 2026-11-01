"""
Modernize Layout 2:
- Compact, elegant 3-slide carousel hero (380-450px) with logo-gradient overlays
- High-contrast, clean amber-50 Flash Deals section (NO white-on-white text!)
- Modern Meesho/Amazon-level white product cards with discount pills, verified maker tags, high-res images
- Fix image field: use $p['image'] with fallback and onerror
- Modern light-stone Agricultural Tools section by Kalyani agrofood pvt ltd
"""

with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_2.php', 'r', encoding='utf-8') as f:
    code = f.read()

# 1. Slide heights
code = code.replace(
    'min-h-[460px] sm:min-h-[520px] lg:min-h-[560px] flex items-center',
    'min-h-[380px] sm:min-h-[420px] lg:min-h-[450px] flex items-center'
)

# 2. Slide gradient overlays (explicit inline style using GanvGaliHat Forest Green #064e3b)
code = code.replace(
    '<div class="absolute inset-0 bg-gradient-to-r from-stone-950/95 via-emerald-950/85 to-stone-950/40"></div>',
    '<div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(6,78,59,0.92) 0%, rgba(6,78,59,0.85) 50%, rgba(15,23,42,0.6) 100%);"></div>'
)
code = code.replace(
    '<div class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-stone-950/85 to-amber-950/40"></div>',
    '<div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(6,78,59,0.92) 0%, rgba(15,23,42,0.85) 50%, rgba(234,88,12,0.6) 100%);"></div>'
)
code = code.replace(
    '<div class="absolute inset-0 bg-gradient-to-r from-stone-950/95 via-purple-950/85 to-stone-950/40"></div>',
    '<div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(15,23,42,0.92) 0%, rgba(6,78,59,0.85) 50%, rgba(245,158,11,0.6) 100%);"></div>'
)

# 3. Flash Deals complete replacement
old_flash_deals = '''<!-- 3. Harvest Flash Deals (Live Countdown Timer + Percentage Savings) -->
<section class="py-8 bg-gradient-to-r from-stone-950 via-emerald-950 to-stone-950 text-white border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-stone-800">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-stone-950 flex items-center justify-center font-black">
          <i data-lucide="zap" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-[10px] text-amber-400 font-black tracking-widest uppercase block">Direct Maker Discounts</span>
          <h2 class="text-xl sm:text-2xl font-black text-white">Harvest Flash Deals of the Day</h2>
        </div>
      </div>

      <!-- Countdown Clock -->
      <div class="flex items-center gap-2 bg-stone-900/90 border border-stone-800 rounded-2xl px-4 py-2 self-start md:self-auto">
        <span class="text-xs text-stone-400 font-bold mr-1">Deals End In:</span>
        <div class="flex items-center gap-1.5 font-mono text-xs font-black">
          <span id="dealHours" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">07</span>
          <span class="text-amber-400">:</span>
          <span id="dealMins" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">42</span>
          <span class="text-amber-400">:</span>
          <span id="dealSecs" class="px-2 py-1 bg-amber-500 text-stone-950 rounded-lg">19</span>
        </div>
      </div>
    </div>

    <!-- Flash Deals Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach (array_slice($featured_products, 0, 4) as $p): ?>
        <div class="bg-stone-900/90 rounded-2xl border border-stone-800 p-3 sm:p-4 hover:border-amber-500/50 transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-800 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($p['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white font-black text-[10px] rounded-md shadow-xs">
                SAVE 25%
              </span>
            </div>

            <span class="text-[10px] text-emerald-400 font-bold block mb-1">
              By <?= htmlspecialchars(str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $p['vendor_name'] ?? 'Local Forge')) ?>
            </span>
            <h3 class="font-bold text-xs sm:text-sm text-white line-clamp-2 leading-snug group-hover:text-amber-400 transition mb-2">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
          </div>

          <div class="pt-2 border-t border-stone-800">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-amber-400">₹<?= number_format($p['price'], 0) ?></span>
              <span class="text-[11px] text-stone-500 line-through">₹<?= number_format($p['price'] * 1.35, 0) ?></span>
            </div>
            
            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                <span>Quick Add</span>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>'''

new_flash_deals = '''<!-- 3. Harvest Flash Deals (Live Countdown Timer + Percentage Savings) -->
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
</section>'''

if old_flash_deals in code:
    code = code.replace(old_flash_deals, new_flash_deals)
    print("SUCCESS: Flash deals replaced with clean modern section")
else:
    print("ERROR: old_flash_deals not found")

# 4. Featured products image fix & link in layout_2
old_feat_img = '''<div class="relative w-full aspect-square bg-stone-100 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($p['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   loading="lazy">'''

new_feat_img = '''<?php $feat_img = !empty($p['image']) ? $p['image'] : (!empty($p['image_url']) ? $p['image_url'] : '/assets/images/placeholder.jpg'); ?>
            <div class="relative w-full aspect-square bg-stone-50 rounded-xl overflow-hidden mb-3 border border-gray-100">
              <img src="<?= htmlspecialchars($feat_img) ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>"
                   onerror="this.onerror=null; this.src='/assets/images/placeholder.jpg';"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   loading="lazy">'''

if old_feat_img in code:
    code = code.replace(old_feat_img, new_feat_img)
    print("SUCCESS: Featured products images fixed")
else:
    print("ERROR: old_feat_img not found")

# 5. Agricultural tools section overhaul (clean light-stone, white cards)
old_tools = '''<!-- 5. Heavy-Duty Agricultural Implements by Kalyani agrofood pvt ltd -->
<section class="py-12 bg-stone-900 text-white border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-400 uppercase tracking-widest block mb-1">Sandila Industrial Hub</span>
        <h2 class="text-2xl sm:text-3xl font-black text-white">Forged Farming Tools by Kalyani agrofood pvt ltd</h2>
        <p class="text-stone-400 text-xs sm:text-sm mt-1">High-carbon tempered railway leaf springs forged on charcoal anvils in Hardoi</p>
      </div>
      <a href="/products.php?category=traditional-tools" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:text-amber-300">
        <span>View All Forged Tools</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach (array_slice($box1_tools, 0, 4) as $tool): ?>
        <div class="bg-stone-950 rounded-2xl border border-stone-800 p-3 sm:p-4 hover:border-amber-400 transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-900 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($tool['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($tool['name']) ?>" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[10px] rounded-md">
                55 HRC Tempered
              </span>
            </div>
            <h3 class="font-bold text-xs sm:text-sm text-white line-clamp-2 mb-1 group-hover:text-amber-400 transition">
              <?= htmlspecialchars($tool['name']) ?>
            </h3>
            <span class="text-[11px] text-stone-400 block mb-2">Kalyani agrofood pvt ltd • Sandila</span>
          </div>

          <div class="pt-2 border-t border-stone-800">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-amber-400">₹<?= number_format($tool['price'], 0) ?></span>
              <span class="text-[10px] px-1.5 py-0.5 bg-emerald-950 text-emerald-400 font-bold rounded">In Stock</span>
            </div>
            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$tool['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition flex items-center justify-center gap-1 cursor-pointer">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                <span>Add to Cart</span>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>'''

new_tools = '''<!-- 5. Heavy-Duty Agricultural Implements by Kalyani agrofood pvt ltd -->
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
</section>'''

if old_tools in code:
    code = code.replace(old_tools, new_tools)
    print("SUCCESS: Tools section overhauled to modern white cards on light stone")
else:
    print("ERROR: old_tools not found")

with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_2.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("layout_2.php updated successfully!")
