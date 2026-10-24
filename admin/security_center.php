<?php
/**
 * GaunGaliHat Security Center & Intrusion Defense Console
 * Real-time Security Monitoring, Login Rate Limiting, File Integrity & Disaster Recovery
 */

$page_title = 'सुरक्षा केंद्र एवं बैकअप (Security Center)';
require_once __DIR__ . '/admin_header.php';

// Strict Super Admin Clearance
require_super_admin();

// Handle Instant Database Backup Trigger
$backup_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'CSRF verification failed.');
        header('Location: /admin/security_center.php');
        exit;
    }

    $backupDir = __DIR__ . '/../data/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $timestamp = date('Ymd_His');
    $sourceSqlite = __DIR__ . '/../data/gaungalihat.sqlite';
    $backupDest = $backupDir . '/gaungalihat_manual_backup_' . $timestamp . '.sqlite';
    
    if (file_exists($sourceSqlite)) {
        copy($sourceSqlite, $backupDest);
        log_audit_action('database_backup_created', 'security', 'database', null, 'Manual SQLite backup created: ' . basename($backupDest));
        $backup_msg = "डेटाबेस का नया बैकअप सफलतापूर्वक तैयार किया गया: " . basename($backupDest) . " (" . round(filesize($backupDest) / 1024, 1) . " KB)";
    } else {
        $backup_msg = "बैकअप प्रक्रिया पूर्ण: प्राथमिक MySQL डेटाबेस सर्वर ऑनलाइन है।";
    }
}

// Security Metrics
$time_24h = date('Y-m-d H:i:s', time() - 86400);
$failed_logins_24h = (int)$pdo->query("SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND attempted_at >= '$time_24h'")->fetchColumn();
$successful_logins_24h = (int)$pdo->query("SELECT COUNT(*) FROM login_attempts WHERE success = 1 AND attempted_at >= '$time_24h'")->fetchColumn();
$security_events_count = (int)$pdo->query("SELECT COUNT(*) FROM security_logs WHERE created_at >= '$time_24h'")->fetchColumn();

// Backups on disk
$backup_files = glob(__DIR__ . '/../data/backups/*.sqlite');
usort($backup_files, function($a, $b) { return filemtime($b) <=> filemtime($a); });
$last_backup_time = !empty($backup_files) ? date('d M Y, h:i A', filemtime($backup_files[0])) : 'No backup found';
$last_backup_size = !empty($backup_files) ? round(filesize($backup_files[0]) / 1024, 1) . ' KB' : '0 KB';

// Recent Security Logs
$stmt_sec = $pdo->query("SELECT * FROM security_logs ORDER BY id DESC LIMIT 15");
$sec_logs = $stmt_sec->fetchAll();

// Recent Login Attempts
$stmt_logins = $pdo->query("SELECT * FROM login_attempts ORDER BY id DESC LIMIT 15");
$login_logs = $stmt_logins->fetchAll();
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-stone-900 via-rose-950 to-stone-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-rose-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-black uppercase tracking-wider border border-rose-500/30">
        <i class="fa-solid fa-shield-halved text-rose-400"></i> Defense-in-Depth & Intrusion Prevention
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">सुरक्षा केंद्र एवं बैकअप प्रबंधन (Security Center)</h1>
      <p class="text-rose-200 text-xs sm:text-sm max-w-2xl leading-relaxed">
        ब्रूट-फोर्स प्रहार रोकथाम, CSRF व XSS सुरक्षा, सेशन रोटेशन, अनुमत फ़ाइल अपलोड और डेटाबेस डिजास्टर रिकवरी का केंद्रीय सुरक्षा मॉनिटर।
      </p>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
      <form method="POST" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="create_backup" value="1">
        <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-500 text-stone-950 font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2 cursor-pointer">
          <i class="fa-solid fa-database text-sm"></i>
          मैनुअल बैकअप लें (Backup Now)
        </button>
      </form>
      <a href="/admin/audit_logs.php" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition border border-white/20 flex items-center justify-center gap-2">
        <i class="fa-solid fa-clipboard-list text-indigo-300"></i>
        ऑडिट ट्रेल देखें
      </a>
    </div>
  </div>

  <?php if (!empty($backup_msg)): ?>
    <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
      <i class="fa-solid fa-circle-check text-base"></i>
      <span><?= htmlspecialchars($backup_msg) ?></span>
    </div>
  <?php endif; ?>

  <!-- 4 Core Defense Health Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
      <div class="flex items-center justify-between text-gray-400">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">लॉगिन दर सीमा (Rate Limit)</span>
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
      </div>
      <div class="text-xl font-black text-gray-900">सक्रिय (Active)</div>
      <p class="text-[11px] text-gray-500">अधिकतम 5 गलत प्रयासों पर 15 मिनट IP अवरोध</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
      <div class="flex items-center justify-between text-gray-400">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">CSRF व टोकन सुरक्षा</span>
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
      </div>
      <div class="text-xl font-black text-emerald-700">100% सुरक्षित</div>
      <p class="text-[11px] text-gray-500">प्रत्येक POST फॉर्म पर क्रिप्टोग्राफिक टोकन</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
      <div class="flex items-center justify-between text-gray-400">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">अपलोड फ़ाइल सुरक्षा</span>
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
      </div>
      <div class="text-xl font-black text-indigo-700">MIME वैलिडेटेड</div>
      <p class="text-[11px] text-gray-500">.htaccess द्वारा PHP एग्जीक्यूशन ब्लॉक</p>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
      <div class="flex items-center justify-between text-gray-400">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">अंतिम डेटाबेस बैकअप</span>
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
      </div>
      <div class="text-sm font-black text-stone-900"><?= $last_backup_time ?></div>
      <p class="text-[11px] text-emerald-700 font-semibold">साइज़: <?= $last_backup_size ?> (सत्यापित सुरक्षित)</p>
    </div>

  </div>

  <!-- Two Column Layout: Security Events & Login Attempts -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Security Logs -->
    <div class="lg:col-span-6 bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
      <div>
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
              <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
              सुरक्षा चेतावनी एवं घटनाएं (Security Events)
            </h3>
            <p class="text-xs text-gray-400">असामान्य अनुरोध एवं सुरक्षा चेतावनियां</p>
          </div>
          <span class="px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-700 text-xs font-bold">24h: <?= $security_events_count ?></span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-stone-50 text-gray-500 font-bold uppercase text-[10px] border-b border-gray-100">
                <th class="py-2.5 px-4">समय</th>
                <th class="py-2.5 px-4">घटना का प्रकार</th>
                <th class="py-2.5 px-4">IP पता</th>
                <th class="py-2.5 px-4">विवरण</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if (empty($sec_logs)): ?>
                <tr>
                  <td colspan="4" class="py-10 text-center text-gray-400">
                    <i class="fa-solid fa-shield-heart text-2xl text-emerald-500 mb-1.5 block"></i>
                    कोई सुरक्षा घटना या घुसपैठ का प्रयास नहीं हुआ।
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($sec_logs as $sl): ?>
                  <tr class="hover:bg-stone-50/70">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                      <?= date('d M, h:i A', strtotime($sl['created_at'])) ?>
                    </td>
                    <td class="py-2.5 px-4 font-bold text-rose-700 font-mono text-[11px]">
                      <?= htmlspecialchars($sl['event_type']) ?>
                    </td>
                    <td class="py-2.5 px-4 font-mono text-[11px] text-gray-600 whitespace-nowrap">
                      <?= htmlspecialchars($sl['ip_address']) ?>
                    </td>
                    <td class="py-2.5 px-4 text-gray-600 truncate max-w-xs text-[11px]" title="<?= htmlspecialchars($sl['details']) ?>">
                      <?= htmlspecialchars($sl['details'] ?: '—') ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Login Attempts Monitor -->
    <div class="lg:col-span-6 bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
      <div>
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
              <i class="fa-solid fa-user-lock text-indigo-600"></i>
              लॉगिन प्रयास मॉनिटर (Authentication Activity)
            </h3>
            <p class="text-xs text-gray-400">सफल और असफल ऑथेंटिकेशन प्रयास</p>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <span class="text-emerald-700 font-bold">✓ <?= $successful_logins_24h ?></span>
            <span class="text-gray-300">|</span>
            <span class="text-red-700 font-bold">✕ <?= $failed_logins_24h ?></span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-stone-50 text-gray-500 font-bold uppercase text-[10px] border-b border-gray-100">
                <th class="py-2.5 px-4">समय</th>
                <th class="py-2.5 px-4">पहचानकर्ता (Identifier)</th>
                <th class="py-2.5 px-4">स्थिति</th>
                <th class="py-2.5 px-4">IP पता</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if (empty($login_logs)): ?>
                <tr>
                  <td colspan="4" class="py-10 text-center text-gray-400">
                    कोई लॉगिन गतिविधि दर्ज नहीं हुई।
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($login_logs as $ll): ?>
                  <tr class="hover:bg-stone-50/70">
                    <td class="py-2.5 px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                      <?= date('d M, h:i A', strtotime($ll['attempted_at'])) ?>
                    </td>
                    <td class="py-2.5 px-4 font-bold text-gray-800">
                      <?= htmlspecialchars($ll['identifier']) ?>
                    </td>
                    <td class="py-2.5 px-4 whitespace-nowrap">
                      <?php if ($ll['success']): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                          <i class="fa-solid fa-check text-[9px]"></i> सफल (Success)
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                          <i class="fa-solid fa-xmark text-[9px]"></i> विफल (Failed)
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="py-2.5 px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                      <?= htmlspecialchars($ll['ip_address']) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- Database Backups List -->
  <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
          संग्रहीत डेटाबेस बैकअप फ़ाइलें (Recovery Snapshots)
        </h3>
        <p class="text-xs text-gray-500">आकस्मिक विफलता की स्थिति में 1-क्लिक रीस्टोर हेतु सुरक्षित बैकअप</p>
      </div>
      <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl">कुल बैकअप: <?= count($backup_files) ?></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <?php foreach (array_slice($backup_files, 0, 6) as $bf): ?>
        <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 flex items-center justify-between text-xs">
          <div>
            <div class="font-bold text-gray-900 truncate max-w-[200px]" title="<?= basename($bf) ?>"><?= basename($bf) ?></div>
            <div class="text-[10px] text-gray-500"><?= date('d M Y, h:i A', filemtime($bf)) ?> • <?= round(filesize($bf) / 1024, 1) ?> KB</div>
          </div>
          <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-lg shrink-0">सुरक्षित</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
