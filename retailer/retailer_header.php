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
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased bg-stone-100">

  <!-- Top Retailer Navigation Bar -->
  <header class="bg-stone-900 text-white border-b border-stone-800 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
        
        <!-- Brand & B2B Badge -->
        <div class="flex items-center gap-3 sm:gap-4">
          <a href="/retailer/dashboard.php" class="flex items-center gap-3 group py-1" title="GanvGaliHat Retailer Hub">
            <div class="h-10 sm:h-12 bg-white/95 px-3 py-1.5 rounded-2xl shadow-sm flex items-center border border-white/20 transition-transform group-hover:scale-105">
              <img src="/logo.png" alt="GanvGaliHat" class="h-7 sm:h-9 w-auto object-contain" />
            </div>
            <div class="hidden sm:block border-l border-stone-700 pl-3">
              <span class="text-xs font-black uppercase tracking-wider text-amber-400 block flex items-center gap-1.5">
                <i class="fa-solid fa-boxes-packing"></i> Retailer Hub
              </span>
              <span class="text-[10px] text-stone-400 block">
                <span class="lang-en">B2B Wholesale & Bulk Sourcing Portal</span>
                <span class="lang-hi hidden">थोक व्यापार एवं B2B खरीद पोर्टल</span>
              </span>
            </div>
          </a>

          <div class="hidden lg:flex items-center gap-2 pl-3 border-l border-stone-700">
            <div class="text-xs">
              <div class="font-bold text-gray-200 flex items-center gap-1.5">
                <i class="fa-solid fa-store text-amber-400"></i>
                <span><?= htmlspecialchars($user['business_name'] ?? $user['name'] ?? 'Retailer Store') ?></span>
              </div>
              <?php if (!empty($user['gstin'])): ?>
                <div class="text-[10px] text-emerald-400 font-mono flex items-center gap-1">
                  <i class="fa-solid fa-circle-check text-[9px]"></i> GST: <?= htmlspecialchars($user['gstin']) ?>
                </div>
              <?php else: ?>
                <div class="text-[10px] text-stone-400">GST Not Added</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Center Nav Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-bold">
          <a href="/retailer/dashboard.php"
             class="px-3 py-2 rounded-xl transition <?= $current_page === 'dashboard.php' ? 'bg-amber-500 text-stone-950 font-black' : 'text-stone-300 hover:text-white hover:bg-stone-800' ?>">
            <i class="fa-solid fa-chart-pie mr-1.5"></i>
            <span class="lang-en">Dashboard</span>
            <span class="lang-hi hidden">डैशबोर्ड</span>
          </a>
          <a href="/retailer/products.php"
             class="px-3 py-2 rounded-xl transition <?= $current_page === 'products.php' ? 'bg-amber-500 text-stone-950 font-black' : 'text-stone-300 hover:text-white hover:bg-stone-800' ?>">
            <i class="fa-solid fa-boxes-stacked mr-1.5"></i>
            <span class="lang-en">Wholesale Catalog</span>
            <span class="lang-hi hidden">थोक कैटलॉग</span>
          </a>
          <a href="/retailer/orders.php"
             class="px-3 py-2 rounded-xl transition <?= $current_page === 'orders.php' ? 'bg-amber-500 text-stone-950 font-black' : 'text-stone-300 hover:text-white hover:bg-stone-800' ?>">
            <i class="fa-solid fa-truck-ramp-box mr-1.5"></i>
            <span class="lang-en">Bulk Orders & Invoices</span>
            <span class="lang-hi hidden">बल्क ऑर्डर्स व बिल</span>
          </a>
          <a href="/retailer/profile.php"
             class="px-3 py-2 rounded-xl transition <?= $current_page === 'profile.php' ? 'bg-amber-500 text-stone-950 font-black' : 'text-stone-300 hover:text-white hover:bg-stone-800' ?>">
            <i class="fa-solid fa-id-card mr-1.5"></i>
            <span class="lang-en">Business Profile</span>
            <span class="lang-hi hidden">व्यापार प्रोफाइल</span>
          </a>
        </nav>

        <!-- Right Side: Bilingual Switcher, Bulk Cart & Controls -->
        <div class="flex items-center gap-2 sm:gap-3">
          
          <div class="inline-block scale-90">
            <?= render_bilingual_toggle() ?>
          </div>

          <!-- Bulk Wholesale Cart -->
          <a href="/retailer/cart.php" class="relative px-3 py-2 bg-stone-800 hover:bg-stone-700 text-amber-400 border border-amber-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-cart-flatbed text-sm"></i>
            <span class="hidden sm:inline">
              <span class="lang-en">Wholesale Cart</span>
              <span class="lang-hi hidden">थोक कार्ट</span>
            </span>
            <?php if ($bulk_cart_count > 0): ?>
              <span class="w-5 h-5 bg-amber-500 text-stone-950 rounded-full flex items-center justify-center text-[11px] font-black">
                <?= $bulk_cart_count ?>
              </span>
            <?php endif; ?>
          </a>

          <!-- Public Site Link -->
          <a href="/index.php" target="_blank"
             class="hidden sm:flex items-center gap-1 text-xs text-stone-300 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-stone-800 transition"
             title="Visit Public Marketplace">
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            <span>
              <span class="lang-en">Customer Store</span>
              <span class="lang-hi hidden">ग्राहक स्टोर</span>
            </span>
          </a>

          <!-- Logout -->
          <a href="/logout.php"
             class="px-3 py-1.5 text-xs font-bold text-red-400 hover:text-red-300 hover:bg-red-950/40 border border-red-900/50 rounded-xl transition flex items-center gap-1"
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

    <!-- Mobile Sub-Navigation -->
    <div class="md:hidden border-t border-stone-800 bg-stone-950/70 px-3 py-2 flex items-center justify-around text-[11px] font-bold">
      <a href="/retailer/dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'text-amber-400' : 'text-stone-400' ?>">
        <i class="fa-solid fa-chart-pie block text-center text-sm mb-0.5"></i>
        <span class="lang-en">Dashboard</span>
        <span class="lang-hi hidden">डैशबोर्ड</span>
      </a>
      <a href="/retailer/products.php" class="<?= $current_page === 'products.php' ? 'text-amber-400' : 'text-stone-400' ?>">
        <i class="fa-solid fa-boxes-stacked block text-center text-sm mb-0.5"></i>
        <span class="lang-en">Wholesale</span>
        <span class="lang-hi hidden">थोक कैटलॉग</span>
      </a>
      <a href="/retailer/orders.php" class="<?= $current_page === 'orders.php' ? 'text-amber-400' : 'text-stone-400' ?>">
        <i class="fa-solid fa-truck-ramp-box block text-center text-sm mb-0.5"></i>
        <span class="lang-en">Orders</span>
        <span class="lang-hi hidden">ऑर्डर्स</span>
      </a>
      <a href="/retailer/profile.php" class="<?= $current_page === 'profile.php' ? 'text-amber-400' : 'text-stone-400' ?>">
        <i class="fa-solid fa-id-card block text-center text-sm mb-0.5"></i>
        <span class="lang-en">Profile</span>
        <span class="lang-hi hidden">प्रोफाइल</span>
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
