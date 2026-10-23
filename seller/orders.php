<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_vendor();

$user = get_logged_in_user();
$vendor = get_vendor_for_current_user();
if (!$vendor) {
    $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id ASC LIMIT 1");
    $vendor = $stmt->fetch();
}
$vendor_id = $vendor['id'];

$status_filter = clean_input($_GET['status'] ?? '');
$view_order_id = intval($_GET['view'] ?? 0);

// Handle Status or Tracking Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = clean_input($_POST['order_status']);
    $consignment_no = clean_input($_POST['consignment_no']);

    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET order_status = ?, 
                tracking_consignment_no = ? 
            WHERE id = ?
        ");
        $stmt->execute([$new_status, $consignment_no, $order_id]);
        set_flash('success', 'आर्डर स्थिति एवं इंडिया पोस्ट कंसाइनमेंट नंबर सफलतापूर्वक अपडेट किया गया!');
        header('Location: /seller/orders.php?view=' . $order_id);
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Fetch orders containing this vendor's items
$sql = "
    SELECT DISTINCT o.*,
           (SELECT SUM(total) FROM order_items WHERE order_id = o.id AND vendor_id = ?) as vendor_subtotal,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id AND vendor_id = ?) as vendor_item_count
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.vendor_id = ?
";
$params = [$vendor_id, $vendor_id, $vendor_id];

if (!empty($status_filter)) {
    $sql .= " AND o.order_status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY o.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// If viewing a specific order
$selected_order = null;
$selected_items = [];
if ($view_order_id) {
    $stmt_sel = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt_sel->execute([$view_order_id]);
    $selected_order = $stmt_sel->fetch();

    if ($selected_order) {
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.image 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ? AND oi.vendor_id = ?
        ");
        $stmt_items->execute([$view_order_id, $vendor_id]);
        $selected_items = $stmt_items->fetchAll();
    }
}

$page_title = 'आर्डर व डिस्पैच प्रबंधन (Orders & India Post Dispatch)';
require_once __DIR__ . '/seller_header.php';
?>

<!-- Title & Status Tabs -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-2">
      <i class="fa-solid fa-truck-fast text-amber-600"></i>
      आर्डर व भारतीय डाक डिस्पैच हब (Order Logistics)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      हरदोई डाकघर से सीधा स्पीड पोस्ट बारकोड जनरेशन एवं पैकेजिंग मैनिफ़ेस्ट
    </p>
  </div>

  <!-- Status Filter Pills -->
  <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
    <a href="/seller/orders.php"
      class="px-3 py-1.5 rounded-lg transition <?= empty($status_filter) ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-stone-50' ?>">
      सभी (All)
    </a>
    <a href="/seller/orders.php?status=Confirmed"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Confirmed' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-stone-50' ?>">
      कन्फर्म (Confirmed)
    </a>
    <a href="/seller/orders.php?status=Dispatched"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Dispatched' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-stone-50' ?>">
      रास्ते में (Dispatched)
    </a>
    <a href="/seller/orders.php?status=Delivered"
      class="px-3 py-1.5 rounded-lg transition <?= $status_filter === 'Delivered' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-stone-50' ?>">
      डिलीवर (Delivered)
    </a>
  </div>
</div>

<div class="grid grid-cols-1 <?= $selected_order ? 'lg:grid-cols-12' : '' ?> gap-6 items-start">
  
  <!-- Orders List Table -->
  <div class="<?= $selected_order ? 'lg:col-span-7' : 'w-full' ?> bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
          <tr>
            <th class="px-4 py-3">आर्डर सं.</th>
            <th class="px-4 py-3">ग्राहक एवं पता</th>
            <th class="px-4 py-3">वस्तुएं</th>
            <th class="px-4 py-3">विक्रेता राशि</th>
            <th class="px-4 py-3">डाक कंसाइनमेंट (AWB)</th>
            <th class="px-4 py-3">स्थिति</th>
            <th class="px-4 py-3 text-right">कार्य</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                कोई आर्डर उपलब्ध नहीं है।
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): ?>
              <tr class="hover:bg-stone-50/50 transition <?= ($selected_order && $selected_order['id'] == $ord['id']) ? 'bg-amber-50/40' : '' ?>">
                <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M Y', strtotime($ord['created_at'])) ?></span>
                </td>
                <td class="px-4 py-3.5">
                  <div class="font-bold text-gray-900"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <div class="text-[10px] text-gray-500"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>
                <td class="px-4 py-3.5 font-medium text-gray-600">
                  <?= $ord['vendor_item_count'] ?> वस्तुएं
                </td>
                <td class="px-4 py-3.5 font-bold text-gray-900">
                  <?= format_price($ord['vendor_subtotal']) ?>
                  <span class="block text-[10px] font-normal text-gray-400 uppercase"><?= htmlspecialchars($ord['payment_method']) ?></span>
                </td>
                <td class="px-4 py-3.5 font-mono text-[11px] text-gray-700">
                  <span class="inline-flex items-center gap-1">
                    <i class="fa-solid fa-barcode text-red-600"></i>
                    <?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'लंबित') ?>
                  </span>
                </td>
                <td class="px-4 py-3.5">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                    <?= htmlspecialchars($ord['order_status']) ?>
                  </span>
                </td>
                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                  <a href="/seller/orders.php?view=<?= $ord['id'] ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>"
                    class="px-3 py-1.5 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-lg text-xs transition">
                    प्रबंधित करें
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Order Detail & Dispatch Control Panel (5 cols) -->
  <?php if ($selected_order): ?>
    <div class="lg:col-span-5 space-y-6">
      
      <!-- Management Card -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
          <div>
            <span class="text-xs text-gray-400 font-mono">आर्डर विवरण</span>
            <h3 class="text-base font-bold text-gray-900">#<?= htmlspecialchars($selected_order['order_number']) ?></h3>
          </div>
          <button onclick="window.print()" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-lg text-xs transition flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i> शिपिंग लेबल प्रिंट करें
          </button>
        </div>

        <!-- Customer Address Block -->
        <div class="bg-stone-50 p-4 rounded-xl text-xs space-y-1 mb-4 border border-gray-100">
          <p class="font-bold text-gray-900"><?= htmlspecialchars($selected_order['customer_name']) ?></p>
          <p class="text-gray-600 leading-relaxed">
            <?= htmlspecialchars($selected_order['shipping_street']) ?><br>
            <?= htmlspecialchars($selected_order['shipping_city']) ?>, <?= htmlspecialchars($selected_order['shipping_state']) ?> - <strong><?= htmlspecialchars($selected_order['shipping_pincode']) ?></strong>
          </p>
          <p class="text-gray-700 pt-1"><strong>फोन:</strong> +91 <?= htmlspecialchars($selected_order['customer_phone']) ?></p>
        </div>

        <!-- Items to Pack -->
        <div class="mb-4">
          <h4 class="text-xs font-bold text-gray-700 mb-2">पैक किए जाने वाले सामान:</h4>
          <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
            <?php foreach ($selected_items as $sit): ?>
              <div class="p-3 bg-white flex items-center justify-between text-xs">
                <div>
                  <p class="font-bold text-gray-900"><?= htmlspecialchars($sit['product_name']) ?></p>
                  <p class="text-gray-400 text-[11px]">कोड: <?= htmlspecialchars($sit['product_code']) ?> • मात्रा: <strong><?= $sit['quantity'] ?></strong></p>
                </div>
                <div class="font-bold text-gray-900">
                  <?= format_price($sit['total']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Status & AWB Dispatch Form -->
        <form action="/seller/orders.php" method="POST" class="space-y-4 pt-2 border-t border-gray-100">
          <input type="hidden" name="update_order_status" value="1">
          <input type="hidden" name="order_id" value="<?= $selected_order['id'] ?>">

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">आर्डर स्थिति बदलें (Update Status)</label>
            <select name="order_status" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-amber-500">
              <option value="Confirmed" <?= $selected_order['order_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed (आर्डर स्वीकार)</option>
              <option value="Processing" <?= $selected_order['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing (पैकिंग जारी)</option>
              <option value="Dispatched" <?= $selected_order['order_status'] === 'Dispatched' ? 'selected' : '' ?>>Dispatched (डाकघर को प्रेषित)</option>
              <option value="Delivered" <?= $selected_order['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered (सफलतापूर्वक डिलीवर)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">
              भारतीय डाक कंसाइनमेंट नंबर (India Post Speed Post AWB)
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-red-600 text-xs font-bold">
                <i class="fa-solid fa-barcode"></i>
              </span>
              <input type="text" name="consignment_no" required
                value="<?= htmlspecialchars($selected_order['tracking_consignment_no'] ?? ('EU' . rand(100000000, 999999999) . 'IN')) ?>"
                class="w-full pl-9 pr-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold focus:outline-none focus:border-amber-500">
            </div>
          </div>

          <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition shadow">
            डिस्पैच स्थिति अपडेट करें
          </button>
        </form>
      </div>

      <!-- Printable India Post Shipping Label (Visual Preview) -->
      <div class="bg-white rounded-2xl border-2 border-dashed border-gray-300 p-5 text-xs font-mono space-y-3">
        <div class="flex items-center justify-between border-b pb-2">
          <span class="font-black text-sm text-red-700">INDIA POST SPEED POST</span>
          <span class="font-bold uppercase text-[11px]">PREPAID / COD</span>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] text-gray-500">SHIP TO:</p>
            <p class="font-bold text-sm text-gray-900"><?= htmlspecialchars($selected_order['customer_name']) ?></p>
            <p><?= htmlspecialchars($selected_order['shipping_street']) ?></p>
            <p><?= htmlspecialchars($selected_order['shipping_city']) ?>, <?= htmlspecialchars($selected_order['shipping_state']) ?> - <strong><?= htmlspecialchars($selected_order['shipping_pincode']) ?></strong></p>
            <p>PH: +91 <?= htmlspecialchars($selected_order['customer_phone']) ?></p>
          </div>
          <div class="text-right">
            <div class="font-black text-xl tracking-widest text-gray-900 font-mono">
              ||||| | |||| ||||
            </div>
            <p class="font-mono text-xs font-bold"><?= htmlspecialchars($selected_order['tracking_consignment_no']) ?></p>
          </div>
        </div>
        <div class="border-t pt-2 text-[10px] text-gray-500">
          <strong>FROM:</strong> <?= htmlspecialchars($vendor['business_name'] ?? $vendor['name']) ?>, <?= htmlspecialchars($vendor['district'] ?? 'Hardoi') ?>, UP - 241126
        </div>
      </div>

    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
