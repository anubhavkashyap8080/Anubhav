with open('product.php', 'r', encoding='utf-8') as f:
    content = f.read()

needle = """          <button 
            onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('detail-qty').value))" 
            class="flex-1 py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md flex items-center justify-center gap-2 cursor-pointer transition-all active:scale-95"
          >
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Add to Cart</span>
          </button>
        </div>
      </div>"""

replacement = """          <button 
            onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('detail-qty').value))" 
            class="flex-1 py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md flex items-center justify-center gap-2 cursor-pointer transition-all active:scale-95"
          >
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Add to Cart</span>
          </button>
        </div>
      </div>

      <!-- B2B Wholesale Bulk Purchase Card for Retailers & Shopkeepers -->
      <?php if (!empty($product['bulk_available'])): ?>
        <?php
          $b_moq = max(1, intval($product['bulk_moq'] ?: 10));
          $b_price = floatval($product['bulk_price'] ?: ($product['price'] * 0.75));
          $b_t2_qty = intval($product['bulk_tier2_qty'] ?? 0);
          $b_t2_price = floatval($product['bulk_tier2_price'] ?? 0);
          $b_margin = max(0, $product['price'] - $b_price);
          $b_margin_pct = ($product['price'] > 0) ? round(($b_margin / $product['price']) * 100) : 25;
        ?>
        <div class="p-5 bg-gradient-to-br from-amber-50 to-orange-50/70 rounded-3xl border border-amber-300 shadow-xs space-y-3">
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 text-xs font-black text-amber-900 uppercase tracking-wider">
              <i class="fa-solid fa-boxes-packing text-amber-600"></i>
              दुकानदार / थोक खरीद (Wholesale B2B)
            </span>
            <span class="bg-amber-600 text-white text-[10px] font-black px-2.5 py-0.5 rounded-full shadow-xs">
              <?= $b_margin_pct ?>% Profit Margin
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs bg-white/80 p-3 rounded-2xl border border-amber-200">
            <div>
              <span class="text-gray-500 text-[10px] block">थोक भाव (Wholesale Rate):</span>
              <span class="text-base font-black text-amber-900"><?= format_price($b_price) ?> <span class="text-[10px] text-gray-500 font-normal">/ पीस</span></span>
            </div>
            <div>
              <span class="text-gray-500 text-[10px] block">न्यूनतम थोक लॉट (MOQ):</span>
              <span class="text-sm font-black text-gray-900"><?= $b_moq ?> Pieces</span>
            </div>
            <?php if ($b_t2_qty > 0 && $b_t2_price > 0): ?>
              <div class="col-span-2 pt-1 border-t border-amber-100 flex items-center justify-between text-[11px] text-amber-950 font-bold">
                <span>टियर 2 महा-बल्क दर:</span>
                <span><?= $b_t2_qty ?>+ Pieces @ <?= format_price($b_t2_price) ?> / pc</span>
              </div>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-between gap-2 pt-1">
            <?php if (is_retailer()): ?>
              <a href="/retailer/products.php?highlight=<?= $product['id'] ?>"
                class="w-full py-2.5 bg-stone-900 hover:bg-black text-amber-400 font-black text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-cart-flatbed"></i>
                थोक लॉट कैलकुलेटर व ऑर्डर &rarr;
              </a>
            <?php else: ?>
              <a href="/retailer-register.php"
                class="flex-1 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 text-center">
                <i class="fa-solid fa-store"></i>
                दुकान के लिए थोक में खरीदें
              </a>
              <a href="/login.php?tab=retailer"
                class="px-3 py-2.5 bg-white text-stone-800 border border-amber-300 font-bold text-xs rounded-xl hover:bg-amber-100/50 transition">
                लॉगिन
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>"""

if needle in content:
    content = content.replace(needle, replacement)
    with open('product.php', 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated product.php successfully")
else:
    # Try with CRLF
    needle_crlf = needle.replace('\n', '\r\n')
    replacement_crlf = replacement.replace('\n', '\r\n')
    if needle_crlf in content:
        content = content.replace(needle_crlf, replacement_crlf)
        with open('product.php', 'w', encoding='utf-8') as f:
            f.write(content)
        print("Updated product.php successfully with CRLF")
    else:
        print("Needle not found in product.php")
