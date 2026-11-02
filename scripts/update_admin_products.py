import re

path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\products.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update PHP POST handler in admin/products.php
old_php = """    $weight_kg = floatval($_POST['weight_kg'] ?? 0.5);
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
            $stmt->execute([$name, $hindi_name, $cat_id, $vendor_id, $price, $orig_price, $discount, $stock, $stock_status, $weight_kg, $image, $seo_title, $seo_description, $seo_keywords, $pid]);"""

new_php = """    $weight_kg = max(0.01, floatval($_POST['weight_kg'] ?? 0.5));
    $length_cm = max(1.0, floatval($_POST['length_cm'] ?? 10.0));
    $width_cm = max(1.0, floatval($_POST['width_cm'] ?? 10.0));
    $height_cm = max(1.0, floatval($_POST['height_cm'] ?? 10.0));
    $volumetric_weight = round(($length_cm * $width_cm * $height_cm) / 5000.0, 2);
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
                    discount = ?, stock = ?, stock_status = ?, weight_kg = ?, length_cm = ?, width_cm = ?, height_cm = ?, volumetric_weight = ?, image = ?,
                    seo_title = ?, seo_description = ?, seo_keywords = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $hindi_name, $cat_id, $vendor_id, $price, $orig_price, $discount, $stock, $stock_status, $weight_kg, $length_cm, $width_cm, $height_cm, $volumetric_weight, $image, $seo_title, $seo_description, $seo_keywords, $pid]);"""

norm_content = content.replace('\r\n', '\n')
norm_old_php = old_php.replace('\r\n', '\n')

if norm_old_php in norm_content:
    norm_content = norm_content.replace(norm_old_php, new_php)
    print("SUCCESS: Updated PHP POST handler in admin/products.php")
else:
    print("FAILED to find PHP POST handler in admin/products.php")

# 2. Add Package Dimensions & Weight HTML in Edit Product modal
old_weight_html = """        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">वजन (kg)</label>
          <input type="number" step="0.01" name="weight_kg" id="edit_p_weight"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>"""

new_weight_html = """        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">वजन (kg) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="weight_kg" id="edit_p_weight" oninput="updateAdminVolumetric()"
            class="w-full px-3 py-2 bg-stone-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:border-indigo-600 transition">
        </div>
      </div>

      <!-- Package Dimensions for Shipping -->
      <div class="p-3 bg-stone-50 border border-stone-200 rounded-2xl space-y-2">
        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
          <i class="fa-solid fa-box text-indigo-600"></i> पार्सल माप (Package Dimensions - cm)
        </span>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-[10px] text-gray-500 font-bold mb-0.5">लंबाई (Length cm)</label>
            <input type="number" step="0.1" name="length_cm" id="edit_p_length" value="10.0" oninput="updateAdminVolumetric()"
              class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold focus:border-indigo-600 outline-none">
          </div>
          <div>
            <label class="block text-[10px] text-gray-500 font-bold mb-0.5">चौड़ाई (Width cm)</label>
            <input type="number" step="0.1" name="width_cm" id="edit_p_width" value="10.0" oninput="updateAdminVolumetric()"
              class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold focus:border-indigo-600 outline-none">
          </div>
          <div>
            <label class="block text-[10px] text-gray-500 font-bold mb-0.5">ऊंचाई (Height cm)</label>
            <input type="number" step="0.1" name="height_cm" id="edit_p_height" value="10.0" oninput="updateAdminVolumetric()"
              class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold focus:border-indigo-600 outline-none">
          </div>
        </div>
        <div class="text-[10px] text-gray-500 flex justify-between">
          <span>Volumetric Weight: <strong id="admin_vol_preview" class="text-indigo-700">0.20 kg</strong></span>
          <span>Formula: (L × W × H) / 5000</span>
        </div>"""

norm_old_weight_html = old_weight_html.replace('\r\n', '\n')
if norm_old_weight_html in norm_content:
    norm_content = norm_content.replace(norm_old_weight_html, new_weight_html)
    print("SUCCESS: Updated weight & dimensions HTML in admin/products.php")
else:
    print("FAILED to find weight HTML in admin/products.php")

# 3. Update JavaScript openEditProductModal to populate dimensions
old_js = """    document.getElementById('edit_p_weight').value = p.weight_kg || 0.5;
    document.getElementById('edit_p_image').value = p.image || '';"""

new_js = """    document.getElementById('edit_p_weight').value = p.weight_kg || 0.5;
    document.getElementById('edit_p_length').value = p.length_cm || 10.0;
    document.getElementById('edit_p_width').value = p.width_cm || 10.0;
    document.getElementById('edit_p_height').value = p.height_cm || 10.0;
    updateAdminVolumetric();
    document.getElementById('edit_p_image').value = p.image || '';"""

norm_old_js = old_js.replace('\r\n', '\n')
if norm_old_js in norm_content:
    norm_content = norm_content.replace(norm_old_js, new_js)
    print("SUCCESS: Updated JS openEditProductModal in admin/products.php")
else:
    print("FAILED to find JS openEditProductModal in admin/products.php")

admin_js_helper = """
function updateAdminVolumetric() {
  const l = parseFloat(document.getElementById('edit_p_length').value) || 0;
  const w = parseFloat(document.getElementById('edit_p_width').value) || 0;
  const h = parseFloat(document.getElementById('edit_p_height').value) || 0;
  const vol = ((l * w * h) / 5000).toFixed(2);
  const el = document.getElementById('admin_vol_preview');
  if (el) el.innerText = vol + ' kg';
}
"""

if '<script>' in norm_content:
    norm_content = norm_content.replace('<script>', '<script>' + admin_js_helper)
    print("SUCCESS: Added updateAdminVolumetric JS helper")

with open(path, 'w', encoding='utf-8') as f:
    f.write(norm_content)
