<?php
/**
 * GaunGaliHat Products Catalog Page
 */

require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$category_slug = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$in_stock_only = !empty($_GET['in_stock']);

// Load all categories for filter sidebar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
$all_categories = $stmt->fetchAll();

// Build query
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug, v.name as vendor_name, v.location as vendor_location 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.hindi_name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
    $p = '%' . $search . '%';
    $params[] = $p;
    $params[] = $p;
    $params[] = $p;
    $params[] = $p;
}

if (!empty($category_slug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
}

if ($in_stock_only) {
    $sql .= " AND p.stock > 0";
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY p.rating DESC";
        break;
    case 'bestselling':
        $sql .= " ORDER BY p.is_best_seller DESC, p.id DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.id DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get active category details if selected
$active_category = null;
if (!empty($category_slug)) {
    foreach ($all_categories as $cat) {
        if ($cat['slug'] === $category_slug) {
            $active_category = $cat;
            break;
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
  
  <!-- Breadcrumbs & Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
        <a href="/index.php" class="hover:text-brand-green">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold"><?= $active_category ? htmlspecialchars($active_category['name']) : 'All Products' ?></span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
        <?= $active_category ? htmlspecialchars($active_category['name']) : (empty($search) ? 'Rural Artisan & Produce Catalog' : 'Search Results for "' . htmlspecialchars($search) . '"') ?>
      </h1>
      <?php if ($active_category && !empty($active_category['hindi_name'])): ?>
        <p class="text-xs text-gray-500 font-hindi mt-0.5"><?= htmlspecialchars($active_category['hindi_name']) ?> — <?= htmlspecialchars($active_category['description']) ?></p>
      <?php endif; ?>
    </div>

    <!-- Active Count & Fast Filter -->
    <div class="flex items-center gap-3">
      <span class="text-xs text-gray-500 font-mono"><?= count($products) ?> Products found</span>
      <form method="GET" class="flex items-center gap-2">
        <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
        <?php if (!empty($category_slug)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category_slug) ?>"><?php endif; ?>
        <select name="sort" onchange="this.form.submit()" class="text-xs bg-white border border-gray-300 rounded-xl px-3 py-2 outline-none font-semibold text-gray-700 cursor-pointer">
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest First</option>
          <option value="bestselling" <?= $sort === 'bestselling' ? 'selected' : '' ?>>Sort: Bestsellers</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
        </select>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    <!-- Sidebar Filters -->
    <aside class="space-y-6">
      <div class="bg-white rounded-3xl border border-gray-200 p-6 space-y-5 shadow-xs sticky top-28">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
            <i data-lucide="filter" class="w-4 h-4 text-brand-green"></i>
            <span>Filter Categories</span>
          </h3>
          <a href="/products.php" class="text-[11px] text-brand-orange font-bold hover:underline">Reset</a>
        </div>

        <ul class="space-y-1.5 text-xs font-semibold">
          <li>
            <a href="/products.php<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>" class="p-2 rounded-xl flex items-center justify-between transition-colors <?= empty($category_slug) ? 'bg-emerald-50 text-brand-green font-bold' : 'text-gray-700 hover:bg-gray-50' ?>">
              <span>All Categories</span>
              <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
            </a>
          </li>
          <?php foreach ($all_categories as $cat): ?>
            <li>
              <a href="/products.php?category=<?= htmlspecialchars($cat['slug']) ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="p-2 rounded-xl flex items-center justify-between transition-colors <?= $category_slug === $cat['slug'] ? 'bg-emerald-50 text-brand-green font-bold' : 'text-gray-700 hover:bg-gray-50' ?>">
                <span class="truncate pr-1"><?= htmlspecialchars($cat['name']) ?></span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="pt-4 border-t border-gray-100 space-y-2 text-xs">
          <span class="font-bold text-gray-800 block">Stock Availability</span>
          <label class="flex items-center gap-2 cursor-pointer text-gray-600">
            <input type="checkbox" onchange="window.location.href='/products.php?category=<?= urlencode($category_slug) ?>&in_stock=' + (this.checked ? '1' : '')" <?= $in_stock_only ? 'checked' : '' ?> class="rounded text-brand-green focus:ring-brand-green">
            <span>In Stock Only</span>
          </label>
        </div>

        <!-- Free Delivery Banner in Sidebar -->
        <div class="p-3.5 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl text-[11px] text-emerald-900 space-y-1">
          <div class="flex items-center gap-1.5 font-bold">
            <i data-lucide="truck" class="w-4 h-4 text-brand-green shrink-0"></i>
            <span>Free Delivery Over ₹499</span>
          </div>
          <p class="text-emerald-700">Orders dispatched via India Post Registered Parcel directly from Hardoi & Varanasi artisan clusters.</p>
        </div>

      </div>
    </aside>

    <!-- Main Products Grid -->
    <main class="lg:col-span-3">
      <?php if (empty($products)): ?>
        <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center space-y-4">
          <div class="w-16 h-16 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto">
            <i data-lucide="search" class="w-8 h-8"></i>
          </div>
          <h3 class="font-bold text-base text-gray-800">No products match your criteria</h3>
          <p class="text-xs text-gray-500 max-w-sm mx-auto">Try clearing your filters or search keywords to explore all authentic rural implements and farm produce.</p>
          <a href="/products.php" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand-green text-white font-bold text-xs rounded-xl shadow-xs">
            <span>View Full Catalog</span>
          </a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <?php foreach ($products as $p): ?>
            <div class="bg-white rounded-3xl border border-gray-200 hover:border-emerald-600/50 hover:shadow-xl transition-all duration-300 p-4 flex flex-col justify-between group relative">
              
              <!-- Wishlist Toggle -->
              <button 
                onclick="toggleWishlist(<?= $p['id'] ?>, this, event)" 
                class="absolute top-6 right-6 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand-orange transition-colors cursor-pointer"
              >
                <i data-lucide="heart" class="w-4 h-4"></i>
              </button>

              <a href="/product.php?id=<?= $p['id'] ?>" class="space-y-3 block">
                <div class="relative w-full h-48 rounded-2xl overflow-hidden bg-stone-100">
                  <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  <?php if ($p['discount'] > 0): ?>
                    <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-brand-orange text-white text-[10px] font-bold rounded-lg shadow-xs">
                      <?= $p['discount'] ?>% OFF
                    </span>
                  <?php endif; ?>
                </div>

                <div>
                  <div class="flex items-center justify-between text-[10px] text-gray-400 font-semibold mb-1">
                    <span class="font-mono text-gray-600"><?= htmlspecialchars($p['code']) ?></span>
                    <span class="text-emerald-700 font-bold"><?= htmlspecialchars($p['category_name']) ?></span>
                  </div>
                  <h3 class="font-bold text-sm text-gray-900 group-hover:text-brand-green transition-colors line-clamp-2">
                    <?= htmlspecialchars($p['name']) ?>
                  </h3>
                  <?php if (!empty($p['hindi_name'])): ?>
                    <p class="text-[11px] text-gray-500 font-hindi truncate mt-0.5"><?= htmlspecialchars($p['hindi_name']) ?></p>
                  <?php endif; ?>
                </div>
              </a>

              <!-- Price & Add -->
              <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-3">
                <div>
                  <div class="flex items-baseline gap-1.5">
                    <span class="text-lg font-extrabold text-gray-900 font-display"><?= format_price($p['price']) ?></span>
                    <?php if ($p['original_price'] > $p['price']): ?>
                      <span class="text-xs text-gray-400 line-through"><?= format_price($p['original_price']) ?></span>
                    <?php endif; ?>
                  </div>
                  <span class="text-[10px] text-emerald-700 font-bold block">✓ <?= htmlspecialchars($p['stock_status']) ?></span>
                </div>

                <button 
                  onclick="addToCart(<?= $p['id'] ?>, 1, event)" 
                  class="px-3.5 py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 cursor-pointer transition-colors active:scale-95"
                >
                  <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                  <span>Add</span>
                </button>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
