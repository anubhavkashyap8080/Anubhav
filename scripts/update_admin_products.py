import re

with open('admin/products.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update POST handler
post_needle = """    $seo_keywords = clean_input($_POST['seo_keywords'] ?? '');

    if ($pid > 0 && !empty($name) && $price > 0) {"""

post_replacement = """    $seo_keywords = clean_input($_POST['seo_keywords'] ?? '');

    // B2B Wholesale Bulk Settings
    $bulk_available = isset($_POST['bulk_available']) ? 1 : 0;
    $bulk_moq = max(1, intval($_POST['bulk_moq'] ?? 10));
    $bulk_price = floatval($_POST['bulk_price'] ?? 0);
    $bulk_tier2_qty = intval($_POST['bulk_tier2_qty'] ?? 0);
    $bulk_tier2_price = floatval($_POST['bulk_tier2_price'] ?? 0);
    $bulk_packaging_desc = clean_input($_POST['bulk_packaging_desc'] ?? '');
    $bulk_lead_time_days = max(1, intval($_POST['bulk_lead_time_days'] ?? 3));

    if ($pid > 0 && !empty($name) && $price > 0) {"""

content = content.replace(post_needle, post_replacement)

# 2. Update SQL UPDATE statement
sql_needle = """            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, hindi_name = ?, category_id = ?, vendor_id = ?, price = ?, original_price = ?,
                    discount = ?, stock = ?, stock_status = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, image = ?,
                    seo_title = ?, seo_description = ?, seo_keywords = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $hindi_name, $cat_id, $vendor_id, $price, $orig_price, $discount, $stock, $stock_status, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $image, $seo_title, $seo_description, $seo_keywords, $pid]);"""

sql_replacement = """            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, hindi_name = ?, category_id = ?, vendor_id = ?, price = ?, original_price = ?,
                    discount = ?, stock = ?, stock_status = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, image = ?,
                    seo_title = ?, seo_description = ?, seo_keywords = ?,
                    bulk_available = ?, bulk_moq = ?, bulk_price = ?, bulk_tier2_qty = ?, bulk_tier2_price = ?, bulk_packaging_desc = ?, bulk_lead_time_days = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $hindi_name, $cat_id, $vendor_id, $price, $orig_price, $discount, $stock, $stock_status, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $image, $seo_title, $seo_description, $seo_keywords, $bulk_available, $bulk_moq, $bulk_price, $bulk_tier2_qty, $bulk_tier2_price, $bulk_packaging_desc, $bulk_lead_time_days, $pid]);"""

content = content.replace(sql_needle, sql_replacement)

# 3. Update table price column to show bulk badge
price_needle = """            <td class="px-4 py-3">
              <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
              <?php if ($p['original_price'] > $p['price']): ?>
                <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
              <?php endif; ?>
            </td>"""

price_replacement = """            <td class="px-4 py-3">
              <div class="font-bold text-gray-900"><?= format_price($p['price']) ?></div>
              <?php if ($p['original_price'] > $p['price']): ?>
                <div class="text-[10px] text-gray-400 line-through"><?= format_price($p['original_price']) ?></div>
              <?php endif; ?>
              <?php if (!empty($p['bulk_available'])): ?>
                <div class="mt-1">
                  <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <i class="fa-solid fa-boxes-packing text-amber-600"></i> B2B: <?= format_price($p['bulk_price']) ?> (MOQ: <?= $p['bulk_moq'] ?>)
                  </span>
                </div>
              <?php endif; ?>
            </td>"""

content = content.replace(price_needle, price_replacement)

# 4. Update JSON data in openEditProductModal
json_needle = """                  "weight_kg" => $p["weight_kg"],
                  "image" => $p["image"],
                  "seo_title" => $p["seo_title"] ?? "",
                  "seo_description" => $p["seo_description"] ?? "",
                  "seo_keywords" => $p["seo_keywords"] ?? ""
              ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'"""

json_replacement = """                  "weight_kg" => $p["weight_kg"],
                  "length_cm" => $p["length_cm"] ?? 10,
                  "width_cm" => $p["width_cm"] ?? 10,
                  "height_cm" => $p["height_cm"] ?? 10,
                  "image" => $p["image"],
                  "seo_title" => $p["seo_title"] ?? "",
                  "seo_description" => $p["seo_description"] ?? "",
                  "seo_keywords" => $p["seo_keywords"] ?? "",
                  "bulk_available" => $p["bulk_available"] ?? 0,
                  "bulk_moq" => $p["bulk_moq"] ?? 10,
                  "bulk_price" => $p["bulk_price"] ?? 0,
                  "bulk_tier2_qty" => $p["bulk_tier2_qty"] ?? 0,
                  "bulk_tier2_price" => $p["bulk_tier2_price"] ?? 0,
                  "bulk_packaging_desc" => $p["bulk_packaging_desc"] ?? "",
                  "bulk_lead_time_days" => $p["bulk_lead_time_days"] ?? 3
              ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'"""

content = content.replace(json_needle, json_replacement)

# 5. Add B2B Bulk Settings section in modal right before SEO section
seo_section_needle = """      <!-- Dedicated Product On-Page SEO Section -->"""

b2b_modal_section = """      <!-- B2B Wholesale & Bulk Supply Section -->
      <div class="p-4 bg-gradient-to-br from-amber-50/70 to-orange-50/70 rounded-2xl border border-amber-200 space-y-3">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-boxes-packing text-amber-600"></i>
            थोक व रिटेलर बल्क सेटिंग (B2B Bulk Wholesale)
          </h4>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="bulk_available" id="edit_bulk_available" value="1" onchange="toggleAdminBulkFields()" class="sr-only peer">
            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-600"></div>
            <span class="ml-2 text-xs font-bold text-amber-950">बल्क बिक्री चालू करें</span>
          </label>
        </div>

        <div id="admin_bulk_fields_box" class="space-y-3 pt-2 border-t border-amber-200/60">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">न्यूनतम थोक ऑर्डर (MOQ - Pieces) <span class="text-red-500">*</span></label>
              <input type="number" name="bulk_moq" id="edit_bulk_moq" min="2" value="10"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-bold text-gray-900 focus:outline-none focus:border-amber-600">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">थोक दर प्रति पीस (Wholesale ₹/pc) <span class="text-red-500">*</span></label>
              <input type="number" step="0.01" name="bulk_price" id="edit_bulk_price"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-bold text-amber-700 focus:outline-none focus:border-amber-600">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">टियर 2 बल्क मात्रा (उदा. 50+ pcs)</label>
              <input type="number" name="bulk_tier2_qty" id="edit_bulk_tier2_qty" min="0"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-amber-600">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">टियर 2 विशेष दर (Tier 2 ₹/pc)</label>
              <input type="number" step="0.01" name="bulk_tier2_price" id="edit_bulk_tier2_price"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-medium text-amber-700 focus:outline-none focus:border-amber-600">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">डिस्पैच तैयारी समय (दिन)</label>
              <input type="number" name="bulk_lead_time_days" id="edit_bulk_lead_time_days" min="1" value="3"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-medium focus:outline-none focus:border-amber-600">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">मास्टर कार्टन / पैकेजिंग विवरण</label>
              <input type="text" name="bulk_packaging_desc" id="edit_bulk_packaging_desc" placeholder="उदा. 24 पीस का मजबूत कोरोगेटेड बॉक्स"
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-medium focus:outline-none focus:border-amber-600">
            </div>
          </div>
        </div>
      </div>

      <!-- Dedicated Product On-Page SEO Section -->"""

content = content.replace(seo_section_needle, b2b_modal_section)

# 6. Update JS function openEditProductModal and add toggleAdminBulkFields
js_needle = """  document.getElementById('edit_p_seo_keywords').value = p.seo_keywords || '';
  document.getElementById('editProductModal').classList.remove('hidden');
}"""

js_replacement = """  document.getElementById('edit_p_seo_keywords').value = p.seo_keywords || '';

  // B2B Bulk Settings
  const bulkChk = document.getElementById('edit_bulk_available');
  if (bulkChk) {
    bulkChk.checked = (p.bulk_available == 1 || p.bulk_available === '1' || p.bulk_available === true);
    document.getElementById('edit_bulk_moq').value = p.bulk_moq || 10;
    document.getElementById('edit_bulk_price').value = p.bulk_price || '';
    document.getElementById('edit_bulk_tier2_qty').value = p.bulk_tier2_qty || '';
    document.getElementById('edit_bulk_tier2_price').value = p.bulk_tier2_price || '';
    document.getElementById('edit_bulk_packaging_desc').value = p.bulk_packaging_desc || '';
    document.getElementById('edit_bulk_lead_time_days').value = p.bulk_lead_time_days || 3;
    toggleAdminBulkFields();
  }

  document.getElementById('editProductModal').classList.remove('hidden');
}

function toggleAdminBulkFields() {
  const chk = document.getElementById('edit_bulk_available');
  const box = document.getElementById('admin_bulk_fields_box');
  if (chk && box) {
    if (chk.checked) {
      box.classList.remove('opacity-40', 'pointer-events-none');
    } else {
      box.classList.add('opacity-40', 'pointer-events-none');
    }
  }
}"""

content = content.replace(js_needle, js_replacement)

with open('admin/products.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated admin/products.php successfully")
