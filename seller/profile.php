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
$success_msg = '';
$error_msg = '';

// Handle Workshop, Avatar & Bank Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_store_profile'])) {
    $business_name = clean_input($_POST['business_name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $whatsapp = clean_input($_POST['whatsapp'] ?? '');
    $village = clean_input($_POST['village'] ?? '');
    $district = clean_input($_POST['district'] ?? '');
    $pincode = clean_input($_POST['pincode'] ?? '');
    $story = clean_input($_POST['story'] ?? '');
    $bank_name = clean_input($_POST['bank_name'] ?? '');
    $bank_account = clean_input($_POST['bank_account'] ?? '');
    $bank_ifsc = clean_input($_POST['bank_ifsc'] ?? '');
    $upi_id = clean_input($_POST['upi_id'] ?? '');
    $avatar_url = $vendor['avatar'] ?? $user['avatar'] ?? null;

    // Handle File Upload if provided
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar_file'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $valid_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($mime, $valid_mimes)) {
                $target_dir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $filename = 'vendor_' . $vendor_id . '_' . time() . '.' . $ext;
                $dest = $target_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $avatar_url = '/uploads/avatars/' . $filename;
                }
            } else {
                $error_msg = 'केवल JPG, PNG या WebP छवियां स्वीकार्य हैं।';
            }
        } else {
            $error_msg = 'फ़ाइल का आकार 5MB से कम होना चाहिए।';
        }
    } elseif (!empty($_POST['avatar_url_input'])) {
        $avatar_url = clean_input($_POST['avatar_url_input']);
    }

    if (empty($error_msg)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE vendors 
                SET business_name = ?, phone = ?, whatsapp = ?, village = ?, district = ?, 
                    pincode = ?, story = ?, bank_name = ?, bank_account = ?, bank_ifsc = ?, upi_id = ?, avatar = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $business_name, $phone, $whatsapp, $village, $district,
                $pincode, $story, $bank_name, $bank_account, $bank_ifsc, $upi_id, $avatar_url, $vendor_id
            ]);

            // Also update users.avatar
            if (!empty($avatar_url)) {
                $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$avatar_url, $user['id']]);
            }
            
            // Refresh vendor & user object
            $stmt_ref = $pdo->prepare("SELECT * FROM vendors WHERE id = ?");
            $stmt_ref->execute([$vendor_id]);
            $vendor = $stmt_ref->fetch();
            $user = get_logged_in_user();

            $success_msg = 'कार्यशाला विवरण, प्रोफ़ाइल फ़ोटो एवं बैंकिंग जानकारी सफलतापूर्वक अपडेट की गई।';
        } catch (Exception $e) {
            $error_msg = 'त्रुटि: ' . $e->getMessage();
        }
    }
}

// Handle Seller Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_seller_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_pass, $user['password'])) {
        $error_msg = 'वर्तमान पासवर्ड गलत है।';
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = 'नया पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।';
    } elseif (strlen($new_pass) < 6) {
        $error_msg = 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।';
    } else {
        try {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            $success_msg = 'विक्रेता खाता पासवर्ड सफलतापूर्वक बदल दिया गया है।';
            $user = get_logged_in_user(); // refresh
        } catch (Exception $e) {
            $error_msg = 'त्रुटि: ' . $e->getMessage();
        }
    }
}

$page_title = 'कार्यशाला प्रोफ़ाइल एवं सुरक्षा (Store Profile & Security)';
require_once __DIR__ . '/seller_header.php';
?>

<!-- Title -->
<div class="mb-6">
  <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-2">
    <i class="fa-solid fa-gear text-amber-600"></i>
    Workshop Settings & Security (कार्यशाला प्रोफ़ाइल व सेटिंग्स)
  </h1>
  <p class="text-xs text-gray-500 mt-1">
    अपनी ग्रामीण कुटीर उद्योग जानकारी, कार्यशाला लोगो, बैंक खाता और लॉगिन पासवर्ड प्रबंधित करें
  </p>
</div>

<!-- Notices -->
<?php if (!empty($success_msg)): ?>
  <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 font-medium">
    <i class="fa-solid fa-circle-check text-base"></i>
    <span><?= htmlspecialchars($success_msg) ?></span>
  </div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
  <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs flex items-center gap-2.5 font-medium">
    <i class="fa-solid fa-circle-exclamation text-base"></i>
    <span><?= htmlspecialchars($error_msg) ?></span>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
  
  <!-- Store Details & Banking Form (8 cols) -->
  <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <form action="/seller/profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="update_store_profile" value="1">

      <!-- Section 1: Workshop details & Logo -->
      <div>
        <h3 class="text-sm font-bold text-gray-900 pb-2 mb-4 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-store text-amber-600"></i>
          Workshop Details (कार्यशाला एवं संस्थान विवरण)
        </h3>

        <!-- Logo / Photo Uploader -->
        <div class="p-4 mb-5 bg-stone-50 rounded-2xl border border-gray-200 flex flex-col sm:flex-row items-center gap-4">
          <div class="relative">
            <?php if (!empty($vendor['avatar'])): ?>
              <img id="vendorAvatarPreview" src="<?= htmlspecialchars($vendor['avatar']) ?>" alt="Workshop"
                class="w-16 h-16 rounded-2xl object-cover border-2 border-amber-500 shadow-sm">
            <?php else: ?>
              <div id="vendorAvatarPreview" class="w-16 h-16 rounded-2xl bg-amber-500 text-stone-900 flex items-center justify-center text-2xl font-bold shadow-sm">
                <?= strtoupper(mb_substr($vendor['business_name'] ?? $vendor['name'] ?? 'V', 0, 1)) ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="flex-1 text-center sm:text-left space-y-1.5">
            <label class="block text-xs font-bold text-gray-800">कार्यशाला का लोगो या कारीगर फ़ोटो</label>
            <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
              <label class="cursor-pointer px-3.5 py-1.5 bg-white border border-gray-300 hover:border-amber-500 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-camera text-amber-600"></i> फ़ोटो अपलोड करें
                <input type="file" name="avatar_file" accept="image/*" class="hidden" onchange="previewVendorAvatar(this)">
              </label>
              <span class="text-[11px] text-gray-400">या छवि URL:</span>
            </div>
            <input type="url" name="avatar_url_input" placeholder="https://example.com/artisan.jpg"
              value="<?= htmlspecialchars(strpos($vendor['avatar'] ?? '', 'http') === 0 ? $vendor['avatar'] : '') ?>"
              class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-gray-700 mb-1">दुकान / ब्रांड का नाम</label>
            <input type="text" name="business_name" required value="<?= htmlspecialchars($vendor['business_name'] ?? $vendor['name'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">फ़ोन नंबर (Calling)</label>
            <input type="tel" name="phone" required value="<?= htmlspecialchars($vendor['phone'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">व्हाट्सएप नंबर (WhatsApp)</label>
            <input type="tel" name="whatsapp" value="<?= htmlspecialchars($vendor['whatsapp'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">गाँव / मोहल्ला</label>
            <input type="text" name="village" value="<?= htmlspecialchars($vendor['village'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">जिला (District)</label>
            <input type="text" name="district" value="<?= htmlspecialchars($vendor['district'] ?? 'Hardoi') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पिन कोड (Pincode)</label>
            <input type="text" name="pincode" value="<?= htmlspecialchars($vendor['pincode'] ?? '241126') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-gray-700 mb-1">हस्तशिल्प की कहानी (Artisan Craft Story)</label>
            <textarea name="story" rows="3" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition"><?= htmlspecialchars($vendor['story'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Section 2: Banking & UPI -->
      <div>
        <h3 class="text-sm font-bold text-gray-900 pb-2 mb-4 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-landmark text-amber-600"></i>
          भुगतान एवं बैंक विवरण (Bank & UPI Information)
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">बैंक का नाम (Bank Name)</label>
            <input type="text" name="bank_name" value="<?= htmlspecialchars($vendor['bank_name'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">खाता संख्या (Account Number)</label>
            <input type="text" name="bank_account" value="<?= htmlspecialchars($vendor['bank_account'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">IFSC कोड</label>
            <input type="text" name="bank_ifsc" value="<?= htmlspecialchars($vendor['bank_ifsc'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">UPI ID (त्वरित सेटलमेंट)</label>
            <input type="text" name="upi_id" value="<?= htmlspecialchars($vendor['upi_id'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
          </div>
        </div>
      </div>

      <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition shadow">
        विवरण सहेजें (Save Changes)
      </button>
    </form>
  </div>

  <!-- Security / Password Change Card (4 cols) -->
  <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
      <i class="fa-solid fa-lock text-brand-green"></i>
      <h3 class="text-sm font-bold text-gray-900">विक्रेता पासवर्ड बदलें</h3>
    </div>

    <form action="/seller/profile.php" method="POST" class="space-y-4">
      <input type="hidden" name="change_seller_password" value="1">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">वर्तमान पासवर्ड</label>
        <input type="password" name="current_password" required
          class="w-full px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड (न्यूनतम 6 अक्षर)</label>
        <input type="password" name="new_password" required minlength="6"
          class="w-full px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">नया पासवर्ड दोबारा लिखें</label>
        <input type="password" name="confirm_password" required minlength="6"
          class="w-full px-3.5 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-brand-green transition">
      </div>

      <button type="submit" class="w-full py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-xl text-xs transition shadow">
        पासवर्ड अपडेट करें (Bcrypt)
      </button>
    </form>

    <div class="pt-4 border-t border-gray-100 text-[11px] text-gray-500 space-y-1">
      <p class="font-bold text-gray-700">सुरक्षा सुझाव:</p>
      <p>नया पासवर्ड किसी के साथ साझा न करें। पासवर्ड परिवर्तन के बाद सभी सक्रिय सत्र सुरक्षित रहेंगे।</p>
    </div>
  </div>

</div>

<script>
function previewVendorAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('vendorAvatarPreview');
      if (preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else {
        const img = document.createElement('img');
        img.id = 'vendorAvatarPreview';
        img.src = e.target.result;
        img.className = 'w-16 h-16 rounded-2xl object-cover border-2 border-amber-500 shadow-sm';
        preview.parentNode.replaceChild(img, preview);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
