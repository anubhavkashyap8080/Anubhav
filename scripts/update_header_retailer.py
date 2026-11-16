with open('includes/header.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Utility top bar
needle_top = """        <a href="/seller/dashboard.php" class="hover:text-amber-300 flex items-center gap-1 font-bold text-white transition-colors" title="Artisan & Seller Hub">
          <i data-lucide="store" class="w-3.5 h-3.5 text-brand-orange shrink-0"></i>
          <span>Seller Hub</span>
        </a>"""

replacement_top = """        <a href="/seller/dashboard.php" class="hover:text-amber-300 flex items-center gap-1 font-bold text-white transition-colors" title="Artisan & Seller Hub">
          <i data-lucide="store" class="w-3.5 h-3.5 text-brand-orange shrink-0"></i>
          <span>Seller Hub</span>
        </a>
        <span class="text-emerald-700 hidden sm:inline">•</span>
        <a href="/retailer/dashboard.php" class="hover:text-amber-300 flex items-center gap-1 font-bold text-amber-300 transition-colors" title="Retailer B2B Wholesale Hub">
          <i class="fa-solid fa-boxes-packing text-amber-400 shrink-0 text-xs"></i>
          <span>Retailer Hub (थोक खरीद)</span>
        </a>"""

if needle_top in content:
    content = content.replace(needle_top, replacement_top)
else:
    needle_top_crlf = needle_top.replace('\n', '\r\n')
    replacement_top_crlf = replacement_top.replace('\n', '\r\n')
    content = content.replace(needle_top_crlf, replacement_top_crlf)

# 2. Dedicated Retailer Button in header actions
needle_actions = """          <!-- Wishlist Icon -->"""

replacement_actions = """          <!-- Dedicated Retailer B2B Button -->
          <?php if ($current_user && $current_user['role'] === 'retailer'): ?>
            <a
              href="/retailer/dashboard.php"
              class="hidden sm:inline-flex items-center gap-1 px-2 sm:px-2.5 lg:px-3 py-1 sm:py-1.5 rounded-lg border border-amber-500 text-stone-950 bg-amber-400 hover:bg-amber-500 transition-all text-xs font-black shadow-xs whitespace-nowrap shrink-0"
              title="Retailer B2B Dashboard"
            >
              <i class="fa-solid fa-boxes-packing text-xs"></i>
              <span>Retailer Hub</span>
            </a>
          <?php endif; ?>

          <!-- Wishlist Icon -->"""

if needle_actions in content:
    content = content.replace(needle_actions, replacement_actions)
else:
    needle_actions_crlf = needle_actions.replace('\n', '\r\n')
    replacement_actions_crlf = replacement_actions.replace('\n', '\r\n')
    content = content.replace(needle_actions_crlf, replacement_actions_crlf)

# 3. User Dropdown Menu
needle_drop = """                  <?php if ($current_user['role'] === 'vendor' || in_array($current_user['role'], ['super_admin', 'admin'])): ?>
                    <a href="/seller/dashboard.php" class="flex items-center gap-2 px-4 py-2 font-bold text-brand-orange hover:bg-orange-50">
                      <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i> Seller Workshop Dashboard
                    </a>
                  <?php endif; ?>"""

replacement_drop = """                  <?php if ($current_user['role'] === 'vendor' || in_array($current_user['role'], ['super_admin', 'admin'])): ?>
                    <a href="/seller/dashboard.php" class="flex items-center gap-2 px-4 py-2 font-bold text-brand-orange hover:bg-orange-50">
                      <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i> Seller Workshop Dashboard
                    </a>
                  <?php endif; ?>

                  <?php if ($current_user['role'] === 'retailer' || in_array($current_user['role'], ['super_admin', 'admin'])): ?>
                    <a href="/retailer/dashboard.php" class="flex items-center gap-2 px-4 py-2 font-bold text-amber-800 hover:bg-amber-50">
                      <i class="fa-solid fa-boxes-packing text-amber-600 w-4 h-4 text-center"></i> Retailer Hub (थोक डैशबोर्ड)
                    </a>
                  <?php endif; ?>"""

if needle_drop in content:
    content = content.replace(needle_drop, replacement_drop)
else:
    needle_drop_crlf = needle_drop.replace('\n', '\r\n')
    replacement_drop_crlf = replacement_drop.replace('\n', '\r\n')
    content = content.replace(needle_drop_crlf, replacement_drop_crlf)

# 4. Guest dropdown links
needle_guest = """                  <a href="/login.php?tab=seller" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-orange-50 hover:bg-orange-100 text-brand-orange border border-brand-orange/30 rounded-xl text-xs font-bold transition-colors">
                    <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i>
                    <span>Artisan & Seller Login</span>
                  </a>"""

replacement_guest = """                  <a href="/login.php?tab=seller" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-orange-50 hover:bg-orange-100 text-brand-orange border border-brand-orange/30 rounded-xl text-xs font-bold transition-colors">
                    <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i>
                    <span>Artisan & Seller Login</span>
                  </a>

                  <a href="/login.php?tab=retailer" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 rounded-xl text-xs font-bold transition-colors">
                    <i class="fa-solid fa-boxes-packing text-amber-600"></i>
                    <span>Retailer (थोक खरीद) Login</span>
                  </a>"""

if needle_guest in content:
    content = content.replace(needle_guest, replacement_guest)
else:
    needle_guest_crlf = needle_guest.replace('\n', '\r\n')
    replacement_guest_crlf = replacement_guest.replace('\n', '\r\n')
    content = content.replace(needle_guest_crlf, replacement_guest_crlf)

# 5. Mobile Drawer
needle_mob = """          <a href="/login.php?tab=seller" class="w-full py-2.5 bg-brand-orange text-white font-bold rounded-xl text-center text-xs block shadow-sm">
            Artisan & Seller Login
          </a>"""

replacement_mob = """          <a href="/login.php?tab=seller" class="w-full py-2.5 bg-brand-orange text-white font-bold rounded-xl text-center text-xs block shadow-sm">
            Artisan & Seller Login
          </a>
          <a href="/login.php?tab=retailer" class="w-full py-2.5 bg-amber-500 text-stone-950 font-black rounded-xl text-center text-xs block shadow-sm">
            📦 Retailer & Wholesale Login
          </a>"""

if needle_mob in content:
    content = content.replace(needle_mob, replacement_mob)
else:
    needle_mob_crlf = needle_mob.replace('\n', '\r\n')
    replacement_mob_crlf = replacement_mob.replace('\n', '\r\n')
    content = content.replace(needle_mob_crlf, replacement_mob_crlf)

with open('includes/header.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated includes/header.php successfully")
