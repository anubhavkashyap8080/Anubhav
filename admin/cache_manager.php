<?php
/**
 * GanvGaliHat Cache Manager (Super Admin only)
 * One-click clearing of: PHP OPcache, LiteSpeed cache, Cloudflare CDN cache,
 * browser asset cache (version bump), temporary DB data and login lock-outs.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Super Admin Clearance (before any output so headers/redirects work)
require_super_admin();

$self = '/admin/cache_manager.php';

/* ------------------------------------------------------------------
 * Cache operations — each returns [bool ok, string message]
 * ------------------------------------------------------------------ */
function cm_opcache_status() {
    if (!function_exists('opcache_get_status')) return null;
    try { $st = @opcache_get_status(false); return is_array($st) ? $st : null; } catch (Throwable $e) { return null; }
}

function cm_clear_opcache() {
    if (!function_exists('opcache_reset')) return [false, 'OPcache: Not available in this PHP build.'];
    $ok = false;
    try { $ok = @opcache_reset(); } catch (Throwable $e) {}
    return $ok ? [true, 'OPcache: Cleared successfully.'] : [false, 'OPcache: Hosting did not allow reset (clear via cPanel > MultiPHP INI Editor or change PHP version).'];
}

function cm_purge_litespeed() {
    $srv = strtolower((string)($_SERVER['SERVER_SOFTWARE'] ?? ''));
    $is_ls = strpos($srv, 'litespeed') !== false || isset($_SERVER['X-LSCACHE']) || !empty($_SERVER['LSWS_EDITION']);
    if (!headers_sent()) {
        // LiteSpeed reads this response header and purges the whole cache of this site
        header('X-LiteSpeed-Purge: *');
    }
    return $is_ls
        ? [true, 'LiteSpeed Cache: Purge signal sent.']
        : [true, 'LiteSpeed Cache: Purge signal sent (server is not running LiteSpeed; may have no effect).'];
}

function cm_cloudflare_cfg() {
    $cfg = function_exists('get_platform_config') ? (get_platform_config('cloudflare') ?: []) : [];
    $zone = $cfg['zone_id'] ?? getenv('CF_ZONE_ID');
    $token = $cfg['api_token'] ?? getenv('CF_API_TOKEN');
    return [trim((string)$zone), trim((string)$token)];
}

function cm_purge_cloudflare() {
    list($zone, $token) = cm_cloudflare_cfg();
    if ($zone === '' || $token === '') {
        return [false, 'Cloudflare: Zone ID / API Token not configured (see setup instructions below).'];
    }
    $url = 'https://api.cloudflare.com/client/v4/zones/' . rawurlencode($zone) . '/purge_cache';
    $payload = json_encode(['purge_everything' => true]);
    $resp = false; $code = 0;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => 12, 'ignore_errors' => true,
            'header' => "Authorization: Bearer $token\r\nContent-Type: application/json\r\n",
            'content' => $payload,
        ]]);
        $resp = @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) $code = (int)$m[1];
    }
    $data = is_string($resp) ? json_decode($resp, true) : null;
    if (!empty($data['success'])) return [true, 'Cloudflare: Entire CDN cache purged successfully.'];
    $err = $data['errors'][0]['message'] ?? ('HTTP ' . $code);
    return [false, 'Cloudflare: Purge failed — ' . $err];
}

function cm_bump_assets() {
    global $pdo;
    $v = (string)time();
    try {
        $st = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('asset_version', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $st->execute([$v]);
        return [true, "Browser CSS/JS cache: New version ($v) applied — all visitors will receive fresh assets."];
    } catch (Throwable $e) {
        return [false, 'Failed to update asset version.'];
    }
}

function cm_clear_temp_data() {
    global $pdo;
    $parts = [];
    $run = function ($label, $sql, $p = []) use ($pdo, &$parts) {
        try { $st = $pdo->prepare($sql); $st->execute($p); $parts[] = "$label: " . $st->rowCount(); }
        catch (Throwable $e) { $parts[] = "$label: —"; }
    };
    $run('Rate limit records', "DELETE FROM rate_limits WHERE first_hit_at < ?", [date('Y-m-d H:i:s', time() - 86400)]);
    $run('Expired login attempts', "DELETE FROM login_attempts WHERE attempted_at < ?", [date('Y-m-d H:i:s', time() - 172800)]);
    $run('Expired password reset tokens', "DELETE FROM password_resets WHERE expires_at < ? OR used = 1", [date('Y-m-d H:i:s')]);
    $run('Expired IP blocks', "DELETE FROM blocked_ips WHERE blocked_until IS NOT NULL AND blocked_until < ?", [date('Y-m-d H:i:s')]);
    return [true, 'Temporary database records cleared — ' . implode(', ', $parts) . '.'];
}

function cm_clear_lockouts() {
    global $pdo;
    try {
        $n = $pdo->exec("DELETE FROM login_attempts WHERE success = 0");
        $pdo->exec("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL");
        return [true, "Login lockouts reset ($n failed attempts cleared). All users can log in again."];
    } catch (Throwable $e) {
        return [false, 'Error clearing login lockouts.'];
    }
}

/* ------------------------------------------------------------------
 * POST handler
 * ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token expired. Please refresh the page and try again.');
        header('Location: ' . $self);
        exit;
    }
    $act = $_POST['action'] ?? '';
    $results = [];
    $map = [
        'opcache'    => ['cm_clear_opcache'],
        'litespeed'  => ['cm_purge_litespeed'],
        'cloudflare' => ['cm_purge_cloudflare'],
        'assets'     => ['cm_bump_assets'],
        'temp'       => ['cm_clear_temp_data'],
        'lockouts'   => ['cm_clear_lockouts'],
        'all'        => ['cm_clear_opcache', 'cm_purge_litespeed', 'cm_purge_cloudflare', 'cm_bump_assets', 'cm_clear_temp_data'],
    ];
    if (isset($map[$act])) {
        $all_ok = true;
        foreach ($map[$act] as $fn) {
            list($ok, $msg) = $fn();
            $results[] = $msg;
            // Cloudflare "not configured" is informational during Clear-All, not a failure
            if (!$ok && !($act === 'all' && $fn === 'cm_purge_cloudflare')) $all_ok = false;
        }
        if (in_array($act, ['all', 'assets'], true) || !empty($_POST['clear_my_browser'])) {
            $_SESSION['ggh_clear_site_data'] = 1; // ask this admin's browser to drop its cache too
        }
        log_audit_action('cache_cleared', 'system', 'cache', $act, implode(' | ', $results));
        record_security_event('cache_cleared', ['action' => $act, 'results' => $results]);
        set_flash($all_ok ? 'success' : 'error', implode('  ', $results));
    } else {
        set_flash('error', 'Invalid action requested.');
    }
    header('Location: ' . $self);
    exit;
}

// Ask the admin's own browser to purge its cached files once after a clear action
if (!empty($_SESSION['ggh_clear_site_data'])) {
    unset($_SESSION['ggh_clear_site_data']);
    if (!headers_sent()) header('Clear-Site-Data: "cache"');
}

/* ------------------------------------------------------------------
 * Status data
 * ------------------------------------------------------------------ */
$op = cm_opcache_status();
$op_enabled = $op && !empty($op['opcache_enabled']);
$op_scripts = $op_enabled ? (int)($op['opcache_statistics']['num_cached_scripts'] ?? 0) : 0;
$op_mem_mb = $op_enabled ? round(((int)($op['memory_usage']['used_memory'] ?? 0)) / 1048576, 1) : 0;
$op_hit = $op_enabled ? round((float)($op['opcache_statistics']['opcache_hit_rate'] ?? 0), 1) : 0;

$srv_sw = (string)($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
$is_ls = stripos($srv_sw, 'litespeed') !== false || !empty($_SERVER['LSWS_EDITION']);
$behind_cf = !empty($_SERVER['HTTP_CF_RAY']) || !empty($_SERVER['HTTP_CF_CONNECTING_IP']);
list($cf_zone, $cf_token) = cm_cloudflare_cfg();
$cf_ready = ($cf_zone !== '' && $cf_token !== '');
$asset_v = get_site_setting('asset_version', '');
$asset_v_txt = $asset_v !== '' ? date('d M Y, h:i A', (int)$asset_v) : 'Never bumped yet';

$db_counts = [];
foreach ([
    'rate_limits' => 'Rate Limit Records', 'login_attempts' => 'Login Attempt Records',
    'password_resets' => 'Password Reset Tokens', 'blocked_ips' => 'Blocked IP Records'
] as $tbl => $label) {
    try { $db_counts[$label] = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn(); }
    catch (Throwable $e) { $db_counts[$label] = null; }
}

$page_title = 'Cache Manager — Super Admin';
require_once __DIR__ . '/admin_header.php';

function cm_dot($on) { return '<span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:' . ($on ? '#10b981' : '#a8a29e') . ';margin-right:6px"></span>'; }
?>

<style>
  .cm-card{background:#fff;border:1px solid #e5e7eb;border-radius:20px;box-shadow:0 1px 2px rgba(0,0,0,.04);padding:18px 20px}
  .cm-grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
  .cm-card h3{font-weight:800;font-size:14px;color:#1c1917;margin:0 0 4px}
  .cm-card p{font-size:12px;color:#57534e;line-height:1.6;margin:0 0 10px}
  .cm-meta{font-size:11px;color:#78716c;margin-bottom:10px;line-height:1.7}
  .cm-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:11px;font-size:12px;font-weight:800;cursor:pointer;border:0;color:#fff;background:#312e81}
  .cm-btn:hover{background:#4338ca}.cm-btn.warn{background:#b45309}.cm-btn.warn:hover{background:#92400e}
  .cm-btn.big{padding:13px 24px;font-size:14px;background:#be123c}.cm-btn.big:hover{background:#9f1239}
  .cm-code{background:#1c1917;color:#fde68a;border-radius:12px;padding:12px 14px;font-size:11px;overflow-x:auto;font-family:ui-monospace,monospace;line-height:1.6}
</style>

<div class="space-y-6">

  <div class="bg-gradient-to-r from-stone-900 via-indigo-950 to-stone-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-indigo-900/50">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px;align-items:center">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white"><i class="fa-solid fa-broom"></i> Cache Manager</h1>
        <p class="text-indigo-200 text-xs sm:text-sm" style="max-width:640px;margin-top:6px">
          Flush server caches, LiteSpeed cache, and reset temporary locks with a single click.
        </p>
      </div>
      <form method="POST" onsubmit="return confirm('All OPcache, LiteSpeed, Cloudflare, and file caches will be cleared. Continue?');">
        <?= csrf_field() ?><input type="hidden" name="action" value="all">
        <button class="cm-btn big" type="submit"><i class="fa-solid fa-bolt"></i> Clear All Caches</button>
      </form>
    </div>
  </div>

  <div class="cm-grid">

    <div class="cm-card">
      <h3><?= cm_dot($op_enabled) ?>PHP OPcache</h3>
      <div class="cm-meta">
        <?php if ($op_enabled): ?>
          Active • <?= $op_scripts ?> files cached • Memory <?= $op_mem_mb ?> MB • Hit Rate <?= $op_hit ?>%
        <?php else: ?>
          Inactive or unavailable
        <?php endif; ?>
        <br>PHP <?= htmlspecialchars(PHP_VERSION) ?>
      </div>
      <p>Clear OPcache if older PHP scripts continue executing following a code update or file upload.</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="opcache"><button class="cm-btn" type="submit"><i class="fa-solid fa-microchip"></i> Clear OPcache</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot($is_ls) ?>LiteSpeed Cache</h3>
      <div class="cm-meta">Server: <?= htmlspecialchars($srv_sw) ?><br><?= $is_ls ? 'LiteSpeed Detected' : 'LiteSpeed Not Detected' ?></div>
      <p>Purges cPanel/LiteSpeed page cache. Most common resolution for stale CSRF tokens and outdated cached responses.</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="litespeed"><button class="cm-btn" type="submit"><i class="fa-solid fa-gauge-high"></i> LiteSpeed Purge</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot($cf_ready) ?>Cloudflare CDN</h3>
      <div class="cm-meta">
        <?= $behind_cf ? 'Site is behind Cloudflare' : 'Cloudflare traffic not detected' ?><br>
        API: <?= $cf_ready ? 'Configured' : 'Not Set' ?>
      </div>
      <p>Purges Cloudflare full edge cache globally.</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="cloudflare"><button class="cm-btn" type="submit" <?= $cf_ready ? '' : 'disabled style="opacity:.5;cursor:not-allowed"' ?>><i class="fa-solid fa-cloud"></i> Cloudflare Purge</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>Browser CSS/JS Cache</h3>
      <div class="cm-meta">Last version bump: <?= htmlspecialchars($asset_v_txt) ?></div>
      <p>Forces all visitor browsers to download updated main.js/CSS assets by updating URL cache-busting tokens.</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="assets"><button class="cm-btn" type="submit"><i class="fa-solid fa-rotate"></i> Bump Asset Version</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>Temporary Database Data</h3>
      <div class="cm-meta">
        <?php foreach ($db_counts as $label => $cnt): ?>
          <?= htmlspecialchars($label) ?>: <b><?= $cnt === null ? '—' : $cnt ?></b><br>
        <?php endforeach; ?>
      </div>
      <p>Removes expired rate-limits, login attempts, expired password reset tokens, and expired temporary IP blocks.</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="temp"><button class="cm-btn" type="submit"><i class="fa-solid fa-database"></i> Purge Temporary Data</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>Login Lockout Reset</h3>
      <div class="cm-meta">Clears temporary lockouts caused by repeated failed password attempts.</div>
      <p>Run this if a user or seller encounters "Security lockout: please try again in a few minutes". (Permanent IP blocklists are not affected).</p>
      <form method="POST" onsubmit="return confirm('All failed login records and lockouts will be reset. Continue?');"><?= csrf_field() ?><input type="hidden" name="action" value="lockouts"><button class="cm-btn warn" type="submit"><i class="fa-solid fa-unlock"></i> Reset Lockouts</button></form>
    </div>

  </div>

  <div class="cm-card">
    <h3><i class="fa-solid fa-cloud"></i> Cloudflare Purge Setup (Optional)</h3>
    <p>Add this configuration block to your config file (<code>/home/&lt;cpanel-user&gt;/ganvgalihat_config.php</code>). Grant the API token <b>Zone → Cache Purge</b> permissions in Cloudflare.</p>
    <pre class="cm-code">'cloudflare' =&gt; [
    'zone_id'   =&gt; 'YOUR_ZONE_ID',
    'api_token' =&gt; 'YOUR_API_TOKEN_WITH_CACHE_PURGE_PERMISSION',
],</pre>
    <p style="margin-top:10px">Every cache operation is permanently logged to the audit logs and security logs (<a href="/admin/security_logs.php" style="color:#4338ca;font-weight:700">Advanced Security Logs</a>).</p>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
