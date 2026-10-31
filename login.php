<?php
/**
 * GaunGaliHat Secure Unified Login Gateway
 */

require_once __DIR__ . '/includes/functions.php';

$redirect = clean_input($_GET['redirect'] ?? '');
$active_tab = clean_input($_GET['tab'] ?? 'shopper'); // shopper, seller, staff
if ($active_tab === 'admin') {
    $active_tab = 'staff';
}

// If already logged in, redirect away from login page
if (is_logged_in()) {
    $logged_user = get_logged_in_user();
    if ($logged_user) {
        if (!empty($redirect) && strpos($redirect, 'http') === false) {
            header('Location: /' . ltrim($redirect, '/'));
            exit;
        }
        if (in_array($logged_user['role'], ['super_admin', 'admin'])) {
            header('Location: /admin/index.php');
            exit;
        } elseif ($logged_user['role'] === 'vendor') {
            header('Location: /seller/dashboard.php');
            exit;
        } else {
            header('Location: /account.php');
            exit;
        }
    }
}

$error = '';
$show_2fa_input = false;
$cached_identifier = '';

// Fetch Google Auth Settings
$google_enabled = false;
$google_client_id = '';
try {
    $stmt_g = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('google_auth_enabled', 'google_client_id')");
    while ($r = $stmt_g->fetch()) {
        if ($r['setting_key'] === 'google_auth_enabled') $google_enabled = ($r['setting_value'] == '1');
        if ($r['setting_key'] === 'google_client_id') $google_client_id = $r['setting_value'];
    }
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Item 15: CSRF Token Check on Login Form
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'सुरक्षा टोकन अमान्य है। कृपया पृष्ठ रीफ़्रेश करके पुनः प्रयास करें। (Invalid security token)';
    } else {
        $identifier = clean_input($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $tab = clean_input($_POST['portal_tab'] ?? 'shopper');
        $two_factor_code = clean_input($_POST['two_factor_code'] ?? '');
        
        $role_group = null;
        if ($tab === 'seller') $role_group = 'seller';
        if ($tab === 'staff') $role_group = 'admin';

        $result = authenticate_user($identifier, $password, $role_group, $two_factor_code);
        if ($result['success']) {
            $user = $result['user'];
            set_flash('success', 'नमस्ते ' . htmlspecialchars($user['name']) . ', आपका स्वागत है!');

            if (!empty($redirect) && strpos($redirect, 'http') === false) {
                header('Location: /' . ltrim($redirect, '/'));
                exit;
            }

            // Direct according to role
            if (in_array($user['role'], ['super_admin', 'admin'])) {
                header('Location: /admin/index.php');
                exit;
            } elseif ($user['role'] === 'vendor') {
                header('Location: /seller/dashboard.php');
                exit;
            } else {
                header('Location: /account.php');
                exit;
            }
        } else {
            $error = $result['error'];
            $active_tab = $tab;
            if (!empty($result['require_2fa'])) {
                $show_2fa_input = true;
                $cached_identifier = $identifier;
            }
        }
    }
}

// Render Header only after all POST and redirect logic has executed
$page_title = 'खाते में लॉगिन करें — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[85vh] py-12 flex items-center justify-center">
  <div class="max-w-md w-full mx-4">
    
    <!-- Brand Emblem -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-wheat-awn text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Sign In (खाते में प्रवेश करें)</h1>
      <p class="text-xs text-gray-500 mt-1">गाँव गली हाट • Kalyani agrofood pvt ltd</p>
    </div>

    <!-- Login Box Card -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl overflow-hidden">
      
      <!-- Tab Header: Shopper, Artisan/Seller, Staff -->
      <div class="grid grid-cols-3 border-b border-gray-200 bg-stone-100 text-center text-xs font-bold text-gray-500">
        <button type="button" onclick="switchLoginTab('shopper')" id="tab-btn-shopper"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'shopper' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-bag-shopping block text-sm mb-1"></i>
          Customer Login (ग्राहक)
        </button>
        <button type="button" onclick="switchLoginTab('seller')" id="tab-btn-seller"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'seller' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-store block text-sm mb-1"></i>
          Seller / Artisan (विक्रेता)
        </button>
        <button type="button" onclick="switchLoginTab('staff')" id="tab-btn-staff"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'staff' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-shield-halved block text-sm mb-1"></i>
          Staff & Admin (स्टाफ)
        </button>
      </div>

      <div class="p-6 sm:p-8">
        <?php if (!empty($error)): ?>
          <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2.5">
            <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <!-- Active Portal Banner -->
        <div id="portal-banner" class="mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 <?= $active_tab === 'staff' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : ($active_tab === 'seller' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200') ?>">
          <i id="portal-banner-icon" class="fa-solid <?= $active_tab === 'staff' ? 'fa-user-tie' : ($active_tab === 'seller' ? 'fa-hammer' : 'fa-basket-shopping') ?>"></i>
          <span id="portal-banner-text">
            <?= $active_tab === 'staff' ? 'Staff & Administrative Portal (स्टाफ एवं प्रशासनिक नियंत्रण पोर्टल)' : ($active_tab === 'seller' ? 'Artisan & Seller Portal (कारीगर व विक्रेता पोर्टल)' : 'Customer Shopping Account (ग्राहक शॉपिंग खाता)') ?>
          </span>
        </div>

        <form action="/login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" method="POST" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="portal_tab" id="portal_tab_input" value="<?= htmlspecialchars($active_tab) ?>">

          <?php if ($show_2fa_input): ?>
            <!-- Item 15: TOTP 2FA Verification Field -->
            <div class="p-3 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 mb-2">
              <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-800">
                <i class="fa-solid fa-shield-halved"></i>
                Two-Factor Authentication Required (2FA)
              </div>
              <span>कृपया अपने Google Authenticator या 2FA ऍप से 6-अंकीय कोड दर्ज करें।</span>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                2FA Verification Code (6-अंकीय सुरक्षा कोड) <span class="text-red-500">*</span>
              </label>
              <input type="text" name="two_factor_code" autofocus pattern="[0-9]{6}" maxlength="6" placeholder="000000"
                class="w-full text-center tracking-widest text-lg font-mono px-3.5 py-2.5 bg-amber-50 border border-amber-300 rounded-xl focus:bg-white focus:border-brand-green focus:outline-none transition">
            </div>
          <?php endif; ?>

          <!-- Username/Email/Phone -->
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1" id="identifier-label">
              <?= $active_tab === 'staff' ? 'Admin Username or Official Email (एडमिन यूजरनेम / ईमेल)' : ($active_tab === 'seller' ? 'Seller Username / Email / Mobile (विक्रेता यूजरनेम / ईमेल / मोबाइल)' : 'Email Address / Mobile / Username (ईमेल / मोबाइल नंबर / यूजरनेम)') ?>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-user text-xs"></i>
              </span>
              <input type="text" name="identifier" required autofocus value="<?= htmlspecialchars($cached_identifier) ?>"
                placeholder="उदा. mobile या email"
                class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
            </div>
          </div>

          <!-- Password with Eye Toggle -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold text-gray-700">Password (पासवर्ड)</label>
              <a href="/forgot-password.php" class="text-[11px] text-brand-green hover:underline">Forgot Password? (पासवर्ड भूल गए?)</a>
            </div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-lock text-xs"></i>
              </span>
              <input type="password" name="password" id="loginPasswordInput" required
                placeholder="••••••••"
                class="w-full pl-10 pr-10 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              <button type="button" onclick="togglePasswordVisibility('loginPasswordInput', this)"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fa-regular fa-eye text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Remember me -->
          <div class="flex items-center justify-between text-xs text-gray-600 pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="remember" class="rounded text-brand-green focus:ring-brand-green">
              <span>Remember me (मुझे याद रखें)</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit"
            class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-2">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span id="submit-btn-label">
              <?= $active_tab === 'staff' ? 'Sign In as Staff (स्टाफ लॉगिन करें)' : ($active_tab === 'seller' ? 'Open Seller Dashboard (विक्रेता डैशबोर्ड खोलें)' : 'Sign In (लॉगिन करें)') ?>
            </span>
          </button>
        </form>

        <!-- Social Sign-in Block (For Shoppers) -->
        <?php if ($google_enabled): ?>
          <div id="google-auth-container" class="<?= $active_tab === 'shopper' ? 'block' : 'hidden' ?> mt-5 pt-4 border-t border-gray-100">
            <div class="relative flex py-2 items-center">
              <div class="flex-grow border-t border-gray-200"></div>
              <span class="flex-shrink mx-3 text-[11px] font-bold text-gray-400 uppercase">Or Sign In with Google (या Google से लॉगिन करें)</span>
              <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <div class="mt-2 flex flex-col items-center justify-center space-y-2">
              <div id="g_id_onload"
                   data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                   data-login_uri="/auth/google-callback.php"
                   data-auto_prompt="false">
              </div>
              <div class="g_id_signin w-full flex justify-center"
                   data-type="standard"
                   data-size="large"
                   data-theme="outline"
                   data-text="sign_in_with"
                   data-shape="rectangular"
                   data-logo_alignment="left"
                   data-width="100%">
              </div>
              
              <a href="https://accounts.google.com/o/oauth2/v2/auth?client_id=<?= urlencode($google_client_id) ?>&redirect_uri=<?= urlencode(SITE_URL . '/auth/google-callback.php') ?>&response_type=code&scope=openid%20profile%20email"
                 class="w-full py-2.5 px-4 bg-white border border-gray-300 hover:bg-stone-50 text-gray-700 font-bold rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-xs">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-4 h-4" alt="Google">
                <span>Sign in with Google (Google खाते से प्रवेश)</span>
              </a>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Footer Action Links -->
      <div class="bg-stone-50 px-6 py-4 border-t border-gray-200 text-center text-xs text-gray-600 space-y-1.5">
        <div id="shopper-footer-links">
          Don't have an account? <a href="/register.php" class="text-brand-green font-bold hover:underline">Register as Customer (नया ग्राहक पंजीकरण करें)</a>
        </div>
        <div>
          Are you a rural artisan or SHG? <a href="/seller-register.php" class="text-amber-800 font-bold hover:underline">Register as Seller (कारीगर विक्रेता के रूप में जुड़ें)</a>
        </div>
      </div>

    </div>

  </div>
</div>

<script>
function switchLoginTab(tab) {
  const tabs = ['shopper', 'seller', 'staff'];
  tabs.forEach(t => {
    const btn = document.getElementById('tab-btn-' + t);
    if (t === tab) {
      btn.className = 'py-3.5 px-2 transition border-b-2 border-brand-green text-brand-green bg-white';
    } else {
      btn.className = 'py-3.5 px-2 transition border-b-2 border-transparent hover:text-gray-800';
    }
  });

  document.getElementById('portal_tab_input').value = tab;

  const banner = document.getElementById('portal-banner');
  const icon = document.getElementById('portal-banner-icon');
  const text = document.getElementById('portal-banner-text');
  const btnLabel = document.getElementById('submit-btn-label');
  const idLabel = document.getElementById('identifier-label');

  if (tab === 'staff') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-indigo-50 text-indigo-900 border border-indigo-200';
    icon.className = 'fa-solid fa-user-tie';
    text.innerText = 'Staff & Administrative Portal (स्टाफ एवं प्रशासनिक नियंत्रण पोर्टल)';
    btnLabel.innerText = 'Sign In as Staff (स्टाफ लॉगिन करें)';
    idLabel.innerText = 'Admin Username or Official Email (एडमिन यूजरनेम / ईमेल)';
  } else if (tab === 'seller') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-amber-50 text-amber-900 border border-amber-200';
    icon.className = 'fa-solid fa-hammer';
    text.innerText = 'Artisan & Seller Portal (कारीगर व विक्रेता पोर्टल)';
    btnLabel.innerText = 'Open Seller Dashboard (विक्रेता डैशबोर्ड खोलें)';
    idLabel.innerText = 'Seller Username / Email / Mobile (विक्रेता यूजरनेम / ईमेल / मोबाइल)';
  } else {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-emerald-50 text-emerald-900 border border-emerald-200';
    icon.className = 'fa-solid fa-basket-shopping';
    text.innerText = 'Customer Shopping Account (ग्राहक शॉपिंग खाता)';
    btnLabel.innerText = 'Sign In (लॉगिन करें)';
    idLabel.innerText = 'Email Address / Mobile / Username (ईमेल / मोबाइल नंबर / यूजरनेम)';
  }

  const googleBox = document.getElementById('google-auth-container');
  if (googleBox) {
    if (tab === 'shopper') {
      googleBox.classList.remove('hidden');
    } else {
      googleBox.classList.add('hidden');
    }
  }
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}
</script>

<?php if ($google_enabled): ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
