<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save Social Auth Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_social_settings'])) {
    $keys = [
        'google_auth_enabled', 'google_client_id',
        'facebook_auth_enabled', 'facebook_app_id',
        'social_auto_register'
    ];

    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        }

        foreach ($keys as $k) {
            $val = $_POST[$k] ?? '0';
            $stmt->execute([$k, trim((string)$val)]);
        }

        set_flash('success', 'सोशल लॉगिन (Google & Facebook) सेटिंग्स सफलतापूर्वक सहेज ली गईं!');
        header('Location: /admin/social_auth.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Fetch all settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$curr_settings = [];
while ($row = $stmt_settings->fetch()) {
    $curr_settings[$row['setting_key']] = $row['setting_value'];
}

function get_soc_val($key, $default = '') {
    global $curr_settings;
    return $curr_settings[$key] ?? $default;
}

// Current Host URL for display
$current_host = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://ganvgalihat.com';

$page_title = 'सोशल लॉगिन एकीकरण (Social Auth Integration) — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
        <i class="fa-brands fa-google text-red-500"></i>
        सोशल साइन-इन एकीकरण (Social Authentication)
      </h1>
      <p class="text-xs text-gray-500 mt-1">
        Google OAuth 2.0 / Google Identity Services एवं Facebook Login द्वारा 1-क्लिक लॉगिन सक्षम करें
      </p>
    </div>

    <div class="flex items-center gap-2">
      <a href="/login.php" target="_blank" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-stone-300">
        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> लॉगिन पेज पर जांचें
      </a>
    </div>
  </div>
</div>

<form action="/admin/social_auth.php" method="POST" class="space-y-8">
  <?= csrf_field() ?>
  <input type="hidden" name="save_social_settings" value="1">

  <!-- 1. Google OAuth Settings Card -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <div class="flex flex-wrap items-center justify-between pb-4 mb-6 border-b border-gray-100 gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl shadow-xs">
          <i class="fa-brands fa-google"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-gray-900">Google OAuth 2.0 (Google Identity Services)</h3>
          <p class="text-xs text-gray-500">ग्राहकों को आधिकारिक Google खाते द्वारा सुरक्षित वन-क्लिक साइन इन प्रदान करें</p>
        </div>
      </div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="google_auth_enabled" value="1" <?= get_soc_val('google_auth_enabled', '1') == '1' ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
        <span class="ml-2 text-xs font-bold text-gray-700">Google लॉगिन सक्रिय</span>
      </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Google Client ID <span class="text-red-500">*</span></label>
        <input type="text" name="google_client_id" required
          value="<?= htmlspecialchars(defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== '' ? GOOGLE_CLIENT_ID : get_soc_val('google_client_id', '')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-red-500">
        <p class="text-[11px] text-gray-400 mt-1">Google Cloud Console से प्राप्त OAuth Client ID</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Google Client Secret (Masked, Config/Env)</label>
        <input type="text" name="google_client_secret" readonly
          value="<?= htmlspecialchars(mask_secret(defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : '')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-100 border border-gray-200 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
        <p class="text-[11px] text-gray-400 mt-1">पर्यावरण चर (config/env) से सुरक्षित पढ़ा गया; डेटाबेस में संग्रहीत नहीं</p>
      </div>
    </div>

    <!-- Google Console Setup Guidance Box -->
    <div class="mt-6 p-4 bg-stone-50 border border-gray-200 rounded-xl text-xs space-y-3">
      <h4 class="font-bold text-gray-800 flex items-center gap-1.5">
        <i class="fa-solid fa-circle-info text-blue-600"></i> Google Cloud Console कॉन्फ़िगरेशन निर्देश (Instructions):
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-[11px] font-mono">
        <div class="p-2.5 bg-white rounded-lg border border-gray-200">
          <span class="text-gray-500 font-sans font-bold block mb-1">1. Authorized JavaScript origins:</span>
          <span class="text-indigo-900 select-all font-bold"><?= htmlspecialchars($current_host) ?></span>
        </div>
        <div class="p-2.5 bg-white rounded-lg border border-gray-200">
          <span class="text-gray-500 font-sans font-bold block mb-1">2. Authorized redirect URIs:</span>
          <span class="text-indigo-900 select-all font-bold"><?= htmlspecialchars($current_host) ?>/auth/google-callback.php</span>
        </div>
      </div>
      <p class="text-[11px] text-gray-500">
        Google Cloud Console (<a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-600 underline">console.cloud.google.com</a>) में जाकर OAuth 2.0 Client IDs सेक्शन में उपरोक्त URL जोड़ें।
      </p>
    </div>
  </div>

  <!-- 2. Facebook / Meta Auth Settings Card -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <div class="flex flex-wrap items-center justify-between pb-4 mb-6 border-b border-gray-100 gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-xs">
          <i class="fa-brands fa-facebook-f"></i>
        </div>
        <div>
          <h3 class="text-base font-bold text-gray-900">Facebook / Meta Login</h3>
          <p class="text-xs text-gray-500">Facebook Meta Developers SDK के माध्यम से सोशल लॉगिन</p>
        </div>
      </div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="facebook_auth_enabled" value="1" <?= (defined('FACEBOOK_AUTH_ENABLED') ? FACEBOOK_AUTH_ENABLED : (get_soc_val('facebook_auth_enabled', '0') == '1')) ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        <span class="ml-2 text-xs font-bold text-gray-700">Facebook लॉगिन सक्रिय</span>
      </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Facebook App ID</label>
        <input type="text" name="facebook_app_id"
          value="<?= htmlspecialchars(defined('FACEBOOK_APP_ID') && FACEBOOK_APP_ID !== '' ? FACEBOOK_APP_ID : get_soc_val('facebook_app_id', '')) ?>" placeholder="उदा. 109283746592817"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:border-blue-500">
        <p class="text-[11px] text-gray-400 mt-1">Meta for Developers से प्राप्त App ID</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Facebook App Secret (Masked, Config/Env)</label>
        <input type="text" name="facebook_app_secret" readonly
          value="<?= htmlspecialchars(mask_secret(defined('FACEBOOK_APP_SECRET') ? FACEBOOK_APP_SECRET : get_soc_val('facebook_app_secret', ''))) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-100 border border-gray-200 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
        <p class="text-[11px] text-gray-400 mt-1">पर्यावरण चर (config/env) से सुरक्षित पढ़ा गया; डेटाबेस में संग्रहीत नहीं</p>
      </div>
    </div>
  </div>

  <!-- 3. Policy & Behavioral Preferences -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h3 class="text-sm font-bold text-gray-900 pb-3 mb-4 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-user-plus text-brand-green"></i> खाता निर्माण नीतियां (Account Policies)
    </h3>

    <div class="space-y-3 text-xs">
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="social_auto_register" value="1" <?= get_soc_val('social_auto_register', '1') == '1' ? 'checked' : '' ?> class="rounded text-brand-green">
        <span class="text-gray-800 font-semibold">नये सोशल यूज़र्स के लिए स्वतः ग्राहक खाता बनाएं (Auto-create Shopper Account if email not found)</span>
      </label>
      <p class="text-gray-500 text-[11px] pl-6">
        यदि कोई उपयोगकर्ता पहली बार Google से साइन इन करता है, तो उनका नाम, ईमेल और प्रोफ़ाइल अवतार स्वचालित रूप से पंजीकृत हो जाएगा।
      </p>
    </div>
  </div>

  <!-- Save Button -->
  <div class="flex items-center justify-end gap-3 pb-8">
    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      सोशल सेटिंग्स सुरक्षित करें (Save Social Auth Settings)
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
