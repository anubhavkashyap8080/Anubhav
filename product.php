<?php
/**
 * GanvGaliHat Single Product Detail Page
 */

require_once __DIR__ . '/includes/functions.php';

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    if (!headers_sent()) {
        header('Location: /products.php');
    } else {
        echo '<script>window.location="/products.php";</script>';
    }
    exit;
}

$product = null;
if ($pdo) {
    try {
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
        if ($product) {
            if (isset($product['vendor_name'])) {
                $product['vendor_name'] = str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $product['vendor_name']);
            }
            if (isset($product['vendor_business'])) {
                $product['vendor_business'] = str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $product['vendor_business']);
            }
            if (isset($product['description'])) {
                $product['description'] = str_ireplace(['S S Industries', 'SS Industries', 'S. S. Industries', 'S.S. Industries'], 'Kalyani agrofood pvt ltd', $product['description']);
            }
        }
    } catch (Throwable $e_prod) {
        $product = null;
    }
}

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: /products.php');
    exit;
}

$page_title = !empty($product['seo_title']) ? $product['seo_title'] : ($product['name'] . ' — ' . APP_NAME);
$page_desc = !empty($product['seo_description']) ? $product['seo_description'] : mb_substr(strip_tags($product['description'] ?? ''), 0, 160);
$page_keywords = !empty($product['seo_keywords']) ? $product['seo_keywords'] : ($product['name'] . ', ' . ($product['category_name'] ?? '') . ', buy online, ' . APP_NAME);
if (!empty($product['image'])) {
    $og_image = $product['image'];
}
$gallery = json_decode($product['gallery'] ?? '[]', true) ?: [$product['image']];
$specifications = json_decode($product['specifications'] ?? '{}', true) ?: [];

// Fetch Related Products from same category
$stmt_rel = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt_rel->execute([$product['category_id'], $product_id]);
$related_products = $stmt_rel->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- JSON-LD Structured Data: Product & Breadcrumbs (Google Merchant Listings Compliant) -->
<?php
// Compute Schema Offer Dates & Shipping Rates for Merchant Listings
$offer_valid_from = !empty($product['created_at']) 
    ? date('Y-m-d\TH:i:sP', strtotime($product['created_at'])) 
    : date('Y-01-01\T00:00:00P');
$price_valid_until = date('Y-m-d', strtotime('+1 year'));

$product_images = [];
if (!empty($product['image'])) {
    $img_url = $product['image'];
    if (!preg_match('~^https?://~i', $img_url)) {
        $img_url = rtrim($canonical_base, '/') . '/' . ltrim($img_url, '/');
    }
    $product_images[] = $img_url;
}
if (!empty($gallery) && is_array($gallery)) {
    foreach ($gallery as $g_img) {
        if (!empty($g_img)) {
            if (!preg_match('~^https?://~i', $g_img)) {
                $g_img = rtrim($canonical_base, '/') . '/' . ltrim($g_img, '/');
            }
            if (!in_array($g_img, $product_images)) {
                $product_images[] = $g_img;
            }
        }
    }
}
if (empty($product_images)) {
    $product_images[] = rtrim($canonical_base, '/') . '/assets/images/logo.png';
}

$product_sku = !empty($product['code']) ? $product['code'] : ('GGH-PRD-' . $product['id']);
$product_brand = !empty($product['vendor_name']) ? $product['vendor_name'] : APP_NAME;
$product_seller = !empty($product['vendor_business']) ? $product['vendor_business'] : $product_brand;
$product_desc = !empty($product['description']) ? trim(strip_tags($product['description'])) : $product['name'];
$shipping_fee = isset($product['shipping_fee']) ? (float)$product['shipping_fee'] : 0.0;
$product_offer_url = !empty($current_canonical_url) ? $current_canonical_url : (rtrim($canonical_base, '/') . '/product.php?id=' . $product['id']);
$return_policy_url = rtrim($canonical_base, '/') . '/shipping-policy.php';

$product_schema = [
    "@context" => "https://schema.org/",
    "@type" => "Product",
    "name" => $product['name'],
    "image" => $product_images,
    "description" => $product_desc,
    "sku" => $product_sku,
    "mpn" => $product_sku,
    "brand" => [
        "@type" => "Brand",
        "name" => $product_brand
    ],
    "offers" => [
        "@type" => "Offer",
        "url" => $product_offer_url,
        "priceCurrency" => "INR",
        "price" => number_format((float)$product['price'], 2, '.', ''),
        "priceValidUntil" => $price_valid_until,
        "validFrom" => $offer_valid_from,
        "itemCondition" => "https://schema.org/NewCondition",
        "availability" => ((int)($product['stock'] ?? 0) > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
        "seller" => [
            "@type" => "Organization",
            "name" => $product_seller
        ],
        "shippingDetails" => [
            "@type" => "OfferShippingDetails",
            "shippingRate" => [
                "@type" => "MonetaryAmount",
                "value" => number_format($shipping_fee, 2, '.', ''),
                "currency" => "INR"
            ],
            "shippingDestination" => [
                "@type" => "DefinedRegion",
                "addressCountry" => "IN"
            ],
            "deliveryTime" => [
                "@type" => "ShippingDeliveryTime",
                "handlingTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 1,
                    "maxValue" => 2,
                    "unitCode" => "DAY"
                ],
                "transitTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 3,
                    "maxValue" => 7,
                    "unitCode" => "DAY"
                ]
            ]
        ],
        "hasMerchantReturnPolicy" => [
            "@type" => "MerchantReturnPolicy",
            "applicableCountry" => "IN",
            "returnPolicyCategory" => "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays" => 7,
            "returnMethod" => "https://schema.org/ReturnByMail",
            "returnFees" => "https://schema.org/FreeReturn",
            "returnShippingFeesAmount" => [
                "@type" => "MonetaryAmount",
                "value" => "0.00",
                "currency" => "INR"
            ],
            "merchantReturnLink" => $return_policy_url
        ]
    ],
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => number_format((float)($product['rating'] ?? 4.9), 1, '.', ''),
        "reviewCount" => max(1, (int)($product['review_count'] ?? 15))
    ]
];

$breadcrumb_schema = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => rtrim($canonical_base, '/') . "/"
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => !empty($product['category_name']) ? $product['category_name'] : "Products",
            "item" => rtrim($canonical_base, '/') . "/products.php?category=" . urlencode($product['category_slug'] ?? '')
        ],
        [
            "@type" => "ListItem",
            "position" => 3,
            "name" => $product['name']
        ]
    ]
];
?>
<script type="application/ld+json">
<?= json_encode($product_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<script type="application/ld+json">
<?= json_encode($breadcrumb_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<!-- GA4 View Item Tracking -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag === 'function') {
    gtag('event', 'view_item', {
      currency: 'INR',
      value: <?= (float)$product['price'] ?>,
      items: [{
        item_id: <?= json_encode($product['code'] ?: ('GGH-' . $product['id']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        item_name: <?= json_encode($product['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        item_category: <?= json_encode($product['category_name'] ?? 'General', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        price: <?= (float)$product['price'] ?>,
        quantity: 1
      }]
    });
  }
});
</script>

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

      <!-- Dynamic Multi-Carrier Delivery Partners & Real-Time Shipping Estimator -->
      <div class="p-5 bg-gradient-to-br from-stone-50 to-amber-50/30 rounded-3xl border border-amber-200/70 space-y-3 text-xs shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-1">
          <span class="font-bold text-gray-900 flex items-center gap-1.5">
            <i class="fa-solid fa-truck-fast text-brand-green"></i>
            <span>All-India Delivery Partners & Shipping Cost</span>
          </span>
          <span class="text-[10px] text-amber-900 bg-amber-100/70 px-2.5 py-0.5 rounded-full font-bold">
            Hub: Sandila (241204)
          </span>
        </div>
        <p class="text-[11px] text-gray-600 leading-snug">
          Check live shipping charges and delivery time to your doorstep across 19,000+ Indian PIN codes via India Post, Delhivery, Blue Dart & Shiprocket.
        </p>

        <div class="flex gap-2">
          <input 
            type="text" 
            id="pincode-input-detail" 
            maxlength="6" 
            value="<?= htmlspecialchars($_SESSION['shipping_pincode'] ?? '241126') ?>" 
            placeholder="Enter 6-digit Indian PIN Code" 
            class="flex-1 p-2.5 bg-white border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 outline-none focus:border-brand-green shadow-xs"
          >
          <button onclick="checkDetailPincode()" class="px-4 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl text-xs cursor-pointer transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-calculator"></i>
            <span>Estimate Cost</span>
          </button>
        </div>
        
        <!-- Package Specs Tag -->
        <div class="flex items-center justify-between text-[10px] text-gray-500 pt-0.5 px-0.5">
          <span>Package: <strong><?= number_format((float)($product['weight_kg'] ?? 0.5), 2) ?> kg</strong></span>
          <span>Size: <strong><?= number_format((float)($product['length_cm'] ?? 10), 1) ?> × <?= number_format((float)($product['width_cm'] ?? 10), 1) ?> × <?= number_format((float)($product['height_cm'] ?? 10), 1) ?> cm</strong></span>
        </div>

        <div id="pincode-detail-result" class="text-xs space-y-2"></div>
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
  if (pin.length !== 6 || isNaN(pin)) {
    resDiv.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded-xl font-medium text-xs"><i class="fa-solid fa-circle-exclamation mr-1"></i> कृपया 6 अंकों का सही भारतीय पिनकोड दर्ज करें।</div>';
    return;
  }
  resDiv.innerHTML = '<div class="p-3 bg-stone-100 text-stone-600 rounded-xl font-medium text-xs flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin text-amber-600"></i> सभी डिलीवरी पार्टनर्स से लाइव शिपिंग दरें खोजी जा रही हैं...</div>';

  const formData = new FormData();
  formData.append('pincode', pin);
  formData.append('product_id', <?= (int)$product['id'] ?>);
  formData.append('subtotal', <?= (float)$product['price'] ?>);
  formData.append('total_weight', <?= (float)($product['weight_kg'] ?? 0.5) ?>);
  formData.append('length_cm', <?= (float)($product['length_cm'] ?? 10.0) ?>);
  formData.append('width_cm', <?= (float)($product['width_cm'] ?? 10.0) ?>);
  formData.append('height_cm', <?= (float)($product['height_cm'] ?? 10.0) ?>);

  fetch('/ajax/calculate_shipping.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const d = data.data;
        let partnersHtml = '';
        if (d.delivery_partners && d.delivery_partners.length > 0) {
          d.delivery_partners.forEach(p => {
            const isRec = p.is_recommended ? '<span class="text-[9px] font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full">अनुशंसित (Recommended)</span>' : '';
            partnersHtml += `
              <div class="p-2.5 bg-white border border-stone-200 rounded-xl flex items-center justify-between gap-2 shadow-2xs hover:border-amber-400 transition">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center text-stone-700 flex-shrink-0 text-sm">
                    <i class="${p.icon || 'fa-solid fa-truck'}"></i>
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="font-bold text-gray-900 truncate">${p.courier_name}</span>
                      ${isRec}
                    </div>
                    <p class="text-[10px] text-gray-500 font-medium">${p.reach} • <strong>${p.eta_days}</strong></p>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="font-black text-sm ${p.is_free ? 'text-emerald-700' : 'text-gray-900'}">${p.rate_formatted}</span>
                  <span class="block text-[9px] text-emerald-700 font-semibold">${p.badge}</span>
                </div>
              </div>
            `;
          });
        }

        resDiv.innerHTML = `
          <div class="space-y-2 mt-2 pt-2 border-t border-amber-200/50">
            <div class="flex items-center justify-between text-[11px] font-bold text-stone-800">
              <span>उपलब्ध डिलीवरी पार्टनर्स (${d.zone_name})</span>
              <span class="text-[10px] font-mono text-gray-500">Billable: ${d.billable_weight_kg} kg</span>
            </div>
            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
              ${partnersHtml}
            </div>
            <div class="text-[10px] text-stone-500 flex items-center justify-between pt-1">
              <span><i class="fa-solid fa-shield-check text-emerald-600"></i> Cash on Delivery Available</span>
              <span><i class="fa-solid fa-box text-amber-600"></i> 100% Insured Transit</span>
            </div>
          </div>
        `;
      } else {
        resDiv.innerHTML = `<div class="p-3 bg-red-50 text-red-600 rounded-xl text-xs"><i class="fa-solid fa-circle-exclamation mr-1"></i> ${data.message}</div>`;
      }
    })
    .catch(err => {
      resDiv.innerHTML = '<div class="p-2.5 bg-red-50 text-red-600 rounded-xl text-xs">दर गणना में अस्थायी त्रुटि। कृपया पुनः प्रयास करें।</div>';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
