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

// Handle Payout Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_payout'])) {
    $amount = floatval($_POST['payout_amount'] ?? 0);
    if ($amount > 500) {
        $payout_ref = 'PAY-' . rand(1000, 9999);
        $utr = 'SBIN' . date('ymd') . rand(10000, 99999);
        $comm = round($amount * 0.04, 2);
        $net = $amount - $comm;

        $stmt = $pdo->prepare("
            INSERT INTO vendor_payouts (payout_ref, vendor_id, gross_amount, commission_amount, net_amount, utr_number, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Settled to Bank')
        ");
        $stmt->execute([$payout_ref, $vendor_id, $amount, $comm, $net, $utr]);
        set_flash('success', '₹' . number_format($net, 2) . ' का बैंक सेटलमेंट सफलतापूर्वक प्रक्रियाबद्ध किया गया! बैंक UTR: ' . $utr);
        header('Location: /seller/payments.php');
        exit;
    } else {
        set_flash('error', 'न्यूनतम सेटलमेंट निकासी राशि ₹500 है।');
    }
}

// Total gross sales
$stmt_sales = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM order_items WHERE vendor_id = ?");
$stmt_sales->execute([$vendor_id]);
$gross_sales = $stmt_sales->fetchColumn() ?: 0.0;

// Total settled
$stmt_settled = $pdo->prepare("SELECT COALESCE(SUM(net_amount), 0) FROM vendor_payouts WHERE vendor_id = ? AND status = 'Settled to Bank'");
$stmt_settled->execute([$vendor_id]);
$total_settled = $stmt_settled->fetchColumn() ?: 0.0;

$commission_pct = $vendor['commission_rate'] ?? 4.0;
$total_commission = round($gross_sales * ($commission_pct / 100), 2);
$available_balance = max(0, ($gross_sales - $total_commission) - $total_settled);

// Payouts List
$stmt_payouts = $pdo->prepare("SELECT * FROM vendor_payouts WHERE vendor_id = ? ORDER BY id DESC");
$stmt_payouts->execute([$vendor_id]);
$payouts = $stmt_payouts->fetchAll();

$page_title = 'भुगतान एवं सेटलमेंट (Payments & UTR Settlements)';
require_once __DIR__ . '/seller_header.php';
?>

<!-- Title & Quick Transfer Button -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-2">
      <i class="fa-solid fa-indian-rupee-sign text-amber-600"></i>
      भुगतान इतिहास एवं UTR सेटलमेंट (Payments & Settlements)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      पारदर्शी 4% फ्लैट कमीशन • सीधे बैंक खाते एवं UPI में समय पर हस्तांतरण
    </p>
  </div>

  <button onclick="document.getElementById('payoutModal').classList.remove('hidden')"
    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-building-columns"></i> बैंक में निकासी अनुरोध करें (Request Payout)
  </button>
</div>

<!-- 4 Financial Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">कुल सकल बिक्री (Gross Sales)</div>
    <div class="text-2xl font-black text-gray-900"><?= format_price($gross_sales) ?></div>
    <p class="text-[11px] text-gray-400 mt-1">सभी डिस्पैच आर्डर का योग</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">प्लेटफ़ॉर्म शुल्क (<?= $commission_pct ?>%)</div>
    <div class="text-2xl font-black text-amber-600"><?= format_price($total_commission) ?></div>
    <p class="text-[11px] text-gray-400 mt-1">सर्वर व इंडिया पोस्ट समन्वय</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">बैंक में जमा (Settled to Bank)</div>
    <div class="text-2xl font-black text-blue-600"><?= format_price($total_settled) ?></div>
    <p class="text-[11px] text-emerald-600 mt-1 flex items-center gap-1 font-semibold">
      <i class="fa-solid fa-circle-check"></i> UTR द्वारा सत्यापित
    </p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">निकासी योग्य शेष (Available)</div>
    <div class="text-2xl font-black text-brand-green"><?= format_price($available_balance) ?></div>
    <p class="text-[11px] text-gray-400 mt-1">तत्काल ट्रांसफर हेतु तैयार</p>
  </div>

</div>

<!-- Linked Bank Account Overview -->
<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-stone-100 flex items-center justify-center text-stone-700 text-xl font-bold">
      <i class="fa-solid fa-landmark"></i>
    </div>
    <div>
      <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
        <span><?= htmlspecialchars($vendor['bank_name'] ?? 'State Bank of India') ?></span>
        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">सत्यापित खाता</span>
      </h3>
      <p class="text-xs text-gray-500 mt-0.5">
        खाता संख्या: <strong>••••<?= substr($vendor['bank_account'] ?? '38920194821', -4) ?></strong> • IFSC: <strong><?= htmlspecialchars($vendor['bank_ifsc'] ?? 'SBIN0001245') ?></strong>
      </p>
      <p class="text-xs text-gray-500 mt-0.5">
        UPI ID: <strong class="text-gray-800"><?= htmlspecialchars($vendor['upi_id'] ?? 'artisan@sbi') ?></strong>
      </p>
    </div>
  </div>

  <a href="/seller/profile.php" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition">
    बैंक / UPI विवरण बदलें
  </a>
</div>

<!-- Settlement History Table with UTR -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
  <div class="p-5 border-b border-gray-100 flex items-center justify-between">
    <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-receipt text-amber-600"></i>
      बैंक सेटलमेंट एवं UTR लेन-देन इतिहास
    </h3>
    <span class="text-xs text-gray-400"><?= count($payouts) ?> सेटलमेंट रिकॉर्ड</span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3">रेफरेंस आईडी</th>
          <th class="px-4 py-3">सकल राशि</th>
          <th class="px-4 py-3">कमीशन (4%)</th>
          <th class="px-4 py-3">बैंक में शुद्ध जमा</th>
          <th class="px-4 py-3">बैंक UTR नंबर</th>
          <th class="px-4 py-3">दिनांक</th>
          <th class="px-4 py-3 text-right">स्थिति</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($payouts)): ?>
          <tr>
            <td colspan="7" class="px-4 py-10 text-center text-gray-400">
              कोई सेटलमेंट इतिहास नहीं मिला।
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($payouts as $pay): ?>
            <tr class="hover:bg-stone-50/50">
              <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
                <?= htmlspecialchars($pay['payout_ref']) ?>
              </td>
              <td class="px-4 py-3.5 text-gray-600">
                <?= format_price($pay['gross_amount']) ?>
              </td>
              <td class="px-4 py-3.5 text-amber-600 font-medium">
                -<?= format_price($pay['commission_amount']) ?>
              </td>
              <td class="px-4 py-3.5 font-bold text-brand-green text-sm">
                <?= format_price($pay['net_amount']) ?>
              </td>
              <td class="px-4 py-3.5 font-mono text-[11px] font-bold text-gray-800">
                <span class="bg-stone-100 px-2 py-0.5 rounded border border-gray-200">
                  <?= htmlspecialchars($pay['utr_number'] ?? 'PENDING') ?>
                </span>
              </td>
              <td class="px-4 py-3.5 text-gray-500 text-[11px]">
                <?= date('d M Y, h:i A', strtotime($pay['settled_at'])) ?>
              </td>
              <td class="px-4 py-3.5 text-right">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                  <?= htmlspecialchars($pay['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Request Payout Modal -->
<div id="payoutModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-md w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-building-columns text-emerald-600"></i>
        बैंक में निकासी अनुरोध (Request Payout)
      </h3>
      <button onclick="document.getElementById('payoutModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/seller/payments.php" method="POST" class="space-y-4">
      <input type="hidden" name="request_payout" value="1">

      <div class="bg-stone-50 p-4 rounded-xl text-xs space-y-1">
        <div class="flex justify-between text-gray-600">
          <span>उपलब्ध शेष:</span>
          <span class="font-bold text-gray-900"><?= format_price($available_balance) ?></span>
        </div>
        <div class="flex justify-between text-gray-600">
          <span>बैंक खाता:</span>
          <span class="font-bold text-gray-900"><?= htmlspecialchars($vendor['bank_name'] ?? 'State Bank of India') ?></span>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">निकासी राशि (₹) <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" name="payout_amount" min="500" max="<?= max(500, $available_balance) ?>"
          value="<?= max(500, min(10000, $available_balance)) ?>" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold focus:bg-white focus:outline-none focus:border-emerald-500 transition">
        <p class="text-[11px] text-gray-400 mt-1">न्यूनतम निकासी: ₹500 • 4% प्लेटफ़ॉर्म शुल्क कटौती होगी।</p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3">
        <button type="button" onclick="document.getElementById('payoutModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition">
          ट्रांसफर की पुष्टि करें
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
