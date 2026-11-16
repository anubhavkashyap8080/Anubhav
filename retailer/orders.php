<?php
/**
 * GanvGaliHat Retailer Hub — B2B Wholesale Orders & GST Tax Invoices
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_retailer();

$page_title = 'थोक ऑर्डर्स एवं GST इनवॉइस (B2B Orders & Tax Invoices)';
require_once __DIR__ . '/retailer_header.php';

$uid = $user['id'];
$view_order_id = intval($_GET['order_id'] ?? 0);
$just_placed = isset($_GET['placed']);

// Fetch All B2B Orders for Retailer
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? AND order_type = 'wholesale_b2b' 
    ORDER BY id DESC
");
$stmt->execute([$uid]);
$all_orders = $stmt->fetchAll();

// If viewing a specific order
$selected_order = null;
$selected_items = [];
if ($view_order_id > 0) {
    $stmt_single = $pdo->prepare("
        SELECT * FROM orders 
        WHERE id = ? AND user_id = ? AND order_type = 'wholesale_b2b'
        LIMIT 1
    ");
    $stmt_single->execute([$view_order_id, $uid]);
    $selected_order = $stmt_single->fetch();

    if ($selected_order) {
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.image, p.bulk_packaging_desc, p.code as pcode
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$view_order_id]);
        $selected_items = $stmt_items->fetchAll();
    }
}
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-truck-ramp-box text-amber-600"></i>
      <span class="lang-en">B2B Wholesale Orders & Tax Invoices</span><span class="lang-hi hidden">थोक ऑर्डर्स एवं इनवॉइस</span>
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      <span class="lang-en">Track wholesale shipments, cargo logistics status, and download formal GST tax invoices</span><span class="lang-hi hidden">आर्डर ट्रैकिंग, फ्रेट स्थिति एवं GST इनवॉइस डाउनलोड करें</span>
    </p>
  </div>

  <a href="/retailer/products.php" class="px-4 py-2 bg-stone-900 hover:bg-black text-amber-400 font-bold text-xs rounded-xl shadow transition flex items-center gap-2 self-start sm:self-auto">
    <i class="fa-solid fa-boxes-stacked"></i> <span class="lang-en">Create New Bulk Order</span><span class="lang-hi hidden">नया थोक ऑर्डर लगाएं</span>
  </a>
</div>

<!-- Order Placed Success Banner -->
<?php if ($just_placed && $selected_order): ?>
  <div class="mb-6 p-6 rounded-3xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-100"><span class="lang-en">Order Placed Successfully</span><span class="lang-hi hidden">ऑर्डर सफलतापूर्वक दर्ज हुआ</span></span>
        <h2 class="text-xl font-black">आर्डर #<?= htmlspecialchars($selected_order['order_number']) ?></h2>
        <p class="text-xs text-emerald-100 mt-0.5">
          <span class="lang-en">Total:</span><span class="lang-hi hidden">कुल:</span> <?= format_price($selected_order['total_amount'] ?? $selected_order['total']) ?> <span class="lang-en">• Production and cargo packing dispatched to rural artisan workshops.</span><span class="lang-hi hidden">• कारीगर वर्कशॉप को उत्पादन निर्देश भेज दिए गए हैं।</span>
        </p>
      </div>
    </div>
    <button onclick="document.getElementById('taxInvoiceModal').classList.remove('hidden')"
      class="px-5 py-2.5 bg-white text-emerald-800 font-black text-xs rounded-xl shadow hover:bg-emerald-50 transition shrink-0 flex items-center gap-2">
      <i class="fa-solid fa-file-invoice"></i> <span class="lang-en">View GST Tax Invoice</span><span class="lang-hi hidden">GST टैक्स इनवॉइस देखें</span>
    </button>
  </div>
<?php endif; ?>

<!-- If an order is selected, show details -->
<?php if ($selected_order): ?>
  <?php 
    $st = $selected_order['order_status'] ?? $selected_order['status'] ?? 'Confirmed';
    $tot = floatval($selected_order['total_amount'] ?? $selected_order['total'] ?? 0);
    $sub = floatval($selected_order['subtotal'] ?? ($tot / 1.05));
    $tax = round($tot - $sub, 2);
  ?>
  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
      <div>
        <div class="flex items-center gap-2">
          <span class="bg-amber-100 text-amber-800 text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase">
            B2B Wholesale Order
          </span>
          <span class="text-xs text-gray-400">दिनांक: <?= date('d M Y, h:i A', strtotime($selected_order['created_at'])) ?></span>
        </div>
        <h2 class="text-xl font-black text-gray-900 mt-1">
          आर्डर विवरण #<?= htmlspecialchars($selected_order['order_number']) ?>
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <button onclick="document.getElementById('taxInvoiceModal').classList.remove('hidden')"
          class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5">
          <i class="fa-solid fa-file-invoice text-amber-600"></i> GST इनवॉइस
        </button>
        <a href="/retailer/orders.php" class="px-3 py-2 bg-stone-50 hover:bg-stone-100 text-stone-500 text-xs rounded-xl">
          बंद करें &times;
        </a>
      </div>
    </div>

    <!-- Status Tracker Bar -->
    <div class="py-6 border-b border-gray-100">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
        <div class="p-3 rounded-2xl <?= in_array($st, ['Confirmed', 'In Production', 'Dispatched', 'Delivered']) ? 'bg-amber-50 text-amber-900 border border-amber-200 font-bold' : 'bg-stone-50 text-stone-400' ?>">
          <i class="fa-solid fa-file-circle-check text-sm block mb-1"></i>
          <span class="lang-en">1. Order Confirmed</span><span class="lang-hi hidden">1. आर्डर स्वीकृत</span>
        </div>
        <div class="p-3 rounded-2xl <?= in_array($st, ['In Production', 'Packing', 'Dispatched', 'Delivered']) ? 'bg-amber-50 text-amber-900 border border-amber-200 font-bold' : 'bg-stone-50 text-stone-400' ?>">
          <i class="fa-solid fa-boxes-packing text-sm block mb-1"></i>
          <span class="lang-en">2. Cluster Packing</span><span class="lang-hi hidden">2. क्लस्टर पैकिंग</span>
        </div>
        <div class="p-3 rounded-2xl <?= in_array($st, ['Dispatched', 'Delivered']) ? 'bg-amber-50 text-amber-900 border border-amber-200 font-bold' : 'bg-stone-50 text-stone-400' ?>">
          <i class="fa-solid fa-truck-fast text-sm block mb-1"></i>
          <span class="lang-en">3. Cargo Dispatched</span><span class="lang-hi hidden">3. ट्रांसपोर्ट डिस्पैच</span>
        </div>
        <div class="p-3 rounded-2xl <?= $st === 'Delivered' ? 'bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold' : 'bg-stone-50 text-stone-400' ?>">
          <i class="fa-solid fa-circle-check text-sm block mb-1"></i>
          <span class="lang-en">4. Doorstep Delivered</span><span class="lang-hi hidden">4. दुकान पर डिलीवरी</span>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between text-xs text-stone-500 bg-stone-50 p-3 rounded-2xl">
        <div>
          <span><span class="lang-en">Logistics Carrier: </span><span class="lang-hi hidden">लॉजिस्टिक्स: </span></span>
          <strong class="text-stone-800"><?= htmlspecialchars($selected_order['tracking_carrier'] ?? 'Rural Cargo Freight') ?></strong>
        </div>
        <div>
          <span><span class="lang-en">Bilty / Docket No: </span><span class="lang-hi hidden">बिल्टी सं.: </span></span>
          <strong class="font-mono text-amber-700 font-bold"><?= htmlspecialchars($selected_order['tracking_consignment_no'] ?? 'Pending') ?></strong>
        </div>
        <div>
          <span><span class="lang-en">Payment Mode: </span><span class="lang-hi hidden">भुगतान प्रकार: </span></span>
          <strong class="uppercase text-stone-800"><?= htmlspecialchars($selected_order['payment_method']) ?></strong>
        </div>
      </div>
    </div>

    <!-- If Bank Transfer, show official company bank account -->
    <?php if ($selected_order['payment_method'] === 'bank_transfer'): ?>
      <div class="my-6 p-5 rounded-2xl bg-amber-50 border border-amber-200 text-xs">
        <h4 class="font-black text-amber-950 flex items-center gap-2 mb-2">
          <i class="fa-solid fa-building-columns text-amber-600"></i>
          <span class="lang-en">Official Company Settlement Account (NEFT / RTGS / IMPS)</span><span class="lang-hi hidden">कंपनी आधिकारिक बैंक खाता</span>
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-stone-700">
          <div>
            <span class="text-stone-400 text-[10px] block"><span class="lang-en">Beneficiary Name:</span><span class="lang-hi hidden">खाता धारक:</span></span>
            <strong class="text-gray-900"><?= htmlspecialchars(get_company_name()) ?></strong>
          </div>
          <div>
            <span class="text-stone-400 text-[10px] block"><span class="lang-en">Bank Name:</span><span class="lang-hi hidden">बैंक का नाम:</span></span>
            <strong class="text-gray-900">State Bank of India (SBI)</strong>
          </div>
          <div>
            <span class="text-stone-400 text-[10px] block"><span class="lang-en">Current Account No:</span><span class="lang-hi hidden">खाता संख्या:</span></span>
            <strong class="font-mono text-gray-900">394820194820</strong>
          </div>
          <div>
            <span class="text-stone-400 text-[10px] block">IFSC कोड:</span>
            <strong class="font-mono text-gray-900">SBIN0001234</strong>
          </div>
        </div>
        <p class="text-[11px] text-amber-800 mt-2">
          भुगतान के उपरांत UTR / ट्रांजैक्शन रसीद व्हाट्सएप नंबर <strong>+91 94540 50000</strong> पर साझा करें।
        </p>
      </div>
    <?php endif; ?>

    <!-- Items Table -->
    <div class="mt-6">
      <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider mb-3">आर्डर किए गए उत्पाद (Ordered Items)</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 text-stone-600 font-bold border-b border-stone-200">
            <tr>
              <th class="px-4 py-3">उत्पाद</th>
              <th class="px-4 py-3">पैकेजिंग</th>
              <th class="px-4 py-3">थोक दर</th>
              <th class="px-4 py-3">मात्रा</th>
              <th class="px-4 py-3 text-right">कुल राशि</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($selected_items as $it): ?>
              <tr>
                <td class="px-4 py-3 flex items-center gap-3">
                  <img src="<?= htmlspecialchars($it['image'] ?? '/assets/placeholder.jpg') ?>" class="w-10 h-10 rounded-xl object-cover bg-stone-100 border border-stone-200 shrink-0">
                  <div>
                    <div class="font-bold text-gray-900"><?= htmlspecialchars($it['product_name']) ?></div>
                    <div class="font-mono text-[10px] text-gray-400"><?= htmlspecialchars($it['product_code'] ?? $it['pcode'] ?? '') ?></div>
                  </div>
                </td>
                <td class="px-4 py-3 text-gray-500">
                  <?= htmlspecialchars($it['bulk_packaging_desc'] ?? 'Master Box') ?>
                </td>
                <td class="px-4 py-3 font-semibold text-gray-900">
                  <?= format_price($it['price']) ?>
                </td>
                <td class="px-4 py-3 font-black text-gray-900">
                  <?= $it['quantity'] ?> पीस
                </td>
                <td class="px-4 py-3 text-right font-black text-gray-900">
                  <?= format_price($it['total']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="border-t border-gray-200 bg-stone-50 font-bold">
            <tr>
              <td colspan="4" class="px-4 py-2.5 text-right text-gray-600">थोक माल मूल्य (Taxable Amount):</td>
              <td class="px-4 py-2.5 text-right font-bold text-gray-900"><?= format_price($sub) ?></td>
            </tr>
            <tr>
              <td colspan="4" class="px-4 py-2 text-right text-gray-600">GST कर (5% Input Tax Credit):</td>
              <td class="px-4 py-2 text-right font-bold text-gray-900">+<?= format_price($tax) ?></td>
            </tr>
            <tr>
              <td colspan="4" class="px-4 py-2 text-right text-gray-600">थोक फ्रेट / डिलीवरी:</td>
              <td class="px-4 py-2 text-right font-bold text-emerald-600">
                <?= ($selected_order['shipping_fee'] > 0) ? format_price($selected_order['shipping_fee']) : 'निःशुल्क (Free)' ?>
              </td>
            </tr>
            <tr class="text-sm">
              <td colspan="4" class="px-4 py-3 text-right font-black text-gray-900">कुल देय राशि (Grand Total):</td>
              <td class="px-4 py-3 text-right font-black text-amber-700"><?= format_price($tot) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <!-- GST Tax Invoice Modal (Printable) -->
  <div id="taxInvoiceModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 my-8">
      
      <!-- Modal Top Controls -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200 no-print">
        <div class="text-xs font-bold text-gray-500">
          <i class="fa-solid fa-file-invoice text-amber-600 mr-1"></i> GST B2B Tax Invoice
        </div>
        <div class="flex items-center gap-2">
          <button onclick="window.print()" class="px-4 py-2 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i> प्रिंट / PDF सेव करें
          </button>
          <button onclick="document.getElementById('taxInvoiceModal').classList.add('hidden')" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl">
            बंद करें
          </button>
        </div>
      </div>

      <!-- Printable Invoice Sheet -->
      <div class="pt-6 space-y-6 text-gray-900" id="printableInvoice">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b pb-4">
          <div>
            <h1 class="text-xl font-black text-stone-900">TAX INVOICE</h1>
            <p class="text-[11px] text-gray-500">ORIGINAL FOR RECIPIENT (B2B SUPPLY)</p>
            <div class="mt-2 text-xs">
              <strong class="text-sm block">GanvGaliHat Rural Commerce Private Limited</strong>
              <p class="text-gray-600">Sandila Industrial Corridor, Hardoi, Uttar Pradesh - 241126</p>
              <p class="font-mono text-gray-700"><strong>GSTIN:</strong> 09AAACG1234F1Z8 | <strong>State Code:</strong> 09 (UP)</p>
            </div>
          </div>
          <div class="text-right text-xs">
            <div class="font-mono font-bold text-stone-900 text-sm">Invoice #: INV-<?= substr($selected_order['order_number'], 4) ?></div>
            <div class="text-gray-500">Order #: <?= htmlspecialchars($selected_order['order_number']) ?></div>
            <div class="text-gray-500">Date: <?= date('d M Y', strtotime($selected_order['created_at'])) ?></div>
          </div>
        </div>

        <!-- Buyer & Consignee Details -->
        <div class="grid grid-cols-2 gap-4 text-xs bg-stone-50 p-4 rounded-2xl border border-stone-200">
          <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Billed To (Retailer / Buyer):</span>
            <strong class="text-sm text-gray-900 block"><?= htmlspecialchars($selected_order['business_name'] ?? $user['business_name'] ?? $user['name']) ?></strong>
            <p class="text-gray-600"><?= htmlspecialchars($selected_order['shipping_street']) ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($selected_order['shipping_city']) ?>, <?= htmlspecialchars($selected_order['shipping_state']) ?> - <?= htmlspecialchars($selected_order['shipping_pincode']) ?></p>
            <p class="font-mono text-emerald-800 font-bold mt-1">
              <strong>GSTIN:</strong> <?= htmlspecialchars($selected_order['gstin'] ?: 'Unregistered Retailer') ?>
            </p>
          </div>
          <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Shipped Destination (Godown):</span>
            <p class="text-gray-700 font-medium"><?= htmlspecialchars($selected_order['shipping_street']) ?></p>
            <p class="text-gray-700"><?= htmlspecialchars($selected_order['shipping_city']) ?>, <?= htmlspecialchars($selected_order['shipping_state']) ?></p>
            <p class="text-gray-600 mt-1">Contact: <?= htmlspecialchars($selected_order['customer_name']) ?> (+91 <?= htmlspecialchars($selected_order['customer_phone']) ?>)</p>
            <p class="text-gray-600">LR / Consignment: <?= htmlspecialchars($selected_order['tracking_consignment_no'] ?? 'N/A') ?></p>
          </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs border border-gray-200">
          <thead class="bg-stone-100 text-stone-700 font-bold border-b border-gray-200">
            <tr>
              <th class="p-2.5">#</th>
              <th class="p-2.5">Description of Goods</th>
              <th class="p-2.5">HSN/SAC</th>
              <th class="p-2.5">Qty</th>
              <th class="p-2.5">Rate (₹)</th>
              <th class="p-2.5">Taxable (₹)</th>
              <th class="p-2.5">GST (5%)</th>
              <th class="p-2.5 text-right">Total (₹)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php $i = 1; foreach ($selected_items as $item): ?>
              <?php 
                $item_tot = floatval($item['total']);
                $item_tax = round($item_tot * 0.05 / 1.05, 2);
                $item_taxable = round($item_tot - $item_tax, 2);
              ?>
              <tr>
                <td class="p-2.5"><?= $i++ ?></td>
                <td class="p-2.5 font-bold"><?= htmlspecialchars($item['product_name']) ?></td>
                <td class="p-2.5 font-mono text-gray-500">8201 / 6302</td>
                <td class="p-2.5 font-bold"><?= $item['quantity'] ?></td>
                <td class="p-2.5 font-mono"><?= number_format($item['price'], 2) ?></td>
                <td class="p-2.5 font-mono"><?= number_format($item_taxable, 2) ?></td>
                <td class="p-2.5 font-mono"><?= number_format($item_tax, 2) ?></td>
                <td class="p-2.5 text-right font-bold font-mono"><?= number_format($item_tot, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="bg-stone-50 font-bold border-t border-gray-200">
            <tr>
              <td colspan="5" class="p-2.5 text-right">Taxable Subtotal:</td>
              <td colspan="3" class="p-2.5 text-right font-mono"><?= number_format($sub, 2) ?></td>
            </tr>
            <tr>
              <td colspan="5" class="p-2.5 text-right">CGST (2.5%) + SGST (2.5%):</td>
              <td colspan="3" class="p-2.5 text-right font-mono"><?= number_format($tax, 2) ?></td>
            </tr>
            <tr class="text-sm">
              <td colspan="5" class="p-2.5 text-right font-black">Invoice Total (INR):</td>
              <td colspan="3" class="p-2.5 text-right font-black font-mono text-stone-900">₹<?= number_format($tot, 2) ?></td>
            </tr>
          </tfoot>
        </table>

        <!-- Signatory & Terms -->
        <div class="flex items-end justify-between pt-4 border-t text-[11px] text-gray-500">
          <div>
            <p>1. Certified that the particulars given above are true and correct.</p>
            <p>2. Subject to Hardoi / Uttar Pradesh Jurisdiction.</p>
            <p>3. This is a computer-generated tax invoice for B2B supply.</p>
          </div>
          <div class="text-right">
            <p class="font-bold text-gray-800">For <?= htmlspecialchars(get_company_name()) ?></p>
            <div class="h-10"></div>
            <p class="text-[10px] text-gray-400">Authorized Signatory</p>
          </div>
        </div>

      </div>

    </div>
  </div>
<?php endif; ?>

<!-- All Orders Table -->
<div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100">
    <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
      <i class="fa-solid fa-list-check text-amber-600"></i>
      <span class="lang-en">All B2B Wholesale Orders</span><span class="lang-hi hidden">सभी B2B ऑर्डर्स</span>
    </h2>
  </div>

  <?php if (empty($all_orders)): ?>
    <div class="p-10 text-center">
      <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3">
        <i class="fa-solid fa-boxes-packing"></i>
      </div>
      <p class="text-xs font-bold text-gray-700"><span class="lang-en">No wholesale orders found yet.</span><span class="lang-hi hidden">कोई थोक आर्डर नहीं मिला।</span></p>
      <a href="/retailer/products.php" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition">
        <span class="lang-en">Browse Wholesale Catalog &rarr;</span><span class="lang-hi hidden">थोक कैटलॉग ब्राउज़ करें &rarr;</span>
      </a>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-stone-50 text-stone-600 font-bold border-b border-stone-200">
          <tr>
            <th class="px-4 py-3.5">Order #</th>
            <th class="px-4 py-3.5">Date</th>
            <th class="px-4 py-3.5">Shop / Business</th>
            <th class="px-4 py-3.5">Destination</th>
            <th class="px-4 py-3.5">Amount (₹)</th>
            <th class="px-4 py-3.5">Payment</th>
            <th class="px-4 py-3.5">Status</th>
            <th class="px-4 py-3.5 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($all_orders as $ord): ?>
            <?php $st = $ord['order_status'] ?? $ord['status'] ?? 'Confirmed'; ?>
            <tr class="hover:bg-amber-50/30 transition <?= ($view_order_id === intval($ord['id'])) ? 'bg-amber-50/60 font-semibold' : '' ?>">
              <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
                #<?= htmlspecialchars($ord['order_number'] ?? $ord['id']) ?>
              </td>
              <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap">
                <?= date('d M Y', strtotime($ord['created_at'])) ?>
              </td>
              <td class="px-4 py-3.5 font-bold text-gray-800">
                <?= htmlspecialchars($ord['business_name'] ?? $user['business_name'] ?? 'Retailer') ?>
              </td>
              <td class="px-4 py-3.5 text-gray-600">
                <?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_state']) ?>
              </td>
              <td class="px-4 py-3.5 font-black text-gray-900">
                <?= format_price($ord['total_amount'] ?? $ord['total']) ?>
              </td>
              <td class="px-4 py-3.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-100 uppercase">
                  <?= htmlspecialchars($ord['payment_method']) ?>
                </span>
              </td>
              <td class="px-4 py-3.5">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $st === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                  <?= htmlspecialchars($st) ?>
                </span>
              </td>
              <td class="px-4 py-3.5 text-right space-x-1 whitespace-nowrap">
                <a href="/retailer/orders.php?order_id=<?= $ord['id'] ?>"
                  class="px-3 py-1.5 bg-stone-900 hover:bg-black text-amber-400 font-bold rounded-xl text-[11px] transition inline-flex items-center gap-1">
                  <span class="lang-en">Details & Invoice</span><span class="lang-hi hidden">विवरण & इनवॉइस</span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/retailer_footer.php'; ?>
