<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Super Admin Edit Vendor Details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_vendor') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        set_flash('error', 'Security token expired. Please refresh the page and try again.');
        header('Location: /admin/vendors.php');
        exit;
    }

    $vid = intval($_POST['vendor_id'] ?? 0);
    $business_name = clean_input($_POST['business_name'] ?? '');
    $name = clean_input($_POST['name'] ?? '');
    $type = clean_input($_POST['type'] ?? 'Individual Artisan');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $village = clean_input($_POST['village'] ?? '');
    $district = clean_input($_POST['district'] ?? '');
    $state = clean_input($_POST['state'] ?? 'Uttar Pradesh');
    $pincode = clean_input($_POST['pincode'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $commission_rate = floatval($_POST['commission_rate'] ?? 4.0);
    $kyc_status = clean_input($_POST['kyc_status'] ?? 'Pending');
    $verified = ($kyc_status === 'Verified') ? 1 : 0;
    $gstin = strtoupper(clean_input($_POST['gstin'] ?? ''));
    $pan_number = strtoupper(clean_input($_POST['pan_number'] ?? ''));

    if ($vid > 0 && !empty($business_name)) {
        try {
            $stmt_up = $pdo->prepare("
                UPDATE vendors SET 
                    business_name = ?, 
                    name = ?, 
                    type = ?, 
                    phone = ?, 
                    email = ?, 
                    village = ?, 
                    district = ?, 
                    state = ?, 
                    pincode = ?, 
                    address = ?, 
                    commission_rate = ?, 
                    kyc_status = ?, 
                    verified = ?,
                    gstin = ?,
                    pan_number = ?
                WHERE id = ?
            ");
            $stmt_up->execute([
                $business_name, $name, $type, $phone, $email, $village, $district, $state,
                $pincode, $address, $commission_rate, $kyc_status, $verified, $gstin, $pan_number, $vid
            ]);

            // Sync with linked user record
            $stmt_v = $pdo->prepare("SELECT user_id FROM vendors WHERE id = ?");
            $stmt_v->execute([$vid]);
            $v_row = $stmt_v->fetch();
            if ($v_row && !empty($v_row['user_id'])) {
                $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ?, city = ?, pincode = ?, address = ?, business_name = ?, gstin = ? WHERE id = ?")
                    ->execute([$name, $phone, $email, $district, $pincode, $address, $business_name, $gstin, $v_row['user_id']]);
            }

            log_audit_action('admin_vendor_updated', 'vendor', 'vendors', $vid, "Updated vendor '{$business_name}' details");
            set_flash('success', "Vendor '{$business_name}' details updated successfully.");
        } catch (Exception $e) {
            set_flash('error', 'Error updating vendor: ' . $e->getMessage());
        }
    } else {
        set_flash('error', 'Please provide a valid vendor ID and business/workshop name.');
    }
    header('Location: /admin/vendors.php');
    exit;
}

// Handle KYC Status Change via quick link
if (isset($_GET['set_kyc']) && isset($_GET['vendor_id'])) {
    $vid = intval($_GET['vendor_id']);
    $new_kyc = clean_input($_GET['set_kyc']);
    if (in_array($new_kyc, ['Verified', 'Pending', 'Rejected'], true)) {
        $pdo->prepare("UPDATE vendors SET kyc_status = ?, verified = ? WHERE id = ?")
            ->execute([$new_kyc, ($new_kyc === 'Verified' ? 1 : 0), $vid]);
        set_flash('success', 'Vendor KYC status updated to: ' . $new_kyc);
    }
    header('Location: /admin/vendors.php');
    exit;
}

// Handle Commission Rate Quick Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commission'])) {
    $vid = intval($_POST['vendor_id']);
    $rate = floatval($_POST['commission_rate']);
    $pdo->prepare("UPDATE vendors SET commission_rate = ? WHERE id = ?")->execute([$rate, $vid]);
    set_flash('success', 'Commission rate updated to ' . $rate . '%');
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

$page_title = 'Artisans & Vendors Administration';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-hammer text-indigo-600"></i>
      Artisans, Producers & Vendors Management
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Super Admin verification, location details, commission controls & KYC management for grassroots rural sellers
    </p>
  </div>

  <div class="flex items-center gap-2">
    <span class="px-3 py-1.5 bg-stone-200 text-stone-700 font-bold text-xs rounded-xl">
      <?= count($vendors) ?> Registered Vendors
    </span>
    <a href="/seller-register.php" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> Onboard New Seller
    </a>
  </div>
</div>

<!-- Vendors Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">Artisan / Workshop</th>
          <th class="px-4 py-3.5">Type</th>
          <th class="px-4 py-3.5">Location</th>
          <th class="px-4 py-3.5">Contact</th>
          <th class="px-4 py-3.5">Catalog & Orders</th>
          <th class="px-4 py-3.5">Commission</th>
          <th class="px-4 py-3.5">KYC Status</th>
          <th class="px-4 py-3.5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($vendors as $v): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <td class="px-4 py-3.5">
              <div class="font-bold text-gray-900 flex items-center gap-1.5">
                <span><?= htmlspecialchars($v['business_name'] ?? $v['name']) ?></span>
                <?php if (!empty($v['gstin'])): ?>
                  <span class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-mono">GST</span>
                <?php endif; ?>
              </div>
              <div class="text-[11px] text-gray-500"><?= htmlspecialchars($v['name']) ?> • ID #<?= $v['id'] ?></div>
            </td>

            <td class="px-4 py-3.5">
              <span class="bg-stone-100 text-stone-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                <?= htmlspecialchars($v['type'] ?? 'Artisan') ?>
              </span>
            </td>

            <td class="px-4 py-3.5">
              <div class="font-medium text-gray-800">
                <?= htmlspecialchars(!empty($v['village']) ? $v['village'] . ', ' : '') ?><?= htmlspecialchars($v['district'] ?? 'Hardoi') ?>, <?= htmlspecialchars($v['state'] ?? 'UP') ?>
              </div>
              <div class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($v['pincode'] ?? '') ?></div>
            </td>

            <td class="px-4 py-3.5">
              <div class="text-gray-800 font-mono"><?= htmlspecialchars($v['phone'] ?? '') ?></div>
              <div class="text-[10px] text-gray-400"><?= htmlspecialchars($v['email'] ?? '') ?></div>
            </td>

            <td class="px-4 py-3.5">
              <span class="font-bold text-gray-900"><?= $v['product_count'] ?></span> Products • 
              <span class="font-bold text-indigo-700"><?= $v['order_count'] ?></span> Orders
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
              <!-- Edit Vendor Modal Trigger -->
              <button type="button"
                onclick='openEditVendorModal(<?= json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-[11px] rounded-lg shadow-xs transition inline-flex items-center gap-1 mr-1">
                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
              </button>

              <a href="/admin/impersonate.php?action=login_as&user_id=<?= $v['user_id'] ?>"
                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold text-[11px] rounded-lg shadow-xs transition inline-flex items-center gap-1 mr-1"
                title="Login directly into this seller's dashboard">
                <i class="fa-solid fa-store text-[10px]"></i> Seller Login
              </a>

              <?php if ($v['kyc_status'] !== 'Verified'): ?>
                <a href="/admin/vendors.php?vendor_id=<?= $v['id'] ?>&set_kyc=Verified" class="px-2.5 py-1 bg-emerald-600 text-white font-bold rounded text-[11px] hover:bg-emerald-700 transition">
                  Verify KYC
                </a>
              <?php else: ?>
                <a href="/admin/vendors.php?vendor_id=<?= $v['id'] ?>&set_kyc=Pending" class="px-2.5 py-1 bg-stone-100 text-stone-700 font-bold rounded text-[11px] hover:bg-stone-200 transition">
                  Re-evaluate
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Vendor Modal -->
<div id="editVendorModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-stone-200">
    <div class="px-6 py-4 bg-stone-50 border-b border-stone-200 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-xs z-10">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
          <i class="fa-solid fa-building-user text-sm"></i>
        </div>
        <div>
          <h3 class="text-sm font-black text-stone-900" id="modalTitle">Edit Vendor Details</h3>
          <p class="text-[11px] text-stone-500">Super Admin direct update of vendor profile and location</p>
        </div>
      </div>
      <button type="button" onclick="closeEditVendorModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:text-stone-800 flex items-center justify-center">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/vendors.php" method="POST" class="p-6 space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit_vendor">
      <input type="hidden" name="vendor_id" id="edit_vendor_id" value="">

      <!-- Row 1: Business Name & Owner Name -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Business / Workshop Name <span class="text-red-500">*</span></label>
          <input type="text" name="business_name" id="edit_business_name" required class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Owner / Artisan Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Row 2: Type & Commission -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Seller Type</label>
          <select name="type" id="edit_type" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="Individual Artisan">Individual Artisan</option>
            <option value="Women SHG">Women SHG</option>
            <option value="Farmer Producer Org">Farmer Producer Org</option>
            <option value="MSME Cluster">MSME Cluster</option>
            <option value="Village Cooperative">Village Cooperative</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Commission Rate (%)</label>
          <input type="number" step="0.1" name="commission_rate" id="edit_commission_rate" required class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">KYC Status</label>
          <select name="kyc_status" id="edit_kyc_status" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="Verified">Verified</option>
            <option value="Pending">Pending</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
      </div>

      <!-- Row 3: Contact Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
          <input type="tel" name="phone" id="edit_phone" required pattern="[0-9]{10}" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Email Address</label>
          <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Row 4: Location Details (Village, District, State, PIN) -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">Village / Town</label>
          <input type="text" name="village" id="edit_village" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">District</label>
          <input type="text" name="district" id="edit_district" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">State</label>
          <input type="text" name="state" id="edit_state" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">PIN Code</label>
          <input type="text" name="pincode" id="edit_pincode" pattern="[0-9]{6}" maxlength="6" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <!-- Row 5: Full Address -->
      <div>
        <label class="block text-xs font-bold text-stone-700 mb-1">Full Workshop / Manufacturing Address</label>
        <textarea name="address" id="edit_address" rows="2" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
      </div>

      <!-- Row 6: GSTIN & PAN -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">GSTIN (Optional)</label>
          <input type="text" name="gstin" id="edit_gstin" maxlength="15" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-stone-700 mb-1">PAN Number</label>
          <input type="text" name="pan_number" id="edit_pan_number" maxlength="10" class="w-full px-3 py-2 bg-stone-50 border border-stone-300 rounded-xl text-xs uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
        <button type="button" onclick="closeEditVendorModal()" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
          <i class="fa-solid fa-check"></i>
          Save Vendor Details
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditVendorModal(v) {
  document.getElementById('edit_vendor_id').value = v.id || '';
  document.getElementById('edit_business_name').value = v.business_name || v.name || '';
  document.getElementById('edit_name').value = v.name || '';
  document.getElementById('edit_type').value = v.type || 'Individual Artisan';
  document.getElementById('edit_commission_rate').value = v.commission_rate || 4.0;
  document.getElementById('edit_kyc_status').value = v.kyc_status || 'Pending';
  document.getElementById('edit_phone').value = v.phone || '';
  document.getElementById('edit_email').value = v.email || '';
  document.getElementById('edit_village').value = v.village || '';
  document.getElementById('edit_district').value = v.district || '';
  document.getElementById('edit_state').value = v.state || 'Uttar Pradesh';
  document.getElementById('edit_pincode').value = v.pincode || '';
  document.getElementById('edit_address').value = v.address || '';
  document.getElementById('edit_gstin').value = v.gstin || '';
  document.getElementById('edit_pan_number').value = v.pan_number || '';
  
  document.getElementById('modalTitle').innerText = 'Edit ' + (v.business_name || v.name);
  document.getElementById('editVendorModal').classList.remove('hidden');
}

function closeEditVendorModal() {
  document.getElementById('editVendorModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
