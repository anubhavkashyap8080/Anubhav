<?php
/**
 * GaunGaliHat Master Header
 * Toolsvilla-standard responsive e-commerce header with Live AJAX Search
 */

require_once __DIR__ . '/functions.php';

$current_user = get_logged_in_user();
$cart_count = get_cart_count();
$wishlist_count = get_wishlist_count();
$current_page = basename($_SERVER['PHP_SELF']);

// Load categories for search dropdown & nav
$header_categories = [];
try {
    $stmt = $pdo->query("SELECT id, name, slug, icon_name FROM categories ORDER BY sort_order ASC, name ASC");
    $header_categories = $stmt->fetchAll();
} catch (Exception $e) {}

$page_title = isset($page_title) ? $page_title . ' — ' . APP_NAME : APP_NAME . ' — Indian Rural Artisan Tools & Farm Produce';
$page_desc = isset($page_desc) ? $page_desc : 'High-durability hand-forged agricultural tools, unpolished pulses, Banarasi pickles, and authentic handlooms direct from Indian village clusters.';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              green: '#1b4332',
              'green-dark': '#0f291e',
              orange: '#d97706',
              cream: '#fdfbf7',
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
            display: ['Outfit', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <!-- Google Fonts & Lucide Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Yatra+One&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- Chart.js for Dashboards -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="flex flex-col min-h-screen text-gray-800 antialiased selection:bg-emerald-200">

  <!-- 1. Top Announcement Bar -->
  <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 text-white text-[11px] py-1.5 px-4 sm:px-8 border-b border-emerald-900/40">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1 text-center sm:text-left">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 font-semibold text-amber-300">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
          <span><?= htmlspecialchars(APP_COMPANY) ?> Enterprise</span>
        </span>
        <span class="hidden md:inline text-gray-400">|</span>
        <span class="hidden md:inline text-gray-200"><?= htmlspecialchars(get_setting('announcement_text')) ?></span>
      </div>
      <div class="flex items-center gap-4 text-emerald-200">
        <a href="tel:<?= preg_replace('/\s+/', '', APP_PHONE) ?>" class="hover:text-white flex items-center gap-1">
          <i data-lucide="phone" class="w-3 h-3 text-amber-300"></i>
          <span><?= htmlspecialchars(APP_PHONE) ?></span>
        </a>
        <span>•</span>
        <a href="/seller/dashboard.php" class="hover:text-amber-300 flex items-center gap-1 font-bold text-white transition-colors">
          <i data-lucide="store" class="w-3 h-3 text-brand-orange"></i>
          <span>Seller Hub</span>
        </a>
      </div>
    </div>
  </div>

  <!-- 2. Primary Navigation Header -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3 sm:gap-6 h-18">
        
        <!-- Mobile Menu Toggle Button -->
        <button id="mobile-menu-open" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Brand Logo -->
        <a href="/index.php" class="flex items-center gap-2.5 shrink-0 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-800 to-brand-green flex items-center justify-center text-white shadow-md group-hover:scale-105 transition-transform">
            <i data-lucide="anvil" class="w-5 h-5 text-amber-300"></i>
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="font-extrabold text-xl sm:text-2xl font-display tracking-tight text-gray-900">
                Gaun<span class="text-brand-orange">Gali</span><span class="text-brand-green">Hat</span>
              </span>
            </div>
            <p class="text-[9px] font-bold text-gray-400 tracking-wider uppercase -mt-1">S S Industries • Hardoi</p>
          </div>
        </a>

        <!-- Delivery Pincode Indicator -->
        <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs text-gray-600 cursor-pointer hover:border-brand-green transition-colors" onclick="openPincodeModal()">
          <i data-lucide="map-pin" class="w-4 h-4 text-brand-orange shrink-0"></i>
          <div class="text-left leading-tight">
            <span class="text-[10px] text-gray-400 block">Deliver to</span>
            <span class="font-bold text-gray-900" id="header-pincode-display"><?= htmlspecialchars($_SESSION['shipping_pincode'] ?? 'Hardoi 241126') ?></span>
          </div>
        </div>

        <!-- 3. AJAX Live Search Bar -->
        <div class="flex-1 max-w-2xl relative">
          <form action="/products.php" method="GET" class="flex items-center border-2 border-brand-green/80 rounded-2xl bg-white shadow-inner focus-within:ring-2 focus-within:ring-brand-green/20 transition-all overflow-hidden">
            <!-- Category Filter Dropdown inside search -->
            <select name="category" id="search-category-select" class="hidden sm:block text-xs font-semibold bg-gray-50 text-gray-700 px-3 py-2.5 border-r border-gray-200 outline-none cursor-pointer hover:bg-gray-100">
              <option value="">All Categories</option>
              <?php foreach ($header_categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Search Input -->
            <div class="relative flex-1 flex items-center">
              <input 
                type="text" 
                name="q" 
                id="header-live-search-input"
                placeholder="Search Hansiya, Khurpi, Desi Dal, Banarasi Achar, Jute Bags..." 
                autocomplete="off"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 outline-none bg-transparent"
              >
              <button type="button" id="search-clear-btn" class="hidden text-gray-400 hover:text-gray-600 p-1 mr-1">
                <i data-lucide="x" class="w-4 h-4"></i>
              </button>
            </div>

            <!-- Search Submit Button -->
            <button type="submit" class="px-4 sm:px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-colors cursor-pointer shrink-0">
              <i data-lucide="search" class="w-4 h-4"></i>
              <span class="hidden sm:inline">Search</span>
            </button>
          </form>

          <!-- Live AJAX Results Dropdown -->
          <div id="live-search-dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 animate-in fade-in duration-150">
            <div id="live-search-spinner" class="hidden p-4 text-center text-xs text-gray-500">
              <span class="inline-block animate-spin mr-1.5">⏳</span> Searching artisan workshops...
            </div>
            <div id="live-search-items" class="max-h-96 overflow-y-auto divide-y divide-gray-100"></div>
            <a href="#" id="live-search-view-all" class="block p-3 bg-stone-50 hover:bg-emerald-50 text-center text-xs font-bold text-brand-green border-t border-gray-100">
              View all matching products →
            </a>
          </div>
        </div>

        <!-- 4. Header Actions (Wishlist, Cart, Account, Seller) -->
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
          
          <!-- Wishlist -->
          <a href="/wishlist.php" class="relative p-2 text-gray-700 hover:text-brand-orange hover:bg-orange-50 rounded-xl transition-colors hidden sm:flex items-center">
            <i data-lucide="heart" class="w-5 h-5"></i>
            <span id="header-wishlist-count" class="absolute -top-1 -right-1 w-5 h-5 bg-brand-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center <?= $wishlist_count === 0 ? 'hidden' : '' ?>">
              <?= $wishlist_count ?>
            </span>
          </a>

          <!-- Cart -->
          <a href="/cart.php" class="relative p-2 text-gray-700 hover:text-brand-green hover:bg-emerald-50 rounded-xl transition-colors flex items-center">
            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            <span id="header-cart-count" class="absolute -top-1 -right-1 w-5 h-5 bg-brand-green text-white text-[10px] font-bold rounded-full flex items-center justify-center <?= $cart_count === 0 ? 'hidden' : '' ?>">
              <?= $cart_count ?>
            </span>
          </a>

          <!-- User Profile / Auth Dropdown -->
          <div class="relative group">
            <?php if ($current_user): ?>
              <button class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-brand-green font-bold text-xs flex items-center justify-center border border-emerald-300">
                  <?= strtoupper(substr($current_user['name'], 0, 1)) ?>
                </div>
                <div class="hidden lg:block text-left text-xs leading-tight">
                  <span class="font-bold text-gray-900 block truncate max-w-[110px]"><?= htmlspecialchars($current_user['name']) ?></span>
                  <span class="text-[10px] text-gray-400 capitalize"><?= htmlspecialchars($current_user['role']) ?></span>
                </div>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden lg:block"></i>
              </button>
            <?php else: ?>
              <a href="/login.php" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-brand-green border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Sign In</span>
              </a>
            <?php endif; ?>

            <!-- Dropdown Menu -->
            <?php if ($current_user): ?>
              <div class="absolute right-0 top-full mt-1.5 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 hidden group-hover:block z-50">
                <div class="p-2 border-b border-gray-100">
                  <p class="font-bold text-xs text-gray-900"><?= htmlspecialchars($current_user['name']) ?></p>
                  <p class="text-[11px] text-gray-400 font-mono"><?= htmlspecialchars($current_user['phone']) ?></p>
                </div>
                <a href="/account.php" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-lg">
                  <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> My Orders & Profile
                </a>
                <?php if (in_array($current_user['role'], ['vendor', 'admin', 'super_admin'])): ?>
                  <a href="/seller/dashboard.php" class="flex items-center gap-2 px-3 py-2 text-xs font-bold text-brand-orange hover:bg-orange-50 rounded-lg">
                    <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i> Artisan Workshop Hub
                  </a>
                <?php endif; ?>
                <?php if (in_array($current_user['role'], ['admin', 'super_admin'])): ?>
                  <a href="/admin/index.php" class="flex items-center gap-2 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-50 rounded-lg">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i> Admin Governance
                  </a>
                <?php endif; ?>
                <a href="/logout.php" class="flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 rounded-lg mt-1 border-t border-gray-100">
                  <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                </a>
              </div>
            <?php endif; ?>
          </div>

        </div>

      </div>
    </div>

    <!-- 5. Category Navigation Bar -->
    <nav class="hidden lg:block bg-stone-50 border-t border-gray-200/80 px-4 sm:px-8 py-2 text-xs">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-6 overflow-x-auto scrollbar-none font-semibold text-gray-700">
        <a href="/products.php" class="hover:text-brand-green flex items-center gap-1.5 shrink-0 py-1 font-bold text-gray-900">
          <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-brand-green"></i>
          <span>All Products</span>
        </a>
        <a href="/products.php?category=farming-traditional-tools" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1">
          <span>🌾 Farming Tools</span>
        </a>
        <a href="/products.php?category=pulses-organic-grains" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1 text-emerald-800 font-bold">
          <span>🥣 Desi Pulses & Grains</span>
        </a>
        <a href="/products.php?category=pickles-village-murabba" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1 text-amber-800 font-bold">
          <span>🫙 Village Pickles & Murabba</span>
        </a>
        <a href="/products.php?category=hardware-cottage-implements" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1">
          <span>🔨 Ironware & Workshop</span>
        </a>
        <a href="/products.php?category=jute-bags-cloth-jholas" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1">
          <span>🛍️ Eco Jute Bags</span>
        </a>
        <a href="/products.php?category=village-home-decor-toys" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1">
          <span>🪵 Wooden Toys & Decor</span>
        </a>
        <a href="/products.php?category=handmade-apparel-textiles" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1">
          <span>👕 Handloom Khadi</span>
        </a>
        <a href="/products.php?category=handmade-by-rural-women" class="hover:text-brand-green flex items-center gap-1 shrink-0 py-1 text-brand-orange font-bold">
          <span>✨ Women SHG Crafts</span>
        </a>
      </div>
    </nav>
  </header>

  <!-- Mobile Drawer Menu -->
  <div id="mobile-menu-drawer" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden lg:hidden">
    <div class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl p-6 flex flex-col justify-between overflow-y-auto">
      <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
          <span class="font-extrabold text-lg font-display text-gray-900">
            Gaun<span class="text-brand-orange">Gali</span><span class="text-brand-green">Hat</span>
          </span>
          <button id="mobile-menu-close" class="p-1 text-gray-400 hover:text-gray-700">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="space-y-1 text-sm font-semibold">
          <a href="/index.php" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">Home</a>
          <a href="/products.php" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">All Products Catalog</a>
          <a href="/products.php?category=farming-traditional-tools" class="block px-3 py-2 rounded-xl hover:bg-emerald-50 text-emerald-800 font-bold">🌾 Agricultural Tools</a>
          <a href="/products.php?category=pulses-organic-grains" class="block px-3 py-2 rounded-xl hover:bg-emerald-50 text-emerald-900 font-bold">🥣 Desi Pulses & Dal</a>
          <a href="/products.php?category=pickles-village-murabba" class="block px-3 py-2 rounded-xl hover:bg-amber-50 text-amber-900 font-bold">🫙 Village Pickles & Murabba</a>
          <a href="/products.php?category=jute-bags-cloth-jholas" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">🛍️ Eco Jute Bags</a>
          <a href="/products.php?category=village-home-decor-toys" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">🪵 Wooden Toys & Chatai</a>
          <a href="/products.php?category=handmade-by-rural-women" class="block px-3 py-2 rounded-xl hover:bg-orange-50 text-brand-orange font-bold">✨ Women SHG Products</a>
          <a href="/about.php" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">About S S Industries</a>
          <a href="/contact.php" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">Contact & Support</a>
        </div>
      </div>

      <div class="pt-6 border-t border-gray-100 space-y-2">
        <?php if ($current_user): ?>
          <a href="/account.php" class="w-full py-2.5 bg-gray-100 text-gray-800 font-bold rounded-xl text-center text-xs block">My Account</a>
          <a href="/logout.php" class="w-full py-2.5 bg-red-50 text-red-700 font-bold rounded-xl text-center text-xs block">Sign Out</a>
        <?php else: ?>
          <a href="/login.php" class="w-full py-2.5 bg-brand-green text-white font-bold rounded-xl text-center text-xs block">Shopper Sign In</a>
          <a href="/login.php?tab=seller" class="w-full py-2.5 bg-brand-orange text-white font-bold rounded-xl text-center text-xs block">Artisan & Seller Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Flash Message Banner -->
  <?php $flash = get_flash(); if ($flash): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-900' ?>">
        <div class="flex items-center gap-2">
          <i data-lucide="<?= $flash['type'] === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-4 h-4 shrink-0"></i>
          <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content Wrapper -->
  <main class="flex-1">
