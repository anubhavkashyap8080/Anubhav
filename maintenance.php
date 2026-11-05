<?php
/**
 * GanvGaliHat Public Maintenance Screen
 * Bilingual (EN / HI) • SEO Safe HTTP 503 • Live Countdown • Contact Support
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/includes/functions.php';
}

$is_preview = isset($_GET['preview']) && (function_exists('is_admin') && is_admin());

// Send HTTP 503 Service Unavailable header if not in preview mode
if (!$is_preview && !headers_sent()) {
    http_response_code(503);
    header('HTTP/1.1 503 Service Unavailable');
    header('Status: 503 Service Unavailable');
    header('Retry-After: 3600');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

$maint = function_exists('get_maintenance_settings') ? get_maintenance_settings() : [
    'title' => "We'll Be Back Shortly!",
    'title_hi' => "हम जल्द ही वापस लौटेंगे!",
    'message' => "GanvGaliHat is currently undergoing scheduled platform upgrades to bring you an even better rural shopping experience. We will be back online shortly.",
    'message_hi' => "गाँव गली हाट पर वर्तमान में बेहतर सेवा और तकनीकी सुधार हेतु नियमित मेंटेनेंस कार्य प्रगति पर है। हुई असुविधा के लिए हमें खेद है।",
    'end_time' => '',
    'show_contact' => '1',
    'secret_bypass' => 'ganv_preview_2026'
];

$title_en = !empty($maint['title']) ? $maint['title'] : "We'll Be Back Shortly!";
$title_hi = !empty($maint['title_hi']) ? $maint['title_hi'] : "हम जल्द ही वापस लौटेंगे!";
$msg_en = !empty($maint['message']) ? $maint['message'] : "GanvGaliHat is undergoing scheduled system upgrades. We will be back online shortly.";
$msg_hi = !empty($maint['message_hi']) ? $maint['message_hi'] : "गाँव गली हाट पर तकनीकी सुधार हेतु मेंटेनेंस चल रहा है। हम जल्द ही ऑनलाइन वापस लौटेंगे।";
$end_time = !empty($maint['end_time']) ? trim((string)$maint['end_time']) : '';
$show_contact = ($maint['show_contact'] == '1');

$phone = defined('APP_PHONE') ? APP_PHONE : '+91 7897492100';
$email = defined('APP_EMAIL') ? APP_EMAIL : 'namaste@ganvgalihat.com';
$brand_name = defined('APP_NAME') ? APP_NAME : 'GanvGaliHat';
$company_name = defined('APP_COMPANY') ? APP_COMPANY : 'Kalyani agrofood pvt ltd';
$tagline = defined('APP_TAGLINE') ? APP_TAGLINE : 'Hunar desh ka, ganv se ghar tak';

// Clean numeric phone for WhatsApp
$clean_wa = preg_replace('/[^0-9]/', '', $phone);
if (strlen($clean_wa) === 10) {
    $clean_wa = '91' . $clean_wa;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Under Scheduled Maintenance — <?= htmlspecialchars($brand_name) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #022c22;
      background: radial-gradient(circle at 50% 20%, #064e3b 0%, #022c22 75%, #051410 100%);
      color: #f8fafc;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }
    .preview-bar {
      background: #f59e0b;
      color: #1c1917;
      padding: 10px 16px;
      text-align: center;
      font-size: 13px;
      font-weight: 800;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }
    .preview-bar a {
      color: #042f2e;
      text-decoration: underline;
      margin-left: 10px;
      font-weight: 900;
    }
    .header-bar {
      padding: 24px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1000px;
      width: 100%;
      margin: 0 auto;
    }
    .logo-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .logo-img-box {
      background: #ffffff;
      padding: 6px 14px;
      border-radius: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .logo-img {
      height: 38px;
      width: auto;
      object-fit: contain;
    }
    .brand-title {
      font-size: 19px;
      font-weight: 900;
      color: #ffffff;
      letter-spacing: -0.5px;
    }
    .brand-sub {
      font-size: 11px;
      color: #a7f3d0;
      font-weight: 600;
    }
    .lang-switcher {
      display: inline-flex;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 9999px;
      padding: 3px;
    }
    .lang-btn {
      background: transparent;
      border: none;
      color: #d1fae5;
      padding: 6px 14px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 9999px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .lang-btn.active {
      background: #f59e0b;
      color: #1c1917;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .container {
      flex: 1;
      max-width: 860px;
      width: 100%;
      margin: 0 auto;
      padding: 20px 20px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(245, 158, 11, 0.18);
      border: 1px solid rgba(245, 158, 11, 0.4);
      color: #fcd34d;
      padding: 6px 16px;
      border-radius: 9999px;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 24px;
    }
    .gear-pulse {
      width: 90px;
      height: 90px;
      background: rgba(255, 255, 255, 0.08);
      border: 2px solid rgba(245, 158, 11, 0.4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 24px;
      position: relative;
      box-shadow: 0 0 30px rgba(245, 158, 11, 0.2);
    }
    .gear-pulse i {
      font-size: 40px;
      color: #f59e0b;
      animation: spin 10s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .main-heading {
      font-size: 32px;
      font-weight: 900;
      color: #ffffff;
      margin-bottom: 14px;
      line-height: 1.25;
      letter-spacing: -0.5px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.4);
    }
    @media (min-width: 640px) {
      .main-heading { font-size: 44px; }
    }
    .description-text {
      font-size: 15px;
      color: #d1fae5;
      max-width: 620px;
      margin: 0 auto 28px;
      line-height: 1.7;
      font-weight: 500;
    }
    @media (min-width: 640px) {
      .description-text { font-size: 17px; }
    }
    /* Countdown Box */
    .countdown-card {
      background: rgba(6, 78, 59, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(8px);
      border-radius: 20px;
      padding: 20px 24px;
      margin-bottom: 32px;
      width: 100%;
      max-width: 520px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .countdown-title {
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #fcd34d;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    .countdown-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }
    .cd-unit {
      background: rgba(2, 44, 34, 0.85);
      border: 1px solid rgba(245, 158, 11, 0.3);
      border-radius: 14px;
      padding: 10px 4px;
    }
    .cd-num {
      font-size: 24px;
      font-weight: 900;
      color: #ffffff;
      line-height: 1;
      font-variant-numeric: tabular-nums;
    }
    @media (min-width: 640px) {
      .cd-num { font-size: 30px; }
    }
    .cd-lbl {
      font-size: 10px;
      font-weight: 700;
      color: #a7f3d0;
      text-transform: uppercase;
      margin-top: 5px;
      letter-spacing: 0.5px;
    }
    /* Contact Channels Card */
    .contact-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 20px;
      padding: 20px;
      width: 100%;
      max-width: 620px;
      margin-bottom: 28px;
    }
    .contact-title {
      font-size: 13px;
      font-weight: 800;
      color: #fcd34d;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .contact-buttons {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }
    .btn-contact {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 800;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-wa {
      background: #25d366;
      color: #064e3b;
    }
    .btn-wa:hover {
      background: #22bf5b;
      transform: translateY(-1px);
    }
    .btn-phone {
      background: #f59e0b;
      color: #1c1917;
    }
    .btn-phone:hover {
      background: #d97706;
      transform: translateY(-1px);
    }
    .btn-mail {
      background: rgba(255, 255, 255, 0.15);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .btn-mail:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-1px);
    }
    /* Staff Bypass Box */
    .bypass-toggle {
      font-size: 12px;
      color: #a7f3d0;
      cursor: pointer;
      text-decoration: underline;
      background: none;
      border: none;
      padding: 6px;
      opacity: 0.8;
      transition: opacity 0.2s;
    }
    .bypass-toggle:hover { opacity: 1; }
    .bypass-box {
      margin-top: 12px;
      display: none;
      background: rgba(0, 0, 0, 0.3);
      padding: 14px 18px;
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      max-width: 380px;
      width: 100%;
    }
    .bypass-input {
      width: 100%;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      font-size: 13px;
      margin-bottom: 8px;
      text-align: center;
    }
    .bypass-submit {
      width: 100%;
      padding: 8px 12px;
      background: #f59e0b;
      color: #1c1917;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
    }
    .footer-bar {
      padding: 20px;
      text-align: center;
      font-size: 11px;
      color: #6ee7b7;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      gap: 6px;
      align-items: center;
    }
    .footer-links {
      display: flex;
      gap: 16px;
      margin-top: 4px;
    }
    .footer-links a {
      color: #a7f3d0;
      text-decoration: none;
      transition: color 0.2s;
    }
    .footer-links a:hover { color: #f59e0b; }
    .hidden { display: none !important; }
  </style>
</head>
<body>

  <?php if ($is_preview): ?>
    <div class="preview-bar">
      <span>👁️ <strong>PREVIEW MODE:</strong> This is a live preview of the Maintenance Mode screen. Site is NOT locked.</span>
      <a href="/admin/maintenance.php">Back to Super Admin Controls</a>
    </div>
  <?php endif; ?>

  <!-- Top Header with Logo & Language Switcher -->
  <header class="header-bar">
    <a href="/" class="logo-wrap" title="<?= htmlspecialchars($brand_name) ?>">
      <div class="logo-img-box">
        <img src="/logo.png" alt="<?= htmlspecialchars($brand_name) ?>" class="logo-img" onerror="this.style.display='none'; document.getElementById('txtLogo').style.display='block';">
        <span id="txtLogo" style="display:none; color:#064e3b; font-weight:900; font-size:18px;">GanvGaliHat</span>
      </div>
      <div>
        <div class="brand-title"><?= htmlspecialchars($brand_name) ?></div>
        <div class="brand-sub"><?= htmlspecialchars($tagline) ?></div>
      </div>
    </a>

    <!-- Language Selector -->
    <div class="lang-switcher">
      <button type="button" class="lang-btn active" id="btnEn" onclick="switchLang('en')">English</button>
      <button type="button" class="lang-btn" id="btnHi" onclick="switchLang('hi')">हिन्दी</button>
    </div>
  </header>

  <!-- Main Container -->
  <main class="container">
    
    <!-- Badge -->
    <div class="badge-pill">
      <i class="fa-solid fa-screwdriver-wrench"></i>
      <span class="lang-en">Scheduled System Maintenance</span>
      <span class="lang-hi hidden">निर्धारित सिस्टम मेंटेनेंस</span>
    </div>

    <!-- Pulsing Animated Gear -->
    <div class="gear-pulse">
      <i class="fa-solid fa-gear"></i>
    </div>

    <!-- Headline -->
    <h1 class="main-heading">
      <span class="lang-en"><?= htmlspecialchars($title_en) ?></span>
      <span class="lang-hi hidden"><?= htmlspecialchars($title_hi) ?></span>
    </h1>

    <!-- Description Message -->
    <p class="description-text">
      <span class="lang-en"><?= nl2br(htmlspecialchars($msg_en)) ?></span>
      <span class="lang-hi hidden"><?= nl2br(htmlspecialchars($msg_hi)) ?></span>
    </p>

    <!-- Real-time Countdown Timer (if configured) -->
    <?php if (!empty($end_time)): ?>
      <div class="countdown-card" id="countdownContainer">
        <div class="countdown-title">
          <i class="fa-regular fa-clock"></i>
          <span class="lang-en">Expected Return Time</span>
          <span class="lang-hi hidden">अनुमानित बहाली समय</span>
        </div>
        <div class="countdown-grid">
          <div class="cd-unit">
            <div class="cd-num" id="cdDays">00</div>
            <div class="cd-lbl"><span class="lang-en">Days</span><span class="lang-hi hidden">दिन</span></div>
          </div>
          <div class="cd-unit">
            <div class="cd-num" id="cdHours">00</div>
            <div class="cd-lbl"><span class="lang-en">Hours</span><span class="lang-hi hidden">घंटे</span></div>
          </div>
          <div class="cd-unit">
            <div class="cd-num" id="cdMins">00</div>
            <div class="cd-lbl"><span class="lang-en">Mins</span><span class="lang-hi hidden">मिनट</span></div>
          </div>
          <div class="cd-unit">
            <div class="cd-num" id="cdSecs">00</div>
            <div class="cd-lbl"><span class="lang-en">Secs</span><span class="lang-hi hidden">सेकंड</span></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Contact Support Channels -->
    <?php if ($show_contact): ?>
      <div class="contact-card">
        <div class="contact-title">
          <span class="lang-en">Need Immediate Assistance with an Existing Order?</span>
          <span class="lang-hi hidden">मौजूदा ऑर्डर के संबंध में सहायता चाहिए?</span>
        </div>
        <div class="contact-buttons">
          <a href="https://wa.me/<?= htmlspecialchars($clean_wa) ?>?text=Namaste%20GanvGaliHat%20Support" target="_blank" class="btn-contact btn-wa">
            <i class="fa-brands fa-whatsapp text-lg"></i>
            <span>WhatsApp Support</span>
          </a>
          <a href="tel:<?= htmlspecialchars($phone) ?>" class="btn-contact btn-phone">
            <i class="fa-solid fa-phone"></i>
            <span><?= htmlspecialchars($phone) ?></span>
          </a>
          <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn-contact btn-mail">
            <i class="fa-solid fa-envelope"></i>
            <span><?= htmlspecialchars($email) ?></span>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Staff & Tester Bypass -->
    <div style="margin-top: 8px;">
      <button type="button" class="bypass-toggle" onclick="toggleBypassBox()">
        <span class="lang-en"><i class="fa-solid fa-key"></i> Staff / Authorized Tester Access</span>
        <span class="lang-hi hidden"><i class="fa-solid fa-key"></i> स्टाफ / अधिकृत परीक्षक लॉगिन</span>
      </button>

      <div class="bypass-box" id="bypassBox">
        <form method="GET" action="/" onsubmit="return handleBypassSubmit(this);">
          <input type="text" name="bypass" id="bypassInput" placeholder="Enter Secret Bypass Key" class="bypass-input" required>
          <button type="submit" class="bypass-submit">Unlock & Browse Store</button>
        </form>
        <div style="margin-top: 10px; font-size: 11px;">
          <a href="/login.php?tab=staff" style="color: #fcd34d; text-decoration: underline;">
            Go to Staff / Super Admin Sign In &rarr;
          </a>
        </div>
      </div>
    </div>

  </main>

  <!-- Footer -->
  <footer class="footer-bar">
    <div>
      &copy; <?= date('Y') ?> <?= htmlspecialchars($brand_name) ?> • A Unit of <?= htmlspecialchars($company_name) ?>.
    </div>
    <div class="footer-links">
      <a href="/login.php?tab=staff"><i class="fa-solid fa-lock"></i> Staff Login</a>
      <span>•</span>
      <a href="mailto:<?= htmlspecialchars($email) ?>"><i class="fa-solid fa-headset"></i> Support Desk</a>
    </div>
  </footer>

  <script>
    // Language Switcher
    function switchLang(lang) {
      var ens = document.querySelectorAll('.lang-en');
      var his = document.querySelectorAll('.lang-hi');
      var btnEn = document.getElementById('btnEn');
      var btnHi = document.getElementById('btnHi');

      if (lang === 'hi') {
        ens.forEach(function(el) { el.classList.add('hidden'); });
        his.forEach(function(el) { el.classList.remove('hidden'); });
        btnHi.classList.add('active');
        btnEn.classList.remove('active');
        document.documentElement.lang = 'hi';
      } else {
        his.forEach(function(el) { el.classList.add('hidden'); });
        ens.forEach(function(el) { el.classList.remove('hidden'); });
        btnEn.classList.add('active');
        btnHi.classList.remove('active');
        document.documentElement.lang = 'en';
      }
      try { localStorage.setItem('ggh_maint_lang', lang); } catch (e) {}
    }

    // Initialize saved language
    try {
      var savedLang = localStorage.getItem('ggh_maint_lang');
      if (savedLang === 'hi') {
        switchLang('hi');
      }
    } catch (e) {}

    // Countdown Logic
    <?php if (!empty($end_time)): ?>
    (function() {
      var targetDate = new Date("<?= date('Y-m-d\TH:i:s', strtotime($end_time)) ?>").getTime();
      if (!isNaN(targetDate)) {
        function updateCd() {
          var now = new Date().getTime();
          var diff = targetDate - now;

          if (diff <= 0) {
            document.getElementById('cdDays').textContent = '00';
            document.getElementById('cdHours').textContent = '00';
            document.getElementById('cdMins').textContent = '00';
            document.getElementById('cdSecs').textContent = '00';
            return;
          }

          var days = Math.floor(diff / (1000 * 60 * 60 * 24));
          var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
          var seconds = Math.floor((diff % (1000 * 60)) / 1000);

          document.getElementById('cdDays').textContent = days < 10 ? '0' + days : days;
          document.getElementById('cdHours').textContent = hours < 10 ? '0' + hours : hours;
          document.getElementById('cdMins').textContent = minutes < 10 ? '0' + minutes : minutes;
          document.getElementById('cdSecs').textContent = seconds < 10 ? '0' + seconds : seconds;
        }
        updateCd();
        setInterval(updateCd, 1000);
      }
    })();
    <?php endif; ?>

    // Bypass box toggle
    function toggleBypassBox() {
      var box = document.getElementById('bypassBox');
      box.style.display = (box.style.display === 'block') ? 'none' : 'block';
      if (box.style.display === 'block') {
        document.getElementById('bypassInput').focus();
      }
    }

    function handleBypassSubmit(form) {
      var val = document.getElementById('bypassInput').value.trim();
      if (!val) return false;
      window.location.href = '/?bypass=' + encodeURIComponent(val);
      return false;
    }
  </script>
</body>
</html>
