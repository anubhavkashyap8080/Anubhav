import re

path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\seller\products.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update add_product in PHP (lines ~60-80)
old_insert_part = """        $stmt = $pdo->prepare("
            INSERT INTO products (
                code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
                discount, weight_kg, stock, stock_status, rating, review_count, image, unit, description,
                seo_title, seo_description, seo_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Stock', 5.0, 1, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $code, $vendor_id, $category_id, $name, $hindi_name, $slug, $price, $original_price,
            $discount, $weight_kg, $stock, $image_url, $unit, $description,
            $seo_title, $seo_description, $seo_keywords
        ]);"""

new_insert_part = """        $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
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

# 2. Update edit_product in PHP (lines ~90-130)
old_update_part = """            $sql_up = "UPDATE products SET name = ?, hindi_name = ?, category_id = ?, price = ?, original_price = ?, discount = ?, weight_kg = ?, stock = ?, unit = ?, description = ?, seo_title = ?, seo_description = ?, seo_keywords = ?";
            $params = [$name, $hindi_name, $category_id, $price, $original_price, $discount, $weight_kg, $stock, $unit, $description, $seo_title, $seo_description, $seo_keywords];"""

new_update_part = """            $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
            $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
            $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
            $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);

            $sql_up = "UPDATE products SET name = ?, hindi_name = ?, category_id = ?, price = ?, original_price = ?, discount = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, stock = ?, unit = ?, description = ?, seo_title = ?, seo_description = ?, seo_keywords = ?";
            $params = [$name, $hindi_name, $category_id, $price, $original_price, $discount, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $stock, $unit, $description, $seo_title, $seo_description, $seo_keywords];"""

norm_content = content.replace('\r\n', '\n')
norm_old_insert = old_insert_part.replace('\r\n', '\n')
norm_old_update = old_update_part.replace('\r\n', '\n')

if norm_old_insert in norm_content:
    norm_content = norm_content.replace(norm_old_insert, new_insert_part)
    print("SUCCESS: Updated add_product SQL in seller/products.php")
else:
    print("FAILED to find add_product SQL")

if norm_old_update in norm_content:
    norm_content = norm_content.replace(norm_old_update, new_update_part)
    print("SUCCESS: Updated edit_product SQL in seller/products.php")
else:
    print("FAILED to find edit_product SQL")

# 3. Add Dimensions block in Add Product Modal (around line 558)
old_add_dim = """        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">कुल भार (Weight in Kg - सटीक शिपिंग हेतु) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="weight_kg" required value="0.50"
            class="w-full px-3.5 py-2.5 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>"""

new_add_dim = """        <!-- Package Dimensions & Weight Box (Add) -->
        <div class="sm:col-span-2 p-4 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
              <i class="fa-solid fa-box-open text-amber-700"></i>
              पार्सल माप एवं वजन (Package Dimensions & Weight)
            </span>
            <span class="text-[10px] text-amber-800 font-semibold">सटीक कूरियर शिपिंग शुल्क गणना हेतु</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">वजन (Weight in kg) <span class="text-red-500">*</span></label>
              <input type="number" step="0.01" name="weight_kg" id="add_weight_kg" required value="0.50" oninput="updateAddVolumetric()"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">लंबाई (Length cm)</label>
              <input type="number" step="0.1" name="length_cm" id="add_length_cm" value="10.0" oninput="updateAddVolumetric()"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">चौड़ाई (Width cm)</label>
              <input type="number" step="0.1" name="width_cm" id="add_width_cm" value="10.0" oninput="updateAddVolumetric()"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-gray-700 mb-1">ऊंचाई (Height cm)</label>
              <input type="number" step="0.1" name="height_cm" id="add_height_cm" value="10.0" oninput="updateAddVolumetric()"
                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
            </div>
          </div>
          <div class="flex items-center justify-between text-[11px] text-amber-900 bg-white/90 p-2.5 rounded-xl border border-amber-200/60">
            <span>आयतनिक वजन (Volumetric Weight): <strong id="add_vol_preview" class="text-amber-800">0.20 kg</strong></span>
            <span class="text-[10px] text-gray-500 font-mono">Formula: (L × W × H) / 5000</span>
          </div>
        </div>"""

norm_old_add_dim = old_add_dim.replace('\r\n', '\n')
if norm_old_add_dim in norm_content:
    norm_content = norm_content.replace(norm_old_add_dim, new_add_dim)
    print("SUCCESS: Updated dimensions input in Add Product Modal")
else:
    print("FAILED to find dimensions in Add Product Modal")

# 4. Add Dimensions block in Edit Product Modal (around line 698)
old_edit_weight = """        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">वजन (kg)</label>
          <input type="number" step="0.01" name="weight_kg" id="s_edit_weight"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-amber-500 transition">
        </div>"""

new_edit_dim = """      </div>

      <!-- Package Dimensions & Weight Box (Edit) -->
      <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
            <i class="fa-solid fa-box-open text-amber-700"></i>
            पार्सल माप एवं वजन (Package Dimensions & Weight)
          </span>
          <span class="text-[10px] text-amber-800 font-semibold">कूरियर शिपिंग शुल्क हेतु</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">वजन (kg)</label>
            <input type="number" step="0.01" name="weight_kg" id="s_edit_weight" oninput="updateEditVolumetric()"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">लंबाई (Length cm)</label>
            <input type="number" step="0.1" name="length_cm" id="s_edit_length" value="10.0" oninput="updateEditVolumetric()"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">चौड़ाई (Width cm)</label>
            <input type="number" step="0.1" name="width_cm" id="s_edit_width" value="10.0" oninput="updateEditVolumetric()"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-700 mb-1">ऊंचाई (Height cm)</label>
            <input type="number" step="0.1" name="height_cm" id="s_edit_height" value="10.0" oninput="updateEditVolumetric()"
              class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold focus:border-amber-600 outline-none">
          </div>
        </div>
        <div class="flex items-center justify-between text-[11px] text-amber-900 bg-white/90 p-2.5 rounded-xl border border-amber-200/60">
          <span>आयतनिक वजन (Volumetric Weight): <strong id="edit_vol_preview" class="text-amber-800">0.20 kg</strong></span>
          <span class="text-[10px] text-gray-500 font-mono">Formula: (L × W × H) / 5000</span>
        </div>"""

norm_old_edit_weight = old_edit_weight.replace('\r\n', '\n')
if norm_old_edit_weight in norm_content:
    norm_content = norm_content.replace(norm_old_edit_weight, new_edit_dim)
    print("SUCCESS: Updated dimensions input in Edit Product Modal")
else:
    print("FAILED to find weight in Edit Product Modal")

# 5. Add JavaScript helpers for volumetric calculation & edit modal populate
old_js_populate = """  document.getElementById('s_edit_weight').value = p.weight_kg || 0.5;
  document.getElementById('s_edit_stock').value = p.stock || 50;"""

new_js_populate = """  document.getElementById('s_edit_weight').value = p.weight_kg || 0.5;
  document.getElementById('s_edit_length').value = p.length_cm || 10.0;
  document.getElementById('s_edit_width').value = p.width_cm || 10.0;
  document.getElementById('s_edit_height').value = p.height_cm || 10.0;
  updateEditVolumetric();
  document.getElementById('s_edit_stock').value = p.stock || 50;"""

norm_old_js_populate = old_js_populate.replace('\r\n', '\n')
if norm_old_js_populate in norm_content:
    norm_content = norm_content.replace(norm_old_js_populate, new_js_populate)
    print("SUCCESS: Updated JS openSellerEditProductModal")
else:
    print("FAILED to find JS openSellerEditProductModal")

# 6. Add JS functions updateAddVolumetric and updateEditVolumetric
js_helper = """
function updateAddVolumetric() {
  const l = parseFloat(document.getElementById('add_length_cm').value) || 0;
  const w = parseFloat(document.getElementById('add_width_cm').value) || 0;
  const h = parseFloat(document.getElementById('add_height_cm').value) || 0;
  const vol = ((l * w * h) / 5000).toFixed(2);
  const el = document.getElementById('add_vol_preview');
  if (el) el.innerText = vol + ' kg';
}

function updateEditVolumetric() {
  const l = parseFloat(document.getElementById('s_edit_length').value) || 0;
  const w = parseFloat(document.getElementById('s_edit_width').value) || 0;
  const h = parseFloat(document.getElementById('s_edit_height').value) || 0;
  const vol = ((l * w * h) / 5000).toFixed(2);
  const el = document.getElementById('edit_vol_preview');
  if (el) el.innerText = vol + ' kg';
}
"""

if '<script>' in norm_content:
    norm_content = norm_content.replace('<script>', '<script>' + js_helper)
    print("SUCCESS: Added JS volumetric calculation functions")

with open(path, 'w', encoding='utf-8') as f:
    f.write(norm_content)
