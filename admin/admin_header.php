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
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  
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

  <!-- Impersonation Notice Banner -->
  <?php if (!empty($_SESSION['impersonator_id'])): ?>
    <div class="bg-amber-500 text-stone-950 px-4 py-2 text-xs font-bold flex flex-wrap items-center justify-between shadow-md z-50 sticky top-0 border-b border-amber-600">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-user-secret text-base"></i>
        <span>आप <strong><?= htmlspecialchars($admin_user['name']) ?></strong> (@<?= htmlspecialchars($admin_user['username']) ?>) के रूप में कार्य कर रहे हैं। मूल एडमिन: <?= htmlspecialchars($_SESSION['impersonator_name'] ?? 'Super Admin') ?></span>
      </div>
      <a href="/admin/impersonate.php?action=exit" class="px-3 py-1 bg-stone-950 text-white rounded-lg hover:bg-black transition flex items-center gap-1.5 shadow-xs text-xs font-bold">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        एडमिन पैनल पर वापस लौटें (Exit Impersonation)
      </a>
    </div>
  <?php endif; ?>

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
            <i class="fa-solid fa-store text-[11px]"></i> लाइव स्टोर
          </a>

          <a href="/seller/dashboard.php" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-bold transition shadow-sm">
            <i class="fa-solid fa-store text-[11px]"></i> सेलर हब
          </a>

          <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-indigo-900">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-800 text-amber-300 font-bold flex items-center justify-center text-xs border border-indigo-700">
              <?php if (!empty($admin_user['avatar'])): ?>
                <img src="<?= htmlspecialchars($admin_user['avatar']) ?>" alt="Admin" class="w-full h-full object-cover">
              <?php else: ?>
                <?= strtoupper(mb_substr($admin_user['name'], 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div class="text-right hidden sm:block">
              <div class="text-xs font-bold text-white"><?= htmlspecialchars($admin_user['name']) ?></div>
              <div class="text-[10px] text-amber-300 uppercase font-semibold"><?= htmlspecialchars($admin_user['role']) ?></div>
            </div>

            <a href="/logout.php" class="p-2 text-indigo-300 hover:text-red-400 rounded-lg hover:bg-indigo-900 transition" title="लॉगआउट">
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
          <i class="fa-solid fa-chart-pie"></i> अवलोकन
        </a>

        <a href="/admin/users.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'users.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-users-gear"></i> यूजर व स्टाफ
        </a>

        <a href="/admin/products.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'products.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-boxes-stacked"></i> उत्पाद कैटलॉग
        </a>

        <a href="/admin/categories.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'categories.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-tags"></i> श्रेणियां
        </a>

        <a href="/admin/vendors.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'vendors.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-hammer"></i> विक्रेता (KYC)
        </a>

        <a href="/admin/orders.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'orders.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-truck-fast"></i> आर्डर व डिस्पैच
        </a>

        <a href="/admin/blogs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'blogs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-newspaper"></i> ब्लॉग CMS
        </a>

        <a href="/admin/tickets.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'tickets.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-headset"></i> सपोर्ट टिकट्स
        </a>

        <a href="/admin/coupons.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'coupons.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-ticket"></i> कूपन व डिस्काउंट
        </a>

        <a href="/admin/hero_customizer.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'hero_customizer.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-wand-magic-sparkles"></i> त्योहार व सेल बैनर
        </a>

        <a href="/admin/live_chat.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'live_chat.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-headset"></i> लाइव चैट कंसोल
        </a>

        <a href="/admin/seo_settings.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'seo_settings.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-magnifying-glass-chart"></i> SEO सेटिंग्स
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
