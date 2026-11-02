<?php
/**
 * GanvGaliHat Multi-Step Seller & Artisan Onboarding Wizard
 * 5-Part Registration Process:
 * Part 1: Personal & Workshop Details
 * Part 2: Business & Workshop Address
 * Part 3: Identity & Business Documents (KYC)
 * Part 4: Payout & Bank Details
 * Part 5: Privacy Policy Acceptance & Final Submit
 */

require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
    } else {
        // Part 1: Personal & Account
        $owner_name = clean_input($_POST['owner_name'] ?? '');
        $shop_name = clean_input($_POST['shop_name'] ?? '');
        $vendor_type = clean_input($_POST['vendor_type'] ?? 'Artisan');
        $email = clean_input($_POST['email'] ?? '');
        $raw_phone = clean_input($_POST['phone'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $raw_phone);
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Part 2: Address
        $address = clean_input($_POST['address'] ?? '');
        $village = clean_input($_POST['village'] ?? '');
        $district = clean_input($_POST['district'] ?? 'Hardoi');
        $state = clean_input($_POST['state'] ?? 'Uttar Pradesh');
        $pincode = clean_input($_POST['pincode'] ?? '241126');

        // Part 3: Documents (KYC)
        $pan_number = strtoupper(clean_input($_POST['pan_number'] ?? ''));
        $aadhaar_number = clean_input($_POST['aadhaar_number'] ?? '');
        $gstin = strtoupper(clean_input($_POST['gstin'] ?? ''));
        $doc_path = null;

        // Part 4: Bank Details
        $bank_beneficiary = clean_input($_POST['bank_beneficiary'] ?? $owner_name);
        $bank_name = clean_input($_POST['bank_name'] ?? '');
        $bank_account = clean_input($_POST['bank_account'] ?? '');
        $bank_account_confirm = clean_input($_POST['bank_account_confirm'] ?? '');
        $bank_ifsc = strtoupper(clean_input($_POST['bank_ifsc'] ?? ''));
        $upi_id = clean_input($_POST['upi_id'] ?? '');

        // Part 5: Privacy & Agreements
        $story = clean_input($_POST['story'] ?? '');
        $agree_terms = !empty($_POST['agree_terms']);
        $agree_authenticity = !empty($_POST['agree_authenticity']);
        $agree_privacy = !empty($_POST['agree_privacy']);

        $client_ip = get_client_ip();
        $pwd_err = '';

        if (!check_rate_limit('seller_register_ip', $client_ip, 5, 3600) || !check_rate_limit('seller_register_phone', $phone, 3, 3600)) {
            $error = 'Too many registration attempts. Please wait an hour before trying again.';
        } elseif (!verify_turnstile_captcha()) {
            $error = 'Security CAPTCHA verification failed. Please try again.';
        } elseif (empty($owner_name) || empty($shop_name) || empty($phone) || empty($email) || empty($password)) {
            $error = 'Please complete all required personal and account fields in Step 1.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $error = 'Please enter a valid 10-digit Indian mobile number.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (!is_strong_password($password, $pwd_err)) {
            $error = $pwd_err ?: 'Password must be at least 10 characters and include uppercase, lowercase, numbers, and symbols.';
        } elseif (empty($address) || strlen($address) < 5 || empty($district) || !preg_match('/^\d{6}$/', $pincode)) {
            $error = 'Please enter a complete workshop address with valid 6-digit PIN code in Step 2.';
        } elseif (!empty($bank_account) && !empty($bank_account_confirm) && $bank_account !== $bank_account_confirm) {
            $error = 'Bank account number and confirmation account number do not match in Step 4.';
        } elseif (!$agree_terms || !$agree_authenticity || !$agree_privacy) {
            $error = 'You must accept the Vendor Terms, Authenticity Guarantee, and Privacy Policy in Step 5.';
        } else {
            // Handle Document Upload if provided
            if (isset($_FILES['kyc_doc_file']) && $_FILES['kyc_doc_file']['error'] === UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . '/uploads/kyc/';
                if (!is_dir($target_dir)) {
                    @mkdir($target_dir, 0755, true);
                }
                
                $orig_name = $_FILES['kyc_doc_file']['name'];
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
                
                if (in_array($ext, $allowed_exts, true) && $_FILES['kyc_doc_file']['size'] <= 5 * 1024 * 1024) {
                    $new_filename = 'kyc_' . bin2hex(random_bytes(12)) . '.' . $ext;
                    $dest_path = $target_dir . $new_filename;
                    if (move_uploaded_file($_FILES['kyc_doc_file']['tmp_name'], $dest_path)) {
                        $doc_path = '/uploads/kyc/' . $new_filename;
                    }
                }
            }

            try {
                $pdo->beginTransaction();

                // Check duplicate email or phone
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
                $stmt->execute([$email, $phone]);
                if ($stmt->fetch()) {
                    $error = 'An account with this email address or mobile phone number already exists.';
                    $pdo->rollBack();
                } else {
                    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
                    $username = $base_username . random_int(10, 99);
                    $unique_code = 'GGH-ART-' . random_int(10000, 99999);
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Insert User record
                    $insert_user = $pdo->prepare("
                        INSERT INTO users (
                            unique_code, name, username, email, phone, password, role, 
                            department, address, city, pincode, status, phone_verified_at, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, 'vendor', ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                        )
                    ");
                    $insert_user->execute([
                        $unique_code, $owner_name, $username, $email, $phone, $hashed_password,
                        $shop_name . ' (' . $vendor_type . ')', $address, $district, $pincode
                    ]);
                    $user_id = (int)$pdo->lastInsertId();

                    // Generate Vendor Slug
                    $base_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $shop_name));
                    $slug = trim($base_slug, '-') . '-' . random_int(10, 99);

                    // Insert Vendor record
                    $insert_vendor = $pdo->prepare("
                        INSERT INTO vendors (
                            user_id, name, business_name, slug, type, address, village, district, state,
                            pincode, phone, whatsapp, email, story, bank_beneficiary, bank_name,
                            bank_account, bank_ifsc, upi_id, pan_number, aadhaar_number, gstin,
                            document_file, terms_accepted_at, verified, kyc_status, commission_rate, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 1, 'Verified', 4.00, CURRENT_TIMESTAMP
                        )
                    ");
                    $insert_vendor->execute([
                        $user_id, $owner_name, $shop_name, $slug, $vendor_type, $address, $village,
                        $district, $state, $pincode, $phone, $phone, $email, $story, $bank_beneficiary,
                        $bank_name, $bank_account, $bank_ifsc, $upi_id, $pan_number, $aadhaar_number,
                        $gstin, $doc_path
                    ]);

                    $pdo->commit();

                    // Send email verification link
                    try {
                        send_email_verification_link($user_id, $email, $owner_name, $phone);
                    } catch (Throwable $e_mail) {}

                    // Session rotation
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $owner_name;
                    $_SESSION['user_role'] = 'vendor';
                    $_SESSION['user_email'] = $email;
                    $_SESSION['phone_verified_' . $user_id] = true;
                    $_SESSION['verified_checkout_phone'] = $phone;

                    log_audit_action('vendor_registered', 'vendors', "New artisan vendor #{$user_id} ({$shop_name}) onboarded through multi-step wizard");
                    set_flash('success', "Welcome to GanvGaliHat Artisan Network! Your workshop ({$shop_name}) has been created successfully.");
                    header('Location: /seller/dashboard.php');
                    exit;
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Registration processing error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Artisan & Seller Onboarding — GanvGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-screen py-10 overflow-x-hidden">
  <div class="max-w-4xl mx-auto px-4 sm:px-6">
    
    <!-- Hero Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-600 text-white shadow-lg mb-3">
        <i class="fa-solid fa-hammer text-2xl"></i>
      </div>
      <span class="inline-block text-xs font-bold text-amber-800 bg-amber-100 px-3.5 py-1 rounded-full mb-2">
        Artisan & Producer Network Onboarding
      </span>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
        Sell Directly on GanvGaliHat Across India
      </h1>
      <p class="text-xs sm:text-sm text-gray-600 max-w-2xl mx-auto mt-2">
        Empowering generational blacksmiths, handloom weavers, cold-pressed millers, and rural self-help groups with direct access to nationwide customers.
      </p>
    </div>

    <!-- Multi-Step Progress Tracker Bar (Strictly Horizontal on All Mobile & Desktop Screens) -->
    <style>
      .stepper-horizontal-container {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        width: 100% !important;
        position: relative !important;
      }
      .stepper-horizontal-item {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 2 !important;
      }
      .stepper-connector-line {
        position: absolute !important;
        top: 14px !important;
        left: 10% !important;
        right: 10% !important;
        height: 2px !important;
        background-color: #e7e5e4 !important;
        z-index: 1 !important;
      }
      .stepper-connector-fill {
        height: 100% !important;
        background-color: #d97706 !important;
        transition: width 0.4s ease-out !important;
      }
      @media (min-width: 640px) {
        .stepper-connector-line {
          top: 18px !important;
        }
      }
    </style>

    <div class="bg-white rounded-3xl border border-gray-200 p-3 sm:p-6 shadow-sm mb-8">
      <div class="relative w-full">
        <!-- Connecting Line Behind Step Pills -->
        <div class="stepper-connector-line">
          <div id="stepProgressBar" class="stepper-connector-fill" style="width: 20%;"></div>
        </div>

        <!-- 5 Step Indicator Pills (Forced Horizontal Flex Row) -->
        <div class="stepper-horizontal-container">
          <!-- Step 1 Tab -->
          <div id="stepTab1" class="stepper-horizontal-item step-tab" onclick="goToStep(1)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white ring-4 ring-amber-100">
              1
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-amber-700 truncate w-full px-0.5">1. Personal</p>
          </div>

          <!-- Step 2 Tab -->
          <div id="stepTab2" class="stepper-horizontal-item step-tab" onclick="goToStep(2)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              2
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">2. Address</p>
          </div>

          <!-- Step 3 Tab -->
          <div id="stepTab3" class="stepper-horizontal-item step-tab" onclick="goToStep(3)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              3
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">3. Docs</p>
          </div>

          <!-- Step 4 Tab -->
          <div id="stepTab4" class="stepper-horizontal-item step-tab" onclick="goToStep(4)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              4
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">4. Bank</p>
          </div>

          <!-- Step 5 Tab -->
          <div id="stepTab5" class="stepper-horizontal-item step-tab" onclick="goToStep(5)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              5
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">5. Submit</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Wizard Container -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-10 relative">
      
      <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs flex items-center gap-2.5 font-medium shadow-xs">
          <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0 text-red-500"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form id="sellerRegForm" action="/seller-register.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>

        <!-- ============================================================== -->
        <!-- STEP 1: PERSONAL & WORKSHOP DETAILS                            -->
        <!-- ============================================================== -->
        <div id="stepPanel1" class="step-panel space-y-5">
          <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">1</span>
              Personal & Workshop Details
            </h2>
            <span class="text-xs text-gray-400">Step 1 of 5</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Owner / Artisan Full Name <span class="text-red-500">*</span>
              </label>
              <input type="text" name="owner_name" id="owner_name" required value="<?= htmlspecialchars($_POST['owner_name'] ?? '') ?>"
                placeholder="e.g. Ram Kishan Lohar"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Workshop / Brand / Shop Name <span class="text-red-500">*</span>
              </label>
              <input type="text" name="shop_name" id="shop_name" required value="<?= htmlspecialchars($_POST['shop_name'] ?? '') ?>"
                placeholder="e.g. Ram Kishan Hand Forged Tools"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Entity / Business Type <span class="text-red-500">*</span>
              </label>
              <select name="vendor_type" id="vendor_type" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
                <option value="Artisan">Individual Rural Artisan / Blacksmith</option>
                <option value="Cottage Industry">Rural Workshop / Cottage Industry</option>
                <option value="SHG">Women Self Help Group (SHG)</option>
                <option value="Farmer">Farmer Producer Group / Natural Staples (FPO)</option>
                <option value="Small Manufacturer">Small Scale Producer</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Mobile Number (WhatsApp Enabled) <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                  +91
                </span>
                <input type="tel" name="phone" id="phone" required pattern="[6-9][0-9]{9}" maxlength="10"
                  value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="10-digit mobile"
                  class="w-full pl-12 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition font-semibold">
              </div>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Primary Contact Email <span class="text-red-500">*</span>
              </label>
              <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                placeholder="artisan@ganvgalihat.com"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Create Secure Password <span class="text-red-500">*</span>
              </label>
              <input type="password" name="password" id="password" required minlength="10"
                placeholder="Min 10 characters (strong)"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
              <p class="text-[11px] text-gray-400 mt-1">Must include uppercase, lowercase, numbers & symbols.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Confirm Secure Password <span class="text-red-500">*</span>
              </label>
              <input type="password" name="confirm_password" id="confirm_password" required minlength="10"
                placeholder="Re-enter password"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>

          <div class="pt-4 flex justify-end">
            <button type="button" onclick="validateAndGoToStep(2)"
              class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2">
              <span>Continue to Workshop Address</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- STEP 2: BUSINESS & WORKSHOP ADDRESS                            -->
        <!-- ============================================================== -->
        <div id="stepPanel2" class="step-panel hidden space-y-5">
          <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">2</span>
              Business & Workshop Address
            </h2>
            <span class="text-xs text-gray-400">Step 2 of 5</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Workshop Street Address / Landmark / Mohalla <span class="text-red-500">*</span>
              </label>
              <textarea name="address" id="address" rows="2" required placeholder="Plot No, Industrial Area, Baghauli Road, or village landmark..."
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Village / Town / Gram Panchayat <span class="text-red-500">*</span>
              </label>
              <input type="text" name="village" id="village" required value="<?= htmlspecialchars($_POST['village'] ?? '') ?>"
                placeholder="e.g. Madhoganj, Newada, Mallawan"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                District / City <span class="text-red-500">*</span>
              </label>
              <input type="text" name="district" id="district" required value="<?= htmlspecialchars($_POST['district'] ?? 'Hardoi') ?>"
                placeholder="e.g. Hardoi"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                State <span class="text-red-500">*</span>
              </label>
              <select name="state" id="state" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
                <option value="Uttar Pradesh" selected>Uttar Pradesh</option>
                <option value="Bihar">Bihar</option>
                <option value="Madhya Pradesh">Madhya Pradesh</option>
                <option value="Rajasthan">Rajasthan</option>
                <option value="Delhi">Delhi NCR</option>
                <option value="Haryana">Haryana</option>
                <option value="Punjab">Punjab</option>
                <option value="Maharashtra">Maharashtra</option>
                <option value="Gujarat">Gujarat</option>
                <option value="West Bengal">West Bengal</option>
                <option value="Other">Other State</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                6-Digit PIN Code <span class="text-red-500">*</span>
              </label>
              <input type="text" name="pincode" id="pincode" required pattern="[0-9]{6}" maxlength="6"
                value="<?= htmlspecialchars($_POST['pincode'] ?? '241126') ?>" placeholder="241126"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>

          <div class="pt-4 flex items-center justify-between">
            <button type="button" onclick="goToStep(1)"
              class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
              <i class="fa-solid fa-arrow-left text-xs"></i>
              <span>Back to Personal</span>
            </button>
            <button type="button" onclick="validateAndGoToStep(3)"
              class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2">
              <span>Continue to Documents</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- STEP 3: IDENTITY & BUSINESS DOCUMENTS (KYC)                    -->
        <!-- ============================================================== -->
        <div id="stepPanel3" class="step-panel hidden space-y-5">
          <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">3</span>
              Identity & Business Documents (KYC)
            </h2>
            <span class="text-xs text-gray-400">Step 3 of 5</span>
          </div>

          <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
            <i class="fa-solid fa-shield-halved text-amber-600 text-base shrink-0 mt-0.5"></i>
            <div>
              <strong class="font-bold">Government Compliance & Trust:</strong>
              <p class="mt-0.5 text-amber-800">
                To guarantee genuine artisan origin and protect customers, seller identity documents are securely encrypted and verified per Indian e-commerce seller compliance guidelines.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                PAN Card Number
              </label>
              <input type="text" name="pan_number" id="pan_number" maxlength="10" placeholder="e.g. ABCDE1234F"
                value="<?= htmlspecialchars($_POST['pan_number'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm uppercase font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
              <p class="text-[11px] text-gray-400 mt-1">Required for TDS compliance and direct bank settlements.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Aadhaar Number (Last 4 digits or Full)
              </label>
              <input type="text" name="aadhaar_number" id="aadhaar_number" maxlength="12" placeholder="e.g. 123456789012"
                value="<?= htmlspecialchars($_POST['aadhaar_number'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
              <p class="text-[11px] text-gray-400 mt-1">Used exclusively for artisan identity validation.</p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-gray-700 mb-1">
                GSTIN (Optional — if registered)
              </label>
              <input type="text" name="gstin" id="gstin" maxlength="15" placeholder="e.g. 09ABCDE1234F1Z5"
                value="<?= htmlspecialchars($_POST['gstin'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm uppercase font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
              <p class="text-[11px] text-gray-400 mt-1">Individual traditional artisans earning below threshold can register without GSTIN.</p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Upload Identity / Workshop Proof (Optional now, can be uploaded in dashboard)
              </label>
              <div class="p-4 bg-stone-50 border border-dashed border-gray-300 rounded-2xl flex flex-col items-center justify-center text-center">
                <i class="fa-solid fa-cloud-arrow-up text-2xl text-amber-600 mb-2"></i>
                <label class="cursor-pointer text-xs font-bold text-amber-700 hover:text-amber-800">
                  <span>Browse Document File</span>
                  <input type="file" name="kyc_doc_file" accept=".jpg,.jpeg,.png,.pdf,.webp" class="hidden" onchange="updateKycFilename(this)">
                </label>
                <p id="kycFilenameText" class="text-[11px] text-gray-500 mt-1">Aadhaar Card, PAN Card, or Udyam Certificate (JPG, PNG, PDF max 5MB)</p>
              </div>
            </div>
          </div>

          <div class="pt-4 flex items-center justify-between">
            <button type="button" onclick="goToStep(2)"
              class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
              <i class="fa-solid fa-arrow-left text-xs"></i>
              <span>Back to Address</span>
            </button>
            <button type="button" onclick="validateAndGoToStep(4)"
              class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2">
              <span>Continue to Bank Details</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- STEP 4: PAYOUT & BANK DETAILS                                  -->
        <!-- ============================================================== -->
        <div id="stepPanel4" class="step-panel hidden space-y-5">
          <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">4</span>
              Payout & Bank Account Details
            </h2>
            <span class="text-xs text-gray-400">Step 4 of 5</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Account Beneficiary / Holder Name
              </label>
              <input type="text" name="bank_beneficiary" id="bank_beneficiary" placeholder="As printed on bank passbook"
                value="<?= htmlspecialchars($_POST['bank_beneficiary'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Bank Name
              </label>
              <input type="text" name="bank_name" id="bank_name" placeholder="e.g. State Bank of India, PNB"
                value="<?= htmlspecialchars($_POST['bank_name'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Bank Account Number
              </label>
              <input type="text" name="bank_account" id="bank_account" placeholder="Enter account number"
                value="<?= htmlspecialchars($_POST['bank_account'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Re-enter Bank Account Number
              </label>
              <input type="text" name="bank_account_confirm" id="bank_account_confirm" placeholder="Re-enter account number"
                value="<?= htmlspecialchars($_POST['bank_account_confirm'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                Bank IFSC Code
              </label>
              <input type="text" name="bank_ifsc" id="bank_ifsc" maxlength="11" placeholder="e.g. SBIN0001245"
                value="<?= htmlspecialchars($_POST['bank_ifsc'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm uppercase font-mono focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                UPI ID (Instant Mobile Payouts)
              </label>
              <input type="text" name="upi_id" id="upi_id" placeholder="e.g. 9876543210@sbi or artisan@paytm"
                value="<?= htmlspecialchars($_POST['upi_id'] ?? '') ?>"
                class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition">
            </div>
          </div>

          <div class="pt-4 flex items-center justify-between">
            <button type="button" onclick="goToStep(3)"
              class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
              <i class="fa-solid fa-arrow-left text-xs"></i>
              <span>Back to Documents</span>
            </button>
            <button type="button" onclick="validateAndGoToStep(5)"
              class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2">
              <span>Continue to Privacy & Submit</span>
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- STEP 5: PRIVACY POLICY & FINAL SUBMISSION                      -->
        <!-- ============================================================== -->
        <div id="stepPanel5" class="step-panel hidden space-y-5">
          <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">5</span>
              Heritage Story & Privacy Policy Acceptance
            </h2>
            <span class="text-xs text-gray-400">Step 5 of 5</span>
          </div>

          <!-- Craft Heritage Story -->
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">
              About Your Craft & Heritage Story (Featured on your Artisan Storefront)
            </label>
            <textarea name="story" id="story" rows="3" placeholder="Tell customers about your generational artisan trade, years of experience, handcrafting techniques, and raw materials used..."
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-amber-600 focus:outline-none transition"><?= htmlspecialchars($_POST['story'] ?? '') ?></textarea>
          </div>

          <!-- Mandatory Acceptance Checkboxes -->
          <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-3.5 text-xs">
            <h3 class="font-bold text-gray-900 text-sm">Mandatory Seller Declarations</h3>

            <label class="flex items-start gap-3 cursor-pointer">
              <input type="checkbox" name="agree_terms" id="agree_terms" required class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
              <span class="text-gray-700">
                I agree to the <a href="/terms.php" target="_blank" class="text-amber-700 font-bold hover:underline">GanvGaliHat Vendor Terms & Conditions</a> and understand that a standard 4% platform facilitation fee applies to completed sales.
              </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
              <input type="checkbox" name="agree_authenticity" id="agree_authenticity" required class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
              <span class="text-gray-700">
                I certify that all products listed from my workshop will be authentic handmade, hand-forged, pure farm-grown, or indigenous rural goods manufactured with honest quality.
              </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
              <input type="checkbox" name="agree_privacy" id="agree_privacy" required class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
              <span class="text-gray-700">
                I accept the <a href="/privacy.php" target="_blank" class="text-amber-700 font-bold hover:underline">GanvGaliHat Privacy Policy</a> and consent to the secure verification of my registration details for courier dispatch and payout settlements.
              </span>
            </label>
          </div>

          <!-- Turnstile CAPTCHA Widget -->
          <div class="flex justify-center pt-2">
            <?= render_turnstile_widget() ?>
          </div>

          <!-- Actions -->
          <div class="pt-4 flex items-center justify-between">
            <button type="button" onclick="goToStep(4)"
              class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
              <i class="fa-solid fa-arrow-left text-xs"></i>
              <span>Back to Bank</span>
            </button>
            <button type="submit" id="submitSellerBtn"
              class="px-8 py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
              <i class="fa-solid fa-shop"></i>
              <span>Complete Seller Registration</span>
            </button>
          </div>
        </div>

      </form>

      <div class="mt-6 pt-5 border-t border-gray-100 text-center text-xs text-gray-600">
        Already have a registered seller account? <a href="/login.php?tab=seller" class="text-amber-700 font-bold hover:underline">Sign In to Seller Central</a>
      </div>

    </div>

  </div>
</div>

<script>
let currentStep = 1;

function updateKycFilename(input) {
  const note = document.getElementById('kycFilenameText');
  if (input.files && input.files[0]) {
    note.innerHTML = '<strong class="text-emerald-700"><i class="fa-solid fa-file-check"></i> ' + input.files[0].name + '</strong> (' + Math.round(input.files[0].size / 1024) + ' KB)';
  }
}

function goToStep(step) {
  // If moving forward manually via tab click, validate prerequisites
  if (step > currentStep) {
    if (!validateCurrentStep(currentStep)) return;
  }

  currentStep = step;

  // Hide all panels, reveal active panel
  for (let i = 1; i <= 5; i++) {
    const p = document.getElementById('stepPanel' + i);
    const tab = document.getElementById('stepTab' + i);
    const ind = tab.querySelector('.step-indicator');
    const lbl = tab.querySelector('.step-label');

    if (i === step) {
      p.classList.remove('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white ring-4 ring-amber-100';
      ind.innerText = i;
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-amber-700 truncate w-full px-0.5';
    } else if (i < step) {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-emerald-600 text-white';
      ind.innerHTML = '<i class="fa-solid fa-check text-[10px] sm:text-xs"></i>';
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-emerald-700 truncate w-full px-0.5';
    } else {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500';
      ind.innerText = i;
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5';
    }
  }

  // Update Progress Bar percentage (20% to 100%)
  const pct = (step / 5) * 100;
  document.getElementById('stepProgressBar').style.width = pct + '%';

  window.scrollTo({ top: document.getElementById('sellerRegForm').offsetTop - 80, behavior: 'smooth' });
}

function validateCurrentStep(step) {
  if (step === 1) {
    const ownerName = document.getElementById('owner_name').value.trim();
    const shopName = document.getElementById('shop_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.replace(/\D/g, '');
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!ownerName) {
      alert('Please enter the Owner / Artisan full name.');
      document.getElementById('owner_name').focus();
      return false;
    }
    if (!shopName) {
      alert('Please enter your Workshop / Brand / Shop name.');
      document.getElementById('shop_name').focus();
      return false;
    }
    if (!email || !email.includes('@')) {
      alert('Please enter a valid email address.');
      document.getElementById('email').focus();
      return false;
    }
    if (!/^[6-9]\d{9}$/.test(phone)) {
      alert('Please enter a valid 10-digit Indian mobile number.');
      document.getElementById('phone').focus();
      return false;
    }
    if (password.length < 10) {
      alert('Password must be at least 10 characters long.');
      document.getElementById('password').focus();
      return false;
    }
    if (password !== confirmPassword) {
      alert('Password and Confirm Password do not match.');
      document.getElementById('confirm_password').focus();
      return false;
    }
  } else if (step === 2) {
    const address = document.getElementById('address').value.trim();
    const village = document.getElementById('village').value.trim();
    const district = document.getElementById('district').value.trim();
    const pincode = document.getElementById('pincode').value.trim();

    if (address.length < 5) {
      alert('Please enter a complete workshop street address (at least 5 characters).');
      document.getElementById('address').focus();
      return false;
    }
    if (!village) {
      alert('Please enter your village, town, or gram panchayat.');
      document.getElementById('village').focus();
      return false;
    }
    if (!district) {
      alert('Please enter your district or city.');
      document.getElementById('district').focus();
      return false;
    }
    if (!/^\d{6}$/.test(pincode)) {
      alert('Please enter a valid 6-digit Indian PIN code.');
      document.getElementById('pincode').focus();
      return false;
    }
  } else if (step === 4) {
    const acct = document.getElementById('bank_account').value.trim();
    const acctConfirm = document.getElementById('bank_account_confirm').value.trim();
    if (acct && acctConfirm && acct !== acctConfirm) {
      alert('Bank Account Number and Confirmation Account Number do not match.');
      document.getElementById('bank_account_confirm').focus();
      return false;
    }
  }
  return true;
}

function validateAndGoToStep(nextStep) {
  if (validateCurrentStep(nextStep - 1)) {
    goToStep(nextStep);
  }
}

// Auto-populate Beneficiary name from Owner name if left blank
document.getElementById('owner_name').addEventListener('blur', function() {
  const b = document.getElementById('bank_beneficiary');
  if (b && !b.value.trim()) {
    b.value = this.value.trim();
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
