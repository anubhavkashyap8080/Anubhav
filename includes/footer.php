<?php
/**
 * GaunGaliHat Master Footer
 * Clean Brand Footer with Vertical Payment Modes List (COD, Net Banking, Cards, UPI)
 */
?>
  </main>

  <!-- Value Propositions Banner -->
  <section class="bg-stone-100 border-t border-gray-200 mt-16 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs">
        <div class="flex items-start gap-3.5 p-4 bg-white rounded-2xl border border-gray-100 shadow-2xs">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-brand-green flex items-center justify-center shrink-0">
            <i data-lucide="hammer" class="w-5 h-5"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-900 text-sm">Direct Village Forge</h4>
            <p class="text-gray-500 mt-0.5">Manufactured by S S Industries & generational blacksmiths in Hardoi, UP.</p>
          </div>
        </div>

        <div class="flex items-start gap-3.5 p-4 bg-white rounded-2xl border border-gray-100 shadow-2xs">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-brand-orange flex items-center justify-center shrink-0">
            <i data-lucide="wheat" class="w-5 h-5"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-900 text-sm">Unpolished Farm Dal & Achar</h4>
            <p class="text-gray-500 mt-0.5">100% natural, stone-milled indigenous pulses & sun-cured mustard oil pickles.</p>
          </div>
        </div>

        <div class="flex items-start gap-3.5 p-4 bg-white rounded-2xl border border-gray-100 shadow-2xs">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
            <i data-lucide="truck" class="w-5 h-5"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-900 text-sm">India Post Express Dispatch</h4>
            <p class="text-gray-500 mt-0.5">Free shipping on orders above ₹499 with live parcel barcode tracking.</p>
          </div>
        </div>

        <div class="flex items-start gap-3.5 p-4 bg-white rounded-2xl border border-gray-100 shadow-2xs">
          <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-900 text-sm">Bank-Grade Payments</h4>
            <p class="text-gray-500 mt-0.5">100% Secure Checkout powered by official integrations.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Master Footer -->
  <footer class="bg-slate-950 text-white text-xs pt-12 pb-24 lg:pb-12 border-t-4 border-brand-orange">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
        
        <!-- Brand & Factory Info with Logo -->
        <div class="lg:col-span-2 space-y-4">
          <?php 
            $footer_logo = (get_setting('use_gif_logo', '0') === '1' && !empty(get_setting('logo_gif_url'))) ? get_setting('logo_gif_url') : get_setting('site_logo_url', '/logo.png');
          ?>
          <a href="/" class="flex items-center gap-2">
            <img src="<?= htmlspecialchars($footer_logo) ?>" alt="<?= htmlspecialchars(APP_NAME) ?>" class="h-10 sm:h-12 w-auto object-contain bg-white/90 p-1 rounded-lg" onerror="this.onerror=null; this.src='/logo.png';" />
            <div>
              <span class="font-extrabold text-lg sm:text-xl font-display text-white block">GaunGaliHat</span>
              <span class="text-[10px] text-amber-300 font-semibold uppercase tracking-wider block">A Brand by S S Industries</span>
            </div>
          </a>
          <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
            <?= htmlspecialchars(get_setting('company_name', 'S S Industries')) ?> enterprise initiative dedicated to empowering rural Indian smallholders, village foundries, and women self-help groups by delivering authentic farming tools, organic pulses and farm produce straight from rural Bharat.
          </p>
          <div class="space-y-1.5 text-[11px] text-slate-400 font-mono">
            <p>📍 Factory: Madhoganj Baghauli Road, Newada, Hardoi, UP - 241126</p>
            <p>🏭 Plant: G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi</p>
            <p>📞 Phone: <?= htmlspecialchars(APP_PHONE) ?></p>
            <p>✉️ Email: <?= htmlspecialchars(APP_EMAIL) ?></p>
          </div>
        </div>

        <!-- Categories -->
        <div>
          <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3 font-display">Village Catalog</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="/products.php?category=farming-traditional-tools" class="hover:text-emerald-400">Agricultural Tools (Hansiya, Khurpi)</a></li>
            <li><a href="/products.php?category=pulses-organic-grains" class="hover:text-emerald-400 font-bold text-emerald-400">Desi Pulses & Dal</a></li>
            <li><a href="/products.php?category=pickles-village-murabba" class="hover:text-amber-400 font-bold text-amber-400">Banarasi Pickles & Murabba</a></li>
            <li><a href="/products.php?category=hardware-cottage-implements" class="hover:text-emerald-400">Heavy Iron Implements</a></li>
            <li><a href="/products.php?category=jute-bags-cloth-jholas" class="hover:text-emerald-400">Eco Jute Totes & Bags</a></li>
            <li><a href="/products.php?category=handmade-by-rural-women" class="hover:text-emerald-400">Women SHG Products</a></li>
          </ul>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3 font-display">Quick Access</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="/about.php" class="hover:text-white">About S S Industries</a></li>
            <li><a href="/seller/dashboard.php" class="hover:text-brand-orange font-bold text-brand-orange">Artisan & Seller Portal</a></li>
            <li><a href="/seller-register.php" class="hover:text-white">Register Artisan Workshop</a></li>
            <li><a href="/account.php" class="hover:text-white">Track India Post Parcel</a></li>
            <li><a href="/contact.php" class="hover:text-white">Customer Support Desk</a></li>
            <li><a href="/admin/index.php" class="hover:text-indigo-400">Staff & Admin Gateway</a></li>
          </ul>
        </div>

        <!-- Vertically Arranged Payment Methods (COD, Net Banking, Cards, UPI) -->
        <div>
          <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3 font-display">Accepted Payment Methods</h4>
          <p class="text-[11px] text-slate-400 mb-3">Safe & Bank-Grade Options:</p>
          <div class="flex flex-col space-y-2 text-xs text-slate-300">
            
            <!-- 1. Cash on Delivery -->
            <div class="flex items-center gap-2.5 py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 transition">
              <i data-lucide="banknote" class="w-4 h-4 text-amber-400 shrink-0"></i>
              <div>
                <span class="font-bold text-white block">Cash on Delivery (COD)</span>
                <span class="text-[10px] text-slate-400 block">Pay with cash at your doorstep</span>
              </div>
            </div>

            <!-- 2. Net Banking -->
            <div class="flex items-center gap-2.5 py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 transition">
              <i data-lucide="landmark" class="w-4 h-4 text-emerald-400 shrink-0"></i>
              <div>
                <span class="font-bold text-white block">Net Banking</span>
                <span class="text-[10px] text-slate-400 block">All major Indian banks supported</span>
              </div>
            </div>

            <!-- 3. Cards -->
            <div class="flex items-center gap-2.5 py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 transition">
              <i data-lucide="credit-card" class="w-4 h-4 text-blue-400 shrink-0"></i>
              <div>
                <span class="font-bold text-white block">Cards (Debit / Credit)</span>
                <span class="text-[10px] text-slate-400 block">RuPay, Visa, Mastercard</span>
              </div>
            </div>

            <!-- 4. UPI -->
            <div class="flex items-center gap-2.5 py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 transition">
              <i data-lucide="smartphone" class="w-4 h-4 text-purple-400 shrink-0"></i>
              <div>
                <span class="font-bold text-white block">UPI</span>
                <span class="text-[10px] text-slate-400 block">GPay, PhonePe, Paytm, BHIM</span>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Copyright & Bottom Bar -->
      <div class="border-t border-slate-900 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
        <p>© <?= date('Y') ?> GaunGaliHat — S S Industries Enterprise. All Rights Reserved.</p>
        <div class="flex items-center gap-4">
          <a href="/about.php" class="hover:text-slate-400">Privacy Policy</a>
          <span>•</span>
          <a href="/about.php" class="hover:text-slate-400">Terms of Supply</a>
          <span>•</span>
          <a href="/about.php" class="hover:text-slate-400">Shipping & Return Policy</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Customer Live Chat & WhatsApp Floating Widget -->
  <?php require_once __DIR__ . '/live_chat_widget.php'; ?>

  <!-- Core Main JS -->
  <script src="/assets/js/main.js"></script>

  <!-- Lucide Icons Refresh Trigger -->
  <script>
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  </script>
</body>
</html>
