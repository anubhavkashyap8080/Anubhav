<?php
/**
 * GaunGaliHat Single Blog Article Detail View
 */

require_once __DIR__ . '/includes/functions.php';

$slug = clean_input($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: /blogs.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: /blogs.php');
    exit;
}

// Increment views
$pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);

$page_title = $blog['title'] . ' — गाँव गली हाट ब्लॉग';
$page_desc = $blog['excerpt'] ?? 'GaunGaliHat rural artisan and agricultural heritage story.';
require_once __DIR__ . '/includes/header.php';

// Related blogs
$stmt_rel = $pdo->prepare("SELECT * FROM blogs WHERE id != ? AND category = ? AND status = 'published' LIMIT 3");
$stmt_rel->execute([$blog['id'], $blog['category']]);
$related = $stmt_rel->fetchAll();
?>

<div class="bg-stone-50 min-h-screen py-10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
      <a href="/" class="hover:text-brand-green">होम</a>
      <span>/</span>
      <a href="/blogs.php" class="hover:text-brand-green">ब्लॉग व कहानियाँ</a>
      <span>/</span>
      <span class="text-gray-800 font-bold truncate max-w-xs"><?= htmlspecialchars($blog['title']) ?></span>
    </nav>

    <!-- Main Article Card -->
    <article class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden p-6 sm:p-10 mb-10">
      
      <!-- Category Badge -->
      <span class="inline-block bg-indigo-50 text-indigo-700 font-bold text-xs px-3 py-1 rounded-full border border-indigo-200 mb-3">
        <?= htmlspecialchars($blog['category']) ?>
      </span>

      <!-- Title -->
      <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 leading-tight font-display mb-3">
        <?= htmlspecialchars($blog['title']) ?>
      </h1>

      <?php if (!empty($blog['hindi_title'])): ?>
        <h2 class="text-base sm:text-xl font-bold text-gray-600 mb-6">
          <?= htmlspecialchars($blog['hindi_title']) ?>
        </h2>
      <?php endif; ?>

      <!-- Author & Meta bar -->
      <div class="flex items-center justify-between flex-wrap gap-4 py-4 border-y border-gray-100 text-xs text-gray-500 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-brand-green text-white font-bold flex items-center justify-center text-sm shadow-xs">
            <?= strtoupper(mb_substr($blog['author_name'], 0, 1)) ?>
          </div>
          <div>
            <div class="font-bold text-gray-900"><?= htmlspecialchars($blog['author_name']) ?></div>
            <div class="text-[11px] text-gray-400">गाँव गली हाट संपादकीय मंडल</div>
          </div>
        </div>

        <div class="flex items-center gap-3 text-[11px]">
          <span><i class="fa-regular fa-calendar mr-1"></i><?= date('d F Y', strtotime($blog['created_at'])) ?></span>
          <span>•</span>
          <span><i class="fa-regular fa-eye mr-1"></i><?= number_format($blog['views'] + 1) ?> व्यूज</span>
        </div>
      </div>

      <!-- Featured Image -->
      <?php if (!empty($blog['image'])): ?>
        <div class="rounded-2xl overflow-hidden mb-8 shadow-md">
          <img src="<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>"
            class="w-full max-h-[420px] object-cover">
        </div>
      <?php endif; ?>

      <!-- Article Content -->
      <div class="prose max-w-none text-sm sm:text-base text-gray-700 leading-relaxed space-y-4">
        <?= nl2br(htmlspecialchars($blog['content'])) ?>
      </div>

      <!-- Tags -->
      <?php if (!empty($blog['tags'])): ?>
        <div class="pt-6 mt-8 border-t border-gray-100 flex flex-wrap items-center gap-2">
          <span class="text-xs font-bold text-gray-400">टैग्स:</span>
          <?php foreach (explode(',', $blog['tags']) as $tag): ?>
            <span class="text-[11px] bg-stone-100 text-stone-700 font-semibold px-2.5 py-1 rounded-lg">
              #<?= htmlspecialchars(trim($tag)) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Social Share -->
      <div class="mt-8 p-4 bg-stone-50 rounded-2xl flex flex-wrap items-center justify-between gap-3 text-xs">
        <span class="font-bold text-gray-700">इस लेख को मित्रों के साथ साझा करें:</span>
        <div class="flex items-center gap-2">
          <a href="https://wa.me/?text=<?= urlencode($blog['title'] . ' ' . 'https://ganvgalihat.com/blog-detail.php?slug=' . $blog['slug']) ?>" target="_blank"
            class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition flex items-center gap-1">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
          </a>
          <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode('https://ganvgalihat.com/blog-detail.php?slug=' . $blog['slug']) ?>" target="_blank"
            class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
            <i class="fa-brands fa-facebook"></i> Facebook
          </a>
        </div>
      </div>

    </article>

    <!-- Related Stories -->
    <?php if (!empty($related)): ?>
      <div class="mb-10">
        <h3 class="text-lg font-bold text-gray-900 font-display mb-4">अन्य संबंधित कहानियाँ</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <?php foreach ($related as $rb): ?>
            <a href="/blog-detail.php?slug=<?= urlencode($rb['slug']) ?>"
              class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs hover:shadow-md transition group block">
              <img src="<?= htmlspecialchars($rb['image'] ?: '/logo.png') ?>" alt="<?= htmlspecialchars($rb['title']) ?>"
                class="w-full h-32 object-cover rounded-xl mb-3">
              <h4 class="text-xs font-bold text-gray-900 group-hover:text-brand-green line-clamp-2 leading-snug">
                <?= htmlspecialchars($rb['title']) ?>
              </h4>
              <span class="text-[10px] text-gray-400 mt-2 block"><?= date('d M Y', strtotime($rb['created_at'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
