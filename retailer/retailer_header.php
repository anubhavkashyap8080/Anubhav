<?php
/**
 * GanvGaliHat Retailer Hub (B2B Wholesale) Layout Header
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Retailer Authorization Check
require_retailer();

// Central CSRF Protection for POST actions in Retailer Portal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

$user = get_logged_in_user();
$current_page = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
$bulk_cart_count = get_bulk_cart_count();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>Retailer Hub — GanvGaliHat</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  
  <!-- Static Compiled Tailwind CSS -->
  <link rel="stylesheet" href="/assets/css/tailwind.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="/assets/css/style.css">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
  
  <style>
    /* Robust Responsive Rules for Retailer Navigation */
    @media (max-width: 767px) {
      .retailer-desktop-only { display: none !important; }
      .retailer-mobile-nav { display: flex !important; }
    }
    @media (min-width: 768px) {
      .retailer-desktop-only { display: block !important; }
      .retailer-mobile-nav { display: none !important; }
    }
  </style>
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased bg-stone-100">

  <!-- Top Retailer Navigation Bar (2-Tier Enterprise B2B Design) -->
  <header class="bg-stone-900 text-white border-b border-stone-800 sticky top-0 z-40 shadow-lg">
    
    <!-- Tier 1: Main Bar (Brand, Store Info, Language, Cart, Profile Actions) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-18 gap-3 sm:gap-4">
        
        <!-- Left: Brand & Retailer Identification -->
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
          <a href="/retailer/dashboard.php" class="flex items-center gap-3 group py-1 shrink-0" title="GanvGaliHat Retailer Hub">
            <div class="h-10 sm:h-11 bg-white/95 px-3 py-1 rounded-xl shadow-xs flex items-center border border-white/20 transition-transform group-hover:scale-105">
              <img src="/logo.png" alt="GanvGaliHat" class="h-7 sm:h-8 w-auto object-contain" />
            </div>
            <div class="hidden sm:block border-l border-stone-700/80 pl-3">
              <span class="text-xs font-black uppercase tracking-wider text-amber-400 block flex items-center gap-1.5">
                <i class="fa-solid fa-boxes-packing"></i> Retailer Hub
              </span>
              <span class="text-[10px] text-stone-400 block font-medium">
                <span class="lang-en">B2B Wholesale Portal</span>
                <span class="lang-hi hidden">थोक व्यापार B2B पोर्टल</span>
              </span>
            </div>
          </a>

          <!-- Store Info Badge (Desktop) -->
          <div class="hidden md:flex items-center gap-2 pl-3 border-l border-stone-800 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-stone-800 border border-stone-700 flex items-center justify-center text-amber-400 text-xs">
              <i class="fa-solid fa-store"></i>
            </div>
            <div class="text-xs">
              <div class="font-bold text-gray-100 truncate max-w-[160px] sm:max-w-[200px]">
                <?= htmlspecialchars($user['business_name'] ?? $user['name'] ?? 'Retailer Store') ?>
              </div>
              <?php if (!empty($user['gstin'])): ?>
                <div class="text-[10px] text-emerald-400 font-mono flex items-center gap-1">
                  <i class="fa-solid fa-circle-check text-[9px]"></i> GST: <?= htmlspecialchars($user['gstin']) ?>
                </div>
              <?php else: ?>
                <div class="text-[10px] text-stone-400 font-medium">GST Not Added</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Right: Language Toggle, Wholesale Cart, Store Link, Logout -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
          
          <!-- Bilingual Switcher -->
          <div class="inline-block scale-90 sm:scale-95">
            <?= render_bilingual_toggle() ?>
          </div>

          <!-- Wholesale Cart -->
          <a href="/retailer/cart.php" 
             class="relative px-3 sm:px-3.5 py-2 bg-stone-800 hover:bg-stone-750 text-amber-400 border border-amber-500/40 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs"
             title="Wholesale Cart">
            <i class="fa-solid fa-cart-flatbed text-sm"></i>
            <span class="hidden sm:inline">
              <span class="lang-en">Wholesale Cart</span>
              <span class="lang-hi hidden">थोक कार्ट</span>
            </span>
            <?php if ($bulk_cart_count > 0): ?>
              <span class="min-w-[20px] h-5 px-1.5 bg-amber-500 text-stone-950 rounded-full flex items-center justify-center text-[11px] font-black">
                <?= $bulk_cart_count ?>
              </span>
            <?php else: ?>
              <span class="text-stone-400 text-[11px] hidden sm:inline">(0)</span>
            <?php endif; ?>
          </a>

          <!-- Public Site Link -->
          <a href="/index.php" target="_blank"
             class="hidden lg:flex items-center gap-1.5 text-xs text-stone-300 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-stone-800 border border-stone-800 hover:border-stone-700 transition"
             title="Open Customer Store in new tab">
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-stone-400"></i>
            <span>
              <span class="lang-en">Customer Store</span>
              <span class="lang-hi hidden">ग्राहक स्टोर</span>
            </span>
          </a>

          <!-- Logout -->
          <a href="/logout.php"
             class="px-2.5 sm:px-3 py-1.5 text-xs font-bold text-red-400 hover:text-red-300 hover:bg-red-950/40 border border-red-900/60 rounded-xl transition flex items-center gap-1.5"
             title="Sign Out">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span class="hidden sm:inline">
              <span class="lang-en">Logout</span>
              <span class="lang-hi hidden">लॉगआउट</span>
            </span>
          </a>
        </div>
      </div>
    </div>

    <!-- Tier 2: Dedicated Navigation Bar (Desktop & Tablet) -->
    <div class="retailer-desktop-only bg-stone-950/90 border-t border-stone-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 py-2 text-xs font-bold">
          <a href="/retailer/dashboard.php"
             class="px-4 py-2 rounded-xl transition flex items-center gap-2 <?= $current_page === 'dashboard.php' ? 'bg-amber-500 text-stone-950 font-black shadow-sm' : 'text-stone-300 hover:text-white hover:bg-stone-850' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span><span class="lang-en">Dashboard</span><span class="lang-hi hidden">डैशबोर्ड</span></span>
          </a>
          <a href="/retailer/products.php"
             class="px-4 py-2 rounded-xl transition flex items-center gap-2 <?= $current_page === 'products.php' ? 'bg-amber-500 text-stone-950 font-black shadow-sm' : 'text-stone-300 hover:text-white hover:bg-stone-850' ?>">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span><span class="lang-en">Wholesale Catalog</span><span class="lang-hi hidden">थोक कैटलॉग</span></span>
          </a>
          <a href="/retailer/orders.php"
             class="px-4 py-2 rounded-xl transition flex items-center gap-2 <?= $current_page === 'orders.php' ? 'bg-amber-500 text-stone-950 font-black shadow-sm' : 'text-stone-300 hover:text-white hover:bg-stone-850' ?>">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <span><span class="lang-en">Bulk Orders & Invoices</span><span class="lang-hi hidden">बल्क ऑर्डर्स व बिल</span></span>
          </a>
          <a href="/retailer/profile.php"
             class="px-4 py-2 rounded-xl transition flex items-center gap-2 <?= $current_page === 'profile.php' ? 'bg-amber-500 text-stone-950 font-black shadow-sm' : 'text-stone-300 hover:text-white hover:bg-stone-850' ?>">
            <i class="fa-solid fa-id-card"></i>
            <span><span class="lang-en">Business Profile</span><span class="lang-hi hidden">व्यापार प्रोफाइल</span></span>
          </a>
        </nav>
      </div>
    </div>

    <!-- Mobile Bottom Tab Navigation (Only visible on screens < 768px) -->
    <div class="retailer-mobile-nav border-t border-stone-800 bg-stone-950 px-3 py-2.5 flex items-center justify-between text-xs font-bold gap-2">
      <a href="/retailer/dashboard.php" class="flex-1 flex flex-col items-center py-2 px-1 rounded-xl transition <?= $current_page === 'dashboard.php' ? 'bg-amber-500 text-stone-950 font-black shadow-xs' : 'text-stone-400 hover:text-white' ?>">
        <i class="fa-solid fa-chart-pie text-sm mb-1"></i>
        <span class="text-[11px]"><span class="lang-en">Dashboard</span><span class="lang-hi hidden">डैशबोर्ड</span></span>
      </a>
      <a href="/retailer/products.php" class="flex-1 flex flex-col items-center py-2 px-1 rounded-xl transition <?= $current_page === 'products.php' ? 'bg-amber-500 text-stone-950 font-black shadow-xs' : 'text-stone-400 hover:text-white' ?>">
        <i class="fa-solid fa-boxes-stacked text-sm mb-1"></i>
        <span class="text-[11px]"><span class="lang-en">Catalog</span><span class="lang-hi hidden">कैटलॉग</span></span>
      </a>
      <a href="/retailer/orders.php" class="flex-1 flex flex-col items-center py-2 px-1 rounded-xl transition <?= $current_page === 'orders.php' ? 'bg-amber-500 text-stone-950 font-black shadow-xs' : 'text-stone-400 hover:text-white' ?>">
        <i class="fa-solid fa-truck-ramp-box text-sm mb-1"></i>
        <span class="text-[11px]"><span class="lang-en">Orders</span><span class="lang-hi hidden">ऑर्डर्स</span></span>
      </a>
      <a href="/retailer/profile.php" class="flex-1 flex flex-col items-center py-2 px-1 rounded-xl transition <?= $current_page === 'profile.php' ? 'bg-amber-500 text-stone-950 font-black shadow-xs' : 'text-stone-400 hover:text-white' ?>">
        <i class="fa-solid fa-id-card text-sm mb-1"></i>
        <span class="text-[11px]"><span class="lang-en">Profile</span><span class="lang-hi hidden">प्रोफाइल</span></span>
      </a>
    </div>
  </header>

  <!-- Flash Messages -->
  <?php if ($flash): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="p-4 rounded-2xl text-xs font-bold shadow-xs flex items-center gap-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?> text-base"></i>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
