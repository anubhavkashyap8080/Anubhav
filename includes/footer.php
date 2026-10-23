<?php
/**
 * GaunGaliHat Master Footer
 */
?>
  </main>

  <!-- Value Propositions Banner -->
  <section class="bg-stone-100 border-t border-gray-200 mt-16 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs">
        <div class="flex items-start gap-3.5 p-4 bg-white rounded-2xl border border-gray-100 shadow-2xs">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-brand-green flex items-center justify-center shrink-0">
            <i data-lucide="anvil" class="w-5 h-5"></i>
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
            <p class="text-gray-500 mt-0.5">Secure UPI, Razorpay, PayU, and Cash on Delivery (COD) supported.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Master Footer -->
  <footer class="bg-slate-950 text-white text-xs pt-12 pb-24 lg:pb-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
        
        <!-- Brand & Factory Info -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-brand-green flex items-center justify-center text-amber-300">
              <i data-lucide="anvil" class="w-4 h-4"></i>
            </div>
            <span class="font-extrabold text-xl font-display text-white">GaunGaliHat</span>
          </div>
          <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
            <?= htmlspecialchars(get_setting('company_name')) ?> enterprise initiative dedicated to empowering rural Indian smallholders, village foundries, and women self-help groups by delivering authentic farming tools and agro-produce straight from rural Bharat.
          </p>
          <div class="space-y-1 text-[11px] text-slate-400 font-mono">
            <p>📍 Works: Madhoganj Baghauli Road, Newada, Hardoi, UP - 241126</p>
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

        <!-- Trust & Payment Badges -->
        <div>
          <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3 font-display">Verified Gateways</h4>
          <p class="text-[11px] text-slate-400 mb-3">100% Secure Checkout powered by official integrations:</p>
          <div class="space-y-2">
            <div class="p-2 bg-slate-900 border border-slate-800 rounded-xl flex items-center gap-2">
              <span class="text-xs font-bold text-blue-400 font-mono">⚡ Razorpay</span>
              <span class="text-[10px] text-slate-400">UPI, Netbanking, Cards</span>
            </div>
            <div class="p-2 bg-slate-900 border border-slate-800 rounded-xl flex items-center gap-2">
              <span class="text-xs font-bold text-emerald-400 font-mono">🔒 PayU Money</span>
              <span class="text-[10px] text-slate-400">SHA-512 Encrypted</span>
            </div>
            <div class="p-2 bg-slate-900 border border-slate-800 rounded-xl flex items-center gap-2">
              <span class="text-xs font-bold text-amber-400 font-mono">💵 Cash on Delivery</span>
              <span class="text-[10px] text-slate-400">Pay on Package Arrival</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Copyright -->
      <div class="border-t border-slate-900 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
        <p>© <?= date('Y') ?> GaunGaliHat — S S Industries. All Rights Reserved.</p>
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

  <!-- 6. Mobile Bottom Sticky Navigation Bar -->
  <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200 py-2 px-3 flex items-center justify-around shadow-2xl">
    <a href="/index.php" class="flex flex-col items-center gap-0.5 text-[10px] font-semibold <?= $current_page === 'index.php' ? 'text-brand-green' : 'text-gray-500' ?>">
      <i data-lucide="home" class="w-5 h-5"></i>
      <span>Home</span>
    </a>
    <a href="/products.php" class="flex flex-col items-center gap-0.5 text-[10px] font-semibold <?= $current_page === 'products.php' ? 'text-brand-green' : 'text-gray-500' ?>">
      <i data-lucide="layout-grid" class="w-5 h-5"></i>
      <span>Catalog</span>
    </a>
    <a href="/wishlist.php" class="flex flex-col items-center gap-0.5 text-[10px] font-semibold relative text-gray-500">
      <i data-lucide="heart" class="w-5 h-5"></i>
      <span id="mobile-wishlist-count" class="absolute -top-1 right-2 w-4 h-4 bg-brand-orange text-white text-[9px] font-bold rounded-full flex items-center justify-center <?= $wishlist_count === 0 ? 'hidden' : '' ?>"><?= $wishlist_count ?></span>
      <span>Wishlist</span>
    </a>
    <a href="/cart.php" class="flex flex-col items-center gap-0.5 text-[10px] font-semibold relative text-gray-500">
      <i data-lucide="shopping-bag" class="w-5 h-5"></i>
      <span id="mobile-cart-count" class="absolute -top-1 right-2 w-4 h-4 bg-brand-green text-white text-[9px] font-bold rounded-full flex items-center justify-center <?= $cart_count === 0 ? 'hidden' : '' ?>"><?= $cart_count ?></span>
      <span>Bag</span>
    </a>
    <a href="/account.php" class="flex flex-col items-center gap-0.5 text-[10px] font-semibold text-gray-500">
      <i data-lucide="user" class="w-5 h-5"></i>
      <span>Account</span>
    </a>
  </div>

  <!-- Pincode Modal -->
  <div id="pincode-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
          <i data-lucide="map-pin" class="w-4 h-4 text-brand-orange"></i>
          <span>Check Delivery PIN Code</span>
        </h3>
        <button onclick="closePincodeModal()" class="text-gray-400 hover:text-gray-600">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
      <p class="text-xs text-gray-500">Enter your 6-digit delivery pincode to get live India Post shipping charges & dispatch ETA.</p>
      <form onsubmit="handlePincodeSubmit(event)" class="space-y-3">
        <input 
          type="text" 
          id="modal-pincode-input"
          maxlength="6"
          placeholder="e.g. 241126 or 110001"
          class="w-full p-3 border border-gray-300 rounded-xl text-center text-lg font-mono font-bold tracking-widest text-gray-900 outline-none focus:border-brand-green"
          required
        >
        <button type="submit" class="w-full py-2.5 bg-brand-green text-white font-bold rounded-xl text-xs shadow-xs hover:bg-brand-green-dark cursor-pointer transition-colors">
          Verify & Apply Delivery Rate
        </button>
      </form>
      <div id="modal-pincode-result" class="hidden p-3 rounded-xl text-xs"></div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toast-container" class="fixed bottom-20 lg:bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

  <!-- Scripts -->
  <script src="/assets/js/main.js"></script>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
