<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$cat_filter = clean_input($_GET['category'] ?? '');
$vendor_filter = intval($_GET['vendor'] ?? 0);
$q = clean_input($_GET['q'] ?? '');

// Toggle Featured (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_featured') {
    $pid = intval($_POST['product_id'] ?? 0);
    $pdo->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?")->execute([$pid]);
    log_audit_action('product_toggle_featured', 'products', 'product', $pid, 'Toggled featured flag');
    set_flash('success', 'उत्पाद फ़ीचर्ड स्थिति बदली गई।');
    header('Location: /admin/products.php');
    exit;
}

// Delete Product (Strictly POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $del_id = intval($_POST['delete_id'] ?? 0);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$del_id]);
    log_audit_action('product_delete', 'products', 'product', $del_id, 'Product deleted by admin');
    set_flash('success', 'उत्पाद सफलतापूर्वक कैटलॉग से हटाया गया।');
    header('Location: /admin/products.php');
    exit;
}

// Reject Insecure GET Mutating Requests
if (isset($_GET['delete_id']) || isset($_GET['toggle_featured'])) {
    http_response_code(405);
    set_flash('error', 'अमान्य अनुरोध विधि (GET अस्वीकृत)। कृपया फ़ॉर्म या बटन का उपयोग करें।');
    header('Location: /admin/products.php');
    exit;
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $pid = intval($_POST['edit_product_id'] ?? 0);
    $name = clean_input($_POST['name'] ?? '');
    $hindi_name = clean_input($_POST['hindi_name'] ?? '');
    $cat_id = intval($_POST['category_id'] ?? 1);
    $vendor_id = intval($_POST['vendor_id'] ?? 1);
    $price = floatval($_POST['price'] ?? 0);
    $orig_price = floatval($_POST['original_price'] ?? $price);
    $stock = intval($_POST['stock'] ?? 50);
    $stock_status = clean_input($_POST['stock_status'] ?? 'In Stock');
    $weight_kg = floatval($_POST['weight_kg'] ?? 0.5);
    $image = clean_input($_POST['image'] ?? '');
    $seo_title = clean_input($_POST['seo_title'] ?? '');
    $seo_description = clean_input($_POST['seo_description'] ?? '');
    $seo_keywords = clean_input($_POST['seo_keywords'] ?? '');

    if ($pid > 0 && !empty($name) && $price > 0) {
        $discount = ($orig_price > $price) ? round((($orig_price - $price) / $orig_price) * 100) : 0;
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, hindi_name = ?, category_id = ?, vendor_id = ?, price = ?, original_price = ?,
                    discount = ?, stock = ?, stock_status = ?, weight_kg = ?, image = ?,
                    seo_title = ?, seo_description = ?, seo_keywords = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $hindi_name, $cat_id, $vendor_id, $price, $orig_price, $discount, $stock, $stock_status, $weight_kg, $image, $seo_title, $seo_description, $seo_keywords, $pid]);
            log_audit_action('admin_edit_product', 'products', "Product #{$pid} edited by admin");
            set_flash('success', 'उत्पाद विवरण सफलतापूर्वक अपडेट किया गया!');
            header('Location: /admin/products.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'त्रुटि: ' . $e->getMessage());
        }
    }
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

  <div class="flex flex-wrap items-center gap-2">
    <a href="/admin/export_products.php" class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5" title="संपूर्ण कैटलॉग CSV डाउनलोड करें">
      <i class="fa-solid fa-file-export text-emerald-600"></i> एक्सपोर्ट कैटलॉग
    </a>
    <button onclick="document.getElementById('importProductsModal').classList.remove('hidden')"
      class="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5" title="CSV फ़ाइल से बल्क उत्पाद अपलोड करें">
      <i class="fa-solid fa-file-import text-blue-600"></i> बल्क इम्पोर्ट (CSV)
    </button>
    <a href="/seller/products.php" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> नया उत्पाद जोड़ें
    </a>
  </div>
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
              <form action="/admin/products.php" method="POST" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_featured">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit"
                  class="p-1 rounded text-base transition cursor-pointer <?= $p['is_featured'] ? 'text-amber-500 hover:text-amber-600' : 'text-gray-300 hover:text-gray-400' ?>"
                  title="फ़ीचर्ड स्थिति बदलें">
                  <i class="fa-solid fa-star"></i>
                </button>
              </form>
            </td>

            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
              <button type="button" onclick='openEditProductModal(<?= json_encode([
                  "id" => $p["id"],
                  "name" => $p["name"],
                  "hindi_name" => $p["hindi_name"] ?? "",
                  "category_id" => $p["category_id"],
                  "vendor_id" => $p["vendor_id"],
                  "price" => $p["price"],
                  "original_price" => $p["original_price"],
                  "stock" => $p["stock"],
                  "stock_status" => $p["stock_status"],
                  "weight_kg" => $p["weight_kg"],
                  "image" => $p["image"],
                  "seo_title" => $p["seo_title"] ?? "",
                  "seo_description" => $p["seo_description"] ?? "",
                  "seo_keywords" => $p["seo_keywords"] ?? ""
              ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                class="p-1.5 text-stone-500 hover:text-indigo-600 rounded hover:bg-indigo-50 transition cursor-pointer" title="संपादित करें">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-gray-700 rounded hover:bg-stone-100 transition" title="देखें">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
              </a>
              <form action="/admin/products.php" method="POST" class="inline" onsubmit="return confirm('क्या आप इस उत्पाद को हटाना चाहते हैं?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition cursor-pointer" title="हटाएं">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Import Products CSV Modal -->
<div id="importProductsModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-md w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-file-import text-blue-600"></i>
        बल्क उत्पाद अपलोड करें (CSV Import)
      </h3>
      <button onclick="document.getElementById('importProductsModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/import_products.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">CSV फ़ाइल चुनें (.csv) <span class="text-red-500">*</span></label>
        <input type="file" name="csv_file" accept=".csv" required
          class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl p-2 bg-stone-50">
        <p class="text-[11px] text-gray-400 mt-1.5">
          सुझाव: पहले <a href="/admin/export_products.php" class="text-indigo-600 font-bold hover:underline">एक्सपोर्ट कैटलॉग</a> करके प्रारूप (format) देख लें।
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('importProductsModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          फ़ाइल प्रोसेस करें (Import)
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
        उत्पाद विवरण संपादित करें (Edit Product)
      </h3>
      <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/admin/products.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="edit_product" value="1">
      <input type="hidden" name="edit_product_id" id="edit_pid" value="">

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">उत्पाद का नाम (English) <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_p_name" required
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">हिंदी नाम (Hindi Name)</label>
        <input type="text" name="hindi_name" id="edit_p_hindi"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी (Category) <span class="text-red-500">*</span></label>
          <select name="category_id" id="edit_p_cat" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">विक्रेता (Vendor) <span class="text-red-500">*</span></label>
          <select name="vendor_id" id="edit_p_vendor" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <?php foreach ($vendors as $v): ?>
              <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?> (<?= htmlspecialchars($v['district']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">विक्रय मूल्य (₹) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="price" id="edit_p_price" required
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">MRP मूल्य (₹)</label>
          <input type="number" step="0.01" name="original_price" id="edit_p_orig_price"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">वजन (kg)</label>
          <input type="number" step="0.01" name="weight_kg" id="edit_p_weight"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">स्टॉक मात्रा (Stock)</label>
          <input type="number" name="stock" id="edit_p_stock" min="0" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">स्टॉक स्थिति</label>
          <select name="stock_status" id="edit_p_stock_status" class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-indigo-600 transition">
            <option value="In Stock">In Stock (उपलब्ध)</option>
            <option value="Out of Stock">Out of Stock (समाप्त)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">चित्र URL (Image URL)</label>
        <input type="url" name="image" id="edit_p_image" placeholder="https://..."
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
      </div>

      <!-- Dedicated Product On-Page SEO Section -->
      <div class="p-4 bg-gradient-to-br from-indigo-50/60 to-purple-50/60 rounded-2xl border border-indigo-200/80 space-y-3">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-black text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-magnifying-glass-chart text-indigo-600"></i>
            उत्पाद ऑन-पेज SEO (Product On-Page SEO Data)
          </h4>
          <span class="text-[10px] font-bold text-indigo-700 bg-white px-2 py-0.5 rounded-full border border-indigo-200">Google SERP Ready</span>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-800 mb-1">SEO Title Tag (सर्च रिजल्ट शीर्षक)</label>
          <input type="text" name="seo_title" id="edit_p_seo_title" maxlength="70" placeholder="उदा. Buy Heavy Forged Kulhadi Online | GaunGaliHat"
            class="w-full px-3.5 py-2 bg-white border border-indigo-200 rounded-xl text-xs focus:outline-none focus:border-indigo-600 transition">
          <p class="text-[10px] text-gray-500 mt-0.5">खाली रहने पर स्वतः '[Product Name] — GaunGaliHat' उपयोग होगा।</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-800 mb-1">SEO Meta Description (गूगल स्निपेट विवरण)</label>
          <textarea name="seo_description" id="edit_p_seo_description" rows="2" maxlength="250" placeholder="गूगल सर्च रिजल्ट में दिखने वाला 140-160 अक्षरों का संक्षिप्त विवरण..."
            class="w-full px-3.5 py-2 bg-white border border-indigo-200 rounded-xl text-xs focus:outline-none focus:border-indigo-600 transition"></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-800 mb-1">SEO Keywords (सर्च कीवर्ड्स, Comma Separated)</label>
          <input type="text" name="seo_keywords" id="edit_p_seo_keywords" placeholder="उदा. sickle, desi hansiya, farm sickle, hand forged tools"
            class="w-full px-3.5 py-2 bg-white border border-indigo-200 rounded-xl text-xs focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
          अपडेट सहेजें (Save Changes)
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditProductModal(p) {
  document.getElementById('edit_pid').value = p.id || '';
  document.getElementById('edit_p_name').value = p.name || '';
  document.getElementById('edit_p_hindi').value = p.hindi_name || '';
  document.getElementById('edit_p_cat').value = p.category_id || 1;
  document.getElementById('edit_p_vendor').value = p.vendor_id || 1;
  document.getElementById('edit_p_price').value = p.price || '';
  document.getElementById('edit_p_orig_price').value = p.original_price || p.price || '';
  document.getElementById('edit_p_weight').value = p.weight_kg || 0.5;
  document.getElementById('edit_p_stock').value = p.stock || 50;
  document.getElementById('edit_p_stock_status').value = p.stock_status || 'In Stock';
  document.getElementById('edit_p_image').value = p.image || '';
  document.getElementById('edit_p_seo_title').value = p.seo_title || '';
  document.getElementById('edit_p_seo_description').value = p.seo_description || '';
  document.getElementById('edit_p_seo_keywords').value = p.seo_keywords || '';
  document.getElementById('editProductModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
