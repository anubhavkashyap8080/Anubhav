<?php
/**
 * GanvGaliHat - Admin: Newsletter subscribers (list, CSV export, remove)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/video_helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect(); // before any POST handling
}

ggh_ensure_newsletter_table($pdo);

// Remove one subscriber (POST + CSRF only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$id]);
        log_audit_action('newsletter_subscriber_removed', 'marketing', 'newsletter_subscribers', $id, 'Subscriber removed');
        set_flash('success', 'Subscriber removed.');
    }
    header('Location: /admin/newsletter.php');
    exit;
}

// CSV export (read-only). Cells starting with = + - @ are neutralised against spreadsheet formula injection.
if (isset($_GET['export'])) {
    $rows = $pdo->query("SELECT email, status, created_at FROM newsletter_subscribers ORDER BY id DESC")->fetchAll();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['email', 'status', 'subscribed_at']);
    foreach ($rows as $r) {
        $cells = [];
        foreach ([$r['email'], $r['status'], $r['created_at']] as $c) {
            $c = (string)$c;
            $cells[] = (isset($c[0]) && strpos("=+-@\t\r", $c[0]) !== false) ? "'" . $c : $c;
        }
        fputcsv($out, $cells);
    }
    fclose($out);
    exit;
}

$total = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$subs = $pdo->query("SELECT id, email, status, created_at FROM newsletter_subscribers ORDER BY id DESC LIMIT 500")->fetchAll();

$page_title = 'Newsletter Subscribers';
require_once __DIR__ . '/admin_header.php';
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Newsletter Subscribers</h1>
    <p class="text-xs sm:text-sm text-gray-500 mt-1"><?= $total ?> subscriber<?= $total === 1 ? '' : 's' ?>. Collected from the newsletter section of the Video Story Home.</p>
  </div>
  <a href="/admin/newsletter.php?export=1" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow">Download CSV</a>
</div>

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
  <div style="overflow-x:auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 text-gray-500 uppercase tracking-wider">
        <tr><th class="px-4 py-3">Email</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Subscribed</th><th class="px-4 py-3 text-right">Action</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($subs as $s): ?>
          <tr>
            <td class="px-4 py-3 font-bold text-gray-800"><?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars((string)$s['status'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars((string)$s['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="px-4 py-3 text-right">
              <form method="POST" class="inline" onsubmit="return confirm('Remove this subscriber?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                <button type="submit" class="text-red-600 font-bold hover:underline">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$subs): ?>
          <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No subscribers yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if ($total > 500): ?><p class="text-xs text-gray-500 mt-3">Showing the latest 500. Use Download CSV for the full list.</p><?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
