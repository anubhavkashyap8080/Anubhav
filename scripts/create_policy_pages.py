# -*- coding: utf-8 -*-
"""
Generate dedicated policy and CMS viewer pages:
1. privacy-policy.php
2. terms-conditions.php
3. shipping-policy.php
4. terms-of-supply.php
5. page.php (Dynamic CMS Router)
"""
import os

base_dir = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat'

def generate_policy_page_code(slug, fallback_title, fallback_hi_title):
    return f"""<?php
/**
 * GanvGaliHat — {fallback_title}
 * Dynamically loaded from Super Admin CMS with English default & bilingual support
 */

require_once __DIR__ . '/includes/functions.php';

$page_data = get_cms_page('{slug}');
$page_title = (!empty($page_data['title']) ? $page_data['title'] : '{fallback_title}') . ' — GanvGaliHat';
$page_desc = !empty($page_data['meta_description']) ? $page_data['meta_description'] : 'Official {fallback_title} of GanvGaliHat rural marketplace.';

require_once __DIR__ . '/includes/header.php';

$title_en = !empty($page_data['title']) ? $page_data['title'] : '{fallback_title}';
$title_hi = !empty($page_data['hindi_title']) ? $page_data['hindi_title'] : '{fallback_hi_title}';
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
"""

# Pages to generate
pages_config = [
    ('privacy-policy.php', 'privacy-policy', 'Privacy Policy', 'गोपनीयता नीति (Privacy Policy)'),
    ('terms-conditions.php', 'terms-conditions', 'Terms & Conditions', 'नियम एवं शर्तें (Terms & Conditions)'),
    ('shipping-policy.php', 'shipping-policy', 'Shipping & Return Policy', 'शिपिंग एवं वापसी नीति (Shipping & Return Policy)'),
    ('terms-of-supply.php', 'terms-of-supply', 'Terms of Supply & Quality Pledge', 'आपूर्ति के नियम एवं गुणवत्ता वचन (Terms of Supply)')
]

for filename, slug, title_en, title_hi in pages_config:
    filepath = os.path.join(base_dir, filename)
    code = generate_policy_page_code(slug, title_en, title_hi)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(code)
    print(f"Created {filename}")

# Generate page.php (Dynamic CMS Page Router)
page_php_code = """<?php
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
"""

with open(os.path.join(base_dir, 'page.php'), 'w', encoding='utf-8') as f:
    f.write(page_php_code)
print("Created page.php")
