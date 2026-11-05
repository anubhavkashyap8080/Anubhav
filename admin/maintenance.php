<?php
/**
 * GanvGaliHat Super Admin Maintenance Mode Manager
 * Real-time Site Lock, Bilingual Announcements, IP Whitelisting, and Secret Bypass
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Clearance
require_admin();

$client_ip = function_exists('get_client_ip') ? get_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? '');

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid or expired. Please refresh the page and try again.');
        header('Location: /admin/maintenance.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. Instant 1-Click Toggle
    if ($action === 'toggle_maintenance') {
        $current_status = is_maintenance_mode_active();
        $new_status = $current_status ? '0' : '1';

        try {
            $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $sql = $is_sqlite 
                ? "INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)"
                : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['maintenance_mode_enabled', $new_status]);

            if ($new_status === '1') {
                $stmt->execute(['maintenance_mode_started_at', date('Y-m-d H:i:s')]);
                if (function_exists('record_security_event')) {
                    record_security_event('maintenance_mode_activated', 'Maintenance Mode activated by admin: ' . ($_SESSION['user_name'] ?? 'Admin'));
                }
                set_flash('success', '⚠️ Maintenance Mode is now ACTIVATED! Public visitors are redirected to the maintenance screen.');
            } else {
                if (function_exists('record_security_event')) {
                    record_security_event('maintenance_mode_deactivated', 'Maintenance Mode deactivated by admin: ' . ($_SESSION['user_name'] ?? 'Admin'));
                }
                set_flash('success', '✅ Maintenance Mode has been DEACTIVATED. The site is now LIVE for all public shoppers.');
            }

            // Clear any active opcode/litespeed cache
            if (function_exists('opcache_reset')) { @opcache_reset(); }
            if (!headers_sent()) { header('X-LiteSpeed-Purge: *'); }

            header('Location: /admin/maintenance.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    // 2. Add My Current IP to Whitelist
    if ($action === 'add_my_ip') {
        try {
            $existing = trim((string)get_site_setting('maintenance_mode_allowed_ips', ''));
            $ips = array_filter(array_map('trim', explode(',', $existing)));
            if (!in_array($client_ip, $ips, true) && !empty($client_ip)) {
                $ips[] = $client_ip;
                $new_val = implode(', ', $ips);

                $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
                $sql = $is_sqlite 
                    ? "INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)"
                    : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['maintenance_mode_allowed_ips', $new_val]);

                set_flash('success', "Your current IP ($client_ip) has been added to the maintenance whitelist.");
            } else {
                set_flash('error', "Your current IP ($client_ip) is already in the whitelist.");
            }
            header('Location: /admin/maintenance.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Failed to update IP whitelist: ' . $e->getMessage());
        }
    }

    // 3. Generate New Random Bypass Key
    if ($action === 'generate_bypass_key') {
        try {
            $new_key = 'ganv_' . bin2hex(random_bytes(6));
            $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $sql = $is_sqlite 
                ? "INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)"
                : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['maintenance_mode_secret_bypass', $new_key]);

            set_flash('success', "New Secret Bypass Token generated: $new_key");
            header('Location: /admin/maintenance.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Failed to generate bypass token: ' . $e->getMessage());
        }
    }

    // 4. Save Comprehensive Maintenance Settings
    if ($action === 'save_settings') {
        $enabled = isset($_POST['maintenance_mode_enabled']) ? '1' : '0';
        $prev_enabled = is_maintenance_mode_active() ? '1' : '0';

        $title = trim((string)($_POST['maintenance_mode_title'] ?? "We'll Be Back Shortly!"));
        $title_hi = trim((string)($_POST['maintenance_mode_title_hi'] ?? "हम जल्द ही वापस लौटेंगे!"));
        $message = trim((string)($_POST['maintenance_mode_message'] ?? ''));
        $message_hi = trim((string)($_POST['maintenance_mode_message_hi'] ?? ''));
        $end_time = trim((string)($_POST['maintenance_mode_end_time'] ?? ''));
        $allowed_ips = trim((string)($_POST['maintenance_mode_allowed_ips'] ?? ''));
        $secret_bypass = trim((string)($_POST['maintenance_mode_secret_bypass'] ?? 'ganv_preview_2026'));
        $show_contact = isset($_POST['maintenance_mode_show_contact']) ? '1' : '0';

        try {
            $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $sql = $is_sqlite 
                ? "INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)"
                : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $stmt = $pdo->prepare($sql);

            $updates = [
                'maintenance_mode_enabled'       => $enabled,
                'maintenance_mode_title'         => $title,
                'maintenance_mode_title_hi'      => $title_hi,
                'maintenance_mode_message'       => $message,
                'maintenance_mode_message_hi'    => $message_hi,
                'maintenance_mode_end_time'      => $end_time,
                'maintenance_mode_allowed_ips'   => $allowed_ips,
                'maintenance_mode_secret_bypass' => $secret_bypass,
                'maintenance_mode_show_contact'  => $show_contact
            ];

            if ($enabled === '1' && $prev_enabled !== '1') {
                $updates['maintenance_mode_started_at'] = date('Y-m-d H:i:s');
            }

            foreach ($updates as $k => $v) {
                $stmt->execute([$k, $v]);
            }

            if (function_exists('record_security_event')) {
                record_security_event('maintenance_settings_saved', [
                    'enabled' => $enabled,
                    'admin'   => $_SESSION['user_name'] ?? 'Admin'
                ]);
            }

            if (function_exists('opcache_reset')) { @opcache_reset(); }
            if (!headers_sent()) { header('X-LiteSpeed-Purge: *'); }

            set_flash('success', 'Maintenance Mode configuration saved successfully.');
            header('Location: /admin/maintenance.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'Error saving maintenance settings: ' . $e->getMessage());
        }
    }
}

// Fetch current values
$maint = get_maintenance_settings();
$is_active = $maint['enabled'];
$started_at = get_site_setting('maintenance_mode_started_at', '');

// Resolve canonical domain for preview link
$site_host = defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com';
$bypass_url = rtrim($site_host, '/') . '/?bypass=' . urlencode($maint['secret_bypass']);

$page_title = 'Maintenance Mode & Site Lock — Super Admin Central';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Breadcrumb -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
      <a href="/admin/index.php" class="hover:text-indigo-600">Admin</a>
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
      <span class="text-gray-700 font-bold">Site Maintenance Control</span>
    </div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
      <i class="fa-solid fa-screwdriver-wrench text-amber-500"></i>
      Maintenance Mode & Platform Site Lock
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Temporarily take the public store offline for system updates, migrations, or database maintenance with SEO safe HTTP 503 response.
    </p>
  </div>

  <!-- Quick Preview and Cache Purge -->
  <div class="flex items-center gap-2">
    <a href="/maintenance.php?preview=1" target="_blank"
      class="px-3.5 py-2 rounded-xl bg-white border border-gray-300 text-gray-700 hover:text-indigo-600 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-eye text-amber-500"></i>
      Preview Maintenance Page
    </a>
    <a href="/admin/cache_manager.php"
      class="px-3.5 py-2 rounded-xl bg-white border border-gray-300 text-gray-700 hover:text-indigo-600 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-broom text-indigo-500"></i>
      Purge Caches
    </a>
  </div>
</div>

<!-- 1. High-Impact Live Status Banner -->
<div class="mb-8 rounded-3xl p-6 sm:p-8 text-white shadow-xl transition-all <?= $is_active ? 'bg-gradient-to-r from-red-700 via-rose-700 to-amber-700 border-2 border-red-500/30' : 'bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 border-2 border-emerald-500/30' ?>">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
    <div class="flex items-start sm:items-center gap-4">
      <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shrink-0 shadow-lg <?= $is_active ? 'bg-white text-rose-700 animate-pulse' : 'bg-white text-emerald-800' ?>">
        <i class="fa-solid <?= $is_active ? 'fa-lock' : 'fa-globe' ?>"></i>
      </div>
      <div>
        <div class="flex items-center gap-2.5 mb-1.5">
          <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider shadow-xs <?= $is_active ? 'bg-white text-rose-700' : 'bg-white text-emerald-800' ?>">
            <?= $is_active ? '⚠️ MAINTENANCE MODE ACTIVE' : '✅ STORE IS ONLINE & LIVE' ?>
          </span>
          <?php if ($is_active && !empty($started_at)): ?>
            <span class="text-xs text-rose-100 font-medium">
              Started: <?= date('M d, Y h:i A', strtotime($started_at)) ?>
            </span>
          <?php endif; ?>
        </div>
        <h2 class="text-xl sm:text-2xl font-black">
          <?= $is_active ? 'Public Access is Blocked — Visitors See Maintenance Screen' : 'Public Marketplace is Open — All Shoppers Can Browse & Order' ?>
        </h2>
        <p class="text-xs sm:text-sm text-stone-100 max-w-2xl mt-1 leading-relaxed opacity-90">
          <?= $is_active 
            ? 'Only authenticated Super Admins, staff accounts, and whitelisted IP addresses can browse the site. Search engines receive HTTP 503 with Retry-After header to prevent ranking loss.' 
            : 'All catalogue pages, shopping cart, instant checkout, and seller registration are fully active and reachable by customers across India.' ?>
        </p>
      </div>
    </div>

    <!-- 1-Click Toggle Button -->
    <div class="shrink-0">
      <form method="POST" action="/admin/maintenance.php" onsubmit="return confirm('<?= $is_active ? 'Are you sure you want to DEACTIVATE maintenance mode and make the site LIVE?' : 'Are you sure you want to ACTIVATE maintenance mode? Public shoppers will be blocked immediately.' ?>');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_maintenance">
        <?php if ($is_active): ?>
          <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-stone-950 font-black text-sm rounded-2xl shadow-xl transition active:scale-95 flex items-center justify-center gap-2">
            <i class="fa-solid fa-power-off text-base"></i>
            <span>Deactivate & Go Live</span>
          </button>
        <?php else: ?>
          <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-amber-400 hover:bg-amber-300 text-stone-950 font-black text-sm rounded-2xl shadow-xl transition active:scale-95 flex items-center justify-center gap-2">
            <i class="fa-solid fa-lock text-base"></i>
            <span>Activate Maintenance Mode</span>
          </button>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- 2. Main Maintenance Configuration Form -->
<form method="POST" action="/admin/maintenance.php" class="space-y-8">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save_settings">

  <!-- A. Core Switch & Bilingual Messages -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <div class="border-b border-gray-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-bullhorn text-indigo-600"></i>
          1. Public Maintenance Notice & Announcement (Bilingual)
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
          Headlines and descriptions shown to visitors on the maintenance screen in English and Hindi.
        </p>
      </div>

      <!-- Mode Toggle Switch in Header -->
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="maintenance_mode_enabled" value="1" <?= $is_active ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
        <span class="ml-3 text-xs font-bold text-gray-800">
          Enable Maintenance Mode
        </span>
      </label>
    </div>

    <!-- Bilingual Tabs Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- English Column -->
      <div class="space-y-4 p-5 rounded-2xl bg-stone-50 border border-gray-200">
        <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-emerald-800">
          <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-900">EN</span>
          <span>English Notice</span>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Headline (Title)
          </label>
          <input type="text" name="maintenance_mode_title" value="<?= htmlspecialchars($maint['title']) ?>"
            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
            placeholder="e.g. We'll Be Back Shortly!">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Detailed Explanation Message
          </label>
          <textarea name="maintenance_mode_message" rows="4"
            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
            placeholder="Explain the scheduled maintenance to customers..."><?= htmlspecialchars($maint['message']) ?></textarea>
        </div>
      </div>

      <!-- Hindi Column -->
      <div class="space-y-4 p-5 rounded-2xl bg-stone-50 border border-gray-200">
        <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-amber-800">
          <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900">HI</span>
          <span>हिन्दी संदेश (Hindi Notice)</span>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            मुख्य शीर्षक (Headline)
          </label>
          <input type="text" name="maintenance_mode_title_hi" value="<?= htmlspecialchars($maint['title_hi']) ?>"
            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
            placeholder="उदा. हम जल्द ही वापस लौटेंगे!">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            विस्तृत संदेश (Detailed Explanation)
          </label>
          <textarea name="maintenance_mode_message_hi" rows="4"
            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
            placeholder="ग्राहकों के लिए संदेश..."><?= htmlspecialchars($maint['message_hi']) ?></textarea>
        </div>
      </div>

    </div>

    <!-- Estimated Completion Time & Countdown -->
    <div class="p-5 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-3">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <label class="block text-xs font-black text-indigo-950 flex items-center gap-2">
          <i class="fa-solid fa-clock text-indigo-600"></i>
          Estimated Completion Date & Time (Real-Time Countdown on Public Screen)
        </label>
        <div class="flex items-center gap-2 text-[11px]">
          <button type="button" onclick="setEndTimeOffset(1)" class="px-2 py-1 rounded bg-white text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-50">+1 Hr</button>
          <button type="button" onclick="setEndTimeOffset(3)" class="px-2 py-1 rounded bg-white text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-50">+3 Hrs</button>
          <button type="button" onclick="setEndTimeOffset(6)" class="px-2 py-1 rounded bg-white text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-50">+6 Hrs</button>
          <button type="button" onclick="document.getElementById('maintEndTime').value='';" class="px-2 py-1 rounded bg-rose-50 text-rose-700 font-bold border border-rose-200 hover:bg-rose-100">Clear</button>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-3">
        <input type="datetime-local" id="maintEndTime" name="maintenance_mode_end_time"
          value="<?= !empty($maint['end_time']) ? date('Y-m-d\TH:i', strtotime($maint['end_time'])) : '' ?>"
          class="w-full sm:w-80 px-3.5 py-2 rounded-xl border border-indigo-200 text-xs font-bold text-gray-800 bg-white focus:ring-2 focus:ring-indigo-500">
        <p class="text-[11px] text-gray-500 leading-tight">
          When configured, a live ticking countdown clock (Days, Hours, Minutes, Seconds) is automatically displayed to shoppers.
        </p>
      </div>
    </div>
  </div>

  <!-- B. IP Whitelist & Testing Secret Bypass -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- 1. IP Whitelist Card -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-4">
      <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
        <div>
          <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-network-wired text-emerald-600"></i>
            2. IP Address Whitelist
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">
            Allow specific IP addresses to browse the frontend without interruption.
          </p>
        </div>
      </div>

      <div class="space-y-3">
        <div class="flex items-center justify-between text-xs">
          <span class="text-gray-600">Your Current Detected IP:</span>
          <span class="font-mono font-bold text-emerald-700 px-2.5 py-0.5 bg-emerald-50 rounded-lg border border-emerald-200">
            <?= htmlspecialchars($client_ip) ?>
          </span>
        </div>

        <textarea name="maintenance_mode_allowed_ips" rows="3" id="allowedIpsField"
          class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500 bg-stone-50"
          placeholder="e.g. 103.21.244.1, 127.0.0.1 (comma-separated)"><?= htmlspecialchars($maint['allowed_ips']) ?></textarea>

        <div class="flex items-center justify-between gap-2 pt-1">
          <p class="text-[11px] text-gray-500">
            Separate multiple IP addresses with commas.
          </p>
          <button type="button" onclick="addMyCurrentIp('<?= htmlspecialchars($client_ip) ?>')"
            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200 transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-plus text-[10px]"></i>
            Add My IP
          </button>
        </div>
      </div>
    </div>

    <!-- 2. Secret Bypass Token & 1-Click Link -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-4">
      <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
        <div>
          <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-key text-amber-500"></i>
            3. Secret Tester Bypass URL
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">
            Share a secret link with partners or QA testers on mobile without requiring staff login.
          </p>
        </div>
      </div>

      <div class="space-y-3">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Secret Bypass Key
          </label>
          <div class="flex items-center gap-2">
            <input type="text" id="secretBypassInput" name="maintenance_mode_secret_bypass"
              value="<?= htmlspecialchars($maint['secret_bypass']) ?>"
              class="w-full px-3.5 py-2 rounded-xl border border-gray-300 font-mono text-xs font-bold bg-stone-50 focus:ring-2 focus:ring-indigo-500">
            <button type="button" onclick="generateRandomToken()"
              class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold transition shrink-0" title="Generate New Key">
              <i class="fa-solid fa-arrows-rotate"></i>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            Sharable Bypass Link (Sets a 7-day authorized testing cookie)
          </label>
          <div class="flex items-center gap-2">
            <input type="text" id="bypassUrlBox" readonly value="<?= htmlspecialchars($bypass_url) ?>"
              class="w-full px-3.5 py-2 rounded-xl border border-gray-200 font-mono text-[11px] bg-stone-100 text-gray-600 select-all">
            <button type="button" onclick="copyBypassUrl()" id="btnCopyUrl"
              class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 shadow-xs">
              <i class="fa-solid fa-copy"></i>
              <span>Copy</span>
            </button>
          </div>
        </div>

        <p class="text-[11px] text-gray-500">
          Anyone opening this link receives an authorized session cookie and can browse the full store during maintenance.
        </p>
      </div>
    </div>

  </div>

  <!-- C. Customer Support Channels on Maintenance Screen -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-4">
    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
      <div>
        <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-headset text-indigo-600"></i>
          4. Customer Support Channels on Maintenance Page
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
          Give shoppers a direct way to reach your team while online orders are temporarily paused.
        </p>
      </div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="maintenance_mode_show_contact" value="1" <?= $maint['show_contact'] == '1' ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
        <span class="ml-3 text-xs font-bold text-gray-800">Show Contact Options</span>
      </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
      <div class="p-4 rounded-2xl bg-stone-50 border border-gray-200 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
          <i class="fa-brands fa-whatsapp"></i>
        </div>
        <div>
          <span class="font-bold text-gray-800 block">WhatsApp Support</span>
          <span class="text-gray-500 font-mono text-[11px]"><?= htmlspecialchars(defined('APP_PHONE') ? APP_PHONE : '') ?></span>
        </div>
      </div>

      <div class="p-4 rounded-2xl bg-stone-50 border border-gray-200 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
          <i class="fa-solid fa-phone"></i>
        </div>
        <div>
          <span class="font-bold text-gray-800 block">Phone Helpline</span>
          <span class="text-gray-500 font-mono text-[11px]"><?= htmlspecialchars(defined('APP_PHONE') ? APP_PHONE : '') ?></span>
        </div>
      </div>

      <div class="p-4 rounded-2xl bg-stone-50 border border-gray-200 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">
          <i class="fa-solid fa-envelope"></i>
        </div>
        <div>
          <span class="font-bold text-gray-800 block">Email Support</span>
          <span class="text-gray-500 font-mono text-[11px]"><?= htmlspecialchars(defined('APP_EMAIL') ? APP_EMAIL : '') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Save Button Bar -->
  <div class="sticky bottom-4 z-20 p-4 bg-white/95 backdrop-blur-md rounded-2xl border border-gray-200 shadow-xl flex items-center justify-between gap-4">
    <div class="text-xs text-gray-500 hidden sm:block">
      Changes take effect instantly. LiteSpeed & PHP OPcache are automatically purged on save.
    </div>

    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
      <a href="/admin/index.php" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-stone-50 text-xs font-bold transition">
        Cancel
      </a>
      <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
        <i class="fa-solid fa-check"></i>
        <span>Save Maintenance Configuration</span>
      </button>
    </div>
  </div>

</form>

<!-- 3. Technical & SEO Safety Assurance Card -->
<div class="mt-8 bg-gradient-to-r from-stone-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-lg space-y-4">
  <div class="flex items-center gap-2.5 text-amber-400 font-black text-xs uppercase tracking-wider">
    <i class="fa-solid fa-shield-halved"></i>
    <span>Enterprise Architecture & Search Engine Protection</span>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-stone-200">
    <div class="space-y-1.5">
      <h4 class="font-bold text-white text-sm flex items-center gap-1.5">
        <i class="fa-brands fa-google text-amber-400"></i>
        Googlebot Safe HTTP 503
      </h4>
      <p class="leading-relaxed text-stone-300">
        When enabled, all pages respond with HTTP status 503 and a <code class="bg-white/10 px-1 py-0.5 rounded text-amber-300">Retry-After: 3600</code> header. Search engines know this is temporary and will NOT drop index rankings.
      </p>
    </div>

    <div class="space-y-1.5">
      <h4 class="font-bold text-white text-sm flex items-center gap-1.5">
        <i class="fa-solid fa-user-shield text-emerald-400"></i>
        Admin Panel Always Available
      </h4>
      <p class="leading-relaxed text-stone-300">
        The entire <code class="bg-white/10 px-1 py-0.5 rounded text-emerald-300">/admin/*</code> portal, staff sign-in routes, and static assets remain 100% accessible to administrators at all times.
      </p>
    </div>

    <div class="space-y-1.5">
      <h4 class="font-bold text-white text-sm flex items-center gap-1.5">
        <i class="fa-solid fa-bolt text-indigo-300"></i>
        Instant Cache Invalidation
      </h4>
      <p class="leading-relaxed text-stone-300">
        Toggling maintenance mode immediately sends LiteSpeed purge headers and clears PHP opcache so your edge proxies respond in real time.
      </p>
    </div>
  </div>
</div>

<script>
  function addMyCurrentIp(ip) {
    if (!ip) return;
    var field = document.getElementById('allowedIpsField');
    var current = field.value.trim();
    if (current) {
      var arr = current.split(',').map(function(s) { return s.trim(); });
      if (arr.indexOf(ip) === -1) {
        arr.push(ip);
        field.value = arr.join(', ');
      }
    } else {
      field.value = ip;
    }
  }

  function generateRandomToken() {
    var chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    var res = 'ganv_';
    for (var i = 0; i < 10; i++) {
      res += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('secretBypassInput').value = res;
    var siteHost = "<?= rtrim($site_host, '/') ?>";
    document.getElementById('bypassUrlBox').value = siteHost + '/?bypass=' + encodeURIComponent(res);
  }

  function copyBypassUrl() {
    var urlBox = document.getElementById('bypassUrlBox');
    urlBox.select();
    urlBox.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(urlBox.value).then(function() {
      var btn = document.getElementById('btnCopyUrl');
      var oldHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-check text-emerald-300"></i> Copied!';
      btn.classList.add('bg-emerald-600');
      btn.classList.remove('bg-indigo-600');
      setTimeout(function() {
        btn.innerHTML = oldHtml;
        btn.classList.remove('bg-emerald-600');
        btn.classList.add('bg-indigo-600');
      }, 2000);
    });
  }

  function setEndTimeOffset(hours) {
    var d = new Date();
    d.setHours(d.getHours() + hours);
    // Format YYYY-MM-DDTHH:MM
    var pad = function(n) { return n < 10 ? '0' + n : n; };
    var str = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    document.getElementById('maintEndTime').value = str;
  }
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
