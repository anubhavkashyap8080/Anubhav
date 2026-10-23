<?php
/**
 * GaunGaliHat Payment Gateway Processor
 * Handles COD, Razorpay, and PayU Order Execution
 */

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /checkout.php');
    exit;
}

$cart_items = get_cart();
if (empty($cart_items)) {
    set_flash('error', 'Your shopping cart is empty.');
    header('Location: /cart.php');
    exit;
}

// Sanitize Customer Details
$customer_name = clean_input($_POST['full_name'] ?? '');
$customer_phone = clean_input($_POST['phone'] ?? '');
$customer_email = clean_input($_POST['email'] ?? '');
$shipping_street = clean_input($_POST['street'] ?? '');
$shipping_city = clean_input($_POST['city'] ?? '');
$shipping_state = clean_input($_POST['state'] ?? 'Uttar Pradesh');
$shipping_pincode = clean_input($_POST['pincode'] ?? '241126');
$payment_method = clean_input($_POST['payment_method'] ?? 'cod');

if (empty($customer_name) || empty($customer_phone) || empty($shipping_street) || empty($shipping_pincode)) {
    set_flash('error', 'Please complete all required delivery address fields.');
    header('Location: /checkout.php');
    exit;
}

// Calculate precise subtotal and shipping fee
$subtotal = get_cart_subtotal();
$total_weight = get_cart_total_weight();
$shipping = calculate_shipping_rate($shipping_pincode, $subtotal, $total_weight);
$shipping_fee = $shipping['shipping_fee'];
$final_total = $subtotal + $shipping_fee;

$order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
$consignment_no = 'EU' . rand(100000000, 999999999) . 'IN';
$user_id = $_SESSION['user_id'] ?? null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, customer_name, customer_phone, customer_email,
            shipping_street, shipping_city, shipping_state, shipping_pincode,
            subtotal, shipping_fee, total_amount, payment_method, payment_status,
            order_status, tracking_carrier, tracking_consignment_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $initial_payment_status = ($payment_method === 'cod') ? 'pending' : 'pending';
    $initial_order_status = ($payment_method === 'cod') ? 'Confirmed' : 'Order Placed';

    $stmt->execute([
        $order_number, $user_id, $customer_name, $customer_phone, $customer_email,
        $shipping_street, $shipping_city, $shipping_state, $shipping_pincode,
        $subtotal, $shipping_fee, $final_total, $payment_method, $initial_payment_status,
        $initial_order_status, 'India Post Express', $consignment_no
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert Order Items
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, vendor_id, product_name, product_code, price, quantity, total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $p = $item['product'];
        $stmt_item->execute([
            $order_id, $p['id'], $p['vendor_id'], $p['name'], $p['code'],
            $p['price'], $item['quantity'], $item['subtotal']
        ]);

        // Decrement product stock safely
        $stmt_stock = $pdo->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?");
        $stmt_stock->execute([$item['quantity'], $p['id']]);
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', 'Order placement failed: ' . $e->getMessage());
    header('Location: /checkout.php');
    exit;
}

// 1. CASH ON DELIVERY (COD) FLOW
if ($payment_method === 'cod') {
    clear_cart();
    set_flash('success', 'Order #' . $order_number . ' successfully confirmed with Cash on Delivery!');
    header('Location: /order-success.php?id=' . $order_id);
    exit;
}

// 2. RAZORPAY GATEWAY CHECKOUT MODAL
if ($payment_method === 'razorpay') {
    // Render Razorpay Standard Checkout
    $key_id = RAZORPAY_KEY_ID;
    $amount_paisa = (int)round($final_total * 100);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Processing Payment — Razorpay</title>
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-xl max-w-md w-full text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto text-xl font-bold animate-pulse">
          ⚡
        </div>
        <h3 class="text-xl font-bold text-gray-900">Redirecting to Razorpay Gateway...</h3>
        <p class="text-xs text-gray-500">Order #<?= htmlspecialchars($order_number) ?> • Amount: <?= format_price($final_total) ?></p>
        <button id="rzp-button" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700">Click if popup does not open</button>
      </div>

      <form id="rzp-form" action="/payments/razorpay_verify.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
      </form>

      <script>
      var options = {
          "key": "<?= $key_id ?>",
          "amount": "<?= $amount_paisa ?>",
          "currency": "INR",
          "name": "<?= htmlspecialchars(APP_NAME) ?>",
          "description": "Order #<?= htmlspecialchars($order_number) ?> - <?= htmlspecialchars(APP_COMPANY) ?>",
          "image": "/logo.png",
          "handler": function (response){
              document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
              document.getElementById('rzp-form').submit();
          },
          "prefill": {
              "name": "<?= htmlspecialchars($customer_name) ?>",
              "email": "<?= htmlspecialchars($customer_email) ?>",
              "contact": "<?= htmlspecialchars($customer_phone) ?>"
          },
          "theme": {
              "color": "#1b4332"
          }
      };
      var rzp1 = new Razorpay(options);
      document.getElementById('rzp-button').onclick = function(e){
          rzp1.open();
          e.preventDefault();
      }
      window.onload = function() {
          rzp1.open();
      }
      </script>
    </body>
    </html>
    <?php
    exit;
}

// 3. PAYU GATEWAY REDIRECT FLOW
if ($payment_method === 'payu') {
    $merchant_key = PAYU_MERCHANT_KEY;
    $salt = PAYU_MERCHANT_SALT;
    $payu_url = PAYU_URL;
    $txnid = $order_number;
    $amount_str = number_format($final_total, 2, '.', '');
    $productinfo = "Order $order_number from GaunGaliHat";
    $firstname = $customer_name;
    $email = $customer_email ?: 'customer@ganvgalihat.com';
    $phone = $customer_phone;
    $surl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/payments/payu_callback.php?status=success&order_id=' . $order_id;
    $furl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/payments/payu_callback.php?status=failure&order_id=' . $order_id;

    // Generate PayU SHA-512 Hash Sequence:
    // sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
    $hashSequence = "$merchant_key|$txnid|$amount_str|$productinfo|$firstname|$email|||||||||||$salt";
    $hash = strtolower(hash('sha512', $hashSequence));
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Redirecting to PayU...</title>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-xl max-w-md w-full text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-brand-green flex items-center justify-center mx-auto text-xl font-bold animate-pulse">
          🔒
        </div>
        <h3 class="text-xl font-bold text-gray-900">Connecting to PayU Money Gateway...</h3>
        <p class="text-xs text-gray-500">Amount: <?= format_price($final_total) ?></p>
        <p class="text-[11px] text-gray-400">Please do not refresh or press back button.</p>
      </div>

      <form id="payuForm" action="<?= $payu_url ?>" method="POST">
        <input type="hidden" name="key" value="<?= $merchant_key ?>" />
        <input type="hidden" name="hash" value="<?= $hash ?>" />
        <input type="hidden" name="txnid" value="<?= $txnid ?>" />
        <input type="hidden" name="amount" value="<?= $amount_str ?>" />
        <input type="hidden" name="firstname" value="<?= htmlspecialchars($firstname) ?>" />
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>" />
        <input type="hidden" name="productinfo" value="<?= htmlspecialchars($productinfo) ?>" />
        <input type="hidden" name="surl" value="<?= htmlspecialchars($surl) ?>" />
        <input type="hidden" name="furl" value="<?= htmlspecialchars($furl) ?>" />
        <input type="hidden" name="service_provider" value="payu_paisa" />
      </form>

      <script>
        window.onload = function() {
          document.getElementById('payuForm').submit();
        };
      </script>
    </body>
    </html>
    <?php
    exit;
}
