<?php
/**
 * GanvGaliHat Secure Unified Login Gateway
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

// Fetch Google & Facebook Auth Settings
// Fetch Google & Facebook Auth Settings (DB Site Settings first, then env/constants)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Check on Login Form
    $csrf = $_POST['csrf_token'] ?? '';
    if (!csrf_check($csrf)) {
        $error = 'Security session expired. Please refresh the page and try again.';
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
            set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

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
            if (!empty($result['require_verification'])) {
                set_flash('info', 'Please verify your 6-digit email OTP to activate your account.');
                header('Location: /verify-signup.php');
                exit;
            }
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
$page_title = 'Sign In — GanvGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-50 min-h-[85vh] py-12 flex items-center justify-center">
  <div class="max-w-md w-full mx-4">
    
    <!-- Brand Emblem -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-green text-white shadow-lg mb-3">
        <i class="fa-solid fa-wheat-awn text-2xl text-amber-300"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-gray-900">Sign In to Your Account</h1>
      <p class="text-xs text-gray-500 mt-1">GanvGaliHat • Kalyani agrofood pvt ltd</p>
    </div>

    <!-- Login Box Card -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xl overflow-hidden">
      
      <!-- Tab Header: Shopper, Artisan/Seller, Staff -->
      <div class="grid grid-cols-3 border-b border-gray-200 bg-stone-100 text-center text-xs font-bold text-gray-500">
        <button type="button" onclick="switchLoginTab('shopper')" id="tab-btn-shopper"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'shopper' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-bag-shopping block text-sm mb-1"></i>
          Customer
        </button>
        <button type="button" onclick="switchLoginTab('seller')" id="tab-btn-seller"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'seller' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-store block text-sm mb-1"></i>
          Seller / Artisan
        </button>
        <button type="button" onclick="switchLoginTab('staff')" id="tab-btn-staff"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'staff' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-shield-halved block text-sm mb-1"></i>
          Staff & Admin
        </button>
      </div>

      <div class="p-6 sm:p-8">
        <?php 
        $flash_msg = get_flash();
        $display_error = !empty($error) ? $error : ($flash_msg && $flash_msg['type'] === 'error' ? $flash_msg['message'] : '');
        $display_success = $flash_msg && $flash_msg['type'] === 'success' ? $flash_msg['message'] : '';
        $display_info = $flash_msg && $flash_msg['type'] === 'info' ? $flash_msg['message'] : '';
        ?>

        <?php if (!empty($display_error)): ?>
          <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2.5">
            <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0"></i>
            <span><?= htmlspecialchars($display_error) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($display_success)): ?>
          <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-base flex-shrink-0"></i>
            <span><?= htmlspecialchars($display_success) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($display_info)): ?>
          <div class="mb-5 p-3.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs flex items-center gap-2.5">
            <i class="fa-solid fa-circle-info text-base flex-shrink-0"></i>
            <span><?= htmlspecialchars($display_info) ?></span>
          </div>
        <?php endif; ?>

        <!-- Active Portal Banner -->
        <div id="portal-banner" class="mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 <?= $active_tab === 'staff' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : ($active_tab === 'seller' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200') ?>">
          <i id="portal-banner-icon" class="fa-solid <?= $active_tab === 'staff' ? 'fa-user-tie' : ($active_tab === 'seller' ? 'fa-hammer' : 'fa-basket-shopping') ?>"></i>
          <span id="portal-banner-text">
            <?= $active_tab === 'staff' ? 'Staff & Administrative Control Portal' : ($active_tab === 'seller' ? 'Artisan Workshop & Seller Central Portal' : 'Customer Shopping & Orders Account') ?>
          </span>
        </div>

        <form action="/login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" method="POST" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="portal_tab" id="portal_tab_input" value="<?= htmlspecialchars($active_tab) ?>">

          <?php if ($show_2fa_input): ?>
            <!-- TOTP 2FA Verification Field -->
            <div class="p-3 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 mb-2">
              <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-800">
                <i class="fa-solid fa-shield-halved"></i>
                Two-Factor Authentication Required (2FA)
              </div>
              <span>Please enter the 6-digit code from your authenticator app.</span>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1">
                2FA Verification Code <span class="text-red-500">*</span>
              </label>
              <input type="text" name="two_factor_code" autofocus pattern="[0-9]{6}" maxlength="6" placeholder="000000"
                class="w-full text-center tracking-widest text-lg font-mono px-3.5 py-2.5 bg-amber-50 border border-amber-300 rounded-xl focus:bg-white focus:border-brand-green focus:outline-none transition">
            </div>
          <?php endif; ?>

          <!-- Username/Email/Phone -->
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1" id="identifier-label">
              <?= $active_tab === 'staff' ? 'Admin Username or Official Email' : ($active_tab === 'seller' ? 'Seller Username / Email / Mobile' : 'Email Address or Mobile Number') ?>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fa-regular fa-user text-xs"></i>
              </span>
              <input type="text" name="identifier" required autofocus value="<?= htmlspecialchars($cached_identifier) ?>"
                placeholder="e.g. mobile or email address"
                class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
            </div>
          </div>

          <!-- Password with Eye Toggle -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold text-gray-700">Password</label>
              <a href="/forgot-password.php" class="text-[11px] text-brand-green hover:underline">Forgot Password?</a>
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
              <span>Remember me</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit"
            class="w-full py-3 bg-brand-green text-white font-bold rounded-xl shadow-md hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm mt-2">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span id="submit-btn-label">
              <?= $active_tab === 'staff' ? 'Sign In as Staff' : ($active_tab === 'seller' ? 'Open Seller Dashboard' : 'Sign In') ?>
            </span>
          </button>
        </form>

        <!-- Social Sign-in Block (For Shoppers) -->
        <?php if ($google_enabled || $facebook_enabled): ?>
          <div id="google-auth-container" class="<?= $active_tab === 'shopper' ? 'block' : 'hidden' ?> mt-5 pt-4 border-t border-gray-100">
            <div class="relative flex py-2 items-center">
              <div class="flex-grow border-t border-gray-200"></div>
              <span class="flex-shrink mx-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Or Sign In with Social</span>
              <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <div class="mt-2 flex flex-col gap-2.5">
              <?php if ($google_enabled && !empty($google_client_id)): ?>
                <!-- Official Single Google Sign In Button -->
                <div id="g_id_onload"
                     data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                     data-login_uri="<?= rtrim(defined('SITE_URL') ? SITE_URL : 'https://ganvgalihat.com', '/') ?>/auth/google-callback.php"
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
              <?php endif; ?>

              <?php if ($facebook_enabled && !empty($facebook_app_id)): ?>
                <a href="https://www.facebook.com/v18.0/dialog/oauth?client_id=<?= urlencode($facebook_app_id) ?>&redirect_uri=<?= urlencode((defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://ganvgalihat.com') . '/auth/facebook-callback.php') ?>&scope=email,public_profile"
                   class="w-full py-2.5 px-4 bg-[#1877F2] hover:bg-[#166fe5] text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2 shadow-xs">
                  <i class="fa-brands fa-facebook-f text-sm"></i>
                  <span>Sign in with Facebook</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Footer Action Links -->
      <div class="bg-stone-50 px-6 py-4 border-t border-gray-200 text-center text-xs text-gray-600 space-y-1.5">
        <div id="shopper-footer-links">
          Don't have an account? <a href="/register.php" class="text-brand-green font-bold hover:underline">Register as Customer</a>
        </div>
        <div>
          Are you a rural artisan or producer? <a href="/seller-register.php" class="text-amber-800 font-bold hover:underline">Register as Seller</a>
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
    text.innerText = 'Staff & Administrative Control Portal';
    btnLabel.innerText = 'Sign In as Staff';
    idLabel.innerText = 'Admin Username or Official Email';
  } else if (tab === 'seller') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-amber-50 text-amber-900 border border-amber-200';
    icon.className = 'fa-solid fa-hammer';
    text.innerText = 'Artisan Workshop & Seller Central Portal';
    btnLabel.innerText = 'Open Seller Dashboard';
    idLabel.innerText = 'Seller Username / Email / Mobile';
  } else {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-emerald-50 text-emerald-900 border border-emerald-200';
    icon.className = 'fa-solid fa-basket-shopping';
    text.innerText = 'Customer Shopping & Orders Account';
    btnLabel.innerText = 'Sign In';
    idLabel.innerText = 'Email Address or Mobile Number';
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
