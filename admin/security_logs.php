<?php
/**
 * GaunGaliHat Advanced Security Log Console (Super Admin only)
 * - Severity classification, filters, search, pagination, CSV export
 * - 7-day threat trend chart, top attacking IPs, suspicious login detection
 * - IP block / unblock list, log retention purge
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Super Admin Clearance (runs before any output so redirects/exports work)
require_super_admin();

$self = '/admin/security_logs.php';
$my_ip = get_client_ip();

/* ------------------------------------------------------------------
 * 0. One-time-per-load severity back-fill for rows logged before upgrade
 * ------------------------------------------------------------------ */
try {
    foreach (security_severity_patterns() as $level => $patterns) {
        $conds = implode(' OR ', array_fill(0, count($patterns), 'event_type LIKE ?'));
        $st = $pdo->prepare("UPDATE security_logs SET severity = ? WHERE (severity = 'info' OR severity IS NULL) AND ($conds)");
        $st->execute(array_merge([$level], $patterns));
    }
} catch (Throwable $e) {}

/* ------------------------------------------------------------------
 * 1. POST actions: block IP, unblock IP, purge old logs
 * ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'सुरक्षा टोकन अमान्य है। पृष्ठ रीफ़्रेश करके पुनः प्रयास करें।');
        header('Location: ' . $self);
        exit;
    }
    $act = $_POST['action'] ?? '';

    if ($act === 'block_ip') {
        $ip = trim((string)($_POST['ip_address'] ?? ''));
        $hours = max(0, (int)($_POST['hours'] ?? 24));
        $reason = substr(trim((string)($_POST['reason'] ?? 'Blocked by super admin')), 0, 255);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            set_flash('error', 'अमान्य IP पता।');
        } elseif ($ip === $my_ip) {
            set_flash('error', 'आप अपना खुद का IP ब्लॉक नहीं कर सकते।');
        } else {
            $until = $hours > 0 ? date('Y-m-d H:i:s', time() + $hours * 3600) : null;
            try {
                $st = $pdo->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_by, blocked_until) VALUES (?, ?, ?, ?)
                                     ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_by = VALUES(blocked_by), blocked_until = VALUES(blocked_until), created_at = CURRENT_TIMESTAMP");
                $st->execute([$ip, $reason, $_SESSION['user_id'] ?? null, $until]);
                log_audit_action('ip_blocked', 'security', 'ip', $ip, "Blocked $ip " . ($hours > 0 ? "for {$hours}h" : 'permanently') . ": $reason");
                record_security_event('ip_blocked_manual', ['ip' => $ip, 'hours' => $hours, 'reason' => $reason]);
                set_flash('success', "IP $ip सफलतापूर्वक ब्लॉक किया गया।");
            } catch (Throwable $e) {
                set_flash('error', 'IP ब्लॉक करने में त्रुटि हुई।');
            }
        }
    } elseif ($act === 'unblock_ip') {
        $id = (int)($_POST['block_id'] ?? 0);
        try {
            $st = $pdo->prepare("SELECT ip_address FROM blocked_ips WHERE id = ?");
            $st->execute([$id]);
            $row = $st->fetch();
            if ($row) {
                $pdo->prepare("DELETE FROM blocked_ips WHERE id = ?")->execute([$id]);
                log_audit_action('ip_unblocked', 'security', 'ip', $row['ip_address'], 'IP unblocked');
                set_flash('success', 'IP ' . $row['ip_address'] . ' अनब्लॉक किया गया।');
            }
        } catch (Throwable $e) {
            set_flash('error', 'अनब्लॉक करने में त्रुटि हुई।');
        }
    } elseif ($act === 'purge_logs') {
        $days = max(7, (int)($_POST['days'] ?? 90));
        try {
            $cutoff = date('Y-m-d H:i:s', time() - $days * 86400);
            $st = $pdo->prepare("DELETE FROM security_logs WHERE created_at < ?");
            $st->execute([$cutoff]);
            $n = $st->rowCount();
            log_audit_action('security_logs_purged', 'security', 'security_logs', null, "Purged $n security log rows older than $days days");
            set_flash('success', "$n पुराने सुरक्षा लॉग ($days दिन से पुराने) हटाए गए।");
        } catch (Throwable $e) {
            set_flash('error', 'लॉग हटाने में त्रुटि हुई।');
        }
    }
    header('Location: ' . $self);
    exit;
}

/* ------------------------------------------------------------------
 * 2. Filters
 * ------------------------------------------------------------------ */
$f_q        = trim((string)($_GET['q'] ?? ''));
$f_sev      = (string)($_GET['severity'] ?? '');
$f_event    = trim((string)($_GET['event'] ?? ''));
$f_ip       = trim((string)($_GET['ip'] ?? ''));
$f_from     = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['from'] ?? '')) ? $_GET['from'] : '';
$f_to       = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['to'] ?? '')) ? $_GET['to'] : '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 25;

$where = ['1=1'];
$params = [];
if ($f_sev !== '' && in_array($f_sev, ['critical', 'high', 'medium', 'info'], true)) {
    $where[] = "s.severity = ?"; $params[] = $f_sev;
}
if ($f_event !== '') { $where[] = "s.event_type = ?"; $params[] = $f_event; }
if ($f_ip !== '')    { $where[] = "s.ip_address LIKE ?"; $params[] = $f_ip . '%'; }
if ($f_from !== '')  { $where[] = "s.created_at >= ?"; $params[] = $f_from . ' 00:00:00'; }
if ($f_to !== '')    { $where[] = "s.created_at <= ?"; $params[] = $f_to . ' 23:59:59'; }
if ($f_q !== '') {
    $where[] = "(s.event_type LIKE ? OR s.details LIKE ? OR s.ip_address LIKE ? OR s.request_uri LIKE ? OR u.name LIKE ? OR u.username LIKE ?)";
    $like = '%' . $f_q . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
$where_sql = implode(' AND ', $where);
$from_sql = "FROM security_logs s LEFT JOIN users u ON u.id = s.user_id WHERE $where_sql";

/* ------------------------------------------------------------------
 * 3. CSV export (respects filters, max 10,000 rows)
 * ------------------------------------------------------------------ */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=GaunGaliHat_Security_Logs_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Time', 'Severity', 'Event', 'IP', 'User', 'Method', 'URL', 'User Agent', 'Details']);
    $st = $pdo->prepare("SELECT s.*, u.name AS user_name $from_sql ORDER BY s.id DESC LIMIT 10000");
    $st->execute($params);
    while ($r = $st->fetch()) {
        fputcsv($out, array_map('neutralize_csv_formula', [
            $r['id'], $r['created_at'], $r['severity'] ?: security_event_severity($r['event_type']), $r['event_type'],
            $r['ip_address'], $r['user_name'] ?? '', $r['request_method'] ?? '', $r['request_uri'] ?? '',
            $r['user_agent'] ?? '', $r['details'] ?? ($r['description'] ?? '')
        ]));
    }
    fclose($out);
    log_audit_action('security_logs_exported', 'security', 'security_logs', null, 'CSV export of security logs');
    exit;
}

/* ------------------------------------------------------------------
 * 4. Data for dashboard
 * ------------------------------------------------------------------ */
$t24 = date('Y-m-d H:i:s', time() - 86400);
$t7d = date('Y-m-d 00:00:00', time() - 6 * 86400);
$t1h = date('Y-m-d H:i:s', time() - 3600);

$scalar = function ($sql, $p = []) use ($pdo) {
    try { $st = $pdo->prepare($sql); $st->execute($p); return (int)$st->fetchColumn(); } catch (Throwable $e) { return 0; }
};
$rows = function ($sql, $p = []) use ($pdo) {
    try { $st = $pdo->prepare($sql); $st->execute($p); return $st->fetchAll(); } catch (Throwable $e) { return []; }
};

$stat_total24   = $scalar("SELECT COUNT(*) FROM security_logs WHERE created_at >= ?", [$t24]);
$stat_critical  = $scalar("SELECT COUNT(*) FROM security_logs WHERE created_at >= ? AND severity = 'critical'", [$t24]);
$stat_high      = $scalar("SELECT COUNT(*) FROM security_logs WHERE created_at >= ? AND severity = 'high'", [$t24]);
$stat_failed    = $scalar("SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND attempted_at >= ?", [$t24]);
$stat_ips       = $scalar("SELECT COUNT(DISTINCT ip_address) FROM security_logs WHERE created_at >= ? AND severity IN ('critical','high','medium')", [$t24]);
$stat_blocked   = $scalar("SELECT COUNT(*) FROM blocked_ips WHERE blocked_until IS NULL OR blocked_until > ?", [date('Y-m-d H:i:s')]);

// 7-day trend by severity
$trend_raw = $rows("SELECT DATE(created_at) d, severity, COUNT(*) c FROM security_logs WHERE created_at >= ? GROUP BY DATE(created_at), severity", [$t7d]);
$days = [];
for ($i = 6; $i >= 0; $i--) { $days[date('Y-m-d', time() - $i * 86400)] = ['critical' => 0, 'high' => 0, 'medium' => 0, 'info' => 0]; }
foreach ($trend_raw as $r) {
    $sv = in_array($r['severity'], ['critical', 'high', 'medium'], true) ? $r['severity'] : 'info';
    if (isset($days[$r['d']])) $days[$r['d']][$sv] += (int)$r['c'];
}
$chart_labels = array_map(function ($d) { return date('d M', strtotime($d)); }, array_keys($days));
$chart_series = ['critical' => [], 'high' => [], 'medium' => [], 'info' => []];
foreach ($days as $d) { foreach ($d as $k => $v) $chart_series[$k][] = $v; }

// Top IPs (7 days, warnings only) and top event types
$top_ips = $rows("SELECT ip_address, COUNT(*) c, MAX(created_at) last_seen FROM security_logs
                  WHERE created_at >= ? AND severity IN ('critical','high','medium') GROUP BY ip_address ORDER BY c DESC LIMIT 8", [$t7d]);
$top_events = $rows("SELECT event_type, severity, COUNT(*) c FROM security_logs WHERE created_at >= ? GROUP BY event_type, severity ORDER BY c DESC LIMIT 8", [$t7d]);

// Suspicious: >= 5 failed logins in the last hour from one IP
$suspicious = $rows("SELECT ip_address, COUNT(*) c, COUNT(DISTINCT identifier) ids, MAX(attempted_at) last_try FROM login_attempts
                     WHERE success = 0 AND attempted_at >= ? GROUP BY ip_address HAVING c >= 5 ORDER BY c DESC LIMIT 10", [$t1h]);

// Blocked IPs
$blocked = $rows("SELECT b.*, u.name AS by_name FROM blocked_ips b LEFT JOIN users u ON u.id = b.blocked_by
                  WHERE b.blocked_until IS NULL OR b.blocked_until > ? ORDER BY b.id DESC LIMIT 50", [date('Y-m-d H:i:s')]);
$blocked_ip_set = array_flip(array_column($blocked, 'ip_address'));

// Event type list for filter dropdown
$event_types = $rows("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type ASC LIMIT 100");

// Main table
$total_rows = $scalar("SELECT COUNT(*) $from_sql", $params);
$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$logs = $rows("SELECT s.*, u.name AS user_name, u.username AS user_username $from_sql ORDER BY s.id DESC LIMIT $per_page OFFSET $offset", $params);

function sl_url($over = []) {
    $q = array_merge($_GET, $over);
    unset($q['export']);
    foreach ($q as $k => $v) { if ($v === '' || $v === null) unset($q[$k]); }
    return '/admin/security_logs.php' . (empty($q) ? '' : '?' . http_build_query($q));
}
function sl_sev_badge($sev) {
    $map = [
        'critical' => ['#fee2e2', '#991b1b', 'CRITICAL'],
        'high'     => ['#ffedd5', '#9a3412', 'HIGH'],
        'medium'   => ['#fef3c7', '#92400e', 'MEDIUM'],
        'info'     => ['#e0e7ff', '#3730a3', 'INFO'],
    ];
    $m = $map[$sev] ?? $map['info'];
    return '<span class="sl-badge" style="background:' . $m[0] . ';color:' . $m[1] . '">' . $m[2] . '</span>';
}

$page_title = 'उन्नत सुरक्षा लॉग (Advanced Security Logs)';
require_once __DIR__ . '/admin_header.php';
?>

<style>
  .sl-badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.04em}
  .sl-input{border:1px solid #d6d3d1;border-radius:10px;padding:7px 10px;font-size:12px;background:#fff;min-width:0}
  .sl-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid transparent;text-decoration:none}
  .sl-btn-primary{background:#312e81;color:#fff}.sl-btn-primary:hover{background:#4338ca}
  .sl-btn-danger{background:#b91c1c;color:#fff}.sl-btn-danger:hover{background:#991b1b}
  .sl-btn-ghost{background:#fff;color:#44403c;border-color:#d6d3d1}.sl-btn-ghost:hover{background:#f5f5f4}
  .sl-btn-xs{padding:3px 9px;font-size:10px;border-radius:8px}
  .sl-card{background:#fff;border:1px solid #e5e7eb;border-radius:20px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
  .sl-stat{padding:16px 18px}.sl-stat b{display:block;font-size:26px;font-weight:900;color:#1c1917;line-height:1.1}
  .sl-stat span{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#78716c}
  .sl-grid{display:grid;gap:16px}
  .sl-g5{grid-template-columns:repeat(auto-fit,minmax(160px,1fr))}
  .sl-g2{grid-template-columns:repeat(auto-fit,minmax(320px,1fr))}
  .sl-table{width:100%;border-collapse:collapse;font-size:12px}
  .sl-table th{background:#fafaf9;color:#78716c;font-size:10px;text-transform:uppercase;text-align:left;padding:9px 12px;border-bottom:1px solid #f0efed}
  .sl-table td{padding:9px 12px;border-bottom:1px solid #f5f5f4;vertical-align:top}
  .sl-mono{font-family:ui-monospace,monospace;font-size:11px}
  .sl-detail{background:#fafaf9;border-radius:10px;padding:10px 12px;margin-top:6px;font-size:11px;line-height:1.6;word-break:break-all;white-space:pre-wrap}
  .sl-row-critical{background:#fff5f5}
  .sl-pill{display:inline-block;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;border:1px solid #d6d3d1;color:#44403c;background:#fff;text-decoration:none}
  .sl-pill.active{background:#312e81;color:#fff;border-color:#312e81}
  .sl-h{font-weight:800;font-size:13px;color:#1c1917;padding:14px 18px;border-bottom:1px solid #f0efed;display:flex;justify-content:space-between;align-items:center;gap:8px}
</style>

<div class="space-y-6">

  <!-- Header -->
  <div class="bg-gradient-to-r from-stone-900 via-rose-950 to-stone-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-rose-900/50">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px;align-items:center">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-black uppercase tracking-wider border border-rose-500/30">
          <i class="fa-solid fa-satellite-dish"></i> Threat Monitoring
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white" style="margin-top:8px">उन्नत सुरक्षा लॉग (Advanced Security Logs)</h1>
        <p class="text-rose-200 text-xs sm:text-sm" style="max-width:640px;margin-top:4px">
          Severity ग्रेडिंग, IP/यूज़र ट्रैकिंग, संदिग्ध लॉगिन डिटेक्शन, IP ब्लॉक सूची और CSV एक्सपोर्ट। Critical घटनाओं पर मालिक को ईमेल अलर्ट जाता है।
        </p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="sl-btn sl-btn-ghost" href="<?= htmlspecialchars(sl_url() . (strpos(sl_url(), '?') === false ? '?' : '&') . 'export=csv') ?>"><i class="fa-solid fa-file-csv"></i> CSV Export</a>
        <a class="sl-btn sl-btn-ghost" href="/admin/security_center.php"><i class="fa-solid fa-shield-halved"></i> सुरक्षा केंद्र</a>
        <a class="sl-btn sl-btn-ghost" href="/admin/cache_manager.php"><i class="fa-solid fa-broom"></i> Cache Manager</a>
        <label class="sl-btn sl-btn-ghost" style="margin:0"><input type="checkbox" id="autoRefresh"> Auto-refresh 30s</label>
      </div>
    </div>
  </div>

  <!-- Stat cards -->
  <div class="sl-grid sl-g5">
    <div class="sl-card sl-stat"><span>घटनाएं (24h)</span><b><?= $stat_total24 ?></b></div>
    <div class="sl-card sl-stat"><span style="color:#b91c1c">Critical (24h)</span><b style="color:#b91c1c"><?= $stat_critical ?></b></div>
    <div class="sl-card sl-stat"><span style="color:#c2410c">High (24h)</span><b style="color:#c2410c"><?= $stat_high ?></b></div>
    <div class="sl-card sl-stat"><span>असफल लॉगिन (24h)</span><b><?= $stat_failed ?></b></div>
    <div class="sl-card sl-stat"><span>संदिग्ध IP (24h)</span><b><?= $stat_ips ?></b></div>
    <div class="sl-card sl-stat"><span>ब्लॉक किए गए IP</span><b><?= $stat_blocked ?></b></div>
  </div>

  <!-- Chart + Top lists -->
  <div class="sl-grid sl-g2">
    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-chart-column"></i> पिछले 7 दिन का ट्रेंड</span></div>
      <div style="padding:14px 18px"><div style="position:relative;height:220px"><canvas id="slTrend"></canvas></div></div>
    </div>

    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-bullseye"></i> टॉप संदिग्ध IP (7 दिन)</span></div>
      <table class="sl-table">
        <thead><tr><th>IP</th><th>घटनाएं</th><th>अंतिम बार</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($top_ips)): ?>
          <tr><td colspan="4" style="text-align:center;color:#a8a29e;padding:24px">कोई संदिग्ध IP नहीं मिला ✓</td></tr>
        <?php endif; ?>
        <?php foreach ($top_ips as $t): ?>
          <tr>
            <td class="sl-mono"><a href="<?= htmlspecialchars(sl_url(['ip' => $t['ip_address'], 'page' => 1])) ?>"><?= htmlspecialchars($t['ip_address']) ?></a></td>
            <td><b><?= (int)$t['c'] ?></b></td>
            <td class="sl-mono"><?= htmlspecialchars(date('d M, h:i A', strtotime($t['last_seen']))) ?></td>
            <td>
              <?php if (isset($blocked_ip_set[$t['ip_address']])): ?>
                <span class="sl-badge" style="background:#e7e5e4;color:#44403c">BLOCKED</span>
              <?php elseif ($t['ip_address'] !== $my_ip): ?>
                <form method="POST" onsubmit="return confirm('IP <?= htmlspecialchars($t['ip_address']) ?> को 24 घंटे के लिए ब्लॉक करें?')">
                  <?= csrf_field() ?><input type="hidden" name="action" value="block_ip">
                  <input type="hidden" name="ip_address" value="<?= htmlspecialchars($t['ip_address']) ?>">
                  <input type="hidden" name="hours" value="24"><input type="hidden" name="reason" value="Blocked from top suspicious IPs">
                  <button class="sl-btn sl-btn-danger sl-btn-xs" type="submit">Block 24h</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="sl-grid sl-g2">
    <!-- Suspicious logins -->
    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-user-secret"></i> ब्रूट-फोर्स संदिग्ध (पिछला 1 घंटा, 5+ असफल लॉगिन)</span></div>
      <table class="sl-table">
        <thead><tr><th>IP</th><th>असफल</th><th>अलग-अलग अकाउंट</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($suspicious)): ?>
          <tr><td colspan="4" style="text-align:center;color:#a8a29e;padding:24px">कोई ब्रूट-फोर्स पैटर्न नहीं मिला ✓</td></tr>
        <?php endif; ?>
        <?php foreach ($suspicious as $sp): ?>
          <tr>
            <td class="sl-mono"><?= htmlspecialchars($sp['ip_address']) ?></td>
            <td><b style="color:#b91c1c"><?= (int)$sp['c'] ?></b></td>
            <td><?= (int)$sp['ids'] ?></td>
            <td>
              <?php if (isset($blocked_ip_set[$sp['ip_address']])): ?>
                <span class="sl-badge" style="background:#e7e5e4;color:#44403c">BLOCKED</span>
              <?php elseif ($sp['ip_address'] !== $my_ip): ?>
                <form method="POST" onsubmit="return confirm('इस IP को 24 घंटे के लिए ब्लॉक करें?')">
                  <?= csrf_field() ?><input type="hidden" name="action" value="block_ip">
                  <input type="hidden" name="ip_address" value="<?= htmlspecialchars($sp['ip_address']) ?>">
                  <input type="hidden" name="hours" value="24"><input type="hidden" name="reason" value="Brute-force login attempts">
                  <button class="sl-btn sl-btn-danger sl-btn-xs" type="submit">Block 24h</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Top events -->
    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-list-ol"></i> टॉप घटनाएं (7 दिन)</span></div>
      <table class="sl-table">
        <thead><tr><th>घटना</th><th>Severity</th><th>गिनती</th></tr></thead>
        <tbody>
        <?php if (empty($top_events)): ?>
          <tr><td colspan="3" style="text-align:center;color:#a8a29e;padding:24px">अभी कोई घटना दर्ज नहीं</td></tr>
        <?php endif; ?>
        <?php foreach ($top_events as $te): ?>
          <tr>
            <td class="sl-mono"><a href="<?= htmlspecialchars(sl_url(['event' => $te['event_type'], 'page' => 1])) ?>"><?= htmlspecialchars($te['event_type']) ?></a></td>
            <td><?= sl_sev_badge($te['severity'] ?: security_event_severity($te['event_type'])) ?></td>
            <td><b><?= (int)$te['c'] ?></b></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Filters + log table -->
  <div class="sl-card">
    <div class="sl-h"><span><i class="fa-solid fa-filter"></i> लॉग खोजें और फ़िल्टर करें</span>
      <span style="font-size:11px;color:#78716c;font-weight:600"><?= number_format($total_rows) ?> रिकॉर्ड</span></div>
    <form method="GET" style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
      <input class="sl-input" style="flex:1;min-width:160px" type="text" name="q" value="<?= htmlspecialchars($f_q) ?>" placeholder="खोजें: event, IP, user, URL, details">
      <select class="sl-input" name="severity">
        <option value="">सभी Severity</option>
        <?php foreach (['critical', 'high', 'medium', 'info'] as $sv): ?>
          <option value="<?= $sv ?>" <?= $f_sev === $sv ? 'selected' : '' ?>><?= ucfirst($sv) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="sl-input" name="event">
        <option value="">सभी घटनाएं</option>
        <?php foreach ($event_types as $et): ?>
          <option value="<?= htmlspecialchars($et['event_type']) ?>" <?= $f_event === $et['event_type'] ? 'selected' : '' ?>><?= htmlspecialchars($et['event_type']) ?></option>
        <?php endforeach; ?>
      </select>
      <input class="sl-input" style="width:130px" type="text" name="ip" value="<?= htmlspecialchars($f_ip) ?>" placeholder="IP">
      <input class="sl-input" type="date" name="from" value="<?= htmlspecialchars($f_from) ?>" title="From">
      <input class="sl-input" type="date" name="to" value="<?= htmlspecialchars($f_to) ?>" title="To">
      <button class="sl-btn sl-btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> खोजें</button>
      <a class="sl-btn sl-btn-ghost" href="/admin/security_logs.php">Reset</a>
    </form>

    <div style="overflow-x:auto">
      <table class="sl-table">
        <thead><tr><th>समय</th><th>Severity</th><th>घटना</th><th>IP</th><th>यूज़र</th><th>URL</th><th>विवरण</th></tr></thead>
        <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="7" style="text-align:center;color:#a8a29e;padding:36px">कोई लॉग नहीं मिला।</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $l):
            $sev = ($l['severity'] ?? '') ?: security_event_severity($l['event_type']);
            $det = $l['details'] ?? ($l['description'] ?? '');
            $pretty = $det;
            $decoded = json_decode((string)$det, true);
            if (is_array($decoded)) { $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
            $short = mb_strimwidth(preg_replace('/\s+/', ' ', (string)$det), 0, 70, '…');
        ?>
          <tr class="<?= $sev === 'critical' ? 'sl-row-critical' : '' ?>">
            <td class="sl-mono" style="white-space:nowrap"><?= htmlspecialchars(date('d M Y', strtotime($l['created_at']))) ?><br><?= htmlspecialchars(date('h:i:s A', strtotime($l['created_at']))) ?></td>
            <td><?= sl_sev_badge($sev) ?></td>
            <td class="sl-mono" style="font-weight:700;color:#9f1239"><?= htmlspecialchars($l['event_type']) ?></td>
            <td class="sl-mono" style="white-space:nowrap">
              <a href="<?= htmlspecialchars(sl_url(['ip' => $l['ip_address'], 'page' => 1])) ?>"><?= htmlspecialchars($l['ip_address']) ?></a>
              <?php if (isset($blocked_ip_set[$l['ip_address']])): ?><br><span class="sl-badge" style="background:#e7e5e4;color:#44403c">BLOCKED</span><?php endif; ?>
            </td>
            <td><?= !empty($l['user_id']) ? htmlspecialchars(($l['user_name'] ?? '') ?: ('#' . $l['user_id'])) : '—' ?></td>
            <td class="sl-mono" style="max-width:200px;word-break:break-all"><?= htmlspecialchars(($l['request_method'] ?? '') . ' ' . ($l['request_uri'] ?? '')) ?></td>
            <td style="max-width:300px">
              <details>
                <summary style="cursor:pointer;color:#4338ca;font-size:11px"><?= htmlspecialchars($short ?: 'विवरण देखें') ?></summary>
                <div class="sl-detail"><?= htmlspecialchars((string)$pretty) ?>
<?php if (!empty($l['user_agent'])): ?>

<b>User-Agent:</b> <?= htmlspecialchars($l['user_agent']) ?>
<?php endif; ?></div>
                <?php if ($l['ip_address'] !== $my_ip && !isset($blocked_ip_set[$l['ip_address']])): ?>
                  <form method="POST" style="margin-top:6px" onsubmit="return confirm('IP ब्लॉक करें?')">
                    <?= csrf_field() ?><input type="hidden" name="action" value="block_ip">
                    <input type="hidden" name="ip_address" value="<?= htmlspecialchars($l['ip_address']) ?>">
                    <input type="hidden" name="hours" value="24"><input type="hidden" name="reason" value="Blocked from log entry #<?= (int)$l['id'] ?>">
                    <button class="sl-btn sl-btn-danger sl-btn-xs" type="submit">Block this IP (24h)</button>
                  </form>
                <?php endif; ?>
              </details>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div style="padding:14px 18px;display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        <?php if ($page > 1): ?><a class="sl-pill" href="<?= htmlspecialchars(sl_url(['page' => $page - 1])) ?>">← पिछला</a><?php endif; ?>
        <?php
          $start = max(1, $page - 2); $end = min($total_pages, $page + 2);
          for ($i = $start; $i <= $end; $i++): ?>
          <a class="sl-pill <?= $i === $page ? 'active' : '' ?>" href="<?= htmlspecialchars(sl_url(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?><a class="sl-pill" href="<?= htmlspecialchars(sl_url(['page' => $page + 1])) ?>">अगला →</a><?php endif; ?>
        <span style="font-size:11px;color:#78716c;margin-left:6px">पृष्ठ <?= $page ?> / <?= $total_pages ?></span>
      </div>
    <?php endif; ?>
  </div>

  <!-- IP block manager + retention -->
  <div class="sl-grid sl-g2">
    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-ban"></i> IP ब्लॉक सूची</span></div>
      <form method="POST" style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:8px">
        <?= csrf_field() ?><input type="hidden" name="action" value="block_ip">
        <input class="sl-input" style="width:150px" type="text" name="ip_address" placeholder="IP जैसे 1.2.3.4" required>
        <select class="sl-input" name="hours">
          <option value="1">1 घंटा</option><option value="24" selected>24 घंटे</option>
          <option value="168">7 दिन</option><option value="720">30 दिन</option><option value="0">स्थायी</option>
        </select>
        <input class="sl-input" style="flex:1;min-width:120px" type="text" name="reason" placeholder="कारण (वैकल्पिक)">
        <button class="sl-btn sl-btn-danger" type="submit">Block</button>
      </form>
      <div style="padding:0 18px 4px;font-size:11px;color:#78716c">आपका वर्तमान IP: <span class="sl-mono"><?= htmlspecialchars($my_ip) ?></span> (सुरक्षा के लिए खुद को ब्लॉक नहीं कर सकते; सुपर एडमिन सत्र हमेशा अनुमत है)</div>
      <table class="sl-table" style="margin-top:8px">
        <thead><tr><th>IP</th><th>कारण</th><th>समाप्ति</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($blocked)): ?>
          <tr><td colspan="4" style="text-align:center;color:#a8a29e;padding:22px">कोई IP ब्लॉक नहीं है</td></tr>
        <?php endif; ?>
        <?php foreach ($blocked as $b): ?>
          <tr>
            <td class="sl-mono"><?= htmlspecialchars($b['ip_address']) ?></td>
            <td><?= htmlspecialchars($b['reason'] ?: '—') ?><br><span style="font-size:10px;color:#a8a29e"><?= htmlspecialchars($b['by_name'] ?: '') ?></span></td>
            <td class="sl-mono"><?= $b['blocked_until'] ? htmlspecialchars(date('d M, h:i A', strtotime($b['blocked_until']))) : 'स्थायी' ?></td>
            <td>
              <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="unblock_ip"><input type="hidden" name="block_id" value="<?= (int)$b['id'] ?>">
                <button class="sl-btn sl-btn-ghost sl-btn-xs" type="submit">Unblock</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="sl-card">
      <div class="sl-h"><span><i class="fa-solid fa-trash-can"></i> लॉग रिटेंशन (पुराने लॉग हटाएं)</span></div>
      <form method="POST" style="padding:18px" onsubmit="return confirm('चुनी हुई अवधि से पुराने सुरक्षा लॉग स्थायी रूप से हट जाएंगे। जारी रखें?')">
        <?= csrf_field() ?><input type="hidden" name="action" value="purge_logs">
        <p style="font-size:12px;color:#57534e;margin-bottom:10px">डेटाबेस छोटा रखने के लिए पुराने सुरक्षा लॉग हटाएं। न्यूनतम 7 दिन। यह क्रिया ऑडिट ट्रेल में दर्ज होती है।</p>
        <select class="sl-input" name="days">
          <option value="30">30 दिन से पुराने</option><option value="60">60 दिन से पुराने</option>
          <option value="90" selected>90 दिन से पुराने</option><option value="180">180 दिन से पुराने</option><option value="365">1 साल से पुराने</option>
        </select>
        <button class="sl-btn sl-btn-danger" type="submit" style="margin-left:6px">पुराने लॉग हटाएं</button>
      </form>
    </div>
  </div>

</div>

<script>
(function () {
  var labels = <?= json_encode($chart_labels) ?>;
  var series = <?= json_encode($chart_series) ?>;
  var el = document.getElementById('slTrend');
  if (el && window.Chart) {
    new Chart(el, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          { label: 'Critical', data: series.critical, backgroundColor: '#dc2626' },
          { label: 'High', data: series.high, backgroundColor: '#ea580c' },
          { label: 'Medium', data: series.medium, backgroundColor: '#f59e0b' },
          { label: 'Info', data: series.info, backgroundColor: '#818cf8' }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
      }
    });
  }
  var cb = document.getElementById('autoRefresh'), timer = null;
  try { cb.checked = localStorage.getItem('ggh_sec_autorefresh') === '1'; } catch (e) {}
  function apply() {
    clearTimeout(timer);
    if (cb.checked) timer = setTimeout(function () { location.reload(); }, 30000);
    try { localStorage.setItem('ggh_sec_autorefresh', cb.checked ? '1' : '0'); } catch (e) {}
  }
  cb.addEventListener('change', apply); apply();
})();
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
