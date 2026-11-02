import re

path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\product.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the Delivery Checker Widget HTML (around lines 348-372)
old_widget = """      <!-- Dynamic Delivery Pincode Checker -->
      <div class="p-5 bg-stone-50 rounded-3xl border border-gray-200 space-y-3 text-xs">
        <div class="flex items-center justify-between">
          <span class="font-bold text-gray-900 flex items-center gap-1.5">
            <i data-lucide="truck" class="w-4 h-4 text-brand-green"></i>
            <span>Check India Post Delivery Charges & ETA</span>
          </span>
          <span class="text-[11px] text-gray-500 font-mono">Origin: Hardoi (UP)</span>
        </div>

        <div class="flex gap-2">
          <input 
            type="text" 
            id="pincode-input-detail" 
            maxlength="6" 
            value="<?= htmlspecialchars($_SESSION['shipping_pincode'] ?? '241126') ?>" 
            placeholder="Enter 6-digit Pincode" 
            class="flex-1 p-2.5 bg-white border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 outline-none focus:border-brand-green"
          >
          <button onclick="checkDetailPincode()" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs cursor-pointer">
            Check Rate
          </button>
        </div>
        <div id="pincode-detail-result" class="text-xs"></div>
      </div>"""

new_widget = """      <!-- Dynamic Multi-Carrier Delivery Partners & Real-Time Shipping Estimator -->
      <div class="p-5 bg-gradient-to-br from-stone-50 to-amber-50/30 rounded-3xl border border-amber-200/70 space-y-3 text-xs shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-1">
          <span class="font-bold text-gray-900 flex items-center gap-1.5">
            <i class="fa-solid fa-truck-fast text-brand-green"></i>
            <span>All-India Delivery Partners & Shipping Cost</span>
          </span>
          <span class="text-[10px] text-amber-900 bg-amber-100/70 px-2.5 py-0.5 rounded-full font-bold">
            Hub: Sandila (241204)
          </span>
        </div>
        <p class="text-[11px] text-gray-600 leading-snug">
          Check live shipping charges and delivery time to your doorstep across 19,000+ Indian PIN codes via India Post, Delhivery, Blue Dart & Shiprocket.
        </p>

        <div class="flex gap-2">
          <input 
            type="text" 
            id="pincode-input-detail" 
            maxlength="6" 
            value="<?= htmlspecialchars($_SESSION['shipping_pincode'] ?? '241126') ?>" 
            placeholder="Enter 6-digit Indian PIN Code" 
            class="flex-1 p-2.5 bg-white border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 outline-none focus:border-brand-green shadow-xs"
          >
          <button onclick="checkDetailPincode()" class="px-4 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl text-xs cursor-pointer transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-calculator"></i>
            <span>Estimate Cost</span>
          </button>
        </div>
        
        <!-- Package Specs Tag -->
        <div class="flex items-center justify-between text-[10px] text-gray-500 pt-0.5 px-0.5">
          <span>Package: <strong><?= number_format((float)($product['weight_kg'] ?? 0.5), 2) ?> kg</strong></span>
          <span>Size: <strong><?= number_format((float)($product['length_cm'] ?? 10), 1) ?> × <?= number_format((float)($product['width_cm'] ?? 10), 1) ?> × <?= number_format((float)($product['height_cm'] ?? 10), 1) ?> cm</strong></span>
        </div>

        <div id="pincode-detail-result" class="text-xs space-y-2"></div>
      </div>"""

# 2. Update checkDetailPincode() JS in product.php
old_js_check = """function checkDetailPincode() {
  const pin = document.getElementById('pincode-input-detail').value.trim();
  const resDiv = document.getElementById('pincode-detail-result');
  if (pin.length !== 6) {
    resDiv.innerHTML = '<span class="text-red-600">Please enter a valid 6-digit PIN code.</span>';
    return;
  }
  resDiv.innerHTML = '<span class="text-gray-500">Calculating accurate delivery rate...</span>';

  const formData = new FormData();
  formData.append('pincode', pin);
  formData.append('subtotal', <?= $product['price'] ?>);
  formData.append('total_weight', <?= $product['weight_kg'] ?>);

  fetch('/ajax/calculate_shipping.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const d = data.data;
        resDiv.innerHTML = `
          <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 space-y-1">
            <div class="flex justify-between font-bold">
              <span>Delivery Fee: ${d.shipping_fee_formatted}</span>
              <span>ETA: ${d.eta_text}</span>
            </div>
            <p class="text-[11px] text-emerald-700">${d.zone_name} via ${d.carrier}</p>
          </div>
        `;
      } else {
        resDiv.innerHTML = `<span class="text-red-600">${data.message}</span>`;
      }
    });
}"""

new_js_check = """function checkDetailPincode() {
  const pin = document.getElementById('pincode-input-detail').value.trim();
  const resDiv = document.getElementById('pincode-detail-result');
  if (pin.length !== 6 || isNaN(pin)) {
    resDiv.innerHTML = '<div class="p-3 bg-red-50 text-red-700 rounded-xl font-medium text-xs"><i class="fa-solid fa-circle-exclamation mr-1"></i> कृपया 6 अंकों का सही भारतीय पिनकोड दर्ज करें।</div>';
    return;
  }
  resDiv.innerHTML = '<div class="p-3 bg-stone-100 text-stone-600 rounded-xl font-medium text-xs flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin text-amber-600"></i> सभी डिलीवरी पार्टनर्स से लाइव शिपिंग दरें खोजी जा रही हैं...</div>';

  const formData = new FormData();
  formData.append('pincode', pin);
  formData.append('product_id', <?= (int)$product['id'] ?>);
  formData.append('subtotal', <?= (float)$product['price'] ?>);
  formData.append('total_weight', <?= (float)($product['weight_kg'] ?? 0.5) ?>);
  formData.append('length_cm', <?= (float)($product['length_cm'] ?? 10.0) ?>);
  formData.append('width_cm', <?= (float)($product['width_cm'] ?? 10.0) ?>);
  formData.append('height_cm', <?= (float)($product['height_cm'] ?? 10.0) ?>);

  fetch('/ajax/calculate_shipping.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const d = data.data;
        let partnersHtml = '';
        if (d.delivery_partners && d.delivery_partners.length > 0) {
          d.delivery_partners.forEach(p => {
            const isRec = p.is_recommended ? '<span class="text-[9px] font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full">अनुशंसित (Recommended)</span>' : '';
            partnersHtml += `
              <div class="p-2.5 bg-white border border-stone-200 rounded-xl flex items-center justify-between gap-2 shadow-2xs hover:border-amber-400 transition">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center text-stone-700 flex-shrink-0 text-sm">
                    <i class="${p.icon || 'fa-solid fa-truck'}"></i>
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="font-bold text-gray-900 truncate">${p.courier_name}</span>
                      ${isRec}
                    </div>
                    <p class="text-[10px] text-gray-500 font-medium">${p.reach} • <strong>${p.eta_days}</strong></p>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="font-black text-sm ${p.is_free ? 'text-emerald-700' : 'text-gray-900'}">${p.rate_formatted}</span>
                  <span class="block text-[9px] text-emerald-700 font-semibold">${p.badge}</span>
                </div>
              </div>
            `;
          });
        }

        resDiv.innerHTML = `
          <div class="space-y-2 mt-2 pt-2 border-t border-amber-200/50">
            <div class="flex items-center justify-between text-[11px] font-bold text-stone-800">
              <span>उपलब्ध डिलीवरी पार्टनर्स (${d.zone_name})</span>
              <span class="text-[10px] font-mono text-gray-500">Billable: ${d.billable_weight_kg} kg</span>
            </div>
            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
              ${partnersHtml}
            </div>
            <div class="text-[10px] text-stone-500 flex items-center justify-between pt-1">
              <span><i class="fa-solid fa-shield-check text-emerald-600"></i> Cash on Delivery Available</span>
              <span><i class="fa-solid fa-box text-amber-600"></i> 100% Insured Transit</span>
            </div>
          </div>
        `;
      } else {
        resDiv.innerHTML = `<div class="p-3 bg-red-50 text-red-600 rounded-xl text-xs"><i class="fa-solid fa-circle-exclamation mr-1"></i> ${data.message}</div>`;
      }
    })
    .catch(err => {
      resDiv.innerHTML = '<div class="p-2.5 bg-red-50 text-red-600 rounded-xl text-xs">दर गणना में अस्थायी त्रुटि। कृपया पुनः प्रयास करें।</div>';
    });
}"""

norm_content = content.replace('\r\n', '\n')
norm_old_widget = old_widget.replace('\r\n', '\n')
norm_old_js = old_js_check.replace('\r\n', '\n')

if norm_old_widget in norm_content:
    norm_content = norm_content.replace(norm_old_widget, new_widget)
    print("SUCCESS: Updated Delivery Widget in product.php")
else:
    print("FAILED to find old widget in product.php")

if norm_old_js in norm_content:
    norm_content = norm_content.replace(norm_old_js, new_js_check)
    print("SUCCESS: Updated checkDetailPincode() JS in product.php")
else:
    print("FAILED to find old checkDetailPincode() JS in product.php")

with open(path, 'w', encoding='utf-8') as f:
    f.write(norm_content)
