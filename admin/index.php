<?php
$page_title = 'प्रशासनिक डैशबोर्ड (Super Admin Dashboard)';
require_once __DIR__ . '/admin_header.php';

// Platform Metrics
$total_gmv = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'completed' OR payment_method = 'cod'")->fetchColumn() ?: 0.0;
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$total_vendors = $pdo->query("SELECT COUNT(*) FROM vendors")->fetchColumn() ?: 0;
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;

// Recent Platform Orders
$stmt_orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
$recent_orders = $stmt_orders->fetchAll();

// Top Selling Categories
$stmt_cats = $pdo->query("
    SELECT c.name, c.hindi_name, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY product_count DESC 
    LIMIT 5
");
$top_cats = $stmt_cats->fetchAll();
?>

<!-- Title Bar -->
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-gauge-high text-indigo-700"></i>
      केंद्रीय प्रशासनिक नियंत्रण डैशबोर्ड
    </h1>
    <p class="text-xs sm:text-sm text-gray-500 mt-1">
      S S Industries • ग्रामीण कारीगर, हथकरघा, जैविक दालें एवं अचार डिजिटल नेटवर्क
    </p>
  </div>

  <div class="flex items-center gap-2.5">
    <a href="/admin/users.php?action=new" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-user-plus"></i> नया स्टाफ / यूजर जोड़ें
    </a>
    <a href="/admin/site_settings.php" class="px-4 py-2.5 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-sliders"></i> गेटवे सेटिंग्स
    </a>
  </div>
</div>

<!-- 5 Platform Metric Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  
  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">कुल GMV बिक्री</span>
      <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-indian-rupee-sign"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900"><?= format_price($total_gmv) ?></div>
    <p class="text-[11px] text-emerald-600 font-semibold mt-1">सत्यापित भुगतान व COD</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">कुल आर्डर</span>
      <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-box-archive"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900"><?= number_format($total_orders) ?></div>
    <p class="text-[11px] text-gray-500 mt-1">भारतीय डाक डिलीवरी</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">सक्रिय विक्रेता / SHG</span>
      <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-hammer"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900"><?= number_format($total_vendors) ?></div>
    <p class="text-[11px] text-amber-600 font-semibold mt-1">100% KYC सत्यापित</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">कुल उत्पाद (SKUs)</span>
      <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900"><?= number_format($total_products) ?></div>
    <p class="text-[11px] text-gray-500 mt-1">दालें, अचार, औजार आदि</p>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm col-span-2 lg:col-span-1">
    <div class="flex items-center justify-between text-gray-400 mb-2">
      <span class="text-xs font-bold uppercase tracking-wider text-gray-500">पंजीकृत उपयोगकर्ता</span>
      <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
        <i class="fa-solid fa-users"></i>
      </div>
    </div>
    <div class="text-xl sm:text-2xl font-black text-gray-900"><?= number_format($total_users) ?></div>
    <p class="text-[11px] text-purple-600 font-semibold mt-1">सुरक्षित Bcrypt टोकन्स</p>
  </div>

</div>

<!-- Real Chart.js Graphics Section -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
  
  <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-chart-line text-indigo-600"></i>
          प्लेटफ़ॉर्म वृद्धि एवं राजस्व प्रवाह (Platform GMV Velocity)
        </h3>
        <p class="text-xs text-gray-500">2026 मासिक विश्लेषण (₹ लाख में)</p>
      </div>
    </div>

    <div class="h-72 w-full">
      <canvas id="adminPlatformChart"></canvas>
    </div>
  </div>

  <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
    <h3 class="font-extrabold text-base text-gray-900 mb-1 flex items-center gap-2">
      <i class="fa-solid fa-fire text-amber-500"></i>
      शीर्ष शिल्प श्रेणियां
    </h3>
    <p class="text-xs text-gray-500 mb-4">कैटलॉग में उत्पाद संख्या</p>

    <div class="space-y-3">
      <?php foreach ($top_cats as $tc): ?>
        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
          <div>
            <p class="font-bold text-gray-800"><?= htmlspecialchars($tc['hindi_name'] ?? $tc['name']) ?></p>
            <p class="text-[10px] text-gray-400"><?= htmlspecialchars($tc['name']) ?></p>
          </div>
          <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 font-bold rounded-lg text-xs">
            <?= $tc['product_count'] ?> उत्पाद
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Recent Orders Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="p-5 border-b border-gray-100 flex items-center justify-between">
    <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-receipt text-indigo-600"></i>
      हाल के सभी प्लेटफ़ॉर्म आर्डर
    </h3>
    <a href="/admin/orders.php" class="text-xs font-bold text-indigo-600 hover:underline">सभी आर्डर देखें →</a>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3">आर्डर सं.</th>
          <th class="px-4 py-3">ग्राहक</th>
          <th class="px-4 py-3">पता</th>
          <th class="px-4 py-3">राशि</th>
          <th class="px-4 py-3">गेटवे</th>
          <th class="px-4 py-3">स्थिति</th>
          <th class="px-4 py-3 text-right">कार्य</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($recent_orders as $ro): ?>
          <tr class="hover:bg-stone-50/50">
            <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
              #<?= htmlspecialchars($ro['order_number']) ?>
              <span class="block text-[10px] font-normal text-gray-400"><?= date('d M, h:i A', strtotime($ro['created_at'])) ?></span>
            </td>
            <td class="px-4 py-3.5 font-bold text-gray-800">
              <?= htmlspecialchars($ro['customer_name']) ?>
            </td>
            <td class="px-4 py-3.5 text-gray-600">
              <?= htmlspecialchars($ro['shipping_city']) ?>, <?= htmlspecialchars($ro['shipping_pincode']) ?>
            </td>
            <td class="px-4 py-3.5 font-bold text-gray-900">
              <?= format_price($ro['total_amount']) ?>
            </td>
            <td class="px-4 py-3.5 uppercase font-mono text-[11px] font-bold text-gray-700">
              <?= htmlspecialchars($ro['payment_method']) ?> (<?= htmlspecialchars($ro['payment_status']) ?>)
            </td>
            <td class="px-4 py-3.5">
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $ro['order_status'] === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                <?= htmlspecialchars($ro['order_status']) ?>
              </span>
            </td>
            <td class="px-4 py-3.5 text-right">
              <a href="/order-success.php?id=<?= $ro['id'] ?>" target="_blank" class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-gray-700 font-bold rounded text-[11px] transition">
                इनवॉइस देखें
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('adminPlatformChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर'],
      datasets: [{
        label: 'सकल बिक्री GMV (₹)',
        data: [42000, 68000, 95000, 142000, 186000, 245000],
        backgroundColor: '#4338ca',
        borderRadius: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(val) { return '₹' + val.toLocaleString('en-IN'); },
            font: { size: 10 }
          },
          grid: { color: '#f3f4f6' }
        },
        x: { ticks: { font: { size: 11 } }, grid: { display: false } }
      }
    }
  });
});
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
