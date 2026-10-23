<?php
$page_title = 'केंद्रीय आर्डर एवं लॉजिस्टिक्स (Central Orders)';
require_once __DIR__ . '/admin_header.php';

$status_filter = clean_input($_GET['status'] ?? '');
$q = clean_input($_GET['q'] ?? '');

// Handle Status & AWB update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin_order'])) {
    $oid = intval($_POST['order_id']);
    $new_status = clean_input($_POST['order_status']);
    $new_awb = clean_input($_POST['tracking_consignment_no']);
    $pdo->prepare("UPDATE orders SET order_status = ?, tracking_consignment_no = ? WHERE id = ?")->execute([$new_status, $new_awb, $oid]);
    set_flash('success', 'आर्डर स्थिति एवं इंडिया पोस्ट कंसाइनमेंट नंबर अपडेट किया गया!');
    header('Location: /admin/orders.php');
    exit;
}

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND order_status = ?";
    $params[] = $status_filter;
}
if (!empty($q)) {
    $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR tracking_consignment_no LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!-- Title & Filters -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-truck-fast text-indigo-600"></i>
      केंद्रीय आर्डर एवं भारतीय डाक डिस्पैच (Global Logistics)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      अखिल भारतीय पार्सल ट्रैकिंग, COD संग्रह एवं इंडिया पोस्ट स्पीड पोस्ट मॉनिटरिंग
    </p>
  </div>

  <div class="flex items-center gap-2">
    <button onclick="window.print()" class="px-3.5 py-2 bg-stone-900 text-white font-bold text-xs rounded-xl shadow hover:bg-stone-800 transition flex items-center gap-1.5">
      <i class="fa-solid fa-print"></i> डाक मैनिफ़ेस्ट प्रिंट करें
    </button>
  </div>
</div>

<!-- Filter Tabs & Search -->
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-3">
  <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
    <a href="/admin/orders.php" class="px-3 py-1.5 rounded-lg transition <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-gray-600 hover:bg-stone-200' ?>">सभी (All)</a>
    <a href="/admin/orders.php?status=Confirmed" class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Confirmed' ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-gray-600 hover:bg-stone-200' ?>">Confirmed</a>
    <a href="/admin/orders.php?status=Dispatched" class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Dispatched' ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-gray-600 hover:bg-stone-200' ?>">Dispatched</a>
    <a href="/admin/orders.php?status=Delivered" class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Delivered' ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-gray-600 hover:bg-stone-200' ?>">Delivered</a>
  </div>

  <form method="GET" action="/admin/orders.php" class="w-full sm:w-64">
    <div class="relative">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="आर्डर सं. या फोन खोजें..."
        class="w-full pl-8 pr-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:outline-none transition">
      <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400 text-xs">
        <i class="fa-solid fa-magnifying-glass"></i>
      </span>
    </div>
  </form>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">आर्डर सं.</th>
          <th class="px-4 py-3.5">ग्राहक विवरण</th>
          <th class="px-4 py-3.5">डिलीवरी पता</th>
          <th class="px-4 py-3.5">राशि व भुगतान</th>
          <th class="px-4 py-3.5">कंसाइनमेंट (AWB)</th>
          <th class="px-4 py-3.5">आर्डर स्थिति</th>
          <th class="px-4 py-3.5 text-right">कार्य</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($orders as $ord): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
              #<?= htmlspecialchars($ord['order_number']) ?>
              <span class="block text-[10px] font-normal text-gray-400"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
            </td>

            <td class="px-4 py-3.5">
              <div class="font-bold text-gray-900"><?= htmlspecialchars($ord['customer_name']) ?></div>
              <div class="text-[11px] text-gray-500">+91 <?= htmlspecialchars($ord['customer_phone']) ?></div>
            </td>

            <td class="px-4 py-3.5 text-gray-600 max-w-xs truncate">
              <?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_state']) ?> - <strong><?= htmlspecialchars($ord['shipping_pincode']) ?></strong>
            </td>

            <td class="px-4 py-3.5">
              <div class="font-bold text-gray-900"><?= format_price($ord['total_amount']) ?></div>
              <span class="font-mono text-[10px] uppercase font-bold <?= $ord['payment_status'] === 'completed' ? 'text-emerald-700' : 'text-amber-700' ?>">
                <?= htmlspecialchars($ord['payment_method']) ?> (<?= htmlspecialchars($ord['payment_status']) ?>)
              </span>
            </td>

            <td class="px-4 py-3.5 font-mono text-[11px] font-bold text-gray-800">
              <?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'लंबित') ?>
            </td>

            <td class="px-4 py-3.5">
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                <?= htmlspecialchars($ord['order_status']) ?>
              </span>
            </td>

            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
              <a href="/order-success.php?id=<?= $ord['id'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-gray-700 rounded hover:bg-stone-100 transition" title="रसीद प्रिंट">
                <i class="fa-solid fa-receipt"></i>
              </a>
              <button onclick="openOrderModal(<?= htmlspecialchars(json_encode($ord)) ?>)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded text-[11px] transition">
                अपडेट
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal to Update Order -->
<div id="adminOrderModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-sm w-full p-6">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
      <h3 class="text-sm font-bold text-gray-900">आर्डर स्थिति व कंसाइनमेंट</h3>
      <button onclick="document.getElementById('adminOrderModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/orders.php" method="POST" class="space-y-4">
      <input type="hidden" name="update_admin_order" value="1">
      <input type="hidden" name="order_id" id="modal_order_id">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">आर्डर स्थिति</label>
        <select name="order_status" id="modal_order_status" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
          <option value="Confirmed">Confirmed</option>
          <option value="Processing">Processing</option>
          <option value="Dispatched">Dispatched</option>
          <option value="Delivered">Delivered</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">भारतीय डाक कंसाइनमेंट सं. (AWB)</label>
        <input type="text" name="tracking_consignment_no" id="modal_tracking_consignment_no" required
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold">
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('adminOrderModal').classList.add('hidden')" class="px-3 py-1.5 bg-stone-100 text-stone-700 font-bold rounded-lg text-xs">रद्द करें</button>
        <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition">अपडेट करें</button>
      </div>
    </form>
  </div>
</div>

<script>
function openOrderModal(order) {
  document.getElementById('modal_order_id').value = order.id;
  document.getElementById('modal_order_status').value = order.order_status;
  document.getElementById('modal_tracking_consignment_no').value = order.tracking_consignment_no || '';
  document.getElementById('adminOrderModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
