<?php
/**
 * GaunGaliHat Rural Heritage & Artisan Blog
 */

$page_title = 'गाँव की कहानियाँ व ब्लॉग (Artisan Heritage & Farming Stories)';
require_once __DIR__ . '/includes/header.php';

$cat_filter = clean_input($_GET['category'] ?? '');
$q = clean_input($_GET['q'] ?? '');

$sql = "SELECT * FROM blogs WHERE status = 'published'";
$params = [];

if (!empty($cat_filter)) {
    $sql .= " AND category = ?";
    $params[] = $cat_filter;
}
if (!empty($q)) {
    $sql .= " AND (title LIKE ? OR hindi_title LIKE ? OR excerpt LIKE ? OR tags LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Distinct categories
$categories = $pdo->query("SELECT DISTINCT category FROM blogs WHERE status = 'published' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="bg-stone-50 min-h-screen py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Hero Banner -->
    <div class="text-center max-w-2xl mx-auto mb-10">
      <span class="inline-block text-xs font-bold text-brand-green bg-emerald-100 px-3 py-1 rounded-full mb-3">
        ग्रामीण हुनर • भारत की मिट्टी की सुगंध
      </span>
      <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 font-display">
        गाँव की कहानियाँ व कृषि परंपरा (Heritage Blog)
      </h1>
      <p class="text-xs sm:text-sm text-gray-600 mt-2">
        हरदोई के हथौड़ों की गूंज, शुद्ध जैविक दालों की मिठास और धूप में पके बनारसी आचार की पारंपरिक विरासत
      </p>
    </div>

    <!-- Category Filter Pills -->
    <div class="flex items-center justify-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none text-xs font-bold">
      <a href="/blogs.php"
        class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= empty($cat_filter) ? 'bg-brand-green text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200' ?>">
        सभी लेख (All Stories)
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="/blogs.php?category=<?= urlencode($cat) ?>"
          class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $cat_filter === $cat ? 'bg-brand-green text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200' ?>">
          <?= htmlspecialchars($cat) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Blog Grid -->
    <?php if (empty($posts)): ?>
      <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center max-w-md mx-auto">
        <i class="fa-solid fa-newspaper text-3xl text-gray-300 mb-3"></i>
        <h3 class="font-bold text-gray-700 text-sm">इस श्रेणी में कोई ब्लॉग नहीं मिला</h3>
        <p class="text-xs text-gray-400 mt-1">कृपया दूसरी श्रेणी चुनें या सभी लेख देखें।</p>
        <a href="/blogs.php" class="mt-4 inline-block px-4 py-2 bg-brand-green text-white text-xs font-bold rounded-xl">सभी लेख देखें</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        <?php foreach ($posts as $p): ?>
          <article class="bg-white rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col group">
            <div class="relative h-48 sm:h-52 overflow-hidden bg-stone-100">
              <img src="<?= htmlspecialchars($p['image'] ?: '/logo.png') ?>" alt="<?= htmlspecialchars($p['title']) ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
              <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                <?= htmlspecialchars($p['category']) ?>
              </span>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
              <div>
                <div class="flex items-center gap-2 text-[11px] text-gray-400 mb-2">
                  <span class="flex items-center gap-1">
                    <i class="fa-regular fa-calendar text-[10px]"></i>
                    <?= date('d M Y', strtotime($p['created_at'])) ?>
                  </span>
                  <span>•</span>
                  <span class="flex items-center gap-1 font-semibold text-gray-600">
                    <i class="fa-regular fa-user text-[10px]"></i>
                    <?= htmlspecialchars($p['author_name']) ?>
                  </span>
                </div>

                <h2 class="text-base font-extrabold text-gray-900 group-hover:text-brand-green transition leading-snug line-clamp-2">
                  <a href="/blog-detail.php?slug=<?= urlencode($p['slug']) ?>">
                    <?= htmlspecialchars($p['title']) ?>
                  </a>
                </h2>

                <?php if (!empty($p['hindi_title'])): ?>
                  <p class="text-xs text-gray-500 font-medium mt-1 line-clamp-1">
                    <?= htmlspecialchars($p['hindi_title']) ?>
                  </p>
                <?php endif; ?>

                <p class="text-xs text-gray-600 mt-2.5 line-clamp-3 leading-relaxed">
                  <?= htmlspecialchars($p['excerpt']) ?>
                </p>
              </div>

              <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-gray-400 font-mono">
                  <i class="fa-regular fa-eye mr-1"></i><?= number_format($p['views'] ?? 120) ?> पाठकों ने पढ़ा
                </span>
                <a href="/blog-detail.php?slug=<?= urlencode($p['slug']) ?>"
                  class="font-bold text-brand-green hover:underline flex items-center gap-1">
                  पूरा पढ़ें <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
