<?php
/**
 * GaunGaliHat Single Product Detail Page
 */

require_once __DIR__ . '/includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header('Location: /products.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug, 
           v.id as vendor_id, v.name as vendor_name, v.business_name as vendor_business, v.avatar as vendor_avatar, 
           v.location as vendor_location, v.district as vendor_district, v.state as vendor_state, 
           v.story as vendor_story, v.verified as vendor_verified, v.rating as vendor_rating
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN vendors v ON p.vendor_id = v.id 
    WHERE p.id = ? LIMIT 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: /products.php');
    exit;
}

$page_title = $product['name'] . ' — ' . APP_NAME;
$gallery = json_decode($product['gallery'] ?? '[]', true) ?: [$product['image']];
$specifications = json_decode($product['specifications'] ?? '{}', true) ?: [];

// Fetch Related Products from same category
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
  
  <!-- Breadcrumbs -->
  <div class="flex items-center gap-2 text-xs text-gray-500">
    <a href="/index.php" class="hover:text-brand-green">Home</a>
    <span>/</span>
    <a href="/products.php?category=<?= htmlspecialchars($product['category_slug']) ?>" class="hover:text-brand-green"><?= htmlspecialchars($product['category_name']) ?></a>
    <span>/</span>
    <span class="text-gray-900 font-semibold truncate"><?= htmlspecialchars($product['name']) ?></span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
    
    <!-- Left Column: Image Gallery -->
    <div class="lg:col-span-6 space-y-4">
      <div class="relative w-full h-80 sm:h-96 md:h-[450px] rounded-3xl overflow-hidden bg-white border border-gray-200 shadow-sm">
        <img id="main-product-img" src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
        <?php if ($product['discount'] > 0): ?>
          <span class="absolute top-4 left-4 px-3 py-1 bg-brand-orange text-white text-xs font-bold rounded-xl shadow-md">
            <?= $product['discount'] ?>% OFF
          </span>
        <?php endif; ?>
        <button onclick="toggleWishlist(<?= $product['id'] ?>, this)" class="absolute top-4 right-4 p-2.5 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-sm text-gray-400 hover:text-brand-orange transition-colors cursor-pointer">
          <i data-lucide="heart" class="w-5 h-5 <?= is_in_wishlist($product['id']) ? 'text-brand-orange fill-current' : '' ?>"></i>
        </button>
      </div>

      <!-- Thumbnail Strip -->
      <?php if (count($gallery) > 1): ?>
        <div class="flex items-center gap-3 overflow-x-auto pb-2">
          <?php foreach ($gallery as $imgUrl): ?>
            <button onclick="document.getElementById('main-product-img').src='<?= htmlspecialchars($imgUrl) ?>'" class="w-16 h-16 rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-green transition-all shrink-0">
              <img src="<?= htmlspecialchars($imgUrl) ?>" class="w-full h-full object-cover">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Authenticity Guarantee -->
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-900">
        <i data-lucide="shield-check" class="w-6 h-6 text-brand-green shrink-0"></i>
        <div>
          <span class="font-bold">100% Genuine Artisan Origin Guaranteed:</span>
          <span>Forged / Produced directly in <?= htmlspecialchars($product['vendor_location'] ?? 'Hardoi, Uttar Pradesh') ?>.</span>
        </div>
      </div>
    </div>

    <!-- Right Column: Product Info & Actions -->
    <div class="lg:col-span-6 space-y-6">
      
      <!-- Header -->
      <div class="space-y-1.5 border-b border-gray-200 pb-4">
        <div class="flex items-center gap-2 text-xs">
          <span class="px-2 py-0.5 rounded-md bg-stone-100 font-mono text-gray-700 font-bold"><?= htmlspecialchars($product['code']) ?></span>
          <span class="text-brand-green font-bold uppercase tracking-wider"><?= htmlspecialchars($product['category_name']) ?></span>
          <span class="text-gray-400">•</span>
          <span class="text-amber-600 font-bold flex items-center gap-1">
            ★ <?= $product['rating'] ?> <span class="text-gray-400 font-normal">(<?= $product['review_count'] ?> reviews)</span>
          </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          <?= htmlspecialchars($product['name']) ?>
        </h1>
        <?php if (!empty($product['hindi_name'])): ?>
          <p class="text-base font-semibold text-gray-600 font-hindi"><?= htmlspecialchars($product['hindi_name']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Pricing -->
      <div class="space-y-1">
        <div class="flex items-baseline gap-3">
          <span class="text-3xl font-extrabold text-gray-900 font-display"><?= format_price($product['price']) ?></span>
          <?php if ($product['original_price'] > $product['price']): ?>
            <span class="text-base text-gray-400 line-through"><?= format_price($product['original_price']) ?></span>
            <span class="px-2 py-0.5 bg-orange-100 text-brand-orange font-bold text-xs rounded-md">
              Save <?= format_price($product['original_price'] - $product['price']) ?>
            </span>
          <?php endif; ?>
        </div>
        <p class="text-[11px] text-gray-400">Inclusive of all local taxes. Free shipping on orders above ₹499.</p>
      </div>

      <!-- Stock & Add to Cart -->
      <div class="p-6 bg-white rounded-3xl border border-gray-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between text-xs">
          <span class="font-bold text-emerald-800 flex items-center gap-1.5">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-brand-green"></i>
            <span><?= htmlspecialchars($product['stock_status']) ?> (<?= $product['stock'] ?> units ready for dispatch)</span>
          </span>
          <span class="text-gray-500 font-mono">Weight: <?= $product['weight_kg'] ?> kg</span>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center border border-gray-300 rounded-xl bg-gray-50 p-1">
            <button onclick="decrementQty()" class="w-8 h-8 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded-lg cursor-pointer">-</button>
            <input type="number" id="detail-qty" value="1" min="1" max="<?= $product['stock'] ?>" class="w-12 text-center text-sm font-bold bg-transparent outline-none">
            <button onclick="incrementQty()" class="w-8 h-8 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded-lg cursor-pointer">+</button>
          </div>

          <button 
            onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('detail-qty').value))" 
            class="flex-1 py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md flex items-center justify-center gap-2 cursor-pointer transition-all active:scale-95"
          >
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Add to Cart</span>
          </button>
        </div>
      </div>

      <!-- Dynamic Delivery Pincode Checker -->
      <div class="p-5 bg-stone-50 rounded-3xl border border-gray-200 space-y-3 text-xs">
        <div class="flex items-center justify-between">
          <span class="font-bold text-gray-900 flex items-center gap-1.5">
            <i data-lucide="truck" class="w-4 h-4 text-brand-green"></i>
            <span>Check India Post Delivery Charges & ETA</span>
          </span>
          <span class="text-[11px] text-gray-500 font-mono">Origin: Hardoi (UP)</span>
        </div>

        <div class="flex gap-2">
          <input 
            type="text" 
            id="pincode-input-detail" 
            maxlength="6" 
            value="<?= htmlspecialchars($_SESSION['shipping_pincode'] ?? '241126') ?>" 
            placeholder="Enter 6-digit Pincode" 
            class="flex-1 p-2.5 bg-white border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 outline-none focus:border-brand-green"
          >
          <button onclick="checkDetailPincode()" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs cursor-pointer">
            Check Rate
          </button>
        </div>
        <div id="pincode-detail-result" class="text-xs"></div>
      </div>

      <!-- Description -->
      <div class="space-y-2 text-xs text-gray-700 leading-relaxed">
        <h3 class="font-bold text-sm text-gray-900">About this Product</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      </div>

      <!-- Specifications Table -->
      <?php if (!empty($specifications)): ?>
        <div class="border border-gray-200 rounded-2xl overflow-hidden text-xs">
          <div class="bg-stone-100 px-4 py-2.5 font-bold text-gray-900 border-b border-gray-200">
            Technical & Craft Specifications
          </div>
          <div class="divide-y divide-gray-100">
            <?php foreach ($specifications as $k => $v): ?>
              <div class="grid grid-cols-2 p-3 bg-white">
                <span class="text-gray-500 font-medium"><?= htmlspecialchars($k) ?></span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($v) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Artisan / Vendor Card -->
      <div class="p-5 bg-white rounded-3xl border border-gray-200 shadow-2xs space-y-3 text-xs">
        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Artisan Workshop / Cluster</span>
        <div class="flex items-center gap-3">
          <img src="<?= htmlspecialchars($product['vendor_avatar']) ?>" alt="<?= htmlspecialchars($product['vendor_name']) ?>" class="w-12 h-12 rounded-2xl object-cover border border-emerald-200">
          <div>
            <h4 class="font-bold text-sm text-gray-900"><?= htmlspecialchars($product['vendor_name']) ?></h4>
            <p class="text-gray-500">📍 <?= htmlspecialchars($product['vendor_location']) ?></p>
          </div>
        </div>
        <p class="text-gray-600 leading-relaxed italic">"<?= htmlspecialchars($product['vendor_story']) ?>"</p>
      </div>

    </div>

  </div>

  <!-- Related Products -->
  <?php if (!empty($related_products)): ?>
    <section class="border-t border-gray-200 pt-10 space-y-6">
      <h3 class="text-xl font-bold font-display text-gray-900">You may also need from this cluster:</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($related_products as $rp): ?>
          <div class="bg-white rounded-3xl border border-gray-200 p-4 space-y-3 hover:shadow-md transition-all">
            <a href="/product.php?id=<?= $rp['id'] ?>">
              <img src="<?= htmlspecialchars($rp['image']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" class="w-full h-40 object-cover rounded-2xl">
              <h4 class="font-bold text-xs text-gray-900 truncate mt-2"><?= htmlspecialchars($rp['name']) ?></h4>
              <span class="font-extrabold text-sm text-gray-900 block mt-1"><?= format_price($rp['price']) ?></span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<script>
function incrementQty() {
  const input = document.getElementById('detail-qty');
  input.value = parseInt(input.value) + 1;
}
function decrementQty() {
  const input = document.getElementById('detail-qty');
  if (parseInt(input.value) > 1) {
    input.value = parseInt(input.value) - 1;
  }
}
function checkDetailPincode() {
  const pin = document.getElementById('pincode-input-detail').value.trim();
  const resDiv = document.getElementById('pincode-detail-result');
  if (pin.length !== 6) {
    resDiv.innerHTML = '<span class="text-red-600">Please enter a valid 6-digit PIN code.</span>';
    return;
  }
  resDiv.innerHTML = '<span class="text-gray-500">Calculating accurate delivery rate...</span>';

  const formData = new FormData();
  formData.append('pincode', pin);
  formData.append('subtotal', <?= $product['price'] ?>);
  formData.append('total_weight', <?= $product['weight_kg'] ?>);

  fetch('/ajax/calculate_shipping.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const d = data.data;
        resDiv.innerHTML = `
          <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 space-y-1">
            <div class="flex justify-between font-bold">
              <span>Delivery Fee: ${d.shipping_fee_formatted}</span>
              <span>ETA: ${d.eta_text}</span>
            </div>
            <p class="text-[11px] text-emerald-700">${d.zone_name} via ${d.carrier}</p>
          </div>
        `;
      } else {
        resDiv.innerHTML = `<span class="text-red-600">${data.message}</span>`;
      }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
