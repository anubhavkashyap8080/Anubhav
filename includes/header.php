<?php
/**
 * GaunGaliHat Master Header
 * Restores original AJAX Site Header, Authentic Logo & Interactive Controls
 */

require_once __DIR__ . '/functions.php';

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
    // Pages embed a per-session CSRF token: never let LiteSpeed/Cloudflare/browser cache them
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-LiteSpeed-Cache-Control: no-cache');
}

$current_user = get_logged_in_user();
$cart_count = get_cart_count();
$wishlist_count = get_wishlist_count();
$cart_subtotal = get_cart_subtotal();
$current_page = basename($_SERVER['PHP_SELF']);

// Load active categories for nav
$header_categories = [];
try {
    $stmt = $pdo->query("SELECT id, name, hindi_name, slug, icon_name FROM categories ORDER BY sort_order ASC, name ASC");
    $header_categories = $stmt->fetchAll();
} catch (Exception $e) {}

// Dynamic SEO & Title Resolution Engine
$raw_home_title = get_site_setting('homepage_title', '');
$raw_seo_title = get_site_setting('seo_meta_title', '');

$site_seo_title = (!empty($raw_home_title) && $raw_home_title !== '0') ? $raw_home_title : $raw_seo_title;
if (empty($site_seo_title) || $site_seo_title === '0') {
    $site_seo_title = APP_NAME . ' — Desh ki mitti ka swad | Rural Tools & Farm Staples';
}

// Clean any stray leading 0 artifact like "0 —", "0 -", "0 __", "0_", "0 "
$site_seo_title = preg_replace('/^0\s*[-—_:]+\s*/u', APP_NAME . ' — ', trim((string)$site_seo_title));
$site_seo_title = preg_replace('/^0\s+/u', '', trim((string)$site_seo_title));
if (empty($site_seo_title) || $site_seo_title === '0') {
    $site_seo_title = APP_NAME . ' — Desh ki mitti ka swad | Rural Tools & Farm Staples';
}

$site_seo_desc = get_site_setting('seo_meta_description', 'High-durability hand-forged agricultural tools, unpolished pulses, Banarasi pickles, and authentic handlooms direct from Indian village clusters.');
$site_seo_keywords = get_site_setting('seo_meta_keywords', 'farming tools, sickle, hansiya, khurpi, unpolished dal, banarasi achar, rural artisans, Hardoi foundries');
$robots_meta = get_site_setting('seo_robots_meta', 'index, follow');

if ($current_page === 'index.php' || empty($page_title)) {
    $page_title = $site_seo_title;
} else {
    $page_title = preg_replace('/^0\s*[-—_:]+\s*/u', '', trim((string)$page_title));
    if (empty($page_title) || $page_title === '0') {
        $page_title = $site_seo_title;
    } elseif (strpos($page_title, APP_NAME) === false) {
        $page_title = $page_title . ' — ' . APP_NAME;
    }
}

if (empty($page_desc)) {
    $page_desc = $site_seo_desc;
}
if (empty($page_keywords)) {
    $page_keywords = $site_seo_keywords;
}

$canonical_base = get_site_setting('seo_canonical_url', defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com');
$current_canonical_url = rtrim($canonical_base, '/') . ($_SERVER['REQUEST_URI'] ?? '/');
if (empty($og_image)) {
    $og_image = get_site_setting('seo_og_image', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200');
}
$twitter_handle = get_site_setting('seo_twitter_handle', '@GaunGaliHat');
$gsc_tag = get_site_setting('seo_google_console', '');
$ga4_id = get_site_setting('ga4_measurement_id', get_site_setting('seo_google_analytics', ''));

// Brand Logo Sizing & Occasion GIF Logo Settings (clamped safely)
$raw_mobile = (int)get_setting('logo_height_mobile', 46);
$raw_desktop = (int)get_setting('logo_height_desktop', 62);
if ($raw_mobile <= 40) $raw_mobile = 46;
if ($raw_desktop <= 50) $raw_desktop = 62;
$logo_height_mobile = max(28, min(80, $raw_mobile));
$logo_height_desktop = max(40, min(120, $raw_desktop));

$use_gif_logo = (get_setting('use_gif_logo', '0') === '1');
$logo_gif_url = get_setting('logo_gif_url', '');
$site_logo_url = get_setting('site_logo_url', '/logo.png');

$active_site_logo = ($use_gif_logo && !empty($logo_gif_url)) ? $logo_gif_url : $site_logo_url;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-stone-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
  <meta name="robots" content="<?= htmlspecialchars($robots_meta) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($current_canonical_url) ?>">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <?php 
    $gsc_token = '';
    if (!empty($gsc_tag)) {
        if (preg_match('/content=[\'"]([A-Za-z0-9_\-]{20,100})[\'"]/i', (string)$gsc_tag, $m)) {
            $gsc_token = $m[1];
        } elseif (preg_match('/^[A-Za-z0-9_\-]{20,100}$/', trim((string)$gsc_tag))) {
            $gsc_token = trim((string)$gsc_tag);
        }
    }
  ?>
  <?php if (!empty($gsc_token)): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($gsc_token, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- Open Graph / Social Cards -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($current_canonical_url) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME) ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="<?= htmlspecialchars($twitter_handle) ?>">
  <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">

  <!-- JSON-LD Structured Data: Organization & WebSite Search -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "<?= htmlspecialchars($canonical_base) ?>/#organization",
        "name": "<?= htmlspecialchars(APP_NAME) ?>",
        "url": "<?= htmlspecialchars($canonical_base) ?>",
        "logo": "<?= htmlspecialchars($canonical_base) ?>/assets/images/logo.png",
        "description": "Indian Rural Artisan Marketplace & Traditional Foundry Tools",
        "sameAs": [
          "https://www.facebook.com/ganvgalihat",
          "https://twitter.com/ganvgalihat",
          "https://www.instagram.com/ganvgalihat"
        ]
      },
      {
        "@type": "WebSite",
        "@id": "<?= htmlspecialchars($canonical_base) ?>/#website",
        "url": "<?= htmlspecialchars($canonical_base) ?>",
        "name": "<?= htmlspecialchars(APP_NAME) ?>",
        "potentialAction": {
          "@type": "SearchAction",
          "target": {
            "@type": "EntryPoint",
            "urlTemplate": "<?= htmlspecialchars($canonical_base) ?>/products.php?q={search_term_string}"
          },
          "query-input": "required name=search_term_string"
        }
      }
    ]
  }
  </script>

  <?php if (!empty($ga4_id)): ?>
    <!-- Google Analytics 4 (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4_id) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= htmlspecialchars($ga4_id) ?>', {
        'send_page_view': true,
        'cookie_flags': 'SameSite=None;Secure'
      });
    </script>
  <?php endif; ?>
  
  <!-- Static Compiled Tailwind CSS (Item 7 Hardening) -->
  <?php
    // Cache-busting version for CSS/JS (bumped from Admin > Cache Manager, falls back to file mtime)
    $asset_v = get_site_setting('asset_version', '');
    $av = function ($rel) use ($asset_v) {
        $v = $asset_v !== '' ? $asset_v : (string)@filemtime(__DIR__ . '/..' . $rel);
        return $rel . '?v=' . rawurlencode($v ?: '1');
    };
  ?>
  <link rel="stylesheet" href="<?= $av('/assets/css/tailwind.css') ?>">

  <!-- Google Fonts & Self-Hosted Pin-Versioned Icons & Libraries -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Yatra+One&display=swap" rel="stylesheet">
  <script src="/assets/vendor/lucide.min.js"></script>
  <script src="/assets/vendor/chart.umd.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= $av('/assets/css/style.css') ?>">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
  <script src="<?= $av('/assets/js/main.js') ?>"></script>
  <style>
    /* Responsive High-Definition Brand Logo & Zero-Overflow Layout */
    .site-header-brand-logo {
      height: <?= (int)$logo_height_mobile ?>px !important;
      max-height: 54px;
      width: auto !important;
      max-width: 185px;
      object-fit: contain;
      display: block;
    }
    @media (min-width: 640px) {
      .site-header-brand-logo {
        height: <?= (int)$logo_height_desktop ?>px !important;
        max-height: 72px;
        max-width: 270px;
      }
    }
    @media (min-width: 1024px) {
      .site-header-brand-logo {
        height: <?= max(62, (int)$logo_height_desktop) ?>px !important;
        max-height: 78px;
        max-width: 320px;
      }
    }
  </style>
</head>
<body class="flex flex-col min-h-screen text-gray-800 antialiased selection:bg-emerald-200 overflow-x-hidden">

  <!-- Global Cart Popup Modal (Bottom on mobile / Top-Right on desktop) -->
  <div id="cartPopupModal" class="hidden fixed z-[9999] pointer-events-auto transition-all duration-300 transform bottom-3 inset-x-3 sm:bottom-auto sm:inset-x-auto sm:top-5 sm:right-5 sm:max-w-md sm:w-96"></div>

  <!-- Global Toast Notification Container (Bottom on mobile / Top-Right on desktop) -->
  <div id="toast-container" class="fixed z-[99999] pointer-events-none flex flex-col gap-2.5 bottom-4 inset-x-4 sm:bottom-auto sm:inset-x-auto sm:top-5 sm:right-5 sm:max-w-sm w-auto"></div>

  <!-- Item 7: Impersonation Alert Banner (POST + CSRF exit) -->
  <?= function_exists('render_impersonation_banner') ? render_impersonation_banner() : '' ?>

  <!-- 1. Top Announcement Bar (Strictly Single-Line Linear & Fully Responsive across all screens) -->
  <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 text-white text-[11px] py-1.5 px-2.5 sm:px-6 border-b border-emerald-900/50 w-full overflow-hidden select-none">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-4 whitespace-nowrap text-nowrap w-full">
      <div class="flex items-center gap-1.5 shrink-0 text-amber-300 font-bold text-[10px] sm:text-xs">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300 shrink-0"></i>
        <span><?= htmlspecialchars(APP_COMPANY) ?></span>
        <span class="hidden md:inline text-emerald-400 font-normal">Enterprise</span>
      </div>
      <div class="flex-1 min-w-0 mx-1 sm:mx-3 text-center overflow-hidden">
        <p class="truncate text-[10px] sm:text-[11.5px] text-gray-200 font-medium tracking-tight" title="<?= htmlspecialchars(get_setting('announcement_text')) ?>">
          🌾 <?= htmlspecialchars(get_setting('announcement_text')) ?>
        </p>
      </div>
      <div class="flex items-center gap-2 sm:gap-3 text-emerald-200 text-[10px] sm:text-xs shrink-0">
        <a href="tel:<?= preg_replace('/\s+/', '', APP_PHONE) ?>" class="hover:text-white flex items-center gap-1 transition-colors" title="Customer Helpline">
          <i data-lucide="phone" class="w-3 h-3 text-amber-300 shrink-0"></i>
          <span class="hidden sm:inline font-mono"><?= htmlspecialchars(APP_PHONE) ?></span>
        </a>
        <span class="text-emerald-700 hidden sm:inline">•</span>
        <a href="/seller/dashboard.php" class="hover:text-amber-300 flex items-center gap-1 font-bold text-white transition-colors" title="Artisan & Seller Hub">
          <i data-lucide="store" class="w-3.5 h-3.5 text-brand-orange shrink-0"></i>
          <span>Seller Hub</span>
        </a>
      </div>
    </div>
  </div>

  <!-- 2. Primary Sticky Header (Unified Full Sticky Header across Desktop & Mobile) -->
  <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm transition-all w-full max-w-full" style="position: -webkit-sticky; position: sticky; top: 0; z-index: 50;">
    <div class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 w-full min-w-0">
      <div class="flex items-center justify-between h-16 sm:h-20 lg:h-22 gap-1.5 sm:gap-3 lg:gap-4 w-full min-w-0">
        
        <!-- Mobile Menu Hamburger Button -->
        <button
          type="button"
          id="mobile-menu-open"
          class="lg:hidden p-1.5 text-gray-700 hover:text-brand-green hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green shrink-0"
          aria-label="Toggle Navigation Menu"
        >
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Brand Logo Only (Configurable via Admin & Responsive) -->
        <a href="/" class="flex items-center shrink-0 group py-1" title="<?= htmlspecialchars(APP_NAME) ?> • Home">
          <div class="relative w-auto flex items-center shrink-0">
            <img
              src="<?= htmlspecialchars($active_site_logo) ?>"
              alt="<?= htmlspecialchars(APP_NAME) ?> Logo"
              class="site-header-brand-logo object-contain transition-transform group-hover:scale-105"
              onerror="this.onerror=null; this.src='/logo.png';"
            />
          </div>
        </a>

        <!-- Delivery Location Selector (Pincode) -->
        <div class="hidden md:flex items-center shrink-0">
          <button
            type="button"
            onclick="openPincodeModal()"
            class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg border border-gray-200 bg-brand-cream/60 hover:border-brand-green hover:bg-emerald-50 transition-colors text-left group shrink-0"
          >
            <i data-lucide="map-pin" class="w-4 h-4 text-brand-orange shrink-0 group-hover:animate-bounce"></i>
            <div class="text-xs leading-tight">
              <span class="block text-gray-500 text-[10px] whitespace-nowrap">Express Village Dispatch</span>
              <span class="font-semibold text-gray-800 truncate max-w-[110px] sm:max-w-[130px] block" id="header-pincode-display">
                <?= htmlspecialchars($_SESSION['delivery_location_text'] ?? ($_SESSION['shipping_pincode'] ?? 'Hardoi 241126')) ?>
              </span>
            </div>
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-700 shrink-0"></i>
          </button>
        </div>

        <!-- 3. AJAX Live Autocomplete Search Bar (Rounded-full Style) -->
        <div class="flex-1 min-w-[140px] max-w-xl relative hidden sm:block" id="headerSearchContainer">
          <form action="/products.php" method="GET" class="relative">
            <input
              type="text"
              name="q"
              id="header-live-search-input"
              placeholder="Search hand-forged tools, sickles, khurpi, pulses, pickles, jute bags..."
              autocomplete="off"
              class="w-full pl-10 pr-24 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-brand-green transition-all"
            />
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            
            <button
              type="submit"
              class="absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-full shadow-sm transition-all"
            >
              Search
            </button>
          </form>

          <!-- Suggestions Dropdown -->
          <div id="live-search-dropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
            <div id="live-search-spinner" class="hidden p-4 text-center text-xs text-gray-500">
              <span class="inline-block animate-spin mr-1.5">⏳</span> Searching artisan workshops...
            </div>
            <div class="px-3 py-2 bg-stone-50 border-b border-gray-100 text-xs text-gray-600 font-medium flex justify-between items-center">
              <span>Matching Products</span>
              <span class="text-[10px] text-brand-green font-semibold">Live Rural Catalog</span>
            </div>
            <div id="live-search-items" class="max-h-80 overflow-y-auto divide-y divide-gray-50"></div>
            <div class="p-2.5 bg-gray-50 border-t border-gray-100 text-center">
              <a href="/products.php" id="live-search-view-all" class="text-xs font-semibold text-brand-green hover:underline flex items-center justify-center gap-1">
                <span>View all matching results</span>
                <i data-lucide="external-link" class="w-3 h-3"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 4. Action Icons: Nearby Artisans, Seller Login, Wishlist, Cart, Profile -->
        <div class="flex items-center gap-1 sm:gap-2 lg:gap-2.5 shrink-0">
          
          <!-- Nearby Artisans Button -->
          <a
            href="/artisans.php"
            class="hidden md:inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg border border-emerald-600/30 text-brand-green bg-emerald-50 hover:bg-brand-green hover:text-white transition-all text-xs font-semibold whitespace-nowrap shrink-0"
            title="Locate rural artisans & foundries"
          >
            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-orange shrink-0"></i>
            <span>Nearby Artisans</span>
          </a>

          <!-- Dedicated Seller Button (Desktop visible, hidden on mobile to prevent clipping) -->
          <?php if ($current_user && $current_user['role'] === 'vendor'): ?>
            <a
              href="/seller/dashboard.php"
              class="hidden sm:inline-flex items-center gap-1 px-2 sm:px-2.5 lg:px-3 py-1 sm:py-1.5 rounded-lg border border-brand-orange text-white bg-brand-orange hover:bg-orange-600 transition-all text-xs font-bold shadow-xs whitespace-nowrap shrink-0"
            >
              <i data-lucide="store" class="w-3.5 h-3.5 shrink-0"></i>
              <span>Seller Dashboard</span>
            </a>
          <?php else: ?>
            <a
              href="/login.php?tab=seller"
              class="hidden sm:inline-flex items-center gap-1 px-2 sm:px-2.5 lg:px-3 py-1 sm:py-1.5 rounded-lg border border-brand-orange/50 text-brand-orange bg-orange-50 hover:bg-brand-orange hover:text-white transition-all text-xs font-bold cursor-pointer whitespace-nowrap shrink-0"
              title="Artisan & Seller Login"
            >
              <i data-lucide="store" class="w-3.5 h-3.5 shrink-0"></i>
              <span>Seller Login</span>
            </a>
          <?php endif; ?>

          <!-- Wishlist Icon -->
          <a
            href="/wishlist.php"
            class="relative p-1.5 sm:p-2 text-gray-600 hover:text-brand-green hover:bg-gray-100 rounded-full transition-colors shrink-0"
            title="Your Wishlist"
            aria-label="Wishlist"
          >
            <i data-lucide="heart" class="w-5 h-5"></i>
            <span id="header-wishlist-count" class="absolute top-0 right-0 w-4 h-4 bg-brand-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center <?= $wishlist_count === 0 ? 'hidden' : '' ?>">
              <?= $wishlist_count ?>
            </span>
          </a>

          <!-- Cart Icon & Total -->
          <a
            href="/cart.php"
            class="flex items-center gap-1.5 sm:gap-2 p-1.5 sm:px-2.5 lg:px-3 sm:py-1.5 rounded-full sm:rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-brand-green border border-transparent sm:border-gray-200 transition-all whitespace-nowrap shrink-0"
            title="Shopping Cart"
            aria-label="Shopping Cart"
          >
            <div class="relative">
              <i data-lucide="shopping-cart" class="w-5 h-5 text-brand-green"></i>
              <span id="header-cart-count" class="absolute -top-1.5 -right-2 w-4 h-4 bg-brand-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center <?= $cart_count === 0 ? 'hidden' : '' ?>">
                <?= $cart_count ?>
              </span>
            </div>
            <div class="hidden lg:block text-left text-xs leading-none">
              <span class="text-[10px] text-gray-500 block">Total</span>
              <span class="font-bold text-gray-900" id="header-cart-total">₹<?= number_format($cart_subtotal, 2) ?></span>
            </div>
          </a>

          <!-- Account Dropdown (Matches Header.tsx) -->
          <div class="relative shrink-0" id="accountMenuContainer">
            <button
              type="button"
              id="accountMenuBtn"
              class="flex items-center gap-1 sm:gap-1.5 p-1 sm:p-2 rounded-full hover:bg-gray-100 text-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-green cursor-pointer shrink-0"
              aria-label="Account Menu"
            >
              <div class="w-8 h-8 rounded-full bg-emerald-100 text-brand-green flex items-center justify-center font-bold text-xs ring-2 ring-emerald-600/30 overflow-hidden shrink-0">
                <?php if ($current_user && !empty($current_user['avatar'])): ?>
                  <img src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
                <?php elseif ($current_user): ?>
                  <?= strtoupper(mb_substr($current_user['name'], 0, 1)) ?>
                <?php else: ?>
                  <i data-lucide="user" class="w-4 h-4"></i>
                <?php endif; ?>
              </div>
              <div class="hidden md:flex flex-col text-left">
                <span class="text-xs font-semibold text-gray-800 leading-tight truncate max-w-[90px]">
                  <?= $current_user ? htmlspecialchars(explode(' ', $current_user['name'])[0]) : 'Sign In' ?>
                </span>
                <span class="text-[10px] text-brand-green font-bold uppercase leading-none">
                  <?= $current_user ? htmlspecialchars(str_replace('_', ' ', $current_user['role'])) : 'Customer' ?>
                </span>
              </div>
              <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden sm:block"></i>
            </button>

            <!-- Dropdown Menu -->
            <div
              id="accountDropdownMenu"
              class="hidden absolute right-0 top-full mt-2 w-[calc(100vw-1rem)] sm:w-64 max-w-xs bg-white rounded-2xl shadow-2xl border border-gray-100 py-2.5 z-50 animate-in fade-in zoom-in-95 duration-150"
            >
              <?php if ($current_user): ?>
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-[11px] text-gray-500">Signed in as</p>
                  <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($current_user['name']) ?></p>
                  <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded uppercase">
                      <?= htmlspecialchars(str_replace('_', ' ', $current_user['role'])) ?>
                    </span>
                    <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-0.5">● Online</span>
                  </div>
                </div>

                <div class="py-1 text-xs">
                  <?php if (in_array($current_user['role'], ['super_admin', 'admin'])): ?>
                    <a href="/admin/index.php" class="flex items-center justify-between px-4 py-2 font-bold text-brand-green hover:bg-emerald-50">
                      <span class="flex items-center gap-2">
                        <i data-lucide="settings" class="w-4 h-4 text-brand-green"></i> Admin Operations Center
                      </span>
                      <span class="px-1.5 py-0.5 bg-emerald-100 text-[9px] font-bold rounded">Portal</span>
                    </a>
                  <?php endif; ?>

                  <?php if ($current_user['role'] === 'vendor' || in_array($current_user['role'], ['super_admin', 'admin'])): ?>
                    <a href="/seller/dashboard.php" class="flex items-center gap-2 px-4 py-2 font-bold text-brand-orange hover:bg-orange-50">
                      <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i> Seller Workshop Dashboard
                    </a>
                  <?php endif; ?>

                  <a href="/account.php" class="flex items-center gap-2 px-4 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-green">
                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Buyer Profile & Orders
                  </a>

                  <a href="/wishlist.php" class="flex items-center gap-2 px-4 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-green">
                    <i data-lucide="heart" class="w-4 h-4 text-gray-400"></i> My Wishlist (<?= $wishlist_count ?>)
                  </a>

                  <a href="/artisans.php" class="flex items-center gap-2 px-4 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-green">
                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-orange"></i> Nearby Artisan Directory
                  </a>
                </div>

                <div class="border-t border-gray-100 pt-1.5 px-3">
                  <a href="/logout.php" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg text-left">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                  </a>
                </div>
              <?php else: ?>
                <!-- Guest visitor state -->
                <div class="p-3 space-y-2.5">
                  <div class="text-center pb-2 border-b border-gray-100">
                    <p class="text-xs font-bold text-gray-900">Welcome to GaunGaliHat</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Please sign in to access your orders and account</p>
                  </div>

                  <a href="/login.php?tab=shopper" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Shopper Login</span>
                  </a>

                  <a href="/login.php?tab=seller" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 bg-orange-50 hover:bg-orange-100 text-brand-orange border border-brand-orange/30 rounded-xl text-xs font-bold transition-colors">
                    <i data-lucide="store" class="w-4 h-4 text-brand-orange"></i>
                    <span>Artisan & Seller Login</span>
                  </a>

                  <div class="pt-1 text-center">
                    <a href="/login.php?tab=staff" class="text-[10px] text-gray-400 hover:text-gray-700 hover:underline">
                      Staff & Administration Login →
                    </a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>

      </div>

      <!-- Mobile Search Input (Visible on small screens) -->
      <div class="pb-3 sm:hidden w-full min-w-0 max-w-full">
        <form action="/products.php" method="GET" class="relative w-full">
          <input
            type="text"
            name="q"
            placeholder="Search village items..."
            class="w-full pl-9 pr-20 py-2 text-xs bg-gray-50 border border-gray-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-green"
          />
          <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 px-3 py-1 bg-brand-green text-white text-[11px] font-semibold rounded-full">
            Search
          </button>
        </form>
      </div>

    </div>

    <!-- 5. Category Navigation Bar (Exact styling from CategoryNav.tsx) -->
    <nav class="bg-stone-50 border-t border-gray-200/80 shadow-xs w-full max-w-full overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0">
        <div class="flex items-center space-x-1 sm:space-x-3 overflow-x-auto py-2.5 scrollbar-none text-xs font-medium text-gray-700 whitespace-nowrap w-full min-w-0">
          
          <a
            href="/products.php"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 <?= empty($_GET['category']) && $current_page === 'products.php' ? 'bg-brand-green text-white font-semibold shadow-xs' : 'hover:bg-amber-50 hover:text-brand-green text-gray-800' ?>"
          >
            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
            <span>All Products</span>
          </a>

          <?php foreach ($header_categories as $cat): 
            $isActive = (isset($_GET['category']) && $_GET['category'] === $cat['slug']);
          ?>
            <a
              href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?>"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 <?= $isActive ? 'bg-brand-green text-white font-semibold shadow-xs' : 'hover:bg-amber-50 hover:text-brand-green text-gray-800' ?>"
            >
              <span><?= htmlspecialchars($cat['name']) ?></span>
              <?php if (strpos($cat['slug'], 'pulses') !== false || strpos($cat['slug'], 'pickle') !== false): ?>
                <span class="ml-0.5 px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">New</span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>

          <a
            href="/artisans.php"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 hover:bg-emerald-100/70 text-brand-green font-semibold border border-emerald-300/60"
          >
            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-orange"></i>
            <span>Nearby Artisans (Map)</span>
          </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Mobile Menu Drawer (Fixed positioning so it never hides or gets cut off) -->
  <div id="mobile-menu-drawer" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden transition-opacity">
    <div id="mobile-menu-content" class="fixed inset-y-0 left-0 w-80 max-w-[85vw] bg-white shadow-2xl p-5 flex flex-col justify-between overflow-y-auto transform -translate-x-full transition-transform duration-300">
      <div class="space-y-5">
        
        <!-- Header in Drawer -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <img src="<?= htmlspecialchars($active_site_logo) ?>" alt="<?= htmlspecialchars(APP_NAME) ?>" style="height: <?= max(34, $logo_height_mobile) ?>px;" class="w-auto object-contain" onerror="this.onerror=null; this.src='/logo.png';" />
          <button id="mobile-menu-close" class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <!-- Links -->
        <div class="space-y-1 text-xs font-semibold">
          <a href="/index.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-gray-50 text-gray-800">
            <i data-lucide="home" class="w-4 h-4 text-brand-green"></i> Home Store
          </a>
          <a href="/artisans.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-stone-900 font-bold border border-amber-200">
            <i data-lucide="map-pin" class="w-4 h-4 text-brand-orange"></i> 📍 Nearby Artisans (निकटतम कारीगर)
          </a>
          <a href="/products.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-gray-50 text-gray-800">
            <i data-lucide="layout-grid" class="w-4 h-4 text-brand-orange"></i> All Products Catalog
          </a>

          <div class="pt-2 pb-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">Categories</div>
          <a href="/products.php?category=farming-traditional-tools" class="block px-3 py-2 rounded-xl hover:bg-emerald-50 text-emerald-800 font-bold">🌾 Agricultural Tools</a>
          <a href="/products.php?category=pulses-organic-grains" class="block px-3 py-2 rounded-xl hover:bg-emerald-50 text-emerald-900 font-bold">🥣 Desi Pulses & Dal</a>
          <a href="/products.php?category=pickles-village-murabba" class="block px-3 py-2 rounded-xl hover:bg-amber-50 text-amber-900 font-bold">🫙 Village Pickles & Murabba</a>
          <a href="/products.php?category=hardware-cottage-implements" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">🔨 Workshop Implements</a>
          <a href="/products.php?category=jute-bags-cloth-jholas" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">🛍️ Eco Jute Bags</a>
          <a href="/products.php?category=village-home-decor-toys" class="block px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">🪵 Wooden Toys & Decor</a>
          <a href="/products.php?category=handmade-by-rural-women" class="block px-3 py-2 rounded-xl hover:bg-orange-50 text-brand-orange font-bold">✨ Women SHG Products</a>

          <div class="pt-2 pb-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">Information</div>
          <a href="/about.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">
            <i data-lucide="info" class="w-4 h-4 text-gray-400"></i> About Kalyani agrofood pvt ltd
          </a>
          <a href="/contact.php" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-800">
            <i data-lucide="headset" class="w-4 h-4 text-gray-400"></i> Help & Contact Desk
          </a>
        </div>
      </div>

      <!-- Drawer Bottom Actions -->
      <div class="pt-5 border-t border-gray-100 space-y-2">
        <?php if ($current_user): ?>
          <?php if ($current_user['role'] === 'vendor' || in_array($current_user['role'], ['super_admin', 'admin'])): ?>
            <a href="/seller/dashboard.php" class="w-full py-2.5 bg-brand-orange text-white font-bold rounded-xl text-center text-xs block shadow-sm">
              🏪 Seller Hub Dashboard
            </a>
          <?php endif; ?>
          <a href="/account.php" class="w-full py-2.5 bg-gray-100 text-gray-800 font-bold rounded-xl text-center text-xs block">
            My Account (<?= htmlspecialchars($current_user['name']) ?>)
          </a>
          <a href="/logout.php" class="w-full py-2.5 bg-red-50 text-red-700 font-bold rounded-xl text-center text-xs block">
            Sign Out
          </a>
        <?php else: ?>
          <a href="/login.php?tab=shopper" class="w-full py-2.5 bg-brand-green text-white font-bold rounded-xl text-center text-xs block shadow-sm">
            Shopper Login
          </a>
          <a href="/login.php?tab=seller" class="w-full py-2.5 bg-brand-orange text-white font-bold rounded-xl text-center text-xs block shadow-sm">
            Artisan & Seller Login
          </a>
          <a href="/login.php?tab=staff" class="text-center text-[11px] text-gray-400 hover:underline block pt-1">
            Staff & Administration Login →
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Pincode / Location Modal -->
  <div id="pincodeModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-sm w-full p-6 text-center">
      <div class="w-12 h-12 rounded-2xl bg-amber-100 text-brand-orange flex items-center justify-center mx-auto mb-3 text-lg font-bold">
        <i data-lucide="map-pin" class="w-6 h-6"></i>
      </div>
      <h3 class="text-base font-bold text-gray-900 mb-1">Choose Delivery Location</h3>
      <p class="text-xs text-gray-500 mb-4">Discover nearby rural artisans, express delivery dispatch, and exact India Post shipping.</p>
      
      <!-- GPS 1-Click Auto Detect Button -->
      <button type="button" onclick="detectGPSLocation()" id="modalGpsBtn" class="w-full mb-3.5 py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition active:scale-95 cursor-pointer">
        <i data-lucide="crosshair" class="w-4 h-4 text-stone-900"></i>
        <span id="gpsBtnText">Use My Current Location (GPS Auto)</span>
      </button>

      <div class="relative flex py-2 items-center mb-2">
        <div class="flex-grow border-t border-gray-200"></div>
        <span class="flex-shrink mx-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">or enter pincode</span>
        <div class="flex-grow border-t border-gray-200"></div>
      </div>

      <div class="relative mb-3">
        <input type="text" id="modalPincodeInput" maxlength="6" pattern="[0-9]{6}" placeholder="e.g. 241126 or 110001"
          class="w-full px-4 py-2.5 text-center font-bold text-base tracking-widest border border-gray-300 rounded-xl focus:border-brand-green focus:outline-none">
        <p id="gpsStatusNotice" class="text-[11px] text-amber-700 font-semibold mt-1 hidden"></p>
      </div>

      <!-- Quick Region Select Pills -->
      <div class="mb-4 text-left">
        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block mb-1.5">Popular Artisan Hubs</span>
        <div class="flex flex-wrap gap-1.5">
          <button type="button" onclick="selectQuickPin('241126')" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-stone-800 border border-amber-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">🌾 Hardoi (241126)</button>
          <button type="button" onclick="selectQuickPin('226001')" class="px-2 py-1 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">Lucknow (226001)</button>
          <button type="button" onclick="selectQuickPin('221001')" class="px-2 py-1 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">Varanasi (221001)</button>
          <button type="button" onclick="selectQuickPin('110001')" class="px-2 py-1 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">Delhi (110001)</button>
          <button type="button" onclick="selectQuickPin('208001')" class="px-2 py-1 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">Kanpur (208001)</button>
          <button type="button" onclick="selectQuickPin('201301')" class="px-2 py-1 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-lg text-[11px] font-semibold transition cursor-pointer">Noida (201301)</button>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <button type="button" onclick="closePincodeModal()" class="flex-1 py-2.5 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-200 transition">
          Cancel
        </button>
        <button type="button" onclick="applyPincode()" class="flex-1 py-2.5 bg-brand-green text-white font-bold rounded-xl text-xs hover:bg-emerald-800 transition">
          Check & Apply
        </button>
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

  <script>
    // Header Dropdown Toggle Logic
    const accountBtn = document.getElementById('accountMenuBtn');
    const accountDropdown = document.getElementById('accountDropdownMenu');
    if (accountBtn && accountDropdown) {
      accountBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        accountDropdown.classList.toggle('hidden');
      });
      document.addEventListener('click', function(e) {
        if (!accountDropdown.contains(e.target) && !accountBtn.contains(e.target)) {
          accountDropdown.classList.add('hidden');
        }
      });
    }

    // Mobile Menu Drawer Logic (Slides smoothly from left without hiding)
    const mobileMenuOpenBtn = document.getElementById('mobile-menu-open');
    const mobileMenuCloseBtn = document.getElementById('mobile-menu-close');
    const mobileMenuDrawer = document.getElementById('mobile-menu-drawer');
    const mobileMenuContent = document.getElementById('mobile-menu-content');

    if (mobileMenuOpenBtn && mobileMenuDrawer && mobileMenuContent) {
      mobileMenuOpenBtn.addEventListener('click', function() {
        mobileMenuDrawer.classList.remove('hidden');
        setTimeout(() => {
          mobileMenuContent.classList.remove('-translate-x-full');
        }, 10);
      });

      function closeDrawer() {
        mobileMenuContent.classList.add('-translate-x-full');
        setTimeout(() => {
          mobileMenuDrawer.classList.add('hidden');
        }, 300);
      }

      if (mobileMenuCloseBtn) mobileMenuCloseBtn.addEventListener('click', closeDrawer);
      mobileMenuDrawer.addEventListener('click', function(e) {
        if (e.target === mobileMenuDrawer) closeDrawer();
      });
    }

    // Location & Pincode modal helpers
    function openPincodeModal() {
      const modal = document.getElementById('pincodeModal');
      if (modal) {
        modal.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }
    }
    function closePincodeModal() {
      const modal = document.getElementById('pincodeModal');
      if (modal) modal.classList.add('hidden');
    }
    
    function selectQuickPin(pin) {
      const input = document.getElementById('modalPincodeInput');
      if (input) input.value = pin;
      applyPincode();
    }

    function detectGPSLocation(callback) {
      const notice = document.getElementById('gpsStatusNotice');
      const btnText = document.getElementById('gpsBtnText');
      if (!navigator.geolocation) {
        if (notice) {
          notice.innerText = 'GPS not supported by your browser. Please enter your PIN code.';
          notice.classList.remove('hidden');
        }
        return;
      }
      if (btnText) btnText.innerText = 'Detecting satellite location...';
      if (notice) notice.classList.add('hidden');
      
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          fetch('/ajax/set_location.php?lat=' + lat + '&lng=' + lng)
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                const elem = document.getElementById('header-pincode-display');
                if (elem) elem.innerText = data.location_text;
                closePincodeModal();
                if (typeof callback === 'function') {
                  callback(data);
                } else {
                  window.location.reload();
                }
              }
            })
            .catch(() => {
              if (btnText) btnText.innerText = 'GPS Auto Detect';
              if (notice) {
                notice.innerText = 'Could not reach server. Please enter your PIN code manually.';
                notice.classList.remove('hidden');
              }
            });
        },
        function(err) {
          if (btnText) btnText.innerText = 'Use Current Location (GPS Auto)';
          if (notice) {
            notice.innerText = 'Location permission blocked. Please select or enter your 6-digit PIN code.';
            notice.classList.remove('hidden');
          }
          const pinInput = document.getElementById('modalPincodeInput');
          if (pinInput) pinInput.focus();
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
      );
    }

    function applyPincode() {
      const pin = document.getElementById('modalPincodeInput').value.trim();
      if (pin.length === 6 && /^\d+$/.test(pin)) {
        fetch('/ajax/set_location.php?pincode=' + pin)
          .then(res => res.json())
          .then(data => {
            closePincodeModal();
            window.location.reload();
          })
          .catch(() => {
            fetch('/ajax/calculate_shipping.php?pincode=' + pin)
              .then(() => { closePincodeModal(); window.location.reload(); });
          });
      } else {
        alert('Please enter a valid 6-digit Indian PIN code.');
      }
    }
  </script>
