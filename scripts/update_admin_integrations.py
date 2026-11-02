import re

path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\integrations.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update $allowed_keys
old_allowed = """        // Payment Gateways
        'razorpay_enabled',
        'razorpay_key_id',
        'payu_enabled',
        'payu_merchant_key',
        'payu_mode',
        'cod_enabled'
    ];"""

new_allowed = """        // Payment Gateways
        'razorpay_enabled',
        'razorpay_key_id',
        'payu_enabled',
        'payu_merchant_key',
        'payu_mode',
        'cod_enabled',
        // Multi-Carrier Delivery Partners & Logistics Hub
        'indiapost_enabled',
        'shipping_base_local',
        'shipping_base_up',
        'shipping_base_north',
        'shipping_base_other',
        'shipping_base_remote',
        'shipping_heavy_per_kg',
        'free_shipping_threshold',
        'shiprocket_enabled',
        'shiprocket_email',
        'shiprocket_password',
        'shiprocket_pickup_pincode',
        'delhivery_enabled',
        'delhivery_token',
        'delhivery_client_id',
        'nimbuspost_enabled',
        'nimbuspost_secret'
    ];"""

norm_content = content.replace('\r\n', '\n')
norm_old_allowed = old_allowed.replace('\r\n', '\n')

if norm_old_allowed in norm_content:
    norm_content = norm_content.replace(norm_old_allowed, new_allowed)
    print("SUCCESS: Updated $allowed_keys in admin/integrations.php")
else:
    print("FAILED to find $allowed_keys in admin/integrations.php")

# 2. Add Delivery Partners section right after Payment Gateways section
old_after_payu = """        <!-- PayU Box -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span> PayU India
            </span>
            <input type="checkbox" name="payu_enabled" value="1" <?= ($settings_raw['payu_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Key</label>
            <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars($settings_raw['payu_merchant_key'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Salt (Masked, Config/Env)</label>
            <input type="text" name="payu_merchant_salt" readonly value="<?= htmlspecialchars(mask_secret(defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : ($settings_raw['payu_merchant_salt'] ?? ''))) ?>" class="w-full px-3 py-2 bg-stone-100 border border-stone-300 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
            <span class="text-[10px] text-gray-400 mt-0.5 block">Managed via config/env for security</span>
          </div>
        </div>
      </div>
    </div>"""

new_delivery_section = """        <!-- PayU Box -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span> PayU India
            </span>
            <input type="checkbox" name="payu_enabled" value="1" <?= ($settings_raw['payu_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Key</label>
            <input type="text" name="payu_merchant_key" value="<?= htmlspecialchars($settings_raw['payu_merchant_key'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Merchant Salt (Masked, Config/Env)</label>
            <input type="text" name="payu_merchant_salt" readonly value="<?= htmlspecialchars(mask_secret(defined('PAYU_MERCHANT_SALT') ? PAYU_MERCHANT_SALT : ($settings_raw['payu_merchant_salt'] ?? ''))) ?>" class="w-full px-3 py-2 bg-stone-100 border border-stone-300 rounded-xl text-xs font-mono text-gray-600 cursor-not-allowed">
            <span class="text-[10px] text-gray-400 mt-0.5 block">Managed via config/env for security</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Multi-Carrier Delivery Partners & Logistics Hub (Last-Mile Rural Reach) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-sm space-y-6">
      <div class="border-b border-stone-100 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
            All-India Logistics & Rural Delivery
          </span>
          <h2 class="text-base font-extrabold text-gray-900 mt-1 flex items-center gap-2">
            <i class="fa-solid fa-truck-fast text-amber-600"></i>
            Delivery Partners Hub (19,000+ PIN Codes Reach)
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Real-time shipping rate estimation, volumetric weight calculation & deep rural last-mile reach.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Multi-Carrier Active
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Partner 1: India Post Speed Post -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-envelope-open-text"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">India Post (Speed Post / पार्सल)</h3>
                <p class="text-[10px] text-gray-500 font-semibold">100% Pan-India Reach (1.55 Lakh Post Offices & Every Village)</p>
              </div>
            </div>
            <input type="checkbox" name="indiapost_enabled" value="1" <?= ($settings_raw['indiapost_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-amber-600">
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Local / Sandila Base (₹)</label>
              <input type="number" step="1" name="shipping_base_local" value="<?= htmlspecialchars($settings_raw['shipping_base_local'] ?? '30') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Within UP Base (₹)</label>
              <input type="number" step="1" name="shipping_base_up" value="<?= htmlspecialchars($settings_raw['shipping_base_up'] ?? '40') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">North India Base (₹)</label>
              <input type="number" step="1" name="shipping_base_north" value="<?= htmlspecialchars($settings_raw['shipping_base_north'] ?? '55') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Rest of India Base (₹)</label>
              <input type="number" step="1" name="shipping_base_other" value="<?= htmlspecialchars($settings_raw['shipping_base_other'] ?? '65') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
            </div>
          </div>
        </div>

        <!-- Partner 2: Shiprocket Multi-Carrier API -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-rocket"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Shiprocket API (14+ Couriers)</h3>
                <p class="text-[10px] text-gray-500 font-semibold">Delhivery, Blue Dart, Xpressbees, Shadowfax, DTDC</p>
              </div>
            </div>
            <input type="checkbox" name="shiprocket_enabled" value="1" <?= ($settings_raw['shiprocket_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
          </div>

          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Shiprocket Registered Email</label>
            <input type="email" name="shiprocket_email" value="<?= htmlspecialchars($settings_raw['shiprocket_email'] ?? '') ?>" placeholder="e.g. logistics@ganvgalihat.com" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Shiprocket Password / Key</label>
              <input type="password" name="shiprocket_password" value="<?= htmlspecialchars($settings_raw['shiprocket_password'] ?? '') ?>" placeholder="••••••••" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
            </div>
            <div>
              <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Pickup PIN Code</label>
              <input type="text" maxlength="6" name="shiprocket_pickup_pincode" value="<?= htmlspecialchars($settings_raw['shiprocket_pickup_pincode'] ?? '241204') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono font-bold">
            </div>
          </div>
        </div>

        <!-- Partner 3: Delhivery Direct -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-red-700 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-truck-ramp-box"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Delhivery Direct Express</h3>
                <p class="text-[10px] text-gray-500 font-semibold">18,500+ PIN Codes B2C Surface Fleet</p>
              </div>
            </div>
            <input type="checkbox" name="delhivery_enabled" value="1" <?= ($settings_raw['delhivery_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-red-600">
          </div>

          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Delhivery Client Token / API Key</label>
            <input type="text" name="delhivery_token" value="<?= htmlspecialchars($settings_raw['delhivery_token'] ?? '') ?>" placeholder="e.g. delh_live_api_token" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs font-mono">
          </div>
          <div>
            <label class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Client ID / Warehouse Name</label>
            <input type="text" name="delhivery_client_id" value="<?= htmlspecialchars($settings_raw['delhivery_client_id'] ?? 'GanvGaliHat_Sandila') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl text-xs">
          </div>
        </div>

        <!-- Global Thresholds & Volumetric Rules -->
        <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <i class="fa-solid fa-tags"></i>
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-gray-900">Free Shipping & Heavy Parcel Rules</h3>
                <p class="text-[10px] text-gray-500 font-semibold">Automatic Threshold Discounts & Volumetric Weight Formula</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Free Shipping Threshold (₹)</label>
              <input type="number" step="1" name="free_shipping_threshold" value="<?= htmlspecialchars($settings_raw['free_shipping_threshold'] ?? '499') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold text-emerald-700">
              <span class="text-[10px] text-gray-400 mt-0.5 block">Orders above this get free delivery</span>
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-500 block mb-1">Heavy Parcel Surcharge (₹ / kg)</label>
              <input type="number" step="1" name="shipping_heavy_per_kg" value="<?= htmlspecialchars($settings_raw['shipping_heavy_per_kg'] ?? '15') ?>" class="w-full px-3 py-2 bg-white border border-stone-300 rounded-xl font-bold">
              <span class="text-[10px] text-gray-400 mt-0.5 block">Applied for parcels exceeding 1 kg</span>
            </div>
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed">
            <i class="fa-solid fa-cube text-amber-700 mr-1"></i>
            <strong>Volumetric Weight Rule:</strong> <code>(L × W × H) / 5000</code>. Couriers bill on whichever is higher between dead weight and volumetric weight.
          </div>
        </div>
      </div>
    </div>"""

norm_old_after_payu = old_after_payu.replace('\r\n', '\n')

if norm_old_after_payu in norm_content:
    norm_content = norm_content.replace(norm_old_after_payu, new_delivery_section)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(norm_content)
    print("SUCCESS: Added Delivery Partners section in admin/integrations.php")
else:
    print("FAILED to find old_after_payu in admin/integrations.php")
