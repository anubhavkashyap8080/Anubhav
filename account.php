<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();
$user = get_logged_in_user();
$active_tab = clean_input($_GET['tab'] ?? 'orders');

$success_msg = '';
$error_msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = clean_input($_POST['name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $city = clean_input($_POST['city'] ?? '');
    $pincode = clean_input($_POST['pincode'] ?? '');
    $address = clean_input($_POST['address'] ?? '');

    if (!empty($name) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, pincode = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $city, $pincode, $address, $user['id']]);
            $success_msg = 'प्रोफ़ाइल सफलतापूर्वक अपडेट की गई।';
            $user = get_logged_in_user(); // refresh
            $active_tab = 'profile';
        } catch (Exception $e) {
            $error_msg = 'त्रुटि: ' . $e->getMessage();
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_pass, $user['password'])) {
        $error_msg = 'वर्तमान पासवर्ड गलत है।';
        $active_tab = 'security';
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = 'नया पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
        $active_tab = 'security';
    } elseif (strlen($new_pass) < 6) {
        $error_msg = 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।';
        $active_tab = 'security';
    } else {
        try {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            $success_msg = 'पासवर्ड सफलतापूर्वक बदल दिया गया है।';
            $active_tab = 'security';
        } catch (Exception $e) {
            $error_msg = 'त्रुटि: ' . $e->getMessage();
        }
    }
}

// Fetch User Orders
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
$stmt_orders->execute([$user['id'], $user['email']]);
$user_orders = $stmt_orders->fetchAll();

$page_title = 'मेरा खाता (My Account) — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-screen py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Header Summary Card -->
    <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-brand-green text-white flex items-center justify-center text-2xl font-bold shadow-md">
          <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900"><?= htmlspecialchars($user['name']) ?></h1>
          <p class="text-xs text-gray-500 mt-0.5">
            <?= htmlspecialchars($user['email']) ?> • +91 <?= htmlspecialchars($user['phone']) ?>
          </p>
          <span class="inline-block mt-1 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
            सत्यापित ग्राहक (Verified Shopper)
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <?php if (in_array($user['role'], ['vendor', 'admin', 'super_admin'])): ?>
          <a href="/seller/dashboard.php" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-store"></i> विक्रेता पोर्टल
          </a>
        <?php endif; ?>
        <?php if (in_array($user['role'], ['admin', 'super_admin'])): ?>
          <a href="/admin/index.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-shield-halved"></i> एडमिन कंट्रोल
          </a>
        <?php endif; ?>
        <a href="/logout.php" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-gray-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
          <i class="fa-solid fa-power-off text-red-500"></i> लॉगआउट
        </a>
      </div>
    </div>

    <!-- Notices -->
    <?php if (!empty($success_msg)): ?>
      <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2 font-medium">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?= htmlspecialchars($success_msg) ?></span>
      </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs flex items-center gap-2 font-medium">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Navigation & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left Tab Navigation (3 cols) -->
      <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 p-3 shadow-sm space-y-1">
        <a href="/account.php?tab=orders"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'orders' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-box-archive text-sm"></i>
          <span>मेरे आर्डर (My Orders)</span>
          <span class="ml-auto text-[10px] <?= $active_tab === 'orders' ? 'bg-white/20' : 'bg-stone-100' ?> px-2 py-0.5 rounded-full">
            <?= count($user_orders) ?>
          </span>
        </a>

        <a href="/account.php?tab=profile"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'profile' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-id-card text-sm"></i>
          <span>प्रोफ़ाइल विवरण (Profile)</span>
        </a>

        <a href="/account.php?tab=security"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition <?= $active_tab === 'security' ? 'bg-brand-green text-white shadow-sm' : 'text-gray-700 hover:bg-stone-50' ?>">
          <i class="fa-solid fa-shield-halved text-sm"></i>
          <span>सुरक्षा व पासवर्ड (Security)</span>
        </a>

        <a href="/wishlist.php"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition text-gray-700 hover:bg-stone-50">
          <i class="fa-regular fa-heart text-sm"></i>
          <span>मेरी विशलिस्ट (Wishlist)</span>
        </a>
      </div>

      <!-- Right Tab Content (9 cols) -->
      <div class="lg:col-span-9">
        
        <!-- Tab 1: Orders -->
        <?php if ($active_tab === 'orders'): ?>
          <div class="space-y-4">
            <h2 class="text-lg font-bold text-gray-900 mb-2">मेरे आर्डर का इतिहास (My Orders)</h2>

            <?php if (empty($user_orders)): ?>
              <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <i class="fa-solid fa-box-open text-4xl text-stone-300 mb-3"></i>
                <h3 class="text-base font-bold text-gray-700">अभी तक कोई आर्डर नहीं दिया गया</h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">हथकरघा और देसी कुटीर उत्पाद अभी एक्सप्लोर करें।</p>
                <a href="/products.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                  शॉपिंग शुरू करें <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($user_orders as $ord): 
                // Fetch items for each order
                $stmt_ord_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt_ord_items->execute([$ord['id']]);
                $items = $stmt_ord_items->fetchAll();
              ?>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                  <!-- Order Header -->
                  <div class="bg-stone-50 p-4 sm:p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                      <span class="text-gray-500">आर्डर सं.:</span>
                      <strong class="text-gray-900 ml-1 font-mono">#<?= htmlspecialchars($ord['order_number']) ?></strong>
                      <span class="text-gray-400 mx-2">•</span>
                      <span class="text-gray-500"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                        <?= htmlspecialchars($ord['order_status']) ?>
                      </span>
                      <a href="/order-success.php?id=<?= $ord['id'] ?>" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-stone-100 font-bold transition text-[11px]">
                        विवरण / रसीद
                      </a>
                    </div>
                  </div>

                  <!-- India Post Tracking Banner -->
                  <div class="p-4 bg-amber-50/50 border-b border-amber-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-truck-fast text-red-600"></i>
                      <span class="text-gray-700">भारतीय डाक (India Post AWB):</span>
                      <span class="font-mono font-bold text-gray-900"><?= htmlspecialchars($ord['tracking_consignment_no'] ?? 'AWB-PENDING') ?></span>
                    </div>
                    <span class="text-[11px] text-gray-500"><?= htmlspecialchars($ord['tracking_carrier'] ?? 'India Post') ?></span>
                  </div>

                  <!-- Items in Order -->
                  <div class="divide-y divide-gray-100 p-4">
                    <?php foreach ($items as $it): ?>
                      <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                          <p class="font-bold text-gray-900"><?= htmlspecialchars($it['product_name']) ?></p>
                          <p class="text-gray-500 text-[11px]">कोड: <?= htmlspecialchars($it['product_code']) ?> • मात्रा: <?= $it['quantity'] ?></p>
                        </div>
                        <div class="font-bold text-gray-900">
                          <?= format_price($it['total']) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- Footer Total -->
                  <div class="bg-stone-50 p-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-600">भुगतान विधि: <strong class="uppercase text-gray-800"><?= htmlspecialchars($ord['payment_method']) ?></strong> (<?= htmlspecialchars($ord['payment_status']) ?>)</span>
                    <span class="font-bold text-sm text-gray-900">कुल: <span class="text-brand-green font-extrabold"><?= format_price($ord['total_amount']) ?></span></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <!-- Tab 2: Profile -->
        <?php elseif ($active_tab === 'profile'): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100">प्रोफ़ाइल विवरण संपादित करें (Edit Profile)</h2>

            <form action="/account.php?tab=profile" method="POST" class="space-y-4">
              <input type="hidden" name="update_profile" value="1">

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">पूरा नाम</label>
                  <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर</label>
                  <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($user['phone']) ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता (बदला नहीं जा सकता)</label>
                  <input type="email" disabled value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs font-bold text-gray-700 mb-1">डिफ़ॉल्ट डिलीवरी पता</label>
                  <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">शहर / जिला</label>
                  <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? 'Hardoi') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">पिन कोड</label>
                  <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?= htmlspecialchars($user['pincode'] ?? '241001') ?>"
                    class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                परिवर्तन सहेजें (Save Changes)
              </button>
            </form>
          </div>

        <!-- Tab 3: Security -->
        <?php elseif ($active_tab === 'security'): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100">सुरक्षा एवं पासवर्ड बदलें (Change Password)</h2>

            <form action="/account.php?tab=security" method="POST" class="space-y-4 max-w-md">
              <input type="hidden" name="change_password" value="1">

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">वर्तमान पासवर्ड (Current Password)</label>
                <input type="password" name="current_password" required
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड (New Password - Min 6 chars)</label>
                <input type="password" name="new_password" required minlength="6"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड दोबारा लिखें (Confirm New Password)</label>
                <input type="password" name="confirm_password" required minlength="6"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <button type="submit" class="px-6 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition">
                पासवर्ड अपडेट करें (Update Password)
              </button>
            </form>
          </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
