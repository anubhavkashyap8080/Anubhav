with open('seller/dashboard.php', 'r', encoding='utf-8') as f:
    content = f.read()

needle_sel = """            o.shipping_pincode,
            o.total_amount,
            o.order_status,"""

replacement_sel = """            o.shipping_pincode,
            o.total_amount,
            o.order_status,
            o.order_type,
            o.business_name,"""

if needle_sel in content:
    content = content.replace(needle_sel, replacement_sel)
else:
    needle_sel_crlf = needle_sel.replace('\n', '\r\n')
    replacement_sel_crlf = replacement_sel.replace('\n', '\r\n')
    content = content.replace(needle_sel_crlf, replacement_sel_crlf)

needle_row = """                <td class="px-4 py-3 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M, h:i A', strtotime($ord['created_at'])) ?></span>
                </td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-gray-800"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <div class="text-[10px] text-gray-400"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>"""

replacement_row = """                <td class="px-4 py-3 font-mono font-bold text-gray-900">
                  #<?= htmlspecialchars($ord['order_number']) ?>
                  <span class="block text-[10px] font-normal text-gray-400"><?= date('d M, h:i A', strtotime($ord['created_at'])) ?></span>
                  <?php if (!empty($ord['order_type']) && $ord['order_type'] === 'wholesale_b2b'): ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-900 border border-amber-300 mt-0.5">
                      <i class="fa-solid fa-boxes-packing text-amber-600"></i> B2B Wholesale
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-gray-800"><?= htmlspecialchars($ord['customer_name']) ?></div>
                  <?php if (!empty($ord['business_name'])): ?>
                    <div class="text-[10px] font-bold text-amber-800"><?= htmlspecialchars($ord['business_name']) ?></div>
                  <?php endif; ?>
                  <div class="text-[10px] text-gray-400"><?= htmlspecialchars($ord['shipping_city']) ?>, <?= htmlspecialchars($ord['shipping_pincode']) ?></div>
                </td>"""

if needle_row in content:
    content = content.replace(needle_row, replacement_row)
else:
    needle_row_crlf = needle_row.replace('\n', '\r\n')
    replacement_row_crlf = replacement_row.replace('\n', '\r\n')
    content = content.replace(needle_row_crlf, replacement_row_crlf)

with open('seller/dashboard.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated seller/dashboard.php successfully")
