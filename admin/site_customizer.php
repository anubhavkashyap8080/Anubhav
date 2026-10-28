<?php
/**
 * GaunGaliHat Header & Footer Live Customizer
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save Customization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_customizer'])) {
    $cust_keys = [
        'brand_name', 'company_name', 'brand_tagline', 'announcement_text',
        'show_announcement', 'contact_phone', 'contact_email',
        'factory_address', 'head_office_address', 'footer_copyright',
        'logo_url', 'favicon_url',
        'social_instagram', 'social_youtube', 'social_facebook', 'social_whatsapp',
        'custom_head_code', 'custom_css', 'custom_footer_code'
    ];

    try {
        $stmt_up = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $stmt_up = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        }

        foreach ($cust_keys as $k) {
            if (!isset($_POST[$k])) {
                continue;
            }
            $val = trim((string)$_POST[$k]);
            if ($k === 'brand_name' && (empty($val) || $val === '0')) {
                $val = 'GaunGaliHat';
            }
            $stmt_up->execute([$k, $val]);
        }

        set_flash('success', 'हेडर, फुटर, सोशल लिंक्स एवं ब्रांडिंग सेटिंग्स सफलतापूर्वक अपडेट की गईं!');
        header('Location: /admin/site_customizer.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'त्रुटि: ' . $e->getMessage());
    }
}

// Fetch current values
$stmt_set = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$cfg = [];
while ($r = $stmt_set->fetch()) {
    $cfg[$r['setting_key']] = $r['setting_value'];
}

function get_cfg_val($key, $default = '') {
    global $cfg;
    return $cfg[$key] ?? $default;
}

$page_title = 'हेडर व फुटर कस्टमाइज़र (Site Customizer)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-palette text-indigo-600"></i>
      हेडर व फुटर कस्टमाइज़र (Theme & Brand Customizer)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      घोषणा पट्टी, लोगो, हेल्पलाइन नंबर, सोशल मीडिया लिंक्स एवं फुटर पते का केंद्रीय नियंत्रण
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> लाइव स्टोर पर देखें
    </a>
  </div>
</div>

<form action="/admin/site_customizer.php" method="POST" class="space-y-6">
  <input type="hidden" name="save_customizer" value="1">

  <!-- 1. Header & Top Announcement Ticker -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-bullhorn text-amber-500"></i> शीर्ष घोषणा पट्टी (Announcement Ticker)
    </h2>

    <div class="space-y-4">
      <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-800">
          <input type="checkbox" name="show_announcement" value="1" <?= get_cfg_val('show_announcement', '1') == '1' ? 'checked' : '' ?>
            class="rounded text-indigo-600 focus:ring-indigo-600 w-4 h-4">
          <span>टॉप घोषणा पट्टी चालू रखें (Show Announcement Bar)</span>
        </label>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">घोषणा संदेश (Announcement Text) <span class="text-red-500">*</span></label>
        <input type="text" name="announcement_text" required
          value="<?= htmlspecialchars(get_cfg_val('announcement_text', 'सीधे ग्रामीण कारीगरों व कुटीर उद्योग से • ₹499+ के आर्डर पर पूरे भारत में मुफ्त डिलीवरी!')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">हेल्पलाइन फोन नंबर</label>
          <input type="text" name="contact_phone"
            value="<?= htmlspecialchars(get_cfg_val('contact_phone', '+91 7897492100')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">आधिकारिक ईमेल</label>
          <input type="email" name="contact_email"
            value="<?= htmlspecialchars(get_cfg_val('contact_email', 'namaste@ganvgalihat.com')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Brand Identity Assets -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-wheat-awn text-emerald-600"></i> ब्रांड पहचान व लोगो संपत्तियां
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य लोगो फ़ाइल पथ (Logo Path)</label>
        <div class="flex items-center gap-3">
          <input type="text" name="logo_url"
            value="<?= htmlspecialchars(get_cfg_val('logo_url', '/logo.png')) ?>"
            class="flex-1 px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <div class="h-10 px-2 bg-stone-100 rounded-lg flex items-center justify-center border border-gray-200">
            <img src="/logo.png" alt="Logo Preview" class="h-8 max-w-[80px] object-contain">
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">फेविकॉन फ़ाइल पथ (Favicon Path)</label>
        <div class="flex items-center gap-3">
          <input type="text" name="favicon_url"
            value="<?= htmlspecialchars(get_cfg_val('favicon_url', '/favicon.png')) ?>"
            class="flex-1 px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
          <div class="w-10 h-10 bg-stone-100 rounded-lg flex items-center justify-center border border-gray-200">
            <img src="/favicon.png" alt="Favicon Preview" class="w-6 h-6 object-contain">
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">ब्रांड का नाम (Brand Display Name)</label>
        <input type="text" name="brand_name"
          value="<?= htmlspecialchars(get_cfg_val('brand_name', 'गाँव गली हाट (GaunGaliHat)')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">कंपनी / मूल संस्थान (Parent Company)</label>
        <input type="text" name="company_name"
          value="<?= htmlspecialchars(get_cfg_val('company_name', 'S S Industries')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>
    </div>
  </div>

  <!-- 3. Social Media Channels -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-share-nodes text-indigo-600"></i> आधिकारिक सोशल मीडिया चैनल्स
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          <i class="fa-brands fa-instagram text-pink-600 mr-1"></i> Instagram URL
        </label>
        <input type="url" name="social_instagram"
          value="<?= htmlspecialchars(get_cfg_val('social_instagram', 'https://instagram.com/ganvgalihat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          <i class="fa-brands fa-youtube text-red-600 mr-1"></i> YouTube Channel URL
        </label>
        <input type="url" name="social_youtube"
          value="<?= htmlspecialchars(get_cfg_val('social_youtube', 'https://youtube.com/@ganvgalihat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          <i class="fa-brands fa-facebook text-blue-600 mr-1"></i> Facebook Page URL
        </label>
        <input type="url" name="social_facebook"
          value="<?= htmlspecialchars(get_cfg_val('social_facebook', 'https://facebook.com/ganvgalihat')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">
          <i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i> WhatsApp Support Link
        </label>
        <input type="url" name="social_whatsapp"
          value="<?= htmlspecialchars(get_cfg_val('social_whatsapp', 'https://wa.me/917897492100')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>
    </div>
  </div>

  <!-- 4. Footer Addresses & Legal Copyright -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
      <i class="fa-solid fa-location-dot text-red-600"></i> फुटर पते व कॉपीराइट (Footer Details)
    </h2>

    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">फ़ैक्टरी / निर्माण इकाई पता (Factory Address)</label>
        <input type="text" name="factory_address"
          value="<?= htmlspecialchars(get_cfg_val('factory_address', 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य प्लांट / कार्यालय (Industrial Plant Address)</label>
        <input type="text" name="head_office_address"
          value="<?= htmlspecialchars(get_cfg_val('head_office_address', 'G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, UP')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">कॉपीराइट पाठ (Copyright Notice)</label>
        <input type="text" name="footer_copyright"
          value="<?= htmlspecialchars(get_cfg_val('footer_copyright', '© 2026 GaunGaliHat. A Unit of S S Industries Enterprise. All Rights Reserved.')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>
    </div>
  </div>

  <!-- Submit Button -->
  <div class="flex items-center justify-end">
    <button type="submit"
      class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      कस्टमाइज़ेशन सेटिंग्स सहेजें (Save All Theme Settings)
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
