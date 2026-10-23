<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

// Handle Save Hero Customizer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero_settings'])) {
    $keys = [
        'hero_festive_preset', 'hero_theme_color', 'hero_badge_text',
        'hero_title_line1', 'hero_title_line2', 'hero_subtitle',
        'hero_btn1_text', 'hero_btn1_link', 'hero_btn2_text', 'hero_btn2_link',
        'hero_stat1_num', 'hero_stat1_label', 'hero_stat2_num', 'hero_stat2_label',
        'hero_stat3_num', 'hero_stat3_label',
        'hero_tag1_title', 'hero_tag1_subtitle', 'hero_tag2_title', 'hero_tag2_subtitle'
    ];

    $image_url = clean_input($_POST['hero_image_url'] ?? '');

    // Handle File Upload if provided
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hero_image_file'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 8 * 1024 * 1024) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $valid_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($mime, $valid_mimes)) {
                $target_dir = __DIR__ . '/../uploads/banners/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $filename = 'hero_banner_' . time() . '.' . $ext;
                $dest = $target_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $image_url = '/uploads/banners/' . $filename;
                }
            }
        }
    }

    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($is_sqlite) {
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        }

        foreach ($keys as $k) {
            $val = $_POST[$k] ?? '';
            $stmt->execute([$k, trim((string)$val)]);
        }

        if (!empty($image_url)) {
            $stmt->execute(['hero_image_url', $image_url]);
        }

        set_flash('success', 'हीरो सेक्शन (त्योहार व सेल बैनर) सफलतापूर्वक अपडेट कर दिया गया!');
        header('Location: /admin/hero_customizer.php');
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

function get_h_val($key, $default = '') {
    global $curr_settings;
    return $curr_settings[$key] ?? $default;
}

$page_title = 'हीरो सेक्शन व त्योहार सेल कस्टमाइज़र — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title Bar -->
<div class="mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
        हीरो सेक्शन व त्योहार / सेल कस्टमाइज़र (Hero Festive Editor)
      </h1>
      <p class="text-xs text-gray-500 mt-1">
        दीपावली, होली, फसल कटाई (संक्रांति), मानसून किसान सेल या किसी भी विशेष ऑफर के अनुसार होमपेज बैनर बदलें
      </p>
    </div>

    <div class="flex items-center gap-2">
      <a href="/" target="_blank" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-stone-300">
        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> लाइव होमपेज देखें
      </a>
    </div>
  </div>
</div>

<!-- Preset Quick Loader -->
<div class="mb-8 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-2xl p-5 text-white shadow-md">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h3 class="font-bold text-sm flex items-center gap-2">
        <i class="fa-solid fa-bolt"></i> 1-क्लिक त्योहार व सेल प्रीसेट लोड करें (Festival Presets):
      </h3>
      <p class="text-xs text-amber-100 mt-0.5">क्लिक करते ही नीचे के सभी फ़ील्ड्स उस त्योहार के अनुसार अपने आप भर जाएंगे।</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button type="button" onclick="loadPreset('diwali')" class="px-3 py-1.5 bg-white text-orange-950 font-bold text-xs rounded-xl hover:bg-amber-50 transition shadow-xs">
        🪔 दीपावली महाबचत
      </button>
      <button type="button" onclick="loadPreset('holi')" class="px-3 py-1.5 bg-white text-orange-950 font-bold text-xs rounded-xl hover:bg-amber-50 transition shadow-xs">
        🎨 होली रंगोत्सव सेल
      </button>
      <button type="button" onclick="loadPreset('harvest')" class="px-3 py-1.5 bg-white text-orange-950 font-bold text-xs rounded-xl hover:bg-amber-50 transition shadow-xs">
        🌾 फसल कटाई व संक्रांति
      </button>
      <button type="button" onclick="loadPreset('monsoon')" class="px-3 py-1.5 bg-white text-orange-950 font-bold text-xs rounded-xl hover:bg-amber-50 transition shadow-xs">
        🌧️ मानसून किसान सेल
      </button>
      <button type="button" onclick="loadPreset('general')" class="px-3 py-1.5 bg-orange-900/40 text-white border border-white/30 font-bold text-xs rounded-xl hover:bg-orange-900/60 transition">
        🏛️ सामान्य देसी हाट
      </button>
    </div>
  </div>
</div>

<form action="/admin/hero_customizer.php" method="POST" enctype="multipart/form-data" class="space-y-8">
  <input type="hidden" name="save_hero_settings" value="1">
  <input type="hidden" name="hero_festive_preset" id="hero_festive_preset" value="<?= htmlspecialchars(get_h_val('hero_festive_preset', 'general')) ?>">

  <!-- Section 1: Themes & Colors -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-palette text-indigo-600"></i>
      1. थीम रंग एवं हेडर घोषणा पट्टी
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-2">पृष्ठभूमि रंग थीम (Theme Color)</label>
        <select name="hero_theme_color" id="hero_theme_color" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
          <option value="emerald" <?= get_h_val('hero_theme_color', 'emerald') === 'emerald' ? 'selected' : '' ?>>🌲 एमराल्ड ग्रीन (देसी खेती व प्रकृति)</option>
          <option value="crimson" <?= get_h_val('hero_theme_color') === 'crimson' ? 'selected' : '' ?>>🪔 फेस्टिव क्रिमसन (दीपावली व करवा चौथ)</option>
          <option value="amber" <?= get_h_val('hero_theme_color') === 'amber' ? 'selected' : '' ?>>🌾 सुनहरा अम्बर (होली व फसल कटाई)</option>
          <option value="indigo" <?= get_h_val('hero_theme_color') === 'indigo' ? 'selected' : '' ?>>🌌 रॉयल इंडिगो (कारीगर एक्सक्लूसिव)</option>
        </select>
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-bold text-gray-700 mb-2">उत्सव/सेल घोषणा बैज (Festive Top Badge Text)</label>
        <input type="text" name="hero_badge_text" id="hero_badge_text" required
          value="<?= htmlspecialchars(get_h_val('hero_badge_text', '🌾 S S Industries Enterprise • Empowering Rural Artisans, Dal Farmers & SHGs')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
      </div>
    </div>
  </div>

  <!-- Section 2: Headlines & Description -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-heading text-brand-green"></i>
      2. मुख्य शीर्षक एवं प्रचार विवरण (Headlines & Promotion Text)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य शीर्षक लाइन 1 (Title Line 1)</label>
        <input type="text" name="hero_title_line1" id="hero_title_line1" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line1', 'Gaon ke hunar,')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">मुख्य शीर्षक लाइन 2 (हाईलाइटेड - Title Line 2)</label>
        <input type="text" name="hero_title_line2" id="hero_title_line2" required
          value="<?= htmlspecialchars(get_h_val('hero_title_line2', 'ab har ghar tak')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold text-amber-600">
      </div>

      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-gray-700 mb-1">प्रचार विवरण / उप-शीर्षक (Subheadline Description)</label>
        <textarea name="hero_subtitle" id="hero_subtitle" rows="3" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars(get_h_val('hero_subtitle', 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.')) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Section 3: Call to Action Buttons -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-computer-mouse text-orange-500"></i>
      3. कॉल टू एक्शन बटन (CTA Buttons)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 1 टेक्स्ट (Primary Button)</label>
        <input type="text" name="hero_btn1_text" id="hero_btn1_text"
          value="<?= htmlspecialchars(get_h_val('hero_btn1_text', 'Explore Marketplace')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 1 लिंक (Primary Link)</label>
        <input type="text" name="hero_btn1_link" id="hero_btn1_link"
          value="<?= htmlspecialchars(get_h_val('hero_btn1_link', '/products.php')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 2 टेक्स्ट (Secondary Button)</label>
        <input type="text" name="hero_btn2_text" id="hero_btn2_text"
          value="<?= htmlspecialchars(get_h_val('hero_btn2_text', 'Farming & Garden Tools')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">बटन 2 लिंक (Secondary Link)</label>
        <input type="text" name="hero_btn2_link" id="hero_btn2_link"
          value="<?= htmlspecialchars(get_h_val('hero_btn2_link', '/products.php?category=farming-traditional-tools')) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
      </div>
    </div>
  </div>

  <!-- Section 4: Image & Floating Cards -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-image text-blue-600"></i>
      4. हीरो बैनर छवि एवं फ्लोटिंग कार्ड्स (Hero Graphic & Cards)
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
      
      <div class="lg:col-span-7 space-y-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">बैनर छवि URL (Banner Image URL)</label>
          <input type="url" name="hero_image_url" id="hero_image_url"
            value="<?= htmlspecialchars(get_h_val('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">या नई छवि अपलोड करें (JPG, PNG, WebP)</label>
          <input type="file" name="hero_image_file" accept="image/*"
            class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs">
          <p class="text-[11px] text-gray-400 mt-1">त्योहार के विशेष बैनर या कारीगर कार्यशाला की वास्तविक छवि जोड़ें।</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">फ्लोटिंग कार्ड 1 (शीर्षक)</label>
            <input type="text" name="hero_tag1_title" id="hero_tag1_title"
              value="<?= htmlspecialchars(get_h_val('hero_tag1_title', 'High-Carbon Tempered Steel')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">फ्लोटिंग कार्ड 1 (उप-शीर्षक)</label>
            <input type="text" name="hero_tag1_subtitle" id="hero_tag1_subtitle"
              value="<?= htmlspecialchars(get_h_val('hero_tag1_subtitle', 'Forged Desi Hansiya')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs">
          </div>
        </div>
      </div>

      <!-- Preview Image Box -->
      <div class="lg:col-span-5 text-center">
        <div class="p-3 bg-stone-50 rounded-2xl border border-gray-200 inline-block">
          <img id="imageDisplayPreview"
            src="<?= htmlspecialchars(get_h_val('hero_image_url', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80')) ?>"
            alt="Hero Preview" class="w-64 h-48 object-cover rounded-xl shadow-md border border-gray-200">
          <p class="text-[11px] text-gray-400 mt-2">वर्तमान सक्रिय हीरो ग्राफिक</p>
        </div>
      </div>

    </div>
  </div>

  <!-- Section 5: Stats Counter Badges -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center gap-2">
      <i class="fa-solid fa-chart-simple text-emerald-600"></i>
      5. आंकड़े एवं गारंटी बैज (Stats Badges)
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-3.5 bg-stone-50 rounded-xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 1 (संख्या)</label>
        <input type="text" name="hero_stat1_num" id="hero_stat1_num" value="<?= htmlspecialchars(get_h_val('hero_stat1_num', '100%')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 1 (लेबल)</label>
        <input type="text" name="hero_stat1_label" id="hero_stat1_label" value="<?= htmlspecialchars(get_h_val('hero_stat1_label', 'Forged Iron & Pure Dal')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-3.5 bg-stone-50 rounded-xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 2 (संख्या)</label>
        <input type="text" name="hero_stat2_num" id="hero_stat2_num" value="<?= htmlspecialchars(get_h_val('hero_stat2_num', '40+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 2 (लेबल)</label>
        <input type="text" name="hero_stat2_label" id="hero_stat2_label" value="<?= htmlspecialchars(get_h_val('hero_stat2_label', 'Village Artisan Hubs')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>

      <div class="p-3.5 bg-stone-50 rounded-xl border border-gray-200 space-y-2">
        <label class="block text-xs font-bold text-gray-700">बैज 3 (संख्या)</label>
        <input type="text" name="hero_stat3_num" id="hero_stat3_num" value="<?= htmlspecialchars(get_h_val('hero_stat3_num', '₹499+')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-brand-green">
        <label class="block text-xs font-bold text-gray-700">बैज 3 (लेबल)</label>
        <input type="text" name="hero_stat3_label" id="hero_stat3_label" value="<?= htmlspecialchars(get_h_val('hero_stat3_label', 'Free India Delivery')) ?>"
          class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs">
      </div>
    </div>
  </div>

  <!-- Save Button -->
  <div class="flex items-center justify-end gap-3 pb-8">
    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center gap-2">
      <i class="fa-solid fa-floppy-disk"></i>
      हीरो सेक्शन सहेजें एवं लाइव करें (Save & Publish Hero)
    </button>
  </div>
</form>

<script>
const presets = {
  diwali: {
    theme: 'crimson',
    badge: '🪔 शुभ दीपावली महाबचत मेला • 50% तक छूट सीधे ग्रामीण कारीगरों से',
    title1: 'दीपावली का असली देसी स्वाद,',
    title2: 'गाँव के हुनर हर घर तक',
    subtitle: 'इस दीपावली पर पारंपरिक मिट्टी के दीये, पूजा-पाठ हेतु तांबे व पीतल के बर्तन, देसी गुड़, अरहर की शुद्ध दाल और बनारसी आचार सीधे गाँव से मंगाएं।',
    btn1: 'दीपावली स्पेशल उत्पाद',
    btn2: 'हथकरघा व कुटीर सामान',
    stat1_num: '50% छूट',
    stat1_label: 'दीपावली महासेल',
    stat2_num: '100% शुद्ध',
    stat2_label: 'देसी अवध दालें',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त एक्सप्रेस डाक',
    tag1_title: 'शुभ दीपावली उत्सव स्पेशल',
    tag1_subtitle: 'कारीगर उपहार बॉक्स',
    image: 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?auto=format&fit=crop&w=800&q=80'
  },
  holi: {
    theme: 'amber',
    badge: '🎨 होली रंगोत्सव सेल • प्राकृतिक खुशबू व पारंपरिक मिठास',
    title1: 'होली का असली देसी आनंद,',
    title2: 'शुद्ध खान-पान हर आँगन तक',
    subtitle: 'होली के पावन पर्व पर शुद्ध सरसों तेल, कद्दूकस आम व नींबू का बनारसी आचार, बिना पॉलिश की दालें और प्राकृतिक हर्बल गुलाल गाँव से सीधे आपके घर।',
    btn1: 'होली स्पेशल उत्पाद देखें',
    btn2: 'बनारसी आचार व मुरब्बा',
    stat1_num: '20% Off',
    stat1_label: 'होली कूपन HOLI20',
    stat2_num: '40+',
    stat2_label: 'स्वयं सहायता समूह',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त डाक डिलीवरी',
    tag1_title: 'होली स्पेशल पैकेज',
    tag1_subtitle: 'देसी सरसों तेल व दालें',
    image: 'https://images.unsplash.com/photo-1576487247299-e2b2023a17e0?auto=format&fit=crop&w=800&q=80'
  },
  harvest: {
    theme: 'emerald',
    badge: '🌾 फसल कटाई व संक्रांति मेला • जनरेशनल लोहार कारीगरी',
    title1: 'खेतों की शान मजबूत औजार,',
    title2: 'गाँव की ताज़ा दालें सीधे घर तक',
    subtitle: 'फसल कटाई के लिए मजबूत टेम्पर्ड स्टील से तैयार हस्तनिर्मित हंसिया, खुरपी, और नई फसल की ताज़ा देसी दालें सीधे हरदोई की कार्यशालाओं से।',
    btn1: 'कृषि औजार कैटलॉग',
    btn2: 'ताज़ा नई फसल दालें',
    stat1_num: '100% लौह',
    stat1_label: 'टेम्पर्ड स्प्रिंग स्टील',
    stat2_num: '50+ वर्ष',
    stat2_label: 'कारीगर अनुभव',
    stat3_num: '₹499+',
    stat3_label: 'मुफ़्त होम डिलीवरी',
    tag1_title: 'हाई-कार्बन स्टील',
    tag1_subtitle: 'हाथ से बना मजबूत हंसिया',
    image: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80'
  },
  monsoon: {
    theme: 'emerald',
    badge: '🌧️ मानसून किसान बुवाई सेल • भारी औजारों पर विशेष छूट',
    title1: 'बरसात और बुवाई की पूरी तैयारी,',
    title2: 'मजबूत औजार सबसे किफायती दर पर',
    subtitle: 'खरीफ सीजन की बुवाई और निराई-गुड़ाई के लिए किसान भाईयों के पसंदीदा मजबूत फावड़ा, खुरपी, कुल्हाड़ी व कुदाल सीधे निर्माता से मंगाएं।',
    btn1: 'बुवाई उपकरण देखें',
    btn2: 'भारी लोहे के उपकरण',
    stat1_num: 'लाइफटाइम',
    stat1_label: 'मजबूत लोहे की धार',
    stat2_num: '18+ केंद्र',
    stat2_label: 'सीधी डिलीवरी',
    stat3_num: 'COD',
    stat3_label: 'घर पर नकद भुगतान',
    tag1_title: 'निराई-गुड़ाई स्पेशल',
    tag1_subtitle: 'मजबूत खुरपी व फावड़ा',
    image: 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80'
  },
  general: {
    theme: 'emerald',
    badge: '🌾 S S Industries Enterprise • Desh ki mitti ka swad',
    title1: 'Gaon ke hunar,',
    title2: 'ab har ghar tak',
    subtitle: 'Discover high-durability hand-forged agricultural sickles, garden khurpis, pure unpolished Awadh pulses, Banarasi mustard oil pickles, eco golden jute bags, and handlooms direct from rural Bharat.',
    btn1: 'Explore Marketplace',
    btn2: 'Farming & Garden Tools',
    stat1_num: '100%',
    stat1_label: 'Forged Iron & Pure Dal',
    stat2_num: '40+',
    stat2_label: 'Village Artisan Hubs',
    stat3_num: '₹499+',
    stat3_label: 'Free India Delivery',
    tag1_title: 'High-Carbon Tempered Steel',
    tag1_subtitle: 'Forged Desi Hansiya',
    image: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80'
  }
};

function loadPreset(key) {
  const p = presets[key];
  if (!p) return;
  document.getElementById('hero_festive_preset').value = key;
  document.getElementById('hero_theme_color').value = p.theme;
  document.getElementById('hero_badge_text').value = p.badge;
  document.getElementById('hero_title_line1').value = p.title1;
  document.getElementById('hero_title_line2').value = p.title2;
  document.getElementById('hero_subtitle').value = p.subtitle;
  document.getElementById('hero_btn1_text').value = p.btn1;
  document.getElementById('hero_btn2_text').value = p.btn2;
  document.getElementById('hero_stat1_num').value = p.stat1_num;
  document.getElementById('hero_stat1_label').value = p.stat1_label;
  document.getElementById('hero_stat2_num').value = p.stat2_num;
  document.getElementById('hero_stat2_label').value = p.stat2_label;
  document.getElementById('hero_stat3_num').value = p.stat3_num;
  document.getElementById('hero_stat3_label').value = p.stat3_label;
  document.getElementById('hero_tag1_title').value = p.tag1_title;
  document.getElementById('hero_tag1_subtitle').value = p.tag1_subtitle;
  document.getElementById('hero_image_url').value = p.image;
  document.getElementById('imageDisplayPreview').src = p.image;
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
