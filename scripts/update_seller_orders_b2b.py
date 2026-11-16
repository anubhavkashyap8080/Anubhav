with open('seller/orders.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update table row
needle_tr = """                <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M Y', strtotime($ord['created_at'])) ?></span>
                </td>
                <td class="px-4 py-3.5">
                  <div class="font-bold text-gray-900"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <div class="text-[10px] text-gray-500"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>"""

replacement_tr = """                <td class="px-4 py-3.5 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M Y', strtotime($ord['created_at'])) ?></span>
                  <?php if (!empty($ord['order_type']) && $ord['order_type'] === 'wholesale_b2b'): ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-900 border border-amber-300 mt-1">
                      <i class="fa-solid fa-boxes-packing text-amber-600"></i> B2B थोक
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3.5">
                  <div class="font-bold text-gray-900"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <?php if (!empty($ord['business_name'])): ?>
                    <div class="text-[10px] font-bold text-amber-800"><?= htmlspecialchars($ord['business_name']) ?></div>
                  <?php endif; ?>
                  <div class="text-[10px] text-gray-500"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>"""

if needle_tr in content:
    content = content.replace(needle_tr, replacement_tr)
else:
    needle_tr_crlf = needle_tr.replace('\n', '\r\n')
    replacement_tr_crlf = replacement_tr.replace('\n', '\r\n')
    content = content.replace(needle_tr_crlf, replacement_tr_crlf)

# 2. Update order detail box
needle_detail = """        <!-- Customer Address Block -->
        <div class="bg-stone-50 p-4 rounded-xl text-xs space-y-1 mb-4 border border-gray-100">"""

replacement_detail = """        <?php if (!empty($selected_order['order_type']) && $selected_order['order_type'] === 'wholesale_b2b'): ?>
          <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-950 font-bold mb-3 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
              <i class="fa-solid fa-boxes-packing text-amber-600"></i>
              B2B थोक: <?= htmlspecialchars($selected_order['business_name'] ?? 'Retailer Store') ?>
            </span>
            <span class="font-mono text-emerald-800">
              GST: <?= htmlspecialchars($selected_order['gstin'] ?: 'Unregistered') ?>
            </span>
          </div>
        <?php endif; ?>

        <!-- Customer Address Block -->
        <div class="bg-stone-50 p-4 rounded-xl text-xs space-y-1 mb-4 border border-gray-100">"""

if needle_detail in content:
    content = content.replace(needle_detail, replacement_detail)
else:
    needle_detail_crlf = needle_detail.replace('\n', '\r\n')
    replacement_detail_crlf = replacement_detail.replace('\n', '\r\n')
    content = content.replace(needle_detail_crlf, replacement_detail_crlf)

with open('seller/orders.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated seller/orders.php successfully")
