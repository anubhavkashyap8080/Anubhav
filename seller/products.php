<?php
$page_title = 'उत्पाद सूची एवं कैटलॉग (Product Catalog)';
require_once __DIR__ . '/seller_header.php';

$vendor_id = $vendor['id'];
$action = clean_input($_GET['action'] ?? 'list');
$selected_category = clean_input($_GET['category'] ?? '');
$search_query = clean_input($_GET['q'] ?? '');

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

// Handle ADD Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = clean_input($_POST['name'] ?? '');
    $hindi_name = clean_input($_POST['hindi_name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $original_price = floatval($_POST['original_price'] ?? $price);
    $weight_kg = floatval($_POST['weight_kg'] ?? 0.5);
    $stock = intval($_POST['stock'] ?? 50);
    $unit = clean_input($_POST['unit'] ?? 'piece');
    $image_url = clean_input($_POST['image_url'] ?? '');
    $description = clean_input($_POST['description'] ?? '');

    if (empty($image_url)) {
        $image_url = 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80';
    }

    $discount = ($original_price > $price) ? round((($original_price - $price) / $original_price) * 100) : 0;
    $code = 'GGH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3)) . '-' . rand(100, 999);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . rand(100, 999);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (
                code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
                discount, weight_kg, stock, stock_status, rating, review_count, image, unit, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', 5.0, 1, ?, ?, ?)
        ");
        $stmt->execute([
            $code, $vendor_id, $category_id, $name, $hindi_name, $slug, $price, $original_price,
            $discount, $weight_kg, $stock, $image_url, $unit, $description
        ]);
        set_flash('success', 'नया उत्पाद "' . htmlspecialchars($name) . '" सफलतापूर्वक कैटलॉग में जोड़ा गया!');
        header('Location: /seller/products.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'उत्पाद जोड़ने में त्रुटि: ' . $e->getMessage());
    }
}

// Handle Quick Stock Toggle
if (isset($_GET['toggle_stock'])) {
    $prod_id = intval($_GET['toggle_stock']);
    $stmt = $pdo->prepare("SELECT stock_status FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$prod_id, $vendor_id]);
    $curr = $stmt->fetchColumn();
    if ($curr) {
        $new_status = ($curr === 'In Stock') ? 'Out of Stock' : 'In Stock';
        $pdo->prepare("UPDATE products SET stock_status = ? WHERE id = ? AND vendor_id = ?")->execute([$new_status, $prod_id, $vendor_id]);
        set_flash('success', 'उत्पाद स्टॉक स्थिति अपडेट की गई।');
    }
    header('Location: /seller/products.php');
    exit;
}

// Handle Delete Product
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$del_id, $vendor_id]);
    set_flash('success', 'उत्पाद कैटलॉग से हटा दिया गया।');
    header('Location: /seller/products.php');
    exit;
}

// Query Vendor Products
$sql = "SELECT p.*, c.name as category_name, c.hindi_name as category_hindi 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.vendor_id = ?";
$params = [$vendor_id];

if (!empty($selected_category)) {
    $sql .= " AND (c.slug = ? OR p.category_id = ?)";
    $params[] = $selected_category;
    $params[] = intval($selected_category);
}

if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.hindi_name LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Title & Controls Bar -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-2">
      <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
      उत्पाद कैटलॉग प्रबंधन (Product Catalog)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      कुल <?= count($products) ?> उत्पाद सूचीबद्ध • श्रेणीवार स्टॉक व मूल्य नियंत्रण
    </p>
  </div>

  <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
    class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="fa-solid fa-plus"></i> नया उत्पाद जोड़ें (Add New Product)
  </button>
</div>

<!-- Category Filter Tabs & Search Bar -->
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
  
  <!-- Category Selector -->
  <form method="GET" action="/seller/products.php" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
    <label class="text-xs font-bold text-gray-600">श्रेणी चुनें:</label>
    <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs font-semibold focus:outline-none">
      <option value="">सभी श्रेणियां (All Categories)</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $selected_category === $cat['slug'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['hindi_name'] ?? $cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <!-- Live Search within Vendor Catalog -->
  <form method="GET" action="/seller/products.php" class="w-full sm:w-72">
    <?php if ($selected_category): ?>
      <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
    <?php endif; ?>
    <div class="relative">
      <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="नाम या कोड से खोजें..."
        class="w-full pl-9 pr-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
        <i class="fa-solid fa-magnifying-glass"></i>
      </span>
    </div>
  </form>

</div>

<!-- Products Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
        <tr>
          <th class="px-4 py-3.5">चित्र</th>
          <th class="px-4 py-3.5">उत्पाद विवरण</th>
          <th class="px-4 py-3.5">श्रेणी (Category)</th>
          <th class="px-4 py-3.5">मूल्य (Price)</th>
          <th class="px-4 py-3.5">भार (Weight)</th>
          <th class="px-4 py-3.5">स्टॉक स्थिति</th>
          <th class="px-4 py-3.5 text-right">कार्य (Actions)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
              <i class="fa-solid fa-box-open text-3xl mb-2 text-stone-300 block"></i>
              कोई उत्पाद नहीं मिला। ऊपर दिए गए बटन से नया उत्पाद जोड़ें।
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr class="hover:bg-stone-50/50 transition">
              <!-- Image -->
              <td class="px-4 py-3">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                  class="w-12 h-12 rounded-xl object-cover border border-gray-200 bg-stone-100">
              </td>

              <!-- Details -->
              <td class="px-4 py-3">
                <div class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($p['name']) ?></div>
                <?php if ($p['hindi_name']): ?>
                  <div class="text-[11px] text-gray-500"><?= htmlspecialchars($p['hindi_name']) ?></div>
                <?php endif; ?>
                <span class="font-mono text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded font-semibold">
                  <?= htmlspecialchars($p['code']) ?>
                </span>
              </td>

              <!-- Category -->
              <td class="px-4 py-3 text-gray-600">
                <span class="inline-block bg-stone-100 text-stone-700 px-2 py-0.5 rounded text-[11px] font-medium">
                  <?= htmlspecialchars($p['category_hindi'] ?? $p['category_name'] ?? 'General') ?>
                </span>
              </td>

              <!-- Price & MRP -->
              <td class="px-4 py-3">
                <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
                <?php if ($p['original_price'] > $p['price']): ?>
                  <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Weight -->
              <td class="px-4 py-3 font-medium text-gray-600">
                <?= number_format($p['weight_kg'], 2) ?> kg
              </td>

              <!-- Stock Toggle -->
              <td class="px-4 py-3">
                <a href="/seller/products.php?toggle_stock=<?= $p['id'] ?>"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition <?= $p['stock_status'] === 'In Stock' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' ?>"
                  title="क्लिक करके स्टॉक स्थिति बदलें">
                  <span class="w-1.5 h-1.5 rounded-full <?= $p['stock_status'] === 'In Stock' ? 'bg-emerald-600' : 'bg-red-600' ?>"></span>
                  <?= htmlspecialchars($p['stock_status']) ?> (<?= $p['stock'] ?>)
                </a>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="p-1.5 text-stone-400 hover:text-stone-700 rounded hover:bg-stone-100 transition" title="लाइव उत्पाद देखें">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
                <a href="/seller/products.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('क्या आप इस उत्पाद को हटाना चाहते हैं?')"
                  class="p-1.5 text-stone-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="हटाएं">
                  <i class="fa-regular fa-trash-can"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-plus-circle text-amber-600"></i>
        नया ग्रामीण शिल्प उत्पाद जोड़ें (Add Product)
      </h3>
      <button onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/seller/products.php" method="POST" class="space-y-4">
      <input type="hidden" name="add_product" value="1">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">उत्पाद का नाम (English) <span class="text-red-500">*</span></label>
          <input type="text" name="name" required placeholder="उदा. Hand-Forged Desi Khurpi"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">हिंदी नाम (Hindi Title)</label>
          <input type="text" name="hindi_name" placeholder="उदा. हाथ से बनी मजबूत खुरपी"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी (Category) <span class="text-red-500">*</span></label>
          <select name="category_id" required class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['hindi_name'] ?? $cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">इकाई (Unit)</label>
          <input type="text" name="unit" value="piece" placeholder="उदा. piece, 1 kg jar, 5 kg bag"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">बिक्री मूल्य (Selling Price ₹) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="price" required placeholder="उदा. 240.00"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">मूल्य एमआरपी (MRP ₹)</label>
          <input type="number" step="0.01" name="original_price" placeholder="उदा. 320.00"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कुल भार (Weight in Kg - सटीक शिपिंग हेतु) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="weight_kg" required value="0.50"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">प्रारंभिक स्टॉक संख्या (Initial Stock) <span class="text-red-500">*</span></label>
          <input type="number" name="stock" required value="50"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">चित्र URL (Image URL)</label>
          <input type="url" name="image_url" placeholder="https://..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">उत्पाद विवरण (Description)</label>
          <textarea name="description" rows="3" placeholder="कारीगर की हस्तकला, धातु का प्रकार, शुद्धता..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition"></textarea>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-200 transition">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition">
          उत्पाद प्रकाशित करें (Publish SKU)
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
