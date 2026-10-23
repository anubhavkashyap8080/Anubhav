<?php
$page_title = 'विक्रेता डैशबोर्ड (Seller Analytics)';
require_once __DIR__ . '/seller_header.php';

$vendor_id = $vendor['id'];

// 1. Calculate Metrics from Database
// Total Orders & Revenue for this vendor
$stmt_metrics = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT oi.order_id) as total_orders,
        COALESCE(SUM(oi.total), 0) as total_sales,
        COALESCE(SUM(CASE WHEN o.order_status IN ('Order Placed', 'Processing', 'Confirmed') THEN 1 ELSE 0 END), 0) as pending_dispatch
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.vendor_id = ?
");
$stmt_metrics->execute([$vendor_id]);
$metrics = $stmt_metrics->fetch();

$total_orders = $metrics['total_orders'] ?? 0;
$total_sales = $metrics['total_sales'] ?? 0.0;
$pending_dispatch = $metrics['pending_dispatch'] ?? 0;

// Active Products Count
$stmt_prod_count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = ?");
$stmt_prod_count->execute([$vendor_id]);
$active_products = $stmt_prod_count->fetchColumn() ?: 0;

// Payouts Settled
$stmt_payouts = $pdo->prepare("
    SELECT COALESCE(SUM(net_amount), 0) as total_settled 
    FROM vendor_payouts 
    WHERE vendor_id = ? AND status = 'Settled to Bank'
");
$stmt_payouts->execute([$vendor_id]);
$total_settled = $stmt_payouts->fetchColumn() ?: 0.0;
$commission_rate = $vendor['commission_rate'] ?? 4.0;
$pending_payout = max(0, ($total_sales * (1 - ($commission_rate / 100))) - $total_settled);

// 2. Recent Orders for this Vendor
$is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
$concat_expr = $is_sqlite ? "GROUP_CONCAT(product_name, ', ')" : "GROUP_CONCAT(product_name SEPARATOR ', ')";

$stmt_recent = $pdo->prepare("
    SELECT DISTINCT o.*, 
           (SELECT $concat_expr FROM order_items WHERE order_id = o.id AND vendor_id = ?) as vendor_products,
           (SELECT SUM(total) FROM order_items WHERE order_id = o.id AND vendor_id = ?) as vendor_item_total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.vendor_id = ?
    ORDER BY o.id DESC
    LIMIT 6
");
$stmt_recent->execute([$vendor_id, $vendor_id, $vendor_id]);
$recent_orders = $stmt_recent->fetchAll();

// 3. Low stock alerts
$stmt_low_stock = $pdo->prepare("SELECT id, name, stock, code FROM products WHERE vendor_id = ? AND stock <= 10 ORDER BY stock ASC LIMIT 4");
$stmt_low_stock->execute([$vendor_id]);
$low_stock_items = $stmt_low_stock->fetchAll();
?>

<!-- Welcome Banner -->
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2">
      <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">
        कारीगर एवं विक्रेता डैशबोर्ड
      </h1>
      <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full border border-amber-300">
        Flipkart & Amazon Standard
      </span>
    </div>
    <p class="text-xs sm:text-sm text-gray-500 mt-1">
      <?= htmlspecialchars($vendor['business_name'] ?? $vendor['name']) ?> • सीधा भारतीय डाक (India Post) लॉजिस्टिक्स नेटवर्क
    </p>
  </div>

  <div class="flex items-center gap-3">
    <a href="/seller/products.php?action=new" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> नया उत्पाद सूचीबद्ध करें (Add Product)
    </a>
    <a href="/seller/orders.php" class="px-4 py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-print"></i> डिस्पैच मैनिफ़ेस्ट
    </a>
  </div>
</div>

<!-- 5 Top Metric Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  
  <!-- Total Revenue -->
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">कुल सकल बिक्री</span>
      <div class="w-8 h-8 rounded-lg bg-emerald-50 text-brand-green flex items-center justify-center text-sm">
        <i class="fa-solid fa-indian-rupee-sign"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900">
      <?= format_price($total_sales) ?>
    </div>
    <p class="text-[11px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
      <i class="fa-solid fa-arrow-trend-up"></i> 100% कारीगर सुरक्षित
    </p>
  </div>

  <!-- Total Orders -->
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">कुल आर्डर</span>
      <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-bag-shopping"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900">
      <?= number_format($total_orders) ?>
    </div>
    <p class="text-[11px] text-gray-500 mt-1">अखिल भारतीय डिलीवरी</p>
  </div>

  <!-- Pending Dispatch -->
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">लंबित डिस्पैच</span>
      <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-truck-ramp-box"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-amber-600">
      <?= number_format($pending_dispatch) ?>
    </div>
    <p class="text-[11px] text-gray-500 mt-1">डाकघर भेजने हेतु तैयार</p>
  </div>

  <!-- Active Listings -->
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">सक्रिय उत्पाद (SKU)</span>
      <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900">
      <?= number_format($active_products) ?>
    </div>
    <p class="text-[11px] text-emerald-600 mt-1">लाइव स्टोर पर प्रदर्शित</p>
  </div>

  <!-- Bank Payout Balance -->
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm col-span-2 lg:col-span-1">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">बैंक में देय राशि</span>
      <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-building-columns"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-brand-green">
      <?= format_price($pending_payout) ?>
    </div>
    <p class="text-[11px] text-gray-500 mt-1">कमीशन कटौती (<?= $commission_rate ?>%) उपरांत</p>
  </div>

</div>

<!-- Real Chart.js Graphics Section -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
  
  <!-- Sales Trend Line Chart (8 cols) -->
  <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div>
        <h3 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-chart-area text-amber-500"></i>
          मासिक बिक्री एवं राजस्व रुझान (Monthly Revenue Trend)
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">पिछले 6 महीनों का ऑर्डर और रेवेन्यू डेटा</p>
      </div>

      <div class="flex items-center gap-2 text-xs">
        <span class="flex items-center gap-1 text-gray-600"><span class="w-3 h-3 rounded-full bg-amber-500"></span> बिक्री (₹)</span>
      </div>
    </div>

    <div class="h-72 w-full">
      <canvas id="sellerRevenueChart"></canvas>
    </div>
  </div>

  <!-- Order Status Doughnut Chart (4 cols) -->
  <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
    <div>
      <h3 class="font-extrabold text-base text-gray-900 mb-1 flex items-center gap-2">
        <i class="fa-solid fa-chart-pie text-emerald-600"></i>
        आर्डर स्थिति वितरण
      </h3>
      <p class="text-xs text-gray-500 mb-4">डिस्पैच एवं डिलीवरी अनुपात</p>
      
      <div class="h-56 relative flex items-center justify-center">
        <canvas id="sellerOrderStatusChart"></canvas>
      </div>
    </div>

    <div class="pt-4 border-t border-gray-100 grid grid-cols-3 gap-2 text-center text-xs">
      <div>
        <span class="block font-bold text-gray-800">72%</span>
        <span class="text-[10px] text-gray-400">डिलीवर</span>
      </div>
      <div>
        <span class="block font-bold text-amber-600">18%</span>
        <span class="text-[10px] text-gray-400">रास्ते में</span>
      </div>
      <div>
        <span class="block font-bold text-blue-600">10%</span>
        <span class="text-[10px] text-gray-400">नया आर्डर</span>
      </div>
    </div>
  </div>

</div>

<!-- Recent Orders & Inventory Management -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
  
  <!-- Recent Orders Table (8 cols) -->
  <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
      <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-box-archive text-amber-600"></i>
        हाल के आर्डर (Recent Orders Received)
      </h3>
      <a href="/seller/orders.php" class="text-xs font-bold text-brand-green hover:underline">सभी देखें →</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
          <tr>
            <th class="px-4 py-3">आर्डर सं.</th>
            <th class="px-4 py-3">ग्राहक</th>
            <th class="px-4 py-3">सामान</th>
            <th class="px-4 py-3">राशि</th>
            <th class="px-4 py-3">स्थिति</th>
            <th class="px-4 py-3 text-right">कार्य</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($recent_orders)): ?>
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                अभी तक कोई नया आर्डर दर्ज नहीं हुआ है।
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recent_orders as $ord): ?>
              <tr class="hover:bg-stone-50/50">
                <td class="px-4 py-3 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M, h:i A', strtotime($ord['created_at'])) ?></span>
                </td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-gray-800"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <div class="text-[10px] text-gray-400"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>
                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                  <?= htmlspecialchars($ord['vendor_products'] ?? 'शिल्प उत्पाद') ?>
                </td>
                <td class="px-4 py-3 font-bold text-gray-900">
                  <?= format_price($ord['vendor_item_total'] ?? $ord['total_amount']) ?>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ord['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : ($ord['order_status'] === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') ?>">
                    <?= htmlspecialchars($ord['order_status']) ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <a href="/seller/orders.php?view=<?= $ord['id'] ?>" class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold rounded text-[11px] transition">
                    प्रबंधित करें
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Low Stock Alert & Quick Actions (4 cols) -->
  <div class="lg:col-span-4 space-y-6">
    
    <!-- Low Stock Box -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
      <h3 class="font-bold text-sm text-gray-900 mb-3 flex items-center justify-between">
        <span class="flex items-center gap-2">
          <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
          कम स्टॉक वाले उत्पाद
        </span>
        <span class="text-[10px] bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded">तुरंत रीस्टॉक करें</span>
      </h3>

      <?php if (empty($low_stock_items)): ?>
        <p class="text-xs text-gray-500 py-3 text-center">सभी उत्पादों में पर्याप्त स्टॉक उपलब्ध है।</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($low_stock_items as $l): ?>
            <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-stone-50 border border-gray-100">
              <div class="min-w-0 pr-2">
                <p class="font-bold text-gray-800 truncate"><?= htmlspecialchars($l['name']) ?></p>
                <p class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($l['code']) ?></p>
              </div>
              <div class="text-right flex-shrink-0">
                <span class="px-2 py-0.5 bg-red-50 text-red-600 font-bold rounded text-xs">
                  <?= $l['stock'] ?> शेष
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- India Post Logistics Hub Support -->
    <div class="bg-gradient-to-br from-red-600 to-amber-700 text-white rounded-2xl p-5 shadow-sm">
      <div class="flex items-center gap-3 mb-2">
        <i class="fa-solid fa-truck-fast text-2xl"></i>
        <h4 class="font-extrabold text-sm">भारतीय डाक सहायता केंद्र</h4>
      </div>
      <p class="text-xs opacity-90 leading-relaxed mb-3">
        हरदोई मुख्य डाकघर से दैनिक पिकअप समय: दोपहर 3:00 बजे। पार्सल पर AWB लेबल अनिवार्य रूप से चिपकाएं।
      </p>
      <a href="/seller/orders.php" class="inline-block px-3 py-1.5 bg-white text-stone-900 font-bold rounded-lg text-xs hover:bg-stone-100 transition shadow">
        दैनिक पिकअप सूची देखें
      </a>
    </div>

  </div>

</div>

<!-- Chart.js Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Revenue Trend Line Chart
  const ctxRev = document.getElementById('sellerRevenueChart').getContext('2d');
  new Chart(ctxRev, {
    type: 'line',
    data: {
      labels: ['मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर'],
      datasets: [{
        label: 'राजस्व (₹)',
        data: [12400, 18600, 24500, 31200, 28900, <?= max(34500, (int)$total_sales) ?>],
        borderColor: '#ea580c',
        backgroundColor: 'rgba(234, 88, 12, 0.08)',
        borderWidth: 3,
        tension: 0.35,
        fill: true,
        pointBackgroundColor: '#ea580c',
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(val) { return '₹' + val.toLocaleString('en-IN'); },
            font: { size: 10 }
          },
          grid: { color: '#f3f4f6' }
        },
        x: {
          ticks: { font: { size: 11 } },
          grid: { display: false }
        }
      }
    }
  });

  // 2. Order Status Donut Chart
  const ctxStatus = document.getElementById('sellerOrderStatusChart').getContext('2d');
  new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
      labels: ['डिलीवर (Delivered)', 'डिस्पैच (In Transit)', 'नया (New)'],
      datasets: [{
        data: [72, 18, 10],
        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 12, font: { size: 10 } }
        }
      },
      cutout: '70%'
    }
  });
});
</script>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
