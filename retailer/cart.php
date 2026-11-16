<?php
/**
 * GanvGaliHat Retailer Hub — Wholesale Cart
 * Dedicated B2B shopping cart enforcing Minimum Order Quantities (MOQ) and tiered wholesale rates.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_retailer();

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_qty') {
        $pid = intval($_POST['product_id'] ?? 0);
        $qty = intval($_POST['quantity'] ?? 0);
        $res = update_bulk_cart($pid, $qty);
        if ($res['success']) {
            set_flash('success', $res['message']);
        } else {
            set_flash('error', $res['error']);
        }
    } elseif ($action === 'remove_item') {
        $pid = intval($_POST['product_id'] ?? 0);
        remove_from_bulk_cart($pid);
        set_flash('success', 'उत्पाद थोक कार्ट से हटा दिया गया है।');
    } elseif ($action === 'clear_cart') {
        clear_bulk_cart();
        set_flash('success', 'Wholesale cart has been cleared.');
    }

    header('Location: /retailer/cart.php');
    exit;
}

$page_title = 'Wholesale Cart — GanvGaliHat';
require_once __DIR__ . '/retailer_header.php';

$cart = get_bulk_cart();
$subtotal = get_bulk_cart_subtotal();
$total_units = get_bulk_cart_count();

// Calculate total weight and GST
$total_weight_kg = 0;
foreach ($cart as $item) {
    $total_weight_kg += ($item['weight_kg'] ?? 0.5) * $item['quantity'];
}

// B2B GST 5% standard rate calculation
$gst_rate = 0.05;
$gst_amount = round($subtotal * $gst_rate, 2);

// Wholesale Freight: Free on orders over ₹4,999, else flat nominal freight ₹199
$shipping_fee = ($subtotal >= 4999 || $subtotal == 0) ? 0.00 : 199.00;
$grand_total = round($subtotal + $gst_amount + $shipping_fee, 2);
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-cart-flatbed text-amber-600"></i>
      <span class="lang-en">Wholesale B2B Cart</span><span class="lang-hi hidden">थोक कार्ट</span>
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      <span class="lang-en">MOQ-based wholesale lots • Total</span><span class="lang-hi hidden">MOQ आधारित थोक लॉट • कुल</span> <?= count($cart) ?> <span class="lang-en">items (</span><span class="lang-hi hidden">उत्पाद (</span><?= $total_units ?> <span class="lang-en">pcs)</span><span class="lang-hi hidden">पीस)</span>
    </p>
  </div>

  <?php if (!empty($cart)): ?>
    <form method="POST" action="/retailer/cart.php" onsubmit="return confirm('Are you sure you want to empty the wholesale cart?');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="clear_cart">
      <button type="submit" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded-xl border border-red-200 transition">
        <i class="fa-solid fa-trash-can mr-1"></i> <span class="lang-en">Clear Cart</span><span class="lang-hi hidden">कार्ट खाली करें</span>
      </button>
    </form>
  <?php endif; ?>
</div>

<?php if (empty($cart)): ?>
  <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xs max-w-lg mx-auto my-8">
    <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-3xl mb-4">
      <i class="fa-solid fa-cart-arrow-down"></i>
    </div>
    <h3 class="text-lg font-black text-gray-900"><span class="lang-en">Your Wholesale Cart is Empty</span><span class="lang-hi hidden">आपकी थोक कार्ट खाली है</span></h3>
    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
      <span class="lang-en">Add high-margin products directly from village producers and artisan clusters at wholesale minimum order quantities (MOQ).</span><span class="lang-hi hidden">सीधे गांव के उत्पादकों व कारीगरों से न्यूनतम थोक लॉट पर उत्पाद जोड़ें।</span>
    </p>
    <a href="/retailer/products.php" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition">
      <i class="fa-solid fa-boxes-stacked"></i> <span class="lang-en">Browse Wholesale Catalog &rarr;</span><span class="lang-hi hidden">थोक कैटलॉग देखें &rarr;</span>
    </a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left 2 Cols: Cart Items List -->
    <div class="lg:col-span-2 space-y-4">
      <?php foreach ($cart as $pid => $item): ?>
        <?php
          $unit_p = floatval($item['bulk_price']);
          $qty = intval($item['quantity']);
          $item_sub = round($unit_p * $qty, 2);
          $moq = intval($item['moq'] ?? 10);
        ?>
        <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          
          <div class="flex items-center gap-4 w-full sm:w-auto">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-stone-100 border border-stone-200 shrink-0">
            
            <div>
              <div class="flex items-center gap-2">
                <span class="bg-stone-900 text-amber-400 font-bold px-2 py-0.5 rounded text-[10px]">
                  MOQ: <?= $moq ?>
                </span>
                <?php if (!empty($item['code'])): ?>
                  <span class="font-mono text-gray-400 text-[10px]"><?= htmlspecialchars($item['code']) ?></span>
                <?php endif; ?>
              </div>
              <h3 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-1 mt-1">
                <?= htmlspecialchars($item['name']) ?>
              </h3>
              <div class="text-[11px] text-gray-500 mt-0.5">
                <span class="lang-en">Wholesale Price:</span><span class="lang-hi hidden">थोक दर:</span> <strong class="text-amber-700 font-black"><?= format_price($unit_p) ?></strong> <span class="lang-en">/ pc</span><span class="lang-hi hidden">/ पीस</span>
              </div>
              <div class="text-[10px] text-stone-400 mt-0.5">
                <i class="fa-solid fa-box text-stone-400 mr-1"></i><?= htmlspecialchars($item['packaging'] ?? 'Master Box') ?>
              </div>
            </div>
          </div>

          <!-- Quantity Controller & Subtotal -->
          <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100">
            <div class="text-right">
              <span class="text-[10px] text-gray-400 block"><span class="lang-en">Lot Subtotal:</span><span class="lang-hi hidden">लॉट कुल:</span></span>
              <span class="text-sm sm:text-base font-black text-gray-900"><?= format_price($item_sub) ?></span>
            </div>

            <!-- Qty Updater Form -->
            <form method="POST" action="/retailer/cart.php" class="flex items-center gap-1.5">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_qty">
              <input type="hidden" name="product_id" value="<?= $pid ?>">
              <input type="number" name="quantity" value="<?= $qty ?>" min="<?= $moq ?>"
                class="w-16 text-center py-1 bg-stone-50 border border-stone-300 rounded-lg text-xs font-black text-gray-900 focus:outline-none focus:border-amber-600">
              <button type="submit" title="Update Quantity" class="px-2 py-1 bg-stone-900 hover:bg-black text-amber-400 rounded-lg text-[11px] font-bold transition">
                <i class="fa-solid fa-arrows-rotate"></i>
              </button>
            </form>

            <!-- Remove Button Form -->
            <form method="POST" action="/retailer/cart.php" onsubmit="return confirm('Remove this bulk item from cart?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove_item">
              <input type="hidden" name="product_id" value="<?= $pid ?>">
              <button type="submit" class="text-[11px] text-red-500 hover:text-red-700 font-semibold transition flex items-center gap-1">
                <i class="fa-regular fa-trash-can"></i> हटाएं
              </button>
            </form>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- Right Col: Order Summary & Checkout -->
    <div class="space-y-4">
      <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-6">
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider pb-3 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-receipt text-amber-600"></i>
          <span class="lang-en">B2B Order Bill Summary</span><span class="lang-hi hidden">थोक ऑर्डर सारांश</span>
        </h2>

        <div class="space-y-3 py-4 text-xs border-b border-gray-100">
          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">Total Line Items:</span><span class="lang-hi hidden">कुल उत्पाद संख्या:</span></span>
            <span class="font-bold text-gray-900"><?= count($cart) ?> <span class="lang-en">items</span><span class="lang-hi hidden">आयटम</span></span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">Total Units:</span><span class="lang-hi hidden">कुल पीस (Units):</span></span>
            <span class="font-bold text-gray-900"><?= $total_units ?> पीस</span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">Estimated Weight:</span><span class="lang-hi hidden">अनुमानित वजन:</span></span>
            <span class="font-bold text-gray-900"><?= number_format($total_weight_kg, 2) ?> kg</span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">Taxable Amount:</span><span class="lang-hi hidden">थोक मूल्य:</span></span>
            <span class="font-bold text-gray-900"><?= format_price($subtotal) ?></span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">GST (5% B2B Tax):</span><span class="lang-hi hidden">GST कर:</span></span>
            <span class="font-bold text-gray-900">+<?= format_price($gst_amount) ?></span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span><span class="lang-en">Wholesale Cargo / Freight:</span><span class="lang-hi hidden">थोक फ्रेट / शिपिंग:</span></span>
            <?php if ($shipping_fee == 0): ?>
              <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded"><span class="lang-en">FREE</span><span class="lang-hi hidden">निःशुल्क</span></span>
            <?php else: ?>
              <span class="font-bold text-gray-900"><?= format_price($shipping_fee) ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="py-4">
          <div class="flex items-baseline justify-between">
            <span class="text-sm font-black text-gray-900"><span class="lang-en">Grand Total (inc. GST):</span><span class="lang-hi hidden">कुल देय राशि:</span></span>
            <span class="text-xl font-black text-amber-700"><?= format_price($grand_total) ?></span>
          </div>
          <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
            <i class="fa-solid fa-shield-check text-emerald-600"></i>
            <span class="lang-en">All commercial taxes & freight included</span><span class="lang-hi hidden">सभी टैक्स एवं डोरस्टेप फ्रेट शामिल</span>
          </div>
        </div>

        <!-- GST ITC Benefit Notice -->
        <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200/80 mb-4 text-[11px] text-amber-900 flex items-start gap-2">
          <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 shrink-0"></i>
          <div>
            <span class="lang-en"><strong>GST Input Tax Credit Benefit:</strong> A formal commercial tax invoice will be generated on your GSTIN so you can claim full <strong><?= format_price($gst_amount) ?></strong> ITC.</span><span class="lang-hi hidden"><strong>GST ITC इनपुट बचत:</strong> आपके GSTIN पर पक्का इनवॉइस बनेगा जिससे आप पूरे <strong><?= format_price($gst_amount) ?></strong> का इनपुट टैक्स क्रेडिट क्लेम कर सकते हैं।</span>
          </div>
        </div>

        <!-- Checkout Button -->
        <a href="/retailer/checkout.php"
          class="w-full py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-600/30 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-lock"></i>
          <span class="lang-en">Proceed to B2B Checkout &rarr;</span><span class="lang-hi hidden">B2B चेकआउट पर जाएं &rarr;</span>
        </a>

        <div class="mt-3 text-center">
          <a href="/retailer/products.php" class="text-xs font-bold text-gray-500 hover:text-stone-800">
            <span class="lang-en">&larr; Add More Wholesale Products</span><span class="lang-hi hidden">&larr; और उत्पाद जोड़ें</span>
          </a>
        </div>
      </div>
    </div>

  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
