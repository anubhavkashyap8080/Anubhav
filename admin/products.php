<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$cat_filter = clean_input($_GET['category'] ?? '');
$vendor_filter = intval($_GET['vendor'] ?? 0);
$q = clean_input($_GET['q'] ?? '');

// Toggle Featured
if (isset($_GET['toggle_featured'])) {
    $pid = intval($_GET['toggle_featured']);
    $pdo->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?")->execute([$pid]);
    set_flash('success', 'उत्पाद फ़ीचर्ड स्थिति बदली गई।');
    header('Location: /admin/products.php');
    exit;
}

// Delete Product
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$del_id]);
    set_flash('success', 'उत्पाद सफलतापूर्वक कैटलॉग से हटाया गया।');
    header('Location: /admin/products.php');
    exit;
}

// Fetch categories & vendors for dropdowns
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$vendors = $pdo->query("SELECT id, name, business_name, district FROM vendors ORDER BY name ASC")->fetchAll();

// Query products
$sql = "
    SELECT p.*, c.name as cat_name, c.hindi_name as cat_hindi, v.business_name, v.name as vendor_name, v.district
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE 1=1
";
$params = [];

if (!empty($cat_filter)) {
    $sql .= " AND (c.slug = ? OR p.category_id = ?)";
    $params[] = $cat_filter;
    $params[] = intval($cat_filter);
}
if ($vendor_filter) {
    $sql .= " AND p.vendor_id = ?";
    $params[] = $vendor_filter;
}
if (!empty($q)) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.hindi_name LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'कैटलॉग एवं उत्पाद प्रशासन (Master Products Catalog)';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Title & Filter Controls -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
      <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
      केंद्रीय उत्पाद कैटलॉग (Global Catalog)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      कुल <?= count($products) ?> उत्पाद • विक्रेता मैपिंग, स्टॉक व मूल्य नियंत्रण
    </p>
  </div>

  <a href="/seller/products.php?action=new" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> नया उत्पाद जोड़ें
  </a>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-3">
  <form method="GET" action="/admin/products.php" class="flex flex-wrap items-center gap-2">
    <!-- Category -->
    <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs font-semibold focus:outline-none">
      <option value="">सभी श्रेणियां</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= htmlspecialchars($c['slug']) ?>" <?= $cat_filter === $c['slug'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['hindi_name'] ?? $c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Vendor -->
    <select name="vendor" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs font-semibold focus:outline-none">
      <option value="">सभी कारीगर / विक्रेता</option>
      <?php foreach ($vendors as $v): ?>
        <option value="<?= $v['id'] ?>" <?= $vendor_filter === $v['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($v['business_name'] ?? $v['name']) ?> (<?= htmlspecialchars($v['district']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <!-- Search -->
  <form method="GET" action="/admin/products.php" class="w-full sm:w-64">
    <div class="relative">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="SKU या नाम से खोजें..."
        class="w-full pl-8 pr-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:outline-none transition">
      <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400 text-xs">
        <i class="fa-solid fa-magnifying-glass"></i>
      </span>
    </div>
  </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">चित्र</th>
          <th class="px-4 py-3.5">उत्पाद विवरण</th>
          <th class="px-4 py-3.5">कारीगर / विक्रेता</th>
          <th class="px-4 py-3.5">श्रेणी</th>
          <th class="px-4 py-3.5">मूल्य</th>
          <th class="px-4 py-3.5">स्टॉक</th>
          <th class="px-4 py-3.5">फ़ीचर्ड</th>
          <th class="px-4 py-3.5 text-right">कार्य</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($products as $p): ?>
          <tr class="hover:bg-stone-50/50 transition">
            <td class="px-4 py-3">
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                class="w-12 h-12 rounded-xl object-cover border border-gray-200 bg-stone-100">
            </td>

            <td class="px-4 py-3">
              <div class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($p['name']) ?></div>
              <div class="text-[11px] text-gray-500"><?= htmlspecialchars($p['hindi_name'] ?? '') ?></div>
              <span class="font-mono text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.2 rounded font-semibold">
                <?= htmlspecialchars($p['code']) ?>
              </span>
            </td>

            <td class="px-4 py-3">
              <div class="font-bold text-gray-800"><?= htmlspecialchars($p['business_name'] ?? $p['vendor_name'] ?? 'N/A') ?></div>
              <div class="text-[10px] text-gray-400"><?= htmlspecialchars($p['district'] ?? 'Hardoi') ?></div>
            </td>

            <td class="px-4 py-3 text-gray-600">
              <span class="bg-stone-100 text-stone-700 px-2 py-0.5 rounded text-[11px] font-medium">
                <?= htmlspecialchars($p['cat_hindi'] ?? $p['cat_name'] ?? 'General') ?>
              </span>
            </td>

            <td class="px-4 py-3">
              <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
              <?php if ($p['original_price'] > $p['price']): ?>
                <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
              <?php endif; ?>
            </td>

            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $p['stock_status'] === 'In Stock' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                <?= $p['stock'] ?> (<?= htmlspecialchars($p['stock_status']) ?>)
              </span>
            </td>

            <td class="px-4 py-3">
              <a href="/admin/products.php?toggle_featured=<?= $p['id'] ?>"
                class="p-1 rounded text-base transition <?= $p['is_featured'] ? 'text-amber-500 hover:text-amber-600' : 'text-gray-300 hover:text-gray-400' ?>"
                title="फ़ीचर्ड स्थिति बदलें">
                <i class="fa-solid fa-star"></i>
              </a>
            </td>

            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
              <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-gray-700 rounded hover:bg-stone-100 transition" title="देखें">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
              </a>
              <a href="/admin/products.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('क्या आप इस उत्पाद को हटाना चाहते हैं?')"
                class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="हटाएं">
                <i class="fa-regular fa-trash-can"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
