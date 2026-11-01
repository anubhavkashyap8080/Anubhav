"""
Modernize Layout 3:
- Fix oversized hero: sleek, compact height (py-8 sm:py-10) with brand logo gradient (#064e3b)
- Fix tiny video crop: place the YouTube embed into a dedicated, responsive 16:9 aspect-video showcase player card on the right!
  Plays muted continuously, zero controls, 100% width & height inside 16:9 frame.
- Fix broken product images: use $p['image'] with fallback and onerror handler.
- Modernize Agricultural Tools section: replace dark stone with clean light stone and crisp white cards.
"""

with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_3.php', 'r', encoding='utf-8') as f:
    code = f.read()

# 1. Replace Hero Section
old_hero_start = '<!-- 2. Cinematic Autoplay Video Hero'
old_hero_end = '<!-- 3. Featured Products & Handcrafted Bestsellers Section -->'

idx_start = code.find(old_hero_start)
idx_end = code.find(old_hero_end)

if idx_start != -1 and idx_end != -1:
    new_hero = '''<!-- 2. Cinematic Autoplay Video Hero (Compact, Sleek 16:9 Responsive Player) -->
<section class="relative w-full overflow-hidden text-white border-b border-stone-800" style="background: linear-gradient(135deg, #064e3b 0%, #032b1e 50%, #0f172a 100%);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
      
      <!-- Left Column: Story & CTAs (7 cols) -->
      <div class="lg:col-span-7 space-y-4 text-left">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-xs">
          <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <span>Village Forge Heritage • Hunar Desh Ka, Ganv Se Ghar Tak</span>
        </div>

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
          Grassroots Rural Forges & <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-300 to-emerald-400">Pure Village Harvest</span>
        </h1>

        <p class="text-xs sm:text-sm text-stone-200 leading-relaxed max-w-xl">
          Direct from the charcoal hearths of Kalyani agrofood pvt ltd in Sandila, bullock-driven wooden mustard expellers in Madhoganj, and handloom cotton weavers in Mallawan. 100% authentic, zero middlemen.
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-1">
          <a href="/products.php" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
            <span>Shop Village Products</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <a href="/artisans.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-md border border-white/20 flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-emerald-400"></i>
            <span>Meet Verified Artisans</span>
          </a>
        </div>

        <!-- Trust Stats Badges -->
        <div class="pt-4 border-t border-emerald-800/60 grid grid-cols-3 gap-3 max-w-md">
          <div>
            <span class="text-lg sm:text-xl font-black text-white block">4,820+</span>
            <span class="text-stone-300 text-[10px] font-medium">Orders Dispatched</span>
          </div>
          <div>
            <span class="text-lg sm:text-xl font-black text-emerald-400 block">42 Villages</span>
            <span class="text-stone-300 text-[10px] font-medium">Direct Maker Hubs</span>
          </div>
          <div>
            <span class="text-lg sm:text-xl font-black text-amber-400 block">Speed Post</span>
            <span class="text-stone-300 text-[10px] font-medium">Pan-India Direct</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Dedicated 16:9 Autoplay Video Showcase Player (5 cols) -->
      <div class="lg:col-span-5">
        <div class="bg-stone-950 rounded-2xl overflow-hidden border-2 border-amber-400/40 shadow-2xl group">
          <!-- 16:9 Responsive Player Container -->
          <div class="relative w-full aspect-video bg-black overflow-hidden">
            <iframe 
              src="<?= htmlspecialchars($yt_embed_url) ?>" 
              title="GanvGaliHat Rural Craft Video"
              class="w-full h-full border-0 absolute inset-0"
              style="width: 100%; height: 100%; border: 0;"
              loading="eager"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              tabindex="-1">
            </iframe>
          </div>

          <!-- Player Status Bar -->
          <div class="p-3 bg-stone-900 border-t border-stone-800 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
              <span class="font-extrabold text-amber-400 text-[11px]">The Charcoal Blacksmiths of Sandila</span>
            </div>
            <span class="text-emerald-400 font-bold flex items-center gap-1 text-[10px]">
              <i data-lucide="volume-x" class="w-3 h-3"></i> Muted Autoplay
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

'''
    code = code[:idx_start] + new_hero + code[idx_end:]
    print("SUCCESS: Hero section overhauled with responsive 16:9 video player card")
else:
    print("ERROR: Hero boundaries not found")

# 2. Fix Featured Products Images in layout_3
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
    print("SUCCESS: Featured products images fixed in layout_3")
else:
    print("ERROR: old_feat_img not found in layout_3")

# 3. Modernize Agricultural Tools section in layout_3
old_tools = '''<!-- 5. Heavy Forged Agricultural Tools by Kalyani agrofood pvt ltd -->
<section class="py-12 bg-stone-950 text-white border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-400 uppercase tracking-widest block mb-1">Sandila Industrial Hub</span>
        <h2 class="text-2xl sm:text-3xl font-black text-white">Hand-Forged Implements by Kalyani agrofood pvt ltd</h2>
        <p class="text-stone-400 text-xs sm:text-sm mt-1">High-carbon leaf-spring steel forged on charcoal hearths in Sandila, Hardoi</p>
      </div>
      <a href="/products.php?category=traditional-tools" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:text-amber-300">
        <span>View All Forged Tools</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach (array_slice($box1_tools, 0, 4) as $tool): ?>
        <div class="bg-stone-900 rounded-2xl border border-stone-800 p-3 sm:p-4 hover:border-amber-400 transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-800 rounded-xl overflow-hidden mb-3">
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

new_tools = '''<!-- 5. Heavy Forged Agricultural Tools by Kalyani agrofood pvt ltd -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Sandila Industrial Hub</span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Hand-Forged Implements by Kalyani agrofood pvt ltd</h2>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">High-carbon leaf-spring steel forged on charcoal hearths in Sandila, Hardoi</p>
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
    print("SUCCESS: Tools section overhauled in layout_3")
else:
    print("ERROR: old_tools not found in layout_3")

with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_3.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("layout_3.php updated successfully!")
