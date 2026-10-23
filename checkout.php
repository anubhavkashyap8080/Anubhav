<?php
$page_title = 'Secure Checkout — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';

$cart = get_cart();
if (empty($cart)) {
    echo '<div class="max-w-2xl mx-auto py-20 px-4 text-center">
            <div class="w-16 h-16 bg-stone-100 rounded-full flex items-center justify-center mx-auto mb-4 text-stone-400">
                <i class="fa-solid fa-cart-shopping text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">आपकी टोकरी खाली है (Your cart is empty)</h2>
            <p class="text-sm text-gray-500 mb-6">कृपया आर्डर करने से पहले हस्तशिल्प उत्पाद जोड़ें।</p>
            <a href="/products.php" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-green text-white font-bold rounded-xl hover:bg-emerald-800 transition">
                उत्पाद देखें (Browse Products) <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$user = get_logged_in_user();
$subtotal = get_cart_subtotal();
$total_weight = get_cart_total_weight();
$default_pincode = $_SESSION['delivery_pincode'] ?? '241126';
$shipping = calculate_shipping_rate($default_pincode, $subtotal, $total_weight);
$shipping_fee = $shipping['shipping_fee'];

$applied_coupon = get_applied_coupon();
$discount_amount = $applied_coupon ? floatval($applied_coupon['discount_amount']) : 0.0;
$grand_total = max(0, $subtotal + $shipping_fee - $discount_amount);
?>

<div class="bg-stone-50 min-h-screen py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb & Title -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-1">
          <a href="/" class="hover:text-brand-green">होम</a>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
          <a href="/cart.php" class="hover:text-brand-green">शॉपिंग कार्ट</a>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
          <span class="text-gray-700 font-semibold">सुरक्षित चेकआउट</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-shield-halved text-brand-green"></i> सुरक्षित चेकआउट (Secure Checkout)
        </h1>
      </div>
      
      <div class="flex items-center gap-3 text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 px-3.5 py-1.5 rounded-full font-semibold">
        <i class="fa-solid fa-lock"></i> 256-Bit SSL एन्क्रिप्टेड सुरक्षा
      </div>
    </div>

    <form id="checkoutForm" action="/payments/process.php" method="POST">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Address & Payment Selection (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- Section 1: Customer & Delivery Address -->
          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
              <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-brand-green text-white flex items-center justify-center text-xs font-bold">1</span>
                डिलीवरी का पता (Delivery Address)
              </h2>
              <?php if (!$user): ?>
                <span class="text-xs text-gray-500">
                  खाता है? <a href="/login.php?redirect=checkout.php" class="text-brand-green font-bold hover:underline">लॉगिन करें</a>
                </span>
              <?php else: ?>
                <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full font-medium">
                  <?= htmlspecialchars($user['name']) ?> (लॉगिन हैं)
                </span>
              <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Full Name -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  पूरा नाम (Full Name) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-regular fa-user text-xs"></i>
                  </span>
                  <input type="text" name="full_name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                    placeholder="उदा. राहुल कुमार"
                    class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <!-- Phone Number -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  मोबाइल नंबर (Mobile Number) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                    +91
                  </span>
                  <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10"
                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                    placeholder="10-अंकीय नंबर"
                    class="w-full pl-12 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <!-- Email (Optional for tracking) -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  ईमेल पता (Email - Receipt & AWB)
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-regular fa-envelope text-xs"></i>
                  </span>
                  <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                    placeholder="name@example.com"
                    class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
              </div>

              <!-- Street Address / Village -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  घर/मकान सं., गली, गाँव या कस्बा (Street Address / Village) <span class="text-red-500">*</span>
                </label>
                <textarea name="street" required rows="2"
                  placeholder="मकान नंबर, मोहल्ला/टोला, निकटतम लैंडमार्क..."
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
              </div>

              <!-- Pincode -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  पिन कोड (PIN Code) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-location-dot text-xs"></i>
                  </span>
                  <input type="text" name="pincode" id="checkoutPincode" required pattern="[0-9]{6}" maxlength="6"
                    value="<?= htmlspecialchars($default_pincode) ?>"
                    class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm font-semibold tracking-wider focus:bg-white focus:border-brand-green focus:outline-none transition">
                </div>
                <p id="pincodeStatusNote" class="text-[11px] text-emerald-700 mt-1 flex items-center gap-1 font-medium">
                  <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($shipping['zone_name']) ?> (<?= htmlspecialchars($shipping['eta']) ?>)
                </p>
              </div>

              <!-- City / District -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  शहर / जिला (City / District) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="city" required value="हरदोई (Hardoi)"
                  class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
              </div>

              <!-- State -->
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  राज्य (State) <span class="text-red-500">*</span>
                </label>
                <select name="state" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-brand-green focus:outline-none transition">
                  <option value="Uttar Pradesh" selected>उत्तर प्रदेश (Uttar Pradesh)</option>
                  <option value="Delhi">दिल्ली (Delhi NCR)</option>
                  <option value="Bihar">बिहार (Bihar)</option>
                  <option value="Madhya Pradesh">मध्य प्रदेश (Madhya Pradesh)</option>
                  <option value="Rajasthan">राजस्थान (Rajasthan)</option>
                  <option value="Haryana">हरियाणा (Haryana)</option>
                  <option value="Punjab">पंजाब (Punjab)</option>
                  <option value="Maharashtra">महाराष्ट्र (Maharashtra)</option>
                  <option value="Gujarat">गुजरात (Gujarat)</option>
                  <option value="West Bengal">पश्चिम बंगाल (West Bengal)</option>
                  <option value="Other">अन्य राज्य (Other State)</option>
                </select>
              </div>

            </div>
          </div>

          <!-- Section 2: Payment Method Selection -->
          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
              <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-brand-green text-white flex items-center justify-center text-xs font-bold">2</span>
                भुगतान का तरीका (Payment Method)
              </h2>
              <span class="text-xs text-gray-400">100% सुरक्षित भुगतान</span>
            </div>

            <div class="space-y-3">
              <!-- Method 1: Cash on Delivery (COD) -->
              <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-emerald-500 bg-emerald-50/40 payment-option" data-method="cod">
                <input type="radio" name="payment_method" value="cod" checked class="mt-1 text-brand-green focus:ring-brand-green">
                <div class="ml-3 flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
                      <i class="fa-solid fa-hand-holding-dollar text-brand-green text-base"></i>
                      कैश ऑन डिलीवरी (Cash on Delivery / COD)
                    </span>
                    <span class="text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">सर्वाधिक लोकप्रिय</span>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    पार्सल आपके घर पहुँचने पर डाकिया (India Post) या डिलीवरी एजेंट को नकद या UPI द्वारा भुगतान करें।
                  </p>
                </div>
              </label>

              <!-- Method 2: Razorpay (UPI, GPay, PhonePe, Cards) -->
              <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-gray-200 hover:border-gray-300 payment-option" data-method="razorpay">
                <input type="radio" name="payment_method" value="razorpay" class="mt-1 text-brand-green focus:ring-brand-green">
                <div class="ml-3 flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
                      <i class="fa-solid fa-bolt text-blue-600 text-base"></i>
                      Razorpay — UPI, GPay, PhonePe, Cards, NetBanking
                    </span>
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">इंस्टेंट छूट संभव</span>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    Google Pay, PhonePe, Paytm, BHIM UPI, डेबिट/क्रेडिट कार्ड या नेट बैंकिंग से तत्काल भुगतान।
                  </p>
                </div>
              </label>

              <!-- Method 3: PayU Gateway -->
              <label class="relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition border-gray-200 hover:border-gray-300 payment-option" data-method="payu">
                <input type="radio" name="payment_method" value="payu" class="mt-1 text-brand-green focus:ring-brand-green">
                <div class="ml-3 flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-gray-900 flex items-center gap-2">
                      <i class="fa-solid fa-credit-card text-emerald-600 text-base"></i>
                      PayU Money Gateway
                    </span>
                    <span class="text-[10px] font-bold text-gray-600 bg-stone-100 px-2 py-0.5 rounded">Cards & Wallets</span>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    PayU सुरक्षित पेमेंट गेटवे — RuPay, Visa, Mastercard, Wallets और नेट बैंकिंग।
                  </p>
                </div>
              </label>
            </div>

            <!-- Trust Highlights -->
            <div class="mt-6 pt-5 border-t border-gray-100 grid grid-cols-3 gap-3 text-center text-[11px] text-gray-500">
              <div class="flex flex-col items-center gap-1">
                <i class="fa-solid fa-shield-cat text-brand-green text-lg"></i>
                <span>कारीगर सीधा संरक्षण</span>
              </div>
              <div class="flex flex-col items-center gap-1">
                <i class="fa-solid fa-truck-fast text-brand-green text-lg"></i>
                <span>इंडिया पोस्ट स्पीड पोस्ट</span>
              </div>
              <div class="flex flex-col items-center gap-1">
                <i class="fa-solid fa-rotate-left text-brand-green text-lg"></i>
                <span>7-दिवसीय आसान वापसी</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Right: Order Summary Sticky Card (5 cols) -->
        <div class="lg:col-span-5 sticky top-24 space-y-4">
          
          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 pb-3 border-b border-gray-100 flex items-center justify-between">
              <span>आर्डर विवरण (Order Summary)</span>
              <span class="text-xs font-semibold text-gray-500"><?= count($cart) ?> वस्तुएं</span>
            </h3>

            <!-- Mini Item List -->
            <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto my-3 pr-1">
              <?php foreach ($cart as $item): 
                $p = $item['product'];
              ?>
                <div class="py-3 flex items-center gap-3">
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                    class="w-12 h-12 object-cover rounded-lg border border-gray-200 bg-stone-100 flex-shrink-0">
                  <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-bold text-gray-900 truncate"><?= htmlspecialchars($p['name']) ?></h4>
                    <p class="text-[11px] text-gray-500">मात्रा: <?= $item['quantity'] ?> × <?= format_price($p['price']) ?></p>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <span class="text-xs font-bold text-gray-900"><?= format_price($item['subtotal']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-2.5 pt-3 border-t border-gray-100 text-xs text-gray-600">
              <div class="flex justify-between">
                <span>सामान का मूल्य (Subtotal)</span>
                <span class="font-bold text-gray-900"><?= format_price($subtotal) ?></span>
              </div>

              <div class="flex justify-between">
                <span>कुल पार्सल भार (Total Weight)</span>
                <span class="font-bold text-gray-900"><?= number_format($total_weight, 2) ?> किग्रा</span>
              </div>

              <div class="flex justify-between items-center">
                <span>
                  डिलीवरी शुल्क (Shipping Fee)
                  <span id="shippingZoneLabel" class="block text-[10px] text-gray-400 font-normal">
                    (Zone: <?= htmlspecialchars($shipping['zone_name']) ?>)
                  </span>
                </span>
                <span id="shippingFeeDisplay" class="font-bold <?= $shipping_fee == 0 ? 'text-emerald-700' : 'text-gray-900' ?>">
                  <?= $shipping_fee == 0 ? 'मुफ़्त (FREE)' : format_price($shipping_fee) ?>
                </span>
              </div>

              <?php if ($discount_amount > 0): ?>
                <div class="flex justify-between items-center text-emerald-800 bg-emerald-50 p-2.5 rounded-xl font-bold text-xs">
                  <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-ticket text-brand-green"></i>
                    कूपन छूट (<?= htmlspecialchars($applied_coupon['code']) ?>)
                  </span>
                  <span>- <?= format_price($discount_amount) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                <div id="freeShippingHint" class="bg-amber-50 text-amber-800 p-2.5 rounded-xl text-[11px] flex items-center gap-2">
                  <i class="fa-solid fa-circle-info"></i>
                  <span>₹<?= number_format(FREE_SHIPPING_THRESHOLD - $subtotal) ?> की और ख़रीदारी करें एवं <strong>मुफ़्त डिलीवरी</strong> पाएं!</span>
                </div>
              <?php else: ?>
                <div class="bg-emerald-50 text-emerald-800 p-2.5 rounded-xl text-[11px] flex items-center gap-2">
                  <i class="fa-solid fa-circle-check"></i>
                  <span>बधाई! आपका आर्डर <strong>मुफ़्त डिलीवरी (Free Delivery)</strong> के पात्र है।</span>
                </div>
              <?php endif; ?>

              <div class="pt-3 border-t border-gray-200 flex justify-between items-baseline">
                <span class="text-sm font-extrabold text-gray-900">कुल भुगतान (Grand Total)</span>
                <span id="grandTotalDisplay" class="text-xl font-extrabold text-brand-green">
                  <?= format_price($grand_total) ?>
                </span>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitOrderBtn"
              class="w-full mt-5 py-3.5 bg-brand-green text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 active:scale-[0.98] transition flex items-center justify-center gap-2 text-sm">
              <i class="fa-solid fa-shield-halved"></i>
              <span id="submitBtnText">आर्डर कन्फर्म करें (Place Order — <?= format_price($grand_total) ?>)</span>
            </button>

            <p class="text-[11px] text-gray-400 text-center mt-3 flex items-center justify-center gap-1">
              <i class="fa-solid fa-truck-pickup text-emerald-600"></i>
              सीधे हथकरघा व लोहार कुटीर से प्रेषित
            </p>
          </div>

          <!-- India Post Speed Post Guaranteed -->
          <div class="bg-emerald-900/5 rounded-2xl border border-emerald-900/10 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-green text-white flex items-center justify-center flex-shrink-0 text-sm">
              <i class="fa-solid fa-truck"></i>
            </div>
            <div class="text-xs">
              <h4 class="font-bold text-brand-dark">भारतीय डाक (India Post) आधिकारिक पार्सल पार्टनर</h4>
              <p class="text-gray-600 text-[11px]">हरदोई मुख्य डाकघर से सीधा स्पीड पोस्ट ट्रैकिंग नंबर प्रदान किया जाएगा।</p>
            </div>
          </div>

        </div>

      </div>
    </form>

  </div>
</div>

<script>
// Dynamic Payment Option Styling
document.querySelectorAll('.payment-option input[type="radio"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.payment-option').forEach(el => {
      el.classList.remove('border-emerald-500', 'bg-emerald-50/40');
      el.classList.add('border-gray-200');
    });
    const parent = this.closest('.payment-option');
    parent.classList.remove('border-gray-200');
    parent.classList.add('border-emerald-500', 'bg-emerald-50/40');

    // Update submit button text
    const method = this.value;
    const btnText = document.getElementById('submitBtnText');
    const grandTotal = document.getElementById('grandTotalDisplay').innerText;
    if (method === 'cod') {
      btnText.innerText = 'आर्डर कन्फर्म करें (Place COD Order — ' + grandTotal + ')';
    } else if (method === 'razorpay') {
      btnText.innerText = 'Razorpay से भुगतान करें (Pay with Razorpay)';
    } else if (method === 'payu') {
      btnText.innerText = 'PayU Gateway पर जाएं (Proceed with PayU)';
    }
  });
});

// Dynamic Pincode Shipping Recalculation
const pincodeInput = document.getElementById('checkoutPincode');
if (pincodeInput) {
  let debounceTimeout;
  pincodeInput.addEventListener('input', function() {
    clearTimeout(debounceTimeout);
    const pin = this.value.trim();
    if (pin.length === 6) {
      debounceTimeout = setTimeout(() => {
        fetch('/ajax/calculate_shipping.php?pincode=' + pin)
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              const fee = data.shipping_fee;
              const subtotal = data.subtotal;
              const grand = data.grand_total;
              
              document.getElementById('shippingFeeDisplay').innerText = fee === 0 ? 'मुफ़्त (FREE)' : '₹' + fee.toLocaleString('en-IN');
              document.getElementById('shippingZoneLabel').innerText = '(Zone: ' + data.zone_name + ')';
              document.getElementById('grandTotalDisplay').innerText = '₹' + grand.toLocaleString('en-IN');
              document.getElementById('pincodeStatusNote').innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.zone_name + ' (' + data.eta + ')';
              
              const activeRadio = document.querySelector('input[name="payment_method"]:checked');
              if (activeRadio && activeRadio.value === 'cod') {
                document.getElementById('submitBtnText').innerText = 'आर्डर कन्फर्म करें (Place COD Order — ₹' + grand.toLocaleString('en-IN') + ')';
              }
            }
          })
          .catch(e => console.error(e));
      }, 300);
    }
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
