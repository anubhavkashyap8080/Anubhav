<?php
/**
 * Video Story Home (Layout 6) Management & Customizer
 * Super Admin Control Panel for Full-Screen Edge-to-Edge Video Sections
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/video_helpers.php';

require_admin();

// Item 6: CSRF protection placed before any POST processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    // 1. Handle Reset to Defaults
    if (isset($_POST['reset_defaults'])) {
        try {
            $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $del_stmt = $pdo->prepare("DELETE FROM site_settings WHERE setting_key LIKE 'v6_%'");
            $del_stmt->execute();

            log_audit_action('v6_reset_defaults', 'customizer', 'site_settings', 0, 'Reset Video Story Home (Layout 6) to factory defaults');
            set_flash('success', 'Video Story Home settings have been reset to factory defaults.');
            header('Location: /admin/video_home.php');
            exit;
        } catch (Exception $e) {
            error_log("Error resetting v6 settings: " . $e->getMessage());
            set_flash('error', 'Could not reset settings. Please try again.');
        }
    }

    // 2. Handle Save Settings
    if (isset($_POST['save_video_home'])) {
        $errors = [];
        $saved_settings = [];

        // Global settings
        $saved_settings['v6_header_transparent'] = isset($_POST['v6_header_transparent']) ? '1' : '0';
        $saved_settings['v6_dot_nav']           = isset($_POST['v6_dot_nav']) ? '1' : '0';
        $saved_settings['v6_scroll_snap']       = isset($_POST['v6_scroll_snap']) ? '1' : '0';
        $saved_settings['v6_save_data_pause']   = isset($_POST['v6_save_data_pause']) ? '1' : '0';

        for ($n = 1; $n <= 6; $n++) {
            $prefix = "v6_sec{$n}_";

            // Enabled toggle
            $saved_settings[$prefix . 'enabled'] = isset($_POST[$prefix . 'enabled']) ? '1' : '0';

            // Source
            $source = in_array($_POST[$prefix . 'source'] ?? '', ['youtube', 'mp4'], true) ? $_POST[$prefix . 'source'] : 'youtube';
            $saved_settings[$prefix . 'source'] = $source;

            // Video input (Validate)
            $video_raw = trim((string)($_POST[$prefix . 'video'] ?? ''));
            if ($video_raw !== '') {
                if ($source === 'youtube') {
                    $yt_id = ggh_youtube_id($video_raw);
                    if ($yt_id === '') {
                        $errors[] = "Section $n: Invalid YouTube URL or video ID. Please enter an 11-character video ID or valid YouTube watch/share link.";
                    } else {
                        $saved_settings[$prefix . 'video'] = $yt_id;
                    }
                } else { // mp4
                    if (!ggh_is_valid_media_url($video_raw, 'mp4')) {
                        $errors[] = "Section $n: Invalid MP4 URL. Must start with 'https://' or internal '/uploads/' and end with .mp4 or .webm.";
                    } else {
                        $saved_settings[$prefix . 'video'] = $video_raw;
                    }
                }
            } else {
                $saved_settings[$prefix . 'video'] = '';
            }

            // Start & End times
            $saved_settings[$prefix . 'start'] = max(0, (int)($_POST[$prefix . 'start'] ?? 0));
            $saved_settings[$prefix . 'end']   = max(0, (int)($_POST[$prefix . 'end'] ?? 0));

            // Poster file upload or URL
            $poster_val = trim((string)($_POST[$prefix . 'poster'] ?? ''));
            $file_field = $prefix . 'poster_file';
            if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
                $up = secure_upload_image($file_field, 'uploads/banners/');
                if ($up['success']) {
                    $poster_val = $up['path'];
                } else {
                    $errors[] = "Section $n Poster Upload Error: " . ($up['error'] ?? 'Upload failed');
                }
            }
            if ($poster_val !== '' && !preg_match('/^(https:\/\/|\/uploads\/)/i', $poster_val)) {
                $errors[] = "Section $n: Poster image must be an https:// URL or an uploaded image.";
            } else {
                $saved_settings[$prefix . 'poster'] = $poster_val;
            }

            // Overlay strength (0 to 90)
            $overlay = (int)($_POST[$prefix . 'overlay'] ?? 50);
            $saved_settings[$prefix . 'overlay'] = max(0, min(90, $overlay));

            // Text alignment
            $align = in_array($_POST[$prefix . 'align'] ?? '', ['left', 'center', 'right'], true) ? $_POST[$prefix . 'align'] : 'left';
            $saved_settings[$prefix . 'align'] = $align;

            // Text content
            $saved_settings[$prefix . 'eyebrow']  = clean_input($_POST[$prefix . 'eyebrow'] ?? '');
            $saved_settings[$prefix . 'title']    = clean_input($_POST[$prefix . 'title'] ?? '');
            $saved_settings[$prefix . 'subtitle'] = clean_input($_POST[$prefix . 'subtitle'] ?? '');

            // Button 1
            $b1_text = clean_input($_POST[$prefix . 'btn1_text'] ?? '');
            $b1_link = trim((string)($_POST[$prefix . 'btn1_link'] ?? ''));
            if ($b1_link !== '' && !ggh_is_valid_btn_url($b1_link)) {
                $errors[] = "Section $n: Button 1 URL must start with '/' or 'https://'.";
            }
            $saved_settings[$prefix . 'btn1_text']   = $b1_text;
            $saved_settings[$prefix . 'btn1_link']   = $b1_link;
            $saved_settings[$prefix . 'btn1_show']   = isset($_POST[$prefix . 'btn1_show']) ? '1' : '0';
            $saved_settings[$prefix . 'btn1_target'] = !empty($_POST[$prefix . 'btn1_target']) ? '_blank' : '_self';

            // Button 2
            $b2_text = clean_input($_POST[$prefix . 'btn2_text'] ?? '');
            $b2_link = trim((string)($_POST[$prefix . 'btn2_link'] ?? ''));
            if ($b2_link !== '' && !ggh_is_valid_btn_url($b2_link)) {
                $errors[] = "Section $n: Button 2 URL must start with '/' or 'https://'.";
            }
            $saved_settings[$prefix . 'btn2_text']   = $b2_text;
            $saved_settings[$prefix . 'btn2_link']   = $b2_link;
            $saved_settings[$prefix . 'btn2_show']   = isset($_POST[$prefix . 'btn2_show']) ? '1' : '0';
            $saved_settings[$prefix . 'btn2_target'] = !empty($_POST[$prefix . 'btn2_target']) ? '_blank' : '_self';

            // Section-specific
            if ($n === 2) {
                $saved_settings[$prefix . 'cat_source'] = in_array($_POST[$prefix . 'cat_source'] ?? '', ['all', 'selected'], true) ? $_POST[$prefix . 'cat_source'] : 'all';
                $saved_settings[$prefix . 'cat_limit']  = max(4, min(12, (int)($_POST[$prefix . 'cat_limit'] ?? 6)));
            }
            if ($n === 3 || $n === 4) {
                $saved_settings[$prefix . 'prod_source'] = in_array($_POST[$prefix . 'prod_source'] ?? '', ['best_sellers', 'newest', 'featured'], true) ? $_POST[$prefix . 'prod_source'] : 'best_sellers';
                $saved_settings[$prefix . 'prod_limit']  = max(4, min(12, (int)($_POST[$prefix . 'prod_limit'] ?? ($n === 3 ? 8 : 5))));
            }
            if ($n === 5) {
                $saved_settings[$prefix . 'artisan_limit'] = max(3, min(8, (int)($_POST[$prefix . 'artisan_limit'] ?? 4)));
            }
        }

        if (!empty($errors)) {
            set_flash('error', implode('<br>', $errors));
        } else {
            try {
                $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
                if ($is_sqlite) {
                    $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                }

                foreach ($saved_settings as $k => $v) {
                    $stmt->execute([$k, (string)$v]);
                }

                log_audit_action('v6_save_settings', 'customizer', 'site_settings', 0, 'Saved Video Story Home (Layout 6) configuration');
                set_flash('success', 'Video Story Home settings saved successfully. Preview changes live.');
                header('Location: /admin/video_home.php');
                exit;
            } catch (Exception $e) {
                error_log("Error saving v6 settings: " . $e->getMessage());
                set_flash('error', 'Database error while saving settings. Please try again.');
            }
        }
    }
}

// Fetch current configurations
$global_header_trans = get_setting('v6_header_transparent', '1') === '1';
$global_dot_nav      = get_setting('v6_dot_nav', '1') === '1';
$global_scroll_snap  = get_setting('v6_scroll_snap', '1') === '1';
$global_data_saver   = get_setting('v6_save_data_pause', '1') === '1';

$sec_configs = [];
for ($i = 1; $i <= 6; $i++) {
    $sec_configs[$i] = ggh_v6_get_section_config($i);
}

$page_title = 'Video Story Home (Layout 6) Customizer — Super Admin';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Action Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 font-display flex items-center gap-2.5">
      <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-film text-lg"></i>
      </span>
      <span>Video Story Home (Layout 6) Customizer</span>
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      Cinematic full-screen edge-to-edge background videos for all 6 homepage sections with zero visible controls, smart IntersectionObserver lazy loading, and mobile data saver support.
    </p>
  </div>

  <div class="flex items-center gap-2.5 flex-wrap">
    <a href="/?layout=layout_6" target="_blank" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition shadow-md flex items-center gap-2 cursor-pointer">
      <i class="fa-solid fa-play text-xs"></i>
      <span>Preview Layout 6 Live</span>
    </a>
    <a href="/admin/hero_customizer.php" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs rounded-xl border border-gray-200 transition flex items-center gap-2">
      <i class="fa-solid fa-layer-group text-xs text-indigo-600"></i>
      <span>Layout Chooser</span>
    </a>
  </div>
</div>

<form method="POST" action="/admin/video_home.php" enctype="multipart/form-data" class="space-y-8">
  <?= csrf_field() ?>

  <!-- Global Video Home Settings Card -->
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-5">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
      <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-sliders text-rose-600"></i>
        <span>Global Playback & Navigation Controls</span>
      </h3>
      <span class="px-2.5 py-1 bg-rose-50 text-rose-800 text-[11px] font-bold rounded-lg uppercase">Global Options</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <label class="p-4 rounded-2xl border border-gray-200 bg-stone-50 hover:bg-white transition cursor-pointer flex items-start gap-3">
        <input type="checkbox" name="v6_header_transparent" value="1" <?= $global_header_trans ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
        <div>
          <span class="block text-xs font-bold text-gray-900">Transparent Overlay Header</span>
          <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Turns solid white on scrolling past 80px.</span>
        </div>
      </label>

      <label class="p-4 rounded-2xl border border-gray-200 bg-stone-50 hover:bg-white transition cursor-pointer flex items-start gap-3">
        <input type="checkbox" name="v6_dot_nav" value="1" <?= $global_dot_nav ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
        <div>
          <span class="block text-xs font-bold text-gray-900">Right-Side Dot Navigation</span>
          <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Floating dots to jump smoothly to any of the 6 sections.</span>
        </div>
      </label>

      <label class="p-4 rounded-2xl border border-gray-200 bg-stone-50 hover:bg-white transition cursor-pointer flex items-start gap-3">
        <input type="checkbox" name="v6_scroll_snap" value="1" <?= $global_scroll_snap ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
        <div>
          <span class="block text-xs font-bold text-gray-900">Scroll Snap Proximity</span>
          <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Gentle snap to section boundaries without locking scrolling.</span>
        </div>
      </label>

      <label class="p-4 rounded-2xl border border-gray-200 bg-stone-50 hover:bg-white transition cursor-pointer flex items-start gap-3">
        <input type="checkbox" name="v6_save_data_pause" value="1" <?= $global_data_saver ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
        <div>
          <span class="block text-xs font-bold text-gray-900">Data-Saver Fallback</span>
          <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Pause video and show poster on slow 2G or Save-Data mode.</span>
        </div>
      </label>
    </div>
  </div>

  <!-- 6 Sections Accordion / Cards -->
  <?php
  $sec_names = [
      1 => ['Hero Banner', 'fa-award', 'H1 Title, CTA buttons, and Village Forge Heritage'],
      2 => ['Artisanal Categories', 'fa-shapes', 'Database categories image tiles on video backdrop'],
      3 => ['Bestsellers Rail', 'fa-fire', 'Horizontal scrollable product rail with quick buy'],
      4 => ['Curated Spotlight', 'fa-sparkles', 'Asymmetric spotlight product card & featured grid'],
      5 => ['Village Artisans Story', 'fa-hammer', 'Overlapping portrait cards of master blacksmiths & weavers'],
      6 => ['Trust & Final CTA', 'fa-shield-heart', 'Delivery promise, reviews, WhatsApp support & shop CTA'],
  ];
  ?>

  <?php for ($n = 1; $n <= 6; $n++): 
    $cfg = $sec_configs[$n];
    $info = $sec_names[$n];
    $prefix = "v6_sec{$n}_";
  ?>
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
    <!-- Section Title & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <span class="px-2.5 py-1 bg-stone-900 text-white font-mono font-bold text-xs rounded-lg uppercase">Section <?= $n ?></span>
          <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
            <i class="fa-solid <?= $info[1] ?> text-rose-600"></i>
            <span><?= $info[0] ?></span>
          </h3>
        </div>
        <p class="text-xs text-gray-500 mt-1"><?= $info[2] ?></p>
      </div>

      <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2 cursor-pointer px-3.5 py-1.5 bg-stone-50 border border-gray-200 rounded-xl">
          <input type="checkbox" name="<?= $prefix ?>enabled" value="1" <?= $cfg['enabled'] === '1' ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
          <span class="text-xs font-bold text-gray-700">Section Active</span>
        </label>
      </div>
    </div>

    <!-- Media Controls: Video & Poster -->
    <div class="p-5 bg-stone-50 rounded-2xl border border-gray-200 space-y-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-video text-rose-600"></i> Video Background & Visual Media
        </span>
        <div class="flex items-center gap-3 text-xs">
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="<?= $prefix ?>source" value="youtube" <?= $cfg['source'] === 'youtube' ? 'checked' : '' ?> class="text-rose-600">
            <span class="font-bold text-gray-700">YouTube</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="<?= $prefix ?>source" value="mp4" <?= $cfg['source'] === 'mp4' ? 'checked' : '' ?> class="text-rose-600">
            <span class="font-bold text-gray-700">Self-Hosted MP4</span>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <div class="lg:col-span-6">
          <label class="block text-xs font-bold text-gray-700 mb-1">YouTube URL / Video ID or MP4 Link</label>
          <input type="text" name="<?= $prefix ?>video" value="<?= htmlspecialchars($cfg['video']) ?>"
            placeholder="e.g. k4m3y1eH5Ww or https://www.youtube.com/watch?v=..."
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          <p class="text-[11px] text-gray-500 mt-1">
            Use a short looping clip (720p, 10-30 seconds, no logos). Video plays muted without player controls.
          </p>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs font-bold text-gray-700 mb-1">Start Second (Optional)</label>
          <input type="number" name="<?= $prefix ?>start" min="0" value="<?= $cfg['start'] ?>"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs font-bold text-gray-700 mb-1">End Second (0 = Loop end)</label>
          <input type="number" name="<?= $prefix ?>end" min="0" value="<?= $cfg['end'] ?>"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>

        <div class="lg:col-span-6">
          <label class="block text-xs font-bold text-gray-700 mb-1">Poster / Fallback Image URL</label>
          <input type="text" name="<?= $prefix ?>poster" value="<?= htmlspecialchars($cfg['poster']) ?>"
            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          <p class="text-[11px] text-gray-500 mt-1">Shown before video starts and on Save-Data mode.</p>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs font-bold text-gray-700 mb-1">Upload New Poster Image</label>
          <input type="file" name="<?= $prefix ?>poster_file" accept="image/jpeg,image/png,image/webp"
            class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-100 file:text-rose-800 hover:file:bg-rose-200">
        </div>

        <div class="lg:col-span-3">
          <label class="block text-xs font-bold text-gray-700 mb-1">Dark Overlay Strength (0-90%)</label>
          <div class="flex items-center gap-2">
            <input type="range" name="<?= $prefix ?>overlay" min="0" max="90" value="<?= $cfg['overlay'] ?>"
              class="w-full accent-rose-600" oninput="document.getElementById('<?= $prefix ?>overlay_val').textContent = this.value + '%'">
            <span id="<?= $prefix ?>overlay_val" class="text-xs font-mono font-bold text-gray-700 w-10 text-right"><?= $cfg['overlay'] ?>%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Typography & Content -->
    <div class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">Top Eyebrow Badge Text</label>
          <input type="text" name="<?= $prefix ?>eyebrow" value="<?= htmlspecialchars($cfg['eyebrow']) ?>"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Text Alignment</label>
          <select name="<?= $prefix ?>align" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold">
            <option value="left" <?= $cfg['align'] === 'left' ? 'selected' : '' ?>>Left Aligned</option>
            <option value="center" <?= $cfg['align'] === 'center' ? 'selected' : '' ?>>Center Aligned</option>
            <option value="right" <?= $cfg['align'] === 'right' ? 'selected' : '' ?>>Right Aligned</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1"><?= $n === 1 ? 'Main Heading (H1)' : 'Section Heading' ?></label>
        <input type="text" name="<?= $prefix ?>title" value="<?= htmlspecialchars($cfg['title']) ?>"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-900">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Subheadline / Narrative Description</label>
        <textarea name="<?= $prefix ?>subtitle" rows="2"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs leading-relaxed"><?= htmlspecialchars($cfg['subtitle']) ?></textarea>
      </div>
    </div>

    <!-- Section-Specific Controls -->
    <?php if ($n === 2): ?>
    <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-200/80 space-y-3">
      <h4 class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
        <i class="fa-solid fa-sliders text-amber-700"></i> Category Tiles Configuration
      </h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
          <label class="block font-bold text-gray-700 mb-1">Category Source</label>
          <select name="<?= $prefix ?>cat_source" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
            <option value="all" <?= ($cfg['cat_source'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Active Categories (by sort order)</option>
          </select>
        </div>
        <div>
          <label class="block font-bold text-gray-700 mb-1">Display Limit (4 to 12 tiles)</label>
          <input type="number" name="<?= $prefix ?>cat_limit" min="4" max="12" value="<?= $cfg['cat_limit'] ?? 6 ?>"
            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>
      </div>
    </div>
    <?php elseif ($n === 3 || $n === 4): ?>
    <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200/80 space-y-3">
      <h4 class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
        <i class="fa-solid fa-box text-emerald-700"></i> Product Source Configuration
      </h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
          <label class="block font-bold text-gray-700 mb-1">Product Stream</label>
          <select name="<?= $prefix ?>prod_source" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
            <option value="best_sellers" <?= ($cfg['prod_source'] ?? '') === 'best_sellers' ? 'selected' : '' ?>>Best Sellers (Popular)</option>
            <option value="featured" <?= ($cfg['prod_source'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured Products</option>
            <option value="newest" <?= ($cfg['prod_source'] ?? '') === 'newest' ? 'selected' : '' ?>>New Arrivals</option>
          </select>
        </div>
        <div>
          <label class="block font-bold text-gray-700 mb-1">Item Limit (4 to 12 items)</label>
          <input type="number" name="<?= $prefix ?>prod_limit" min="4" max="12" value="<?= $cfg['prod_limit'] ?? ($n === 3 ? 8 : 5) ?>"
            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
        </div>
      </div>
    </div>
    <?php elseif ($n === 5): ?>
    <div class="p-4 bg-purple-50/50 rounded-2xl border border-purple-200/80 space-y-3">
      <h4 class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
        <i class="fa-solid fa-users text-purple-700"></i> Artisan Showcase Configuration
      </h4>
      <div class="text-xs max-w-xs">
        <label class="block font-bold text-gray-700 mb-1">Number of Artisans to Feature (3 to 8)</label>
        <input type="number" name="<?= $prefix ?>artisan_limit" min="3" max="8" value="<?= $cfg['artisan_limit'] ?? 4 ?>"
          class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
      </div>
    </div>
    <?php endif; ?>

    <!-- Action Buttons for Section N (TASK B Behaviour) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-2">
      <!-- Button 1 Box -->
      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="text-xs font-bold text-gray-800">Button 1 (Primary)</span>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="<?= $prefix ?>btn1_show" value="1" <?= $cfg['btn1_show'] === '1' ? 'checked' : '' ?> class="w-4 h-4 text-rose-600 rounded">
            <span class="text-xs font-bold text-gray-700">Show button</span>
          </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div>
            <label class="block text-gray-600 mb-1 font-medium">Button Text</label>
            <input type="text" name="<?= $prefix ?>btn1_text" value="<?= htmlspecialchars($cfg['btn1_text']) ?>"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-gray-600 mb-1 font-medium">Link URL</label>
            <input type="text" name="<?= $prefix ?>btn1_link" value="<?= htmlspecialchars($cfg['btn1_link']) ?>"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>
        <div class="pt-1">
          <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="<?= $prefix ?>btn1_target" value="_blank" <?= ($cfg['btn1_target'] ?? '_self') === '_blank' ? 'checked' : '' ?> class="w-3.5 h-3.5 text-indigo-600 rounded">
            <span class="text-[11px] text-gray-600">Open link in new tab (<code class="text-[10px]">_blank</code>)</span>
          </label>
        </div>
      </div>

      <!-- Button 2 Box -->
      <div class="p-4 bg-stone-50 rounded-2xl border border-gray-200 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200">
          <span class="text-xs font-bold text-gray-800">Button 2 (Secondary)</span>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="<?= $prefix ?>btn2_show" value="1" <?= $cfg['btn2_show'] === '1' ? 'checked' : '' ?> class="w-4 h-4 text-rose-600 rounded">
            <span class="text-xs font-bold text-gray-700">Show button</span>
          </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div>
            <label class="block text-gray-600 mb-1 font-medium">Button Text</label>
            <input type="text" name="<?= $prefix ?>btn2_text" value="<?= htmlspecialchars($cfg['btn2_text']) ?>"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold">
          </div>
          <div>
            <label class="block text-gray-600 mb-1 font-medium">Link URL</label>
            <input type="text" name="<?= $prefix ?>btn2_link" value="<?= htmlspecialchars($cfg['btn2_link']) ?>"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono">
          </div>
        </div>
        <div class="pt-1">
          <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="<?= $prefix ?>btn2_target" value="_blank" <?= ($cfg['btn2_target'] ?? '_self') === '_blank' ? 'checked' : '' ?> class="w-3.5 h-3.5 text-indigo-600 rounded">
            <span class="text-[11px] text-gray-600">Open link in new tab (<code class="text-[10px]">_blank</code>)</span>
          </label>
        </div>
      </div>
    </div>

  </div>
  <?php endfor; ?>

  <!-- Sticky Bottom Save Bar -->
  <div class="sticky bottom-4 z-40 bg-stone-900/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border border-stone-700 flex items-center justify-between gap-4">
    <div class="flex items-center gap-2">
      <button type="submit" name="reset_defaults" value="1" onclick="return confirm('Reset all Video Story Home settings to factory defaults?');"
        class="px-4 py-2 bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-xs rounded-xl border border-stone-600 transition cursor-pointer">
        Reset to Defaults
      </button>
    </div>

    <div class="flex items-center gap-3">
      <a href="/?layout=layout_6" target="_blank" class="px-4 py-2.5 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl border border-stone-600 transition flex items-center gap-1.5">
        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
        <span>Preview Live</span>
      </a>
      <button type="submit" name="save_video_home" value="1"
        class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-black text-xs sm:text-sm rounded-xl transition shadow-lg flex items-center gap-2 cursor-pointer active:scale-95">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Save Video Home Settings</span>
      </button>
    </div>
  </div>

</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
