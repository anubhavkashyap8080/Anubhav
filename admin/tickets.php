<?php
/**
 * GaunGaliHat Support Ticket & Helpdesk Management
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Reply to Ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_ticket'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $reply_msg = trim($_POST['message'] ?? '');
    $new_status = clean_input($_POST['status'] ?? 'In Progress');

    if (!empty($reply_msg)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ticket_replies (ticket_id, user_id, sender_name, sender_role, message)
                VALUES (?, ?, ?, 'admin', ?)
            ");
            $stmt->execute([$ticket_id, $admin_user['id'], $admin_user['name'], $reply_msg]);

            // Update ticket status
            $pdo->prepare("UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$new_status, $ticket_id]);
            set_flash('success', 'सपोर्ट टिकट पर उत्तर सफलतापूर्वक भेजा गया!');
            header('Location: /admin/tickets.php?view=' . $ticket_id);
            exit;
        } catch (Exception $e) {
            set_flash('error', 'त्रुटि: ' . $e->getMessage());
        }
    }
}

// Handle Status Change via Quick POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_ticket_status') {
    $tid = intval($_POST['ticket_id'] ?? 0);
    $st = clean_input($_POST['status'] ?? '');
    if (in_array($st, ['Open', 'In Progress', 'Resolved', 'Closed'], true)) {
        $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?")->execute([$st, $tid]);
        log_audit_action('ticket_set_status', 'tickets', 'ticket', $tid, "Ticket status set to {$st}");
        set_flash('success', 'टिकट स्थिति अपडेट की गई: ' . $st);
    }
    header('Location: /admin/tickets.php');
    exit;
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['set_status'])) {
    http_response_code(405);
    set_flash('error', 'अमान्य अनुरोध विधि (GET अस्वीकृत)। कृपया फ़ॉर्म या बटन का उपयोग करें।');
    header('Location: /admin/tickets.php');
    exit;
}

$status_filter = clean_input($_GET['status'] ?? '');
$view_ticket_id = intval($_GET['view'] ?? 0);

$sql = "SELECT * FROM tickets WHERE 1=1";
$params = [];
if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// If viewing a specific ticket
$active_ticket = null;
$active_replies = [];
if ($view_ticket_id > 0) {
    $stmt_t = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt_t->execute([$view_ticket_id]);
    $active_ticket = $stmt_t->fetch();

    if ($active_ticket) {
        $stmt_r = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
        $stmt_r->execute([$view_ticket_id]);
        $active_replies = $stmt_r->fetchAll();
    }
}

$page_title = 'सपोर्ट टिकट्स एवं ग्राहक सहायता (Helpdesk Tickets)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Status Filters -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-headset text-indigo-600"></i>
      सपोर्ट टिकट्स एवं हेल्पडेस्क प्रशासन (Helpdesk & Grievance Control)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      ग्राहक आर्डर शिकायतें, कारीगर भुगतान प्रश्न एवं भारतीय डाक डिलीवरी ट्रैकिंग सहायता
    </p>
  </div>

  <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
    <a href="/admin/tickets.php"
      class="px-3 py-1.5 rounded-lg transition <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-stone-100 border border-gray-200' ?>">
      सभी (<?= count($tickets) ?>)
    </a>
    <a href="/admin/tickets.php?status=Open"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Open' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-stone-100 border border-gray-200' ?>">
      खुले टिकट्स (Open)
    </a>
    <a href="/admin/tickets.php?status=In+Progress"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'In Progress' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-stone-100 border border-gray-200' ?>">
      प्रगति पर (In Progress)
    </a>
    <a href="/admin/tickets.php?status=Resolved"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Resolved' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-stone-100 border border-gray-200' ?>">
      हल किए गए (Resolved)
    </a>
  </div>
</div>

<div class="grid grid-cols-1 <?= $active_ticket ? 'lg:grid-cols-12' : '' ?> gap-8 items-start">
  
  <!-- Tickets Table -->
  <div class="<?= $active_ticket ? 'lg:col-span-7' : 'w-full' ?> bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
          <tr>
            <th class="px-4 py-3.5">टिकट ID</th>
            <th class="px-4 py-3.5">यूजर व विषय</th>
            <th class="px-4 py-3.5">श्रेणी</th>
            <th class="px-4 py-3.5">प्राथमिकता</th>
            <th class="px-4 py-3.5">स्थिति</th>
            <th class="px-4 py-3.5">दिनांक</th>
            <th class="px-4 py-3.5 text-right">कार्य</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($tickets as $t): ?>
            <tr class="hover:bg-stone-50 transition <?= ($view_ticket_id === $t['id']) ? 'bg-indigo-50/60' : '' ?>">
              <td class="px-4 py-3.5 font-mono font-bold text-[11px] text-indigo-700">
                <?= htmlspecialchars($t['ticket_number']) ?>
              </td>

              <td class="px-4 py-3.5 max-w-xs">
                <div class="font-bold text-gray-900"><?= htmlspecialchars($t['subject']) ?></div>
                <div class="text-[11px] text-gray-500"><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['role']) ?>)</div>
              </td>

              <td class="px-4 py-3.5">
                <span class="bg-stone-100 text-stone-700 font-semibold px-2 py-0.5 rounded text-[10px]">
                  <?= htmlspecialchars($t['category']) ?>
                </span>
              </td>

              <td class="px-4 py-3.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $t['priority'] === 'Urgent' ? 'bg-red-100 text-red-800' : ($t['priority'] === 'High' ? 'bg-orange-100 text-orange-800' : 'bg-stone-100 text-stone-700') ?>">
                  <?= htmlspecialchars($t['priority']) ?>
                </span>
              </td>

              <td class="px-4 py-3.5">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $t['status'] === 'Resolved' ? 'bg-emerald-100 text-emerald-800' : ($t['status'] === 'Open' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                  <?= htmlspecialchars($t['status']) ?>
                </span>
              </td>

              <td class="px-4 py-3.5 text-gray-400 text-[11px]">
                <?= date('d M Y', strtotime($t['created_at'])) ?>
              </td>

              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <a href="/admin/tickets.php?view=<?= $t['id'] ?>"
                  class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg border border-indigo-200 transition inline-flex items-center gap-1">
                  <i class="fa-regular fa-comment-dots"></i> जवाब दें
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Ticket Conversation & Reply Drawer (if viewing) -->
  <?php if ($active_ticket): ?>
    <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 shadow-lg p-6 space-y-6">
      
      <!-- Drawer Header -->
      <div class="flex items-start justify-between pb-4 border-b border-gray-100">
        <div>
          <span class="font-mono text-xs font-bold text-indigo-600 block"><?= htmlspecialchars($active_ticket['ticket_number']) ?></span>
          <h3 class="text-base font-bold text-gray-900 mt-1"><?= htmlspecialchars($active_ticket['subject']) ?></h3>
          <p class="text-xs text-gray-500 mt-0.5">
            <?= htmlspecialchars($active_ticket['name']) ?> • <?= htmlspecialchars($active_ticket['email']) ?>
          </p>
        </div>
        <a href="/admin/tickets.php" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg">
          <i class="fa-solid fa-xmark"></i>
        </a>
      </div>

      <!-- Initial Message -->
      <div class="p-4 rounded-2xl bg-stone-50 border border-gray-200 text-xs space-y-2">
        <div class="flex items-center justify-between text-gray-400 text-[11px]">
          <span class="font-bold text-gray-700"><?= htmlspecialchars($active_ticket['name']) ?> (प्रारंभिक संदेश)</span>
          <span><?= date('d M Y, h:i A', strtotime($active_ticket['created_at'])) ?></span>
        </div>
        <p class="text-gray-800 leading-relaxed"><?= nl2br(htmlspecialchars($active_ticket['message'])) ?></p>
      </div>

      <!-- Replies Thread -->
      <?php if (!empty($active_replies)): ?>
        <div class="space-y-3">
          <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">प्रतिक्रियाएं (Responses)</h4>
          <?php foreach ($active_replies as $rep): ?>
            <div class="p-3.5 rounded-xl border text-xs space-y-1.5 <?= $rep['sender_role'] === 'admin' ? 'bg-indigo-50 border-indigo-200 text-indigo-950 ml-4' : 'bg-stone-50 border-gray-200 mr-4' ?>">
              <div class="flex items-center justify-between text-[11px]">
                <strong class="font-bold <?= $rep['sender_role'] === 'admin' ? 'text-indigo-700' : 'text-gray-700' ?>">
                  <?= htmlspecialchars($rep['sender_name']) ?> <?= $rep['sender_role'] === 'admin' ? '(स्टाफ सपोर्ट)' : '' ?>
                </strong>
                <span class="text-gray-400"><?= date('d M, h:i A', strtotime($rep['created_at'])) ?></span>
              </div>
              <p class="leading-relaxed"><?= nl2br(htmlspecialchars($rep['message'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Reply Box -->
      <form action="/admin/tickets.php" method="POST" class="space-y-4 pt-4 border-t border-gray-100">
        <?= csrf_field() ?>
        <input type="hidden" name="reply_ticket" value="1">
        <input type="hidden" name="ticket_id" value="<?= $active_ticket['id'] ?>">

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">आधिकारिक प्रतिक्रिया लिखें (Official Response)</label>
          <textarea name="message" rows="4" required placeholder="नमस्ते, आपके आर्डर की स्थिति..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition"></textarea>
        </div>

        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-gray-700">स्थिति बदलें:</label>
            <select name="status" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
              <option value="In Progress" <?= $active_ticket['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress (प्रगति पर)</option>
              <option value="Resolved" <?= $active_ticket['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved (समस्या हल हुई)</option>
              <option value="Closed" <?= $active_ticket['status'] === 'Closed' ? 'selected' : '' ?>>Closed (बंद करें)</option>
            </select>
          </div>

          <button type="submit"
            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-paper-plane"></i> उत्तर भेजें
          </button>
        </div>
      </form>

    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
