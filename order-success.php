<?php
/**
 * GaunGaliHat Order Confirmation & Receipt (Item 11 Hardened Access)
 */

require_once __DIR__ . '/includes/functions.php';

$order_id = intval($_GET['id'] ?? 0);
$token = trim((string)($_GET['token'] ?? ''));

if (!$order_id) {
    header('Location: /');
    exit;
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $page_title = 'Order Not Found — GaunGaliHat';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="max-w-xl mx-auto py-20 px-4 text-center">
            <h2 class="text-xl font-bold text-gray-800">आर्डर नहीं मिला (Order Not Found)</h2>
            <a href="/" class="mt-4 inline-block px-5 py-2.5 bg-brand-green text-white rounded-xl">होमपेज पर जाएं</a>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Item 11: Access Authorization Check (Owner, Admin, or Valid Guest Access Token)
$is_authorized = false;

// 1. Session owner check
if (is_logged_in()) {
    $current_uid = (int)($_SESSION['user_id'] ?? 0);
    $current_role = $_SESSION['user_role'] ?? '';
    if ($current_uid > 0 && (int)$order['user_id'] === $current_uid) {
        $is_authorized = true;
    } elseif (in_array($current_role, ['admin', 'super_admin'], true)) {
        $is_authorized = true;
    }
}

// 2. Just placed in current session checkout
if (!empty($_SESSION['last_placed_order_id']) && (int)$_SESSION['last_placed_order_id'] === $order_id) {
    $is_authorized = true;
}

// 3. Guest long random order access token check
if (!$is_authorized && !empty($token) && !empty($order['order_access_token'])) {
    if (hash_equals($order['order_access_token'], $token)) {
        $is_authorized = true;
    }
}

if (!$is_authorized) {
    http_response_code(403);
    set_flash('error', 'अनधिकृत पहुंच: इस आर्डर विवरण को देखने की अनुमति नहीं है। (Access Denied)');
    header('Location: /login.php');
    exit;
}

$page_title = 'Order Confirmation — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';

// Fetch items
$stmt_items = $pdo->prepare("
    SELECT oi.*, p.image_url, p.category_id, v.shop_name, v.district 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN vendors v ON oi.vendor_id = v.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>

<div class="bg-stone-50 min-h-screen py-10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6">

    <!-- Success Banner -->
    <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm text-center mb-8">
      <div class="w-16 h-16 bg-emerald-100 text-brand-green rounded-full flex items-center justify-center mx-auto text-2xl font-bold mb-4 shadow-sm animate-bounce">
        <i class="fa-solid fa-check"></i>
      </div>
      
      <span class="inline-block text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 mb-2">
        आर्डर सफलतापूर्वक स्वीकार किया गया
      </span>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
        धन्यवाद! आपका आर्डर दर्ज हो चुका है।
      </h1>
      <p class="text-sm text-gray-600 max-w-lg mx-auto mb-4">
        आर्डर क्रमांक <strong class="text-brand-green">#<?= htmlspecialchars($order['order_number']) ?></strong> की पुष्टि कर दी गई है। हरदोई के ग्रामीण कारीगर आपके सामान को सुरक्षित पैक कर रहे हैं।
      </p>

      <!-- India Post Tracking Banner -->
      <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 max-w-lg mx-auto text-left flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center flex-shrink-0 text-sm font-bold shadow-sm">
          <i class="fa-solid fa-envelope"></i>
        </div>
        <div class="flex-1 min-w-0 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-bold text-red-950">भारतीय डाक (India Post Speed Post)</span>
            <span class="text-[10px] bg-red-100 text-red-800 px-2 py-0.5 rounded font-bold">Consignment</span>
          </div>
          <p class="font-mono text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($order['tracking_consignment_no']) ?></p>
          <p class="text-[11px] text-gray-500 mt-0.5">पार्सल डिस्पैच होने पर आप indiapost.gov.in पर लाइव ट्रैक कर सकते हैं।</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center justify-center gap-3 mt-6">
        <button onclick="window.print()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-gray-800 text-xs font-bold rounded-xl transition flex items-center gap-2">
          <i class="fa-solid fa-print"></i> रसीद / इनवॉइस प्रिंट करें (Print Invoice)
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/account.php?tab=orders" class="px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition flex items-center gap-2">
            <i class="fa-solid fa-box-archive"></i> मेरे सभी आर्डर देखें
          </a>
        <?php else: ?>
          <a href="/products.php" class="px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl hover:bg-emerald-800 transition flex items-center gap-2">
            <i class="fa-solid fa-store"></i> और खरीदारी करें
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Order Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      
      <!-- Delivery Address -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm text-xs">
        <h3 class="font-bold text-gray-900 pb-2 mb-3 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-location-dot text-brand-green"></i> डिलीवरी का पता
        </h3>
        <p class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($order['customer_name']) ?></p>
        <p class="text-gray-600 leading-relaxed mb-2">
          <?= htmlspecialchars($order['shipping_street']) ?><br>
          <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?><br>
          पिन कोड: <span class="font-bold text-gray-800"><?= htmlspecialchars($order['shipping_pincode']) ?></span>
        </p>
        <p class="text-gray-700"><strong>फोन:</strong> +91 <?= htmlspecialchars($order['customer_phone']) ?></p>
        <?php if ($order['customer_email']): ?>
          <p class="text-gray-500 truncate"><strong>ईमेल:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Payment Summary -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm text-xs">
        <h3 class="font-bold text-gray-900 pb-2 mb-3 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-credit-card text-brand-green"></i> भुगतान विवरण
        </h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-500">भुगतान का तरीका:</span>
            <span class="font-bold text-gray-900 uppercase"><?= htmlspecialchars($order['payment_method']) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">भुगतान स्थिति:</span>
            <span class="font-bold <?= $order['payment_status'] === 'completed' ? 'text-emerald-700' : 'text-amber-700' ?>">
              <?= ucfirst(htmlspecialchars($order['payment_status'])) ?>
            </span>
          </div>
          <?php if (!empty($order['transaction_id'])): ?>
            <div class="flex justify-between">
              <span class="text-gray-500">लेन-देन आईडी:</span>
              <span class="font-mono text-[11px] text-gray-800"><?= htmlspecialchars($order['transaction_id']) ?></span>
            </div>
          <?php endif; ?>
          <div class="flex justify-between">
            <span class="text-gray-500">आर्डर दिनांक:</span>
            <span class="text-gray-800"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
          </div>
        </div>
      </div>

      <!-- Order Status -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm text-xs">
        <h3 class="font-bold text-gray-900 pb-2 mb-3 border-b border-gray-100 flex items-center gap-2">
          <i class="fa-solid fa-truck text-brand-green"></i> डिस्पैच स्थिति
        </h3>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-500">वर्तमान स्थिति:</span>
            <span class="font-bold text-brand-green bg-emerald-50 px-2 py-0.5 rounded">
              <?= htmlspecialchars($order['order_status']) ?>
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">कूरियर / डाक:</span>
            <span class="font-semibold text-gray-800">India Post Express</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">अनुमानित डिलीवरी:</span>
            <span class="font-semibold text-gray-800">4-6 कार्य दिवस</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Purchased Items Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
      <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-bold text-gray-900 text-sm">सामान की सूची (Ordered Items)</h3>
        <span class="text-xs text-gray-500"><?= count($items) ?> वस्तुएं</span>
      </div>

      <div class="divide-y divide-gray-100">
        <?php foreach ($items as $item): ?>
          <div class="p-4 sm:p-5 flex items-center gap-4">
            <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=200&q=80') ?>"
                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                 class="w-16 h-16 object-cover rounded-xl border border-gray-200 bg-stone-100 flex-shrink-0">
            
            <div class="flex-1 min-w-0">
              <h4 class="font-bold text-sm text-gray-900 truncate"><?= htmlspecialchars($item['product_name']) ?></h4>
              <p class="text-xs text-gray-500 mt-0.5">
                कोड: <span class="font-mono"><?= htmlspecialchars($item['product_code']) ?></span>
                <?php if (!empty($item['shop_name'])): ?>
                  • कारीगर: <span class="text-emerald-700 font-medium"><?= htmlspecialchars($item['shop_name']) ?> (<?= htmlspecialchars($item['district']) ?>)</span>
                <?php endif; ?>
              </p>
              <p class="text-xs text-gray-600 mt-1">
                <?= format_price($item['price']) ?> × <?= $item['quantity'] ?>
              </p>
            </div>

            <div class="text-right flex-shrink-0">
              <span class="font-bold text-sm text-gray-900"><?= format_price($item['total']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Financial Calculation Bar -->
      <div class="bg-stone-50 p-5 border-t border-gray-200 text-xs text-gray-700 space-y-2">
        <div class="flex justify-between">
          <span>सामान उप-योग (Subtotal):</span>
          <span class="font-bold text-gray-900"><?= format_price($order['subtotal']) ?></span>
        </div>
        <div class="flex justify-between">
          <span>डाक एवं डिलीवरी प्रभार (Shipping Fee):</span>
          <span class="font-bold <?= $order['shipping_fee'] == 0 ? 'text-emerald-700' : 'text-gray-900' ?>">
            <?= $order['shipping_fee'] == 0 ? 'मुफ़्त (FREE)' : format_price($order['shipping_fee']) ?>
          </span>
        </div>
        <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
          <span>कुल भुगतेय राशि (Total Paid / Payable):</span>
          <span class="text-brand-green"><?= format_price($order['total_amount']) ?></span>
        </div>
      </div>
    </div>

    <!-- Need Help & Kalyani agrofood pvt ltd Support -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm text-center text-xs text-gray-500 space-y-1">
      <p class="font-bold text-gray-800">किसी भी सहायता के लिए संपर्क करें</p>
      <p>कल्याणी एग्रोफूड हेल्पलाइन: <a href="tel:<?= APP_PHONE ?>" class="text-brand-green font-bold"><?= APP_PHONE ?></a> | ईमेल: <a href="mailto:<?= APP_EMAIL ?>" class="text-brand-green font-bold"><?= APP_EMAIL ?></a></p>
      <p>पता: <?= APP_ADDRESS ?></p>
    </div>

  </div>
</div>

<!-- GA4 Purchase Event Tracking -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof gtag === 'function') {
    gtag('event', 'purchase', {
      transaction_id: '<?= htmlspecialchars($order['order_number']) ?>',
      value: <?= (float)$order['total_amount'] ?>,
      currency: 'INR',
      shipping: <?= (float)$order['shipping_fee'] ?>,
      items: [
        <?php foreach ($items as $item): ?>
        {
          item_id: '<?= htmlspecialchars($item['product_code'] ?: ('GGH-' . $item['product_id'])) ?>',
          item_name: '<?= htmlspecialchars(addslashes($item['product_name'])) ?>',
          price: <?= (float)$item['price'] ?>,
          quantity: <?= (int)$item['quantity'] ?>
        },
        <?php endforeach; ?>
      ]
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
