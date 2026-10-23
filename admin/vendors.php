<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle KYC Status Change
if (isset($_GET['set_kyc']) && isset($_GET['vendor_id'])) {
    $vid = intval($_GET['vendor_id']);
    $new_kyc = clean_input($_GET['set_kyc']);
    if (in_array($new_kyc, ['Verified', 'Pending', 'Rejected'])) {
        $pdo->prepare("UPDATE vendors SET kyc_status = ?, verified = ? WHERE id = ?")->execute([$new_kyc, ($new_kyc === 'Verified' ? 1 : 0), $vid]);
        set_flash('success', 'विक्रेता KYC स्थिति अपडेट की गई: ' . $new_kyc);
    }
    header('Location: /admin/vendors.php');
    exit;
}

// Handle Commission Rate Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commission'])) {
    $vid = intval($_POST['vendor_id']);
    $rate = floatval($_POST['commission_rate']);
    $pdo->prepare("UPDATE vendors SET commission_rate = ? WHERE id = ?")->execute([$rate, $vid]);
    set_flash('success', 'कमीशन दर अपडेट की गई: ' . $rate . '%');
    header('Location: /admin/vendors.php');
    exit;
}

// Fetch all vendors with product count and order count
$stmt_vendors = $pdo->query("
    SELECT v.*, 
           (SELECT COUNT(*) FROM products WHERE vendor_id = v.id) as product_count,
           (SELECT COUNT(DISTINCT order_id) FROM order_items WHERE vendor_id = v.id) as order_count
    FROM vendors v 
    ORDER BY v.id ASC
");
$vendors = $stmt_vendors->fetchAll();

$page_title = 'कारीगर व विक्रेता प्रशासन (Artisans & Vendors)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-hammer text-indigo-600"></i>
      कारीगर एवं स्वयं सहायता समूह प्रशासन (Vendors & KYC)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      हरदोई, अवध, बनारस एवं चन्नापटना के ग्रामीण कारीगरों का सत्यापन व कमीशन नियंत्रण
    </p>
  </div>

  <a href="/seller-register.php" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-shop"></i> नया विक्रेता ऑनबोर्ड करें
  </a>
</div>

<!-- Vendors Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">कारीगर / कार्यशाला</th>
          <th class="px-4 py-3.5">प्रकार (Type)</th>
          <th class="px-4 py-3.5">स्थान (Location)</th>
          <th class="px-4 py-3.5">संपर्क</th>
          <th class="px-4 py-3.5">उत्पाद व आर्डर</th>
          <th class="px-4 py-3.5">कमीशन</th>
          <th class="px-4 py-3.5">KYC सत्यापन</th>
          <th class="px-4 py-3.5 text-right">कार्य</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($vendors as $v): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <td class="px-4 py-3.5">
              <div class="font-bold text-gray-900"><?= htmlspecialchars($v['business_name'] ?? $v['name']) ?></div>
              <div class="text-[11px] text-gray-500"><?= htmlspecialchars($v['name']) ?> • स्था. <?= $v['year_established'] ?? 2015 ?></div>
            </td>

            <td class="px-4 py-3.5">
              <span class="bg-stone-100 text-stone-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                <?= htmlspecialchars($v['type']) ?>
              </span>
            </td>

            <td class="px-4 py-3.5">
              <div class="font-medium text-gray-800"><?= htmlspecialchars($v['district'] ?? 'Hardoi') ?>, <?= htmlspecialchars($v['state'] ?? 'UP') ?></div>
              <div class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($v['pincode']) ?></div>
            </td>

            <td class="px-4 py-3.5">
              <div class="text-gray-800"><?= htmlspecialchars($v['phone']) ?></div>
              <div class="text-[10px] text-gray-400"><?= htmlspecialchars($v['email']) ?></div>
            </td>

            <td class="px-4 py-3.5">
              <span class="font-bold text-gray-900"><?= $v['product_count'] ?></span> उत्पाद • 
              <span class="font-bold text-indigo-700"><?= $v['order_count'] ?></span> आर्डर
            </td>

            <td class="px-4 py-3.5 font-bold text-gray-800">
              <?= $v['commission_rate'] ?>%
            </td>

            <td class="px-4 py-3.5">
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $v['kyc_status'] === 'Verified' ? 'bg-emerald-100 text-emerald-800' : ($v['kyc_status'] === 'Pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') ?>">
                <?= htmlspecialchars($v['kyc_status']) ?>
              </span>
            </td>

            <td class="px-4 py-3.5 text-right whitespace-nowrap">
              <a href="/admin/impersonate.php?action=login_as&user_id=<?= $v['user_id'] ?>"
                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold text-[11px] rounded-lg shadow-xs transition inline-flex items-center gap-1 mr-1"
                title="इस कारीगर/विक्रेता के डैशबोर्ड में सीधे प्रवेश करें (Super Admin Clearance)">
                <i class="fa-solid fa-store text-[10px]"></i> सेलर लॉगिन
              </a>
              <?php if ($v['kyc_status'] !== 'Verified'): ?>
                <a href="/admin/vendors.php?vendor_id=<?= $v['id'] ?>&set_kyc=Verified" class="px-2.5 py-1 bg-emerald-600 text-white font-bold rounded text-[11px] hover:bg-emerald-700 transition">
                  सत्यापित करें
                </a>
              <?php else: ?>
                <a href="/admin/vendors.php?vendor_id=<?= $v['id'] ?>&set_kyc=Pending" class="px-2.5 py-1 bg-stone-100 text-stone-700 font-bold rounded text-[11px] hover:bg-stone-200 transition">
                  पुनः समीक्षा
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
