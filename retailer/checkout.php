<?php
/**
 * GanvGaliHat Retailer Hub — B2B Wholesale Checkout
 * Captures GSTIN, Business Delivery Address, and dispatches wholesale orders directly to village makers.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_retailer();

$cart = get_bulk_cart();
if (empty($cart)) {
    set_flash('error', 'आपकी थोक कार्ट खाली है। कृपया चेकआउट करने से पहले उत्पाद जोड़ें।');
    header('Location: /retailer/products.php');
    exit;
}

$user = get_logged_in_user();
$subtotal = get_bulk_cart_subtotal();
$total_units = get_bulk_cart_count();

$gst_rate = 0.05;
$gst_amount = round($subtotal * $gst_rate, 2);
$shipping_fee = ($subtotal >= 4999 || $subtotal == 0) ? 0.00 : 199.00;
$grand_total = round($subtotal + $gst_amount + $shipping_fee, 2);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    csrf_protect();

    $business_name  = clean_input($_POST['business_name'] ?? ($user['business_name'] ?? $user['name']));
    $gstin          = strtoupper(clean_input($_POST['gstin'] ?? ($user['gstin'] ?? '')));
    $contact_name   = clean_input($_POST['contact_name'] ?? $user['name']);
    $contact_phone  = clean_input($_POST['contact_phone'] ?? $user['phone']);
    $contact_email  = clean_input($_POST['contact_email'] ?? $user['email']);
    
    $shipping_street  = clean_input($_POST['shipping_street'] ?? ($user['business_address'] ?? $user['address'] ?? ''));
    $shipping_city    = clean_input($_POST['shipping_city'] ?? ($user['business_city'] ?? $user['city'] ?? ''));
    $shipping_state   = clean_input($_POST['shipping_state'] ?? ($user['business_state'] ?? $user['state'] ?? 'Uttar Pradesh'));
    $shipping_pincode = clean_input($_POST['shipping_pincode'] ?? ($user['business_pincode'] ?? $user['pincode'] ?? ''));
    $transport_pref   = clean_input($_POST['transport_pref'] ?? 'Standard Surface Logistics');
    $payment_method   = clean_input($_POST['payment_method'] ?? 'bank_transfer');
    $order_notes      = clean_input($_POST['order_notes'] ?? '');

    if (empty($business_name) || empty($contact_name)) {
        $error = 'कृपया फर्म/दुकान का नाम और संपर्क व्यक्ति का नाम दर्ज करें।';
    } elseif (empty($contact_phone) || strlen(preg_replace('/[^0-9]/', '', $contact_phone)) < 10) {
        $error = 'कृपया वैध 10 अंकों का संपर्क फोन नंबर दर्ज करें।';
    } elseif (empty($shipping_street) || empty($shipping_city) || empty($shipping_pincode)) {
        $error = 'कृपया माल डिलीवरी का पूरा पता, शहर और पिनकोड दर्ज करें।';
    } else {
        try {
            $pdo->beginTransaction();

            // Generate B2B Order Identifier
            $order_number = 'B2B-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $order_access_token = bin2hex(random_bytes(16));
            $consignment_no = 'PL-' . date('ym') . '-' . rand(10000, 99999);

            $initial_payment_status = ($payment_method === 'online') ? 'pending' : 'pending_confirmation';
            $initial_order_status = 'Confirmed';

            // Insert into orders table with B2B columns
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_phone, customer_email,
                    shipping_street, shipping_city, shipping_state, shipping_pincode,
                    subtotal, shipping_fee, total_amount, payment_method, payment_status,
                    order_status, tracking_carrier, tracking_consignment_no, order_access_token,
                    order_type, business_name, gstin, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    'wholesale_b2b', ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $order_number,
                $user['id'],
                $contact_name,
                $contact_phone,
                $contact_email,
                $shipping_street . ($order_notes ? " (Note: {$order_notes})" : ""),
                $shipping_city,
                $shipping_state,
                $shipping_pincode,
                $subtotal,
                $shipping_fee,
                $grand_total,
                $payment_method,
                $initial_payment_status,
                $initial_order_status,
                'Rural Cargo Freight',
                $consignment_no,
                $order_access_token,
                $business_name,
                $gstin
            ]);

            $order_id = $pdo->lastInsertId();

            // Insert into order_items
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, vendor_id, product_name, product_code, price, quantity, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($cart as $item) {
                $item_total = round(floatval($item['bulk_price']) * intval($item['quantity']), 2);
                $stmt_item->execute([
                    $order_id,
                    $item['product_id'],
                    $item['vendor_id'] ?? 1,
                    $item['name'],
                    $item['code'] ?? '',
                    $item['bulk_price'],
                    $item['quantity'],
                    $item_total
                ]);
            }

            // Also update user's business profile if not previously set
            $stmt_upd_user = $pdo->prepare("
                UPDATE users SET 
                    business_name = COALESCE(NULLIF(business_name, ''), ?),
                    gstin = COALESCE(NULLIF(gstin, ''), ?),
                    business_address = COALESCE(NULLIF(business_address, ''), ?),
                    business_city = COALESCE(NULLIF(business_city, ''), ?),
                    business_state = COALESCE(NULLIF(business_state, ''), ?),
                    business_pincode = COALESCE(NULLIF(business_pincode, ''), ?)
                WHERE id = ?
            ");
            $stmt_upd_user->execute([
                $business_name,
                $gstin,
                $shipping_street,
                $shipping_city,
                $shipping_state,
                $shipping_pincode,
                $user['id']
            ]);

            $pdo->commit();

            // Clear wholesale cart
            clear_bulk_cart();

            log_audit_action('retailer_placed_b2b_order', 'orders', "B2B Order #{$order_number} placed by {$business_name} (User #{$user['id']}) for ₹{$grand_total}");
            set_flash('success', "बधाई हो! आपका थोक आर्डर #{$order_number} सफलतापूर्वक दर्ज कर लिया गया है।");
            header('Location: /retailer/orders.php?order_id=' . $order_id . '&placed=1');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'आर्डर सुरक्षित करने में त्रुटि: ' . $e->getMessage();
        }
    }
}

$page_title = 'थोक चेकआउट (B2B Bulk Checkout)';
require_once __DIR__ . '/retailer_header.php';
?>

<div class="mb-6 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-lock text-emerald-600"></i>
      थोक चेकआउट (B2B Bulk Checkout)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      पक्का GST इनवॉइस एवं सुरक्षित फ्रेट डिलीवरी • आर्डर कुल <?= format_price($grand_total) ?>
    </p>
  </div>
  <a href="/retailer/cart.php" class="text-xs font-bold text-amber-600 hover:text-amber-700">
    &larr; थोक कार्ट में वापस जाएं
  </a>
</div>

<?php if (!empty($error)): ?>
  <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-2.5">
    <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5"></i>
    <div><?= htmlspecialchars($error) ?></div>
  </div>
<?php endif; ?>

<form method="POST" action="/retailer/checkout.php">
  <?= csrf_field() ?>
  <input type="hidden" name="place_order" value="1">

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left 7 Cols: Address & Tax & Payment Details -->
    <div class="lg:col-span-7 space-y-6">
      
      <!-- Box 1: Business & Tax Credentials -->
      <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-6 space-y-4">
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-gray-100">
          <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">1</span>
          व्यापार एवं इनवॉइस विवरण (GST & Business Details)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">दुकान / फर्म का नाम <span class="text-red-500">*</span></label>
            <input type="text" name="business_name" required
              value="<?= htmlspecialchars($_POST['business_name'] ?? ($user['business_name'] ?? $user['name'])) ?>"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">GSTIN नंबर (ITC क्लेम हेतु)</label>
            <input type="text" name="gstin" maxlength="15"
              value="<?= htmlspecialchars($_POST['gstin'] ?? ($user['gstin'] ?? '')) ?>"
              placeholder="उदा. 09ABCDE1234F1Z5"
              class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-mono font-bold uppercase focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">संपर्क व्यक्ति नाम <span class="text-red-500">*</span></label>
            <input type="text" name="contact_name" required
              value="<?= htmlspecialchars($_POST['contact_name'] ?? $user['name']) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">मोबाइल नंबर <span class="text-red-500">*</span></label>
            <input type="tel" name="contact_phone" required maxlength="10"
              value="<?= htmlspecialchars($_POST['contact_phone'] ?? $user['phone']) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">ईमेल पता</label>
            <input type="email" name="contact_email"
              value="<?= htmlspecialchars($_POST['contact_email'] ?? $user['email']) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>
        </div>
      </div>

      <!-- Box 2: Delivery Godown Address -->
      <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-6 space-y-4">
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-gray-100">
          <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">2</span>
          डिलीवरी गोदाम / दुकान का पता (Delivery Destination)
        </h2>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">पूरा पता व लैंडमार्क <span class="text-red-500">*</span></label>
          <textarea name="shipping_street" rows="2" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-amber-600 transition"><?= htmlspecialchars($_POST['shipping_street'] ?? ($user['business_address'] ?? $user['address'] ?? '')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">शहर / कस्बा <span class="text-red-500">*</span></label>
            <input type="text" name="shipping_city" required
              value="<?= htmlspecialchars($_POST['shipping_city'] ?? ($user['business_city'] ?? $user['city'] ?? '')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">राज्य <span class="text-red-500">*</span></label>
            <input type="text" name="shipping_state" required
              value="<?= htmlspecialchars($_POST['shipping_state'] ?? ($user['business_state'] ?? $user['state'] ?? 'Uttar Pradesh')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">पिनकोड <span class="text-red-500">*</span></label>
            <input type="text" name="shipping_pincode" required maxlength="6"
              value="<?= htmlspecialchars($_POST['shipping_pincode'] ?? ($user['business_pincode'] ?? $user['pincode'] ?? '')) ?>"
              class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-600 transition">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">डिस्पैच / फ्रेट प्राथमिकता</label>
          <select name="transport_pref" class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-amber-600 transition">
            <option value="Direct Cluster Freight">डायरेक्ट क्लस्टर फ्रेट (Standard Surface Cargo)</option>
            <option value="India Post Express Logistics">इंडिया पोस्ट एक्सप्रेस पार्सल</option>
            <option value="Local Transport Godown Booking">स्थानीय ट्रांसपोर्ट गोदाम बिल्टी (To-Pay/Paid)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">विशेष निर्देश / पैकिंग निर्देश (वैकल्पिक)</label>
          <input type="text" name="order_notes" placeholder="उदा. लकड़ी की पेटियों में पैक करें, दुकान समय 10 AM - 7 PM"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-amber-600 transition">
        </div>
      </div>

      <!-- Box 3: Payment Method -->
      <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-6 space-y-3">
        <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-gray-100">
          <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold">3</span>
          थोक भुगतान विकल्प (B2B Payment Method)
        </h2>

        <div class="space-y-3">
          <label class="p-3.5 rounded-2xl border border-gray-200 flex items-start gap-3 cursor-pointer hover:border-amber-500 hover:bg-amber-50/30 transition">
            <input type="radio" name="payment_method" value="bank_transfer" checked class="mt-1 text-amber-600 focus:ring-amber-500">
            <div>
              <div class="font-bold text-xs text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-amber-600"></i>
                सीधे बैंक ट्रांसफर / NEFT / RTGS / IMPS
              </div>
              <p class="text-[11px] text-gray-500 mt-0.5">
                आर्डर प्लेस होते ही कंपनी का करंट खाता व IFSC कोड स्क्रीन पर दिखेगा। शून्य अतिरिक्त गेटवे शुल्क।
              </p>
            </div>
          </label>

          <label class="p-3.5 rounded-2xl border border-gray-200 flex items-start gap-3 cursor-pointer hover:border-amber-500 hover:bg-amber-50/30 transition">
            <input type="radio" name="payment_method" value="online" class="mt-1 text-amber-600 focus:ring-amber-500">
            <div>
              <div class="font-bold text-xs text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-credit-card text-blue-600"></i>
                ऑनलाइन पेमेंट (UPI / NetBanking / डेबिट व क्रेडिट कार्ड)
              </div>
              <p class="text-[11px] text-gray-500 mt-0.5">
                सुरक्षित तत्काल भुगतान। आर्डर तुरंत उत्पादन / डिस्पैच कतार में लग जाएगा।
              </p>
            </div>
          </label>

          <label class="p-3.5 rounded-2xl border border-gray-200 flex items-start gap-3 cursor-pointer hover:border-amber-500 hover:bg-amber-50/30 transition">
            <input type="radio" name="payment_method" value="cod_wholesale" class="mt-1 text-amber-600 focus:ring-amber-500">
            <div>
              <div class="font-bold text-xs text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i>
                थोक COD (20% एडवांस फ्रेट / शेष डिलीवरी पर)
              </div>
              <p class="text-[11px] text-gray-500 mt-0.5">
                सत्यापित खुदरा दुकानदारों के लिए माल डिलीवरी के समय शेष भुगतान की सुविधा।
              </p>
            </div>
          </label>
        </div>
      </div>

    </div>

    <!-- Right 5 Cols: Items Review & Order Summary -->
    <div class="lg:col-span-5 space-y-4">
      <div class="bg-white rounded-3xl border border-gray-200/90 shadow-xs p-6">
        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider pb-3 border-b border-gray-100 flex items-center justify-between">
          <span>ऑर्डर आयटम्स (<?= count($cart) ?>)</span>
          <a href="/retailer/cart.php" class="text-xs font-bold text-amber-600 hover:text-amber-700">बदलें</a>
        </h3>

        <!-- Mini items list -->
        <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto my-3 pr-1">
          <?php foreach ($cart as $item): ?>
            <div class="py-2.5 flex items-center justify-between text-xs">
              <div class="flex items-center gap-2.5">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="w-10 h-10 rounded-xl object-cover border border-stone-200">
                <div>
                  <div class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="text-[10px] text-gray-500"><?= $item['quantity'] ?> पीस × <?= format_price($item['bulk_price']) ?></div>
                </div>
              </div>
              <div class="font-black text-gray-900">
                <?= format_price($item['bulk_price'] * $item['quantity']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Bill breakdown -->
        <div class="space-y-2.5 py-3 border-t border-b border-gray-100 text-xs">
          <div class="flex items-center justify-between text-gray-600">
            <span>थोक माल मूल्य (Taxable):</span>
            <span class="font-bold text-gray-900"><?= format_price($subtotal) ?></span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span>GST कर (5% Input Credit):</span>
            <span class="font-bold text-gray-900">+<?= format_price($gst_amount) ?></span>
          </div>

          <div class="flex items-center justify-between text-gray-600">
            <span>थोक फ्रेट / डिलीवरी:</span>
            <?php if ($shipping_fee == 0): ?>
              <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">निःशुल्क (Free)</span>
            <?php else: ?>
              <span class="font-bold text-gray-900"><?= format_price($shipping_fee) ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="py-4">
          <div class="flex items-baseline justify-between">
            <span class="text-sm font-black text-gray-900">कुल देय राशि:</span>
            <span class="text-2xl font-black text-amber-700"><?= format_price($grand_total) ?></span>
          </div>
        </div>

        <button type="submit"
          class="w-full py-4 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-600/30 transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-circle-check"></i>
          थोक आर्डर कन्फर्म करें (Place Wholesale Order)
        </button>

        <p class="text-[10px] text-gray-400 text-center mt-3">
          आर्डर प्लेस करने पर आप GanvGaliHat B2B थोक डिलीवरी एवं कैंसिलेशन नियमों से सहमत होते हैं।
        </p>
      </div>
    </div>

  </div>
</form>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
