with open('seller/products.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Add Product POST handler
target_add = """        $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
        $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
        $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
        $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);

        $stmt = $pdo->prepare("
            INSERT INTO products (
                code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
                discount, weight_kg, length_cm, width_cm, height_cm, volumetric_weight,
                stock, stock_status, rating, review_count, image, unit, description,
                seo_title, seo_description, seo_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', 5.0, 1, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $code, $vendor_id, $category_id, $name, $hindi_name, $slug, $price, $original_price,
            $discount, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight,
            $stock, $image_url, $unit, $description,
            $seo_title, $seo_description, $seo_keywords
        ]);"""

new_add = """        $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
        $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
        $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
        $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);

        $bulk_available = isset($_POST['bulk_available']) ? 1 : 0;
        $bulk_moq = max(1, intval($_POST['bulk_moq'] ?? 10));
        $bulk_price = (!empty($_POST['bulk_price']) && floatval($_POST['bulk_price']) > 0) ? floatval($_POST['bulk_price']) : null;
        $bulk_tier2_qty = (!empty($_POST['bulk_tier2_qty']) && intval($_POST['bulk_tier2_qty']) > 0) ? intval($_POST['bulk_tier2_qty']) : null;
        $bulk_tier2_price = (!empty($_POST['bulk_tier2_price']) && floatval($_POST['bulk_tier2_price']) > 0) ? floatval($_POST['bulk_tier2_price']) : null;
        $bulk_packaging_desc = clean_input($_POST['bulk_packaging_desc'] ?? '');
        $bulk_lead_time_days = max(1, intval($_POST['bulk_lead_time_days'] ?? 3));

        $stmt = $pdo->prepare("
            INSERT INTO products (
                code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
                discount, weight_kg, length_cm, width_cm, height_cm, volumetric_weight,
                stock, stock_status, rating, review_count, image, unit, description,
                seo_title, seo_description, seo_keywords,
                bulk_available, bulk_moq, bulk_price, bulk_tier2_qty, bulk_tier2_price, bulk_packaging_desc, bulk_lead_time_days
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', 5.0, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $code, $vendor_id, $category_id, $name, $hindi_name, $slug, $price, $original_price,
            $discount, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight,
            $stock, $image_url, $unit, $description,
            $seo_title, $seo_description, $seo_keywords,
            $bulk_available, $bulk_moq, $bulk_price, $bulk_tier2_qty, $bulk_tier2_price, $bulk_packaging_desc, $bulk_lead_time_days
        ]);"""

assert target_add in content, "target_add not found in seller/products.php"
content = content.replace(target_add, new_add, 1)

# 2. Update Edit Product POST handler
target_edit = """            $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
            $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
            $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
            $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);

            $sql_up = "UPDATE products SET name = ?, hindi_name = ?, category_id = ?, price = ?, original_price = ?, discount = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, stock = ?, unit = ?, description = ?, seo_title = ?, seo_description = ?, seo_keywords = ?";
            $params = [$name, $hindi_name, $category_id, $price, $original_price, $discount, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $stock, $unit, $description, $seo_title, $seo_description, $seo_keywords];"""

new_edit = """            $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
            $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
            $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
            $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);

            $bulk_available = isset($_POST['bulk_available']) ? 1 : 0;
            $bulk_moq = max(1, intval($_POST['bulk_moq'] ?? 10));
            $bulk_price = (!empty($_POST['bulk_price']) && floatval($_POST['bulk_price']) > 0) ? floatval($_POST['bulk_price']) : null;
            $bulk_tier2_qty = (!empty($_POST['bulk_tier2_qty']) && intval($_POST['bulk_tier2_qty']) > 0) ? intval($_POST['bulk_tier2_qty']) : null;
            $bulk_tier2_price = (!empty($_POST['bulk_tier2_price']) && floatval($_POST['bulk_tier2_price']) > 0) ? floatval($_POST['bulk_tier2_price']) : null;
            $bulk_packaging_desc = clean_input($_POST['bulk_packaging_desc'] ?? '');
            $bulk_lead_time_days = max(1, intval($_POST['bulk_lead_time_days'] ?? 3));

            $sql_up = "UPDATE products SET name = ?, hindi_name = ?, category_id = ?, price = ?, original_price = ?, discount = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, stock = ?, unit = ?, description = ?, seo_title = ?, seo_description = ?, seo_keywords = ?, bulk_available = ?, bulk_moq = ?, bulk_price = ?, bulk_tier2_qty = ?, bulk_tier2_price = ?, bulk_packaging_desc = ?, bulk_lead_time_days = ?";
            $params = [$name, $hindi_name, $category_id, $price, $original_price, $discount, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $stock, $unit, $description, $seo_title, $seo_description, $seo_keywords, $bulk_available, $bulk_moq, $bulk_price, $bulk_tier2_qty, $bulk_tier2_price, $bulk_packaging_desc, $bulk_lead_time_days];"""

assert target_edit in content, "target_edit not found in seller/products.php"
content = content.replace(target_edit, new_edit, 1)

# 3. Add bulk filter to product query
target_query = """if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.hindi_name LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}"""

new_query = """if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.hindi_name LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$bulk_filter = clean_input($_GET['bulk'] ?? '');
if ($bulk_filter === '1') {
    $sql .= " AND p.bulk_available = 1";
} elseif ($bulk_filter === '0') {
    $sql .= " AND (p.bulk_available = 0 OR p.bulk_available IS NULL)";
}"""

assert target_query in content, "target_query not found in seller/products.php"
content = content.replace(target_query, new_query, 1)

# 4. Add Wholesale Badge in the Price table column
target_price_col = """              <!-- Price & MRP -->
              <td class="px-4 py-3">
                <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
                <?php if ($p['original_price'] > $p['price']): ?>
                  <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
                <?php endif; ?>
              </td>"""

new_price_col = """              <!-- Price & MRP -->
              <td class="px-4 py-3">
                <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
                <?php if ($p['original_price'] > $p['price']): ?>
                  <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
                <?php endif; ?>
                <?php if (!empty($p['bulk_available']) && $p['bulk_available'] == 1): ?>
                  <div class="mt-1">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Wholesale MOQ: <?= (int)($p['bulk_moq'] ?? 10) ?> units">
                      <i class="fa-solid fa-boxes-packing text-[9px]"></i>
                      Bulk: <?= format_price($p['bulk_price'] ?: $p['price'] * 0.75) ?> (MOQ: <?= (int)($p['bulk_moq'] ?? 10) ?>)
                    </span>
                  </div>
                <?php endif; ?>
              </td>"""

assert target_price_col in content, "target_price_col not found in seller/products.php"
content = content.replace(target_price_col, new_price_col, 1)

# 5. Add bulk fields to openSellerEditProductModal JSON serialization in table row
target_json = """                <button type="button" onclick='openSellerEditProductModal(<?= json_encode([
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
                    "description" => $p["description"] ?? "",
                    "seo_title" => $p["seo_title"] ?? "",
                    "seo_description" => $p["seo_description"] ?? "",
                    "seo_keywords" => $p["seo_keywords"] ?? ""
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'"""

new_json = """                <button type="button" onclick='openSellerEditProductModal(<?= json_encode([
                    "id" => $p["id"],
                    "name" => $p["name"],
                    "hindi_name" => $p["hindi_name"] ?? "",
                    "category_id" => $p["category_id"],
                    "price" => $p["price"],
                    "original_price" => $p["original_price"],
                    "weight_kg" => $p["weight_kg"],
                    "length_cm" => $p["length_cm"] ?? 10.0,
                    "width_cm" => $p["width_cm"] ?? 10.0,
                    "height_cm" => $p["height_cm"] ?? 10.0,
                    "bulk_available" => $p["bulk_available"] ?? 0,
                    "bulk_moq" => $p["bulk_moq"] ?? 10,
                    "bulk_price" => $p["bulk_price"] ?? "",
                    "bulk_tier2_qty" => $p["bulk_tier2_qty"] ?? "",
                    "bulk_tier2_price" => $p["bulk_tier2_price"] ?? "",
                    "bulk_packaging_desc" => $p["bulk_packaging_desc"] ?? "",
                    "bulk_lead_time_days" => $p["bulk_lead_time_days"] ?? 3,
                    "stock" => $p["stock"],
                    "unit" => $p["unit"] ?? "piece",
                    "image" => $p["image"],
                    "description" => $p["description"] ?? "",
                    "seo_title" => $p["seo_title"] ?? "",
                    "seo_description" => $p["seo_description"] ?? "",
                    "seo_keywords" => $p["seo_keywords"] ?? ""
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'"""

assert target_json in content, "target_json not found in seller/products.php"
content = content.replace(target_json, new_json, 1)

# 6. Add B2B Bulk section in Add Product Modal
target_add_modal = """        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">चित्र URL (Image URL)</label>"""

add_b2b_section = """        <!-- B2B Wholesale & Bulk Orders Section -->
        <div class="sm:col-span-2 p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-3">
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="bulk_available" id="add_bulk_available" value="1" onchange="toggleBulkFields('add')"
                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300">
              <span class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                <i class="fa-solid fa-boxes-packing text-blue-700"></i>
                रिटेलर्स के लिए थोक / बल्क बिक्री सक्षम करें (Enable Wholesale Bulk Orders)
              </span>
            </label>
            <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded-full">
              B2B Wholesale
            </span>
          </div>

          <div id="add_bulk_options" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2 border-t border-blue-200/60">
            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                न्यूनतम थोक मात्रा (Bulk MOQ)
              </label>
              <input type="number" min="2" name="bulk_moq" id="add_bulk_moq" value="10" placeholder="e.g. 10"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
              <p class="text-[9px] text-gray-500 mt-0.5">न्यूनतम थोक ऑर्डर मात्रा (उदा. 10 पीस)</p>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                थोक मूल्य प्रति पीस (Bulk Unit Price ₹)
              </label>
              <input type="number" step="0.01" name="bulk_price" id="add_bulk_price" placeholder="उदा. 210.00"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
              <p class="text-[9px] text-gray-500 mt-0.5">रिटेलर को मिलने वाला डिस्काउंटेड प्रति पीस दाम</p>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                सुपर बल्क मात्रा (Tier 2 Qty)
              </label>
              <input type="number" min="10" name="bulk_tier2_qty" id="add_bulk_tier2_qty" placeholder="उदा. 50"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
              <p class="text-[9px] text-gray-500 mt-0.5">बड़ी लॉट मात्रा (उदा. 50+ पीस)</p>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                सुपर बल्क मूल्य (Tier 2 Price ₹)
              </label>
              <input type="number" step="0.01" name="bulk_tier2_price" id="add_bulk_tier2_price" placeholder="उदा. 190.00"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
              <p class="text-[9px] text-gray-500 mt-0.5">बड़ी लॉट का विशेष मूल्य</p>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                मास्टर पैकेजिंग विवरण
              </label>
              <input type="text" name="bulk_packaging_desc" id="add_bulk_packaging_desc" placeholder="उदा. 20 पीस का मास्टर कार्टन"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:border-blue-600 outline-none">
            </div>

            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">
                थोक डिस्पैच समय (Lead Days)
              </label>
              <input type="number" min="1" max="30" name="bulk_lead_time_days" id="add_bulk_lead_time_days" value="3"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
            </div>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-gray-700 mb-1">चित्र URL (Image URL)</label>"""

assert target_add_modal in content, "target_add_modal not found in seller/products.php"
content = content.replace(target_add_modal, add_b2b_section, 1)

# 7. Add B2B Bulk section in Edit Product Modal
target_edit_modal = """      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">स्टॉक मात्रा (Stock Units) <span class="text-red-500">*</span></label>"""

edit_b2b_section = """      <!-- B2B Wholesale & Bulk Orders Section (Edit) -->
      <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl space-y-3">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="bulk_available" id="s_edit_bulk_available" value="1" onchange="toggleBulkFields('s_edit')"
              class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300">
            <span class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
              <i class="fa-solid fa-boxes-packing text-blue-700"></i>
              रिटेलर्स के लिए थोक / बल्क बिक्री सक्षम करें (Enable Wholesale Bulk Orders)
            </span>
          </label>
          <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded-full">
            B2B Wholesale
          </span>
        </div>

        <div id="s_edit_bulk_options" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2 border-t border-blue-200/60">
          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              न्यूनतम थोक मात्रा (Bulk MOQ)
            </label>
            <input type="number" min="2" name="bulk_moq" id="s_edit_bulk_moq" value="10"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              थोक मूल्य प्रति पीस (Bulk Unit Price ₹)
            </label>
            <input type="number" step="0.01" name="bulk_price" id="s_edit_bulk_price"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              सुपर बल्क मात्रा (Tier 2 Qty)
            </label>
            <input type="number" min="10" name="bulk_tier2_qty" id="s_edit_bulk_tier2_qty"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              सुपर बल्क मूल्य (Tier 2 Price ₹)
            </label>
            <input type="number" step="0.01" name="bulk_tier2_price" id="s_edit_bulk_tier2_price"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              मास्टर पैकेजिंग विवरण
            </label>
            <input type="text" name="bulk_packaging_desc" id="s_edit_bulk_packaging_desc"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:border-blue-600 outline-none">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">
              थोक डिस्पैच समय (Lead Days)
            </label>
            <input type="number" min="1" max="30" name="bulk_lead_time_days" id="s_edit_bulk_lead_time_days" value="3"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-blue-600 outline-none">
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">स्टॉक मात्रा (Stock Units) <span class="text-red-500">*</span></label>"""

assert target_edit_modal in content, "target_edit_modal not found in seller/products.php"
content = content.replace(target_edit_modal, edit_b2b_section, 1)

# 8. Update JS openSellerEditProductModal and add toggleBulkFields
target_js_modal = """  document.getElementById('s_edit_seo_keywords').value = p.seo_keywords || '';
  document.getElementById('sellerEditProductModal').classList.remove('hidden');
}"""

new_js_modal = """  document.getElementById('s_edit_seo_keywords').value = p.seo_keywords || '';

  const isBulk = (parseInt(p.bulk_available) === 1);
  const chkBulk = document.getElementById('s_edit_bulk_available');
  if (chkBulk) chkBulk.checked = isBulk;
  document.getElementById('s_edit_bulk_moq').value = p.bulk_moq || 10;
  document.getElementById('s_edit_bulk_price').value = p.bulk_price || '';
  document.getElementById('s_edit_bulk_tier2_qty').value = p.bulk_tier2_qty || '';
  document.getElementById('s_edit_bulk_tier2_price').value = p.bulk_tier2_price || '';
  document.getElementById('s_edit_bulk_packaging_desc').value = p.bulk_packaging_desc || '';
  document.getElementById('s_edit_bulk_lead_time_days').value = p.bulk_lead_time_days || 3;
  toggleBulkFields('s_edit');

  document.getElementById('sellerEditProductModal').classList.remove('hidden');
}

function toggleBulkFields(prefix) {
  const chk = document.getElementById(prefix + '_bulk_available');
  const box = document.getElementById(prefix + '_bulk_options');
  if (chk && box) {
    if (chk.checked) {
      box.classList.remove('hidden');
    } else {
      box.classList.add('hidden');
    }
  }
}"""

assert target_js_modal in content, "target_js_modal not found in seller/products.php"
content = content.replace(target_js_modal, new_js_modal, 1)

with open('seller/products.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("seller/products.php updated successfully")
