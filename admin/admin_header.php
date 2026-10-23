<?php
/**
 * GaunGaliHat Super Admin Layout Header
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Authorization Check
require_admin();

$admin_user = get_logged_in_user();
$current_page = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="hi" class="h-full bg-stone-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>सुपर एडमिन नियंत्रण (Central Admin) — GaunGaliHat</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'brand-green': '#1b4332',
            'brand-dark': '#081c15',
            'admin-indigo': '#4338ca',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased bg-stone-100">

  <!-- Top Admin Bar -->
  <header class="bg-indigo-950 text-white border-b border-indigo-900 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        
        <!-- Brand & Title -->
        <div class="flex items-center gap-3">
          <a href="/admin/index.php" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-sm">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
              <span class="text-sm font-extrabold tracking-tight text-white block">GaunGaliHat Control</span>
              <span class="text-[10px] uppercase tracking-wider text-indigo-300 font-bold block">प्रशासनिक पोर्टल (Enterprise Admin)</span>
            </div>
          </a>
        </div>

        <!-- Right Side: User & Links -->
        <div class="flex items-center gap-3">
          <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-900/80 hover:bg-indigo-900 text-indigo-200 text-xs font-semibold transition border border-indigo-800">
            <i class="fa-solid fa-store text-[11px]"></i> स्टोर देखें
          </a>

          <a href="/seller/dashboard.php" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-900 text-xs font-bold transition shadow-sm">
            <i class="fa-solid fa-hammer text-[11px]"></i> विक्रेता हब
          </a>

          <div class="flex items-center gap-3 pl-3 border-l border-indigo-900">
            <div class="text-right hidden sm:block">
              <div class="text-xs font-bold text-white"><?= htmlspecialchars($admin_user['name']) ?></div>
              <div class="text-[10px] text-indigo-300 uppercase font-semibold"><?= htmlspecialchars($admin_user['role']) ?></div>
            </div>

            <a href="/logout.php" class="p-2 text-indigo-300 hover:text-red-400 rounded-lg hover:bg-indigo-900 transition" title="लॉगआउट">
              <i class="fa-solid fa-power-off text-sm"></i>
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- Secondary Admin Navigation Tabs -->
    <div class="bg-indigo-900 border-t border-indigo-800 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto py-1 scrollbar-none text-xs font-bold">
        <a href="/admin/index.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'index.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-chart-pie"></i> अवलोकन (Overview)
        </a>

        <a href="/admin/users.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'users.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-users-gear"></i> यूजर व स्टाफ (Users & Passwords)
        </a>

        <a href="/admin/products.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'products.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-boxes-stacked"></i> कैटलॉग (Products)
        </a>

        <a href="/admin/categories.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'categories.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-tags"></i> श्रेणियां (Categories)
        </a>

        <a href="/admin/vendors.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'vendors.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-hammer"></i> कारीगर व विक्रेता (Vendors)
        </a>

        <a href="/admin/orders.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'orders.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-truck-fast"></i> आर्डर व डिस्पैच (Orders)
        </a>

        <a href="/admin/site_settings.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'site_settings.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-sliders"></i> गेटवे व सेटिंग्स (Settings)
        </a>
      </div>
    </div>
  </header>

  <!-- Flash Notification -->
  <?php if ($flash): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="p-4 rounded-2xl text-xs font-semibold flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-base flex-shrink-0"></i>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
