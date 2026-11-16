<?php
require_once __DIR__ . '/includes/functions.php';

// Fetch Google & Facebook Auth Settings
$db_google_client_id = get_site_setting('google_client_id', '');
$google_client_id = !empty($db_google_client_id)
    ? trim($db_google_client_id)
    : (defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID) ? trim(GOOGLE_CLIENT_ID) : (getenv('GOOGLE_CLIENT_ID') ?: ''));

$db_google_enabled = get_site_setting('google_auth_enabled', '1');
$google_enabled = !empty($google_client_id) && ($db_google_enabled == '1' || (defined('GOOGLE_AUTH_ENABLED') && GOOGLE_AUTH_ENABLED));

$db_facebook_id = get_site_setting('facebook_app_id', '');
$facebook_app_id = !empty($db_facebook_id)
    ? trim($db_facebook_id)
    : (defined('FACEBOOK_APP_ID') && !empty(FACEBOOK_APP_ID) ? trim(FACEBOOK_APP_ID) : (getenv('FACEBOOK_APP_ID') ?: ''));

$db_facebook_enabled = get_site_setting('facebook_auth_enabled', '0');
$facebook_enabled = !empty($facebook_app_id) && ($db_facebook_enabled == '1' || (defined('FACEBOOK_AUTH_ENABLED') && FACEBOOK_AUTH_ENABLED));

$name = '';
$phone = '';
$email = '';
$city = 'Hardoi';
$pincode = '241001';
$address = '';
$business_name = '';
$business_type = 'Kirana & Grocery Store';
$gstin = '';
$godown_address = '';
$is_retailer = (!empty($_GET['type']) && $_GET['type'] === 'retailer') || (!empty($_GET['role']) && $_GET['role'] === 'retailer');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $city = clean_input($_POST['city'] ?? 'Hardoi');
        $pincode = clean_input($_POST['pincode'] ?? '241001');
        $address = clean_input($_POST['address'] ?? '');
        
        $is_retailer = !empty($_POST['is_retailer']) || (isset($_POST['role']) && $_POST['role'] === 'retailer');
        $business_name = clean_input($_POST['business_name'] ?? '');
        $business_type = clean_input($_POST['business_type'] ?? 'Kirana & Grocery Store');
        $gstin = strtoupper(clean_input($_POST['gstin'] ?? ''));
        $godown_address = clean_input($_POST['godown_address'] ?? '');

        // Rate limiting and CAPTCHA
        $client_ip = get_client_ip();
        if (!check_rate_limit('register_ip', $client_ip, 8, 3600) || !check_rate_limit('register_phone', $phone, 5, 3600)) {
            $error = 'Too many registration attempts. Please try again after 1 hour.';
        } elseif (!verify_turnstile_captcha()) {
            $error = 'CAPTCHA security verification failed. Please try again.';
        } else {
            $pwd_err = '';
            if (empty($name) || empty($phone) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields marked with an asterisk (*).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
                $error = 'Please enter a valid 10-digit mobile number.';
            } elseif ($password !== $confirm_password) {
                $error = 'Password and confirmation password do not match.';
            } elseif (!is_strong_password($password, $pwd_err)) {
                $error = $pwd_err ?: 'Password must be at least 10 characters with uppercase, lowercase, number, and special character.';
            } else {
                try {
                    // Check existing user
                    $stmt = $pdo->prepare("SELECT id, status FROM users WHERE email = ? OR phone = ?");
                    $stmt->execute([$email, $phone]);
                    $existing = $stmt->fetch();
                    if ($existing) {
                        $error = 'An account with this email address or mobile number already exists. Please sign in instead.';
                    } else {
                        $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
                        $username = $base_username . random_int(10, 99);
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        if ($is_retailer) {
                            $role = 'retailer';
                            $unique_code = 'GGH-RET-' . random_int(10000, 99999);
                            if (empty($business_name)) {
                                $business_name = $name . ' Trading Co.';
                            }
                            $final_b_address = !empty($godown_address) ? $godown_address : $address;

                            $insert = $pdo->prepare("
                                INSERT INTO users (unique_code, name, username, email, phone, password, role, city, pincode, address, business_name, gstin, business_type, business_address, status)
                                VALUES (?, ?, ?, ?, ?, ?, 'retailer', ?, ?, ?, ?, ?, ?, ?, 'pending')
                            ");
                            $insert->execute([$unique_code, $name, $username, $email, $phone, $hashed_password, $city, $pincode, $address, $business_name, $gstin, $business_type, $final_b_address]);
                        } else {
                            $role = 'customer';
                            $unique_code = 'GGH-CUST-' . random_int(10000, 99999);

                            $insert = $pdo->prepare("
                                INSERT INTO users (unique_code, name, username, email, phone, password, role, city, pincode, address, status)
                                VALUES (?, ?, ?, ?, ?, ?, 'customer', ?, ?, ?, 'pending')
                            ");
                            $insert->execute([$unique_code, $name, $username, $email, $phone, $hashed_password, $city, $pincode, $address]);
                        }

                        $user_id = $pdo->lastInsertId();

                        // Send Email Verification with OTP & One-Click Link
                        send_email_verification_link($user_id, $email, $name, $phone);

                        // Set pending session variables for verification step
                        $_SESSION['pending_verification_user_id'] = $user_id;
                        $_SESSION['pending_verification_email'] = $email;
                        $_SESSION['pending_verification_phone'] = $phone;
                        $_SESSION['pending_verification_name'] = $name;
                        $_SESSION['pending_verification_role'] = $role;

                        set_flash('info', 'Please verify your email address to complete registration. A 6-digit OTP has been sent to ' . htmlspecialchars($email) . '.');
                        header('Location: /verify-signup.php');
                        exit;
                    }
                } catch (Exception $e) {
                    $error = 'Registration system error: ' . $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'Create an Account — GanvGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[85vh] py-12 flex items-center justify-center">
  <div class="max-w-xl w-full mx-4">
    
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-user-plus text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Create an Account</h1>
      <p class="text-xs text-gray-500 mt-1">Authentic farm staples, hand-forged tools, and village handicrafts directly to your doorstep</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
          <i class="fa-solid fa-circle-exclamation flex-shrink-0 text-base"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form action="/register.php" method="POST" class="space-y-4">
        <?= csrf_field() ?>

        <!-- Retailer B2B Option Checkbox (Unified Registration) -->
        <div class="p-3.5 bg-amber-50/70 border border-amber-300 rounded-2xl transition-all">
          <label class="flex items-start gap-3 cursor-pointer select-none">
            <input type="checkbox" name="is_retailer" id="is_retailer" value="1" <?= $is_retailer ? 'checked' : '' ?>
              class="mt-0.5 h-4 w-4 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-black text-amber-950 uppercase tracking-wide">
                  Register as Wholesale Retailer / Shopkeeper (B2B Bulk Buyer)
                </span>
                <span class="px-1.5 py-0.5 bg-amber-200 text-amber-900 text-[10px] font-bold rounded">B2B</span>
              </div>
              <p class="text-[11px] text-amber-800/90 mt-0.5 leading-relaxed">
                Check this box to unlock tiered wholesale prices, direct village manufacturer supply, bulk carton delivery, and commercial GST invoices.
              </p>
            </div>
          </label>

          <!-- Expandable Retailer Business Information -->
          <div id="retailer_fields" class="mt-3.5 pt-3 border-t border-amber-200/80 space-y-3" style="<?= $is_retailer ? 'display: block;' : 'display: none;' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-amber-950 mb-1">Business / Shop Name <span class="text-red-500">*</span></label>
                <input type="text" name="business_name" id="business_name" placeholder="e.g. Sharma General Store" value="<?= htmlspecialchars($business_name) ?>"
                  class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-amber-950 mb-1">Trade Category</label>
                <select name="business_category" class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                  <option value="Kirana & Grocery Store" <?= $business_type === 'Kirana & Grocery Store' ? 'selected' : '' ?>>Kirana & Grocery Store</option>
                  <option value="Handloom & Garments Retail" <?= $business_type === 'Handloom & Garments Retail' ? 'selected' : '' ?>>Handloom & Garments Retail</option>
                  <option value="Organic & Health Food Outlet" <?= $business_type === 'Organic & Health Food Outlet' ? 'selected' : '' ?>>Organic & Health Food Outlet</option>
                  <option value="Hardware & Agro Tools Dealer" <?= $business_type === 'Hardware & Agro Tools Dealer' ? 'selected' : '' ?>>Hardware & Agro Tools Dealer</option>
                  <option value="Hotel / Restaurant / Catering (HoReCa)" <?= $business_type === 'Hotel / Restaurant / Catering (HoReCa)' ? 'selected' : '' ?>>Hotel / Restaurant / Catering (HoReCa)</option>
                  <option value="Other Wholesale Trader" <?= $business_type === 'Other Wholesale Trader' ? 'selected' : '' ?>>Other Wholesale Trader</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-amber-950 mb-1">GSTIN (Optional, for Input Tax Credit)</label>
                <input type="text" name="gstin" maxlength="15" placeholder="15-digit GSTIN (e.g. 09ABCDE1234F1Z5)" value="<?= htmlspecialchars($gstin) ?>"
                  class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs uppercase focus:outline-none focus:ring-2 focus:ring-amber-500">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-amber-950 mb-1">Godown / Commercial Delivery Point</label>
                <input type="text" name="godown_address" placeholder="Commercial godown / unloading address" value="<?= htmlspecialchars($godown_address) ?>"
                  class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
              </div>
            </div>
          </div>
        </div>
        
        <!-- Name -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" required placeholder="e.g. Anuj Kumar" value="<?= htmlspecialchars($name) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
        </div>

        <!-- Phone & Email Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Number <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit number" value="<?= htmlspecialchars($phone) ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
            <input type="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($email) ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Passwords -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="10" placeholder="Min 10 characters (Strong)"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
            <input type="password" name="confirm_password" required minlength="10" placeholder="Re-enter password"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Delivery Address -->
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Delivery Address</label>
          <textarea name="address" rows="2" placeholder="House/Flat number, street/locality, landmark..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($address) ?></textarea>
        </div>

        <!-- City & Pincode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">City / District</label>
            <input type="text" name="city" value="<?= htmlspecialchars($city) ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">PIN Code</label>
            <input type="text" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?= htmlspecialchars($pincode) ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
          </div>
        </div>

        <!-- Security CAPTCHA -->
        <?= render_turnstile_widget() ?>

        <!-- Submit Button -->
        <button type="submit" id="submitBtn"
          class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-3">
          <i class="fa-solid fa-check"></i>
          <span id="submitBtnText">Create Account & Continue</span>
        </button>
      </form>

      <!-- Social Logins (Google & Facebook) -->
      <?php if ($google_enabled || $facebook_enabled): ?>
        <div class="mt-6 pt-5 border-t border-gray-100">
          <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="flex-shrink mx-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Or Sign Up With Social</span>
            <div class="flex-grow border-t border-gray-200"></div>
          </div>

          <div class="mt-2 flex flex-col gap-2.5">
            <?php if ($google_enabled && !empty($google_client_id)): ?>
              <div id="g_id_onload"
                   data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                   data-login_uri="<?= rtrim(defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com', '/') ?>/auth/google-callback.php"
                   data-auto_prompt="false">
              </div>
              <div class="g_id_signin w-full flex justify-center"
                   data-type="standard"
                   data-size="large"
                   data-theme="outline"
                   data-text="signup_with"
                   data-shape="rectangular"
                   data-logo_alignment="left"
                   data-width="100%">
              </div>
            <?php endif; ?>

            <?php if ($facebook_enabled && !empty($facebook_app_id)): ?>
              <a href="https://www.facebook.com/v18.0/dialog/oauth?client_id=<?= urlencode($facebook_app_id) ?>&redirect_uri=<?= urlencode((defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://ganvgalihat.com') . '/auth/facebook-callback.php') ?>&scope=email,public_profile"
                 class="w-full py-2.5 px-4 bg-[#1877F2] hover:bg-[#166fe5] text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-xs">
                <i class="fa-brands fa-facebook-f text-sm"></i>
                <span>Sign up with Facebook</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="mt-6 pt-5 border-t border-gray-200 text-center text-xs text-gray-600">
        Already have an account? <a href="/login.php" class="text-brand-green font-bold hover:underline">Sign In here</a>
      </div>
    </div>

  </div>
</div>

<script>
const isRetailerCb = document.getElementById('is_retailer');
const retailerFields = document.getElementById('retailer_fields');
const submitBtnText = document.getElementById('submitBtnText');

function updateRetailerState() {
  if (isRetailerCb && retailerFields) {
    if (isRetailerCb.checked) {
      retailerFields.style.display = 'block';
      if (submitBtnText) submitBtnText.innerText = 'Register Wholesale Retailer Account & Continue';
    } else {
      retailerFields.style.display = 'none';
      if (submitBtnText) submitBtnText.innerText = 'Create Account & Continue';
    }
  }
}

if (isRetailerCb) {
  isRetailerCb.addEventListener('change', updateRetailerState);
  updateRetailerState();
}

function onGoogleAuthCallback(response) {
  if (response && response.credential) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= rtrim(defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com', '/') ?>/auth/google-callback.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'credential';
    input.value = response.credential;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}
</script>

<?php if ($google_enabled): ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
