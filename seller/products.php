<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_vendor();

$user = get_logged_in_user();
$vendor = get_vendor_for_current_user();
if (!$vendor) {
    $stmt = $pdo->query("SELECT * FROM vendors ORDER BY id ASC LIMIT 1");
    $vendor = $stmt->fetch();
}
$vendor_id = $vendor['id'];

$action = clean_input($_GET['action'] ?? 'list');
$selected_category = clean_input($_GET['category'] ?? '');
$search_query = clean_input($_GET['q'] ?? '');

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

// Handle ADD Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    csrf_protect();
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
    $code = 'GGH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name) . 'PRD', 0, 3)) . '-' . rand(100, 999);
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
        log_audit_action('seller_product_created', 'products', "Vendor #{$vendor_id} added product {$name} ({$code})");
        set_flash('success', 'नया उत्पाद "' . htmlspecialchars($name) . '" सफलतापूर्वक कैटलॉग में जोड़ा गया!');
        header('Location: /seller/products.php');
        exit;
    } catch (Exception $e) {
        set_flash('error', 'उत्पाद जोड़ने में त्रुटि: ' . $e->getMessage());
    }
}

// Handle EDIT Product (Scoped strictly to current vendor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    csrf_protect();
    $pid = intval($_POST['edit_product_id'] ?? 0);
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

    if ($pid > 0 && !empty($name) && $price > 0) {
        $discount = ($original_price > $price) ? round((($original_price - $price) / $original_price) * 100) : 0;
        try {
            $sql_up = "UPDATE products SET name = ?, hindi_name = ?, category_id = ?, price = ?, original_price = ?, discount = ?, weight_kg = ?, stock = ?, unit = ?, description = ?";
            $params = [$name, $hindi_name, $category_id, $price, $original_price, $discount, $weight_kg, $stock, $unit, $description];
            if (!empty($image_url)) {
                $sql_up .= ", image = ?";
                $params[] = $image_url;
            }
            $sql_up .= " WHERE id = ? AND vendor_id = ?";
            $params[] = $pid;
            $params[] = $vendor_id;

            $stmt = $pdo->prepare($sql_up);
            $stmt->execute($params);
            log_audit_action('seller_product_updated', 'products', "Vendor #{$vendor_id} updated product #{$pid} ({$name})");
            set_flash('success', 'उत्पाद "' . htmlspecialchars($name) . '" सफलतापूर्वक अपडेट किया गया!');
            header('Location: /seller/products.php');
            exit;
        } catch (Exception $e) {
            set_flash('error', 'उत्पाद अपडेट करने में त्रुटि: ' . $e->getMessage());
        }
    }
}

// Handle Bulk CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_import_csv'])) {
    csrf_protect();
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'कृपया एक मान्य CSV फ़ाइल अपलोड करें।');
    } else {
        $file_path = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file_path, 'r');
        if ($handle !== false) {
            $imported_count = 0;
            $row_idx = 0;
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                $row_idx++;
                if ($row_idx === 1) continue; // Skip header
                if (empty($data[0])) continue;

                $p_name = trim($data[0]);
                $p_hindi = trim($data[1] ?? '');
                $p_cat_id = intval($data[2] ?? 1);
                $p_price = floatval($data[3] ?? 0);
                $p_orig_price = floatval($data[4] ?? $p_price);
                $p_weight = floatval($data[5] ?? 0.5);
                $p_stock = intval($data[6] ?? 50);
                $p_unit = trim($data[7] ?? 'piece');
                $p_img = trim($data[8] ?? 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80');
                $p_desc = trim($data[9] ?? '');

                if ($p_price <= 0) continue;

                $p_disc = ($p_orig_price > $p_price) ? round((($p_orig_price - $p_price) / $p_orig_price) * 100) : 0;
                $p_code = 'GGH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $p_name) . 'CSV', 0, 3)) . '-' . rand(100, 999);
                $p_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $p_name)) . '-' . rand(100, 999);

                try {
                    $stmt_ins = $pdo->prepare("
                        INSERT INTO products (
                            code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
                            discount, weight_kg, stock, stock_status, rating, review_count, image, unit, description
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', 5.0, 1, ?, ?, ?)
                    ");
                    $stmt_ins->execute([
                        $p_code, $vendor_id, $p_cat_id, $p_name, $p_hindi, $p_slug, $p_price, $p_orig_price,
                        $p_disc, $p_weight, $p_stock, $p_img, $p_unit, $p_desc
                    ]);
                    $imported_count++;
                } catch (Exception $e) {}
            }
            fclose($handle);
            log_audit_action('seller_bulk_import', 'products', "Vendor #{$vendor_id} imported {$imported_count} products via CSV");
            set_flash('success', "सफलतापूर्वक {$imported_count} उत्पाद बल्क में कैटलॉग में जोड़े गए!");
            header('Location: /seller/products.php');
            exit;
        }
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
        log_audit_action('seller_stock_toggle', 'products', "Vendor #{$vendor_id} toggled product #{$prod_id} to {$new_status}");
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
    log_audit_action('seller_product_deleted', 'products', "Vendor #{$vendor_id} deleted product #{$del_id}");
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

// Handle Products CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="vendor_catalog_' . $vendor_id . '_' . date('Y_m_d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Product ID', 'SKU Code', 'Name', 'Hindi Name', 'Category', 'Price (INR)', 'MRP (INR)', 'Discount %', 'Stock', 'Stock Status', 'Weight (KG)', 'Unit']);
    foreach ($products as $p) {
        fputcsv($out, [
            $p['id'],
            $p['code'],
            $p['name'],
            $p['hindi_name'] ?? '',
            $p['category_name'] ?? '',
            $p['price'],
            $p['original_price'],
            $p['discount'],
            $p['stock'],
            $p['stock_status'],
            $p['weight_kg'],
            $p['unit']
        ]);
    }
    fclose($out);
    exit;
}

$page_title = 'उत्पाद सूची एवं कैटलॉग (Product Catalog)';
require_once __DIR__ . '/seller_header.php';
?>

<!-- Title & Controls Bar -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-2">
      <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
      Product Catalog Management (उत्पाद कैटलॉग प्रबंधन)
    </h1>
    <p class="text-xs text-gray-500 mt-1">
      कुल <?= count($products) ?> उत्पाद सूचीबद्ध • श्रेणीवार स्टॉक व मूल्य नियंत्रण
    </p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <a href="/seller/products.php?export=csv"
      class="px-3.5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-file-csv text-emerald-600"></i> Export CSV (एक्सपोर्ट)
    </a>
    <button onclick="document.getElementById('importCsvModal').classList.remove('hidden')"
      class="px-3.5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl border border-stone-300 transition flex items-center gap-1.5 shadow-xs">
      <i class="fa-solid fa-file-import text-blue-600"></i> Import CSV (बल्क आयात)
    </button>
    <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
      class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
      <i class="fa-solid fa-plus"></i> + Add New Product (+ नया उत्पाद)
    </button>
  </div>
</div>

<!-- Category Filter Tabs & Search Bar -->
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
  
  <!-- Category Selector -->
  <form method="GET" action="/seller/products.php" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
    <label class="text-xs font-bold text-gray-600">Category (श्रेणी):</label>
    <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 bg-stone-50 border border-gray-200 rounded-lg text-xs font-semibold focus:outline-none">
      <option value="">All Categories (सभी श्रेणियां)</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $selected_category === $cat['slug'] ? 'selected' : '' ?>>
          <?= htmlspecialchars(fix_utf8_mojibake($cat['hindi_name'] ?? $cat['name'])) ?>
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
      <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search by name or code (नाम या कोड से खोजें)..."
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
          <th class="px-4 py-3.5">Image (चित्र)</th>
          <th class="px-4 py-3.5">Product Details (उत्पाद विवरण)</th>
          <th class="px-4 py-3.5">Category (श्रेणी)</th>
          <th class="px-4 py-3.5">Price (मूल्य)</th>
          <th class="px-4 py-3.5">Weight (भार)</th>
          <th class="px-4 py-3.5">Stock Status (स्टॉक स्थिति)</th>
          <th class="px-4 py-3.5 text-right">Actions (कार्य)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
              <i class="fa-solid fa-box-open text-3xl mb-2 text-stone-300 block"></i>
              No products found. Add a new product using the button above. (कोई उत्पाद नहीं मिला)
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
                  <div class="text-[11px] text-gray-500 font-medium"><?= htmlspecialchars(fix_utf8_mojibake($p['hindi_name'])) ?></div>
                <?php endif; ?>
                <span class="font-mono text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded font-semibold">
                  <?= htmlspecialchars($p['code']) ?>
                </span>
              </td>

              <!-- Category -->
              <td class="px-4 py-3 text-gray-600">
                <span class="inline-block bg-stone-100 text-stone-700 px-2 py-0.5 rounded text-[11px] font-medium">
                  <?= htmlspecialchars(fix_utf8_mojibake($p['category_hindi'] ?? $p['category_name'] ?? 'General')) ?>
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
                <button type="button" onclick='openSellerEditProductModal(<?= json_encode([
                    "id" => $p["id"],
                    "name" => $p["name"],
                    "hindi_name" => $p["hindi_name"] ?? "",
                    "category_id" => $p["category_id"],
                    "price" => $p["price"],
                    "original_price" => $p["original_price"],
                    "weight_kg" => $p["weight_kg"],
                    "stock" => $p["stock"],
                    "unit" => $p["unit"] ?? "piece",
                    "image" => $p["image"],
                    "description" => $p["description"] ?? ""
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                  class="p-1.5 text-stone-500 hover:text-amber-600 rounded hover:bg-amber-50 transition cursor-pointer" title="संपादित करें">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
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
        Add New Artisan Product (नया उत्पाद जोड़ें)
      </h3>
      <button onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/seller/products.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
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
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(fix_utf8_mojibake($cat['hindi_name'] ?? $cat['name'])) ?></option>
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

<!-- Bulk CSV Import Modal -->
<div id="importCsvModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-lg w-full p-6 sm:p-8">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-file-import text-blue-600"></i>
        बल्क उत्पाद आयात (Bulk CSV Import)
      </h3>
      <button onclick="document.getElementById('importCsvModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/seller/products.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="bulk_import_csv" value="1">

      <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 space-y-1">
        <p class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> CSV फॉर्मेट निर्देश:</p>
        <p class="text-[11px] text-blue-800 leading-relaxed">
          कॉलम्स क्रम: <strong>Name, Hindi Name, Category ID, Price, Original Price, Weight (Kg), Stock, Unit, Image URL, Description</strong>
        </p>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">CSV फ़ाइल चुनें (.csv) <span class="text-red-500">*</span></label>
        <input type="file" name="csv_file" accept=".csv" required
          class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:outline-none">
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('importCsvModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-200 transition">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5">
          <i class="fa-solid fa-cloud-arrow-up"></i> अपलोड एवं आयात करें
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<div id="sellerEditProductModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
  <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square text-amber-600"></i>
        उत्पाद संपादित करें (Edit Product)
      </h3>
      <button type="button" onclick="document.getElementById('sellerEditProductModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="/seller/products.php" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="edit_product" value="1">
      <input type="hidden" name="edit_product_id" id="s_edit_pid" value="">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">उत्पाद का नाम (English) <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="s_edit_name" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">हिंदी नाम (Hindi Name)</label>
          <input type="text" name="hindi_name" id="s_edit_hindi"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">श्रेणी (Category) <span class="text-red-500">*</span></label>
          <select name="category_id" id="s_edit_cat" required class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-amber-500 transition">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(fix_utf8_mojibake($cat['hindi_name'] ?? $cat['name'])) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">इकाई (Unit)</label>
          <input type="text" name="unit" id="s_edit_unit" value="piece"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">विक्रय मूल्य (₹) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="price" id="s_edit_price" required
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">MRP मूल्य (₹)</label>
          <input type="number" step="0.01" name="original_price" id="s_edit_orig_price"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">वजन (kg)</label>
          <input type="number" step="0.01" name="weight_kg" id="s_edit_weight"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">स्टॉक मात्रा (Stock Units) <span class="text-red-500">*</span></label>
          <input type="number" name="stock" id="s_edit_stock" min="0" required
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">चित्र URL (Image URL)</label>
          <input type="url" name="image_url" id="s_edit_img" placeholder="https://..."
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">विवरण (Description)</label>
        <textarea name="description" id="s_edit_desc" rows="3"
          class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button type="button" onclick="document.getElementById('sellerEditProductModal').classList.add('hidden')"
          class="px-4 py-2 bg-stone-100 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-200 transition">
          रद्द करें
        </button>
        <button type="submit"
          class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition">
          अपडेट सहेजें (Save Changes)
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openSellerEditProductModal(p) {
  document.getElementById('s_edit_pid').value = p.id || '';
  document.getElementById('s_edit_name').value = p.name || '';
  document.getElementById('s_edit_hindi').value = p.hindi_name || '';
  document.getElementById('s_edit_cat').value = p.category_id || 1;
  document.getElementById('s_edit_unit').value = p.unit || 'piece';
  document.getElementById('s_edit_price').value = p.price || '';
  document.getElementById('s_edit_orig_price').value = p.original_price || p.price || '';
  document.getElementById('s_edit_weight').value = p.weight_kg || 0.5;
  document.getElementById('s_edit_stock').value = p.stock || 50;
  document.getElementById('s_edit_img').value = p.image || '';
  document.getElementById('s_edit_desc').value = p.description || '';
  document.getElementById('sellerEditProductModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/seller_footer.php'; ?>
