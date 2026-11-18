<?php
/**
 * GanvGaliHat - Admin: Video Story Home (layout_6) settings
 * Controls the YouTube / MP4 background video, text, buttons (show/hide) and data
 * sources for every section of the video home page.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/video_helpers.php';
require_admin();

// CSRF is verified BEFORE any POST handling.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
}

$is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
$upsert = $is_sqlite
    ? $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)")
    : $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

$defs = ggh_v6_section_defaults();

/** Short plain text: strip tags, trim, limit length. */
function v6a_text($v, $max) {
    $v = trim(strip_tags((string)$v));
    return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}

$errors = [];
$posted = null;

// ---------- Reset one section to defaults ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_section'])) {
    $n = (int)$_POST['reset_section'];
    if (isset($defs[$n])) {
        $del = $pdo->prepare("DELETE FROM site_settings WHERE setting_key = ?");
        foreach (array_merge(array_keys($defs[$n]), ['source', 'video', 'start', 'end', 'poster']) as $suffix) {
            if ($suffix !== 'name') { $del->execute(['v6_sec' . $n . '_' . $suffix]); }
        }
        log_audit_action('video_home_reset', 'content', 'site_settings', null, "Reset video home section $n to defaults");
        set_flash('success', 'Section ' . $n . ' was reset to its default settings.');
    }
    header('Location: /admin/video_home.php');
    exit;
}

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_video_home'])) {
    $vals = [];
    $cat_slugs = [];
    foreach ($pdo->query("SELECT slug FROM categories")->fetchAll() as $c) { $cat_slugs[$c['slug']] = true; }

    foreach ($defs as $n => $d) {
        $p = "s{$n}_";
        $k = "v6_sec{$n}_";
        $vals[$k . 'enabled'] = isset($_POST[$p . 'enabled']) ? '1' : '0';

        // Video source
        $source = (($_POST[$p . 'source'] ?? 'youtube') === 'mp4') ? 'mp4' : 'youtube';
        $vals[$k . 'source'] = $source;
        $raw = trim((string)($_POST[$p . 'video'] ?? ''));

        // Handle MP4 file upload if provided
        $vfile = $p . 'video_file';
        if (isset($_FILES[$vfile]) && $_FILES[$vfile]['error'] === UPLOAD_ERR_OK) {
            $vname = $_FILES[$vfile]['name'] ?? '';
            $vext = strtolower(pathinfo($vname, PATHINFO_EXTENSION));
            if (in_array($vext, ['mp4', 'webm'], true) && $_FILES[$vfile]['size'] <= 35 * 1024 * 1024) {
                $dest_dir = __DIR__ . '/../uploads/videos/';
                if (!is_dir($dest_dir)) { @mkdir($dest_dir, 0755, true); }
                $vtarget = 'v6_sec' . $n . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $vext;
                if (move_uploaded_file($_FILES[$vfile]['tmp_name'], $dest_dir . $vtarget)) {
                    $raw = '/uploads/videos/' . $vtarget;
                    $source = 'mp4';
                    $vals[$k . 'source'] = 'mp4';
                } else {
                    $errors[] = "Section $n: video upload failed.";
                }
            } else {
                $errors[] = "Section $n: video must be .mp4 or .webm under 35MB.";
            }
        }

        if ($raw === '') {
            $vals[$k . 'video'] = '';
        } elseif ($source === 'youtube') {
            $id = ggh_yt_id($raw);
            if ($id === '') {
                $errors[] = "Section $n: that is not a valid YouTube video link or ID.";
                $vals[$k . 'video'] = '';
            } else {
                $vals[$k . 'video'] = 'https://www.youtube.com/watch?v=' . $id;
            }
        } else {
            $mp4 = ggh_safe_media_url($raw, true);
            if ($mp4 === '') {
                $errors[] = "Section $n: MP4 must be a .mp4/.webm file under /uploads/ or on this website (https).";
                $vals[$k . 'video'] = '';
            } else {
                $vals[$k . 'video'] = $mp4;
            }
        }
        $vals[$k . 'start'] = (string)max(0, (int)($_POST[$p . 'start'] ?? 0));
        $end = trim((string)($_POST[$p . 'end'] ?? ''));
        $vals[$k . 'end'] = ($end === '') ? '' : (string)max(0, (int)$end);

        // Poster image: upload wins, otherwise URL
        $poster = trim((string)($_POST[$p . 'poster'] ?? ''));
        $field = $p . 'poster_file';
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $up = secure_upload_image($field, 'uploads/banners/');
            if (!empty($up['success'])) {
                $poster = $up['path'];
            } else {
                $errors[] = "Section $n: poster upload failed" . (!empty($up['error']) ? ' (' . $up['error'] . ')' : '') . '.';
            }
        }
        if ($poster !== '' && ggh_safe_media_url($poster) === '') {
            $errors[] = "Section $n: poster must be an https:// image URL or an uploaded image.";
            $poster = '';
        }
        $vals[$k . 'poster'] = $poster;

        $vals[$k . 'overlay'] = (string)max(0, min(90, (int)($_POST[$p . 'overlay'] ?? $d['overlay'])));
        $al = $_POST[$p . 'align'] ?? 'center';
        $vals[$k . 'align'] = in_array($al, ['left', 'center', 'right'], true) ? $al : 'center';
        $vals[$k . 'eyebrow']  = v6a_text($_POST[$p . 'eyebrow'] ?? '', 80);
        $vals[$k . 'title']    = v6a_text($_POST[$p . 'title'] ?? '', 140);
        $vals[$k . 'subtitle'] = v6a_text($_POST[$p . 'subtitle'] ?? '', 300);

        foreach (['btn1', 'btn2'] as $b) {
            $vals[$k . $b . '_text'] = v6a_text($_POST[$p . $b . '_text'] ?? '', 40);
            $link = trim((string)($_POST[$p . $b . '_link'] ?? ''));
            if ($link !== '' && ggh_safe_link($link) === '') {
                $errors[] = "Section $n, button " . substr($b, 3) . ": use a path like /products.php or a full https:// link.";
                $link = '';
            }
            $vals[$k . $b . '_link'] = $link;
            $vals[$k . $b . '_show'] = (($_POST[$p . $b . '_show'] ?? '0') === '1') ? '1' : '0';
        }

        if (isset($d['limit'])) {
            $vals[$k . 'limit'] = (string)max(1, min(12, (int)($_POST[$p . 'limit'] ?? $d['limit'])));
        }
        if ($n === 2) {
            $vals[$k . 'show_products'] = isset($_POST[$p . 'show_products']) ? '1' : '0';
            $vals[$k . 'prod_limit'] = (string)max(1, min(12, (int)($_POST[$p . 'prod_limit'] ?? 4)));
            $vals[$k . 'cat_source'] = (($_POST[$p . 'cat_source'] ?? 'all') === 'selected') ? 'selected' : 'all';
            $ids = array_filter(array_map('intval', (array)($_POST[$p . 'cat_ids'] ?? [])));
            $vals[$k . 'cat_ids'] = implode(',', $ids);
        }
        if ($n === 3 || $n === 4) {
            $ps = $_POST[$p . 'prod_source'] ?? $d['prod_source'];
            $vals[$k . 'prod_source'] = in_array($ps, ['best_sellers', 'featured', 'newest', 'category'], true) ? $ps : $d['prod_source'];
            $pc = trim((string)($_POST[$p . 'prod_cat'] ?? ''));
            $vals[$k . 'prod_cat'] = isset($cat_slugs[$pc]) ? $pc : '';
        }
    }

    $vals['v6_header_transparent'] = isset($_POST['g_header_transparent']) ? '1' : '0';
    $vals['v6_dots'] = isset($_POST['g_dots']) ? '1' : '0';
    $vals['v6_snap'] = isset($_POST['g_snap']) ? '1' : '0';

    if ($errors) {
        $posted = $vals;
        set_flash('error', 'Nothing was saved. Please fix the highlighted problems and try again.');
    } else {
        try {
            $pdo->beginTransaction();
            foreach ($vals as $key => $val) { $upsert->execute([$key, $val]); }
            if (isset($_POST['activate_layout'])) { $upsert->execute(['homepage_layout', 'layout_6']); }
            $pdo->commit();
            log_audit_action('video_home_saved', 'content', 'site_settings', null, 'Video Story Home settings updated');
            set_flash('success', 'Video home settings saved' . (isset($_POST['activate_layout']) ? ' and the layout is now active.' : '.'));
            header('Location: /admin/video_home.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('video_home save: ' . $e->getMessage());
            set_flash('error', 'Could not save the settings. Please try again.');
        }
    }
}

// ---------- Load current values ----------
$cur = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll() as $r) { $cur[$r['setting_key']] = $r['setting_value']; }
if ($posted !== null) { $cur = array_merge($cur, $posted); }
function v6a_val($key, $default = '') { global $cur; return $cur[$key] ?? $default; }
function v6a_e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$cats = $pdo->query("SELECT id, name, slug FROM categories ORDER BY sort_order ASC")->fetchAll();
$active_layout = get_setting('homepage_layout', 'layout_1');

$page_title = 'Video Story Home';
require_once __DIR__ . '/admin_header.php';

$inp = 'w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold';
$lbl = 'block text-xs font-bold text-gray-700 mb-1';
?>
<style>
.vh-card{background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:20px;margin-bottom:18px}
.vh-grid{display:grid;gap:14px;grid-template-columns:1fr}
@media(min-width:700px){.vh-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1100px){.vh-grid.c3{grid-template-columns:repeat(3,1fr)}}
.vh-h{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.vh-num{display:inline-flex;width:30px;height:30px;border-radius:50%;background:#14532d;color:#fff;font-weight:900;align-items:center;justify-content:center;margin-right:8px;font-size:13px}
.vh-sw{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:#374151;cursor:pointer}
.vh-sw input{width:18px;height:18px}
.vh-hint{font-size:11px;color:#6b7280;margin-top:4px;line-height:1.4}
.vh-box{background:#f9fafb;border:1px dashed #d1d5db;border-radius:14px;padding:12px}
.vh-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:14px;padding:12px 16px;margin-bottom:16px;font-size:13px}
.vh-btn{padding:10px 18px;border-radius:12px;font-weight:800;font-size:13px;border:0;cursor:pointer}
</style>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Video Story Home (Layout 6) Customizer</h1>
    <p class="text-xs sm:text-sm text-gray-500 mt-1">Layout 6 &mdash; every section plays a muted, looping video in the background. Videos play smoothly without controls or sound.</p>
  </div>
  <div class="flex items-center gap-2.5">
    <span class="px-3 py-1.5 rounded-full text-[11px] font-black <?= $active_layout === 'layout_6' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
      <?= $active_layout === 'layout_6' ? 'Currently ACTIVE on the home page' : 'Not active yet' ?>
    </span>
    <a href="/?layout=6" target="_blank" rel="noopener" class="vh-btn bg-stone-900 text-white">Preview</a>
  </div>
</div>

<?php if ($errors): ?>
  <div class="vh-err"><b>Please fix:</b><ul style="margin:6px 0 0 18px;list-style:disc"><?php foreach ($errors as $er): ?><li><?= v6a_e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="vh-card" style="background:#fffbeb;border-color:#fde68a">
  <b class="text-sm">Tips for smooth, buffer-free videos</b>
  <ul class="vh-hint" style="margin:6px 0 0 18px;list-style:disc;font-size:12px">
    <li>Use <b>short looping clips (10&ndash;30 seconds)</b>, 720p, no intro, no logos, slow camera movement.</li>
    <li>Paste a normal YouTube link (watch, youtu.be, shorts or embed). Use <b>Start / End seconds</b> to loop only the best part.</li>
    <li>The poster image shows instantly while the video loads. Visitors with data-saver or reduced-motion settings only see the poster.</li>
    <li>For the smoothest result use <b>MP4</b>: upload a video file or paste its path under <code>/uploads/videos/</code>.</li>
    <li>Only the section being viewed (plus the next one) plays, keeping your store lightning fast.</li>
  </ul>
</div>

<form method="POST" enctype="multipart/form-data" autocomplete="off">
  <?= csrf_field() ?>

  <div class="vh-card">
    <h2 class="text-sm font-black mb-3">Global options</h2>
    <div class="vh-grid c3">
      <label class="vh-sw"><input type="checkbox" name="g_header_transparent" value="1" <?= v6a_val('v6_header_transparent', '1') !== '0' ? 'checked' : '' ?>> Transparent header over the video (turns solid on scroll)</label>
      <label class="vh-sw"><input type="checkbox" name="g_dots" value="1" <?= v6a_val('v6_dots', '1') !== '0' ? 'checked' : '' ?>> Show section dots on the right (desktop)</label>
      <label class="vh-sw"><input type="checkbox" name="g_snap" value="1" <?= v6a_val('v6_snap', '0') === '1' ? 'checked' : '' ?>> Soft snap-scrolling between sections</label>
    </div>
  </div>

<?php foreach ($defs as $n => $d):
    $k = "v6_sec{$n}_"; $p = "s{$n}_";
    $src = v6a_val($k . 'source', 'youtube');
    $sel_cats = array_filter(array_map('intval', explode(',', (string)v6a_val($k . 'cat_ids', ''))));
?>
  <div class="vh-card" id="sec<?= $n ?>">
    <div class="vh-h">
      <h2 class="text-base font-black"><span class="vh-num"><?= $n ?></span><?= v6a_e($d['name']) ?> section</h2>
      <div class="flex items-center gap-4 flex-wrap">
        <label class="vh-sw"><input type="checkbox" name="<?= $p ?>enabled" value="1" <?= v6a_val($k . 'enabled', '1') !== '0' ? 'checked' : '' ?>> Show this section</label>
        <button type="submit" name="reset_section" value="<?= $n ?>" class="vh-btn bg-gray-100 text-gray-700" formnovalidate
                onclick="return confirm('Reset section <?= $n ?> to its default settings? Your videos and text for this section will be cleared.')">Reset to defaults</button>
      </div>
    </div>

    <div class="vh-box" style="margin-bottom:14px">
      <div class="vh-grid c3">
        <div>
          <label class="<?= $lbl ?>">Video source</label>
          <select name="<?= $p ?>source" class="<?= $inp ?>">
            <option value="youtube" <?= $src === 'youtube' ? 'selected' : '' ?>>YouTube link</option>
            <option value="mp4" <?= $src === 'mp4' ? 'selected' : '' ?>>MP4 / WebM file</option>
          </select>
        </div>
        <div style="grid-column:span 2">
          <label class="<?= $lbl ?>">Video link or file path</label>
          <input type="text" name="<?= $p ?>video" value="<?= v6a_e(v6a_val($k . 'video', '')) ?>" class="<?= $inp ?> font-mono" placeholder="https://www.youtube.com/watch?v=...  or  /uploads/videos/hero.mp4">
          <p class="vh-hint">Leave empty to show only the poster / gradient. Muted, looping, no controls.</p>
        </div>
        <div>
          <label class="<?= $lbl ?>">Upload MP4/WebM video (optional)</label>
          <input type="file" name="<?= $p ?>video_file" accept="video/mp4,video/webm" class="text-xs">
        </div>
        <div>
          <label class="<?= $lbl ?>">Start at (seconds)</label>
          <input type="number" min="0" name="<?= $p ?>start" value="<?= v6a_e(v6a_val($k . 'start', '0')) ?>" class="<?= $inp ?>">
        </div>
        <div>
          <label class="<?= $lbl ?>">End at (seconds, optional)</label>
          <input type="number" min="0" name="<?= $p ?>end" value="<?= v6a_e(v6a_val($k . 'end', '')) ?>" class="<?= $inp ?>" placeholder="until the end">
        </div>
        <div>
          <label class="<?= $lbl ?>">Dark overlay: <b><?= (int)v6a_val($k . 'overlay', $d['overlay']) ?>%</b></label>
          <input type="range" min="0" max="90" name="<?= $p ?>overlay" value="<?= (int)v6a_val($k . 'overlay', $d['overlay']) ?>" style="width:100%" oninput="this.previousElementSibling.querySelector('b').textContent=this.value+'%'">
        </div>
        <div>
          <label class="<?= $lbl ?>">Poster image URL (shown while loading)</label>
          <input type="text" name="<?= $p ?>poster" value="<?= v6a_e(v6a_val($k . 'poster', '')) ?>" class="<?= $inp ?> font-mono" placeholder="https://... or /uploads/banners/...  (empty = YouTube thumbnail)">
        </div>
        <div>
          <label class="<?= $lbl ?>">&hellip;or upload a poster</label>
          <input type="file" name="<?= $p ?>poster_file" accept="image/jpeg,image/png,image/webp" class="text-xs">
        </div>
      </div>
    </div>

    <div class="vh-grid">
      <div>
        <label class="<?= $lbl ?>">Small label above the title</label>
        <input type="text" name="<?= $p ?>eyebrow" maxlength="80" value="<?= v6a_e(v6a_val($k . 'eyebrow', $d['eyebrow'])) ?>" class="<?= $inp ?>">
      </div>
      <div>
        <label class="<?= $lbl ?>">Text alignment</label>
        <select name="<?= $p ?>align" class="<?= $inp ?>">
          <?php foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $av => $al): ?>
            <option value="<?= $av ?>" <?= v6a_val($k . 'align', $d['align']) === $av ? 'selected' : '' ?>><?= $al ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="grid-column:1/-1">
        <label class="<?= $lbl ?>">Title<?= $n === 1 ? ' (main page heading)' : '' ?></label>
        <input type="text" name="<?= $p ?>title" maxlength="140" value="<?= v6a_e(v6a_val($k . 'title', $d['title'])) ?>" class="<?= $inp ?>">
      </div>
      <div style="grid-column:1/-1">
        <label class="<?= $lbl ?>">Subtitle</label>
        <textarea name="<?= $p ?>subtitle" rows="2" maxlength="300" class="<?= $inp ?>"><?= v6a_e(v6a_val($k . 'subtitle', $d['subtitle'])) ?></textarea>
      </div>
    </div>

<?php if ($n === 8): ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Subscribe form</h3>
    <div style="max-width:320px">
      <label class="<?= $lbl ?>">Subscribe button text</label>
      <input type="text" name="<?= $p ?>btn1_text" maxlength="40" value="<?= v6a_e(v6a_val($k . 'btn1_text', $d['btn1_text'])) ?>" class="<?= $inp ?>">
      <p class="vh-hint">Emails are saved and can be viewed or downloaded in Admin &gt; Newsletter.</p>
    </div>
<?php else: ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Buttons</h3>
    <div class="vh-grid">
      <?php foreach (['btn1' => 'Button 1 (main)', 'btn2' => 'Button 2 (secondary)'] as $b => $bl): ?>
        <div class="vh-box">
          <div class="vh-h" style="margin-bottom:8px">
            <b class="text-xs"><?= $bl ?></b>
            <label class="vh-sw">
              <input type="hidden" name="<?= $p . $b ?>_show" value="0">
              <input type="checkbox" name="<?= $p . $b ?>_show" value="1" <?= v6a_val($k . $b . '_show', $d[$b . '_show']) !== '0' ? 'checked' : '' ?>> Show button
            </label>
          </div>
          <label class="<?= $lbl ?>">Button text</label>
          <input type="text" name="<?= $p . $b ?>_text" maxlength="40" value="<?= v6a_e(v6a_val($k . $b . '_text', $d[$b . '_text'])) ?>" class="<?= $inp ?>" style="margin-bottom:8px">
          <label class="<?= $lbl ?>">Link</label>
          <input type="text" name="<?= $p . $b ?>_link" value="<?= v6a_e(v6a_val($k . $b . '_link', $d[$b . '_link'])) ?>" class="<?= $inp ?> font-mono" placeholder="/products.php or https://...">
        </div>
      <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($n === 2): ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Categories shown</h3>
    <div class="vh-grid c3">
      <div>
        <label class="<?= $lbl ?>">Which categories</label>
        <select name="<?= $p ?>cat_source" class="<?= $inp ?>">
          <option value="all" <?= v6a_val($k . 'cat_source', 'all') === 'all' ? 'selected' : '' ?>>All categories (in your sort order)</option>
          <option value="selected" <?= v6a_val($k . 'cat_source', 'all') === 'selected' ? 'selected' : '' ?>>Only the ones I tick below</option>
        </select>
      </div>
      <div>
        <label class="<?= $lbl ?>">Maximum tiles (1&ndash;12)</label>
        <input type="number" min="1" max="12" name="<?= $p ?>limit" value="<?= (int)v6a_val($k . 'limit', $d['limit']) ?>" class="<?= $inp ?>">
      </div>
      <div class="vh-box" style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:10px 18px">
        <?php foreach ($cats as $c): ?>
          <label class="vh-sw"><input type="checkbox" name="<?= $p ?>cat_ids[]" value="<?= (int)$c['id'] ?>" <?= in_array((int)$c['id'], $sel_cats, true) ? 'checked' : '' ?>> <?= v6a_e($c['name']) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="vh-grid" style="margin-top:12px">
      <label class="vh-sw"><input type="checkbox" name="<?= $p ?>show_products" value="1" <?= v6a_val($k . 'show_products', '1') !== '0' ? 'checked' : '' ?>> Also show a &ldquo;New arrivals&rdquo; product row under the categories</label>
      <div>
        <label class="<?= $lbl ?>">Products in that row (1&ndash;12)</label>
        <input type="number" min="1" max="12" name="<?= $p ?>prod_limit" value="<?= (int)v6a_val($k . 'prod_limit', '4') ?>" class="<?= $inp ?>">
      </div>
    </div>
<?php elseif ($n === 3 || $n === 4): ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Products shown</h3>
    <div class="vh-grid c3">
      <div>
        <label class="<?= $lbl ?>">Product source</label>
        <select name="<?= $p ?>prod_source" class="<?= $inp ?>">
          <?php foreach (['best_sellers' => 'Best sellers', 'featured' => 'Featured (starred in Products)', 'newest' => 'Newest first', 'category' => 'One category (choose below)'] as $sv => $sl): ?>
            <option value="<?= $sv ?>" <?= v6a_val($k . 'prod_source', $d['prod_source']) === $sv ? 'selected' : '' ?>><?= $sl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="<?= $lbl ?>">Category (only for &ldquo;One category&rdquo;)</label>
        <select name="<?= $p ?>prod_cat" class="<?= $inp ?>">
          <option value="">&mdash;</option>
          <?php foreach ($cats as $c): ?><option value="<?= v6a_e($c['slug']) ?>" <?= v6a_val($k . 'prod_cat', '') === $c['slug'] ? 'selected' : '' ?>><?= v6a_e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="<?= $lbl ?>">How many products (1&ndash;12)</label>
        <input type="number" min="1" max="12" name="<?= $p ?>limit" value="<?= (int)v6a_val($k . 'limit', $d['limit']) ?>" class="<?= $inp ?>">
        <?php if ($n === 4): ?><p class="vh-hint">First product is shown large. Products already shown in section 3 are skipped.</p><?php endif; ?>
      </div>
    </div>
<?php elseif ($n === 7): ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Articles shown</h3>
    <div style="max-width:260px">
      <label class="<?= $lbl ?>">How many articles (1&ndash;12)</label>
      <input type="number" min="1" max="12" name="<?= $p ?>limit" value="<?= (int)v6a_val($k . 'limit', $d['limit']) ?>" class="<?= $inp ?>">
      <p class="vh-hint">The latest published posts from Admin &gt; Blogs are shown automatically.</p>
    </div>
<?php elseif ($n === 5): ?>
    <h3 class="text-xs font-black mt-4 mb-2 uppercase tracking-wide text-gray-500">Artisans shown</h3>
    <div style="max-width:260px">
      <label class="<?= $lbl ?>">How many artisans (1&ndash;12)</label>
      <input type="number" min="1" max="12" name="<?= $p ?>limit" value="<?= (int)v6a_val($k . 'limit', $d['limit']) ?>" class="<?= $inp ?>">
      <p class="vh-hint">Verified vendors from the Vendors list are used.</p>
    </div>
<?php endif; ?>
  </div>
<?php endforeach; ?>

  <div class="vh-card" style="position:sticky;bottom:12px;z-index:10;box-shadow:0 10px 30px rgba(0,0,0,.15)">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <label class="vh-sw"><input type="checkbox" name="activate_layout" value="1" <?= $active_layout === 'layout_6' ? '' : 'checked' ?>> Make Layout 6 the live home page when I save</label>
      <button type="submit" name="save_video_home" value="1" class="vh-btn bg-emerald-700 text-white">Save video home settings</button>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
