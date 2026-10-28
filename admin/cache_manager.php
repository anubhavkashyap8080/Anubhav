<?php
/**
 * GaunGaliHat Cache Manager (Super Admin only)
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
    if (!function_exists('opcache_reset')) return [false, 'OPcache: इस PHP में उपलब्ध नहीं है।'];
    $ok = false;
    try { $ok = @opcache_reset(); } catch (Throwable $e) {}
    return $ok ? [true, 'OPcache: साफ़ किया गया।'] : [false, 'OPcache: होस्टिंग ने reset की अनुमति नहीं दी (cPanel > MultiPHP INI Editor से या PHP version बदलकर साफ़ करें)।'];
}

function cm_purge_litespeed() {
    $srv = strtolower((string)($_SERVER['SERVER_SOFTWARE'] ?? ''));
    $is_ls = strpos($srv, 'litespeed') !== false || isset($_SERVER['X-LSCACHE']) || !empty($_SERVER['LSWS_EDITION']);
    if (!headers_sent()) {
        // LiteSpeed reads this response header and purges the whole cache of this site
        header('X-LiteSpeed-Purge: *');
    }
    return $is_ls
        ? [true, 'LiteSpeed Cache: purge सिग्नल भेजा गया।']
        : [true, 'LiteSpeed Cache: purge सिग्नल भेजा गया (यह सर्वर LiteSpeed नहीं दिख रहा, इसलिए असर नहीं भी हो सकता)।'];
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
        return [false, 'Cloudflare: Zone ID / API Token कॉन्फ़िगर नहीं है (नीचे सेटअप निर्देश देखें)।'];
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
    if (!empty($data['success'])) return [true, 'Cloudflare: पूरा CDN cache purge किया गया।'];
    $err = $data['errors'][0]['message'] ?? ('HTTP ' . $code);
    return [false, 'Cloudflare: purge विफल — ' . $err];
}

function cm_bump_assets() {
    global $pdo;
    $v = (string)time();
    try {
        $st = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('asset_version', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $st->execute([$v]);
        return [true, "ब्राउज़र CSS/JS cache: नया version ($v) लागू — सभी विज़िटर्स को नई फ़ाइलें मिलेंगी।"];
    } catch (Throwable $e) {
        return [false, 'Asset version अपडेट नहीं हो सका।'];
    }
}

function cm_clear_temp_data() {
    global $pdo;
    $parts = [];
    $run = function ($label, $sql, $p = []) use ($pdo, &$parts) {
        try { $st = $pdo->prepare($sql); $st->execute($p); $parts[] = "$label: " . $st->rowCount(); }
        catch (Throwable $e) { $parts[] = "$label: —"; }
    };
    $run('रेट-लिमिट रिकॉर्ड', "DELETE FROM rate_limits WHERE first_hit_at < ?", [date('Y-m-d H:i:s', time() - 86400)]);
    $run('पुराने लॉगिन प्रयास', "DELETE FROM login_attempts WHERE attempted_at < ?", [date('Y-m-d H:i:s', time() - 172800)]);
    $run('समाप्त पासवर्ड-रीसेट टोकन', "DELETE FROM password_resets WHERE expires_at < ? OR used = 1", [date('Y-m-d H:i:s')]);
    $run('समाप्त IP ब्लॉक', "DELETE FROM blocked_ips WHERE blocked_until IS NOT NULL AND blocked_until < ?", [date('Y-m-d H:i:s')]);
    return [true, 'अस्थायी डेटा साफ़ — ' . implode(', ', $parts) . '।'];
}

function cm_clear_lockouts() {
    global $pdo;
    try {
        $n = $pdo->exec("DELETE FROM login_attempts WHERE success = 0");
        $pdo->exec("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL");
        return [true, "लॉगिन लॉकआउट हटाए गए ($n असफल-प्रयास रिकॉर्ड साफ़)। सभी यूज़र्स फिर से लॉगिन कर सकते हैं।"];
    } catch (Throwable $e) {
        return [false, 'लॉकआउट साफ़ करने में त्रुटि।'];
    }
}

/* ------------------------------------------------------------------
 * POST handler
 * ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'सुरक्षा टोकन अमान्य है। पृष्ठ रीफ़्रेश करके पुनः प्रयास करें।');
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
        set_flash('error', 'अमान्य क्रिया।');
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
$asset_v_txt = $asset_v !== '' ? date('d M Y, h:i A', (int)$asset_v) : 'अभी तक कभी नहीं';

$db_counts = [];
foreach ([
    'rate_limits' => 'रेट-लिमिट रिकॉर्ड', 'login_attempts' => 'लॉगिन प्रयास रिकॉर्ड',
    'password_resets' => 'पासवर्ड रीसेट टोकन', 'blocked_ips' => 'ब्लॉक किए IP'
] as $tbl => $label) {
    try { $db_counts[$label] = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn(); }
    catch (Throwable $e) { $db_counts[$label] = null; }
}

$page_title = 'कैश मैनेजर (Cache Manager)';
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
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white"><i class="fa-solid fa-broom"></i> कैश मैनेजर (Cache Manager)</h1>
        <p class="text-indigo-200 text-xs sm:text-sm" style="max-width:640px;margin-top:6px">
          साइट पर पुराना पेज/टोकन/JS दिखने (जैसे Add to Cart में CSRF error) की समस्या होने पर यहाँ से सभी cache एक क्लिक में साफ़ करें।
        </p>
      </div>
      <form method="POST" onsubmit="return confirm('OPcache, LiteSpeed, Cloudflare (अगर सेट है), ब्राउज़र-asset cache और अस्थायी डेटा सब साफ़ होगा। जारी रखें?')">
        <?= csrf_field() ?><input type="hidden" name="action" value="all">
        <button class="cm-btn big" type="submit"><i class="fa-solid fa-bolt"></i> सब कुछ साफ़ करें (Clear All)</button>
      </form>
    </div>
  </div>

  <div class="cm-grid">

    <div class="cm-card">
      <h3><?= cm_dot($op_enabled) ?>PHP OPcache</h3>
      <div class="cm-meta">
        <?php if ($op_enabled): ?>
          सक्रिय • <?= $op_scripts ?> फ़ाइलें cached • मेमोरी <?= $op_mem_mb ?> MB • hit rate <?= $op_hit ?>%
        <?php else: ?>
          निष्क्रिय या उपलब्ध नहीं
        <?php endif; ?>
        <br>PHP <?= htmlspecialchars(PHP_VERSION) ?>
      </div>
      <p>कोड अपडेट/अपलोड के बाद भी पुराना PHP कोड चल रहा हो तो OPcache साफ़ करें।</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="opcache"><button class="cm-btn" type="submit"><i class="fa-solid fa-microchip"></i> OPcache साफ़ करें</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot($is_ls) ?>LiteSpeed Cache</h3>
      <div class="cm-meta">सर्वर: <?= htmlspecialchars($srv_sw) ?><br><?= $is_ls ? 'LiteSpeed पहचाना गया' : 'LiteSpeed नहीं दिख रहा' ?></div>
      <p>cPanel/LiteSpeed का पेज-कैश हटाता है। यही पुराने CSRF टोकन वाले cached पेज की सबसे संभावित वजह है।</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="litespeed"><button class="cm-btn" type="submit"><i class="fa-solid fa-gauge-high"></i> LiteSpeed Purge</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot($cf_ready) ?>Cloudflare CDN</h3>
      <div class="cm-meta">
        <?= $behind_cf ? 'साइट Cloudflare के पीछे है' : 'Cloudflare ट्रैफ़िक नहीं दिखा' ?><br>
        API: <?= $cf_ready ? 'कॉन्फ़िगर है' : 'सेट नहीं' ?>
      </div>
      <p>Cloudflare का पूरा edge cache purge करता है।</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="cloudflare"><button class="cm-btn" type="submit" <?= $cf_ready ? '' : 'disabled style="opacity:.5;cursor:not-allowed"' ?>><i class="fa-solid fa-cloud"></i> Cloudflare Purge</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>ब्राउज़र CSS/JS Cache</h3>
      <div class="cm-meta">अंतिम version bump: <?= htmlspecialchars($asset_v_txt) ?></div>
      <p>सभी विज़िटर्स के ब्राउज़र को नई main.js/CSS फ़ाइलें डाउनलोड करवाता है (URL में version बदलकर)।</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="assets"><button class="cm-btn" type="submit"><i class="fa-solid fa-rotate"></i> Asset Version बदलें</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>अस्थायी डेटाबेस डेटा</h3>
      <div class="cm-meta">
        <?php foreach ($db_counts as $label => $cnt): ?>
          <?= htmlspecialchars($label) ?>: <b><?= $cnt === null ? '—' : $cnt ?></b><br>
        <?php endforeach; ?>
      </div>
      <p>पुराने रेट-लिमिट, लॉगिन प्रयास, समाप्त रीसेट-टोकन और समाप्त IP-ब्लॉक हटाता है।</p>
      <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="temp"><button class="cm-btn" type="submit"><i class="fa-solid fa-database"></i> अस्थायी डेटा साफ़ करें</button></form>
    </div>

    <div class="cm-card">
      <h3><?= cm_dot(true) ?>लॉगिन लॉकआउट रीसेट</h3>
      <div class="cm-meta">बार-बार गलत पासवर्ड से लगा अस्थायी ब्लॉक हटाता है।</div>
      <p>अगर कोई यूज़र/सेलर "सुरक्षा अवरोध: कुछ मिनट बाद प्रयास करें" देख रहा है तो इसे चलाएं। (IP ब्लॉक सूची प्रभावित नहीं होती)</p>
      <form method="POST" onsubmit="return confirm('सभी असफल-लॉगिन रिकॉर्ड और लॉकआउट हट जाएंगे। जारी रखें?')"><?= csrf_field() ?><input type="hidden" name="action" value="lockouts"><button class="cm-btn warn" type="submit"><i class="fa-solid fa-unlock"></i> लॉकआउट हटाएं</button></form>
    </div>

  </div>

  <div class="cm-card">
    <h3><i class="fa-solid fa-cloud"></i> Cloudflare Purge सेटअप (वैकल्पिक)</h3>
    <p>अपने कॉन्फ़िग फ़ाइल (<code>/home/&lt;cpanel-user&gt;/gaungalihat_config.php</code>) में यह ब्लॉक जोड़ें। Token को Cloudflare में केवल <b>Zone → Cache Purge</b> अनुमति दें।</p>
    <pre class="cm-code">'cloudflare' =&gt; [
    'zone_id'   =&gt; 'YOUR_ZONE_ID',
    'api_token' =&gt; 'YOUR_API_TOKEN_WITH_CACHE_PURGE_PERMISSION',
],</pre>
    <p style="margin-top:10px">हर cache क्रिया ऑडिट लॉग और सुरक्षा लॉग (<a href="/admin/security_logs.php" style="color:#4338ca;font-weight:700">Advanced Security Logs</a>) में दर्ज होती है।</p>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
