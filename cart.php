<?php
/**
 * GaunGaliHat Shopping Cart
 * Interactive cart with automatic shipping price calculator
 */

require_once __DIR__ . '/includes/header.php';

$coupon_msg = '';
$coupon_err = '';

// Handle Coupon Post Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_coupon'])) {
        $res = validate_and_apply_coupon($_POST['coupon_code'] ?? '');
        if ($res['success']) {
            $coupon_msg = $res['message'];
        } else {
            $coupon_err = $res['error'];
        }
    } elseif (isset($_POST['remove_coupon'])) {
        remove_applied_coupon();
        $coupon_msg = 'कूपन हटा दिया गया है।';
    }
}

$cart_items = get_cart();
$cart_subtotal = get_cart_subtotal();
$cart_weight = get_cart_total_weight();
$pincode = $_SESSION['shipping_pincode'] ?? '241126';
$shipping = calculate_shipping_rate($pincode, $cart_subtotal, $cart_weight);

$applied_coupon = get_applied_coupon();
$discount_amount = $applied_coupon ? floatval($applied_coupon['discount_amount']) : 0.0;
$final_total = max(0, $cart_subtotal + $shipping['shipping_fee'] - $discount_amount);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
  
  <div class="flex items-center justify-between border-b border-gray-200 pb-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
        Your Shopping Bag (<?= get_cart_count() ?> <?= get_cart_count() === 1 ? 'item' : 'items' ?>)
      </h1>
      <p class="text-xs text-gray-500 mt-1">Dispatched directly from Hardoi & Varanasi rural artisan forges and agricultural cooperatives.</p>
    </div>
    <a href="/products.php" class="text-xs font-bold text-brand-green hover:underline">
      ← Continue Shopping
    </a>
  </div>

  <?php if (empty($cart_items)): ?>
    <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center space-y-4 max-w-md mx-auto my-8">
      <div class="w-16 h-16 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto">
        <i data-lucide="shopping-bag" class="w-8 h-8"></i>
      </div>
      <h3 class="font-bold text-lg text-gray-900 font-display">Your bag is currently empty</h3>
      <p class="text-xs text-gray-500">
        Discover hand-forged agricultural sickles, khurpi, unpolished Awadh pulses, and Banarasi achar handcrafted by rural artisans.
      </p>
      <a href="/products.php" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
        <span>Explore Marketplace</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
  <?php else: ?>

    <!-- Free Delivery Progress Bar -->
    <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-xs space-y-2 text-xs">
      <div class="flex justify-between items-center font-bold">
        <span class="flex items-center gap-1.5 <?= $shipping['is_free'] ? 'text-emerald-800' : 'text-gray-800' ?>">
          <i data-lucide="<?= $shipping['is_free'] ? 'check-circle-2' : 'truck' ?>" class="w-4 h-4 text-brand-green"></i>
          <span><?= $shipping['is_free'] ? 'You have unlocked FREE Delivery across India!' : 'Add ' . format_price($shipping['remaining_for_free']) . ' more for FREE Delivery!' ?></span>
        </span>
        <span class="text-gray-400 font-mono">Threshold: <?= format_price($shipping['threshold']) ?></span>
      </div>
      <?php 
        $progress = min(100, round(($cart_subtotal / $shipping['threshold']) * 100));
      ?>
      <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-500 to-brand-green h-2.5 rounded-full transition-all duration-500" style="width: <?= $progress ?>%"></div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Cart Items Table -->
      <div class="lg:col-span-8 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-xs divide-y divide-gray-100">
        <?php foreach ($cart_items as $item): $p = $item['product']; ?>
          <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-20 h-20 object-cover rounded-2xl border border-gray-100 shrink-0">
              <div class="min-w-0">
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold"><?= htmlspecialchars($p['code']) ?></span>
                <h4 class="font-bold text-sm text-gray-900 truncate mt-0.5">
                  <a href="/product.php?id=<?= $p['id'] ?>" class="hover:text-brand-green"><?= htmlspecialchars($p['name']) ?></a>
                </h4>
                <p class="text-[11px] text-gray-500">By <?= htmlspecialchars($p['vendor_name'] ?? 'Rural Foundry') ?> • <?= format_price($p['price']) ?> / <?= htmlspecialchars($p['unit']) ?></p>
                <button onclick="handleCartAction('remove', <?= $p['id'] ?>)" class="text-[11px] text-red-500 hover:text-red-700 font-semibold mt-1 inline-flex items-center gap-1 cursor-pointer">
                  <i data-lucide="trash-2" class="w-3 h-3"></i> Remove
                </button>
              </div>
            </div>

            <!-- Qty & Total -->
            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
              <div class="flex items-center border border-gray-300 rounded-xl bg-gray-50 p-1">
                <button onclick="handleCartAction('update', <?= $p['id'] ?>, <?= $item['quantity'] - 1 ?>)" class="w-7 h-7 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded-md cursor-pointer">-</button>
                <span class="w-8 text-center text-xs font-bold font-mono"><?= $item['quantity'] ?></span>
                <button onclick="handleCartAction('update', <?= $p['id'] ?>, <?= $item['quantity'] + 1 ?>)" class="w-7 h-7 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded-md cursor-pointer">+</button>
              </div>
              <span class="font-extrabold text-sm text-gray-900 font-display shrink-0">
                <?= format_price($item['subtotal']) ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Right Column: Order Summary & Live Shipping Calculator -->
      <div class="lg:col-span-4 space-y-4">
        
        <!-- Live Shipping Calculator Card -->
        <div class="bg-white rounded-3xl border border-gray-200 p-6 space-y-4 shadow-xs">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
              <i data-lucide="calculator" class="w-4 h-4 text-brand-green"></i>
              <span>Live Delivery Fee Estimator</span>
            </h3>
            <span class="text-[10px] text-gray-400 font-mono">India Post</span>
          </div>

          <div class="space-y-2 text-xs">
            <label class="block font-semibold text-gray-700">Destination PIN Code</label>
            <div class="flex gap-2">
              <input 
                type="text" 
                id="cart-shipping-pincode" 
                maxlength="6" 
                value="<?= htmlspecialchars($pincode) ?>" 
                class="flex-1 p-2.5 border border-gray-300 rounded-xl font-mono font-bold text-gray-900 text-center outline-none focus:border-brand-green"
              >
              <button onclick="calculateCartShipping()" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs cursor-pointer">
                Calculate
              </button>
            </div>
            <div id="cart-shipping-status" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-900 space-y-0.5">
              <div class="flex justify-between font-bold">
                <span><?= htmlspecialchars($shipping['zone_name']) ?></span>
                <span class="text-brand-green"><?= $shipping['is_free'] ? 'FREE' : format_price($shipping['shipping_fee']) ?></span>
              </div>
              <p class="text-emerald-700">Estimated Delivery: <?= htmlspecialchars($shipping['eta_text']) ?> via <?= htmlspecialchars($shipping['carrier']) ?></p>
              <p class="text-gray-400 text-[10px]">Parcel Weight: <?= $shipping['total_weight_kg'] ?> kg</p>
            </div>
          </div>
        </div>

        <!-- Promo / Coupon Code Card -->
        <div class="bg-white rounded-3xl border border-gray-200 p-6 space-y-3 shadow-xs">
          <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
              <i data-lucide="tag" class="w-4 h-4 text-amber-500"></i>
              <span>कूपन कोड (Promo Code)</span>
            </h3>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full font-bold">छूट पाएं</span>
          </div>

          <?php if (!empty($coupon_msg)): ?>
            <div class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
              <?= htmlspecialchars($coupon_msg) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($coupon_err)): ?>
            <div class="p-2.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold">
              <?= htmlspecialchars($coupon_err) ?>
            </div>
          <?php endif; ?>

          <?php if ($applied_coupon): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs">
              <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-brand-green shrink-0"></i>
                <div>
                  <span class="font-bold text-emerald-950 font-mono"><?= htmlspecialchars($applied_coupon['code']) ?></span>
                  <span class="text-[11px] text-emerald-700 block"><?= htmlspecialchars($applied_coupon['title']) ?></span>
                </div>
              </div>
              <form action="/cart.php" method="POST">
                <input type="hidden" name="remove_coupon" value="1">
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold hover:underline">
                  हटाएं
                </button>
              </form>
            </div>
          <?php else: ?>
            <form action="/cart.php" method="POST" class="flex gap-2">
              <input type="hidden" name="apply_coupon" value="1">
              <input type="text" name="coupon_code" required placeholder="उदा. WELCOME50, FESTIVAL20"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-xl font-mono uppercase text-xs font-bold outline-none focus:border-brand-green">
              <button type="submit" class="px-4 py-2 bg-brand-green hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition">
                लागू करें
              </button>
            </form>
            <p class="text-[10px] text-gray-400">नए ग्राहक पहले आर्डर पर <strong>WELCOME50</strong> का उपयोग कर सकते हैं।</p>
          <?php endif; ?>
        </div>

        <!-- Summary & Checkout CTA -->
        <div class="bg-white rounded-3xl border border-gray-200 p-6 space-y-4 shadow-xs">
          <h3 class="font-bold text-base text-gray-900 font-display border-b border-gray-100 pb-3">Order Summary</h3>
          <div class="space-y-2.5 text-xs text-gray-600">
            <div class="flex justify-between">
              <span>Items Subtotal</span>
              <span id="cart-subtotal-disp" class="font-bold text-gray-900"><?= format_price($cart_subtotal) ?></span>
            </div>
            
            <?php if ($discount_amount > 0): ?>
              <div class="flex justify-between text-emerald-700 font-semibold bg-emerald-50/60 p-2 rounded-lg">
                <span class="flex items-center gap-1">
                  <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                  कूपन छूट (<?= htmlspecialchars($applied_coupon['code']) ?>)
                </span>
                <span>- <?= format_price($discount_amount) ?></span>
              </div>
            <?php endif; ?>

            <div class="flex justify-between">
              <span>Shipping Fee</span>
              <span id="cart-shipping-disp" class="font-bold <?= $shipping['is_free'] ? 'text-brand-green' : 'text-gray-900' ?>">
                <?= $shipping['is_free'] ? 'FREE' : format_price($shipping['shipping_fee']) ?>
              </span>
            </div>
            <div class="border-t border-gray-100 pt-3 flex justify-between font-extrabold text-base text-gray-900 font-display">
              <span>Total Payable</span>
              <span id="cart-final-disp" class="text-brand-green text-xl"><?= format_price($final_total) ?></span>
            </div>
          </div>

          <a href="/checkout.php" class="w-full py-4 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg hover:shadow-emerald-900/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95 block text-center">
            <i data-lucide="lock" class="w-4 h-4"></i>
            <span>Proceed to Secure Checkout</span>
          </a>

          <p class="text-[10px] text-center text-gray-400 flex items-center justify-center gap-1">
            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
            <span>256-Bit SSL Encrypted & India Post Tracked</span>
          </p>
        </div>

      </div>

    </div>

  <?php endif; ?>

</div>

<script>
function handleCartAction(action, productId, qty = 1) {
  const formData = new FormData();
  formData.append('action', action);
  formData.append('product_id', productId);
  formData.append('quantity', qty);

  fetch('/ajax/cart_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      }
    });
}

function calculateCartShipping() {
  const pin = document.getElementById('cart-shipping-pincode').value.trim();
  const statusDiv = document.getElementById('cart-shipping-status');
  if (pin.length !== 6) {
    statusDiv.innerHTML = '<span class="text-red-600">Please enter a valid 6-digit PIN.</span>';
    return;
  }
  statusDiv.innerHTML = '<span class="text-gray-500">Calculating...</span>';

  const formData = new FormData();
  formData.append('pincode', pin);
  formData.append('subtotal', <?= $cart_subtotal ?>);
  formData.append('total_weight', <?= $cart_weight ?>);

  fetch('/ajax/calculate_shipping.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.reload();
      } else {
        statusDiv.innerHTML = `<span class="text-red-600">${res.message}</span>`;
      }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
