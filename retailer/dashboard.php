<?php
/**
 * GanvGaliHat Retailer Hub — Dashboard
 */

$page_title = 'Wholesale Dashboard — GanvGaliHat';
require_once __DIR__ . '/retailer_header.php';

$uid = $user['id'];

// Aggregate retailer B2B order statistics with robust error handling
$total_orders = 0;
$total_spend = 0;
$active_orders = 0;
$total_bulk_products = 0;
$recent_orders = [];
$wholesale_deals = [];

try {
    $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_type = 'wholesale_b2b'");
    $stmt_orders->execute([$uid]);
    $total_orders = (int)($stmt_orders->fetchColumn() ?: 0);
} catch (Exception $e) {
    error_log("Retailer Dashboard Orders Count Error: " . $e->getMessage());
}

try {
    $stmt_spend = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ? AND order_type = 'wholesale_b2b' AND order_status != 'Cancelled'");
    $stmt_spend->execute([$uid]);
    $total_spend = (float)($stmt_spend->fetchColumn() ?: 0);
} catch (Exception $e) {
    error_log("Retailer Dashboard Spend Error: " . $e->getMessage());
}

try {
    $stmt_active = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_type = 'wholesale_b2b' AND order_status NOT IN ('Delivered', 'Cancelled')");
    $stmt_active->execute([$uid]);
    $active_orders = (int)($stmt_active->fetchColumn() ?: 0);
} catch (Exception $e) {
    error_log("Retailer Dashboard Active Orders Error: " . $e->getMessage());
}

try {
    $stmt_bulk_count = $pdo->query("SELECT COUNT(*) FROM products WHERE bulk_available = 1 AND (status = 'active' OR status IS NULL) AND (stock > 0 OR stock IS NULL)");
    $total_bulk_products = (int)($stmt_bulk_count->fetchColumn() ?: 0);
} catch (Exception $e) {
    error_log("Retailer Dashboard Bulk Count Error: " . $e->getMessage());
}

// Fetch Recent B2B Wholesale Orders
try {
    $stmt_recent = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? AND order_type = 'wholesale_b2b' 
        ORDER BY id DESC LIMIT 5
    ");
    $stmt_recent->execute([$uid]);
    $recent_orders = $stmt_recent->fetchAll();
} catch (Exception $e) {
    error_log("Retailer Dashboard Recent Orders Error: " . $e->getMessage());
    $recent_orders = [];
}

// Fetch Featured Wholesale Deals (Products ready for bulk buying)
try {
    $stmt_deals = $pdo->query("
        SELECT p.*, v.business_name as artisan_vendor, v.district
        FROM products p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        WHERE p.bulk_available = 1 AND (p.status = 'active' OR p.status IS NULL) AND (p.stock > 0 OR p.stock IS NULL)
        ORDER BY p.is_featured DESC, p.id DESC
        LIMIT 6
    ");
    $wholesale_deals = $stmt_deals->fetchAll();
} catch (Exception $e) {
    error_log("Retailer Dashboard Wholesale Deals Error: " . $e->getMessage());
    $wholesale_deals = [];
}
?>

<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 relative overflow-hidden">
  <div class="relative z-10 max-w-2xl">
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-xs text-white border border-white/20 mb-3">
      <i class="fa-solid fa-store"></i> <span class="lang-en">B2B Wholesale Portal</span><span class="lang-hi hidden">B2B थोक व्यापारी पोर्टल</span>
    </span>
    <h1 class="text-xl sm:text-3xl font-black">
      <span class="lang-en">Welcome,</span><span class="lang-hi hidden">स्वागत है,</span> <?= htmlspecialchars($user['business_name'] ?? $user['name']) ?>!
    </h1>
    <p class="text-amber-100 text-xs sm:text-sm mt-1.5 leading-relaxed font-medium">
      <span class="lang-en">Source directly from verified village artisan clusters at wholesale tier pricing to maximize retail margins for your store.</span><span class="lang-hi hidden">सीधे ग्रामीण क्लस्टरों से थोक भाव पर प्रामाणिक उत्पाद मंगाएं और अपनी दुकान के लिए अधिकतम <span class="lang-en">Margin</span><span class="lang-hi hidden">मार्जिन</span> प्राप्त करें।</span>
    </p>

    <div class="mt-5 flex flex-wrap items-center gap-3">
      <a href="/retailer/products.php" class="px-5 py-2.5 bg-stone-900 hover:bg-black text-amber-400 font-black text-xs sm:text-sm rounded-xl transition shadow flex items-center gap-2">
        <i class="fa-solid fa-boxes-stacked"></i> <span class="lang-en">Browse Wholesale Catalog &rarr;</span><span class="lang-hi hidden">थोक कैटलॉग देखें &rarr;</span>
      </a>
      <a href="/retailer/cart.php" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white font-bold text-xs sm:text-sm rounded-xl transition backdrop-blur-xs flex items-center gap-1.5">
        <i class="fa-solid fa-cart-flatbed"></i> <span class="lang-en">Wholesale Cart (</span><span class="lang-hi hidden">थोक कार्ट (</span><?= $bulk_cart_count ?>)
      </a>
    </div>
  </div>

  <div class="hidden lg:block absolute right-8 top-1/2 -translate-y-1/2 opacity-20 text-white pointer-events-none">
    <i class="fa-solid fa-boxes-packing text-9xl"></i>
  </div>
</div>

<!-- Metrics 4-Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
      <i class="fa-solid fa-file-invoice"></i>
    </div>
    <div>
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><span class="lang-en">Total Bulk Orders</span><span class="lang-hi hidden">कुल बल्क ऑर्डर्स</span></div>
      <div class="text-2xl font-black text-gray-900"><?= $total_orders ?></div>
      <div class="text-[10px] text-gray-500"><span class="lang-en">B2B Wholesale Orders</span><span class="lang-hi hidden">B2B थोक खरीद</span></div>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
      <i class="fa-solid fa-indian-rupee-sign"></i>
    </div>
    <div>
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><span class="lang-en">Total Bulk Spend</span><span class="lang-hi hidden">कुल थोक खरीदारी</span></div>
      <div class="text-2xl font-black text-emerald-700"><?= format_price($total_spend) ?></div>
      <div class="text-[10px] text-gray-500"><span class="lang-en">GST Invoiced Value</span><span class="lang-hi hidden">GST <span class="lang-en">Invoice</span><span class="lang-hi hidden">इनवॉइस</span> वैल्यू</span></div>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
      <i class="fa-solid fa-truck-fast"></i>
    </div>
    <div>
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><span class="lang-en">Active Shipments</span><span class="lang-hi hidden">सक्रिय शिपमेंट</span></div>
      <div class="text-2xl font-black text-blue-700"><?= $active_orders ?></div>
      <div class="text-[10px] text-gray-500"><span class="lang-en">In Transit / Dispatch</span><span class="lang-hi hidden">रास्ते में / प्रक्रियाधीन</span></div>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
      <i class="fa-solid fa-warehouse"></i>
    </div>
    <div>
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"><span class="lang-en">Wholesale Products</span><span class="lang-hi hidden">उपलब्ध थोक उत्पाद</span></div>
      <div class="text-2xl font-black text-purple-700"><?= $total_bulk_products ?></div>
      <div class="text-[10px] text-gray-500"><span class="lang-en">Direct from Rural Hubs</span><span class="lang-hi hidden">सीधे क्लस्टरों से</span></div>
    </div>
  </div>
</div>

<!-- Main Split: Recent Orders & Business Details -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  
  <!-- Left 2 Cols: Recent B2B Orders -->
  <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="text-sm font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
        हालिया थोक ऑर्डर्स (Recent B2B Orders)
      </h2>
      <a href="/retailer/orders.php" class="text-xs font-bold text-amber-600 hover:text-amber-700">
        सभी देखें &rarr;
      </a>
    </div>

    <?php if (empty($recent_orders)): ?>
      <div class="p-8 text-center">
        <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3">
          <i class="fa-solid fa-cart-flatbed"></i>
        </div>
        <p class="text-xs font-bold text-gray-700">अभी तक कोई थोक ऑर्डर नहीं दिया गया है।</p>
        <p class="text-[11px] text-gray-400 mt-1">थोक कैटलॉग से सीधे कारीगरों से माल मंगाएं और भारी बचत करें।</p>
        <a href="/retailer/products.php" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition">
          <i class="fa-solid fa-boxes-stacked"></i> थोक उत्पाद खोजें
        </a>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200">
            <tr>
              <th class="px-4 py-3">ऑर्डर सं.</th>
              <th class="px-4 py-3">दिनांक</th>
              <th class="px-4 py-3">राशि</th>
              <th class="px-4 py-3"><span class="lang-en">Payment</span><span class="lang-hi hidden">भुगतान</span></th>
              <th class="px-4 py-3">स्थिति</th>
              <th class="px-4 py-3 text-right">कार्रवाई</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($recent_orders as $ord): ?>
              <?php $ord_status = $ord['order_status'] ?? $ord['status'] ?? 'Pending'; ?>
              <tr class="hover:bg-amber-50/40 transition">
                <td class="px-4 py-3 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number'] ?? $ord['id']) ?>
                </td>
                <td class="px-4 py-3 text-gray-500">
                  <?= date('d M Y', strtotime($ord['created_at'])) ?>
                </td>
                <td class="px-4 py-3 font-bold text-gray-900">
                  <?= format_price($ord['total_amount'] ?? $ord['total'] ?? 0) ?>
                </td>
                <td class="px-4 py-3 text-gray-600">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-100">
                    <?= htmlspecialchars(ucfirst($ord['payment_method'] ?? 'COD')) ?>
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ord_status === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                    <?= htmlspecialchars($ord_status) ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <a href="/retailer/orders.php?order_id=<?= $ord['id'] ?>" class="text-amber-600 hover:text-amber-800 font-bold">
                    विवरण & बिल &rarr;
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Col: Business Profile & Quick Actions -->
  <div class="space-y-6">
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs p-6">
      <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
        <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
          <i class="fa-solid fa-id-card text-amber-600"></i>
          व्यापार व टैक्स स्थिति
        </h3>
        <a href="/retailer/profile.php" class="text-[11px] font-bold text-amber-600 hover:text-amber-700">
          संपादित करें
        </a>
      </div>

      <div class="space-y-3 text-xs">
        <div>
          <span class="text-gray-400 block text-[10px]">दुकान / फर्म का नाम:</span>
          <span class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($user['business_name'] ?? $user['name']) ?></span>
        </div>

        <div>
          <span class="text-gray-400 block text-[10px]">GSTIN नंबर:</span>
          <?php if (!empty($user['gstin'])): ?>
            <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
              <?= htmlspecialchars($user['gstin']) ?>
            </span>
          <?php else: ?>
            <span class="text-amber-600 text-[11px] font-semibold">दर्ज नहीं है • <a href="/retailer/profile.php" class="underline">GST जोड़ें</a></span>
          <?php endif; ?>
        </div>

        <div>
          <span class="text-gray-400 block text-[10px]">गोदाम / डिलीवरी पता:</span>
          <p class="text-gray-700 font-medium leading-relaxed">
            <?= htmlspecialchars($user['business_address'] ?? $user['address'] ?? 'पता दर्ज नहीं है') ?><br>
            <?= htmlspecialchars($user['business_city'] ?? $user['city'] ?? '') ?>, <?= htmlspecialchars($user['business_state'] ?? $user['state'] ?? '') ?> - <?= htmlspecialchars($user['business_pincode'] ?? $user['pincode'] ?? '') ?>
          </p>
        </div>

        <div>
          <span class="text-gray-400 block text-[10px]">पंजीकृत मोबाइल:</span>
          <span class="font-bold text-gray-900">+91 <?= htmlspecialchars($user['phone'] ?? '9876543210') ?></span>
        </div>
      </div>
    </div>

    <!-- B2B Help Card -->
    <div class="bg-gradient-to-br from-stone-900 to-stone-800 rounded-3xl p-5 text-white shadow-md">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
          <i class="fa-solid fa-headset"></i>
        </div>
        <div>
          <h4 class="text-xs font-black text-amber-400 uppercase tracking-wider">समर्पित थोक सहायता</h4>
          <span class="text-[10px] text-stone-300">विशेष B2B रिलेशनशिप मैनेजर</span>
        </div>
      </div>
      <p class="text-stone-300 text-xs mb-3">
        किसी विशेष उत्पाद की बड़ी खेप (Full Truck Load / Bulk Container) या कस्टम ब्रांडिंग के लिए हमसे संपर्क करें।
      </p>
      <a href="https://wa.me/919454050000?text=Hello%20GanvGaliHat%20B2B%20Team" target="_blank"
         class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition">
        <i class="fa-brands fa-whatsapp text-sm"></i> व्हाट्सएप B2B डेस्क से बात करें
      </a>
    </div>
  </div>

</div>

<!-- High Margin Wholesale Deals Section -->
<div>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
    <div>
      <h2 class="text-base sm:text-lg font-black text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-tags text-amber-600"></i>
        उच्च <span class="lang-en">Margin</span><span class="lang-hi hidden">मार्जिन</span> वाले थोक सौदे (High-Margin Wholesale Deals)
      </h2>
      <p class="text-xs text-gray-500">कारीगरों से सीधे कम से कम MOQ में थोक लॉट खरीदें और 25% से 45% तक मुनाफा कमाएं</p>
    </div>
    <a href="/retailer/products.php" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1">
      कैटलॉग में और उत्पाद खोजें &rarr;
    </a>
  </div>

  <?php if (empty($wholesale_deals)): ?>
    <div class="bg-white p-6 rounded-2xl border border-gray-200 text-center text-xs text-gray-500">
      वर्तमान में कोई विशेष बल्क डील उपलब्ध नहीं है।
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($wholesale_deals as $p): ?>
        <?php
          $retail_p = floatval($p['price'] ?? 0);
          $orig_p = floatval($p['original_price'] ?? $p['mrp'] ?? ($retail_p * 1.3));
          $bulk_p = floatval($p['bulk_price'] ?? ($retail_p * 0.7));
          $moq = max(1, intval($p['bulk_moq'] ?? 10));
          $margin_pc = $retail_p - $bulk_p;
          $margin_pct = ($retail_p > 0) ? round(($margin_pc / $retail_p) * 100) : 30;
          $p_img = !empty($p['image_url']) ? $p['image_url'] : (!empty($p['image']) ? $p['image'] : '/placeholder.png');
        ?>
        <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs hover:shadow-md transition p-4 flex flex-col justify-between">
          <div>
            <div class="relative h-44 rounded-2xl overflow-hidden bg-stone-100 mb-3 border border-stone-200">
              <img src="<?= htmlspecialchars($p_img) ?>" alt="<?= htmlspecialchars($p['name'] ?? 'Product') ?>" class="w-full h-full object-cover">
              <div class="absolute top-2 left-2 bg-stone-900/90 backdrop-blur-xs text-amber-400 px-2 py-0.5 rounded-lg text-[10px] font-black">
                MOQ: <?= $moq ?> Pieces
              </div>
              <div class="absolute top-2 right-2 bg-emerald-600 text-white px-2 py-0.5 rounded-lg text-[10px] font-black">
                <?= $margin_pct ?>% Retail Margin
              </div>
            </div>

            <h3 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-1 mb-0.5">
              <?= htmlspecialchars($p['name']) ?>
            </h3>
            <div class="text-[11px] text-gray-400 line-clamp-1 mb-2">
              <?= htmlspecialchars($p['artisan_vendor'] ?? get_company_name()) ?> (<?= htmlspecialchars($p['district'] ?? 'Hardoi') ?>)
            </div>

            <!-- Price Compare Box -->
            <div class="bg-amber-50/70 p-3 rounded-2xl border border-amber-200/70 mb-3">
              <div class="flex items-baseline justify-between">
                <span class="text-[11px] font-bold text-gray-600"><span class="lang-en">Wholesale:</span><span class="lang-hi hidden">थोक भाव:</span></span>
                <span class="text-base font-black text-amber-800"><?= format_price($bulk_p) ?> <span class="text-[10px] text-gray-500 font-normal"><span class="lang-en">/ pc</span><span class="lang-hi hidden">/ <span class="lang-en">pcs</span><span class="lang-hi hidden">पीस</span></span></span></span>
              </div>
              <div class="flex items-baseline justify-between text-[11px] text-gray-500 mt-0.5">
                <span><span class="lang-en">Retail MRP:</span><span class="lang-hi hidden">खुदरा बिक्री मूल्य (MRP):</span></span>
                <span class="line-through"><?= format_price($orig_p) ?></span>
              </div>
              <div class="flex items-baseline justify-between text-[11px] text-emerald-700 font-bold mt-1 pt-1 border-t border-amber-200/60">
                <span>प्रति <span class="lang-en">pcs</span><span class="lang-hi hidden">पीस</span> आपका मुनाफा:</span>
                <span>+<?= format_price($margin_pc) ?></span>
              </div>
            </div>
          </div>

          <a href="/retailer/products.php?highlight=<?= $p['id'] ?>" class="w-full py-2.5 bg-stone-900 hover:bg-black text-amber-400 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition">
            <i class="fa-solid fa-calculator"></i> लॉट कैलकुलेटर व ऑर्डर &rarr;
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
