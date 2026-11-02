<?php
/**
 * GanvGaliHat — Shipping & Return Policy
 * Dynamically loaded from Super Admin CMS with English default & bilingual support
 */

require_once __DIR__ . '/includes/functions.php';

$page_data = get_cms_page('shipping-policy');
$page_title = (!empty($page_data['title']) ? $page_data['title'] : 'Shipping & Return Policy') . ' — GanvGaliHat';
$page_desc = !empty($page_data['meta_description']) ? $page_data['meta_description'] : 'Official Shipping & Return Policy of GanvGaliHat rural marketplace.';

require_once __DIR__ . '/includes/header.php';

$title_en = !empty($page_data['title']) ? $page_data['title'] : 'Shipping & Return Policy';
$title_hi = !empty($page_data['hindi_title']) ? $page_data['hindi_title'] : 'शिपिंग एवं वापसी नीति (Shipping & Return Policy)';
$content_en = !empty($page_data['content_en']) ? $page_data['content_en'] : '<p>Content coming soon.</p>';
$content_hi = !empty($page_data['content_hi']) ? $page_data['content_hi'] : '<p>सामग्री जल्द आ रही है।</p>';
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
      
      <!-- Page Header -->
      <div class="border-b border-gray-100 pb-6">
        <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg uppercase tracking-wider mb-2">
          <span class="lang-en">Official Policy</span>
          <span class="lang-hi hidden">आधिकारिक नीति</span>
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
          <span class="lang-en"><?= htmlspecialchars($title_en) ?></span>
          <span class="lang-hi hidden"><?= htmlspecialchars($title_hi) ?></span>
        </h1>
        <p class="text-xs text-gray-400 mt-1">
          <span class="lang-en">Last updated: <?= date('F d, Y') ?></span>
          <span class="lang-hi hidden">अंतिम अद्यतन: <?= date('d F, Y') ?></span>
        </p>
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
