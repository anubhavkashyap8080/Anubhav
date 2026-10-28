<?php
/**
 * GaunGaliHat SEO Redirects & 404 Broken Link Governance
 * Super Admin Management Console for 301/302 Redirects and Real-time 404 Crawl Monitoring
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title = 'SEO रीडायरेक्ट्स एवं 404 एरर मॉनिटरिंग (Redirects & 404 Logs)';

// Handle Add / Update Redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_redirect'])) {
    csrf_protect();
    $id = (int)($_POST['redirect_id'] ?? 0);
    $source_path = '/' . ltrim(trim($_POST['source_path'] ?? ''), '/');
    $target_url = trim($_POST['target_url'] ?? '');
    $status_code = in_array((int)($_POST['status_code'] ?? 301), [301, 302]) ? (int)$_POST['status_code'] : 301;
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    if (empty($source_path) || empty($target_url)) {
        set_flash('error', 'स्रोत पथ (Source Path) एवं लक्ष्य URL (Target URL) अनिवार्य हैं।');
    } else {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE seo_redirects SET source_path = ?, target_url = ?, status_code = ?, status = ? WHERE id = ?");
                $stmt->execute([$source_path, $target_url, $status_code, $status, $id]);
                log_audit_action('seo_redirect_updated', 'seo', "Updated redirect #{$id} from {$source_path} to {$target_url} ({$status_code})");
                set_flash('success', 'रीडायरेक्ट सफलतापूर्वक अपडेट किया गया!');
            } else {
                $stmt = $pdo->prepare("INSERT INTO seo_redirects (source_path, target_url, status_code, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$source_path, $target_url, $status_code, $status]);
                log_audit_action('seo_redirect_created', 'seo', "Created redirect from {$source_path} to {$target_url} ({$status_code})");
                set_flash('success', 'नया रीडायरेक्ट नियम सफलतापूर्वक जोड़ा गया!');
            }
            header('Location: /admin/seo_redirects.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'डेटाबेस त्रुटि: ' . $e->getMessage());
        }
    }
}

// Handle Delete Redirect (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_redirect') {
    $del_id = (int)($_POST['redirect_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM seo_redirects WHERE id = ?");
        $stmt->execute([$del_id]);
        log_audit_action('seo_redirect_deleted', 'seo', 'redirect', $del_id, "Deleted redirect #{$del_id}");
        set_flash('success', 'रीडायरेक्ट नियम हटा दिया गया।');
        header('Location: /admin/seo_redirects.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'हटाने में त्रुटि: ' . $e->getMessage());
    }
}

// Handle Mark 404 Resolved (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_404') {
    $res_id = (int)($_POST['log_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("UPDATE seo_404_logs SET resolved = 1 WHERE id = ?");
        $stmt->execute([$res_id]);
        log_audit_action('seo_404_resolved', 'seo', '404_log', $res_id, "Resolved 404 error #{$res_id}");
        set_flash('success', '404 एरर को रिसॉल्व्ड मार्क किया गया।');
        header('Location: /admin/seo_redirects.php?tab=404');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['delete_redirect']) || isset($_GET['resolve_404'])) {
    http_response_code(405);
    set_flash('error', 'अमान्य अनुरोध विधि (GET अस्वीकृत)। कृपया फ़ॉर्म या बटन का उपयोग करें।');
    header('Location: /admin/seo_redirects.php');
    exit;
}

// Fetch Redirects
$redirects = [];
try {
    $redirects = $pdo->query("SELECT * FROM seo_redirects ORDER BY hits DESC, id DESC")->fetchAll();
} catch (Exception $e) {}

// Fetch 404 Logs
$logs_404 = [];
try {
    $logs_404 = $pdo->query("SELECT * FROM seo_404_logs ORDER BY hit_count DESC, last_occurred_at DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {}

$active_tab = $_GET['tab'] ?? 'redirects';

require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Navigation -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-route text-indigo-600"></i>
      SEO रीडायरेक्ट्स एवं 404 एरर मॉनिटरिंग
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      301 परमानेंट एवं 302 टेम्परेरी रीडायरेक्ट्स प्रबंधित करें तथा क्रॉलर्स द्वारा पाई गई 404 एरर्स को ठीक करें।
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/admin/seo_settings.php" class="px-3.5 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-magnifying-glass-chart text-indigo-600"></i> SEO सेटिंग्स
    </a>
    <a href="/sitemap.xml" target="_blank" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-sitemap"></i> Sitemap.xml
    </a>
  </div>
</div>

<!-- Tabs -->
<div class="flex items-center gap-3 border-b border-gray-200 mb-6">
  <a href="/admin/seo_redirects.php?tab=redirects"
    class="pb-3 px-1 text-sm font-bold flex items-center gap-2 border-b-2 transition <?= $active_tab === 'redirects' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-900' ?>">
    <i class="fa-solid fa-arrows-split-up-and-left"></i>
    एक्टिव रीडायरेक्ट्स (<?= count($redirects) ?>)
  </a>
  <a href="/admin/seo_redirects.php?tab=404"
    class="pb-3 px-1 text-sm font-bold flex items-center gap-2 border-b-2 transition <?= $active_tab === '404' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-900' ?>">
    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
    404 नॉट फाउंड एरर लॉग (<?= count($logs_404) ?>)
  </a>
</div>

<?php if ($active_tab === 'redirects'): ?>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add Redirect Form -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:col-span-1">
      <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
        <i class="fa-solid fa-plus-circle text-indigo-600"></i>
        नया रीडायरेक्ट जोड़ें
      </h2>

      <form action="/admin/seo_redirects.php" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="save_redirect" value="1">
        <input type="hidden" name="redirect_id" id="form_redirect_id" value="0">

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            स्रोत पथ (Source Path) <span class="text-red-500">*</span>
          </label>
          <input type="text" name="source_path" id="form_source_path" required placeholder="/old-category/item"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <p class="text-[11px] text-gray-400 mt-1">वेबसाइट का पुराना URL जिसे रीडायरेक्ट करना है।</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">
            लक्ष्य URL (Target URL / Path) <span class="text-red-500">*</span>
          </label>
          <input type="text" name="target_url" id="form_target_url" required placeholder="/product.php?id=12"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <p class="text-[11px] text-gray-400 mt-1">नया पेज जहाँ यूज़र को भेजना है।</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">HTTP स्टेटस</label>
            <select name="status_code" id="form_status_code" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600">
              <option value="301">301 (Permanent)</option>
              <option value="302">302 (Temporary)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">स्थिति (Status)</label>
            <select name="status" id="form_status" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 mt-2">
          <i class="fa-solid fa-floppy-disk"></i> नियम सहेजें (Save Rule)
        </button>
      </form>
    </div>

    <!-- Redirects Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:col-span-2 overflow-hidden">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
          <i class="fa-solid fa-list-check text-indigo-600"></i>
          रीडायरेक्ट सूची (Active SEO Rules)
        </h2>
        <span class="text-xs text-gray-500 font-medium">कुल: <?= count($redirects) ?> नियम</span>
      </div>

      <?php if (empty($redirects)): ?>
        <div class="text-center py-12 text-gray-400">
          <i class="fa-solid fa-route text-4xl mb-3 text-gray-300"></i>
          <p class="text-xs font-bold">कोई कस्टम रीडायरेक्ट नियम कॉन्फ़िगर नहीं है।</p>
          <p class="text-[11px] mt-1 text-gray-400">पुराने लिंक्स को 301 रीडायरेक्ट देने के लिए बायीं ओर से नियम जोड़ें।</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-stone-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-2.5 px-3">सोर्स पाथ</th>
                <th class="py-2.5 px-3">टारगेट URL</th>
                <th class="py-2.5 px-3 text-center">कोड</th>
                <th class="py-2.5 px-3 text-center">हिट्स</th>
                <th class="py-2.5 px-3 text-center">स्थिति</th>
                <th class="py-2.5 px-3 text-right">कार्रवाई</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php foreach ($redirects as $r): ?>
                <tr class="hover:bg-stone-50 transition">
                  <td class="py-3 px-3 font-mono font-bold text-gray-900 break-all"><?= htmlspecialchars($r['source_path']) ?></td>
                  <td class="py-3 px-3 font-mono text-indigo-600 break-all"><?= htmlspecialchars($r['target_url']) ?></td>
                  <td class="py-3 px-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $r['status_code'] == 301 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                      <?= $r['status_code'] ?>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center font-bold text-gray-700"><?= number_format($r['hits']) ?></td>
                  <td class="py-3 px-3 text-center">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $r['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                      <?= ucfirst($r['status']) ?>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <button type="button" onclick="editRedirect(<?= htmlspecialchars(json_encode($r)) ?>)" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="एडिट करें">
                        <i class="fa-solid fa-pen text-xs"></i>
                      </button>
                      <form action="/admin/seo_redirects.php" method="POST" class="inline" onsubmit="return confirm('क्या आप इस रीडायरेक्ट को हटाना चाहते हैं?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_redirect">
                        <input type="hidden" name="redirect_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="हटाएं">
                          <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <!-- 404 Error Log View -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div>
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
          404 नॉट फाउंड एरर लॉग (Real-time Crawl Failures)
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">Googlebot व यूज़र्स द्वारा हिट किए गए ब्रोकन URLs। 1-क्लिक में रीडायरेक्ट बनाएं।</p>
      </div>
      <span class="text-xs text-gray-500 font-medium">हालिया 50 घटनाएं</span>
    </div>

    <?php if (empty($logs_404)): ?>
      <div class="text-center py-12 text-emerald-600">
        <i class="fa-solid fa-circle-check text-4xl mb-3 text-emerald-500"></i>
        <p class="text-xs font-bold text-gray-900">कोई 404 एरर दर्ज नहीं है!</p>
        <p class="text-[11px] mt-1 text-gray-500">आपकी साइट के सभी लिंक्स सुचारु रूप से कार्य कर रहे हैं।</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-[11px]">
            <tr>
              <th class="py-2.5 px-3">अनुरोधित पथ (Requested URL)</th>
              <th class="py-2.5 px-3">रेफरर (Referrer)</th>
              <th class="py-2.5 px-3 text-center">हिट काउंट</th>
              <th class="py-2.5 px-3">अंतिम घटना</th>
              <th class="py-2.5 px-3 text-center">स्थिति</th>
              <th class="py-2.5 px-3 text-right">त्वरित समाधान</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($logs_404 as $log): ?>
              <tr class="hover:bg-stone-50 transition">
                <td class="py-3 px-3 font-mono font-bold text-red-600 break-all"><?= htmlspecialchars($log['request_path']) ?></td>
                <td class="py-3 px-3 text-gray-500 truncate max-w-xs"><?= htmlspecialchars($log['referrer'] ?: 'Direct / Search Engine') ?></td>
                <td class="py-3 px-3 text-center">
                  <span class="px-2 py-0.5 rounded font-black text-[11px] <?= $log['hit_count'] > 5 ? 'bg-red-100 text-red-700' : 'bg-stone-100 text-stone-700' ?>">
                    <?= $log['hit_count'] ?>
                  </span>
                </td>
                <td class="py-3 px-3 text-gray-500 text-[11px]"><?= htmlspecialchars($log['last_occurred_at'] ?? 'हाल ही में') ?></td>
                <td class="py-3 px-3 text-center">
                  <?php if (!empty($log['resolved'])): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">Resolved</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Unresolved</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button type="button" onclick="createRedirectFrom404('<?= htmlspecialchars(addslashes($log['request_path'])) ?>')"
                      class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg transition flex items-center gap-1">
                      <i class="fa-solid fa-turn-down-right"></i> रीडायरेक्ट बनाएं
                    </button>
                    <?php if (empty($log['resolved'])): ?>
                      <form action="/admin/seo_redirects.php" method="POST" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="resolve_404">
                        <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition cursor-pointer" title="रिसॉल्व्ड मार्क करें">
                          <i class="fa-solid fa-check text-xs"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<script>
function editRedirect(data) {
  document.getElementById('form_redirect_id').value = data.id;
  document.getElementById('form_source_path').value = data.source_path;
  document.getElementById('form_target_url').value = data.target_url;
  document.getElementById('form_status_code').value = data.status_code;
  document.getElementById('form_status').value = data.status;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function createRedirectFrom404(path) {
  window.location.href = '/admin/seo_redirects.php?tab=redirects';
  setTimeout(() => {
    document.getElementById('form_source_path').value = path;
    document.getElementById('form_target_url').focus();
  }, 300);
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
