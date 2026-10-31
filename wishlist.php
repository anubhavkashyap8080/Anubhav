<?php
/**
 * GaunGaliHat Wishlist Page
 */

require_once __DIR__ . '/includes/header.php';

$wishlist_ids = array_values(array_filter(array_map('intval', (array)($_SESSION['wishlist'] ?? []))));
$wishlist_products = [];

if (!empty($wishlist_ids) && $pdo) {
    try {
        $placeholders = implode(',', array_fill(0, count($wishlist_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, v.name as vendor_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vendors v ON p.vendor_id = v.id 
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($wishlist_ids);
        $wishlist_products = $stmt->fetchAll() ?: [];
        foreach ($wishlist_products as &$wlp) {
            if (isset($wlp['vendor_name'])) {
                $wlp['vendor_name'] = str_ireplace('S S Industries', 'Kalyani agrofood pvt ltd', $wlp['vendor_name']);
            }
        }
        unset($wlp);
    } catch (Throwable $e_wl) {
        $wishlist_products = [];
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
  <div class="flex items-center justify-between border-b border-gray-200 pb-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
        My Saved Wishlist (<?= count($wishlist_products) ?>)
      </h1>
      <p class="text-xs text-gray-500 mt-1">Village implements, pure farm pulses, and pickles you have saved for later.</p>
    </div>
    <a href="/products.php" class="text-xs font-bold text-brand-green hover:underline">
      ← Continue Browsing
    </a>
  </div>

  <?php if (empty($wishlist_products)): ?>
    <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center space-y-4 max-w-md mx-auto my-8">
      <div class="w-16 h-16 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto">
        <i data-lucide="heart" class="w-8 h-8"></i>
      </div>
      <h3 class="font-bold text-lg text-gray-900 font-display">Your wishlist is empty</h3>
      <p class="text-xs text-gray-500">Tap the heart icon on any product to save it here for convenient checkout.</p>
      <a href="/products.php" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-green text-white font-bold text-xs rounded-xl shadow-xs">
        <span>Explore Catalog</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($wishlist_products as $p): ?>
        <div class="bg-white rounded-3xl border border-gray-200 p-4 flex flex-col justify-between space-y-3 relative group">
          <button onclick="Promise.resolve(toggleWishlist(<?= $p['id'] ?>, this)).finally(function(){ window.location.reload(); });" class="absolute top-6 right-6 z-10 w-8 h-8 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-brand-orange hover:bg-orange-50 cursor-pointer">
            <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
          </button>

          <a href="/product.php?id=<?= $p['id'] ?>">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-44 object-cover rounded-2xl">
            <span class="text-[10px] font-mono text-gray-400 block mt-2"><?= htmlspecialchars($p['code']) ?></span>
            <h4 class="font-bold text-xs text-gray-900 line-clamp-2 mt-0.5"><?= htmlspecialchars($p['name']) ?></h4>
            <span class="text-sm font-extrabold text-gray-900 font-display block mt-1"><?= format_price($p['price']) ?></span>
          </a>

          <div class="pt-2 border-t border-gray-100">
            <button onclick="Promise.resolve(addToCart(<?= $p['id'] ?>, 1)).then(function(){ return toggleWishlist(<?= $p['id'] ?>); }).finally(function(){ window.location.reload(); });" class="w-full py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold cursor-pointer transition-colors flex items-center justify-center gap-1.5">
              <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
              <span>Move to Cart</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
