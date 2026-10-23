<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

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
        set_flash('error', 'कृपया कूपन कोड, शीर्षक एवं वैध डिस्काउंट वैल्यू दर्ज करें।');
    } else {
        try {
            $stmt_chk = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt_chk->execute([$code]);
            if ($stmt_chk->fetch()) {
                set_flash('error', "कूपन कोड '$code' पहले से ही मौजूद है। कृपया दूसरा कोड चुनें।");
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
                set_flash('success', "नया कूपन कोड '$code' सफलतापूर्वक बना दिया गया है!");
                header('Location: /admin/coupons.php');
                exit;
            }
        } catch (Exception $e) {
            set_flash('error', 'त्रुटि: ' . $e->getMessage());
        }
    }
}

// Handle Toggle Status (Active / Inactive)
if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $stmt_cur = $pdo->prepare("SELECT status FROM coupons WHERE id = ?");
    $stmt_cur->execute([$id]);
    $cur = $stmt_cur->fetchColumn();
    $new = ($cur === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?")->execute([$new, $id]);
    set_flash('success', 'कूपन स्थिति अपडेट की गई: ' . $new);
    header('Location: /admin/coupons.php');
    exit;
}

// Handle Delete Coupon
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    set_flash('success', 'कूपन सफलतापूर्वक हटा दिया गया।');
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

$page_title = 'कूपन एवं प्रोमो कोड प्रबंधन (Coupons & Promo Codes)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-ticket text-amber-500"></i>
      कूपन एवं प्रोमो कोड प्रबंधन (Coupon Codes)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      न्यू यूजर स्पेशल, वन कोड पर यूजर, और त्योहार व सेल डिस्काउंट कूपन बनाएं व नियंत्रित करें
    </p>
  </div>

  <div class="flex items-center gap-2">
    <button type="button" onclick="document.getElementById('addCouponModal').classList.remove('hidden')"
      class="px-4 py-2.5 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-2 shadow-md">
      <i class="fa-solid fa-plus"></i> नया कूपन कोड बनाएं (Create Coupon)
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
      <span class="text-xs text-gray-500 font-medium">कुल सक्रिय कूपन</span>
      <h3 class="text-2xl font-black text-gray-900"><?= count($coupons) ?></h3>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
      <i class="fa-solid fa-gift"></i>
    </div>
    <div>
      <span class="text-xs text-gray-500 font-medium">कुल रिडेम्प्शन (उपयोग)</span>
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
      <span class="text-xs text-gray-500 font-medium">कूपन के प्रकार</span>
      <h3 class="text-xs font-bold text-gray-800 mt-1">One-per-user • New User • Sale</h3>
    </div>
  </div>
</div>

<!-- Coupons Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-list-check text-brand-green"></i> सभी कूपन कोड्स की सूची
    </h3>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs text-gray-600">
      <thead class="bg-stone-50 border-b border-gray-200 text-stone-700 uppercase font-mono text-[10px] tracking-wider">
        <tr>
          <th class="px-4 py-3">कूपन कोड</th>
          <th class="px-4 py-3">शीर्षक एवं विवरण</th>
          <th class="px-4 py-3">कूपन प्रकार</th>
          <th class="px-4 py-3">छूट (Discount)</th>
          <th class="px-4 py-3">न्यूनतम आर्डर</th>
          <th class="px-4 py-3">उपयोग / सीमा</th>
          <th class="px-4 py-3">वैधता (Expiry)</th>
          <th class="px-4 py-3">स्थिति</th>
          <th class="px-4 py-3 text-right">कार्रवाई</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($coupons)): ?>
          <tr>
            <td colspan="9" class="p-8 text-center text-gray-400">कोई कूपन नहीं मिला। नया कूपन बनाएं।</td>
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
                  <div class="text-[10px] text-gray-400">अधिकतम: ₹<?= number_format($c['max_discount'], 0) ?></div>
                <?php endif; ?>
              </td>

              <td class="px-4 py-3.5 font-bold text-gray-700">
                ₹<?= number_format($c['min_order_amount'], 0) ?>
              </td>

              <td class="px-4 py-3.5">
                <span class="font-bold text-gray-900"><?= $c['used_count'] ?></span> / <?= $c['usage_limit'] ?>
              </td>

              <td class="px-4 py-3.5 text-[11px] text-gray-500 font-mono">
                <?= $c['expiry_date'] ? date('d M Y', strtotime($c['expiry_date'])) : 'हमेशा मान्य' ?>
              </td>

              <td class="px-4 py-3.5">
                <a href="/admin/coupons.php?toggle_status=<?= $c['id'] ?>"
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $c['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                  <span class="w-1.5 h-1.5 rounded-full <?= $c['status'] === 'active' ? 'bg-emerald-600' : 'bg-stone-500' ?>"></span>
                  <?= ucfirst($c['status']) ?>
                </a>
              </td>

              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <a href="/admin/coupons.php?delete_id=<?= $c['id'] ?>" onclick="return confirm('क्या आप वाकई इस कूपन को हटाना चाहते हैं?')"
                  class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="डिलीट करें">
                  <i class="fa-regular fa-trash-can"></i>
                </a>
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
      <i class="fa-solid fa-receipt text-indigo-600"></i> हालिया कूपन उपयोग इतिहास (Recent Coupon Usages)
    </h3>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs text-gray-600">
      <thead class="bg-stone-50 border-b border-gray-200 text-stone-700 uppercase font-mono text-[10px] tracking-wider">
        <tr>
          <th class="px-4 py-3">तारीख व समय</th>
          <th class="px-4 py-3">कूपन कोड</th>
          <th class="px-4 py-3">ग्राहक विवरण</th>
          <th class="px-4 py-3">छूट राशि</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($usages)): ?>
          <tr>
            <td colspan="4" class="p-6 text-center text-gray-400">अभी तक कोई रिडेम्प्शन दर्ज नहीं हुआ।</td>
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
                <div class="font-bold text-gray-900"><?= htmlspecialchars($u['customer_email'] ?? 'अतिथि ग्राहक') ?></div>
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
        <i class="fa-solid fa-ticket text-brand-green"></i> नया कूपन कोड बनाएं (Create Coupon)
      </h3>
      <button onclick="document.getElementById('addCouponModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/coupons.php" method="POST" class="space-y-4">
      <input type="hidden" name="add_coupon" value="1">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कूपन कोड (Uppercase) <span class="text-red-500">*</span></label>
          <input type="text" name="code" required placeholder="उदा. DIWALI50 या WELCOME100"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कूपन प्रकार (Type) <span class="text-red-500">*</span></label>
          <select name="coupon_type" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-brand-green">
            <option value="sale_discount">🏷️ सेल डिस्काउंट (General Sale Discount)</option>
            <option value="one_per_user">👤 वन कोड पर यूजर (One Code Per User)</option>
            <option value="new_user_only">🌟 न्यू यूजर स्पेशल (First Order Only)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">कूपन शीर्षक (Title) <span class="text-red-500">*</span></label>
        <input type="text" name="title" required placeholder="उदा. दीपावली महाधमाका सेल 20% छूट"
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">विवरण (Description)</label>
        <textarea name="description" rows="2" placeholder="कूपन के नियम व शर्तें लिखें..."
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green"></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">छूट प्रकार (Type)</label>
          <select name="discount_type" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold">
            <option value="percentage">प्रतिशत (% Off)</option>
            <option value="fixed">फ्लैट नकद (₹ Flat)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">छूट मान (Value) <span class="text-red-500">*</span></label>
          <input type="number" step="1" name="discount_value" required placeholder="20 या 100"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">न्यूनतम आर्डर (₹)</label>
          <input type="number" step="1" name="min_order_amount" value="499"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">अधिकतम छूट सीमा (₹)</label>
          <input type="number" step="1" name="max_discount" value="300" placeholder="0 = असीमित"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
          <p class="text-[10px] text-gray-400 mt-0.5">% छूट पर कैप</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कुल उपयोग सीमा</label>
          <input type="number" step="1" name="usage_limit" value="1000"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-brand-green">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">अंतिम तिथि (Expiry)</label>
          <input type="date" name="expiry_date" value="<?= date('Y-m-d', strtotime('+90 days')) ?>"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green">
        </div>
      </div>

      <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
        <button type="button" onclick="document.getElementById('addCouponModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition">
          रद्द करें
        </button>
        <button type="submit"
          class="px-6 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition shadow">
          कूपन सक्रिय करें (Save & Activate)
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
