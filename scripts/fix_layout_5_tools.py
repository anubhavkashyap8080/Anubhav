"""
Update layout_5 tools section to clean light stone with crisp white cards
"""

with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_5.php', 'r', encoding='utf-8') as f:
    code5 = f.read()

old_tools5 = '''<!-- 5. Heavy Farm Implements by Kalyani agrofood pvt ltd Showcase -->
<section class="py-12 bg-stone-950 text-white border-b border-stone-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-amber-400 uppercase tracking-widest block mb-1">Sandila Industrial Hub Spotlight</span>
        <h2 class="text-2xl sm:text-3xl font-black text-white">Hand-Forged Implements by Kalyani agrofood pvt ltd</h2>
        <p class="text-stone-400 text-xs sm:text-sm mt-1">High-carbon leaf-spring steel forged on charcoal hearths in Sandila, Hardoi</p>
      </div>
      <a href="/products.php?category=traditional-tools" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-400 hover:text-amber-300">
        <span>View All Forged Tools</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      <?php foreach (array_slice($box1_tools, 0, 4) as $tool): ?>
        <div class="bg-stone-900 rounded-2xl border border-stone-800 p-3 sm:p-4 hover:border-amber-500/50 transition flex flex-col justify-between group">
          <div>
            <div class="relative w-full aspect-square bg-stone-800 rounded-xl overflow-hidden mb-3">
              <img src="<?= htmlspecialchars($tool['image_url'] ?? '/assets/images/placeholder.jpg') ?>" 
                   alt="<?= htmlspecialchars($tool['name']) ?>" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                   loading="lazy">
              <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-stone-950 font-black text-[10px] rounded-md shadow-xs">
                Forged Steel
              </span>
            </div>

            <h3 class="font-bold text-xs sm:text-sm text-white line-clamp-2 leading-snug group-hover:text-amber-400 transition mb-1">
              <?= htmlspecialchars($tool['name']) ?>
            </h3>
            <span class="text-[11px] text-stone-400 block mb-2">Kalyani agrofood pvt ltd • Sandila</span>
          </div>

          <div class="pt-2 border-t border-stone-800">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-amber-400">₹<?= number_format($tool['price'], 0) ?></span>
              <?php if (!empty($tool['mrp']) && $tool['mrp'] > $tool['price']): ?>
                <span class="text-[11px] text-stone-500 line-through">₹<?= number_format($tool['mrp'], 0) ?></span>
              <?php endif; ?>
            </div>

            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$tool['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1 cursor-pointer">
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

new_tools5 = '''<!-- 5. Heavy Farm Implements by Kalyani agrofood pvt ltd Showcase -->
<section class="py-12 bg-stone-50 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest block mb-1">Sandila Industrial Hub Spotlight</span>
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
                Forged Steel
              </span>
            </div>

            <h3 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-800 transition mb-1">
              <a href="/product.php?id=<?= $tool['id'] ?>">
                <?= htmlspecialchars($tool['name']) ?>
              </a>
            </h3>
            <span class="text-[11px] text-emerald-700 font-bold block mb-2">Kalyani agrofood pvt ltd • Sandila</span>
          </div>

          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-baseline justify-between mb-2">
              <span class="text-base sm:text-lg font-black text-gray-900">₹<?= number_format($tool['price'], 0) ?></span>
              <?php if (!empty($tool['mrp']) && $tool['mrp'] > $tool['price']): ?>
                <span class="text-[11px] text-gray-400 line-through">₹<?= number_format($tool['mrp'], 0) ?></span>
              <?php endif; ?>
            </div>

            <form action="/cart.php" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$tool['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1 cursor-pointer">
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

if old_tools5 in code5:
    code5 = code5.replace(old_tools5, new_tools5)
    print("SUCCESS: Tools section overhauled in layout_5")
    with open('C:/Users/HP/.gemini/antigravity/scratch/ganvgalihat/includes/home_layouts/layout_5.php', 'w', encoding='utf-8') as f:
        f.write(code5)
else:
    print("ERROR: old_tools5 not found")
