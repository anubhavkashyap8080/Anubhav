<?php
/**
 * GaunGaliHat Seller Central Layout Header
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Vendor Authorization Check
require_vendor();

$user = get_logged_in_user();
$vendor = get_vendor_for_current_user();

if (!$vendor) {
    // If user is admin/super_admin but no vendor record, create a mock or prompt
    $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id ASC LIMIT 1");
    $vendor = $stmt->fetch();
}

$current_page = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="hi" class="h-full bg-stone-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>विक्रेता डैशबोर्ड (Seller Central) — GaunGaliHat</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'brand-green': '#1b4332',
            'brand-dark': '#081c15',
            'brand-accent': '#d4a373',
            'seller-orange': '#ea580c',
          }
        }
      }
    }
  </script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Chart.js for real graphics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased bg-stone-100">

  <!-- Impersonation Notice Banner -->
  <?php if (!empty($_SESSION['impersonator_id'])): ?>
    <div class="bg-amber-500 text-stone-950 px-4 py-2 text-xs font-bold flex flex-wrap items-center justify-between shadow-md z-50 sticky top-0 border-b border-amber-600">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-user-secret text-base"></i>
        <span>आप <strong><?= htmlspecialchars($vendor['business_name'] ?? $user['name']) ?></strong> के विक्रेता पोर्टल में हैं। मूल एडमिन: <?= htmlspecialchars($_SESSION['impersonator_name'] ?? 'Super Admin') ?></span>
      </div>
      <a href="/admin/impersonate.php?action=exit" class="px-3 py-1 bg-stone-950 text-white rounded-lg hover:bg-black transition flex items-center gap-1.5 shadow-xs text-xs font-bold">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        एडमिन पैनल पर वापस लौटें (Exit Impersonation)
      </a>
    </div>
  <?php endif; ?>

  <!-- Top Seller Central Bar -->
  <header class="bg-stone-900 text-white border-b border-stone-800 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
        
        <!-- Brand & Workshop Info with Authentic Logo -->
        <div class="flex items-center gap-3 sm:gap-4">
          <a href="/seller/dashboard.php" class="flex items-center gap-3 group py-1" title="GaunGaliHat Seller Central">
            <div class="h-10 sm:h-12 bg-white/95 px-3 py-1.5 rounded-2xl shadow-sm flex items-center border border-white/20 transition-transform group-hover:scale-105">
              <img src="/logo.png" alt="GaunGaliHat" class="h-7 sm:h-9 w-auto object-contain" />
            </div>
            <div class="hidden sm:block border-l border-stone-700 pl-3">
              <span class="text-xs font-black uppercase tracking-wider text-amber-400 block">विक्रेता एवं कारीगर हब</span>
              <span class="text-[10px] text-stone-400 block">Seller Central Portal</span>
            </div>
          </a>

          <div class="hidden lg:flex items-center gap-2 pl-3 border-l border-stone-700">
            <div class="text-xs">
              <div class="font-bold text-gray-200 flex items-center gap-1.5">
                <span><?= htmlspecialchars($vendor['business_name'] ?? $vendor['name'] ?? 'Artisan Workshop') ?></span>
                <span class="text-[10px] bg-emerald-900/80 text-emerald-300 px-1.5 py-0.2 rounded font-semibold border border-emerald-700/50">सत्यापित</span>
              </div>
              <span class="text-[11px] text-stone-400"><?= htmlspecialchars($vendor['district'] ?? 'Hardoi') ?>, UP</span>
            </div>
          </div>
        </div>

        <!-- Right: Public Store Link & Profile -->
        <div class="flex items-center gap-3">
          <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> लाइव स्टोर देखें
          </a>

          <div class="flex items-center gap-3 pl-3 border-l border-stone-800">
            <a href="/seller/profile.php" class="flex items-center gap-2 group">
              <div class="w-8 h-8 rounded-full overflow-hidden bg-amber-500 text-stone-900 font-bold flex items-center justify-center text-xs border border-amber-400">
                <?php if (!empty($vendor['avatar'])): ?>
                  <img src="<?= htmlspecialchars($vendor['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
                <?php elseif (!empty($user['avatar'])): ?>
                  <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
                <?php else: ?>
                  <?= strtoupper(mb_substr($vendor['business_name'] ?? $user['name'], 0, 1)) ?>
                <?php endif; ?>
              </div>
              <div class="text-right hidden sm:block">
                <div class="text-xs font-bold text-white group-hover:text-amber-400 transition"><?= htmlspecialchars($user['name']) ?></div>
                <div class="text-[10px] text-amber-400 uppercase font-semibold"><?= htmlspecialchars($user['role']) ?></div>
              </div>
            </a>

            <a href="/logout.php" class="p-2 text-stone-400 hover:text-red-400 rounded-lg hover:bg-stone-800 transition" title="लॉगआउट">
              <i class="fa-solid fa-power-off text-sm"></i>
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- Secondary Navigation Menu -->
    <div class="bg-stone-800 border-t border-stone-700 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto py-1 scrollbar-none text-xs font-bold">
        <a href="/seller/dashboard.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'dashboard.php' ? 'bg-amber-500 text-stone-900' : 'text-stone-300 hover:text-white hover:bg-stone-700' ?>">
          <i class="fa-solid fa-chart-line"></i> डैशबोर्ड (Dashboard)
        </a>

        <a href="/seller/products.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'products.php' ? 'bg-amber-500 text-stone-900' : 'text-stone-300 hover:text-white hover:bg-stone-700' ?>">
          <i class="fa-solid fa-boxes-stacked"></i> उत्पाद सूची (Products)
        </a>

        <a href="/seller/orders.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'orders.php' ? 'bg-amber-500 text-stone-900' : 'text-stone-300 hover:text-white hover:bg-stone-700' ?>">
          <i class="fa-solid fa-truck-fast"></i> आर्डर व डिस्पैच (Orders)
        </a>

        <a href="/seller/payments.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'payments.php' ? 'bg-amber-500 text-stone-900' : 'text-stone-300 hover:text-white hover:bg-stone-700' ?>">
          <i class="fa-solid fa-indian-rupee-sign"></i> भुगतान व UTR (Payments)
        </a>

        <a href="/seller/profile.php"
          class="px-3.5 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-2 <?= $current_page === 'profile.php' ? 'bg-amber-500 text-stone-900' : 'text-stone-300 hover:text-white hover:bg-stone-700' ?>">
          <i class="fa-solid fa-gear"></i> कार्यशाला सेटिंग्स (Settings)
        </a>

        <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
          <a href="/admin/index.php" class="ml-auto px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center gap-1.5 text-[11px]">
            <i class="fa-solid fa-shield-halved"></i> सुपर एडमिन पैनल
          </a>
        <?php endif; ?>
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
