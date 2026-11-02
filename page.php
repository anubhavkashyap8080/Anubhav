<?php
/**
 * GanvGaliHat — Dynamic CMS Page Router
 * Renders any custom page created via Super Admin CMS
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim(strtolower($_GET['slug'] ?? ''));
if (empty($slug)) {
    header('Location: /');
    exit;
}

$page_data = get_cms_page($slug);

if (!$page_data || empty($page_data['is_active'])) {
    http_response_code(404);
    $page_title = 'Page Not Found — GanvGaliHat';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="max-w-2xl mx-auto px-4 py-20 text-center space-y-4">
      <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center mx-auto text-2xl font-bold">404</div>
      <h1 class="text-2xl font-black text-gray-900">Page Not Found</h1>
      <p class="text-sm text-gray-500">The page you requested does not exist or has been unpublished.</p>
      <a href="/" class="inline-block px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs rounded-xl transition">Return Home</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = (!empty($page_data['title']) ? $page_data['title'] : 'Page') . ' — GanvGaliHat';
$page_desc = !empty($page_data['meta_description']) ? $page_data['meta_description'] : '';

require_once __DIR__ . '/includes/header.php';

$title_en = !empty($page_data['title']) ? $page_data['title'] : '';
$title_hi = !empty($page_data['hindi_title']) ? $page_data['hindi_title'] : $title_en;
$content_en = !empty($page_data['content_en']) ? $page_data['content_en'] : '';
$content_hi = !empty($page_data['content_hi']) ? $page_data['content_hi'] : $content_en;
?>

<!-- Breadcrumbs & Language Switcher Bar -->
<div class="bg-stone-100 border-b border-stone-200 py-3">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2 text-gray-500">
      <a href="/" class="hover:text-emerald-800 font-medium">
        <span class="lang-en">Home</span>
        <span class="lang-hi hidden">होम</span>
      </a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
      <span class="text-stone-900 font-bold">
        <span class="lang-en"><?= htmlspecialchars($title_en) ?></span>
        <span class="lang-hi hidden"><?= htmlspecialchars($title_hi) ?></span>
      </span>
    </div>

    <!-- Bilingual Switcher Toggle (English Default) -->
    <div class="flex items-center gap-2">
      <span class="text-[11px] font-bold text-gray-400">Language:</span>
      <?= render_bilingual_toggle('en') ?>
    </div>
  </div>
</div>

<main class="py-10 sm:py-14 bg-stone-50 min-h-[60vh]">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-12 shadow-sm space-y-6">
      
      <div class="border-b border-gray-100 pb-6">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
          <span class="lang-en"><?= htmlspecialchars($title_en) ?></span>
          <span class="lang-hi hidden"><?= htmlspecialchars($title_hi) ?></span>
        </h1>
      </div>

      <!-- English Content (Default) -->
      <div class="lang-en prose max-w-none">
        <?= $content_en ?>
      </div>

      <!-- Hindi Content -->
      <div class="lang-hi hidden prose max-w-none">
        <?= $content_hi ?>
      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
