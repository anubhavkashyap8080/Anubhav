<?php
/**
 * GanvGaliHat Retailer Hub — Business Profile & Tax Settings
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_retailer();

$user = get_logged_in_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    csrf_protect();

    $business_name  = clean_input($_POST['business_name'] ?? '');
    $gstin          = strtoupper(clean_input($_POST['gstin'] ?? ''));
    $business_type  = clean_input($_POST['business_type'] ?? 'Kirana / Grocery');
    $pan_number     = strtoupper(clean_input($_POST['pan_number'] ?? ''));
    $contact_name   = clean_input($_POST['name'] ?? '');
    $email          = clean_input($_POST['email'] ?? '');
    $address        = clean_input($_POST['business_address'] ?? '');
    $city           = clean_input($_POST['business_city'] ?? '');
    $state          = clean_input($_POST['business_state'] ?? 'Uttar Pradesh');
    $pincode        = clean_input($_POST['business_pincode'] ?? '');

    if (empty($business_name) || empty($contact_name)) {
        $error = 'कृपया दुकान/फर्म का नाम और संपर्क व्यक्ति का नाम दर्ज करें।';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'कृपया वैध ईमेल पता दर्ज करें।';
    } elseif (empty($address) || empty($city) || empty($pincode)) {
        $error = 'कृपया पूरा डिलीवरी पता, शहर और पिनकोड दर्ज करें।';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE users SET
                    name = ?, email = ?, business_name = ?, gstin = ?,
                    business_type = ?, pan_number = ?, business_address = ?,
                    business_city = ?, business_state = ?, business_pincode = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $contact_name, $email, $business_name, $gstin,
                $business_type, $pan_number, $address,
                $city, $state, $pincode, $user['id']
            ]);

            $_SESSION['user_name'] = $contact_name;
            $_SESSION['business_name'] = $business_name;

            log_audit_action('retailer_updated_profile', 'users', "Retailer #{$user['id']} updated business profile: {$business_name}");
            set_flash('success', 'व्यापार एवं टैक्स प्रोफाइल सफलतापूर्वक अपडेट किया गया!');
            header('Location: /retailer/profile.php');
            exit;
        } catch (Exception $e) {
            $error = 'प्रोफाइल सहेजने में त्रुटि: ' . $e->getMessage();
        }
    }
}

// Reload updated user
$user = get_logged_in_user();

$page_title = 'व्यापार प्रोफाइल (Retailer Profile & GST)';
require_once __DIR__ . '/retailer_header.php';
?>

<div class="max-w-4xl mx-auto">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-id-card text-amber-600"></i>
        व्यापार व इनवॉइस प्रोफाइल (Business Profile)
      </h1>
      <p class="text-xs text-gray-500 mt-1">
        दुकान का नाम, GSTIN, टैक्स इनपुट क्रेडिट विवरण एवं डिलीवरी गोदाम पता प्रबंधित करें
      </p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-2.5">
      <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5"></i>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
      <div>
        <h2 class="text-sm font-black flex items-center gap-2">
          <i class="fa-solid fa-store"></i>
          <?= htmlspecialchars($user['business_name'] ?? $user['name']) ?>
        </h2>
        <span class="text-xs text-amber-100 font-mono">पंजीकृत फोन: +91 <?= htmlspecialchars($user['phone']) ?></span>
      </div>
      <?php if (!empty($user['gstin'])): ?>
        <span class="bg-emerald-500 text-white font-mono font-bold text-xs px-2.5 py-1 rounded-full shadow-xs">
          GST Verified
        </span>
      <?php endif; ?>
    </div>

    <form method="POST" action="/retailer/profile.php" class="p-6 sm:p-8 space-y-6">
      <?= csrf_field() ?>
      <input type="hidden" name="save_profile" value="1">

      <!-- Section 1: Firm Info -->
      <div>
        <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
          <i class="fa-solid fa-shop text-amber-600"></i>
          1. फर्म / दुकान का विवरण
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">दुकान / फर्म का नाम <span class="text-red-500">*</span></label>
            <input type="text" name="business_name" required
              value="<?= htmlspecialchars($user['business_name'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">व्यापार श्रेणी</label>
            <select name="business_type" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
              <?php
                $types = [
                  'Kirana / Grocery' => 'किराना व जनरल स्टोर',
                  'Handloom / Apparel' => 'हैंडलूम व वस्त्र विक्रेता',
                  'Hardware / Agro Tools' => 'हार्डवेयर व कृषि औजार',
                  'Supermarket / Departmental' => 'सुपरमार्केट / डिपार्टमेंटल स्टोर',
                  'Ayurveda / Organic Outlet' => 'आयुर्वेदिक व जैविक आउटलेट',
                  'Wholesaler / Reseller' => 'थोक डीलर / ऑनलाइन रीसेलर',
                  'Other' => 'अन्य खुदरा व्यापार'
                ];
                $current_type = $user['business_type'] ?? 'Kirana / Grocery';
                foreach ($types as $val => $lbl):
              ?>
                <option value="<?= $val ?>" <?= $current_type === $val ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Section 2: Tax Details -->
      <div>
        <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
          <i class="fa-solid fa-file-invoice text-amber-600"></i>
          2. टैक्स पहचान संख्या (GSTIN & PAN)
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">GSTIN नंबर (15 Digits)</label>
            <input type="text" name="gstin" maxlength="15"
              value="<?= htmlspecialchars($user['gstin'] ?? '') ?>"
              placeholder="उदा. 09ABCDE1234F1Z5"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-amber-600 transition">
            <p class="text-[10px] text-gray-500 mt-0.5">GST नंबर दर्ज करने पर टैक्स इनवॉइस में ITC इनपुट क्लेम स्वतः उपलब्ध होगा।</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">PAN नंबर (10 Digits)</label>
            <input type="text" name="pan_number" maxlength="10"
              value="<?= htmlspecialchars($user['pan_number'] ?? '') ?>"
              placeholder="उदा. ABCDE1234F"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>
        </div>
      </div>

      <!-- Section 3: Contact Person -->
      <div>
        <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
          <i class="fa-solid fa-user text-amber-600"></i>
          3. संपर्क व्यक्ति विवरण
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">नाम <span class="text-red-500">*</span></label>
            <input type="text" name="name" required
              value="<?= htmlspecialchars($user['name'] ?? '') ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर (Login ID)</label>
            <input type="text" disabled readonly
              value="+91 <?= htmlspecialchars($user['phone'] ?? '') ?>"
              class="w-full px-3 py-2 bg-stone-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-500 cursor-not-allowed">
            <p class="text-[10px] text-gray-400 mt-0.5">सुरक्षा कारणों से लॉगिन नंबर बदलने हेतु सपोर्ट से संपर्क करें।</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता</label>
            <input type="email" name="email"
              value="<?= htmlspecialchars($user['email'] ?? '') ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>
        </div>
      </div>

      <!-- Section 4: Godown Address -->
      <div>
        <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
          <i class="fa-solid fa-location-dot text-amber-600"></i>
          4. दुकान / गोदाम का स्थायी डिलीवरी पता
        </h3>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पूरा पता व लैंडमार्क <span class="text-red-500">*</span></label>
            <textarea name="business_address" rows="2" required
              class="w-full px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-amber-600 transition"><?= htmlspecialchars($user['business_address'] ?? $user['address'] ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">शहर / कस्बा <span class="text-red-500">*</span></label>
              <input type="text" name="business_city" required
                value="<?= htmlspecialchars($user['business_city'] ?? $user['city'] ?? '') ?>"
                class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">राज्य <span class="text-red-500">*</span></label>
              <input type="text" name="business_state" required
                value="<?= htmlspecialchars($user['business_state'] ?? $user['state'] ?? 'Uttar Pradesh') ?>"
                class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">पिनकोड <span class="text-red-500">*</span></label>
              <input type="text" name="business_pincode" required maxlength="6"
                value="<?= htmlspecialchars($user['business_pincode'] ?? $user['pincode'] ?? '') ?>"
                class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
            </div>
          </div>
        </div>
      </div>

      <div class="pt-4 border-t border-gray-100 flex items-center justify-end">
        <button type="submit"
          class="px-6 py-3 bg-stone-900 hover:bg-black text-amber-400 font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
          <i class="fa-solid fa-floppy-disk"></i>
          प्रोफाइल अपडेट सहेजें (Save Changes)
        </button>
      </div>

    </form>
  </div>
</div>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
