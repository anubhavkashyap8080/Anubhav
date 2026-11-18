<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Security Audit H2: Enforce CSRF protection for all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

// Handle ADD COUPON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(clean_input($_POST['code'] ?? ''));
    $title = clean_input($_POST['title'] ?? '');
    $description = clean_input($_POST['description'] ?? '');
    $discount_type = clean_input($_POST['discount_type'] ?? 'percentage');
    $discount_value = floatval($_POST['discount_value'] ?? 0);
    $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $coupon_type = clean_input($_POST['coupon_type'] ?? 'sale_discount');
    $usage_limit = intval($_POST['usage_limit'] ?? 1000);
    $expiry_date = clean_input($_POST['expiry_date'] ?? '');

    if (empty($code) || empty($title) || $discount_value <= 0) {
        set_flash('error', 'Please enter a valid coupon code, title, and discount value.');
    } else {
        try {
            $stmt_chk = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt_chk->execute([$code]);
            if ($stmt_chk->fetch()) {
                set_flash('error', "Coupon code '$code' already exists. Please choose a different code.");
            } else {
                $stmt_ins = $pdo->prepare("
                    INSERT INTO coupons (code, title, description, discount_type, discount_value, min_order_amount, max_discount, coupon_type, usage_limit, expiry_date, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt_ins->execute([
                    $code, $title, $description, $discount_type, $discount_value,
                    $min_order_amount, $max_discount, $coupon_type, $usage_limit,
                    !empty($expiry_date) ? $expiry_date : null
                ]);
                set_flash('success', "Coupon code '$code' created successfully.");
                header('Location: /admin/coupons.php');
                exit;
            }
        } catch (Exception $e) {
            set_flash('error', 'Error saving coupon. Please try again.');
        }
    }
}

// Handle Toggle Status (Active / Inactive) (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = intval($_POST['coupon_id'] ?? 0);
    $stmt_cur = $pdo->prepare("SELECT status FROM coupons WHERE id = ?");
    $stmt_cur->execute([$id]);
    $cur = $stmt_cur->fetchColumn();
    $new = ($cur === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?")->execute([$new, $id]);
    log_audit_action('coupon_toggle_status', 'coupons', 'coupon', $id, "Status changed to {$new}");
    set_flash('success', 'Coupon status updated to ' . $new . '.');
    header('Location: /admin/coupons.php');
    exit;
}

// Handle Delete Coupon (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_coupon') {
    $id = intval($_POST['delete_id'] ?? 0);
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    log_audit_action('coupon_delete', 'coupons', 'coupon', $id, 'Coupon deleted');
    set_flash('success', 'Coupon deleted successfully.');
    header('Location: /admin/coupons.php');
    exit;
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['delete_id']) || isset($_GET['toggle_status'])) {
    http_response_code(405);
    set_flash('error', 'Invalid request method. Only POST requests are allowed.');
    header('Location: /admin/coupons.php');
    exit;
}

// Fetch all coupons
$stmt_coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC");
$coupons = $stmt_coupons->fetchAll();

// Fetch recent redemptions
$stmt_usages = $pdo->query("
    SELECT cu.*, c.code, c.title as coupon_title 
    FROM coupon_usages cu 
    JOIN coupons c ON cu.coupon_id = c.id 
    ORDER BY cu.id DESC LIMIT 15
");
$usages = $stmt_usages->fetchAll();

$page_title = 'Coupons & Promo Codes — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-ticket text-amber-500"></i>
      Coupon & Promotional Codes Management
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Create and manage first-order discounts, single-use per customer coupons, and festival promotional campaigns
    </p>
  </div>

  <div class="flex items-center gap-2">
    <button type="button" onclick="document.getElementById('addCouponModal').classList.remove('hidden')"
      class="px-4 py-2.5 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-2 shadow-md">
      <i class="fa-solid fa-plus"></i> Create New Coupon Code
    </button>
  </div>
</div>

<!-- Quick Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div>
      <span class="text-xs text-gray-500 font-medium">Active Coupons</span>
      <h3 class="text-2xl font-black text-gray-900"><?= count($coupons) ?></h3>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
      <i class="fa-solid fa-gift"></i>
    </div>
    <div>
      <span class="text-xs text-gray-500 font-medium">Total Redemptions</span>
      <h3 class="text-2xl font-black text-emerald-700">
        <?= array_sum(array_column($coupons, 'used_count')) ?>
      </h3>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
      <i class="fa-solid fa-users"></i>
    </div>
    <div>
      <span class="text-xs text-gray-500 font-medium">Coupon Types</span>
      <h3 class="text-xs font-bold text-gray-800 mt-1">One-per-user • New User • Sale</h3>
    </div>
  </div>
</div>

<!-- Coupons Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-list-check text-brand-green"></i> All Configured Promotional Coupons
    </h3>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs text-gray-600">
      <thead class="bg-stone-50 border-b border-gray-200 text-stone-700 uppercase font-mono text-[10px] tracking-wider">
        <tr>
          <th class="px-4 py-3">Coupon Code</th>
          <th class="px-4 py-3">Title & Description</th>
          <th class="px-4 py-3">Coupon Type</th>
          <th class="px-4 py-3">Discount</th>
          <th class="px-4 py-3">Min Order</th>
          <th class="px-4 py-3">Usage / Limit</th>
          <th class="px-4 py-3">Expiry Date</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($coupons)): ?>
          <tr>
            <td colspan="9" class="p-8 text-center text-gray-400">No coupons found. Create a new coupon to get started.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($coupons as $c): ?>
            <tr class="hover:bg-stone-50 transition">
              <td class="px-4 py-3.5">
                <span class="font-mono font-bold text-brand-green bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg text-xs">
                  <?= htmlspecialchars($c['code']) ?>
                </span>
              </td>

              <td class="px-4 py-3.5 max-w-xs">
                <div class="font-bold text-gray-900"><?= htmlspecialchars($c['title']) ?></div>
                <div class="text-[11px] text-gray-400 truncate"><?= htmlspecialchars($c['description'] ?? '') ?></div>
              </td>

              <td class="px-4 py-3.5">
                <?php if ($c['coupon_type'] === 'new_user_only'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                    <i class="fa-solid fa-sparkles text-[9px]"></i> New User Special
                  </span>
                <?php elseif ($c['coupon_type'] === 'one_per_user'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                    <i class="fa-solid fa-user-check text-[9px]"></i> One Code Per User
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                    <i class="fa-solid fa-fire text-[9px]"></i> Sale Discount
                  </span>
                <?php endif; ?>
              </td>

              <td class="px-4 py-3.5">
                <div class="font-bold text-gray-900">
                  <?= $c['discount_type'] === 'fixed' ? '₹' . number_format($c['discount_value'], 0) . ' Flat' : number_format($c['discount_value'], 0) . '% Off' ?>
                </div>
                <?php if ($c['max_discount'] > 0): ?>
                  <div class="text-[10px] text-gray-400">Max: ₹<?= number_format($c['max_discount'], 0) ?></div>
                <?php endif; ?>
              </td>

              <td class="px-4 py-3.5 font-bold text-gray-700">
                ₹<?= number_format($c['min_order_amount'], 0) ?>
              </td>

              <td class="px-4 py-3.5">
                <span class="font-bold text-gray-900"><?= $c['used_count'] ?></span> / <?= $c['usage_limit'] ?>
              </td>

              <td class="px-4 py-3.5 text-[11px] text-gray-500 font-mono">
                <?= $c['expiry_date'] ? date('d M Y', strtotime($c['expiry_date'])) : 'Never Expires' ?>
              </td>

              <td class="px-4 py-3.5">
                <form action="/admin/coupons.php" method="POST" class="inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="coupon_id" value="<?= $c['id'] ?>">
                  <button type="submit"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer <?= $c['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $c['status'] === 'active' ? 'bg-emerald-600' : 'bg-stone-500' ?>"></span>
                    <?= ucfirst($c['status']) ?>
                  </button>
                </form>
              </td>

              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <form action="/admin/coupons.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete_coupon">
                  <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                  <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition cursor-pointer" title="Delete">
                    <i class="fa-regular fa-trash-can"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent Redemptions Log -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
  <div class="p-4 sm:p-5 border-b border-gray-100">
    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-receipt text-indigo-600"></i> Recent Coupon Redemption History
    </h3>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs text-gray-600">
      <thead class="bg-stone-50 border-b border-gray-200 text-stone-700 uppercase font-mono text-[10px] tracking-wider">
        <tr>
          <th class="px-4 py-3">Date & Time</th>
          <th class="px-4 py-3">Coupon Code</th>
          <th class="px-4 py-3">Customer Details</th>
          <th class="px-4 py-3">Discount Given</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($usages)): ?>
          <tr>
            <td colspan="4" class="p-6 text-center text-gray-400">No coupon redemptions recorded yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($usages as $u): ?>
            <tr class="hover:bg-stone-50 transition">
              <td class="px-4 py-3 font-mono text-[11px]"><?= date('d M Y, h:i A', strtotime($u['used_at'])) ?></td>
              <td class="px-4 py-3">
                <span class="font-mono font-bold text-brand-green bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-xs">
                  <?= htmlspecialchars($u['code']) ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="font-bold text-gray-900"><?= htmlspecialchars($u['customer_email'] ?? 'Guest Customer') ?></div>
                <div class="text-[10px] text-gray-400"><?= htmlspecialchars($u['customer_phone'] ?? '') ?></div>
              </td>
              <td class="px-4 py-3 font-bold text-emerald-700">
                <?= format_price($u['discount_amount']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add New Coupon -->
<div id="addCouponModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-ticket text-brand-green"></i> Create New Promotional Coupon
      </h3>
      <button onclick="document.getElementById('addCouponModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/coupons.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="add_coupon" value="1">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Coupon Code (Uppercase) <span class="text-red-500">*</span></label>
          <input type="text" name="code" required placeholder="e.g. FESTIVAL50 or WELCOME100"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Coupon Type <span class="text-red-500">*</span></label>
          <select name="coupon_type" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-brand-green">
            <option value="sale_discount">🏷️ General Sale Discount</option>
            <option value="one_per_user">👤 One Use Per User</option>
            <option value="new_user_only">🌟 New User Special (First Order Only)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Coupon Title <span class="text-red-500">*</span></label>
        <input type="text" name="title" required placeholder="e.g. Festival Mega Sale 20% Off"
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="2" placeholder="Terms and conditions for this promotional code..."
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Discount Type</label>
          <select name="discount_type" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold">
            <option value="percentage">Percentage (% Off)</option>
            <option value="fixed">Flat Amount (₹ Flat)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Discount Value <span class="text-red-500">*</span></label>
          <input type="number" step="1" name="discount_value" required placeholder="20 or 100"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Minimum Order (₹)</label>
          <input type="number" step="1" name="min_order_amount" value="499"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Maximum Discount Cap (₹)</label>
          <input type="number" step="1" name="max_discount" value="300" placeholder="0 = Unlimited"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
          <p class="text-[10px] text-gray-400 mt-0.5">Cap for percentage discount</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Total Usage Limit</label>
          <input type="number" step="1" name="usage_limit" value="1000"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Expiry Date</label>
          <input type="date" name="expiry_date" value="<?= date('Y-m-d', strtotime('+90 days')) ?>"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
        </div>
      </div>

      <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
        <button type="button" onclick="document.getElementById('addCouponModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition">
          Cancel
        </button>
        <button type="submit"
          class="px-6 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition shadow">
          Save & Activate Coupon
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
