<?php
/**
 * GaunGaliHat Administrative Audit Trail Console
 * Immutable record of all administrative, financial, and security actions
 */

$page_title = 'प्रशासनिक ऑडिट लॉग्स (Audit Trail)';
require_once __DIR__ . '/admin_header.php';

// Strict Super Admin Clearance
require_super_admin();

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=GaunGaliHat_Audit_Logs_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'User ID', 'User Name', 'Role', 'Action', 'Module', 'Entity Type', 'Entity ID', 'Details', 'IP Address', 'Timestamp']);
    
    $stmt_exp = $pdo->query("SELECT * FROM admin_audit_logs ORDER BY id DESC LIMIT 5000");
    while ($r = $stmt_exp->fetch()) {
        fputcsv($out, [
            $r['id'],
            $r['user_id'],
            $r['user_name'],
            $r['user_role'],
            $r['action'],
            $r['module'],
            $r['entity_type'],
            $r['entity_id'],
            $r['details'],
            $r['ip_address'],
            $r['created_at']
        ]);
    }
    fclose($out);
    exit;
}

// Filtering & Search
$search = trim($_GET['q'] ?? '');
$filter_module = trim($_GET['module'] ?? '');

$sql = "SELECT * FROM admin_audit_logs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (user_name LIKE ? OR action LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if (!empty($filter_module)) {
    $sql .= " AND module = ?";
    $params[] = $filter_module;
}

$sql .= " ORDER BY id DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get module list for filter
$modules = $pdo->query("SELECT DISTINCT module FROM admin_audit_logs WHERE module IS NOT NULL AND module != ''")->fetchAll(PDO::FETCH_COLUMN) ?: [];
$total_count = (int)$pdo->query("SELECT COUNT(*) FROM admin_audit_logs")->fetchColumn();
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-stone-900 via-indigo-950 to-stone-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black uppercase tracking-wider border border-indigo-500/30">
        <i class="fa-solid fa-clipboard-check"></i> Accountability & Governance Layer
      </div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">प्रशासनिक ऑडिट ट्रेल (Admin Audit Logs)</h1>
      <p class="text-indigo-200 text-xs sm:text-sm max-w-2xl leading-relaxed">
        सभी स्टाफ सदस्यों, सुपर एडमिन और विक्रेताओं द्वारा किए गए संवेदनशील कार्यों, लॉगिन, कूपन बदलाव, उत्पाद मॉडरेशन और बैंकिंग पे-आउट का अपरिवर्तनीय डिजिटल रिकॉर्ड।
      </p>
    </div>

    <div class="flex items-center gap-3">
      <a href="/admin/audit_logs.php?export=csv" class="px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-500 text-stone-950 font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2">
        <i class="fa-solid fa-file-csv text-sm"></i>
        CSV ऑडिट निर्यात करें
      </a>
      <a href="/admin/security_center.php" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition border border-white/20 flex items-center justify-center gap-2">
        <i class="fa-solid fa-shield-halved text-emerald-400"></i>
        सुरक्षा केंद्र
      </a>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div class="bg-white p-4 sm:p-5 rounded-3xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
    <form method="GET" class="flex-1 w-full flex flex-col sm:flex-row items-center gap-2.5">
      <div class="relative flex-1 w-full">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="स्टाफ नाम, एक्शन, विवरण या IP द्वारा खोजें..." 
          class="w-full pl-9 pr-4 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <select name="module" class="w-full sm:w-48 px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">सभी मॉड्यूल (All Modules)</option>
        <?php foreach ($modules as $m): ?>
          <option value="<?= htmlspecialchars($m) ?>" <?= $filter_module === $m ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($m)) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
        <span>फ़िल्टर करें</span>
      </button>

      <?php if (!empty($search) || !empty($filter_module)): ?>
        <a href="/admin/audit_logs.php" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs rounded-xl transition">रीसेट</a>
      <?php endif; ?>
    </form>

    <div class="text-xs font-bold text-gray-500 shrink-0">
      कुल रिकॉर्ड्स: <strong class="text-indigo-900"><?= number_format($total_count) ?></strong>
    </div>
  </div>

  <!-- Logs Table -->
  <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-50 text-gray-600 font-black uppercase text-[10px] tracking-wider border-b border-gray-200">
            <th class="py-3.5 px-4">समय (Timestamp)</th>
            <th class="py-3.5 px-4">स्टाफ / ऑपरेटर</th>
            <th class="py-3.5 px-4">भूमिका (Role)</th>
            <th class="py-3.5 px-4">मॉड्यूल</th>
            <th class="py-3.5 px-4">गतिविधि (Action)</th>
            <th class="py-3.5 px-4">विवरण (Details)</th>
            <th class="py-3.5 px-4">IP पता</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="7" class="py-12 text-center text-gray-400">
                <i class="fa-solid fa-clipboard-question text-3xl mb-2 text-gray-300 block"></i>
                कोई ऑडिट लॉग प्रविष्टि नहीं मिली।
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $l): ?>
              <tr class="hover:bg-stone-50/70 transition">
                <td class="py-3 px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                  <?= date('d M Y, h:i:s A', strtotime($l['created_at'])) ?>
                </td>
                <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                  <?= htmlspecialchars($l['user_name']) ?>
                  <?php if (!empty($l['user_id'])): ?>
                    <span class="block text-[10px] text-gray-400 font-normal">ID: #<?= $l['user_id'] ?></span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $l['user_role'] === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-stone-100 text-stone-700' ?>">
                    <?= htmlspecialchars($l['user_role']) ?>
                  </span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <?= htmlspecialchars($l['module']) ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-bold text-stone-900 font-mono text-[11px]">
                  <?= htmlspecialchars($l['action']) ?>
                </td>
                <td class="py-3 px-4 max-w-xs truncate text-[11px] text-gray-600" title="<?= htmlspecialchars($l['details']) ?>">
                  <?= htmlspecialchars($l['details'] ?: '—') ?>
                </td>
                <td class="py-3 px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                  <?= htmlspecialchars($l['ip_address'] ?: '127.0.0.1') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
