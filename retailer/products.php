<?php
/**
 * GanvGaliHat Retailer Hub — Wholesale Product Catalog
 * Allows B2B buyers & shopkeepers to order bulk lots directly from rural producers.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_retailer();

// Handle Add to Bulk Cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_bulk') {
    csrf_protect();
    $pid = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['quantity'] ?? 0);

    $res = add_to_bulk_cart($pid, $qty);
    if ($res['success']) {
        set_flash('success', $res['message']);
        if (isset($_POST['go_to_cart']) && $_POST['go_to_cart'] === '1') {
            header('Location: /retailer/cart.php');
            exit;
        }
    } else {
        set_flash('error', $res['error']);
    }

    $redirect_url = '/retailer/products.php';
    if (!empty($_GET['category'])) {
        $redirect_url .= '?category=' . urlencode($_GET['category']);
    }
    header('Location: ' . $redirect_url);
    exit;
}

$page_title = 'थोक कैटलॉग (Wholesale B2B Catalog)';
require_once __DIR__ . '/retailer_header.php';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Query Filters
$cat_filter = clean_input($_GET['category'] ?? '');
$q = clean_input($_GET['q'] ?? '');
$highlight_id = intval($_GET['highlight'] ?? 0);
$only_bulk_preset = isset($_GET['bulk_only']) ? intval($_GET['bulk_only']) : 1;

$sql = "
    SELECT p.*, c.name as cat_name, c.hindi_name as cat_hindi, v.business_name, v.name as vendor_name, v.district
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE p.stock_status = 'In Stock'
";
$params = [];

if ($only_bulk_preset === 1) {
    $sql .= " AND p.bulk_available = 1";
}
if (!empty($cat_filter)) {
    $sql .= " AND (c.slug = ? OR p.category_id = ?)";
    $params[] = $cat_filter;
    $params[] = intval($cat_filter);
}
if (!empty($q)) {
    $sql .= " AND (p.name LIKE ? OR p.hindi_name LIKE ? OR p.code LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY (p.id = {$highlight_id}) DESC, p.bulk_available DESC, p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Catalog Header & Search Bar -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
      थोक कैटलॉग (Wholesale B2B Catalog)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      सीधे ग्रामीण क्लस्टरों से थोक भाव पर प्रामाणिक माल खरीदें • कुल <?= count($products) ?> उत्पाद उपलब्ध
    </p>
  </div>

  <div class="flex items-center gap-2">
    <a href="/retailer/cart.php" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-cart-flatbed"></i>
      थोक कार्ट देखें (<?= $bulk_cart_count ?>)
    </a>
  </div>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-2xl border border-gray-200/90 shadow-xs mb-6">
  <form method="GET" action="/retailer/products.php" class="flex flex-wrap items-center justify-between gap-3">
    
    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
      <!-- Search Input -->
      <div class="relative flex-grow sm:flex-grow-0 sm:w-64">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-gray-400 text-xs"></i>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
          placeholder="उत्पाद या कोड खोजें..."
          class="w-full pl-9 pr-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-600 transition">
      </div>

      <!-- Category Filter -->
      <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none">
        <option value="">सभी श्रेणियां (All Categories)</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['slug'] ?>" <?= $cat_filter === $c['slug'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['hindi_name'] ?? $c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Bulk Only Filter Toggle -->
      <select name="bulk_only" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none">
        <option value="1" <?= $only_bulk_preset === 1 ? 'selected' : '' ?>>विशेष B2B थोक उत्पाद (Ready Bulk Lots)</option>
        <option value="0" <?= $only_bulk_preset === 0 ? 'selected' : '' ?>>समस्त उत्पाद कैटलॉग (All Catalog)</option>
      </select>

      <button type="submit" class="px-3 py-1.5 bg-stone-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
        खोजें
      </button>

      <?php if (!empty($q) || !empty($cat_filter) || $only_bulk_preset === 0): ?>
        <a href="/retailer/products.php" class="text-xs text-red-600 hover:underline font-bold px-2 py-1">
          रीसेट करें &times;
        </a>
      <?php endif; ?>
    </div>

    <div class="text-[11px] text-gray-500 hidden sm:block">
      <i class="fa-solid fa-truck-fast text-emerald-600 mr-1"></i> डोरस्टेप फ्रेट व पैन-इंडिया डिलीवरी
    </div>

  </form>
</div>

<!-- Product Cards Grid -->
<?php if (empty($products)): ?>
  <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xs">
    <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-4">
      <i class="fa-solid fa-boxes-packing"></i>
    </div>
    <h3 class="text-base font-black text-gray-900">कोई थोक उत्पाद नहीं मिला</h3>
    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
      कृपया दूसरा सर्च शब्द आजमाएं या सभी उत्पादों को देखने के लिए फ़िल्टर रीसेट करें।
    </p>
    <a href="/retailer/products.php?bulk_only=0" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-stone-900 text-amber-400 font-bold text-xs rounded-xl">
      समस्त उत्पाद कैटलॉग देखें &rarr;
    </a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($products as $p): ?>
      <?php
        $retail_p = floatval($p['price']);
        $orig_p = floatval($p['original_price'] ?: $retail_p * 1.3);
        $bulk_p = floatval($p['bulk_price'] ?: ($retail_p * 0.75));
        $moq = max(1, intval($p['bulk_moq'] ?: 10));
        $tier2_qty = intval($p['bulk_tier2_qty'] ?? 0);
        $tier2_price = floatval($p['bulk_tier2_price'] ?? 0);
        $margin_pc = max(0, $retail_p - $bulk_p);
        $margin_pct = ($retail_p > 0) ? round(($margin_pc / $retail_p) * 100) : 25;
        $lead_time = intval($p['bulk_lead_time_days'] ?? 3);
        $pkg = htmlspecialchars($p['bulk_packaging_desc'] ?? 'Standard Master Box');
        $is_highlighted = ($highlight_id === intval($p['id']));
      ?>
      <div id="product-card-<?= $p['id'] ?>"
           class="bg-white rounded-3xl border <?= $is_highlighted ? 'border-amber-500 ring-2 ring-amber-400 shadow-lg' : 'border-gray-200/90 shadow-xs' ?> p-5 flex flex-col justify-between hover:shadow-md transition">
        
        <div>
          <!-- Top Row: Image & Badges -->
          <div class="relative h-48 rounded-2xl overflow-hidden bg-stone-100 mb-4 border border-stone-200">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover">
            
            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start">
              <span class="bg-stone-900/90 backdrop-blur-xs text-amber-400 px-2.5 py-1 rounded-lg text-[10px] font-black shadow-xs">
                MOQ: <?= $moq ?> Pieces
              </span>
              <?php if ($tier2_qty > 0 && $tier2_price > 0): ?>
                <span class="bg-blue-900/90 backdrop-blur-xs text-blue-200 px-2 py-0.5 rounded-lg text-[9px] font-bold">
                  <?= $tier2_qty ?>+ pcs @ <?= format_price($tier2_price) ?>
                </span>
              <?php endif; ?>
            </div>

            <div class="absolute top-2.5 right-2.5 bg-emerald-600 text-white px-2.5 py-1 rounded-lg text-[10px] font-black shadow-xs">
              <?= $margin_pct ?>% Retail Margin
            </div>
          </div>

          <!-- Title & Vendor -->
          <div class="mb-3">
            <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1">
              <span class="font-bold text-stone-700 bg-stone-100 px-2 py-0.5 rounded">
                <?= htmlspecialchars($p['cat_hindi'] ?? $p['cat_name'] ?? 'General') ?>
              </span>
              <span class="font-mono text-gray-400">Code: <?= htmlspecialchars($p['code']) ?></span>
            </div>

            <h3 class="font-black text-gray-900 text-sm sm:text-base leading-snug line-clamp-1">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">
              <?= htmlspecialchars($p['hindi_name'] ?? '') ?>
            </p>
            <div class="text-[11px] text-stone-500 flex items-center gap-1.5 mt-1">
              <i class="fa-solid fa-industry text-amber-600"></i>
              <span><?= htmlspecialchars($p['business_name'] ?? $p['vendor_name'] ?? 'Village Artisan Cluster') ?></span>
              <span class="text-gray-300">•</span>
              <span><?= htmlspecialchars($p['district'] ?? 'Hardoi') ?></span>
            </div>
          </div>

          <!-- Wholesale Pricing Breakdown Box -->
          <div class="bg-gradient-to-br from-amber-50/70 to-orange-50/60 p-3.5 rounded-2xl border border-amber-200/70 mb-3 space-y-1.5 text-xs">
            <div class="flex items-baseline justify-between">
              <span class="font-bold text-stone-700">थोक दर (Wholesale Price):</span>
              <span class="text-lg font-black text-amber-900" id="display_price_<?= $p['id'] ?>">
                <?= format_price($bulk_p) ?> <span class="text-[10px] text-gray-500 font-normal">/ पीस</span>
              </span>
            </div>
            
            <div class="flex items-baseline justify-between text-[11px] text-gray-500">
              <span>खुदरा बिक्री मूल्य (MRP):</span>
              <span class="line-through"><?= format_price($orig_p) ?></span>
            </div>

            <div class="flex items-baseline justify-between text-[11px] text-emerald-700 font-bold pt-1 border-t border-amber-200/50">
              <span>दुकान का शुद्ध मुनाफा:</span>
              <span id="display_margin_<?= $p['id'] ?>">+<?= format_price($margin_pc) ?> / pc (<?= $margin_pct ?>%)</span>
            </div>
          </div>

          <!-- Logistics Info -->
          <div class="flex items-center justify-between text-[11px] text-gray-500 px-1 mb-4">
            <span title="डिस्पैच तैयारी"><i class="fa-solid fa-clock text-amber-600 mr-1"></i> डिस्पैच: <?= $lead_time ?> दिन</span>
            <span title="पैकेजिंग प्रकार"><i class="fa-solid fa-box text-stone-400 mr-1"></i> <?= $pkg ?></span>
          </div>
        </div>

        <!-- Interactive Lot Calculator & Add to Cart Form -->
        <form method="POST" action="/retailer/products.php" class="pt-3 border-t border-gray-100">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_bulk">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

          <div class="space-y-2.5">
            <!-- Quantity Step Controller -->
            <div class="flex items-center justify-between gap-2 bg-stone-50 p-2 rounded-xl border border-stone-200">
              <span class="text-[11px] font-bold text-gray-700">ऑर्डर मात्रा:</span>
              <div class="flex items-center gap-1.5">
                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, -1, <?= $moq ?>)"
                  class="w-7 h-7 bg-white hover:bg-stone-100 border border-stone-300 rounded-lg text-xs font-black flex items-center justify-center transition">
                  -
                </button>
                <input type="number" name="quantity" id="qty_<?= $p['id'] ?>" value="<?= $moq ?>" min="<?= $moq ?>"
                  oninput="recalcBulkLot(<?= $p['id'] ?>, <?= $bulk_p ?>, <?= $tier2_qty ?>, <?= $tier2_price ?>, <?= $retail_p ?>, <?= $moq ?>)"
                  class="w-16 text-center py-1 bg-white border border-stone-300 rounded-lg text-xs font-black text-gray-900 focus:outline-none focus:border-amber-600">
                <button type="button" onclick="adjustQty(<?= $p['id'] ?>, 1, <?= $moq ?>)"
                  class="w-7 h-7 bg-white hover:bg-stone-100 border border-stone-300 rounded-lg text-xs font-black flex items-center justify-center transition">
                  +
                </button>
              </div>
            </div>

            <!-- Live Lot Total Preview -->
            <div class="flex items-center justify-between text-xs px-1">
              <span class="text-gray-500">कुल लॉट अनुमान:</span>
              <strong class="text-sm font-black text-gray-900" id="lot_total_<?= $p['id'] ?>">
                <?= format_price($bulk_p * $moq) ?>
              </strong>
            </div>

            <!-- Add to Cart CTA -->
            <button type="submit"
              class="w-full py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2">
              <i class="fa-solid fa-cart-flatbed"></i>
              थोक कार्ट में जोड़ें (Add Lot)
            </button>
          </div>
        </form>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
function adjustQty(pid, delta, moq) {
  const inp = document.getElementById('qty_' + pid);
  if (!inp) return;
  let val = parseInt(inp.value) || moq;
  val = Math.max(moq, val + delta);
  inp.value = val;
  inp.dispatchEvent(new Event('input'));
}

function recalcBulkLot(pid, baseBulkPrice, tier2Qty, tier2Price, retailPrice, moq) {
  const inp = document.getElementById('qty_' + pid);
  if (!inp) return;
  let qty = parseInt(inp.value) || moq;
  if (qty < moq) {
    qty = moq;
    inp.value = moq;
  }

  let unitPrice = baseBulkPrice;
  if (tier2Qty > 0 && tier2Price > 0 && qty >= tier2Qty) {
    unitPrice = tier2Price;
  }

  const lotTotal = unitPrice * qty;
  const marginPerPc = Math.max(0, retailPrice - unitPrice);
  const marginPct = (retailPrice > 0) ? Math.round((marginPerPc / retailPrice) * 100) : 25;

  const lotEl = document.getElementById('lot_total_' + pid);
  if (lotEl) lotEl.innerText = '₹' + lotTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const dispPrice = document.getElementById('display_price_' + pid);
  if (dispPrice) {
    dispPrice.innerHTML = '₹' + unitPrice.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' <span class="text-[10px] text-gray-500 font-normal">/ पीस</span>';
  }

  const dispMargin = document.getElementById('display_margin_' + pid);
  if (dispMargin) {
    dispMargin.innerText = '+₹' + marginPerPc.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' / pc (' + marginPct + '%)';
  }
}
</script>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
