<?php
/**
 * GaunGaliHat Super Admin Layout Header
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Authorization Check
require_admin();

// Item 16: Central CSRF Protection for all Admin POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

$admin_user = get_logged_in_user();
$current_page = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>सुपर एडमिन नियंत्रण (Central Admin) — GaunGaliHat</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  
  <link rel="stylesheet" href="/assets/css/tailwind.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="/assets/vendor/chart.umd.min.js"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased bg-stone-100">

  <!-- Item 7: Impersonation Notice Banner with POST + CSRF exit -->
  <?= function_exists('render_impersonation_banner') ? render_impersonation_banner() : '' ?>

  <!-- Top Admin Bar -->
  <header class="bg-indigo-950 text-white border-b border-indigo-900 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
        
        <!-- Authentic Brand Logo (No text placeholder) -->
        <div class="flex items-center gap-3">
          <a href="/admin/index.php" class="flex items-center gap-3 group py-1" title="GaunGaliHat Master Admin">
            <div class="h-10 sm:h-12 bg-white/95 px-3 py-1.5 rounded-2xl shadow-sm flex items-center border border-white/20 transition-transform group-hover:scale-105">
              <img src="/logo.png" alt="GaunGaliHat" class="h-7 sm:h-9 w-auto object-contain" />
            </div>
            <div class="hidden md:block border-l border-indigo-800 pl-3">
              <span class="text-xs font-black uppercase tracking-wider text-amber-300 block">केंद्रीय प्रशासनिक नियंत्रण</span>
              <span class="text-[10px] text-indigo-300 block">S S Industries • Central Governance Hub</span>
            </div>
          </a>
        </div>

        <!-- Right Side: User & Links -->
        <div class="flex items-center gap-2 sm:gap-3">
          <a href="/" target="_blank" class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-900/80 hover:bg-indigo-900 text-indigo-200 text-xs font-semibold transition border border-indigo-800">
            <i class="fa-solid fa-store text-[11px]"></i> Live Store (लाइव स्टोर)
          </a>

          <a href="/seller/dashboard.php" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-bold transition shadow-sm">
            <i class="fa-solid fa-hammer text-[11px]"></i> Seller Hub (सेलर हब)
          </a>

          <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-indigo-900">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-800 text-amber-300 font-bold flex items-center justify-center text-xs border border-indigo-700">
              <?php 
                $admin_display_name = !empty($admin_user['name']) ? (string)$admin_user['name'] : 'Admin';
                $admin_display_role = !empty($admin_user['role']) ? (string)$admin_user['role'] : 'admin';
              ?>
              <?php if (!empty($admin_user['avatar'])): ?>
                <img src="<?= htmlspecialchars((string)$admin_user['avatar']) ?>" alt="Admin" class="w-full h-full object-cover">
              <?php else: ?>
                <?= strtoupper(mb_substr($admin_display_name, 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div class="text-right hidden sm:block">
              <div class="text-xs font-bold text-white"><?= htmlspecialchars($admin_display_name) ?></div>
              <div class="text-[10px] text-amber-300 uppercase font-semibold"><?= htmlspecialchars($admin_display_role) ?></div>
            </div>

            <a href="/logout.php" class="p-2 text-indigo-300 hover:text-red-400 rounded-lg hover:bg-indigo-900 transition" title="Sign Out (लॉगआउट)">
              <i class="fa-solid fa-power-off text-sm"></i>
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- Secondary Admin Navigation Tabs -->
    <div class="bg-indigo-900 border-t border-indigo-800 px-3 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto py-1 scrollbar-none text-xs font-bold">
        <a href="/admin/index.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'index.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-chart-pie"></i> Overview (अवलोकन)
        </a>

        <a href="/admin/users.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'users.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-users-gear"></i> Users & Staff (यूजर व स्टाफ)
        </a>

        <a href="/admin/products.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'products.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-boxes-stacked"></i> Products (उत्पाद कैटलॉग)
        </a>

        <a href="/admin/categories.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'categories.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-tags"></i> Categories (श्रेणियां)
        </a>

        <a href="/admin/vendors.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'vendors.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-hammer"></i> Vendors / KYC (विक्रेता)
        </a>

        <a href="/admin/orders.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'orders.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-truck-fast"></i> Orders (आर्डर व डिस्पैच)
        </a>

        <a href="/admin/blogs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'blogs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-newspaper"></i> Blogs CMS (ब्लॉग)
        </a>

        <a href="/admin/tickets.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'tickets.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-headset"></i> Tickets (सपोर्ट टिकट्स)
        </a>

        <a href="/admin/coupons.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'coupons.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-ticket"></i> Coupons (कूपन व डिस्काउंट)
        </a>

        <a href="/admin/hero_customizer.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'hero_customizer.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Hero Campaigns (त्योहार बैनर)
        </a>

        <a href="/admin/live_chat.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'live_chat.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-headset"></i> लाइव चैट कंसोल
        </a>

        <a href="/admin/seo_settings.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'seo_settings.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-magnifying-glass-chart"></i> SEO सेटिंग्स
        </a>

        <a href="/admin/seo_redirects.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'seo_redirects.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-route text-amber-300"></i> रीडायरेक्ट्स व 404
        </a>

        <a href="/admin/social_auth.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'social_auth.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-brands fa-google"></i> सोशल लॉगिन
        </a>

        <a href="/admin/site_customizer.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'site_customizer.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-palette"></i> हेडर व फुटर कस्टमाइज़र
        </a>

        <a href="/admin/site_settings.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'site_settings.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-sliders"></i> गेटवे व शिपिंग
        </a>

        <a href="/admin/merchant_feed.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'merchant_feed.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-brands fa-google text-amber-300"></i> मर्चेंट XML फ़ीड (GMC)
        </a>

        <a href="/admin/integrations.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'integrations.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-network-wired text-amber-300"></i> इंटीग्रेशन्स हब
        </a>

        <a href="/admin/security_center.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'security_center.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-shield-halved text-emerald-400"></i> सुरक्षा केंद्र
        </a>

        <?php if (function_exists('is_super_admin') && is_super_admin()): ?>
        <a href="/admin/security_logs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'security_logs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-satellite-dish text-rose-300"></i> सुरक्षा लॉग्स
        </a>

        <a href="/admin/cache_manager.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'cache_manager.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-broom text-amber-300"></i> कैश मैनेजर
        </a>
        <?php endif; ?>

        <a href="/admin/audit_logs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'audit_logs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-clipboard-check text-indigo-300"></i> ऑडिट लॉग्स
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

  <main class="flex-1 py-5 sm:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
